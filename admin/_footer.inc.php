	</div>
	<div id="adminFooterContainer" class="adminFooterContainer">
		<div id="adminFooter" class="adminFooter">
			<div style="float:right">
				Created by <a href="http://www.mfscripts.com" target="_blank">MFScripts.com</a> - v<?php echo _CONFIG_SCRIPT_VERSION; ?>
				<?php if($Auth->loggedIn()): ?>
				&nbsp;&nbsp;|&nbsp;&nbsp;<a href="server_info.php"><?php echo t("server_info", "Server Info"); ?></a>
				<?php endif; ?>
				<?php if($Auth->loggedIn()): ?>
				&nbsp;&nbsp;|&nbsp;&nbsp;<a href="http://www.mfscripts.com/forum-index.html" target="_blank"><?php echo t("support"); ?></a>
				<?php endif; ?>
			</div>
			<div style="float:left">
				<?php echo t("copyright"); ?> &copy; <?php echo date("Y"); ?> <?php echo SITE_CONFIG_SITE_NAME; ?>
			</div>
		</div>
	</div>
</body>
</html>