/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
	function loadWMSLayer(base_id, base_url, bbox_format, use_geo, cp_name, cp_name_short, cp_text, min_scale, max_scale, opacity, isPng) {
        cpc = new GCopyrightCollection(cp_name);
        cpc.addCopyright(new GCopyright(base_id, new GLatLngBounds(new GLatLng(-90,-180), new GLatLng(90,180)), 0, cp_text));

		var baseMap = new GTileLayer(cpc, min_scale, max_scale);
		addWMSPropertiesToLayer(baseMap, base_url, '', '', '', bbox_format, '', use_geo);

		baseMap.getTileUrl = getTileUrlForWMS;

		var baseMapType = new GMapType([baseMap], new GMercatorProjection(max_scale + 1), cp_name, { shortName: cp_name_short });

		return baseMapType;
	}

	function loadTileLayer(base_id, base_url, bbox_format, use_geo, cp_name, cp_name_short, cp_text, min_scale, max_scale, opacity, isPng) {
        cpc = new GCopyrightCollection(cp_name);
        cpc.addCopyright(new GCopyright(base_id, new GLatLngBounds(new GLatLng(-90,-180), new GLatLng(90,180)), 0, cp_text));

		isPng = (isPng == 1) ? true : false;

		var baseMap = new GTileLayer(cpc, min_scale, max_scale, {
			tileUrlTemplate: base_url,
			isPng: isPng,
			opacity: opacity });

		var baseMapType = new GMapType([baseMap], new GMercatorProjection(max_scale + 1), cp_name, { shortName: cp_name_short });

		return baseMapType;
	}

	function loadWMSLayerOverlay(base_id, base_url, bbox_format, use_geo, cp_name, cp_name_short, cp_text, min_scale, max_scale, opacity, isPng) {
        cpc = new GCopyrightCollection(cp_name);
        cpc.addCopyright(new GCopyright(base_id, new GLatLngBounds(new GLatLng(-90,-180), new GLatLng(90,180)), 0, cp_text));

		var baseMap = new GTileLayer(cpc, min_scale, max_scale);
		addWMSPropertiesToLayer(baseMap, base_url, '', '', '', bbox_format, '', use_geo);

		baseMap.getTileUrl = getTileUrlForWMS;

		var baseMapOverlay = new GTileLayerOverlay(baseMap);

		return baseMapOverlay;
	}

	function loadTileLayerOverlay(base_id, base_url, bbox_format, use_geo, cp_name, cp_name_short, cp_text, min_scale, max_scale, opacity, isPng) {
        cpc = new GCopyrightCollection(cp_name);
        cpc.addCopyright(new GCopyright(base_id, new GLatLngBounds(new GLatLng(-90,-180), new GLatLng(90,180)), 0, cp_text));

		isPng = (isPng == 1) ? true : false;

		var baseMap = new GTileLayer(cpc, min_scale, max_scale, {
			tileUrlTemplate: base_url,
			isPng: isPng,
			opacity: opacity });

		var baseMapOverlay = new GTileLayerOverlay(baseMap);

		return baseMapOverlay;
	}

	function showLayerOverlay(mObj, overlay, show)
	{
		if (show) {
			mObj.removeOverlay(overlay);
			mObj.addOverlay(overlay);
		}
		else {
			mObj.removeOverlay(overlay);
		}
	}
	function delOverlays(mObj) {
		mObj.clearOverlays();
	}
	function drawCrossScope(mObj, reticule){
		if (reticule) {
			mObj.removeOverlay(reticule);
		}
		reticule = new GScreenOverlay('/image/cross.png', 
						new GScreenPoint(0.5, 0.5, 'fraction', 'fraction'),
						new GScreenPoint(17, 17),
						new GScreenSize(35, 35 )
					);
		mObj.addOverlay(reticule);
	}
	function drawCrossScopeBind(mObj) {
		jQuery(window).bind('resize', function() {
			drawCrossScope(mObj);
		});
	}
