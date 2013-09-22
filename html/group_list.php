<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require dirname(__FILE__). '/lib.php';
require_once 'Calendar/Month/Weekdays.php';

global $COMUNI, $COMUNI_HEAD_JS, $COMUNI_HEAD_CSS;

session_start();

$gid = intval($_GET["gid"]);

$_SESSION["return"]  = '/group.php?gid='. $gid;
$_SESSION["toppage"] = '/group.php?gid='. $gid;

$public = $COMUNI["is_login"] ? 2 : 1;

$p = mysql_exec("select page.* from page".
				" inner join element on page.id = element.id".
				" left join unit on element.unit = unit.id".
				" where page.gid > 0 and (element.unit < %s or unit.uid = %s)".
				" order by page.updymd desc",
				mysql_num($public), mysql_num($COMUNI["uid"]));

if (!$p) {
	die('グループが見つかりません。');
}

$content = '<h3>グループリスト</h3><ul class="group_list">';
while ($r = mysql_fetch_array($p)) {
	$content .= '<li><a href="/group.php?gid='. $r["gid"]. '">'. $r["sitename"]. '</a></li>';
}
$content .= '</ul>';

$COMUNI["columns"] = 1;
$data = array(
			space_1 => array(
							array(id => 12334, title => 'グループリスト', content => $content)
					)
		);

show_page(0, $data);

?>
