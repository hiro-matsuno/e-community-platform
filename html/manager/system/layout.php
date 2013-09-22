<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

$temp_types = array(0 => '管理者専用',1 => '制限無し');

require dirname(__FILE__). '/../../lib.php';

admin_check();

switch ($_REQUEST['action']){
	case 'add_group':
		add_group();
		break;
	case 'add_user':
		add_user();
		break;
	case 'regist':
		regist();
	case 'edit_temp':
		edit_temp();
		break;
	case 'delete':
		delete();
		break;
	case 'delete_do':
		delete_do();
		break;
	default:
		template_list();
}
function delete_do(){
	global $SYS_FORM,$temp_types;
	$id = intval($_REQUEST['id']);
	if(!$id)show_error('編集対象が指定されていません');

	$tpl = mysql_uniq('select * from site_template where id=%s',
					mysql_num($id));
	if(!$tpl)show_error('指定されたテンプレートはありません');

	mysql_exec("delete from site_template where id=%s",mysql_num($id));

	if(isset($_REQUEST['del_page'])){
		$p = mysql_uniq("select * from page where uid=%s and gid=%s",
					mysql_num($tpl['uid']),mysql_num($tpl['gid']));

		$site_id = $p['id'];
		$d = mysql_exec('delete from element where id = %s',mysql_num($site_id));
		// ブロック削除
		$d = mysql_exec('delete from block where pid = %s',mysql_num($site_id));

		if($tpl['uid']){
			mysql_exec('delete from user where id = %s',mysql_num($id));
			mysql_exec('delete from group_member where uid = %s',mysql_num($id));
			mysql_exec('delete from unit where uid = %s',mysql_num($id));
		}
	}

	template_list();
}
function delete(){
	global $SYS_FORM,$temp_types;
	$id = intval($_REQUEST['id']);
	if(!$id)show_error('編集対象が指定されていません');

	$tpl = mysql_uniq('select * from site_template where id=%s',
					mysql_num($id));
	if(!$tpl)show_error('指定されたテンプレートはありません');

	$attr = array('name' => 'action', 'value' => 'delete_do');
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));

	$attr = array('name' => 'id', 'value' => $id);
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));

	if($tpl['gid'])$pname = get_site_name(get_eid_by_group($tpl['gid']));
	else $pname = get_site_name(get_eid_by_mypage($tpl['uid']));
	$SYS_FORM["input"][] = array('title' => 'ページ名', 'name' => 'pname', 'body' => $pname);

	if($tpl['uid'])
	$SYS_FORM["input"][] = array('title' => 'ユーザー名', 'name' => 'pname', 'body' => get_handle($tpl['uid']));

	$SYS_FORM["input"][] = array('title' => 'テンプレート名', 'name' => 'tname', 'body' => $tpl['name']);

	$ug_str = $tpl['gid']? '': 'およびユーザー';
	$ug_str2 = $tpl['gid']? '': 'および'.get_handle($tpl['uid']);
	$str = "$pname${ug_str2}を削除する";
	$attr = array('name' => 'del_page', 'option' => array(1 => $str));
	$SYS_FORM["input"][] = array('title' => "ページ$ug_strの削除",
								'name' => 'tname',
								'body' => get_form("checkbox", $attr));

	$SYS_FORM["action"] = 'layout.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["submit"] = '設定';
	$SYS_FORM["cancel"] = '戻る';

	$html = create_form(array(eid => 0));
	
	$data = array('title'   => 'テンプレート基本情報',
				  'content' => $html);

	show_input($data);
}
function edit_temp(){
	global $SYS_FORM,$temp_types;
	$gid = intval($_REQUEST['gid']);
	$uid = intval($_REQUEST['uid']);
	$id = intval($_REQUEST['id']);
	$u_or_g = isset($_REQUEST['u_or_g'])?$_REQUEST['u_or_g']:null;
	$is_copy = intval($_REQUEST['copy']);
	
	if(!$gid and !$uid and !$id and !$u_or_g)show_error('編集対象が指定されていません');
	
	if($id){
		$q = mysql_uniq('select * from site_template where id=%s',
						mysql_num($_REQUEST['id']));
		if(!$q)show_error('指定されたテンプレートはありません');
		$tname = $q['name'];
		$gid = $q['gid'];
		$uid = $q['uid'];
		$type = $q['type'];
		$q = mysql_full("select block.* from block".
						" inner join page on block.pid = page.id".
						" where page.uid = %s and page.gid = %s",
						mysql_num($uid),mysql_num($gid));
		$blocks = array();
		$del_lock = array();
		while($b = mysql_fetch_assoc($q)){
			$blocks[$b['id']] = $b['name'];
			$del_lock[$b['id']] = $b['del_lock'];
		}
	}else{
		$q = mysql_uniq('select max(id) from site_template');
		$max_id = $q['max(id)']+1;
		$tname = "テンプレート${max_id}";
		$type = 1;
	}
	if($SYS_FORM["cache"]){
		$type = $SYS_FORM["cache"]['type'];
		$tname = $SYS_FORM["cache"]['name'];
		$del_lock = $SYS_FORM["cache"]['del_lock'];
	}

	$p = mysql_uniq('select * from page where uid=%s and gid=%s',
					mysql_num($uid),mysql_num($gid));
	if($p)
		$pname = $p['sitename'];
	else
		$pname = '(未作成)';
	
	// hidden:action
	$attr = array(name => 'action', value => 'regist');
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));
	
	$attr = array(name => 'uid', value => $uid);
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));
	
	$attr = array(name => 'gid', value => $gid);
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));
	
	$attr = array(name => 'id', value => $id);
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));
	
	$attr = array(name => 'u_or_g', value => $u_or_g);
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));
	
	$attr = array(name => 'copy', value => $is_copy);
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));
	
	$SYS_FORM["input"][] = array('title' => 'ページ名', 'name' => 'pname', 'body' => $pname);
	
	$attr = array(name => 'name', value => $tname, size => 32);
	$SYS_FORM["input"][] = array(title => 'テンプレート名',
								name  => 'name',
								body  => get_form("text", $attr));

	$attr = array(name => 'type', value => $type, option => $temp_types, break_num => 1);
	$SYS_FORM["input"][] = array(title => '公開範囲',
								 name  => 'type',
								 body  => get_form("radio", $attr));
								 
	if(isset($blocks)){
		$attr = array(name => 'del_lock', value => $del_lock, option => $blocks, break_num => 1);
		$SYS_FORM["input"][] = array(title => '削除禁止パーツ',
									 name  => 'del_lock',
									 body  => get_form("checkbox", $attr));
	}

	$SYS_FORM["action"] = 'layout.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["submit"] = '設定';
	$SYS_FORM["cancel"] = '戻る';

	$html = create_form(array(eid => 0));
	
	$data = array('title'   => 'テンプレート基本情報',
				  'content' => $html);

	show_input($data);

	exit(0);
}

function regist(){
	global $SYS_FORM;

	if(!$_REQUEST['name'])
		$SYS_FORM['error']['name'] = 'テンプレート名を入力してください';

	$name = htmlspecialchars($_REQUEST['name']);
	$SYS_FORM["cache"]['name'] = $name;
	
	$p = mysql_uniq('select * from site_template where name = %s and id <> %s',
					mysql_str('$name'),mysql_num($_REQUEST['id']));
	if($p)$SYS_FORM['error']['name'] = 'テンプレート名の重複があります';
	
	if(!is_numeric($_REQUEST['type']))
		$SYS_FORM['error']['type'] = '公開範囲を設定してください';
	else
		$SYS_FORM["cache"]['type'] = $_REQUEST['type'];
		
	$SYS_FORM["cache"]['del_lock'] = $_REQUEST['del_lock'];
	
	if($SYS_FORM['error'])return;
	
	if($_REQUEST['id']){
		$q = mysql_exec('update site_template set name=%s,type=%s where id=%s',
					mysql_str($name),mysql_num($_REQUEST['type']),mysql_num($_REQUEST['id']));
		if(!$q)show_error(mysql_error());

		$q = mysql_full("update block".
						" inner join page on block.pid = page.id".
						" inner join site_template".
						"  on site_template.uid = page.uid and site_template.gid = page.gid".
						" set del_lock = 0".
						" where site_template.id = %s",
						mysql_num($_REQUEST['id']));
	
		foreach($_REQUEST['del_lock'] as $b){
			mysql_exec('update block set del_lock=1 where id=%s',mysql_num($b));
		}
	}else{
		$uid = intval($_REQUEST['uid']);
		$gid = intval($_REQUEST['gid']);
		$u_or_g = $_REQUEST['u_or_g'];
		
		if(!$uid and !$gid and !$u_or_g)
			show_error('不正な入力です');
		
		if($u_or_g or $is_copy){
			$o_uid = $uid;
			$o_gid = $gid;
			if($u_or_g == 'user' or $uid){
				$uid = get_seqid('user');

				$pass = rand_str(32);
				$mail = rand_str(32);
				
				$f = mysql_exec("insert into user (id, handle, level, email, password, enable, initymd)".
								" values (%s, %s, %s, %s, %s, %s, %s);",
								mysql_num($uid), mysql_str('テンプレート用ユーザー'),
								10,mysql_str($mail), mysql_str(md5($pass)),
								mysql_num(1), mysql_current_timestamp());
				if(!$f)show_error(mysql_error);
				create_friend_user($uid);
				create_friend_extra($uid);
			}else{
				$gid = get_seqid('group');
				create_friend_group($gid);

				$q = mysql_exec("insert into group_joinable".
								" (gid, type, ent_max) values (%s, %s, %s)",
								mysql_num($gid), mysql_num(2),mysql_num(1000));

				join_group(array(gid   => $gid, uid   => myuid(), level => 100));
			}
			$page_id = get_seqid();

			$p = mysql_uniq(" select id from theme_skin where filename='e-community_blue_3c'");
			if($p)$skin_id = $p['id'];
			else $skin_id = 31;

			$f = mysql_exec("insert into page (id, uid, gid, sitename, description, skin, initymd)".
							" values (%s,%s,%s,%s,%s,%s,now())",
							mysql_num($page_id),mysql_num($uid),mysql_num($gid),
							mysql_str('テンプレート用ページ'),mysql_str(''),mysql_num($skin_id));
			if(!$f)show_error(mysql_error);
			set_pmt(array(eid => $page_id, gid =>$gid, uid => $uid, unit => PMT_MEMBER));
			
		}
		if($is_copy){
			$o_page = mysql_uniq("select * from page where uid=%s and gid=%s",
								mysql_num($o_uid),mysql_num($o_gid));
			mysql_exec("update page set skin=%s where id=%s",$o_page['skin'],$page_id);
			$p = mysql_full("select * from block where pid = %s",
							mysql_num($o_page['id']));
			while($b = mysql_fetch_assoc($p)){
				$new_block_id = get_seqid();
				$r = mysql_exec("insert into block (id, pid, module, name, vpos, hpos, del_lock)".
								" values (%s, %s, %s, %s, %s, %s, %s);",
								mysql_num($new_block_id),mysql_num($new_id),
								mysql_str($b['module']),mysql_str($b['name']),
								$b['vpos'],$b['hpos'],$b['del_lock']);
				set_pmt(array(eid  => $new_block_id,
							  uid  => $uid,
							  gid  => $gid,
							  unit => get_pmt($b['id'])));
			}
		}
		$max_ord = mysql_uniq("select max(ord) from site_template".
							" where uid=%s or gid=%s",
							mysql_num($uid),mysql_num($gid));
		mysql_exec('insert into site_template (uid,gid,name,ord,type)'.
					' values( %s,%s,%s,%s,%s )',
					mysql_num($uid),mysql_num($gid),mysql_str($name),
					mysql_num(intval($max_ord['max(ord)'])),
					mysql_num($_REQUEST['type']));
	}

	template_list();
}

function add_group(){

	$is_copy=$_REQUEST['type']=='copy'?1:0;
	$list = array();
	$list[] = array('gid'    => 'GID',
					'sitename' => 'ページ名',
					'edit'  => '操作');

	$style = array('gid'     => 'width: 40px;text-align: center;',
				   'sitename'  => 'width: 60px;text-align: center;',
				   'edit'   => 'width: 60px;text-align: center;');

	$q = mysql_full("select * from site_template where gid>0");
	$g_tmpls = array();// gid => テンプレートID 
	while($t = mysql_fetch_assoc($q)){
		$g_tmpls[$t['gid']] = $t['id'];
	}

	$p_r = mysql_full("select * from page where page.gid>0");
	while($p = mysql_fetch_assoc($p_r)){
		if($g_tmpls[$p['gid']])continue;//既にテンプレートに使われているものを飛ばす
		$edit_links = mkhref(array('s' => "[選択]", 
									'h' => "layout.php?action=edit_temp&gid=$p[gid]&copy=$is_copy"));
		$site_link = mkhref(array('s' => $p['sitename'], 'h' => "/index.php?gid=$p[gid]"));
		$list[] = array('gid' => $p['gid'],
						'sitename' => $site_link,
						'edit' => $edit_links);
	}

	$html = 'テンプレートとして使用するグループページを選択してください。<br>';
	if($is_copy)$html .= '選択されたページのレイアウトをコピーしたグループページを作成します。';
	else $html .= '指定されたページがテンプレートとして使用されます。';
	$html .= create_list($list, $style);
	
	$data = array('title'   => 'テンプレート用ユーザーページ選択',
				  'content' => $html);

	show_input($data);

	exit(0);
	
}

function add_user(){
	$is_copy=$_REQUEST['type']=='copy'?1:0;
	$list = array();
	$list[] = array('uid'    => 'UID',
					'handle' => 'ニックネーム',
					'sitename' => 'ページ名',
					'edit'  => '操作');

	$style = array('uid'     => 'width: 40px;text-align: center;',
				   'handle'   => 'width: 60px;text-align: center;',
				   'sitename'  => 'width: 60px;text-align: center;',
				   'edit'   => 'width: 60px;text-align: center;');

	$q = mysql_full("select * from site_template where uid>0");
	$u_tmpls = array();// uid => テンプレートID 
	while($t = mysql_fetch_assoc($q)){
		$u_tmpls[$t['uid']] = $t['id'];
	}

	$p_r = mysql_full("select * from page inner join user on user.id = page.uid where page.uid>0");
	while($p = mysql_fetch_assoc($p_r)){
		if($u_tmpls[$p['uid']])continue;//既にテンプレートに使われているものを飛ばす
		$edit_links = mkhref(array('s' => "[選択]", 
									'h' => "layout.php?action=edit_temp&uid=$p[uid]&copy=$is_copy"));
		$site_link = mkhref(array('s' => $p['sitename'], 'h' => "/index.php?uid=$p[uid]"));
		$list[] = array('uid' => $p['uid'],
						'handle' => $p['handle'],
						'sitename' => $site_link,
						'edit' => $edit_links);
	}
	
	$html = 'テンプレートとして使用するユーザーページを選択してください。<br>';
	if($is_copy)$html .= '新規ユーザーが作成し、選択されたページのレイアウトをコピーしたマイページを作成します。';
	else $html .= '指定されたページがテンプレートとして使用されます。';
	$html .= create_list($list, $style);
	
	$data = array('title'   => 'テンプレート用ユーザーページ選択',
				  'content' => $html);

	show_input($data);

	exit(0);
	
}

function template_list(){
	global $temp_types;
	$u_or_g = array(
				array('uid','マイページ','user','ユーザー'),
				array('gid','グループページ','group','グループ')	
				);
	$tpl_u[] = array('id'    => '',
					'name'  => 'マイページ用標準２カラム',
					'pname' => '',
					'type'  => '制限無し',
					'edit'  => '編集不可');
	$tpl_u[] = array('id'    => '',
					'name'  => 'マイページ用標準３カラム',
					'pname' => '',
					'type'  => '制限無し',
					'edit'  => '編集不可');
	$tpl_g[] = array('id'    => '',
					'name'  => 'グループページ用標準２カラム',
					'pname' => '',
					'type'  => '制限無し',
					'edit'  => '編集不可');
	$tpl_g[] = array('id'    => '',
					'name'  => 'グループページ用標準３カラム',
					'pname' => '',
					'type'  => '制限無し',
					'edit'  => '編集不可');
	$html = '';
	foreach($u_or_g as $bbb){
		list($fi_na,$page_str,$add_arg,$ug_str) = $bbb;
		$p = mysql_full("select st.id, st.name, st.type, page.sitename, page.id as site_id".
						" from site_template as st".
						" inner join page on st.$fi_na = page.$fi_na".
						" where st.$fi_na > 0");
		
		$list = array();
		$list[] = array('id'    => 'ID',
						'name'  => 'テンプレート名',
						'pname' => 'ページ名',
						'type'  => '公開範囲',
						'edit'  => '操作');
	
		$style = array('id'     => 'width: 40px;text-align: center;',
					   'name'   => 'width: 60px;text-align: center;',
					   'pname'  => 'width: 60px;text-align: center;',
					   'type'   => 'width: 60px;text-align: center;',
					   'edit'   => 'width: 60px;text-align: center;');
		if($fi_na == 'uid'){
			$list[] = $tpl_u[0];
			$list[] = $tpl_u[1];
		}else{
			$list[] = $tpl_g[0];
			$list[] = $tpl_g[1];
		}
		while($t = mysql_fetch_assoc($p)){
			$edit_links = mkhref(array('s' => '[レイアウト編集]', 'h' => "/index.php?setting=layout&site_id=$t[site_id]", 't' => '_blank'));
			$edit_links .= mkhref(array('s' => '[基本情報編集]', 'h' => "layout.php?action=edit_temp&id=$t[id]"));
			$edit_links .= mkhref(array('s' => '[削除]', 'h' => "layout.php?action=delete&id=$t[id]"));
			$list[] = array('id'    => $t['id'],
							'name'  => $t['name'],
							'pname' => mkhref(array('s' => $t['sitename'], 'h' => "/index.php?site_id=$t[site_id]", 't' => '_blank')),
							'type'  => $temp_types[$t['type']],
							'edit'  => $edit_links
							);
		}
		
		$html .= "<h3>${page_str}用テンプレート一覧</h3>";
		$html .= create_list($list, $style);
		$html .= '<h4>テンプレート追加</h4>';
		$html .= mkhref(array('s' => "新規${ug_str}作成",'h' => "layout.php?action=edit_temp&u_or_g=$add_arg")).'<br>';
		$html .= mkhref(array('s' => "既存${page_str}を指定",'h' => "layout.php?action=add_$add_arg&type=assign")).'<br>';
		$html .= mkhref(array('s' => "既存${page_str}をコピーして${ug_str}作成",'h' => "layout.php?action=add_$add_arg&type=copy")).'<br>';
	}

	$data = array('title'   => 'テンプレート一覧',
				  'content' => $html);

	show_input($data);

	exit(0);
}
/*
 create table site_template(
     `id` bigint(20) key auto_increment,
     `uid` bigint(20) default 0,
     `gid` bigint(20) default 0,
     `name` text,
     `ord` int(11),
     `type` int(11) default 1
 )ENGINE=MyISAM DEFAULT CHARSET=utf8;
 type:管理者用0 共用1
 */
?>