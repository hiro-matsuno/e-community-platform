<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require dirname(__FILE__). '/../../lib.php';

/* 振り分け*/
$eid = intval($_POST['eid']);

switch ($_REQUEST["action"]) {
	default:
		regist_data($eid);
}

function regist_data($eid = null) {
	global $JQUERY, $SYS_INPUT_SCRIPT;

	$auth_mode = intval($_POST['auth_mode']);
	$correct   = $_POST['correct'];
	if ($correct == '<br />') {
		$correct = '';
	}

	$f = mysql_uniq("select rb.eid, ra.display".
					" from blog_data as d".
					" inner join reporter_block as rb".
					" on rb.block_id = d.pid".
					" left join reporter_auth as ra".
					" on d.id = ra.id".
					" where d.id = %s",
					mysql_num($eid));

	if (!$f) {
		show_error(mysql_error());
	}

	$pid = $f['eid'];

	switch ($auth_mode) {
		case 1:
			$u = mysql_exec('update reporter_auth set display = 2 where id = %s',
							mysql_num($eid));
			set_pmt(array(eid => $eid, gid =>get_gid($eid), name => 'pmt_0', not_owner => 1));
			break;
		default:
			$i = mysql_exec('update reporter_auth set comment = %s, display = 0'.
							' where id = %s',
							mysql_str($correct), mysql_num($eid));
			set_pmt(array(eid => $eid, gid =>get_gid($eid), unit => PMT_CLOSE, not_owner => 1));
	}



	$ref  = '/modules/reporter/edit.php?pid='. $pid;
	$html = '編集完了しました。';
	$data = array(title   => '校正/承認完了',
				  icon    => 'finish',
				  content => $html. create_form_return(array(eid => $eid, href => $ref, string => '一覧に戻る')));


	show_input($data);

	exit(0);
}

?>
