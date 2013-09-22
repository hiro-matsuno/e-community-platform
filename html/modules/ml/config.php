<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

function mod_ml_block_config($id = 0) {
	$menu   = array();
	if (owner_level($id) == 100) {
		$menu[] = array(title => '基本設定',
						url => '/modules/ml/input.php?eid='. $id,
						inline => false);
	}

	return $menu;
}

// main config
// $id: element id
function mod_ml_main_config($id = 0) {
	$menu   = array();

	return $menu;
}

function mod_ml_tmp_css() {
	global $COMUNI_HEAD_CSSRAW;

	$COMUNI_HEAD_CSSRAW[] = <<<__CSS__
.mod_ml_title {
	margin: 2px;
	font-weight: normal;
	border-left: solid 4px #a8b6d3;
	padding: 2px 2px;
}
.mod_ml_desc {
	padding: 5px;
	font-size: 0.9em;
}
.mod_ml_joind {
	text-align: right;
	font-size: 0.9em;
	padding: 3px;
	color: #3d9de2;
}
.mod_ml_post {
	text-align: right;
	font-size: 0.9em;
	padding: 3px;
}
.mod_ml_regist {
	text-align: right;
	font-size: 0.9em;
	padding: 3px;
}
.mod_ml_quit {
	text-align: right;
	font-size: 0.8em;
	padding: 3px;
}
.mod_ml_backnumber {
	text-align: right;
	font-size: 0.9em;
	padding: 3px;
}
.mod_ml_header {
	;
}
.mod_ml_footer {
	;
}

.mod_ml_backnumber_list {
	list-style-type: none;
	margin: 0;
	padding: 0;
}
.mod_ml_backnumber_list li {
	font-size: 0.8em;
}
__CSS__;

}

?>
