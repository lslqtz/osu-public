<?
require_once('config.php');
require_once('global.php');

//Read in request variables.
$noVideo = strpos($beatmapSetId,'n');
$filename = $_REQUEST['f'];
$receivedChecksum = $_REQUEST['c'];
$timestamp = (int)$_REQUEST['t'];
$beatmapSetId = (int)$_REQUEST['s'];
$userId = (int)$_REQUEST['u'];

$calculatedChecksum = md5($filename . $userId . $timestamp . SECRET_KEY);

//Require a valid user...
if ($userId == 0)
	exit();

//Check incoming checksum...
if ($receivedChecksum != $calculatedChecksum)
	exit();

//Check link expiry.
if (abs($timestamp - time()) > MAX_LINK_AGE)
{
	echo "<head><META HTTP-EQUIV='REFRESH' CONTENT='1;url=http://osu.ppy.sh/s/$beatmapSetId'></head>";
	echo "This link has expired.  Please go back a step and try again.";
	exit();
}

if (URL_FOPEN_SUPPORT)
{
	//If we can support it, we notify the main server that the fulfillment was completed successfully.
	$handle = fopen("http://osu.ppy.sh/web/dl-check.php?k=$SECRET_KEY&u=$userId&s=$beatmapSetId&t=$timestamp", "r");
	$success = fgets($handle);
	fclose($handle);
	
	if (!$success)
	{
		echo "No download managers please!";
		exit();
	}
}

$localFilename = FILES_DIRECTORY . $beatmapSetId . ($noVideo ? 'n' : '');

$filesize = filesize($localFilename);

header("Content-Type: application/force-download");
header("Content-Type: application/octet-stream");
header("Content-Type: application/download");
header("Content-Disposition: attachment;filename=\"$filename\";");
header("Content-Transfer-Encoding: binary");
header("Content-Length: $filesize");

//Set request timeout to 1 hour.
set_time_limit(3600);

//KB per file read operation.  Larger requires less IO seeking but uses more memory.
$READ_CHUNK_SIZE = 512;

ob_implicit_flush(true);
ob_end_flush();

if($file = fopen($localFilename, 'rb'))
{
	while(!feof($file) && !connection_aborted())
		echo fread($file, 1024 * $READ_CHUNK_SIZE);
	fclose($file);
}

exit();
