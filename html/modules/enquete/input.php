<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require_once dirname(__FILE__). '/../../lib.php';
include_once dirname(__FILE__). '/func.php';

session_start();


/* ふりわけ。*/
list($eid, $pid) = get_edit_ids();

//var_dump($_SESSION);
switch ($_REQUEST["action"]) {
	case 'regist':
		regist_data($eid, $pid);
	case 'canvas':
		canvas_edit($eid, $pid);
	default:
		input_data($eid, $pid);
}

/* とうろく。*/
function regist_data($eid = null, $pid = null) {
	global $SYS_FORM;

//	echo '<!--';
//	var_dump($_REQUEST);
//	echo '-->';

	$sort_rec_val = preg_replace('/div\[\]=/', '', 'div[]=0&'. $_REQUEST['sort_rec']);

	$sort_rec = explode('&', $sort_rec_val);
//	var_dump($sort_rec);
	$form = array();
	foreach ($_REQUEST as $key => $value) {
		if (preg_match('/^type_(\d)/', $key, $match)) {
			$num = intval($match[1]);
			$form[$num] = array(num       => $num,
								uniq_id   => $_REQUEST['uniqid_'. $num],
								type      => $_REQUEST['type_'. $num],
								title     => $_REQUEST['title_'. $num],
								comment   => $_REQUEST['comment_'. $num],
								req_check => $_REQUEST['req_'. $num],
								admin_only => $_REQUEST['adm_'. $num],
								value     => $_REQUEST['enq_'. $num],
								opt_size  => $_REQUEST['opt_size_'. $num],
								opt_list  => $_REQUEST['opt_list_'. $num]);
		}
	}

	foreach ($sort_rec as $s => $v) {
		$keys_sort[$v] = $s;
	}
//	var_dump($keys_sort);

	$d = mysql_exec('delete from enquete_form_data where eid = %s', $eid);
	$i = 1;
	foreach ($form as $f) {
//		echo $i. '/'. $f['num']. '/'. $keys_sort[$f['num']]. '<br>';
		$opt_list = $f['opt_list'];
		$opt_list = ereg_replace("\r|\n", "", $opt_list);
		$opt_list = preg_replace('/-_-$/', '', $opt_list);

		$q = mysql_exec('insert into enquete_form_data'.
						' (eid, num, uniq_id, type, title, req_check, admin_only, comment, opt_size, opt_list, def_val)'.
						' values(%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)',
						mysql_num($eid),
						mysql_num($keys_sort[$f['num']]), mysql_num($f['uniq_id']),
						mysql_str($f['type']), mysql_str($f['title']),
						mysql_num(intval($f['req_check'])), mysql_num(intval($f['admin_only'])),
						mysql_str($f['comment']),
						mysql_str($f['opt_size']), mysql_str($opt_list),
						mysql_str($f['value']));
		$i++;
	}

	if (intval($_REQUEST['status']) == 1) {
		change_status($eid, $pid);
	}

	$html = '編集完了しました。';
	$data = array(title   => 'アンケートの編集完了',
				  icon    => 'finish',
				  content => $html. create_form_return(array(eid => $eid, href => home_url($eid))));

	show_input($data);

	exit(0);
}

function close_status($eid = null, $pid = null) {
	$d = mysql_exec("delete from enquete_status where pid = %s and eid = %s",
					mysql_num($pid), mysql_num($eid));
}

function change_status($eid = null, $pid = null) {
	if (!$pid) {
		$pid = mod_enquete_get_pid($eid);
	}

	$d = mysql_exec("delete from enquete_status where pid = %s",
					mysql_num($pid));
	$q = mysql_exec("insert into enquete_status (pid, eid) values (%s, %s)",
					mysql_num($pid), mysql_num($eid));
}

/* フォーム作成*/
function canvas_edit($eid = null, $pid = null) {
	global $SYS_FORM, $COMUNI_HEAD_JSRAW, $COMUNI_FOOT_JSRAW, $JQUERY;

	if (!isset($_REQUEST['reload'])) {
		/* to session */
		$SYS_FORM['cache']['subject'] = $_REQUEST['subject'];
		$SYS_FORM['cache']['note']    = $_REQUEST['note'];
		$SYS_FORM['cache']['type']    = $_REQUEST['type'];
		$SYS_FORM['cache']['dup']     = $_REQUEST['dup'];
		$SYS_FORM['cache']['result']  = $_REQUEST['result'];
		$SYS_FORM['cache']['status']  = $_REQUEST['status'];
		$SYS_FORM['cache']['thxmsg']  = $_REQUEST['thxmsg'];
		$SYS_FORM['cache']['app_type'] = $_REQUEST['app_type'];
		$SYS_FORM['cache']['tell_vote'] = $_REQUEST['tell_vote'];

		if ($_REQUEST["start_date"]) {
			$SYS_FORM['cache']['startymd'] = $_REQUEST["start_date"]. ' '.
											 intval($_REQUEST["start_time_h"]). ':'.
											 intval($_REQUEST["start_time_m"]). ':00';
		}
		else {
			$SYS_FORM['cache']['startymd'] = date('Y-m-d H:i:s');
		}
		if ($_REQUEST["end_date"]) {
			$SYS_FORM['cache']['endymd'] = $_REQUEST["end_date"]. ' '.
											 intval($_REQUEST["end_time_h"]). ':'.
											 intval($_REQUEST["end_time_m"]). ':00';
		}

		// 入力エラーチェック(仮)
		if (!$SYS_FORM["cache"]["subject"]) {
			$SYS_FORM["error"]["subject"] = '題名はなにかいれてください。';
		}
		if ($SYS_FORM["error"]) {
			input_data($eid, $pid);
			exit(0);
		}

		$subject = htmlspecialchars($SYS_FORM['cache']['subject'], ENT_QUOTES);
		$note    = $SYS_FORM['cache']['note'];
		$type    = intval($SYS_FORM['cache']['type']);
		$dup     = intval($SYS_FORM['cache']['dup']);
		$result  = intval($SYS_FORM['cache']['result']);
		$status  = intval($SYS_FORM['cache']['status']);
		$thxmsg  = $SYS_FORM['cache']['thxmsg'];

		$app_type = intval($SYS_FORM['cache']['app_type']);
		$tell_vote = intval($SYS_FORM['cache']['tell_vote']);


		if ($eid == 0) {
			$eid = get_seqid();

			$q = mysql_exec("insert into enquete_data".
							" (id, subject, note, type, dup, result, thxmsg, tell_vote, startymd, endymd, initymd)".
							" values(%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
							mysql_num($eid), mysql_str($subject), mysql_str($note),
							mysql_num($type), mysql_num($dup), mysql_num($result), mysql_str($thxmsg),
							mysql_num($tell_vote),
							mysql_str($SYS_FORM["cache"]["startymd"]),
							mysql_str($SYS_FORM["cache"]["endymd"]),
							mysql_current_timestamp());
			if (!$q) {
				show_error('登録エラー'. mysql_error());
			}
			if ($SYS_FORM['cache']['status'] == 1) {
				$attr = array(name => 'status', value => '1');
				$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));
			}

			mod_enquete_set_pid($eid, $pid);

			$u = mysql_exec('insert into mod_enquete_allow (eid, type) values (%s, %s)',
							mysql_num($eid), mysql_num($app_type));

			$new_input = true;
		}
		else {
			$q = mysql_exec("update enquete_data set".
							" subject = %s, note = %s, type = %s, dup = %s, result = %s, thxmsg = %s,".
							" tell_vote = %s,".
							" startymd = %s, endymd = %s".
							" where id = %s",
							mysql_str($subject), mysql_str($note),
							mysql_num($type), mysql_num($dup), mysql_num($result),
							mysql_str($thxmsg), mysql_str($tell_vote),
							mysql_str($SYS_FORM["cache"]["startymd"]),
							mysql_str($SYS_FORM["cache"]["endymd"]),
							mysql_num($eid));
			if (!$q) {
				show_error('登録エラー'. mysql_error());
			}

			$d = mysql_exec('delete from mod_enquete_allow where eid = %s', mysql_num($eid));
			$u = mysql_exec('insert into mod_enquete_allow (eid, type) values (%s, %s)',
							mysql_num($eid), mysql_num($app_type));

			$new_input = false;
		}
		set_keyword($eid);
	//	set_pmt(array(eid => $eid, gid =>get_gid($pid), name => 'pmt_0'));
		set_pmt(array(eid => $eid, gid =>get_gid($pid), 'unit' => PMT_PUBLIC));

		mod_enquete_get_pid($eid, $pid);

		if ($status == 1) {
			change_status($eid, mod_enquete_get_pid($eid));
		}
		else {
			close_status($eid, mod_enquete_get_pid($eid));
		}

		if (!$new_input && (time() - strtotime($SYS_FORM["cache"]["startymd"]) > 0)) {
			$html = '編集完了しました。<br>すでに開始日時を過ぎた項目はフォームの変更はできません。';
			$data = array(title   => 'アンケートの編集完了',
					  icon    => 'finish',
					  content => $html. create_form_return(array(eid => $eid, href => home_url($eid))));

			show_input($data);

			exit(0);
		}
	}

	$q = mysql_full('select * from enquete_form_data where eid = %s'.
					' order by num',
					mysql_num($eid));

	$form = array(); $sort_rec = array();
	if ($q) {
		while ($res = mysql_fetch_array($q)) {
			$value = ''; $option = array();
			switch ($res['type']) {
				case 'hidden':
				case 'text':
				case 'textarea':
					$value = $res['def_val'];
					$size  = $res['opt_size'];
					break;
				case 'radio':
				case 'checkbox':
				case 'select':
					$list = $res['opt_list'];
					break;
				default :
					$value = $res['def_val'];
					$size  = $res['opt_size'];
			}

			$sort_rec[] = 'div[]='. $res['num'];

			$form[] = array('num'      => $res['num'],
							'uniq_id'  => $res['uniq_id'],
							'type'     => $res['type'],
							'title'    => $res['title'],
							'required' => $res['req_check'],
							'admin_only' => $res['admin_only'],
							'comment'  => $res['comment'],
							'default'  => $value,
							'opt_size' => $size,
							'opt_list' => $list);
		}
	}

	// hidden:action
	$attr = array(name => 'action', value => 'regist');
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));

//	$attr = array(name => 'sort_rec', value => join('&', $sort_rec));
	$attr = array(name => 'sort_rec', value => '');
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));

	$option = array('none'     => '追加するフォームの選択',
					'text'     => '文字入力（一行）',
					'textarea' => '文字入力（複数行）',
					'select'   => '一つを選択（リスト）',
					'radio'    => '一つを選択（ラジオボタン）',
					'checkbox' => '複数選択（チェックボックス）');
	$attr = array(name => 'form_select', value => 'none', option => $option);
	$form_html .= '<div style="padding-top: 3px; padding-left: 3px; font-size: 0.9em;">種類</div>'.
				  get_form("select", $attr);

	$attr = array(name => 'c_title', value => '', size => 32);
	$form_html .= '<div style="padding-top: 3px; padding-left: 3px; font-size: 0.9em;">題名</div>'.
				  get_form("text", $attr);

	$attr = array(name => 'req_check', value => '', option => array(1 => '必須項目'));
	$form_html .= get_form("checkbox", $attr);

	$attr = array(name => 'c_note', value => '', size => 48);
	$form_html .= '<div style="padding-top: 3px; padding-left: 3px; font-size: 0.9em;">コメント</div>'.
				  get_form("text", $attr);

	$attr = array(name => 'c_size', value => '', size => 4);
	$form_html .= '<div id="form_option_text" style="display: none;">'.
				  '<div style="padding-top: 3px; padding-left: 3px; font-size: 0.9em;" id="opt_str">入力サイズ</div>'.
					get_form("text", $attr). '</div>';

	$attr = array(name => 'c_list', value => '');
	$form_html .= '<div id="form_option_textarea" style="display: none; width: 350px;">'.
				  '<div style="padding-top: 3px; padding-left: 3px; font-size: 0.9em;">リスト入力 (改行区切り)</div>'.
					get_form("textarea", $attr). '</div>';

	$attr = array(name => 'admin_only', value => '', option => array(1 => 'この項目の投票結果はアンケート作成者だけに表示'));
	$form_html .= get_form("checkbox", $attr);

	$form_html .= '<button id="add_to_canvas" class="input_button" onClick="return false;">フォームを追加</button>';
	$form_html .= '<div style="margin-bottom: 15px;">&nbsp;</div>';


	$SYS_FORM["input"][] = array(title => 'フォーム',
								 name  => 'subject',
								 body  => $form_html);

	$canvas_head  = '<h2>'. $SYS_FORM['cache']['subject']. '</h2>';
	$canvas_head .= '<div>'. $SYS_FORM['cache']['note']. '</div>';

	$SYS_FORM["input"][] = array(title => 'プレビューと初期値の入力 (タイトルをドラッグして並び替えが行えます。)',
								 name  => 'enquete_preview',
								 body  => $canvas_head. '<div id="canvas" style="padding: 15px auto;"></div>');

	$SYS_FORM["action"] = 'input.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["submit"]  = 'アンケートを作成';
	$SYS_FORM["cancel"]  = '取消';
//	$SYS_FORM["onCancel"]  = "location.href = 'input.php?eid=". $eid. "'; return false;";
	$SYS_FORM["onCancel"]  = 'location.href =\''. home_url($eid). '\'; return false;';


	$COMUNI_HEAD_JSRAW[] = <<<__JAVASCRIPT__
var enquete_fnum = 0;

function add_text(r, s, v, t, n, id, a) {
	num = add_fhead(r, t, n, id, a);
	size = jQuery('#c_size').val();
	if (size == '') size = 24;

	val = '';

	if (s != undefined) size = s;
	if (v != undefined) val = v;

	jQuery('#div_' + num).append('<div class="enquete_block" style="margin-bottom: 15px;">'
								 + '<input type="text" name="enq_' + num + '" value="' + val + '" size="'
								 + size + '" class="input_text">'
								 + '</div>');
	jQuery('#div_' + num).append('<input type="hidden" name="type_' + num + '" value="text">');
	jQuery('#div_' + num).append('<input type="hidden" name="opt_size_' + num + '" value="' + size + '">');
}
function add_textarea(r, s, v, t, n, id, a) {
	num = add_fhead(r, t, n, id, a);
	val = '';

	cols = jQuery('#c_size').val();
	if (cols == '') cols = 6;

	if (s != undefined) cols = s;
	if (v != undefined) val  = v;

	jQuery('#div_' + num).append('<div class="enquete_block" style="margin-bottom: 15px;">'
								 + '<textarea name="enq_' + num + '" rows="'
								 + cols
								 + '" style="width: 95%;" class="input_text">' + val + '</textarea>'
								 + '</div>');
	jQuery('#div_' + num).append('<input type="hidden" name="type_' + num + '" value="textarea">');
	jQuery('#div_' + num).append('<input type="hidden" name="opt_size_' + num + '" value="' + cols + '">');
}
function add_select(r, s, v, t, n, id, a) {
	num = add_fhead(r, t, n, id, a);

	if (s != undefined) {
		d = s.split('-_-');
	}
	else {
		d = jQuery('#c_list').val().split(String.fromCharCode(10));
	}

	var option = '<option value="">選択して下さい</option>';
	var list = '';
	for (var i = 0; i < d.length; i++) {
		if (d[i] == '') continue;
		if (v == i) {
			option += '<option value="' + i + '" selected>' + d[i] + '</option>';
		}
		else {
			option += '<option value="' + i + '">' + d[i] + '</option>';
		}
		list += d[i] + '-_-';
	}
	jQuery('#div_' + num).append('<div class="enquete_block" style="margin-bottom: 15px;">'
								 + '<select name="enq_' + num + '">' + option + '</select'
								 + '</div>');
	jQuery('#div_' + num).append('<input type="hidden" name="type_' + num + '" value="select">');
	jQuery('#div_' + num).append('<input type="hidden" name="opt_list_' + num + '" value="' + list + '">');
}
function add_radio(r, s, v, t, n, id, a) {
	num = add_fhead(r, t, n, id, a);

	if (s != undefined) {
		d = s.split('-_-');
	}
	else {
		d = jQuery('#c_list').val().split(String.fromCharCode(10));
	}

	var list = '';
	for (var i = 0; i < d.length; i++) {
		if (d[i] == '') continue;
		if (v == i) {
			jQuery('#div_' + num).append(''
										 + '<input type="radio" id="' + num + '_' + i + '" name="enq_' + num + '" style="margin-left: 15px;">'
										 + '<label for="' + num + '_' + i + '" checked>' + d[i] + '</label>'
										 + '&nbsp;');
		}
		else {
			jQuery('#div_' + num).append(''
										 + '<input type="radio" id="' + num + '_' + i + '" name="enq_' + num + '" style="margin-left: 15px;">'
										 + '<label for="' + num + '_' + i + '">' + d[i] + '</label>'
										 + '&nbsp;');
		}
		list += d[i] + '-_-';
	}
	jQuery('#div_' + num).append('<input type="hidden" name="type_' + num + '" value="radio">');
	jQuery('#div_' + num).append('<input type="hidden" name="opt_list_' + num + '" value="' + list + '">');
}

function add_checkbox(r, s, v, t, n, id, a) {
	num = add_fhead(r, t, n, id, a);
	if (s != undefined) {
		d = s.split('-_-');
	}
	else {
		d = jQuery('#c_list').val().split(String.fromCharCode(10));
	}
	var list = '';
	for (var i = 0; i < d.length; i++) {
		if (d[i] == '') continue;
		if (v == i) {
			jQuery('#div_' + num).append(''
										 + '<input type="checkbox" id="' + num + '_' + i + '" name="enq_' + num + '" style="margin-left: 15px;">'
										 + '<label for="' + num + '_' + i + '" checked>' + d[i] + '</label>'
										 + '&nbsp;');
		}
		else {
			jQuery('#div_' + num).append(''
										 + '<input type="checkbox" id="' + num + '_' + i + '" name="enq_' + num + '" style="margin-left: 15px;">'
										 + '<label for="' + num + '_' + i + '">' + d[i] + '</label>'
										 + '&nbsp;');
		}
		list += d[i] + '-_-';
	}
	jQuery('#div_' + num).append('<input type="hidden" name="type_' + num + '" value="checkbox">');
	jQuery('#div_' + num).append('<input type="hidden" name="opt_list_' + num + '" value="' + list + '">');
}
function del_enq(id) {
	if (confirm('指定したフォームを削除します。よろしいですか？')) {
		jQuery('#' +  id).remove();
	}
}
function edit_enq(id) {
	jQuery('#edit_to_canvas').show();

	jQuery('#c_title').val(jQuery('#title_' + id).val());
	jQuery('#c_note').val(jQuery('#comment_' + id).val());
	if (jQuery('#req_' + id).val() == 1) {
		jQuery('#req_check_0').attr('checked', 'checked');
	}
	else {
		jQuery('#req_check_0').removeAttr('checked');
	}
	if (jQuery('#req_' + id).val() == 1) {
		jQuery('#admin_only_0').attr('checked', 'checked');
	}
	else {
		jQuery('#admin_only_0').removeAttr('checked');
	}
}

function add_fhead(r, t, n, id, a) {
	enquete_fnum++;
	div = 'div_' + enquete_fnum;
	name = 'enq' + enquete_fnum;

	if (t == undefined) t = jQuery('#c_title').val();
	if (n == undefined) n = jQuery('#c_note').val();

	jQuery('#canvas').append('<div style="margin-bottom: 10px;" class="stbl" id="' + div + '"></div>');
	jQuery('#' + div).append('<input type="hidden" id="title_' + enquete_fnum + '" name="title_' + enquete_fnum + '" value="' + t + '">');
	jQuery('#' + div).append('<input type="hidden" id="comment_' + enquete_fnum + '" name="comment_' + enquete_fnum + '" value="' + n + '">');

	if (id == 0 || id == undefined) {
		rdm = 10000 + Math.floor(Math.random()*10000);
		uniqid = '${eid}' + rdm;
//		alert(uniqid);
	}
	else {
		uniqid = id;
	}
	jQuery('#' + div).append('<input type="hidden" id="uniqid_' + enquete_fnum + '" name="uniqid_' + enquete_fnum + '" value="' + uniqid + '">');

	req  = '';
	if (jQuery('#req_check_0').attr('checked') == true || r == 1) {
		req = '&nbsp;<span style="color: #f00; font-size: 0.8em; font-weight: normal;">必須</span>&nbsp;';
		jQuery('#' + div).append('<input type="hidden" id="req_' + enquete_fnum + '" name="req_' + enquete_fnum + '" value="1">');
	}

	adm  = '';
	if (jQuery('#admin_only_0').attr('checked') == true || a == 1) {
		adm = '&nbsp;<span style="color: #f00; font-size: 0.8em; font-weight: normal;">結果非公開</span>&nbsp;';
		jQuery('#' + div).append('<input type="hidden" id="adm_' + enquete_fnum + '" name="adm_' + enquete_fnum + '" value="1">');
	}

	delbtn = ' <button class="delbtn" onClick="del_enq(\'' +  div + '\'); return false;">消</button>';
	editbtn = ' <button class="delbtn" style="color: #000;" onClick="tb_show(\\'編集\\', \\'subedit.php?eid=${eid}&uniqid=' + uniqid + '&keepThis=true&TB_iframe=true&height=480&width=640\\', false); return false;">編集</button> ';

	jQuery('#' + div).append('<h4 class="enquete_title">' + t + req + adm + editbtn + delbtn + '</h4>');
	jQuery('#' + div).append('<div class="enquete_block" style="margin-bottom: 7px;">' + n + '</div>');

	dval = jQuery('#sort_rec').val();
	if (dval == '' || dval == undefined) {
		jQuery('#sort_rec').val('div[]=' + enquete_fnum);
	}
	else {
		jQuery('#sort_rec').val(jQuery('#sort_rec').val() + '&' + 'div[]=' + enquete_fnum);
	}

	jQuery('#canvas').sortable({
		accept : 'stbl',
		handle : '.enquete_title',
		axis : 'y',
		start: function(event, ui) {
			;
		},
		update: function(event, ui) {
			var order = jQuery('#canvas').sortable('serialize');
			jQuery('#sort_rec').val(order);
//			alert(order);
		},
		stop: function(event, ui) {
			;
		}
	});

	return enquete_fnum;
}
__JAVASCRIPT__
	;

	$JQUERY["ready"][] = <<<___READY_CODE__
$('#form_select').change(function() {
	$('#c_title').val('');
	$('#c_size').val('');
	$('#c_list').val('');
	$('#c_note').val('');

	$('#req_check_0').removeAttr('checked');

	val = $('#form_select').val();
	if (val == 'none') {
		$('#form_option_text').css('display', 'none');
		$('#form_option_textarea').css('display', 'block');
	}
	if (val == 'text') {
		$('#opt_str').html('サイズ');
		$('#c_size').val('24');
		$('#form_option_text').css('display', 'block');
		$('#form_option_textarea').css('display', 'none');
	}
	if (val == 'textarea') {
		$('#opt_str').html('行数');
		$('#c_size').val('5');
		$('#form_option_text').css('display', 'block');
		$('#form_option_textarea').css('display', 'none');
	}
	if (val == 'select' || val == 'radio' || val == 'checkbox') {
		$('#form_option_text').css('display', 'none');
		$('#form_option_textarea').css('display', 'block');
	}
});

$('#add_to_canvas').click(function() {
	val = $('#form_select').val();
	if (val == 'none')     return;
	if (val == 'text')     add_text();
	if (val == 'textarea') add_textarea();
	if (val == 'select')   add_select();
	if (val == 'radio')    add_radio();
	if (val == 'checkbox') add_checkbox();

	return false;
});
___READY_CODE__;
	;

	foreach ($form as $f) {
		switch ($f['type']) {
			case 'text':
				$COMUNI_FOOT_JSRAW[] = 'add_text('. $f['required']. ', "'. $f['opt_size']. '", "'. $f['default']. '", "'. $f['title']. '", "'. $f['comment']. '", '.$f['uniq_id']. ', '. $f['admin_only']. ');';
				break;
			case 'textarea':
				$COMUNI_FOOT_JSRAW[] = 'add_textarea('. $f['required']. ', "'. $f['opt_size']. '", "'. $f['default']. '", "'. $f['title']. '", "'. $f['comment']. '", '.$f['uniq_id']. ', '. $f['admin_only']. ');';
				break;
			case 'select':
				$COMUNI_FOOT_JSRAW[] = 'add_select('. $f['required']. ', "'. $f['opt_list']. '", "'. $f['default']. '", "'. $f['title']. '", "'. $f['comment']. '", '.$f['uniq_id']. ', '. $f['admin_only']. ');';
				break;
			case 'radio':
				$COMUNI_FOOT_JSRAW[] = 'add_radio('. $f['required']. ', "'. $f['opt_list']. '", "'. $f['default']. '", "'. $f['title']. '", "'. $f['comment']. '", '.$f['uniq_id']. ', '. $f['admin_only']. ');';
				break;
			case 'checkbox':
				$COMUNI_FOOT_JSRAW[] = 'add_checkbox('. $f['required']. ', "'. $f['opt_list']. '", "'. $f['default']. '", "'. $f['title']. '", "'. $f['comment']. '", '.$f['uniq_id']. ', '. $f['admin_only']. ');';
				break;
			default:
				;
		}
	}

	$html = create_form(array(eid => $eid, pid => $pid));

	$html .= '<script type="text/javascript">';
	foreach ($COMUNI_FOOT_JSRAW as $k => $v) {
		$html .= $v. "\n";
	}
	$html .= '</script>';

	$data = array(title   => 'アンケートフォームの編集',
				  icon    => 'write',
				  content => $html);

	show_input($data);
}

function create_preview($form = array()) {
	foreach ($form as $f => $d) {
		switch ($d['type']) {
			case 'hidden':
			case 'text':
				

				break;
			case 'textarea':
				$value = $res['value'];
				$size  = $res['attr_size'];
				break;
			case 'radio':
			case 'checkbox':
			case 'select':
				$option = explode('<>', $res['option']);
				break;
			default :
		}
	}


}

/* ふぉーむ。*/
function input_data($eid = null, $pid = null) {
	global $SYS_FORM, $JQUERY, $COMUNI_HEAD_JS, $COMUNI_HEAD_CSS;

	if ($eid > 0) {
		$d = mysql_uniq("select * from enquete_data where id = %s",
						mysql_num($eid));

		$pid = mod_enquete_get_pid($eid);
	}

	if ($d) {
		$subject = $d["subject"];
		$note    = $d["note"];
		$type    = $d["type"];
		$dup     = $d["dup"];
		$result  = $d["result"];
		$thxmsg  = $d["thxmsg"];
		$tell_vote = $d["tell_vote"];
		$startymd  = strtotime($d["startymd"]);
		if ($d["endymd"]) {
			$endymd    = strtotime($d["endymd"]);
		}
		$updymd  = tm2time($d["updymd"]);

		$s = mysql_uniq("select * from enquete_status where pid = %s",
						mysql_num(mod_enquete_get_pid($eid)));
		if ($s) {
			if ($eid == $s["eid"]) {
				$status = 1;
			}
		}

		$c = mysql_uniq("select * from mod_enquete_allow where eid = %s",
						mysql_num($eid));
		if ($c) {
			$app_type = $c['type'];
		}
	}
	else {
		$subject = '';
		$note    = '';
		$type    = 0;
		$dup     = 0;
		$result  = 0;
		$status  = 1;
		$thxmsg  = '';
		$tell_vote = 1;
		$startymd = time();
		$app_type = 0;
	}
	// 再入力ならキャッシュから拾う
	if (isset($SYS_FORM["cache"])) {
		$subject   = $SYS_FORM["cache"]["subject"];
		$note      = $SYS_FORM["cache"]["note"];
		$type      = $SYS_FORM["cache"]["type"];
		$dup       = $SYS_FORM["cache"]["dup"];
		$result    = $SYS_FORM["cache"]["result"];
		$status    = $SYS_FORM["cache"]["status"];
		$thxmsg    = $SYS_FORM["cache"]["thxmsg"];
		$tell_vote    = $SYS_FORM["cache"]["tell_vote"];

		$app_type  = $SYS_FORM["cache"]["app_type"];
		$startymd  = strtotime($SYS_FORM["cache"]["startymd"]);
		if ($SYS_FORM["cache"]["endymd"]) {
			$endymd    = strtotime($SYS_FORM["cache"]["endymd"]);
		}
	}
	if ($startymd) {
		$start_date = date('Y-m-d', $startymd);
	}
	if ($endymd) {
		$end_date   = date('Y-m-d', $endymd);
	}

	// hidden:action
	$attr = array(name => 'action', value => 'canvas');
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));

	// text:subject
	if ($eid > 0) {
		$clear_href = make_href('結果をクリアする &raquo;', 'clear_vote.php?eid='. $eid, true);

		$p = mysql_full('select past_id from mod_enquete_csv_past where eid = %s group by past_id order by past_id desc',
						mysql_num($eid));

		if ($p) {
			while ($r = mysql_fetch_assoc($p)) {
				$past_ids[] = $r['past_id'];
			}
			if (count($past_ids) > 0) {
				$past_log = '<div style="margin-top: 8px; border-top: solid 2px #ccc; padding-top: 4px;">過去ログのダウンロード (クリア時間): </div>';
				foreach ($past_ids as $past_id) {
					$past_log .= '&lt;<a href="csv_past.php?id='. $eid. '&past_id='. $past_id. '">'. date('Y-m-d G:i', $past_id). '</a>&gt; ';
				}
			}
		}

		$SYS_FORM["input"][] = array(title => '結果のクリア',
									 name  => 'clear_vote',
									 body  => '<div style="padding: 3px; font-size: 0.9em;">'. $clear_href. $past_log. '</div>');
	}

	// text:subject
	$attr = array(name => 'subject', value => $subject, size => 64);
	$SYS_FORM["input"][] = array(title => '題名',
								 name  => 'subject',
								 body  => get_form("text", $attr));

	// fck:body
	$attr = array(name => 'note', value => $note, toolbar => 'Basic',
				  cols => 64, rows => 6);

	$SYS_FORM["input"][] = array(title => '説明',
								 name  => 'note',
								 body  => get_form("fck", $attr));


	// text:start_date
	$bhtml = '<div style="color: #f00; font-size: 0.9em;">'.
			 'アンケートの実施期間を設定してください。<br>アンケートを表示するためには、公開日に関わらず「アンケートの公開」をチェックする必要があります。'.
			 '</div>';

	$attr = array(name => 'start_date', value => $start_date, size => 12,
				  title => '日付', 'bhtml' => $bhtml);
	$form_date = get_form("text", $attr);

	$attr = array(name => 'start_time', value => $startymd, format => 'h時m分',
				  title => '時刻');
	$form_time = get_form("date", $attr);

	$SYS_FORM["input"][] = array(title => '開始日時',
								 name  => 'start_date',
								 body  => $sub_str. $form_date. '<hr size="1">'. $form_time);


	// text:end_date
	$attr = array(name => 'end_date', value => $end_date, size => 12,
				  title => '日付');
	$form_date = get_form("text", $attr);

	$attr = array(name => 'end_time', value => $endymd, format => 'h時m分',
				  title => '時刻');
	$form_time = get_form("date", $attr);

	$SYS_FORM["input"][] = array(title => '終了日指定',
								 name  => 'end_date',
								 body  => $form_date. '<hr size="1">'. $form_time);

	// text:type
	$option = array(0 => 'トップページで投票', 1 => '別ページで投票');
	$attr = array(name => 'type', value => $type, option => $option, break_num => 1);
	$SYS_FORM["input"][] = array(title => '表示タイプ',
								 name  => 'type',
								 body  => get_form("radio", $attr));

	// text:type
	$bhtml = '<div style="color: #f00; font-size: 0.9em;">'.
			 '公開できるアンケートは、１パーツにつき１つまでです。</span><br>'.
			 '公開した場合、他のアンケートは過去ログとなります。'.
			 '</div>';
	$option = array(1 => 'フォームの作成と同時に公開');
	$attr = array(name => 'status', value => $status, option => $option, break_num => 1, bhtml => $bhtml);
	$SYS_FORM["input"][] = array(title => 'アンケートの公開',
								 name  => 'status',
								 body  => get_form("checkbox", $attr));

	// text:type
	$option = array(0 => ' 公開しない', 1 => '終了後に公開', 2 => '投票中も公開');
	$attr = array(name => 'result', value => $result, option => $option, break_num => 1);
	$SYS_FORM["input"][] = array(title => '投票結果の公開',
								 name  => 'result',
								 body  => get_form("radio", $attr));

	// text:start_date
/*
	$attr = array(name => 'start_date', value => $start_date, size => 12,
				  title => '');
	$form_date = get_form("text", $attr);

	$SYS_FORM["input"][] = array(title => '開始日指定',
								 name  => 'start_date',
								 body  => $form_date);
*/



	// fck:body
	$attr = array(name => 'thxmsg', value => $thxmsg, toolbar => 'Basic',
				  cols => 64, rows => 6);

	$SYS_FORM["input"][] = array(title => '送信後メッセージ',
								 name  => 'thxmsg',
								 body  => get_form("fck", $attr));

	// text:type
	$option = array(0 => '誰でも可', 1 => '登録ユーザーのみ可');
	if (get_gid($pid) > 0) {
		$option[2] = 'グループ参加者のみ可';
	}
	$attr = array(name => 'app_type', value => $app_type, option => $option);
	$SYS_FORM["input"][] = array(title => '送信できるユーザーのレベル',
								 name  => 'app_type',
								 body  => get_form("radio", $attr));


	// text:type
	$option = array(0 => '許可', 1 => '許可しない');
	$attr = array(name => 'dup', value => $dup, option => $option);
	$SYS_FORM["input"][] = array(title => '重複投票の許可',
								 name  => 'dup',
								 body  => get_form("radio", $attr));

	// text:type
	$option = array(0 => '通知する', 1 => '通知しない');
	$attr = array(name => 'tell_vote', value => $tell_vote, option => $option);
	$SYS_FORM["input"][] = array(title => '投票時にメールて管理者へ通知',
								 name  => 'tell_vote',
								 body  => get_form("radio", $attr));


	$SYS_FORM["action"] = 'input.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["keyword"] = true;
	$SYS_FORM["pmt"]     = false;
	$SYS_FORM["submit"]  = '次に進む';
	$SYS_FORM["cancel"]  = '取消';
	$SYS_FORM["onCancel"]  = 'location.href =\''. home_url($pid). '\'; return false;';

	// 日付用のスクリプト
	$COMUNI_HEAD_JS[]  = '/ui.datepicker.js';
	$COMUNI_HEAD_CSS[] = '/ui.datepicker.css';

	$JQUERY["ready"][] = <<<___READY_CODE__
\$('#start_date').datepicker({ dateFormat: 'yy-mm-dd'});
\$('#end_date').datepicker({ dateFormat: 'yy-mm-dd'});
___READY_CODE__;
	;

	$html = create_form(array(eid => $eid, pid => $pid));

	$data = array(title   => 'アンケート基本設定の登録/変更',
				  icon    => 'write',
				  content => $html);

	show_input($data);

	exit(0);
}

function load_formset($eid = 0) {
	$q = mysql_full('select * from enquete_form_data where eid = %s'.
					' order by num',
					mysql_num($eid));

	$form = array();
	if ($q) {
		while ($res = mysql_fetch_array($q)) {
			$value = ''; $option = array();
			switch ($res['type']) {
				case 'hidden':
				case 'text':
				case 'textarea':
					$value = $res['def_val'];
					$size  = $res['opt_size'];
					break;
				case 'radio':
				case 'checkbox':
				case 'select':
					$list = $res['opt_list'];
					break;
				default :
					$value = $res['def_val'];
					$size  = $res['opt_size'];
			}

			$form[] = array('num'       => $res['num'],
							'type'     => $res['type'],
							'title'    => $res['title'],
							'required' => $res['req_check'],
							'comment'  => $res['comment'],
							'default'  => $value,
							'opt_size' => $size,
							'opt_list' => $list);
		}
	}

	foreach ($form as $f) {
		$vote_cid = $vote_id. $f['num'];

		$req = '';
		if ($f['required'] == 1) {
			$req = '&nbsp;<span class="enquete_srb">必須</span>';
			$jqcode .= mod_enquete_jqcode($vote_cid, $f['title']);
		}

		$adm = '';
		if ($f['admin_only'] == 1) {
			$adm = '&nbsp;<span class="enquete_srb">結果非公開</span>';
		}

		$content .= '<h3 class="enquete_stb">'. $f['title']. $req. '</h3>'. "\n";
		$content .= '<div class="enquete_scb">'. $f['note']. '</div>'. "\n";
		$content .= '<div class="enquete_errb" id="'. $vote_cid. '_err"></div>'. "\n";

		$content .= '<div class="enquete_seb">';
		switch ($f['type']) {
			case 'text':
				$opt = '';
				if ($f['opt_size'] > 12 && $full == 0) {
					$opt = ' style="width: 100%;"';
				}
				else {
					$opt = ' size="'. $f['opt_size']. '"';
				}
				$content .= '<input type="text" id="'. $vote_cid. '" name="enq_'. $f['num']. '" value="'. $f['default']. '" class="enquete_sib"'. $opt. '>';
				break;
			case 'textarea':
				$opt = '';
				if ($f['opt_size'] > 5 && $full == 0) {
					$opt = ' rows="5" style="width: 100%;"';
				}
				else {
					$opt = ' rows="'. $f['opt_size']. '" style="width: 100%;"';
				}
				$content .= '<textarea id="'. $vote_cid. '" name="enq_'. $f['num']. '" class="enquete_sib"'. $opt. '>'. $f['default']. '</textarea>';
				break;
			case 'select':
				$opt = array();
				$opt = explode('-_-', $f['opt_list']);
				$list = '<option value="">選択して下さい</option>';
				$i = 0;
				foreach ($opt as $o) {
					$i++;
					if ($o == $f['default']) {
						$list .= '<option value="'. $i. '" selected>'. $o. '</option>';
					}
					else {
						$list .= '<option value="'. $i. '">'. $o. '</option>';
					}
				}
				$content .= '<select id="'. $vote_cid. '" name="enq_'. $f['num']. '" class="enquete_ssb">'. $list. '</select>';
				break;
			case 'radio':
				$opt = array();
				$opt = explode('-_-', $f['opt_list']);
				$list = '';
				$i = 0;
				foreach ($opt as $o) {
					if ($o == '') coninue;
					$i++;
					if ($o == $f['default']) {
						$content .= '<input type="radio" name="enq_'.  $f['num']. '" value="'. $i. '" checked>'. $o;
					}
					else {
						$content .= '<input type="radio" name="enq_'.  $f['num']. '" value="'. $i. '">'. $o;
					}
				}
				break;
			case 'checkbox':
				$opt = array();
				$opt = explode('-_-', $f['opt_list']);
				$list = '';
				$i = 0;
				foreach ($opt as $o) {
					if ($o == '') coninue;
					$i++;
					if ($o == $f['default']) {
						$content .= '<input type="checkbox" name="enq_'.  $f['num']. '[]" value="'. $o. '" checked>'. $o. '</option>';
					}
					else {
						$content .= '<input type="checkbox" name="enq_'.  $f['num']. '[]" value="'. $o. '">'. $o. '</option>';
					}
				}
				break;
			default:
				;
		}
		$content .= '</div>';
	}

	return $content;
}

?>
