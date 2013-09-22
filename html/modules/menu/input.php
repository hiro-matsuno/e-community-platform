<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require_once dirname(__FILE__). '/../../lib.php';

/* 振り分け*/
list($eid, $pid) = get_edit_ids();

switch ($_REQUEST["action"]) {
	case 'regist':
		regist_data($eid, $pid);
	default:
		input_data($eid, $pid);
}

/* 登録*/
function regist_data($eid = null, $pid = null) {
	global $SYS_FORM;

	$title  = htmlspecialchars($_POST["title"], ENT_QUOTES);
	if (!$title) {
		$SYS_FORM["error"]["title"] = '表示タイトルを入力してください。';
	}

	// TODO あとでURLエンコードしないとアウト
	if (intval($_POST["radio_href"]) == 1) {
		$href = $_POST["href_inside"];
	}
	else {
		$href = $_POST["href_outside"];
	}
	if (!$href) {
		$SYS_FORM["error"]["href"] = 'リンク先を選択または入力してください。';
	}
	$target = htmlspecialchars($_POST["target"], ENT_QUOTES);

	if ($SYS_FORM["error"]) {
		return;
	}

	if ($eid == 0) {
		$eid = get_seqid();

		$h = mysql_uniq("select max(m.hpos) from menu_data as m".
						" where m.pid = %s",
						mysql_num($pid));

		$hpos = intval($h["max(m.hpos)"]) + 1;

		$q = mysql_exec("insert into menu_data(id, pid, title, href, target, hpos)".
					" values(%s, %s, %s, %s, %s, %s)",
					mysql_num($eid), mysql_num($pid), mysql_str($title), mysql_str($href),
					mysql_str($target), mysql_num($hpos));
	}
	else {
		$q = mysql_exec("update menu_data set title = %s, href = %s, target = %s".
						" where id = %s",
						mysql_str($title), mysql_str($href), mysql_str($target), mysql_num($eid));
	}

	if (!$q) {
		show_error('登録に失敗しました。'. mysql_error());
	}

	set_pmt(array(eid => $eid, gid =>get_gid($pid), name => 'pmt_0'));

	$html = '編集完了しました。';
	$data = array(title   => 'メニューの編集完了',
				  icon    => 'finish',
				  content => $html. create_rform(array(eid => $eid, href => home_url($eid))));

	show_input($data);

	exit(0);
}

/* フォーム*/
function input_data($eid = null, $pid = null) {
	global $SYS_FORM, $JQUERY;

	if ($eid > 0) {
		$d = mysql_uniq("select * from menu_data where id = %s".
						" order by hpos", mysql_num($eid));
	}

	if ($d) {
		$eid     = $d["id"];
		$title   = $d["title"];
		$href    = $d["href"];
		$target  = $d["target"];
	}

	$SYS_FORM["input"][] = array(body  => get_form("hidden",
												   array(name  => 'action',
														 value => 'regist')));
	$SYS_FORM["input"][] = array(title => '表示タイトル',
								 name  => 'title',
								 body  => get_form("text",
												   array(name  => 'title',
														 value => $title,
														 size  => 48)));

	if (!$pid) {
		$pid = $d['pid'];
	}

	$b = mysql_uniq("select * from block where id = %s",
					mysql_num($pid));

	$block_id = $b['pid'];

	$q = mysql_full("select * from block where pid = %s",
					mysql_num($block_id));

	$href_inside = '';	$href_outside = '';
	$base_url = home_url($id); $option = array('NULL' => '選択して下さい');
	if ($q) {
		while ($r = mysql_fetch_array($q)) {
			$key = '/index.php?module='. $r["module"]. '&blk_id='. $r["id"];
			if ($key == $href) {
				$href_inside = $href;
			}
			$option[$key] = $r["name"];
		}
	}
	if ($href && !$href_inside) {
		$chk_value = 2;
		$href_outside = $href;
		$JQUERY["ready"][] = '$(\'#href_inside\').attr(\'disabled\', \'disabled\').css("background-color", "#efefef");';
	}
	else {
		$chk_value = 1;
		$JQUERY["ready"][] = '$(\'#href_outside\').attr(\'disabled\', \'disabled\').css("background-color", "#efefef");';
	}

	$radio_href_inside = get_form("radio",
								  array(name  => 'radio_href',
										id    => 'radio_href_inside',
										value  => $chk_value,
										option => array(1 => 'ブロックから選択')));
	$radio_href_outside = get_form("radio",
								  array(name  => 'radio_href',
										id    => 'radio_href_outside',
										value  => $chk_value,
										option => array(2 => '外部URLを入力')));

	$SYS_FORM["input"][] = array(title => 'リンク先',
								 name  => 'href',
								 body  => $radio_href_inside. get_form("select",
												   array(name  => 'href_inside',
														 value => $href_inside,
														 option => $option)).
										  $radio_href_outside. get_form("text",
												   array(name  => 'href_outside',
														 value => $href_outside,
														 size  => 64)));

	$JQUERY["ready"][] = <<<___READY_CODE__
$('#radio_href_inside_0').click(function() {
	$('#href_outside').attr('disabled', 'disabled').css("background-color", "#efefef");
	$('#href_inside').removeAttr('disabled').css("background-color", "#ffffff");
});
$('#radio_href_outside_0').click(function() {
	$('#href_inside').attr('disabled', 'disabled').css("background-color", "#efefef");
	$('#href_outside').removeAttr('disabled').css("background-color", "#ffffff");
});
___READY_CODE__;
	;

	$ahtml = 'オプションでターゲットを設定します。("_blank"など)';
	$SYS_FORM["input"][] = array(title => 'ターゲット',
								 name  => 'target',
								 body  => get_form("text",
												   array(name  => 'target',
														 value => $target,
														 size  => 24,
														 ahtml => $ahtml)));
	$SYS_FORM["action"] = 'input.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["pmt"]    = true;
	$SYS_FORM["submit"] = '登録';
	$SYS_FORM["cancel"] = '取消';

	$html = create_form(array(eid => $eid, pid => $pid));

	$data = array(title   => 'メニューの編集',
				  icon    => 'write',
				  content => $html);

	show_input($data);

	exit(0);
}

?>
