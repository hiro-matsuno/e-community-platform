{include file="skin/_wrap_header.tpl"}

{$menubar}

<div id="header">
	<h1><a href="{$page_url}">{$page_title}</a></h1>
	<h2>{$description}</h2>
</div><!-- /#header -->

<div id="wrapper">
<div id="container" class="clearfix">

<div id="nav">
	<div class="nav_tp">
{section name=n loop=$topic_path}
{if $smarty.section.n.index > 0} > {/if}<a {if $topic_path[n].url}href="{$topic_path[n].url}"{/if}>{$topic_path[n].title}</a>
{/section}
	</div>
</div><!-- /#nav -->

{$contents}
</div><!-- /#container -->

</div><!-- /#wrapper -->

<div id="footer">
  <div class="footer_content">Copyright {$smarty.server.SERVER_NAME} All Rights Reserved.</div> 
</div><!-- /#footer -->

{include file="skin/_wrap_footer.tpl"}
