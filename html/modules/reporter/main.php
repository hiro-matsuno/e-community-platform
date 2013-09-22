<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require_once dirname(__FILE__). '/config.php';
require_once dirname(__FILE__). '/../blog/main.php';

function mod_reporter_main($id) {
	global $SYS_FORM, $SYS_REPORTER, $JQUERY;

	$SYS_REPORTER['auth_mode'] = true;
	load_editcss();

	$data = mod_blog_main($id);

	$f = mysql_uniq("select rb.eid, ra.display".
					" from blog_data as d".
					" inner join reporter_block as rb".
					" on rb.block_id = d.pid".
					" left join reporter_auth as ra".
					" on d.id = ra.id".
					" where d.id = %s",
					mysql_num($id));

	if (!$f) {
		show_error(mysql_error());
	}

	$pid = $f['eid'];
	
	if(!is_owner($pid))show_error("防災webの管理権限がありません");
	
	$option = array(0 => '校正を連絡', 1 => 'この記事を承認');
	$attr = array(name => 'auth_mode', value => 0, option => $option);
	$SYS_FORM["input"][] = array(title => '記事の状態',
								 name  => 'body',
								 body  => get_form("radio", $attr));

	$JQUERY['ready'][] = <<<__SCRIPT__
$('#pmt_0_div').hide();
$('#pmt_0_div').after('<div id="pmt_0_after">校正中は設定しません。</div>');
$('#auth_mode_1').click(function() {
	$('#pmt_0_div').show();
	$('#pmt_0_after').html('');
});
$('#auth_mode_0').click(function() {
	$('#pmt_0_div').hide();
	$('#pmt_0_after').html('校正中は設定しません。');
});
__SCRIPT__;

	// fck:body
	$attr = array(name => 'correct', value => $correct, cols => 64, rows => 7, toolbar => 'Basic');
	$SYS_FORM["input"][] = array(title => '校正の内容',
								 name  => 'correct',
								 body  => get_form("fck", $attr));

	$SYS_FORM["action"] = '/modules/reporter/auth.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["pmt"]     = $id;
	$SYS_FORM["submit"]  = '処理の完了';
	$SYS_FORM["cancel"]  = '前に戻る';

	$data .= create_form(array(eid => $id, pid => $pid));

	return $data;
}

?>
