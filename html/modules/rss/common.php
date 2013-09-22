<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

function mod_rss_crawl($eid) {
	define('MAGPIE_CACHE_AGE', 1);
	define('MAGPIE_CACHE_DIR', dirname(__FILE__). '/../../_rss_cache');
	define('MAGPIE_OUTPUT_ENCODING', 'UTF-8');

	require_once dirname(__FILE__). '/../../lib/rss/rss_fetch.inc';

//	write_syslog('mod_rss_crawl '. $eid);

	$q = mysql_full("select ru.*, rs.disp_num, rs.keyword from rss_url as ru".
					" left join rss_setting as rs on ru.eid = rs.eid".
					" where ru.eid = %s".
					" order by ru.num",
					mysql_num($eid));

	if (!$q) {
		return;
	}

	$t = mysql_exec("delete from rss_data_tmp where eid = %s", mysql_num($eid));

	$update = true; $count = array();
	while ($d = mysql_fetch_array($q)) {
		$count[$eid]    = 0;
		$disp_num[$eid] = $d["disp_num"];
		$keyword[$eid]  = $d["keyword"];
		$rss = @fetch_rss($d["url"]);
		write_syslog('get '. $d["url"]);
		if (!$rss) {
//			$update = false;
			write_syslog('missing fetch '. $d['url']);
			continue;
		}

		$sitename = $rss->channel['title'];
		foreach ($rss->items as $item) {
			$title = $item["title"];
			$url   = $item["link"];
			$body  = $item["description"];
			$d4s   = $item["title"]. "\t".
					 $item["summary"]. "\t".
					 $item["category"]. "\t".
					 $item["description"]. "\t".
					 $item["dc"]["subject"]. "\t".
					 $item['content']['encoded'];
			if ($item["dc"]["date"]) {
				$date = date("Y-m-d H:i:s", strtotime(str_replace("T", " ", substr($item["dc"]["date"], 0, 19))));
			}
			else if ($item["pubdate"]) {
				$date = date("Y-m-d H:i:s", strtotime($item["pubdate"]));
			}
			else {
				$date = date("Y-m-d H:i:s");
			}
			$key_array = array();
			if ($keyword[$eid]) {
				$key_array = preg_split("/ +/", trim(mb_ereg_replace('[　 ]{2,}', ' ', $keyword[$eid])));
				$find = 0;
				foreach ($key_array as $key) {
					if (strpos($d4s, $key) !== false) {
						$find++;
					}
				}

				if ($find == count($key_array)) {
					$i = mysql_exec("insert into rss_data_tmp (eid, sitename, title, url, body, initymd)".
									" values (%s, %s, %s, %s, %s, %s)",
									mysql_num($eid), mysql_str($sitename), mysql_str($title), 
									mysql_str($url), mysql_str($body), mysql_str($date));
					if (!$i) {
//						write_syslog('oooops!!'. mysql_error());
						$update = false;
					}
				}
			}
			else {
				$i = mysql_exec("insert into rss_data_tmp (eid, sitename, title, url, body, initymd)".
								" values (%s, %s, %s, %s, %s, %s)",
								mysql_num($eid), mysql_str($sitename), mysql_str($title), 
								mysql_str($url), mysql_str($body), mysql_str($date));
				if (!$i) {
//					write_syslog('oops!!'. mysql_error());
					$update = false;
				}
			}

			$count[$eid]++;

			if ($count[$eid] >= $disp_num[$eid]) {
//				write_syslog($eid. ' breaking '. $count[$eid]. '>='. $disp_num[$eid]);
//				break;
			}
		}
	}

	if ($update == true) {
		$b = mysql_exec("delete from rss_data where eid = %s", mysql_num($eid));
		$t = mysql_full("select * from rss_data_tmp where eid = %s order by id", mysql_num($eid));
		if ($t) {
			while ($d = mysql_fetch_array($t)) {
				$i = mysql_exec("insert into rss_data (eid, sitename, title, url, body, initymd)".
								" values (%s, %s, %s, %s, %s, %s)",
								mysql_num($d["eid"]), mysql_str($d["sitename"]), mysql_str($d["title"]),
								mysql_str($d["url"]), mysql_str($d["body"]), mysql_str($d["initymd"]));
//				write_syslog('updateeeeeee'. $d["url"]);
			}
//			$a = mysql_exec("delete from rss_data_tmp where eid = %s", mysql_num($eid));
		}
		mod_rss_update_crawl_time($eid);
	}
	return;
}

function mod_rss_update_crawl_time($eid) {
	$rct_d = mysql_exec("delete from rss_crawl_time where eid = %s",
						mysql_num($eid));
	$rct = mysql_exec("insert into rss_crawl_time (eid) values (%s)",
					  mysql_num($eid));
}

?>
