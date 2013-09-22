<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require dirname(__FILE__). '/../../lib.php';
require dirname(__FILE__). '/../tagreader/common.php';
require dirname(__FILE__). '/../rss/common.php';

$new_mode = false;

/* 振り分け*/
list($eid, $pid) = get_edit_ids();

switch ($_REQUEST["action"]) {
	case 'regist':
		regist_data($eid, $pid);
	default:
		input_data($eid, $pid);
}

/* 登録*/
function regist_data($eid = null, $pid = null) {

	//	フォームIDが設定されているかどうかを確認する.
	if ( false === FormBuildId::checkFormBuildId() ) {
		show_error("セッションが無効になった可能性があります<br/>"
				."もういちど編集画面からやり直して下さい");
		exit(0);
	}

	global $SYS_FORM;

	// フォームのキャッシュに溜め込む
	$SYS_FORM["cache"]["subject"] = htmlspecialchars( isset($_POST["subject"]) ? $_POST["subject"] : '無題', ENT_QUOTES );
	$SYS_FORM["cache"]["body"]    = stripslashes( $_POST["body"] );
//	$SYS_FORM["cache"]["body"]    = XssFilter::filter( stripslashes( $_POST["body"] ) );
	if (intval($_POST["initymd_set"]) == 1) {
		$SYS_FORM["cache"]["initymd"]     = post2timestamp('initymd');
		$SYS_FORM["cache"]["initymd_set"] = 1;
	}
	else {
		$SYS_FORM["cache"]["initymd"] = date('Y-m-d H:i:s');
		$SYS_FORM["cache"]["initymd_set"] = 0;
	}

	// 入力エラーチェック
	if (!$SYS_FORM["cache"]["body"] || $SYS_FORM["cache"]["body"] == '<br />') {
		$SYS_FORM["error"]["body"] = '内容は何か書いてください。';
	}
	if ($SYS_FORM["error"]) {
		return;
	}
	// 登録
	$subject = $SYS_FORM["cache"]["subject"];
	$body    = $SYS_FORM["cache"]["body"];
	$initymd = $SYS_FORM["cache"]["initymd"];


	// pidはもう使いません。コード整理時に消します。
	if ($eid == 0) {
		$eid = get_seqid();

		$q = mysql_exec("insert into blog_data".
						" (id, pid, subject, body, initymd)".
					" values(%s, %s, %s, %s, %s)",
					mysql_num($eid), mysql_num($pid),
					mysql_str($subject), mysql_str($body), mysql_str($initymd));
	}
	else {
		$p = mysql_uniq("select * from blog_data where id = %s",
					mysql_num($eid));
		$pid = $p['pid'];
		
		$q = mysql_exec("update blog_data set subject = %s, body = %s, initymd = %s".
						" where id = %s",
						mysql_str($subject), mysql_str($body), mysql_str($initymd),
						mysql_num($eid));
	}

	if (!$q) {
		show_error('登録に失敗しました。'. mysql_error());
	}

	$post_bosai_web = $_POST["post_bosai_web"] ? intval($_POST["post_bosai_web"]) : 0;
	$post_reporter = $_POST["post_reporter"] ? intval($_POST["post_reporter"]) : 0;
	$pmt_mode = $_POST["pmt_mode"] ? intval($_POST["pmt_mode"]) : 0;

	set_keyword($eid,$pid);
	set_point($eid,$pid);
	set_comment($eid);
	set_trackback($eid);

	if ($post_bosai_web == 1) {
		if ($pmt_mode == 1) {
			$auth_mode = intval($_POST["auth_mode"]);
			if (mysql_uniq('select * from bosai_web_auth where id = %s', mysql_num($eid))) {
				if ($auth_mode == 1) {
					$u = mysql_exec('update bosai_web_auth set comment = %s, display = %s where id = %s',
									mysql_str(''), mysql_num($auth_mode), mysql_num($eid));
				}
				else {
					$u = mysql_exec('update bosai_web_auth set display = %s where id = %s',
									mysql_num($auth_mode), mysql_num($eid));
				}
			}
			else {
				$a = mysql_exec('insert into bosai_web_auth (id, display) values (%s, %s)',
								mysql_num($eid), mysql_num($auth_mode));
			}
			set_pmt(array(eid => $eid, gid =>get_gid($pid), unit => PMT_PUBLIC));
			$b = mysql_uniq('select * from bosai_web_block where block_id = %s', mysql_num($pid));
			if ($b) {
				$target_gid = get_gid($b['eid']);
				if ($target_gid > 0) {
					$t = mysql_full("select rs.* from rss_setting as rs".
									" inner join owner as o on rs.eid = o.id".
									" where o.gid = %s", mysql_num($target_gid));
					if ($t) {
						while ($r = mysql_fetch_array($t)) {
							mod_rss_crawl($r['eid']);
						}
					}
					$y = mysql_full("select rs.* from tagreader_setting as rs".
									" inner join owner as o on rs.eid = o.id".
									" where o.gid = %s", mysql_num($target_gid));
					if ($y) {
						while ($r = mysql_fetch_array($y)) {
							mod_tagreader_crawl($r['eid']);
						}
					}
				}
			}
		}
		else {
			set_pmt(array(eid => $eid, gid =>get_gid($pid), name => 'pmt_0'));
			tell_update($eid, 'ブログ');
		}
	}
	else if ($post_reporter == 1) {
		if ($pmt_mode == 1) {
			$auth_mode = intval($_POST["auth_mode"]);
		$p = mysql_uniq("select * from blog_data where id = %s",
						mysql_num($eid));
		$pid = $p['pid'];
			
			$s = mysql_uniq('select * from reporter_block as rb'.
							' inner join reporter_setting as rs on rb.eid = rs.id'.
							' where rb.block_id = %s', mysql_num($pid));

			if ($s) {
				if ($s['auth_mode'] == 1) {
					$auth_mode = 2;
				}
			}

			if (mysql_uniq('select * from reporter_auth where id = %s', mysql_num($eid))) {
				if ($auth_mode == 1) {
					$u = mysql_exec('update reporter_auth set comment = %s, display = %s where id = %s',
									mysql_str(''), mysql_num($auth_mode), mysql_num($eid));
				}
				else {
					$u = mysql_exec('update reporter_auth set display = %s where id = %s',
									mysql_num($auth_mode), mysql_num($eid));
				}
			}
			else {
				$a = mysql_exec('insert into reporter_auth (id, display) values (%s, %s)',
								mysql_num($eid), mysql_num($auth_mode));
			}
			if ($auth_mode == 2) {
				set_pmt(array(eid => $eid, gid =>get_gid($pid), name => 'pmt_0'));
				tell_update($eid, 'ブログ');
			}
			else {
				set_pmt(array(eid => $eid, gid =>get_gid($pid), unit => PMT_PUBLIC));
			}
		}
	}
	else {
		set_pmt(array(eid => $eid, gid =>get_gid($pid), name => 'pmt_0'));

		tell_update($eid, 'ブログ');
	}

	$html = '編集完了しました。';
	$data = array(title   => '編集完了',
				  icon    => 'finish',
				  content => $html. create_form_return(array(eid => $eid, href => home_url($eid))));

	show_input($data);

	exit(0);
}

function input_data($eid = null, $pid = null) {
	global $new_mode;
	global $SYS_FORM, $JQUERY, $COMUNI_HEAD_CSSRAW;
	global $SYS_BOX_TITLE;

	$d = mysql_uniq('select * from blog_data'.
					' where id = %s',
					mysql_num($eid));

	// Y-m-d H:i:s
	if ($d) {
		$pid         = $d['pid'];
		$subject     = $d["subject"];
		$body        = $d["body"];
		$initymd     = strtotime($d["initymd"]);
		$initymd_set = 1;
	}
	else {
		$subject     = '';
		$body        = '';
		$initymd     = time();
		$initymd_set = 0;
	}
	// 再入力ならキャッシュから拾う
	if (isset($SYS_FORM["cache"])) {
		$subject     = $SYS_FORM["cache"]["subject"];
		$body        = $SYS_FORM["cache"]["body"];
		$initymd     = strtotime($SYS_FORM["cache"]["initymd"]);
		$initymd_set = strtotime($SYS_FORM["cache"]["initymd_set"]);
		if ($initymd_set == 0) {
			$initymd = time();
		}
	}

	$COMUNI_HEAD_CSSRAW[] = '#initymd { padding-left: 1.5em; }';

	if ($initymd_set == 0) {
		$JQUERY['ready'][] = '$(\'#initymd > input\').attr(\'disabled\', \'disabled\').css("background-color", "#efefef");';
	}
	$JQUERY['ready'][] = <<<__READY_FUNCTION__
$('#initymd_set_0').click(function() {
	$('#initymd > input').attr('disabled', 'disabled').css("background-color", "#efefef");
});
$('#initymd_set_1').click(function() {
	$('#initymd > input').removeAttr('disabled').css("background-color", "#ffffff");
});
__READY_FUNCTION__;
	;

	// hidden:action
	$attr = array(name => 'action', value => 'regist');
	$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));

	if ($eid > 0) {
		$p = mysql_uniq("select * from blog_data where id = %s",
					mysql_num($eid));
		$pid = $p['pid'];
		
		$check_id = $pid;
	}
	else {
		$new_mode = true;
		$check_id = $pid;
	}

	$p = mysql_uniq('select * from reporter_block as rb'.
					' left join reporter_setting as rs'.
					' on rb.eid = rs.id'.
					' where rb.block_id = %s',
					mysql_num($check_id));

	$SYS_BOX_TITLE = 'ブログの編集';
	if ($p) {
		$parent_sitename = get_site_name(get_site_id($p['id']));

		$notice = '';
		$notice .= '<h4 class="reporter_msg">こちらで投稿された内容は'.
				 $parent_sitename. 'で管理しています。'.
				 '</h4>';
		$notice .= '記事の公開については、'. $parent_sitename. 'の判断で行われます。';

		// date:initymd
		$SYS_FORM["input"][] = array(title => 'ご注意',
								 name  => 'reporter',
								 body  => $notice);

		$c = mysql_uniq('select * from reporter_auth where id = %s',
						mysql_num($eid));
		if ($c && ($c['comment'] != '')) {
			$correct = '<div style="border: solid 3px #ffcdbe;">'. $c['comment']. '</div>';
			$SYS_FORM["input"][] = array(title => '校正依頼',
									 name  => 'correct',
									 body  => $correct);
		}

		$attr = array(name => 'post_reporter', value => '1');
		$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));
		$attr = array(name => 'pmt_mode', value => '1');
		$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));
		if ($p['auth_mode'] == 0) {
			$option = array(0 => '編集中 (下書き)', 1 => '正式投稿 (承認待ち)');
			$attr = array(name => 'auth_mode', value => 0, option => $option);
			$SYS_FORM["input"][] = array(title => '記事の状態',
										 name  => 'body',
										 body  => get_form("radio", $attr));
		}
		else {
			$notice = 'この記事は管理者の承認無しで自動で投稿されますのでご注意下さい。<br>';
			$notice .= '記事のパーミッションは各自で設定して下さい。<br>';

			$SYS_FORM["input"][] = array(title => 'この記事は自動で承認されます。',
										 name  => 'notice',
										 body  => $notice);
			$SYS_FORM["pmt"] = true;
		}

		if ($new_mode) {
			$SYS_FORM["keyword"] = $p['eid'];
		}
		else {
			$SYS_FORM["keyword"] = true;
		}

		$SYS_BOX_TITLE = '市民レポーター投稿';
	}
	else {
		$SYS_FORM["pmt"] = true;
		$SYS_FORM["keyword"] = true;

		form_bosai_web($eid, $check_id);
	}

	// date:initymd
	$option = array(0 => '投稿した日時', 1=> '日付を指定');
	$attr = array(name => 'initymd_set', value => $initymd_set, option => $option, break_num => 1);
	$radio  = get_form("radio", $attr);

	$attr = array(name => 'initymd', value => $initymd, format => 'Y年M月D日 h時m分');
	$SYS_FORM["input"][] = array(title => '日時',
								 name  => 'subject',
								 body  => $radio. get_form("date", $attr));
	// text:subject
	$attr = array(name => 'subject', value => $subject, size => 64);
	$SYS_FORM["input"][] = array(title => '題名',
								 name  => 'subject',
								 body  => get_form("text", $attr));

	// fck:body
	$attr = array(name => 'body', value => $body, cols => 64, rows => 8);
	$SYS_FORM["input"][] = array(title => '内容',
								 name  => 'body',
								 body  => get_form("fck", $attr));

	$SYS_FORM["action"] = 'input.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["map"]     = '位置情報';
	$SYS_FORM["comment"] = true;
	$SYS_FORM["trackback"] = true;

	$SYS_FORM["submit"] = '登録';
	$SYS_FORM["cancel"] = '取消';

	$html = create_form(array(eid => $eid, pid => $pid));

	$data = array(title   => $SYS_BOX_TITLE,
				  icon    => 'write',
				  content => $html);

	show_input($data);

	exit(0);
}

function form_bosai_web($eid = null, $check_id = null) {
	global $new_mode;
	global $SYS_FORM, $SYS_BOX_TITLE, $COMUNI_HEAD_CSSRAW;

	$p = mysql_uniq('select * from bosai_web_block as rb'.
					' left join bosai_web_setting as rs'.
					' on rb.eid = rs.id'.
					' where rb.block_id = %s',
					mysql_num($check_id));

	if ($p) {
		$parent_sitename = get_site_name(get_site_id($p['id']));

		$notice = '';
		$notice .= '<h4 class="reporter_msg">こちらで投稿された内容情報は'.
				 $parent_sitename. 'で管理しています。'.
				 '</h4>';
		$notice .= '記事の公開については、'. $parent_sitename. 'の判断で行われます。';

		// date:initymd
		$SYS_FORM["input"][] = array(title => 'ご注意',
								 name  => 'reporter',
								 body  => $notice);

		$c = mysql_uniq('select * from bosai_web_auth where id = %s',
						mysql_num($eid));
		if ($c && ($c['comment'] != '')) {
			$correct = '<div style="border: solid 3px #ffcdbe;">'. $c['comment']. '</div>';
			$SYS_FORM["input"][] = array(title => '校正依頼',
									 name  => 'correct',
									 body  => $correct);
		}

		$attr = array(name => 'post_bosai_web', value => '1');
		$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));
		$attr = array(name => 'pmt_mode', value => '1');
		$SYS_FORM["input"][] = array(body => get_form("hidden", $attr));

		$view_href= '/modules/bosai_web/view_template.php?block_id='. $check_id. '&site_id='. get_site_id($check_id);
		$SYS_FORM["input"][] = array(title => '防災ウェブ雛形一覧',
									 name  => 'view_template',
									 body  => '<div id="newwindow">'. make_href('雛形一覧を開く', $view_href, true). '</div>');
/*
		$SYS_FORM["input"][] = array(title => '防災ウェブユーザー雛形一覧',
									 name  => 'view_template_bysite',
									 body  => make_href('新規登録', '/modules/bosai_web/template_bysite.php?pid='. $check_id). ' / '.
											  make_href('雛形一覧', '/modules/bosai_web/template_list_bysite.php?pid='. $check_id));
*/

		$COMUNI_HEAD_CSSRAW[] = '#newwindow { text-align: center; width: 150px; margin: 3px; border: solid 1px #bcd2de; background: #f9fbfc; }';
		$COMUNI_HEAD_CSSRAW[] = '#newwindow a { display: block; padding: 3px; }';

		$option = array(0 => '編集中 (下書き)', 1 => '正式投稿 (承認待ち)');
		$attr = array(name => 'auth_mode', value => 0, option => $option);
		$SYS_FORM["input"][] = array(title => '記事の状態',
									 name  => 'body',
									 body  => get_form("radio", $attr));

		if ($new_mode) {
			$SYS_FORM["keyword"] = $p['eid'];
		}
		else {
			$SYS_FORM["keyword"] = true;
		}
		$SYS_FORM["pmt"] = false;

		$SYS_BOX_TITLE = '防災ウェブ投稿';
	}
	else {
		$SYS_FORM["pmt"] = true;
	}
}

function reload_rss($id = null) {
	$target_gid = get_gid($id);
	$target_uid = myuid();

	if ($target_gid > 0) {
		$q = mysql_full("select rs.* from rss_setting as rs".
						" inner join owner as o on rs.eid = o.id".
						" where o.gid = %s", mysql_num($target_gid));
	}
	else {
		$q = mysql_full("select rs.* from rss_setting as rs".
						" inner join owner as o on rs.eid = o.id".
						" where o.uid = %s", mysql_num($target_uid));
	}

	if ($q) {
		while ($r = mysql_fetch_array($q)) {
			mod_rss_crawl($r['eid']);
		}
	}
}

function reload_tagreader($id = null) {
	$target_gid = get_gid($id);
	$target_uid = myuid();

	if ($target_gid > 0) {
		$q = mysql_full("select rs.* from tagreader_setting as rs".
						" inner join owner as o on rs.eid = o.id".
						" where o.gid = %s", mysql_num($target_gid));
	}
	else {
		$q = mysql_full("select rs.* from tagreader_setting as rs".
						" inner join owner as o on rs.eid = o.id".
						" where o.uid = %s", mysql_num($target_uid));
	}

	if ($q) {
		while ($r = mysql_fetch_array($q)) {
			mod_tagreader_crawl($r['eid']);
		}
	}
}


?>
