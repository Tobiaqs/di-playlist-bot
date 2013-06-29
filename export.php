<?php

/*
 * @author _Tobias
 * @dependencies db.php / functions.php / php-sqlite3
 */

require_once('db.php');
require_once('functions.php');
require_once('config.php');
$str = '*The newest tracks are on top*'.PHP_EOL.PHP_EOL;

$link = new Database($_db, $_arch);

$link->createArch('channels');
$link->createArch('history');

$channels = result2array($link->db->query('SELECT id,name FROM channels ORDER BY name ASC'));

foreach($channels as $channel) {
	$str .= '#### '.$channel['name'].' ####'.PHP_EOL.PHP_EOL;
	foreach(result2array($link->db->query('SELECT track FROM history WHERE channel = '.$channel['id'].' ORDER BY id DESC')) as $track) {
		$str .= '- '.$track['track'].PHP_EOL;
	}
	$str .= PHP_EOL;
}

$postdata = http_build_query(
	array(
		'content' => $str,
		'parser' => 'markdown',
		'custom_css' => null,
		'create' => 'create'
	)
);

$opts = array('http' =>
    array(
        'method'  => 'POST',
        'header'  => 'Content-type: application/x-www-form-urlencoded',
        'content' => $postdata
    )
);

$html = file_get_contents('http://wrttn.in/create', false, stream_context_create($opts));
preg_match('/<a href="\/(.+?)" class="lab" target="_blank">public url<\/a>/', $html, $matches);
header('Location: http://wrttn.in/'.$matches[1]);