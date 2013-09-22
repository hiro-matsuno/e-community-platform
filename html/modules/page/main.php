<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

function mod_page_main_config($id) {
	if (!is_owner($id)) {
		return;
	}
	$menu = array();
	$menu[] = array(title => '編集',
					url => '/modules/page/input.php?eid='. $eid,
					inline => false);

	return block_edit_menu($id, $menu);
}

function mod_page_main($id) {
	$data = array(id       => $id,
				  editlink => mod_page_main_config($id),
				  title    => 'ページ',
				  content  => '');

	$p = mysql_uniq("select * from page_data where pid = %s".
					" order by initymd limit 1",
					mysql_num($id));

	if (!$p) {
		return $data;
	}

	$comment = load_comment($id);

	if ($p["subject"]) {
		$html = '<h3 class="m_page_subject">'. $p["subject"]. '</h3>'.
				'<div class="m_page_body">'. $p["body"]. '</div>';
	}
	else {
		$html = '<div class="m_page_body">'. $p["body"]. '</div>';
	}

	$data["content"] = $html. $comment;

	return $data;
}

?>
