<?php
require_once('ajax_auth.inc.php');
$db = Database::getDatabase();

/* get vars */
$id = $_REQUEST['id'];

$response = array();
$response['content'] 	= "";
$response['javascript']	= "";
$response['errors']		= array();
$response['success']	= 0;

/* get user details */
$sQL = "SELECT file_server.id, file_server.serverLabel, file_server.serverType, file_server.ipAddress, file_server.connectionMethod, ";
$sQL .= "file_server.ftpPort, file_server.ftpUsername, file_server.ftpPassword, file_server.statusId, file_server.storagePath, file_server_status.label AS status ";
$sQL .= "FROM file_server LEFT JOIN file_server_status ON file_server.statusId = file_server_status.id ";
$sQL .= "WHERE file_server.id = ".(int)$id;
$response = $db->getRow($sQL);
if(!$response)
{
	$response = array();
	$response['errors'] = array('Could not load server details.');
}

echo json_encode($response);
