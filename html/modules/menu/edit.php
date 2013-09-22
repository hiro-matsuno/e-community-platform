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
	case 'sort':
		sort_data($eid, $pid);
	break;
	case 'regist':
		regist_data($eid, $pid);
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
		$subq[] = sprintf('(%s, %s, %s)', mysql_num($id), mysql_num($pos), mysql_num($pid));
	}
	$d = mysql_exec('delete from mod_menu_data_pos where parent = %s',
					mysql_num($pid));
	$u = mysql_exec('insert into mod_menu_data_pos (id, position, parent) values '.
					implode(',', $subq));

	echo '順番を変更しました。';

	exit(0);
}

function input_data($eid = null, $pid = null) {
	global $JQUERY, $SYS_INPUT_SCRIPT, $SYS_BACK_HREF;

	// 親IDチェック
	if ($pid == 0) {
		show_error('ブロックIDが不明です。');
	}
	// 編集チェック
	if (!is_owner($pid)) {
		show_error('編集権限がありません。');
	}

	$f = mysql_full("select d.* from menu_data as d".
					' left join mod_menu_data_pos as pos on d.id = pos.id'.
					" where d.pid = %s order by pos.position, d.hpos",
					mysql_num($pid));

	$list  = array();
	$style = array('pos'   => 'width: 20px; text-align: center;',
				   'title' => 'width: 470px; text-align: left;',
				   'edit'  => 'width: 40px; font-size: 0.8em; text-align: center;',
				   'del'   => 'width: 40px; font-size: 0.8em; text-align: center;');

	$list[] = array('title'   => 'メニュー項目',
					'edit'  => '編集',
					'del'   => '削除');

	if ($f) {
		while ($r = mysql_fetch_array($f)) {
			$subject = $r['title'] ? $r['title'] : '無題';
			$list[] = array('pos'    => $r['id'],
							title   => make_href($subject, $r['href'], null, '_blank', 128),
							hpos    => $r['hpos'],
							'edit'   => '<div class="edit_button">'. make_href('編集', '/modules/menu/input.php?eid='. $r['id']). '</div>',
							'del'    => '<div class="edit_delbtn">'. make_href('削除', '/del_content.php?module=menu&eid='. $r['id'], true). '</div>');
		}
	}

	set_return_url();

	$SYS_BACK_HREF = home_url($pid);

	$edit_url = '/modules/menu/input.php?eid=';
	$del_url  = '/del_content.php?module=menu&eid=';

	$html  = '<div style="padding: 4px;">';
	$html .= mkhref(array('s' => '項目を新規追加&raquo;', 'h' => 'input.php?pid='. $pid, 'c' => 'add_input'));
	$html .= '</div>';
	$html .= create_list_sortable($list, $style, 'pos', 'edit.php?action=sort&pid='. $pid. '&');

	$data = array(title   => 'メニューの編集',
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
			'<button class="input_submit" id="submit_'. $table_id. '">登録</button>'.
			' <button class="input_cancel" id="cancel_'. $table_id. '">取消</button>'.
			'</div></div>'.
			'<div style="clear: both;"></div>';

	$back_href = $SYS_BACK_HREF;

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
		location.href = '${back_href}';
	});
//	location.href = '${script}' + $('#data_${table_id}').text();
});
$('#cancel_${table_id}').click(function() {
	location.href = '${back_href}';
	return false;
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


?>
