/* 
 * Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
j$ = jQuery;

function EcomMap(moduleUrl)
{
	this.moduleUrl = moduleUrl;
}
EcomMap.prototype = 
{
	moduleUrl : null,
	mapUrl : null,
	mapCid : null,
	mapGid : null,
	returnUrl : null,
	authUrl : null,
	
	/** ユーザカスタマイズ設定 */
	userLon : -10000,
	userLat : -10000,
	userScale : 0,
	userVisibleLayers : null,
	
	win : null,
	
	init : function(ecommapUrl, ecommapCid, ecommapGid, returnUrl, authUrl)
	{
		this.mapUrl = ecommapUrl;
		this.mapCid = ecommapCid;
		this.mapGid = ecommapGid;
		if (!ecommapGid) this.mapGid = 0;
		this.returnUrl = returnUrl;
		this.authUrl = authUrl;
	},
	
	////////////////////////////////////////////////////////////////
	openWindow : function(url)
	{
		var win = window.open(url, "_blank");
		win.focus();
	},
	////////////////////////////////////////////////////////////////
	//eコミマップ連携
	/** eコミマップエディタ表示 */
	openMapWindow : function(mid, layer, fid)
	{
		var url = this.mapUrl+"map/?cid="+this.mapCid+"&gid="+this.mapGid+"&mid="+mid+(this.authUrl?"&auth="+this.authUrl:"");
		if (layer && fid) url += "&layer="+layer+"&fid="+fid;
		var lon = this.userLon;
		var lat = this.userLat;
		if (-180 <= lon && lon <= 180 && -90 <= lat && lat <= 90) {
			url += "&lon="+lon+"&lat="+lat;
		}
		if (this.userScale > 0) url += "&scale="+this.userScale;
		if (this.userVisibleRefLayerId) url += "&visible_ref="+this.userVisibleRefLayerId;
		
		var win = window.open(url, "ecom_map", "location=no,personalbar=no,status=no,resizable=yes");
		win.focus();
	},
	
	/** eコミマップ作成ウィザード表示 */
	showWizard : function()
	{
		var win = window.open(this.mapUrl+"wizard/wizard.jsp?cid="+this.mapCid+"&gid="+this.mapGid+(this.authUrl?"&auth="+this.authUrl:""),
				"wizard", "location=no,personalbar=no,scrollbars=yes,status=no,resizable=yes,width=620,height=560");
		win.focus();
		var thread = setInterval(
			function() {if (win != null && win.closed) { clearTimeout(thread); win = null; location.reload(); } }, 1000
		);
	},
	
	/** 地図管理画面表示 */
	mapAdmin : function()
	{
		var url = this.mapUrl+"admin/map.jsp?cid="+this.mapCid+"&gid="+this.mapGid+(this.authUrl?"&auth="+this.authUrl:"");
		location.href = url;
	},
	/** 地図設定画面表示 */
	mapEdit : function(mid)
	{
		var url = this.mapUrl+"ecom/admin/map-edit.jsp?cid="+this.mapCid+"&gid="+this.mapGid+"&mid="+mid+(this.authUrl?"&auth="+this.authUrl:"");
		url += "&ret="+encodeURI(this.moduleUrl+"/tb_close.php");
		//location.href = url;
		tb_show( "eコミマップ設定", url+"&TB_iframe=true&height="+Math.min(480, ((document.all?document.body.clientHeight:innerHeight)*0.8)) );
	},
	/** 地図項目一覧画面表示 */
	mapFeatureType : function()
	{
		var url = this.mapUrl+"ecom/admin/featuretype.jsp?cid="+this.mapCid+"&gid="+this.mapGid+(this.authUrl?"&auth="+this.authUrl:"");
		url += "&ret="+encodeURI(this.moduleUrl+"/tb_close.php");
		//location.href = url;
		tb_show( "マップ項目一覧", url+"&TB_iframe=true&height="+Math.min(640,(document.all?document.body.clientHeight:innerHeight)*0.8));
	},
	
	/** 認証キーの有効期限延長 */
	setAuthExpiry :function()
	{
		j$.ajax({
			url: this.moduleUrl+"/auth_expiry.php",
			cache: false
		});
	},
	
	////////////////////////////////////////////////////////////////
	//パーツブロック表示用 
	/** Ajaxでブロックをロード */
	loadBlock : function(blk_id, params)
	{
		j$.ajax({
			url: this.moduleUrl+"/block_load.php?blk_id="+blk_id+"&"+params,
			cache: false,
			success: function(data) {
				j$('#ecommap_'+blk_id).html(data);
			}
		});
	},
	
	//Getで取得後Callback
	ajaxGet : function(url, callback)
	{
		j$.ajax({
			type: "GET",
			url: this.moduleUrl+url,
			success: function(data){
				if (callback) callback(data);
			}
		});
	},
	
	//Postでフォームの内容を送信後Callback
	ajaxPost : function(url, form, callback)
	{
		j$.ajax({
			type: "POST",
			url: this.moduleUrl+url,
			data: j$(form).formToArray(),
			success: function(data){
				if (callback) callback(data);
			}
		});
	},
	
	//フォーム送信後メッセージ表示とリダイレクト
	ajaxSubmit : function(url, form, returnUrl, noAlert)
	{
		var options = {
			type: form?"POST":"GET",
			url: this.moduleUrl+url,
			success: function(msg){
				if (!noAlert) alert(msg);
				if (returnUrl) location.href = returnUrl;
				else location.reload();
			}
		};
		if (form) options.data = j$(form).formToArray();
		j$.ajax(options);
	},
	
	/*---------------- チェックボックス ----------------*/
	isCheckedAll : function(checkboxes)
	{
		var size = checkboxes.length;
		for (var i=0; i<size; i++) {
			if (checkboxes[i].checked) return true;
		}
		return false;
	},
	checkAll : function(checkboxes, bOn)
	{
		if (!checkboxes) return;
		var size = checkboxes.length;
		for (var i=0; i<size; i++) {
			checkboxes[i].checked = bOn;
		}
	},
	getCheckCount : function(checkboxes)
	{
		var count = 0;
		if (!checkboxes) return;
		var size = checkboxes.length;
		for (var i=0; i<size; i++) {
			if (checkboxes[i].checked) count++;
		}
		return count;
	},
	getCheckValues : function(checkboxes)
	{
		var values = [];
		if (!checkboxes) return;
		var size = checkboxes.length;
		for (var i=0; i<size; i++) {
			if (checkboxes[i].checked) values.push(checkboxes[i].value);
		}
		return values;
	},
	
	
	/*---------------- ページレイアウト ----------------*/
	/** ３カラムは右を非表示 */
	setLayoutMain : function(blk_id)
	{
		var s3 = j$('#space_3');
		if (s3) {
			var s1 = j$('#space_1');
			var sw = j$('#space_wrapper');
			s3 .hide();
			var s2 = j$('#space_2');
			s2.css('width', s2.width()+'px');//%表記の場合サイズが変わらないようにする
			var w = s3.width();
			sw.width(sw.width()+w);
			s1.width(sw.width()-s2.width()-8);
		}
	},
	/** 全画面切り換え
	 * @param full trueなら全画面 */
	resize : function(blk_id, full)
	{
		try {
			var d = document;
			var box = d.getElementById('box_'+blk_id); 
			if (full) {
				j$('#wrapper').hide();
				j$('#space_2').hide();
				j$('#space_3').hide();
				j$('#header').hide();
				j$('#nav').hide();
				j$('#menubar').hide();
				j$('#footer').hide();
				if (box) {
					box.style.position = "absolute";
					box.style.top = "0px";
					box.style.left = "0px";
					d.body.style.width = "100%";
					d.body.style.height = "100%";
					this.bodyBg = d.body.style.background;
					d.body.style.background = "white";
					d.body.appendChild(box);
				}
			} else {
				j$('#space_1')[0].appendChild(box);
				box.style.position = "static";
				box.style.top = "";
				box.style.left = "";
				d.body.style.width = "";
				d.body.style.height = "";
				d.body.style.background = this.bodyBg;
				j$('#wrapper').show();
				j$('#space_2').show();
				j$('#space_3').show();
				j$('#header').show();
				j$('#nav').show();
				j$('#menubar').show();
				j$('#footer').show();
			}
		} catch (e) {}
	},
	bodyBg : null,
	
	/*---------------- ダイアログ ----------------*/
	dialog : null,
	/** 適当な高さを指定するとadjustSize時にtopが移動しない
	 * @param options 連想配列 html:'',url:''のどちらかとjQueryUIのDialogのoption */
	openDialog : function(title, options, onLoad)
	{
		if (!this.dialog) {
			this.dialog = jQuery('<div>');
			var _dialog = this.dialog;
			_dialog.dialog({
				autoOpen: false,
				buttons: {
					//'閉じる': function(){ _dialog.dialog('close');
				},
				open: function(){
					var url = _dialog.dialog("option", "url");
					var html = _dialog.dialog("option", "html");
					if (url) {
						_dialog.html('ロード中...');
						_dialog.load(url, _dialog.dialog("option", "postData"), function() {
							//リンクを別ウィンドウになるように設定
							var a = _dialog[0].getElementsByTagName('a');
							for (var i=0; i<a.length; i++) a[i].target = '_blank';
							//イメージをロード
							var img = _dialog[0].getElementsByTagName('img');
							if (img.length == 0) _dialog.adjustSize();
							for (var i=0; i<img.length; i++) {
								img[i].onload = _dialog.adjustSize;
								img[i].onerror = _dialog.adjustSize;
							}
							if (_dialog.onLoad) _dialog.onLoad();
						});
					} else if (html) {
						_dialog.html(html);
						_dialog.adjustSize();
					}
					for (key in {url:0,html:0,width:0,height:0,postData:0, onLoad:null}) _dialog.dialog("option", key, null);
				},
				close: function(){
					_dialog.children().remove();
				},
				resizeStop: function(){
					_dialog.adjustSize();
				}
			});
			//サイズ調整関数を追加
			_dialog.adjustSize = function()
			{
				if (!_dialog[0].firstChild) return;
				var style = _dialog[0].style;
				style.height = '';
				style.overflow = '';
				
				var max = _dialog.dialog('option', 'maxWidth');
				max = Math.min(jQuery(window).width(),max?max:10000);
				var w = Math.min(max, _dialog[0].scrollWidth);
				_dialog.dialog('option', 'width', w);
				
				var buttonHeight = _dialog[0].parentNode.nextSibling ? _dialog[0].parentNode.nextSibling.scrollHeight : 0;
				//タイトルバーとボタンとボーダー+マージン
				var marginH = _dialog[0].offsetTop + buttonHeight + 14;
				max = _dialog.dialog('option', 'maxHeight');
				max = Math.min(jQuery(window).height(),max?max:10000);
				var h = Math.min(max, _dialog[0].scrollHeight+marginH);
				_dialog.dialog('option', 'height', h);
				//スクロールバー表示
				style.height = (h-marginH)+'px';
				style.overflow = 'auto';
				//画面の中心に移動
				_dialog[0].parentNode.parentNode.style.top = ((j$(window).height()-h)/2+j$(window).scrollTop())+'px';
			};
		} else {
			this.dialog.dialog('close');
		}
		
		//タイトルとオプション設定
		this.dialog.dialog("option", "title", title);
		for (key in options) this.dialog.dialog("option", key, options[key]);
		this.dialog.onLoad = onLoad;
		this.dialog.dialog('open');
	},
	closeDialog : function()
	{
		this.dialog.dialog('close');
	},
	
	/*---------------- ページ表示 ----------------*/
	
	/*---------------- ダイアログ表示 ----------------*/
	
	/*---------------- メインページ表示 ----------------*/
	
	/*---------------- 管理画面用 ----------------*/
	
	CLASS_NAME : 'EcomMap'
};
