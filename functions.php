<?php

/*
 * @author _Tobias
 * @description Some handy functions for SQLite interfacing and JSON parsing etc.
 */

function objectToArray($o) { // from http://forrst.com/posts/PHP_Recursive_Object_to_Array_good_for_handling-0ka
	if(is_object($o))
		$o = get_object_vars($o);
	return is_array($o) ? array_map(__FUNCTION__, $o) : $o;
}

function jd($raw) {
	return objectToArray(json_decode($raw));
}

function je($arr) {
	header('Content-type: application/json');
	return die(json_encode($arr));
}

function result2array($result) {
	$out = array();

	while($row = $result->fetchArray()) {
		$entry = array();
		foreach($row as $k => $v) {
			if(!is_numeric($k)) {
				$entry[$k] = $v;
			}
		}
		$out[] = $entry;
	}

	return $out;
}