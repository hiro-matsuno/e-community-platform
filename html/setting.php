<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
 
require dirname(__FILE__). '/lib.php';

$myuid   = myuid();

if ( 0 == $myuid ) { show_error("ログイン状態ではないようです"); }

$arg = array();

$arg['gid'] = ( isset( $_GET['gid'] ) ) ? intval($_GET['gid']) : 0;
$arg['uid'] = ( isset( $_GET['uid'] ) ) ? intval($_GET['uid']) : 0;

if ($arg['gid'] > 0) {
	$query[] = 'gid='. $arg['gid'];
	$eid = get_eid(array(gid => $arg['gid']));
}
else {
	$query[] = 'uid='. $arg['uid'];
	$eid = get_eid(array('uid' => $arg['uid']));
}

$raw_query = implode('&amp;', $query);

$content = <<<__HTML__
<div class="setting_menu">
<h3>個人設定</h3>
<ul>
<li><a href="/profile.php" target="_parent" class="icon profile">プロフィールの設定</a></li>
<li><a href="/mbox.php" class="icon mbox">メッセージボックス</a></li>
<li><a href="/mail_setting.php" class="icon mail_setting">メール転送設定</a></li>
<!-- <li><a href="/log_setting.php" class="icon log_setting">あしあとの設定</a></li> -->

<li><a href="/passwd_change.php" class="icon passwd_change">ログインパスワードの変更</a></li>
<li><a href="/mail_change.php" class="icon mail_change">メールアドレスの変更</a></li>

<!-- <li><a href="/modules/favorite/input.php?uid=${myuid}" target="_parent" class="icon favorite">お気に入り/購読の設定</a></li>-->
<li><a href="/friend.php?uid=${myuid}" class="icon friend">フレンドリスト</a></li>
<li><a href="/filebox.php?uid=${myuid}" class="icon filebox">ファイル倉庫</a></li>
</ul>
<h3 style="padding-top:8px;">ページ作成</h3>
<ul>
<li><a href="/manager/site/select.php" target="_parent" class="icon site_select">マイページ・グループページを作る</a></li>
</ul>
__HTML__;

if (is_master(array('gid' => $arg['gid'], 'uid' => $arg['uid'])) == true and ($arg['gid'] or $arg['uid'])) {
	if ($arg['gid'] > 0) {
		$add_menu = '<li><a href="/group.php?refresh=on&gid='. $arg['gid']. '" target="_parent" class="icon reload">ページを再読込</a></li>';

		$add_menu .= '<li><a href="/manager/site/group_profile.php?action=edit&gid='. $arg['gid']. '" target="_parent" class="icon group_profile">グループページの基本設定</a></li>';

		$add_menu .= '<li><a href="/modules/antispam/setting_group.php?gid='. $arg['gid']. '" class="icon friend">スパム対策設定</a></li>';

		$add_menu .= '<li><a href="/manager/site/delete_gpage.php?gid='. $arg['gid']. '" class="icon delete_gpage">グループページの削除</a></li>';
	}
	else {
		$add_menu = '<li><a href="/user.php?refresh=on&uid='. $arg['uid']. '" target="_parent" class="icon reload">ページを再読込</a></li>';

		$add_menu .= '<li><a href="/manager/site/mypage_profile.php?action=edit&uid='. $arg['uid']. '" target="_parent" class="icon mypage_profile">マイページの基本設定</a></li>';

		$add_menu .= '<li><a href="/modules/antispam/setting_user.php" class="icon friend">スパム対策設定</a></li>';

		$add_menu .= '<li><a href="/manager/site/delete_mypage.php?uid='. $arg['uid']. '" class="icon delete_mypage">マイページの削除</a></li>';
	}

	$add_menu .= '<li><a href="/mail_noti_comment.php?eid='. $eid. '" class="icon mail_noti_list">コメント・トラックバック通知</a></li>';

	$content .= <<<__HTML__
<h3 style="padding-top:8px;">このサイトで可能な設定</h3>
<ul>
<li><a href="/add_block.php?eid=${eid}" class="icon add_block">パーツの追加</a></li>
<li><a href="/skin.php?${raw_query}" target="_parent" class="icon skin">スキン変更</a></li>
${add_menu}
</ul>
__HTML__;
	;
}

$content .= <<<__HTML__
<h3 style="padding-top:8px;">メールの通知</h3>
<ul>
<li><a href="/mail_noti.php?eid=${eid}" class="icon mail_noti">このページをメール通知に加える</a></li>
<li><a href="/mail_noti_list.php" class="icon mail_noti_list">メール通知一覧</a></li>
</ul>
__HTML__;

$menuArray = ModuleManager::getInstance()->execCallbackFunctions( "user_config" );

if ( 0 < count( $menuArray ) ) {

	$content .= "<h3 style=\"padding-top:8px;\">その他の設定</h3>"
				."<ul>";

	foreach ( $menuArray as $menus ) {

		foreach ( $menus as $menu ) {

			$menu = (Object)$menu;

			$href = Path::makeURL( $menu->url );
			$classes = "";
			foreach ( $menu->classes as $class ) {
				$classes .= "$class ";
			}

			$content .= "<li><a href=\"$href\" class=\"$classes\" >{$menu->title}</a>";

		}

	}

	$content .= "</ul>";

}

if (is_admin()) {

	$content .= <<<__HTML__
<br/>
<h3>システム管理者向け</h3>
<ul>
<li><a href="/manager/system/default.php" target="_parent"  class="icon system_default">管理設定へ</a></li>
</ul>
__HTML__;
/*<ul>
<li><a href="/manager/system/listuser.php" target="_parent">ユーザーの管理</a></li>
<li><a href="/manager/system/edituser.php" target="_parent">ユーザーレベル設定</a></li>
<li><a href="/manager/system/listgroup.php" target="_parent">グループの管理</a></li>

<li><a href="/manager/system/keyword.php" target="_parent">キーワード編集</a></li>

<li><a href="/manager/site/agreement.php" target="_parent">利用規約の編集</a></li>
<li><a href="/manager/system/portal.php" target="_parent">ポータルの設定</a></li>
<li><a href="/manager/system/regist_setting.php" target="_parent">ユーザー登録方法の設定</a></li>

<li><a href="/manager/system/layout.php" target="_parent">マイページ・グループページのテンプレート</a></li>
<!--
<li><a href="/manager/skin/input.php?action=new" target="_parent">スキンの登録</a></li>
<li><a href="/manager/skin/create.php" target="_parent">スキンの作成</a></li>
<li><a href="/manager/skin/input.php" target="_parent">スキンの編集</a></li>
<li><a href="/manager/skin/delete.php" target="_parent">スキンの削除</a></li>
<li><a href="/manager/skin/pmt.php" target="_parent">スキンの表示設定</a></li>
-->
<li><a href="/manager/skin/list.php" target="_parent">スキンの管理</a></li>
<!--
<li><a href="/manager/parts/input.php?action=new" target="_parent">パーツの追加</a></li>
<li><a href="/manager/parts/input.php" target="_parent">パーツの設定</a></li>
<li><a href="/manager/parts/pmt.php" target="_parent">パーツの表示設定</a></li>
<li><a href="/manager/parts/delete.php" target="_parent">パーツの削除</a></li>
-->
<li><a href="/manager/parts/list.php" target="_parent">パーツの管理</a></li>
<li><a href="/manager/css/menubar.php" target="_parent">メニューバーの管理</a></li>
<li><a href="/manager/install/setup.php" target="_parent">システム初期設定</a></li>
</ul>
__HTML__;*/
	;
}


$content .= "</div>";

$data = array('title'   => '設定',
			  'content' => $content);

show_dialog2($data);

exit(0);

?>
