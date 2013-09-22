<?php
/*
 *  地域防災キット基本モジュール
 *  CAPTCHA設定(ユーザー)
 */

require dirname(__FILE__). '/../../lib.php';

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

	$word_array = explode("\n", $list);

	$d = mysql_exec('delete from core_ngword_user where uid = %s',
					mysql_num(myuid()));

	foreach ($word_array as $word) {
		if ($word == '') {
			continue;
		}
		$i = mysql_exec('insert into core_ngword_user (uid, word) values (%s, %s)',
						mysql_num(myuid()), mysql_str($word));
	}

	$html = 'コメント・トラックバック禁止ワードを設定しました。';
	$data = array('title'   => 'コメント・トラックバック禁止ワード',
				  'icon'    => 'finish',
				  'content' => $html. create_form_remove());

	show_dialog($data);

	exit(0);
}

/* input_new */
function input_new() {
	global $SYS_FORM;

	$q = mysql_full('select * from core_ngword_user where uid = %s order by id',
					mysql_num(myuid()));

	if ($q) {
		while ($res = mysql_fetch_assoc($q)) {
			$list .= $res['word']. "\n";
		}
	}

	$attr = array('name' => 'action', 'value' => 'regist');
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));

	$bhtml  = '<div style="padding: 3px; line-height: 1.2em;">';
	$bhtml .= 'コメント投稿・トラックバックで拒否したい言葉を入力して下さい。<br />';
	$bhtml .= '言葉は改行で区切ることで複数登録できます。<br />';
	$bhtml .= '</div>';

	$attr = array('name' => 'list', 'value' => $list, 'width' => '200px', 'height' => '300px',
				  'bhtml' => $bhtml);
	$SYS_FORM["input"][] = array('title' => 'コメント・トラックバック禁止ワード',
								 'name'  => 'list',
								 'body'  => get_form("textarea", $attr));

	$SYS_FORM["action"] = 'setting_user.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["submit"] = '設定';
	$SYS_FORM["cancel"] = '戻る';

	$html = create_form(array(eid => 0));

	$data = array(title   => 'コメント・トラックバック禁止ワード',
				  icon    => 'write',
				  content => $html);

	show_dialog($data);

	exit(0);
}

?>
