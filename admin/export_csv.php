<?php
set_time_limit(60*10);
require_once('local_auth.inc.php');
$db = Database::getDatabase();

/* resulting csv data */
$formattedCSVData = array();

/* header */
$lArr = array();
$lArr[] = "Id";
$lArr[] = "Filename";
$lArr[] = "Url";
$lArr[] = "Filesize";
$lArr[] = "Total Downloads";
$lArr[] = "Uploaded Date";
$lArr[] = "Last Accessed";
$formattedCSVData[] = "\"" . implode("\",\"", $lArr) . "\"";
	
/* get all url data */
$urlData = $db->getRows("SELECT * FROM file ORDER BY uploadedDate asc");
foreach($urlData AS $row)
{
	$lArr = array();
	$lArr[] = $row['id'];
	$lArr[] = $row['originalFilename'];
	$lArr[] = WEB_ROOT."/".$row['shortUrl'];
	$lArr[] = $row['fileSize'];
	$lArr[] = $row['visits'];
	$lArr[] = ($row['uploadedDate']!="0000-00-00 00:00:00")?dater($row['uploadedDate']):"";
	$lArr[] = ($row['lastAccessed']!="0000-00-00 00:00:00")?dater($row['lastAccessed']):"";
	
	$formattedCSVData[] = "\"" . implode("\",\"", $lArr) . "\"";
}

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT\n");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Content-type: application/zip;\n"); //or yours?
header("Content-Transfer-Encoding: binary");
//$len = strlen($formattedCSVData);
//header("Content-Length: $len;\n");
$outname = "file_data.csv";
header("Content-Disposition: attachment; filename=\"$outname\";\n\n");

echo implode("\n", $formattedCSVData);