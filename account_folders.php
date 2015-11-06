<?php
/* setup includes */
require_once('includes/master.inc.php');

/* require login */
$Auth->requireUser('login.php');

/* remove fileFolder */
if (isset($_REQUEST['d']))
{
	if(_CONFIG_DEMO_MODE == true)
    {
        setError(t("no_changes_in_demo_mode"));
    }

	if (!isErrors())
    {
		$fileFolder = fileFolder::loadById($_REQUEST['d']);
		if ($fileFolder)
		{
			/* check user id */
			if ($fileFolder->userId == $Auth->id)
			{
				$fileFolder->removeByUser();
			}
		}
	}
}

/* setup page */
define("PAGE_NAME", t("account_folder_name", "File Folders"));
define("PAGE_DESCRIPTION", t("account_folder_meta_description", "Your File Folders"));
define("PAGE_KEYWORDS", t("account_folder_meta_keywords", "file, folders, home, file, your, interface, upload, download, site"));

require_once('_header.php');

// load all fileFolder for this account
$fileFolders = fileFolder::loadAllByAccount($Auth->id);
?>

<script>
    $(document).ready(function() {
        $('#fileData').dataTable( {
            "sPaginationType": "full_numbers",
            "aaSorting": [[ 1, "asc" ]],
            "aoColumns": [
                { "asSorting": [ "asc", "desc" ] },
                { "asSorting": [ "asc", "desc" ] },
                { "asSorting": [ "asc", "desc" ] },
                { "asSorting": [ "asc", "desc" ] },
                { "asSorting": [ "asc", "desc" ] },
                null
            ]
        } );
    } );
</script>

<div class="contentPageWrapper">

	<?php
	if (isErrors())
    {
        echo outputErrors();
    }
	?>

    <!-- main section -->
    <div class="pageSectionMainFull ui-corner-all">
        <div class="pageSectionMainInternal">
            <div id="pageHeader">
                <h2><?php echo t("folders", "Folders"); ?></h2>
            </div>
            <div>
                <p class="introText">
                    <?php if (COUNT($fileFolders))
                    {
                        ?>
                        You have <?php echo COUNT($fileFolders); ?> folder<?php echo (COUNT($fileFolders) != 1) ? 's' : ''; ?> within your account. Use the table below to manage existing folders or <a href="http://<?php echo _CONFIG_SITE_FULL_URL; ?>/account_add_folder.<?php echo SITE_CONFIG_PAGE_EXTENSION; ?>">click here</a> to create a new one.
                    <?php
                    }
                    else
                    {
                        ?>
                        You have no folders within your account. <a href="http://<?php echo _CONFIG_SITE_FULL_URL; ?>/account_add_folder.<?php echo SITE_CONFIG_PAGE_EXTENSION; ?>">Click here</a> to create one.
<?php } ?>
                    <br/><br/>
                </p>
            </div>
            <div class="clear"><!-- --></div>

            <div>
                <p class="introText">
                    <?php
                    if ($fileFolders)
                    {
                        echo '<table id="fileData" width="100%" cellpadding="3" cellspacing="0">';
                        echo '<thead>';
                        echo '<th style="width: 19px;" class="ui-state-default"></th>';
                        echo '<th  style="text-align: left;"class="ui-state-default">' . t('folder_name', 'Folder Name:') . '</th>';
                        echo '<th style="width: 300px; text-align: left;" class="ui-state-default">' . t('sharing_url', 'Sharing Url:') . '</th>';
                        echo '<th style="width: 55px; text-align: center;" class="ui-state-default">' . t('active_files', 'Active Files:') . '</th>';
                        echo '<th style="width: 65px; text-align: center;" class="ui-state-default">' . t('is_public', 'Public:') . '</th>';
                        echo '<th style="width: 85px; text-align: center;" class="ui-state-default">' . t('options', 'Options:') . '</th>';
                        echo '</thead>';
                        echo '<tbody>';
                        foreach ($fileFolders AS $fileFolder)
                        {
                            // get total active files
                            $allFiles   = file::loadAllActiveByFolderId($fileFolder['id']);
                            $totalFiles = COUNT($allFiles);
                            $icon       = 'folder';
                            if (strlen($fileFolder['accessPassword']) > 0)
                            {
                                $icon = 'folder_lock';
                            }
                            elseif ($totalFiles > 0)
                            {
                                $icon = 'folder_full';
                            }

                            // output row
                            echo '<tr>';
                            echo '<td class="txtCenter">';
                            echo '  <img src="' . SITE_IMAGE_PATH . '/' . $icon . '.png" width="32" height="32" title="folder"/>';
                            echo '</td>';

                            echo '<td>';
                            echo '<a href="http://' . _CONFIG_SITE_FULL_URL . '/' . $fileFolder['id'] . '~f" target="_blank">';
                            echo htmlentities($fileFolder['folderName']);
                            echo '</a>';
                            echo '</td>';

                            echo '<td>';
                            echo '<a href="http://' . _CONFIG_SITE_FULL_URL . '/' . $fileFolder['id'] . '~f" target="_blank">' . $fileFolder['id'] . '~f</a>';
                            echo $fileFolder['isPublic'] == 1 ? '&nbsp;&nbsp;('.t('public_link', 'public link').')' : '';
                            echo '</td>';

                            echo '<td class="txtCenter">';
                            echo $totalFiles;
                            echo '</td>';

                            echo '<td class="txtCenter">';
                            echo $fileFolder['isPublic'] == 1 ? t('public_yes', 'yes') : t('public_no', 'no');
                            echo '</td>';

                            $links = array();
                            $links[] = '<a href="http://' . _CONFIG_SITE_FULL_URL . '/account_edit_folder.' . SITE_CONFIG_PAGE_EXTENSION . '?u=' . $fileFolder['id'] . '"><img src="' . SITE_IMAGE_PATH . '/edit.png" width="16" height="16" title="edit item" style="margin:1px;"/></a>';
                            $links[] = '<a href="' . _CONFIG_SITE_PROTOCOL . '://' . _CONFIG_SITE_FULL_URL . '/account_folders.' . SITE_CONFIG_PAGE_EXTENSION . '?d=' . $fileFolder['id'] . '" onClick="return confirm(\'Are you sure you want to remove this folder?\');"><img src="' . SITE_IMAGE_PATH . '/delete.png" width="16" height="16" title="remove folder" style="margin:1px;"/></a>';
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