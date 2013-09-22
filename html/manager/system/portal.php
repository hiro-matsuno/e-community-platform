<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require dirname(__FILE__). '/../../lib.php';

admin_check();

switch ($_REQUEST["action"]) {
	case 'regist':
		entry_data();
		break;
	case 'confirm':
		confirm_data();
		break;
	default:
		;
}

input_new();

function entry_data() {
	$portal_gid = $_POST['portal_gid'];

	if (isset($portal_gid) && $portal_gid != '') {
		$d = mysql_exec('delete from portal');
		$i = mysql_exec('insert portal (gid) values (%s)', mysql_num($portal_gid));
	}
	$u = mysql_exec('update page set enable = 1 where gid = %s',
					mysql_num($portal_gid));
					
	$ref = '/manager/system/portal.php';
	$html = 'ポータルページを設定しました。';
	$data = array(title   => 'ポータルページ設定',
				  icon    => 'finish',
				  content => $html. create_form_return(array(eid => 0, href => $ref, string => 'ポータルページの設定に戻る',)));

	show_input($data);

	exit(0);
}

function confirm_data($eid = null, $pid = null) {
	global $SYS_FORM, $JQUERY, $COMUNI_HEAD_JS, $COMUNI_HEAD_CSS;

	$ses_data = array();
	$ses_data['nickname'] = htmlesc($_POST['nickname']);
	$ses_data['email']    = htmlesc($_POST['email']);
	$ses_data['passwd']   = htmlesc($_POST['passwd']);

	$_SESSION[SES_NAME] = $ses_data;

	if (!preg_match("/^[_\-a-zA-Z0-9]+$/", $ses_data['nickname'])) {
		$SYS_FORM['error']['nickname'] = 'ニックネームは半角英数字、ハイフン、アンダーバーのみ使えます。';
	}
	if ($ses_data['nickname'] == '') {
		$SYS_FORM['error']['nickname'] = 'ニックネームを記入して下さい。';
	}
	$c = mysql_uniq('select * from user where handle = %s',
					mysql_str($ses_data['nickname']));
	if ($c) {
		$SYS_FORM['error']['nickname'] = '使用済のニックネームです。別のニックネームにしてください。';
		unset($ses_data['nickname']);
	}
	if ($ses_data['email'] == '') {
		$SYS_FORM['error']['email'] = 'メールアドレスを記入して下さい。';
	}
	$c = mysql_uniq('select * from user where email = %s',
					mysql_str($ses_data['email']));
	if ($c) {
		$SYS_FORM['error']['email'] = 'すでに登録済のメールアドレスです。';
		unset($ses_data['email']);
	}
	if ($ses_data['passwd'] == '') {
		$SYS_FORM['error']['passwd'] = 'パスワードを記入して下さい。';
	}
	if ($ses_data['passwd'] != htmlesc($_POST['passwd_c'])) {
		$SYS_FORM['error']['passwd'] = 'パスワードが再入力と一致しません。';
	}
	if (isset($SYS_FORM['error'])) {
		return;
	}

	// text:sitename
	$SYS_FORM["header"][] = '下記の内容をご確認下さい。';

	// hidden:regist
	$attr = array(name => 'action', value => 'regist');
	$SYS_FORM["input"][] = array(body  => get_form("hidden", $attr));

	// text:sitename
	$SYS_FORM["input"][] = array(title => 'ニックネーム',
								 name  => 'nickname',
								 body  => $ses_data['nickname']);
	$SYS_FORM["input"][] = array(title => 'メールアドレス',
								 name  => 'email',
								 body  => $ses_data['email']);
	$SYS_FORM["input"][] = array(title => 'パスワード',
								 name  => 'passwd',
								 body  => $ses_data['passwd']);

	$SYS_FORM["action"] = 'adduser.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["submit"] = 'ユーザーを追加';
	$SYS_FORM["cancel"] = '取消';

	$html = create_form(array(eid => 0));

	$data = array(title   => 'ユーザーの追加',
				  icon    => 'write',
				  content => $html);

	show_input($data);

	exit(0);
}

function input_new() {
	global $SYS_FORM;

	$q = mysql_uniq('select * from portal');

	$current_gid = '';
	if ($q) {
		$current_gid = $q['gid'];
	}

	$g = mysql_full('select * from page where gid > 0');
	
	$option = array();
	if ($g) {
		while ($r = mysql_fetch_array($g)) {
			$option[$r['gid']] = $r['sitename'];
		}
	}

	$attr = array(name => 'action', value => 'regist');
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));

	$attr = array(name => 'portal_gid', value => $current_gid, option => $option);
	$SYS_FORM["input"][] = array(title => 'ポータルに設定するグループページの選択',
								 name  => 'portal_gid',
								 body  => get_form("select", $attr));

	$SYS_FORM["action"] = 'portal.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["submit"] = '設定';
	$SYS_FORM["cancel"] = '戻る';

	$html = create_form(array(eid => 0));

	$data = array(title   => 'ポータルページの設定',
				  icon    => 'write',
				  content => $html);

	show_input($data);

	exit(0);
}

?>
