<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require dirname(__FILE__). '/../../lib.php';

unset_session('/^mod_sites$/');


	if ($COMUNI[is_login]) {
		$u = mysql_uniq("select * from page where uid= %s;", mysql_num($COMUNI["uid"]));

		if (!$u) {
			$mypage_img = '<a href="mypage_profile.php"><img src="image/mypage.png" border="0"></a>';
		}
		else {
			$mypage_img = '<img src="image/mypage2.png" border="0">';
		}

		$content =<<<__HTML__
<style type="text/css">
.form_table td {
	padding: 4px;
	text-align: left;
	background-color: #f4f4f4;
	font-size: 0.9em;
}
.form_table th {
	width: 10em;
	background-color: #ffffff;
	padding: 4px;
	text-align: left;
}
.input_text {
	border: solid 1px #cccccc;
	font-size: 1.2em;
}
a { font-size: 1.0em; }
</style>
<div style="margin: 10px auto; text-align: center; width: 65%;">
ページの種類を選んでください。<br><br>
<form action="regist.php" method="POST">
<input type="hidden" name="action" value="confirm">
<table class="form_table" style="margin: 0 auto; text-align: center; width: 100%;">
<tr>
<td><div style="margin: 8px auto; text-align: center;">${mypage_img}</div>個人でページを作る場合はこちら。<br>ブログの作成や、フレンドリストの管理ができます。</td>
<td><div style="margin: 8px auto; text-align: center;"><a href="group_profile.php"><img src="image/group.png" border="0"></a></div>グループでページを作る場合はこちら。<br>ブログの作成や掲示板、スケジュールの共有もできます。</td>
</tr>
</table>
<br>
</form>
</div>
__HTML__;
	}
	else {
		$_SESSION["return"] = CONF_URLBASE. '/manager/site/select.php';

		header('Location: '. CONF_URLBASE. '/login.php');
		exit(0);
	}

$data = array(
			space_1 => array(
							array(id => 12334, title => 'ページを作る', content => $content)
					)
		);


$COMUNI["columns"] = 1;
show_page(0, $data);

exit(0);
?>
