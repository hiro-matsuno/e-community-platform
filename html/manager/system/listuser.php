<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require dirname(__FILE__). '/../../lib.php';
require dirname(__FILE__). '/../../regist_lib.php';

admin_check();

$act = isset($_REQUEST["act"]) ? $_REQUEST["act"] : '';

switch ($act) {
	case 'regist':
		regist_user();
		break;
	case 'edit':
		edit_user();
		break;
	case 'view':
		view_user();
		break;
	case 'en':
		enable_user();
		break;
	case 'dis':
		disable_user();
		break;
	case 'del':
		del_user();
		break;
	default:
		;
}

print_list();

function regist_user($id = 0){
	$id   = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
	if ($id == 0) {
		return;
	}

	$handle = $_REQUEST['handle'];
	$email   = $_REQUEST['email'];
	$name   = $_REQUEST['name'];
	$name_kana = $_REQUEST['name_kana'];
	$zip    = $_REQUEST['zip'];
	$address = $_REQUEST['address'];
	$tel    = $_REQUEST['tel'];

	if(mysql_uniq('select * from user where handle=%s and id <> %s',mysql_str($handle),mysql_num($id))){
		show_error("ニックネーム'$handle'は既に使用されています。");
	}
	if(mysql_uniq('select * from user where email=%s and id <> %s',mysql_str($email),mysql_num($id))){
		show_error("メールアドレス'$email'は既に他の人のメールアドレスとして登録されています。");
	}

	$q = mysql_uniq('update user set user.handle=%s, user.email = %s'.
					' where user.id = %s',
	mysql_str($handle),mysql_str($email),mysql_num($id));
/*
	//プロフィールのデータが用意されていなければ作る
	$q = mysql_uniq('select id from profile_data where uid=%s',mysql_num($id));
	if(!$q){
		$new_id = get_seqid();
		mysql_exec('insert into profile_data (id,uid) values (%s,%s)',mysql_num($id),mysql_num($new_id));
		mysql_exec('insert into profile_pmt (id) values (%s)',mysql_num($new_id));
	}

	$q = mysql_uniq('update profile_data set'.
					' profile_data.name = %s, profile_data.name_kana = %s,'.
					' profile_data.zip = %s, profile_data.address = %s, profile_data.tel = %s'.
					' where uid = %s',
	mysql_str($name),mysql_str($name_kana),
	mysql_str($zip),mysql_str($address),mysql_str($tel),mysql_num($id));
*/
	foreach($_REQUEST['add_form'] as $req_id => $value){
		if(!$value)continue;
		
		if(is_array($value))$value = implode("\n",$value);
		else $value = str_replace("\r","\n",str_replace("\r\n","\n",$value));
		mysql_exec('delete from prof_add_data where uid=%s and req_id=%s',
					mysql_num($id),mysql_num($req_id));
		mysql_exec('insert into prof_add_data (uid,req_id,data) values (%s,%s,%s)',
					mysql_num($id),mysql_num($req_id),mysql_str($value));
	}

	
	view_user($id);
}
function edit_user($id = 0) {
	global $COMUNI_HEAD_CSSRAW;

	$id   = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
	if ($id == 0) {
		return;
	}

	$q = mysql_uniq('select * from user where id = %s',
	mysql_num($id));
/*
	$i = mysql_uniq('select * from profile_data where uid = %s',
	mysql_num($id));
*/
	$add_items = regist_data_get_reqdata($id); 

	$add_form = regist_form($add_items);

	$date = date('Y-m-d H:i:s', tm2time($q['initymd']));

	$COMUNI_HEAD_CSSRAW[] = <<<__CSS__
#view_detail {
	border-collapse: collapse;
	border: solid 3px #c4cdd9;
	margin: 0 auto;
	width: 100%;
	margin-bottom: 10px;
}
#view_detail th {
	width: 25%;
	border: solid 1px #c4cdd9;
	background: #ececec;
	padding: 3px;
	text-align: left;
}
#view_detail td {
	border: solid 1px #c4cdd9;
	background: #ffffff;
	padding: 3px;
	text-align: left;
}
#view_detail td.nochange{
	background: #ececec;
}
__CSS__;

	$content = <<<__HTML__
<div style="width: 95%; text-align: center; margin: 0 auto;">
<form name='user_conf' method='POST'>
<input type='hidden' name='act' value='regist'>
<input type='hidden' name='id' value='${q['id']}'>
<table id="view_detail">
<tr><th>UID</th><td class='nochange'>${q['id']}</td></tr>
<tr><th>ニックネーム</th><td><input class='input_text' type='text' name='handle' value='${q['handle']}'></td></tr>
<tr><th>メールアドレス</th><td><input class='input_text' type='text' name='email' value='${q['email']}' size=48></td></tr>
<tr><th>登録日</th><td class='nochange'>${date}</td></tr>
<!--
<tr><th>名前（漢字）</th><td><input class='input_text' type='text' name='name' value='${i['name']}'></td></tr>
<tr><th>名前（カナ）</th><td><input class='input_text' type='text' name='name_kana' value='${i['name_kana']}'></td></tr>
<tr><th>住所</th><td>〒<input class='input_text' type='text' name='zip' value='${i['zip']}'><br><input class='input_text' type='text' name='address' value='${i['address']}' size=48></td></tr>
<tr><th>電話番号</th><td><input class='input_text' type='text' name='tel' value='${i['tel']}'></td></tr>
-->
$add_form
</table>
</div>
<div class="input_submit_wrap"><div style="margin: 0px auto; padding: 5px;">
<button onClick="document.user_conf.submit()" class="input_submit">登録</button>
<button onClick="parent.tb_remove(); return false; return false;" class="input_cancel">閉じる</button>
</div>
</form>
</div>
__HTML__;

	$data = array('title'   => 'ユーザー情報の編集',
				  'icon'    => 'write',
				  'content' => $content);

	show_dialog($data);
}

function view_user($id = 0) {
	global $COMUNI_HEAD_CSSRAW;

	$id   = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
	if ($id == 0) {
		return;
	}

	$q = mysql_uniq('select * from user where id = %s',
	mysql_num($id));
/*
	$i = mysql_uniq('select * from profile_data where uid = %s',
	mysql_num($id));
*/	
	$add_items = regist_data_get_reqdata($id);

	$add_recs = '';
	foreach($add_items as $item){
		$title = htmlspecialchars($item['title']);
		$data = (is_array($item['data']) ? implode("\n",$item['data']) : $item['data']);
		$data = str_replace("\n","<br>",htmlspecialchars($data));
		$add_recs .= "<tr><th>$title</th><td>$data</td></tr>\n";
	}

	$date = date('Y-m-d H:i:s', tm2time($q['initymd']));

	$COMUNI_HEAD_CSSRAW[] = <<<__CSS__
#view_detail {
	border-collapse: collapse;
	border: solid 3px #c4cdd9;
	margin: 0 auto;
	width: 100%;
	margin-bottom: 10px;
}
#view_detail th {
	width: 25%;
	border: solid 1px #c4cdd9;
	background: #ececec;
	padding: 3px;
	text-align: left;
}
#view_detail td {
	border: solid 1px #c4cdd9;
	background: #ffffff;
	padding: 3px;
	text-align: left;
}
__CSS__;

	$content = <<<__HTML__
<div style="width: 95%; text-align: center; margin: 0 auto;">
<table id="view_detail">
<tr><th>UID</th><td>${q['id']}</td></tr>
<tr><th>ニックネーム</th><td>${q['handle']}</td></tr>
<tr><th>メールアドレス</th><td>${q['email']}</td></tr>
<tr><th>登録日</th><td>${date}</td></tr>
<!--
<tr><th>名前（漢字）</th><td>${i['name']}</td></tr>
<tr><th>名前（カナ）</th><td>${i['name_kana']}</td></tr>
<tr><th>住所</th><td>〒${i['zip']}<br>${i['address']}</td></tr>
<tr><th>電話番号</th><td>${i['tel']}</td></tr>
-->
$add_recs
</table>
</div>
<div class="input_submit_wrap"><div style="margin: 0px auto; padding: 5px;">
<button onClick="location.href='listuser.php?act=edit&id=${q['id']}&keepThis=true&TB_iframe=true&height=480&width=640'" class="input_submit">編集</button>
<button onClick="parent.tb_remove(); return false; return false;" class="input_cancel">閉じる</button>
</div>
</div>
__HTML__;

	$data = array('title'   => 'ユーザーの詳細',
				  'icon'    => 'notice',
				  'content' => $content);

	show_dialog($data);
}

function enable_user() {
	$id   = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

	if ($id == 0) {
		return;
	}

	$d = mysql_exec('update user set enable = 1 where id = %s',
	mysql_num($id));

	if (!$d) {
		show_error(mysql_error());
	}

	$msg  = '<div style="padding: 0 5px;">ユーザーを復帰しました。</div>';
	$data = array('title'   => 'ユーザー復帰',
				  'content' => $msg. reload_form());

	show_dialog($data);
}

function disable_user() {
	$id   = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

	if ($id == 0) {
		return;
	}

	$d = mysql_exec('update user set enable = -1 where id = %s',
	mysql_num($id));

	if (!$d) {
		show_error(mysql_error());
	}

	$msg  = '<div style="padding: 0 5px;">ユーザーを一時停止しました。</div>';
	$data = array('title'   => 'ユーザー一時停止',
				  'content' => $msg. reload_form());

	show_dialog($data);
}

function del_user() {
	$id   = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
	$sure = isset($_REQUEST['sure']) ? true : false;

	if ($id == 0) {
		return;
	}
	if (!$sure) {
		conf_del($id);
		exit(0);
	}

	$d = mysql_exec('delete from user where id = %s',
	mysql_num($id));

	$d = mysql_exec('delete from page where uid = %s',
	mysql_num($id));

	$d = mysql_exec('delete from group_member where uid = %s',
	mysql_num($id));

	$d = mysql_exec('delete from unit where uid = %s',
	mysql_num($id));

	//	モジュールコールバックを呼び出し.
	ModuleManager::getInstance()
		->execCallbackFunctions( "user_delete", array( $id ) );

	$msg  = '<div style="padding: 0 5px;">ユーザーを削除しました。</div>';
	$data = array('title'   => 'ユーザー削除',
				  'content' => $msg. reload_form());

	show_dialog($data);
}

function conf_del($id = 0) {
	global $SYS_FORM;

	$id   = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
	if ($id == 0) {
		return;
	}

	$q = mysql_uniq('select * from user where id = %s',
	mysql_num($id));

	$SYS_FORM['action'] = 'listuser.php';
	$SYS_FORM['submit'] = 'ユーザーを削除する';
	$SYS_FORM['cancel'] = 'キャンセル';
	$SYS_FORM['onCancel'] = 'parent.tb_remove(); return false;';

	$SYS_FORM['head'][] = 'ユーザー <strong>'. $q['handle']. '</strong> を削除してもよろしいですか？';
	$SYS_FORM['head'][] = '<span style="color: #f00;">この操作は取り消しできません。本当によろしいですか？</span>';

	$SYS_FORM['input'][] = array('title' => '削除するユーザー',
								 'body'  => 'UID: '. $q['id']. '<br>'.
											'ニックネーム: '. $q['handle']. '<br>'.
											'メールアドレス: '. $q['email']. '<br>'.
											'登録日: '. date('Y-m-d H:i:s', tm2time($q['initymd'])));

	$SYS_FORM['input'][] = array('body' => get_form('hidden',
	array('name'  => 'act',
														  'value' => 'del')));
	$SYS_FORM['input'][] = array('body' => get_form('hidden',
	array('name'  => 'id',
														  'value' => $id)));
	$SYS_FORM['input'][] = array('body' => get_form('hidden',
	array('name'  => 'sure',
														  'value' => 1)));

	$data = array('title'   => 'ユーザーの削除',
				  'icon'    => 'warning',
				  'content' => create_confirm());

	show_dialog($data);
}

function print_list() {
	global $user_level;

	$q = mysql_full('select u.*, m.id as mypage_id from user as u'.
					' left join page as m on m.uid = u.id'.
					' order by u.id desc');

	$list = array();
	$list[] = array('dis'     => '',
					'uid'     => 'UID',
					'handle'  => 'ニックネーム',
					'view'     => '詳細',
					'initymd' => '登録日',
					'status'  => '状態',
					'del'     => '');

	$style = array('del'  => 'width: 60px;text-align: center;',
				   'view'  => 'width: 80px;text-align: center;',
				   'dis'  => 'width: 60px;text-align: center;',
				   'uid'  => 'width: 60px;text-align: center;',
				   'initymd' => 'white-space: nowrap;',
				   'status'  => 'width: 60px;text-align: center;');

	if ($q) {
		while ($r = mysql_fetch_array($q)) {
			if (isset($r['mypage_id'])) {
				$handle = mkhref(array('s' => $r['handle'], 'h' => '/user.php?uid='. $r['id'], 't' => '_blank'));
			}
			else {
				$handle = $r['handle'];
			}

			$status = isset($r['enable']) ? intval($r['enable']) : 0;
			switch ($status) {
				case -1:
					$status_str = '<span style="color: #f00;">一時停止</span>';
					$dis_href = mkhref(array('s' => '[再開]', 'h' => 'listuser.php?act=en&id='. $r['id'], 'c' => 'thickbox'));
					break;
				case 1:
					$status_str = '<span style="color: #3747d5;">参加中</span>';
					$dis_href = mkhref(array('s' => '[停止]', 'h' => 'listuser.php?act=dis&id='. $r['id'], 'c' => 'thickbox'));
					break;
				default:
					$t = mysql_uniq('select * from regist_temp where uid = %s;',
					mysql_str($r['id']));
					if ($t) {
						$dis_href = mkhref(array('s' => '[承認]', 'h' => '/regist.php?a='. $t['auth_code'], 't' => '_blank'));
					}
					else {
						$dis_href = mkhref(array('s' => '---'));
					}
					$status_str = '<span style="color: #43bb47;">未承認</span>';
			}

			$list[] = array('dis'     => $dis_href,
							'uid'     => $r['id'],
							'handle'  => $handle,
							'view'    => mkhref(array('s' => '[詳細]', 'h' => 'listuser.php?act=view&id='. $r['id'], 'c' => 'thickbox')).
			mkhref(array('s' => '[編集]', 'h' => 'listuser.php?act=edit&id='. $r['id'], 'c' => 'thickbox')),
							'initymd' => date('Y-m-d H:i:s', tm2time($r['initymd'])),
							'owner'   => $r['handle'],
							'status'  => $status_str,
							'del'     => mkhref(array('s' => '[削除]', 'h' => 'listuser.php?act=del&id='. $r['id'], 'c' => 'thickbox')));
		}
	}

//	$html  = '<div style="padding: 5px;">';
//	$html .= mkhref(array('s' => 'ユーザーを追加する&raquo;', 'h' => 'adduser.php', 'c' => 'add_input'));
//	$html .= '</div>';
	$html = '<ul>';
	$html .= '<li>'.mkhref(array('s' => 'ユーザーの追加&raquo;', 'h' => 'adduser.php'));
	$html .= '<li>'.mkhref(array('s' => '統計情報&raquo;', 'h' => 'statuser.php'));
	$html .= '</ul>';
	
	$html .= create_list($list, $style);

	$data = array('title'   => 'ユーザー一覧',
				  'content' => $html);

	show_input($data);

	exit(0);
}

?>
