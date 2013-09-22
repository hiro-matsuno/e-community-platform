<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

include_once dirname(__FILE__). '/config.php';

function mod_blog_calendar_main($id){
	//ページにあるブログの名前と
	$blk_names = array();
	$b = mysql_full('select * from block where pid = %s and module = %s',
					mysql_num(get_site_id($id)), mysql_str('blog'));

	if ($b) {
		while ($r = mysql_fetch_array($b)) {
			$blk_names[$r['id']] = $r['name'];
		}
	}
	
	
	//設定を取得
	$c = mysql_uniq('select * from blog_calendar_setting where id = %s',
					mysql_num($id));
	if ($c) {
		$config['view_type'] = $c['view_type'];
		$blog_id = array();
		$qq = mysql_full('select * from blog_calendar_list where id = %s',
						mysql_num($id));
	
		if ($qq) {
			while ($r = mysql_fetch_array($qq)) {
				$blog_id[] = $r['blog_id'];
			}
		}
	}else{
		$config['view_type'] = 0;
		$blog_id = array_keys($blk_names);
	}

	if(!$blog_id){
		return 'ブログが指定されていません';
	}

	//日付を取得
	$date = $_REQUEST["date"];
	list($year, $month, $day) = split('-', $_REQUEST["date"]);
	$date_str = "${year}年${month}月".(intval($day)?"${day}日":'')."に投稿されたブログ";
	$date_match_str = sprintf('%04d-%02d-%02d',$year,$month,$day);
	
	//上部にカレンダーを表示する
	include_once dirname(__FILE__). '/block.php';
	$_REQUEST["year"] = $year;
	$_REQUEST["month"] = $month;
	$blk_data = mod_blog_calendar_block($id);
	$html = $blk_data['content'];

	$per_blk = isset($_REQUEST['per_blk']) and count($blog_id)>1;
	//タイトル表示
	$html .= "<h3 style='margin-top:10px;'>$date_str</h3>\n";

	//ブログ別に表示するか否かの切り替え
	if(count($blog_id)>1){
		if($per_blk)
			$html .= '<div style="text-align:right;"><a href="'. preg_replace('/([&?])per_blk(&|$)/','\1',$_SERVER['REQUEST_URI']).'">ブログ別表示しない</a></div>';
		else
			$html .= '<div style="text-align:right;"><a href="'.$_SERVER['REQUEST_URI'].'&per_blk'.'" >ブログ別表示する</a></div>';
	}

	if($per_blk)$add_query = ' d.pid, ';
	//ブログ一覧を取得
	$q = mysql_full('select d.*, o.uid from blog_data as d'.
					' inner join element as e on d.id = e.id'.
					' left join unit as u on e.unit = u.id'.
					' left join owner as o on o.id = d.id'.
					' inner join element as be on d.pid = be.id'.
					' left join unit as bu on be.unit = bu.id'.
					(intval($day)?
						' where DATE_FORMAT(d.initymd, \'%%Y-%%m-%%d\') = %s':
						' where DATE_FORMAT(d.initymd, \'%%Y-%%m-00\') = %s').
					' and d.pid in %s'.
					' and (e.unit <= %s or u.uid = %s)'.
					' and (be.unit <= %s or bu.uid = %s)'.
					' order by '.$add_query.' d.initymd DESC',
					mysql_str($date_match_str),
					mysql_numin($blog_id),
					mysql_num(public_status($id)), mysql_num(myuid()),
					mysql_num(public_status($id)), mysql_num(myuid()));

	$curr_blk = '';
	while($b = mysql_fetch_assoc($q)){
		$blk_str = make_href($blk_names[$b['pid']], CONF_URLBASE.'/index.php?blk_id='.$b['pid']);
		if($per_blk and $curr_blk!=$b['pid'])
			$html .= "<h3 style='margin-top:10px;'>${blk_str}の記事一覧</h3>";
		if(!$per_blk)
			$blk_str2 = $blk_str;
		$user_str = get_handle($b['uid']);
		if(get_eid_by_mypage($b['uid']))
			$user_str = "<a href='".CONF_URLBASE."/index.php?uid=$b[uid]'>$user_str</a>";
		$curr_blk = $b['pid'];
		$date_str = date('Y年n月d日 G時i分', strtotime($b['initymd']));

		if ($config['view_type'] == 1) {
			$comment   = count_comment($b['id']);
			$trackback = count_trackback($b['id']);

			$body = $b["body"];

			$html .= <<<BODY
<div class="mod_blog_block">
<div class="mod_blog_block_content">
<div style="font-size: 0.8em; text-align: right;">$blk_str2&nbsp;${date_str}($user_str)</div>
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
<div style="font-size: 0.8em; text-align: right;">$blk_str2&nbsp;${date_str}($user_str)</div>
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
					 '<div class="common_date">'.$blk_str2.'&nbsp;'.date('n月d日 G時i分', strtotime($b['initymd'])). "($user_str)".'</div>';
		}
	}
	$html .= '<div style="margin-top:10px;">&nbsp;</div>';

	return $html;
}

?>
