// ==UserScript==
// @name            url_breaker+
// @namespace       http://piro.sakura.ne.jp/
// @description     URL Breaker Modified Version
// @include         *
// ==/UserScript==

(function () {
try{
	if (navigator.appName != 'Netscape') {
		// from http://www.koikikukan.com/archives/2005/08/04-235647.php
		return;
	}
	var resolver = document.createNSResolver(document.documentElement);
	var nodes = document.evaluate(
			'/descendant::*[not(contains(" TITLE STYLE SCRIPT TEXTAREA XMP ", concat(" ", local-name(), " ")))]/child::text()',
			document.documentElement,
			resolver,
			XPathResult.ORDERED_NODE_SNAPSHOT_TYPE,
			null
		);
//	var regexp = new RegExp("([!-%'-/:=\\?@\\[-`\\{-~]|&amp;)");
	var regexp = new RegExp("([!-%'\\)-/:=\\?@\\\\-`\\|-~]|&amp;)");
	var range  = document.createRange();
	var wbr    = document.createElement('wbr');
	var lastIndex;
	var node;
	for (var i = 0; i < nodes.snapshotLength; i++)
	{
		node = nodes.snapshotItem(i);
		range.selectNode(node);
		while (node && (lastIndex = range.toString().search(regexp)) > -1)
		{
			range.setStart(node, lastIndex+RegExp.$1.length);
			range.insertNode(wbr.cloneNode(true));
			node = node.nextSibling.nextSibling;
			range.selectNode(node);
		}
	}
	range.detach();
}catch(e){}
})();

/*
	Original Script:
		url_breaker (made by asukaze)
		http://www.asukaze.net/soft/url_breaker/url_breaker.user.js
*/
