# di-playlist-bot #

Script that saves track history from DI.fm in a SQLite3 database

## Deps ##
- PHP
- SQLite 3 for PHP
- Something that'll trigger the script every now and then
- file\_get\_contents() must work in PHP

## Usage ##
Run **index.php** once, that'll prepare the database and insert the current playing tracks.

To keep the database updated, trigger the script every 15 seconds or so.

## Export ##
To export all tracks in the database, just call export.php. It will publish the entire database, formatted in Markdown, to [wrttn.in](http://wrttn.in "wrttn.in") and redirect to the [wrttn.in](http://wrttn.in "wrttn.in") URL.

## Warning ##
The database will use a lot of space (65+ channels!). Please make sure you've got enough space on your hosting or hard drive.

## Config ##
In case you feel urged to change the database's file name, see **config.php**.