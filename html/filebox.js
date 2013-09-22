/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
var FileboxBuilder = function( selected_folder, root ) {

	var instance = this;

	this.selected_folder = selected_folder;
	
	if ( undefined != root ) { this.root = root; }
	else { this.root = jQuery("body"); }

	this.rootDom = this.root.get(0);

}

FileboxBuilder.prototype = {
	selected_folder: 0,
	root: null,
	rootDom: null
};

FileboxBuilder.prototype.readyUpload = function() {

	var instance = this;

	jQuery("#upload form",instance.rootDom).submit( function() {

		if ( 1 < parseInt( instance.selected_folder ) ) {
			jQuery(this).append('<input type="hidden" name="gid" value="'+instance.selected_folder+"\">");
		}

		return true;

	} );

}

FileboxBuilder.prototype.readyPmt = function() {

	var instance = this;

	jQuery("#upload",instance.rootDom).find("input[name=unit]").click( function() {

		var val = jQuery(this).val();

		document.cookie = "filebox_default_pmt=" + val
							+ "; expires=Tue, 1-Jan-2030 00:00:00 GMT;";

	} );

   var sCookie = document.cookie;
   var aData = sCookie.split(";");
   var oExp = new RegExp(" ", "g");

   var val = '0';

   for ( var i=0; aData.length > i; ++i ) {
		var aWord = aData[i].split("=");
		aWord[0] = aWord[0].replace(oExp, "");
		if ( 'filebox_default_pmt' == aWord[0] ) {
			val = unescape(aWord[1]);
			break;
		 }
   }

	var userAgent = window.navigator.userAgent.toLowerCase();
	
	if ( -1 < userAgent.indexOf("msie 7.0") ) {
		jQuery("#upload",instance.rootDom).find("input[name=unit]").get(0).defaultChecked = false;
		jQuery("#upload",instance.rootDom).find("input[name=unit][value="+val+"]").get(0).defaultChecked = true;
	} else {
		jQuery("#upload",instance.rootDom).find("input[name=unit][value="+val+"]").get(0).checked = true;
	}

}

FileboxBuilder.prototype.readyFolder = function() {

	var instance = this;

	jQuery(".filebox_page",instance.rootDom).hide();

	jQuery(".filebox_folder",instance.rootDom).click( function() {

		jQuery(".filebox_page",instance.rootDom).hide();
		jQuery(".filebox_folder",instance.rootDom).removeClass("filebox_folder_selected");

		var folder_id = jQuery(this).attr('folder_id');

		instance.selected_folder = folder_id;

		jQuery(".filebox_folder[folder_id="+folder_id+"]",instance.rootDom).addClass("filebox_folder_selected");
		jQuery(".filebox_page[folder_id="+folder_id+"]",instance.rootDom).show();

		jQuery(".filebox_page[folder_id="+folder_id+"]",instance.rootDom)
			.find(".filebox_thumbbox img").trigger("pageopen");

		var fileNum = jQuery(".filebox_page[folder_id="+folder_id+"]",instance.rootDom)
						.find(".filebox_div").length;

		jQuery("#filebox_foldername",instance.rootDom).empty();

		jQuery("#filebox_foldername",instance.rootDom).append( jQuery("<div>")
			.append( jQuery(this).text() + " (" + fileNum + "件)" ).css("float","left") );

		//	アップロードダイアログのメッセージを変更
		var toFolderName = ( 1 == folder_id ) ? "マイフォルダ" : jQuery(this).text();
		jQuery("#upload_to_folder",instance.rootDom).text( "「"+toFolderName+"」にアップロードされます" )

		if ( 1 == folder_id ) {

			var q = jQuery("<div>").append( "ごみ箱を空にする" );
			q.css( "float", "right" );
			q.css( "cursor", "pointer" );
			jQuery("#filebox_foldername",instance.rootDom).append( q );

			jQuery("#filebox_foldername",instance.rootDom).append( jQuery("<div>").css("clear","both") );

			q.click( function() {

				if ( !window.confirm("ごみ箱を空にします。\n"
									+ "この操作は元に戻すことが出来ません。よろしいですか?") ) {
					return;
				}

				jQuery.post(
					"filebox.php",
					{act: "clear_trash"},
					function( data, status ) {

						if ( 0 == data.code ) {

							jQuery(".filebox_trash_page .filebox_div",instance.rootDom).each( function() {
								removeListItem( jQuery(this) );
							} );
							showMessage( "ごみ箱を空にしました." );

						} else {
							showMessage( data.message, true );
						}

					},
					"json"
				);

			} );

		}

	});

	$(window).load( function() {

		jQuery(".filebox_folder[folder_id="+instance.selected_folder+"]",instance.rootDom).click();

		$("body").animate( { opacity: 1.0 }, 1000, function () {

			var folder = jQuery(".filebox_folder[folder_id="+instance.selected_folder+"]",instance.rootDom);

			var panel = jQuery(".filebox_folderlist > div",instance.rootDom);

			var folder_top = folder.get(0).offsetTop;
			var panel_top = panel.get(0).offsetTop;
			var top = folder_top - panel_top;
			var height = panel.get(0).offsetHeight;

			if ( top > height ) {
					panel.attr( "scrollTop", top );
			}

		} );

	} );

}

FileboxBuilder.prototype.readyDroppable = function() {

	var instance = this;

	jQuery(".filebox_droppable",instance.rootDom).droppable({

		accept: '.filebox_draggable',
		drop: function(e, ui) {

			var droppable = jQuery(this);

			var param;
			var callback;

			var from_page = ui.draggable.parents(".filebox_page");
			var to_folder = droppable;
			var to_page = jQuery(".filebox_page[folder_id="+to_folder.attr("folder_id")+"]",instance.rootDom);
			var file_id = ui.draggable.attr("file_id");

			if ( droppable.attr("folder_id") == from_page.attr("folder_id") ) {
				showMessage("同じフォルダです.", true);
				return;
			}

			function callback_copy( data, status ) {

				if ( 0 == data.code ) {

					var q = jQuery( data.html );

					to_page.prepend(q);
					new ListItemDirector(q,instance);
					instance.viewReset();
					showMessage("「"+ui.draggable.text()+"」を「"+to_folder.find("a").text()+"」にコピーしました.");

				} else

					showMessage(data.message, true);

			}

			function callback_trash( data, status ) {

				if ( 0 == data.code ) {

					removeListItem( jQuery(".filebox_div[file_id="+file_id+"]",instance.rootDom) );

					var q = jQuery( data.html );
					to_page.prepend( q );
					new ListItemDirector(q,instance);
					instance.viewReset();

					showMessage("「"+ui.draggable.text()+"」をごみ箱に入れました.");

				} else

					showMessage(data.message, true);

			}

			function callback_move( data, status ) {

				if ( 0 == data.code ) {

					removeListItem( jQuery(".filebox_div[file_id="+file_id+"]",instance.rootDom) );

					var q = jQuery( data.html );

					to_page.prepend(q);
					new ListItemDirector(q,instance);
					instance.viewReset();
					showMessage("「"+ui.draggable.text()+"」を「"+to_folder.find("a").text()+"」に移動しました.");

				} else

					showMessage(data.message, true);

			}

			if ( from_page.hasClass("filebox_my_page") ) {

				if ( to_folder.hasClass("filebox_group_folder") ) {

					//copy
					param =
						{
							"act": "copy",
							"eid": file_id,
							"folder_id": to_folder.attr("folder_id")
						};

					callback = callback_copy;

				} else if ( to_folder.hasClass("filebox_trash_folder" ) ) {

					//trash
					param =
						{
							"act": "trash",
							"eid": file_id
						};

					callback = callback_trash;

				}

			} else if ( from_page.hasClass("filebox_group_page") ) {

				if ( to_folder.hasClass("filebox_my_folder") ) {

					//copy
					param =
						{
							"act": "copy",
							"eid": file_id
						};

					callback = callback_copy;

				} else if ( to_folder.hasClass("filebox_group_folder") ) {

					//copy
					param =
						{
							"act": "copy",
							"eid": file_id,
							"folder_id": to_folder.attr("folder_id")
						};

					callback = callback_copy;

				} else if ( to_folder.hasClass("filebox_trash_folder" ) ) {

					//trash
					param =
						{
							"act": "trash",
							"eid": file_id
						};

					callback = callback_trash;

				}

			} else if ( from_page.hasClass("filebox_trash_page") ) {

				if ( to_folder.hasClass("filebox_my_folder") ) {

					//trash_out
					param =
						{
							"act": "move",
							"eid": file_id
						};

					callback = callback_move;

				} else if ( to_folder.hasClass("filebox_group_folder") ) {

					//trash_out
					param =
						{
							"act": "move",
							"eid": file_id,
							"folder_id": to_folder.attr("folder_id")
						};

					callback = callback_move;

				}

			}

			jQuery.post(
				"filebox.php",
				param,
				callback,
				"json"
			);

		},
		activeClass: 'filebox_folder_highright',
		hoverClass: 'filebox_folder_hover'

	});

}

FileboxBuilder.prototype.readySort = function() {

	var instance = this;

	jQuery(".filebox_sort",instance.rootDom).css( "border", "outset 1px #cccccc" );

	jQuery(".filebox_sort",instance.rootDom).bind( 'mousedown', function() {
		jQuery(this).css( "border", "inset 1px #cccccc" );
	} );

	jQuery(".filebox_sort",instance.rootDom).bind( 'mouseup', function() {
		jQuery(this).css( "border", "outset 1px #cccccc" );
	} );

	jQuery(".filebox_filelisthead #sort_title_button",instance.rootDom).click( function () {
		var order = ( jQuery(this).hasClass("sort_order_desc") );
		sortListItems( sortByTitle, order );
		if( order ) {jQuery(this).removeClass("sort_order_desc");jQuery(this).addClass("sort_order_asc");}
		else {jQuery(this).addClass("sort_order_desc");}
	});
	jQuery(".filebox_filelisthead #sort_owner_button",instance.rootDom).click( function () {
		var order = ( jQuery(this).hasClass("sort_order_desc") );
		sortListItems( sortByOwner, order );
		if( order ) {jQuery(this).removeClass("sort_order_desc");jQuery(this).addClass("sort_order_asc");}
		else {jQuery(this).addClass("sort_order_desc");}
	});
	jQuery(".filebox_filelisthead #sort_size_button",instance.rootDom).click( function () {
		var order = ( jQuery(this).hasClass("sort_order_desc") );
		sortListItems( sortBySize, order );
		if( order ) {jQuery(this).removeClass("sort_order_desc");jQuery(this).addClass("sort_order_asc");}
		else {jQuery(this).addClass("sort_order_desc");}
	});
	jQuery(".filebox_filelisthead #sort_date_button",instance.rootDom).click( function () {
		var order = ( jQuery(this).hasClass("sort_order_desc") );
		sortListItems( sortByDate, order );
		if( order ) {jQuery(this).removeClass("sort_order_desc");jQuery(this).addClass("sort_order_asc");}
		else {jQuery(this).addClass("sort_order_desc");}
	});

	function sortListItems( method, order ) {

		jQuery('.filebox_page').each( function() {

			var items = [];

			jQuery('.filebox_div',this).each( function () {
				items.push( jQuery(this) );
			});

			items.sort( method );

			if ( !order ) {items.reverse();}

			jQuery(".filebox_div",this,instance.rootDom).remove();

			for ( var i in items ) {
				jQuery(this).append( items[i] );
			}

		});

	}

	function sortByTitle( a, b ) {

		if( jQuery('.filebox_title:first',a).text() < jQuery('.filebox_title:first',b).text() ) {return 1;}
		else if ( jQuery('.filebox_title:first',a).text() == jQuery('.filebox_title:first',b).text() ) {return 0;}
		else {return -1;}

	}

	function sortByOwner( a, b ) {

		if( jQuery('.filebox_owner:first',a).text() < jQuery('.filebox_owner:first',b).text() ) {return 1;}
		else if ( jQuery('.filebox_owner:first',a).text() == jQuery('.filebox_owner:first',b).text() ) {return 0;}
		else {return -1;}

	}

	function sortBySize( a, b ) {

		var num_a = Number( jQuery('.filebox_size:first',a).attr('filesize') );
		var num_b = Number( jQuery('.filebox_size:first',b).attr('filesize') );

		if ( NaN == num_a || NaN == num_b ) return 0;

		return num_b - num_a;

	}

	function sortByDate( a, b ) {

		var num_a = Number( jQuery('.filebox_date:first',a).attr('filedate') );
		var num_b = Number( jQuery('.filebox_date:first',b).attr('filedate') );

		if ( NaN == num_a ) return 1;
		else if ( NaN == num_b ) return -1;

		return num_b - num_a;

	}

}

FileboxBuilder.prototype.readyList = function() {

	var instance = this;

	jQuery(".filebox_list_select",instance.rootDom).change( function () {

		switch( jQuery(this).val() ) {

		case 'view_thumb':

			{

				var height = jQuery(".filebox_listbody",instance.rootDom).height() + jQuery(".filebox_listhead",instance.rootDom).height();
				jQuery(".filebox_listbody",instance.rootDom).height( height );

				jQuery('#sort_title_button, #sort_owner_button, #sort_size_button, #sort_date_button').hide();

				jQuery(".filebox_div",instance.rootDom).each( function () {
					jQuery(".filebox_thumbbox",this).show();
					jQuery(".filebox_listbox",this).hide();
				} );

			}
			break;

		case 'view_list':

			{

				var height = jQuery(".filebox_listbody",instance.rootDom).height() - jQuery(".filebox_listhead",instance.rootDom).height();
				jQuery(".filebox_listbody",instance.rootDom).height( height );

				jQuery('#sort_title_button, #sort_owner_button, #sort_size_button, #sort_date_button').show();

				jQuery(".filebox_div",instance.rootDom).each( function () {
					jQuery(".filebox_listbox",this).show();
					jQuery(".filebox_thumbbox",this).hide();
				} );

			}
			break;

		}

		document.cookie = "filebox_listview=" + val
							+ "; expires=Tue, 1-Jan-2030 00:00:00 GMT;";

	} );

	var sCookie = document.cookie;
	var aData = sCookie.split(";");
	var oExp = new RegExp(" ", "g");

	var val = 'view_thumb';

	for ( var i=0; aData.length > i; ++i ) {
		var aWord = aData[i].split("=");
		aWord[0] = aWord[0].replace(oExp, "");
		if ( 'filebox_listview' == aWord[0] ) {
			val = unescape(aWord[1]);
			break;
		}
	}

	jQuery(".filebox_list_select",instance.rootDom).val( val );
	jQuery(".filebox_list_select",instance.rootDom).change();

}

FileboxBuilder.prototype.readyDialog = function() {

	var instance = this;

	jQuery("#upload",instance.rootDom).css("display","block");

	jQuery("#upload",instance.rootDom).dialog({
		autoOpen: false
	});

	jQuery("#upload_button",instance.rootDom).click( function () {
		jQuery("#upload",instance.rootDom).dialog("open");
	});

}

FileboxBuilder.prototype.viewReset = function() {

	var instance = this;

	jQuery(".filebox_list_select",instance.rootDom).change();

}


var ListItemBuilder = function ( item, fileboxBuilder ) {
	
	this.item = item;
	this.file_id = item.attr("file_id");
	this.fileboxBuilder = fileboxBuilder;

}

ListItemBuilder.prototype = {
	item: null,
	file_id: null,
	fileboxBuilder: null
};

ListItemBuilder.prototype.destroy = function () {

	this.item.find(".clear_tag").unbind( "click" );
	this.item.find(".delete_tag").unbind( "click" );
	this.item.find(".clear_tag").unbind( "click" );
	this.item.find(".revert_tag").unbind( "click" );
	this.item.find(".filebox_pmt").unbind( "click" );
	this.item.find(".filebox_draggable").draggable({disabled: 'true'});

}

ListItemBuilder.prototype.readyLazy = function() {
	
	jQuery(".filebox_thumbbox img",this.item.get(0))
		.lazyload( { event: "pageopen", placeholder: "/images/lightbox-ico-loading.gif" } );

}

ListItemBuilder.prototype.readyLightbox = function() {

	this.item.find(".lightbox_a").lightBox({fixedNavigation:true});

}

ListItemBuilder.prototype.readyDraggable = function() {

	this.item.find(".filebox_draggable")
		.draggable({helper: 'clone', opacity: 0.70, cursor: 'pointer'});

}

ListItemBuilder.prototype.readyClearTag = function() {

	var instance = this;

	this.item.find(".clear_tag").click( function() {

		if ( !window.confirm("ファイルを削除します。\n"
							+ "この操作は元に戻すことが出来ません。よろしいですか?") ) {
			return;
		}

		var param =
			{
				"act": "delete",
				"eid": instance.file_id
			};

		jQuery.post(
			"filebox.php",
			param,
			function( data, status ) {

				if ( 0 == data.code ) {

					removeListItem( jQuery(".filebox_div[file_id="+instance.file_id+"]",instance.fileboxBuilder.rootDom) );
					showMessage("「"+instance.item.find(".filebox_title:first").text()+"」を削除しました.");

				} else

					showMessage(data.message, true);

			},
			"json"
		);

	} );

}

ListItemBuilder.prototype.readyDeleteTag = function() {

	var instance = this;

	this.item.find(".delete_tag").click( function() {

		var param =
			{
				"act": "trash",
				"eid": instance.file_id
			};

		jQuery.post(
			"filebox.php",
			param,
			function( data, status ) {

				if ( 0 == data.code ) {

					removeListItem( jQuery(".filebox_div[file_id="+instance.file_id+"]",instance.fileboxBuilder.rootDom) );

					var q = jQuery( data.html );
					jQuery(".filebox_trash_page",instance.fileboxBuilder.rootDom).append( q );
					new ListItemDirector(q,instance.fileboxBuilder);
					instance.fileboxBuilder.viewReset();

					showMessage("「"+instance.item.find(".filebox_title:first").text()+"」をごみ箱に入れました.");

				} else

					showMessage(data.message, true);

			},
			"json"
		);

	} );

}

ListItemBuilder.prototype.readyRevertTag = function() {

	var instance = this;

	this.item.find(".revert_tag").click( function() {

		var param =
			{
				"act": "revert_trash",
				"eid": instance.file_id
			};

		jQuery.post(
			"filebox.php",
			param,
			function( data, status ) {

				if ( 0 == data.code ) {

					removeListItem( jQuery(".filebox_div[file_id="+instance.file_id+"]",instance.fileboxBuilder.rootDom) );

					var q = jQuery( data.html );
					jQuery(".filebox_page[folder_id="+data.folder_id+"]",instance.fileboxBuilder.rootDom).append( q );
					new ListItemDirector(q,instance.fileboxBuilder);
					instance.fileboxBuilder.viewReset();

					showMessage("「"+instance.item.find(".filebox_title:first").text()+"」を元に戻しました.");

				} else

					showMessage(data.message, true);

			},
			"json"
		);

	} );

}

ListItemBuilder.prototype.readyPmt = function() {

	var instance = this;

	this.item.find(".filebox_pmt").click( function() {

		var tag = jQuery(this);

		var id = tag.attr("file_id");
		var pmt;

		if ( 0 != tag.attr("pmt") ) pmt=0;
		else pmt = 2;

		jQuery.post(

			"filebox.php",
			{
				"act": "update_only",
				"eid": id,
				"unit": pmt
			},
			function( data, status ) {

				if ( 0 == data.code ) {

					var text = ( 0 == pmt ) ? '公開' : '非公開';
					var color = ( 0 == pmt ) ? 'darkcyan' : 'magenta';
					tag.attr( "pmt", pmt );
					tag.css( "color", color );
					tag.text( text );

					showMessage("「"+instance.item.find(".filebox_title:first").text()+"」の公開権限を変更しました");

				} else

					showMessage( "公開権限の変更に失敗しました", true );

			},
			"json"

		);

	} );

}

function removeListItem( item ) {

	listItemBuilder = new ListItemBuilder( item );
	listItemBuilder.destroy();

	item.css("background-color","silver");

	item.animate( {opacity: 0.0}, "slow" );

	item.slideUp("fast", function() {
		jQuery(this).remove();
	} );

}



function filebox2fck(elem, eid, title, type, isImg) {
	var oEditor;

	oEditor = window.parent.FCKeditorAPI.GetInstance(elem);

	if (!oEditor.Status) {
		document.getElementById(elem).value += '[' + eid + ']';
	}
	else {
		var str = '<a class="lightbox_a" href="/fbox.php?eid=' + eid + '">'
				+ '<img src="/fbox.php?eid=' + eid + '&s=' + type + '" '
				+ 'alt="" border="0">';
//		if (!isImg) str += title;

		str	+= '</a>';

		oEditor.InsertHtml(str);
	}
	showMessage('「'+title+'」を挿入しました.');
}

function filebox2fck_text(elem, eid, title ) {
	var oEditor;

	oEditor = window.parent.FCKeditorAPI.GetInstance(elem);

	if (!oEditor.Status) {
		document.getElementById(elem).value += '[' + eid + ']';
	}
	else {
		var str = '<a href="/fbox.php?eid=' + eid + '">'+title+'</a>';
		oEditor.InsertHtml(str);
	}
	showMessage('「'+title+'」を挿入しました.');
}

function filebox2fck_video(elem, eid, title) {
	var oEditor;

	oEditor = window.parent.FCKeditorAPI.GetInstance(elem);

	if (!oEditor.Status) {
		document.getElementById(elem).value += '[' + eid + ']';
	}
	else {
		var str = '<embed src="/fbox.php?eid=' + eid + '" width="80%" autoplay=false/>'
					+ '<noembed>プラグインがインストールされていません</noembed>';

		oEditor.InsertHtml(str);
	}
	showMessage('「'+title+'」を挿入しました.');
}

function filebox2fck_pdf(elem, eid, title) {
	var oEditor;

	oEditor = window.parent.FCKeditorAPI.GetInstance(elem);

	if (!oEditor.Status) {
		document.getElementById(elem).value += '[' + eid + ']';
	}
	else {
		var str = '<embed class="pdf_embed" src="/fbox.php?eid=' + eid + '" width="80%" height="500" />'
					+ '<noembed>プラグインがインストールされていません</noembed>';

		oEditor.InsertHtml(str);
	}
	showMessage('「'+title+'」を挿入しました.');
}

function filebox2fck_pdfthumb(elem, eid, title, type, isImg) {
	var oEditor;

	oEditor = window.parent.FCKeditorAPI.GetInstance(elem);

	if (!oEditor.Status) {
		document.getElementById(elem).value += '[' + eid + ']';
	}
	else {
		var str = '<a href="/fbox.php?eid=' + eid + '" target="_blank">'
				+ '<img src="/fbox.php?eid=' + eid + '&s=' + type + '" '
				+ 'alt="" border="0">';
//		if (!isImg) str += title;

		str	+= '</a>';

		oEditor.InsertHtml(str);
	}
	showMessage('「'+title+'」を挿入しました.');
}

function filebox2fck_pdftext(elem, eid, title ) {
	var oEditor;

	oEditor = window.parent.FCKeditorAPI.GetInstance(elem);

	if (!oEditor.Status) {
		document.getElementById(elem).value += '[' + eid + ']';
	}
	else {
		var str = '<a href="/fbox.php?eid=' + eid + '" target="_blank">'+title+'</a>';
		oEditor.InsertHtml(str);
	}
	showMessage('「'+title+'」を挿入しました.');
}

function filebox2fck_youtube( elem, yt_id ) {

       var oEditor;

       oEditor = window.parent.FCKeditorAPI.GetInstance(elem);

       if (!oEditor.Status) {
               document.getElementById(elem).value += '[' + yt_id + ']';
       }
       else {
               var str = '<object width="560" height="340">'
                       + '<param name="movie" value="http://www.youtube.com/v/' + yt_id + '&hl=ja_JP&fs=1&">'
                       + '</param>'
                       + '<param name="allowFullScreen" value="true">'
                       + '</param>'
                       + '<param name="allowscriptaccess" value="always">'
                       + '</param>'
                       + '<embed src="http://www.youtube.com/v/' + yt_id + '&hl=ja_JP&fs=1&" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="560" height="340" alt="">'
                       + '</embed>'
                       +'</object>';

               oEditor.InsertHtml(str);
       }

		showMessage('「'+title+'」を挿入しました.');

}

function showMessage( message, error ) {

	if ( undefined == error ) {error=false;}

	var q = jQuery("<div>");
	q.text( message );
	if ( error ) q.css( "color", "red" );
	q.css( "width", jQuery("body").width() );
	q.css( "padding", "16px" );
	q.css( "border", "solid 1px lightgreen" );
	q.css( "text-align", "center" );
	q.css( "background-color", "lightyellow" );
	q.css( "position", "fixed" );
	q.css( "left", "0" );
	q.css( "top", "0" );
	q.css( "z-index", "100" );

	q.hide();
	q.fadeIn( "fast" );

	jQuery("body").append(q);

//	q.click( function() {
//		jQuery(this).slideUp( "normal", function() {
//			jQuery(this).remove();
//		} );
//	} );

//	q.animate( {opacity: 1.0}, 5000, "swing", function() {
//		jQuery(this).animate( {opacity: 0.0, height: "0px"}, "normal", "linear", function() {
//			jQuery(this).remove();
//		} );
//	} );

	var plane = jQuery("<div>");
	plane.css( "position", "absolute" );
	plane.css( "width", "100%" );
	plane.css( "height", "100%" );
	plane.css( "top", "0" );
	plane.css( "left", "0" );

	plane.click( function() {
		q.animate( {opacity: 0.0, height: "0px"}, "normal", "linear", function() {
			jQuery(this).remove();
		} );
		jQuery(this).remove();
	} );

	jQuery("body").append(plane);

}