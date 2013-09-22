<?php
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

/* count_comment */
function count_comment($eid = null) {
	if (!is_comment($eid)) {
		return '-';
	}

	$q = mysql_uniq('select count(*) from comment_alt where eid = %s', mysql_num($eid));
	if ($q) {
		return $q['count(*)'];
	}
	return '0';
}

function is_comment($eid = 0) {
	global $COMMENT_NOTICE;

	$q = mysql_uniq('select * from comment_allow where eid = %s',
					mysql_num($eid));

	if ($q) {
		switch($q['unit']) {
			case 2:
				$COMMENT_NOTICE = 'この記事にコメントすることはできません。';
				return is_owner($eid) ? true : false;
			break;
			case 1:
				$COMMENT_NOTICE = 'コメントするためには<a href="/login.php">ログイン</a>が必要です。';
				return is_login() ? true : false;
			break;
			default:
				$COMMENT_NOTICE = '';
				;
		}
	}

	return true;
}

function load_comment($eid = null) {
	global $JQUERY, $COMUNI_HEAD_JSRAW;

	$JQUERY["ready"][] = <<<__JQ__
$('#comment_${eid}').html(jQuery.ajax({
	url: "/comment.php?eid=${eid}",
	async: false
}).responseText);
__JQ__;
	;

	$COMUNI_HEAD_JSRAW[] =  <<<__JS__
function post_comment_${eid}() {
	jQuery('#act_${eid}').val('post');
	jQuery('#eid_${eid}').val('${eid}');
	jQuery.ajax({
	   type: "POST",
	   url: "/comment.php",
	   data: jQuery("#cf_${eid}").serialize(),
		dataType: 'html',
	   success: function(res){
			jQuery('#comment_${eid}').html(res);

			var timestamp = new Date().getTime();
			jQuery('#captcha_${eid}').attr('src', jQuery('#captcha_${eid}').attr('src') + '&rnd' + timestamp);
	   }
	});
	return false;
}
__JS__;
	;

	return '<div id="comment_'. $eid. '" style="width: 100%;"></div>';
}

function send_noti_comment($id = 0) {
//	write_syslog('comment notice on '. $id);

	$site_id = get_site_id($id);

//	write_syslog('comment notice on '. $site_id);
	if (!$site_id || $site_id == 0) {
		return;
	}

	$q = mysql_full('select * from mail_noti_ct as m'.
					' inner join user as u on m.uid = u.id'.
					' where m.type in (1, 2) and m.eid = %s',
					mysql_num($site_id));

	$sitename = get_site_name($site_id);
	$url      = CONF_URLBASE. home_url($id);

	$subject = CONF_SITENAME. 'コメント通知';

	$body    = <<<_BODY_
${sitename}にコメントがありました。
${url}
_BODY_;

	$udata = array();
	if ($q) {
		while ($res = mysql_fetch_assoc($q)) {
			$udata[$res['uid']] = $res['email'];
		}
		foreach ($udata as $uid => $email) {
//			if (!check_pmt($eid, $uid)) {
//				continue;
//			}
			$fwd = get_fwd_mail($uid);
			if (isset($fwd) && count($fwd) > 0) {
				$to = $fwd;
			}
			else {
				$to = $email;
			}

			send_message( 0, $uid, 0, $subject, $body );

//			$body_head = get_handle($uid). " 様\n\n";
//			sys_fwdmail(array('to' => $to, 'subject' => $subject, 'body' => $body_head. $body));
		}
	}
}

function comment_post() {
	global $COMUNI, $COMMENT_NOTICE, $SYS_FORM;

	$act = isset($_REQUEST["action"]) ? $_REQUEST["action"] : '';
	$eid = isset($_REQUEST["eid"]) ? intval($_REQUEST["eid"]) : 0;
	$uid = myuid();

	echo '<?xml version="1.0" encoding="utf-8"?>'. "\n";
	echo '<div class="comment_box" style="width: 98%;">';

	$notice = '';
	switch ($act) {
		case 'post':
			if (!is_comment($eid)) {
				echo $COMMENT_NOTICE;
				return;
			}

			if (!check_blacklist($eid)) {
				$notice = 'こちらのページでは、現在コメントの受付を停止していいます。';
				break;
			}

			if (count_comment($eid) > 1000) {
				$notice = 'この記事にこれ以上コメントは書けません。';
				break;
			}

			$name = htmlesc($_POST["name"]);
			$url  = htmlesc($_POST["url"]);
			$msg  = htmlesc($_POST["msg"]);

			$SYS_FORM['cache']['name'] = $name;
			$SYS_FORM['cache']['url'] = $url;
			$SYS_FORM['cache']['msg'] = $msg;

			if (!is_login() && is_captcha($eid) && !check_captcha()) {
				$notice = '画像の文字を正しく入力して下さい。';
				break;
			}

			if (!check_ngword($eid, $msg) || !check_ngword($eid, $name)) {
				$notice = '禁止ワードが混ざっています。';
				break;
			}

			if ($name == '') $name = '無記名';
			if ($url == '' || $url == 'http://') $url = '';
			if ($msg == '') {
				$notice = '内容を入力して下さい。';
				break;
			}
			if ($eid == 0) break;
			if ($uid > 0) $name = '';

			$public = (isset($_POST["public"]) == 1) ? 1 : 0;

			$host = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';

			$i = mysql_exec("insert into comment_alt(eid, uid, name, url, msg, host, public)".
							" values(%s, %s, %s, %s, %s, %s, %s);",
							mysql_num($eid), mysql_num($uid),
							mysql_str($name), mysql_str($url), mysql_str($msg),
							mysql_str($host),
							mysql_num($public));

			if (!$i) {
				$notice = '書き込み時にエラーが発生しました。'. mysql_error();
			}

			unset($SYS_FORM['cache']);

			send_noti_comment($eid);
			break;
		default:
			;
	}

	$q_opt = '';
/*
	if (!is_owner($eid)) {
		$q_opt = ' and public < 0';
	}
*/

	$is_su    = is_su();
	$is_owner = is_owner($eid);

	$q = mysql_full('select * from comment_alt where eid = %s%s order by initymd desc',
					mysql_num($eid), $q_opt);

	echo '<a name="comment"></a>* コメント<br><div style="padding: 3px;">';
	if ($q) {
		while ($d = mysql_fetch_array($q)) {
			if (preg_match("/(\d+)[\/\-\.](\d+)[\/\-\.](\d+) (\d+):(\d+)/", $d["initymd"], $match)) {
				$fmt = "%d月%d日 %d時%02d分";
				$date =  sprintf($fmt, $match[2], $match[3], $match[4], $match[5]);
			}
			$name = htmlesc($d["name"]);
			$url  = htmlesc($d["url"]);

			if ($d['uid'] > 0) {
				$name = get_handle($d["uid"]);
			}
			$href = '';
			if ($url != '') {
				$href = ' <a href="'. $url. '" target="_blank">'. 'URL</a>';
			}
			$comment_str = htmlesc($d["msg"]);
			$comment_str = strip_tags($comment_str);
			$comment_str = nl2br($comment_str);

			$del_href = '';
			if ($is_owner) {
				$del_href = '&nbsp;<a href="/comment_del.php?id='. $d['id']. '&keepThis=true&TB_iframe=true&height=480&width=640" title="コメントの削除" '.
							' onClick="return my_tb(this);">[削除]</a><br />';
				$del_href .= '<span style="color: #999">['. $d['host']. ']</span>';
			}

			if ($d["public"] == 1) {
				if (!is_owner($eid) && !$is_su) {
					$comment[] = '<div style="text-align: left; width: 100%; font-size: 0.9em; border-top: solid 1px #cccccc; padding: 3px;">'.
								'--- 非公開コメント ---</div>';
				}
				else {
					$comment[] = '<div style="text-align: left; width: 100%; font-size: 0.9em; border-top: solid 1px #cccccc; padding: 3px;">'. '<u>このメッセージは管理者のみに表示されています。</u><br>'.
								 $comment_str.
								 '<div style="text-align: right; font-size: 0.8em;">by '. $name. $href.
								 ' at '. $date. $del_href. '</div></div>'. "\n";
				}
			}
			else {
					$comment[] = '<div style="text-align: left; width: 100%; font-size: 0.9em; border-top: solid 1px #cccccc; padding: 3px;">'.
								 $comment_str.
								 '<div style="text-align: right; font-size: 0.8em;">by '. $name. $href.
								 ' at '. $date. $del_href. '</div></div>'. "\n";
			}
		}
		if (count($comment) > 0) {
			foreach (array_reverse($comment) as $c) {
				echo $c;
			}
		}
		else {
			echo $comment;
		}
	}

	echo '</div>';

	if ($notice) {
		echo '<div style="color: #ff9999">[!] '. $notice. '</div>';
	}

	echo comment_form($eid);
}

function comment_form($eid) {
	global $SYS_FORM;

	$name = '';
	$url  = 'http://';
	$msg  = '';

	if (isset($SYS_FORM['cache'])) {
		$name = $SYS_FORM['cache']['name'];
		$url  = $SYS_FORM['cache']['url'];
		$msg  = $SYS_FORM['cache']['msg'];
	}

	if (is_login()) {
		$name = get_handle(myuid());
	}
	else {
		$name = '<input type="text" name="name" size="24" value="'. $name. '" style="width: 50%;">';
	}

	$captcha = '';
	$owner = get_owner($eid);
	if (!is_login() && is_captcha($eid)) {
		$captcha = create_form_captcha($eid);
	}

	echo <<<___FORM___
<div class="comment_form" style="width: 98%;">
<form action="/comment.php" method="POST" id="cf_${eid}" onSubmit="return post_comment_${eid}();">
<input type="hidden" name="action" id="act_${eid}" value="">
<input type="hidden" name="eid" id="eid_${eid}" value="">
名前: ${name}<br>
URL: <input type="text" name="url" size="32" value="${url}" style="width: 70%;"><br>
<textarea name="msg" style="width: 100%; height: 100px;">${msg}</textarea><br />
${captcha}
<div style="text-align: left; float: left;"><input type="checkbox" class="no_border" id="comment_${eid}_4adm" name="public" value="1"> <label for="comment_${eid}_4adm">この記事の作成者にのみ表示</label></div>
<div style="text-align: right;"><input type="submit" value="コメントを投稿" style="width: 20%;"></div>
<div style="clear: both"></div>
</form>
</div>
___FORM___;
	;
}

function get_handle($uid) {
	$f = mysql_uniq("select * from user where id = %s", mysql_num($uid));
	return $f["handle"] ? $f["handle"] : '';
}

function get_owner($eid = 0) {
	global $SYS_UID, $SYS_GID;

	if (!isset($SYS_UID[$eid]) && !isset($SYS_GID[$eid])) {
		set_eid_info($eid);
	}

	return array('uid' => $SYS_UID[$eid], 'gid' => $SYS_GID[$eid]);
}

function is_captcha($eid = 0) {
	$owner = get_owner($eid);
	if ($owner['gid'] > 0) {
		$c = mysql_uniq('select * from core_captcha_setting_group where gid = %s',
						mysql_num($owner['gid'] ));

		if (!$c) {
			$m = mysql_uniq('select * from core_captcha_setting_master limit 1');
			if ($m && $m['type'] > 0) {
				return true;
			}
		}

		if ($c && $c['type'] > 0) {
			return true;
		}
	}
	else {
		$c = mysql_uniq('select * from core_captcha_setting_user where uid = %s',
						mysql_num($owner['uid'] ));

		if (!$c) {
			$m = mysql_uniq('select * from core_captcha_setting_master limit 1');
			if ($m && $m['type'] > 0) {
				return true;
			}
		}

		if ($c && $c['type'] > 0) {
			return true;
		}
	}
	return false;
}

function create_form_captcha($eid = 0) {
	$sname = session_name();
	$sid   = session_id();

    $tag  = '<img id="captcha_'. $eid. '"src="/modules/captcha/index.php?'.
			$sname. '='. $sid. '" style="margin: 3px 0; border: solid 1px #ddd;"><br />';
	$tag .= '<div style="font-size: 0.8em;">画像の文字を入力して下さい。</div>';
	$form = '<div>'. get_form_text(array('name'  => 'captcha_code',
								'value' => '',
								'size'  => 12)). '</div>';
	return $tag. $form;
}


function check_captcha() {
	if(isset($_SESSION['captcha_keystring']) && $_SESSION['captcha_keystring'] ==  $_POST['captcha_code']) {
	    unset($_SESSION['captcha_keystring']);
		return true;
	}
	else {
	    unset($_SESSION['captcha_keystring']);
		return false;
	}
}

function check_blacklist($eid = 0, $host = null) {
	$owner = get_owner($eid);
	if ($host == null) {
		$ip   = getenv("REMOTE_ADDR");
		$host = getenv("REMOTE_HOST");
		if ($host == null || $host == $ip) {
			$host = gethostbyaddr($ip);
		}
	}
	else {
		$ip = gethostbyname($host);
	}

	$c = mysql_full('select * from core_blacklist_ip_master');
	if ($c) {
		while ($res = mysql_fetch_assoc($c)) {
			if ($res['ip'] == $host || $res['ip'] == $ip) {
				return false;
			}
		}
	}
	if ($owner['gid'] > 0) {
		$c = mysql_full('select * from core_blacklist_ip_group where gid = %s',
						mysql_num($owner['gid']));
		if ($c) {
			while ($res = mysql_fetch_assoc($c)) {
				if ($res['ip'] == $host || $res['ip'] == $ip) {
					return false;
				}
			}
		}
	}
	else {
		$c = mysql_full('select * from core_blacklist_ip_user where uid = %s',
						mysql_num($owner['uid']));
		if ($c) {
			while ($res = mysql_fetch_assoc($c)) {
				if ($res['ip'] == $host || $res['ip'] == $ip) {
					return false;
				}
			}
		}
	}
	return true;
}

function check_ngword($eid = 0, $t = '') {
	if ($t == '') {
		return true;
	}
	$owner = get_owner($eid);
	$c = mysql_full('select * from core_ngword_master');

	$check_words = array();

	if ($c) {
		while ($res = mysql_fetch_assoc($c)) {
			$check_words[] = $res['word'];
		}
	}
	if ($owner['gid'] > 0) {
		$c = mysql_full('select * from core_ngword_group where gid = %s', 
						mysql_num($owner['gid']));
		if ($c) {
			while ($res = mysql_fetch_assoc($c)) {
				$check_words[] = $res['word'];
			}
		}
	}
	else {
		$c = mysql_full('select * from core_ngword_user where uid = %s', 
						mysql_num($owner['uid']));
		if ($c) {
			while ($res = mysql_fetch_assoc($c)) {
				$check_words[] = $res['word'];
			}
		}
	}
	foreach ($check_words as $key) {
		if (strpos($t, $key) === false) {
			continue;
		}
		else {
			return false;
		}
	}

	return true;
}

?>
