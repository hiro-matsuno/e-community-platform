<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require_once dirname(__FILE__). '/../../lib.php';

/* 振り分け*/
list($eid, $pid) = get_edit_ids();

switch ($_REQUEST["action"]) {
	case 'regist':
		regist_data($eid, $pid);
	default:
		input_data($eid, $pid);
}

/* 登録*/
function regist_data($eid = null, $pid = null) {
	global $SYS_FORM;

	// フォームのキャッシュに溜め込む
	$SYS_FORM["cache"]["subject"]   = $_POST["subject"];
	$SYS_FORM["cache"]["body"]      = $_POST["footer"];
	if ($_POST["start_date"]) {
		$SYS_FORM["cache"]["startymd"] = $_POST["start_date"]. ' '.
										 sprintf('%02d', intval($_POST["start_time_h"])). ':'.
										 sprintf('%02d', intval($_POST["start_time_m"])). ':00';
	}
	if ($_POST["end_date"]) {
		$SYS_FORM["cache"]["endymd"] = $_POST["end_date"]. ' '.
										 sprintf('%02d', intval($_POST["end_time_h"])). ':'.
										 sprintf('%02d', intval($_POST["end_time_m"])). ':00';
	}
	if (isset($SYS_FORM["cache"]["endymd"])) {
		if (strtotime($SYS_FORM["cache"]["endymd"]) - strtotime($SYS_FORM["cache"]["startymd"]) < 0) {
			$SYS_FORM["error"]["end_date"] = '終了日は開始日より後の日付を設定して下さい。';
		}
	}

	// 入力エラーチェック
	if (!$SYS_FORM["cache"]["subject"]) {
		$SYS_FORM["error"]["subject"] = '題名を入力してください。';
	}
	if (!$SYS_FORM["cache"]["startymd"]) {
		$SYS_FORM["error"]["startymd"] = '開始日時を指定してください。';
	}
	if ($SYS_FORM["error"]) {
		return;
	}
	// 登録
	$subject = htmlspecialchars($_POST["subject"], ENT_QUOTES);
	$body    = $_POST["body"];

	if ($eid == 0) {
		$eid = get_seqid();

		$q = mysql_exec("insert into schedule_data".
						" (id, pid, subject, body, startymd, endymd, initymd)".
					" values(%s, %s, %s, %s, %s, %s, %s)",
					mysql_num($eid), mysql_num($pid), mysql_str($subject), mysql_str($body),
					mysql_str($SYS_FORM["cache"]["startymd"]),
					mysql_str($SYS_FORM["cache"]["endymd"]),
					mysql_current_timestamp());
	}
	else {
		$q = mysql_uniq("select pid from schedule_data where id=%s",
						mysql_num($eid));
		$pid = $q['pid'];
		if ($SYS_FORM["cache"]["endymd"]) {
			$q = mysql_exec("update schedule_data set subject = %s, body = %s, startymd = %s, ".
							" endymd = %s where id = %s",
							mysql_str($subject), mysql_str($body),
							mysql_str($SYS_FORM["cache"]["startymd"]),
							mysql_str($SYS_FORM["cache"]["endymd"]),
							mysql_num($eid));
		}
		else {
			$q = mysql_exec("update schedule_data set subject = %s, body = %s, startymd = %s".
							" where id = %s",
							mysql_str($subject), mysql_str($body),
							mysql_str($SYS_FORM["cache"]["startymd"]),
							mysql_num($eid));
		}
	}

	if (!$q) {
		die('insert failure...'. mysql_error());
	}

	set_keyword($eid, $pid);
	set_point($eid,$pid);
	set_pmt(array(eid => $eid, gid =>get_gid($pid), name => 'pmt_0'));

	tell_update($eid, 'スケジュール');

	$html = '編集完了しました。';
	$data = array(title   => 'ページデータの編集完了',
				  icon    => 'finish',
				  content => $html. create_form_return(array(eid => $eid, href => home_url($eid))));

	show_input($data);

	exit(0);
}

/* フォーム*/
function input_data($eid = null, $pid = null) {
	global $SYS_FORM, $JQUERY, $COMUNI_HEAD_JS, $COMUNI_HEAD_CSS;

	if ($eid > 0) {
		$d = mysql_uniq("select * from schedule_data where id = %s",
						mysql_num($eid));
	}

	// Y-m-d H:i:s
	if ($d) {
		$subject   = $d["subject"];
		$body      = $d["body"];
		$startymd  = strtotime($d["startymd"]);
		if ($d["endymd"]) {
			$endymd    = strtotime($d["endymd"]);
		}
	}
	else {
		$subject   = '';
		$body      = '';
		$startymd  = time();
		$endymd    = '';
	}
	// 再入力ならキャッシュから拾う
	if (isset($SYS_FORM["cache"])) {
		$subject   = $SYS_FORM["cache"]["subject"];
		$body      = $SYS_FORM["cache"]["body"];
		$startymd  = strtotime($SYS_FORM["cache"]["startymd"]);
		if ($SYS_FORM["cache"]["endymd"]) {
			$endymd    = strtotime($SYS_FORM["cache"]["endymd"]);
		}
	}

	$start_date = date('Y-m-d', $startymd);
	if ($endymd) {
		$end_date   = date('Y-m-d', $endymd);
	}

	// hidden:action
	$attr = array(name => 'action', value => 'regist');
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));

	// text:start_date
	$attr = array(name => 'start_date', value => $start_date, size => 12,
				  title => '日付');
	$form_date = get_form("text", $attr);

	$attr = array(name => 'start_time', value => $startymd, format => 'h時m分',
				  title => '時刻');
	$form_time = get_form("date", $attr);

	$SYS_FORM["input"][] = array(title => '開始日時',
								 name  => 'start_date',
								 body  => $form_date. '<hr size="1">'. $form_time);

	// text:end_date
	$attr = array(name => 'end_date', value => $end_date, size => 12,
				  title => '日付');
	$form_date = get_form("text", $attr);

	$attr = array(name => 'end_time', value => $endymd, format => 'h時m分',
				  title => '時刻');
	$form_time = get_form("date", $attr);

	$SYS_FORM["input"][] = array(title => '終了日時',
								 name  => 'end_date',
								 body  => $form_date. '<hr size="1">'. $form_time);

	// text:subject
	$attr = array(name => 'subject', value => $subject, size => 64);
	$SYS_FORM["input"][] = array(title => '題名',
								 name  => 'subject',
								 body  => get_form("text", $attr));

	// fck:body
	$attr = array(name => 'body', value => $body,
				  cols => 64, rows => 8);

	$SYS_FORM["input"][] = array(title => '内容',
								 name  => 'body',
								 body  => get_form("fck", $attr));

	// url用のスクリプト
	$COMUNI_HEAD_JS[]  = '/ui.datepicker.js';
	$COMUNI_HEAD_CSS[] = '/ui.datepicker.css';

	$JQUERY["ready"][] = <<<___READY_CODE__
\$('#start_date').datepicker({
	yearRange:'-10:+10',
    onSelect:function(date,ui){
		sdate = new Date($('#start_date').datepicker('getDate'));
		edate = new Date($('#end_date').datepicker('getDate'));
		if($('#end_date').datepicker('getDate') == null || sdate > edate)
			$('#end_date').datepicker('setDate',$('#start_date').datepicker('getDate'));
		}
});
\$('#end_date').datepicker({yearRange:'-10:+10'});
___READY_CODE__;
	;

	$SYS_FORM["action"] = 'input.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["keyword"]    = true;
	$SYS_FORM["map"]    = true;
	$SYS_FORM["pmt"]    = true;
	$SYS_FORM["submit"] = '登録';
	$SYS_FORM["cancel"] = '取消';

	$html = create_form(array(eid => $eid, pid => $pid));

	$data = array(title   => 'スケジュールの編集',
				  icon    => 'write',
				  content => $html);

	show_input($data);

	exit(0);
}

?>
