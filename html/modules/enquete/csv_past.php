<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require_once dirname(__FILE__). '/../../lib.php';
require_once dirname(__FILE__). '/config.php';
include_once dirname(__FILE__). '/func.php';
//include_once dirname(__FILE__). '/php-ofc-library/open-flash-chart.php';
ini_set('display_errors', 0);

global $COMUNI_DEBUG, $JQUERY, $COMUNI_TPATH;

$id  = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
$past_id  = isset($_REQUEST['past_id']) ? intval($_REQUEST['past_id']) : 0;

if (!is_owner($id)) {
	error_window('この機能はサイト管理者のみ使用できます。');
	exit(0);
}

$q = mysql_full('select * from enquete_form_data'.
				' where eid = %s order by num',
				mysql_num($id));

$head = 'No.,日時,回答ユーザー,';
if ($q) {
	while ($res = mysql_fetch_assoc($q)) {
		$head .= '"'. $res['title']. '",';
	}
}

$csv[] = $head;

$c = mysql_full('select * from mod_enquete_csv_past where eid = %s and past_id = %s'.
				' order by initymd desc', mysql_num($id), mysql_num($past_id));

if ($c) {
	$i = 1;
	while ($res = mysql_fetch_assoc($c)) {
		$date = date('m月d日 H：i', strtotime($res['initymd']));
		$csv[] = $i. ', "'. $date. '",'. $res['data'];
		$i++;
	}
}

header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=output.csv");

$output = implode("\n", $csv);
//echo $output;
echo mb_convert_encoding($output, 'SJIS-win', 'UTF-8');

?>
