<?php 
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
?>
<?php

	//	 このファイルへの直接アクセスを防止する.
	EcomUtil::denyDirectAccess();

	EcomGlobal::addHeadJs( "js/json2.js" );
	EcomGlobal::addHeadJs( "modules/memo/browse/block.js" );
	EcomGlobal::addHeadCss( "modules/memo/browse/block.css" );

	$data = MemoData::createInstanceByBlockId( $blk_id );
	if ( null === $data ) { $data = new MemoData(); }

	//	CSRF攻撃を防ぐために、フォームに固有のIDを取得する.
	$formBuildId = FormBuildId::getFormBuildId();

?>

<div class="memo_block" blk_id=<?= $blk_id ?> >

	<form action="modules/memo/get.php" method="POST">

		<input type="hidden" name="<?= FormBuildId::PARAM_NAME ?>"
			   value="<?= $formBuildId ?>" />

		<div class="memo_form_elem">

			<textarea name="data" class="memo_textarea" rows="4" style="color: #<?= $data->getFgcolor() ?>; background-color: #<?= $data->getBgcolor() ?>;" ><?= $data->getData(); ?></textarea>

		</div>

	</form>

	<script type="text/javascript">
		var memoBuilder_<?= $blk_id ?> = new MemoBuilder( <?= $blk_id ?>, <?php

			$me = User::getMe();
			$blockElement = new Element( $blk_id );
			echo ( Permission::USER_LEVEL_EDITOR <= $blockElement->getOwnerLevel( $me )
					? "true" : "false" );

		?> );
	</script>

</div>
