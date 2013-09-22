<?php /* Smarty version 2.6.19, created on 2013-09-22 03:01:06
         compiled from layout/3column_nocss.tpl */ ?>
  <div id="space_wrapper">
    <div id="space_1">
<?php unset($this->_sections['n']);
$this->_sections['n']['name'] = 'n';
$this->_sections['n']['loop'] = is_array($_loop=$this->_tpl_vars['space_1']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['n']['show'] = true;
$this->_sections['n']['max'] = $this->_sections['n']['loop'];
$this->_sections['n']['step'] = 1;
$this->_sections['n']['start'] = $this->_sections['n']['step'] > 0 ? 0 : $this->_sections['n']['loop']-1;
if ($this->_sections['n']['show']) {
    $this->_sections['n']['total'] = $this->_sections['n']['loop'];
    if ($this->_sections['n']['total'] == 0)
        $this->_sections['n']['show'] = false;
} else
    $this->_sections['n']['total'] = 0;
if ($this->_sections['n']['show']):

            for ($this->_sections['n']['index'] = $this->_sections['n']['start'], $this->_sections['n']['iteration'] = 1;
                 $this->_sections['n']['iteration'] <= $this->_sections['n']['total'];
                 $this->_sections['n']['index'] += $this->_sections['n']['step'], $this->_sections['n']['iteration']++):
$this->_sections['n']['rownum'] = $this->_sections['n']['iteration'];
$this->_sections['n']['index_prev'] = $this->_sections['n']['index'] - $this->_sections['n']['step'];
$this->_sections['n']['index_next'] = $this->_sections['n']['index'] + $this->_sections['n']['step'];
$this->_sections['n']['first']      = ($this->_sections['n']['iteration'] == 1);
$this->_sections['n']['last']       = ($this->_sections['n']['iteration'] == $this->_sections['n']['total']);
?>
<div class="box" id="box_<?php echo $this->_tpl_vars['space_1'][$this->_sections['n']['index']]['id']; ?>
">
  <div class="box_menu"><span><?php echo $this->_tpl_vars['space_1'][$this->_sections['n']['index']]['title']; ?>
</span></div>
<?php if ("{".($this->_tpl_vars['space_1'][$this->_sections['n']['index']]).".editlink"): ?>
  <div class="box_edit"><?php echo $this->_tpl_vars['space_1'][$this->_sections['n']['index']]['editlink']; ?>
</div>
<?php endif; ?>
  <div class="box_main">
    <?php echo $this->_tpl_vars['space_1'][$this->_sections['n']['index']]['content']; ?>

    <div style="clear: both; height: 0px;"></div>
  </div>
</div>
<?php endfor; endif; ?>
    </div><!-- /#space_1 -->

    <div id="space_2">
<?php unset($this->_sections['n']);
$this->_sections['n']['name'] = 'n';
$this->_sections['n']['loop'] = is_array($_loop=$this->_tpl_vars['space_2']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['n']['show'] = true;
$this->_sections['n']['max'] = $this->_sections['n']['loop'];
$this->_sections['n']['step'] = 1;
$this->_sections['n']['start'] = $this->_sections['n']['step'] > 0 ? 0 : $this->_sections['n']['loop']-1;
if ($this->_sections['n']['show']) {
    $this->_sections['n']['total'] = $this->_sections['n']['loop'];
    if ($this->_sections['n']['total'] == 0)
        $this->_sections['n']['show'] = false;
} else
    $this->_sections['n']['total'] = 0;
if ($this->_sections['n']['show']):

            for ($this->_sections['n']['index'] = $this->_sections['n']['start'], $this->_sections['n']['iteration'] = 1;
                 $this->_sections['n']['iteration'] <= $this->_sections['n']['total'];
                 $this->_sections['n']['index'] += $this->_sections['n']['step'], $this->_sections['n']['iteration']++):
$this->_sections['n']['rownum'] = $this->_sections['n']['iteration'];
$this->_sections['n']['index_prev'] = $this->_sections['n']['index'] - $this->_sections['n']['step'];
$this->_sections['n']['index_next'] = $this->_sections['n']['index'] + $this->_sections['n']['step'];
$this->_sections['n']['first']      = ($this->_sections['n']['iteration'] == 1);
$this->_sections['n']['last']       = ($this->_sections['n']['iteration'] == $this->_sections['n']['total']);
?>
<div class="box" id="box_<?php echo $this->_tpl_vars['space_2'][$this->_sections['n']['index']]['id']; ?>
">
  <div class="box_menu"><span><?php echo $this->_tpl_vars['space_2'][$this->_sections['n']['index']]['title']; ?>
</span></div>
<?php if ("{".($this->_tpl_vars['space_2'][$this->_sections['n']['index']]).".editlink"): ?>
  <div class="box_edit"><?php echo $this->_tpl_vars['space_2'][$this->_sections['n']['index']]['editlink']; ?>
</div>
<?php endif; ?>
  <div class="box_main">
    <?php echo $this->_tpl_vars['space_2'][$this->_sections['n']['index']]['content']; ?>

    <div style="clear: both; height: 0px;"></div>
  </div>
</div>
<?php endfor; endif; ?>
    </div>
　</div><!-- /#space_2 -->

  <div id="space_3">
<?php unset($this->_sections['n']);
$this->_sections['n']['name'] = 'n';
$this->_sections['n']['loop'] = is_array($_loop=$this->_tpl_vars['space_3']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['n']['show'] = true;
$this->_sections['n']['max'] = $this->_sections['n']['loop'];
$this->_sections['n']['step'] = 1;
$this->_sections['n']['start'] = $this->_sections['n']['step'] > 0 ? 0 : $this->_sections['n']['loop']-1;
if ($this->_sections['n']['show']) {
    $this->_sections['n']['total'] = $this->_sections['n']['loop'];
    if ($this->_sections['n']['total'] == 0)
        $this->_sections['n']['show'] = false;
} else
    $this->_sections['n']['total'] = 0;
if ($this->_sections['n']['show']):

            for ($this->_sections['n']['index'] = $this->_sections['n']['start'], $this->_sections['n']['iteration'] = 1;
                 $this->_sections['n']['iteration'] <= $this->_sections['n']['total'];
                 $this->_sections['n']['index'] += $this->_sections['n']['step'], $this->_sections['n']['iteration']++):
$this->_sections['n']['rownum'] = $this->_sections['n']['iteration'];
$this->_sections['n']['index_prev'] = $this->_sections['n']['index'] - $this->_sections['n']['step'];
$this->_sections['n']['index_next'] = $this->_sections['n']['index'] + $this->_sections['n']['step'];
$this->_sections['n']['first']      = ($this->_sections['n']['iteration'] == 1);
$this->_sections['n']['last']       = ($this->_sections['n']['iteration'] == $this->_sections['n']['total']);
?>
<div class="box" id="box_<?php echo $this->_tpl_vars['space_3'][$this->_sections['n']['index']]['id']; ?>
">
  <div class="box_menu"><span><?php echo $this->_tpl_vars['space_3'][$this->_sections['n']['index']]['title']; ?>
</span></div>
<?php if ("{".($this->_tpl_vars['space_3'][$this->_sections['n']['index']]).".editlink"): ?>
  <div class="box_edit"><?php echo $this->_tpl_vars['space_3'][$this->_sections['n']['index']]['editlink']; ?>
</div>
<?php endif; ?>
  <div class="box_main">
    <?php echo $this->_tpl_vars['space_3'][$this->_sections['n']['index']]['content']; ?>

    <div style="clear: both; height: 0px;"></div>
  </div>
</div>
<?php endfor; endif; ?>
  </div><!-- /#space_3 -->