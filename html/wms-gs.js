/*
 * Call generic wms service for GoogleMaps v2
 * John Deck, UC Berkeley
 * Inspiration & Code from:
 *	Mike Williams http://www.econym.demon.co.uk/googlemaps2/ V2 Reference & custommap code
 *	Brian Flood http://www.spatialdatalogic.com/cs/blogs/brian_flood/archive/2005/07/11/39.aspx V1 WMS code
 *	Kyle Mulka http://blog.kylemulka.com/?p=287  V1 WMS code modifications
 *      http://search.cpan.org/src/RRWO/GPS-Lowrance-0.31/lib/Geo/Coordinates/MercatorMeters.pm
 *
 * Modified by Chris Holmes, TOPP to work by default with GeoServer.
 *
 * Bundled with YM4R with John Deck's permission.
 * Slightly modified to fit YM4R.
 * See johndeck.blogspot.com for the original version and for examples and instructions of how to use it.
 */

var WGS84_SEMI_MAJOR_AXIS = 6378137.0; //equatorial radius
var WGS84_ECCENTRICITY = 0.0818191913108718138;
var DEG2RAD=0.0174532922519943;
var PI=3.14159267;

function dd2MercMetersLng(p_lng) { 
	return WGS84_SEMI_MAJOR_AXIS * (p_lng*DEG2RAD); 
}

function dd2MercMetersLat(p_lat) {
        var lat_rad = p_lat * DEG2RAD;
	return WGS84_SEMI_MAJOR_AXIS * Math.log(Math.tan((lat_rad + PI / 2) / 2) * Math.pow( ((1 - WGS84_ECCENTRICITY * Math.sin(lat_rad)) / (1 + WGS84_ECCENTRICITY * Math.sin(lat_rad))), (WGS84_ECCENTRICITY/2)));
}

function addWMSPropertiesToLayer(tile_layer,base_url,layers,styles,format,view_format,merc_proj,use_geo){
   tile_layer.format = format;
   tile_layer.baseURL = base_url;
   tile_layer.styles = styles;
   tile_layer.layers = layers;
   tile_layer.mercatorEpsg = merc_proj;
   tile_layer.useGeographic = use_geo;
	tile_layer.view_format = view_format ? view_format : '[west],[south],[east],[north]';
   return tile_layer;
}

getTileUrlForWMS=function(a,b,c) {
	var lULP = new GPoint(a.x*256,(a.y+1)*256);
	var lLRP = new GPoint((a.x+1)*256,a.y*256);
	var lUL = G_NORMAL_MAP.getProjection().fromPixelToLatLng(lULP,b,c);
	var lLR = G_NORMAL_MAP.getProjection().fromPixelToLatLng(lLRP,b,c);
	 
	if (this.useGeographic){
           var lBbox = this.view_format;
			lBbox = lBbox.replace('[west]',  lUL.x);
			lBbox = lBbox.replace('[north]', lLR.y);
			lBbox = lBbox.replace('[east]',  lLR.x);
			lBbox = lBbox.replace('[south]', lUL.y);
    	   var lSRS="EPSG:4326";
	}else{
	   var lBbox = this.view_format;
			lBbox = lBbox.replace('[west]',  dd2MercMetersLng(lUL.x));
			lBbox = lBbox.replace('[north]', dd2MercMetersLat(lLR.y));
			lBbox = lBbox.replace('[east]',  dd2MercMetersLng(lLR.x));
			lBbox = lBbox.replace('[south]', dd2MercMetersLat(lUL.y));

    	   var lSRS="EPSG:" + this.mercatorEpsg;
        }

//	document.getElementById('debug').innerHTML = lBbox;

	var lURL=this.baseURL;

	if (!lURL.match(/\?/)) {
		lURL += '?';
	}
	else if (!lURL.match(/&$/) && !lURL.match(/\?$/)) {
		lURL += '&';
	}

	if (!lURL.match(/REQUEST=/i)) {
		lURL += "REQUEST=GetMap&";
	}
	if (!lURL.match(/SERVICE=/i)) {
		lURL += "SERVICE=WMS&";
	}
	if (!lURL.match(/VERSION=/i)) {
		lURL += "VERSION=1.1.1&";
	}
	if (!lURL.match(/STYLES=/i)) {
		lURL += "STYLES=" + this.styles + "&";
	}
	if (!lURL.match(/FORMAT=/i)) {
		if (this.format) {
			lURL += "FORMAT=image/" + this.format + "&";
		}
		else {
			lURL += "FORMAT=image/png&";
		}
	}
	if (!lURL.match(/LAYERS=/i)) {
		if (this.layers) {
			lURL += "LAYERS=" + this.layers + "&";
		}
	}
	if (!lURL.match(/BGCOLOR=/i)) {
		lURL += "BGCOLOR=0xFFFFFF&";
	}
	if (!lURL.match(/TRANSPARENT=/i)) {
		lURL += "TRANSPARENT=TRUE&";
	}
	if (!lURL.match(/SRS=/i)) {
		lURL += "SRS=" + lSRS + "&";
	}
	if (!lURL.match(/WIDTH=/i)) {
		lURL += "WIDTH=256&";
	}
	if (!lURL.match(/HEIGHT=/i)) {
		lURL += "HEIGHT=256&";
	}
	if (!lURL.match(/BBOX=/i)) {
		lURL += "BBOX=" + lBbox + "&";
	}
//	document.getElementById('debug').innerHTML += lURL + '<br>';
/*
	lURL+="?REQUEST=GetMap";
	lURL+="&SERVICE=WMS";
	lURL+="&VERSION=1.1.1";
	lURL+="&LAYERS="+this.layers;
	lURL+="&STYLES="+this.styles; 
	lURL+="&FORMAT=image/"+this.format;
	lURL+="&BGCOLOR=0xFFFFFF";
	lURL+="&TRANSPARENT=TRUE";
	lURL+="&SRS="+lSRS;

	lURL+="&BBOX="+lBbox;
	lURL+="&WIDTH=256";
	lURL+="&HEIGHT=256";
*/
//	lURL+="&reaspect=false";
	return lURL;
}
