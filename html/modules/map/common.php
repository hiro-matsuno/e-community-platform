<?php

/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */

function mod_map_layer_chkbox($id = null) {
	global $JQUERY;

	$q = mysql_full("select ml.* from map_layer_data as mld".
					" inner join map_layer as ml".
					" on mld.layer_id = ml.id".
					" where mld.id = %s order by mld.vpos",
					mysql_num($id));

	$map_obj = 'map_'. $id; $tag = array();
	if (!$q) {
		return;
	}
	else {
		while ($r = mysql_fetch_array($q)) {
			$layer_id = 'overlay_'. $r['id'];
			$cbox_id  = $layer_id. '_cbox';
			$tag[] = '<input type="checkbox" id="'. $cbox_id. '">'.
					 '<label for="'. $cbox_id. '">'. $r['cp_name']. '</label>';
			$JQUERY['ready'][] = <<<___READY_CODE__
    \$("#${cbox_id}").click(function() {
        if (this.checked) {
			showLayerOverlay(${map_obj}, ${layer_id}, true);
        } else {
			showLayerOverlay(${map_obj}, ${layer_id});
        }
    });
___READY_CODE__;
			;
		}
	}
	return '<div class="map_layer_cbox">地図レイヤー: '. join(' ', $tag). '</div>';
}

function mod_map_kml_chkbox($id = null) {
	global $JQUERY, $SYS_MAP_KML;

	$q = mysql_full("select b.* from map_kml_data as mkd".
					" inner join block as b".
					" on mkd.kml_id = b.id".
					" where mkd.id =%s order by mkd.vpos",
					mysql_num($id));

	$map_obj = 'map_'. $id; $tag = array();
	if (!$q) {
		return;
	}
	else {
		while ($r = mysql_fetch_array($q)) {
			$SYS_MAP_KML[] = $r['id'];
			$layer_id = 'kml_'. $r['id'];
			$cbox_id  = $layer_id. '_cbox';
			$name = $r['name'];
			$tag[] = '<input type="checkbox" id="'. $cbox_id. '" checked="checked">'.
					 '<label for="'. $cbox_id. '">'. $name. '</label>';
			$JQUERY['ready'][] = <<<___READY_CODE__
    \$("#${cbox_id}").click(function() {
        if (this.checked) {
			showLayerOverlay(${map_obj}, ${layer_id}, true);
        } else {
			showLayerOverlay(${map_obj}, ${layer_id});
        }
    });
___READY_CODE__;
			;
		}
	}
	return '<div class="map_layer_cbox">情報レイヤー: '. join(' ', $tag). '</div>';
}

?>
