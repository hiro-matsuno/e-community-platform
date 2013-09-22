<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require dirname(__FILE__). '/../../lib.php';
require dirname(__FILE__). '/func.php';

list($eid, $pid) = get_edit_ids();

switch ((isset($_REQUEST["action"]) ? $_REQUEST["action"] : null)) {
	case 'sort':
		sort_data($eid, $pid);
	break;
	case 'entry':
		entry_data($eid, $pid);
	break;
	default:
		input_data($eid, $pid);
}

function sort_data($eid = 0, $pid = 0) {
	$pos = isset($_GET['pos']) ? $_GET['pos'] : array();

	if (!$pos || count($pos) == 0) {
		echo '並び順に変更はありません。';
		exit(0);
	}

	$subq = array();
	foreach ($_GET['pos'] as $pos => $id) {
		$subq[] = sprintf('(%s, %s, %s)', mysql_num($id), mysql_num($pos), mysql_num($eid));
	}
	$d = mysql_exec('delete from mod_contact_form_pos where parent = %s',
					mysql_num($eid));
	$u = mysql_exec('insert into mod_contact_form_pos (id, position, parent) values '.
					implode(',', $subq));

	echo '順番を変更しました。';

	exit(0);
}

function entry_data($eid = null, $pid = null) {
	die('調整中');

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
	global $SYS_FORM, $JQUERY, $COMUNI_HEAD_JS, $COMUNI_HEAD_CSSRAW;

	if ($eid > 0) {
		$d = mysql_uniq("select * from mod_contact_setting where id = %s",
						mysql_num($eid));
	}

	$f = mysql_full("select d.* from mod_contact_form_data as d".
					' inner join mod_contact_form_pos as pos on d.id = pos.id'.
					" where d.eid = %s order by pos.position",
					mysql_num($eid));

	$list  = array();
	$style = array('pos'   => 'width: 20px; text-align: center;',
				   'title' => 'width: 350px; text-align: left;',
				   'type' => 'width: 120px; text-align: left;',
				   'edit'  => 'width: 40px; font-size: 0.8em; text-align: center;',
				   'del'   => 'width: 40px; font-size: 0.8em; text-align: center;');

	$list[] = array('title'  => 'タイトル',
					'type'   => 'フォームの種類',
					'edit'   => '編集',
					'del'    => '削除');

	if ($f) {
		while ($r = mysql_fetch_array($f)) {
			$subject = $r['title'] ? $r['title'] : '無題';

			switch($r['type']) {
				case 'text':
					$type = 'テキスト１行';
				break;
				case 'textarea':
					$type = 'テキスト複数行';
				break;
				case 'select':
					$type = '一つを選択 (リスト)';
				break;
				case 'radio':
					$type = '一つを選択 (ラジオボタン)';
				break;
				case 'checkbox':
					$type = '複数を選択 (チェックボックス)';
				break;
				default:
					$type = '不明';
			}

			$list[] = array('pos'   => $r['id'],
							'title' => make_href($subject, $r['href'], null, '_blank', 128),
							'type'  => $type,
							'hpos'  => $r['hpos'],
							'edit'  => '<div class="edit_button">'.
									   make_href('編集', 'edit.php?id='. $r['id'], true). '</div>',
							'del'   => '<div class="edit_delbtn">'.
									   make_href('削除', 'delete.php?id='. $r['id'], true). '</div>');
		}
	}

	$html  = '<div style="padding: 4px;">';
	$html .= mkhref(array('s' => '入力フォームを追加&raquo;', 'h' => 'add.php?eid='. $eid, 'c' => 'thickbox add_input'));
	$html .= mkhref(array('s' => 'CSVデータをダウンロード&raquo;', 'h' => 'csv.php?eid='. $eid, 'c' => 'add_input'));
	$html .= mkhref(array('s' => 'CSVデータを削除&raquo;', 'h' => 'clear_csv.php?eid='. $eid, 'c' => 'thickbox add_input'));
	$html .= '</div>';
	$html .= create_list_sortable($list, $style, 'pos', 'input.php?action=sort&eid='. $eid. '&');

	$html .= '<div style="padding: 10px;">';
	$html .= '<h4 style="margin: 5px 0;">プレビュー</h4>';
	$html .= mod_contact_create_form($eid, false);
	$html .= '</div>';

	$COMUNI_HEAD_CSSRAW[] = $d['css'];

	$data = array(title   => 'お問い合わせフォーム機能設定',
				  icon    => 'write',
				  content => $html);

	show_input($data);

	exit(0);
}

function create_list_sortable($list = array(), $style = array(), $sort_name = '', $script = '') {
	global $JQUERY, $COMUNI_HEAD_CSSRAW, $SYS_BACK_HREF;

	$head = array_shift($list);

	$table_id = 'sort_'. $sort_name;

	$res  = '<div class="edit_table_wrap">'. "\n";
	$res .= '<table class="edit_table">'. "\n";
	$res .= '<tr>'. "\n";

	$res .= '<th style="width: 16px;">&nbsp;</th>';
	foreach ($head as $h => $v) {
		if (isset($style[$h])) {
			$res .= '<th style="font-size: 0.8em; text-align: center;">'. $v. '</th>';
		}
		else {
			$res .= '<th>'. $v. '</th>';
		}
	}

	$res .= '</tr>'. "\n";
	$res .= '<tbody id="'. $table_id. '">'. "\n";

	$i = 0;
	foreach ($list as $l) {
		$res .= '<tr class="sortableitem" id="'. $sort_name. '_'. $l[$sort_name]. '">'. "\n";
		$res .= '<td><img src="image/arrow.png" class="handle"/></td>';
		foreach ($l as $ll => $v) {
			if (!isset($head[$ll])) {
				continue;
			}
			$td_class = '';
			if (preg_match('/ymd$/', $ll, $match) || strlen($v) < 16) {
				$td_class = ' class="td_nowrap"';
			}
			if (isset($style[$ll])) {
				$res .= '<td style="'. $style[$ll]. '" '. $td_class. '>'. $v. '</td>';
			}
			else {
				$res .= '<td'. $td_class. '>'. $v. '</td>';
			}
		}
		$res .= '</tr>'. "\n";
		$i++;
	}
	$res .= '</tbody>'. "\n";
	$res .= '</table>'. "\n";
	$res .= '</div>'. "\n";
	$res .= '<div style="padding: 5px; font-size: smaller;">';
	$res .= '順番を並び替えるには <img src="image/arrow.png" align="absmiddle"> を掴んで上下に移動してから、下の「並び替え」を押してください。';
	$res .= '</div>'. "\n";
	$res .= '<div id="data_'. $table_id. '" style="display: none;"></div>';
	$res .= '<div id="msg_'. $table_id. '" style="line-height: 2em; color: #fc6d5c; font-size: 0.8em; text-align: center;"></div>';
	$res .= '<div class="input_submit_wrap">'.
			'<div style="margin: 0px auto; padding: 5px;">'.
			'<button class="input_submit" id="submit_'. $table_id. '">並び替え</button>'.
			'</div></div>'.
			'<div style="clear: both;"></div>';

	$JQUERY['ready'][] = <<<__JQ__
$('#submit_${table_id}').attr('disabled', 'disabled');
$('#${table_id} tr').css('height', '2em');
$('tbody tr:even').addClass('tr_2ndline');
$('#${table_id}').sortable({
	accept : 'sortableitem',
	handle : '.handle',
	axis : 'y',
	start: function(event, ui) {
		ui.item.removeClass('tr_2ndline');
		ui.item.addClass('sortableactive');
	},
	update : function () {
		var order = $('#${table_id}').sortable('serialize');
		$('#data_${table_id}').text(order);
		$("tbody tr").removeClass('tr_2ndline');
		$("tbody tr:even").addClass('tr_2ndline');
		$('#msg_${table_id}').text('並び順が変更されています。保存するには下のボタンを押してください。');
		$('#msg_${table_id}').fadeIn(100);
		$('#submit_${table_id}').removeAttr('disabled');
	},
	stop: function(event, ui) {
		ui.item.removeClass('sortableactive');
		$("tbody tr").removeClass('tr_2ndline');
		$("tbody tr:even").addClass('tr_2ndline');
	}
});
$('#submit_${table_id}').click(function() {
	$('#msg_${table_id}').load('${script}' + $('#data_${table_id}').text(), '', function() {
		$('#data_${table_id}').text('');
		$('#submit_${table_id}').attr('disabled', 'disabled');
		$('#msg_${table_id}').fadeOut(5000);
		location.reload(true);
	});
//	location.href = '${script}' + $('#data_${table_id}').text();
});

__JQ__;

	$COMUNI_HEAD_CSSRAW[] = <<<__CSS__
.handle {
	cursor: move;
}
.sortableitem {
	height: 2em;
}
.sorthelper {
	border: 3px dashed #666;
	background: #ccc;
	height: 2em;
	width: auto !important;
}
.sortableactive {
	background: #eeffeb;
	height: 2em;
}
.sortableactive td {
	border: none;
}
.sortablehover {
	background: #ffd698;
	height: 2em;
}
.edit_button {
	padding: 2px;
	display: block;
	border: solid 1px #cccccc;
	background-color: #dbf6e7;
	text-align: center;
}
.edit_delbtn {
	padding: 2px;
	display: block;
	border: solid 1px #cccccc;
	background-color: #ffefef;
	text-align: center;
}
__CSS__;

	return $res;
}


function default_css() {
	return <<<__CSS__
.mod_contact_title {
	border-left: solid 5px #ccc;
	border-bottom: solid 1px #ccc;
	line-height: 18px;
	font-size: 14px;
	font-weight: normal;
}

.mod_contact_body {
	padding: 5px;
}
__CSS__;
	;
}

?>
