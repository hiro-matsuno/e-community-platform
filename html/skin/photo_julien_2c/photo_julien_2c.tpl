<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja-JP" lang="ja-JP">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
{section name=n loop=$head_meta}
<link rel="stylesheet" href="{$head_meta[n]}" type="text/css">
{/section}
<title>{$site_name}</title>
{section name=n loop=$head_css}
<link rel="stylesheet" href="{$head_css[n]}" type="text/css">
{/section}
{section name=n loop=$head_js}
<script type="text/javascript" src="{$head_js[n]}"></script>
{/section}

{if $jquery_ready_script}
{literal}
<script type="text/javascript">
    //<![CDATA[
	
	jQuery(document).ready(function() {
{/literal}
{section name=n loop=$jquery_ready_script}
{$jquery_ready_script[n]}

{/section}
{literal}
	});
//]]>
</script>
{/literal}
{/if}

{if $head_jsraw}
<script type="text/javascript">
//<![CDATA[
{section name=n loop=$head_jsraw}
{$head_jsraw[n]}

{/section}
//]]>
</script>
{/if}

{if $head_cssraw}
<style type="text/css">
{section name=n loop=$head_cssraw}
{$head_cssraw[n]}

{/section}
</style>
{/if}

</head>

{if $inbodytag}
<body {$inbodytag}>
{else}
<body>
{/if}

{$menubar}

{if $setting_layout}
<div id="post_status"></div>
<div id="nav_admin">
  <form id="layout_setting" action="/layout.php" style="margin: 0;">
  <input type="hidden" name="save" value="1">
  <input type="hidden" name="eid" value="{$eid}">
  <a href="/layout.php?nosave=1&eid={$eid}" title="">保存しないで終了</a>
  <a href="/layout.php?save=1&eid={$eid}" id="layout_save" title="" onClick="return false;">保存して終了</a>
  <a href="/add_block.php?eid={$eid}&keepThis=true&TB_iframe=true&height=480&width=640" title="" class="thickbox">ブロックを追加</a>
  </form>
</div><!-- /#nav_admin -->
{/if}

<div id="wrapper">

<div id="header">
	<h1><a href="{$page_url}">{$page_title}</a></h1>
	<h2>{$description}</h2>
</div><!-- /#header -->

<div id="nav">
	<div class="nav_tp">
{section name=n loop=$topic_path}
{if $smarty.section.n.index > 0} > {/if}<a {if $topic_path[n].url}href="{$topic_path[n].url}"{/if}>{$topic_path[n].title}</a>
{/section}
	</div>
</div><!-- /#nav -->

<div id="container">
{$contents}
  <div id="footer_push"></div>
　<div id="container_foot"></div>
</div><!-- /#container -->
</div><!-- /#wrapper -->

<div id="footer">
  <div class="footer_content">Copyright &copy; {$smarty.server.SERVER_NAME} All Rights Reserved.</div>
</div><!-- /#footer -->

{section name=n loop=$after_js}
<script type="text/javascript" src="{$head_js[n]}">
{/section}

<div id="debug">
{section name=n loop=$debug}
{$debug[n]}<br>
{/section}
</div>

{section name=n loop=$foot_js}
<script type="text/javascript" src="{$foot_js[n]}"></script>
{/section}

{if $foot_jsraw}
<script type="text/javascript">
//<![CDATA[
	{$foot_jsraw}
// ]]>
</script>
{/if}

</body>
</html>
