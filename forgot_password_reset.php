<?php
/* setup includes */
require_once('includes/master.inc.php');

// check for pending hash
$userId       = (int) $_REQUEST['u'];
$passwordHash = $_REQUEST['h'];
$user         = UserPeer::loadUserByPasswordResetHash($passwordHash);
if (!$user)
{
    redirect(WEB_ROOT);
}

// check user id passed is valid
if($user->id != $userId)
{
    redirect(WEB_ROOT);
}

/* setup page */
define("PAGE_NAME", t("forgot_password_page_name", "Forgot Password"));
define("PAGE_DESCRIPTION", t("forgot_password_meta_description", "Forgot account password"));
define("PAGE_KEYWORDS", t("forgot_password_meta_keywords", "forgot, password, account, short, url, user"));
$success = false;

/* register user */
if ((int) $_REQUEST['submitme'])
{
    // validation
    $password        = trim($_REQUEST['password']);
    $confirmPassword = trim($_REQUEST['confirmPassword']);
    if (!strlen($password))
    {
        setError(t("please_enter_your_password", "Please enter your new password"));
    }
    elseif ((strlen($password) < 6) || (strlen($password) > 20))
    {
        setError(t("password_length_incorrect", "Password should be between 6 - 20 characters in length"));
    }
    elseif (containsInvalidCharacters(strtolower($password, 'abcdefghijklmnopqrstuvwxyz1234567890@~#!-_£$&*()^%}{()')))
    {
        setError(t("password_contains_illegal_characters", "Password contains invalid characters, please choose another."));
    }
    elseif ($password != $confirmPassword)
    {
        setError(t("password_confirmation_does_not_match", "Your password confirmation does not match"));
    }

    // create the account
    if (!isErrors())
    {
        // update password
        $db = Database::getDatabase(true);
	$db->query('UPDATE users SET passwordResetHash = "", password = :password WHERE id = :id', array('password' => MD5($password), 'id' => $userId));
        
        // success
        $success = true;
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
                <h2><?php echo t("forgot_password", "forgot password"); ?></h2>
            </div>
            <div>
                <?php if ($success === true): ?>
                    <p class="introText">
                        <?php echo t("forgot_password_reset_confirm_intro_text", "Your password has been reset. You can now login to the site above."); ?>
                    </p>
                <?php else: ?>
                    <p class="introText">
                        <?php echo t("forgot_password_reset_intro_text", "Set your new password below to access your account."); ?>
                    </p>
                    <form class="international" method="post" action="http://<?php echo _CONFIG_SITE_FULL_URL; ?>/forgot_password_reset.<?php echo SITE_CONFIG_PAGE_EXTENSION; ?>" id="form-join" autocomplete="off">
                        <ul>
                            <li class="field-container"><label for="password">
                                    <span class="field-name"><?php echo t("password", "Password"); ?></span>
                                    <input type="password" value="" id="password" name="password" class="uiStyle" onFocus="showHideTip(this);"></label>
                                <div id="passwordTip" class="hidden formTip">
                                    Your new password. Min 6 characters, alpha numeric and hypens only.
                                </div>
                            </li>

                            <li class="field-container"><label for="confirmPassword">
                                    <span class="field-name"><?php echo t("confirm_password", "Confirm Password"); ?></span>
                                    <input type="password" value="" id="confirmPassword" name="confirmPassword" class="uiStyle" onFocus="showHideTip(this);"></label>
                                <div id="confirmPasswordTip" class="hidden formTip">
                                    Confirm your new password.
                                </div>
                            </li>

                            <li class="field-container">
                                <span class="field-name"></span>
                                <input tabindex="99" type="submit" name="submit" value="<?php echo t("update_password", "update password"); ?>" class="submitInput" />
                            </li>
                        </ul>

                        <input type="hidden" value="1" name="submitme"/>
                        <input type="hidden" value="<?php echo (int) $_REQUEST['u']; ?>" name="u"/>
                        <input type="hidden" value="<?php echo safeOutputToScreen($_REQUEST['h']); ?>" name="h"/>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include_once("_bannerRightContent.inc.php"); ?>
    <div class="clear"><!-- --></div>

</div>

<?php
require_once('_footer.php');
?>