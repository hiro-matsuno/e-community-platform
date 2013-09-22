<?php
/* Copyright (c) 2009 National Research Institute for Earth Science and
 * Disaster Prevention (NIED).
 * This code is licensed under the GPL 3.0 license, availible at the root
 * application directory.
 */
//PEAR JSON用ラッパ (5.2以降は不要)
if (!function_exists('json_encode')) {
	require_once dirname(__FILE__)."/../PEAR/JSON.php";
	$service_json = new Services_JSON();
	/** JSON encode (PEAR Wrapper).
	 * @param $value Object or Array */
	function json_encode($value) {
		global $service_json;
		return  $service_json->encode($value);
	}
	if (function_exists('json_decode')) return;
	/** JSON decode (PEAR Wrapper).
	 * @param $json JSON String
	 * @param $assoc Convert to Array */
	function json_decode($json, $assoc=false) {
		global $service_json;
		$decoded = $service_json->decode($json);
		if ($assoc && !is_array($decoded)) return object_to_array($decoded);
		return  $decoded;
	}
	if (function_exists('object_to_array')) return;
	function object_to_array($obj)
	{
		$arr = get_object_vars($obj);
		if (is_array($arr)) {
			foreach ($arr as $key => $value) {
				$arr[$key] = object_to_array($value);
			}
			return $arr;
		}
		return $obj;
	}
}
?>
