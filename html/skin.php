<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require dirname(__FILE__). '/lib.php';

global $SYS_FORM;

$myuiduid   = myuid();

if ($_REQUEST['action'] == 'regist') {
	regist_skin();
}

$arg = array();

$gid = intval($_GET['gid']);
$uid = intval($_GET['uid']);

if ($gid > 0) {
	$eid = get_eid(array(gid => $gid));
}
else {
	$eid = get_eid(array(uid => $uid));
}

//echo 'EID: '. $eid;
list($skin_id, $layout_id) = get_current_skin($eid);

$s = mysql_uniq("select * from theme_skin where id = %s", mysql_num($skin_id));
$l = mysql_uniq("select * from theme_layout where id = %s", mysql_num($layout_id));

$cur_skin_id = $s['id'];
$title   = $s['title'];
$thumb   = $s['thumb'];
$title .= ' ('. $s['var_title']. ') ['. $s['filename']. ']';

$pmt = array();
if ($gid > 0) {
	if (is_portal($gid)) {
		$pmt = array(1, 3, 5, 7);
	}
	else {
		$pmt = array(2, 3, 6, 7);
	}
}
else {
	$pmt = array(4, 5, 6, 7);
}

if (is_admin()) {
	$pmt = array(0, 1, 2, 3, 4, 5, 6, 7);
}

$s = mysql_full('select * from theme_skin as s'.
				' where pmt in %s order by s.updymd desc',
				mysql_numin($pmt));

$skin = array();
$rel_skin = array();
$skin_parent = array();
while ($r = mysql_fetch_array($s)) {
	$skin[$r['id']] = array(filename => $r['filename'],
							title    => $r['title'],
							thumb    => $r['thumb'],
							desc     => $r['description']);
	if ($r['id'] == $r['parent_skin_id']) {
		$rel_skin[$r['id']] = 1;
	}
	$skin_parent[$r['parent_skin_id']][] = array(id    => $r['id'],
												 title => $r['var_title']);
}

$cur_skin_value = $cur_skin_id;

$option = array();
foreach ($skin as $id => $d) {
	$var_opt = array(); $cur_var_id = '';
	if (!isset($rel_skin[$id])) {
		continue;
	}
	foreach ($skin_parent[$id] as $sd) {
		if ($sd['id'] == $cur_skin_id) {
			$cur_var_id = $cur_skin_id;
			$cur_skin_value = $id;
		}
		$var_opt[$sd['id']] = $sd['title'];
	}
	ksort($var_opt);

	$option[$id] = $d['title']. '<br><img src="/skin/t/'. $d['thumb']. '">';
	$option[$id] .= get_form("select",
							 array(name => 'var_opt_'. $id,
							 id    => 'var_opt'. $id,
							 value  => $cur_var_id,
							 option => $var_opt));
}

$SYS_FORM = array();

$attr = array(name => 'action', value => 'regist');
$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));


$cur_skin_html = <<<__HTML__
<div style="padding: 10px;">
<div>${title}</div>
<img src="/skin/t/${thumb}">
</div>
__HTML__;

$attr = array(value => $cur_skin_html);
$SYS_FORM["input"][] = array(title => '現在のスキン', body => get_form("plain", $attr));


$SYS_FORM["input"][] = array(title => 'スキン',
								 name  => 'title',
								 body  => get_form("radio",
												   array(name  => 'select_skin',
												   id    => 'select_skin',
												   value  => $cur_skin_value,
												   option => $option,
												   style => 'float: left; margin: 2px; padding: 2px; width: 30%; border: solid 1px #dfdfdf;')));

$SYS_FORM["action"] = 'skin.php';
$SYS_FORM["method"] = 'POST';

$SYS_FORM["submit"] = '登録';
$SYS_FORM["cancel"] = '取消';

$content .= create_form(array(eid => $eid, pid => 0));

$data = array(title   => 'スキンの編集',
			  icon    => 'write',
			  content => $content);

show_input($data);

exit(0);

/* スキンの変更 */
function regist_skin() {
	$COMUNI_HEAD_JSRAW;
	list($eid, $pid) = get_edit_ids();//eidはページのid
	mysql_exec('lock table theme_skin,page write');
	$parent_id = intval($_REQUEST['select_skin']);

	$skin_id = intval($_REQUEST['var_opt_'. $parent_id]);

	$l = mysql_uniq('select layout_id from theme_skin where id = %s',
					mysql_num($skin_id));

	if ($l) {
		$layout_id = $l['layout_id'];
	}else{
		mysql_exec('unlock tables');
		$COMUNI_HEAD_JSRAW[] = 'alert("指定されたスキンは存在しません。スキンを選びなおしてください")';
		return;
	}

	$p = mysql_exec('update page set skin=%s where id=%s',
					mysql_num($skin_id),mysql_num($eid));
	mysql_exec('unlock tables');
	$html = 'スキンを変更しました。';
	$data = array(title   => 'スキンの編集完了',
				  icon    => 'finish',
				  content => $html. create_form_return(array(eid => $eid, href => home_url($eid))));

	show_input($data);

	exit(0);
}

/* 現在のスキン取得 */
function get_current_skin($eid = null) {
	$s = mysql_uniq('select skin from page where id = %s',
					mysql_num($eid));

	$skin_id = $s['skin'];

	$l = mysql_uniq('select layout_id from theme_skin where id = %s',
					mysql_num($skin_id));

	if ($l) {
		$layout_id = $l['layout_id'];
	}

	return array($skin_id, $layout_id);
}

?>
