<?php 
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
?>
<?php

require dirname(__FILE__).'/../../lib.php';

su_check();

define ( "ADD_TO_GROUP_OK", 0 );
define ( "ADD_TO_GROUP_FATAL", -1 );
define ( "ADD_TO_GROUP_DENIED", -2 );

$result = (Object)array();
$result->code = ADD_TO_GROUP_OK;
$result->message = "";

try {

	$me = User::getMe();

	if ( !$me or !$me->isAdmin() ) {
		throw new PermissionDeniedException();
	}

	switch ( $_REQUEST["act"] ) {

		case "regist":
			registData();
			$result->message = "変更されました.";
			break;

		case "view":
		default:
			break;

	}

} catch ( PermissionDeniedException $e ) {

	$result->code = ADD_TO_GROUP_DENIED;
	$result->message = "その操作を行う権限がありません.";

} catch ( Exception $e ) {

	$result->code = ADD_TO_GROUP_FATAL;
	$result->message = $e->getMessage();

}

view( $result );


function registData() {

	$gid = ( isset( $_REQUEST["gid"] ) ? $_REQUEST["gid"] : null );
	$setting = ( $_REQUEST["setting"] ? stripslashes( $_REQUEST["setting"] ) : null );

	if ( null === $gid ) {
		throw new InvalidArgumentException();
   	}

	if ( null === $setting ) { return; }

	if ( false === FormBuildId::checkFormBuildId() ) {
		throw new PermissionDeniedException();
	}

	$settingArray = json_decode( $setting );

	foreach ( $settingArray as $obj ) {

		$uid = (int)$obj->uid;
		$level = (int)$obj->level;

		$group = new Group( $gid );

		if ( 0 < $level ) {

			$group->addVisitor( $uid );
			$group->addMember( $uid, $level );

		} else {

			$group->removeVisitor( $uid );
			$group->removeMember( $uid );

		}

	}

}

function view( $result=null ) {

	$gid = ( isset( $_REQUEST["gid"] ) ? (int)$_REQUEST["gid"] : null );

	$pages = Page::getPages( Page::PAGE_GROUP_PAGE );

	if ( 0 === count( $pages ) ) { throw new Exception( "グループがひとつもありません." ); }

	if ( !$gid ) { $gid = $pages[0]->getGid(); }

	$group = new Group( $gid );


	$html = '';

	$page = $group->getPage();

	if ( $result->message ) {

		$errorMessage = ( 0 === $result->code ? "false" : "true" );

		$html .= <<<__JS_CODE__
		<script type="text/javascript">
		//<!--
			showMessage( "{$result->message}", $errorMessage );
		//-->
		</script>
__JS_CODE__;

	}


	$html .= "<form id=\"change_group\" action=\"addtogroup.php\" method=\"GET\""
			."style=\"padding: 4px;\">";

	$html .= "<i style=\"font-size: 0.8em; color: darkgray\">グループ名称：</i>";
	$html .= "<select name=\"gid\" style=\"margin: 4px;\">";

	foreach ( $pages as $p ) {

		$selected = ( $gid === $p->getGid() ? "selected=\"true\"" : "" );
		$html .= "<option value=\"{$p->getGid()}\" $selected>{$p->getSitename()}</option>";

	}

	$html .= "</select>";

	$html .= "<input type=\"submit\" value=\"移動\" style=\"display: none; margin: 4px;\" />";

	$html .= "</form>";

	$html .= "<div style=\"padding: 4px; font-size: 0.8em\">"
			."<a href=\"/group.php?gid=".$gid."\">グループページに移動</a>"
			."</div>";

	$html .= makeUserList($group);

	$html .= "<form id=\"regist_form\" method=\"POST\" style=\"padding: 4px; text-align: left;\">";

	$formBuildId = FormBuildId::getFormBuildId();
	$html .= '<input type="hidden" name="'.FormBuildId::PARAM_NAME.'" value="'.$formBuildId.'" />';
	$html .= "<input type=\"hidden\" name=\"act\" value=\"regist\" />";
	$html .= "<input type=\"hidden\" name=\"gid\" value=\"$gid\" />";

	$html .= "<input type=\"submit\" name=\"regist\" value=\"登録\" style=\"margin: 4px;\" />";
	$html .= "<input type=\"button\" name=\"cancel\" value=\"戻る\" style=\"margin: 4px;\" />";

	$html .= "</form>";

	$data = array('title'   => 'グループ「'.$page->getSitename().'」参加ユーザの設定',
				  'icon'    => 'write',
				  'content' => "<div id=\"addtogroup_main\">$html</div>" );

	global $COMUNI;
	$COMUNI['manager_mode'] = true;

	EcomGlobal::addHeadJs( "/js/json2.js" );
	EcomGlobal::addHeadJs( "addtogroup.js" );
	EcomGlobal::addJqueryReady( "new AddToGroupBuilder();" );

	show_input($data);

}

function makeUserList( $group ) {

	$users = User::getUsers();

	$list = array();
	$list[] = array('uid'     => 'UID',
					'handle'  => 'ニックネーム',
					'level'     => 'レベル');

	$style = array('level'  => 'width: 60px;text-align: center;',
				   'uid'  => 'width: 60px;text-align: center;');

	foreach ( $users as $user ) {

		$group_level = $group->getUserLevel( $user );

		$option = array( Permission::USER_LEVEL_ADMIN => "グループ管理者",
						Permission::USER_LEVEL_POWERED => "グループ副管理者",
						Permission::USER_LEVEL_EDITOR => "編集者",
	//					Permission::USER_LEVEL_DELETER => "デリーター",
						Permission::USER_LEVEL_AUTHORIZED => "一般利用者",
						0 => "未参加");

		$listbox = get_form("select",
						   array(name => "group_level",
								 id  => "group_level_".$user->getUid(),
								 value => $group_level,
								 option => $option ));

		$list[] = array( 'uid'     => "<div class=\"uid\" uid=\"".$user->getUid()."\">"
									.$user->getUid()."</div>",
						'handle'  => "<div class=\"handle\">".$user->getHandle()."</div>",
						'level' => $listbox );

	}

	return "<div id=\"user_list\">".create_list( $list, $style )."</div>";

}

?>
