<?php

require_once('includes/master.inc.php');

/* setup page */
define("PAGE_NAME", t("file_download_title", "Download File"));
define("PAGE_DESCRIPTION", t("file_download_description", "Download file"));
define("PAGE_KEYWORDS", t("file_download_keywords", "download, file, upload, mp3, avi, zip"));

// try to load the file object
$file = null;
if (isset($_REQUEST['u']))
{
    // only keep the initial part if there's a forward slash
    $shortUrl = current(explode("/", $_REQUEST['u']));
    $file     = file::loadByShortUrl($shortUrl);
}

// could not load the file, redirect to home page
if (!$file)
{
    redirect(WEB_ROOT . "/index." . SITE_CONFIG_PAGE_EXTENSION);
}

// has the url been removed
if ($file->statusId == 2)
{
    $errorMsg = t("error_file_has_been_removed_by_user", "File has been removed.");
    redirect(WEB_ROOT . "/error." . SITE_CONFIG_PAGE_EXTENSION . "?e=" . urlencode($errorMsg));
}
elseif ($file->statusId == 3)
{
    $errorMsg = t("error_file_has_been_removed_by_admin", "File has been removed by the site administrator.");
    redirect(WEB_ROOT . "/error." . SITE_CONFIG_PAGE_EXTENSION . "?e=" . urlencode($errorMsg));
}
if ($file->statusId == 4)
{
    $errorMsg = t("error_file_has_been_removed_due_to_copyright", "File has been removed due to copyright issues.");
    redirect(WEB_ROOT . "/error." . SITE_CONFIG_PAGE_EXTENSION . "?e=" . urlencode($errorMsg));
}

$skipCountdown = false;
if ((!isset($_SESSION['showDownload'])) || ($_SESSION['showDownload'] == null))
{
    $_SESSION['showDownload'] = time();
}

// if logged in
if ($Auth->loggedIn() === true)
{
    // if the current logged in user is a paid subscriber or admin
    if (in_array($Auth->level, array('paid user', 'admin')))
    {
        $skipCountdown = true;
    }
}
else
{
    if (!isset($_REQUEST['d']))
    {
        $_SESSION['showDownload'] = time();
    }
}

if ($skipCountdown == false)
{
    // check whether we need to display the countdown timer
    if ($_SESSION['showDownload'] >= time() - SITE_CONFIG_REDIRECT_DELAY_SECONDS)
    {
        include_once(_CONFIG_SCRIPT_ROOT . '/delayedRedirect.php');
        exit();
    }
}

// do we need to display the captcha?
if (($Auth->loggedIn() === false) || ($Auth->level == 'free user'))
{
    if (useCaptcha() == true)
    {
        /* do we require captcha validation? */
        $showCaptcha = false;
        if (!isset($_REQUEST['recaptcha_response_field']))
        {
            $showCaptcha = true;
        }

        /* check captcha */
        if (isset($_REQUEST['recaptcha_response_field']))
        {
            $resp = recaptcha_check_answer(SITE_CONFIG_CAPTCHA_PRIVATE_KEY, $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);
            if (!$resp->is_valid)
            {
                setError(t("invalid_captcha", "Captcha confirmation text is invalid."));
                $showCaptcha = true;
            }
        }

        if ($showCaptcha == true)
        {
            include_once(_CONFIG_SCRIPT_ROOT . '/fileDownloadCaptcha.inc.php');
            exit();
        }
    }
}

// update stats
$rs = Stats::track($file, $file->id);
if ($rs)
{
    $file->updateLastAccessed();
}

// close database so we don't cause locks during the download
$db = Database::getDatabase();
$db->close();

// download file
$rs = $file->download();
if (!$rs)
{
    $errorMsg = t("error_can_not_locate_file", "File can not be located, please try again later.");
    if ($file->errorMsg != null)
    {
        $errorMsg = 'Error: ' . $file->errorMsg;
    }
    redirect(WEB_ROOT . "/error." . SITE_CONFIG_PAGE_EXTENSION . "?e=" . urlencode($errorMsg));
}
