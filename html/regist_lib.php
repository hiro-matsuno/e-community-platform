<?php
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
function regist_form_regist($gid=1){
	$req = isset($_POST['req']) ? $_POST['req'] : array();
	$num = isset($_POST['num']) ? $_POST['num'] : array();
	$default = isset($_POST['default']) ? $_POST['default'] : array();
	$new_item = isset($_POST['new_item']) ? $_POST['new_item'] : array();
	$del = isset($_POST['del']) ? $_POST['del'] : array();
	
		//削除フォームを削除
	foreach($del as $id){
		mysql_exec('delete from join_req_info where gid=%s and req_id=%s',
					mysql_num($gid),mysql_num($id));
		$q = mysql_uniq('select req_id from join_req_info where req_id=%s limit 1',
						mysql_num($id));
		if(!$q)
			mysql_exec('delete from prof_add_req where gid=%s and req_id=%s',
						mysql_num($gid),mysql_num($id));
	}

	//新フォームを設定
	$new_id=array();
	foreach($new_item as $id => $data){
		if($data['del'])continue;
		$options = '';
		if($data != 'text' and $data != 'textarea')
			$options = str_replace("\r","\n",str_replace("\r\n","\n",$data['options']));
		$def_val = $default[$id];
		if($data['type'] == 'checkbox')
			$def_val = implode("\n",$def_val);
			//$default = implode('"',explode("\n",htmlspecialchars($data['options'])));
		mysql_exec('insert into prof_add_req (gid,type,title,opt_list,def_val)'.
					' values (%s,%s,%s,%s,%s)',
					mysql_num($gid),mysql_str($data['type']),mysql_str($data['title']),
					mysql_str($options),mysql_str($def_val));
		//if(!$q)print mysql_error();
		$new_id[$id] = mysql_insert_id();
		mysql_exec('insert into join_req_info (gid,req_id) values (%s,%s)',
					mysql_num($gid),mysql_num($new_id[$id]));
		//if(!$q)print mysql_error();
	}

	//順序とデフォルト値と必須を設定
	foreach($num as $ord=>$id){
		if(strncmp($id,'a_',2) == 0)$req_id = $new_id[$id];
		else $req_id = $id;
		if(isset( $req[$id]))$req_check=1;
		else $req_check=0;
		$q = mysql_uniq('select type from prof_add_req where req_id=%s',
								mysql_num($req_id));
		$type = $q['type'];
		if(!$type)continue;
		$val = $default[$id];
		if($type == 'checkbox')//$val = implode('"',explode("\n",htmlspecialchars($val)));
			$val = implode("\n",$val);
		mysql_exec('update join_req_info set num=%s,req_check=%s,def_val=%s where gid=%s and req_id=%s',
					mysql_num($ord),mysql_num($req_check),mysql_str($val),mysql_num($gid),mysql_num($req_id));
	}
}

function regist_data_read_all($req_id){
	$datas = array();
	$q = mysql_full('select * from prof_add_data where req_id=%d',mysql_num($req_id));
	if($q){
		while($r = mysql_fetch_assoc($q)){
			$datas[] = $r;
		}
	}
	return $datas;
}
function regist_form($add_items,$confirm = false){
	if($confirm){
		$readonly = ' readonly';
		$disabled = ' disabled';
	}else{
		$readonly = '';
		$disabled = '';
	}
	$add_form = <<<__HTML__
<style type="text/css">
span.required {color: #FF0000;}
</style>
__HTML__;
	foreach($add_items as $item){
		$form = '';
		$hidden = '';
		$value = (isset($item['data'])? $item['data'] : $item['default']);
		if(isset($_REQUEST['add_form']))$value = $_REQUEST['add_form'][$item['id']];
		if(!$confirm)
			$req = $item['req']? '<span class="required">*</span>' : '';
		$title = htmlspecialchars($item['title']);
		switch($item['type']){
			case 'text':
				$value = htmlspecialchars($value);
				$form = "<input class='input_text' type='text' name='add_form[$item[id]]' value=\"$value\" $readonly>";		
				break;
			case 'textarea':
				$value = htmlspecialchars($value);
				$form = "<textarea class='input_text' name='add_form[$item[id]]' $readonly>$value</textarea>";		
				break;
			case 'select':
				$options = '';
				foreach($item['options']as $opt){
					$selected = ($opt == $value? 'selected' : '');
					$options .= "<option $selected>".htmlspecialchars($opt)."</option>";
				}
				if($confirm){
					$form = "<select name='add_form_view[$item[id]]' $disabled>$options</select>";
					$v=htmlspecialchars($value,ENT_QUOTES);
					$hidden = "<input type='hidden' name='add_form[$item[id]]' value='$v'>";
				}else{
					$form = "<select name='add_form[$item[id]]' $disabled>$options</select>";
				}
				break;
			case 'checkbox':
				$form='';$i=0;
				foreach($item['options'] as $opt){
					$checked = '';
					foreach($value as $d)
						if($d == $opt)$checked = 'checked';
					$opt = htmlspecialchars($opt);
					//$form .= '<div style="float: left; margin-right: 3px;">';
					if($confirm)
						$form .= "<input type='$item[type]' name='add_form_view[$item[id]][]' value=\"$opt\" $checked $disabled id='add_form_view_$item[id]_$i'><label for='add_form_view_$item[id]_$i' style='margin-left: 2px;;margin-right: 5px;white-space:nowrap;'>$opt</label>";
					else
						$form .= "<input type='$item[type]' name='add_form[$item[id]][]' value=\"$opt\" $checked $disabled id='add_form_$item[id]_$i'><label for='add_form_$item[id]_$i' style='margin-left: 2px;margin-right: 5px;white-space:nowrap;'>$opt</label>";
					//$form .= '</div>';
					if($confirm){
						foreach($value as $d){
							$d = htmlspecialchars($d,ENT_QUOTES);
							$hidden = "<input type='hidden' name='add_form[$item[id]][]' value='$d'>";
						}
					}
					$i++;
				}
				break;
			case 'radio':
				$form='';$i=0;
				foreach($item['options'] as $opt){
					$checked = ($opt == $value? 'checked':'');
					$opt = htmlspecialchars($opt);
					$form .= '<div style="float: left; margin-right: 3px;">';
					if($confirm)
						$form .= "<input type='$item[type]' name='add_form_view[$item[id]]' value=\"$opt\" $checked $disabled id='add_form_view_$item[id]_$i'><label for='add_form_view_$item[id]_$i' style='margin: 2px;margin-right: 5px;white-space:nowrap;'>$opt</label>";
					else
						$form .= "<input type='$item[type]' name='add_form[$item[id]]' value=\"$opt\" $checked $disabled  id='add_form_$item[id]_$i'><label for='add_form_$item[id]_$i' style='margin-left: 2px;margin-right: 5px;white-space:nowrap;'>$opt</label>";
					$form .= '</div>';
					$i++;
				}
				$v = htmlspecialchars($value,ENT_QUOTES);
				if($confirm)
					$hidden = "<input type='hidden' name='add_form[$item[id]]' value='$v'>";
				break;
		}
		$add_form .= <<<__HTML__
<tr>
<th>$title$req</th>
<td>$form$hidden</td>
</tr>
__HTML__;
	}
	return $add_form;
}

function regist_data_get_reqs($gid=1){
	$add_items = array();
	$q = mysql_full('select jri.del_lock,jri.req_id,jri.req_check,jri.def_val,par.title,par.opt_list,par.type'.
					' from join_req_info as jri'.
					' inner join prof_add_req as par on jri.req_id = par.req_id'.
					' where jri.gid = %s order by jri.num', mysql_num($gid));
	
	if($q){
		while($r = mysql_fetch_assoc($q)){
	                        $item = array('id' => $r['req_id'],
	                                                'title' => $r['title'],
	                                                'type' => $r['type']);
	                        $item['req'] = ($r['req_check']?true:false);
	                        $item['del_lock'] = ($r['del_lock']?true:false);
	                        switch($r['type']){
	                                case 'text':
	                                case 'textarea':
	                                        $item['default'] = $r['def_val'];
	                                        break;
	                                case 'checkbox':
	                                        $item['options'] = explode("\n",$r['opt_list']);
	                                        $item['default'] = explode("\n",$r['def_val']);
	                                        break;
	                                default:
	                                        $item['options'] = explode("\n",$r['opt_list']);
	                                        $item['default'] = $r['def_val'];
	                        }
			$add_items[$item['id']] = $item;
		}
	}
	return $add_items;
}
function regist_data_get_reqdata($uid,$gid=1){
	$add_items = regist_data_get_reqs($gid);

	$pd = mysql_full('select jri.req_id,jri.req_check,jri.def_val,par.title,par.opt_list,par.type,pad.data'.
					' from join_req_info as jri'.
					' inner join prof_add_req as par on jri.req_id = par.req_id'.
					' left join prof_add_data as pad on jri.req_id = pad.req_id'.
					' where jri.gid = %s and pad.uid = %s order by jri.num',
	mysql_num($gid), mysql_num($uid));

	if($pd){
		while($r = mysql_fetch_assoc($pd)){
			if($add_items[$r['req_id']]['type'] == 'checkbox')
				$add_items[$r['req_id']]['data'] = explode("\n",$r['data']);
			else
				$add_items[$r['req_id']]['data'] = $r['data'];
		}
	}
	return $add_items;
}
function array_to_js($array,$is_escape){
	$new_a = array();
	foreach($array as $e){
		if($is_escape)$e = htmlspecialchars($e);
		$new_a[] =  '"'.$e.'"';
	}
	return 'new Array('.implode(',',$new_a).')';
}
function regist_form_create_html($items = array(),$default_items = array()){
	global $COMUNI_HEAD_JS,$COMUNI_HEAD_CSSRAW,$JQUERY;
		$COMUNI_HEAD_CSSRAW[] = <<<__CSS__
#regist_item{border:solid}
#regist_item td {
	padding: 4px;
	text-align: left;
}
#regist_item .handle {
	width: 10em;
	background-color: #f1f1f1;
	padding: 4px;
	text-align: left;
}
#regist_item .chk{
	padding: 4px;
	width: 2.5em;
}
#regist_item th {
	background-color: #f1f1f1;
	padding: 4px;
	text-align: left;
}
#regist_item_table {
	width: 100%;
}
span.required {color: #FF0000;}
#form_add_input td{
	padding: 4px;
}
__CSS__;

	//登録済みフォームを表現するJavaScriptコード生成
	//ページ読み込み時にJavaScriptでフォームのプレビューを表示
	$js_add_str = '';
	foreach($items as $item){
		$req = $item['req']? 'true' : 'false';
		$del_lock = $item['del_lock'] ? 'true' : 'false';
		if(is_array($item['options']))
			$options = array_to_js($item['options'],true);
		else
			$options = '"'.htmlspecialchars($item[options]).'"';
		if(is_array($item['default']))
			$default = array_to_js($item['default'],true);
		else
			$default = '"'.str_replace("\n",'\n', htmlspecialchars($item['default'])).'"';
		$title = htmlspecialchars($item['title']);
		$js_add_str .= <<<__str__
	items.push({'id' : $item[id], 'title' : "$item[title]", 'type' : "$item[type]",
				'options' : $options, 'default' : $default,'req': $req, 'del_lock': $del_lock});

__str__;
	}

	$JQUERY["ready"][] = <<<__JS__
	var items = Array();
$js_add_str 
	jQuery('table#regist_item_table').sortable({items:'tr[class!="nosort"]',cursor:'move',handle:'td.handle',axis:'y'});
	jQuery.each(items, function() {
		add_form(this['id'],this['title'],this['type'],this['options'],this['default'],this['req'],false,this['del_lock']);
	});

	jQuery('#form_options_input').hide();
	jQuery('#form_type').change(form_type_sel);
	jQuery('#form_add').click(btn_add_form);
__JS__;

	$COMUNI_HEAD_JS[] = CONF_URLBASE.'/regist_lib.js';
	
	$items_html = '';

	$default_tr = '';
	foreach($default_items as $item){
		if($item['req'])$req = '<span class="required">*</span>';
		else $req = '';
		$default_tr .= <<<__TR__
<tr class='nosort'><td>$req</td><td>-</td><th>$item[title]</th><td><input type='text' readonly></td></tr>
__TR__;
	}

	$items_html = <<<__FORM_ITEMS__
タイトルをクリックして順序を変更できます<br>
各入力項目に初期値が必要な場合はここに入力してください
$add_item_html
<div id='regist_item'>
<table id='regist_item_table'>
<tr class='nosort'><th>必須</th><th>削除</th><th>タイトル</th><th>入力フォーム</th></tr>
$default_tr
</table>
</div>
__FORM_ITEMS__;

	$items_html .= <<<__HTML__
<div style="text-align:center;margin:10px;">
<div style="width:18em; max-width:100%; margin-left: auto; margin-right: auto; text-align:center;">
<div style="border: 1px #efefef solid;width:auto;">
<h3>登録事項の追加</h3>
<table id='form_add_input'>
<tr>
<th>種類</th>
<td>
<select id="form_type" class="input_form">
<option value="none" selected="selected">追加するフォームの選択</option>
<option value="text">文字入力（一行）</option>
<option value="textarea">文字入力（複数行）</option>
<option value="select">一つを選択（リスト）</option>
<option value="radio">一つを選択（ラジオボタン）</option>
<option value="checkbox">複数選択（チェックボックス）</option>
</select>
</td>
</tr>
<tr>
<th>題名</th>
<td>
<input type='text' id='form_title' class='input_text'>
</td>
<tr id='form_options_input'>
<th>リスト入力 (改行区切り)</th>
<td><textarea id='form_options' class="input_text"></textarea></td>
</tr>
<tr><th></th><td>
<input type='button' id='form_add' value='追加' class='input_submit' style="background:#f8d7e5;">
</td></tr>
</table>
</div></div></div>
__HTML__;
	
	return $items_html;
}
function regist_chk_req($items){
	$error_msg = '';
	foreach($items as $idx => $item){
		$value = $_REQUEST['add_form'][$item['id']];
		if($item['req'] and (!isset($_REQUEST['add_form'][$item['id']]) or !$_REQUEST['add_form'][$item['id']]))
			$error_msg .= htmlspecialchars($item['title']).'を入力してください<br>';
	}
	return $error_msg;
}
function regist_data_data($uid){
	foreach($_REQUEST['add_form'] as $req_id => $value){
		if(!$value)continue;
		if(is_array($value))$value = implode("\n",$value);
		else $value = str_replace("\r","\n",str_replace("\r\n","\n",$value));
		mysql_exec('delete from prof_add_data where uid=%s and req_id=%s',
					mysql_num($uid),mysql_num($req_id));
		mysql_exec('insert into prof_add_data (uid,req_id,data) values (%s,%s,%s)',
					mysql_num($uid),mysql_num($req_id),mysql_str($value));
	}
}
?>
