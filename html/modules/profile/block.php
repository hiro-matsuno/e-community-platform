<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require_once dirname(__FILE__). '/config.php';
require_once dirname(__FILE__). '/common.php';

function mod_profile_block($id = null) {
	global $SYS_VIEW_GID, $SYS_VIEW_UID;
	global $SYS_PROFILE;

	$html    = '';
	$columns = array();

	if ($SYS_VIEW_GID > 0) {
		$pd = mysql_uniq("select * from profile_data where gid = %s",
						mysql_num($SYS_VIEW_GID));
		$columns = $SYS_PROFILE['group'];
	}
	else {
		$pd = mysql_uniq("select * from profile_data where uid = %s",
						mysql_num($SYS_VIEW_UID));
		$columns = $SYS_PROFILE['user'];
	}

	if (isset($pd)) {
		$pp = mysql_uniq("select * from profile_pmt where id = %s",
						mysql_num($pd['id']));				
	}
	else {
		$html = mod_profile_context($SYS_VIEW_UID, $SYS_VIEW_GID);

		if (is_owner($id)) {
			return $html. 'プロフィールが未登録です。';
		}
		else {
			return $html;
		}
	}
	if (is_array($pd)) {
	foreach ($pd as $key => $val) {
		if (isset($columns[$key])) {
			if ($pp[$key] > public_status($id)) {
				continue;
			}
			if($SYS_PROFILE['target'][$key] == 'timestamp')$val = strtotime($val);
			$html .= mod_profile_mkbody($key, $columns[$key], $val);
		}
	}
	}

	if ($SYS_VIEW_GID > 0) {
		$html .= mod_profile_context($SYS_VIEW_UID, $SYS_VIEW_GID);
	}
	else {
		$html = mod_profile_context($SYS_VIEW_UID, $SYS_VIEW_GID). $html;
	}

	return $html;
}

function mod_profile_mkbody($key = null, $title = null, $data = null) {
	if (!$data) {
		return '';
	}

	$title = '<div class="profile_title">'. $title. '</div>';
	$class = 'profile_body';
	switch ($key) {
		case 'thumb':
			$title   = '';
			$content = '<img src="/databox/profile/b/'. $data. '" border="0">';
			$class   = 'profile_icon';
			break;
		case 'sex':
			$option = array(1 => '男性', 2 => '女性', 3 => '秘密');
			$content = $option[$data];
			break;
		case 'birthday':
			$content = date('Y年m月d日', $data);
			break;
		case 'blood':
			$option = array(1 => 'A型', 2 => 'B型', 3 => 'O型', 4 => 'AB型', 5 => '?');
			$content = $option[$data];
			break;
		case 'name':
		case 'address':
		case 'birthplace':
		case 'hobby':
		case 'job':
 		case 'profile':
		case 'fav1':
		case 'fav2':
		case 'fav3':
		default:
			$content = $data;
	}

	$body = '<div class="'. $class. '">'. $content. '</div>';

	return $title. $body;
}

function mod_profile_context($uid = 0, $gid = 0) {
	global $JQUERY, $COMUNI_FOOT_HTML;

	if ($gid > 0) {
		$q = mysql_uniq('select owner.uid from page'.
						' inner join owner on page.id = owner.id'.
						' where page.gid = %s',
						mysql_num($gid));
		if ($q) {
			$uid = $q['uid'];
		}
		$title = '管理者';
		$href['msg'] = thickbox_href(CONF_URLBASE. '/message.php?to='. $uid);
		$href['add'] = thickbox_href(CONF_URLBASE. '/friend_add.php?to='. $uid);
	}
	else {
		$title = 'ニックネーム';
		$href['msg'] = thickbox_href(CONF_URLBASE. '/message.php?to='. $uid);
		$href['add'] = thickbox_href(CONF_URLBASE. '/friend_add.php?to='. $uid);
	}

	$JQUERY['ready'][] = <<<__JQ_CODE__
var href_${uid} = new Array();
href_${uid}['msg'] = '${href['msg']}';
href_${uid}['add'] = '${href['add']}';

$("#myDiv_${uid}").contextMenu({
	menu: 'myMenu_${uid}'
},
function(action, el, pos) {
	if (action == 'quit') {
		$(".contextMenu").hide();
		return;
	}
//	alert(href_${uid}[action]);
	tb_show('', href_${uid}[action], false);
	return false;
//	alert('現在調整中です');
/*
	alert('Action: ' + action + '\\n\\n' +
		'Element ID: ' + $(el).attr('id') + '\\n\\n' + 
		'X: ' + pos.x + '  Y: ' + pos.y + ' (relative to element)\\n\\n' + 
		'X: ' + pos.docX + '  Y: ' + pos.docY+ ' (relative to document)'
		);
*/
});

__JQ_CODE__;

	$href = array();

	$handle = get_handle($uid);

	$COMUNI_FOOT_HTML[] = <<<__HTML__
<div>
<ul id="myMenu_${uid}" class="contextMenu">
  <li class="msg"><a href="#msg">メッセージを送る</a></li>
  <li class="add separator"><a href="#add">フレンドリスト追加</a></li>
  <li class="quit separator"><a href="#quit">なにもしない</a></li>
</ul>
</div>
__HTML__;

	$hide_div = <<<__HTML__
<div class="profile_title">${title}</div>
<div class="profile_body"><a id="myDiv_${uid}" href="#" onClick="return false;">${handle}</a></div>
__HTML__;
	;

	return $hide_div;
}

?>
