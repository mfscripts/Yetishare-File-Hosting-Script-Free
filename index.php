<?php
// setup includes
require_once('includes/master.inc.php');

// setup page
define("PAGE_NAME", t("index_page_name", "Upload Files"));
define("PAGE_DESCRIPTION", t("index_meta_description", "Upload, share, track, manage your files in one simple to use file host."));
define("PAGE_KEYWORDS", t("index_meta_keywords", "upload, share, track, file, hosting, host"));

// check for file deletions & accounts to downgrade. This can be moved to a cron if required. It will only run checks every hour as-is.
downgradePaidAccounts();
deleteRedundantFiles();

// max allowed upload size
$maxUploadSize = SITE_CONFIG_FREE_USER_MAX_UPLOAD_FILESIZE;
if ($Auth->loggedIn())
{
    // check if user is a premium/paid user
    if ($Auth->level != 'free user')
    {
        $maxUploadSize = SITE_CONFIG_PREMIUM_USER_MAX_UPLOAD_FILESIZE;
    }

    // load user folders for later
    $userFolders = fileFolder::loadAllByAccount($Auth->id);
}
// if php restrictions are lower than permitted, override
$phpMaxSize  = getPHPMaxUpload();
if ($phpMaxSize < $maxUploadSize)
{
    $maxUploadSize = $phpMaxSize;
}

// get accepted file types
$acceptedFileTypes = getAcceptedFileTypes();

// header section
require_once('_header.php');

// index JS
require_once('_indexJS.inc.php');
?>

<div class="preLoadImages hidden">
    <img src="<?php echo SITE_IMAGE_PATH; ?>/delete_small.png" height="1" width="1"/>
    <img src="<?php echo SITE_IMAGE_PATH; ?>/add_small.png" height="1" width="1"/>
    <img src="<?php echo SITE_IMAGE_PATH; ?>/red_error_small.png" height="1" width="1"/>
    <img src="<?php echo SITE_IMAGE_PATH; ?>/green_tick_small.png" height="1" width="1"/>
    <img src="<?php echo SITE_IMAGE_PATH; ?>/blue_right_arrow.png" height="1" width="1"/>
    <img src="<?php echo SITE_IMAGE_PATH; ?>/processing_small.gif" height="1" width="1"/>
</div>

<div class="fileUploadMain ui-corner-all">
    <div id="fileUploadBadge" class="fileUploadBadge"></div>
    <div class="fileUploadMainInternal contentPageWrapper">

        <!-- uploader -->
        <div id="uploaderContainer" class="uploaderContainer">

            <div id="fileupload">
                <form action="<?php echo _CONFIG_SITE_PROTOCOL . '://' . _CONFIG_SITE_FULL_URL; ?>/uploadHandler.php" method="POST" enctype="multipart/form-data">
                    <div class="fileupload-buttonbar hiddenAlt">
                        <label class="fileinput-button">
                            <span><?php echo t('add_files', 'Add files...'); ?></span>
                            <input id="add_files_btn" type="file" name="files[]" multiple>
                        </label>
                        <button id="start_upload_btn" type="submit" class="start"><?php echo t('start_upload', 'Start upload'); ?></button>
                        <button id="cancel_upload_btn" type="reset" class="cancel"><?php echo t('cancel_upload', 'Cancel upload'); ?></button>
                    </div>
                    <div class="fileupload-content">
                        <label for="add_files_btn">
                            <div id="initialUploadSection" class="initialUploadSection"<?php if (!browserIsIE()): ?> onClick="$('#add_files_btn').click(); return false;"<?php endif; ?>>
                                <div class="initialUploadText">
                                    <div class="uploadText">
                                        <h2><?php echo t('select_files', 'Select files'); ?>:</h2>
                                    </div>
                                    <div class="clearLeft"><!-- --></div>

                                    <div class="uploadElement">
                                        <div class="internal">
                                            <?php if (browserIsIE()): ?>
                                                <?php echo t('click_here_to_browse_your_files', 'Click here to browse your files...'); ?>
                                            <?php else: ?>
                                                <?php echo t('drag_and_drop_files_here_or_click_to_browse', 'Drag &amp; drop files here or click to browse...'); ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="uploadFooter">
                                    <div class="baseText">
                                        <?php echo t('max_file_size', 'Max file size'); ?>: <?php echo formatSize($maxUploadSize); ?>. <?php echo COUNT($acceptedFileTypes) ? (t('allowed_file_types', 'Allowed file types') . ': ' . str_replace(".", "", implode(", ", $acceptedFileTypes)) . '.') : ''; ?>
                                    </div>
                                </div>
                                <div class="clear"><!-- --></div>
                            </div>
                        </label>
                        <div id="fileListingWrapper" class="fileListingWrapper hidden">
                            <div class="introText">
                                <h2><?php echo t('files', 'Files'); ?>:</h2>
                            </div>
                            <div class="clearLeft"><!-- --></div>

                            <div class="fileSection">
                                <table id="files" class="files" width="100%"><tbody></tbody></table>
                                <table id="addFileRow" class="addFileRow" width="100%">
                                    <tr class="template-upload">
                                        <td class="cancel">
                                            <a href="#"<?php if (!browserIsIE()): ?> onClick="$('#add_files_btn').click(); return false;"<?php endif; ?>>
                                                <label for="add_files_btn">
                                                    <img src="<?php echo SITE_IMAGE_PATH; ?>/add_small.gif" height="9" width="9" alt="<?php echo t('add_file', 'add file'); ?>"/>
                                                </label>
                                            </a>
                                        </td>
                                        <td class="name">
                                            <a href="#"<?php if (!browserIsIE()): ?> onClick="$('#add_files_btn').click(); return false;"<?php endif; ?>>
                                                <label for="add_files_btn">
                                                    <?php echo t('add_file', 'add file'); ?>
                                                </label>
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <div id="processQueueSection" class="fileSectionFooterText">
                                <div id="uploadButton" class="uploadButton" title="upload queue" onClick="$('#start_upload_btn').click();"><!-- --></div>
                                <div class="baseText">
                                    <?php echo t('max_file_size', 'Max file size'); ?>: <?php echo formatSize($maxUploadSize); ?>. <?php echo COUNT($acceptedFileTypes) ? (t('allowed_file_types', 'Allowed file types') . ': ' . str_replace(".", "", implode(", ", $acceptedFileTypes)) . '.') : ''; ?>
                                </div>
                                <div class="clear"><!-- --></div>
                            </div>

                            <div id="processingQueueSection" class="fileSectionFooterText hidden">
                                <div class="uploadProcessingButton" title="processing queue"><!-- --></div>
                                <div class="globalProgressWrapper">
                                    <div class="fileupload-progressbar" style="width:720px; height:10px;"></div>
                                    <div id="fileupload-progresstext" class="fileupload-progresstext" style="width:720px;">
                                        <div id="fileupload-progresstextRight" style="width:50%; float: right; text-align: right; <?php if (browserIsIE()): ?>display:none;<?php endif; ?>"><!-- --></div>
                                        <div id="fileupload-progresstextLeft" style="width:50%; float: left; <?php if (browserIsIE()): ?>display:none;<?php endif; ?>"><!-- --></div>
                                    </div>
                                </div>
                                <div class="clear"><!-- --></div>
                            </div>

                            <div id="completedSection" class="fileSectionFooterText hidden">
                                <div class="copyAllLinkWrapper">
                                    <a id="copyAllLink" href="#">[<?php echo t('copy_all_links', 'copy all links'); ?>]</a>
                                </div>
                                <div class="baseText">
                                    <?php echo t('file_upload_completed', 'File uploads completed.'); ?> <a href="<?php echo WEB_ROOT; ?>">Click here</a> to upload more files.
                                </div>
                                <div class="clear"><!-- --></div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <script id="template-upload" type="text/x-jquery-tmpl">
                <tr class="template-upload{{if error}} errorText{{/if}}">
                    <td class="cancel">
                        <a href="#" onClick="return false;">
                            <img src="<?php echo SITE_IMAGE_PATH; ?>/delete_small.png" height="10" width="10" alt="delete"/>
                        </a>
                    </td>
                    <td class="name">${name}&nbsp;&nbsp;${sizef}
                        {{if !error}}
                        <div class="start hidden"><button>start</button></div>
                        {{/if}}
                        <div class="cancel hidden"><button>cancel</button></div>
                    </td>
                    {{if error}}
                    <td colspan="2" class="error">Error:
                        {{if error === 'maxFileSize'}}File is too big
                        {{else error === 'minFileSize'}}File is too small
                        {{else error === 'acceptFileTypes'}}Filetype not allowed
                        {{else error === 'maxNumberOfFiles'}}Max number of files exceeded
                        {{else}}${error}
                        {{/if}}
                    </td>
                    {{else}}
                    <td colspan="2" class="preview"></td>
                    {{/if}}
                </tr>
                </script>
                <script id="template-download" type="text/x-jquery-tmpl">
                    <tr class="template-download{{if error}} errorText{{/if}}" onClick="return showAdditionalInformation(this);">
                        {{if error}}
                        <td class="cancel">
                            <img src="<?php echo SITE_IMAGE_PATH; ?>/red_error_small.png" height="16" width="16" alt="error"/>
                        </td>
                        <td class="name">${name} (${sizef})</td>
                        <td class="error" colspan="2">Error:
                            {{if error === 1}}File exceeds upload_max_filesize (php.ini directive)
                            {{else error === 2}}File exceeds MAX_FILE_SIZE (HTML form directive)
                            {{else error === 3}}File was only partially uploaded
                            {{else error === 4}}No File was uploaded
                            {{else error === 5}}Missing a temporary folder
                            {{else error === 6}}Failed to write file to disk
                            {{else error === 7}}File upload stopped by extension
                            {{else error === 'maxFileSize'}}File is too big
                            {{else error === 'minFileSize'}}File is too small
                            {{else error === 'acceptFileTypes'}}Filetype not allowed
                            {{else error === 'maxNumberOfFiles'}}Max number of files exceeded
                            {{else error === 'uploadedBytes'}}Uploaded bytes exceed file size
                            {{else error === 'emptyResult'}}Empty file upload result
                            {{else}}${error}
                            {{/if}}
                        </td>
                        {{else}}
                        <td class="cancel">
                            <img src="<?php echo SITE_IMAGE_PATH; ?>/green_tick_small.png" height="16" width="16" alt="success"/>
                        </td>
                        <td class="name">
                            ${name} (${sizef})
                            <div class="sliderContent" style="display: none;">
                                <!-- popup content -->
                                <table width="100%">
                                    <tr>
                                        <td class="odd" style="width: 90px; border-top:1px solid #fff;">
                                            <label><?php echo t('download_url', 'Download Url'); ?>:</label>
                                        </td>
                                        <td class="odd" style="border-top:1px solid #fff;">
                                            <a href="${url}" target="_blank">${url}</a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="even">
                                            <label><?php echo t('delete_url', 'Delete Url'); ?>:</label>
                                        </td>
                                        <td class="even">
                                            <a href="${delete_url}" target="_blank">${delete_url}</a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="odd">
                                            <label><?php echo t('stats_url', 'Stats Url'); ?>:</label>
                                        </td>
                                        <td class="odd">
                                            <a href="${stats_url}" target="_blank">${stats_url}</a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="even">
                                            <label><?php echo t('html_code', 'HTML Code'); ?>:</label>
                                        </td>
                                        <td class="even htmlCode" onClick="return false;">
                                            &lt;a href=&quot;${url}&quot; target=&quot;_blank&quot; title=&quot;Download from <?php echo SITE_CONFIG_SITE_NAME; ?>&quot;&gt;Download ${name} from <?php echo SITE_CONFIG_SITE_NAME; ?>&lt;/a&gt;
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="odd">
                                            <label><?php echo t('forum_code', 'Forum Code'); ?>:</label>
                                        </td>
                                        <td class="odd htmlCode">
                                            [url]${url}[/url]
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="even">
                                            <label><?php echo t('full_info', 'Full Info'); ?>:</label>
                                        </td>
                                        <td class="even htmlCode">
                                            <a href="${info_url}" target="_blank" onClick="window.open('${info_url}'); return false;">[<?php echo t('click_here', 'click here'); ?>]</a>
                                        </td>
                                    </tr>

                                    <?php if ($Auth->loggedIn() && COUNT($userFolders)): ?>
                                        <tr>
                                            <td class="odd">
                                                <label><?php echo t('save_to_folder', 'Save To Folder'); ?>:</label>
                                            </td>
                                            <td class="odd">
                                                <form>
                                                    <select name="folderId" id="folderId" class="saveToFolder" onChange="saveFileToFolder(this); return false;">
                                                        <option value="">- none -</option>
                                                        <?php foreach ($userFolders AS $userFolder): ?>
                                                            <option value="<?php echo $userFolder['id']; ?>"><?php echo htmlentities($userFolder['folderName']); ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </table>
                                <input type="hidden" value="${short_url}" name="shortUrlHidden" class="shortUrlHidden"/>
                            </div>
                        </td>
                        <td class="rightArrow"><img src="<?php echo SITE_IMAGE_PATH; ?>/blue_right_arrow.png" width="8" height="6" /></td>
                        <td class="url urlOff">
                            <a href="${url}" target="_blank">${url}</a>
                            <div class="fileUrls hidden">${url}</div>
                        </td>
                        {{/if}}
                    </tr>
                    </script>

                </div>
                <!-- end uploader -->

            </div>

            <div class="clear"><!-- --></div>
        </div>

        <div class="clear homePageSpacer"><!-- --></div>

        <div class="contentPageWrapper" style="padding-top: 12px;">
            <div style="float: right;">
                <?php include_once("_bannerRightContent.inc.php"); ?>
            </div>
            <div class="fileUploadContent ui-corner-all">
                <div class="fileUploadContentInternal contentPageWrapper">
                    <div class="left">
                        <h2><?php echo t('upload_share_and_manage_your_files_for_free', 'Upload, share and manage your files for free.'); ?></h2>
                        <div class="homepageInfoWrapper">
                            <div class="icon1"><!-- --></div>
                            <div class="homepageInfo">
                                <div class="homepageInfoTitle"><?php echo t('store_and_manage_all_your_files', 'Store and manage all your files!'); ?></div>
                                <div class="homepageInfoText"><?php echo t('upload_multiple_files_at_once_and_keep_them', 'Upload multiple files at once and keep them forever on this site. If you\'re using FireFox or Chrome, you can simply drag &amp; drop your files to begin uploading.'); ?></div>
                            </div>
                            <div class="clear"><!-- --></div>
                        </div>

                        <div class="homepageInfoWrapper">
                            <div class="icon2"><!-- --></div>
                            <div class="homepageInfo">
                                <div class="homepageInfoTitle"><?php echo t('share_your_files_with_everyone', 'Share your files with everyone!'); ?></div>
                                <div class="homepageInfoText"><?php echo t('we_supply_you_with_all_the_tools_necessary_to_easily_share', 'We supply you with all the tools necessary to easily share your files. Use our pre-generated html code to link from your website or post directly to Facebook or Twitter.'); ?></div>
                            </div>
                            <div class="clear"><!-- --></div>
                        </div>

                        <div class="homepageInfoWrapper">
                            <div class="icon3"><!-- --></div>
                            <div class="homepageInfo">
                                <div class="homepageInfoTitle"><?php echo t('fast_and_instant_downloading', 'Fast and instant downloading!'); ?></div>
                                <div class="homepageInfoText"><?php echo t('our_premium_members_benefit_from_no_waiting_time_and_direct', 'Our premium members benefit from no waiting time and direct downloads for all of their files. Unlike other file hosts we don\'t limit the transfer speed of our downloads.'); ?></div>
                            </div>
                            <div class="clear"><!-- --></div>
                        </div>

                        <div class="homepageInfoWrapper">
                            <div class="icon4"><!-- --></div>
                            <div class="homepageInfo">
                                <div class="homepageInfoTitle"><?php echo t('email_large_attachments', 'Email large attachments!'); ?></div>
                                <div class="homepageInfoText"><?php echo t('no_longer_do_you_have_to_risk_a_large_file_being_bounced_by_a_mail', 'No longer do you have to risk a large file being bounced by a mail server. Upload and send your recipient a link to download the file. You can even track when it\'s been downloaded.'); ?></div>
                            </div>
                            <div class="clear"><!-- --></div>
                        </div>
                        <div class="clear"><!-- --></div>
                    </div>
                    <div class="clear"><!-- --></div>
                </div>
                <div class="clear"><!-- --></div>
            </div>
            <div class="clear"><!-- --></div>
        </div>

        <?php
        require_once('_footer.php');
        ?>