<?php

require_once('ajax_auth.inc.php');
$db = Database::getDatabase();

$id = (int) $_REQUEST['id'];
$statusId = (int) $_REQUEST['statusId'];

// check for removal
if($statusId == 3)
{
	// load file
	$file = file::loadById($id);
	if(!$file)
	{
		die("0");
	}

	// remove
	$file->removeBySystem();
}

$db->query('UPDATE file SET statusId = :statusId WHERE id = :id', array('statusId' => $statusId, 'id' => $id));
if ($db->affectedRows() == 1)
{
    die("1");
}
die("0");
