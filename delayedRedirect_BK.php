<?php
/* setup includes */
require_once('includes/master.inc.php');

if(!isset($file))
{
    die("Error: No file found.");
}

require_once('_header.php');
?>

<script>
    <!--
    var milisec = 0;
    var seconds = <?php echo (int) SITE_CONFIG_REDIRECT_DELAY_SECONDS; ?>;

    function display()
    {
        if (milisec<=0)
        {
            milisec = 9;
            seconds -= 1;
        }
        if (seconds<=-1)
        {
            milisec = 0;
            seconds += 1;
        } 
        else
        {
            milisec -= 1;
        }
        if(seconds == 0)
        {
            document.getElementById("loadingSpinner").innerHTML = "<br/>";
            document.getElementById("countDownTimer").innerHTML = "<a href='<?php echo $file->getFullShortUrl(); ?>'><?php echo t("download_now", "download now"); ?></a>";
        }
        else
        {
            document.getElementById("countDownTimer").innerHTML = seconds+" second";
            if(seconds != 1)
            {
                document.getElementById("countDownTimer").innerHTML += "s";
            }
            setTimeout("display()", 100);
        }
    }

    $(document).ready(function() {
        document.getElementById("countDownTimer").innerHTML = '30';
        display();
    });
    -->
</script>

<div class="contentPageWrapper">
    <div class="pageSectionMainFull ui-corner-all">
        <div class="pageSectionMainInternal">
            
            <!-- top ads -->
            <!--
            <div class="metaRedirectWrapperTopAds">
            <?php echo SITE_CONFIG_ADVERT_DELAYED_REDIRECT_TOP; ?>
            </div>
            -->

            <div class="metaRedirectWrapper">
                <div class="metaRedirect">
                    <strong><?php echo t("loading_file_please_wait", "loading file, please wait"); ?></strong><br/>

                    <?php echo $file->originalFilename; ?> (<?php echo formatSize($file->fileSize); ?>)<br/>
                    <span id="loadingSpinner">
                        <img src="<?php echo SITE_IMAGE_PATH; ?>/pleaseWait.gif" alt="<?php echo t("please_wait", "please wait"); ?>" width="50" height="50" style="padding: 8px;"/><br/>
                    </span>
                    (<span id="countDownTimer"><!-- --></span>)
                </div>
            </div>
            
            <div id="pageHeader">
                <h2><?php echo t("upgrade_now_for_instant_access", "upgrade now for instant access"); ?></h2>
            </div>
            <div class="clear"><!-- --></div>

            <?php include_once('_upgradeBoxes.inc.php'); ?>
            
            <div id="pageHeader" style="padding-top: 12px;">
                <h2><?php echo t("account_benefits", "account benefits"); ?></h2>
            </div>
            <div class="clear"><!-- --></div>
            
            <?php include_once('_upgradeBenefits.inc.php'); ?>

            <!-- bottom ads -->
            <!--
            <div class="metaRedirectWrapperBottomAds">
            <?php echo SITE_CONFIG_ADVERT_DELAYED_REDIRECT_BOTTOM; ?>
            </div>
            -->
        </div>
    </div>
</div>
        

<?php
require_once('_footer.php');
?>