<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

// block config
// $id: block element id
function mod_blog_block_config($id = 0) {
	$menu   = array();

	if(is_owner($id,80)){
		$menu[] = array(title => '基本設定',
						url => '/modules/blog/setting.php?pid='. $id,
						inline => false);
	}

	$menu[] = array(title => '新規投稿',
					url => '/modules/blog/input.php?pid='. $id,
					inline => false);

	$menu[] = array(title => '記事管理',
					url => '/modules/blog/edit.php?pid='. $id,
					inline => false);

	$menu[] = array(title => '携帯アドレス発行',
//					url => '/mobile/issue.php?eid='. $id,
					url => '/mobile/setting.php?uid='. myuid(). '&gid='. get_gid($id),
					inline => true);

	return $menu;
}

// main config
// $id: element id
function mod_blog_main_config($id = 0) {
	$eid = isset($_REQUEST['eid']) ? intval($_REQUEST['eid']) : 0;
	$pid = isset($_REQUEST['pid']) ? intval($_REQUEST['pid']) : 0;

	$menu   = array();
	$p = mysql_uniq("select * from blog_data where id = %s",
					mysql_num($id));
	$pid = $p['pid'];
	
	if ($eid > 0) {
		$menu[] = array(title => 'この記事を編集',
						url => '/modules/blog/input.php?eid='. $eid,
						inline => false);

		$menu[] = array(title => '記事管理',
						url => '/modules/blog/edit.php?pid='. $pid,
						inline => false);

		$menu[] = array('title'  => '削除',
						'url'    => '/del_content.php?module=blog&eid='. $eid,
						'inline' => true);
	}
	else {
		if(is_owner($id,80)){
			$menu[] = array(title => '基本設定',
							url => '/modules/blog/setting.php?pid='. $id,
							inline => false);
		}
		$menu[] = array(title => '新規投稿',
						url => '/modules/blog/input.php?pid='. $id,
						inline => false);

		$menu[] = array(title => '記事管理',
						url => '/modules/blog/edit.php?pid='. $id,
						inline => false);

		$menu[] = array(title => '携帯アドレス発行',
	//					url => '/mobile/issue.php?eid='. $id,
						url => '/mobile/setting.php?uid='. myuid(). '&gid='. get_gid($id),
						inline => true);
	}
	return $menu;
}

function stripTagsIfForhidden( $str ) {

	return preg_replace_callback( '/<\s*([^>\s]+)[^>]*src="([^>"]*)"[^>]*>/',
								'stripTagsIfForhiddenCallback',
								$str );

}

function stripTagsIfForhiddenCallback( $m ) {

	$str = $m[0];
	$tag = $m[1];
	$srcURI = $m[2];

	if ( preg_match( '/^\/fbox.php.*eid=(\d+)/', $srcURI, $match ) ) {

		try {

			$eid = $match[1];

			$fileData = new FileboxData( (int)$eid );

			if ( $fileData->isVisible( User::getMe() ) ) {
				return $str;
			}

		} catch ( Exception $e ) {}

		return "";

	} else {

		return $str;

	}

}
?>
