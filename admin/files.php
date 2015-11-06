<?php
require_once('_header.inc.php');

// load all file servers
$sQL = "SELECT id, serverLabel FROM file_server ORDER BY serverLabel";
$serverDetails = $db->getRows($sQL);

// load all users
$sQL = "SELECT id, username AS selectValue FROM users ORDER BY username";
$userDetails = $db->getRows($sQL);

// do we need to filter by user
$userFilter = false;
if(isset($_REQUEST['user']))
{
    $userFilter = (int)$_REQUEST['user'];
    
    // load user details
    $userObj = UserPeer::loadUserById($userFilter);
}
?>

<div>
    <div style="float:left; padding-right:10px;">
        <?php echo t("files_filter_results", "Filter Results:"); ?>
    </div>
    <div style="float:left; padding-right:20px;">
        <input id="files_search" name="files_search" class="fileSearch" value="" type="text" onKeyUp="mfScripts.filesTable.updateFilteredResults();"/>
    </div>

    <div style="float:left; padding-right:10px;">
        <?php echo t("files_filter_user", "By User"); ?>:
    </div>
    <div style="float:left; padding-right:20px;">
        <select id="file_search_user" name="file_search_user" onChange="mfScripts.filesTable.updateFilteredResults();">
            <option value="">- all -</option>
            <?php
            if(COUNT($userDetails))
            {
                foreach($userDetails AS $userDetail)
                {
                    echo '<option value="'.$userDetail['id'].'"';
                    if(($userFilter) && ($userFilter == $userDetail['id']))
                    {
                        echo ' SELECTED';
                    }
                    echo '>'.$userDetail['selectValue'].'</option>';
                }
            }
            ?>
        </select>
    </div>
    
    <div style="float:left; padding-right:10px;">
        <?php echo t("files_filter_server", "By File Server"); ?>:
    </div>
    <div style="float:left; padding-right:20px;">
        <select id="file_search_server" name="file_search_server" onChange="mfScripts.filesTable.updateFilteredResults();">
            <option value="">- all -</option>
            <?php
            if(COUNT($serverDetails))
            {
                foreach($serverDetails AS $serverDetail)
                {
                    echo '<option value="'.$serverDetail['id'].'">'.$serverDetail['serverLabel'].'</option>';
                }
            }
            ?>
        </select>
    </div>
    
    <div style="float:left; padding-right:10px;">
        <?php echo t("files_filter_removed", "Show Removed"); ?>:
    </div>
    <div style="float:left; padding-right:20px;">
        <input id="file_search_disabled" name="file_search_disabled" value="1" type="checkbox" onChange="mfScripts.filesTable.updateFilteredResults();"/>
    </div>
    <div class="clear"><!-- --></div>
</div>
<div class="clear"><!-- --></div>

<div id="dataTable" class="yuiTable" style="padding-top:14px;"></div>
<div id="paginatorContainer"></div>

<script>
    /* main table setup */
    YAHOO.util.Event.addListener(window, "load", function() {
        mfScripts.filesTable.DynamicData();
    });
</script>

<ul class="navLink" style="padding-top: 10px;">
    <li><a href="export_csv.php?type=files"><?php echo t("export_files_as_csv", "Export File Data"); ?></a></li>
</ul>

<?php
require_once('_footer.inc.php');
?>