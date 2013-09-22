<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require_once 'Calendar/Month/Weekdays.php';

function mod_blog_calendar_config($id) {
	if (!is_owner($id)) {
		return;
	}
	$menu = array();

	$menu[] = array(title => '設定',
					url => '/modules/blog_calendar/setting.php?pid='. $id,
					inline => false);

	return block_edit_menu($id, $menu);
}

function mod_blog_calendar_block($id) {
	global $COMUNI_DEBUG, $JQUERY, $COMUNI_HEAD_CSSRAW;

	$q = mysql_uniq('select * from blog_calendar_setting where id = %s',
					mysql_num($id));

	$blog_id = array();
	if (!$q) {
		$b = mysql_full('select * from block where pid = %s and module = %s',
						mysql_num(get_site_id($id)), mysql_str('blog'));

		if ($b) {
			while ($r = mysql_fetch_array($b)) {
				$blog_id[] = $r['id'];
			}
		}
	}else {
		$qq = mysql_full('select * from blog_calendar_list where id = %s',
						mysql_num($id));
	
		if ($qq) {
			while ($r = mysql_fetch_array($qq)) {
				$blog_id[] = $r['blog_id'];
			}
		}
	}

	$data = array('id'       => $id,
				  'editlink' => mod_blog_calendar_config($id),
				  'title'    => 'GLIST',
				  'content'  => '');

	$now     = date('Y/m/d H:i:s');

	list($cur_year, $cur_month) = split('-', date('Y-m'));

	if($_REQUEST["ajaxmode"] or $_REQUEST['blk_id'] == $id){
		$year  = (isset($_REQUEST["year"]) )
					? intval($_REQUEST["year"])  : $cur_year;
		$month = (isset($_REQUEST["month"]) )
					? intval($_REQUEST["month"]) : $cur_month;
	}else{
		$year = $cur_year;
		$month = $cur_month;
	}

	// 今月の投稿
	$q = mysql_full('select d.id,'.
					' DATE_FORMAT(d.initymd, \'%%Y-%%m-%%d\') as initymd'.
					' from blog_data as d'.
					' inner join element as e on d.id = e.id'.
					' left join unit as u on e.unit = u.id'.
					' inner join element as be on d.pid = be.id'.
					' left join unit as bu on be.unit = bu.id'.
					' where YEAR(d.initymd) = %s'.
					' and MONTH(d.initymd) = %s'.
					' and d.pid in %s'.
					' and (e.unit <= %s or u.uid = %s)'.
					' and (be.unit <= %s or bu.uid = %s)'.
					' order by d.initymd DESC',
					mysql_num($year), mysql_num($month), mysql_numin($blog_id),
					mysql_num(public_status($id)), mysql_num(myuid()),
					mysql_num(public_status($id)), mysql_num(myuid()));

	if ($q) {
		while ($r = mysql_fetch_array($q)) {
			$date_href[$r['initymd']] = true;
		}
	}

$JQUERY["ready"][] = <<<__READY_CODE__
var blog_calendar_${id}_current_year  = ${year};
var blog_calendar_${id}_current_month = ${month};
var blog_calendar_${id}_year  = blog_calendar_${id}_current_year;
var blog_calendar_${id}_month = blog_calendar_${id}_current_month;

$('#blog_calendar_${id}_prev').click(function() {
	if (--blog_calendar_${id}_month < 1) {
		blog_calendar_${id}_month = 12;
		blog_calendar_${id}_year--;
	}
	$('#blog_calendar_${id}_current').text(blog_calendar_${id}_year + '年' + blog_calendar_${id}_month + '月');
	$('#blog_calendar_${id}').load('/modules/blog_calendar/reload.php',
							 { blk_id:${id}, year:blog_calendar_${id}_year, month:blog_calendar_${id}_month, ajaxmode:1 },
							 function() {
								tb_init('a.thickbox, area.thickbox, input.thickbox');
							 });
});
$('#blog_calendar_${id}_current').click(function() {
	location.href='/index.php?blk_id=${id}&date='+blog_calendar_${id}_year+'-'+blog_calendar_${id}_month+'-00';
});
$('#blog_calendar_${id}_next').click(function() {
	if (++blog_calendar_${id}_month > 12) {
		blog_calendar_${id}_month = 1;
		blog_calendar_${id}_year++;
	}
	$('#blog_calendar_${id}_current').text(blog_calendar_${id}_year + '年' + blog_calendar_${id}_month + '月');

	$('#blog_calendar_${id}').load('/modules/blog_calendar/reload.php',
							 { blk_id:${id}, year:blog_calendar_${id}_year, month:blog_calendar_${id}_month, ajaxmode:1 },
							 function() {
								tb_init('a.thickbox, area.thickbox, input.thickbox');
							 });
});
__READY_CODE__;
	;

	$calMonth = new Calendar_Month_Weekdays($year, $month, 0); 
	$calMonth->build();

	$week    = array("日", "月", "火", "水", "木", "金", "土");
	$nowdate =  date( "Y年n月j日", time() ) . $week[date( "w", time() ) ] . "曜日";
	$ymstr   = $year. '年'. $month. '月';

	$COMUNI_HEAD_CSSRAW[]  = <<<CSSSSS
.cal {
	width: 100%;
	margin: 2px auto;
    border: 1px #E3E3E3 solid;
    border-collapse: collapse;
}
.cal th { border: 1px #E3E3E3 solid; font-size: 0.9em; font-weight: normal; text-align: center;}
.cal td { border: 1px #E3E3E3 solid; font-size: 0.9em; font-weight: normal; text-align: right;}
.cal a {
	font-weight: bold;
	text-decoration:underline;
}

.blog_calendar_w_t {
	font-size: 0.9em;
	padding: 2px 2px 1px 3px;
	text-align: left;
	font-weight: bold;
	color: #666666;
	border-bottom: solid 1px #dddddd;
	border-top: solid 1px #dddddd;
}
CSSSSS;

	if (!isset($_REQUEST["ajaxmode"])) {
		$buff .= <<<__CAL_HEAD__
<div style="text-align: center; font-size: 0.9em;">
<a id="blog_calendar_${id}_prev" href='#' onClick="return false;">&laquo;</a> 
<a id="blog_calendar_${id}_current" href='#' onClick="return false;">${ymstr}</a> 
<a id="blog_calendar_${id}_next" href='#' onClick="return false;">&raquo;</a> 
</div>
__CAL_HEAD__;
		;
	}
	$buff .= '<div id="blog_calendar_'. $id. '" style="padding:3px;text-align: center;">';
	$buff .= '<div style="width: 98%;margin: 0 auto;;"><table class="cal" width="100%"><thead><tr>'; 
	$buff .= '<th>日</th><th>月</th><th>火</th><th>水</th><th>木</th><th>金</th><th>土</th></th>'; 
	$buff .= '</tr></thead><tbody>';

	$today = date('j'); $range = array();
	while ($day = $calMonth->fetch()) {
		$df = sprintf("%04d-%02d-%02d", $year, $month, $day->thisDay());

	    if ($day->isFirst()) { 
	        $buff .= '<tr>'; 
	    } 
	    if ($day->isEmpty()) { 
	        $buff .= '<td>&nbsp;</td>'; 
	    } else {
			if ($date_href[$df]) {
				$title = $df. 'のブログ';
				$link = '/index.php?blk_id='. $id. '&date='. $df;
		        $buff .= '<td><a href="'. $link. '" title="'. $title.'" >'.
						 $day->thisDay(). '</a></td>'; 
			}
			else if ($cur_year == $year && $cur_month == $month && $today == $day->thisDay()) {
		        $buff .= '<td style="background-color: #fffdb7;">'.$day->thisDay().'</td>'; 
			}
			else {
		        $buff .= '<td>'.$day->thisDay().'</td>'; 
			}
	    }
	    if ($day->isLast()) { 
	        $buff .= '</tr>'; 
  	  } 
	} 
	$buff .= '</tbody></table></div>'; 
	$buff .= '</div>';

	$data["content"] = $buff;

	return $data;
}

?>
