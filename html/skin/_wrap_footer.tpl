<!-- end of contents -->
{if $foot_html}
{section name=n loop=$foot_html}
{$foot_html[n]}
{/section}
{/if}
{if $foot_js}
{section name=n loop=$foot_js}
<script type="text/javascript" src="{$foot_js[n]}"></script>
{/section}
{/if}
{if $foot_jsraw}
<script type="text/javascript">
//<![CDATA[
{section name=n loop=$foot_jsraw}
{$foot_jsraw[n]}
{/section}
// ]]>
</script>
{/if}
{if $debug}
<div class="debug">
{section name=n loop=$debug}
<div class="debug_line">{$debug[n]}</div>
{/section}
</div>
{/if}
<div id="debug" style="background: #fff; color: #333; font-size: 10px;"></div>
</body>
</html>
