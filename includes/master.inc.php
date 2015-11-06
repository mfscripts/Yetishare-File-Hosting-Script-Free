<?php

// Application flag
define('SPF', true);

// Determine our absolute document root
define('DOC_ROOT', realpath(dirname(__FILE__) . '/../'));

// Global include files
require DOC_ROOT . '/includes/functions.inc.php'; // __autoload() is contained in this file
require DOC_ROOT . '/includes/class.dbobject.php';
require DOC_ROOT . '/includes/class.objects.php';
require DOC_ROOT . '/includes/email_class/class.phpmailer.php';
require DOC_ROOT . '/includes/recaptcha/recaptchalib.php';

// Fix magic quotes
if (get_magic_quotes_gpc())
{
    $_POST = fix_slashes($_POST);
    $_GET = fix_slashes($_GET);
    $_REQUEST = fix_slashes($_REQUEST);
    $_COOKIE = fix_slashes($_COOKIE);
}

// Load our config settings
$Config = Config::getConfig();

/* load db config settings into constants */
$db = Database::getDatabase();
$rows = $db->getRows("SELECT config_key, config_value FROM site_config ORDER BY config_group, config_key");
if (COUNT($rows))
{
    foreach ($rows AS $row)
    {
        $constantName = "SITE_CONFIG_" . strtoupper($row['config_key']);
        define($constantName, $row['config_value']);
    }
}

/* setup translations */
translate::setUpTranslationConstants();

// Store session info in the database?
if ($Config->useDBSessions === true)
    DBSession::register();

// Initialize our session
session_name($Config->sessionName);
session_start();

// Initialize current user
$Auth = Auth::getAuth();

// Object for tracking and displaying error messages
$Error = Error::getError();

define("SITE_IMAGE_PATH", WEB_ROOT . "/themes/" . SITE_CONFIG_SITE_THEME . "/images");
define("SITE_CSS_PATH", WEB_ROOT . "/themes/" . SITE_CONFIG_SITE_THEME . "/styles");
define("SITE_JS_PATH", WEB_ROOT . "/themes/" . SITE_CONFIG_SITE_THEME . "/js");

/* check for banned ip */
$bannedIP = bannedIP::getBannedType();
if (strtolower($bannedIP) == "whole site")
{
    header('HTTP/1.1 404 Not Found');
    die();
}
