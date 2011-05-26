CREATE TABLE IF NOT EXISTS source (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  name VARCHAR(255),
  acronym VARCHAR(10),
  name_clean VARCHAR(255),
  eng VARCHAR(255),
  country_code varchar(2) REFERENCES country(code),
  local_id int(2) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY name (name(255))
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;