CREATE TABLE employer (
	id INT UNSIGNED NOT NULL AUTO_INCREMENT,
	name TEXT,
	address_id INT(10) REFERENCES geo(id),
	homepage VARCHAR(255),
	scraper_date DATE,
	scraper_hour TIME,
	PRIMARY KEY (id)
) DEFAULT CHARACTER SET utf8;
