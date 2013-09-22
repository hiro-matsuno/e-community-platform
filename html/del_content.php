<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
 
require_once dirname(__FILE__). '/lib.php';

$tables = array('blog'     => 'blog_data',
				'reporter_new' => 'reporter_new_data',
				'schedule' => 'schedule_data',
				'bosai_web' => 'bosai_web_template',
				'bosai_web_bysite' => 'bosai_web_template_bysite',
				'bosai_web_auth' => 'bosai_web_auth',
				'mailmag' => 'mailmag_data',
				'bbs' => 'mod_bbs_thread',
				'timeline' => 'mod_timeline_data',
				'menu'     => 'menu_data',
				'enquete'   => 'enquete_data',
				'page'     => 'page_data');

$module = htmlspecialchars(strip_tags($_REQUEST["module"]), ENT_QUOTES);
$eid    = intval($_REQUEST["eid"]);

if (!$eid) {
	die('please set eid...');
}
if (!is_owner($eid)) {
	die('You are not owner of '. $eid);
}

if (!isset($tables[$module])) {
	die('no module');
}

if (!isset($_REQUEST["sure"])) {
	$SYS_FORM["action"] = 'del_content.php';
	$SYS_FORM["submit"] = '記事の消去';
	$SYS_FORM["cancel"] = 'キャンセル';
	$SYS_FORM["onCancel"] = 'parent.tb_remove(); return false;';

	$SYS_FORM["input"][] = array(body => get_form("hidden",
												  array(name  => 'module',
														 value => $module)));

	$SYS_FORM["input"][] = array(body => get_form("hidden",
												  array(name  => 'sure',
														 value => 1)));

	$comment = 'この記事を削除してよろしいですか？';
	$data = array(title   => '本当に削除しますか？',
				  icon    => 'warning',
				  content => $comment. create_confirm(array(eid => $eid)));

	show_dialog2($data);

	exit(0);
}

$d = mysql_uniq("select * from ${tables[$module]}".
				" where id = %s", mysql_num($eid));

if (!$d) {
	die($eid. " is not exist.");
}

$q = mysql_exec("delete from ${tables[$module]}".
				" where id = %s", mysql_num($eid));

$module = 'bosai_web_auth';
$q = mysql_exec("delete from ${tables[$module]}".
				" where id = %s", mysql_num($eid));

global $SYS_FORM;

$SYS_FORM["submit"]   = "了解";
$SYS_FORM["onSubmit"] = "parent.tb_remove(); parent.location.reload(); return false;";

$data = array(title   => '記事を削除しました。',
			  icon    => 'finish',
			  content => create_form($param));

show_dialog2($data);

exit(0);

?>
