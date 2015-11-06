<?php
/* setup includes */
require_once('includes/master.inc.php');

// initial checks
$folderId  = $_REQUEST['f'];
$folderExp = explode('~', $folderId);
$folderId  = (int) $folderExp[0];

// make sure it's a public folder or the owner is logged in
if ($folderId)
{
    $fileFolder = fileFolder::loadById($folderId);
    if (!$fileFolder)
    {
        // failed lookup of the fileFolder
        redirect('http://' . _CONFIG_SITE_FULL_URL . '/index.' . SITE_CONFIG_PAGE_EXTENSION);
    }

    // check the folder is public
    if (($fileFolder->isPublic == 0) && ($fileFolder->userId != $Auth->id))
    {
        redirect('http://' . _CONFIG_SITE_FULL_URL . '/index.' . SITE_CONFIG_PAGE_EXTENSION);
    }
}
else
{
    redirect('http://' . _CONFIG_SITE_FULL_URL . '/account_folders.' . SITE_CONFIG_PAGE_EXTENSION);
}

// check for password if we need it
$showFolder = true;
if(strlen($fileFolder->accessPassword) > 0)
{
    /* check folder password */
    if ((int) $_REQUEST['passwordSubmit'])
    {
        // check password
        $folderPassword = trim($_REQUEST['folderPassword']);

        if (!strlen($folderPassword))
        {
            setError(t("please_enter_the_folder_password", "Please enter the folder password"));
        }
        else
        {
            if(md5($folderPassword) == $fileFolder->accessPassword)
            {
                // successful
                $_SESSION['folderPassword'] = md5($folderPassword);
            }
            else
            {
                // login failed
                setError(t("password_is_invalid", "The folder password is invalid"));
            }
        }
    }

    // figure out whether to show the folder
    $showFolder = false;
    if(isset($_SESSION['folderPassword']))
    {
        // check password
        if($_SESSION['folderPassword'] == $fileFolder->accessPassword)
        {
            $showFolder = true;
        }
        
        // if owner, skip password requirement
        if($fileFolder->userId == $Auth->id)
        {
            $showFolder = true;
        }
    }
}

/* setup page */
define("PAGE_NAME", t("account_home_page_name", "View Folder"));
define("PAGE_DESCRIPTION", t("account_home_meta_description", "Your Account Home"));
define("PAGE_KEYWORDS", t("account_home_meta_keywords", "account, home, file, your, interface, upload, download, site"));

require_once('_header.php');

// show login box if password required
if($showFolder == false)
{
?>
    <div class="contentPageWrapper">
        <?php
        if (isErrors())
        {
            echo outputErrors();
        }
        ?>
        <!-- password form -->
        <div class="pageSectionMain ui-corner-all">
            <div class="pageSectionMainInternal">
                <div id="pageHeader">
                    <h2><?php echo t("folder_restricted", "Folder Restricted"); ?></h2>
                </div>
                <div>
                    <p class="introText">
                        <?php echo t("folder_login_intro_text", "Please enter the password below to access this folder."); ?>
                    </p>
                    <form class="international" method="post" action="http://<?php echo _CONFIG_SITE_FULL_URL; ?>/<?php echo $fileFolder->id; ?>~f" id="form-join" AUTOCOMPLETE="off">
                        <ul>
                            <li class="field-container"><label for="folderPassword">
                                    <span class="field-name"><?php echo t("password", "password"); ?></span>
                                    <input type="password" tabindex="2" value="" id="folderPassword" name="folderPassword" class="uiStyle" onFocus="showHideTip(this);"></label>
                                <div id="loginPasswordMainTip" class="hidden formTip">
                                    <?php echo t("folder_password_requirements", "The folder password."); ?>
                                </div>
                            </li>

                            <li class="field-container">
                                <span class="field-name"></span>
                                <input tabindex="99" type="submit" name="submit" value="<?php echo t("continue", "continue"); ?>" class="submitInput" />
                            </li>
                        </ul>

                        <input type="hidden" value="1" name="passwordSubmit"/>
                    </form>

                    <div class="clear"></div>
                </div>
            </div>
        </div>
        <?php include_once("_bannerRightContent.inc.php"); ?>
        <div class="clear"><!-- --></div>
    </div>
<?php
}
// show folder listing
else
{
// load all files by folder
$files = file::loadAllActiveByFolderId($folderId);
?>

<script>
    $(document).ready(function() {
        $('#fileData').dataTable( {
            "sPaginationType": "full_numbers",
            "aaSorting": [[ 1, "asc" ]],
            "aoColumns": [
                { "asSorting": [ "asc", "desc" ] },
                { "asSorting": [ "asc", "desc" ] },
                null
            ]
        } );
    } );
</script>

<div class="contentPageWrapper">

    <!-- main section -->
    <div class="pageSectionMainFull ui-corner-all">
        <div class="pageSectionMainInternal">
            <div id="pageHeader">
                <h2><?php echo t("files_within_folder", "Files Within Folder"); ?> '<?php echo htmlentities($fileFolder->folderName); ?>'</h2>
            </div>

            <div>
                <p class="introText">
                    <?php
                    if ($files)
                    {
                        echo '<table id="fileData" width="100%" cellpadding="3" cellspacing="0">';
                        echo '<thead>';
                        echo '<th style="width: 19px;" class="ui-state-default"></th>';
                        echo '<th class="ui-state-default">' . t('download_url_filename', 'Download Url/Filename:') . '</th>';
                        echo '<th style="width: 85px; text-align: center;" class="ui-state-default">' . t('options', 'Options:') . '</th>';
                        echo '</thead>';
                        echo '<tbody>';
                        foreach ($files AS $file)
                        {
                            echo '<tr>';
                            echo '<td class="txtCenter">';
                            $fileTypePath = DOC_ROOT . '/themes/' . SITE_CONFIG_SITE_THEME . '/images/file_icons/32px/' . $file['extension'] . '.png';
                            if (file_exists($fileTypePath))
                            {
                                echo '  <img src="' . SITE_IMAGE_PATH . '/file_icons/32px/' . $file['extension'] . '.png" width="32" height="32" title="' . $file['extension'] . ' file"/>';
                            }
                            echo '</td>';
                            echo '<td title="' . $file['originalFilename'] . '">';

                            // whether to add the filename on the end
                            $addedFilename = '';
                            if (SITE_CONFIG_FILE_URL_SHOW_FILENAME == 'yes')
                            {
                                $addedFilename = '/' . slugify($file['originalFilename']);
                            }

                            echo '<a href="' . _CONFIG_SITE_PROTOCOL . '://' . _CONFIG_SITE_FILE_DOMAIN . '/' . $file['shortUrl'] . $addedFilename . '" target="_blank">' . _CONFIG_SITE_FILE_DOMAIN . '/' . $file['shortUrl'] . $addedFilename . '</a>';

                            echo '<br/><span style="color: #999;">' . $file['originalFilename'];
                            echo '&nbsp;(' . formatSize($file['fileSize']) . ')</font>';
                            echo '</td>';

                            $links = array();
                            $links[] = '<a href="' . WEB_ROOT . '/' . $file['shortUrl'] . '~i"><img src="' . SITE_IMAGE_PATH . '/group.png" width="16" height="16" title="share" style="margin:1px;"/></a>';
                            echo '<td class="txtCenter">' . implode("&nbsp;", $links) . '</td>';
                            echo '</tr>';
                        }
                        echo '</tbody>';
                        echo '</table>';
                    }
                    else
                    {
                        echo t('there_are_no_files_within_this_folder', 'There are no files within this folder.');
                    }
                    ?>
                </p>
                <div class="clear"></div>
            </div>
        </div>
    </div>
</div>
<?php
}
?>

<?php
require_once('_footer.php');
?>