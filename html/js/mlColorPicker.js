/**
 * @author mlambir
 */

(function($) {
	var rgbRE = /^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/;
	var colorRE = /^[a-fA-F0-9]{6}|[a-fA-F0-9]{3}$/;
	function mouseEnterColor(event){
		var rgbString = $(event.target).css("background-color");
		var parts = rgbString.match(rgbRE);
		if(parts!=null){
			delete (parts[0]);
			for (var i = 1; i <= 3; ++i) {
			    parts[i] = parseInt(parts[i]).toString(16);
			    if (parts[i].length == 1) parts[i] = '0' + parts[i];
			}
			var hexString = parts.join('');
			$("#mlSelectedColorText").val(hexString).keyup();
		}else{
			$("#mlSelectedColorText").val(rgbString.substring(1)).keyup();

		}
	}
	function mouseClickColor(){
		colorSave();
	}
	function textKeyUp(event){
		color = $(event.target).val();
		if (color.match(colorRE)) {
			colorChange(color)
		}
		if (event.keyCode == 13) {//enter
			colorSave()
		}
	}
	function colorChange(color){
		$("#mlSelectedColorDiv").css("background-color", "#"+color);
	}
	function colorSave(){
		if ($("#mlColorPicker").data("mlOnChange")) {
			color = $("#mlSelectedColorText").val();
			if (color.match(colorRE)) {
				$("#mlColorPicker").data("mlOnChange")(color);
				close()
			}
		}
	}

	function close(){
		$("#mlColorPicker").hide().removeData("mlOnChange");
	}

	function createColorPicker(){
		var colors = ["#000000","#000000","#000000","#006600","#008800","#00AA00","#00DD00","#00FF00","#660000","#666600","#668800","#66AA00","#66DD00","#66FF00","#880000","#886600","#888800","#88AA00","#88DD00","#88FF00","#666666","#000000","#000066","#006666","#008866","#00AA66","#00DD66","#00FF66","#660066","#666666","#668866","#66AA66","#66DD66","#66FF66","#880066","#886666","#888866","#88AA66","#88DD66","#88FF66","#888888","#000000","#000088","#006688","#008888","#00AA88","#00DD88","#00FF88","#660088","#666688","#668888","#66AA88","#66DD88","#66FF88","#880088","#886688","#888888","#88AA88","#88DD88","#88FF88","#AAAAAA","#000000","#0000AA","#0066AA","#0088AA","#00AAAA","#00DDAA","#00FFAA","#6600AA","#6666AA","#6688AA","#66AAAA","#66DDAA","#66FFAA","#8800AA","#8866AA","#8888AA","#88AAAA","#88DDAA","#88FFAA","#DDDDDD","#000000","#0000DD","#0066DD","#0088DD","#00AADD","#00DDDD","#00FFDD","#6600DD","#6666DD","#6688DD","#66AADD","#66DDDD","#66FFDD","#8800DD","#8866DD","#8888DD","#88AADD","#88DDDD","#88FFDD","#FFFFFF","#000000","#0000FF","#0066FF","#0088FF","#00AAFF","#00DDFF","#00FFFF","#6600FF","#6666FF","#6688FF","#66AAFF","#66DDFF","#66FFFF","#8800FF","#8866FF","#8888FF","#88AAFF","#88DDFF","#88FFFF","#FF0000","#000000","#AA0000","#AA6600","#AA8800","#AAAA00","#AADD00","#AAFF00","#DD0000","#DD6600","#DD8800","#DDAA00","#DDDD00","#DDFF00","#FF0000","#FF6600","#FF8800","#FFAA00","#FFDD00","#FFFF00","#00FF00","#000000","#AA0066","#AA6666","#AA8866","#AAAA66","#AADD66","#AAFF66","#DD0066","#DD6666","#DD8866","#DDAA66","#DDDD66","#DDFF66","#FF0066","#FF6666","#FF8866","#FFAA66","#FFDD66","#FFFF66","#0000FF","#000000","#AA0088","#AA6688","#AA8888","#AAAA88","#AADD88","#AAFF88","#DD0088","#DD6688","#DD8888","#DDAA88","#DDDD88","#DDFF88","#FF0088","#FF6688","#FF8888","#FFAA88","#FFDD88","#FFFF88","#FFFF00","#000000","#AA00AA","#AA66AA","#AA88AA","#AAAAAA","#AADDAA","#AAFFAA","#DD00AA","#DD66AA","#DD88AA","#DDAAAA","#DDDDAA","#DDFFAA","#FF00AA","#FF66AA","#FF88AA","#FFAAAA","#FFDDAA","#FFFFAA","#00FFFF","#000000","#AA00DD","#AA66DD","#AA88DD","#AAAADD","#AADDDD","#AAFFDD","#DD00DD","#DD66DD","#DD88DD","#DDAADD","#DDDDDD","#DDFFDD","#FF00DD","#FF66DD","#FF88DD","#FFAADD","#FFDDDD","#FFFFDD","#FF00FF","#000000","#AA00FF","#AA66FF","#AA88FF","#AAAAFF","#AADDFF","#AAFFFF","#DD00FF","#DD66FF","#DD88FF","#DDAAFF","#DDDDFF","#DDFFFF","#FF00FF","#FF66FF","#FF88FF","#FFAAFF","#FFDDFF","#FFFFFF"];
		$("body").append(('<div style="position:absolute;" id="mlColorPicker"><div id="mlSelectedColorDiv"></div><input id="mlSelectedColorText" type="text" maxlength="6"><div id="mlColors"></div></div>'))
		var colorHolder=$("#mlColors")
		$.each(colors, function(i, color){
			colorHolder.append('<div class="mlColor" style="background-color:' + color + '" />')
		});
		$('.mlColor').bind("mouseenter", mouseEnterColor).click(mouseClickColor);
		$("#mlSelectedColorText").keyup(textKeyUp);
		$("#mlColorPicker").hide()
        $(document).bind('click', function(e){
            $("#mlColorPicker").hide();
        });
		$('#mlColorPicker,#mlColorPicker *').click(function(e){e.stopPropagation()})
	}

	$.fn.mlColorPicker = function(settings) {
		var config = {
			'onChange': function(value){}
		};

		if($("#mlColorPicker").length==0){
			createColorPicker()
		}

		if (settings) $.extend(config, settings);

		this.each(function() {
			$(this).click(function(event){
				$("#mlColorPicker").hide()
								   .css("top", event.pageY)
								   .css("left", event.pageX)
								   .show("slow")
								   .data("mlOnChange", config["onChange"]);
			});
		});
		return this;
	};
})(jQuery);
