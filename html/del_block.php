<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
 
require_once dirname(__FILE__). '/lib.php';

$eid = intval($_REQUEST["eid"]);

if (!$eid) {
	die('please set eid...');
}
if (!is_owner($eid)) {
	die('You are not owner of '. $eid);
}

$d = mysql_uniq("select * from block where id = %s", mysql_num($eid));

if (!$d) {
	die($eid. " is not exist.");
}

if (!isset($_REQUEST["sure"])) {
	$SYS_FORM["action"] = 'del_block.php';
	$SYS_FORM["submit"] = 'パーツ消去';
	$SYS_FORM["cancel"] = 'キャンセル';
	$SYS_FORM["onCancel"] = 'parent.tb_remove(); return false;';

	$SYS_FORM["input"][] = array(body  => get_form("hidden",
												   array(name  => 'sure',
														 value => 1)));

	$comment = null;

	ModuleManager::getInstance()->getModule( $d["module"] )
		->execCallbackFunction( "delete_block_confirm", array( (int)$eid ), $comment );

	if ( null == $comment ) {

		$comment = 'このパーツを削除してよろしいですか？<br>'.
				   '<span style="color:#f00;">パーツを削除すると、この中に登録されているデータを含めてパーツを丸ごと削除します。<br>本当に削除してよろしいですか。?</span>';

	}
	
	$data = array(title   => '本当に削除しますか？',
				  icon    => 'warning',
				  content => $comment. create_confirm(array(eid => $eid)));

	show_dialog2($data);

	exit(0);
}

$q = mysql_exec("delete from block where id = %s", mysql_num($eid));

write_log('[delete block '. $eid. ']'. join("\t", $d));

ModuleManager::getInstance()->getModule( $d["module"] )
	->execCallbackFunction( "delete_block", array( (int)$eid ) );

$message = <<<__CONTENTS__
<script type="text/javascript">parent.jQuery('#box_${eid}').css('display', 'none');</script>
<div id="message">パーツを削除しました。</div>
</div>
__CONTENTS__;
	;

$data = array(title   => 'パーツを削除しました。',
			  icon    => 'finish',
			  content => $message. create_form_remove());

show_dialog($data);

exit(0);

?>
