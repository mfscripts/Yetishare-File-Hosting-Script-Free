<?php
/* setup includes */
require_once('includes/master.inc.php');

/* require login */
$Auth->requireUser('login.php');

/* remove file */
if(isset($_REQUEST['d']))
{
    $file = file::loadById($_REQUEST['d']);
    if ($file)
    {
        /* check user id */
        if ($file->userId == $Auth->id)
        {
            $file->removeByUser();
        }
    }
}

/* setup page */
define("PAGE_NAME", t("account_home_page_name", "Account Home"));
define("PAGE_DESCRIPTION", t("account_home_meta_description", "Your Account Home"));
define("PAGE_KEYWORDS", t("account_home_meta_keywords", "account, home, file, your, interface, upload, download, site"));

require_once('_header.php');

// load all files for this account
$files = file::loadAllByAccount($Auth->id);

// load all active files for this account
$activeFiles = file::loadAllActiveByAccount($Auth->id);
?>

<script>
    $(document).ready(function() {
        $('#fileData').dataTable( {
            "sPaginationType": "full_numbers",
            "aaSorting": [[ 1, "asc" ]],
            "bAutoWidth": false,
            "aoColumns": [
                { "asSorting": [ "asc", "desc" ] },
                { "asSorting": [ "asc", "desc" ] },
                { "asSorting": [ "asc", "desc" ] },
                { "asSorting": [ "asc", "desc" ] },
                { "asSorting": [ "asc", "desc" ] },
                null
            ],
            "fnRowCallback": function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                var id = aData[0];
                $(nRow).attr("id", id);
                cellContents = aData[4];
                if (cellContents.substr(0,7) == 'expired')
                {
                    $(nRow).addClass('rowUrlExpired');
                }
                return nRow;
            }
        } );
    } );
</script>

<div class="contentPageWrapper">

    <!-- main section -->
    <div class="pageSectionMainFull ui-corner-all">
        <div class="pageSectionMainInternal">
            <div id="pageHeader">
                <h2><?php echo t("your_files", "Your Files"); ?></h2>
            </div>
            <div>
                <p class="introText">
                    <?php if (COUNT($activeFiles))
                    { ?>
                        You have <?php echo COUNT($activeFiles); ?> active file<?php echo (COUNT($activeFiles) != 1) ? 's' : ''; ?> within your account. Use the table below to navigate or search for files you've previously uploaded. <a href="index.<?php echo SITE_CONFIG_PAGE_EXTENSION; ?>">Click here</a> to upload a file.
                    <?php }
                    else
                    { ?>
                        You have no active files within your account. <a href="index.<?php echo SITE_CONFIG_PAGE_EXTENSION; ?>">Click here</a> to upload a file.
<?php } ?>
                    <br/><br/>
                </p>
            </div>
            <div class="clear"><!-- --></div>

            <div>
                <p class="introText">
                    <?php
                    if ($files)
                    {
                        echo '<table id="fileData" width="100%" cellpadding="3" cellspacing="0">';
                        echo '<thead>';
                        echo '<th style="width: 19px;" class="ui-state-default"></th>';
                        echo '<th class="ui-state-default">'.t('download_url_filename', 'Download Url/Filename:').'</th>';
                        echo '<th  style="width: 135px; text-align: center;"class="ui-state-default">'.t('uploaded_last_visited', 'Uploaded/Last Visited:').'</th>';
                        echo '<th style="width: 55px; text-align: center;" class="ui-state-default">'.t('downloads', 'Downloads:').'</th>';
                        echo '<th style="width: 65px; text-align: center;" class="ui-state-default">'.t('status', 'Status:').'</th>';
                        echo '<th style="width: 85px; text-align: center;" class="ui-state-default">'.t('options', 'Options:').'</th>';
                        echo '</thead>';
                        echo '<tbody>';
                        foreach ($files AS $file)
                        {
                            echo '<tr>';
                            echo '<td class="txtCenter">';
                            $fileTypePath = DOC_ROOT.'/themes/' . SITE_CONFIG_SITE_THEME . '/images/file_icons/32px/'.$file['extension'].'.png';
                            if(file_exists($fileTypePath))
                            {
                                echo '  <img src="'.SITE_IMAGE_PATH.'/file_icons/32px/'.$file['extension'].'.png" width="32" height="32" title="'.$file['extension'].' file"/>';
                            }
                            $statusLabel = file::getStatusLabel($file['statusId']);
                            echo '</td>';
                            echo '<td title="' . $file['originalFilename'] . '">';
                            
                            // whether to add the filename on the end
                            $addedFilename = '';
                            if(SITE_CONFIG_FILE_URL_SHOW_FILENAME == 'yes')
                            {
                                $addedFilename = '/'.slugify($file['originalFilename']);
                            }
                            
                            if($file['statusId'] == 1)
                            {
                                echo '<a href="'._CONFIG_SITE_PROTOCOL.'://' . _CONFIG_SITE_FILE_DOMAIN . '/' . $file['shortUrl'] . $addedFilename.'" target="_blank">';
                            }
                            echo _CONFIG_SITE_FILE_DOMAIN . '/' . $file['shortUrl'].$addedFilename;
                            if($file['statusId'] == 1)
                            {
                                echo '</a>';
                            }
                            echo '<br/><span style="color: #999;">' . $file['originalFilename'];
                            echo '&nbsp;('.formatSize($file['fileSize']).')</font>';
                            echo '</td>';
                            echo '<td class="txtCenter">' . dater($file['uploadedDate']) . '<br/>' . dater($file['lastAccessed']) . '</td>';
                            echo '<td class="txtCenter">' . $file['visits'] . '</td>';
                            echo '<td class="txtCenter">' . $statusLabel;
                            echo '</td>';
                            $links = array();
							if ($statusLabel == 'active')
                            {
                                $links[] = '<a href="http://' . _CONFIG_SITE_FULL_URL . '/account_edit_item.' . SITE_CONFIG_PAGE_EXTENSION . '?u=' . $file['id'] . '"><img src="' . SITE_IMAGE_PATH . '/edit.png" width="16" height="16" title="edit item" style="margin:1px;"/></a>';
                            }
                            $links[] = '<a href="'._CONFIG_SITE_PROTOCOL.'://' . _CONFIG_SITE_FILE_DOMAIN . '/' . $file['shortUrl'] . '~s"><img src="' . SITE_IMAGE_PATH . '/chart_pie.png" width="16" height="16" title="download stats" style="margin:1px;"/></a>';
                            if ($statusLabel == 'active')
                            {
                                $links[] = '<a href="'.WEB_ROOT.'/'.$file['shortUrl'].'~i?'.$file['deleteHash'].'"><img src="' . SITE_IMAGE_PATH . '/group.png" width="16" height="16" title="share" style="margin:1px;"/></a>';
                            }
							if ($statusLabel == 'active')
                            {
                                $links[] = '<a href="'._CONFIG_SITE_PROTOCOL.'://' . _CONFIG_SITE_FULL_URL . '/account_home.' . SITE_CONFIG_PAGE_EXTENSION . '?d=' . $file['id'] . '" onClick="return confirm(\'Are you sure you want to remove this file?\');"><img src="' . SITE_IMAGE_PATH . '/delete.png" width="16" height="16" title="remove file" style="margin:1px;"/></a>';
                            }
                            echo '<td class="txtCenter">' . implode("&nbsp;", $links) . '</td>';
                            echo '</tr>';
                        }
                        echo '</tbody>';
                        echo '</table>';
                    }
                    ?>
                </p>
                <div class="clear"></div>
            </div>
        </div>
    </div>
</div>

<?php
require_once('_footer.php');
?>