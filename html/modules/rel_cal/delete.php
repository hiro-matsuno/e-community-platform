<?php
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
require dirname(__FILE__). '/../../lib.php';

/* 振り分け*/
list($eid, $pid) = get_edit_ids();

switch ($_REQUEST["action"]) {
	case 'delete':
		delete($eid, $pid);
	default:
		confirm($eid, $pid);
}
function delete($eid = null,$pid = null){
	$scd = mysql_uniq("select * from schedule_data natural join schedule_data_add_ical where id = %s",mysql_num($eid));
	$gid = get_gid($eid);
	if(!$gid){
		$p = mysql_uniq("select count(*) from schedule_data_add_ical where ical_uid = %s",mysql_str($scd['ical_uid']));
		if($p['count(*)']>1){
			//グループを取得
			$p = mysql_uniq("select owner.gid from schedule_data".
							" natural join schedule_data_add_ical".
							" inner join owner on owner.id = schedule_data.pid".
							" where gid <> 0 and ical_uid = %s",
							mysql_str($scd['ical_uid']));
			$group_name = get_gname($p['gid']);
			$group_url = CONF_URLBASE."/index.php?gid=$p[gid]";
			//変更内容のメッセージを送信
			$title = htmlesc($scd['title']);
			$location = htmlesc($scd['location']);
			$startymd = datestr_from_mysqldatatime($scd['startymd']);
			$endymd = datestr_from_mysqldatatime($scd['endymd']);
			$body = strip_tags($body);
			$message = <<<__MESSG__
連携スケジュールパーツ　スケジュール削除の通知

あなたは連携スケジュールパーツに登録されたグループのスケジュールデータを削除しました。
この変更はあなたのスケジュールパーツにのみ反映され、グループ全体のスケジュールには影響しません。

グループ名：${group_name}
${group_url}

-----------
削除した内容
-----------
タイトル：$title
場所:$location
日時: $startymd より $endymd
内容:
$body
__MESSG__;
			//メッセージの送信を実行する
			send_message(myuid(),get_block_name($pid)."スケジュール更新",$message);
		}
		mysql_exec("delete schedule_data,schedule_data_add_ical".
					" from schedule_data natural join schedule_data_add_ical".
					" where schedule_data_add_ical.ical_id = %s",
					mysql_num($eid));
	}else{
		mysql_exec("delete schedule_data,schedule_data_add_ical".
					" from schedule_data natural join schedule_data_add_ical".
					" where schedule_data_add_ical.ical_uid = %s",
					mysql_str($scd['ical_uid']));
	}

	$data = array(title   => '記事を削除しました。',
				  icon    => 'finish',
				  content => reload_form());
	
	show_dialog2($data);
	
	show_input($data);
	
}
function confirm($eid = null,$pid = null){
	global $SYS_FORM;
	$scd = mysql_uniq("select * from schedule_data natural join schedule_data_add_ical where id = %s",mysql_num($eid));
	$gid = get_gid($eid);
	if(!$gid){
		$p = mysql_uniq("select count(*) from schedule_data_add_ical where ical_uid = %s",mysql_str($scd['ical_uid']));
		if($p['count(*)']>1){
			//グループを取得
			$p = mysql_uniq("select owner.gid from schedule_data".
							" natural join schedule_data_add_ical".
							" inner join owner on owner.id = schedule_data.pid".
							" where gid <> 0 and ical_uid = %s",
							mysql_str($scd['ical_uid']));
			$group = make_href(get_gname($p['gid']),CONF_URLBASE."/index.php?gid=$p[gid]");
			//注意書き
			$note .= "${group}から登録されたスケジュールです。<br>";
			$note .= "削除はあなたのスケジュールパーツからのみ行われ、グループ全体のスケジュールには影響しません。<br>";
		}
	}
	$title = $scd['subject'];
	$location = $scd['location'];
	$startymd = datestr_from_mysqldatatime($scd['startymd']);
	$endymd = datestr_from_mysqldatatime($scd['endymd']);	
	$confirm = <<<__MESSG__
<h2>スケジュールの削除</h2>
以下の内容のスケジュールを削除します。<br>
$note
<hr>
<div>
タイトル：$title<br>
場所:$location<br>
日時: $startymd より $endymd<br>
内容:<br>
$scd[body]
</div>
<hr>
__MESSG__;

	// hidden:action
	$attr = array(name => 'action', value => 'delete');
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));
	$SYS_FORM["submit"] = '削除';
	$SYS_FORM["cancel"] = '取消';

	$html = create_form(array(eid => $eid, pid => $pid));

	$data = array(title   => '記事の削除',
				  icon    => 'write',
				  content => $confirm.$html);

	show_dialog2($data);

	exit(0);
}
function datestr_from_mysqldatatime($str){
	if(!$str)return null;
	$time = strtotime($str);
	$week    = array("日", "月", "火", "水", "木", "金", "土");
	return date( "Y年n月j日", $time ). $week[date( "w", $time ) ] . "曜日 ".
			date("h時i分",$time);
}
function send_message($to,$subject,$body){
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
