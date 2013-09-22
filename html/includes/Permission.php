<?php 
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
?>
<?php

define('PMT_PUBLIC', 0);
define('PMT_MEMBER', 1);
define('PMT_CLOSE',  2);

//-----------------------------------------------------
// * 閲覧権限のセット
//-----------------------------------------------------
function set_pmt($param) {
	global $COMUNI;
	$eid  = $param["eid"];
	$uid  = isset($param["uid"]) ? intval($param["uid"]) : $COMUNI["uid"];
	$gid  = isset($param["gid"]) ? intval($param["gid"]) : get_gid($eid);
	$name = isset($param["name"]) ? $param["name"] : 'pmt_0';
	$unit = isset($param["unit"]) ? intval($param["unit"]) : null;
	$not_owner = isset($param["not_owner"]) ? 1 : 0;

	if (!$eid) {
		return false;
	}

	if (!isset($unit)) {
		$pmt  = isset($_REQUEST[$name]) ? intval($_REQUEST[$name]) : 2;

		$unit = 0;
		switch ($pmt) {
			// インターネット
			case 0:
			case 1:
			case 2:
				$unit = $pmt;
				break;
			case 3:
				if (is_array($_REQUEST[$name. '_sub'])) {
					if (count($_REQUEST[$name. '_sub']) > 1) {
						foreach ($_REQUEST[$name. '_sub'] as $pmt_sub) {
							$unit_sub[] = intval($pmt_sub);
						}
						$unit_in = join(",", $unit_sub);
						$c = mysql_uniq("select * from unit_sub group by id".
										" having gid in(${unit_in}) and count(id) = %s",
										mysql_num(count($unit_sub)));
						if (!$c) {
							$unit = get_seqid('group');
							add_pmt_group($unit, $unit_sub);
							foreach ($unit_sub as $u) {
								$q = mysql_exec("insert into unit_sub(id, gid) values(%s, %s)",
												mysql_num($unit), mysql_num($u));
							}
						}
					}
					else {
						$unit = $_REQUEST[$name. '_sub'][0];
					}
				}
				else {
					$unit = $_REQUEST[$name. '_sub'];
				}
				if (!isset($unit)) {
					$unit = PMT_CLOSE;
				}
				break;
			default:
				$unit = $pmt;
		}
	}

	$q1 = mysql_exec("delete from element where id = %s", mysql_num($eid));
	if (!$q1) {
		die(mysql_error());
	}
	$q2 = mysql_exec("insert into element (id, unit) values (%s, %s);",
					 mysql_num($eid), mysql_num($unit));
	if (!$q2) {
		die(mysql_error());
	}

	if ($not_owner == 0) {
		set_owner($eid, $uid, $gid);
	}
}

//-----------------------------------------------------
// * 閲覧権限の追加 (つながりID指定)
//-----------------------------------------------------
function add_pmt_group($unit, $unit_sub) {
	$unit_in = join(",", $unit_sub);
	$q = mysql_exec("select distinct uid from unit where id in(${unit_in})");
	while ($r = mysql_fetch_array($q)) {
		$uid[] = $r["uid"];
	}

	$d = mysql_exec("delete from unit where id = %s", mysql_num($unit));
	foreach ($uid as $u) {
		$f = mysql_exec("insert into unit(id, uid) values(%s, %s)",
						mysql_num($unit), mysql_num($u));
	}
}

//-----------------------------------------------------
// * $id の所有者をセット
//-----------------------------------------------------
function set_owner($id, $uid, $gid) {
	if (!is_su()) {
		$c = mysql_uniq("select * from owner where id = %s", mysql_num($id));
		if ($c) {
			if ($c['uid'] != myuid()) {
				return;
			}
		}
	}

	$q1 = mysql_exec("delete from owner where id = %s", mysql_num($id));
	if (!$q1) {
		die(mysql_error());
	}
	$q2 = mysql_exec("insert into owner (id, uid, gid) values (%s, %s, %s);",
					 mysql_num($id), mysql_num($uid), mysql_num($gid));
	if (!$q2) {
		die('set_owner'. mysql_error());
	}
}

//-----------------------------------------------------
// * 閲覧権限をチェック
//-----------------------------------------------------
function check_pmt($eid = 0, $chk_uid = null) {
	global $COMUNI, $COMUNI_DEBUG;

	if (!$chk_uid) {
		if (is_su()) {
			return true;
		}
		if (is_bosai_web_admin($eid)) {
			return true;
		}
		if (is_reporter_admin($eid)) {
			return true;
		}
	}

	$uid = $chk_uid ? $chk_uid : myuid();

	$e = mysql_uniq("select * from element where id = %s;", mysql_num($eid));

	if (!$e) {
		return false;
	}
//	$COMUNI_DEBUG[] = $eid. "/". $e["unit"]. "/". $uid;

	if (is_owner($eid, 100)) {
		return true;
	}

	if ($uid > 0) {
		if ($e["unit"] > 2) {
			$u = mysql_uniq("select * from unit where id = %s and uid = %s",
							mysql_num($e["unit"]), mysql_num($uid));
			if ($u) {
				return true;
			}
		}
		else {
			if ($e["unit"] < 2) {
				return true;
			}
		}
	}
	else {
		if ($e["unit"] < 1) {
			return true;
		}
	}
	return false;
}

//パーツの公開範囲を階層的に求める
//パーツの配置されたページの公開範囲との狭いほうを返す
//ただしパーツの設定が特殊な設定ならばそちらを優先
function get_pmt_blk_hier($blk_id = null){
	$q = mysql_uniq('select be.unit as blk_pmt,pe.unit as page_pmt,page.gid,page.uid from block'.
					' left join element as be on block.id = be.id'.
					' left join page on block.pid = page.id'.
					' left join element as pe on page.id = pe.id'.
					' where block.id = %s', mysql_num($blk_id));
	if(!$q)return false;
	if($q[blk_pmt]<=PMT_CLOSE){
		$pmt = $q['blk_pmt']>$q['page_pmt']?$q['blk_pmt']:$q['page_pmt'];
	}else{
		$pmt = $q['blk_pmt'];
	}
	return array('pmt' => $pmt,'uid' => $q['uid'], 'gid' => $q['gid']);
}
function check_pmt_blk($blk_id = null,$uid = null){
	if(is_su())return true;
	$pmt = get_pmt_blk_hier($blk_id);
	if(!$pmt)return faluse;
	if($pmt['pmt']<=public_status())return true;
	if(is_null($uid))$uid = myuid();
	if($uid == $pmt['uid'])return true;
	if(join_level($pmt['gid'])>=100)return true;
	$q = mysql_uniq('select * from unit where id = %s and uid = %s',
					$pmt['pmt'],$uid);
	if($q)return true;
	return false;
}

//-----------------------------------------------------
// * 閲覧権限付与のためのフォームを生成
//-----------------------------------------------------
function pmt_form($eid = null) {
	if (get_gid($eid) > 0) {
		return pmt_form_group($eid);
	}
	else {
		return pmt_form_user($eid);
	}
}

//-----------------------------------------------------
// * 閲覧権限付与のためのフォームを生成 (マイページ用)
//-----------------------------------------------------
function pmt_form_user($eid) {
	global $COMUNI, $JQUERY;

	$current_pmt = get_pmt($eid);

	if (!$COMUNI["__pmt_num"]) {
		$COMUNI["__pmt_num"] = 0;
	}

	$uid = $COMUNI["uid"];
	$num = $COMUNI["__pmt_num"];

	// 友達の友達
	$fx = mysql_uniq("select * from friend_extra where uid = %s",
					 mysql_num($uid));

	// 友達
	$f = mysql_exec("select * from friend_user where owner = %s",
					mysql_num($uid));

	$friends = array();
	while ($r = mysql_fetch_array($f)) {
		if ($r["gid"] == $r["pid"]) {
			$friend_all = $r["gid"];
		}
		else {
			$friends[] = array(gid => $r["gid"], name => $r["name"]);
		}
	}

	if ($current_pmt == $fx["gid"]) {
		$checked_id = $current_pmt. ':radio';
		$opt_ready_code = "\$('#pmt_${num}_sub').hide();";
	}
	else if ($current_pmt > 2) {
		$checked_id = 'sub_'. $current_pmt. ':checkbox';
		$opt_ready_code = "\$('#pmt_${num}_3:radio').attr('checked', true);";
	}
	else {
		$checked_id = $current_pmt. ':radio';
		$opt_ready_code = "\$('#pmt_${num}_sub').hide();";
	}

	$JQUERY["ready"][] = <<<__PMT_FORM__
/* pmt_ready num = ${num} */
	\$("#pmt_${num}_${checked_id}").attr('checked', true);
	${opt_ready_code}
	\$('#pmt_${num}_0').click(function() { \$('#pmt_${num}_sub').hide(); });
	\$('#pmt_${num}_1').click(function() { \$('#pmt_${num}_sub').hide(); });
	\$('#pmt_${num}_2').click(function() { \$('#pmt_${num}_sub').hide(); });
	\$('#pmt_${num}_3').click(function() { \$('#pmt_${num}_sub').show('fast'); });
	\$('#pmt_${num}_${fx["gid"]}').click(function() { \$('#pmt_${num}_sub').hide(); });

	\$('#pmt_${num}_sub_${friend_all}').click(function() {
		if (\$("#pmt_${num}_sub_${friend_all}:checkbox").attr('checked') == true) {
			\$("#pmt_${num}_sub_${friend_all}_c > :checkbox").attr('checked', false);
			\$("#pmt_${num}_sub_${friend_all}_c > :checkbox").attr('disabled', true);
		}
		else {
			\$("#pmt_${num}_sub_${friend_all}_c > :checkbox").attr('disabled', false);
		}
	});
/* pmt_ready num = ${num} */
__PMT_FORM__;

	$form = <<<__PMT_FORM__
<div id="pmt_${num}_div">
<input type="radio" name="pmt_${num}" value="2" id="pmt_${num}_2">
  <label for="pmt_${num}_2">非公開 (自分だけ)</label><br>
<input type="radio" name="pmt_${num}" value="1" id="pmt_${num}_1">
  <label for="pmt_${num}_1">登録ユーザーのみ</label><br>
<input type="radio" name="pmt_${num}" value="0" id="pmt_${num}_0">
  <label for="pmt_${num}_0">インターネット</label><br>
<input type="radio" name="pmt_${num}" value="3" id="pmt_${num}_3">
  <label for="pmt_${num}_3">フレンドリストから選択</label><br>
<div id="pmt_${num}_sub" style="padding-left: 2em;">
  <input type="checkbox" name="pmt_${num}_sub" value="${friend_all}" id="pmt_${num}_sub_${friend_all}">
    <label for="pmt_${num}_sub_${friend_all}">フレンドリスト全員</label><br>
  <div id="pmt_0_sub_${friend_all}_c">
__PMT_FORM__;

	$i = 1;
	foreach ($friends as $f) {
		$c = '';
		if ($current_pmt == $f["gid"]) {
			$c = ' checked';
		}
		$form .= "  <input type=\"checkbox\" name=\"pmt_${num}_sub\" value=\"". $f["gid"]. "\" id=\"pmt_${num}_sub_${i}\"${c}>";
		$form .= "    <label for=\"pmt_${num}_sub_${i}\">". $f["name"]. "</label><br>";
		$i++;
	}

	$form .= <<<__PMT_FORM__
  </div>
</div>
<!--
<input type="radio" name="pmt_${num}" value="${fx["gid"]}" id="pmt_${num}_${fx["gid"]}">
  <label for="pmt_${num}_${fx["gid"]}">フレンドリストつながり全員</label><br>
-->
</div>
<!-- /pmt_form -->
__PMT_FORM__;

	$COMUNI["__pmt_num"]++;

	return $form;
}

/* 簡易パーミッション選択 */
function pmt_miniform_val($str = null, $val = 2, $gid = 0) {
	$selected = array($val => ' selected');

	$retval = <<<__PMT_FORM__
<select name="pmt_${str}" class="input_minipmt">
<option value="2"${selected[2]}>非公開 (自分だけ)</option>
<option value="1"${selected[1]}>登録ユーザーのみ</option>
__PMT_FORM__;

	if($gid>0)$retval .= <<<__PMT_FORM__
<option value="${gid}"${selected[$gid]}>このグループのみ</option>
__PMT_FORM__;
	$retval .= <<<__PMT_FORM__
<option value="0"${selected[0]}>インターネット</option>
</select>
__PMT_FORM__;
	return $retval;
}

//-----------------------------------------------------
// * 閲覧権限付与のためのミニフォームを生成
//-----------------------------------------------------
function pmt_miniform($eid = null) {
	if (get_gid($eid) > 0) {
		return pmt_miniform_group($eid);
	}
	else {
		return pmt_miniform_user($eid);
	}
}

//-----------------------------------------------------
// * 閲覧権限付与のためのミニフォームを生成 (マイページ用)
//-----------------------------------------------------
function pmt_miniform_user($eid = null) {
	$current_pmt = get_pmt($eid);

	$selected = array($current_pmt => ' selected');

	return <<<__PMT_FORM__
<select name="pmt_${eid}" class="input_minipmt">
<option value="2"${selected[2]}>非公開 (自分だけ)</option>
<option value="1"${selected[1]}>登録ユーザーのみ</option>
<option value="0"${selected[0]}>インターネット</option>
</select>
__PMT_FORM__;
	;
}

//-----------------------------------------------------
// * 閲覧権限付与のためのミニフォームを生成 (グループ用)
//-----------------------------------------------------
function pmt_miniform_group($eid = null) {
	$current_pmt = get_pmt($eid);
	$gid = get_gid($eid);

	$selected = array($current_pmt => ' selected');

	return <<<__PMT_FORM__
<select name="pmt_${eid}" class="input_minipmt">
<option value="2"${selected[2]}>非公開 (自分だけ)</option>
<option value="1"${selected[1]}>登録ユーザーのみ</option>
<option value="${gid}"${selected[$gid]}>このグループのみ</option>
<option value="0"${selected[0]}>インターネット</option>
</select>
__PMT_FORM__;
	;
}


//-----------------------------------------------------
// * 閲覧権限付与のためのフォームを生成 (グループ用)
//-----------------------------------------------------
function pmt_form_group($eid = null) {
	global $COMUNI, $JQUERY;

	$current_pmt = get_pmt($eid);

	if (!$COMUNI["__pmt_num"]) {
		$COMUNI["__pmt_num"] = 0;
	}

	$gid = get_gid($eid);
	$num = $COMUNI["__pmt_num"];

	// 友達
	$f = mysql_exec("select * from friend_group where owner = %s",
					mysql_num($gid));

	if ($f) {
		while ($r = mysql_fetch_array($f)) {
			if ($r["gid"] == $r["pid"]) {
				$friend_all = $r["gid"];
			}
			else {
				$friends[] = array(gid => $r["gid"], name => $r["name"]);
			}
		}
	}

	if ($current_pmt > 2 and $current_pmt != $gid) {
		$checked_id = 'sub_'. $current_pmt. ':checkbox';
		$opt_ready_code = "\$('#pmt_${num}_3:radio').attr('checked', true)";
	}
	else {
		$checked_id = $current_pmt. ':radio';
		$opt_ready_code = "\$('#pmt_${num}_sub').hide();";
	}

	$JQUERY["ready"][] = <<<__PMT_FORM__
/* pmt_ready num = ${num} */
	\$("#pmt_${num}_${checked_id}").attr('checked', true);
	${opt_ready_code}
	\$('#pmt_${num}_0').click(function() { \$('#pmt_${num}_sub').hide(); });
	\$('#pmt_${num}_1').click(function() { \$('#pmt_${num}_sub').hide(); });
	\$('#pmt_${num}_2').click(function() { \$('#pmt_${num}_sub').hide(); });
	\$('#pmt_${num}_${gid}').click(function() { \$('#pmt_${num}_sub').hide(); });
	\$('#pmt_${num}_3').click(function() { \$('#pmt_${num}_sub').show('fast'); });

	\$('#pmt_${num}_sub_${friend_all}').click(function() {
		if (\$("#pmt_${num}_sub_${friend_all}:checkbox").attr('checked') == true) {
			\$("#pmt_${num}_sub_${friend_all}_c > :checkbox").attr('checked', false);
			\$("#pmt_${num}_sub_${friend_all}_c > :checkbox").attr('disabled', true);
		}
		else {
			\$("#pmt_${num}_sub_${friend_all}_c > :checkbox").attr('disabled', false);
		}
	});
/* pmt_ready num = ${num} */
__PMT_FORM__;

	$form = <<<__PMT_FORM__
<div id="pmt_${num}_div">
<input type="radio" name="pmt_${num}" value="2" id="pmt_${num}_2">
  <label for="pmt_${num}_2">非公開 (自分だけ)</label><br>
<input type="radio" name="pmt_${num}" value="1" id="pmt_${num}_1">
  <label for="pmt_${num}_1">登録ユーザーのみ</label><br>
<input type="radio" name="pmt_${num}" value="0" id="pmt_${num}_0">
  <label for="pmt_${num}_0">インターネット</label><br>
<input type="radio" name="pmt_${num}" value="${gid}" id="pmt_${num}_${gid}">
  <label for="pmt_${gid}">このグループのみ</label><br>
</div>
<!-- /pmt_form -->
__PMT_FORM__;

	$COMUNI["__pmt_num"]++;

	return $form;
}

//-----------------------------------------------------
// * テーブルから閲覧権限と共にデータを取得
//-----------------------------------------------------
function get_data_with_pmt($table = null, $subq = '') {
	global $COMUNI;

	if (!$table) {
		return false;
	}

	$public = $COMUNI["is_login"] ? 2 : 1;

	$opt = '';
	if ($subq) {
		$opt = ' and '. $subq;
	}
	return mysql_exec("select ${table}.* from ${table}".
					  " inner join element on ${table}.eid = element.id".
					  " left join unit on element.unit = unit.id".
					  " where (element.unit < %s or unit.uid = %s)${opt}",
					  mysql_num($public), mysql_num($COMUNI["uid"]));
}

//-----------------------------------------------------
// * 指定 URL から編集権限を検索
//-----------------------------------------------------
function get_edit_ids() {
	$eid = isset($_REQUEST["eid"]) ? intval($_REQUEST["eid"]) : 0;
	$pid = isset($_REQUEST["pid"]) ? intval($_REQUEST["pid"]) : 0;

	if ($eid > 0) {
		if (!is_owner($eid)) {
			if (!is_login()) {
				jump2login();
			}
			show_error('あなたには編集権限がありません。[EID]');
		}
	}
	else if ($pid > 0) {
		if (!is_owner($pid)) {
			if (!is_login()) {
				jump2login();
			}
			show_error('あなたには編集権限がありません。[PID]');
		}
	}
	return array($eid, $pid);
}


/**
 * Description of Permission
 *
 * @author ikeda
 */
class Permission {

	const PMT_BROWSE_PUBLIC			= PMT_PUBLIC;
	const PMT_BROWSE_FOR_AUTHORIZED = PMT_MEMBER;
	const PMT_BROWSE_PRIVATE		= PMT_CLOSE;
	const PMT_BROWSE_FOR_GROUP		= 4;
	const PMT_BROWSE_MASK            = 3;

	const USER_LEVEL_ADMIN		= 100;	// 管理者(設定変更)
	const USER_LEVEL_POWERED	= 80;	// パワーユーザー(参加処理):  80
	const USER_LEVEL_EDITOR		= 50;	// エディター(記事の作成)  :  50
	const USER_LEVEL_DELETER	= 30;	// デリーター(記事の削除)  :  30
	const USER_LEVEL_AUTHORIZED	= 10;	// 一般参加者
	const USER_LEVEL_ANONYMOUS	= 0;	// 認証無し

}
?>
