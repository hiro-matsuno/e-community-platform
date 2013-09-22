<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require dirname(__FILE__). '/../../lib.php';

admin_check();

/* 振り分け*/
switch ($_REQUEST["action"]) {
	case 'regist':
		regist_data();
	break;
	default:
		input_data();
}

/* 登録*/
function regist_data() {
	global $SYS_FORM;

	$id     = 1;
	$title  = CONF_SITENAME. '利用規約';
	$body   = $_REQUEST['body'];

	$q = mysql_exec('update conf_agreement set'.
					' title = %s, body = %s'.
					' where id = %s',
					mysql_str($title), mysql_str($body),
					mysql_num($id));
					
	if (mysql_affected_rows() > 0) {
		;
	}
	else {
		$q = mysql_exec('insert into conf_agreement '.
					' (title, body, id) values (%s, %s, %s)',
					mysql_str($title), mysql_str($body), mysql_num($id));
	}

	if (!$q) {
		show_error(mysql_error());
	}

	$ref = '/manager/site/agreement.php';

	$html = '編集完了しました。';
	$data = array(title   => '利用規約編集完了',
				  icon    => 'finish',
				  content => $html. create_form_return(array(eid => 0, href => $ref, string => '利用規約編集に戻る',)));

	show_input($data);

	exit(0);
}

function input_data() {
	global $SYS_FORM, $JQUERY, $COMUNI_HEAD_CSSRAW;

	$skin_id = 1;

	$q = mysql_uniq('select * from conf_agreement where id = %s', mysql_num($skin_id));
	if ($q) {
		$title = CONF_SITENAME. '利用規約';
		$body  = $q['body'];
	}
	else {
		$title = CONF_SITENAME. '利用規約';
		$body  = '';
	}

	// hidden:action
	$attr = array(name => 'action', value => 'regist');
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));

	// select:skin;
	$SYS_FORM["input"][] = array(title => 'ページ名',
								 name  => 'title',
								 body  => $title);

	$attr = array(name => 'body', value => $body, height =>'560px', width => '100%');
	$SYS_FORM["input"][] = array(title => '内容',
								 name  => 'body',
								 body  => get_form("fck", $attr));

	$SYS_FORM["action"] = 'agreement.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["submit"] = '設定';
	$SYS_FORM["cancel"] = '戻る';

	$html = create_form(array(eid => 0));

	$data = array(title   => '利用規約の編集',
				  icon    => 'write',
				  content => $html);

	show_input($data);

	exit(0);
}
?>