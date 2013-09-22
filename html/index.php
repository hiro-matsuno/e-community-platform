<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require dirname(__FILE__). '/lib.php';

/*
if (!portal_gid()) {
	show_error('先に管理者登録を行ってください。');
}
*/

global $SYS_REFRESH;

$eid = isset($_GET["eid"]) ? intval($_GET["eid"]) : null;
$uid = isset($_GET["uid"]) ? intval($_GET["uid"]) : null;
$gid = isset($_GET["gid"]) ? intval($_GET["gid"]) : portal_gid();
$blk_id = isset($_GET["blk_id"]) ? intval($_GET["blk_id"]) : null;
$site_id = isset($_GET["site_id"]) ? intval($_GET["site_id"]) : null;
$module_main = isset($_GET["module"])? $_GET["module"] : null;

if($blk_id and !$module_main){
	$b = mysql_uniq('select module from block where id = %s',mysql_num($blk_id));
	$module_main = $b['module'];
}

if ($uid) {
	$gid = null;

	$SYS_VIEW_GID = 0;
}

if($blk_id){
	$uid = get_uid($blk_id);
	$gid = get_gid($blk_id);

	if(!$eid)$eid = $blk_id;

	$SYS_VIEW_UID = $uid;
	$SYS_VIEW_GID = $gid;
}elseif ($eid) {
	$uid = get_uid($eid);
	$gid = get_gid($eid);

	$SYS_VIEW_UID = $uid;
	$SYS_VIEW_GID = $gid;
}

if($site_id){
	$_SESSION["return"]  = '/index.php?site_id='. $gid;
	
	$eid = $site_id;

	$p = mysql_uniq("select * from page where id = %s;", mysql_num($site_id));
	if(!$p)error_window('指定されたページが見つかりません');
	if($p['enable']<0)error_window('指定されたページは現在公開停止されています。');
	if($p['gid']>0)$SYS_VIEW_GID = $p['gid'];
	else $SYS_VIEW_UID = $p['uid'];
}elseif ($gid > 0) {
	if (is_portal($gid)) {
		$_SESSION["return"]  = '/index.php';
	}
	else {
		$_SESSION["return"]  = '/group.php?gid='. $gid;
	}

	$SYS_VIEW_GID = $gid;

	$p = mysql_uniq("select * from page where gid = %s;", mysql_num($gid));
	if (!$p) {
		error_window('グループが見つかりません。');
	}
	if($p['enable']<0)error_window('指定されたページは現在公開停止されています。');
	if (!$eid) {
		if($blk_id)$eid = $blk_id;
		else $eid = $p["id"];
	}
	$site_id = $p["id"];
}
else {
	$i = mysql_uniq('select enable from user where id = %s', mysql_num($uid));
	if ($i) {
		if ($i['enable'] < 1) {
			error_window('現在このユーザーは停止中です。');
		}
	}

	$_SESSION["return"]  = '/user.php?uid='. $uid;
	$_SESSION["toppage"] = '/user.php?uid='. $uid;

	$p = mysql_uniq("select * from page where uid = %s;", mysql_num($uid));
	if (!$p) {
		error_window('マイページが見つかりません。');
	}
	if($p['enable']<0)error_window('指定されたページは現在公開停止されています。');
	if (!$eid) {
		$eid = $p["id"];
	}
	$site_id = $p["id"];

	$SYS_VIEW_UID = $uid;
}

if (is_owner($eid)) {
	$COMUNI["is_owner"] = true;
	$SYS_REFRESH = isset($_GET["refresh"]) ? true : false;
}
else if (!check_pmt($site_id)) {
	error_window('このページを閲覧するための権限が与えられていません.');
}
else {
	if (!check_pmt($eid) && !is_reporter_admin($eid) && !is_bosai_web_admin($eid)) {
		error_window('このページを閲覧するための権限が与えられていません。');
	}
}

//あしあとを記録
//access_log();

/* モジュールのロード */
$module = get_modules($eid);

//	モジュールの init 関数を呼ぶ.
ModuleManager::getInstance()->execCallbackFunctions( "init" );

/* パーツのロード */
$l = mysql_exec("select block.*, element.unit from block".
				" inner join element on block.id = element.id".
				" left join unit on element.unit = unit.id".
				" left join owner on block.id = owner.id".
				" where block.pid = %s".
				" and (element.unit <= %s or unit.uid = %s or owner.uid = %s)".
				" group by block.id".
				" order by block.hpos",
				mysql_num($site_id),
				mysql_num(public_status($site_id)),
				mysql_num(myuid()),
				mysql_num(myuid()));

				
$data = array();

//	info_html のパーツを追加する。
InfoHtmlGetHtml( $data );

global $COMUNI_HEAD_CSSRAW;
if ( isset( $data['css'] ) and is_array($data['css']) ) {
foreach ( $data['css'] as $css ) {
	$COMUNI_HEAD_CSSRAW[] = $css;
}
}

/* モジュールの指定があった場合space_1へ */
if ($module_main) {
	$COMUNI["use_map"] = false;

	$mod_name = htmlspecialchars($module_main, ENT_QUOTES);

	$block_array = array();
//	if ($module[$mod_name]) {

		unset($data['space_1']);

		$blockData = array();

		$blockData["id"] = $eid;
		
		if($blk_id)
			$blockData["title"] = get_block_name($blk_id);
		else
			$blockData["title"] = get_module_name($module_main);

		$data["title"] = $blockData["title"];

		try {

			$content = null;

			if ( !ModuleManager::getInstance()->getModule( $mod_name )
					->execCallbackFunction( "main", array( $eid ), $content ) ) {
				
				throw new NoSuchFunctionException();

			}

			if ( is_array( $content ) ) {

				//	mod_*_main の戻り値が完成されたオブジェクトで返される可能性があるらしい.
				$blockData = $content;

			} else {

				$blockData["content"] =  $content.'<div align="right">'.
							make_href(get_site_name($site_id).'へ戻る',home_url($site_id)).'</div>';

				if ( is_owner($eid) ) {

					$edit_menu = array();

					ModuleManager::getInstance()->getModule( $mod_name )
							->execCallbackFunction( "main_config", array( $eid ), $edit_menu );

					$blockData["editlink"] = main_edit_menu($id, $edit_menu);

				}

			}

		} catch ( Exception $e ) {

			$blockData["content"] = "<div class=\"ecom_block_error_message\">"
								."エラーが発生しました."
								.EcomUtil::debugString( "<div>{$e->__toString()}</div>" )
								."</div>";

		}

		$data['space_1'][] = $blockData;

	//}

} else {
	$COMUNI['is_top'] = true;
}
/* パーツのロード */
if (mysql_num_rows($l) > 0) {
	while($row = mysql_fetch_array($l)) {
		if (isset($SYS_HIDDEN_BLOCK[$row['id']])) {
			continue;
		}
		if ($module_main) {
			if ($row["vpos"] == 1 || $row["vpos"] > 3) {
				continue;
			}
		}

		if (!check_pmt($row['id'])) {
			continue;
		}

		if ($blk_id == $row['id']) {
			continue;
		}
		
		$block_array = array();

		$block_array['id']    = $row["id"];
		$block_array["title"] = get_block_name($row["id"]);

//		if ($module[$row["module"]]) {
		try {

			if ( !ModuleManager::getInstance()->getModule( $row["module"] )
					->execCallbackFunction( "block", array($row["id"]), $content ) ) {

				throw new NoSuchFunctionException();
				
			}

			if (is_array($content)) {

				//	mod_*_block で完全なオブジェクトが返される可能性があるらしい.
				$block_array = $content;
				$block_array["title"] = get_block_name($row["id"]);

			} else {

				$block_array["content"] = $content;

				if (is_owner($row["id"])) {

					$edit_menu = array();

					ModuleManager::getInstance()->getModule( $row["module"] )
						->execCallbackFunction( "block_config", array($row["id"]), $edit_menu );

					$block_array["editlink"] = block_edit_menu($row["id"], $edit_menu, $row["unit"]);
						
				}

			}
				
		} catch ( Exception $e ) {

			$block_array["content"] = "<div class=\"ecom_block_error_message\">"
									."エラーが発生しました."
									.EcomUtil::debugString( "<div>{$e->__toString()}</div>" )
									."</div>";
			$block_array["editlink"] = block_edit_menu($row["id"], array(), $row["unit"]);
			
		}

		if (is_layoutmode()) {
			$block_array["editlink"] = '';
			$block_array["content"]  = '<div style="text-align: center; padding: 15px 3px 5px 3px; font-size: 0.8em; color: #999999">'.
									  'タイトルをドラッグで移動できます。</div>';
		}

		$data['space_'. $row["vpos"]][] = $block_array;
		unset($block_data);

	}
	
}

if (is_layoutmode()) {
	$COMUNI["use_map"] = false;
}else{
        $COMUNI_HEAD_JSRAW[]=<<<SCRIPT
function update_block_content(id){
        jQuery(function(){
            jQuery.ajax({
                url: '/block_data.php?blk_id='+id,
                type: 'GET',
                dataType: 'xml',
                timeout: 10000,
                error: function(){
                    alert("表示データの更新に失敗しました。\\n作業終了後リロードしてください。");
                },
                success: function(xml){
                    if(jQuery(xml).find("error").text() == 0){
                        jQuery('div.box_main','#box_'+id).html(jQuery(xml).find("data").text());
                        }
                }
            });
        });
}
SCRIPT;

}

show_page($site_id, $data);

exit(0);

//あしあとを記録する
//function access_log() {
//	global $CURRENT_SITE_ID;
//
//	$site_id = isset($CURRENT_SITE_ID) ? $CURRENT_SITE_ID : 0;
//	$uri     = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
//
//	if (is_logging()) {
//		$i = mysql_exec('insert into access_log_site (site_id, uid, uri) values (%s, %s, %s)',
//						mysql_num($site_id), mysql_num(myuid()), mysql_str($uri));
//	}
//}

?>
