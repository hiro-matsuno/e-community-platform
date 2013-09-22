<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

include_once dirname(__FILE__). '/config.php';

function mod_search_block($id = null) {
	$html = <<<__HTML__
<div class="form_wrap">
<form action="/index.php" method="GET">
<input type="hidden" name="eid" value="${id}">
<input type="hidden" name="module" value="search">
<input type="text" name="q" value="${keyword}" class="input_text"> <input type="submit" value="検索" class="search_btn">
</form>
</div>
__HTML__;
	;
	return $html;
}

?>
