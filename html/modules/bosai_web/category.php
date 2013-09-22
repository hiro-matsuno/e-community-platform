<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require_once dirname(__FILE__). '/../../lib.php';

/* 振り分け*/
list($eid, $pid) = get_edit_ids();

//var_dump($_SESSION);
switch ($_REQUEST["action"]) {
	case 'order':
		order_data($eid, $pid);
		break;
	case 'regist':
		regist_data($eid, $pid);
	default:
		input_data($eid, $pid);
}

function order_data($eid = null, $pid = null) {
	echo 'a';
}

/* 登録*/
function regist_data($eid = null, $pid = null) {
	global $SYS_FORM;

	$SYS_FORM["cache"]["name"] = htmlesc(trim($_POST['name']));

	if (!$SYS_FORM["cache"]["name"]) {
		$orders = array();
		$order_data = $_POST['order_data'];

		if ($order_data != '') {
			$order_data = preg_replace('/list_/', '', $order_data);
			$orders = explode(',', $order_data);
			if (count($orders) > 0) {
				$num = 1;
				foreach ($orders as $o) {
					$u = mysql_exec('update bosai_web_category set num = %s'.
									' where eid = %s',
									mysql_num($num), mysql_num($o));
					$num++;
				}
				$SYS_FORM["error"]["order"] = '並べ替えを行いました。';
				return;
			}
		}
	}

	// 入力エラーチェック
	if (!$SYS_FORM["cache"]["name"] || $SYS_FORM["cache"]["name"] == '<br />') {
		$SYS_FORM["error"]["name"] = '分類名称を入力して下さい。';
	}
	if ($SYS_FORM["error"]) {
		return;
	}

	$f = mysql_uniq('select max(d.num) from bosai_web_category as d'.
					' where d.pid = %s',
					mysql_num($pid));

	$name = $SYS_FORM["cache"]["name"];
	$num  = $f['max(d.num)'] + 1;

	if ($eid == 0) {
		$eid = get_seqid();
		$q = mysql_exec("insert into bosai_web_category".
						" (eid, pid, num, name)".
						" values(%s, %s, %s, %s)",
						mysql_num($eid), mysql_num($pid), mysql_num($num), mysql_str($name));
	}
	else {
		//$pid = get_pid($eid);
		$q = mysql_exec("update bosai_web_category set name = %s".
						" where eid = %s",
						mysql_str($name), mysql_num($eid));
		$p = mysql_uniq("select * from bosai_web_category where eid = %s",
						mysql_num($eid));
		$pid = $p['pid'];
	}
	if (!$q) {
		show_error('登録に失敗しました。'. mysql_error());
	}
	set_pmt(array(eid => $eid, gid =>get_gid($pid), unit => PMT_PUBLIC));

	//$ref  = 'category.php?pid='. $pid;
	$html = '分類追加。';
	$string = '閉じる';
	$data = array(title   => '分類追加',
				  icon    => 'finish',
	//			  content => $html. return_dialog(array(eid => $eid, href => $ref, string => $string)));
				  content => $html.reload_form(array(string => $string)));

	show_dialog2($data);

	exit(0);
}

/* フォーム*/
function input_data($eid = null, $pid = null) {
	global $SYS_FORM, $JQUERY, $COMUNI_HEAD_JS, $COMUNI_HEAD_CSS, $COMUNI_HEAD_CSSRAW;

	if (isset($SYS_FORM["cache"])) {
		$name = $SYS_FORM["cache"]['name'];
	}
	else {
		$name = '';
	}

	$SYS_FORM["head"][] = '追加と並び変更は同時に行えません。';

	// hidden:action
	$attr = array(name => 'action', value => 'regist');
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));

	$attr = array(name => 'name', value => $name, size => 24);
	$SYS_FORM["input"][] = array(title => '分類の追加',
								 name  => 'name',
								 body  => get_form("text", $attr));

	$f = mysql_full('select d.* from bosai_web_category as d'.
					' where d.pid = %s'.
					' order by d.num',
					mysql_num($pid));

	if ($f) {
		$body = '<div id="order_wrap"><ul id="order_sort">';
		while ($c = mysql_fetch_array($f)) {
			$body .= '<li id="list_'. $c['eid']. '">'. $c['name']. '</li>';
		}
		$body .= '</ul></div>';
		$SYS_FORM["input"][] = array(title => '並び替え',
									 name  => 'order',
									 body  => $body);

		$attr = array(name => 'order_data', value => '', size => 24);
		$SYS_FORM["input"][] = array(body  => get_form("hidden", $attr));

		$COMUNI_HEAD_CSSRAW[] = <<<__CSSRAW__
#order_wrap {
	width: 100%;
	margin: 0;
	padding: 3px;
}
#order_sort {
	list-style: none;
}
#order_sort li {
	width: 80%;
	cursor: move;
	margin: 4px 0;
	padding: 3px;
	border: dashed 1px #cfcfcf;
	background-color: #ffffff;
}
__CSSRAW__;
		;
		$JQUERY['ready'][] = <<<__JQRYCODE__
$("#order_sort").sortable({ 
	items: "li",
	stop : function(){
		var data=[];
		$("li", "#order_sort").each(function(i,v){
			data.push(v.id);
		});
		$('#order_data').val(data.toString());
	}
});
__JQRYCODE__;
		;
	}

	$SYS_FORM["action"] = 'category.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["submit"]  = '設定';
	$SYS_FORM["cancel"]  = '前に戻る';

	$html = create_form(array(eid => $eid, pid => $pid));

	$data = array(title   => '分類設定',
				  icon    => 'write',
				  content => $html);

	show_dialog2($data);

	exit(0);
}

?>
