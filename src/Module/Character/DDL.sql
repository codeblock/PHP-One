CREATE TABLE `tb_character` (
  `seq` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'PK',
  `name` varchar(100) NOT NULL COMMENT 'UK',
  `user_seq` bigint(20) unsigned NOT NULL COMMENT 'FK - tb_user.seq',
  `gold` int(10) unsigned NOT NULL DEFAULT '0',
  `gold_free` int(10) unsigned NOT NULL DEFAULT '0',
  `date_create` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_login` timestamp NULL DEFAULT NULL,
  `date_logout` timestamp NULL DEFAULT NULL,
  `date_block` timestamp NULL DEFAULT NULL,
  `date_delete` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`seq`),
  UNIQUE KEY `name` (`name`),
  KEY `user_seq` (`user_seq`)
) ENGINE=InnoDB;