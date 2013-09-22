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
	case 'regist':
		regist_data($eid, $pid);
	default:
		input_data($eid, $pid);
}

/* 登録*/
function regist_data($eid = null, $pid = null) {
	global $SYS_FORM;

	$orders = array();
	$order_data = $_POST['order_data'];

	if ($order_data != '') {
		$order_data = preg_replace('/list_/', '', $order_data);
		$orders = explode(',', $order_data);
		if (count($orders) > 0) {
			$num = 1;
			foreach ($orders as $o) {
				$u = mysql_exec('update bosai_web_template set num = %s'.
								' where id = %s',
								mysql_num($num), mysql_num($o));
				$num++;
			}
			$SYS_FORM["error"]["order"] = '並べ替えを行いました。';
		}
	}

	$ref  = 'template_list.php?pid='. $pid;
	$html = '並び替え完了。';
	$data = array(title   => '並び替え完了。',
				  icon    => 'finish',
				  content => $html. create_form_return(array(eid => $eid, href => $ref)));

	show_input($data);

	exit(0);
}

/* フォーム*/
function input_data($eid = null, $pid = null) {
	global $SYS_FORM, $JQUERY, $COMUNI_HEAD_JS, $COMUNI_HEAD_CSS, $COMUNI_HEAD_CSSRAW;

	$cat = intval($_REQUEST['cat']);

	$c = mysql_uniq('select * from bosai_web_category'.
					' where eid = %s',
					mysql_num($cat));

	$SYS_FORM["head"][] = $c['name']. 'の並び替え';

	// hidden:action
	$attr = array(name => 'action', value => 'regist');
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));

	$f = mysql_full('select d.* from bosai_web_template as d'.
					' where d.pid = %s'.
					' and category = %s'.
					' order by d.num',
					mysql_num($pid), mysql_num($cat));

	if ($f) {
		$body = '<div id="order_wrap"><ul id="order_sort">';
		while ($c = mysql_fetch_array($f)) {
			$body .= '<li id="list_'. $c['id']. '">'. $c['subject']. '</li>';
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

	$SYS_FORM["action"] = 'template_order.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["submit"]  = '設定';
	$SYS_FORM["cancel"]  = '前に戻る';

	$html = create_form(array(eid => $eid, pid => $pid));

	$data = array(title   => '防災ウェブ雛形一覧',
				  icon    => 'write',
				  content => $html);

	show_input($data);

	exit(0);
}

?>
