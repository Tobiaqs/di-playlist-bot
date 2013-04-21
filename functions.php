<?php
function objectToArray($o) {
	// check if object
	if(is_object($o))
		$o = get_object_vars($o);

	// output array
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

function filterHidden($arr) {
	foreach($arr as $k => $v) {
		if($v['hide'] === 1)
			unset($arr[$k]);
	}
	return $arr;
}


function sample($link) {
	$forums = array(
		array(
			'id' => 1,
			'hide' => false,
			'title' => 'TestForum',
			'description' => 'Cool forum',
			'parent' => 0,
			'image' => null,
			'type' => 'f'
			),
		array(
			'id' => 2,
			'hide' => false,
			'title' => 'Cat',
			'description' => 'Cool forum',
			'parent' => 0,
			'image' => null,
			'type' => 'c'
			),
		array(
			'id' => 3,
			'hide' => false,
			'title' => 'TestCat',
			'description' => 'Cool cat',
			'parent' => 0,
			'image' => null,
			'type' => 'c'
			),
		array(
			'id' => 4,
			'hide' => false,
			'title' => 'for',
			'description' => 'Cool cat',
			'parent' => 2,
			'image' => null,
			'type' => 'f'
			),
		array(
			'id' => 5,
			'hide' => false,
			'title' => 'for2',
			'description' => 'Cool cat',
			'parent' => 2,
			'image' => null,
			'type' => 'f'
			),
		array(
			'id' => 6,
			'hide' => false,
			'title' => 'TestForum12312312',
			'description' => 'Cool cat',
			'parent' => 3,
			'image' => null,
			'type' => 'f'
			),
		array(
			'id' => 7,
			'hide' => true,
			'title' => 'TestCat',
			'description' => 'Cool cat',
			'parent' => 3,
			'image' => null,
			'type' => 'f'
			)
		);

	foreach($forums as $insert) {
		$link->insertArray($insert, 'forums');
	}

	$threads = array(
		array(
			'id' => 1,
			'title' => 'Thread 1',
			'sticky' => false,
			'parent' => 6
			),
		array(
			'id' => 2,
			'title' => 'Thread 2',
			'sticky' => false,
			'parent' => 6
			),
		array(
			'id' => 3,
			'title' => 'Thread 3',
			'sticky' => false,
			'parent' => 4
			)
		);

	foreach($threads as $insert) {
		$link->insertArray($insert, 'threads');
	}

	$posts = array(
		array(
			'text' => 'Hello world!!',
			'thread' => 3,
			'position' => 0,
			'author' => '_Tobias'
			),
		array(
			'text' => 'Second one',
			'thread' => 3,
			'position' => 1,
			'author' => '_Tobias'
			),
		array(
			'text' => 'another thread',
			'thread' => 2,
			'position' => 0,
			'author' => '_Tobias'
			)
		);

	foreach($posts as $insert) {
		$link->insertArray($insert, 'posts');
	}
}