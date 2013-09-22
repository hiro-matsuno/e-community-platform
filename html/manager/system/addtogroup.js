/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
var AddToGroupBuilder = function() {

	this.readyChangeGroup();
	this.readyChangeLevel();
	this.readyChangeSubmit();
	this.readyCancelButton();

}

AddToGroupBuilder.prototype.readyChangeGroup = function() {

	$("#change_group").find("select").change( function() {
		$("#change_group").submit();
	} );

}

AddToGroupBuilder.prototype.readyChangeLevel = function() {

	$("select[name=group_level]").change( function() {

		var asterisk = $("<span class=\"changed\">*</span>");
		asterisk.css( "color", "red" );

		var handle = $(this).parents("tr").find(".handle");

		if ( 0 == handle.find(".changed").length ) {
			handle.append( asterisk );
		}

		showMessage("状態が変更されました. 適用するには、「登録」ボタンを押してください",true);

	} );

}

AddToGroupBuilder.prototype.readyChangeSubmit = function() {

	$("#regist_form").submit( function() {

		var changeList = [];

		$("#user_list tr").each( function() {

			if ( 0 < $(this).find(".changed").length ) {

				var uid = $(this).find(".uid").attr("uid");
				var level = $(this).find("select[name=group_level]").val();

				var obj = {uid: uid, level: level};

				changeList.push( obj );

			}

		} );

		if ( 0 == changeList.length ) {showMessage("変更はありません.",true);return false;}
		
		var str = JSON.stringify( changeList );

		$(this).append( $("<input type=\"hidden\" name=\"setting\" value='"+str+"' />") );

	} );

}

AddToGroupBuilder.prototype.readyCancelButton = function() {

	$("input[name=cancel]").click( function() {
		document.location.href = "default.php";
	} );

}

function showMessage( message, attention ) {

	$(".message").remove();

	if ( undefined == attention ) {attention = false;}

	var mes = $("<i>" + message + "</i>");
	
	mes.css("font-size","0.8em");
	mes.css("margin","8px");
	mes.css("cursor","pointer");

	if ( attention ) {
		mes.css("border","1px solid red");
		mes.css("color","red");
	} else {
		mes.css("border","1px solid green");
		mes.css("color","green");
	}

	mes.click( function() {

		$(this).fadeOut( "normal", function() {
			$(this).slideUp("slow", function() {
				$(this).remove();
			});
		} );

	} );

	$("#addtogroup_main").prepend( $("<div class=\"message\">").append( mes ) );

}