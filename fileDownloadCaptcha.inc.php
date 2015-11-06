<?php
/* setup includes */
require_once('includes/master.inc.php');

if (!isset($file))
{
    die("Error: No file found.");
}

require_once('_header.php');
?>

<?php
if (isErrors())
{
    echo outputErrors();
}
?>

<div class="contentPageWrapper">
    <div class="pageSectionMainFull ui-corner-all">
        <div class="pageSectionMainInternal">
            
            <!-- top ads -->
            <div class="metaRedirectWrapperTopAds">
            <?php echo SITE_CONFIG_ADVERT_DELAYED_REDIRECT_TOP; ?>
            </div>

            <div class="captchaPageTable">
                <form method="POST" action="<?php echo $file->getFullLongUrl(); ?>" autocomplete="off" id="form-join">
                    <table>
                        <tbody>
                            <tr>
                                <td>
                                    <div style="padding: 14px;">
                                        <div style="float: right;">
                                            <?php
                                            echo recaptcha_get_html(SITE_CONFIG_CAPTCHA_PUBLIC_KEY);
                                            ?>
                                            <div class="clear"><!-- --></div>
                                        </div>
                                        <div style="text-align:left;">
                                            <strong><?php echo $file->originalFilename; ?> (<?php echo formatSize($file->fileSize); ?>)</strong>
                                        </div>
                                        <div style="font-size: 12px; text-align:left; padding-top: 14px;">
                                            <?php echo t("in_order_to_prevent_abuse_captcha_intro", "In order to prevent abuse of this service, please copy the words into the text box on the right."); ?>
                                        </div>
                                        <div style="font-size: 12px; text-align:left; padding-top: 14px;">
                                            <input name="submit" type="submit" value="<?php echo t('continue', 'continue'); ?>" class="submitInput"/>
                                            <input type="hidden" name="submitted" value="1"/>
                                            <input type="hidden" name="d" value="1"/>
                                        </div>
                                        <div class="clear"><!-- --></div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </form>
                <div class="clear"><!-- --></div>
            </div>

            <!-- bottom ads -->
            <div class="metaRedirectWrapperBottomAds">
            <?php echo SITE_CONFIG_ADVERT_DELAYED_REDIRECT_BOTTOM; ?>
            </div>
            
        </div>
    </div>
</div>

<?php
require_once('_footer.php');
?>