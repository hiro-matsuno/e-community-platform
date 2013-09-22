<?php
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require_once dirname(__FILE__). '/../../lib.php';
require_once dirname(__FILE__). '/../../regist_lib.php';

$gid = intval($_REQUEST['gid']);
if(!$gid)show_error('グループが指定されていません');
if (join_level($gid) < 80) {
	show_error('グループ副管理者以上が行える操作です。');
}

$uid = intval($_REQUEST['uid']);

$data = regist_data_get_reqdata($uid,$gid);

$show_data = "<table class='edit_table group_user_description'>\n";
if(get_eid_by_mypage($uid)){
	$link = make_href(get_site_name(get_eid_by_mypage($uid)),CONF_SITEURL."/index.php?uid=$uid",false,'_brank');
	$show_data .= "<tr><th>マイページ</th><td>$link</td></tr>\n";
}
foreach($data as $d){
	$title = htmlspecialchars($d['title']);
	$dd = $d['data'];
	if(is_array($dd))$dd = implode("\n",$dd);
	$dd = str_replace("\n",'<br>',htmlspecialchars($dd));
	$show_data .= "<tr><th>$title</th><td>$dd</td></tr>\n";
}
$show_data .= "</table>\n";

//システム管理者は情報を編集できるように

$gname = get_gname($gid);
$nickname = get_nickname($uid);
$b = mysql_uniq('select block.id,gid from block'.
							' inner join owner on owner.id=block.id'.
							' where module="glist" and gid=%s',mysql_num($gid));
$blk_id = $b['id']; 
$content = "${gname}への${nickname}(UID:${uid})の登録情報です\n";
$content .= $show_data;
$content .= make_href("参加承認ページへ戻る",'member.php?eid='.$blk_id);

$data = array(title   => 'ユーザー登録情報確認',
			  icon    => 'default',
			  content => $content);

$COMUNI_HEAD_CSSRAW[] = <<<__CSS__
table.group_user_description th{
	width: 30%;
}
__CSS__;

show_dialog2($data);

?>
