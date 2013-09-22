<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

require_once dirname(__FILE__). '/../../lib.php';
require_once dirname(__FILE__). '/config.php';
include_once dirname(__FILE__). '/func.php';
include_once dirname(__FILE__). '/php-ofc-library/open-flash-chart.php';

// generate some random data
srand((double)microtime()*1000000);

$max = 20;
$tmp = array();
for ($i = 0; $i < 9; $i++) {
	$tmp[] = rand(0, $max);
}

global $COMUNI_DEBUG, $JQUERY, $COMUNI_TPATH;

$id  = isset($_REQUEST['eid']) ? intval($_REQUEST['eid']) : 0;
$num = isset($_REQUEST['num']) ? $_REQUEST['num'] : 0;

$path_info = $_SERVER[(isset($_SERVER['ORIG_PATH_INFO']) ? 'ORIG_PATH_INFO' : 'PATH_INFO')];

list($id, $num) = explode('/', preg_replace('/^\//', '', $path_info));

$q = mysql_uniq('select * from enquete_form_data'.
				' where eid = %s and uniq_id = %s',
				mysql_num($id), mysql_num($num));

if ($q) {
	$title    = $q['title'];
	$type     = $q['type'];
	$opt_list = $q['opt_list'];
}

$title = new title($title);

$chart = new open_flash_chart();
$chart->set_title( $title );

if ($type == 'radio' || $type == 'select') {
	$list = explode('-_-', $opt_list);

	$f = mysql_full('select * from enquete_vote_data'.
					' where eid = %s and num = %s',
					mysql_num($id), mysql_num($num));

	$cnt = array();
	if ($f) {
		while ($res = mysql_fetch_assoc($f)) {
			if (isset($res['data']) && $res['data'] > 0) {
				$dec = $res['data'] - 1;
				$cnt[$dec]++;
			}
		}
	}

	$pie = new pie();
	$pie->set_alpha(0.6);
	$pie->set_start_angle( 35 );
	$pie->add_animation( new pie_fade() );
//	$pie->set_tooltip( '#val# of #total#<br>#percent# of 100%' );
	$pie->set_tooltip('#percent#');
	$pie->set_colours(array('#ff6666','#ff96e0','#afd9f7','#ffdf87'));

	$pie_val = array();
	for ($i = 0; $i < count($list); $i++) {
		if (!$cnt[$i]) continue;
		$pie_val[] = new pie_value($cnt[$i], $list[$i]. '('. $cnt[$i]. ')');
//		$pie_val[] = $cnt[$i];
	}
	$pie->set_values($pie_val);

	$chart->add_element($pie);

	$chart->x_axis = null;
}
else if ($type == 'checkbox') {
	$list = explode('-_-', $opt_list);

	$f = mysql_full('select * from enquete_vote_data'.
					' where eid = %s and num = %s',
					mysql_num($id), mysql_num($num));

	$cnt = array();
	if ($f) {
		while ($res = mysql_fetch_assoc($f)) {
			$v_opt = explode('-_-', $res['data']);
			foreach ($v_opt as $vo) {
				$cnt[$vo]++;
			}
		}
	}
//			var_dump($list);
//			var_dump($cnt);

	$pie = new bar_filled( '#afd9f7', '#728eb2' );

	$pie_val = array();

	$max = 10;
	for ($i = 0; $i < count($list); $i++) {
		if (!$cnt[$list[$i]]) continue;
		if ($max < $cnt[$list[$i]]) {
			$max = $cnt[$list[$i]];
		}
//		$pie_val[] = new bar_value($cnt[$i], $list[$i]);
		$pie_val[] = $cnt[$list[$i]];
	}
	$pie->set_values($pie_val);

	if ($max > 10) {
		$max = $max + 2;
	}

	$y = new y_axis(); 
	$y->set_range(0, $max, intval($max / 10)); 

	$x = new x_axis();
	$x->set_labels_from_array($list); 
	$chart->add_element($pie);
	$chart->set_x_axis( $x );
	$chart->set_y_axis( $y );

	$chart->set_bg_colour( '#FFFFFF' );
}


echo $chart->toPrettyString();

exit(0);

/*
$v = mysql_full('select * from enquete_vote_data where eid = %s'.
				' order by updymd desc',
				mysql_num($id));

$ans = array();
if ($v) {
	while ($r = mysql_fetch_array($v)) {
		$ans[$r['num']][] = $r;
	}
}
else {
	return mod_enquete_none();
}

$show_hidden_data = false;
if (is_owner($id)) {
	$show_hidden_data = true;
}

foreach ($que as $q) {
	if (($q['admin_only'] == 1) && ($show_hidden_data == false)) {
		$html .= '<h4 style="font-size: 1.2em; margin-top: 15px;">'. $q['title']. '</h4>';
		$html .= '(この項目は結果を非公開としています。)';
		continue;
	}

	$sub_total = 0;
	$html .= '<h4 style="font-size: 1.2em; margin-top: 15px;">'. $q['title']. '</h4>';

	$data = array();
	switch ($q['type']) {
		case 'radio':
		case 'select':
			$opt = explode('-_-', $q['opt_list']);
			foreach($opt as $o) {
				$data[$o] = 0;
			}
			$i = 0;
			foreach($opt as $o) {
				$i++;
				$s[$i] = $o;
			}
		case 'checkbox':
			$opt = explode('-_-', $q['opt_list']);
			foreach($opt as $o) {
				$data[$o] = 0;
			}
			break;
		default:
			$opt = array();
	}
	$sub_total = 0; $count = array();
	foreach ($ans[$q['uniq_id']] as $a) {
		switch ($q['type']) {
			case 'radio':
			case 'select':
				$count[$s[$a['data']]]++;
				break;
			case 'checkbox':
				$v_opt = explode('-_-', $a['data']);
				foreach ($v_opt as $vo) {
					$count[$vo]++;
				}
				break;
			default:
				$article[$a['num']][] = $a['data'];
		}
		$sub_total++;
	}
	if ($count) {
		foreach ($count as $d => $c) {
			$w = sprintf("%.1f", $c / $sub_total * 100);
			$style = 'background-color: #a4cddf; height: 10px; width: '. $w. '%;';
			$html .= $d. ' ('. $c. '票、'. $w. '%)<div style="'. $style. '"></div>';
		}
	}
	else {
		$num = 0;
		foreach ($article[$q['uniq_id']] as $arti) {
			if ($arti != '') {
				$html .= nl2br($arti). '<hr size="1">';
			}
			if ($num > 8) {
				break;
			}
			$num++;
		}
	}
}
*/

echo <<<__JSON__
{
  "title":{
    "text":  "Many data lines",
    "style": "{font-size: 20px; color:#0000ff; font-family: Verdana; text-align: center;}"
  },
 
  "y_legend":{
    "text": "Open Flash Chart",
    "style": "{color: #736AFF; font-size: 12px;}"
  },
 
  "elements":[
    {
      "type":      "bar",
      "alpha":     0.5,
      "colour":    "#9933CC",
      "text":      "Page views",
      "font-size": 10,
      "values" :   [9,6,7,9,5,7,6,9,7]
    },
    {
      "type":      "bar",
      "alpha":     0.5,
      "colour":    "#CC9933",
      "text":      "Page views 2",
      "font-size": 10,
      "values" :   [6,7,9,5,7,6,9,7,3]
    }
  ],
 
  "x_axis":{
    "stroke":1,
    "tick_height":10,
    "colour":"#d000d0",
    "grid_colour":"#00ff00",
    "labels": {
        "labels": ["January","February","March","April","May","June","July","August","Spetember"]
    }
   },
 
  "y_axis":{
    "stroke":      4,
    "tick_length": 3,
    "colour":      "#d000d0",
    "grid_colour": "#00ff00",
    "offset":      0,
    "max":         20
  }
}

__JSON__;

?>
