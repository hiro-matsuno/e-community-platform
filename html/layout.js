/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
function Layout( eid ) {

	var updateUpDown = function(sortable){
		jQuery('div.box', sortable)
			.removeClass('first').removeClass('last')
			.find('.up, .down').removeClass('disabled').end()
			.filter(':first').addClass('first').find('.up').addClass('disabled').end().end()
			.filter(':last').addClass('last').find('.down').addClass('disabled').end().end();

	};

	var emptyTrashCan = function(item){
		item.remove();
	};

	var sortableChange = function(e, ui){
		if(ui.sender){
			var w = ui.element.width();
			ui.placeholder.width(w);
			ui.helper.css("width",ui.element.children().width());
		}
	};

	var sortableUpdate = function(e, ui) {
		if(ui.item[0].id == 'trashcan'){
			emptyTrashCan(ui.item);
		} else {
			updateUpDown(ui.item[0]);
			if(ui.sender)
				updateUpDown(ui.sender[0]);
		}
	};
	var sortableStop = function(e, ui) {
		var q = '';
		for (var i = 1; i <= 10; i++) {
			var d = document.getElementById("space_" + i);
			if (!d || d.getContext) {
				break;
			}
			if (q != '') q = q + '&';
			q = q + "space_" + i + '=';
			s = jQuery("#space_" + i).sortable('serialize');
			if (s != '') {
				s_array = s.split('&');
				for (var j = 0; j < s_array.length; j++) {
					if (j > 0) q = q + ',';
					k = s_array[j].split('=');
					q = q + k[1];
				}
			}
//			alert(q);
		}
//		jQuery('#debug_window').html(q);

		var query = 'save=1&eid=' + eid;

		jQuery('#post_status').html('');
		jQuery.each(["space_1", "space_2", "space_3", "space_4", "space_5"], function() {
				if (document.getElementById(this)) {
				if (query != '') {query += '&';}
				query += this + '=' + jQuery('#' + this).sortable('toArray');
			}
		});

		query = query.replace(/box_/g, '');

		jQuery.ajax({
			url: '/layout.php',
			type: 'POST',
			dataType: 'html',
			timeout: 5000,
			data : query,
			beforeSend : function(){
//					jQuery('#post_status').html(query).fadeIn(200);
//					jQuery('#post_status').html('送信中...').fadeIn(200);
			},
			error: function() {alert('Error Occured');},
			success: function(r){
				jQuery('#post_status').html('');
				//alert( "レイアウトを保存しました。");
			}});
	}

	var els = ['div#space_1', 'div#space_2', 'div#space_3', 'div#space_4', 'div#space_5'];

	var els = jQuery(els.toString());

	els.sortable({
		items: 'div.box',
		handle: 'div.box_menu',
		cursor: 'move',
		//cursorAt: { top: 2, left: 2 },
		//opacity: 0.8,
		//helper: 'clone',
//			appendTo: 'body',
		//placeholder: 'clone',
		//placeholder: 'placeholder',
		connectWith: els,
		start: function(e,ui) {
			ui.helper.css("width", ui.item.width());
		},
		stop: sortableStop,
		change: sortableChange,
		update: sortableUpdate
	});

	jQuery(window).bind('load',function(){
		setTimeout(function(){
			jQuery('#overlay').fadeOut(function(){
				jQuery('body').css('overflow', 'auto');
			});
		}, 750);
	});

	jQuery(".box_menu").css("cursor","move");

}

function BlockMenu() {

	var z=100;

	jQuery(".box_edit").each( function() {

		if ( 0 == jQuery("a",this).length ) {return;}

		var box_menu = jQuery(this).parent().find(".box_menu");

		var menu = jQuery('<ul class="nav_droppy"></ul>');

		var pub = jQuery(".ecom_block_pmt_icon",this);

		var menu_item = jQuery("<li><img src=\"/image/icons/menu.png\" width=\"16px\" /></li>");

		var menu_sub = jQuery('<ul>');
		jQuery("a",this).each( function() {

			menu_sub.append( jQuery("<li>").append( jQuery(this) ) );

		} );

		jQuery(this).empty();

		pub.css( "float", "right" );
		box_menu.prepend( pub );

		menu.append( menu_item.append( menu_sub ) );
		menu.css( "float", "right" );
		menu.css( "text-align", "right" );
		menu.css( "cursor", "default" );

		box_menu.prepend( menu );

		var userAgent = window.navigator.userAgent.toLowerCase();

		if ( -1 < userAgent.indexOf("msie") ) {
			box_menu.css( "position", "relative" );
			box_menu.css( "z-index", --z );
		}

		menu.droppy();

	} );

}