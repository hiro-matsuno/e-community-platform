<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

function mod_tagreader_crawl($eid) {
	$q = mysql_full("select * from tagreader_setting".
					" where eid = %s",
					mysql_num($eid));

	if (!$q) {
		return;
	}

	$t = mysql_exec("delete from tagreader_data_tmp where eid = %s", mysql_num($eid));

	$update = true; $count = 0; $throw = array();
	while ($d = mysql_fetch_array($q)) {
		$disp_num[$eid]  = $d["disp_num"];
		$keyword[$eid]   = preg_replace('/ /', '|', $d["keyword"]);

		$mod_target[$eid] = explode(' ', $d["mod_target"]);

		foreach ($mod_target[$eid] as $mod) {
			$table = $mod. '_data';

			$b = mysql_full("select d.id, d.subject, d.initymd, d.pid, ts.keyword".
							" from ${table} as d".
							" inner join tag_data as td on td.pid = d.id".
							" inner join tag_setting as ts on td.tag_id = ts.id".
							" where".
//							" d.subject REGEXP %s".
//							" or d.body REGEXP %s".
//							" or ts.keyword REGEXP %s",
							" ts.keyword REGEXP %s",
//							mysql_str($keyword[$eid]), mysql_str($keyword[$eid]), mysql_str($keyword[$eid]));
							mysql_str($keyword[$eid]));
			if ($b) {
				while ($item = mysql_fetch_array($b)) {
					if ($throw[$item['id']] == true) {
						continue;
					}

					$subject = $item["subject"];
					$url     = "/index.php?module=$mod&eid=$item[id]&blk_id=".($item['blk_id']?$item['blk_id']:$item['pid']);
					$date    = $item['initymd'];

					$i = mysql_exec("insert into tagreader_data_tmp (eid, article_id, blk_id, title, url, initymd)".
									" values (%s, %s, %s, %s, %s, %s)",
									mysql_num($eid), mysql_num($item['id']), mysql_num($item['pid']), mysql_str($subject), 
									mysql_str($url), mysql_str($date));
					if (!$i) {
						echo mysql_error();
						$update = false;
					}
					$throw[$item['id']] = true;
					$count++;
				}
			}
		}
		if ($count >= 50) {
			break;
		}
	}

	if ($update == true) {
		$b = mysql_exec("delete from tagreader_data where eid = %s", mysql_num($eid));
		$t = mysql_full("select * from tagreader_data_tmp where eid = %s", mysql_num($eid));
		if ($t) {
			while ($d = mysql_fetch_array($t)) {
				$i = mysql_exec("insert into tagreader_data (eid, article_id, blk_id, title, url, initymd)".
								" values (%s, %s, %s, %s, %s, %s)",
								mysql_num($d["eid"]), mysql_num($d['article_id']), mysql_num($d['blk_id']), mysql_str($d["title"]),
								mysql_str($d["url"]), mysql_str($d["initymd"]));
			}
			$a = mysql_exec("delete from tagreader_data_tmp where eid = %s", mysql_num($eid));
		}
		mod_tagreader_update_crawl_time($eid);
	}
	return;
}

function mod_tagreader_update_crawl_time($eid) {
	$rct_d = mysql_exec("delete from tagreader_crawl_time where eid = %s",
						mysql_num($eid));
	$rct = mysql_exec("insert into tagreader_crawl_time (eid) values (%s)",
					  mysql_num($eid));
}

?>
