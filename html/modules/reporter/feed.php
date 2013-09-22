<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require_once dirname(__FILE__). '/../../lib.php';
include_once dirname(__FILE__). '/../../lib/feedcreator.class.php';

$id = intval($_REQUEST['id']);

if(!check_pmt_blk($id))exit(0);

$q = mysql_full("select o.uid, rb.eid, rb.site_id, d.*, ra.display".
				" from blog_data as d".
				" inner join element on d.id = element.id".
				" left join unit on element.unit = unit.id".
				" inner join reporter_block as rb".
				" on rb.block_id = d.pid".
				" inner join reporter_auth as ra".
				" on d.id = ra.id".
				" inner join owner as o".
				" on d.id = o.id".
				" where (element.unit <= %s or unit.uid = %s)".
				" and rb.eid = %s".
				" and ra.display = 2".
				" order by d.updymd",
				mysql_num(public_status($id)), mysql_num(myuid()),
				mysql_num($id));

header('Content-Type: application/xml');

$rss = new UniversalFeedCreator();
//$rss->useCached();
$rss->encoding = 'utf-8';
$rss->title = get_block_name($id);
$rss->description = '';
$rss->link = CONF_SITEURL;
$rss->syndicationURL = '';

if ($q) {
	while ($d = mysql_fetch_array($q)) {
//		$sitename = get_writer_name($d['uid'], $d['site_id']);
		$href     = CONF_URLBASE. "/index.php?module=blog&blk_id=$d[pid]&eid=$d[id]";
		$subject  = htmlspecialchars($d["subject"], ENT_QUOTES);
		$body     = $d["body"];
		$desc     = clip_str(strip_tags($body), 200);
		$date     = strtotime($d["initymd"]);

		$item = new FeedItem();
		$item->title = $subject;
		$item->link  = $href;
		$item->description = $desc;
		$item->date = $date;
//		$item->source = CONF_SITEURL;
//		$item->author = CONF_SITENAME;

		$rss->addItem($item);
	}
}

echo $rss->createFeed("RSS2.0");

?>
