<?php
// setup includes
require_once('includes/master.inc.php');

// setup page
define("PAGE_NAME", t("payment_complete_page_name", "Payment Complete"));
define("PAGE_DESCRIPTION", t("payment_complete_meta_description", "Payment Complete"));
define("PAGE_KEYWORDS", t("payment_complete_meta_keywords", "payment, complete, file, hosting, site"));

// include header
require_once('_header.php');
?>

<div class="contentPageWrapper">

    <!-- main section -->
    <div class="pageSectionMain ui-corner-all">
        <div class="pageSectionMainInternal">
            <div id="pageHeader">
                <h2><?php echo PAGE_NAME; ?></h2>
            </div>
            <p>
				Thanks for your payment!
			</p>
			<p>
				Once we receive notification from PayPal, your account will be upgraded/extended. Please allow up to an hour for this to complete.
			</p>
			<p>
				You can check your account status <a href="upgrade.<?php echo SITE_CONFIG_PAGE_EXTENSION; ?>">here</a>.
			</p>
        </div>
    </div>
    <?php include_once("_bannerRightContent.inc.php"); ?>
    <div class="clear"><!-- --></div>
</div>

<?php
require_once('_footer.php');
?>