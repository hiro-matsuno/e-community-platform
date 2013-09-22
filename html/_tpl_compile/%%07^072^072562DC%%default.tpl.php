<?php /* Smarty version 2.6.19, created on 2013-09-21 18:38:21
         compiled from skin/default/default.tpl */ ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "skin/_wrap_header.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<?php echo $this->_tpl_vars['menubar']; ?>


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
<?php unset($this->_sections['n']);
$this->_sections['n']['name'] = 'n';
$this->_sections['n']['loop'] = is_array($_loop=$this->_tpl_vars['topic_path']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
<?php if ($this->_sections['n']['index'] > 0): ?> > <?php endif; ?><a <?php if ($this->_tpl_vars['topic_path'][$this->_sections['n']['index']]['url']): ?>href="<?php echo $this->_tpl_vars['topic_path'][$this->_sections['n']['index']]['url']; ?>
"<?php endif; ?>><?php echo $this->_tpl_vars['topic_path'][$this->_sections['n']['index']]['title']; ?>
</a>
<?php endfor; endif; ?>
  </div>
</div><!-- /#nav -->

<div id="container">
<?php echo $this->_tpl_vars['contents']; ?>

　<div id="container_foot"></div>
</div><!-- /#container -->

<div id="footer_push"></div>
</div><!-- /#wrapper -->

<div id="footer">
  <div class="footer_content">Copyright &copy; <?php echo $_SERVER['SERVER_NAME']; ?>
 All Rights Reserved.</div>
</div><!-- /#footer -->

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "skin/_wrap_footer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>