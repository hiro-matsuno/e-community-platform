<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

function mod_enquete_print($eid = null, $full = 0) {
	global $JQUERY;

	$vote_id = 'vote_'. rand_str(6);

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

			$form[] = array('num'        => $res['num'],
							'uniq_id'    => $res['uniq_id'],
							'type'       => $res['type'],
							'title'      => $res['title'],
							'required'   => $res['req_check'],
							'admin_only' => $res['admin_only'],
							'comment'    => $res['comment'],
							'default'    => $value,
							'opt_size'   => $size,
							'opt_list'   => $list);
		}
	}
	else {
		return;
	}

	$content = '';
	$content .= '<div class="enquete_fwb">';
	$content .= '<form action="/modules/enquete/vote.php" method="POST" class="enquete_form">';
	$content .= '<input type="hidden" name="eid" value="'. $eid. '">';

	foreach ($form as $f) {
		$vote_cid = $vote_id. $f['num'];

		$req = '';
		if ($f['required'] == 1) {
			$req = '&nbsp;<span class="enquete_srb">必須</span>';
			if ($f['type'] == 'radio') {
				$jqcode .= mod_enquete_jqcode_ary($vote_cid, $f['title'], 'enq_'. $f['uniq_id']);
			}
			else if ($f['type'] == 'checkbox') {
				$jqcode .= mod_enquete_jqcode_ary($vote_cid, $f['title'], 'enq_'. $f['uniq_id']. '[]');
			}
			else {
				$jqcode .= mod_enquete_jqcode($vote_cid, $f['title']);
			}
		}

		$adm_only = '';
		if ($f['admin_only'] == 1) {
			$adm_only = '<div class="enquete_sra">※ この項目はアンケート作成者にのみ表示されます。</div>';
		}

		$content .= '<h3 class="enquete_stb">'. $f['title']. $req. '</h3>'. "\n";
		$content .= $adm_only;
		$content .= '<div class="enquete_scb">'. $f['comment']. '</div>'. "\n";
		$content .= '<div class="enquete_errb" id="'. $vote_cid. '_err"></div>'. "\n";

		$content .= '<div class="enquete_seb" style="margin-bottom: 5px;">';
		switch ($f['type']) {
			case 'text':
				$opt = '';
				if ($f['opt_size'] > 12 && $full == 0) {
					$opt = ' style="width: 100%;"';
				}
				else {
					$opt = ' size="'. $f['opt_size']. '"';
				}
				$content .= '<input type="text" id="'. $vote_cid. '" name="enq_'. $f['uniq_id']. '" value="'. $f['default']. '" class="enquete_sib"'. $opt. '>';
				break;
			case 'textarea':
				$opt = '';
				if ($f['opt_size'] > 5 && $full == 0) {
					$opt = ' rows="5" style="width: 100%;"';
				}
				else {
					$opt = ' rows="'. $f['opt_size']. '" style="width: 100%;"';
				}
				$content .= '<textarea id="'. $vote_cid. '" name="enq_'. $f['uniq_id']. '" class="enquete_sib"'. $opt. '>'. $f['default']. '</textarea>';
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
				$content .= ' <select id="'. $vote_cid. '" name="enq_'. $f['uniq_id']. '" class="enquete_ssb">'. $list. '</select>';
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
						$content .= ' <input type="radio" name="enq_'.  $f['uniq_id']. '" value="'. $i. '" checked> '. $o;
					}
					else {
						$content .= ' <input type="radio" name="enq_'.  $f['uniq_id']. '" value="'. $i. '"> '. $o;
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
						$content .= ' <input type="checkbox" name="enq_'.  $f['uniq_id']. '[]" value="'. $o. '" checked> '. $o;
					}
					else {
						$content .= ' <input type="checkbox" name="enq_'.  $f['uniq_id']. '[]" value="'. $o. '"> '. $o;
					}
				}
				break;
			default:
				;
		}
		$content .= '</div>';
	}

	$content .= '<div class="enquete_sswb"><input type="submit" value="送信" class="enquete_sbb" id="'.
				$vote_id. '_submit" style="cursor: pointer;"></div>';

	$content .= '</form></div>';

	$JQUERY["ready"][] = <<<___READY_CODE__
$('#${vote_id}_submit').click(function() {
${jqcode}
if (err_msg) {
	err_msg = null;
	return false;
}
return true;
});
___READY_CODE__;
	;

	return $content;
}

function mod_enquete_jqcode($id = '', $title = '') {
	return "if (\$('#${id}').val() == '') { \$('#${id}_err').html('必須項目なので入力してください。'); err_msg = true; } else { \$('#${id}_err').html(''); }\n";
}

function mod_enquete_jqcode_ary($id = '', $title = '', $name = '') {
	return "if (!\$('input[name=\"${name}\"]:checked').val()) { \$('#${id}_err').html('必須項目なので入力してください。'); err_msg = true; } else { \$('#${id}_err').html(''); }\n";
}

function mod_enquete_result($eid = null, $result = 0) {
	if ($result > 0) {
		return '<div align="right"><a href="/index.php?action=result&module=enquete&eid='. $eid. '">結果 &raquo;</a></div>';
	}
}

function mod_enquete_none() {
	return 'ただ今、実施しているアンケートはありません。';
}

function mod_enquete_get_pid($eid = null) {
	$d = mysql_uniq("select * from mod_enquete_element_relation where id = %s",
					mysql_num($eid));
	if ($d) {
		return $d['pid'];
	}
	else {
		return false;
	}
}

function mod_enquete_set_pid($eid = null, $pid = null) {
	if ($pid == null) {
		return;
	}
	$d = mysql_exec("delete from mod_enquete_element_relation where id = %s",
					mysql_num($eid));
	$i = mysql_exec("insert into mod_enquete_element_relation (id, pid)".
					" values (%s, %s)",
					mysql_num($eid), mysql_num($pid));
	return;
}


?>
