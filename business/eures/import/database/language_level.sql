-- phpMyAdmin SQL Dump
-- version 3.2.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 12. Mai 2011 um 15:42
-- Server Version: 5.1.41
-- PHP-Version: 5.3.1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Datenbank: `eures`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `language_level`
--

CREATE TABLE IF NOT EXISTS `language_level` (
  `ilr_level` int(1) unsigned NOT NULL,
  `eng` set('elementary','basic','extremely limited','limited','limited working','fair','modest','competent','professional working','working knowledge','good','very good','full professional','fluent','expert','native','bilingual','mother tongue') NOT NULL,
  `deu` set('elementar','grundkenntnisse','einfach','basiswissen','schulkenntnisse','erweiterte grundkenntnisse','selbständig','begrenzt','angemessen','mittlere Kenntnisse','gut','fließend','konversationssicher','kompetent','verhandlungssicher','sehr gut','muttersprache','muttersprachlich') NOT NULL,
  `spa` set('nociones','elemental','basico','limitada','acceso','plataforma','limitada de trabajo','umbral','independiente','bueno','muy bueno','profesional de trabajo','dominio
fluido','profesional plena','maestría','nativa','bilingüe','idioma materno') NOT NULL,
  `por` set('elementar','básico','limitada','iniciante','profissional limitada','intermediário','independente','profissional','bom','muito bom','proficiente','fluente','profissional pleno','domínio pleno','
nativa','bilíngüe','língua materna') NOT NULL,
  `fra` set('introductif','découverte','seuil','indépendant','bon','trés bon','autonome','maîtrise','bilingue') NOT NULL,
  PRIMARY KEY (`ilr_level`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `language_level`
--

INSERT INTO `language_level` (`ilr_level`, `eng`, `deu`, `spa`, `por`, `fra`) VALUES
(
	1, 
	'elementary,basic,extremely limited,limited', 
	'elementar,grundkenntnisse,einfach,basiswissen,schulkenntnisse',
	'nociones,elemental,basico,limitada,acceso,plataforma',
	'elementar,básico,limitada,iniciante',
	'introductif,découverte'
),
(
	2, 
	'limited working,fair,modest,competent', 
	'erweiterte grundkenntnisse,selbständig,begrenzt,angemessen,mittlere Kenntnisse',
	'limitada de trabajo,umbral,independiente',
	'profissional limitada,intermediário,independente',
	'seuil,indépendant'
),
(
	3, 
	'professional working,working knowledge,good,very good', 
	'gut,fließend,konversationssicher',
	'bueno,muy bueno,profesional de trabajo,dominio',
	'profissional,bom,muito bom,proficiente',
	'bon,trés bon,autonome'
),
(
	4, 
	'full professional,fluent,expert', 
	'kompetent,verhandlungssicher,sehr gut',
	'fluido,profesional plena,maestría',
	'fluente,profissional pleno,domínio pleno',
	'maîtrise'
),
(
	5, 
	'native,bilingual,mother tongue', 
	'muttersprache,muttersprachlich',
	'nativa,bilingüe,idioma materno',
	'nativa,bilíngüe,língua materna',
	'bilingue'
);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
