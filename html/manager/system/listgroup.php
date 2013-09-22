<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require dirname(__FILE__). '/../../lib.php';

admin_check();

$act = isset($_REQUEST["act"]) ? $_REQUEST["act"] : '';

switch ($act) {
	case 'en':
		enable_group();
	break;
	case 'dis':
		disable_group();
	break;
	case 'del':
		del_group();
	break;
	default:
		;
}

print_list();

function enable_group() {
	$id   = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

	if ($id == 0) {
		return;
	}

	$d = mysql_exec('update page set enable = 1 where gid = %s',
					mysql_num($id));
					
	if (!$d) {
		show_error(mysql_error());
	}

	$msg  = '<div style="padding: 0 5px;">グループページを再開しました。</div>';
	$data = array('title'   => 'グループページ再開',
				  'content' => $msg. reload_form());

	show_dialog($data);
}

function disable_group() {
	$id   = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

	if ($id == 0) {
		return;
	}

	$d = mysql_exec('update page set enable = -1 where gid = %s',
					mysql_num($id));
					
	if (!$d) {
		show_error(mysql_error());
	}

	$msg  = '<div style="padding: 0 5px;">グループページを一時停止しました。</div>';
	$data = array('title'   => 'グループページ一時停止',
				  'content' => $msg. reload_form());

	show_dialog($data);
}

function del_group() {
	$id   = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
	$sure = isset($_REQUEST['sure']) ? true : false;

	if ($id == 0) {
		return;
	}
	if (!$sure) {
		conf_del($id);
		exit(0);
	}

	$d = mysql_exec('delete from page where gid = %s',
					mysql_num($id));

	$d = mysql_exec('delete from group_member where gid = %s',
					mysql_num($id));

	$d = mysql_exec('delete from unit where gid = %s',
					mysql_num($id));

	$msg  = '<div style="padding: 0 5px;">グループページを削除しました。</div>';
	$data = array('title'   => 'グループページ削除',
				  'content' => $msg. reload_form());

	show_dialog($data);
}

function conf_del($id = 0) {
	global $SYS_FORM;

	$id   = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
	if ($id == 0) {
		return;
	}

	$q = mysql_uniq('select * from page where gid = %s',
					mysql_num($id));

	$SYS_FORM['action'] = 'listgroup.php';
	$SYS_FORM['submit'] = 'グループページを削除する';
	$SYS_FORM['cancel'] = 'キャンセル';
	$SYS_FORM['onCancel'] = 'parent.tb_remove(); return false;';

	$SYS_FORM['head'][] = 'グループページ <strong>'. $q['sitename']. '</strong> を削除してもよろしいですか？';
	$SYS_FORM['head'][] = '<span style="color: #f00;">この操作は取り消しできません。本当によろしいですか？</span>';

	$SYS_FORM['input'][] = array('title' => '削除するグループページ',
								 'body'  => 'GID: '. $q['gid']. '<br>'.
											'サイト名: '. $q['sitename']. '<br>'.
											'登録日: '. date('Y-m-d H:i:s', tm2time($q['initymd'])));

	$SYS_FORM['input'][] = array('body' => get_form('hidden',
													array('name'  => 'act',
														  'value' => 'del')));
	$SYS_FORM['input'][] = array('body' => get_form('hidden',
													array('name'  => 'id',
														  'value' => $id)));
	$SYS_FORM['input'][] = array('body' => get_form('hidden',
													array('name'  => 'sure',
														  'value' => 1)));

	$data = array('title'   => 'グループページの削除',
				  'icon'    => 'warning',
				  'content' => create_confirm());

	show_dialog($data);
}

function print_list() {
	global $user_level;

	$q = mysql_full('select g.gid, g.id, g.sitename, count(g.gid) as count, g.enable, g.initymd, gm.uid'.
					' from page as g'.
					' left join group_member as gm on g.gid = gm.gid'.
					' where (gm.level = 100 or gm.uid is null) and g.gid > 0'.
					' group by g.gid'.
					' order by g.gid desc');
	
	$list = array();
	$list[] = array('dis'      => '',
					'gid'      => 'GID',
					'sitename' => 'サイト名',
					'admin'    => '管理者',
					'initymd'  => '登録日',
					'status'   => '状態<hr size="1">公開範囲',
					'del'      => '');

	$style = array('del'  => 'width: 60px;text-align: center;',
				   'dis'  => 'width: 60px;text-align: center;',
				   'gid'  => 'width: 60px;text-align: center;',
				   'initymd' => 'white-space: nowrap;',
				   'status'  => 'width: 60px;text-align: center;');

	if ($q) {
		while ($r = mysql_fetch_array($q)) {
			if($r['uid']){
				$handle = get_handle($r['uid']);
				if ($r['count'] > 1) {
					$handle .= ' 他 ' . ($r['count'] - 1). ' 名';
				}
			}else{
				$handle = '管理者不在';
			}

			$status = isset($r['enable']) ? intval($r['enable']) : 0;
			switch ($status) {
				case -1:
					$status_str = '<span style="color: #f00;">一時停止</span>';
					$dis_href = mkhref(array('s' => '[再開]', 'h' => 'listgroup.php?act=en&id='. $r['gid'], 'c' => 'thickbox'));
				break;
				case 1:
					$status_str = '<span style="color: #3747d5;">公開中</span>';
					$dis_href = mkhref(array('s' => '[停止]', 'h' => 'listgroup.php?act=dis&id='. $r['gid'], 'c' => 'thickbox'));
				break;
				default:
					$status_str = '<span style="color: #f00;">不明</span>';
					$dis_href = mkhref(array('s' => '[開始]', 'h' => 'listgroup.php?act=en&id='. $r['gid'], 'c' => 'thickbox'));
			}
			$pmt = get_pmt($r['id']);
			switch ($pmt) {
				case PMT_PUBLIC:
					$pmt_str = '<small>インターネット</small>';
				break;
				case PMT_MEMBER:
					$pmt_str = '登録ユーザー';
				break;
				case PMT_CLOSE:
					$pmt_str = '非公開';
				break;
				default:
					$pmt_str = '特定の人だけ';
			}

			$status_str .= '<hr size="1">'. $pmt_str;

			$list[] = array('dis'      => $dis_href,
							'gid'      => $r['gid'],
							'sitename' => make_href($r['sitename'], '/group.php?gid='. $r['gid'], false, '_blank'),
							'admin'    => $handle,
							'initymd'  => date('Y-m-d H:i:s', tm2time($r['initymd'])),
							'status'   => $status_str,
							'del'      => mkhref(array('s' => '[削除]', 'h' => 'listgroup.php?act=del&id='. $r['gid'], 'c' => 'thickbox')));
		}
	}

	$html .= create_list($list, $style);

	$data = array('title'   => 'グループページ一覧',
				  'content' => $html);

	show_input($data);

	exit(0);
}

?>
