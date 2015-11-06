<?php
// setup includes
require_once('includes/master.inc.php');

// load file
$file = null;
if (isset($_REQUEST['u']))
{
    // figure out the delete hash
    $loggedInHash = '';
    foreach($_REQUEST AS $k=>$item)
    {
        if(strlen($k) == 32)
        {
            $loggedInHash = $k;
        }
    }

    // only keep the initial part if there's a forward slash
    $shortUrl = current(explode("/", str_replace("~i", "", $_REQUEST['u'])));
    $file = file::loadByShortUrl($shortUrl);

    // check whether we can display the delete/password options
    $showAll = false;
    if($file->deleteHash == $loggedInHash)
    {
        $showAll = true;
    }

    // double check the owner for logged in user
    if(($showAll == false) && ($file))
    {
        if($file->userId == $Auth->id)
        {
            $showAll = true;
        }
    }
}

/* load file details */
if(!$file)
{
    /* if no file found, redirect to home page */
    redirect(WEB_ROOT . "/index." . SITE_CONFIG_PAGE_EXTENSION);
}

// setup page
define("PAGE_NAME", $file->originalFilename.' '.t("file_information_page_name", ""));
define("PAGE_DESCRIPTION", t("file_information_description", "Information about").' '.$file->originalFilename);
define("PAGE_KEYWORDS", strtolower($file->originalFilename).t("file_information_meta_keywords", ", share, information, file, upload, download, site"));

require_once('_header.php');

?>

<div class="contentPageWrapper">
    <div class="pageSectionMainFull ui-corner-all">
        <div class="pageSectionMainInternal">
            <?php if($file->getLargeIconPath()): ?>
            <div style="float: right;">
                <img src="<?php echo $file->getLargeIconPath(); ?>" width="160" alt="<?php echo $file->extension; ?>"/>
            </div>
            <?php endif; ?>
            <div id="pageHeader">
                <h2><?php echo PAGE_NAME; ?></h2>
            </div>
            <div>
                <table class="accountStateTable" style="width: 740px;">
                    <tbody>
                        <tr>
                            <td class="first">
                                <?php echo UCWords(t('filename', 'filename')); ?>:
                            </td>
                            <td>
                                <?php echo $file->originalFilename; ?>&nbsp;&nbsp;<a href="<?php echo $file->getFullShortUrl(); ?>" target="_blank">(<?php echo t('download', 'download'); ?>)</a>
                            </td>
                        </tr>
                        <tr>
                            <td class="first">
                                <?php echo UCWords(t('filesize', 'filesize')); ?>:
                            </td>
                            <td>
                                <?php echo formatSize($file->fileSize); ?>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <table class="accountStateTable" style="width: 740px; margin-top: 16px;">
                    <tbody>
                        <tr>
                            <td class="first">
                                <?php echo UCWords(t('url', 'url')); ?>:
                            </td>
                            <td>
                                <a href="<?php echo $file->getFullShortUrl(); ?>" target="_blank"><?php echo $file->getFullShortUrl(); ?></a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="clear"><!-- --></div>

            <div id="pageHeader" style="padding-top: 12px;">
                <h2><?php echo t("download_urls", "download urls"); ?></h2>
            </div>
            <div>
                <table class="accountStateTable">
                    <tbody>
                        <tr>
                            <td class="first">
                                <?php echo t('html_code', 'HTML Code'); ?>:
                            </td>
                            <td class="htmlCode">
                                &lt;a href=&quot;<?php echo $file->getFullShortUrl(); ?>&quot; target=&quot;_blank&quot; title=&quot;<?php echo t('download_from', 'Download from'); ?> <?php echo SITE_CONFIG_SITE_NAME; ?>&quot;&gt;Download <?php echo $file->originalFilename; ?> from <?php echo SITE_CONFIG_SITE_NAME; ?>&lt;/a&gt;
                            </td>
                        </tr>
                        <tr>
                            <td class="first">
                                <?php echo UCWords(t('forum_code', 'forum code')); ?>
                            </td>
                            <td class="htmlCode">
                                [url]<?php echo $file->getFullShortUrl(); ?>[/url]
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="clear"><!-- --></div>

            <div id="pageHeader" style="padding-top: 12px;">
                <h2><?php echo t("share", "share"); ?></h2>
            </div>
            <div>
                <table class="accountStateTable">
                    <tbody>
                        <tr>
                            <td class="first">
                                <?php echo UCWords(t('share_file', 'share file')); ?>:
                            </td>
                            <td>
                                <!-- AddThis Button BEGIN -->
                                <div class="addthis_toolbox addthis_default_style addthis_32x32_style">
                                <a class="addthis_button_preferred_1" addthis:url="<?php echo $file->getFullShortUrl(); ?>" addthis:title="<?php echo str_replace("\"", "", $file->originalFilename); ?>"></a>
                                <a class="addthis_button_preferred_2" addthis:url="<?php echo $file->getFullShortUrl(); ?>" addthis:title="<?php echo str_replace("\"", "", $file->originalFilename); ?>"></a>
                                <a class="addthis_button_preferred_3" addthis:url="<?php echo $file->getFullShortUrl(); ?>" addthis:title="<?php echo str_replace("\"", "", $file->originalFilename); ?>"></a>
                                <a class="addthis_button_preferred_4" addthis:url="<?php echo $file->getFullShortUrl(); ?>" addthis:title="<?php echo str_replace("\"", "", $file->originalFilename); ?>"></a>
                                <a class="addthis_button_compact" addthis:url="<?php echo $file->getFullShortUrl(); ?>" addthis:title="<?php echo str_replace("\"", "", $file->originalFilename); ?>"></a>
                                <a class="addthis_counter addthis_bubble_style" addthis:url="<?php echo $file->getFullShortUrl(); ?>" addthis:title="<?php echo str_replace("\"", "", $file->originalFilename); ?>"></a>
                                </div>
                                <script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js#pubid=xa-4f10918d56581527"></script>
                                <!-- AddThis Button END -->
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="clear"><!-- --></div>

            <div id="pageHeader" style="padding-top: 12px;">
                <h2><?php echo t("other_options", "other options"); ?></h2>
            </div>
            <div>
                <table class="accountStateTable">
                    <tbody>
                        <tr>
                            <td class="first">
                                <?php echo UCWords(t('statistics', 'statistics')); ?>:
                            </td>
                            <td>
                                <a href="<?php echo $file->getStatisticsUrl(); ?>" target="_blank"><?php echo $file->getStatisticsUrl(); ?></a>
                            </td>
                        </tr>
                        <?php if($showAll == true): ?>
                        <tr>
                            <td class="first">
                                <?php echo UCWords(t('delete_file', 'delete file')); ?>:
                            </td>
                            <td>
                                <a href="<?php echo $file->getDeleteUrl(); ?>" target="_blank"><?php echo $file->getDeleteUrl(); ?></a>
                            </td>
                        </tr>
                        <?php endif ?>
                    </tbody>
                </table>
            </div>
            <div class="clear"><!-- --></div>
        </div>

    </div>
</div>
<div class="clear"></div>

<?php
require_once('_footer.php');
?>