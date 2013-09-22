<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require_once dirname(__FILE__). '/../../lib.php';

$uid = myuid();
$eid = intval($_POST['eid']);

$enq = array();
foreach ($_POST as $key => $value) {
	if (preg_match('/^enq_(\d+)/', $key, $match)) {
		$enq[$match[1]] = $value;
	}
}

$new_id = 0;

$cookie = isset($_COOKIE['mod_eq']) ? $_COOKIE['mod_eq'] : null;

if (is_login()) {
	if (!is_allow_dup($eid)) {
		$c = mysql_uniq('select * from mod_enquete_vcheck'.
						' where eid = %s and uid = %s',
						mysql_num($eid), mysql_num(myuid()));

		if ($c) {
			$ref = CONF_URLBASE. "/index.php?module=enquete&eid=${eid}&action=denymsg";
			$_SESSION['enquete_denymsg'] = true;
			header('Location: '. $ref); 
	
			exit(0);
		}
		else {
			if (!$cookie) {
				$str = rand_str(16, 'alpha');
				setcookie ("mod_eq", $str, time() + 24 * 3600);
				$cookie = $str;
			}

			$i = mysql_exec('insert into mod_enquete_vcheck (eid, uid, cookie)'.
							' values(%s, %s, %s)',
							mysql_num($eid), mysql_num(myuid()), mysql_str($cookie));
		}
	}
}
else {
	if (!is_allow_dup($eid)) {
		$q = mysql_uniq('select * from mod_enquete_vcheck'.
						' where eid = %s and uid = %s and cookie = %s',
						mysql_num($eid), mysql_num(myuid()), mysql_str($cookie));

		if ($q) {
			$ref = CONF_URLBASE. "/index.php?module=enquete&eid=${eid}&action=denymsg";
			$_SESSION['enquete_denymsg'] = true;
			header('Location: '. $ref); 

			exit(0);
		}
		else {
			if (!$cookie) {
				$str = rand_str(16, 'alpha');
				setcookie ("mod_eq", $str, time() + 24 * 3600);

				$cookie = $str;
			}

			$i = mysql_exec('insert into mod_enquete_vcheck (eid, uid, cookie)'.
							' values(%s, %s, %s)',
							mysql_num($eid), mysql_num(myuid()), mysql_str($cookie));
		}
	}
}

/*
if (!$cookie) {
	$str = rand_str(16, 'alpha');
	setcookie ("mod_eq", $str, time() + 24 * 3600);

//	$d = mysql_exec('delete from mod_enquete_vcheck where initymd < (CURDATE() - 2)');

	$i = mysql_exec('insert into mod_enquete_vcheck (eid, uid, cookie)'.
					' values(%s, %s, %s)',
					mysql_num($eid), mysql_num(myuid()), mysql_str($str));

	$cookie = $str;
}
else {
	if (!is_allow_dup($eid)) {
		$q = mysql_uniq('select * from mod_enquete_vcheck'.
						' where eid = %s and uid = %s and cookie = %s',
						mysql_num($eid), mysql_num(myuid()), mysql_str($cookie));

		if ($q) {
			$ref = CONF_URLBASE. "/index.php?module=enquete&eid=${eid}&action=denymsg";
			$_SESSION['enquete_denymsg'] = true;
			header('Location: '. $ref); 
	
			exit(0);
		}
	}
}
*/

$line = '';
foreach ($enq as $e => $d) {
	if (is_array($d)) {
		$data = implode('-_-', $d);
	}
	else {
		$data = $d;
	}
	$data = htmlesc(strip_tags($data));

	$uniq_id = rand_str(24);

	$q = mysql_exec('insert into enquete_vote_data'.
					' (eid, uid, uniq_id, cookie, num, data)'.
					' values (%s, %s, %s, %s, %s, %s)',
					mysql_num($eid), mysql_num($uid), mysql_str($uniq_id), mysql_str($cookie), mysql_num($e), mysql_str($data));

	if (!$q) {
		show_error(mysql_error());
	}

	$q = mysql_uniq('select * from enquete_form_data where uniq_id = %s', mysql_num($e));

	$form = array();
	if ($q) {
		$title = $q['title'];
		$value = ''; $option = array();

		switch ($q['type']) {
			case 'hidden':
			case 'text':
			case 'textarea':
				$value = $data;
			break;
			case 'radio':
			case 'select':
				$list = explode('-_-', $q['opt_list']);
				if (intval($data) > 0) {
					$value = $list[intval($data)-1];
				}
			break;
			case 'checkbox':
				$list = explode('-_-', $data);
				$value = implode(',', $list);
			break;
			default :
				$value = $q['def_val'];
				$size  = $q['opt_size'];
		}
	}
	$line .= '"'. $value. '",';
}

if (is_login()) {
	$name = get_handle(myuid());
}
else {
	$name = '未登録ユーザー';
}
$line = '"'. $name. '",'. $line;
$i = mysql_exec('insert into mod_enquete_csv (eid, data) values (%s, %s)', mysql_num($eid), mysql_str($line));

$ref = CONF_URLBASE. "/index.php?module=enquete&eid=${eid}&action=thxmsg";

$_SESSION['enquete_thxmsg'] = true;

tell_vote($eid, $name);

header('Location: '. $ref); 

function is_allow_dup($id = 0) {
	$q = mysql_uniq('select dup from enquete_data where id = %s',
					mysql_num($id));
	if ($q) {
		if ($q['dup'] > 0) {
			return false;
		}
	}
	return true;
}

function tell_vote($eid = 0, $name = '') {
	$e = mysql_uniq('select * from enquete_data where id = %s', mysql_num($eid));

	if ($e['tell_vote'] > 0) {
		return;
	}

	$udata = eget_owner($eid);

	$subject = 'アンケート: '. $e['subject']. 'への投票';
	$body    = 'アンケート: '. $e['subject']. 'へ'. $name. 'さんから投票がありました。'.
				"\n\n下記より確認して下さい。\n\n". CONF_URLBASE. home_url($eid);

	foreach ($udata as $uid => $email) {
		$fwd = get_fwd_mail($uid);
		if (isset($fwd) && count($fwd) > 0) {
			$to = $fwd;
		}
		else {
			$to = $email;
		}

		send_message( 0, $uid, 0, $subject, $body );

//		$body_head = get_handle($uid). " 様\n\n";
//		sys_fwdmail(array('to' => $to, 'subject' => $subject, 'body' => $body_head. $body));
	}
}

function eget_owner($eid = 0) {
	$q = mysql_uniq('select * from owner where id = %s', mysql_num($eid));

	$admin = array();

	if ($q) {
		if ($q['gid'] == 0) {
			$g = mysql_uniq('select * from user where id = %s', mysql_num($q['uid']));

			$admin[$q['uid']] = $g['email'];
		}
		else {
			$g = mysql_full('select * from group_member'.
							' where gid = %s and level = 100', mysql_num($q['gid']));
			while ($res = mysql_fetch_assoc($g)) {
				$admin[$res['uid']] = $res['email'];
			}
		}
	}
	else {
		return false;
	}

	return $admin;
}

?>
