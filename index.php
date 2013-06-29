<?php

/*
 * @author _Tobias
 * @dependencies db.php / functions.php / php-sqlite3
 */

$start = microtime(true);

header('Content-type: text/plain; charset=utf-8');

require_once('config.php');

$last_call = @filemtime($_db);
if($last_call)
	echo '~ database has been last modified at '.date('r', $last_call).PHP_EOL;

require_once('db.php');
require_once('functions.php');

$link = new Database($_db, $_arch);

if($link->createArch('channels')) {
	// import channels list
	$json = jd(file_get_contents('http://listen.di.fm/premium'));
	if(!$json)
		die('Error getting channel list from API (listen).');
	
	foreach($json as $channel) {
		$link->insertArray(array(
			'id' => $channel['id'],
			'key' => $channel['key'],
			'name' => $channel['name']
			), 'channels');
	}
}

$initial = $link->createArch('history');

if($initial)
	echo '~ first run, here we go!'.PHP_EOL.PHP_EOL;

$new = 0;


// check if there are changes in the json string
$json_md5 = 'json.md5'; // cache file

$raw = file_get_contents('http://api.audioaddict.com/v1/di/track_history'); // get new json
echo '~ '.strlen($raw).' bytes of json downloaded'.PHP_EOL;

$new_md5 = md5($raw); // calculate md5 of new json
touch($json_md5); // make sure the cache file exists
if(file_get_contents($json_md5) === $new_md5 && !$initial) { // if they're the same, why'd we do all dem database calls?
	echo '~ no md5 difference detected'.PHP_EOL;
	stop();
}
else
	file_put_contents($json_md5, $new_md5); // put new md5 in cache for the next run

$json = jd($raw); // parse json
$channels_raw = result2array($link->db->query('SELECT * FROM channels')); // get all channels
$channels = array();

foreach($channels_raw as $entry)
	$channels[$entry['id']] = $entry['key'];

foreach($json as $cid => $entry) {
	if(isset($channels[$cid])) {
		$isFirst = true;
		if(!$initial) {
			$arr = result2array($link->db->query('SELECT track FROM history WHERE channel = '.$cid.' ORDER BY id DESC LIMIT 1'));
			if($arr)
				$last = end($arr);
		}

		if($initial || !$arr || ($last['track'] != $entry['track'] && $entry['type'] == 'track')) {
			$link->insertArray(array(
				'channel' => $cid,
				'track' => $entry['track']
				), 'history');

			if($new === 0) // a little formatting
				echo PHP_EOL;

			$new += 1;

			/* Why I'm not using multi-valued insert?
			 * "The new multi-valued insert is merely syntactic suger (sic) for the compound insert.
			 * There is no performance advantage one way or the other."
			 */

			echo '['.$entry['track'].'] -> '.$channels[$cid].'('.$cid.')'.PHP_EOL;
		}
	}
}

if($new > 0) // some more formatting
	echo PHP_EOL;

stop();

function stop() {
	global $new, $start;
	echo '~ '.$new.' tracks added'.PHP_EOL;
	echo '~ script ran in '.round(microtime(true)-$start, 4).' seconds';
	exit;
}