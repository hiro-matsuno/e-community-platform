<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require_once dirname(__FILE__). '/../../lib.php';
include_once dirname(__FILE__). '/func.php';

$id  = isset($_REQUEST['eid']) ? intval($_REQUEST['eid']) : 0;

if (!is_owner($id)) {
	error_window('この機能はサイト管理者のみ使用できます。');
	exit(0);
}

$s = mysql_full("select d.* from mod_contact_form_data as d".
				' inner join mod_contact_form_pos as pos on d.id = pos.id'.
				" where d.eid = %s order by pos.position",
				mysql_num($id));

$head = 'No.,日時,';
if ($s) {
	while ($res = mysql_fetch_assoc($s)) {
		$head .= '"'. $res['title']. '",';
	}
}

$csv = array();

$csv[] = $head;

$q = mysql_full('select * from mod_contact_send_data where eid = %s order by updymd',
				mysql_num($id));

if ($q) {
	$i = 1;
	while ($res = mysql_fetch_assoc($q)) {
		$date = date('m月d日 H：i', strtotime($res['updymd']));
		$csv[] = $i. ',"'. $date. '",'. data_split($res['eid'], $res['data']);
		$i++;
	}
}

header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=output.csv");

$output = implode("\n", $csv);
//echo $output;
echo mb_convert_encoding($output, 'SJIS-win', 'UTF-8');

function data_split($id, $data) {
	$value = array();
	$split = '<_'. $id. '_>';
	$array = explode($split, $data);
	foreach ($array as $v) {
		$value[] = '"'. preg_replace('/"/', '""', $v). '"';
	}
	return implode(',', $value);
}

?>
