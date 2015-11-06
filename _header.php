<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title><?php echo PAGE_NAME; ?> - <?php echo SITE_CONFIG_SITE_NAME; ?></title>
	<meta name="description" content="<?php echo PAGE_DESCRIPTION; ?>" />
	<meta name="keywords" content="<?php echo PAGE_KEYWORDS; ?>" />
	<meta name="copyright" content="Copyright &copy; <?php echo date("Y"); ?> - <?php echo SITE_CONFIG_SITE_NAME; ?>" />
	<meta name="robots" content="all" />
	<meta http-equiv="Cache-Control" content="no-cache" />
	<meta http-equiv="Expires" content="-1" />
	<meta http-equiv="Pragma" content="no-cache" />
        <link rel="stylesheet" href="<?php echo SITE_CSS_PATH; ?>/jquery-ui-1.8.9.custom.css" type="text/css" charset="utf-8" />
        <link rel="stylesheet" href="<?php echo SITE_CSS_PATH; ?>/screen.css" type="text/css" charset="utf-8" />
        <link rel="stylesheet" href="<?php echo SITE_CSS_PATH; ?>/tabview-core.css" type="text/css" charset="utf-8" />
        <link rel="stylesheet" href="<?php echo SITE_CSS_PATH; ?>/data_table.css" type="text/css" charset="utf-8" />
        <script type="text/javascript">
		<?php echo translate::generateJSLanguageCode(); ?>
	</script>
	<script type="text/javascript" src="<?php echo WEB_ROOT; ?>/js/jquery-1.6.1.min.js"></script>
	<script type="text/javascript" src="<?php echo WEB_ROOT; ?>/js/jquery-ui-1.8.9.custom.min.js"></script>
	<script type="text/javascript" src="<?php echo WEB_ROOT; ?>/js/jquery.dataTables.min.js"></script>
	<script type="text/javascript" src="<?php echo WEB_ROOT; ?>/js/fusionCharts/JSClass/FusionCharts.js"></script>
	<script type="text/javascript" src="<?php echo WEB_ROOT; ?>/js/jquery.tmpl.min.js"></script>
	<script type="text/javascript" src="<?php echo WEB_ROOT; ?>/js/jquery.fileupload.js"></script>
	<script type="text/javascript" src="<?php echo WEB_ROOT; ?>/js/jquery.fileupload-ui.js"></script>
	<script type="text/javascript" src="<?php echo WEB_ROOT; ?>/js/jquery.iframe-transport.js"></script>
	<script type="text/javascript" src="<?php echo WEB_ROOT; ?>/js/jquery.zclip.min.js"></script>
	<script type="text/javascript" src="<?php echo WEB_ROOT; ?>/js/global.js"></script>
</head>

<body>
    <div class="globalPageWrapper">
	<!-- header section -->
	<div class="headerBar">
                <!-- extra links -->
                <div class="mainNavigation">
                    <?php if($Auth->loggedIn() == false): ?>
                    <a href="<?php echo WEB_ROOT; ?>/register.<?php echo SITE_CONFIG_PAGE_EXTENSION; ?>"><?php echo t('register', 'register'); ?></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="<?php echo WEB_ROOT; ?>/faq.<?php echo SITE_CONFIG_PAGE_EXTENSION; ?>"><?php echo t('faq', 'faq'); ?></a>&nbsp;&nbsp;|&nbsp;<span id="loginLinkWrapper" class="loginLink">&nbsp;<a id="loginLink" href="<?php echo WEB_ROOT; ?>/login.<?php echo SITE_CONFIG_PAGE_EXTENSION; ?>"><?php echo t('login', 'login'); ?></a>&nbsp;</span>
                    <?php else: ?>
                    <a href="<?php echo WEB_ROOT; ?>/account_home.<?php echo SITE_CONFIG_PAGE_EXTENSION; ?>"><?php echo t('your_files', 'your files'); ?></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="<?php echo WEB_ROOT; ?>/account_folders.<?php echo SITE_CONFIG_PAGE_EXTENSION; ?>"><?php echo t('folders', 'folders'); ?></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="<?php echo WEB_ROOT; ?>/upgrade.<?php echo SITE_CONFIG_PAGE_EXTENSION; ?>"><?php echo($Auth->level == 'free user')?t('uprade_account', 'upgrade account'):t('extend_account', 'extend account'); ?></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="<?php echo WEB_ROOT; ?>/account_edit.<?php echo SITE_CONFIG_PAGE_EXTENSION; ?>"><?php echo t('settings', 'settings'); ?></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="<?php echo WEB_ROOT; ?>/logout.<?php echo SITE_CONFIG_PAGE_EXTENSION; ?>"><?php echo t('logout', 'logout'); ?> (<?php echo $Auth->username; ?>)</a>
                    <?php endif; ?>
                </div>

                <!-- Code for Login Link -->
                <!-- xHTML Code -->
                <div class="loginWrapper">
                    <div id="loginPanel" class="loginPanel">
                        <form action="<?php echo WEB_ROOT; ?>/login.<?php echo SITE_CONFIG_PAGE_EXTENSION; ?>" method="post" AUTOCOMPLETE="off">
                            <span class="fieldWrapper">
                                <label for="loginUsername">
                                    <span class="field-name"><?php echo t("username", "username"); ?></span>
                                    <input type="text" tabindex="50" value="" id="loginUsername" name="loginUsername" style="padding:3px;"/>
                                </label>
                            </span>
                            <div class="clear"><!-- --></div>

                            <span class="fieldWrapper">
                                <label for="loginPassword">
                                    <span class="field-name"><?php echo t("password", "password"); ?></span>
                                    <input type="password" tabindex="51" value="" id="loginPassword" name="loginPassword" style="padding:3px;"/>
                                </label>
                            </span>
                            <div class="clear"><!-- --></div>

                            <div class="submitButton">
                                <input name="submit" value="<?php echo t("login", "login"); ?>" type="submit" class="submitInput"/>
                            </div>
                            <div class="forgotPassword">
                                <a href="<?php echo WEB_ROOT; ?>/forgot_password.<?php echo SITE_CONFIG_PAGE_EXTENSION; ?>"><?php echo t("forgot_password", "forgot password"); ?>?</a>
                            </div>
                            <div class="clear"><!-- --></div>

                            <input name="submitme" type="hidden" value="1" />
                        </form>
                    </div>
                </div>

		<!-- main logo -->
		<div class="mainLogo">
                    <a href="<?php echo WEB_ROOT; ?>"><img src="<?php echo SITE_IMAGE_PATH; ?>/main_logo.jpg" height="48" alt="<?php echo SITE_CONFIG_SITE_NAME; ?>"/></a>
		</div>
	</div>
	
	<!-- body section -->
	<div class="bodyBarWrapper">
            <div class="bodyBar">