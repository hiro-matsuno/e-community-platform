{include file="skin/_wrap_header.tpl"}

{$menubar}

<div id="wrapper">

<div id="header">
  <a href="/"><img src="/skin/default/image/ecom_logo.png" alt="e-community platform" style="float: left; border: none;"></a>
  <br style="clear: both;">
</div><!-- /#header -->

<div id="menu">
<div class="menu_title">{$site_name}編集画面</div>
</div><!-- /#menu -->

<div id="nav">
  <div class="nav_tp">
{section name=n loop=$topic_path}
{if $smarty.section.n.index > 0} &gt; {/if}<a {if $topic_path[n].url}href="{$topic_path[n].url}"{/if}>{$topic_path[n].title}</a>
{/section}
  </div>
</div><!-- /#nav -->

<div id="container">
{$contents}
<div id="container_foot"></div>
</div><!-- /#container -->

<div id="footer_push"></div>
</div><!-- /#wrapper -->

<div id="footer">
  <div class="footer_content">Copyright &copy; {$smarty.server.SERVER_NAME} All Rights Reserved.</div>
</div><!-- /#footer -->

{include file="skin/_wrap_footer.tpl"}
