<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require dirname(__FILE__). '/lib.php';

$uid   = $COMUNI["uid"];

/***  ***/
$act = $_REQUEST["act"];
switch ($act) {
	case 'toggle':
		$message = toggle_list();
	break;
	case 'move':
		$message = move_list();
	break;
	case 'delete':
		$message = delete_list();
	break;
	case 'edit_category':
		$message = edit_category();
	break;
	default:
		;
}

/*** get gids ***/
$q = mysql_full("select * from friend_user where owner = %s".
				" order by gid",
				mysql_num($uid));
if (!$q) {
	die($uid. ' friend list not found.');
}

$pid = null; $group = array(); $friend_select = array();
while ($r = mysql_fetch_array($q)) {
	$group[] = array(gid   => $r["gid"],
					 owner => $r["owner"],
					 pid   => $r["pid"],
				     name  => $r["name"]);
	if ($r["gid"] == $r["pid"]) {
		$pid = $r["pid"];
		$name = 'すべて';
		continue;
	}
	else {
		$name = $r["name"] ? $r["name"] : '無題';
	}
	$friend_select[$r['gid']] = $name;
}
/*** show friends ***/
$li  = '';
$div = '';
$select_tab = '<option value="">- 新規追加</option>';
foreach ($group as $grp) {
	if ($grp["gid"] == $grp["pid"]) {
		$name = 'すべて';
	}
	else {
		$name = $grp["name"] ? $grp["name"] : '無題';
	}
	$shortname = mb_strimwidth($name,0,16,'…');

	$li  .= '    <li><a href="#tab'. $grp["gid"]. '" title="'.$name.'"><span>'. $shortname. '</span></a></li>';
	$div .= '<div id="tab'. $grp["gid"] . '"><div style="width: 100%;">'. load_friends($grp["gid"]).
			'</div><div style="clear: both;"></div></div>';
	$select_tab .= '<option value="'. $grp["gid"]. '">'. $name. '</option>';
}

$COMUNI_HEAD_CSS[] = "/ui.tabs.css";
$JQUERY["ready"][] = <<<__TAB_CODE__
	\$('#category').hide();

	\$('#filebox > ul').tabs();
	\$('#act_category').click(function() {
		\$('#category').toggle();
	});
__TAB_CODE__;
;

$html = <<<__CONTENTS__
<input type='text' titile='aaa'>
<style type="text/css">
<!--
	#upload, #category {
		border: solid 2px #eaeaff;
		background-color: #fcfcfc;
		padding: 8px;
		margin: 5px 3px;
		font-size: 0.8em;
	}
	.upload_input {
		border: solid 1px #cccccc;
	}
	.upload_submit {
		border: solid 1px #cccccc;
		background-color: #dfdfff;
		font-size: 0.8em;
	}
	.friend_data {
		width: 100px;
		height: 100px;
		padding: 3px;
		margin: 6px;
		vertical-align: top;
		text-align: center;
		border: solid 1px #cccccc;
		float: left;
	}
	#message {
		float: left;
		font-size: 0.8em;
		color: #fcc;
		padding-left: 24px;
		padding-bottom: 4px;
	}
-->
</style>
<div style="padding: 10px;">

<div style="text-align: right; padding: 4px;">
<img src="/image/fr.gif" align="absmiddle"> <a href="#" style="font-size: 0.8em;" id="act_category">リストを追加・変更</a>
</div>
<div id="message">${message}</div>
<div style="clear: both;"></div>
<div id="category">
<form action="/friend.php" method="POST">
<input type="hidden" name="act" value="edit_category">
<input type="hidden" name="pid" value="${pid}">

<select name="category">
${select_tab}
</select><input type="text" name="name" size="24" class="upload_input">
<input type="submit" value="追加・変更" class="upload_submit">
<input type="submit" name="delete" value="削除" class="upload_submit">
</form>
</div>

<div id="filebox">
  <ul>
${li}
  </ul>
</div>

<div style="clear: both;"></div>

${div}

</div>
__CONTENTS__;
	;

$contents = array('title'   => 'フレンドリスト',
			  'icon'    => 'friend',
			  'content' => $html);

show_dialog($contents);

exit(0);

/*
 * upload 
 */

function move_list() {
	$target_uid = isset($_REQUEST['target']) ? intval($_REQUEST['target']) : 0;
	$from = isset($_REQUEST['f']) ? intval($_REQUEST['f']) : 0;
	$to   = isset($_REQUEST['t']) ? intval($_REQUEST['t']) : 0;

	$f = mysql_full("select * from friend_user where owner = %s order by gid",
					mysql_num(myuid()));

	$g = array();
	if ($f) {
		while ($r = mysql_fetch_array($f, MYSQL_ASSOC)) {
			if ($r["gid"] == $r["pid"]) {
				$name = 'すべて';
			}
			else {
				$name = $r["name"] ? $r["name"] : '無題';
			}
			$g[$r['gid']] = $name;
		}
	}

	$u = mysql_exec('update unit set id = %s where id = %s and uid = %s',
					mysql_num($to), mysql_num($from), mysql_num($target_uid));

	return get_handle($target_uid). 'さんを '. $g[$from]. ' から '.
		   $g[$to]. ' に移動しました。';

}
function toggle_list() {
	$target_uid = isset($_REQUEST['target']) ? intval($_REQUEST['target']) : 0;
	$from = isset($_REQUEST['f']) ? intval($_REQUEST['f']) : 0;
	$to   = isset($_REQUEST['t']) ? intval($_REQUEST['t']) : 0;

	$f = mysql_full("select * from friend_user where owner = %s order by gid",
					mysql_num(myuid()));

	$g = array();
	if ($f) {
		while ($r = mysql_fetch_array($f, MYSQL_ASSOC)) {
			if ($r["gid"] == $r["pid"]) {
				$name = 'すべて';
			}
			else {
				$name = $r["name"] ? $r["name"] : '無題';
			}
			$g[$r['gid']] = $name;
		}
	}

	$u = mysql_uniq("select * from unit where id=%s and uid=%s",mysql_num($to),mysql_num($target_uid));
	if($u){
		$q = mysql_exec('delete from unit where id=%s and uid=%s',
					mysql_num($to),mysql_num($target_uid));
		$message = get_handle($target_uid). 'さんを '.
		   $g[$to]. ' から削除しました。';
	}else{
		$q = mysql_exec('insert into unit (id,uid) values (%s,%s)',
					mysql_num($to),mysql_num($target_uid));
		$message = get_handle($target_uid). 'さんを '.
		   $g[$to]. ' に所属させました。';
	}

	return $message;

}

function edit_category() {
	global $COMUNI;

	$uid = $COMUNI["uid"];
	$cid  = $_REQUEST["category"] ? intval($_REQUEST["category"]) : null;
	$pid = intval($_REQUEST["pid"]);
	if (!$pid) { reutrn; }

	
	if($_REQUEST['delete']){
		if($pid == $cid){
			return 'リスト "すべて"は削除できません';
		}
		$p = mysql_uniq("select * from friend_user where gid=%s and pid=%s and owner=%s",
						mysql_num($cid),mysql_num($pid),mysql_num($uid));
		$q = mysql_exec("delete from friend_user where gid=%s and pid=%s and owner=%s",
						mysql_num($cid),mysql_num($pid),mysql_num($uid));
		if(!$q)return;
		$name = $p['name'];
		$ret = '削除';
	}else{
		$name = trim(htmlesc($_REQUEST["name"]));
		if ($name == '') { return; }

		$new_id = get_seqid('group');
		if ($cid > 0) {
			$q = mysql_exec("update friend_user set name = %s where gid = %s",
							mysql_str($name), mysql_num($cid));
			$ret = '変更';
		}
		else {
			$q = mysql_exec("insert into friend_user (gid, owner, pid, name)".
							" values (%s, %s, %s, %s);",
							mysql_num($new_id), mysql_num($uid), mysql_num($pid), mysql_str($name));
			$ret = '追加';
		}
		if (!$q) {
			die("missing upload...". mysql_error());
		}
	}

	return 'リスト('. $name. ')を'. $ret. 'しました。';
}

function load_friends($id) {
	$q = mysql_full("select unit.*, user.handle from unit".
					" inner join user on unit.uid = user.id".
					" where unit.id = %s",
					mysql_num($id));

	if (!$q) { return '未登録'; }

	$data = ''; $opt_js = '';
	while ($r = mysql_fetch_array($q)) {
		$data .= '<div class="friend_data">'.
				 '<a href="/user.php?uid='. $r["uid"]. '" target="_blank">'. $r["handle"]. '</a>'.
				 '  <div style="text-align: center; font-size: 0.8em; padding: 2px;">'.
//				 '  <a href="/filebox.php?act=delete&id='. $r["id"]. '">リストから削除</a>'.
				 friend_select_tab('friend_'. $r['uid'], $id).
				 '  </div>'.
				 '</div>';
	}
	return $data;
}

function friend_select_tab($name, $value) {
	global $friend_select;

	list($n, $t) = explode('_', $name);
	$q = mysql_full("select * from unit inner join friend_user on unit.id=friend_user.gid".
					" where friend_user.owner=%s and unit.uid=%s",mysql_num(myuid()),mysql_num($t));
	while($p = mysql_fetch_array($q)){
		$unit[$p['gid']]=true;
	}
	foreach($friend_select as $key => $val){
		if($unit[$key])
			$options[$key] = '●'.$val;
		else
			$options[$key] = '　'.$val;
	}
	$options[0] = '所属リスト';
	$attr = array('name' => $name, 'value' => 0, 'option' => $options, 'width' => 14,
//				  'onChange' => 'location.href=\'friend.php?act=move&target='. $t. '&f='. $value. '&t=\' + this.value;');
				  'onChange' => 'location.href=\'friend.php?act=toggle&target='. $t. '&f='. $value. '&t=\' + this.value;');
	return get_form('select', $attr);
}


?>
