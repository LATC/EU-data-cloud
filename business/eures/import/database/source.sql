CREATE TABLE IF NOT EXISTS `source` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255),
  `acronym` VARCHAR(10),
  `name_clean` VARCHAR(255),
  `eng` VARCHAR(255),
  `country_code` varchar(2) REFERENCES country(code),
  `local_id` int(2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`(255))
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=27 ;

--
-- Dumping data for table `source`
--

INSERT INTO `source` (`id`, `name`, `acronym`, `name_clean`, `eng`, `country_code`, `local_id`) VALUES
(1, 'EURES Central Database', 'EURES', 'European Employment Services', 'European Employment Services', '', 1),
(2, 'AMS, Public Employment Services, Austria', 'AMS', 'Arbeitmarktservice', 'Austrian Public Employment Service', 'AT', 1),
(3, 'Pôle Emploi, Public Employment Services, France', 'PE', 'Pôle Emploi', 'Employment center', 'FR', 1),
(4, 'Bolzano-Alto Adige/Bozen-Südtirol, Public Employment Services', '', 'Bolzano-Alto Adige/Bozen-Südtirol', '', 'IT', 1),
(5, 'AMS, Public Employment Services, Denmark', 'AMS', 'Arbejdsmarkedsstyrelsen', 'Public Employment Service', 'DK', 1),
(6, 'VDAB, Public Employment Services, Belgium', 'VDAB', 'Vlaamse Dienst voor Arbeidsbemiddeling en Beroepsopleiding', '', 'BE', 1),
(7, 'BA, Public Employment Services, Germany', 'BA', 'Bundesagentur für Arbeit', 'Federal Employment Agency', 'DE', 1),
(8, 'YEKA/MLSI, Public Employment Services, Cyprus', 'YEKA', 'Ypourgeio Ergasias kai Koinonikon Asfaliseon', 'Ministry of Labor and Social Insurance', 'CY', 1),
(9, 'MPSV, Public Employment Services, Czech Republic', 'MPSV', 'Ministerstvo práce a sociálních věcí', 'Ministry of Labour and Social Affairs', 'CZ', 1),
(10, 'OAED, Public Employment Services, Greece', 'OAED', '', 'Greek Manpower Employment Organization', 'GR', 1),
(11, 'VMST, Public Employment Services, Iceland', 'VMST', '', '', 'IS', 1),
(12, 'Actiris, Public Employment Services, Belgium', 'ACTIRIS', 'Actiris', '', 'BE', 2),
(13, 'MOL/Työ- ja elinkeinoministeriö, Public Employment Services, Finland', 'MOL', 'MOL/Työ- ja elinkeinoministeriö', '', 'FI', 1),
(14, 'LDB, Public Employment Services, Lithuania', 'LDB', '', '', 'LT', 1),
(15, 'ADEM, Public Employment Services, Luxembourg', 'ADEM', '', '', 'LU', 1),
(16, 'ETC, Public Employment Services, Malta', 'ETC', '', '', 'MT', 1),
(17, 'JOBCENTRE PLUS, Public Employment Services, United Kingdom', 'JCP', 'JobCentre Plus', 'JobCentre Plus', 'UK', 1),
(18, 'UWV WERKbedrijf, Public Employment Services, Netherlands', 'UWV', 'UWV WERKbedrijf', '', 'NL', 1),
(19, 'NAV, Public Employment Services, Norway', 'NAV', '', '', 'NO', 1),
(20, 'PSZ, Public Employment Services, Poland', 'PSZ', '', '', 'PL', 1),
(21, 'UPSVAR, Public Employment Services, Slovakia', 'UPSVAR', '', '', 'SK', 1),
(22, 'ZRSZ, Public Employment Services, Slovenia', 'ZRSZ', '', '', 'SI', 1),
(23, 'Public Employment Service of Spain', 'INEM', 'Instituto Nacional de Empleo', 'National Institute of Employment', 'ES', 1),
(24, 'Arbetsförmedlingen, Public Employment Services, Sweden', 'AF', 'Arbetsförmedlingen', '', 'SE', 1),
(25, 'SECO, Public Employment Services, Switzerland', 'SECO', '', '', 'CH', 1),
(26, 'DEL, Public Employment Services, Northern Ireland', 'DEL', '', '', 'IE', 1);

-- --------------------------------------------------------






 


































































