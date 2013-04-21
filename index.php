<?php

/*
 * @author _Tobias
 * @dependencies db.php / functions.php / php-sqlite3
 * Please see index_with_phpbb.php for comments
 */

require_once('db.php');
require_once('functions.php');

header('Content-type: text/plain');

$link = new Database('bot', 'architecture.json');

if($link->createArch('channels')) {
	// import channels list
	$json = jd(file_get_contents('http://listen.di.fm/premium'));
	if(!$json)
		die('Bot: Error getting channel list from API (listen).');
	
	foreach($json as $channel) {
		$link->insertArray(array(
			'id' => $channel['id'],
			'key' => $channel['key'],
			'name' => $channel['name']
			), 'channels');
	}
}
$link->createArch('history');

$json = jd(file_get_contents('http://api.audioaddict.com/v1/di/track_history'));
$channels_array = result2array($link->db->query('SELECT * FROM channels'));
$channels = array();
$channelidname = array();
foreach($channels_array as $entry) {
	$channels[$entry['id']] = $entry['key'];
	$channelidname[$entry['id']] = $entry['name'];
}

foreach($json as $cid => $entry) {
	if(isset($channels[$cid])) {
		$arr = result2array($link->db->query('SELECT track FROM history WHERE channel = '.$cid));
		if(sizeof($arr) > 0)
			$last = end($arr);
		else
			$last = array('track' => null);

		if(sizeof($arr) === 0 || ($last['track'] != $entry['track'] && $entry['type'] == 'track')) {
			$link->insertArray(array(
				'channel' => $cid,
				'track' => $entry['track']
				), 'history');
			echo $entry['track'].PHP_EOL;
		}
	}
}