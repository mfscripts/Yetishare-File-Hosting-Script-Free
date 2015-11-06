<?php

require_once('ajax_auth.inc.php');
require_once('../../includes/class.file.php');
require_once('../../includes/functions.inc.php');
$db = Database::getDatabase();

$results = (int) $_REQUEST['results'];
$startIndex = (int) $_REQUEST['startIndex'];
$sort = $_REQUEST['sort'] ? $_REQUEST['sort'] : "uploadedDate";
$dir = $_REQUEST['dir'] ? $_REQUEST['dir'] : "desc";
$filter = $_REQUEST['filter'] ? $_REQUEST['filter'] : "";
$filterDisabled = $_REQUEST['filterDisabled'] ? $_REQUEST['filterDisabled'] : "true";
$filterServer = strlen($_REQUEST['filterServer']) ? $_REQUEST['filterServer'] : false;
$filterUser = strlen($_REQUEST['filterUser']) ? $_REQUEST['filterUser'] : false;

$sqlClause = "WHERE 1=1 ";
if ($filter)
{
    $filter = $db->escape($filter);
    $sqlClause .= "AND (file_status.label = '" . $filter . "' OR ";
    $sqlClause .= "CONCAT('" . _CONFIG_SITE_FILE_DOMAIN . "/', file.shortUrl) LIKE '%" . $filter . "%' OR ";
    $sqlClause .= "file.originalFilename LIKE '%" . $filter . "%' OR ";
    $sqlClause .= "file.uploadedIP LIKE '%" . $filter . "%' OR ";
    $sqlClause .= "file.id = '" . $filter . "')";
}

if($filterServer)
{
    $sqlClause .= " AND file.serverId = ".$filterServer;
}

if($filterUser)
{
    $sqlClause .= " AND file.userId = ".$filterUser;
}

if ($filterDisabled == "false")
{
    $sqlClause .= " AND file.statusId = 1";
}

$totalRS = $db->getRows("SELECT file.id FROM file LEFT JOIN file_status ON file.statusId = file_status.id " . $sqlClause);
$limitedRS = $db->getRows("SELECT file.*, file_status.label FROM file LEFT JOIN file_status ON file.statusId = file_status.id " . $sqlClause . " ORDER BY " . $sort . " " . $dir . " LIMIT " . $startIndex . ", " . $results);

$resultArr = array();
$resultArr["totalRecords"] = COUNT($totalRS);
$resultArr["startIndex"] = $startIndex;
$resultArr["sort"] = $sort;
$resultArr["dir"] = $dir;
$resultArr["totalResultsReturned"] = 10;
$resultArr["firstResultPosition"] = 1;
$resultArr["records"] = array();

if (COUNT($limitedRS) > 0)
{
    foreach ($limitedRS AS $row)
    {
        $resultArr["records"][] = array("shortUrl" => _CONFIG_SITE_PROTOCOL.'://'._CONFIG_SITE_FILE_DOMAIN . "/" . $row['shortUrl'],
            "originalFilename" => $row['originalFilename'],
            "uploadedDate" => dater($row['uploadedDate']),
            "visits" => (int) $row['visits'],
            "uploadedIp" => $row['uploadedIP'],
            "lastAccessed" => dater($row['lastAccessed']),
            "status" => $row['label'],
            "fileSize" => (int)$row['fileSize']>0?formatSize($row['fileSize']):0,
            "id" => $row['id']);
    }
}

echo json_encode($resultArr);
