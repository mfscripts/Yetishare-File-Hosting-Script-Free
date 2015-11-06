<?php
/* setup includes */
require_once('includes/master.inc.php');

/* setup page */
define("PAGE_NAME", t("login_page_name", "Login"));
define("PAGE_DESCRIPTION", t("login_meta_description", "Login to your account"));
define("PAGE_KEYWORDS", t("login_meta_keywords", "login, register, short url"));

/* register user */
if ((int) $_REQUEST['submitme'])
{
    // do login
    $loginUsername = trim($_REQUEST['loginUsername']);
    $loginPassword = trim($_REQUEST['loginPassword']);

    if (!strlen($loginUsername))
    {
        setError(t("please_enter_your_username", "Please enter your username"));
    }
    elseif (!strlen($loginPassword))
    {
        setError(t("please_enter_your_password", "Please enter your password"));
    }
    else
    {
        $rs = $Auth->login($loginUsername, $loginPassword);
        if ($rs)
        {
            // successful login
            redirect(WEB_ROOT . '/account_home.html');
        }
        else
        {
            // login failed
            setError(t("username_and_password_is_invalid", "Your username and password are invalid"));
        }
    }
}

require_once('_header.php');
?>

<div class="contentPageWrapper">

    <?php
    if (isErrors())
    {
        echo outputErrors();
    }
    ?>

    <!-- register form -->
    <div class="pageSectionMain ui-corner-all">
        <div class="pageSectionMainInternal">
            <div id="pageHeader">
                <h2><?php echo t("account_login", "Account Login"); ?></h2>
            </div>
            <div>
                <p class="introText">
                    <?php echo t("login_intro_text", "Please enter your username and password below to login."); ?>
                </p>
                <form class="international" method="post" action="http://<?php echo _CONFIG_SITE_FULL_URL; ?>/login.<?php echo SITE_CONFIG_PAGE_EXTENSION; ?>" id="form-join" AUTOCOMPLETE="off">
                    <ul>
                        <li class="field-container"><label for="loginUsernameMain">
                                <span class="field-name"><?php echo t("username", "username"); ?></span>
                                <input type="text" tabindex="1" value="<?php echo isset($loginUsername) ? safeOutputToScreen($loginUsername, 'abcdefghijklmnopqrstuvwxyz 1234567890_') : ''; ?>" id="loginUsernameMain" name="loginUsername" class="uiStyle" onFocus="showHideTip(this);"></label>
                            <div id="loginUsernameMainTip" class="hidden formTip">
                                <?php echo t("username_requirements", "Your account username. 6 characters or more and alpha numeric."); ?>
                            </div>
                        </li>

                        <li class="field-container"><label for="loginPasswordMain">
                                <span class="field-name"><?php echo t("password", "password"); ?></span>
                                <input type="password" tabindex="2" value="" id="loginPasswordMain" name="loginPassword" class="uiStyle" onFocus="showHideTip(this);"></label>
                            <div id="loginPasswordMainTip" class="hidden formTip">
                                <?php echo t("password_requirements", "Your account password. Min 6 characters, alpha numeric, no spaces."); ?>
                            </div>
                        </li>

                        <li class="field-container">
                            <span class="field-name"></span>
                            <input tabindex="99" type="submit" name="submit" value="<?php echo t("login", "login"); ?>" class="submitInput" />
                        </li>
                    </ul>

                    <input type="hidden" value="1" name="submitme"/>
                </form>

                <div class="clear"></div>
            </div>
        </div>
    </div>
    <?php include_once("_bannerRightContent.inc.php"); ?>
    <div class="clear"><!-- --></div>
</div>

<?php
require_once('_footer.php');
?>