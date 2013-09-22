<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

include_once dirname(__FILE__). '/config.php';

function mod_login_block($id = null) {
	if (is_login()) {
		return mod_login_status();
	}

	$href = CONF_URLBASE. '/login.php';
	$ref = urlencode($_SERVER['REQUEST_URI']);

	$lost_passwd = thickbox_href(CONF_URLBASE. '/passwd_lost.php');

	return <<<__HTML__
<div style="width: 100%;">
<form method="post" action="${href}" style="margin: 0px;">
<input type="hidden" name="action" value="login">
<input type="hidden" name="ref" value="${ref}">
<div style="text-align: right; padding: 3px;">
  <span style="font-size: 0.9em;">メールアドレス</span><br>
  <input type="text" name="email" value="" class="input_text" style="width: 95%;"><br>
  <span style="font-size: 0.9em;">パスワード</span><br>
  <input type="password" name="password" value="" class="input_text" style="width: 70%;"><br>
</div>
<!--
<div align="right">
<input type="checkbox" name="autologin" id="autologin" value="on" >
<label for="autologin">次回から自動的ログイン</label><br />
-->
<div style="text-align: center; padding: 3px;">
<input type="submit" name="submit" value="ログイン" style="border: solid 1px #333; background: #fff; font-weight: bold;">
</div>
<div style="text-align: right; font-size: smaller;">
&raquo; <a href="${lost_passwd}" class="thickbox">パスワードを忘れたら？</a>
</div>
</form>
</div>
__HTML__
	;
}

function mod_login_status() {
	$handle = get_nickname(myuid());

	$href = array();

	$href['logout'] = CONF_URLBASE. '/logout.php?ref='.urlencode($_SERVER["REQUEST_URI"]);
	$href['change'] = thickbox_href(CONF_URLBASE. '/passwd_change.php');

	return <<<__HTML__
<small>現在 ${handle} としてログイン中です。</small><br>
<a href="${href['logout']}" class="common_href">ログアウト</a>
<a href="${href['change']}" class="common_href thickbox">パスワード変更</a>
__HTML__
	;
}

?>
