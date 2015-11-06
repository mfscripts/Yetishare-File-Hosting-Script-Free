<?php
// setup includes
require_once('includes/master.inc.php');

// setup page
define("PAGE_NAME", t("report_abuse_page_name", "Report Abuse"));
define("PAGE_DESCRIPTION", t("report_abuse_meta_description", "Report Abuse or Copyright Infringement"));
define("PAGE_KEYWORDS", t("report_abuse_meta_keywords", "report, abuse, copyright, infringement, file, hosting"));

// send report if submitted
if ((int) $_REQUEST['submitme'])
{
    if (!strlen(trim($_REQUEST['fileDetails'])))
    {
        setError(t("report_abuse_error_no_content", "Please enter the details of the reported file."));
    }
    else
    {
        $subject = "New abuse report on " . SITE_CONFIG_SITE_NAME;
        $plainMsg = "There is a new abuse report on " . SITE_CONFIG_SITE_NAME . " with the following details:\n\n";
        $plainMsg .= "***************************************\n";
        $plainMsg .= trim($_REQUEST['fileDetails']) ."\n";
        $plainMsg .= "***************************************\n";
        $plainMsg .= "Submitted IP: ".getUsersIPAddress()."\n";
        $plainMsg .= "***************************************\n\n";
        $plainMsg .= "Please login via " . WEB_ROOT . "/admin/ to investigate further.";
        send_html_mail(SITE_CONFIG_REPORT_ABUSE_EMAIL, $subject, str_replace("\n", "<br/>", $plainMsg), SITE_CONFIG_REPORT_ABUSE_EMAIL, $plainMsg);
        redirect(WEB_ROOT);
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

    <!-- report abuse form -->
    <div class="pageSectionMain ui-corner-all">
        <div class="pageSectionMainInternal">
            <div id="pageHeader">
                <h2><?php echo t("report_abuse", "Report Abuse"); ?></h2>
            </div>
            <div class="introText">
                <?php echo t("report_abuse_intro", "Please use the following form to report any copyright infringements ensuring you supply all the following information:<br/><br/>
<ul class='formattedList'>
<li>A physical or electronic signature of the copyright owner or the person authorized to act on its behalf;</li>
<li>A description of the copyrighted work claimed to have been infringed;</li>
<li>A description of the infringing material and information reasonably sufficient to permit File Upload Script to locate the material;</li>
<li>Your contact information, including your address, telephone number, and email;</li>
<li>A statement by you that you have a good faith belief that use of the material in the manner complained of is not authorized by the copyright owner, its agent, or the law; and</li>
<li>A statement that the information in the notification is accurate, and, under the pains and penalties of perjury, that you are authorized to act on behalf of the copyright owner.</li>
</ul>"); ?>
                <br/>
                <form class="international" method="post" action="http://<?php echo _CONFIG_SITE_FULL_URL; ?>/report_file.<?php echo SITE_CONFIG_PAGE_EXTENSION; ?>" id="form-join">
                    <ul>
                        <li>
                            <textarea rows="12" id="fileDetails" name="fileDetails" style="width: 580px;" onFocus="showHideTip(this);"><?php echo isset($_REQUEST['fileDetails']) ? $_REQUEST['fileDetails'] : ''; ?></textarea>
                            <div id="problemUrlMainTip" class="hidden formTip">
                                <?php echo t("problem_file_requirements", "Enter the details of the file (as above) you wish to report."); ?>
                            </div>
                        </li>

                        <li>
                            <span class="field-name"></span>
                            <input name="submitme" type="hidden" value="1"/>
                            <input tabindex="99" type="submit" name="submit" value="<?php echo t("submit_report", "submit report"); ?>" class="submitInput" />
                        </li>
                    </ul>
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