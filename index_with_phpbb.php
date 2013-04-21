<?php

/*
 * @author _Tobias
 * @dependencies db.php / functions.php / php-sqlite3
 * @warning Inserting any track into the forum database will result in a HUGE database file! Better implement some check whether a track is an event.
 */

// My very own SQLite database wrapper class :D
require_once('db.php');

// Some handy functions for deserializing JSON to an array etc.
require_once('functions.php');

define('IN_PHPBB', true); // Don't remove
$phpbb_root_path = '../'; // The PHPBB root folder is one folder up.
$phpEx = 'php'; // Don't remove this, it's required by PHPBB for some reason.

require_once($phpbb_root_path.'config.php');
require_once($phpbb_root_path.'common.php');
require_once($phpbb_root_path.'includes/functions_posting.php');

// Output formatting is gonna be plain text
header('Content-type: text/plain');

// Connect or create the database named "bot".
$link = new Database('bot', 'architecture.json');

if($link->createArch('channels')) { // In case the table "channels" has to be created...
	// import channels list into "channels" table
	$json = jd(file_get_contents('http://listen.di.fm/premium'));
	if(!$json)
		die('Bot: Error getting channel list from API (listen).');
	
	foreach($json as $channel) {
		$link->insertArray(array( // Insert a channel into the database.
			'id' => $channel['id'],
			'key' => $channel['key'],
			'name' => $channel['name']
			), 'channels');
	}
}

$link->createArch('history'); // Create the "history" table in case it doesn't exist yet.

// A bit messy variable names

$json = jd(file_get_contents('http://api.audioaddict.com/v1/di/track_history'));
$channels_array = result2array($link->db->query('SELECT * FROM channels'));
$channels = array();
$channelidname = array();

foreach($channels_array as $entry) {
	$channels[$entry['id']] = $entry['key'];
	$channelidname[$entry['id']] = $entry['name'];
}

$nametofid = jd(file_get_contents('forums.json')); // This reads out the forum IDs from a json file.

$user->session_begin();

$original_user_id = $user->data['user_id']; // Back up the current logged in user.

$user->session_create(54); // Replace 54 by the user ID of the Playlist user.
@$auth->acl($user->data);

foreach($json as $cid => $entry) { // 
	if(isset($channels[$cid])) { // !! There appear to be channels in the track history API that don't exist :P Therefore check if the channel exists ;)
		$arr = result2array($link->db->query('SELECT track FROM history WHERE channel = '.$cid)); // Lets see if a new track's started playing..
		if(sizeof($arr) > 0)
			$last = end($arr);
		else
			$last = array('track' => null); // We're just starting up with a new database? No prob.

		if((sizeof($arr) === 0 && $entry['type'] == 'track') || ($last['track'] != $entry['track'] && $entry['type'] == 'track')) { // In case there are no entries yet, and the current one is not an ad, or a new track has started playing, insert.
			$link->insertArray(array(
				'channel' => $cid,
				'track' => $entry['track']
				), 'history');
			echo $entry['track'].PHP_EOL;
			
			// Posting to forum
			newPost($entry['track'], $entry['track'].PHP_EOL.'Post your replies here!', $nametofid[$channelidname[$cid]]); // First parameter is thread title, second is the message, third is the forum ID.
		}
	}
}

@$user->session_create($original_user_id, false, true); // This isn't really necessary, it's been handy during the debugging. This restores the user that was logged in before logged in to the playlist bot

function newPost($subject, $text, $channelIndex) {
	$subject = utf8_normalize_nfc($subject);
	$text = utf8_normalize_nfc($text);

	$es = '';

	generate_text_for_storage($subject, $es, $es, $es, false, false, false);
	generate_text_for_storage($text, $es, $es, $es, true, true, true);

	// Shitload of settings required for posting, leave the $es as is.
	$data = array(
		'forum_id' => $channelIndex,
		'icon_id' => false,
		'enable_bbcode' => false,
		'enable_smilies' => false,
		'enable_urls' => false,
		'enable_sig' => false,
		'message' => $text,
		'message_md5' => md5($text),
		'bbcode_bitfield' => $es,
		'bbcode_uid' => $es,
		'post_edit_locked' => 0,
		'topic_title' => $subject,
		'notify_set' => false,
		'notify' => false,
		'post_time' => 0,
		'enable_indexing' => true
	);

	submit_post('post', $subject, 'Playlist', POST_NORMAL, $es, $data); // Replace 'Playlist' by the Playlist bot's user name
}