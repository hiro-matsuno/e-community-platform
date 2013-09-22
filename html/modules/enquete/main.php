<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require_once dirname(__FILE__). '/config.php';
include_once dirname(__FILE__). '/func.php';

function mod_enquete_main($id) {
	$action = $_REQUEST['action'];

	switch ($action) {
		case 'result':
			return mod_enquete_print_result($id);
		break;
		case 'thxmsg':
			return mod_enquete_thxmsg($id);
		break;
		case 'denymsg':
			return mod_enquete_denymsg($id);
		break;
		default:
			return mod_enquete_vote($id);
	}

	return 'empty';

	global $SYS_BLOG_ID;

	$now     = date('Y/m/d h:i:s');

	list($year, $month, $day) = split('-', $_REQUEST["date"]);

	$year  = intval($year);
	$month = intval($month);
	$day   = intval($day);

	if (!check_pmt($id)) {
		return '403';
	}

	// 今月の予定
	$s = mysql_uniq("select * from schedule_data".
					" where id = %s",
					mysql_num($id));

	if ($s) {
		$id       = $s["id"];
		$subject  = $s["subject"];
		$body     = $s["body"];
		$startymd = date('Y年m月d日 H時i分', strtotime($s["startymd"]));
		$endymd   = $s["endymd"];
	}
	else {
		return 'miss';
	}

	if ($endymd) {
		$date = $startymd . ' から '. date('Y年m月d日 H時i分', strtotime($endymd)). ' まで';
	}
	else {
		$date = $startymd;
	}

	$comment = load_comment($id);

	$map = view_map($id);

	$buff .= <<<__CONTENT__
<div class="schedule_d_subject">${subject}</div>
<div class="schedule_d_date">${date}</div>
<div class="schedule_d_body">${body}${map}</div>
<hr>
${comment}
__CONTENT__;
	;

	return $buff;
}

function mod_enquete_vote($id) {
	global $COMUNI_DEBUG, $JQUERY, $COMUNI_TPATH;


	$allow_type = 0;
	$c = mysql_uniq('select * from mod_enquete_allow where eid = %s', mysql_num($id));
	if ($c) {
		$allow_type = $c['type'];
	}
	if ($allow_type == 2) {
		if (!is_joined(get_gid($id))) {
			$content .= '<div class="enquete_fwb">';
			$content .= '送信するためにはグループへの参加が必要です。';
			$content .= '</div>';
			return $content;
		}
	}
	else if ($allow_type == 1) {
		if (!is_login()) {
			$href = make_href('ログイン', '/login.php?type=dialog&ref='. urlencode(home_url($id)), true);
			$content .= '<div class="enquete_fwb">';
			$content .= '送信するためには'. $href. 'が必要です。';
			$content .= '</div>';

			return $content;
		}
	}



	$q = mysql_uniq('select * from enquete_status where eid = %s',
					mysql_num($id));

	if ($q) {
		$p = mysql_uniq('select * from enquete_data where id = %s',
						mysql_num($q['eid']));

		$enq_id   = $p['id'];
		$subject  = $p['subject'];
		$note     = $p['note'];
		$type     = $p['type'];
		$result   = $p['result'];
		$startymd = $p['startymd'] ? strtotime($p['startymd']) : null;
		$endymd   = $p['endymd'] ? strtotime($p['endymd']) : null;
	}
	else {
		return mod_enquete_none();
	}

	if ($startymd) {
		if (time() - $startymd < 0) {
			return mod_enquete_none();
		}
		$dstr = '実施期間: '. date('Y年m月d日H時i分', $startymd). '～';
	}
	if ($endymd) {
		$dstr .= date('Y年m月d日H時i分', $endymd);
	}
	$dstr = preg_replace('/00時00分/', '', $dstr);

	$html = '';

	$html .= '<h2 class="enquete_tb">'. $subject. '</h2>';
	$html .= '<div class="enquete_nb">'. $note. '</div>';
	$html .= '<div style="font-size: 0.9em; text-align: right;">'. $dstr. '</div>';

	if ($endymd) {
		if (time() - $endymd > 0) {
			return mod_enquete_result($enq_id, $result);
		}
	}

	$html .= mod_enquete_print($enq_id, 1);

	if ($result > 0) {
		$html .= mod_enquete_result($enq_id, $result);
	}

	$COMUNI_TPATH[] = array(name => get_block_name($id));

	return $html;
}

function mod_enquete_print_result_t($id) {
	global $COMUNI_DEBUG, $JQUERY, $COMUNI_TPATH;

	$p = mysql_uniq('select * from enquete_data where id = %s',
					mysql_num($id));

	$subject  = $p['subject'];
	$note     = $p['note'];
	$startymd = $p['startymd'] ? strtotime($p['startymd']) : null;
	$endymd   = $p['endymd'] ? strtotime($p['endymd']) : null;

	if ($startymd) {
		if (time() - $startymd < 0) {
			return mod_enquete_none();
		}
		$dstr = '実施期間: '. date('Y年m月d日H時i分', $startymd). '～';
	}
	if ($endymd) {
		$dstr .= date('Y年m月d日H時i分', $endymd);
	}
	$dstr = preg_replace('/00時00分/', '', $dstr);

	$html = '';

	$html .= '<h2 class="enquete_tb">'. $subject. 'の結果</h2>';
	$html .= '<div class="enquete_nb">'. $note. '</div>';
	$html .= '<div style="font-size: 0.9em; text-align: right;">'. $dstr. '</div>';

	if (is_owner($id)) {
		$html .= '<div style="font-size: 0.9em; text-align: right;"><a href="/modules/enquete/csv.php?id='. $id. '">一覧をCSV形式でダウンロード &raquo;</a></div>';
	}

	$f = mysql_full('select * from enquete_form_data where eid = %s'.
					' order by num',
					mysql_num($id));

	if ($f) {
		while ($r = mysql_fetch_array($f)) {
			$que[$r['uniq_id']] = $r;
		}
	}
	else {
		return '集計対象のアンケートが無いか、結果がありません';
	}

	$v = mysql_full('select * from enquete_vote_data where eid = %s'.
					' order by updymd desc',
					mysql_num($id));

	$ans = array();
	if ($v) {
		while ($r = mysql_fetch_array($v)) {
			$ans[$r['num']][] = $r;
		}
	}
	else {
		return '集計対象が無いか、結果がありません';
	}

	$show_hidden_data = false;
	if (is_owner($id)) {
		$show_hidden_data = true;
	}

	foreach ($que as $q) {
		if (($q['admin_only'] == 1) && ($show_hidden_data == false)) {
			$html .= '<h4 style="font-size: 1.2em; margin-top: 15px;">'. $q['title']. '</h4>';
			$html .= '(この項目は結果を非公開としています。)';
			continue;
		}

		$sub_total = 0;
		$html .= '<h4 style="font-size: 1.2em; margin-top: 15px;">'. $q['title']. '</h4>';

		if ($q['type'] == 'text' || $q['type'] == 'textarea') {
			foreach ($ans[$q['uniq_id']] as $a) {
				$html .= $a['data']. ' / ';
			}
		}
		else {
			$html .= mod_enquete_print_result_js($q['id'], $q['eid'], $q['uniq_id']);
		}
		$html .= '<hr>';
	}

	$back_href = home_url($id);

	$html .= '<div id="mod_enquete_result_back" style="margin-top: 15px; text-align: center;">'.
			 '<a href="'. $back_href. '">トップページに戻る</a>'.
			 '</div>';

	$COMUNI_TPATH[] = array(name => get_block_name($id));

	return $html;
}

function mod_enquete_print_result_js($id, $eid, $num) {
	global $COMUNI_HEAD_JS, $COMUNI_HEAD_JSRAW;

	$COMUNI_HEAD_JSRAW[] = <<<__JS__
swfobject.embedSWF(
	"/modules/enquete/swf/open-flash-chart.swf", "my_chart_${id}", "96%", "300",
	"9.0.0", "expressInstall.swf",
	{"data-file":"/modules/enquete/json.php/${eid}/${num}"},
	{"wmode":"transparent"}
);
__JS__;

	return '<div id="my_chart_'. $id. '"></div>';
}

function mod_enquete_print_result($id) {
	global $COMUNI_HEAD_JS, $COMUNI_DEBUG, $JQUERY, $COMUNI_TPATH;

	$COMUNI_HEAD_JS[] = CONF_URLBASE. '/modules/enquete/js/swfobject.js';

	return mod_enquete_print_result_t($id);
//	if ($id == 15713) {
//		return mod_enquete_print_result_t($id);
//	}

	$html = '結果';

	if (is_owner($id)) {
		$html .= ' <a href="csv.php?id='. $id. '">一覧をCSV形式でダウンロード &raquo;</a>';
	}

	$f = mysql_full('select * from enquete_form_data where eid = %s'.
					' order by num',
					mysql_num($id));

	if ($f) {
		while ($r = mysql_fetch_array($f)) {
			$que[$r['uniq_id']] = $r;
		}
	}
	else {
		return mod_enquete_none();
	}

	$v = mysql_full('select * from enquete_vote_data where eid = %s'.
					' order by updymd desc',
					mysql_num($id));

	$ans = array();
	if ($v) {
		while ($r = mysql_fetch_array($v)) {
			$ans[$r['num']][] = $r;
		}
	}
	else {
		return mod_enquete_none();
	}

	$show_hidden_data = false;
	if (is_owner($id)) {
		$show_hidden_data = true;
	}

	foreach ($que as $q) {
		if (($q['admin_only'] == 1) && ($show_hidden_data == false)) {
			$html .= '<h4 style="font-size: 1.2em; margin-top: 15px;">'. $q['title']. '</h4>';
			$html .= '(この項目は結果を非公開としています。)';
			continue;
		}

		$sub_total = 0;
		$html .= '<h4 style="font-size: 1.2em; margin-top: 15px;">'. $q['title']. '</h4>';

		$data = array();
		switch ($q['type']) {
			case 'radio':
			case 'select':
				$opt = explode('-_-', $q['opt_list']);
				foreach($opt as $o) {
					$data[$o] = 0;
				}
				$i = 0;
				foreach($opt as $o) {
					$i++;
					$s[$i] = $o;
				}
			case 'checkbox':
				$opt = explode('-_-', $q['opt_list']);
				foreach($opt as $o) {
					$data[$o] = 0;
				}
				break;
			default:
				$opt = array();
		}
		$sub_total = 0; $count = array();
		foreach ($ans[$q['uniq_id']] as $a) {
			switch ($q['type']) {
				case 'radio':
				case 'select':
					$count[$s[$a['data']]]++;
					break;
				case 'checkbox':
					$v_opt = explode('-_-', $a['data']);
					foreach ($v_opt as $vo) {
						$count[$vo]++;
					}
					break;
				default:
					$article[$a['num']][] = $a['data'];
			}
			$sub_total++;
		}
		if ($count) {
			foreach ($count as $d => $c) {
				$w = sprintf("%.1f", $c / $sub_total * 100);
				$style = 'background-color: #a4cddf; height: 10px; width: '. $w. '%;';
				$html .= $d. ' ('. $c. '票、'. $w. '%)<div style="'. $style. '"></div>';
			}
		}
		else {
			$num = 0;
			foreach ($article[$q['uniq_id']] as $arti) {
				if ($arti != '') {
					$html .= nl2br($arti). '<hr size="1">';
				}
				if ($num > 8) {
					break;
				}
				$num++;
			}
		}
	}

	$back_href = home_url($id);

	$html .= '<div id="mod_enquete_result_back" style="margin-top: 15px; text-align: center;">'.
			 '<a href="'. $back_href. '">トップページに戻る</a>'.
			 '</div>';

	$COMUNI_TPATH[] = array(name => get_block_name($id));

	return $html;
}

function mod_enquete_thxmsg($eid) {
	if (isset($_SESSION['enquete_thxmsg'])) {
		unset($_SESSION['enquete_thxmsg']);

		$q = mysql_uniq('select * from enquete_data where id = %s',
						mysql_num($eid));

		$thxmsg = $q['thxmsg']. '<div style="text-align: center; margin-top: 10px;">'.
				  '<a href="'. home_url($eid). '">トップページに戻る</a></div>';

		return $thxmsg;
	}
	else {
		header('Location: '. home_url($eid)); 
	}
}

function mod_enquete_denymsg($eid) {
	if (isset($_SESSION['enquete_denymsg'])) {
		unset($_SESSION['enquete_thxmsg']);

		$thxmsg = 'あなたは既に一度送信が完了しているため複数行えません。'.
				  '<div style="text-align: center; margin-top: 10px;">'.
				  '<a href="'. home_url($eid). '">トップページに戻る</a></div>';

		return $thxmsg;
	}
	else {
		header('Location: '. home_url($eid)); 
	}
}

?>
