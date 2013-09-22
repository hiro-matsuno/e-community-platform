<?php
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
?>
<?php

require_once dirname(__FILE__)."/../../../lib.php";
require_once dirname(__FILE__)."/../classes/MemoModule.php";

$blk_id = $_REQUEST["blk_id"];

$data = MemoData::createInstanceByBlockId( $blk_id );
if ( null === $data ) { 
	$data = new MemoData();
	$data->setBlockId( $blk_id );
}

$block = new Block( $blk_id );
$page = $block->getPage();

switch ( $_REQUEST["act"] ) {
case "regist":
	$data->setFgcolor($_REQUEST["fgcolor"]);
	$data->setBgcolor($_REQUEST["bgcolor"]);
	$data->regist();

	header("HTTP/1.1 301 Moved Permanently");
	header("Location: ".$page->getUrl());

	exit(0);

default:
	break;
}

$fgcolor = $data->getFgcolor();
$bgcolor = $data->getBgcolor();

EcomGlobal::addHeadJs('../../../js/mlColorPicker.js');
EcomGlobal::addHeadCss('../../../css/mlColorPicker.css');
EcomGlobal::addJqueryReady("jQuery('#bgcolorbox').mlColorPicker({onChange:function(val){jQuery('#bgcolorbox').css('background-color', '#'+val);jQuery('#bgcolor').val(val);}});");
EcomGlobal::addJqueryReady("jQuery('#fgcolorbox').mlColorPicker({onChange:function(val){jQuery('#fgcolorbox').css('background-color', '#'+val);jQuery('#fgcolor').val(val);}});");
EcomGlobal::addHeadCssRaw( "#mlColorPicker { z-index: 10000; }" );

ob_start();
	
?>

<div style="padding: 8px; font-size: 0.8em">

<div><?=$message?></div>

<form method="post" action="setting.php?act=regist">

	<input type="hidden" name="act" value="regist" />
	<input type="hidden" name="blk_id" value=<?=$blk_id?> />

	<div>
		<h3 class="input_title">前景色を変更します</h3>
		<table><tr>
			<td><input type="text" id="fgcolor" name="fgcolor" value="<?=$fgcolor?>" size="8"/></td>
			<td><div id="fgcolorbox" style="width:22px;height:18px;border:1px solid gray;<?=$fgcolor?'background-color:#'.$fgcolor:''?>"></div></td>
		</tr></table>
	</div>

	<div>
		<h3 class="input_title">背景色を変更します</h3>
		<table><tr>
			<td><input type="text" id="bgcolor" name="bgcolor" value="<?=$bgcolor?>" size="8"/></td>
			<td><div id="bgcolorbox" style="width:22px;height:18px;border:1px solid gray;<?=$bgcolor?'background-color:#'.$bgcolor:''?>"></div></td>
		</tr></table>
	</div>

	<div style="padding: 4px">
		<input type="submit" value="保存" />
		<input type="button" onclick="document.location.href='<?= $page->getUrl() ?>';" value="戻る"/>
	</div>

</form>

</div>

<?php

	$html = ob_get_clean();

	$COMUNI['manager_mode'] = true;
	show_input( array(title   => "メモモジュールの設定",
					  icon    => 'write',
					  content => $html) );

?>