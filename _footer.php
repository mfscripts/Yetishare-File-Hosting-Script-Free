            </div>
	</div>

	<!-- footer section -->
	<div class="footerBar">
            <!-- footer ads -->
            <div class="footerAds">
                <?php echo SITE_CONFIG_ADVERT_SITE_FOOTER; ?>
            </div>
            <div class="footerLinks">
                <div class="section1">
                    <?php if(!$Auth->loggedIn()): ?>
                        <strong><?php echo t('main_navigation', 'Main Navigation'); ?></strong>
                        <ul>
                            <li><a href="<?php echo WEB_ROOT; ?>/"><?php echo t('upload_file', 'upload file'); ?></a></li>
                            <li><a href="<?php echo WEB_ROOT; ?>/register.<?php echo SITE_CONFIG_PAGE_EXTENSION; ?>"><?php echo t('register', 'register'); ?></a></li>
                            <li><a href="<?php echo WEB_ROOT; ?>/faq.<?php echo SITE_CONFIG_PAGE_EXTENSION; ?>"><?php echo t('faq', 'faq'); ?></a></li>
                            <li><a href="<?php echo WEB_ROOT; ?>/login.<?php echo SITE_CONFIG_PAGE_EXTENSION; ?>"><?php echo t('login', 'login'); ?></a></li>
                        </ul>
                    <?php else: ?>
                        <strong><?php echo t('your_account', 'Your Account'); ?></strong>
                        <ul>
                            <li><a href="<?php echo WEB_ROOT; ?>/"><?php echo t('upload_file', 'upload file'); ?></a></li>
                            <li><a href="<?php echo WEB_ROOT; ?>/account_home.<?php echo SITE_CONFIG_PAGE_EXTENSION; ?>"><?php echo t('your_files', 'your files'); ?></a></li>
                            <li><a href="<?php echo WEB_ROOT; ?>/account_folders.<?php echo SITE_CONFIG_PAGE_EXTENSION; ?>"><?php echo t('folders', 'folders'); ?></a></li>
                            <?php if($Auth->level != 'admin'): ?>
                            <li><a href="<?php echo WEB_ROOT; ?>/upgrade.<?php echo SITE_CONFIG_PAGE_EXTENSION; ?>"><?php echo($Auth->level == 'free user')?t('uprade_account', 'upgrade account'):t('extend_account', 'extend account'); ?></a></li>
                            <?php endif; ?>
                            <li><a href="<?php echo WEB_ROOT; ?>/account_edit.<?php echo SITE_CONFIG_PAGE_EXTENSION; ?>"><?php echo t('settings', 'settings'); ?></a></li>
                            <li><a href="<?php echo WEB_ROOT; ?>/faq.<?php echo SITE_CONFIG_PAGE_EXTENSION; ?>"><?php echo t('faq', 'faq'); ?></a></li>
                            <li><a href="<?php echo WEB_ROOT; ?>/logout.<?php echo SITE_CONFIG_PAGE_EXTENSION; ?>"><?php echo t('logout', 'logout'); ?> (<?php echo $Auth->username; ?>)</a></li>
                        </ul>
                    <?php endif; ?>
                </div>
                <div class="section2">
                    <strong><?php echo t('legal_bits', 'Legal Bits'); ?></strong>
                    <ul>
                        <li><a href="<?php echo WEB_ROOT; ?>/terms.<?php echo SITE_CONFIG_PAGE_EXTENSION; ?>"><?php echo t('term_and_conditions', 'terms and conditions'); ?></a></li>
                        <li><a href="<?php echo WEB_ROOT; ?>/report_file.<?php echo SITE_CONFIG_PAGE_EXTENSION; ?>"><?php echo t('report_file', 'report file'); ?></a></li>
                    </ul>
                </div>
                <div class="clear"><!-- --></div>
                
            </div>
            <div class="clear"><!-- --></div>
            
            <div class="footerCopyrightText">
                <?php
                if(($Auth->loggedIn() == true) && ($Auth->level == 'admin'))
                {
                    echo '<strong>[ <a href="'.WEB_ROOT.'/admin/" target="_blank">'.t('admin_area', 'admin area').'</a> ]</strong><br/><br/>';
                }
                ?>
                <?php echo t("copyright", "copyright"); ?> &copy; <?php echo date("Y"); ?> - <?php echo SITE_CONFIG_SITE_NAME; ?> - <a href="https://yetishare.com" target="_blank">File Sharing Script</a> <?php echo t("created_by", "created by "); ?> <a href="https://mfscripts.com" target="_blank">MFScripts.com</a>
            </div>
            <div class="clear"><!-- --></div>
            
	</div>
    </div>
</body>
</html>
