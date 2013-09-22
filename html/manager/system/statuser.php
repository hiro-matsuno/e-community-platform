<?php 
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
?>
<?php
require dirname(__FILE__). '/../../lib.php';
require dirname(__FILE__). '/../../regist_lib.php';
include_once dirname(__FILE__). '/../../modules/enquete/php-ofc-library/open-flash-chart.php';

//質問が指定されていればswfへわたすデータを生成
if(isset($_REQUEST['req_id'])){
	$req = mysql_uniq('select * from prof_add_req where req_id = %s',
						mysql_num($_REQUEST['req_id']));
	if(!$req)exit(0);

	//選択肢を取得・選択肢ごとの選ばれた数を格納する配列を用意
	$options = explode("\n",$req['opt_list']);
	$opt_count = array();
	foreach($options as $o)$opt_count[$o] = 0;

	$datas = regist_data_read_all($req['req_id']);

	$title = new title($req['title']);

	$chart = new open_flash_chart();
	$chart->set_title( $title );
	
	if($req['type'] == 'checkbox'){
		//選択枝ごとの選ばれた数を集計
		foreach($datas as $d){
			$s = explode("\n",$d['data']);
			foreach($s as $ss)$opt_count[$ss]++;
		}

		$pie = new bar_filled( '#afd9f7', '#728eb2' );
	
		$pie->set_values(array_values($opt_count));
	
		$max = 10;
		foreach ($opt_count as $count) 
			if ($max < $count) 
				$max = $count;
		if ($max > 10) 
			$max = $max + 2;
	
		$y = new y_axis(); 
		$y->set_range(0, $max, intval($max / 10)); 
	
		$x = new x_axis();
		$x->set_labels_from_array($options); 
		$chart->add_element($pie);
		$chart->set_x_axis( $x );
		$chart->set_y_axis( $y );
	
		$chart->set_bg_colour( '#FFFFFF' );
	}else{
		//選択枝ごとの選ばれた数を集計
		foreach($datas as $d)
			$opt_count[$d['data']]++;

		$pie = new pie();
		$pie->set_alpha(0.6);
		$pie->set_start_angle( 35 );
		$pie->add_animation( new pie_fade() );
		$pie->set_tooltip('#percent#');
		$pie->set_colours(array('#ff6666','#ff96e0','#afd9f7','#ffdf87'));
	
		$pie_val = array();
		foreach ($opt_count as $opt => $count) {
			if($count == 0)continue;
			$pie_val[] = new pie_value($count, $opt. '('. $count. ')');
		}
		$pie->set_values($pie_val);
	
		$chart->add_element($pie);
	
		$chart->x_axis = null;
	}
	echo $chart->toPrettyString();
	exit(0);
}

admin_check();

$add_items = regist_data_get_reqs();

$content = "<h2>ユーザー登録情報集計</h2>\n";

foreach($add_items as $item){
	global $COMUNI_HEAD_JS,$COMUNI_HEAD_JSRAW;
	$title = htmlspecialchars($item['title']);
	$content .= "<hr>\n<h4>$title</h4>\n";
	switch($item['type']){
		case 'text':
		case 'textarea':

			$datas = regist_data_read_all($item['id']);

			foreach($datas as $d)$content .= htmlspecialchars($d['data']) . '/';
			break;
		default:
			$COMUNI_HEAD_JS[] = CONF_URLBASE. '/modules/enquete/js/swfobject.js';

			$COMUNI_FOOT_JSRAW[] = <<<__JS__
swfobject.embedSWF(
	"/modules/enquete/swf/open-flash-chart.swf", "my_chart_$item[id]", "96%", "300",
	"9.0.0", "expressInstall.swf",
	{"data-file":"/manager/system/statuser.php?req_id=$item[id]"},
	{"wmode":"transparent"}
);
__JS__;

			$content .= '<div id="my_chart_'. $item['id']. '"></div>';
			break;
	}
}

$data = array('title'   => 'ユーザー情報の統計',
			  'icon'    => 'notice',
			  'content' => $content);

show_input($data);


?>