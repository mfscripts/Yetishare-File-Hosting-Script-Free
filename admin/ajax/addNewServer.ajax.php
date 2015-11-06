<?php

require_once('ajax_auth.inc.php');
$db = Database::getDatabase();

/* get vars */
$params = json_decode($_REQUEST['value']);

$serverLabel = trim($params->group1->serverLabel);
$serverType  = trim($params->group1->serverType);
$status      = trim($params->group1->status);
$ipAddress   = trim($params->group2->ipAddress);
$ftpPort     = trim($params->group2->ftpPort);
$ftpUsername = trim($params->group2->ftpUsername);
$ftpPassword = trim($params->group2->ftpPassword);
$storagePath = trim($params->group3->storagePath);

$response = array();
$response['content']    = "";
$response['javascript'] = "";
$response['errors']     = array();
$response['success'] = 1;

/* validate submission */
if (strlen($serverLabel) == 0)
{
    $response['errors']['serverLabel'] = array(t("server_label_invalid", "Please specify the server label."));
}
elseif (_CONFIG_DEMO_MODE == true)
{
    $response['errors']['serverLabel'] = array(t("no_changes_in_demo_mode"));
}
elseif ($serverType == 'remote')
{
    if (strlen($ipAddress) == 0)
    {
        $response['errors']['ftpHost'] = array(t("server_ftp_host_invalid", "Please specify the server ftp host."));
    }
    elseif (strlen($ftpPort) == 0)
    {
        $response['errors']['ftpPort'] = array(t("server_ftp_port_invalid", "Please specify the server ftp port."));
    }
    elseif (strlen($ftpUsername) == 0)
    {
        $response['errors']['ftpUsername'] = array(t("server_ftp_username_invalid", "Please specify the server ftp username."));
    }
}

/* insert/update db */
if (COUNT($response['errors']) == 0)
{
    /* lookup status id */
    $db->query('SELECT id FROM file_server_status WHERE label = :label', array('label'   => $status));
    $statusId = $db->getValue();

    /* create the intial record */
    $dbInsert = new DBObject("file_server", array("serverLabel", "serverType", "ipAddress", "connectionMethod", "ftpPort", "ftpUsername", "ftpPassword", "statusId", "storagePath"));
    $dbInsert->serverLabel = $serverLabel;
    $dbInsert->serverType = $serverType;
    $dbInsert->ipAddress = $ipAddress;
    $dbInsert->connectionMethod = $serverType == 'remote' ? 'ftp' : '';
    $dbInsert->ftpPort = $ftpPort;
    $dbInsert->ftpUsername = $ftpUsername;
    $dbInsert->ftpPassword = $ftpPassword;
    $dbInsert->statusId = $statusId;
    $dbInsert->storagePath = $storagePath;
    if (!$dbInsert->insert())
    {
        $response['errors']['serverLabel'] = array(t("error_problem_record"));
    }
}

if (COUNT($response['errors']) > 0)
{
    $response['success'] = 0;
}

echo json_encode($response);