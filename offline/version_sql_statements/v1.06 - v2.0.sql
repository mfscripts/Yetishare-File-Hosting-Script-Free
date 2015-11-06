INSERT INTO `language_key` (`languageKey`, `defaultContent`, `isAdminArea`) VALUES ('optional_account_expiry', 'Paid Expiry Y-m-d (optional)', '1');
INSERT INTO `language_key` (`languageKey`, `defaultContent`, `isAdminArea`) VALUES ('account_expiry_invalid', 'Account expiry date is invalid. It should be in the format YYYY-mm-dd', '1');

DROP TABLE IF EXISTS `file_folder`;
CREATE TABLE `file_folder` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `folderName` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `file_server`
-- ----------------------------
DROP TABLE IF EXISTS `file_server`;
CREATE TABLE `file_server` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `serverLabel` varchar(100) NOT NULL,
  `serverType` enum('remote','local') DEFAULT NULL,
  `ipAddress` varchar(15) NOT NULL,
  `connectionMethod` enum('ftp') NOT NULL DEFAULT 'ftp',
  `ftpPort` int(11) NOT NULL DEFAULT '21',
  `ftpUsername` varchar(50) NOT NULL,
  `ftpPassword` varchar(50) DEFAULT NULL,
  `statusId` int(11) NOT NULL DEFAULT '1',
  `storagePath` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `statusId` (`statusId`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `file_server_status`
-- ----------------------------
DROP TABLE IF EXISTS `file_server_status`;
CREATE TABLE `file_server_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of file_server_status
-- ----------------------------
INSERT INTO `file_server_status` VALUES ('1', 'disabled');
INSERT INTO `file_server_status` VALUES ('2', 'active');
INSERT INTO `file_server_status` VALUES ('3', 'read only');

-- ----------------------------
-- Records of file_server
-- ----------------------------
INSERT INTO `file_server` VALUES ('1', 'Local Default', 'local', '', '', '0', '', null, '2', null);

ALTER TABLE `file` ADD `folderId` INT( 11 ) NULL DEFAULT NULL;

INSERT INTO `site_config` (`config_key`, `config_value`, `config_description`, `availableValues`, `config_type`, `config_group`) VALUES ('file_url_show_filename', 'no', 'Show the original filename on the end of the generated url.', '[\"yes\",\"no\"]', 'select', 'File Uploads');

ALTER TABLE `file`
ADD COLUMN `serverId`  int(11) DEFAULT 1 AFTER `folderId`;

INSERT INTO `language_key` (`languageKey`, `defaultContent`, `isAdminArea`) VALUES ('admin_file_servers', 'File Servers', '1');
INSERT INTO `language_key` (`languageKey`, `defaultContent`, `isAdminArea`) VALUES ('ftp_host', 'FTP Ip Address', '1');
INSERT INTO `language_key` (`languageKey`, `defaultContent`, `isAdminArea`) VALUES ('ftp_port', 'FTP Port', '1');

INSERT INTO `site_config` VALUES ('49', 'default_file_server', 'Local Default', 'The file server to use for all new uploads. Only used if \'active\' state and \'server selection method\' is \'specific server\'.', 'SELECT serverLabel AS itemValue FROM file_server LEFT JOIN file_server_status ON file_server.statusId = file_server_status.id ORDER BY serverLabel', 'select', 'File Uploads');
INSERT INTO `site_config` VALUES ('50', 'c_file_server_selection_method', 'Least Used Space', 'Server selection method. How to select the file server to use.', '[\"Least Used Space\",\"Specific Server\"]', 'select', 'File Uploads');