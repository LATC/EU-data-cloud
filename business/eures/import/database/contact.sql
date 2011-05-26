CREATE TABLE contact (
	id INT UNSIGNED NOT NULL AUTO_INCREMENT,
	employer_id INT REFERENCES employer(id),
	country_code CHAR(2) REFERENCES country(id),
	information VARCHAR(255),
	email VARCHAR(255),
	fax VARCHAR(255),
	url TEXT,
	scraper_date DATE,
	scraper_hour TIME,
	PRIMARY KEY (id),
	UNIQUE (information(100), email(100),fax(50), country_code(2))
) DEFAULT CHARACTER SET utf8;