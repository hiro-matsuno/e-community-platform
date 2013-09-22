{include file="skin/_wrap_header.tpl"}

{$menubar}

<div id="wrapper">

<div id="header">
  <a href="/"><img src="/skin/default/image/title.png" alt="地域防災キット" style="float: left; border: none;"></a>
  <img src="/skin/default/image/ecom_logo.png" alt="e-community platform" style="float: right; margin-right: 30px;">
  <br style="clear: both;">
</div><!-- /#header .-->

<div id="menu">
  <div class="search_field">
    <form action="/search.php" method="GET">
      <input type="text" id="input_text" name="q" value="キーワード検索" onFocus="this.value='';"><input type="image" src="/skin/default/image/search.png" id="submit_button" value="検索">
    </form>
  </div><!-- /#search_field -->
  <ul class="sf-menu">
    <li><a href="/">ポータル</a></li>
    <li><a href="/index.php?module=list_g">グループページ一覧</a></li>
    <li><a href="/index.php?module=list_u">マイページ一覧</a></li>
  </ul>
</div><!-- /#menu -->

<div id="nav">
  <div class="nav_tp">
{section name=n loop=$topic_path}
{if $smarty.section.n.index > 0} > {/if}<a {if $topic_path[n].url}href="{$topic_path[n].url}"{/if}>{$topic_path[n].title}</a>
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
