<?php
/* setup includes */
require_once('includes/master.inc.php');

/* require login */
$Auth->requireUser('login.php');

/* load the fileFolder */
if (isset($_REQUEST['u']))
{
    $fileFolder = fileFolder::loadById($_REQUEST['u']);
    if (!$fileFolder)
    {
        // failed lookup of the fileFolder
        redirect('http://' . _CONFIG_SITE_FULL_URL . '/account_folders.' . SITE_CONFIG_PAGE_EXTENSION);
    }

    // check current user has permission to edit the fileFolder
    if ($fileFolder->userId != $Auth->id)
    {
        redirect('http://' . _CONFIG_SITE_FULL_URL . '/account_folders.' . SITE_CONFIG_PAGE_EXTENSION);
    }
}
else
{
    redirect('http://' . _CONFIG_SITE_FULL_URL . '/account_folders.' . SITE_CONFIG_PAGE_EXTENSION);
}

/* setup page */
define("PAGE_NAME", t("edit_page_name", "Edit"));
define("PAGE_DESCRIPTION", t("edit_meta_description", "Edit existing item"));
define("PAGE_KEYWORDS", t("edit_meta_keywords", "edit, existing, item"));

/* handle submission */
if ((int) $_REQUEST['submitme'])
{
    // validation
    $folderName     = trim($_REQUEST['folderName']);
    $isPublic       = (int) trim($_REQUEST['isPublic']);
    $accessPassword = trim($_REQUEST['accessPassword']);
    if (!strlen($folderName))
    {
        setError(t("please_enter_the_filename", "Please enter the folder name"));
    }
    elseif(_CONFIG_DEMO_MODE == true)
    {
        setError(t("no_changes_in_demo_mode"));
    }
    else
    {
        // check for existing folder
        $rs = $db->getRow('SELECT id FROM file_folder WHERE folderName = ' . $db->quote($folderName) . ' AND userId = ' . (int) $Auth->id . ' AND id != ' . $fileFolder->id);
        if ($rs)
        {
            if (COUNT($rs))
            {
                setError(t("already_a_folder_with_that_name", "You already have a folder with that name, please use another"));
            }
        }
    }

    if ($isPublic == 0)
    {
        $accessPassword = '';
    }

    // create the account
    if (!isErrors())
    {
        // prepare password
        if (strlen($accessPassword))
        {
            $accessPassword = MD5($accessPassword);
        }

        // update folder
        $db = Database::getDatabase(true);
        if ((strlen($accessPassword)) || ($isPublic == 0))
        {
            $rs = $db->query('UPDATE file_folder SET folderName = :folderName, isPublic = :isPublic, accessPassword = :accessPassword WHERE id = :id', array('folderName'     => $folderName, 'isPublic'       => $isPublic, 'id'             => $fileFolder->id, 'accessPassword' => $accessPassword));
        }
        else
        {
            $rs = $db->query('UPDATE file_folder SET folderName = :folderName, isPublic = :isPublic WHERE id = :id', array('folderName' => $folderName, 'isPublic'   => $isPublic, 'id'         => $fileFolder->id));
        }

        if ($rs)
        {
            // redirect
            redirect(WEB_ROOT . "/account_folders." . SITE_CONFIG_PAGE_EXTENSION);
        }
        else
        {
            setError(t("problem_updating_item", "There was a problem updating the item, please try again later."));
        }
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

    <!-- main section -->
    <div class="pageSectionMainFull ui-corner-all">
        <div class="pageSectionMainInternal">
            <div id="pageHeader">
                <h2><?php echo t("edit_existing_folder", "Edit Existing Folder"); ?> (<?php echo htmlentities($fileFolder->folderName); ?>)</h2>
            </div>
            <div>
                <p class="introText">
<?php echo t("edit_existing_folder_intro_text", "Use the form below to amend the selected folder."); ?>
                    <br/><br/>
                </p>

                <form class="international" method="post" action="http://<?php echo _CONFIG_SITE_FULL_URL; ?>/account_edit_folder.<?php echo SITE_CONFIG_PAGE_EXTENSION; ?>" id="form-join">
                    <ul>
                        <li class="field-container"><label for="folderUrl"><span class="field-name"><?php echo t('sharing_url', 'Sharing Url:'); ?></span>
                                <div style="padding-top: 2px;">
                                    <a href="http://<?php echo _CONFIG_SITE_FULL_URL; ?>/<?php echo $fileFolder->id; ?>~f" target="_blank">http://<?php echo _CONFIG_SITE_FULL_URL; ?>/<?php echo $fileFolder->id; ?>~f</a>
                                </div>
                            </label>
                        </li>
                        
                        <li class="field-container"><label for="folderName"><span class="field-name"><?php echo t("folder_name", "folder name"); ?></span><input type="text" value="<?php echo htmlentities($fileFolder->folderName); ?>" id="folderName" name="folderName" class="uiStyle" onFocus="showHideTip(this);" style="width:300px;"></label>
                            <div id="folderNameTip" class="hidden formTip" style="left: 522px;">
<?php echo t('the_folder_name', 'The folder name'); ?>
                            </div>
                        </li>

                        <li class="field-container"><label for="isPublic"><span class="field-name"><?php echo t('is_public', 'Public:'); ?></span><select id="isPublic" name="isPublic" class="uiStyle" onFocus="showHideTip(this);">
                                    <option value="0" <?php echo $fileFolder->isPublic == 0 ? 'SELECTED' : ''; ?>><?php echo t('no_keep_private', 'No, keep private'); ?></option>
                                    <option value="1" <?php echo $fileFolder->isPublic == 1 ? 'SELECTED' : ''; ?>><?php echo t('yes_allow_public', 'Yes, allow sharing'); ?></option>
                                </select></label>
                            <div id="isPublicTip" class="hidden formTip" style="left: 522px;">
<?php echo t('whether_to_allow_public_access_to_the_folder', 'Whether to allow public access to the folder or not'); ?>
                            </div>
                        </li>

                        <li class="field-container"><label for="accessPassword"><span class="field-name"><?php echo t("folder_password", "Folder Password:"); ?></span><input type="password" value="" id="accessPassword" name="accessPassword" class="uiStyle" onFocus="showHideTip(this);" style="width:300px;" autocomplete="off"></label>
                            <div id="accessPasswordTip" class="hidden formTip" style="left: 522px;">
<?php echo t('the_folder_password', 'Optional. The folder password. (must be a public folder)'); ?>
                            </div>
                        </li>

                        <li class="field-container">
                            <span class="field-name"></span>
                            <input tabindex="99" type="submit" name="submit" value="<?php echo t("update_item", "update item"); ?>" class="submitInput" />
                        </li>
                    </ul>

                    <input type="hidden" value="1" name="submitme"/>
                    <input type="hidden" value="<?php echo (int) $_REQUEST['u']; ?>" name="u"/>
                </form>
            </div>
            <div class="clear"><!-- --></div>
        </div>
    </div>
</div>

<?php
require_once('_footer.php');
?>