<?php

require_once('ajax_auth.inc.php');
$db = Database::getDatabase();

/* get vars */
$params        = json_decode($_REQUEST['value']);
$username      = trim(strtolower($params->group1->username));
$password      = trim($params->group1->password);
$state         = $params->group2->state;
$accounttype   = $params->group2->accounttype;
$title         = $params->group3->title;
$firstname     = UCWords(strtolower(trim($params->group3->firstname)));
$lastname      = UCWords(strtolower(trim($params->group3->lastname)));
$email         = strtolower(trim($params->group3->email));
$accountexpiry = trim($params->group2->accountexpiry);

$response = array();
$response['content']    = "";
$response['javascript'] = "";
$response['errors']     = array();
$response['success'] = 1;

/* validate submission */
/* @TODO - check for existing user/email */
if (_CONFIG_DEMO_MODE == true)
{
    $response['errors']['serverLabel'] = array(t("no_changes_in_demo_mode"));
}
elseif ((strlen($username) < 6) || (strlen($username) > 16))
{
    $response['errors']['username'] = array(t("username_length_invalid"));
}
elseif ((strlen($password) < 6) || (strlen($password) > 16))
{
    $response['errors']['password'] = array(t("password_length_invalid"));
}
elseif (strlen($firstname) == 0)
{
    $response['errors']['firstname'] = array(t("enter_first_name"));
}
elseif (strlen($lastname) == 0)
{
    $response['errors']['lastname'] = array(t("enter_last_name"));
}
elseif (strlen($email) == 0)
{
    $response['errors']['email'] = array(t("enter_email_address"));
}
elseif (valid_email($email) == false)
{
    $response['errors']['email'] = array(t("entered_email_address_invalid"));
}
elseif (strlen($accountexpiry))
{
    if (strlen($accountexpiry) == 10)
    {
        $accountexpiry .= ' 00:00:00';
    }

    // check length
    if (strlen($accountexpiry) != 19)
    {
        $response['errors']['accountexpiry'] = array(t("account_expiry_invalid"));
    }

    // check format
    if (strtotime($accountexpiry) == false)
    {
        $response['errors']['accountexpiry'] = array(t("account_expiry_invalid"));
    }
}

/* insert/update db */
if (COUNT($response['errors']) == 0)
{
    /* create the intial record */
    $dbInsert = new DBObject("users", array("username", "password", "level", "email", "status", "title", "firstname", "lastname", "paidExpiryDate"));
    $dbInsert->username = $username;
    $dbInsert->password = MD5($password);
    $dbInsert->level = $accounttype;
    $dbInsert->email = $email;
    $dbInsert->status = $state;
    $dbInsert->title = $title;
    $dbInsert->firstname = $firstname;
    $dbInsert->lastname = $lastname;
    $dbInsert->paidExpiryDate = date("Y-m-d H:i:s", strtotime($accountexpiry));
    if (!$dbInsert->insert())
    {
        $response['errors']['username'] = array(t("error_problem_record"));
    }
}

if (COUNT($response['errors']) > 0)
{
    $response['success'] = 0;
}

echo json_encode($response);