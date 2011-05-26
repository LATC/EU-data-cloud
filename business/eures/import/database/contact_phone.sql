CREATE TABLE contact_phone
(
	contact_id INT NOT NULL REFERENCES contact(id),
	number VARCHAR(255) NOT NULL,
	PRIMARY KEY (contact_id,number)
);