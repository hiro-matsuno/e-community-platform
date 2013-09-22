<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require dirname(__FILE__). '/lib.php';

$act = isset($_REQUEST['action']) ? $_REQUEST['action'] : null;

switch ($act) {
	case 'del':
		noti_delete();
	break;
	default:
		print_form();
}

exit(0);

function noti_delete() {
	$target = isset($_POST['target']) ? $_POST['target'] : array();

	if (count($target) == 0) {
		$content = '選択して下さい。';
	}
	else {
		foreach ($target as $t) {
			$d = mysql_exec('delete from mail_noti where eid = %s and uid = %s',
							mysql_num($t), mysql_num(myuid()));
		}

		$content = '更新通知設定を削除しました。';
	}

	$content .= '<div style="padding: 3px; font-size: 12px;"><a href="./mail_noti_list.php">戻る</a></div>';

	$data = array('title'   => '更新通知設定削除',
				  'icon'    => 'mail',
				  'content' => $content);

	show_dialog($data);
}

function print_form() {

	$q = mysql_full('select m.eid, p.gid, p.sitename from page as p'.
					' inner join mail_noti as m on p.id = m.eid'.
					' where m.uid = %s and p.gid > 0'.
					' order by m.id desc',
					mysql_num(myuid()));

	$glist = array();
	$glist[] = array('del'     => '',
					 'sitename' => '設定済みグループページ');
	if ($q) {
		while ($r = mysql_fetch_array($q)) {
			$sitename = '<a href="/group.php?gid='. $r['gid']. '" target="_blank">'. $r['sitename']. '</a>';
			$glist[] = array('del'     => '<input type="checkbox" name="target[]" value="'. $r['eid']. '">',
							 'sitename' => $sitename);
		}
	}

	$q = mysql_full('select m.eid, m.uid, p.sitename from page as p'.
					' inner join mail_noti as m on p.id = m.eid'.
					' where m.uid = %s and p.gid = 0'.
					' order by m.id desc',
					mysql_num(myuid()));

	$mlist = array();
	$mlist[] = array('del'     => '',
					 'sitename' => '設定済みマイページ');
	if ($q) {
		while ($r = mysql_fetch_array($q)) {
			$sitename = '<a href="/user.php?uid='. $r['uid']. '" target="_blank">'. $r['sitename']. '</a>';
			$mlist[] = array('del'     => '<input type="checkbox" name="target[]" value="'. $r['eid']. '">',
							 'sitename' => $sitename);
		}
	}


	$style = array('del'     => 'width: 30px;',
				   'subject' => 'width: 60%;');

	$content .= '<form action="mail_noti_list.php" method="POST"><input type="hidden" name="action" value="del">';

	$content .= '<div style="text-align: right; float: right; width: 40%;">'.
				'<input type="submit" value="チェックした項目の削除" style="background: #f0f0f0; border: solid 1px #999; margin: 2px;"></div>';
	$content .= '<br clear="all">';
	$content .= create_list($glist, $style);
	$content .= '<br clear="all">';
	$content .= create_list($mlist, $style);

//	$content .= '<div style="padding: 3px; font-size: 12px;"><a href="javascript: history.back()">戻る</a></div>';
	$content .= '</form>';

	$data = array('title'   => 'メール通知一覧',
				  'icon'    => 'mail',
				  'content' => $content);

	show_dialog($data);

	exit(0);
}

?>
