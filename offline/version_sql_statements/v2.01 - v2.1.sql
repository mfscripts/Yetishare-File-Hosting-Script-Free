ALTER TABLE `file_folder`
ADD COLUMN `isPublic`  int(1) NOT NULL DEFAULT 0 AFTER `folderName`,
ADD COLUMN `accessPassword`  varchar(32) NULL AFTER `isPublic`;

ALTER TABLE `users`
ADD COLUMN `passwordResetHash`  varchar(32) NULL AFTER `paymentTracker`;

INSERT INTO `site_config` (`config_key`, `config_value`, `config_description`, `availableValues`, `config_type`, `config_group`) VALUES ('free_user_show_captcha', 'no', 'Show the captcha after a free user sees the countdown timer.', '[\"yes\",\"no\"]', 'select', 'Captcha');
INSERT INTO `site_config` (`config_key`, `config_value`, `config_description`, `availableValues`, `config_type`, `config_group`) VALUES ('captcha_private_key', '6LeuAc4SAAAAAL71eifhISYsbL-yPTtNZVnXTHVt', 'Private key for captcha. Register at https://www.google.com/recaptcha', '', 'string', 'Captcha');
INSERT INTO `site_config` (`config_key`, `config_value`, `config_description`, `availableValues`, `config_type`, `config_group`) VALUES ('captcha_public_key', '6LeuAc4SAAAAAOSry8eo2xW64K1sjHEKsQ5CaS10', 'Public key for captcha. Register at https://www.google.com/recaptcha', '', 'string', 'Captcha');
