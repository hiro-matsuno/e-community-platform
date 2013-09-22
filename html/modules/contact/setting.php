<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require dirname(__FILE__). '/../../lib.php';

list($eid, $pid) = get_edit_ids();

switch ((isset($_REQUEST["action"]) ? $_REQUEST["action"] : null)) {
	case 'entry':
		entry_data($eid, $pid);
	break;
	default:
		input_data($eid, $pid);
}

function entry_data($eid = null, $pid = null) {
	global $SYS_FORM;

	$subject = isset($_POST['subject']) ? htmlesc($_POST['subject']) : '';
	$note = isset($_POST['note']) ? $_POST['note'] : '';
	$href = isset($_POST['href']) ? $_POST['href'] : '';
	$mail = isset($_POST['mail']) ? htmlesc($_POST['mail']) : '';
	$css  = isset($_POST['css']) ? $_POST['css'] : '';

	$d = mysql_exec('delete from mod_contact_setting where id = %s', mysql_num($eid));
	$i = mysql_exec('insert into mod_contact_setting'.
					' (id, subject, note, href, mail, css)'.
					' values (%s, %s, %s, %s, %s, %s)',
					mysql_num($eid), mysql_str($subject), mysql_str($note),
					mysql_str($href), mysql_str($mail), mysql_str($css));

	$html = 'お問い合わせフォーム機能設定を完了しました。<br>'.
			'フォームを作成・編集する場合は、パーツの「編集」から行って下さい。';

	$data = array(title   => 'お問い合わせフォーム機能設定',
				  icon    => 'finish',
				  content => $html. create_form_return(array('eid' => $eid, 'href' => home_url($eid))));

	show_input($data);

	exit(0);
}

function input_data($eid = null, $pid = null) {
	global $SYS_FORM, $JQUERY, $COMUNI_HEAD_JS, $COMUNI_HEAD_CSS;

	if ($eid > 0) {
		$d = mysql_uniq("select * from mod_contact_setting where id = %s",
						mysql_num($eid));
	}

	if (!$d) {
		$d = array('href' => defaut_href($eid), 'css' => default_css($eid));
	}

	if (isset($SYS_FORM["cache"])) {
		foreach ($d as $key => $value) {
			if (isset($SYS_FORM['cache'][$key])) {
				$d[$key] = $value;
			}
		}
	}

	$attr = array('name' => 'action', 'value' => 'entry');
	$SYS_FORM['input'][] = array('body' => get_form('hidden', $attr));

	$attr = array('name' => 'subject', 'value' => $d['subject'], 'size' => '64',
				  'bhtml' => '<div>送信されるメールのタイトルを入力して下さい。</div>');
	$SYS_FORM["input"][] = array(title => 'タイトル',
								 name  => 'subject',
								 body  => get_form("text", $attr));

	$attr = array('name' => 'note', 'value' => $d['note'], 'toolbar' => 'Basic',
				  'cols' => 64, 'rows' => 6);

	$SYS_FORM["input"][] = array('title' => '説明',
								 'name'  => 'note',
								 'body'  => get_form("fck", $attr));

	$sub_attr = array('name' => 'href_sample', 'value' => CONF_URLBASE. '/index.php?module=contact&eid='. $eid. '&blk_id='. $eid,
				  	  'size' => 72, 'bhtml' => 'URL: ');

	$attr = array('name' => 'href', 'value' => $d['href'], 'toolbar' => 'Basic',
				  'cols' => 64, 'rows' => 6,
				  'bhtml' => '<div>下記URLがお問い合わせページへのリンクとなります。<br>コピー&ペーストしてリンクを作成してください。'. get_form("text", $sub_attr). '</div>');

	$JQUERY['ready'][] = '$(\'#href_sample\').click(function() { this.select(); });';

	$SYS_FORM["input"][] = array('title' => 'フォームへのリンク',
								 'name'  => 'href',
								 'body'  => get_form("fck", $attr));

	$attr = array('name' => 'mail', 'value' => $d['mail'], 'size' => '64',
				  'ahtml' => '<div style="padding: 3px 0;">お問い合わせは、メッセージボックス及びここで指定したメールアドレスに届きます。<br>複数指定する場合は半角カンマ (,) で区切って下さい。</div>');
	$SYS_FORM["input"][] = array('title' => '送信先メールアドレス',
								 'name'  => 'mail',
								 'body'  => get_form("text", $attr));

	$attr = array('name' => 'css', 'value' => $d['css'], 'height' => '180px');
	$SYS_FORM["input"][] = array(title => 'スタイルシート',
								 name  => 'css',
								 body  => get_form("textarea", $attr));

	$SYS_FORM["action"] = 'setting.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["pmt"]    = false;
	$SYS_FORM["submit"] = '登録';
	$SYS_FORM["cancel"] = '取消';

	$html = create_form(array('eid' => $eid, 'pid' => $pid));

	$data = array(title   => 'お問い合わせフォーム機能設定',
				  icon    => 'write',
				  content => $html);

	show_input($data);

	exit(0);
}

function defaut_href($eid) {
	$url = CONF_URLBASE. '/index.php?module=contact&eid='. $eid. '&blk_id='. $eid;

	return <<<__HTML__
<p style="text-align: center;"><a href="$url">お問い合わせフォームへ</a></p>
__HTML__;
	;
}

function default_css($id = 0) {
	return <<<__CSS__
.mod_contact_${id}_title {
	border-left: solid 5px #ccc;
	border-bottom: solid 1px #ccc;
	padding-left: 3px;
	line-height: 18px;
	font-size: 14px;
	font-weight: normal;
}
.mod_contact_${id}_body {
	padding: 5px;
	margin-bottom: 0.5em;
}
.mod_contact_${id}_body label {
	margin-right: 0.5em;
}
.mod_contact_${id}_comment {
	font-size: 0.9em;
	margin-bottom: 0.4em;
	_width: 100%;
}
.mod_contact_${id}_error {
	background: #cc3333;
	font-size: 0.9em;
	color: #fff;
	padding: 2px;
	_width: 100%;
}
.mod_contact_${id}_required {
	margin-left: 0.5em;
	color: #f00;
	font-size: 0.9em;
	font-weight: normal;
}
#mod_contact_${id}_confirm {
	text-align: center;
	background: #fcfcfc;
	margin: 6px auto;
}
#mod_contact_${id}_form {
	padding: 4px;
}
#mod_contact_${id}_submit {
	text-align: center;
}
.input_body {
	margin: 0;
	padding: 0;
	width: 100%;
}
__CSS__;
	;
}

?>
