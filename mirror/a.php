<?
require_once("config.php");

//Handle remote administration requests from main server.

set_time_limit(300);

//Read in request variables...
$mirrorId = (int)$_REQUEST['m'];
$key = $_REQUEST['k'];
$beatmapSetId = (int)$_REQUEST['s'];
$noVideo = $_REQUEST['n'] == '1';

//Check for correct key...
if ($key != SECRET_KEY)
	exit();

set_time_limit(3600);

//wget is most reliable and memory-efficient.
exec('wget ' . "\"http://osu.ppy.sh/d/$beatmapSetId?m=$mirrorId&k=" . SECRET_KEY . "\" -O " . FILES_DIRECTORY . $beatmapSetId);
if ($noVideo)
	exec('wget ' . "\"http://osu.ppy.sh/d/$beatmapSetId"."n?m=$mirrorId&k=" . SECRET_KEY . "\" -O " . FILES_DIRECTORY . $beatmapSetId . 'n');

echo("success");

exit();
