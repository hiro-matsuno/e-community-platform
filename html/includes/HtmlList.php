<?php 
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
?>
<?php
//-----------------------------------------------------
// * パーツの編集リスト生成補助 (その1)
// * 編集と削除のみ必要な場合
//-----------------------------------------------------
function create_edit_list($edit_url = null, $del_url = null, $list = array()) {
	$head = array_shift($list);

	$res  = '<div class="edit_table_wrap">'. "\n";
	$res .= '<table class="edit_table">'. "\n";
	$res .= '<tr>'. "\n";
	foreach ($head as $h => $v) {
		if ($h == 'id') {
			$res .= '<th>'. $v. '</th>';
			$res .= '<th>'. $v. '</th>';
		}
		else {
			$res .= '<th>'. $v. '</th>';
		}
	}
	$res .= '</tr>'. "\n";
	$i = 0;
	foreach ($list as $l) {
		$tr_class = '';
		if (($i % 2) == 1) {
			$tr_class = ' class="tr_2ndline"';
		}
		$res .= '<tr'. $tr_class. '>'. "\n";
		foreach ($l as $ll => $v) {
			$td_class = '';
			if (preg_match('/ymd$/', $ll, $match) || strlen($v) < 16) {
				$td_class = ' class="td_nowrap"';
			}
			if ($ll == 'id') {
				$res .= '<td'. $td_class. '><a href="'. $edit_url. $v. '" class="edit_button">編集</a></td>';
				$res .= '<td'. $td_class. '><a href="'. thickbox_href($del_url. $v). '" class="edit_delbtn thickbox">削除</a></td>';
			}
			else {
				$res .= '<td'. $td_class. '>'. $v. '</td>';
			}
		}
		$res .= '</tr>'. "\n";
		$i++;
	}
	$res .= '</table>'. "\n";
	$res .= '</div>'. "\n";

	return $res;
}

//-----------------------------------------------------
// * パーツの編集リスト生成補助 (その2)
// * 編集と削除ボタンも指定
//-----------------------------------------------------
function create_auth_list($editor = array(), $list = array()) {
	$head = array_shift($list);

	$res  = '<div class="edit_table_wrap">'. "\n";
	$res .= '<table class="edit_table">'. "\n";
	$res .= '<tr>'. "\n";

	for ($i = 0; $i < count($editor); $i++) {
		$res .= '<th></th>';
	}
	foreach ($head as $h => $v) {
		$res .= '<th>'. $v. '</th>';
	}

	$res .= '</tr>'. "\n";
	$i = 0;
	foreach ($list as $l) {
		$tr_class = '';
		if (($i % 2) == 1) {
			$tr_class = ' class="tr_2ndline"';
		}
		$res .= '<tr'. $tr_class. '>'. "\n";

		foreach ($editor as $e => $v) {
			list($t, $c) = explode('.', $e);
			$c = isset($c) ? $c : 'edit_button';
			$href = $v. $l['id'];
			if (preg_match('/thickbox/',$c)) {
				$href = thickbox_href($href);
			}
			$res .= '<td'. $td_class. '><a href="'. $href. '" class="'. $c. '">'. $t. '</a></td>';
		}
		foreach ($l as $ll => $v) {
			if (!isset($head[$ll])) {
				continue;
			}
			$td_class = '';
			if (preg_match('/ymd$/', $ll, $match) || strlen($v) < 16) {
				$td_class = ' class="td_nowrap"';
			}
			$res .= '<td'. $td_class. '>'. $v. '</td>';
		}
		$res .= '</tr>'. "\n";
		$i++;
	}
	$res .= '</table>'. "\n";
	$res .= '</div>'. "\n";

	return $res;
}

//-----------------------------------------------------
// * リスト生成補助
//-----------------------------------------------------
function create_list($list = array(), $style = array()) {
	$head = array_shift($list);

	$res  = '<div class="edit_table_wrap">'. "\n";
	$res .= '<table class="edit_table">'. "\n";
	$res .= '<tr>'. "\n";

	foreach ($head as $h => $v) {
		if (isset($style[$h])) {
			$res .= '<th style="'. $style[$h]. '">'. $v. '</th>';
		}
		else {
			$res .= '<th>'. $v. '</th>';
		}
	}

	$res .= '</tr>'. "\n";
	$i = 0;
	foreach ($list as $l) {
		$tr_class = '';
		if (($i % 2) == 1) {
			$tr_class = ' class="tr_2ndline"';
		}
		$res .= '<tr'. $tr_class. '>'. "\n";
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
	$res .= '</table>'. "\n";
	$res .= '</div>'. "\n";

	return $res;
}


/**
 * Description of List
 *
 * @author ikeda
 */
class HtmlList {
    //put your code here
}
?>
