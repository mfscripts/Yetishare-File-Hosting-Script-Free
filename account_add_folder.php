<?php
/* setup includes */
require_once('includes/master.inc.php');

/* require login */
$Auth->requireUser('login.php');

/* setup page */
define("PAGE_NAME", t("add_page_name", "Add"));
define("PAGE_DESCRIPTION", t("add_meta_description", "Add existing item"));
define("PAGE_KEYWORDS", t("add_meta_keywords", "add, existing, item"));

/* handle submission */
if ((int) $_REQUEST['submitme'])
{
    // validation
    $folderName     = trim($_REQUEST['folderName']);
    $isPublic       = (int) trim($_REQUEST['isPublic']);
    $accessPassword = trim($_REQUEST['accessPassword']);
    if (!strlen($folderName))
    {
        setError(t("please_enter_the_foldername", "Please enter the folder name"));
    }
    elseif(_CONFIG_DEMO_MODE == true)
    {
        setError(t("no_changes_in_demo_mode"));
    }
    else
    {
        // check for existing folder
        $rs = $db->getRow('SELECT id FROM file_folder WHERE folderName = ' . $db->quote($folderName) . ' AND userId = ' . (int) $Auth->id);
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
        $rs = $db->query('INSERT INTO file_folder (folderName, isPublic, userId, accessPassword) VALUES (:folderName, :isPublic, :userId, :accessPassword)', array('folderName'     => $folderName, 'isPublic'       => $isPublic, 'userId'         => $Auth->id, 'accessPassword' => $accessPassword));
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
                <h2><?php echo t("add_folder", "Add Folder"); ?></h2>
            </div>
            <div>
                <p class="introText">
<?php echo t("add_folder_intro_text", "Use the form below to add a new folder to your account."); ?>
                    <br/><br/>
                </p>

                <form class="international" method="post" action="http://<?php echo _CONFIG_SITE_FULL_URL; ?>/account_add_folder.<?php echo SITE_CONFIG_PAGE_EXTENSION; ?>" id="form-join">
                    <ul>
                        <li class="field-container"><label for="folderName"><span class="field-name"><?php echo t("folder_name", "folder name"); ?></span><input type="text" value="" id="folderName" name="folderName" class="uiStyle" onFocus="showHideTip(this);" style="width:300px;"></label>
                            <div id="folderNameTip" class="hidden formTip" style="left: 522px;">
<?php echo t('the_folder_name', 'The folder name'); ?>
                            </div>
                        </li>

                        <li class="field-container"><label for="isPublic"><span class="field-name"><?php echo t('is_public', 'Public:'); ?></span><select id="isPublic" name="isPublic" class="uiStyle" onFocus="showHideTip(this);">
                                    <option value="0"><?php echo t('no_keep_private', 'No, keep private'); ?></option>
                                    <option value="1"><?php echo t('yes_allow_public', 'Yes, allow sharing'); ?></option>
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
                            <input tabindex="99" type="submit" name="submit" value="<?php echo t("add_item", "add item"); ?>" class="submitInput" />
                        </li>
                    </ul>

                    <input type="hidden" value="1" name="submitme"/>
                </form>
            </div>
            <div class="clear"><!-- --></div>
        </div>
    </div>
</div>

<?php
require_once('_footer.php');
?>