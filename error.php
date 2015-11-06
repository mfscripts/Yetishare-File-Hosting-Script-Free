<?php
/* setup includes */
require_once('includes/master.inc.php');

/* setup page */
define("PAGE_NAME", t("error_page_name", "Error"));
define("PAGE_DESCRIPTION", t("error_meta_description", "Error"));
define("PAGE_KEYWORDS", t("error_meta_keywords", "error, file, upload, script"));

if (!$e = $_REQUEST['e'])
{
    $e = t("general_site_error", "There has been an error, please try again later.");
}
$e = strip_tags($e);

setError($e);

require_once('_header.php');
?>

<div class="searchBoxWrapper">
<?php echo outputErrors(); ?>
</div>

<?php
require_once('_footer.php');
?>