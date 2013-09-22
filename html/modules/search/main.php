<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require_once dirname(__FILE__). '/config.php';

// $id: site_id?
function mod_search_main($id = null) {
	$keyword = htmlesc($_REQUEST['q']);

	if ($_REQUEST['type'] == 'tag') {
		return mod_search_by_tag($id, $keyword);
	}

	$result = array(); $gpage = array();
	if ($keyword != '' && $keyword != 'キーワード検索') {
		$q = mysql_full("select * from blog_data where subject like %s or body like %s limit 0, 30",
						mysql_like($keyword), mysql_like($keyword));
		if ($q) {
			while ($r = mysql_fetch_array($q)) {
				$result[] = array(id => $r['id'], subject => $r['subject'], blk_id => $r['pid']);
			}
		}
		else { echo mysql_error(); }

		$g = mysql_full('select g.gid, g.sitename, g.description'.
						' from page as g'.
						' inner join element on g.id = element.id'.
						' left join unit on element.unit = unit.id'.
						' where (element.unit <= %s or unit.uid = %s)'.
						' and (g.sitename like %s or sd.description like %s)'.
						' limit 0, 30',
						mysql_num(public_status()),	mysql_num(myuid()),
						mysql_like($keyword), mysql_like($keyword));
		
		if ($g) {
			while ($r = mysql_fetch_array($g)) {
				$gpage[] = array('gid' => $r['gid'], 'name' => $r['sitename'], 'description' => $r['description']);
			}
		}
	}
	else {
		$keyword = '';
	}

	$html = <<<__HTML__
<div class="form_wrap">
<form action="/index.php" method="GET">
<input type="hidden" name="eid" value="${id}">
<input type="hidden" name="module" value="search">
<input type="text" name="q" value="${keyword}" class="input_text"> <input type="submit" value="検索" class="search_btn">
</form>
</div>
__HTML__;
	;

//	$html .= '<h4>キーワードから探す</h4>'. mod_search_tag_href();

	if (count($gpage) > 0) {
		$html .= '<h4>グループページ検索</h4>';
		foreach ($gpage as $r) {
			$html .= '<a class="common_href" href="/group.php?gid='. $r['gid']. '">'. $r['name']. '</a>';
			$html .= '<div class="common_body">'. $r['description']. '</div>';
		}
	}

	if (count($result) > 0) {
		$html .= '<h4>ブログ検索</h4>';
		foreach ($result as $r) {
			$html .= '<a class="common_href" href="/index.php?module=blog&eid='. $r['id']. "&blk_id=$r[blk_id]" . '">'.
					 $r['subject']. '</a>';
		}
	}

	$data = array(id       => $id,
				  title    => '検索',
				  content  => $html);

	return $data;
}

//現在はブログに対してしか有効でない。
//ページに対しても利かない
function mod_search_by_tag($id = 0, $keyword = null) {
	if (isset($keyword) && $keyword != '') {
		$f = mysql_full('select ts.keyword, d.pid, block.module from tag_setting as ts'.
						' inner join tag_data as d on ts.id = d.tag_id'.
						' inner join element on d.pid = element.id'.
						' left juoin unit on element.unit = unit.id'.
						' inner join block on d.blk_id = block.id'.
						' where (element.unit <= %s or unit.uid = %s)'.
						' and ts.keyword like %s',
						mysql_num(public_status()),	mysql_num(myuid()),
						mysql_like($keyword));
	}

	$buff = array(); $in_ids = array();
	if ($f) {
		while ($r = mysql_fetch_array($f)) {
			$in_ids[] = $r['pid'];
			$buff[$r['module']][] = array('id' => $r['pid'], 'tag' => $r['keyword']);
		}
	}

	$html = <<<__HTML__
<div class="form_wrap">
<form action="/index.php" method="GET">
<input type="hidden" name="eid" value="${id}">
<input type="hidden" name="module" value="search">
<input type="hidden" name="type" value="tag">
<input type="text" name="q" value="${keyword}" class="input_text"> <input type="submit" value="検索" class="search_btn">
</form>
</div>
__HTML__;
	;

	$html .= mod_search_tag_href();

	foreach ($buff as $module => $d) {
		switch ($module) {
			case 'blog':
				$l = mysql_full('select * from blog_data where id in %s', mysql_numin($in_ids));
				if ($l) {
					while ($r = mysql_fetch_array($l)) {
						$html .= mod_search_href(array('id'      => $r['id'],
													   'blk_id'  => $r['pid'],
													   'module'  => $module,
													   'subject' => $r['subject'] ? $r['subject'] : '無題',
													   'body'    => clip_str(strip_tags($r['body']), 256),
													   'tag'     => $d['tag']));
					}
				}
			case 'page':
				$l = mysql_uniq('select * from page_data where id in %s', mysql_numin($in_ids));
				if ($l) {
					while ($r = mysql_fetch_array($l)) {
						$html .= mod_search_href(array('id'      => $r['id'],
													   'blk_id'  => $r['pid'],
													   'module'  => $module,
													   'subject' => $r['subject'] ? $r['subject'] : '無題',
													   'body'    => clip_str(strip_tags($r['body']), 256),
													   'tag'     => $d['tag']));
					}
				}
			break;
			default:
				;
		}
	}

	return array(id       => 0,
				 title    => 'キーワード検索',
				 content  => $html);
}

function mod_search_href($param = array()) {
	$location = CONF_URLBASE. "/index.php?module=$param[module]&eid=$param[id]&blk_id=$param[blk_id]";

	$sitename = get_site_name(get_site_id($param['id']));
//	$sitehref = '/location.php?eid='. get_site_id($param['id']);
	$sitehref = '/index.php?site_id='. get_site_id($param['id']);
	
//	$tag = '<span class="keyword_inline">'. $param['tag']. '</span>';

	return '<a href="'. $location. '" class="common_href"><span>'. $param['subject']. '</span></a>'.
		   '<div class="common_body">'. $param['body']. '</div>'.
		   '<div class="common_date">'. make_href($sitename, $sitehref). '</div>';
}

function mod_search_tag_href() {
	$html = '<div class="mod_tag_wrap">';
	$f = mysql_full('SELECT ts.keyword, count(*) AS cnt'.
					' FROM tag_setting AS ts'.
					' INNER JOIN tag_data AS d ON ts.id = d.tag_id'.
					' GROUP BY ts.keyword'.
					' ORDER BY cnt DESC');

	$base_url      = CONF_URLBASE. '/index.php?module=search&type=tag&q=';
	$base_fontsize = 0.7;
	$zoom_weight   = 0.5;

	if ($f) {
		while ($r = mysql_fetch_array($f)) {
//			$font_size = sprintf("%.1f", $r['cnt'] * $base_fontsize * $zoom_weight);
			$font_size = 1;
			$html .= '<a class="mod_tag_keyword" href="'. $base_url. urlencode($r['keyword']). '">'.
					 '<span style="font-size: '. $font_size. 'em;">'. $r['keyword']. '</span></a>';
		}
	}
	$html .= '</div>';
	$html .= '<div style="clear: both; height: 0.1em;"></div>';

	return $html;
}

?>
