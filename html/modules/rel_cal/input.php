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
	if(!$pid){
		$q = mysql_uniq("select pid from schedule_data where id=%s",
						mysql_num($eid));
		$pid = $q['pid'];
	}
	$gid = get_gid($pid);

	if($gid){
		//連携するカレンダー一覧を取得
		$p = mysql_full('select * from rel_cal_blk_rel where gid = %s',$gid);
		$rel_blk = array();
		while($b = mysql_fetch_assoc($p)){
			$rel_blk[$b['blk_id']] = $b['uid'];
		}
		//スケジュール関連のテーブルのフィールドを取得
		$p = mysql_full('show columns from schedule_data');
		$schedule_data_fields = array();
		while($c = mysql_fetch_assoc($p))
			if($c['Field']!='id' and $c['Field']!='pid')
				$schedule_data_fields[] = $c['Field'];
		$schedule_data_fields = implode(',',$schedule_data_fields);
		$p = mysql_full('show columns from schedule_data_add_ical');
		$schedule_data_add_ical_fields = array();
		while($c = mysql_fetch_assoc($p))
			if($c['Field']!='id' and $c['Field']!='pid')
				$schedule_data_add_ical_fields[] = $c['Field'];
		$schedule_data_add_ical_fields = implode(',',$schedule_data_add_ical_fields);
	}else{
		$rel_blk = array();
	}

	// フォームのキャッシュに溜め込む
	$SYS_FORM["cache"]["subject"]   = $_POST["subject"];
	$SYS_FORM["cache"]["body"]      = $_POST["body"];
	$SYS_FORM["cache"]["location"]      = $_POST["location"];
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
	$location = htmlspecialchars($_POST["location"], ENT_QUOTES);
	$body    = $_POST["body"];

	if ($eid == 0) {
		$eid = get_seqid();
		$ical_uid = $eid.'@'.CONF_URLBASE;

		$q = mysql_exec("insert into schedule_data".
						" (id, pid, subject, body, startymd, endymd, initymd)".
					" values(%s, %s, %s, %s, %s, %s, %s)",
					mysql_num($eid), mysql_num($pid), mysql_str($subject), mysql_str($body),
					mysql_str($SYS_FORM["cache"]["startymd"]),
					mysql_str($SYS_FORM["cache"]["endymd"]),
					mysql_current_timestamp());
		$q = mysql_exec("insert into schedule_data_add_ical".
						"(id, location, ical_uid) values (%s, %s, %s)",
						mysql_num($eid), mysql_str($location), mysql_str($ical_uid));

		foreach($rel_blk as $blk => $uid){
			$new_eid = get_seqid();
			mysql_exec('insert into schedule_data'.
						" (id, pid, $schedule_data_fields)".
						" select %s,%s,$schedule_data_fields from schedule_data where id=%s",
						mysql_num($new_eid),mysql_num($blk),mysql_num($eid));
			mysql_exec('insert into schedule_data_add_ical'.
						" (id, $schedule_data_add_ical_fields)".
						" select %s,$schedule_data_add_ical_fields".
						" from schedule_data_add_ical where id=%s",
						mysql_num($new_eid),mysql_num($eid));
			set_pmt(array(eid => $new_eid, gid =>0, uid=>$uid));
		}
	}
	else {
		$p = mysql_uniq("select ical_uid from schedule_data_add_ical where id = %s",mysql_num($eid));
		$ical_uid = $p['ical_uid'];
		if(!$gid){
			$p = mysql_uniq("select count(*) from schedule_data_add_ical where ical_uid = %s",mysql_str($ical_uid));
			if($p['count(*)']>1){//グループページから登録されたデータである。
				$scd = mysql_uniq("select * from schedule_data natural join schedule_data_add_ical where id = %s",mysql_num($eid));

				//グループを取得
				$p = mysql_uniq("select owner.gid from schedule_data".
								" natural join schedule_data_add_ical".
								" inner join owner on owner.id = schedule_data.pid".
								" where gid <> 0 and ical_uid = %s",
								mysql_num($scd['ical_uid']));
				$group_name = get_site_name($p['gid']);
				$group_url = CONF_URLBASE."/index.php?gid=$p[gid]";

				//変更内容のメッセージを送信
				$startymd_old = datestr_from_mysqldatatime($scd['startymd']);
				$endymd_old = datestr_from_mysqldatatime($scd['endymd']);
				$body_old = $scd['body'];
				$startymd = datestr_from_mysqldatatime($SYS_FORM["cache"]["startymd"]);
				$endymd = datestr_from_mysqldatatime($SYS_FORM["cache"]["endymd"]);
				$message = <<<__MESSG__
連携スケジュールパーツ　スケジュール変更の通知

あなたは連携スケジュールパーツに登録されたグループのスケジュールデータを編集しました。
この変更はあなたのスケジュールにのみ反映され、グループ全体のスケジュールには影響しません。

グループ名：${group_name}
${goup_url}

-----------
変更前の情報
-----------
題名：$scd[subject]
場所:$scd[location]
日時: $startymd_old より $endymd_old
内容:
$body_old

-----------
変更後の情報
-----------
題名：$subject
場所: $location
日時: $startymd より $endymd
内容:
$body

__MESSG__;
				//メッセージの送信を実行する
				send_message_rel_cal(myuid(),get_block_name($pid)."スケジュール更新",$message);
				
				//ical_uidを変更して連携を断つ
				$ical_uid = $eid.'@'.CONF_URLBASE;
				$q = mysql_exec("update schedule_data_add_ical set ical_uid = %s,sequence = 0 where id = %s",mysql_str($ical_uid),mysql_num($eid));
			}
		}
		$q = mysql_exec("update schedule_data natural join schedule_data_add_ical".
						" set subject = %s, body = %s, startymd = %s, ".
						" endymd = %s, location = %s, sequence = sequence+1 where ical_uid = %s",
						mysql_str($subject), mysql_str($body),
						mysql_str($SYS_FORM["cache"]["startymd"]),
						mysql_str($SYS_FORM["cache"]["endymd"]),
						mysql_str($SYS_FORM["cache"]["location"]),
						mysql_str($ical_uid));
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
		$d = mysql_uniq("select * from schedule_data".
						" natural join schedule_data_add_ical".
						" where id = %s",
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
		$location = $d['location'];
	}
	else {
		$subject   = '';
		$body      = '';
		$startymd  = time();
		$endymd    = '';
		$location = '';
	}
	// 再入力ならキャッシュから拾う
	if (isset($SYS_FORM["cache"])) {
		$subject   = $SYS_FORM["cache"]["subject"];
		$body      = $SYS_FORM["cache"]["body"];
		$startymd  = strtotime($SYS_FORM["cache"]["startymd"]);
		if ($SYS_FORM["cache"]["endymd"]) {
			$endymd    = strtotime($SYS_FORM["cache"]["endymd"]);
		}
		$location = $SYS_FORM["cache"]["location"];
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

if(!get_gid($eid) and get_gid($pid))
	$SYS_FORM["input"][] = array(title => '空き時間一覧',
								name => 'fbtable',
								body => 'このグループに対してスケジュールを開示しているユーザーの空き時間を示しています。<br>'.
										'開始・終了時刻設定の参考にしてください。'.
										'<iframe id="fb" width="500" height="500"></iframe>'
								);
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
								 body  => get_form("textarea", $attr));

	// text:location
	$attr = array(name => 'location', value => $location, size => 64);
	$SYS_FORM["input"][] = array(title => '場所',
								 name  => 'location',
								 body  => get_form("text", $attr));

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
		$('#fb').attr("src","fbtable.php?pid=${pid}&date="+$('#start_date').val());
	}
});
\$('#end_date').datepicker({yearRange:'-10:+10'});
$('#end_date').datepicker('setDate',$('#start_date').datepicker('getDate'));
$('#fb').attr("src","fbtable.php?pid=${pid}&date="+$('#start_date').val());
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
function datestr_from_mysqldatatime($str){
	if(!$str)return null;
	$time = strtotime($str);
	$week    = array("日", "月", "火", "水", "木", "金", "土");
	return date( "Y年n月j日", $time ). $week[date( "w", $time ) ] . "曜日 ".
			date("h時i分",$time);
}
function send_message_rel_cal($to,$subject,$body){
	if(!is_array($to))$to = array($to);
	foreach($to as $t){
		$new_id   = get_seqid();
		$i = mysql_exec('insert into message_data'.
						' (id, from_uid, to_uid, subject, message, is_new)'.
						' values (%s, %s, %s, %s, %s, %s)',
						mysql_num($new_id),
						mysql_num(myuid()), mysql_num($t),
						mysql_str($subject), mysql_str(nl2br($body)), mysql_num(1));
		if($mail_to = get_fwd_mail($t)){
			$body_head = get_handle($t). " 様\n\n";
			sys_fwdmail(array('to' => $mail_to, 'subject' => $subject, 'body' => $body_head. $body));
		}
	}
}
?>
