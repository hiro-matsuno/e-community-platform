<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

//-----------------------------------------------------
// comuni_core.php
//-----------------------------------------------------
function keyword_form($pmt_id = null) {
	global $SYS_KEYWORD_COUNT, $JQUERY;

	if ($SYS_KEYWORD_COUNT > 0) {
		$SYS_KEYWORD_COUNT++;
	}
	else {
		$SYS_KEYWORD_COUNT = 0;
	}

	$f = mysql_full("select * from tag_data where pid = %s",
					mysql_num($pmt_id));

	$cur_tag = array();
	if ($f) {
		while ($r = mysql_fetch_array($f)) {
			$cur_tag[$r['tag_id']] = true;
		}
	}

	$t = mysql_full("select ts.* from tag_setting as ts".
					" inner join element".
					" on ts.id = element.id");
	if ($t) {
		while ($r = mysql_fetch_array($t)) {
			if ($cur_tag[$r['id']] == true) {
				$value .= $r["keyword"]. ' ';
					$tags .= '<span class="tag_'. $SYS_KEYWORD_COUNT. '" style="white-space: nowrap; float: left; margin: 3px; padding: 2px 3px; display: block; border: solid 1px #9f9f9f; background-color: #1c78bf; color: #ffffff; height: 1.1em;">'. $r["keyword"]. '</span>';
			}
			else {
				$tags .= '<span class="tag_'. $SYS_KEYWORD_COUNT. '" style="white-space: nowrap; float: left; margin: 3px; padding: 2px 3px; display: block; border: solid 1px #dfdfdf; background-color: #ffffff; color: #666666; height: 1.1em;">'. $r["keyword"]. '</span>';
			}
		}
	}

	$JQUERY["ready"][] = keyword_ready_script($SYS_KEYWORD_COUNT);

	$ahtml =<<<__ATHML__
<div style="clear: both;font-size: 0.8em; margin-top: 5px;">
キーワードを選択
</div>
<div style="color: #3366cc; padding: 2px; width: 100%;">
${tags}
</div>
<!--
<div style="clear: both;font-size: 0.8em; margin-top: 5px; margin-bottom: 1px;">
新しく入力する場合、キーワードは空白で区切ってください。
</div>
-->
__ATHML__;

	$attr = array(name => 'tag_'. $SYS_KEYWORD_COUNT. '_i',
				  value => $value,
				  size => 50,
				  ahtml => $ahtml);

	return get_form("hidden", $attr);
}

function set_keyword($eid = null, $blk_id = null, $value = null, $name = 'tag_0_i') {
	global $COMUNI, $JQUERY;

	if (!$eid) { return; }
	if (isset($value)) {
		$input_kwd = $value;
	}
	else {
		$input_kwd = $_REQUEST[$name];
	}

	$input_kwd = mb_convert_encoding($input_kwd, 'UTF-8', 'auto');
	$input_kwd = mb_ereg_replace("　", " ", $input_kwd);

	$keywords = split(' ', $input_kwd);
	$d = mysql_exec("delete from tag_data where pid = %s", mysql_num($eid));
	foreach ($keywords as $keyword) {
		$keyword = ereg_replace('/^ +/', '', $keyword);
		$keyword = ereg_replace('/ +$/', '', $keyword);
		if ($keyword == '') {
			continue;
		}
		$q = mysql_uniq("select * from tag_setting where keyword = %s", mysql_str($keyword));
		$tag_id = $q["id"];
		if (!$tag_id) {
			$tag_id = get_seqid();
			$f = mysql_exec("insert into tag_setting(id, keyword) values(%s, %s)",
							mysql_num($tag_id), mysql_str($keyword));
			set_pmt(array(eid => $tag_id, unit => 0));
		}
		$a = mysql_exec("insert into tag_data(pid, tag_id, blk_id) values(%s, %s, %s)",
						mysql_num($eid), mysql_num($tag_id), mysql_num($blk_id));
	}
}

function get_keyword_form($mode = 'public') {
	global $COMUNI, $JQUERY;

	switch ($mode) {
		case 'private':
			$srch_id = $COMUNI["uid"];
			break;
		default:
			$srch_id = 0;
	}

	if ($COMUNI["__keyword_num"] > 0) {
		$COMUNI["__keyword_num"]++;
	}
	else {
		$COMUNI["__keyword_num"] = 0;
	}

	$JQUERY["ready"][] = keyword_ready_script($COMUNI["__keyword_num"]);

	$num = $COMUNI["__keyword_num"];

	$f = mysql_exec("select * from tag_setting as ts;");

	$tags = '';
	while ($r = mysql_fetch_array($f)) {
		$tags .= '<span class="tag_'. $num. '" style="white-space: nowrap; float: left; margin: 3px; padding: 2px 3px; display: block; border: solid 1px #ffffff;">'. $r["keyword"]. '</span>';
	}

	return <<<__HTML_CODE__
<!-- tag_input_form num = ${num}-->
<tr>
<th>キーワード</th>
<td>
<input type="text" id="tag_${num}_i" name="tag_${num}_i" class="input_text" size="38" value="${keyword}"><br>
<span style="font-size: 0.8em;">登録済キーワード</span><br>
<div style="color: #3366cc; padding: 4px;">
${tags}
<br clear="all">
</div>
<span style="font-size: 0.9em;">新しく入力する場合、キーワードはカンマ「,」で区切ってください。</span>
</td>
</tr>
<!-- /tag_input_form num = ${num}-->
__HTML_CODE__;
	;
}

function keyword_ready_script($num) {
	global $COMUNI, $JQUERY;

// 登録不可
	return <<<__READY_CODE__
/* tag_ready num = ${num} */
	\$('.tag_${num}').css('cursor', 'pointer');

	\$('.tag_${num}').each(function() {
		\$(this).click(function() {
			var current_tags = \$('#tag_${num}_i:hidden').val();
			if (\$(this).css('background-color') == '#ffffff' || \$(this).css('background-color') == 'rgb(255, 255, 255)' ) {
				\$('#tag_${num}_i:hidden').val(current_tags + \$(this).text() + ' ');
				\$(this).css('background-color', '#1c78bf');
				\$(this).css('border', 'solid 1px #9f9f9f');
				\$(this).css('color', '#ffffff');
			}
			else {
				var new_tags = current_tags.replace(\$(this).text() + ' ', '');
				\$('#tag_${num}_i:hidden').val(new_tags);
				\$(this).css('background-color', '#ffffff');
				\$(this).css('border', 'solid 1px #dfdfdf');
				\$(this).css('color', '#666666');
			}
		});
	});
/* /tag_ready num = ${num} */
__READY_CODE__;
	;

// 登録可
	return <<<__READY_CODE__
/* tag_ready num = ${num} */
	\$('.tag_${num}').css('cursor', 'pointer');

	\$('.tag_${num}').each(function() {
		\$(this).click(function() {
			var current_tags = \$('#tag_${num}_i:text').val();
			if (\$(this).css('background-color') == '#ffffff' || \$(this).css('background-color') == 'rgb(255, 255, 255)' ) {
				\$('#tag_${num}_i:text').val(current_tags + \$(this).text() + ' ');
				\$(this).css('background-color', '#ffffcc');
				\$(this).css('border', 'solid 1px #a4b7dd');
			}
			else {
				var new_tags = current_tags.replace(\$(this).text() + ' ', '');
				\$('#tag_${num}_i:text').val(new_tags);
				\$(this).css('background-color', '#ffffff');
				\$(this).css('border', 'solid 1px #dfdfdf');
			}
		});
	});
/* /tag_ready num = ${num} */
__READY_CODE__;
	;
}

function keyword_regist($eid = null, $blk_id = null, $name = 'tag_0_i') {
	global $COMUNI, $JQUERY;

	if (!$eid) { return; }

	$input_kwd = $_REQUEST[$name];

	$input_kwd = mb_convert_encoding($input_kwd, 'UTF-8', 'auto');
	$input_kwd = mb_ereg_replace("　", " ", $input_kwd);

	$keywords = split(' ', $input_kwd);

	mysql_exec("delete from tag_data where pid = %s", mysql_num($eid));
	$d = mysql_exec("delete from tag_data where pid = %s", mysql_num($eid));
	foreach ($keywords as $keyword) {
		$keyword = ereg_replace('/^ +/', '', $keyword);
		$keyword = ereg_replace('/ +$/', '', $keyword);
		if ($keyword == '') {
			continue;
		}
		$q = mysql_uniq("select * from tag_setting where keyword = %s", mysql_str($keyword));

		$tag_id = $q["id"];
		if (!$tag_id) {
			$tag_id = get_seqid();
			$f = mysql_exec("insert into tag_setting(id, keyword) values(%s, %s)",
							mysql_num($tag_id), mysql_str($keyword));
		}
		$a = mysql_exec("insert into tag_data(pid, tag_id, blk_id) values(%s, %s, %s)",
						mysql_num($eid), mysql_num($tag_id), mysql_num($blk_id));
	}
}

?>
