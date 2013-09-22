<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja-JP" lang="ja-JP">
<head>
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
{section name=n loop=$head_meta}
<link rel="stylesheet" href="{$head_meta[n]}" type="text/css" />
{/section}
<title>{$site_name}</title>
{section name=n loop=$head_css}
<link rel="stylesheet" href="{$head_css[n]}" type="text/css" />
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
