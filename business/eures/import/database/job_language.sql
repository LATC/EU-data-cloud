CREATE TABLE job_language
(
	job_id INT REFERENCES job(id),
	iso639p3 VARCHAR(3) REFERENCES language(iso639p3),
	ilr_level INT(1) REFERENCES language_level(ilr_level),
	PRIMARY KEY (job_id,iso639p3,ilr_level)
);