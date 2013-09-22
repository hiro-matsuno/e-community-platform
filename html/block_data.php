<?php
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
 
header("Content-Type: text/xml; charset=utf-8");
header("Pragma: no-cache");

require dirname(__FILE__). '/lib.php';


$blk_id = isset($_GET["blk_id"]) ? intval($_GET["blk_id"]) : null;
if(!$blk_id)send_error(1,'パーツidが指定されていません');

$blk_info = mysql_uniq("select * from block where id=%s",mysql_num($blk_id));
if(!$blk_info)send_error(2,'指定されたパーツidは無効です');

//--
if(!check_pmt($blk_id)or!check_pmt($blk_info['pid']))send_error(3,'パーツの表示権限がありません');

include_once dirname(__FILE__). '/modules/'. $blk_info["module"]. '/block.php';

$func_name = 'mod_'. $blk_info["module"]. '_block';

if (function_exists($func_name)) {
	$block_data = call_user_func_array($func_name, array($blk_id));

	if (is_array($block_data)) {
		$block_content = $block_data["content"];
	}
	else {
		$block_content = $block_data;
	}
}

$content = htmlspecialchars($block_content);
print <<<EOD
<?xml version="1.0" encoding="UTF-8" ?>
<block_data>
<error>0</error>
<data>
$content
</data>
</block_data>
EOD;

function send_error($code,$desc){
	$description = htmlspecialchars($desc);
	print <<<EOD
<?xml version="1.0" encoding="UTF-8" ?>
<block_data>
<error>$code</error>
<description>
$description
</description>
</block_data>
EOD;
exit(0);
}
?>
