<?php
require_once('ajax_auth.inc.php');
$db = Database::getDatabase();

/* get vars */
$params			= json_decode($_REQUEST['value']);
$ip_address 	= trim(strtolower($params->group1->ip_address));
$ban_type 		= trim($params->group1->ban_type);
$notes	 		= $params->group1->notes;

$response = array();
$response['content'] 	= "";
$response['javascript']	= "";
$response['errors']		= array();
$response['success']	= 1;

/* validate submission */
if(!isValidIP($ip_address))
{
	$response['errors']['ip_address'] = array(t("ip_address_invalid_try_again"));
}
/* check to see if it exists in db */
else
{
	$db = Database::getDatabase(true);
	$row = $db->getRow('SELECT id FROM banned_ips WHERE ipAddress = '.$db->quote($ip_address));
	if(is_array($row))
	{
		$response['errors']['ip_address'] = array(t("ip_address_already_blocked"));
	}
}

/* insert/update db */
if(COUNT($response['errors']) == 0)
{
	/* create the intial record */
	$dbInsert = new DBObject("banned_ips", array("ipAddress", "banType", "banNotes", "dateBanned"));
	$dbInsert->ipAddress 		= $ip_address;
	$dbInsert->banType 			= $ban_type;
	$dbInsert->banNotes 		= $notes;
	$dbInsert->dateBanned	 	= sqlDateTime();
	if(!$dbInsert->insert())
	{
		$response['errors']['ip_address'] = array("error_problem_record");
	}
}

if(COUNT($response['errors']) > 0)
{
	$response['success'] = 0;
}

echo json_encode($response);
?>