<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

include_once dirname(__FILE__). '/config.php';

function mod_blog_main($id = null) {
	global $SYS_BLOG_ID, $SYS_REPORTER;

	$blk_id = ($_REQUEST['blk_id'])?($_REQUEST['blk_id']):($_REQUEST['pid']);
	if ($id==$blk_id) {
		include_once dirname(__FILE__). '/block.php';
		return mod_blog_block($blk_id);
	}
	$p = mysql_uniq("select * from blog_data where id = %s",
					mysql_num($id));
	$pid = $p['pid'];
	
	$SYS_BLOG_ID = $pid;

	$b = mysql_uniq("select * from blog_data where id = %s",
					mysql_num($id));

	if (!$b) {
		return mod_blog_top($id);
	}

	$date_str = date('Y年n月d日 G時i分', strtotime($b['initymd']));

	$map = view_map($id);

	if (!$SYS_REPORTER['auth_mode']) {
		$comment = load_comment($id);
		$trackback = load_trackback($id);
	}

	$body = stripTagsIfForhidden( $b["body"] );

	$html = <<<__BLOG_BODY__
<div class="mod_blog_main">
<div class="mod_blog_main_content">

<div style="font-size: 0.8em; text-align: right;">${date_str}</div>
<h4 class="mod_blog_main_title">${b["subject"]}</h4>
${body}
${map}
${comment}
${trackback}
</div></div>
<br clear="all">
__BLOG_BODY__;
	;

	return $html;
}

function mod_blog_main_pager() {
	global $JQUERY;

	$id    = isset($_REQUEST['pid']) ? intval($_REQUEST['pid']) : '';
	$ym    = isset($_REQUEST['ym']) ? $_REQUEST['ym'] : '';
	$page  = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;

	$config = array('view_type' => 1,
					'view_num'  => 5);

	$c = mysql_uniq('select * from blog_setting where id = %s',
					mysql_num($id));
	if ($c) {
		$config['view_type'] = $c['view_type'];
		$config['view_num']  = $c['view_num'];
	}

	$add_query = '';
	if (preg_match('/^[0-9]+-[0-9]+$/', $ym)) {
		$add_query = sprintf(' and DATE_FORMAT(d.initymd, \'%%Y-%%m\') = %s',
							 mysql_str($ym));
	}

	$c = mysql_uniq('select count(*) as total from blog_data as d'.
					' inner join owner as o on d.id = o.id'.
					' inner join element as e on d.id = e.id'.
					' left join unit as u on e.unit = u.id'.
					' where d.pid = %s'.
					' and (e.unit <= %s or u.uid = %s or o.uid = %s)',
					mysql_num($block_id), mysql_num(public_status($block_id)),
					mysql_num(myuid()), mysql_num(myuid()));

	$total  = $c['total'];
	$offset = ($page - 1) * $config['view_num'] + 1;
	$limit  = $config['view_num'];

	$pagecount = intval($total / $limit + 0.5);

	$href = '/index.php?module=blog&blk_id='. $id;

	$JQUERY['ready'][] = <<<__JQ_CODE__
$("#pager_${id}").pager({
	pagenumber: ${page},
	pagecount: ${pagecount},
	buttonClickCallback: function(pageclickednumber) {
		location.href = '${href}&page=' + pageclickednumber;
	}
});
__JQ_CODE__;
	;

	return '<div class="navi_pager" id="pager_'. $id. '"></div>';
}

function mod_blog_top($id = null) {
	global $CUR_BLOG;

	$CUR_BLOG = $id;

	$config = array('view_type' => 1,
					'view_num'  => 5);

	$c = mysql_uniq('select * from blog_setting where id = %s',
					mysql_num($id));
	if ($c) {
		$config['view_type'] = $c['view_type'];
		$config['view_num']  = $c['view_num'];
	}

	$f = mysql_full("select d.* from blog_data as d".
					" inner join element on d.id = element.id".
					" left join unit on element.unit = unit.id".
					" where d.pid = %s".
					" and (element.unit <= %s or unit.uid = %s)".
					" order by d.initymd DESC limit %s;",
					mysql_num($id), mysql_num(public_status($id)),
					mysql_num(myuid()), mysql_num($config['view_num']));

	$blog = array();
	if ($f) {
		while ($r = mysql_fetch_array($f)) {
			$blog[] = array(id      => $r['id'],
							subject => $r["subject"],
							body    => $r["body"],
							initymd => $r["initymd"]);
		}
	}
	else {
		return '不明なブログです。';
	}
	unset($f);
	unset($r);

	$html = '';
	foreach ($blog as $b) {
		$comment   = count_comment($b['id']);
		$trackback = 0;

		$date_str = date('Y年n月d日 G時i分', strtotime($b['initymd']));
		$href = "/index.php?module=blog&eid=$b[id]&blk_id=$b[blk_id]";

		$html .= <<<__BLOG_BODY__
<div class="mod_blog_main">
<div class="mod_blog_main_date">${date_str}</div>
<h3 class="mod_blog_main_subject"><a href="${href}">${b["subject"]}</a></h4>
<div class="mod_blog_main_body">
${b["body"]}
</div>
<div class="mod_blog_main_opt">
<a href="${href}#comment">Comment (${comment})</a> 
<a href="${href}#trackback">Trackback (${trackback})</a>
</div>
</div>
__BLOG_BODY__;
		;
	}
	return $html;
}

?>
