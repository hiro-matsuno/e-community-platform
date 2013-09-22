<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require_once 'Calendar/Month/Weekdays.php';
include_once 'config.php';

function mod_rel_cal_config($id) {
	if (!is_owner($id)) {
		return;
	}
	$menu = array();
	$menu[] = array(title => '新規登録',
					url => '/modules/rel_cal/input.php?pid='. $id,
					inline => false);

	$menu[] = array(title => '編集',
					url => '/modules/rel_cal/edit.php?pid='. $id,
					inline => false);
	if(!get_gid($id)){
		$menu[] = array(title => '機能設定',
						url => '/modules/rel_cal/setting.php?pid='. $id,
						inline => false);
	}
					
	return block_edit_menu($id, $menu);
}

function mod_rel_cal_block($id) {
	global $COMUNI_DEBUG, $JQUERY;

	$data = array(id       => $id,
				  editlink => mod_rel_cal_config($id),
				  title    => 'GLIST',
				  content  => '');

	$now     = date('Y/m/d H:i:s');

	list($cur_year, $cur_month) = split('-', date('Y-m'));

	$year  = isset($_REQUEST["year"])  ? intval($_REQUEST["year"])  : $cur_year;
	$month = isset($_REQUEST["month"]) ? intval($_REQUEST["month"]) : $cur_month;

	// 今月の予定
	$q = mysql_full('select d.id,'.
					' DATE_FORMAT(d.startymd, \'%%Y-%%m-%%d\') as startymd,'.
					' DATE_FORMAT(d.endymd, \'%%Y-%%m-%%d\') as endymd'.
					' from schedule_data as d'.
					' inner join element as e on d.id = e.id'.
					' left join unit as u on e.unit = u.id'.
					' where YEAR(d.startymd) = %s'.
					' and MONTH(d.startymd) = %s'.
					' and d.pid = %s'.
					' and (e.unit <= %s or u.uid = %s)'.
					' order by d.startymd DESC',
					mysql_num($year), mysql_num($month), mysql_num($id),
					mysql_num(public_status($id)), mysql_num(myuid()));

	if ($q) {
		while ($r = mysql_fetch_array($q)) {
			$date_href[$r['startymd']] = true;
			if (isset($r['endymd'])) {
				$stime = intval(strtotime($r['startymd']));
				$etime = intval(strtotime($r['endymd']));

				if ($etime > 0 && $etime <= strtotime('+1 month', $etime)) {
					for ($t = $stime; $t <= $etime; $t = strtotime('+1 day', $t)) {
						if ($t == false || $t == 0) {
							break;
						}
						$date_href[date('Y-m-d', $t)] = true;
					}
				}
			}
/*

			$att = substr($r["startymd"], 0, 10);
			$sch_month[$att][] = array(id       => $r["id"],
									   subject  => $r["subject"],
									   body     => $r["body"],
									   startymd => $r["startymd"],
									   endymd   => $r["endymd"]);
*/
		}
	}
	unset($q);

	// これから一週間の予定
	$q = mysql_full("select sd.*, o.uid from schedule_data as sd".
					" inner join element on sd.id = element.id".
					" left join unit on element.unit = unit.id".
					" left join owner as o on o.id = sd.id".
					" where (element.unit <= %s or unit.uid = %s)".
					" and ((TO_DAYS(sd.startymd) - TO_DAYS(NOW()) >= 0".
					" and TO_DAYS(sd.startymd) - TO_DAYS(NOW()) <= 7)".
					" or (TO_DAYS(NOW()) between TO_DAYS(sd.startymd) and TO_DAYS(sd.endymd)))".
//					" and sd.startymd > now()".
//					" and sd.startymd < DATE_ADD(now(), INTERVAL 7 DAY)".
					" and sd.pid = %s".
					" order by sd.startymd",
					mysql_num(public_status($id)), mysql_num(myuid()),
					mysql_num($id));

	if ($q) {
		while ($r = mysql_fetch_array($q)) {
			$att = substr($r["startymd"], 0, 10);
			$sch_week[$att][] = array(id       => $r["id"],
									  blk_id   => $r['pid'],
									  'owner' => get_handle($r["uid"]),
									  subject  => $r["subject"],
									  body     => $r["body"],
									  startymd => $r["startymd"],
									  endymd   => $r["endymd"]);
		}
	}


$JQUERY["ready"][] = <<<__READY_CODE__
var schedule_${id}_current_year  = ${year};
var schedule_${id}_current_month = ${month};
var schedule_${id}_year  = schedule_${id}_current_year;
var schedule_${id}_month = schedule_${id}_current_month;

$('#schedule_${id}_prev').click(function() {
	if (--schedule_${id}_month < 1) {
		schedule_${id}_month = 12;
		schedule_${id}_year--;
	}
	$('#schedule_${id}_current').text(schedule_${id}_year + '年' + schedule_${id}_month + '月');
	$('#schedule_${id}').load('/modules/rel_cal/reload.php',
							 { eid:${id}, year:schedule_${id}_year, month:schedule_${id}_month, ajaxmode:1 },
							 function() {
								tb_init('a.thickbox, area.thickbox, input.thickbox');
							 });
});
$('#schedule_${id}_current').click(function() {
	schedule_${id}_year  = schedule_${id}_current_year;
	schedule_${id}_month = schedule_${id}_current_month;
	$('#schedule_${id}_current').text(schedule_${id}_year + '年' + schedule_${id}_month + '月');

	$('#schedule_${id}').load('/modules/rel_cal/reload.php', { eid:${id}, ajaxmode:1 },
							 function() {
								tb_init('a.thickbox, area.thickbox, input.thickbox');
							 });
});
$('#schedule_${id}_next').click(function() {
	if (++schedule_${id}_month > 12) {
		schedule_${id}_month = 1;
		schedule_${id}_year++;
	}
	$('#schedule_${id}_current').text(schedule_${id}_year + '年' + schedule_${id}_month + '月');

	$('#schedule_${id}').load('/modules/rel_cal/reload.php',
							 { eid:${id}, year:schedule_${id}_year, month:schedule_${id}_month, ajaxmode:1 },
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

	$buff  = <<<CSSSSS
<style type="text/css">
.cal {
	width: 100%;
	margin: 2px auto;
    border: 1px #E3E3E3 solid;
    border-collapse: collapse;
}
.cal th { border: 1px #E3E3E3 solid; font-size: 0.9em; font-weight: normal; text-align: center;}
.cal td { border: 1px #E3E3E3 solid; font-size: 0.9em; font-weight: normal; text-align: right;}

.schedule_w_t {
	font-size: 0.9em;
	padding: 2px 2px 1px 3px;
	text-align: left;
	font-weight: bold;
	color: #666666;
	border-bottom: solid 1px #dddddd;
	border-top: solid 1px #dddddd;
}

</style>
CSSSSS;

	if (!isset($_REQUEST["ajaxmode"])) {
		$buff .= <<<__CAL_HEAD__
<div style="text-align: center; font-size: 0.9em;">
<a id="schedule_${id}_prev" href='#' onClick="return false;">&laquo;</a> 
<a id="schedule_${id}_current" href='#' onClick="return false;">${ymstr}</a> 
<a id="schedule_${id}_next" href='#' onClick="return false;">&raquo;</a> 
</div>
__CAL_HEAD__;
		;
	}
	$buff .= '<div id="schedule_'. $id. '" style="padding:3px;text-align: center;">';
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
				$title = $df. 'のスケジュール';
				$link = '/modules/rel_cal/window.php?pid='. $id. '&date='. $df.
						'&keepThis=true&TB_iframe=true&height=480&width=640';
		        $buff .= '<td><a href="'. $link. '" title="'. $title.'" class="thickbox">'.
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
	if (count($sch_week) > 0) {
		$buff .= '<div class="schedule_w_t">1週間以内の予定</div>';

		foreach ($sch_week as $df => $d) {
			$dfa = split('-', $df);
			foreach ($d as $a) {
				if ($a["endymd"]) {
					$tm = date('m月d日 H:i', strtotime($a["startymd"])).
						  ' - '.
						  date('m月d日 H:i', strtotime($a["endymd"]));
				}
				else {
					$tm = date('m月d日 H:i', strtotime($a["startymd"]));
				}
				$link = '/modules/rel_cal/window.php?eid='. $a["id"].
						'&keepThis=true&TB_iframe=true&height=480&width=640';

				$link = main_href('schedule', $a['id'], $a['blk_id']);

				$buff .= '<div class="schedule_date">'. $tm. '</div>';
				$buff .= '<a class="schedule_href" href="'. $link. '"><span>'.
						 $a["subject"]. '('. $a['owner'] .')</span></a>';
			}
		}
	}
	$buff .= '</div>';

	$data["content"] = $buff;

	return $data;
}

//function mod_schedule_date_count($table) {
//// table が initymd と id をもっていること。
//	$q = mysql_full('select count(*) as count, DATE_FORMAT(d.initymd, \'%%Y-%%m-%%d\') as ymd'.
//					' from '. $table. ' as d'.
//					' inner join owner as o on d.id = o.id'.
//					' inner join element as e on d.id = e.id'.
//					' left join unit as u on e.unit = u.id'.
//					' where d.pid = %s'.
//					' and (e.unit <= %s or u.uid = %s or o.uid = %s)'.
//					' and DATE_FORMAT(d.initymd, \'%%Y-%%m\') = %s'.
//					' group by DATE_FORMAT(d.initymd, \'%%Y-%%m-%%d\')',
//					mysql_num($id), mysql_num(public_status($id)),
//					mysql_num(myuid()), mysql_num(myuid()),
//					mysql_str(date('Y-m')));
//	if (!$q) {
//		return array();
//	}
//	$result = array();
//	while ($d = mysql_fetch_array($q, MYSQL_ASSOC)) {
//		$result[$d['ymd']] = $d['count'];
//	}
//	return $result;
//}

?>
