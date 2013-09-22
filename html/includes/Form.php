<?php 
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
?>
<?php
//-----------------------------------------------------
// * 確認用フォームの生成
//-----------------------------------------------------
function create_confirm($param = array()) {
	global $SYS_FORM;
	$SYS_FORM["is_confirm"] = true;
	return create_form($param);
}

//-----------------------------------------------------
// * フォームの生成
//-----------------------------------------------------
function create_form($param = array()) {

	//	フォーム固有のIDを設定する.
	$formBuildId = FormBuildId::getFormBuildId();

	global $SYS_FORM;

	$eid = isset($param["eid"]) ? $param["eid"] : 0;
	$pid = isset($param["pid"]) ? $param["pid"] : 0;

	if ($eid) {
		$gid    = get_gid($eid);
		$pmt_id = $eid;
	}
	else if ($pid) {
		$gid = get_gid($pid);
		$pmt_id = $pid;
	}

	if (!$pmt_id) {
		$eid = 0;
		$gid = 0;
	}

	$method = isset($SYS_FORM['method']) ? $SYS_FORM['method'] : 'GET';
	switch (strtolower($method)) {
		case 'post':
			$method = 'POST';
			break;
		default :
			$method = 'GET';
	}
	if (isset($SYS_FORM["enctype"])) {
		$method  = 'POST';
		$enctype = ' enctype="'. $SYS_FORM["enctype"]. '"';
	}
	else {
		$enctype = '';
	}
	if (isset($SYS_FORM["onSubmit"])) {
		$onsubmit = ' onSubmit="'. $SYS_FORM["onSubmit"]. '"';
	}
	else {
		$onsubmit = '';
	}

	$form = '';

	if ($SYS_FORM['head']) {
		foreach ($SYS_FORM["head"] as $h) {
			$form .= '<div class="input_head">'. $h. '</div>';
		}
	}

	$action = isset($SYS_FORM["action"]) ? $SYS_FORM["action"] : '#';

	$form .= '<div class="input_wrap"><div class="input">';
	$form .= '<form class="input_form" action="'. $action.
			 '" method="'. $method. '"'. $enctype. $onsubmit. '>';
	$form .= '<input type="hidden" name="form_build_id" value="'. $formBuildId. '">';
	$form .= '<input type="hidden" name="eid" value="'. $eid. '">';
	$form .= '<input type="hidden" name="gid" value="'. $gid. '">';
	$form .= '<input type="hidden" name="pid" value="'. $pid. '">';

	if (!isset($SYS_FORM["is_confirm"])) {
		if (isset($SYS_FORM["map"])) {
			if (is_array($SYS_FORM["map"])) {
				$SYS_FORM["input"][] = array(title => $SYS_FORM["map"]['title'],
											 body  => map_form($pmt_id, $SYS_FORM["map"]['type']));
			}
			else {
				$SYS_FORM["input"][] = array(title => CONF_MAP_TITLE,
											 body  => map_form($pmt_id));
			}
		}
		if (isset($SYS_FORM["keyword"])) {
			if (is_bool($SYS_FORM["keyword"])) {
				$SYS_FORM["input"][] = array(title => CONF_KEYWORD_TITLE,
											 body  => keyword_form($pmt_id));
			}
			else if (is_numeric($SYS_FORM["keyword"])) {
				$SYS_FORM["input"][] = array(title => CONF_KEYWORD_TITLE,
											 body  => keyword_form($SYS_FORM["keyword"]));
			}
		}
		if (isset($SYS_FORM["pmt"]) && ($SYS_FORM["pmt"] != false)) {
			if (is_numeric($SYS_FORM["pmt"])) {
				$SYS_FORM["input"][] = array(title => CONF_PMT_TITLE,
											 body  => pmt_form($SYS_FORM["pmt"]));
			}
			else {
				$SYS_FORM["input"][] = array(title => CONF_PMT_TITLE,
											 body  => pmt_form($pmt_id));
			}
		}
		if (isset($SYS_FORM["comment"])) {
			if (is_bool($SYS_FORM["comment"]) && $SYS_FORM["comment"]) {
				$SYS_FORM["input"][] = array(title => 'コメント機能',
											 body  => comment_setting($eid));
			}
		}
		if (isset($SYS_FORM["trackback"])) {
			if (is_bool($SYS_FORM["trackback"]) && $SYS_FORM["trackback"]) {
				$SYS_FORM["input"][] = array(title => 'トラックバック機能',
											 body  => trackback_setting($eid));
				$SYS_FORM["input"][] = array(title => 'トラックバック送信先',
											 body  => trackback_url_form($eid));

			}
		}

	}

	foreach ($SYS_FORM["input"] as $input) {
		$title = isset($input["title"]) ? $input["title"] : '';
		$name  = isset($input["name"])  ? $input["name"] : '';
		$body  = $input["body"];

		if ($title != '') {
			if (isset($SYS_FORM["required"][$name])) {
				$form .= '<h3 class="input_title_required">'. $title. '</h3>';
			}
			else {
				$form .= '<h3 class="input_title">'. $title. '</h3>';
			}
		}
		if (isset($SYS_FORM["error"][$name])) {
			$form .= '<div class="input_error">※'.  $SYS_FORM["error"][$name]. '</div>';
		}
		$form .= $body;
		$form .= '<div style="clear: both"></div>';
	}
	$form .= '<div class="input_submit_wrap">'.
			 '<div style="margin: 0px auto; padding: 5px;">'.
			 get_form_submit(array('value'    => $SYS_FORM["submit"],
								   'cancel'   => $SYS_FORM["cancel"],
								   'onCancel' => $SYS_FORM["onCancel"])).
			 '</div></div>';
	$form .= '</form>';
	$form .= '</div></div>';

	return $form;
}

//-----------------------------------------------------
// * フォームの生成 (コメントの閲覧権限)
//-----------------------------------------------------
function comment_setting($eid = 0) {
	$value  = 0;
	$option = array(0 => '全ての人', 1 => '登録ユーザーのみ', 2 => '禁止(管理者にだけ表示)');

	$q = mysql_uniq('select * from comment_allow where eid = %s',
					mysql_num($eid));
	if ($q) {
		$value = $q['unit'];
	}

	$attr = array(name => 'comment_'. $eid,
				  value => $value,
				  option => $option);

	return get_form("radio", $attr);
}

//-----------------------------------------------------
// * フォームの生成 (トラックバックの閲覧権限)
//-----------------------------------------------------
function trackback_setting($eid = 0) {
	$value  = 0;
	$option = array(0 => '受信を許可', 1 => '受信を禁止');

	$q = mysql_uniq('select * from trackback_allow where eid = %s',
					mysql_num($eid));
	if ($q) {
		$value = $q['unit'];
	}

	$attr = array(name => 'trackback_'. $eid,
				 value => $value,
				 option => $option);

	return get_form("radio", $attr);
}

//-----------------------------------------------------
// * フォームの生成
//-----------------------------------------------------
function get_form($type = null, $param = array()) {
	$param["title"] = isset($param["title"]) ? $param["title"] : null;
	$param["value"] = isset($param["value"]) ? $param["value"] : null;
	$param["bhtml"] = isset($param["bhtml"]) ? $param["bhtml"] : null;
	$param["ahtml"] = isset($param["ahtml"]) ? $param["ahtml"] : null;

	$pmt = '';
	if (isset($param["pmt"])) {
		$pmt = ' '. CONF_PMT_TITLE. ' '. pmt_miniform($param["pmt"]);
	}
	if (isset($param["pmt_val"])) {
		$pmt = ' '. CONF_PMT_TITLE. ' '. pmt_miniform_val($param['name'],$param["pmt_val"],$param['gid']);
	}

	$html = '';

	$title = '';
	if ($param["title"]) {
		$title = $param["title"]. ' ';
	}

	switch ($type) {
		case 'hidden':
			if ($param["bhtml"] || $param["ahtml"]) {
				$html .= '<div class="input_body">'. $param["bhtml"]. $param["ahtml"];
			}
			$html .= get_form_hidden($param);
			if ($param["bhtml"] || $param["ahtml"]) {
				$html .= '</div>';
			}
			break;
		case 'plain':
			$html .= '<div class="input_body">'. $param["bhtml"].
					 $param["value"]. $param["ahtml"]. '</div>';
			break;
		case 'file':
			$html .= '<div class="input_body">'. $param["bhtml"].
					 $title. get_form_file($param). $param["ahtml"]. $pmt. '</div>';
			break;
		case 'select':
			$html .= '<div class="input_body">'. $param["bhtml"].
					 $title. get_form_select($param). $param["ahtml"]. $pmt. '</div>';
			break;
		case 'radio':
			$html .= '<div class="input_body">'. $param["bhtml"].
					 $title. get_form_radio($param). $param["ahtml"]. $pmt. '</div>';
			break;
		case 'checkbox':
			$html .= '<div class="input_body">'. $param["bhtml"].
					 $title. get_form_checkbox($param). $param["ahtml"]. $pmt. '</div>';
			break;
		case 'num':
			$html .= '<div class="input_body">'. $param["bhtml"].
					 $title. get_form_num($param). $param["ahtml"]. $pmt. '</div>';
			break;
		case 'sex':
			$html .= '<div class="input_body">'. $param["bhtml"].
					 get_form_sex($param). $param["ahtml"]. $pmt. '</div>';
			break;
		case 'date':
			$html .= '<div class="input_body">'. $param["bhtml"].
					 $title. get_form_datetime($param). $param["ahtml"]. $pmt. '</div>';
			break;
		case 'textarea':
			$html .= '<div class="input_body">'. $param["bhtml"].
					 get_form_textarea($param). $param["ahtml"]. $pmt. '</div>';
			break;
		case 'fck':
			$html .= '<div class="input_body">'. $param["bhtml"].
					 get_form_fck($param). $param["ahtml"]. $pmt. '</div>';
			break;
		case 'password':
			$html .= '<div class="input_body">'. $param["bhtml"].
					 $title. get_form_password($param). $param["ahtml"]. $pmt. '</div>';
			break;
		default:
			$html .= '<div class="input_body">'. $param["bhtml"].
					 $title. get_form_text($param). $param["ahtml"]. $pmt. '</div>';
	}
	return $html;
}

//-----------------------------------------------------
// * $SYS_FORM にフォーム追加 (ファイル参照)
//-----------------------------------------------------
function get_form_file($param = array()) {
	global $SYS_FORM;

	$SYS_FORM["enctype"] = 'multipart/form-data';

	$name  = $param["name"];
	$value = $param["value"] ? $param["value"] : '';
	$size  = $param["size"] ? $param["value"] : '16';

	return "<input type=\"file\" id=\"${name}\" name=\"${name}\" class=\"input_file\" size=\"${size}\" >";
}

//-----------------------------------------------------
// * $SYS_FORM にフォーム追加 (テキスト入力)
//-----------------------------------------------------
function get_form_text($param = array()) {
	$name  = $param["name"];
	$value = isset($param["value"])? $param["value"] : '';
	$size  = $param["size"] ? $param["size"] : '16';
	$style = $param["style"] ? $param["style"] : '';

	return "<input type=\"text\" id=\"${name}\" name=\"${name}\" value=\"${value}\" class=\"input_text\" size=\"${size}\" style=\"${style}\">";
}

//-----------------------------------------------------
// * $SYS_FORM にフォーム追加 (数字入力)
//-----------------------------------------------------
function get_form_num($param = array()) {
	$name  = $param["name"];
	$value = $param["value"] ? $param["value"] : '0';
	$size  = $param["size"] ? $param["size"] : '16';

	return "<input type=\"text\" id=\"${name}\" name=\"${name}\" value=\"${value}\" class=\"input_num\" size=\"${size}\" >";
}

//-----------------------------------------------------
// * $SYS_FORM にフォーム追加 (パスワード入力)
//-----------------------------------------------------
function get_form_password($param = array()) {
	$name  = $param["name"];
	$value = isset($param["value"]) ? $param["value"] : '';
	$size  = $param["size"] ? $param["size"] : '16';

	return "<input type=\"password\" id=\"${name}\" name=\"${name}\" value=\"${value}\" class=\"input_text\" size=\"${size}\" >";
}

//-----------------------------------------------------
// * $SYS_FORM にフォーム追加 (ラジオボタン)
//-----------------------------------------------------
function get_form_radio($param = array()) {
	$name      = $param["name"];
	$id        = ($param["id"] != '')    ? $param["id"] : $name;
	$value     = isset($param["value"]) ? $param["value"] : '';
	$option    = $param["option"] ? $param["option"] : array();
	$split     = isset($param["split"]) ? $param["split"] : '';
	$style     = $param["style"] ? $param["style"] : 'float: left; margin-right: 3px;';
	$break_num = intval($param["break_num"]);

	$tag = array(); $i = 0;
	foreach ($option as $o => $t) {
		if (strval($value) == strval($o)) {
			$tag[] = '<div style="'. $style. '">'.
					 '<input type="radio" id="'. $id. '_'. $i. '" name="'. $name. '" value="'. $o. '" checked="checked"> <label for="'.
					 $id. '_'. $i. '">'. $t. '</label>'.
					 '</div>';
		}
		else {
			$tag[] = '<div style="'. $style. '">'.
					 '<input type="radio" id="'. $id. '_'. $i. '" name="'. $name. '" value="'. $o. '"> <label for="'.
					 $id. '_'. $i. '">'. $t. '</label>'.
					 '</div>';
		}
		$i++;
		if ($break_num > 0) {
			if ($i % $break_num == 0) {
				$tag[] = '<br clear="all">';
			}
		}
	}
	if ($split != '') {
		$split = '<div style="'. $style. '">'. $split. '</div>';
	}

	return join($split, $tag). '<div style="clear: both;"></div>';
}

//-----------------------------------------------------
// * $SYS_FORM にフォーム追加 (チェックボックス)
//-----------------------------------------------------
function get_form_checkbox($param = array()) {
	$name     = $param["name"];
	$id       = ($param["id"] != '')    ? $param["id"] : $name;
	$value    = isset($param["value"]) ? $param["value"] : array();
	$option   = $param["option"];
	$split    = isset($param["split"]) ? $param["split"] : '';
	$break_num = intval($param["break_num"]);

	if (!is_array($value)) {
		$value = array($value => true);
	}

	$tag = array(); $i = 0;
	foreach ($option as $o => $t) {
		if ($value[$o]) {
			$tag[] = '<input type="checkbox" class="'. $id. '_class" id="'. $id. '_'. $i. '" name="'. $name. '[]" value="'. $o. '" checked="checked"> <label for="'.
					$id. '_'. $i. '">'. $t. '</label>';
		}
		else {
			$tag[] = '<input type="checkbox" class="'. $id. '_class" id="'. $id. '_'. $i. '" name="'. $name. '[]" value="'. $o. '"> <label for="'.
					$id. '_'. $i. '">'. $t. '</label>';
		}
		$i++;
		if ($break_num > 0) {
			if ($i % $break_num == 0) {
				$tag[] = '<br clear="all">';
			}
		}
	}

	return join($split, $tag);
}

//-----------------------------------------------------
// * $SYS_FORM にフォーム追加 (セレクトタブ)
//-----------------------------------------------------
function get_form_select($param = array()) {
	$name     = $param["name"];
	$id       = ($param["id"] != '') ? $param["id"] : $name;
	$value    = isset($param["value"]) ? $param["value"] : '';
	$option   = $param["option"];
	$onChange = isset($param["onChange"]) ? $param["onChange"] : '';

	if ($onChange != '') {
		$onChange = ' onChange="'. $onChange. '"';
	}

	$opt_tag = '';
	foreach ($option as $o => $t) {
                if(isset($param['width']))$st = mb_strimwidth($t,0,$param['width
'],'…');
                if($t != $st)$title = "title='$t'";
                else $title = '';

		if (strval($value) == strval($o)) {
			$opt_tag .= '<option value="'. $o. '" selected="selected" '.$title.'>'. $t. "</option>\n";
		}
		else {
			$opt_tag .= '<option value="'. $o. '" '.$title.'>'. $t. "</option>\n";
		}
	}

	return "<select name=\"${name}\" id=\"${id}\"  class=\"input_select\"${onChange}>".
		   $opt_tag. '</select>';
}

//-----------------------------------------------------
// * $SYS_FORM にフォーム追加 (隠し要素)
//-----------------------------------------------------
function get_form_hidden($param = array()) {
	$name  = $param["name"];
	$value = isset($param["value"]) ? $param["value"] : '';

	return "<input type=\"hidden\" id=\"${name}\"  name=\"${name}\" value=\"${value}\">";
}

//-----------------------------------------------------
// * $SYS_FORM にフォーム追加 (日付入力)
//-----------------------------------------------------
function get_form_datetime($param = array()) {
	$size = array('Y' => 5, 'M' => 3, 'D' => '3',
				  'h' => '3', 'm' => '3', 's' => '3');
	$fmt     = array('Y', 'M', 'D', 'h', 'm', 's');
	$tmp_fmt = array('__Y__', '__M__', '__D__', '__h__', '__m__', '__s__');

	$format = str_replace($fmt, $tmp_fmt, $param["format"]);

	$form = '';
	if (isset($param["value"]) and ($param["value"] or $param["value"]===0)) {
		$tm = date('Y/n/j/G/i/s', $param["value"]);
	}
	else if (isset($param["default_value"])) {
		$tm = date('Y/n/j/G/i/s', intval($param["default_value"]));
	}
	else {
		$tm = '/////';
	}

	$date = split('/', $tm);
	$i = 0;
	foreach($size as $s => $v) {
		$input[] = '<input type="text" id="'. $param["name"]. '_'. $s.
				   '" name="'. $param["name"]. '_'. $s.
				   '" size="'. $v. '" value="'. ($date[$i]!='' ? intval($date[$i]) : ''). '" class="input_datetime">';
		$i++;
	}
	$html = str_replace($tmp_fmt, $input, $format);

	return '<div id="'. $param['name']. '" style="display: inline;">'. $html. '</div>';
}

//-----------------------------------------------------
// * $SYS_FORM にフォーム追加 (テキストエリア)
//-----------------------------------------------------
function get_form_textarea($param = array()) {
	$name   = $param["name"];
	$id     = ($param["id"] != '')    ? $param["id"] : $name;
	$value  = isset($param["value"]) ? $param["value"] : '';
	$width  = $param["width"] ? $param["width"] : '100%';
	$height = $param["height"] ? $param["height"] : '80px';

	return <<<__FORM__
<div style="width: 99%;">
<textarea name="${name}" id="${id}" class="input_text" style="width: ${width}; height: ${height};">${value}</textarea>
</div>
__FORM__;
	;
}

//-----------------------------------------------------
// * $SYS_FORM にフォーム追加 (FCKエディター)
//-----------------------------------------------------
function get_form_fck($param = array()) {
	global $COMUNI_HEAD_JS, $COMUNI_FOOT_JS, $COMUNI_FOOT_JSRAW;
	global $SYS_FCK_LOADED;

	if (!$SYS_FCK_LOADED) {
		include_once dirname(__FILE__). "/../fckeditor/fckeditor.php";
		$COMUNI_HEAD_JS[] = '/fckeditor/fckeditor.js';

		$SYS_FCK_LOADED = true;
	}

	$name    = $param["name"];
	$id      = ($param["id"] != '')    ? $param["id"] : $name;
	$value   = isset($param["value"]) ? $param["value"] : '';
	$cols    = isset($param["cols"])   ? intval($param["cols"]) : 64;
	$rows    = isset($param["rows"])   ? intval($param["rows"]) : 8;
	$toolbar = ($param["toolbar"] != '') ? $param["toolbar"] : 'Default';
	$fckConfig = is_array($param["config"]) ? $fckConfig = $param["config"] : $fckConfig = array();

	switch ($toolbar) {
		case 'Basic':
			$height = $rows * 15 + 24;
			break;
		case 'Default':
		default:
			$height = $rows * 40 + 80;
	}

	$oFCKeditor = new FCKeditor($id) ;
	$oFCKeditor->BasePath   = '/fckeditor/';
	$oFCKeditor->Width      = '100%';
	$oFCKeditor->Height     = $height. 'px';
	$oFCKeditor->ToolbarSet = $toolbar;
	$oFCKeditor->Value      = $value;

	if (!isset($fckConfig['FormatOutput'])) $fckConfig['FormatOutput'] = false;
	if (!isset($fckConfig['FormatSource'])) $fckConfig['FormatSource'] = false;
	if (!isset($fckConfig['ImageBrowserURL'])) $fckConfig['ImageBrowserURL'] = '/filebox.php?f='.$name;
	$oFCKeditor->Config     = $fckConfig;

	return $oFCKeditor->CreateHtml();
}

//-----------------------------------------------------
// * $SYS_FORM にフォーム追加 (サブミット)
//-----------------------------------------------------
function get_form_submit($param = array()) {
	global $SYS_SUBMIT_COUNT;

	if (!isset($SYS_SUBMIT_COUNT)) {
		$SYS_SUBMIT_COUNT = 0;
	}
	else {
		$SYS_SUBMIT_COUNT++;
	}

	$name     = isset($param["name"])     ? $param["name"]     : '';
	$id       = isset($param["id"])       ? $param["id"]       : $name;
	$value    = isset($param["value"])    ? $param["value"]    : '登録';
	$cancel   = isset($param["cancel"])   ? $param["cancel"]   : '';
	$oncancel = isset($param["onCancel"]) ? $param["onCancel"] : '';

	$add_style = '';
	if (mb_strlen($value, 'UTF-8') > 9) {
		$w = intval(mb_strlen($value, 'UTF-8')) + 3;
		$add_style = ' style="text-align: center; width: '. $w. '.5em;"';
	}

	if ($id == '') {
		$id = 'submit_'. $SYS_SUBMIT_COUNT;
	}

	if ($cancel != '') {
		if ($oncancel == '') {
			$oncancel = 'history.back();';
		}
		$cancel = ' <button onClick="'. $oncancel. ' return false;" class="input_cancel">'. $cancel. '</button>';
	}

	return <<<__FORM__
<input type="submit" id="${id}" value="${value}" class="input_submit"${add_style}>${cancel}
__FORM__;
	;
}


/**
 * Description of Form
 *
 * @author ikeda
 */
class Form {
    //put your code here
}
?>
