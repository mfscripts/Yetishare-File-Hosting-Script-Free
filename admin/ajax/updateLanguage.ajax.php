<?php
require_once('ajax_auth.inc.php');
$db = Database::getDatabase();

/* fail if in demo mode */
if(_CONFIG_DEMO_MODE == true)
{
	die("0");
}

$newValue = $_REQUEST['newValue'];
$recordID = (int)$_REQUEST['recordID'];

$db->query('UPDATE language_content SET content = :content WHERE id = :id', array('content' => $newValue, 'id' => $recordID));
if($db->affectedRows() == 1)
{
	die("1");
}
die("0");
?>