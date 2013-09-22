<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

function mod_contact_create_form($id = 0, $submit = true) {
	$f = mysql_full('select d.*, p.position from mod_contact_form_data as d'.
					' inner join mod_contact_form_pos as p on d.id = p.id'.
					' where d.eid = %s order by p.position',
					mysql_num($id));

	$html .= '<div id="mod_contact_'. $id. '_form">';
	if (isset($_REQUEST['confirm'])) {
		$html .= '<h4 id="mod_contact_'. $id. '_confirm">内容を確認して正しければ「内容を送信」をクリックしてください</h4>';
		$html .= '<form action="'. CONF_URLBASE. '/modules/contact/send.php" method="POST">';
		$html .= get_form("hidden", array('name' => 'eid', 'value' => $id));

		if ($f) {
			while ($res = mysql_fetch_assoc($f)) {
				$html .= mod_contact_add_confirm_data($res);
			}
		}

		if ($submit == true) {
			$onclick = "location.href = '". CONF_URLBASE. '/index.php?module=contact&eid='. $id. "&rewrite=1';";
			$html .= '<div id="mod_contact_'. $id. '_submit">'.
					 '<input type="submit" value="内容を送信"> <input type="reset" value="修正" onClick="'. $onclick. '">'.
					 '</div>';
		}
		$html .= '</form>';
	}
	else {
		$html .= '<form action="'. CONF_URLBASE. '/modules/contact/confirm.php" method="POST">';
		$html .= get_form("hidden", array('name' => 'eid', 'value' => $id));
		$html .= get_form("hidden", array('name' => 'blk_id', 'value' => $id));

		if ($f) {
			while ($res = mysql_fetch_assoc($f)) {
				$html .= mod_contact_add_form($res);
			}
		}

		if ($submit == true) {
			$html .= '<div id="mod_contact_'. $id. '_submit">'.
					 '<input type="submit" value="内容の確認"> <input type="reset" value="書き直し">'.
					 '</div>';
		}
		$html .= '</form>';

		unset_session('/^mod_contact/');
	}
	$html .= '</div>';

	return $html;
}

function mod_contact_add_confirm_data($res = array()) {
	$tag = array();

	$tag[] = '<div class="mod_contact_'. $res['eid']. '_title">'. $res['title']. '</div>';
	$tag[] = '<div class="mod_contact_'. $res['eid']. '_body">';

	switch ($res['type']) {
		case 'text':
		case 'textarea':
		case 'select':
		case 'radio':
			$value = nl2br(htmlesc($_SESSION['mod_contact_data'][$res['position']]));
		break;
		case 'checkbox':
			$value = nl2br(htmlesc(implode(', ', $_SESSION['mod_contact_data'][$res['position']])));
		break;
		default: 
			$value = '';
	}

	$tag[] = '<div class="mod_contact_'. $res['eid']. '_comment">'. $value. '</div>';

	$tag[] = '</div>';

	return implode("\n", $tag);
}

function mod_contact_add_form($res = array()) {
	$tag = array();

	$required = '';
	if ($res['req_check'] > 0) {
		$required = '<span class="mod_contact_'. $res['eid']. '_required">※必須項目</span>';
	}

	$tag[] = '<div class="mod_contact_'. $res['eid']. '_title">'. $res['title']. $required. '</div>';
	$tag[] = '<div class="mod_contact_'. $res['eid']. '_body">';
	$tag[] = '<div class="mod_contact_'. $res['eid']. '_comment">'. $res['comment']. '</div>';

	$value = '';
	if (isset($_REQUEST['rewrite'])) {
		$value = $_SESSION['mod_contact_data'][$res['position']];

		if (isset($_SESSION['mod_contact_error'][$res['position']])) {
			$tag[] = '<div class="mod_contact_'. $res['eid']. '_error">この項目は必須項目です。</div>';
		}
	}

	switch ($res['type']) {
		case 'text':
			$size = isset($res['opt_size']) ? $res['opt_size'] : '24';
			$attr = array('name' => 'q'. $res['eid']. '_'. $res['position'], 'value' => $value, 'size' => $size);
			$tag[] = get_form("text", $attr);
		break;
		case 'textarea':
			$size = isset($res['opt_size']) ? intval($res['opt_size']) : '5';
			$height = $size. 'em';
			$attr = array('name' => 'q'. $res['eid']. '_'. $res['position'], 'value' => $value, 'height' => $height);
			$tag[] = get_form("textarea", $attr);
		break;
		case 'select':
			$option = array('' => '選択して下さい');
			foreach(explode('-_-', $res['opt_list']) as $v) {
				$option[$v] = $v;
			}
			$attr = array('name' => 'q'. $res['eid']. '_'. $res['position'], 'value' => $value, 'option' => $option);
			$tag[] = get_form($res['type'], $attr);
		break;
		case 'radio':
			$option = array();
			foreach(explode('-_-', $res['opt_list']) as $v) {
				$option[$v] = $v;
			}
			$attr = array('name' => 'q'. $res['eid']. '_'. $res['position'], 'value' => $value, 'option' => $option);
			$tag[] = get_form($res['type'], $attr);
		break;
		case 'checkbox':
			foreach(explode('-_-', $res['opt_list']) as $v) {
				$option[$v] = $v;
			}
			$chkval = array();
			foreach ($value as $v) {
				$chkval[$v] = true;
			}
			$attr = array('name' => 'q'. $res['eid']. '_'. $res['position'], 'value' => $chkval, 'option' => $option);
			$tag[] = get_form($res['type'], $attr);
		break;
		default: 
			$tag[] = '<!-- no attr -->';
	}

	$tag[] = '</div>';

	return implode("\n", $tag);
}

?>
