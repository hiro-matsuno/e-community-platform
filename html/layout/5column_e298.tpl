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
    <div style="clear: both; height: 0px;"></div>
  </div>
</div>
{/section}

<div id="subspace_wrap"><!-- boxMiddleWrap -->
<div id="space_4"><!-- space_4 -->
{section name=n loop=$space_4}
<div class="box" id="box_{$space_4[n].id}">
  <div class="box_menu"><span>{$space_4[n].title}</span></div>
{if {$space_1[n].editlink}
  <div class="box_edit">{$space_4[n].editlink}</div>
{/if}
  <div class="box_main">
    {$space_4[n].content}
    <div style="clear: both; height: 0px;"></div>
  </div>
</div>
{/section}
</div><!-- /space_4 -->

<div id="space_5"><!-- space_5 -->
{section name=n loop=$space_5}
<div class="box" id="box_{$space_5[n].id}">
  <div class="box_menu"><span>{$space_5[n].title}</span></div>
{if {$space_5[n].editlink}
  <div class="box_edit">{$space_5[n].editlink}</div>
{/if}
  <div class="box_main">
    {$space_5[n].content}
    <div style="clear: both; height: 0px;"></div>
  </div>
</div>
{/section}
</div><!-- /space_5 -->
</div><!-- /subspace_wrap -->
<div style="clear: both;"></div>

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
    <div style="clear: both; height: 0px;"></div>
  </div>
</div>
{/section}
    </div><!-- /#space_2 -->
　</div>

  <div id="space_3">
{section name=n loop=$space_3}
<div class="box" id="box_{$space_3[n].id}">
  <div class="box_menu"><span>{$space_3[n].title}</span></div>
{if {$space_3[n].editlink}
  <div class="box_edit">{$space_3[n].editlink}</div>
{/if}
  <div class="box_main">
    {$space_3[n].content}
    <div style="clear: both; height: 0px;"></div>
  </div>
</div>
{/section}
  </div><!-- /#space_3 -->
