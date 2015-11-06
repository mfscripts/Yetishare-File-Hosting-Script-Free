<?php
/* setup includes */
require_once('includes/master.inc.php');

/* setup page */
define("PAGE_NAME", t("terms_page_name", "Terms and Conditions"));
define("PAGE_DESCRIPTION", t("terms_meta_description", "Terms and Conditions"));
define("PAGE_KEYWORDS", t("terms_meta_keywords", "terms, and, conditions, file, hosting, site"));

require_once('_header.php');
?>

<div class="contentPageWrapper">

    <!-- main section -->
    <div class="pageSectionMain ui-corner-all">
        <div class="pageSectionMainInternal">
            <div id="pageHeader">
                <h2><?php echo t("terms_page_name", "Terms &amp; Conditions"); ?></h2>
            </div>
            <div>
<strong>Basic TOS</strong><br/>
<br/>
All users must be of at least the age of 13, and agree to not use the <?php echo SITE_CONFIG_SITE_NAME; ?> service for any illegal 
or unauthorized purposes. All users must agree to comply with local laws regarding online conduct, and copyright laws. <?php echo SITE_CONFIG_SITE_NAME; ?> 
is intended for personal use, and any business use is strictly prohibited. All users must not use <?php echo SITE_CONFIG_SITE_NAME; ?>'s services to 
violate any laws which include but are not limited to copyright laws. Any violations will result in immediate deletion of all files
<?php echo SITE_CONFIG_SITE_NAME; ?> has on record for your IP Address.<br/>
<br/>
All users use <?php echo SITE_CONFIG_SITE_NAME; ?> at their own risk, 
users understand that files uploaded on <?php echo SITE_CONFIG_SITE_NAME; ?> are not private, they may be displayed for others to view, and
<?php echo SITE_CONFIG_SITE_NAME; ?> users understand and agree that <?php echo SITE_CONFIG_SITE_NAME; ?> cannot be responsible for the content 
posted on its web site and you nonetheless may be exposed to such materials and that you use <?php echo SITE_CONFIG_SITE_NAME; ?>'s service at 
your own risk.<br/>
<br/>
<strong>Conditions</strong><br/>
<br/>
- We reserve the right to modify or terminate the <?php echo SITE_CONFIG_SITE_NAME; ?> service for any reason, without notice at any time.<br/>
- We reserve the right to alter these Terms of Use at any time.<br/>
- We reserve the right to refuse service to anyone for any reason at any time.<br/>
- We may, but have no obligation to, remove Content and accounts containing Content that we determine in our sole discretion are unlawful, offensive, threatening, libelous, defamatory, obscene or otherwise objectionable or violates any party's intellectual property or these Terms of Use.<br/>
- If a user is found to be using <?php echo SITE_CONFIG_SITE_NAME; ?> to host icons, smileys, buddy icons, forum avatars, forum badges, forum signature images, or any other graphic for website design all your images will be removed.<br/>
<br/>
<strong>Copyright Information</strong><br/>
<br/>
<?php echo SITE_CONFIG_SITE_NAME; ?> claims no intellectual property rights over the images uploaded by its' users.<br/>
<br/>
<?php echo SITE_CONFIG_SITE_NAME; ?> will review all copyright &copy; infringement claims received and remove files found to have been upload or distributed in violation of any such laws. To make a valid claim you must provide <?php echo SITE_CONFIG_SITE_NAME; ?> with the following information:<br/>
<br/>
- A physical or electronic signature of the copyright owner or the person authorized to act on its behalf;<br/>
- A description of the copyrighted work claimed to have been infringed;<br/>
- A description of the infringing material and information reasonably sufficient to permit <?php echo SITE_CONFIG_SITE_NAME; ?> to locate the material;<br/>
- Your contact information, including your address, telephone number, and email;<br/>
- A statement by you that you have a good faith belief that use of the material in the manner complained of is not authorized by the copyright owner, its agent, or the law; and<br/>
- A statement that the information in the notification is accurate, and, under the pains and penalties of perjury, that you are authorized to act on behalf of the copyright owner.<br/>
<br/>
Claims can be sent to us via the <a href="report_file.<?php echo SITE_CONFIG_PAGE_EXTENSION; ?>">report abuse</a> page.
            </div>
        </div>
    </div>
    <?php include_once("_bannerRightContent.inc.php"); ?>
    <div class="clear"><!-- --></div>
</div>

<?php
require_once('_footer.php');
?>