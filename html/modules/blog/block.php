<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

include_once dirname(__FILE__). '/config.php';

function mod_blog_block($id) {
	global $SYS_BLOG_ID;
	global $COMUNI_HEAD_JS, $JQUERY;

//	$COMUNI_HEAD_JS[] = CONF_URLBASE. '/modules/blog/jquery.linkwrapper-1.0.3.js';
//	$JQUERY['ready'][] = '$(".wbr").linkwrapper();';

	$config = array('view_type' => 1,
					'view_num'  => 5);

	$c = mysql_uniq('select * from blog_setting where id = %s',
					mysql_num($id));
	if ($c) {
		$config['view_type'] = $c['view_type'];
		$config['view_num']  = $c['view_num'];
	}

	$blog = array();

	$page     = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
	$offset = ($page - 1) * $config['view_num'];
	$f = mysql_full("select d.* from blog_data as d".
					" inner join owner on d.id = owner.id".
					" inner join element on d.id = element.id".
					" left join unit on element.unit = unit.id".
					" where d.pid = %s".
					" and (element.unit <= %s or unit.uid = %s or owner.uid = %s)".
					" group by d.id".
					" order by d.initymd DESC limit %s, %s;",
					mysql_num($id), mysql_num(public_status($id)),
					mysql_num(myuid()), mysql_num(myuid()),
					mysql_num($offset), mysql_num($config['view_num']));

	if ($f) {
		$SYS_BLOG_ID = $id;
		while ($r = mysql_fetch_array($f)) {
			$blog[] = array(id => $r['id'],
							blk_id  => $r['pid'],
							subject => $r["subject"],
							body    => stripTagsIfForhidden( $r["body"] ),
							date    => $r["initymd"]);
		}
	}

	$html = ''; $mode = null;
/*** ***/
	$correct = array(); $not_auth = array(); $auth = array();
	if (is_owner($id)) {
		$p = mysql_uniq('select * from bosai_web_block as rb'.
						' left join bosai_web_setting as rs'.
						' on rb.eid = rs.id'.
						' where rb.block_id = %s',
					mysql_num($id));
		if ($p) {
			$parent_sitename = get_site_name(get_site_id($p['id']));
			$bname = get_block_name($p['id']);

			$html .= '<h4 class="bosai_web_block_note">このブログは'.
					 $parent_sitename. '/'. $bname. 'への投稿パーツです。'.
					 '</h4>';
			$html .= '<div class="bosai_web_block_msg">'. $p['msg']. '</div>';
			$c = mysql_full('select a.display, a.comment, b.*'.
							' from bosai_web_auth as a'.
							' inner join blog_data as b on a.id = b.id'.
							' where a.pid = %s', mysql_num($id));
			if ($c) {
				while ($a = mysql_fetch_array($c)) {
					switch ($a['display']) {
						case 0:
							if ($a['comment'] != '') {
								$correct[$a['id']] = $a;
							}
							else {
								$not_auth[$a['id']] = $a;
							}
							break;
						case 1:
							$auth[$a['id']] = $a;
							break;
						default;
							;
					}
				}
			}
			$mode = 'bosai_web';
		}
		else {
			$p = mysql_uniq('select * from reporter_block as rb'.
							' left join reporter_setting as rs'.
							' on rb.eid = rs.id'.
							' where rb.block_id = %s',
						mysql_num($id));
			if ($p) {
				$parent_sitename = get_site_name(get_site_id($p['id']));
				$bname = get_block_name($p['id']);
				$html .= '<h4 class="reporter_block_note">このパーツは'.
						 $parent_sitename. '/'. $bname. 'へのレポートパーツです。'.
						 '</h4>';

				$html .= '<div class="reporter_block_msg">'. $p['msg']. '</div>';
			}
			$c = mysql_full('select a.display, a.comment, b.*'.
							' from reporter_auth as a'.
							' inner join blog_data as b on a.id = b.id'.
							' where a.pid = %s', mysql_num($id));
			if ($c) {
				while ($a = mysql_fetch_array($c)) {
					switch ($a['display']) {
						case 0:
							if ($a['comment'] != '') {
								$correct[$a['id']] = $a;
							}
							else {
								$not_auth[$a['id']] = $a;
							}
							break;
						case 1:
							$auth[$a['id']] = $a;
							break;
						default;
							;
					}
				}
				$mode = 'reporter';
			}
		}
	}
	if (count($correct) > 0) {
		$html .= '<hr size="1">';
		foreach ($correct as $k => $v) {
			$html .= '<div class="correct_order">'.
					 '「'. $v['subject']. '」に対して校正依頼が来ています。'.
					 make_href('編集&raquo;', '/modules/blog/input.php?eid='. $k).
					 '</div>';
		}
	}
	if (count($not_auth) > 0) {
		$html .= '<hr size="1">';
		foreach ($not_auth as $k => $v) {
			$html .= '<div class="correct_not_auth">'.
					 '「'. $v['subject']. '」は編集中の状態です。'.
					 make_href('編集&raquo;', '/modules/blog/input.php?eid='. $k).
					 '</div>';
		}
	}
	if (count($auth) > 0) {
		$html .= '<hr size="1">';
		foreach ($auth as $k => $v) {
			$html .= '<div class="correct_auth">'.
					 '「'. $v['subject']. '」は承認待ちの状態です。'.
//					 make_href('編集&raquo;', '/modules/blog/input.php?eid='. $k).
					 '</div>';
		}
	}

	$i = 0;
	foreach ($blog as $b) {
		$date_str = date('Y年n月d日 G時i分', strtotime($b['date']));

		if ($config['view_type'] == 0) {
			;
		}
		else if ($config['view_type'] == 1) {
			$comment   = count_comment($b['id']);
			$trackback = count_trackback($b['id']);

			$body = $b["body"];

			$html .= <<<BODY
<div class="mod_blog_block">
<div class="mod_blog_block_content">
<div style="font-size: 0.8em; text-align: right;">${date_str}</div>
<h4 class="mod_blog_block_title"><a href="/index.php?module=blog&eid=${b['id']}&blk_id=${b['blk_id']}">${b["subject"]}</a></h4>
${body}
<div style="text-align: right; font-size: 0.9em;">
<a href="/index.php?module=blog&eid=${b['id']}&blk_id=${b['blk_id']}#comment">Comment (${comment})</a> 
<a href="/index.php?module=blog&eid=${b['id']}&blk_id=${b['blk_id']}#trackback">Trackback (${trackback})</a>
</div>
</div>
</div>
<br clear="all">
BODY;
			;
		}
		else if ($config['view_type'] == 2) {
			$comment = count_comment($b['id']);
			$trackback = 0;

			$body = clip_str(strip_tags($b['body']), 250);
			$html .= <<<BODY
<div class="mod_blog_block">
<div class="mod_blog_block_content">
<div style="font-size: 0.8em; text-align: right;">${date_str}</div>
<h4 class="mod_blog_block_title"><a href="/index.php?module=blog&eid=${b['id']}&blk_id=${b['blk_id']}">${b["subject"]}</a></h4>
${body}
<div style="text-align: right;clear: both;">
<a href="/index.php?module=blog&eid=${b['id']}&blk_id=${b['blk_id']}">続きを読む &raquo;</a></div>
<div style="text-align: right; font-size: 0.9em; width: 100%;">
<a href="/index.php?module=blog&eid=${b['id']}&blk_id=${b['blk_id']}#comment">Comment (${comment})</a> 
<a href="/index.php?module=blog&eid=${b['id']}&blk_id=${b['blk_id']}#trackback">Trackback (${trackback})</a>
</div>
</div>
</div>
<br clear="all">
BODY;
			;
		}
		else {
			$href = CONF_URLBASE. "/index.php?module=blog&eid=$b[id]&blk_id=${b['blk_id']}";
			$html .= '<div class="common_href">'.
					 make_href($b['subject'], $href).
					 '</div>'.
					 '<div class="common_date">'. date('n月d日 G時i分', strtotime($b['date'])). '</div>';
		}

		$i++;

		if ($i >= $config['view_num']) {
			break;
		}
	}
	$html .= mod_blog_block_pager($id);

	$html .= '<div class="common_feed">'.
		 '<a href="/modules/blog/feed.php?id='. $id. '" target="_blank">'.
		 '<img src="/image/feed.png" width="16" height="16" alt="feed" border="0"></a> '.
		 '<a href="/modules/kml/get.php/'. $id. '.kml" target="_blank">'.
		 '<img src="/image/kml_feed_small.png" width="16" height="16" alt="KML" border="0"></a>'.
		 '</div>';

	return $html;
}

function mod_blog_block_pager($id = 0) {
	global $JQUERY;

	$block_id = $id;
	$ym       = isset($_REQUEST['ym']) ? $_REQUEST['ym'] : '';
	$page     = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;

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
	$offset = ($page - 1) * $config['view_num'];
	$limit  = $config['view_num'];

	if (!$total || ($total == 0)) {
		return ;
	}

	$pagecount = ceil($total / $limit);

	$href = "/index.php?module=blog&blk_id=$id";

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

	return '<div style="text-align: center; width: 100%;" class="clearfix">'.
			'<div class="navi_pager clearfix" id="pager_'. $id. '" style="text-align: center; margin: 0 auto;"></div>'.
			'<div style="clear: both;"></div></div>';
}


?>
