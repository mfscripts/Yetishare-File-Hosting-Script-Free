<?php
/* setup includes */
require_once('includes/master.inc.php');

/* setup page */
define("PAGE_NAME", t("register_page_name", "Register"));
define("PAGE_DESCRIPTION", t("register_meta_description", "Register for an account"));
define("PAGE_KEYWORDS", t("register_meta_keywords", "register, account, short, url, user"));

/* register user */
if ((int) $_REQUEST['submitme'])
{
    // validation
    $title = trim($_REQUEST['title']);
    $firstname = trim($_REQUEST['firstname']);
    $lastname = trim($_REQUEST['lastname']);
    $emailAddress = trim(strtolower($_REQUEST['emailAddress']));
    $emailAddressConfirm = trim(strtolower($_REQUEST['emailAddressConfirm']));
    $username = trim(strtolower($_REQUEST['username']));

    if (!strlen($title))
    {
        setError(t("please_enter_your_title", "Please enter your title"));
    }
    elseif (!strlen($firstname))
    {
        setError(t("please_enter_your_firstname", "Please enter your firstname"));
    }
    elseif (!strlen($lastname))
    {
        setError(t("please_enter_your_lastname", "Please enter your lastname"));
    }
    elseif (!strlen($emailAddress))
    {
        setError(t("please_enter_your_email_address", "Please enter your email address"));
    }
    elseif ($emailAddress != $emailAddressConfirm)
    {
        setError(t("your_email_address_confirmation_does_not_match", "Your email address confirmation does not match"));
    }
    elseif (!valid_email($emailAddress))
    {
        setError(t("your_email_address_is_invalid", "Your email address is invalid"));
    }
    elseif (!strlen($username))
    {
        setError(t("please_enter_your_preferred_username", "Please enter your preferred username"));
    }
    elseif ((strlen($username) < 6) || (strlen($username) > 20))
    {
        setError(t("username_must_be_between_6_and_20_characters", "Your username must be between 6 and 20 characters"));
    }
    else
    {
        $checkEmail = UserPeer::loadUserByEmailAddress($emailAddress);
        if ($checkEmail)
        {
            // username exists
            setError(t("email_address_already_exists", "Email address already exists on another account"));
        }
        else
        {
            $checkUser = UserPeer::loadUserByUsername($username);
            if ($checkUser)
            {
                // username exists
                setError(t("username_already_exists", "Username already exists on another account"));
            }
        }
    }

    // create the account
    if (!isErrors())
    {
        $newPassword = createPassword();
        $newUser = UserPeer::create($username, $newPassword, $emailAddress, $title, $firstname, $lastname);
        if ($newUser)
        {
            $subject = "Account details for " . SITE_CONFIG_SITE_NAME;
            $plainMsg = "Dear " . $firstname . ",\n\n";
            $plainMsg .= "Your account on " . SITE_CONFIG_SITE_NAME . " has be created. Use the details below to login to your new account:\n\n";
            $plainMsg .= "<strong>Url:</strong> <a href='" . WEB_ROOT . "'>" . WEB_ROOT . "</a>\n";
            $plainMsg .= "<strong>Username:</strong> " . $username . "\n";
            $plainMsg .= "<strong>Password:</strong> " . $newPassword . "\n\n";
            $plainMsg .= "Feel free to contact us if you need any support with your account.\n\n";
            $plainMsg .= "Regards,\n";
            $plainMsg .= SITE_CONFIG_SITE_NAME . " Admin\n";

            send_html_mail($emailAddress, $subject, str_replace("\n", "<br/>", $plainMsg), SITE_CONFIG_DEFAULT_EMAIL_ADDRESS_FROM, strip_tags($plainMsg));
            redirect(WEB_ROOT . "/register_complete." . SITE_CONFIG_PAGE_EXTENSION);
        }
        else
        {
            setError(t("problem_creating_your_account_try_again_later", "There was a problem creating your account, please try again later"));
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
                <h2><?php echo t("register_account", "Register Account"); ?></h2>
            </div>
            <div>
                <p class="introText">
                    Please enter your information below to register for an account. Your new account password will be sent to your email address.
                </p>
                <form class="international" method="post" action="http://<?php echo _CONFIG_SITE_FULL_URL; ?>/register.<?php echo SITE_CONFIG_PAGE_EXTENSION; ?>" id="form-join">
                    <ul>
                        <li class="field-container">
                            <label for="title">
                                <span class="field-name"><?php echo t("title", "title"); ?></span>
                                <select autofocus="autofocus" tabindex="1" id="title" name="title" class="uiStyle" onFocus="showHideTip(this);">
                                    <option value="Mr">Mr</option>
                                    <option value="Mrs">Mrs</option>
                                    <option value="Miss">Miss</option>
                                    <option value="Dr">Dr</option>
                                    <option value="Pro">Pro</option>
                                </select>
                            </label>
                            <div id="titleTip" class="hidden formTip">
                                Your title
                            </div>
                        </li>

                        <li class="field-container"><label for="firstname">
                                <span class="field-name"><?php echo t("firstname", "firstname"); ?></span>
                                <input type="text" tabindex="1" value="<?php echo isset($firstname) ? safeOutputToScreen($firstname) : ''; ?>" id="firstname" name="firstname" class="uiStyle" onFocus="showHideTip(this);"></label>
                            <div id="firstnameTip" class="hidden formTip">
                                Your firstname
                            </div>
                        </li>

                        <li class="field-container"><label for="lastname">
                                <span class="field-name"><?php echo t("lastname", "lastname"); ?></span>
                                <input type="text" tabindex="1" value="<?php echo isset($lastname) ? safeOutputToScreen($lastname) : ''; ?>" id="lastname" name="lastname" class="uiStyle" onFocus="showHideTip(this);"></label>
                            <div id="lastnameTip" class="hidden formTip">
                                Your lastname
                            </div>
                        </li>

                        <li class="field-container"><label for="emailAddress">
                                <span class="field-name"><?php echo t("email_address", "email address"); ?></span>
                                <input type="text" tabindex="1" value="<?php echo isset($emailAddress) ? safeOutputToScreen($emailAddress) : ''; ?>" id="emailAddress" name="emailAddress" class="uiStyle" onFocus="showHideTip(this);"></label>
                            <div id="emailAddressTip" class="hidden formTip">
                                Check your inbox. You'll receive a confirmation email.
                            </div>
                        </li>

                        <li class="field-container"><label for="emailAddressConfirm">
                                <span class="field-name"><?php echo t("email_address_confirm", "Email Confirm"); ?></span>
                                <input type="text" tabindex="2" value="<?php echo isset($emailAddressConfirm) ? safeOutputToScreen($emailAddressConfirm) : ''; ?>" id="emailAddressConfirm" name="emailAddressConfirm" class="uiStyle" onFocus="showHideTip(this);"></label>
                            <div id="emailAddressConfirmTip" class="hidden formTip">
                                Please confirm your email address.
                            </div>
                        </li>

                        <li class="field-container"><label for="username">
                                <span class="field-name"><?php echo t("username", "username"); ?></span>
                                <input type="text" tabindex="3" value="<?php echo isset($username) ? safeOutputToScreen($username) : ''; ?>" id="username" name="username" class="uiStyle" onFocus="showHideTip(this);"></label>
                            <div id="usernameTip" class="hidden formTip">
                                Your account username. 6 characters or more and alpha numeric.
                            </div>
                        </li>

                        <li class="field-container">
                            <span class="field-name"></span>
                            <input tabindex="99" type="submit" name="submit" value="<?php echo t("register", "register"); ?>" class="submitInput" />
                        </li>
                    </ul>

                    <input type="hidden" value="1" name="submitme"/>
                </form>

                <div class="disclaimer">
                    By clicking 'register', you agree to our's <a href="#" onClick="showTerms(); return false;">Terms of service</a>.
                </div>
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