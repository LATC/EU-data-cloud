CREATE TABLE IF NOT EXISTS `language_level` (
  `ilr_level` int(1) unsigned NOT NULL,
  `eng` set('elementary','basic','extremely limited','limited','limited working','fair','modest','competent','professional working','working knowledge','good','very good','full professional','fluent','expert','native','bilingual','mother tongue') NOT NULL,
  `deu` set('elementar','grundkenntnisse','einfach','basiswissen','schulkenntnisse','erweiterte grundkenntnisse','selbständig','begrenzt','angemessen','mittlere Kenntnisse','gut','fließend','konversationssicher','kompetent','verhandlungssicher','sehr gut','muttersprache','muttersprachlich') NOT NULL,
  `spa` set('nociones','elemental','basico','limitada','acceso','plataforma','limitada de trabajo','umbral','independiente','bueno','muy bueno','profesional de trabajo','dominio
fluido','profesional plena','maestría','nativa','bilingüe','idioma materno') NOT NULL,
  `por` set('elementar','básico','limitada','iniciante','profissional limitada','intermediário','independente','profissional','bom','muito bom','proficiente','fluente','profissional pleno','domínio pleno','
nativa','bilíngüe','língua materna') NOT NULL,
  `fra` set('introductif','découverte','seuil','indépendant','bon','trés bon','autonome','maîtrise','bilingue') NOT NULL,
  labels TEXT,
  PRIMARY KEY (`ilr_level`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

