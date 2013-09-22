<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require dirname(__FILE__). '/../../lib.php';

define('SES_NAME', 'sys_keyword');

admin_check();

$act = isset($_REQUEST["act"]) ? $_REQUEST["act"] : '';

switch ($act) {
	case 'add':
		add_keyword();
	break;
	case 'edit':
		edit_keyword();
	break;
	case 'del':
		del_keyword();
	break;
	default:
		;
}

print_list();

function add_keyword() {
	global $SYS_FORM;

	$id      = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
	$keyword = isset($_REQUEST['keyword']) ? strip_tags($_REQUEST['keyword']) : '';

	$resulet = '';
	if ($keyword != '') {
		$keyword = mb_convert_encoding($keyword, 'UTF-8', 'auto');
		$keyword = mb_ereg_replace("　", " ", $keyword);
		$keyword = trim($keyword);

		$kwds = array();
		if (preg_match('/ /', $keyword)) {
			$kwds = split(' ', $keyword);
		}
		else {
			$kwds = array($keyword);
		}
		foreach ($kwds as $k) {
			if ($k == '') {
				continue;
			}
			$q = mysql_uniq("select * from tag_setting where keyword = %s", mysql_str($k));

			$tag_id = isset($q["id"]) ? $q["id"] : null;
			if (!$tag_id) {
				$tag_id = get_seqid();
				$f = mysql_exec('insert into tag_setting(id, keyword) values(%s, %s)',
								mysql_num($tag_id), mysql_str($k));
				set_pmt(array('eid' => $tag_id, 'unit' => 0));
			}
			else {
				$result .= $k. 'はすでに追加済です。<br>';
			}
		}

		$msg  = '<div style="padding: 0 5px;">キーワードを追加しました。</div>';
		if ($result != '') {
			$msg .= '<div style="padding: 0 5px;">'. $result. '</div>';
		}
		$data = array('title'   => 'キーワード追加',
					  'content' => $msg. reload_form());

		show_dialog($data);
	}

	$q = mysql_uniq('select * from tag_setting where id = %s',
					mysql_num($id));

	$SYS_FORM['head'][] = 'キーワード <strong>'. $q['keyword']. '</strong> を変更します。';

	$SYS_FORM['input'][] = array('body' => get_form('hidden',
													array('name'  => 'act',
														  'value' => 'add')));

	$attr = array('name' => 'keyword', 'value' => $q['keyword'], 'size' => 40,
				  'ahtml' => '<div>一気に複数追加したい場合は空白で区切って下さい。</div>');
	$SYS_FORM['input'][] = array('title' => 'キーワード', 'body' => get_form('text', $attr));

	$SYS_FORM['action'] = 'keyword.php';
	$SYS_FORM['submit'] = '追加する';
	$SYS_FORM['cancel'] = 'キャンセル';
	$SYS_FORM['onCancel'] = 'parent.tb_remove(); return false;';

	$data = array('title'   => 'キーワードの追加',
				  'icon'    => 'write',
				  'content' => create_form());

	show_dialog($data);
}

function edit_keyword() {
	global $SYS_FORM;

	$id      = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
	$keyword = isset($_REQUEST['keyword']) ? strip_tags($_REQUEST['keyword']) : '';

	if ($keyword != '') {
		$keyword = mb_convert_encoding($keyword, 'UTF-8', 'auto');
		$keyword = mb_ereg_replace("　", " ", $keyword);
		$keyword = trim($keyword);

		$u = mysql_exec('update tag_setting set keyword = %s where id = %s',
						mysql_str($keyword), mysql_num($id));

		$msg  = '<div style="padding: 0 5px;">キーワードを変更しました。</div>';
		$data = array('title'   => 'キーワード編集',
					  'content' => $msg. reload_form());

		show_dialog($data);
	}

	$q = mysql_uniq('select * from tag_setting where id = %s',
					mysql_num($id));

	$SYS_FORM['head'][] = 'キーワード <strong>'. $q['keyword']. '</strong> を変更します。';

	$SYS_FORM['input'][] = array('body' => get_form('hidden',
													array('name'  => 'act',
														  'value' => 'edit')));
	$SYS_FORM['input'][] = array('body' => get_form('hidden',
													array('name'  => 'id',
														  'value' => $id)));

	$attr = array('name' => 'keyword', 'value' => $q['keyword'], 'size' => 40);
	$SYS_FORM['input'][] = array('title' => 'キーワード', 'body' => get_form('text', $attr));

	$SYS_FORM['action'] = 'keyword.php';
	$SYS_FORM['submit'] = '変更する';
	$SYS_FORM['cancel'] = 'キャンセル';
	$SYS_FORM['onCancel'] = 'parent.tb_remove(); return false;';

	$data = array('title'   => 'キーワードの変更',
				  'icon'    => 'warning',
				  'content' => create_form());

	show_dialog($data);
}

function del_keyword() {
	$id   = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
	$sure = isset($_REQUEST['sure']) ? true : false;

	if ($id == 0) {
		return;
	}
	if (!$sure) {
		conf_del($id);
		exit(0);
	}

	$d = mysql_exec('delete from tag_setting where id = %s',
					mysql_num($id));

	if (!$d) {
		show_error(mysql_error());
	}

	$msg  = '<div style="padding: 0 5px;">キーワードを削除しました。</div>';
	$data = array('title'   => 'キーワード削除',
				  'content' => $msg. reload_form());

	show_dialog($data);
}

function conf_del($id = 0) {
	global $SYS_FORM;

	$id   = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
	if ($id == 0) {
		return;
	}

	$q = mysql_uniq('select * from tag_setting where id = %s',
					mysql_num($id));

	$SYS_FORM['action'] = 'keyword.php';
	$SYS_FORM['submit'] = '削除する';
	$SYS_FORM['cancel'] = 'キャンセル';
	$SYS_FORM['onCancel'] = 'parent.tb_remove(); return false;';

	$SYS_FORM['head'][] = 'キーワード <strong>'. $q['keyword']. '</strong> を削除してもよろしいですか？';

	$SYS_FORM['input'][] = array('body' => get_form('hidden',
													array('name'  => 'act',
														  'value' => 'del')));
	$SYS_FORM['input'][] = array('body' => get_form('hidden',
													array('name'  => 'id',
														  'value' => $id)));
	$SYS_FORM['input'][] = array('body' => get_form('hidden',
													array('name'  => 'sure',
														  'value' => 1)));

	$data = array('title'   => 'キーワードの削除',
				  'icon'    => 'warning',
				  'content' => create_confirm());

	show_dialog($data);
}

function print_list() {
	global $user_level;

	$q = mysql_full('select t.*, o.uid, u.handle from tag_setting as t'.
					' left join owner as o on t.id = o.id'.
					' left join user as u on u.id = o.uid'.
					' order by t.id desc');

	$list = array();
	$list[] = array('edit'    => '',
					'del'     => '',
					'keyword' => 'キーワード',
					'initymd' => '登録日',
					'owner'   => '登録者');

	$style = array('edit' => 'width: 60px;text-align: center;',
				   'del'  => 'width: 60px;text-align: center;',
				   'initymd' => 'white-space: nowrap;');

	if ($q) {
		while ($r = mysql_fetch_array($q)) {
			$list[] = array('edit'    => mkhref(array('s' => '[編集]', 'h' => 'keyword.php?act=edit&id='. $r['id'], 'c' => 'thickbox')),
							'del'     => mkhref(array('s' => '[削除]', 'h' => 'keyword.php?act=del&id='. $r['id'], 'c' => 'thickbox')),
							'keyword' => $r['keyword'],
							'initymd' => date('Y-m-d H:i:s', tm2time($r['initymd'])),
							'owner'   => isset($r['handle']) ? $r['handle'] : '削除済みユーザー');
		}
	}

	$html  = '<div style="padding: 5px;">';
	$html .= mkhref(array('s' => 'キーワードを追加する&raquo;', 'h' => 'keyword.php?act=add', 'c' => 'add_input thickbox'));
	$html .= '</div>';

	$html .= create_list($list, $style);

	$data = array('title'   => 'キーワード一覧',
				  'content' => $html);

	show_input($data);

	exit(0);
}

function user_exists($uid = 0) {
	if (mysql_uniq('select * from user where id = %s', mysql_num($uid))) {
		return true;
	}
	return false;
}

function get_user_level() {
	$user_level = array();
	$q = mysql_full('select * from conf_user_level order by level desc');
	if ($q) {
		while ($r = mysql_fetch_array($q)) {
			$user_level[$r['level']] = $r['name'];
		}
	}
	return $user_level;
}

function select_tab($name, $value) {
	global $user_level;

	if ($value == null) {
		$value = 10;
	}

	list($n, $t) = explode('_', $name);

	$attr = array('name' => $name, 'value' => $value, 'option' => $user_level,
				  'onChange' => 'location.href=\'edituser.php?target='. $t. '&level=\' + this.value;');
	return get_form('select', $attr);
}

?>
