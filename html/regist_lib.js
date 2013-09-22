/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

var num_new = 0;
var use_list=false;
function form_type_sel(){
	switch(this.value){
		case 'none':
		case 'text':
		case 'textarea':
			jQuery('#form_options_input').hide();
			use_list=false;
			break;
		default:
			jQuery('#form_options_input').show();
			use_list=true;
	}
}
function btn_add_form(){
	error_messg = '';
	if(jQuery('#form_title').val() == ''){
		error_messg += "題名を入力してください\n";
	}
	if(use_list && jQuery('#form_title').val() == ''){
		error_messg += "選択肢のリストを入力してください\n";
	}
	if(jQuery('#form_type').val() == 'none'){
		error_messg = "追加するフォームの種類を選択してください\n";
	}
	if(error_messg != ''){
		alert(error_messg);
		return;
	}

	id='a_'+num_new;

	jQuery('#regist_item').append(make_hidden('new_item['+id+'][title]',jQuery('#form_title').val())+
							make_hidden('new_item['+id+'][type]',jQuery('#form_type').val())+
							make_hidden('new_item['+id+'][options]',jQuery('#form_options').val())
							);

	opt = jQuery('#form_options').val().split(String.fromCharCode(10));

	if(jQuery('#form_type').val() == 'checkbox'){
		defval=new Array();
	}else{
		defval='';
	}

	add_form(id,jQuery('#form_title').val(),jQuery('#form_type').val(),opt,defval,true,true,false);

	jQuery('#form_type').val('none');
	jQuery('#form_title').val('');
	jQuery('#form_options').val('');

	num_new++;
}

function add_form(id,title,type,options,defval,req,new_item,del_lock){
	switch(type){
		case 'text':
			content = '<input type="text" name="default['+id+']" value="'+escapehtml(defval)+'" class="input_text">';
			break;
		case 'textarea':
			content = "<textarea name='default["+id+"]' class='input_text'>"+escapehtml(defval)+"</textarea>";
			break;
		case 'select':
			content = "<select name='default["+id+"]'>";
			jQuery.each(options,function(){
				if(defval == this){
					selected=' selected'
				}else{
					selected='';
				}
				content += "<option"+selected+">"+escapehtml(this)+"</option>";
			});
			content += "</select>";
			break;
		case 'radio':
			content = '';
			jQuery.each(options,function(){
				if(defval==this){
					checked=' checked'
				}else{
					checked='';
				}
				content += '<div style="float: left; margin-right: 3px;">';
				content += '<input type="'+type+'" name="default['+id+']" value="'+escapehtml(this)+'" '+checked+'>'+'<label style="margin: 2px;">'+escapehtml(this)+'</label>';
				content += '</div>'
			});
			break;
		case 'checkbox':
			content = '';
			jQuery.each(options,function(){
				if(array_in(defval,this)){
					checked=' checked'
				}else{
					checked='';
				}
				content += '<div style="float: left; margin-right: 3px;">';
				content += '<input type="'+type+'" name="default['+id+'][]" value="'+escapehtml(this)+'" '+checked+'>'+'<label style="margin: 2px;">'+escapehtml(this)+'</label>';
				content += '</div>'
			});
			break;
	}

	add_item(id,escapehtml(title),content,req,new_item,del_lock);
}

function add_item(id,title,content,req,new_item,del_lock){
	if(req)checked='checked';
	else checked = '';

	if(del_lock){
		del= '';
	}else{
		if(new_item)del="<input type='button' class='form_del_new' name='"+id+"' value='削除'>";
//		else del="<input type='checkbox' name='del[]' value='"+id+"'>";
		else del="<input type='button' class='form_del' name='"+id+"' value='削除'>";
	}

	jQuery('table#regist_item_table').append("<tr id='"+id+"'><td class='chk'><input type='checkbox' name='req["+id+"]' "+checked+" value='1'></td><td class='chk'>"+del+"</td><td class='handle'>"+title+"<input type='hidden' name='num[]' value='"+id+"'></td><td>"+content+"</td></tr>");
	jQuery('td.handle').css('cursor','move');

	jQuery('input.form_del_new').click(function(){
		jQuery('#regist_item').append(make_hidden('new_item['+id+'][del]','1'));
		jQuery('#'+this.name).remove();
	});
	jQuery('input.form_del').click(function(){
		jQuery('#regist_item').append(make_hidden('del[]',id));
		jQuery('tr#'+this.name).remove();
	});
}

function escapehtml(text){
	return String(text).replace(/&/g,"&amp;").
				replace(/</g,"&lt;").
				replace(/>/g,"&gt;").
				replace(/"/g,"&quot;");
}

function make_hidden(key,val){
	return '<input type="hidden" name="'+key+'" value="'+escapehtml(val)+'">';
}

function array_in(a,e){
	for(i=0;i<a.length;i++){
		if(a[i]==e){return true;}
	}
	return false;
}
