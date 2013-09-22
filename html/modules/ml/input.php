<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require_once dirname(__FILE__). '/../../lib.php';

list($eid, $pid) = get_edit_ids();

/* ふりわけ。*/
switch ($_REQUEST["action"]) {
	case 'regist':
		regist_data($eid, $pid);
	default:
		input_data($eid, $pid);
}

/* とうろく。*/
function regist_data($eid = null, $pid = null) {
	global $SYS_FORM;

	// フォームのキャッシュに溜め込む
	$SYS_FORM["cache"]["title"]     = $_POST["title"];
	$SYS_FORM["cache"]["desc"]      = preg_replace('/\n/', '<br />', $_POST["desc"]);
	$SYS_FORM["cache"]["ml_prefix"] = $_POST["ml_prefix"];
	$SYS_FORM["cache"]["archive_pmt"] = $_POST["archive_pmt"];

	$SYS_FORM["cache"]["block_header"] = $_POST["block_header"];
	$SYS_FORM["cache"]["block_footer"] = $_POST["block_footer"];

	// settingにとうろく
	$d = mysql_exec("delete from mod_ml_setting where id = %s", mysql_num($eid));
	$q = mysql_exec("insert into mod_ml_setting".
					" (`id`, `title`, `desc`, `ml_prefix`, `archive_pmt`, `header`, `footer`)".
					" values (%s, %s, %s, %s, %s, %s, %s)",
					mysql_num($eid),
					mysql_str($SYS_FORM["cache"]["title"]),
					mysql_str($SYS_FORM["cache"]["desc"]),
					mysql_str($SYS_FORM["cache"]["ml_prefix"]),
					mysql_num($SYS_FORM["cache"]["archive_pmt"]),
					mysql_str($SYS_FORM["cache"]["block_header"]),
					mysql_str($SYS_FORM["cache"]["block_footer"]));

	if (!$q) {
		error_window('データの登録に失敗しました。'. mysql_error());
	}

	$c = mysql_uniq('select * from mod_ml_backnumber where ml_id = %s',
					mysql_num($eid));

	if (!$c) {
		$i = mysql_exec('insert into mod_ml_backnumber (id, ml_id) values (%s, %s)',
						mysql_num(get_seqid()), mysql_num($eid));

		$i = mysql_exec('insert into mod_ml_member (ml_id, uid, status) values (%s, %s, %s)',
						mysql_num($eid), mysql_num(myuid()), mysql_num(1));
	}

	$html = '編集完了しました。';
	$data = array(title   => 'メッセージングリスト基本設定',
				  icon    => 'finish',
				  content => $html. create_form_return(array(eid => $eid, href => home_url($eid))));

	show_input($data);

	exit(0);
}

/* ふぉーむ。*/
function input_data($eid = null, $pid = null) {
	global $SYS_FORM, $JQUERY;

	$d = mysql_uniq("select * from mod_ml_setting where id = %s",
					mysql_num($eid));

	// settingからロード
	if ($d) {
		$title     = $d["title"];
		$desc      = preg_replace('/<br \/>/', "\n", $d["desc"]);
		$ml_prefix = $d["ml_prefix"];
		$archive_pmt = $d["archive_pmt"];
		$block_header = $d["header"];
		$block_footer = $d["footer"];
	}
	else {
		$title     = '';
		$desc      = '';
		$ml_prefix = '';
		$archive_pmt = 0;
		$block_header    = '';
		$block_footer    = '';
	}
	// 再入力ならキャッシュから拾う
	if (isset($SYS_FORM["cache"])) {
		$title        = $SYS_FORM["cache"]["title"];
		$desc         = preg_replace('/<br \/>/', "\n", $SYS_FORM["cache"]["desc"]);
		$ml_prefix    = $SYS_FORM["cache"]["ml_prefix"];
		$archive_pmt  = $SYS_FORM["cache"]["archive_pmt"];
		$block_header = $SYS_FORM["cache"]["block_header"];
		$block_footer = $SYS_FORM["cache"]["block_footer"];
	}

	// hidden:action
	$attr = array(name => 'action', value => 'regist');
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));

	// text:title
	$attr = array('name' => 'title', 'value' => $title, 'size' => 48);
	$SYS_FORM["input"][] = array('title' => 'メッセージングリストのタイトル',
								 'name'  => 'title',
								 'body'  => get_form("text", $attr));

	// text:title
	$attr = array('name' => 'desc', 'value' => $desc, 'height' => '80px');
	$SYS_FORM["input"][] = array('title' => 'メッセージングリストの説明',
								 'name'  => 'desc',
								 'body'  => get_form("textarea", $attr));

	// text:title
	$attr = array('name' => 'ml_prefix', 'value' => $ml_prefix, 'size' => 32);
	$SYS_FORM["input"][] = array('title' => 'メールの題名に追加する文字列 (オプション)',
								 'name'  => 'ml_prefix',
								 'body'  => get_form("text", $attr));

	$option = array(0 => '非公開', 1 => 'メッセージングリスト参加者のみ', 2 => '公開');
	$attr = array('name' => 'archive_pmt', value => $archive_pmt, option => $option);
	$SYS_FORM["input"][] = array(title => 'バックナンバーの公開範囲',
								 name  => 'archive_pmt',
								 body  => get_form("radio", $attr));

	// fck:header
	$attr = array('name' => 'block_header', 'value' => $block_header,
				  'cols' => 64, 'rows' => 8, 'toolbar' => 'Basic');
	$SYS_FORM["input"][] = array('title' => 'ヘッダー (ブロック上部に表示)',
								 'name'  => 'block_header',
								 'body'  => get_form("fck", $attr));

	// fck:footer
	$attr = array('name' => 'block_footer', 'value' => $block_footer,
				  'cols' => 64, 'rows' => 8, 'toolbar' => 'Basic');
	$SYS_FORM["input"][] = array('title' => 'フッター (ブロック下部に表示)',
								 'name'  => 'block_footer',
								 'body'  => get_form("fck", $attr));

	$SYS_FORM["input"][] = array('title' => '現在の登録メンバー',
								 'name'  => 'member',
								 'body'  => mod_ml_load_member($eid));


	$SYS_FORM["action"] = 'input.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["pmt"]    = false;
	$SYS_FORM["submit"] = '登録';
	$SYS_FORM["cancel"] = '取消';
	$SYS_FORM["onCancel"] = 'history.back(); return false;';

	$html = create_form(array('eid' => $eid));

	$data = array(title   => 'メッセージングリスト基本設定',
				  icon    => 'write',
				  content => $html);

	show_input($data);

	exit(0);
}

function mod_ml_load_member($id = 0) {
	$q = mysql_full('select * from mod_ml_member where ml_id = %s', mysql_num($id));

	$member = array();
	if (count($q) > 0) {
		while ($res = mysql_fetch_assoc($q)) {
			$p = mysql_uniq('select * from page where uid = %s and gid = 0',
							mysql_num($res['uid']));
			if (isset($p['id'])) {
				$member[] = '<a href="/index.php?uid='. $res['uid']. '">'.
							get_handle($res['uid']). '</a>&nbsp;さん';
			}
			else {
				$member[] = get_handle($res['uid']). ' さん';
			}
		}
	}

	return '<div style="padding: 3px;">'. implode(', ', $member). '</div>';
}


?>
