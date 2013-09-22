<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require_once dirname(__FILE__). '/../../lib.php';

$eid = intval($_REQUEST['eid']);

if (!is_owner($eid)) {
	show_error('編集権限がありません。');
}

switch ((isset($_REQUEST["action"]) ? $_REQUEST["action"] : null)) {
	case 'entry':
		entry_data($eid);
	break;
	default:
		input_data($eid);
}

function entry_data($eid = 0) {
	$title = isset($_POST['title']) ? htmlesc($_POST['title']) : '';
	$type  = isset($_POST['type']) ? htmlesc($_POST['type']) : '';
	$req_check  = isset($_POST['req_check']) ? 1 : 0;
	$comment  = isset($_POST['comment']) ? nl2br(htmlesc($_POST['comment'])) : '';

	$opt_size  = isset($_POST['opt_size']) ? htmlesc($_POST['opt_size']) : 24;
	$opt_line  = isset($_POST['opt_line']) ? htmlesc($_POST['opt_line']) : 4;

	$opt_list  = isset($_POST['opt_list']) ? htmlesc($_POST['opt_list']) : '';

	$opt_list = ereg_replace("\r\n", "-_-", $opt_list);
	$opt_list = ereg_replace("\r", "-_-", $opt_list);
	$opt_list = ereg_replace("\n", "-_-", $opt_list);
	$opt_list = preg_replace('/-_-$/', '', $opt_list);

	if ($type == 'textarea') {
		$opt_size = $opt_line;
	}

	$id = get_seqid();
	$i = mysql_exec('insert into mod_contact_form_data'.
					' (id, eid, type, title, req_check, comment, opt_size, opt_list, def_val)'.
					' values (%s, %s, %s, %s, %s, %s, %s, %s, %s)',
					mysql_num($id), mysql_num($eid), mysql_str($type), 
					mysql_str($title), mysql_num($req_check), mysql_str($comment),
					mysql_str($opt_size), mysql_str($opt_list), mysql_str(''));

	$m = mysql_uniq('select max(position) as max from mod_contact_form_pos where parent = %s', mysql_num($eid));
	$max = $m['max'];

	$d = mysql_exec('delete from mod_contact_form_pos where id = %s', mysql_num($id));
	$i = mysql_exec('insert into mod_contact_form_pos'.
					' (id, position, parent)'.
					' values (%s, %s, %s)',
					mysql_num($id), mysql_num($max + 1), mysql_num($eid));

	$data = array('title'   => '入力項目の編集',
				  'icon'    => 'write',
				  'content' => 'フォームを追加ました。'. reload_form(array('string'=>'了解')));

	show_dialog($data);
}

function input_data($eid = 0) {
	global $SYS_FORM, $JQUERY;

	$attr = array(name => 'action', value => 'entry');
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));

	$attr = array(name => 'title', value => $q['title'], size => 48);
	$SYS_FORM["input"][] = array('title' => '題名', 'body' => get_form("text", $attr));

	$attr = array(name => 'req_check', value => $q['req_check'], option => array(1 => '入力必須項目とする'));
	$SYS_FORM["input"][] = array('body' => get_form("checkbox", $attr));

	$attr = array(name => 'comment', value => $q['comment'], 'height' => '60px');
	$SYS_FORM["input"][] = array('title' => 'コメント', 'body' => get_form("textarea", $attr));

	$option = array('none' => '選択して下さい',
					'text' => 'テキスト１行', 'textarea' => 'テキスト複数行', 'select' => '一つを選択 (リスト)',
					'radio' => '一つを選択 (ラジオボタン)', 'checkbox' => '複数を選択 (チェックボックス)');

	$attr = array('name' => 'type', 'value' => $q['type'], 'option' => $option);
	$SYS_FORM["input"][] = array('title' => 'フォームの種類', 'body' => get_form("select", $attr));

	$JQUERY['ready'][] = <<<__JQUERY__
$('#opt_size_div').hide();
$('#opt_line_div').hide();
$('#opt_list_div').hide();

$('#type').change(function() {
	if (this.value == 'none') { $('#opt_size_div').hide(); $('#opt_line_div').hide(); $('#opt_list_div').hide(); } 
	if (this.value == 'text') { $('#opt_size_div').show(); $('#opt_line_div').hide(); $('#opt_list_div').hide(); }
	if (this.value == 'textarea') { $('#opt_size_div').hide(); $('#opt_line_div').show(); $('#opt_list_div').hide(); }
	if (this.value == 'select') { $('#opt_size_div').hide(); $('#opt_line_div').hide(); $('#opt_list_div').show(); }
	if (this.value == 'radio') { $('#opt_size_div').hide(); $('#opt_line_div').hide(); $('#opt_list_div').show(); }
	if (this.value == 'checkbox') { $('#opt_size_div').hide(); $('#opt_line_div').hide(); $('#opt_list_div').show(); }
});

__JQUERY__;
	;

	$attr = array('name' => 'opt_size', 'value' => 24, 'size' => 4, 'bhtml' => '表示サイズ: ');
	$opt_tag .= '<div id="opt_size_div">'. get_form("text", $attr). '</div>';

	$attr = array('name' => 'opt_line', 'value' => 4, 'size' => 4, 'bhtml' => '表示行: ');
	$opt_tag .= '<div id="opt_line_div">'. get_form("text", $attr). '</div>';

	$attr = array('name' => 'opt_list', 'value' => preg_replace('/-_-/', "\n", $q['opt_list']),
				  'width' => '200px', 'height' => '80px', 'bhtml' => 'リスト (改行で区切って下さい): ');
	$opt_tag .= '<div id="opt_list_div">'. get_form("textarea", $attr). '</div>';

	$SYS_FORM["input"][] = array('title' => 'フォームオプション', 'body' => $opt_tag);

	$SYS_FORM["action"] = 'add.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["submit"]  = '追加';
	$SYS_FORM["cancel"]  = '取消';
//	$SYS_FORM["onCancel"]  = "location.href = 'input.php?eid=". $eid. "'; return false;";
	$SYS_FORM["onCancel"]  = 'parent.tb_remove(); return false;';

	$form_html .= create_form(array(eid => $eid));

	$data = array('title'   => '入力項目の編集',
				  'icon'    => 'write',
				  'content' => $form_html);

	show_dialog($data);
}

function br2nl($string){
	return preg_replace('/\<br(\s*)?\/?\>/i', "\n", $string);
}

?>

