<?php
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require_once dirname(__FILE__). '/../../lib.php';

list($eid, $pid) = get_edit_ids();
$gid = get_gid($pid);
$date = $_REQUEST['date'] ? $_REQUEST['date'] : date('Y-m-d');

//グループメンバー
$p = mysql_full('select uid,handle from group_member'.
				' inner join user on group_member.uid = user.id where gid = %s',
				mysql_num($gid));
$members = array();
while($m = mysql_fetch_assoc($p)){
	$members[$m['uid']] = $m['handle']; 
}

//連携するカレンダー一覧を取得
$p = mysql_full('select * from rel_cal_blk_rel where gid = %s',$gid);
$rel_blk = array();
while($b = mysql_fetch_assoc($p)){
	$rel_blk[$b['blk_id']] = $b['uid'];
}

//人毎に当日の予定を取得
$fb_time = array_fill(0,24,array_fill(0,4,0));//全体の空き時間 0が空き
$uid_fb = array();//個人の空き時間
foreach($rel_blk as $blk_id => $uid){
  if(!isset($uid_fb[$uid])) $uid_fb[$uid] = array_fill(0,24,array_fill(0,4,0));
  $p = mysql_full('select * from schedule_data'.
  				' where pid = %s and date(startymd) <= %s and date(endymd) >= %s',
  				mysql_num($blk_id),mysql_str($date),mysql_str($date));
  while($s = mysql_fetch_assoc($p)){
  	$stime = (strtotime($s['startymd'])-strtotime($date))/900;
  	if($stime<0)$stime = 0;
  	$etime = (strtotime($s['endymd'])-strtotime($date))/900;
  	if($etime>=24*4)$etime = 24*4-1;
  	for($i = $stime; $i < $etime; $i++){
  		$uid_fb[$uid][$i/4][$i%4] = 1;
  		$fb_time[$i/4][$i%4] = 1;
  	}
  }
}

//uid_fbと同じ順序でニックネームの配列を作成
$uid_name = array();
foreach($uid_fb as $uid => $fb){
	$uid_name[$uid] = $members[$uid];
}

?>

<html>
<script type="text/javascript" src="/jquery-1.2.6.js"></script>
<script>
var scrWidth;
var scrHeight;
jQuery(document).ready(function($){
  //---- get width/height of scroll bar ----
  scrWidth=$('#aaa').width()-$('#bbb').width();
  scrHeight=$('#aaa').height()-$('#bbb').height();
  //---- delete content of div which is to calc below vals ----
  $('#ccc').html('');
  //---- set header size ----
  $('#fbtime').width($('#fbmain').width() - scrWidth);
  $('#fbspace').width($('#fbnames').width());

  //---- sincronization scroll ----
  $('#fbmain').scroll(function(){
    $('#fbtime').scrollLeft($(this).scrollLeft());
    $('#fbnames').scrollTop($(this).scrollTop());
  });

  //---- drag scroll ----
  $('#fbmain').mousedown(dragscrollon);
  $('#fbmain').mouseup(dragscrolloff);
  $('#fbmain').mousemove(dragscroll);
  $('#fbmain').css({'cursor' : '-moz-grab'});
});

function dragscrolloff(event){
  $(this).data('mousedown',false);
}

function dragscrollon(e){
  if(e.pageX-$(this).position().left>$(this).width()-scrWidth ||
     e.pageY-$(this).position().top>$(this).height()-scrHeight){
      return false;
  }

  $(this).data('x',e.clientX)
         .data('scrollLeft', this.scrollLeft)
         .data('y',e.clientY)
         .data('scrollTop', this.scrollTop)
         .data('mousedown', true);
}

function dragscroll(event){
  if(!$(this).data('mousedown')){return false;}
  if(event.pageX-$(this).position().left<0 ||
     event.pageY-$(this).position().top<0 ||
     event.pageX-$(this).position().left>$(this).width()-scrWidth ||
     event.pageY-$(this).position().top>$(this).height()-scrHeight){
     $(this).data('mousedown', false);
    return false;
  }
  this.scrollLeft = $(this).data('scrollLeft')+$(this).data('x')-event.clientX;
  this.scrollTop = $(this).data('scrollTop')+$(this).data('y')-event.clientY;
}

</script>

<style>
#name_table{
  white-space: nowrap;
}
.fbnamescell{
  white-space: nowrap;
}
#fbspace{
  width:50px;
  float:left;
}
#fbtime{
  color:blue;
  width:400px;
  overflow:hidden;
}
#fbmain{
  color:red;
  width:400px;
  height:400px;
  overflow:scroll;
}
#fbnames{
  overflow-x:scroll;
  width:80px;
  height:400px;
  overflow-y:hidden;
  float:left;
}
#aaa{
  width:100px;
  height:100px;
overflow:scroll;
}
#bbb{
  width:100%;
  height:100%;
}
.fbcell{
  height:1.2em;
  width:3em;
}
.fb_free{
  width:1em;
  height:5px;
  background-color:#80ffff;
  border-style:solid;
  border-color:#404040;
  border-width:1px;
}
.fb_busy{
  width:1em;
  height:5px;
  background-color:#ff80ff;
  border-style:solid;
  border-color:#808080;
  border-width:1px;
}
.nospace{
  border-spacing:0;
  border-style:hidden;
  border-collapse: collapse;
  padding:0;
  margin:0;
}
.hour_fb{
  height:1em;
  width:3em;
}
.fbnamescell .fbmaincell{
  height:1.2em;
}
</style>



<div>
<div id='fbspace'>&nbsp;</div>
<div id='fbtime'>
<table class='nospace'><tr>
<?
for($i=0;$i<24;$i++)
  print sprintf('<td>%02d:00</td>',$i);
print "</tr>\n<tr>";
for($i=0;$i<24;$i++){
  $fb='<table class="nospace hour_fb"><tr>';
  for($j=0;$j<4;$j++){
    if($fb_time[$i][$j])$fb .= '<td class="fb_busy"></td>';
    else $fb .= '<td class="fb_free"></td>';
  }
  $fb.='</tr></table>';
?>
<td><div class='fbcell'><?= $fb ?></div></td>
<?
}
?>
</tr></table>
</div>
</div>

<div>
<div id='fbnames'>
<table class='nospace' id='name_table'>
<?
foreach($uid_name as $uid => $name){
?>
<tr><th><div class="fbnamescell"><?= $name ?></div></th></tr>
<?
}
?>
</table>
</div>
<div id='fbmain'>
<table class='nospace'>
<?
if(!$uid_fb){
	print '<tr><td>このグループに対してスケジュールを開示しているユーザーはいません。</td></tr>';
}
foreach($uid_fb as $uid => $fb){
  print '<tr>';
  for($i=0;$i<24;$i++){
    print '<td class="fbmaincell"><div class="fbcell"><table class="nospace hour_fb"><tr>';
    for($j=0;$j<4;$j++){
      if($fb[$i][$j])print '<td class="fb_busy"></td>';
      else print '<td class="fb_free"></td>';
    }
    print '</tr></table></div></td>';
  }
  print "</tr>\n";
}
?>
</table>

</div>
</div>
<div id='messg'>&nbsp;</div>
<div id='ccc'><div id='aaa'><div id='bbb'>&nbsp;</div></div></div>
</html>

