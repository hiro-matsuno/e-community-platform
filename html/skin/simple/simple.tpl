{include file="skin/_wrap_header.tpl"}

<div id="wrapper">

{$menubar}

<div id="header">
  <h1 id="header-title"><a href="{$page_url}">{$page_title}</a></h1>
</div><!-- /#header -->

<div id="nav">
  <div class="nav_tp">
{section name=n loop=$topic_path}
{if $smarty.section.n.index > 0} > {/if}<a {if $topic_path[n].url}href="{$topic_path[n].url}"{/if}>{$topic_path[n].title}</a>
{/section}
  </div>
</div><!-- /#nav -->

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
