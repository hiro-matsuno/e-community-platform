    <div id="space_1">
{section name=n loop=$space_1}
<div class="box" id="box_{$space_1[n].id}">
  <div class="box_menu"><span>{$space_1[n].title}</span></div>
  <div class="box_edit">{$space_1[n].editlink}</div>
  <div class="box_main">
    {$space_1[n].content}
  </div>
</div>
{/section}
    </div><!-- /#space_1 -->

