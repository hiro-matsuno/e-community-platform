<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require dirname(__FILE__). '/lib.php';

$_SESSION["return"]  = '/index.php';

$public = $COMUNI["is_login"] ? 2 : 1;

$p = mysql_exec("select page.* from page".
				" inner join element on page.id = element.id".
				" left join unit on element.unit = unit.id".
				" where element.unit < %s or unit.uid = %s;",
				mysql_num($public), mysql_num($COMUNI["uid"]));

if (!$p) {
	die('公開ユーザーが見つかりません。');
}

$content = '<h3>公開ユーザーサイト</h3><ul>';
while ($r = mysql_fetch_array($p)) {
	$content .= '<li><a href="/user.php?uid='. $r["uid"]. '">'. $r["sitename"]. '</a></li>';
}
$content .= '</ul>';

$COMUNI["columns"] = 1;
$data = array(
			space_1 => array(
							array(id => 12334, title => '公開ユーザーサイト', content => $content)
					)
		);

show_page(0, $data);

?>
