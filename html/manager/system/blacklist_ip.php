<?php
/*
 *  地域防災キット基本モジュール
 *  ブラックリストIP設定(マスター)
 */

require dirname(__FILE__). '/../../lib.php';

su_check();

$act = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

switch ($act) {
	case 'regist':
		entry_data();
		break;
	default:
		input_new();
}

/* entry_data */
function entry_data() {
	$list = isset($_POST['list']) ? $_POST['list'] : '';

	$list = str_replace("\r", "\n", str_replace("\r\n", "\n", $list));

	$ip_array = explode("\n", $list);

	$d = mysql_exec('delete from core_blacklist_ip_master');

	foreach ($ip_array as $ip) {
		if ($ip == '') {
			continue;
		}
		$i = mysql_exec('insert into core_blacklist_ip_master (ip) values (%s)',
						mysql_str($ip));
	}

	$ref  = '/manager/system/blacklist_ip.php';
	$html = 'コメント・トラックバック拒否IPアドレスを設定しました。';
	$data = array(title   => 'コメント・トラックバック拒否IPアドレス',
				  icon    => 'finish',
				  content => $html. create_form_return(array(eid => 0, href => $ref, string => '設定画面に戻る')));

	show_input($data);

	exit(0);
}

/* input_new */
function input_new() {
	global $SYS_FORM;

	$list = '';

	$q = mysql_full('select * from core_blacklist_ip_master');

	if ($q) {
		while ($res = mysql_fetch_assoc($q)) {
			$list .= $res['ip']. "\n";
		}
	}

	$attr = array(name => 'action', value => 'regist');
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));

	$bhtml  = '<div style="padding: 3px; line-height: 1.2em;">';
	$bhtml .= '拒否したいIPアドレス、またはホスト名を入力して下さい。<br />';
	$bhtml .= 'ここで指定した拒否設定は、全ユーザー・グループに適用されます。<br />';
	$bhtml .= '</div>';

	$attr = array('name' => 'list', 'value' => $list, 'width' => '200px', 'height' => '300px',
				  'bhtml' => $bhtml);
	$SYS_FORM["input"][] = array('title' => 'コメント・トラックバック拒否IPアドレス',
								 'name'  => 'list',
								 'body'  => get_form("textarea", $attr));

	$SYS_FORM["action"] = 'blacklist_ip.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["submit"] = '設定';
	$SYS_FORM["cancel"] = '取消';

	$html = create_form(array(eid => 0));

	$data = array(title   => 'コメント・トラックバック拒否IPアドレス',
				  icon    => 'write',
				  content => $html);

	show_input($data);

	exit(0);
}

?>
