/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
var FileboxBlockBuilder = function( blockId, sessionId, gid, serverName ) {

	this.blockId = blockId;
	this.block = $(".filebox_block[blk_id="+blockId+"]");

	this.sessionId = sessionId;
	this.gid = gid;
	this.serverName = serverName;

	this.readyApplet();
	//this.readyList();

};

FileboxBlockBuilder.prototype.blockId = null;
FileboxBlockBuilder.prototype.block = null;

FileboxBlockBuilder.prototype.sessionId = null;
FileboxBlockBuilder.prototype.gid = null;
FileboxBlockBuilder.prototype.serverName = null;


FileboxBlockBuilder.prototype.readyApplet = function() {

	var instance = this;
	var block = this.block;
	var blockDom = block.get(0);

	$("filebox_block_upload_applet",blockDom).hide();

	$(".filebox_block_open_applet",blockDom).click( function () {

		if ( !$(".filebox_block_open_applet",blockDom).hasClass("appletOpened") ) {

			$(".filebox_block_open_applet",blockDom).addClass("appletOpened");
			$(".filebox_block_open_applet",blockDom).text("アップロードフォームを閉じる");

			var width = $(".filebox_block_open_applet",blockDom).parent().width();
			if ( 200 < width ) width = 200;

			if ( 0 == $(".filebox_block_upload_applet",blockDom).find("applet").length ) {

				$(".filebox_block_upload_applet",blockDom)
					.append( $( "<applet code=\"org.oklab.upload.Applet\""
							+ " archive=\"/modules/filebox/DnD.jar,/modules/filebox/jsonic-1.1.3.jar\""
							+ " width=\"" + width + "\""
							+ " height=\"" + width * 1.0 + "\">"
							+ "<param name=\"server_url\" value=\"http://" + instance.serverName + "\">"
							+ "<param name=\"sessionid\" value=\"" + instance.sessionId + "\">"
							+ ( null != instance.gid ? "<param name=\"gid\" value=\"" + instance.gid + "\">" : "" )
							+ "<param name=\"program_name\" value=\"uploader\">"
							+ "<param name=\"type_file_name\" value=\"file\">"
							+ "Java アプレットを利用できません."
							+ "ご利用のPCの環境を確認してください."
							+ "</applet>"
							+ "<div>"
							+ "<i style=\"font-color: darkgray\">"
							+ "ファイルをアップロードした後、ページをリロードすると"
							+ "下のリストに反映されます."
							+ "</i>"
							+ "</div>" ) );

			}

			$(".filebox_block_upload_applet",blockDom).show('slow');

		} else {
			$(".filebox_block_open_applet",blockDom).removeClass("appletOpened");
			$(".filebox_block_upload_applet",blockDom).hide("fast");
			$(".filebox_block_open_applet",blockDom).text("アップロードフォームを開く");
		}

	} );

}

FileboxBlockBuilder.prototype.readyList = function() {

	var block = this.block;
	var blockDom = block.get(0);

	jQuery(".filebox_block_select",blockDom).change( function() {

		var val = jQuery(this).val();

		if ( 'view_thumb' == jQuery(this).val() ) {
			jQuery(".filebox_block_listview .filebox_thumbbox",blockDom).show();
			jQuery(".filebox_block_listview .filebox_listbox",blockDom).hide();
		} else {
			jQuery(".filebox_block_listview .filebox_thumbbox",blockDom).hide();
			jQuery(".filebox_block_listview .filebox_listbox",blockDom).show();
		}

		document.cookie = "filebox_block_listview=" + val
							+ "; expires=Tue, 1-Jan-2030 00:00:00 GMT;";

	} );

	var sCookie = document.cookie;
	var aData = sCookie.split(";");
	var oExp = new RegExp(" ", "g");

	var val = 'view_thumb';

	for ( var i=0; aData.length > i; ++i ) {
		var aWord = aData[i].split("=");
		aWord[0] = aWord[0].replace(oExp, "");
		if ( 'filebox_block_listview' == aWord[0] ) {
			val = unescape(aWord[1]);
			break;
		}
	}

	jQuery(".filebox_block_select",blockDom).val( val );
	jQuery(".filebox_block_select",blockDom).change();

}
