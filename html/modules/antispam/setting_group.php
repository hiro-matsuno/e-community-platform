<?php
/*
 *  地域防災キット基本モジュール
 *  CAPTCHA設定(ユーザー)
 */

require dirname(__FILE__). '/../../lib.php';

$act = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

$gid  = isset($_REQUEST['gid']) ? intval($_REQUEST['gid']) : 0;

if (!is_master(array('gid' => $gid))) {
	show_error('グループ管理者のみ変更可能な機能です。');
}

switch ($act) {
	case 'regist':
		entry_data($gid);
		break;
	default:
		input_new($gid);
}

/* entry_data */
function entry_data($gid = 0) {
	$list = isset($_POST['list']) ? $_POST['list'] : '';

	$list = str_replace("\r", "\n", str_replace("\r\n", "\n", $list));

	$ip_array = explode("\n", $list);

	$d = mysql_exec('delete from core_blacklist_ip_group where gid = %s',
					mysql_num($gid));

	foreach ($ip_array as $ip) {
		if ($ip == '') {
			continue;
		}
		$i = mysql_exec('insert into core_blacklist_ip_group (gid, ip) values (%s, %s)',
						mysql_num($gid), mysql_str($ip));
	}


	$list = isset($_POST['ngword_list']) ? $_POST['ngword_list'] : '';

	$list = str_replace("\r", "\n", str_replace("\r\n", "\n", $list));

	$word_array = explode("\n", $list);

	$d = mysql_exec('delete from core_ngword_group where gid = %s',
					mysql_num($gid));

	foreach ($word_array as $word) {
		if ($word == '') {
			continue;
		}
		$i = mysql_exec('insert into core_ngword_group (gid, word) values (%s, %s)',
						mysql_num($gid), mysql_str($word));
	}

	$type = isset($_POST['type']) ? intval($_POST['type']) : 0;

	$d = mysql_exec('delete from core_captcha_setting_group where gid = %s',
					mysql_num($gid));
	$i = mysql_exec('insert into core_captcha_setting_group (gid, type) values (%s, %s)',
					mysql_num($gid), mysql_num($type));


	$html = 'コメント・トラックバック拒否IPアドレスを設定しました。';
	$data = array('title'   => 'コメント・トラックバック拒否IPアドレス',
				  'icon'    => 'finish',
				  'content' => $html. create_form_remove());

	show_dialog($data);

	exit(0);
}

/* input_new */
function input_new($gid = 0) {
	global $SYS_FORM;


	$q = mysql_uniq('select * from core_captcha_setting_group where gid = %s',
					mysql_num($gid));

	if ($q) {
		$type = $q['type'];
	}
	else {
		$c = mysql_uniq('select * from core_captcha_setting_master limit 1');
		if ($c) {
			$type = $c['type'];
		}
		else {
			$type = 0;
		}
	}

	$attr = array('name' => 'action', 'value' => 'regist');
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));

	$option = array(0 => '無効にする', 1 => '有効にする');
	$attr = array('name' => 'type', 'value' => $type, 'option' => $option, 'ahtml' => $ahtml);
	$SYS_FORM["input"][] = array('title' => 'コメント書き込み時のCAPTCHAの設定',
								 'name'  => 'use_confirm',
								 'body'  => get_form("radio", $attr));


	$q = mysql_full('select * from core_blacklist_ip_group where gid = %s order by id',
					mysql_num($gid));

	if ($q) {
		while ($res = mysql_fetch_assoc($q)) {
			$list .= $res['ip']. "\n";
		}
	}

	$bhtml  = '<div style="padding: 3px; line-height: 1.2em;">';
	$bhtml .= 'コメント投稿・トラックバックを拒否したいIPアドレス、またはホスト名を入力して下さい。<br />';
	$bhtml .= '改行で区切ることで複数登録できます。<br />';
	$bhtml .= '</div>';

	$attr = array('name' => 'list', 'value' => $list, 'width' => '200px', 'height' => '150px',
				  'bhtml' => $bhtml);
	$SYS_FORM["input"][] = array('title' => 'コメント・トラックバック拒否IPアドレス',
								 'name'  => 'list',
								 'body'  => get_form("textarea", $attr));


	$q = mysql_full('select * from core_ngword_group where gid = %s order by id',
					mysql_num($gid));

	if ($q) {
		while ($res = mysql_fetch_assoc($q)) {
			$ngword_list .= $res['word']. "\n";
		}
	}

	$bhtml  = '<div style="padding: 3px; line-height: 1.2em;">';
	$bhtml .= 'コメント投稿・トラックバックで拒否したい言葉を入力して下さい。<br />';
	$bhtml .= '言葉は改行で区切ることで複数登録できます。<br />';
	$bhtml .= '</div>';

	$attr = array('name' => 'ngword_list', 'value' => $ngword_list, 'width' => '200px', 'height' => '150px',
				  'bhtml' => $bhtml);
	$SYS_FORM["input"][] = array('title' => 'コメント・トラックバック禁止ワード',
								 'name'  => 'ngword_list',
								 'body'  => get_form("textarea", $attr));


	$SYS_FORM["action"] = 'setting_group.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["submit"] = '設定';
	$SYS_FORM["cancel"] = '戻る';

	$html = create_form(array('eid' => get_eid(array('gid' => $gid))));

	$data = array(title   => 'スパム対策の設定',
				  icon    => 'write',
				  content => $html);

	show_dialog($data);

	exit(0);
}

?>
