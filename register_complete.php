<?php
/* setup includes */
require_once('includes/master.inc.php');

/* setup page */
define("PAGE_NAME", t("register_complete_page_name", "Registration Completed"));
define("PAGE_DESCRIPTION", t("register_complete_meta_description", "Your registration has been completed."));
define("PAGE_KEYWORDS", t("register_complete_meta_keywords", "registration, completed, file, hosting, site"));

require_once('_header.php');
?>

<div class="contentPageWrapper">

    <!-- main section -->
    <div class="pageSectionMain ui-corner-all">
        <div class="pageSectionMainInternal">
            <div id="pageHeader">
                <h2><?php echo UCWords(t('register_account_complete', 'Register Account - Complete')); ?></h2>
            </div>
            <div>
                <p class="introText">
                    <strong><?php echo t("register_complete_sub_title", "Thank you for registering!"); ?></strong><br/><br/>
                    <?php echo t("register_complete_main_text", "We've sent an email to your registered email address with your access password. " .
                            "Please check your spam filters to ensure emails from this site get through. "); ?><br/><br/>
                    <?php echo t("register_complete_email_from", "Emails from this site are sent from "); ?>
                    <a href="mailto:<?php echo SITE_CONFIG_DEFAULT_EMAIL_ADDRESS_FROM; ?>"><?php echo SITE_CONFIG_DEFAULT_EMAIL_ADDRESS_FROM; ?></a>
                </p>
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