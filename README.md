di-playlist-bot
===============

Bot that saves track history from DI.fm

Usage
======
Run index.php once, that'll prepare the database and insert the current playing tracks.

To keep the database updated, trigger the script every 15 seconds or so.

To use the direct posting to phpBB, please read the comments in index_with_phpbb.php. You'll need to setup some values. The file forum.json contains the forum IDs associated with the channel names, you can create a file like this for your forum by being creative with jQuery ;)
