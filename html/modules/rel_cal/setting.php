<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require_once dirname(__FILE__). '/../../lib.php';

define('BLOCK_TITLE', '連携カレンダー機能設定');

/*----------------------------------------------------*
 *- action
 *----------------------------------------------------*/
list($eid, $pid) = get_edit_ids();

if(!is_owner($pid,80))show_error('権限がありません');

switch ($_REQUEST["action"]) {
	case 'regist':
		regist_data($eid, $pid);
	default:
		input_data($eid, $pid);
}

/*----------------------------------------------------*
 *- regist_data
 *----------------------------------------------------*/
function regist_data($eid = null, $pid = null) {
	global $SYS_FORM;

	$new_allow_groups = $_POST['allow_groups'];

	//スケジュール関連のテーブルのフィールドを取得
	$p = mysql_full('show columns from schedule_data');
	$schedule_data_fields = array();
	while($c = mysql_fetch_assoc($p))
		if($c['Field']!='id' and $c['Field']!='pid')
			$schedule_data_fields[] = $c['Field'];
	$schedule_data_fields = implode(',',$schedule_data_fields);
	$p = mysql_full('show columns from schedule_data_add_ical');
	$schedule_data_add_ical_fields = array();
	while($c = mysql_fetch_assoc($p))
		if($c['Field']!='id' and $c['Field']!='pid')
			$schedule_data_add_ical_fields[] = $c['Field'];
	$schedule_data_add_ical_fields = implode(',',$schedule_data_add_ical_fields);
	
	//既に許可済みのグループ
	$p = mysql_full("select * from rel_cal_blk_rel where uid = %s",
					mysql_num(myuid()));
	$allow_groups = array();
	while($g = mysql_fetch_assoc($p)){
		$allow_groups[$g['gid']] = $g['gid'];
	}

	$p = mysql_full();
	$d = mysql_exec('delete from rel_cal_blk_rel where uid = %s', myuid());
	foreach($new_allow_groups as $gid){
	//スケジュール公開先グループを設定
		$q = mysql_exec('insert into rel_cal_blk_rel'.
						' (gid,uid,blk_id) values (%s, %s, %s)',
						mysql_num($gid), mysql_num(myuid()), mysql_num($pid));
	//新たなスケジュール公開先グループの予定を取り込み
		if(!isset($allow_groups[$gid])){
			//スケジュール公開先グループのカレンダーパーツ
			$p = mysql_uniq('select block.id from block natural join owner where owner.gid = %s and module="rel_cal"',mysql_num($gid));
			$rel_blk = $p['id'];
			//当該パーツの記事一覧
			$p = mysql_full('select id,ical_uid from schedule_data natural join schedule_data_add_ical where pid = %s',mysql_num($rel_blk));
			while($scd = mysql_fetch_assoc($p)){
				//すでに登録されているか確認
				$p = mysql_uniq('select id from schedule_data natural join schedule_data_add_ical where pid = %s and ical_uid = %s',
								mysql_num($pid),mysql_str($p['ical_uid']));
				$new_eid = get_seqid();
				$d = mysql_exec('insert into schedule_data'.
							" (id, pid, $schedule_data_fields)".
							" select %s,%s,$schedule_data_fields from schedule_data where id=%s",
							mysql_num($new_eid),mysql_num($pid),mysql_num($scd['id']));
				$d = mysql_exec('insert into schedule_data_add_ical'.
							" (id, $schedule_data_add_ical_fields)".
							" select %s,$schedule_data_add_ical_fields from schedule_data_add_ical where id=%s",
							mysql_num($new_eid),mysql_num($scd['id']));
				set_pmt(array('eid' => $new_eid,'uid' => myuid(),'gid' => 0));
			}
		}
	}

	$html = '設定が完了しました。';
	$data = array(title   => BLOCK_TITLE,
				  icon    => 'finish',
				  content => $html. create_form_return(array(eid => $pid, href => home_url($pid))));

	show_input($data);

	exit(0);
}

/*----------------------------------------------------*
 *- input_data
 *----------------------------------------------------*/
function input_data($eid = null, $pid = null) {
	global $SYS_FORM;

	//加入グループのリスト
	$p = mysql_full('select group_member.gid,sitename from group_member'.
					' inner join page on page.gid = group_member.gid'.
					' where group_member.uid = %s',
					mysql_num(myuid()));
	$join_groups = array();
	while($g = mysql_fetch_assoc($p)){
		$join_groups[$g['gid']] = make_href($g['sitename'],'/index.php?gid='.$g['gid']);
	}

	//既に許可済みのグループ
	$p = mysql_full("select * from rel_cal_blk_rel where uid = %s",
					mysql_num(myuid()));
	$allow_groups = array();
	while($g = mysql_fetch_assoc($p)){
		$allow_groups[$g['gid']] = 1;
	}

	//---- フォーム生成 ----
	// hidden:action
	$attr = array(name => 'action', value => 'regist');
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));

	// check:view_group
	$attr = array(name => 'allow_groups', option =>$join_groups, value => $allow_groups, break_num => 1);
	$SYS_FORM["input"][] = array(title => 'スケジュールを開示するグループ',
								 name  => 'allow_groups',
								 body  => get_form("checkbox", $attr));

	$SYS_FORM["action"] = 'setting.php';
	$SYS_FORM["method"] = 'POST';
	$SYS_FORM["submit"]  = '設定';
	$SYS_FORM["cancel"]  = '前に戻る';

	$html = create_form(array(eid => $eid, pid => $pid));

	$data = array(title   => BLOCK_TITLE,
				  icon    => 'write',
				  content => $html);

	show_input($data);

	exit(0);
}

?>
