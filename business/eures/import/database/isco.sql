CREATE TABLE IF NOT EXISTS `isco` (
  `code` varchar(10) NOT NULL,
  `name` varchar(1000) DEFAULT NULL,
  `major_code` varchar(10) DEFAULT NULL,
  `submajor_code` varchar(10) DEFAULT NULL,
  `minor_code` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;