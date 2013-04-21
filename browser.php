<?php

/*
 * @author _Tobias
 * @dependencies db.php / functions.php / php-sqlite3
 */

require_once('db.php');
require_once('functions.php');

$link->createArch('channels');
$link->createArch('history');

$link = new Database('bot', 'architecture.json');
if(isset($_POST['listChannels']))
	je(result2array($link->db->query('SELECT name, id FROM channels')));

if(@is_numeric($_POST['listHistory']))
	je(result2array($link->db->query('SELECT track FROM history WHERE channel = '.$_POST['listHistory'])));
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<title> History browser </title>
	<script src="http://code.jquery.com/jquery-latest.min.js"></script>
	<script>
	$(document).ready(function() {
		var data;
		$.post('', { listChannels: 1 }, function(x) {
			x.sort(function(a, b) {
				var nameA = a.name.toLowerCase(), nameB = b.name.toLowerCase();
				if (nameA < nameB) return -1 ;
				if (nameA > nameB) return 1;
				return 0;
			});

			data = x;

			selectChannel(x[0]);

			$('#channel').on('change', function() {
				selectChannel(x[this.selectedIndex]);
			});

			$('#refresh').on('click', function() {
				selectChannel(x[$('#channel')[0].selectedIndex]);
			});

			$.each(x, function(k,v) {
				$('#channel').append('<option value="'+v.id+'">'+v.name+'</option>');
			});
		});

		function selectChannel(c) {
			$('#history').empty();
			$.post('', { listHistory: c.id }, function(x) {
				$.each(x.reverse(), function(k,v) {
					$('#history').append(v.track+"\n");
				});
			});
		}
	});
	</script>
	<style>
	body {
		font-family: sans-serif;
	}
	</style>
</head>
<body>
<select id="channel"></select> <a href="javascript:;" id="refresh">Refresh</a>
<pre id="history"></pre>
</body>
</html>