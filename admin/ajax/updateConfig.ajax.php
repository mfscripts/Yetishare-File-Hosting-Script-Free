<?php

require_once('ajax_auth.inc.php');
$db = Database::getDatabase();

/* fail if in demo mode */
if (_CONFIG_DEMO_MODE == true)
{
    die("0");
}

$newValue = $_REQUEST['newValue'];
$recordID = (int) $_REQUEST['recordID'];

$db->query('UPDATE site_config SET config_value = :newValue WHERE id = :id', array('newValue' => $newValue, 'id' => $recordID));
if ($db->affectedRows() == 1)
{
    die("1");
}
die("0");