{include file="skin/_wrap_header.tpl"}

{$menubar}

<div id="wrapper">

<div id="header">

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
</div><!-- /#container -->
<div class="clearfix"></div>
</div><!-- /#wrapper -->

<div id="footer">
	<div class="footer_content">Copyright &copy; {$smarty.server.SERVER_NAME} All Rights Reserved.</div>
</div><!-- /#footer -->

{include file="skin/_wrap_footer.tpl"}
