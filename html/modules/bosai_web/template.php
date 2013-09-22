<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require_once dirname(__FILE__). '/../../lib.php';

session_start();

/* 振り分け*/
list($eid, $pid) = get_edit_ids();

if($eid and !is_owner($eid,80))show_error('権限がありません');
if($pid and !is_owner($pid,80))show_error('権限がありません');

//var_dump($_SESSION);
switch ($_REQUEST["action"]) {
	case 'regist':
		regist_data($eid, $pid);
	default:
		input_data($eid, $pid);
}

/* 登録*/
function regist_data($eid = null, $pid = null) {
	global $SYS_FORM;

	$category = intval($_POST['category']);
	$target_s = $_POST['target_s'];
	$subject  = $_POST['subject'];
	$body     = $_POST['body'];
	$order    = $_POST['order'];
	$initymd  = date('Y-m-d H:i:s');

	if ($category == 0) {
		$SYS_FORM['error']['category'] = '時期が未登録です。';
		return;
	}

	/* 記事の登録 */
	if ($eid == 0) {
		$eid = get_seqid();

		$q = mysql_exec("insert into bosai_web_template".
						" (id, pid, num, category, subject, body, initymd)".
					" values(%s, %s, %s, %s, %s, %s, %s)",
					mysql_num($eid), mysql_num($pid), mysql_num(0), 
					mysql_num($category), mysql_str($subject), mysql_str($body), mysql_str($initymd));
	}
	else {
		$q = mysql_exec('update bosai_web_template set'.
						' num = %s, category = %s, subject = %s, body = %s, initymd = %s'.
						' where id = %s',
						mysql_num(0), mysql_num($category),
						mysql_str($subject), mysql_str($body), mysql_str($initymd),
						mysql_num($eid));
	}

	set_pmt(array(eid => $eid, gid => get_gid($pid), unit => PMT_PUBLIC));

	/* ターゲットの登録 */
	if (is_array($target_s)) {
		$d = mysql_exec('delete from bosai_web_template_rel'.
						' where eid = %s',
						mysql_num($eid));
		foreach ($target_s as $t) {
			if ($t == 0) {
				continue;
			}
			$i = mysql_exec('insert into bosai_web_template_rel'.
							' (eid, site_id) values (%s, %s)',
							mysql_num($eid), mysql_num($t));
		}
	}

	/* 並び順 */
	if (is_numeric($order)) {
		$q = mysql_uniq('select * bosai_web_template where id = %s',
						mysql_num($order));
		if ($q) {
			$num = $q['num'];
		}
	}
	else if ($order == 'last') {
		$q = mysql_uniq('select max(d.num) from bosai_web_template as d'.
						' where d.pid = %s',
						mysql_num($pid));
		if ($q) {
			$num = $q['max(d.num)'] + 1;
		}
		else {
			$num = 0;
		}
	}
	else {
		//直す　すでに登録されている同じパーツ用のテンプレートのnumをインクリメント
		$num = 0;
	}

	$o = mysql_full('select d.*, el.pid from bosai_web_template as d'.
					' where d.pid = %s',
					mysql_num($pid));

	if ($o) {
		$inc = false;
		while ($r = mysql_fetch_array($o)) {
			if ($r['id'] == $eid) {
				$ins_num = $num;
			}
			else {
				if ($r['num'] == $num) {
					$inc = true;
				}
			}
			if ($inc == true) {
				$ins_num = $r['num'] + 1;
			}
			$u = mysql_exec('update bosai_web_template set num = %s'.
							' where id = %s',
							mysql_num($ins_num), mysql_num($r['id']));
		}
	}

	$ref  = '/modules/bosai_web/template_list.php?pid='. $pid;
	$html = '登録完了。';
	$string = '雛形一覧';
	$data = array(title   => '防災ウェブ基本設定完了',
				  icon    => 'finish',
				  content => $html. create_form_return(array(eid => $eid, href => $ref, string => $string)));

	show_input($data);

	exit(0);
}

/* フォーム*/
function input_data($eid = null, $pid = null) {
	global $SYS_FORM, $JQUERY, $COMUNI_HEAD_JS, $COMUNI_HEAD_CSS;

	if ($eid > 0) {
		$d = mysql_uniq("select * from bosai_web_template where id = %s",
						mysql_num($eid));
	}
	// Y-m-d H:i:s
	if ($d) {
		$pid         = $d['pid'];

		$category    = $d['category'];
		$subject     = $d["subject"];
		$body        = $d["body"];

		$a = mysql_full('select * from bosai_web_template_rel where eid = %s',
						mysql_num($eid));
		if ($a) {
			while ($r = mysql_fetch_array($a)) {
				$target_s[$r['site_id']] = true;
			}
		}
	}
	else {
		$subject     = '';
		$body        = '';
		$target_s    = array();
	}

	// hidden:action
	$attr = array(name => 'action', value => 'regist');
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));

	$f = mysql_full('select d.* from bosai_web_category as d'.
					' where d.pid = %s'.
					' order by d.num',
					mysql_num($pid));

	if ($f) {
		while ($c = mysql_fetch_array($f)) {
			$option[$c['eid']] = $c['name'];
		}
	}
	else {
		$option[0] = '時期が未登録です。';
	}

	$ahtml = '<br>'. make_href('時期の編集&raquo;', 'category.php?pid='. $pid, true);
	$attr = array(name => 'category', value => $category, option => $option, ahtml => $ahtml);
	$SYS_FORM["input"][] = array(title => '時期',
								 name  => 'category',
								 body  => get_form("select", $attr));

	// checkbox:sites
	$JQUERY['ready'][] = <<<_JQ_
$('#target_s_0').click(function() {
	if ($('#target_s_0').attr('checked') == true) {
		$('.input_form').find(".target_s_class").attr('checked', true);
	}
	else {
		$('.input_form').find("input[@type='checkbox']").attr('checked', false);
	}
});
_JQ_;

	$option = array('0' => '全ての投稿者');
	$a = mysql_full('select * from bosai_web_block where eid = %s',
					mysql_num($pid));
	if ($a) {
		while ($r = mysql_fetch_array($a)) {
			$option[$r['site_id']] = get_site_name($r['site_id']);
		}
	}
	$attr = array(name => 'target_s', value => $target_s, option => $option, break_num => 1);
	$SYS_FORM["input"][] = array(title => 'この項目を表示する投稿先',
								 name  => 'target_g',
								 body  => get_form("checkbox", $attr));

	$attr = array(name => 'subject', value => $subject, size => 50);
	$SYS_FORM["input"][] = array(title => 'タイトル',
								 name  => 'subject',
								 body  => get_form("text", $attr));

	$attr = array(name => 'body', value => $body, size => 50);
	$SYS_FORM["input"][] = array(title => '雛形文',
								 name  => 'body',
								 body  => get_form("fck", $attr));

	$option = array();
	$option['top']  = '先頭';
	$option['last'] = '最後';
	$attr = array(name => 'order', value => 'last', option => $option, break_num => 1);
	$SYS_FORM["input"][] = array(title => '表示順',
								 name  => 'order',
								 body  => get_form("radio", $attr));

	$SYS_FORM["action"] = 'template.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["submit"]  = '設定';
	$SYS_FORM["cancel"]  = '前に戻る';

	$html = create_form(array(eid => $eid, pid => $pid));

	$data = array(title   => '防災ウェブ基本設定',
				  icon    => 'write',
				  content => $html);

	show_input($data);

	exit(0);
}

?>
