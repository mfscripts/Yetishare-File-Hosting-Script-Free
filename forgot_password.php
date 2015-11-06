<?php
/* setup includes */
require_once('includes/master.inc.php');

/* setup page */
define("PAGE_NAME", t("forgot_password_page_name", "Forgot Password"));
define("PAGE_DESCRIPTION", t("forgot_password_meta_description", "Forgot account password"));
define("PAGE_KEYWORDS", t("forgot_password_meta_keywords", "forgot, password, account, short, url, user"));

/* register user */
if ((int) $_REQUEST['submitme'])
{
    // validation
    $emailAddress = trim(strtolower($_REQUEST['emailAddress']));
    if (!strlen($emailAddress))
    {
        setError(t("please_enter_your_email_address", "Please enter the account email address"));
    }
    else
    {
        $checkEmail = UserPeer::loadUserByEmailAddress($emailAddress);
        if (!$checkEmail)
        {
            // username exists
            setError(t("account_not_found", "Account with that email address not found"));
        }
    }

    // create the account
    if (!isErrors())
    {
        $userAccount = UserPeer::loadUserByEmailAddress($emailAddress);
        if ($userAccount)
        {
			// create password reset hash
			$resetHash = UserPeer::createPasswordResetHash($userAccount->id);
			
            $subject = "Password reset instructions for account on " . SITE_CONFIG_SITE_NAME;
            $plainMsg = "Dear " . $userAccount->firstname . ",\n\n";
            $plainMsg .= "We've a request to reset your password on ".SITE_CONFIG_SITE_NAME.". Follow the url below to set a new account password:\n\n";
            $plainMsg .= "<a href='" . WEB_ROOT . "/forgot_password_reset.".SITE_CONFIG_PAGE_EXTENSION."?u=".$userAccount->id."&h=".$resetHash."'>" . WEB_ROOT . "/forgot_password_reset.".SITE_CONFIG_PAGE_EXTENSION."?u=".$userAccount->id."&h=".$resetHash."</a>\n\n";
            $plainMsg .= "If you didn't request a password reset, just ignore this email and your existing password will continue to work.\n\n";
            $plainMsg .= "Regards,\n";
            $plainMsg .= SITE_CONFIG_SITE_NAME . " Admin\n";

            send_html_mail($emailAddress, $subject, str_replace("\n", "<br/>", $plainMsg), SITE_CONFIG_DEFAULT_EMAIL_ADDRESS_FROM, strip_tags($plainMsg));
            redirect(WEB_ROOT . "/forgot_password." . SITE_CONFIG_PAGE_EXTENSION."?s=1");
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
                <h2><?php echo t("forgot_password", "forgot password"); ?></h2>
            </div>
            <div>
				<?php if(isset($_REQUEST['s'])): ?>
				<p class="introText">
					<?php echo t("forgot_password_sent_intro_text", "An email has been sent with further instructions on how to reset your password. Please check your email inbox."); ?>
                </p>
				<?php else: ?>
                <p class="introText">
					<?php echo t("forgot_password_intro_text", "Enter your email address below to receive further instructions on how to reset your account password."); ?>
                </p>
                <form class="international" method="post" action="http://<?php echo _CONFIG_SITE_FULL_URL; ?>/forgot_password.<?php echo SITE_CONFIG_PAGE_EXTENSION; ?>" id="form-join">
                    <ul>
                        <li class="field-container"><label for="emailAddress">
                                <span class="field-name"><?php echo t("email_address", "email address"); ?></span>
                                <input type="text" tabindex="1" value="<?php echo isset($emailAddress) ? safeOutputToScreen($emailAddress) : ''; ?>" id="emailAddress" name="emailAddress" class="uiStyle" onFocus="showHideTip(this);"></label>
                            <div id="emailAddressTip" class="hidden formTip">
                                Your registered account email address.
                            </div>
                        </li>

                        <li class="field-container">
                            <span class="field-name"></span>
                            <input tabindex="99" type="submit" name="submit" value="<?php echo t("request_reset", "request reset"); ?>" class="submitInput" />
                        </li>
                    </ul>

                    <input type="hidden" value="1" name="submitme"/>
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