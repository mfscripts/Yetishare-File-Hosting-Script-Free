<?php
require_once('ajax_auth.inc.php');
$db = Database::getDatabase();

/* get vars */
$params			= json_decode($_REQUEST['value']);
$language_name 	= trim($params->group1->language_name);

$response = array();
$response['content'] 	= "";
$response['javascript']	= "";
$response['errors']		= array();
$response['success']	= 1;

/* validate submission */
/* check to see if it exists in db */
$db = Database::getDatabase(true);
$row = $db->getRow('SELECT id FROM language WHERE languageName = '.$db->quote($language_name));
if(is_array($row))
{
	$response['errors']['language_name'] = array(t("language_already_in_system"));
}

/* insert/update db */
if(COUNT($response['errors']) == 0)
{
	/* create the intial record */
	$dbInsert = new DBObject("language", array("languageName"));
	$dbInsert->languageName 	= $language_name;
	if(!$dbInsert->insert())
	{
		$response['errors']['language_name'] = array(t("error_problem_record"));
	}
}

if(COUNT($response['errors']) > 0)
{
	$response['success'] = 0;
}

echo json_encode($response);
?>