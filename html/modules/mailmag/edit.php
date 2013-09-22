<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require dirname(__FILE__). '/../../lib.php';

/* 振り分け*/
$eid = intval($_REQUEST["eid"]);
$pid = intval($_REQUEST["pid"]);

switch ($_REQUEST["action"]) {
	case 'regist':
		regist_data($eid, $pid);
	default:
		input_data($eid, $pid);
}

/* 登録*/
function regist_data($eid = null, $pid = null) {
	global $SYS_FORM;

	if ($eid > 0) {
		$id = $eid;
	}
	else if ($pid > 0) {
		$id = $pid;
	}
	else {
		show_error('選択元が不明です。');
	}

	if (!is_owner($id)) {
		die('You are not owner of '. $id);
	}

	// フォームのキャッシュに溜め込む
	$SYS_FORM["cache"]["subject"] = isset($_POST["subject"]) ? $_POST["subject"] : '無題';
	$SYS_FORM["cache"]["body"]    = $_POST["body"];
	if (intval($_POST["initymd_set"]) == 1) {
		$SYS_FORM["cache"]["initymd"]     = post2timestamp('initymd');
		$SYS_FORM["cache"]["initymd_set"] = 1;
	}
	else {
		$SYS_FORM["cache"]["initymd"] = date('Y-m-d H:i:s');
		$SYS_FORM["cache"]["initymd_set"] = 0;
	}

	// 入力エラーチェック
	if (!$SYS_FORM["cache"]["body"] || $SYS_FORM["cache"]["body"] == '<br />') {
		$SYS_FORM["error"]["body"] = '内容は何か書いてください。';
	}
	if ($SYS_FORM["error"]) {
		return;
	}
	// 登録
	$subject = htmlspecialchars($SYS_FORM["cache"]["subject"], ENT_QUOTES);
	$body    = $SYS_FORM["cache"]["body"];
	$initymd = $SYS_FORM["cache"]["initymd"];

	// pidはもう使いません。コード整理時に消します。
	if ($eid == 0) {
		$eid = get_seqid();

		$q = mysql_exec("insert into blog_data".
						" (id, pid, subject, body, initymd)".
					" values(%s, %s, %s, %s, %s)",
					mysql_num($eid), mysql_num($pid),
					mysql_str($subject), mysql_str($body), mysql_str($initymd));
	}
	else {
		$q = mysql_exec("update blog_data set subject = %s, body = %s, initymd = %s".
						" where id = %s",
						mysql_str($subject), mysql_str($body), mysql_str($initymd),
						mysql_num($eid));
	}

	if (!$q) {
		show_error('登録に失敗しました。'. mysql_error());
	}

//	set_keyword($eid);
	set_point($eid,$pid);
	set_pmt(array(eid => $eid, gid =>get_gid($pid), name => 'pmt_0'));

	$html = '編集完了しました。';
	$data = array(title   => 'ブログ編集完了',
				  icon    => 'finish',
				  content => $html. create_form_return(array(eid => $eid, href => home_url($eid))));

	show_input($data);

	exit(0);
}

function input_data($eid = null, $pid = null) {
	global $JQUERY, $SYS_INPUT_SCRIPT;
	global $SYS_BOX_TITLE;

	$SYS_BOX_TITLE = '過去の配信一覧';

	$f = mysql_full("select * from mailmag_data as d".
					" where d.pid = %s".
					" order by d.initymd desc",
					mysql_num($eid));

	$list = array(); $style =array();
	
	$style = array(id =>'width: 50px;');

	$list[] = array(id      => '',
					subject => '題名',
					body    => '内容',
					initymd => '日付');
	if ($f) {
		while ($r = mysql_fetch_array($f)) {
			$href = "/index.php?eid=$r[id]&blk_id=$r[pid]";
			$subject = $r['subject'] ? $r['subject'] : '無題';
			$list[] = array(id      => make_href('削除', '/del_content.php?module=mailmag&eid='. $r['id'], true),
							subject => make_href($subject, $href, null, '_blank', 32),
							body    => clip_str($r['body'], 50),
							initymd => date('Y年m月d日 H時i分', strtotime($r['initymd'])));
		}
	}

	$html = create_list($list);

	$data = array(title   => $SYS_BOX_TITLE,
				  icon    => 'write',
				  content => $html);

	show_input($data);

	exit(0);
}

?>
