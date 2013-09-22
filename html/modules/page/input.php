<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require_once dirname(__FILE__). '/../../lib.php';

/* 振り分け*/
list($eid, $pid) = get_edit_ids();

switch ($_REQUEST["action"]) {
	case 'regist':
		regist_page($eid, $pid);
	default:
		input_page($eid, $pid);
}

/* 登録*/
function regist_page($eid = null, $pid = null) {
	global $SYS_FORM;

	$subject = htmlspecialchars($_POST["subject"], ENT_QUOTES);
	$body    = $_POST["body"];

	if (!$body) {
		$SYS_FORM["error"]["body"] = '内容を入力してください。';
	}

	if ($SYS_FORM["error"]) {
		return;
	}

	if ($eid == 0) {
		$eid = get_seqid();

		$q = mysql_exec("insert into page_data(id, pid, subject, body, initymd)".
					" values(%s, %s, %s, %s, %s)",
					mysql_num($eid), mysql_num($pid), mysql_str($subject),
					mysql_str($body), mysql_current_timestamp());
	}
	else {
		$q = mysql_exec("update page_data set subject = %s, body = %s where id = %s",
					mysql_str($subject), mysql_str($body), mysql_num($eid));
	}

	if (!$q) {
		die('insert failure...');
	}

	set_keyword($eid, $pid);
	set_owner($eid,(get_uid($eid)?get_uid($eid):myuid()),get_gid($eid));

	$path = Path::makeURL( "/" );

	try {
		$block = new Block( $pid );
		$path = $block->getPage()->getUrl();
	} catch ( Exception $e ) {}

	$html = '編集完了しました。';
	$data = array(title   => 'ページデータの編集完了',
				  icon    => 'finish',
				  content => $html. create_form_return(array(eid => $eid, href => $path)));

	show_input($data);
}

/* フォーム*/
function input_page($eid = null, $pid = null) {
	global $SYS_FORM;

	if ($eid > 0) {
		$data = mysql_uniq("select * from page_data where id = %s",
						   mysql_num($eid));
	}
	else {
		$data = mysql_uniq("select * from page_data where pid = %s".
							   " order by initymd desc limit 1;", mysql_num($pid));
	}

	if ($data) {
		$eid     = $data['id'];
		$subject = $data["subject"];
		$body    = $data["body"];
	}

	$SYS_FORM["input"][] = array(body  => get_form("hidden",
												   array(name  => 'action',
														 value => 'regist')));
	$SYS_FORM["input"][] = array(title => '題名',
								 name  => 'subject',
								 body  => get_form("text",
												   array(name  => 'subject',
														 value => $subject,
														 size  => 48)));
	$SYS_FORM["input"][] = array(title => '内容',
								 name  => 'body',
								 body  => get_form("fck",
												   array(name  => 'body',
														 value => $body)));

	$SYS_FORM["action"] = 'input.php';
	$SYS_FORM["method"] = 'POST';

	$SYS_FORM["keyword"] = true;
	$SYS_FORM["submit"]  = '登録';
	$SYS_FORM["cancel"]  = '取消';
	$SYS_FORM["onCancel"] = 'history.back(); return false;';

	$html = create_form(array(eid => $eid, pid => $pid));

	$data = array(title   => 'パーツデータの編集',
				  content => $html);

	show_input($data);

	exit(0);
}

/************************************************************
 * 以下色々
 ************************************************************/
function create_icon() {
	require_once dirname(__FILE__). '/../../lib/class.upload.php';

	$image_file = $_FILES['thumb'];
	$handle = new Upload($image_file);

	$upload_dir       = CONF_BASEDIR. "/u/tmp/";
	$upload_orgin_dir = CONF_BASEDIR. "/u/t/b/";
	$upload_thumb_dir = CONF_BASEDIR. "/u/t/n/";
	$upload_petit_dir = CONF_BASEDIR. "/u/t/m/";

	if(!$handle->uploaded)
		return false;

	$file_ext  = $handle->file_src_name_ext;
	$tmp_filename = rand_str(). getmypid(). time();

	$handle->file_overwrite     = true;
	$handle->file_auto_rename   = false;
	$handle->file_src_name_body = $tmp_filename;
	$handle->Process($upload_dir);

	if (!$handle->processed)
		return false;

	$tmp_filepath = $upload_dir. $tmp_filename. '.'. $file_ext;

	$file_md5 = md5_file($tmp_filepath);
	$re_md5   = md5(rand_str(64). $file_md5. $tmp_filename);

	$new_filename = $re_md5. "." . $file_ext;

	$new_filepath = $upload_orgin_dir. $new_filename;
	//サムネイル画像
	exec('/usr/local/bin/convert'." -geometry 128\\>x128\\> $tmp_filepath $new_filepath ");
	chmod($new_filepath, 0666);

	$thumb_filepath = $upload_thumb_dir. $new_filename;
	//サムネイル画像
	exec('/usr/local/bin/convert'." -geometry 64\\>x64\\> $new_filepath $thumb_filepath ");
	chmod($thumb_filepath, 0666);

	$petit_filepath = $upload_petit_dir. $new_filename;
	//サムネイル画像
	exec('/usr/local/bin/convert'." -geometry 32\\>x32\\> $new_filepath $petit_filepath ");
	chmod($petit_filepath, 0666);

	unlink($tmp_filepath);

	return $new_filename;
}

function update_data($param = array()) {
	$id    = isset($param["id"]) ? intval($param["id"]) : null;
	$type  = isset($param["type"]) ? $param["type"] : 'text';
	$value = isset($param["value"]) ? $param["value"] : null;

	if (!$id) { return; }

	$d = mysql_exec("delete from profile_data where id = %s", $cid);

	switch ($type) {
		case 'value':
			$q = mysql_exec("insert into profile_data (id, value) values(%s, %s)",
							mysql_num($id), mysql_num($value));
			break;
		case 'date':
			$q = mysql_exec("insert into profile_data (id, timestamp) values(%s, %s)",
							mysql_num($id), mysql_str($value));
			break;
		case 'text':
		default:
			$q = mysql_exec("insert into profile_data (id, text) values(%s, %s)",
							mysql_num($id), mysql_str($value));
			break;
	}
}

function form2datetime($prefix = null) {
	$d = array();
	$pat = '/^'. $prefix. '_([a-z])/i';

	foreach ($_REQUEST as $key => $value) {
		if (preg_match($pat, $key, $match)) {
			switch ($match[1]) {
				case 'Y':
				case 'M':
				case 'D':
				case 'h':
				case 'm':
				case 's':
					$d[$match[1]] = $value;
					break;
				default:
					break;
			}
		}
	}

	$utime = mktime(intval($d['h']), intval($d['m']), intval($d['s']),
					intval($d['M']), intval($d['D']), intval($d['Y']));

	return date('Y-m-d H:i:s', $utime);
}

?>
