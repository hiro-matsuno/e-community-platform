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

if($eid and is_owner($eid,80))show_error('権限がありません');
if($pid and is_owner($pid,80))show_error('権限がありません');

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
		show_error('選択元が謎です。');
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
	$data = array(title   => '防災ウェブ雛形編集完了',
				  icon    => 'finish',
				  content => $html. create_form_return(array(eid => $eid, href => home_url($eid))));

	show_input($data);

	exit(0);
}

function input_data($eid = null, $pid = null) {
	global $JQUERY, $SYS_INPUT_SCRIPT, $SYS_FORM;

	$f = mysql_full('select d.* from bosai_web_category as d'.
					' where d.pid = %s'.
					' order by d.num',
					mysql_num($pid));

	if ($f) {
		while ($c = mysql_fetch_array($f)) {
			$option[$c['eid']] = $c['name'];
		}
	}
	else {
		$option[0] = '分類が未登録です。';
	}

	$f = mysql_full('select d.* from bosai_web_template as d'.
					' where d.pid = %s'.
					' order by d.category, d.num',
					mysql_num($pid));

	if (!$f) {
		$f = array();
	}

	$list = array();
	$list[] = array(category => '時期',
					subject => 'タイトル',
					body    => '内容');

	if ($f) {
		while ($r = mysql_fetch_array($f)) {
			$subject = $r['subject'] ? $r['subject'] : '無題';
			$count = intval($r['count']) ? intval($r['count']) : 1;
			switch (intval($r['display'])) {
				case '2':
					$display = '<div style="white-space: nowrap; color: #8abfd6;">承認済</div>';
					break;
				case '1':
					$count++;
					$display = '<div style="white-space: nowrap; color: #fca890;">承認待ち</div>';
					break;
				default:
					$display = '<div style="white-space: nowrap; color: #999;">編集中</div>';
			}
			$list[] = array(id      => $r['id'],
							category => $option[$r['category']],
							subject => $r['subject'],
							body    => clip_str($r['body'], 50));
//							updymd  => date('Y年m月d日 H時i分', tm2time($r['updymd'])));
		}
	}

	set_return_url();

	$editor = array('編集' => '/modules/bosai_web/template.php?eid=',
					'削除.edit_delbtn thickbox' => '/del_content.php?module=bosai_web&eid=');


	$c = mysql_full('select d.* from bosai_web_category as d'.
					' where d.pid = %s',
					mysql_num($pid));

	$option = array();
	if ($c) {
		while ($r = mysql_fetch_array($c)) {
			$option[$r['eid']] = $r['name'];
		}
	}

	$bhtml = '';
	$ahtml = ' <input type="submit" value="並び替えモード">';
	$attr = array(name => 'cat', option => $option, ahtml => $ahtml);
	$html = '<form action="template_order.php" method="GET">'.
			'<input type="hidden" name="pid" value="'. $pid. '">'.
			get_form("select", $attr).
			'</form>';

	$html .= create_auth_list($editor, $list);

	$data = array(title   => '防災ウェブ雛形一覧',
				  icon    => 'write',
				  content => $html);

	show_input($data);

	exit(0);
}

?>
