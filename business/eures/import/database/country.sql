CREATE TABLE IF NOT EXISTS `country` (
  `code` char(2) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `dbpedia_uri` varchar(255) NOT NULL,
  PRIMARY KEY (`code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;