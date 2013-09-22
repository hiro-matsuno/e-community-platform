<?php 
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
?>
<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

function mod_filebox_delete_block_confirm($blk_id) {

	return "<div style=\"padding: 4px\">"
		."ファイル倉庫パーツを削除しても、"
		."ファイル倉庫にアップされたデータ自体は削除されません。"
		."「各種設定」の「ファイル倉庫」から引き続き利用することができます。"
		."</div>";

}

?>
