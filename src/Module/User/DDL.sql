CREATE TABLE `tb_user` (
  `seq` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'PK',
  `id` varchar(100) NOT NULL COMMENT 'UK',
  `cash` int(10) unsigned NOT NULL DEFAULT '0',
  `cash_free` int(10) unsigned NOT NULL DEFAULT '0',
  `date_create` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_login` timestamp NULL DEFAULT NULL,
  `date_logout` timestamp NULL DEFAULT NULL,
  `date_block` timestamp NULL DEFAULT NULL,
  `date_delete` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`seq`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB;