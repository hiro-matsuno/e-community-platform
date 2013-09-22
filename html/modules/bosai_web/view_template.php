<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require_once dirname(__FILE__). '/../../lib.php';

$site_id = intval($_REQUEST['site_id']);
$block_id = intval($_REQUEST['block_id']);

$b = mysql_uniq('select * from bosai_web_block'.
				' where block_id = %s',
				mysql_num($block_id));
if ($b) {
	$f = mysql_full('select d.* from bosai_web_category as d'.
					' where d.pid = %s'.
					' order by d.num',
					mysql_num($b['eid']));

	if ($f) {
		while ($c = mysql_fetch_array($f)) {
			$option[$c['eid']] = $c['name'];
		}
	}
	else {
		$option[0] = '分類が未登録です。';
	}
}

$q = mysql_full('select d.* from bosai_web_template as d'.
				' inner join bosai_web_template_rel as r'.
				' on d.id = r.eid'.
				' where r.site_id = %s'.
				' order by d.category, d.num',
				mysql_num($site_id));

$table = array();
if ($q) {
	while ($r = mysql_fetch_array($q)) {
		$table[$r['category']][] = array(id => $r['id'],
										 custom  => false,
										 subject => $r['subject'],
										 body    => $r['body']);
	}
}

$u = mysql_full('select d.* from bosai_web_template_bysite as d'.
				' inner join bosai_web_template_rel as r'.
				' on d.id = r.eid'.
				' where r.site_id = %s'.
				' order by d.category, d.num',
				mysql_num($block_id));

if ($u) {
	while ($r = mysql_fetch_array($u)) {
		$table[$r['category']][] = array(id      => $r['id'],
										 custom  => true,
										 subject => $r['subject'],
										 body    => $r['body']);
	}
}

$content = <<<AAAA
<style type="text/css">
table#ttt {
    width: 100%;
    border: 2px #E3E3E3 solid;
    border-collapse: collapse;
    border-spacing: 0;
}

table#ttt th {
    padding: 5px;
    border: #E3E3E3 solid;
    border-width: 0 0 1px 1px;
    background: #F5F5F5;
    font-weight: bold;
    line-height: 120%;
    text-align: center;
}
table#ttt td {
    padding: 5px;
    border: 1px #E3E3E3 solid;
    border-width: 0 0 1px 1px;
    text-align: left;
}
</style>
AAAA;

$funcname = 'copy2body'. rand_str(5, 'alpha');

$tmpl_input = make_href('ユーザー雛形を登録する', '/modules/bosai_web/template_bysite.php?pid='. $block_id);
$tmpl_list = make_href('登録したユーザー雛形一覧', '/modules/bosai_web/template_list_bysite.php?pid='. $block_id);
$content .= '<div class="bwt_edit">'. $tmpl_input. ' / '. $tmpl_list. 
			'<br>ユーザー雛形のタイトルには <img src="/skin/default/image/person.png"> が付きます。'.
			'</div>';

$content .= '<table id="ttt">';
foreach ($option as $o => $v) {
	if (isset($table[$o])) {
		$content .= '<tr><th rowspan="'. count($table[$o]). '">'. $v. '</th>';
		$f = 0;
		foreach ($table[$o] as $c) {
			if ($f > 0) { $content .= '<tr>'; };
			$content .= '<td>';
			if ($c['custom'] == true) {
				$class = 'bwt_title_bysite';
			}
			else {
				$class = 'bwt_title';
			}
			$content .= '<h4 class="'. $class. '" id="title_'. $c['id']. '">'. $c['subject']. '</h4>';
			$content .= '<div class="bwt_body" id="str_'. $c['id']. '">'. $c['body']. '</div>';
			$content .= '<div style="text-align: right;"><a href="#" onClick="return '. $funcname. '('. $c['id']. '); return false;">この項目を引用&raquo;</a></div>';
			$content .= '</td></tr>';
			$f = 1;
		}
	}
	else {
		$content .= '<tr><th>'. $v. '</th>';
		$content .= '<td><small>(未登録)</small></td></tr>';
	}
}

$COMUNI_HEAD_JSRAW[] = <<<_JS_
function ${funcname}(eid) {
	var oEditor;
	var target_title = '#title_' + eid;
	var target_body = '#str_' + eid;

	oEditor = window.parent.FCKeditorAPI.GetInstance('body');

	if (!oEditor.Status) {
		alert('test no oEditor');
		window.parent.document.getElementById('subject').value += jQuery(target_title).text();
		window.parent.document.getElementById('body').value += jQuery(target_body).html();
	}
	else {
		window.parent.document.getElementById('subject').value += jQuery(target_title).text();
		oEditor.InsertHtml(jQuery(target_body).html());
		window.parent.document.getElementById('subject').focus();
	}
//	window.parent.focus();
//	return false;
	return self.parent.tb_remove();
}
_JS_;

$content .= '</table>';

$ref  = '/modules/bosai_web/template_list.php?pid='. $pid;
$html = '登録完了。';
$string = '雛型。';
$data = array(title   => '防災ウェブ雛形一覧',
			  icon    => 'finish',
			  content => $content);

show_dialog2($data);

exit(0);

?>
