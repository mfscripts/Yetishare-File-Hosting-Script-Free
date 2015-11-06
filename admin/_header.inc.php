<?php
if(!defined("ADMIN_PUBLIC_AREA"))
{
    require_once('local_auth.inc.php');
}
else
{
    require_once('../includes/master.inc.php');
}
$db = Database::getDatabase();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title><?php echo t("admin_panel", "Admin Panel"); ?></title>
	
	<!-- YUI -->
	<script type="text/javascript" src="js/yui_combo.js"></script>
	<!-- http://developer.yahoo.com/yui/articles/hosting/?animation&base&button&connection&connectioncore&container&containercore&datasource&datatable&dom&dragdrop&element&event&fonts&get&grids&json&layout&menu&paginator&reset&resize&tabview&treeview&utilities&yahoo-dom-event&yuiloader&yuiloader-dom-event&MIN -->

	<link rel="stylesheet" href="admin_screen.css" type="text/css" media="screen" title="Screen" charset="utf-8" />
	<link rel="stylesheet" type="text/css" href="combo_yui.css">
	
	<script>
		<?php echo translate::generateJSLanguageCode(); ?>
	</script>
	
	<!-- inputEx -->
	<script type="text/javascript" src="../js/inputex-0.2.2/js/inputex-loader.js"></script>
	<link  href="../js/inputex-0.2.2/css/inputEx.css" rel="stylesheet" type="text/css" />
	<script src="../js/inputex-0.2.2/js/inputex.js" type="text/javascript"></script>
	<script src="../js/inputex-0.2.2/js/Field.js" type="text/javascript"></script>
	<script src="../js/inputex-0.2.2/js/Group.js" type="text/javascript"></script>
	<script src="../js/inputex-0.2.2/js/Form.js" type="text/javascript"></script>
	<script src="../js/inputex-0.2.2/js/fields/StringField.js" type="text/javascript"></script>
	<script src="../js/inputex-0.2.2/js/fields/CheckBox.js" type="text/javascript"></script>
	<script src="../js/inputex-0.2.2/js/fields/EmailField.js" type="text/javascript"></script>
	<script src="../js/inputex-0.2.2/js/fields/UrlField.js" type="text/javascript"></script>
	<script src="../js/inputex-0.2.2/js/fields/Textarea.js" type="text/javascript"></script>
	<script src="../js/inputex-0.2.2/js/fields/SelectField.js" type="text/javascript"></script>
	<script src="../js/inputex-0.2.2/js/fields/PasswordField.js" type="text/javascript"></script>
	<script src="../js/inputex-0.2.2/js/fields/HiddenField.js" type="text/javascript"></script>

	<!-- local -->
	<script type="text/javascript" src="js/admin.js"></script>
	<script type="text/javascript" src="js/admin_script.js"></script>
</head>

<body class="yui-skin-sam">
	<div id="adminHeaderContainer" class="adminHeaderContainer">
		<?php if($Auth->loggedIn()): ?>
			<div id="adminHeader" class="adminHeader">
				<div id="adminSubHeader" class="adminSubHeader">
					<div class="adminLinksRight">
						<?php echo t("logged_in_as"); ?> <strong><?php echo $userObj->username; ?></strong>&nbsp;&nbsp;|&nbsp;&nbsp;
						<?php echo date("l, jS F Y"); ?>
						<?php
							// max allowed upload size
							$maxUploadSizeFreeAcc = SITE_CONFIG_FREE_USER_MAX_UPLOAD_FILESIZE;
							$maxUploadSizePaidAcc = SITE_CONFIG_PREMIUM_USER_MAX_UPLOAD_FILESIZE;

							// if php restrictions are lower than permitted, add to notice
							$phpMaxSize = getPHPMaxUpload();
							$errorStrArr = array();
							if($phpMaxSize < $maxUploadSizeFreeAcc)
							{
								$errorStrArr[] = 'free account level ('.formatSize($maxUploadSizeFreeAcc).')';
							}
							if($phpMaxSize < $maxUploadSizePaidAcc)
							{
								$errorStrArr[] = 'paid account level ('.formatSize($maxUploadSizePaidAcc).')';
							}

							// prepare an error msg if we've found an issue
							$errorStr = '';
							if(COUNT($errorStrArr))
							{
								$errorStr = '<span onClick="window.location=\'server_info.php\';" style="cursor: pointer;">*** ERROR: Max upload size in php.ini file ('.formatSize($phpMaxSize).') is less than the '.implode(' and ', $errorStrArr).'. Please contact your host to resolve. ***</span>';
							}

							// output error
							if(strlen($errorStr))
							{
								echo '<br/><div class="error">'.$errorStr.'</div>';
							}
						?>
					</div>
					<div style="float:left;">
						<a href="index.php"><img src="admin_images/admin_logo.png" width="300" height="42" alt="admin logo"></a>
					</div>
				</div>
			</div>
			<div id="toolbar"></div>
		<?php endif; ?>
	</div>
	<div id="adminBody" class="adminBody">
            <!-- <?php t("manage_files", "Manage Files"); ?> -->