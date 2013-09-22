/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
var MemoBuilder = function( blockId, editable ) {

	this.blockId = blockId;
	this.block = $(".memo_block[blk_id="+this.blockId+"]");
	this.editable = editable;

	this.readyTextArea();
	if ( editable ) this.readyTextAreaEdit();

}

MemoBuilder.prototype.blockId = null;
MemoBuilder.prototype.editable = false;

MemoBuilder.prototype.readyTextArea = function() {

	var instance = this;
	var blockDom = instance.block.get(0);

	$(".memo_textarea",blockDom).each( function() {

		var name = $(this).attr("name");
		var value = $(this).val();

		value = value.replace( /&/mg, "&amp;" );
		value = value.replace( /</mg, "&lt;" );
		value = value.replace( />/mg, "&gt;" );
		value = value.replace( /"/mg, "&quot;" );

		value = value.replace( /\n\n/g, "<p>" );
		value = value.replace( /\n/g, "<br>" );

		$(this).after( $("<div>").html( value ) );

		$(this).css("display","none");

	} );

}

MemoBuilder.prototype.readyTextAreaEdit= function() {

	var instance = this;
	var blockDom = instance.block.get(0);

	$(".memo_textarea",blockDom).each( function() {

		var div = $(this).parent().find("div");

		div.css( "cursor", "pointer" )
			.click( function() {

				$(".memo_textarea",blockDom).blur();

				var textarea = $(this).parent().find("textarea");

				textarea.css("display","inline");
				$(this).css("display","none");
				textarea.focus();

			} );

	} );

	$(".memo_textarea",blockDom).blur( function() {

		var title = $(this).parent().find("h2").text();
		var name = $(this).attr("name");
		var value = $(this).val();
		var fgcolor = $(this).css("color");
		var bgcolor = $(this).css("background-color");

		var div = $(this).parent().find("div");

		if ( "" == value ) {
			div.text(title+"クリックすると編集できます")
				.css( "color", "darkgray" )
				.css( "font-style", "italic" )
				.css( "background-color", bgcolor );

		} else {

			value = value.replace( /&/mg, "&amp;" );
			value = value.replace( /</mg, "&lt;" );
			value = value.replace( />/mg, "&gt;" );
			value = value.replace( /"/mg, "&quot;" );

			value = value.replace( /\n\n/g, "<p>" );
			value = value.replace( /\n/g, "<br>" );

			div.html( value );
			div.css( "color", fgcolor )
				.css( "font-style", "normal" )
				.css( "background-color", bgcolor );

		}

		div.css("display","block");
		$(this).css("display","none");

	} )
	.change( function() {

		$.post( "modules/memo/get.php",
				{
					blk_id: instance.blockId,
					act: "regist",
					data: $(".memo_textarea",blockDom).val(),
					form_build_id: $("input[name=form_build_id]",blockDom).val()
				},
				function ( data, status ) {

					if ( 0 == data.code ) {
						//instance.showMessage("登録しました.");
						$("input[name=form_build_id]",blockDom).val( data.form_build_id );
					} else {
						instance.showMessage( data.message, true );
					}
					scroll( 0, 0 );

				},
				"json" );

	} );

	$(".memo_textarea",blockDom).blur();

}
