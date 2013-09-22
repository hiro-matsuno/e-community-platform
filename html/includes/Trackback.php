<?php 
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
?>
<?php

require_once dirname(__FILE__).'/../PEAR/Service/Trackback.php';

//-----------------------------------------------------
// * トラックバック送信先入力欄生成
//-----------------------------------------------------
function trackback_url_form($eid = 0) {
	$url      = array();
	$value    = '';
	$send_url = '';
	$q = mysql_full('select * from trackback_url where eid = %s'.
					' order by id',
					mysql_num($eid));
	if ($q) {
		while ($d = mysql_fetch_array($q)) {
			$url[] = $d['url'];
		}
	}

	if (count($url) > 0) {
		$value = implode("<br>\n", $url);
		$send_url = '送信済URL (同じURLに複数回送信する場合はご注意下さい)<br>'. $value;
	}

	$attr = array('name'   => 'trackback_url_'. $eid,
				  'value'  => '',
				  'width'  => '80%',
				  'height' => '80px',
				  'ahtml'  => $send_url);

	return get_form("textarea", $attr);
}

//-----------------------------------------------------
// * トラックバックのセット
//-----------------------------------------------------
function set_trackback($eid = 0) {
	$unit = isset($_REQUEST['trackback_'. $eid]) ?
				intval($_REQUEST['trackback_'. $eid]) :
				intval($_REQUEST['trackback_0']);

	$d = mysql_exec('delete from trackback_allow where eid = %s',
					mysql_num($eid));
	$i = mysql_exec('insert into trackback_allow (eid, unit)'.
					' values (%s, %s)',
					mysql_num($eid), mysql_num($unit));

	$tb_input = isset($_REQUEST['trackback_url_'. $eid]) ?
				$_REQUEST['trackback_url_'. $eid] :
				$_REQUEST['trackback_url_0'];

	$tb_input = preg_replace("/\r\n/", "\n", $tb_input);
	$tb_input = preg_replace("/\r/", "\n", $tb_input);
	$tb_input = trim($tb_input);

	$tb_list = array();
	$value   = array();
	if ($tb_input != '') {
		if (preg_match("/\n/", $tb_input)) {
			$tb_list = explode("\n", $tb_input);
		}
		else {
			$tb_list = array($tb_input);
		}
	}
	if (count($tb_list) > 0) {
		foreach ($tb_list as $tb_url) {
			if (send_trackback($eid, $tb_url)) {
				$value[] = '('. $eid. ', '. mysql_str($tb_url). ')';
			}
		}
	}
	if (count($value) > 0) {
//		$d = mysql_exec('delete from trackback_url where eid = %s',
//						mysql_num($eid));
		$i = mysql_exec('insert into trackback_url (eid, url) values %s',
						implode(',', $value));
	}
}

//-----------------------------------------------------
// * トラックバック可能かどうか
//-----------------------------------------------------
function is_trackback($eid = 0) {
	global $TRACKBACK_NOTICE;

	$q = mysql_uniq('select * from trackback_allow where eid = %s',
					mysql_num($eid));

	if ($q) {
		switch($q['unit']) {
			case 1:
				$TRACKBACK_NOTICE = 'この記事にトラックバックすることはできません。';
				return false;
			break;
			default:
				$TRACKBACK_NOTICE = '';
				;
		}
	}

	return true;
}

//-----------------------------------------------------
// * トラックバックの件数を取得
//-----------------------------------------------------
function count_trackback($eid = 0) {
	$q = mysql_uniq('select count(*) from trackback where eid = %s', mysql_num($eid));
	if ($q) {
		return $q['count(*)'];
	}
	return '0';
}

//-----------------------------------------------------
// * トラックバックのロード
//-----------------------------------------------------
function load_trackback($eid = 0) {
	global $JQUERY;

	$host = CONF_URLBASE;
	$uri  = '/trackback.php/';

	$rand_id = rand_str(12);

	$html = '<div><a name="trackback"></a>* トラックバック</div>';

	$is_owner = is_owner($eid);

	$q = mysql_full('select * from trackback where eid = %s order by id',
					mysql_num($eid));
	if ($q) {
		while ($r = mysql_fetch_array($q)) {
			$del_href = '';
			if ($is_owner) {
				$del_href = '&nbsp;<a href="/trackback_del.php?id='. $r['id']. '&keepThis=true&TB_iframe=true&height=480&width=640" title="トラックバックの削除" '.
							' onClick="return my_tb(this);">[削除]</a><br />';
				$del_href .= '<span style="color: #999">['. $r['host']. ']</span>';
			}

			$r['excerpt'] = preg_replace('/&lt;/', '<', $r['excerpt']);
			$r['excerpt'] = preg_replace('/&gt;/', '>', $r['excerpt']);

			$html .= '<a class="common_href" href="'. $r['url']. '" target="_blank"><span>'.
					 $r['title']. '</span></a>'.
					 '<div class="common_body">'. clip_str(strip_tags($r['excerpt']), 300). '</div>'.
					 '<div class="common_date">'. date('Y年m月d日 H:i', strtotime($r['date'])).
					 $del_href. '</div>'. "\n";
		}
	}

	if (is_trackback($eid)) {
		$JQUERY['ready'][] = <<<__JQ__
$('#trackback_${rand_id}').before('${host}');
$('#trackback_${rand_id}').html('${uri}');
$('#trackback_${rand_id}').after('${eid}');
__JQ__;
		;
		$html .= '<div style="text-align: right;">'.
				 'トラックバックURL: <span id="trackback_'. $rand_id. '"></span>'.
				 '</div>';
	}
	else {
		$html .= '<div style="text-align: right;">'.
				 '<small>この記事へのトラックバックは許可されていません</small>'.
				 '</div>';
	}

	return $html;

}

//-----------------------------------------------------
// * トラックバックの送信
//-----------------------------------------------------
function send_trackback($eid = 0, $target = '') {
	require_once 'Service/Trackback.php';

	if ($eid == 0) {
		return false;;
	}

	$d = permalink($eid);

	if ($d) {
		$trackback = new Services_Trackback();
		$trackback->set('title',         $d['title']);
		$trackback->set('url',           $d['url']);
		$trackback->set('excerpt',       $d['excerpt']);
		$trackback->set('blog_name',     $d['sitename']);
		$trackback->set('trackback_url', $target);

		$result = $trackback->send();
		if (PEAR::isError($result)) {
			return false;
			echo $result->getMessage();
		}
	}
	return true;
}

//-----------------------------------------------------
// * パーマリンクの収録 (パーツに依存)
//-----------------------------------------------------
function permalink($eid = 0) {
	$p = mysql_uniq('select pid from blog_data where id = %s',$eid);
	$pid = $p['pid'];
	$q = mysql_uniq('select * from block where id = %s',
					mysql_num($pid));
	if (!$q) {
		return;
	}
	$module = $q['module'];

	$func_name = 'mod_'. $module. '_permalink';
	if (function_exists($func_name)) {
		return call_user_func_array($func_name, array($eid));
	}
	return array();
}

//-----------------------------------------------------
// * パーマリンクの収録 (ブログパーツ専用)
//-----------------------------------------------------
function mod_blog_permalink($eid = 0) {
	$q = mysql_uniq('select * from blog_data where id = %s',
					mysql_num($eid));
	if ($q) {
		return array('id'       => $eid,
					 'url'      => CONF_URLBASE. "/index.php?module=blog&eid=$eid&blk_id=$$q[pid]",
					 'title'    => $q['subject'],
					 'excerpt'  => clip_str(strip_tags($q['body']), 256),
					 'date'     => $q['initymd'],
					 'sitename' => get_sitename($eid));
	}
}

?>
