<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

function mod_glist_config($id) {
	if (!is_owner($id,80)) {
		return;
	}
	$menu = array();
	$menu[] = array(title => '参加設定',
					url => '/modules/glist/input.php?eid='. $id,
					inline => true);

	$menu[] = array(title => '参加承認',
					url => '/modules/glist/member.php?eid='. $id,
					inline => true);

	return block_edit_menu($id, $menu);
}

function mod_glist_block($id = null) {
	global $JQUERY;

	$data = array(id       => $id,
				  editlink => mod_glist_config($id),
				  title    => 'GLIST',
				  content  => '');

	$gid = get_gid($id);

	$i = mysql_uniq("select * from group_joinable".
					" where gid = %s",
					mysql_num($gid));

	if (!$i) {
		if (is_owner($id,80)) {
			$data["content"] = 'はじめに参加設定を行って下さい。';
			return $data;
		}
		return $data;
	}

	$entry_tag = '';
	if (is_login()) {
		if (mod_glist_joined($gid)) {
			$href = '/modules/glist/bye.php?eid='. $id. '&keepThis=true&TB_iframe=true&height=480&width=640';
			$entry_tag = '<div class="glist_bye">'.
						 '<a class="thickbox" href="'. $href. '">このグループから脱退</a>'.
						 '</div>';
		}
		else if (mod_glist_inapp($gid)) {
			$entry_tag = '<div class="glist_entry">'.
						 '<a href="#" onclick="alert(\'参加承認が行われるまでお待ち下さい。\');return false;">現在参加申請中</a>'.
						 '</div>';
		}
		else if ($i["type"] < 2) {
			$href = '/modules/glist/entry.php?eid='. $id. '&keepThis=true&TB_iframe=true&height=480&width=640';
			$entry_tag = '<div class="glist_entry">'.
						 '<a class="thickbox" href="'. $href. '">このグループに参加</a>'.
						 '</div>';
		}
	}

	$q = mysql_full("select gm.*, user.handle, page.id from group_member as gm ".
					" inner join user on gm.uid = user.id".
					" left join page on page.uid = gm.uid".
					" where user.enable = 1 and gm.gid = %s".
					" order by gm.level DESC",
					mysql_num($gid));

	if (!$q) {
		echo mysql_error();
		return $data;
	}

	$num = 0;
	$html = '<div class="glist_main">';
	$more = '<div class="glist_main" id="glist_more">';

	if (is_owner($id,80)) {
		$count = mod_glist_count_app($gid);
		if ($count > 0) {
			$html .= '<div id="glist_app" style="font-size: 0.8em;">現在 '. $count. ' 人が承認待ちです。</div>';
		}
	}

	while ($res = mysql_fetch_array($q)) {
		$handle = htmlspecialchars($res["handle"]);
		if ($res["id"]) {
			$href = '/user.php?uid='. $res["uid"];
			if ($res['level'] == 100) {
				$line = '<a class="ulist_block_owner" href="'. $href. '"><span>'.
						 $handle. ' さん</span></a>';
			}
			else {
				$line = '<a class="ulist_block" href="'. $href. '"><span>'.
						 $handle. ' さん</span></a>';
			}
		}
		else {
			$line = '<div class="glist_nohref"><span>'. $handle. ' さん</span></div>';
		}
		if ($num > 10) {
			$more .= $line;
		}
		else {
			$html .= $line;
		}
		$num++;
	}
	$html .= '</div>';
	$more .= '</div>';
	if ($num > 10) {
		$JQUERY['ready'][] = <<<__JQ__
$('#glist_more').hide();
$('#glist_hide_href').hide();
$('#glist_show_href').click(function() {
	$('#glist_show_href').hide();
	$('#glist_hide_href').show();
	$('#glist_more').slideDown();
});
$('#glist_hide_href').click(function() {
	$('#glist_show_href').show();
	$('#glist_hide_href').hide();
	$('#glist_more').hide();
});
__JQ__;
		;
		$html .= '<div style="text-align: right;" id="glist_show_href"><a href="#" onClick="return false;">全て表示 &raquo;</a></div>';
		$more .= '<div style="text-align: right;" id="glist_hide_href"><a href="#" onClick="return false;">&laquo; 隠す</a></div>';

	}

	$data["content"] = $html. $more. $entry_tag;

	return $data;
}

function mod_glist_joined($gid) {
	return mysql_uniq("select * from group_member where gid = %s and uid = %s",
					  mysql_num($gid), mysql_num(myuid()));
}

function mod_glist_inapp($gid) {
	return mysql_uniq("select * from group_app where gid = %s and uid = %s",
					  mysql_num($gid), mysql_num(myuid()));
}

function mod_glist_count_app($gid = 0) {
	$c = mysql_uniq('select count(*) from group_app'.
					' inner join user on user.id = group_app.uid'.
					' where group_app.gid = %s',
					mysql_num($gid));
	if ($c) {
		return $c['count(*)'];
	}
	return 0;
}

?>
