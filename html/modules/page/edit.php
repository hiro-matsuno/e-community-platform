<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require dirname(__FILE__). '/../../lib.php';

session_start();

$act = $_POST["action"] ? $_POST["action"] : $_GET["action"];

switch ($act) {
	case 'post':
		$content = entry();
		break;
	default:
		$content = print_form();
}

$COMUNI["columns"] = 1;

$data = array(
			space_1 => array(
							array(id => 0, title => 'ページ編集', content => $content)
					)
		);


show_page(0, $data);

exit(0);

/*
 *  
 */
function entry() {
	global $COMUNI;

	$eid = $_POST["eid"];
	$pid = $_POST["pid"];
	$uid = $COMUNI["uid"];
	$gid = $_POST["gid"];

	if (!$pid) {
		die('編集先が不明です。');
	}
	if ($eid) {
		$f = mysql_exec("update page_data set subject = %s, body = %s where id = %s;",
						mysql_str($_POST["subject"]), mysql_str($_POST["body"]),
						mysql_num($eid));
	}
	else {
		$eid = get_seqid();
		$f = mysql_exec("insert into page_data (id, pid, subject, body, initymd)".
						" values (%s, %s, %s, %s, %s);",
						mysql_num($eid), mysql_num($pid),
						mysql_str($_POST["subject"]), mysql_str($_POST["body"]),
						mysql_current_timestamp());
	}

	if (!$f) {
		die(mysql_error());
	}

	set_pmt(array(eid  => $eid,
				  gid  => $gid,
				  name => "pmt_0"));

	if (is_portal($gid)) {
		$rstr  = 'ポータルページへ';
		$rpath = '/index.php';
	}
	else if ($gid > 0) {
		$rstr  = 'グループページへ';
		$rpath = '/group.php?gid='. $gid. '&eid='. $new_id;
	}
	else {
		$rstr  = 'マイページへ';
		$rpath = '/user.php?uid='. $uid. '&eid='. $new_id;
	}

	return <<<__HTML__
<style type="text/css">
.form_table td {
	padding: 4px;
	text-align: left;
}
.form_table th {
	width: 10em;
	background-color: #f1f1f1;
	padding: 4px;
	text-align: left;
}
.input_text {
	border: solid 1px #ffffff;
	font-size: 1.2em;
}
a { font-size: 1.0em; }
</style>
<div style="margin: 10px auto; text-align: center; width: 65%;">
ページの編集が完了しました。<br>
<form action="${rpath}" method="GET">
<input type="submit" value="${rstr}" onClick="location.href='${rpath}'; return false;">
</form>
</div>
__HTML__;
	;
}

function print_form() {
	global $COMUNI;

	$eid = intval($_GET["eid"]);
	$pid = intval($_GET["pid"]);

	if ($pid > 0) {
		$q = mysql_uniq("select * from owner where id = %s", mysql_num($pid));

		if (!$q || !is_owner($pid)) {
			$_SESSION["return"] = CONF_URLBASE. '/modules/page/edit.php?eid='. $eid;
			header('Location: '. CONF_URLBASE. '/login.php');
			exit(0);
//			die('編集先が見つからないか、権限がありません。'. mysql_error());
		}
		if ($q["uid"] == 0 && $q["gid"] == 0) {
			$gid = 0;
		}
		else if ($q["gid"] > 0) {
			$gid = $q["gid"];
		}
		else {
			$gid = '';
		}
	}

/*
 * パーツの設定
 */
	$info = mysql_uniq("select * from page_setting where id = %s",
					   mysql_num($pid));
	if (!$info) {
		$m = mysql_exec("insert into page_setting(id, disp_type) values (%s, %s);",
						mysql_num($pid), mysql_num(1));
		if (!$m) {
			die('編集パーツの設定が不明です。');
		}
		$info = array(id => $pid, title => '', summary => '', disp_type => 1);
	}

	switch ($info["disp_type"]) {
//		case 1:
		case 2:
			break;
		default:
			$data = mysql_uniq("select * from page_data where pid = %s".
							   " order by initymd desc limit 1;", mysql_num($pid));
			if ($data) {
				$eid = $data["id"];
				$subject = $data["subject"];
				$body    = $data["body"];
			}
	}

	$pmt = pmt_form(($eid ? $eid : $pid));

	return <<<__HTML__
<style type="text/css">
.form_table td {
	padding: 4px;
	text-align: left;
	background-color: #ffffff;
}
.form_table th {
	width: 10em;
	background-color: #f4f4f4;
	padding: 4px;
	font-size: 0.9em;
	text-align: center;
}
.input_text {
	border: solid 1px #cccccc;
	font-size: 1.2em;
}
#dated > input {
	border: solid 1px #999;
}
a { font-size: 1.0em; }
</style>
<div style="margin: 10px auto; text-align: center; width: 85%;">
<div style="padding: 10px;">ページの編集</div>
<form action="edit.php" id="input" method="POST">
<input type="hidden" name="action" value="post">
<input type="hidden" name="eid" value="${eid}">
<input type="hidden" name="pid" value="${pid}">
<input type="hidden" name="gid" value="${gid}">

<table class="form_table" style="margin: 0 auto; text-align: center; width: 100%;">
<tr>
<th>題名</th>
<td><input type="text" name="subject" class="input_text" size="42" value="${subject}"></td>
</tr>
<tr>
<th>本文</th>
<td>
  <textarea name="body" id="bcontent">${body}</textarea>
</td>
</tr>

<tr>
<th>公開レベル</th>
<td>${pmt}
</td>
</tr>

</table>
<div style="padding: 10px;"><input type="submit" value="ページを作成"> <input type="submit" value="プレビュー" onClick="return false;"></div>

</form>
</div>
<script type="text/javascript" src="/jquery.FCKEditor.js"></script>
<script type="text/javascript">
	$('#bcontent').fck({ path:'/fckeditor/' });
</script>
__HTML__;
	;
}

$data = array(
			space_1 => array(
							array(id => 12334, title => 'ブログ新規投稿', content => $content)
					)
		);

//$COMUNI_HEAD_JS[] = '/tiny_mce/tiny_mce.js';

$COMUNI["columns"] = 1;
show_page(0, $data);

exit(0);

?>
