  <div id="space_wrapper">
    <div id="space_1">
{section name=n loop=$space_1}
<div class="box" id="box_{$space_1[n].id}">
  <div class="box_menu"><span>{$space_1[n].title}</span></div>
{if {$space_1[n].editlink}
  <div class="box_edit">{$space_1[n].editlink}</div>
{/if}
  <div class="box_main">
    {$space_1[n].content}
    <div style="clear: both; height: 1px;"></div>
  </div>
</div>
{/section}
    </div><!-- /#space_1 -->

    <div id="space_2">
{section name=n loop=$space_2}
<div class="box" id="box_{$space_2[n].id}">
  <div class="box_menu"><span>{$space_2[n].title}</span></div>
{if {$space_2[n].editlink}
  <div class="box_edit">{$space_2[n].editlink}</div>
{/if}
  <div class="box_main">
    {$space_2[n].content}
    <div style="clear: both; height: 1px;"></div>
  </div>
</div>
{/section}
    </div><!-- /#space_2 -->
</div><!-- /#space_wrapper -->
