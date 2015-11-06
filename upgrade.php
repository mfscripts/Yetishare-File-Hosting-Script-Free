<?php
// setup includes
require_once('includes/master.inc.php');

// require login
$Auth->requireUser('login.php');

// page title
$title = UCWords(t('extend', 'extend'));
if($Auth->level == 'free user')
{
    $title = UCWords(t('upgrade', 'upgrade'));
}

// setup page
define("PAGE_NAME", $title.' '.t("upgrade_page_name", "Account"));
define("PAGE_DESCRIPTION", $title.' '.t("upgrade_meta_description", "Your Account"));
define("PAGE_KEYWORDS", strtolower($title).t("upgrade_meta_keywords", ", account, paid, membership, upload, download, site"));

require_once('_header.php');

?>

<div class="contentPageWrapper">
    <div class="pageSectionMainFull ui-corner-all">
        <div class="pageSectionMainInternal">
            <div id="pageHeader">
                <h2><?php echo t("account_status", "account status"); ?></h2>
            </div>
            <div>
                <table class="accountStateTable">
                    <tbody>
                        <tr>
                            <td class="first">
                                <?php echo UCWords(t('account_type', 'account type')); ?>:
                            </td>
                            <td>
                                <?php echo UCWords($Auth->level); ?>
                            </td>
                        </tr>
                        <?php if($Auth->level != 'free user'): ?>
                        <tr>
                            <td class="first">
                                <?php echo UCWords(t('reverts_to_free_account', 'reverts to free account')); ?>:
                            </td>
                            <td>
                                <?php echo($Auth->level == 'paid user')?dater($Auth->paidExpiryDate):UCWords(t('never', 'never')); ?>
                            </td>
                        </tr>
                        <?php endif ?>
                    </tbody>
                </table>
            </div>
            <div class="clear"><!-- --></div>

            <div id="pageHeader" style="padding-top: 18px;">
                <h2><?php echo strtolower(PAGE_NAME); ?></h2>
            </div>
            <div class="clear"><!-- --></div>

            <?php include_once('_upgradeBoxes.inc.php'); ?>

            <div id="pageHeader" style="padding-top: 18px;">
                <h2><?php echo t("account_benefits", "account benefits"); ?></h2>
            </div>
            <div class="clear"><!-- --></div>
            
            <?php include_once('_upgradeBenefits.inc.php'); ?>

        </div>
    </div>
</div>
<div class="clear"></div>

<?php
require_once('_footer.php');
?>