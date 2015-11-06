<?php
die(); // DISABLED - ENABLED AS REQUIRED FOR TESTING
// setup includes
require_once('includes/master.inc.php');

$subject = "Test email from email_test.php";
$plainMsg = "Test email content";
send_html_mail($_REQUEST['email'], $subject, $plainMsg, SITE_CONFIG_REPORT_ABUSE_EMAIL, $plainMsg, true);
