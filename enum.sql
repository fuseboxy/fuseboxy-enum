CREATE TABLE IF NOT EXISTS `enum` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(50) DEFAULT NULL,
  `key` varchar(200) DEFAULT NULL,
  `value` text,
  `remark` text,
--`value__zh_hk` text,
--`value__zh_cn` text,
--`remark__zh_hk` text,
--`remark__zh_cn` text,
  `seq` int(11) DEFAULT NULL,
  `disabled` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `key` (`key`),
  KEY `disabled` (`disabled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;