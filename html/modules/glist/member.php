<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require_once dirname(__FILE__). '/../../lib.php';
require_once dirname(__FILE__). '/../../regist_lib.php';

define('MOD_GLIST_DEFAULT_ENT_MAX', 1000);

/* 振り分け*/
$id = (intval($_REQUEST["eid"]) > 0) ? $_REQUEST["eid"] : $_REQUEST["pid"];
$gid   = isset($_POST['gid']) ? intval($_POST['gid']) : get_gid($id);

if (join_level($gid) < 80) {
	show_error('グループ副管理者以上が行える操作です。');
}

switch ($_REQUEST["action"]) {
	case 'change_level':
		chg_level($gid);
	break;
	case 'app':
		app_data($id);
	break;
	default:
		;
}

input_data($id);

/* 登録*/

function chg_level($gid = 0) {
	$uid   = isset($_POST['uid']) ? intval($_POST['uid']) : 0;
	$level = isset($_POST['level']) ? intval($_POST['level']) : null;

	if (join_level($gid) < 100 and $level == 100) {
		show_error('グループ管理者だけが行える操作です。');
	}

	$c = mysql_full('select * from group_member'.
					' where gid = %s and uid = %s and level = %s',
					mysql_num($gid), mysql_num($uid), mysql_num(100));
	if($c and join_level($gid) < 100)show_error('グループ管理者だけが行える操作です。');

	if ($uid == 0 || $gid == 0) {
		return;
	}

	$c = mysql_full('select * from group_member'.
					' where gid = %s and level = %s',
					mysql_num($gid), mysql_num(100));

	if (mysql_num_rows($c) == 1) {
		$d = mysql_fetch_array($c);
		if ($d['uid'] == $uid && $level < 100) {
			show_error('それを行うと管理者がいなくなってしまいます。');
		}
	}

	$u = mysql_exec('update group_member set level = %s'.
					' where gid = %s and uid = %s',
					mysql_num($level), mysql_num($gid), mysql_num($uid));
}

function app_data($id = null) {
	global $SYS_FORM, $JQUERY;

	$gid = get_gid($id);
	$uid = intval($_REQUEST["uid"]);

	$d = mysql_exec("delete from group_app".
					" where gid = %s and uid = %s",
					mysql_num($gid), mysql_num($uid));

	join_group(array(gid => $gid, uid => $uid, 'level' => 10));
	
	$JQUERY['ready'][]="parent.update_block_content($id);";
}

/* フォーム*/
function input_data($id = null) {
	global $SYS_FORM, $JQUERY;

	$gid = isset($_REQUEST['gid']) ? intval($_REQUEST['gid']) : get_gid($id);
	$uid = myuid();

	$p = mysql_uniq("select * from prof_add_req where gid = %s order by req_id",
					mysql_num($gid));
	$req_id = $p['req_id'];

	// 承認中
	$p = mysql_full("select u.id as uid, u.handle from group_app as gm".
					" inner join user as u on gm.uid = u.id".
					" where gm.gid = %s order by gm.initymd desc",
					mysql_num($gid));

	$member_app = array();
	$app_uid_list = array(); 
	if ($p) {
		while ($r = mysql_fetch_array($p)) {
			$member_app[$r["uid"]] = array(uid     => $r["uid"],
								  handle  => $r["handle"]);
			$app_uid_list[] = $r['uid'];
		}
	}

	if (count($member_app) > 0) {
		$html_memapp = '<div style="clear: both;"></div>';
		$html_memapp .= '<h3>参加申請リスト</h3>';

		$list = array();

		if($req_id){
			$list[] = array('nickname' => 'ニックネーム', 'info' => '登録情報');
	
			foreach ($member_app as $m) {
				$nickname = make_href($m["handle"], 'user.php?uid='. $m["uid"].'&gid='.$gid, false);

				$data = regist_data_get_reqdata($m['uid'],$gid);
				
				$reg_data = "<table class='edit_table'>\n";
				foreach($data as $d){
					$title = htmlspecialchars($d['title']);
					$dd = $d['data'];
					if(is_array($dd))$dd = implode("\n",$dd);
					$dd = str_replace("\n",'<br>',htmlspecialchars($dd));
					$reg_data .= "<tr><th style='width:30%'>$title</th><td>$dd</td></tr>\n";
				}
				$reg_data .= "</table>\n";

				$list[] = array('id' => $m['uid'],
								'nickname' => $nickname,
								'info'  => $reg_data);
			}
		}else{
			$list[] = array('nickname' => 'ニックネーム');
	
			foreach ($member_app as $m) {
				$nickname = make_href($m["handle"], 'user.php?uid='. $m["uid"].'&gid='.$gid, false);
	
				$list[] = array('id' => $m['uid'],
								'nickname' => $nickname);
			}
		}
		$editor = array('承認' => 'member.php?eid='. $id. '&action=app&uid=');
		
		$html_memapp .= create_auth_list($editor, $list);
	}

	$data = array(title   => 'グループの参加承認設定',
				  icon    => 'write',
				  content => get_thumblist($gid, $id). $html_memapp);

	show_dialog2($data);

	exit(0);
}

function get_thumblist($gid = 0, $id = 0) {
	global $JQUERY,$COMUNI_HEAD_CSSRAW,$COMUNI_HEAD_JSRAW;

	$mylevel = join_level($gid);

	$option = array();
	$top_option = array();
	$q = mysql_full('select * from conf_group_level order by level desc');
	if ($q) {
		while ($r = mysql_fetch_array($q)) {
			if($r['level'] == 100 and $mylevel != 100)$top_option[$r['level']] = $r['name'];
			else $option[$r['level']] = $r['name'];
		}
	}

	$q = mysql_full('select gm.gid, gm.uid, pd.thumb, gm.level'.
					' from group_member as gm'.
					' left join profile_data as pd on pd.uid = gm.uid'.
					' where gm.gid = %s'.
					' group by gm.uid'.
					' order by gm.level desc',
					mysql_num($gid));

	$html .= '<h3>参加中のメンバー</h3>';

	$attr = array('name' => 'action', 'value' => 'chg_level');
	$select = get_form('hidden', $attr);
	if ($q) {
		while ($d = mysql_fetch_array($q, MYSQL_ASSOC)) {
			$nickname = get_handle($d['uid']);
			if($mylevel != 100 and $d['level'] == 100){
				$attr = array('name' => 'level', 'id' => 'level_'. $d['uid'], 'value' => $d['level'], 'option' => $top_option);
				$select = get_form('select', $attr);
			}else{
				$attr = array('name' => 'level', 'id' => 'level_'. $d['uid'], 'value' => $d['level'], 'option' => $option);
				$select = get_form('select', $attr);
			}

			$JQUERY['ready'][] = <<<__JQ__
$("#level_${d['uid']}").change(function(){
	$("#member_${d['uid']}").submit();
});
__JQ__;
			;
			$html .= <<<__DIV_BOX__
<form action="member.php" id="member_${d['uid']}" method="POST" style="margin: 0;">
<input type="hidden" name="action" value="change_level">
<input type="hidden" name="eid" value="${id}">
<input type="hidden" name="gid" value="${gid}">
<input type="hidden" name="uid" value="${d['uid']}">
<div class="clearfix member_enclose">
<div style="float: left;height: 32px; width: 32px; margin-right: 2px; background: url(/databox/profile/m/${d['thumb']}) no-repeat top center;"></div>
<div style="float: right; width: 86px; text-align: left; margin-top: 6px;"><a href="user.php?gid=${gid}&uid=${d['uid']}">${nickname}</a></div>
<div style="clear: both;">
${select}
</div>
</div>
</form>
__DIV_BOX__;
			;
		}
	}

	$COMUNI_HEAD_CSSRAW[] = <<<__CSS__
div.member_enclose{
	width: 120px;
	margin: 4px;
	padding: 0;
	float: left;
	border: solid 1px #ddd;
	height: auto;
}
__CSS__;

/*
 * ボックスを同じ高さにそろえる
 * リロードや履歴を戻るときにはうまくいくが、新たにThickboxウィンドウを開いて表示するときはだめなので
 * うまくいかないときはリロード*/
	$JQUERY['ready'][] = <<<__JS__
var max_height = 0;
function height(){
	$('div.member_enclose').each(function(){
		if(max_height < $(this).height())max_height = $(this).height();
	});
	if($('div.member_enclose').length>0 && max_height == 0){
		setTimeout("location.reload()", 500);
		return;
	}
	$('div.member_enclose').each(function(){
		$(this).height(max_height);
	});
}
height();
__JS__;
	return $html;
}

?>
