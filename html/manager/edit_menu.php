<?php
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

//		$edit_menu[] = array(name => '前に戻る', href => 'javascript: history.back();');
		$sub_menu = array();
		$sub_menu[] = array(title => 'ユーザー管理', url => '/manager/system/listuser.php', 'class'=>"listuser");
		$sub_menu[] = array(title => 'ユーザーレベル設定', url => '/manager/system/edituser.php', 'class'=>"edituser");
		$sub_menu[] = array(title => 'ユーザーグループの編集', url => '/manager/system/addtogroup.php', 'class'=>"addtogroup");
		$sub_menu[] = array(title => 'グループ管理', url => '/manager/system/listgroup.php', 'class'=>"listgroup");

		$sub_menu[] = array(title => '利用規約編集', url => '/manager/site/agreement.php', 'class'=>"agreement");
		$sub_menu[] = array(title => 'ポータル設定', url => '/manager/system/portal.php', 'class'=>"portal");
		$sub_menu[] = array(title => 'ユーザー登録設定', url => '/manager/system/regist_setting.php', 'class'=>"regist_setting");
		$sub_menu[] = array(title => 'キーワード編集', url => '/manager/system/keyword.php', 'class'=>"keyword");
		$sub_menu[] = array(title => 'マイページ・グループページのテンプレート', url => '/manager/system/layout.php', 'class'=>"layout");
		$sub_menu[] = array(title => 'ファイル倉庫の設定', url => '/manager/system/filebox.php', 'class'=>"filebox");

		$sub_menu[] = array(title => 'IPブラックリスト', url => '/manager/system/blacklist_ip.php', 'class'=>"agreement");
		$sub_menu[] = array(title => 'CAPCHA設定', url => '/manager/system/captcha.php', 'class'=>"agreement");
		$sub_menu[] = array(title => 'NGワード', url => '/manager/system/ng_word.php', 'class'=>"agreement");


		$edit_menu[] = array(name => 'システム関連', sub => $sub_menu);
		$sub_menu = array();
//		$sub_menu[] = array(title => 'スキンの登録', url => '/manager/skin/input.php?action=new');
//		$sub_menu[] = array(title => 'スキンの作成', url => '/manager/skin/create.php');
//		$sub_menu[] = array(title => 'スキンの編集', url => '/manager/skin/input.php');
//		$sub_menu[] = array(title => 'スキンの表示設定', url => '/manager/skin/pmt.php');
//		$sub_menu[] = array(title => 'スキンの削除', url => '/manager/skin/delete.php');
		$sub_menu[] = array(title => 'スキンの管理', url => '/manager/skin/list.php', 'class'=>"skin_list");
		$sub_menu[] = array(title => '共通CSSの編集', url => '/manager/css/input.php', 'class'=>"css_input");
		$edit_menu[] = array(name => 'スキン関連', sub => $sub_menu);
		$sub_menu = array();
//		$sub_menu[] = array(title => 'パーツの登録', url => '/manager/parts/input.php?action=new');
//		$sub_menu[] = array(title => 'パーツの編集', url => '/manager/parts/input.php');
//		$sub_menu[] = array(title => 'パーツの表示設定', url => '/manager/parts/pmt.php');
//		$sub_menu[] = array(title => 'パーツの削除', url => '/manager/parts/delete.php');
		$sub_menu[] = array(title => 'パーツの管理', url => '/manager/parts/list.php', 'class'=>"parts");
		$edit_menu[] = array(name => 'パーツ関連', sub => $sub_menu);
		$sub_menu = array();
		$sub_menu[] = array(title => '設定', url => '/manager/css/menubar.php?action=select', 'class'=>"menubar_select");
		$sub_menu[] = array(title => '追加', url => '/manager/css/menubar.php?action=entry', 'class'=>"menubar_entry");
		$sub_menu[] = array(title => '編集', url => '/manager/css/menubar.php?action=edit', 'class'=>"menubar_edit");
		$edit_menu[] = array(name => 'メニューバー関連', sub => $sub_menu);
		$sub_menu = array();
		$sub_menu[] = array(title => 'システム初期設定', url => '/manager/install/setup.php?action=select', 'class'=>"install");
		$edit_menu[] = array(name => 'システム初期設定', sub => $sub_menu);
		$sub_menu = array();
		$sub_menu[] = array(title => 'システムのアップグレード', url => '/manager/upgrade/upgrade.php', 'class'=>"upgrade");
		$edit_menu[] = array(name => 'アップグレード', sub => $sub_menu);
		
		//モジュールのメニューを追加
		$results = ModuleManager::getInstance()
					->execCallbackFunctions( "editmenu", array() );

		foreach ( $results as $r ) { $edit_menu[] = $r; }

?>