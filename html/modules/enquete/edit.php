<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require dirname(__FILE__). '/../../lib.php';
include_once dirname(__FILE__). '/func.php';

/* ふりわけ。*/
list($eid, $pid) = get_edit_ids();

switch ($_REQUEST["action"]) {
	default:
		input_data($eid, $pid);
}

function input_data($eid = null, $pid = null) {
	global $JQUERY, $SYS_INPUT_SCRIPT;

	// 親IDチェック
	if ($pid == 0) {
		if (!($pid = mod_enquete_get_pid($eid))) {
			show_error('パーツIDが不明です。');
		}
	}
	// 編集チェック
	if (!is_owner($pid)) {
		show_error('編集権限がありません。');
	}

	$f = mysql_full("select * from enquete_data as d".
					" inner join mod_enquete_element_relation as el".
					" on d.id = el.id".
					" where el.pid = %s",
					mysql_num($pid));

	$list = array();

	$list[] = array(id       => '',
					current  => '表示',
					subject  => '題名',
//					body     => '内容',
					startymd => '開始日時',
					endymd   => '終了日時',
//					updymd  => '作成日時',
					result  => '回答');

	$current = 0;
	$c = mysql_uniq('select * from enquete_status where pid = %s', mysql_num($pid));
	if ($c) {
		$current = $c['eid'];
	}

	if ($f) {
		while ($r = mysql_fetch_array($f)) {
			$href = '/index.php?module=enquete&eid='. $r['id'];
			$subject = $r['subject'] ? $r['subject'] : '無題';
			if ($r['endymd']) {
				$endymd = date('Y年m月d日<br>H時i分', strtotime($r['endymd']));
			}
			else {
				$endymd = '未設定';
			}

			$cnt = 0;
			$c = mysql_uniq('select count(*) as cnt from mod_enquete_csv where eid = %s', mysql_num($r['id']));
			if ($c) {
				$cnt = $c['cnt'];
			}

			$list[] = array(id      => $r['id'],
//							current  => ($current == $r['id']) ? '<div style="text-align: center; font-size: 10px;">表示中</div>' :  '&nbsp;',
							current  => ($current == $r['id']) ? '<div style="text-align: center;"><input type="radio" name="select" checked="checked" value="'. $current. '"></div>' :  '<div style="text-align: center;"><input type="radio" name="select" value="'. $current. '" onClick="location.href=\'set_status.php?pid='. $pid. '&t='. $r['id']. '\'; return false;"></div>',
							subject => make_href($subject, $href, null, '_blank', 32),
//							note    => clip_str($r['note'], 50),
							startymd => date('Y年m月d日<br>H時i分', strtotime($r['startymd'])),
							endymd   => $endymd,
							result   => '<div style="text-align: center; white-space: nowrap;"><a href="/index.php?action=result&module=enquete&eid='. $r['id']. '" target="_blank">('. $cnt. '件)</a></div>');
//							updymd   => date('Y年m月d日<\b\r />H時i分', tm2time($r['initymd'])));
		}
	}

	set_return_url();

	$edit_url = '/modules/enquete/input.php?eid=';
	$del_url  = '/del_content.php?module=enquete&eid=';
	$html = create_edit_list($edit_url, $del_url, $list);

	$html .= '<div style="padding: 5px; font-size: 0.9em;">「表示」のラジオボタンをクリックすると、アンケートの公開を行います。</div>';


	$data = array(title   => 'アンケートの編集',
				  icon    => 'write',
				  content => $html);

	show_input($data);

	exit(0);
}

?>
