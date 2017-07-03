CREATE TABLE `tb_version` (
  `major` tinyint(3) unsigned NOT NULL COMMENT 'major.x.x',
  `minor` tinyint(3) unsigned NOT NULL COMMENT 'x.minor.x',
  `micro` tinyint(3) unsigned NOT NULL COMMENT 'x.x.micro',
  `shop` varchar(20) NOT NULL DEFAULT 'Google' COMMENT 'platform (Google, Apple, ...)',
  `status` tinyint(1) unsigned DEFAULT '1' COMMENT '{"0":"asis","1":"service","2":"tobe","3":"update"}',
  `stable` tinyint(1) unsigned DEFAULT '1' COMMENT '{"0":"no","1":"yes"}',
  `checksum` varchar(128) DEFAULT NULL,
  `resource` text COMMENT 'json strings for additional informations',
  `date_create` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_check` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`major`,`minor`,`micro`,`shop`),
  KEY `status` (`status`),
  KEY `stable` (`stable`)
) ENGINE=InnoDB;