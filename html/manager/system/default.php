<?php
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require dirname(__FILE__). '/../../lib.php';

admin_check();

$data = array(title   => '管理者用ツール 編集画面',
			  icon    => 'write',
			  content => '<div style="padding:5px;">編集メニューを選択してください</div>');
show_input($data);
input_new();
?>
