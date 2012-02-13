<?php
$timestamp = time();
$date = date("d-m-Y",($timestamp));
$time_end = strtotime($date);
$start_date = "01-01-2010";
$time_start = strtotime($start_date);
$username="root";
$password="Traxdata1";
$database="euraxess";
//$ids_array = array();
$query_array = array();
$affected_array = array();

//Connect to local database
mysql_connect(localhost,$username,$password) or die("Unable to connect to database");
$create_database = "CREATE DATABASE `euraxess`;";
mysql_query($create_database);
mysql_select_db($database) or die("Unable to select database");
$create_career_stage = "CREATE TABLE IF NOT EXISTS `career_stage` (
  `ID` int(255) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
mysql_query($create_career_stage);
$create_company = "
CREATE TABLE IF NOT EXISTS `company` (
  `ID` int(255) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `address` blob NOT NULL,
  `fax` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `website` blob NOT NULL,
  `country` varchar(255) NOT NULL,
  `state` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `postal_code` varchar(255) NOT NULL,
  `street` varchar(255) NOT NULL,
  `community_language` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
mysql_query($create_company);
$create_company_phone = "
CREATE TABLE IF NOT EXISTS `company_phone` (
  `company_ID` int(255) NOT NULL,
  `phone_number` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
mysql_query($create_company_phone);
$create_contract_type = "CREATE TABLE IF NOT EXISTS `contract_type` (
  `ID` int(255) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
mysql_query($create_contract_type);
$create_degree = "CREATE TABLE IF NOT EXISTS `degree` (
  `ID` int(255) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
mysql_query($create_degree);
$create_degree_field = "CREATE TABLE IF NOT EXISTS `degree_field` (
  `ID` int(255) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
mysql_query($create_degree_field);
$create_degree_level = "CREATE TABLE IF NOT EXISTS `degree_level` (
  `ID` int(255) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
mysql_query($create_degree_level);
$create_framework_programme = "CREATE TABLE IF NOT EXISTS `framework_programme` (
  `ID` int(255) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
mysql_query($create_framework_programme);
$create_job = "CREATE TABLE IF NOT EXISTS `job` (
  `ID` int(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `summary` blob NOT NULL,
  `description` blob NOT NULL,
  `additional_details` blob NOT NULL,
  `additional_requirements` blob NOT NULL,
  `contract_type_ID` int(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `hours_per_week` int(11) NOT NULL,
  `company_ID` int(255) NOT NULL,
  `application_deadline` date NOT NULL,
  `comment_website` blob NOT NULL,
  `education_level_ID` int(11) NOT NULL,
  `benefits` blob NOT NULL,
  `sesame_agreement_number` int(255) NOT NULL,
  `framework_programme_ID` int(255) NOT NULL,
  `application_starting_date` date NOT NULL,
  `application_website` blob NOT NULL,
  `application_email` varchar(255) NOT NULL,
  `research_sub_field_ID` int(255) NOT NULL,
  `years_of_experience` int(255) NOT NULL,
  `date_posted` date NOT NULL,
  `original_url` varchar(255) NOT NULL,
  `research_experience` varchar(255) NOT NULL,
  `research_sub_experience` varchar(255) NOT NULL,
  `how_to_apply` varchar(255) NOT NULL,
  `country` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
mysql_query($create_job);
$create_job_career_stage = "CREATE TABLE IF NOT EXISTS `job_career_stage` (
  `job_ID` int(255) NOT NULL,
  `career_stage_ID` int(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
mysql_query($create_job_career_stage);
$create_job_degree = "CREATE TABLE IF NOT EXISTS `job_degree` (
  `job_ID` int(255) NOT NULL,
  `degree_ID` int(255) NOT NULL,
  `degree_field_ID` int(255) NOT NULL,
  `degree_level_id` int(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
mysql_query($create_job_degree);
$create_job_required_languages = "CREATE TABLE IF NOT EXISTS `job_required_languages` (
  `job_ID` int(255) NOT NULL,
  `language_iso639p3` varchar(255) NOT NULL,
  `language_ilr_level` int(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
mysql_query($create_job_required_languages);
$create_job_requirements = "CREATE TABLE IF NOT EXISTS `job_requirements` (
  `job_ID` int(255) NOT NULL,
  `requirement` blob NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
mysql_query($create_job_requirements);
$create_job_research_fields = "CREATE TABLE IF NOT EXISTS `job_research_fields` (
  `job_ID` int(255) NOT NULL,
  `research_field_ID` int(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
mysql_query($create_job_research_fields);

$create_research_field = "CREATE TABLE IF NOT EXISTS `research_field` (
  `ID` int(255) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
mysql_query($create_research_field);
$create_statistics = "CREATE TABLE IF NOT EXISTS `statistics` (
  `ID` int(255) NOT NULL AUTO_INCREMENT,
  `timestamp` varchar(255) NOT NULL,
  `career_stage` int(255) NOT NULL,
  `company` int(255) NOT NULL,
  `company_phone` int(255) NOT NULL,
  `contract_type` int(255) NOT NULL,
  `degree` int(255) NOT NULL,
  `degree_field` int(255) NOT NULL,
  `degree_level` int(255) NOT NULL,
  `framework_programme` int(255) NOT NULL,
  `job` int(255) NOT NULL,
  `job_career_stage` int(255) NOT NULL,
  `job_degree` int(255) NOT NULL,
  `job_required_languages` int(255) NOT NULL,
  `job_requirements` int(255) NOT NULL,
  `job_research_fields` int(255) NOT NULL,
  `language` int(255) NOT NULL,
  `language_level` int(255) NOT NULL,
  `research_field` int(255) NOT NULL,
  `todo_job_required_languages` int(255) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
";
mysql_query($create_statistics);

$fill_statistics = "
INSERT INTO statistics(timestamp,career_stage,company,company_phone,contract_type,degree,degree_field,degree_level,framework_programme,job,job_career_stage,job_degree,job_required_languages,job_requirements,job_research_fields,language,language_level,research_field) VALUES ('".$date."','0','0','0','0','0','0','0','0','0','0','0','0','0','0','0','0','0')";
mysql_query($fill_statistics);
$create_language = "
CREATE TABLE `language` (
  `iso639p3` varchar(10) NOT NULL DEFAULT '',
  `iso639p1` varchar(2) DEFAULT NULL,
  `labels` text NOT NULL,
  `dbpedia_uri` text NOT NULL,
  `eng` text,
  PRIMARY KEY (`iso639p3`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
mysql_query($create_language);
$fill_language= "INSERT INTO `language` VALUES ('nav','nv','navajo;navajo language;navac\'hoeg;நாவஹோ ம௚ழி;ペ�?ホ語;basa navajo;idioma navajo;navaho;나바호어;lingua navajo;נ�?וו�?חו;navaha lingvo','http://dbpedia.org/resource/Navajo_language','Navajo; Navaho'),('tsn','tn','tswana;tswaneg;tsuana;tswanan kieli;蜨瓦纳语;tsvana keel;ツワペ語;setsuana;tswana language;setswana;bahasa tswana;lingua tswana;bahasa setswana;tswana simi;cvana lingvo;tswana dili;tiếng tswana;tswanum;gjuha tsvana;츠와나어','http://dbpedia.org/resource/Tswana_language','Tswana'),('hun','hu','ungairis;ungarsk;हंगेरियन भाषा;হাঙ�?গেরীয় ভাষা;�?가리어;ungerska;ungari kiil;lingua ungherese;ungarisch;ungari keel;kihungaria;magyar nyelv;hungareg;unkarin kieli;macar dili;lingua hungare;圈牙利语;hongaars;hongrois;�?ונג�?ריש;lingua ungaraisa;unriya simi;bahasa hongaria;hungarek;lingua ungarisa;அங�?கேரிய ம௚ழி;hwngareg;hungariera;hongaarsk;უნგრული ენ�?;lingua hungarica;�?ンガリー語;pinulongang unggaro;idioma hongaro;tiếng hungary;hungarian language;hungarian;gagana hungary;hungariana linguo;gjuha hungareze;ungverska;macar tili;basa hongaria;bahasa hungary;ภาษาฮัง�?ารี;tok hangari;macarca;hungara lingvo;hungarian leid;הונגרית;ungaarish','http://dbpedia.org/resource/Hungarian_language','Hungarian'),('hrv','hr','kroaziera;lengua croata;ხ�?რვ�?ტული ენ�?;croata;क�?रो�?शियन भाषा;basa kroasia;�?�로아티아어;pinulongang krowata;lingua croata;kroatisch;lingua cruata;קר�?�?טיש;hrvatski jezik;croat;kroaatsch;croato;xorvat dili;croatian language;croate;ক�?রোয়েশীয় ভাষা;gjuha kroate;kroatisk;croatie leid;croatais;bahasa kroasia;krowodische sproch;クロア�?ア語;bahasa croatia;gagana croatian;קרו�?טית;kroaties;hurwat simi;horvaadi keel;kroatiska;kroateg;ภาษาโครเอเบีย;idioma croata;kroata lingvo;kroatek;croatian;croateg;isi-croatia;克罗地亚语;kroatian kieli','http://dbpedia.org/resource/Croatian_language','Croatian'),('sot','st','塞索托语;sota lingvo;sesotho;sotho du sud;suid-sotho;सिसोथो भाषा;zuid-sotho;sothoeg;솜토어;sutum;ソト語;sesoto kalba;bahasa sesotho;gjuha sesote;sesotho simi;sotho language;sesotho do sul;southern sotho;lengua sesotho;isisotho;isisuthu;sotho, southern;sotho;lingua sesotho','http://dbpedia.org/resource/Sotho_language','sotho language'),('glv','gv','manx;מ�?נית;mannois;manx gaelic;gaelg;맨어;lingua monensis;mansk;lengua manx;manaveg;manksin kieli;manowek;マン島語;gaeilge mhanann;manski jezik;manksa lingvo;manaweg;lingua manx;limba manx;manska;manx-gaelisch;bahasa manx;曼島語;adam dili;manx-gaelies;godka manx;manx gaelic leid;manx language;manxera;მენური ენ�?;lingua mannese;manx nyelv','http://dbpedia.org/resource/Manx_language','Manx'),('tha','th','tai linguo;lingua thai;thai;bahasa thai;থাই ঠার;thaish;lingua thailandese;থাই ভাষা;泰文;泰语;vahdai;tai keel;タイ語;thay simi;थाई भाषा;타�?�어;thai language;kithai;ພາສາໄທ;lingua tai;thain kieli;ภาษาไทย;thai nyelv;tayca;tajlandski jezik;taja lingvo;ტ�?ი ენ�?','http://dbpedia.org/resource/Thai_language','Thai'),('lug','lg','bahasa luganda;luganda;ganda language;לוגנדה;ganda simi;gandan kieli;ganda;ルガンダ語;�?�干达语;gandaeg;ganda lingvo;idioma luganda','http://dbpedia.org/resource/Ganda_language','ganda language'),('ful','ff','peul;fulah','','Fulah'),('lim','li','lingua limburgica;bahasa limburgish;lingua limburghese;לימבורגית;limburger;lengua limburgheise;limbourgek;limburgs;limborgsch;limburgisch;limburgan;limburgisc;ሚ�?ቡር�?᚛;limbourgeois;limburgish;林堡语;limburgiska;limburgsk;limburga lingvo;lingua limburguesa;limbuurchsk;リンブルフ語;limburchsk;gjuha limburge;limburgera;limbourgeg','http://dbpedia.org/resource/Limburgish','Limburgish'),('aym','ay','aymara','','Aymara'),('nor','no','norwegian;norwegisch;norvegian','','Norwegian'),('ile','ie','interlingue;occidental language;occidental;oktsidentaal;インターリング;ᚢᚕተርሚᚕ�?ዜ;okcidentalo;interlingue (occidental) lingue;idioma occidental;lingua occidental;occidental nyelv','http://dbpedia.org/resource/Occidental_language','Occidental'),('chv','cv','bahasa chuvash;idioma chuvasio;tchouvache;tschuwaschisch;tschuwasch’sche spraak;txuvaix;tjuvasjiska;an tsuvaisis;chuwash simi;楚瓦什語;च�?वाश भाषा;lingua ciuvascia;tsjuvasjisk;chuvash;chuvash language;csuvas nyelv;ภาษาบูวับ;tsjoevasjisch;tchouvacheg;ჩუვ�?შური ენ�?;צ\'ובשית;추바시어;�?ュヴァシ語','http://dbpedia.org/resource/Chuvash_language','Chuvash'),('mkd','mk','मेसिडोनियन भाषा;ภาษามาซิโดเนีย;makedonsk;mazedonisch;makedon dili;makedonski jezik;idioma macedonio;macedonie leid;マケドニア語;mazedoniera;lingua macedonia;lingua macedonica moderna;makedoniagiella;מקדונית;makidunya simi;makedonska;makedoonia keel;bahasa macedonia;macedonisch;macedoniana linguo;lingua macedone;macedoniu;makedonca;bahasa makedonia;macedonies;მ�?კედ�?ნური ენ�?;makedonek;makedonian kieli;马其顿语;마케�?�니아어;makedona lingvo;macedonian language;macedonian;gjuha sllavomaqedone','http://dbpedia.org/resource/Macedonian_language','Macedonian'),('ave','ae','lingua avestana;avestisk;avestiska;avestan language;ภาษาอเวสตะ;avestisch;avesties;avestique;idioma abestico;অবেস�?তা ভাষা;avesta jezik;avesteg;pinulongang abestiko;அவெஸ�?தான�? ம௚ழி;awesta dili;avesta;avestos kalba;阿維斯陀語;avestan;bahasa avesta;アヴェスター語;lingua avestica','http://dbpedia.org/resource/Avestan_language','Avestan'),('ita','it','�?�탈리아어;iddaalish;italya simi;italiensk;eadailtis;lengua italiana;wikang italyano;italian;lingua taliana;luenga italiana;italien;italijanski jezik;იტ�?ლიური ენ�?;talijanski jezik;itala lingvo;basa italia;�?יטלקית;italiaans;bahasa itali;italian kieli;italianeg;tok itali;olasz nyelv;italsko godka;isitalian;italek;lingua italiana;italian leid;initalyano;gjuha italiane;italienska;இத�?தாலிய ம௚ழி;lenghe taliane;�?大利語;इतालवी भाषा;kiitalia;ইতালীয় ভাষা;lingwa taljana;ইতালীয় ঠার;basa itali;lenga italian-a;italiany;limba italiana;�?大利話;�?יט�?ליעניש;ภาษาอิตาลี;�?大利语;italiian;pinulongang italyanhon;idioma italiano;italieensch;italienisch;italiano;italjaansk;lingua italian;italiera;イタリア語;italiaons;bahasa italia;eidaleg;itaalia keel;italian language;italiana linguo;italu kalba;ഇറ�?റാലിയൻ ഭാഷ;ཡི་�?་ལིའི་ས�?ད�?','http://dbpedia.org/resource/Italian_language','Italian'),('ido','io','ido;bahasa ido;会多语;lingwa ido;ईदो;lingua ido;�?��?�;�?ידו;ido nyelv;ido leid;gjuha ido;იდ�?;ภาษาอิดอ;kiido;イド語','http://dbpedia.org/resource/Ido','Ido'),('tat','tt','타타르어;tatariska;tataru;tatarisk;ภาษาตาตาร๜;lingua tatarica;თ�?თრული ენ�?;tatarca;tatar tele;tataars;鞑�?�语;tatarski jezik;bahasa tatar;tatar;tatariske sproake;tatar tili;lingua tatara;tatarisch;tatar dili;tatara lingvo;tatar language;tatareg;タタール語;tataarin kieli;טטרית','http://dbpedia.org/resource/Tatar_language','Tatar'),('swa','sw','swahili (macrolanguage);swahili','','Swahili'),('uzb','uz','ouszbek;uzbek','','Uzbek'),('kau','kr','kanuri;kanouri','','Kanuri'),('fry','fy','westerlauwers fries;vestfrisisk;freeshlannish heear;frison occidental;frysk;idioma frisio ozidental;西弗釜斯语;westerlauwersfrisisk;lenga frison-a ossidental;west frisian language;westlaauwers frais;westfriesisch;dialetto frisone occidentale;okcidentfrisa lingvo;western frisian','http://dbpedia.org/resource/West_Frisian_language','Western Frisian'),('bak','ba','lingua baskir;बाश�?किर भाषा;basjkirsk;bashkir language;바시키르어;idioma baskir;ภาษาบับคีร๜;bachkir;basjkiers;bachkireg;�?シキール語;baschkirisch;baixkir;bashkir;bahasa bashkir;basjkiriska;lingua baschira','http://dbpedia.org/resource/Bashkir_language','Bashkir'),('ipk','ik','inupiaq;inupiak;bahasa inupiaq;inupiatun;inupiaka lingvo;inupiaq language','http://dbpedia.org/resource/Inupiaq_language','Inupiaq'),('hin','hi','hindi;hindi nyelv;הינדי;ჰინდი ენ�?;ภาษาฮินดี;indi;இந�?தி;རྒྱ་གར་ས�?ད�?;kihindi;limba hindi;ਹਿੰਦੀ ਭਾਸ਼ਾ;hindeg;fiteny hindi;�?�地语;हिन�?दी;hindski jezik;bahasa hindi;hindi keel;ഹിന�?ദി;हिंदी भाषा;హిందీ భాష;힜디어;lengua hindi;lingua hindi;হিন�?দী ঠার;हिन�?दीकानी छीब;હિંદી ભાષા;ヒンディー語;hindia lingvo;hind dili;pinulongang indi;�?�地語;hinndi;hindigiella;ಹಿಂದಿ;हिन�?दी भाषा;hindi linguo;হিন�?দি ভাষা;tiếng hindi;hindi simi;hindjan;�?��?��?�?/hintii;lingua indiana;hindi language','http://dbpedia.org/resource/Hindi','Hindi'),('lat','la','latinki;latin;lingua latin;latina;latinski jezik;lenga latin-a;latijn;lotin tili;ലാറ�?റിൻ;bahasa latin;latin nyelv;latin language;拉�?文;latyn;ラテン語;拉�?語;basa latin;an laidin;lingua latina;gjuha latine;ladjyn;laideann;ladina keel;latien;latein;pinulongang latin;ლ�?თინური ენ�?;latinum;kilatini;latiensk;tok latin;laitin leid;luenga latina;拉�?話;latynsk;ሮማይስᜥ;ภาษาละติน;linatin;latinh;लातिन भाषा;לטינית;lengua latinn-a;lenghe latine;latina lingvo;lengua latin;இலத�?தீன�?;latince;লাতিন ভাষা;lengua latina;�?�틴어;limba latina;wikang latin;lladin;latiensche spraak;tataramon na latin;latin simi;拉�?语;latim;ພາສາລາ�?ຕັງ','http://dbpedia.org/resource/Latin','Latin'),('gla','gd','bahasa gaelik skotlandia;gaeli keel;scottish gaelic language;sjots gaelic;�?格兰盖尔语;limba gaelica scotzesa;iskut kilta simi;schots-gaelisch;gaelic;gaelich scuzzes;შ�?ტლ�?ნდიური ენ�?;scottish gaelic;욤코틀랜드 겜�?�어;gaeilge na halban;lingua scotica;gaelg albinagh;lingua gaelica scozzese;lengua gaelica scosseise;スコットランド・ゲール語;gouezeleg skos;skots-gaelies;ג�?לית סקוטית;eskoziako gaelera;gaeleg yr alban;skotgaela lingvo;scots gaelic leid;albanek;gaelagiella;gaeli;szkocko gaelicko godka','http://dbpedia.org/resource/Scottish_Gaelic','Scottish Gaelic'),('gle','ga','iriska;lengua irlandeise;irski jezik;irlandais;iers;tok aialan;আইরিশ ভাষা;gaelicu irlandiesu;an ghaeilge;yernish;आयरिश भाषा;bahasa irlandia;lenghe irlandese;irish;irsk;�?יריש;iirragiella;irisch;愛爾蘭語;lingua irlandaisa;tirlandit;irlanda lingvo;lingua irlandese;erse leid;lingua irlandesa;iersk;�?ירית;gwyddeleg;아�?�랜드어;iers-gaelies;iwerdhonek;ilanda simi;irische sproch;iwerzhoneg;gaelera;irish language;gaelana linguo;wikang irlandes;iiri;アイルランド語;gjuha irlandeze;lingua hibernica;iiri keel','http://dbpedia.org/resource/Irish_language','Irish'),('aze','az','azerbaijani','','Azerbaijani'),('sna','sn','kishona;choneg;shona;shonum;ショペ語;শোনা ভাষা;lingua shona;shona language;idioma shona;bahasa shona;shona simi;சோனா ம௚ழி','http://dbpedia.org/resource/Shona_language','Shona'),('sun','su','sundanesisch;სუნდური ენ�?;bahasa sunda;basa sundha;basa sunda;swndaneg;スンダ語;sundanesisk;sundanese language;sundanesiska;lengua sundaneixe;sundan kieli;sundanais;lingua sondanese;ภาษาซุนดา;soundanais;sundanese;tiếng sunda;sundski jezik;巽他語;luenga sondanesa;स�?न�?दा भाषा;soendanees;sundu valoda;sunda lingvo;순다어;sunda simi','http://dbpedia.org/resource/Sundanese_language','Sundanese'),('cha','ch','chamorro;�?ャモロ語;lingua chamorro;bahasa chamorro;lingua chamorra;gagana chamorro;idioma chamorro;lengua chamorro;차모로어;chamorro jezik;fino\' chamoru;chamoru;tchamoroueg;chamorro language','http://dbpedia.org/resource/Chamorro_language','Chamorro'),('pus','ps','pashto;pushto;pachto','','Pushto; Pashto'),('mar','mr','marathi;మరాఠీ భాష;marathi language;মারাঠি ঠার;lingua marathica;ಮರಾಠಿ;limba marathi;მ�?რ�?თჰი;मराठी भाषा;ภาษามรา�?ี;lengua marathi;马拉地语;마�?�티어;マラーティー語;bahasa marathi;marathi jezik;marathin kieli;marathi bhasa;மராத�?தி;marati;marathi simi;marati jezik;מרטהי;മറാഠി;મરાઠી;মারাঠি ভাষা;lingua marathi;marateg;marata lingvo;marathe','http://dbpedia.org/resource/Marathi_language','Marathi'),('tgl','tl','pagsasao a tagalog;tagalog;ფილიპინური ენ�?;tagaloga lingvo;タガログ語;tagalogeg;fiteny tagalog;tataramon na tagalog;tiếng tagalog;lingua tagalog;bahasa tagalog;tagalog nyelv;tinag-alog;他嚠祿語;wikang tagalog;তাগালোগ ভাষা;타갈로그어;tagalu simi;idioma tagalo;ภาษาตา�?าล็อ�?;tinagalog;lengua tagalog;tagalu;tagalog language','http://dbpedia.org/resource/Tagalog_language','Tagalog'),('tgk','tg','tadschiksche spraak;lingua taxica;lingua tadzikistanica;タジク語;தாஜிக�? ம௚ழி;타지�?�어;ტ�?ჯიკური ენ�?;taciki;tadzjikiska;tadschikisch;tayik simi;tadsjikisk;tajik;tadjik;塔�?�克语;bahasa tajik;tadzjieks;kitajiki;tacik tili;tajik language;lingua tagica;তাজিকিস�?তানের ভাষা;tadjikeg;idioma tayiko;ภาษาทาจิ�?;tacik dili','http://dbpedia.org/resource/Tajik_language','Tajik'),('iii','ii','sichuan yi;ภาษาอี้;�?語;यी (लोलो) भाषा;yi de sichuan;yi;nuosu language;yieg;nuosu;idioma yi;�?语','http://dbpedia.org/resource/Nuosu_language','Sichuan Yi; Nuosu'),('heb','he','hebrew','','Hebrew'),('lao','lo','bahasa lao;laotiaans;ภาษาลาว;lao language;bahasa laos;lao nyelv;लाओ भाषा;�?�오어;laotisch;laoca;lao;laon kieli;laotisk;law simi;�?�?语;ພາສາລາວ;idioma lao;লাও ভাষা;ラーオ語;lingua lao','http://dbpedia.org/resource/Lao_language','Lao'),('che','ce','idioma checheno;chechen;bahasa chechen;csecsen nyelv;צ\'צ\'נית;lingua cecena;체첸어;tschetschenisch;�?ェ�?ェン語;tsjetsjeens;tsjetsjensk;tjetjenska;lingua chechena;chechen language;tchetcheneg;軚臣語;tjetjensk;ჩ�?ჩნური ენ�?;ภาษาเบเบน','http://dbpedia.org/resource/Chechen_language','Chechen'),('bam','bm','�?�巴拉语;bambara;bambara keel;bambaran kieli;bambara simi;bambareg;lingua bambara;bambaru valoda;bamanankan;�?ン�?ラ語;tiếng bambara;bambara lingvo;밤바�?�어;bahasa bambara;idioma bambara;lenga bamanankan;பம�?பாரா ம௚ழி;bambara language','http://dbpedia.org/resource/Bambara_language','Bambara'),('aar','aa','afar language;afarski jezik;idioma afar;afar;lingua afar;afariko;afarin kieli;ᚠ�?�ር᚛;afara lingvo;gjuha afare;アファル語;अफ़ार भाषा;afareg;bahasa afar','http://dbpedia.org/resource/Afar_language','Afar'),('msa','ms','malay;malay (macrolanguage);malais','','Malay'),('tur','tr','basa turki;ภาษาตุร�?ี;lengua turca;turc;turkish bhasa;turksk;turski jezik;lingua turcica;turkish leid;idioma turco;turkiana linguo;turkeg;lingua turca;bahasa turki;tinurkiya;torku kalba;त�?र�?की भाषा;turkin kieli;turku simi;lingua turkana;turkiska;turcu;kituruki;turk tili;tyrkneska;tyrkisk;an tuircis;トルコ語;lingua tirca;wikang turko;turkish;gjuha turke;turku valoda;pinulongang turko;טורקית;turkek;თურქული ენ�?;tyrceg;ത�?ര�?�?ക�?കി ഭാഷ;turka lingvo;turecko godka;durkkagiella;turks;த�?ர�?க�?கிய ம௚ழி;turkish language;turkiera;터키어;bahsa tureuki;ত�?র�?কি ভাষা;trouk','http://dbpedia.org/resource/Turkish_language','Turkish'),('hmo','ho','hiri motu;hiri motu language;ヒリモツ語;hiri motu kalba;bahasa hiri motu;hirimotoueg;帜釜摩圖語;hirimotua lingvo;히리 모투;hiri motu nyelv','http://dbpedia.org/resource/Hiri_Motu_language','Hiri Motu'),('nld','nl','inolandes;iseldiryek;nederlandsk;lengua olandeise;nederlandum;isiholandi;felemenk tili;lingua nederlandese;ნიდერლ�?ნდური ენ�?;nederlandera;nederlandana linguo;ה�?לענדיש;オランダ語;�?�蘭語;lingua neerlandese;འཇར་མན་ས�?ད�?;niderland dili;nederlanda lingvo;bakratongo;ภาษาดัตบ๜;gjuha holandeze;डच भाषा;flemish;kiholanzi;lingua olandese;iseldireg;wikang olandes;nedderlandsche spraak;flemenki;se-dutch;dutch language;dutch;hollandi keel;dutch leid;nederlands;lingua ollandaisa;bahasa belanda;�?�蘭話;ollanish;lingua neerlandesa;idioma hulandes;lingua ulannisa;basa landa;duitsis;hollannin kieli;an ollainnis;lingua olandesa;네�?�란드어;nederlaands;ওলন�?দাজ ভাষা;holandski jezik;urasuyu simi;ഡച�?ച�? ഭാഷ;הולנדית;nederlandeg;nizozemski jezik;niederlaendische schprooch;lingua batava;niederloundisk;hollenska;holland nyelv;hollandais','http://dbpedia.org/resource/Dutch_language','Flemish'),('nde','nd','norda ndebela lingvo;ndebele;sindebele;ndebele, north;northern ndebele language;idioma ndebele del norte;north ndebele;nordndebele;noord-ndebele;isindebele;圗ンデベレ語','http://dbpedia.org/resource/Northern_Ndebele_language','Northern Ndebele language'),('yid','yi','yiddish','','Yiddish'),('nbl','nr','gjuha ndebele;south ndebele;sydndebele;suid-ndebele;lingua ndebele;southern ndebele language;nrebele;ndebeleg;suda ndebela lingvo;ndebele, south;zuid-ndebele;ndebelen kieli;�?�ンデベレ語;idioma ndebele del sur','http://dbpedia.org/resource/Southern_Ndebele_language','Southern Ndebele language'),('sme','se','lingua sami settentrionale;pohjoissaame;ስሜᚕ ሳሚ᚛;noord-samisch;nordsamisk;nord-samea lingvo;saameg gogleddol;sami septentrional;same du nord;lingua samica septentrionalis;northern sami;samieg an norzh;nordsamiska;sami du nord;nordsamisch;saamish hwoaie','http://dbpedia.org/resource/Northern_Sami','Northern Sami'),('ind','id','endonezce;gjuha indoneziane;indoneesia keel;bahasa indonesia;indonesiera;indonezijski jezik;indonesian kieli;idioma indonesio;indonesian;ภาษาอินโดนีเซีย;basa indonesia;�?ינדונזית;malaiische und indonesisch#geschichte;wikang indones;indonesisk;インド�?シア語;indonesiska;indonesisch;�?��?�네시아어;lingua indonesia;indonezeg;lengua indonexiann-a;pinulongang indonesyo;indonesi;tiếng indonesia;बहासा इण�?डोनेशिया;indonezia lingvo;lingua indonesiana;fiteny indonezianina;indonesek;indonesian language;indoneseg;இந�?தோனேசிய ம௚ழி;ইন�?দোনেশীয় ভাষা;indunisya simi;�?�尼语;ინდ�?ნეზიური ენ�?','http://dbpedia.org/resource/Indonesian_language','Indonesian'),('zha','za','zhuang;chuang','','Zhuang; Chuang'),('run','rn','kirundi;קירונדי;rundi;rundum;ルンディ語;rundi language;rundi simi;idioma kirundi;rundi jezik;bahasa kirundi;lingua kirundi;burunda lingvo','http://dbpedia.org/resource/Rundi_language','Rundi'),('slk','sk','slowaaks;basa slowakia;tok slovakia;סלובקית;slovakeg;lingua slovacca;slovakisk;slovak;slobhacais;isluwakya simi;slovakek;bahasa slowakia;eslovac;idioma eslovaco;eslovacu;slovak dili;slovakiska;slovak language;slovackish;slowak dili;slowaaksk;lengua slovacca;ภาษาสโลวั�?;lingua slovaca;স�?লোভাক ভাষা;スロ�?キア語;slovaque;lingua eslovaca;slovaco;slovaka lingvo;sluovaku kalba;gjuha sllovake;eslovakiera;slovakiana linguo;סל�?וו�?קיש;bahasa slovak;斯洛�?克语;slowaoks;სლ�?ვ�?კური ენ�?;욬로바키아어;tiếng slovak;lenga slovaca;lingua sluvacca;slovaki keel;slovakki;slowakisch;slovakimiusut;kislovakia','http://dbpedia.org/resource/Slovak_language','Slovak'),('tuk','tk','turkmenski jezik;turkmeenin kieli;ภาษาเติร๜�?เมน;turkmensk;turkmen;turkmeniska;투르�?�멘어;თურქმენული ენ�?;ತ�?ರ�?ಕ�?“ಮೇನಿಸ�?ತಾನ�?“ನ ಭಾಷೆ;トルクメン語;lingua turkmena;turkmenisch;turcomanu;bahasa turkmen;lingua turcomannica;turkmena lingvo;ቱርᚭመᚕ᚛;turkmeneg;त�?र�?कमेन भाषा;turcman;ত�?র�?কমেনীয় ভাষা;idioma turcomano;turkmen language;turkmin simi;turkmeens','http://dbpedia.org/resource/Turkmen_language','Turkmen'),('mlt','mt','lingua maltese;maltais;bahasa malta;lingua melitica;maltaca;maltan kieli;maltese;malteesk;wikang maltes;maltese language;maltish;maltees;lingua maltisa;basa malta;malteg;maltera;malta lingvo;maltesisch;kimalta;マルタ語;maltana linguo;maltneska;bangrmalti;몰타어;ภาษามอลตา;pinulongang maltes;maltesisk;马耳他语;ማ�?ት᚛;malta keel;lingua maltesa;lingwa maltija;maltesiska;malta simi;מלטית;malta dili;gjuha malteze','http://dbpedia.org/resource/Maltese_language','Maltese'),('fas','fa','persan;persian','','Persian'),('bos','bs','bosniera;bosniska;bosnisch;busna simi;bosnisk;lengua bosniaca;בוסנית;波斯尼亚语;bosnian language;bosnian kieli;bosanski jezik;bosniaque;idioma bosnio;basa bosnia;bahasa bosnia;bosnies;bosniu;lingua bosniaca;ボスニア語;bosniagiella;bosnien;bosnian;bosnia lingvo;wikang bosniyo;lingua busniaca;보욤니아어;gjuha boshnjake;pinulongang bosniyo;bosnieg;bosniya dili;lenga bosnian-a','http://dbpedia.org/resource/Bosnian_language','Bosnian'),('oci','oc','lingua occitanica;okzitanisch;tuksitant;oksitansk;lingua occitana;occitanska;occitan;liosita;bahasa occitan;idioma occitano;inutsitan;occitan language;oksitanski jezik;ऑक�?सितान भाषा;occitaans;오�?�어;oksitanek;ภาษาอ็อ�?ซิตัน;gjuha oksitaneze;okitaneg;oksitanca;oksitanisk;ocsitaneg;奥克西当语;kioksitania;luenga ocitana;ocitaniana linguo;オック語;�?וקסיטנית;ocsitaanish;ocseadanais;occitan (post 1500);occitansk;ucitan;অক�?সিত�? ভাষা;lenga ossitan-a;occitanu;okzitaniera;oksitaani keel;奥克语;okcitana lingvo;oksitaani;okcitanski jezik;oksitaans;okzitaansche spraak;kiunsita','http://dbpedia.org/resource/Occitan_language','Occitan'),('eus','eu','baskijski jezik;basque language;basc;lingua basca;lenghe basche;baskisk;巴斯克语;baskisch;�?スク語;basku valoda;bahasa basque;baszk nyelv;巴斯克語;limba basca;luenga vasca;바욤�?�어;idioma vasco;an bhascais;euskareg;bascais;gjuha baske;baskies;ბ�?სკური ენ�?;basko;pinulongang basko;yuskara simi;euskara;ภาษาบาส�?๜;baskijsko godka;baskiana linguo;vascu;basque;lengua basca;bascish;lingua vasconica;baskysk;euskera;tiếng basque;l:巴斯克語;baski keel;basgeg;baskneska;baskek;ב�?סקיש;baskiska;בסקית;பாஸ�?க�? ம௚ழி;baskin kieli','http://dbpedia.org/resource/Basque_language','Basque'),('kaz','kk','কাজাখ ভাষা;카�?�??어;kazachsko godka;qazax dili;kazakiska;qazah tili;kazachs;kazakeg;kikazakhi;qasaq simi;ყ�?ზ�?ხური ენ�?;kasachisch;cazac;kazakh;カザフ語;קזחית;an chasaicis;gjuha kazake;kazakin kieli;哈�?�克语;qazaq tele;idioma kazajo;ภาษาคาซัค;कजाक भाषा;kazakh language;qazaq tili;qozoq tili;kazahu valoda;bahasa kazak;lingua kazaka;kasahhi keel;kasakhisk;kasakska;idioma cazaco;lingua casachica;lingua casaca','http://dbpedia.org/resource/Kazakh_language','Kazakh'),('pli','pi','lingua palica;פ�?לי;pali;पालि भाषा;ප�?ලි;পালি ভাষা;lingua pali;tiếng pali;パーリ語;basa pali;limba pali;palia lingvo;ਪਾਲੀ;பாளி;bahasa pali;paalin kieli;పాళీ భాష;巴利语;പാലി;휔리어;ภาษาบาลี;पली;পালি','http://dbpedia.org/resource/Pali','Pali'),('deu','de','deutsch;neuhochdeutsch;allemand;haut-allemand moderne;german;new high german','http://dbpedia.org/resource/New_High_German','New High German'),('mah','mh','マーシャル語;marshallesisk;marshallesisch;marshallese;gjuha marshalleze;marshallin kieli;marshallees;marshallesiska;marshall;lingua marshallese;marshallese language;marchalleg;lengua marscialleise;marshallais','http://dbpedia.org/resource/Marshallese_language','Marshallese'),('tso','ts','tsonga;tsonga language;bahasa tsonga;conga lingvo;tsongan kieli;lingua tsonga;xitsonga;tsongum;idioma tsonga;gjuha tsonga;tsongeg;ツォンガ語','http://dbpedia.org/resource/Tsonga_language','Tsonga'),('ori','or','ఒరియా భాష;oriya;オリヤー語;lingua orissensis;उडिया भाषा;ওড়িয়া ঠার;奥釜亚语;orija;ଓଡ଼ିଆ;orija lingvo;ઓરિયા ભાષા;oriya simi;�?ורייה;ওড়িয়া ভাষা;oria keel;�?რი�?;ओड़िया भाषा;ओडिया;oriya dili;ภาษาโอริยา;오리야어;ഒറിയ;bahasa oriya;oriyeg;lingua oriya;oriya language;ஒரியா ம௚ழி;idioma oriya','http://dbpedia.org/resource/Oriya_language','Oriya'),('ina','ia','interlingua;interlingva nyelv;interlingua (international auxiliary language association);gjuha interlingua;ภาษาอินเทอร๜ลิง�?วา;interlingua (langue auxiliaire internationale);�?�템르�?구아;国际语;ინტერლინგუ�?;interlingva;�?ינטרלינגו�?ה;interlingvao;インターリングア','http://dbpedia.org/resource/Interlingua','Interlingua'),('hat','ht','ჰ�?იტიური კრე�?ლი;creol;�?イ�?語;lingua creol haitian;海地克釜奥耳语;criollo haitiano;haitianisch;haitian;海地克釜奧爾語;아�?�티어;crioulo haitiano;हैतीयन क�?रियोल;haitin kreoli;haitiko kreolera;हैतियाई क�?रियोल;haitisk kreol;haitisk;haitian creole language;kreyol;kreoleg haiti;creolo haitiano;haitian creole;haitia kreola lingvo;haiti kreol nyelv','http://dbpedia.org/resource/Haitian_Creole_language','Haitian; Haitian Creole'),('ukr','uk','乜克兰语;�?וקר�?יניש;ukrainiana linguo;ukrain tili;ukrajinski jezik;ukraineg;ইউক�?রেনীয় ভাষা;tiếng ukraina;bahasa ukraina;ukrainera;lingua ucraina;ukraina keel;gjuha ukrainase;უკრ�?ინული ენ�?;idioma ucraniano;ukrainian;wikang ukranyano;ukrainien;lenga ucrain-a;ukrainagiella;kiukraine;lengua ucrainn-a;ukrainian language;lingua ucrainica;ukrayna dili;यूक�?रेनी भाषा �?वं साहित�?य;ウクライペ語;pinulongang ukranyano;bahasa ukraine;�?וקר�?ינית;ukrainisch;우�?��?��?�나어;ukraina lingvo;ukrainsk;ukraynaca;ภาษายูเครน;ucranianu;ukrainska;ukranya simi;ukraynek;ukrainan kieli;ukrainsche spraak;ookraanish','http://dbpedia.org/resource/Ukrainian_language','Ukrainian'),('guj','gu','gujarati;�?��?�拉特语;gujaratagiella;ग�?जराती;kigujarati;lengua gujarati;goudjarateg;goudjrati;gujarati simi;fiteny gojaratia;ᜉᜃራቲ;グジャラート語;gujarati bhasa;lingua gujaratensis;ગ�?જરાતી ભાષા;gujarati language;bahasa gujarati;구�?�?�횸어;గ�?జరాతి భాష;ภาษาคุบราต;גוג\'ר�?טית;ग�?जराती भाषा;க�?ஜராத�?தி;gujarati jezik;გუჯ�?რ�?თული ენ�?;bahasa gujarat;lingua gujarati;ഗ�?ജറാത�?തി ഭാഷ','http://dbpedia.org/resource/Gujarati_language','Gujarati'),('tel','te','তেল�?গ�? ভাষা;telugu;bahasa telugu;ภาษาเตลู�?ู;ತೆಲ�?ಗ�?;telugua lingvo;तेल�?ग�?;तेल�?गू भाषा;泰�?�固语;தெல�?ங�?க�?;തെല�?ഗ�?;తెల�?గ�?;lingua telugu;ტელუგუ;telugu language;テルグ語;telugu jezik;તેલ�?ગ�? ભાષા;telougoueg;telugu simi;lingua telingana;তেল�?গ�? ঠার;텔루구어','http://dbpedia.org/resource/Telugu_language','Telugu'),('eng','en','�?�代英語;modern english;nyengelska;english;moderna angla lingvo;თ�?ნ�?მედრ�?ვე ინგლისური პერი�?დი;inglese moderno;moderne engelsk;anglais;近代英語;anglais moderne;modernes englisch;englisch','http://dbpedia.org/resource/Modern_English','Modern English'),('hye','hy','armenian','','Armenian'),('ltz','lb','bahasa luxembourg;luxemburgs;לוקסמבורגית;lucsambuirgis;luksemburga lingvo;ルクセンブルク語;luxembourgish;letzeburgesch;luxemborjesh;luxembourgeois;lingua luxemburgica;taluksemburgit;�?�森堡语;luxemburgisch;bahasa luksemburg;lingua lussemburghese;룩셈부르�?�어;luxembourgsk;luxenburgera;luxnbuagisch;לוקסעמבורגיש;luxembourgish language;ლუქსემბურგული ენ�?;luxemburgi nyelv;lengua luxemburgheise;luxemburgiska;盧森堡話;lushaborgek;luxemburggagiella;luksembourgeg;luxemburgin kieli;lucsamburgais;gjuha luksemburgishte;letseburgi keel;lingua luxemburguesa','http://dbpedia.org/resource/Luxembourgish_language','Luxembourgish; Letzeburgesch'),('srd','sc','sarde;sardinian','','Sardinian'),('kua','kj','kwanyamaeg;kuanyama;kwanyama;kuanjama lingvo;bahasa kwanyama;クワニャマ語;idioma kuanyama;क�?वान�?यामा भाषा','http://dbpedia.org/resource/Kwanyama','kwanyama'),('lub','lu','kiluba;luba-katanga;luba-katanga language','http://dbpedia.org/resource/Luba-Katanga_language','Luba-Katanga'),('fra','fr','frans;franska;�?��?�ᔥᑞᑯᔮ�?��?�ᔨᒧ�?��?;kifalanse;pagsasao a frances;ഫ�?രഞ�?ച�? ഭാഷ;franciana linguo;பிரெஞ�?ச�? ம௚ழி;tataramon na pranses;lingua francese;法语;french;basa prancis;फ़�?रांसीसी भाषा;lingua francogallica;tafransist;prinanses;fransegbe;faransekan;l:法蘭西語;프랑욤어;wikang pranses;फ�?रेञ�?च भाषा;limba frantzesa;prantsuse keel;fasybau;fransuz tili;frenkek;bahsa peurancih;frinanses;tok pranis;fazyij;frangish;an fhraincis;ཕ་རན་སིའི་ས�?ད�?;francuski jezik;se-french;lingua fransesa;ఫ�?రెంచి భాష;צרפתית;francia nyelv;�?�ረᚕሳይ᚛;ภาษา�?รั่งเศส;ພາສາ�?ະລັ່ງ;फ�?रान�?सेली भाषा;french language;fransuz dili;法文;फ�?रेंच भाषा;sifulentshi;fransum;frantses;פר�?נצויזיש;ffrangeg;luenga francesa;francusko godka;frenk tili;法語;fransk;franca lingvo;lengua franzese;lingua francisa;phransya simi;フランス語;wu-faraas;法國話;lingua franzosa;galleg;fiteny frantsay;frangais;ranskan kieli;kifaransa;�?��?�ᕖᑞ�?ᑦ/uiviititut;bahasa perancis;lang franse;ফরাসি ভাষা;french leid;ფრ�?ნგული ენ�?;french bhasa;lenghe francese;ಫ�?ರೆಂಚ�? ಭಾಷೆ;lingua francesa','http://dbpedia.org/resource/French_language','French'),('arg','an','lingua aragunesa;aragonski jezik;aragonees;lengua aragoneise;aragonish;阿拉贡语;aragonska;lingua aragonensis;aragonesisch;アラゴン語;aragonese language;aragoiera;aragoniagiella;aragonisk;아�?�곤어;aragonese;aragonca;aragoneg;aragoonsche spraak;bahasa aragon;luenga aragonesa;aragonais;lingua aragonesa;aragonek;ภาษาอารา�?อน;lingua aragonese;reo aragon;bahasa aragones;aragonesisk;aragona lingvo;aragonian kieli','http://dbpedia.org/resource/Aragonese_language','Aragonese'),('nya','ny','�?切瓦语;chilankhulo cha chichewa;bahasa chichewa;tchitcheweg;চিচেওয়া ভাষা;chichewa;lingua chichewa;chichewa simi;�?ェワ語;chinyanja;chewa;nyanja;치체와어;chewa language','http://dbpedia.org/resource/Chewa_language','Nyanja'),('smo','sm','samoansk;samoaeg;samoan;bahasa samoa;lingua samoana;samoan kieli;samoan language;samoanski jezik;samoaans;samoanska;samoa kalba;samwa simi;idioma samoano;samoa lingvo;サモア語;lengua samoann-a;�?�摩亚语;samoisch','http://dbpedia.org/resource/Samoan_language','Samoan'),('lav','lv','lett nyelv;lenga leton-a;拉脱维亚语;latvian language;litunya simi;latviu kalba;ภาษาลัตเวีย;letsk;an laitvis;lettneska;latvian kieli;latvisk;letton;lettisch;bahasa latvia;latwyan;�?�횸비아어;lets;gjuha letoneze;uotewsko godka;latvijski jezik;latviana linguo;לטבית;letonca;lingua lettonica;ლ�?ტვიური ენ�?;latvish;lengua lettone;lettiskt;लात�?व�?हियन भाषा;latva lingvo;लातवियाई भाषा;latvian;lettisk;latviek;lettiska;lettsch;lingua letoa;lingua lettone;letonski jezik;pinulongang leton;ラトビア語;bahasa latvi;letoniera','http://dbpedia.org/resource/Latvian_language','Latvian'),('amh','am','amharic;amharique','','Amharic'),('kir','ky','키르기욤어;kirgiski jezik;kyrgyz;किर�?गिज़ भाषा;bahasa kirgiz;kirghiz;ყირგიზული ენ�?;kirkis simi;kirgizera;kirgiziska;kyrgyz language;kirgisisk;קירגיזית;lingua kirghiza;kirgizisch;ภาษาคีร๜�?ีซ;kirgizeg;takirgizit;kirgiisi keel;kirgiisin kieli;キルギス語;किर�?गि�? भाषा;kirgiza lingvo;kirgisisch;kirgizysk','http://dbpedia.org/resource/Kyrgyz_language','Kyrgyz'),('ben','bn','bangla;bengali;bengalgiella;ბენგ�?ლური ენ�?;bengalera;fiteny bengali;bengali language;gjuha bengali;ಬಂಗಾಳಿ;bengalski jezik;बांग�?ला भाषा;bengali keel;bengali bhasa;bahasa bengali;bengaleg;bengalek;lengua bengali;bengala lingvo;ବାଂଲା ଭାଷା;bengali linguo;बाङगला;বাংলা ঠার;bengalu kalba;bengalin;बंगाली भाषा;kibengali;bengaals;bengalce;lingua bengalica;bengalin kieli;ベンガル語;banglaeg;lingua bengalese;bengalska;binengali;בנגלית;બંગાળી ભાષા;tiếng bengal;bengalisch;banla simi;ബംഗാളി;bengali leid;বাংলা ভাষা;வங�?காள ம௚ழி;벵골어;బంగ�?లా భాష;ภาษาเบง�?าลี','http://dbpedia.org/resource/Bengali_language','Bengali'),('afr','af','afrikaans;afrikaani keel;アフリカーンス語;lingua afrikaans;ஆபிரிக�?கான ம௚ழி;afrikansum;afrikaanca;아프리칸욤어;�?פריק�?נס;afrikaans nyelv;afrikaansk;afrikoansk;bahasa afrikaans;kiafrikaans;afrikandu valoda;afrikansa lingvo;আফ�?রিকান�?স ভাষা;limba afrikaans;�?ფრიკ�?�?ნსი;godka afrikaans;gjuha afrikane;afrikaneg;afrikaans leid;�?��?�語;lingua africana;isibhulu;isibhunu;afrikans simi','http://dbpedia.org/resource/Afrikaans','Afrikaans'),('cat','ca','balear;balearisch;balearai;catalan;valencian;balearic','http://dbpedia.org/resource/Balearic','Valencian'),('nno','nn','norwegian nynorsk','','Norwegian Nynorsk; Nynorsk, Norwegian'),('isl','is','islandski jezik;アイスランド語;ภาษาไอซ๜�?ลนด๜;lingua islannisa;islandais;ijslands;islandeg;�?יסלנדית;lingua islandesa;izlandi nyelv;lingua islandese;yslands;tok aislan;冰島語;islandsk;ieslaans;冰岛语;islannin kieli;islandiera;आईसल�?डिक भाषा;আইসল�?যান�?ডীয় ভাষা;islanda lingvo;आइसलैंडिक भाषा;islandi keel;icelandic language;ისლ�?ნდიური ენ�?;lingua islandica;icelandic leid;ieslandjs;tiếng iceland;islandya simi;bahasa iceland;아�?�욬란드어;icelandic;冰岛文;eeslynnish;islandu kalba;luenga islandesa;bahasa islan;islandana linguo;gjuha islandeze;islandek;lengua islandesa;lengua islandeise','http://dbpedia.org/resource/Icelandic_language','Icelandic'),('slv','sl','slovensk;lingua sluvena;esloveniera;slovenagiella;sloveens;slovena lingvo;slovenska;tiếng slovenia;स�?लोवेनियन भाषा;lenga sloven-a;斯洛文尼亚语;slovenski jezik;sloweensche spraak;slovenek;slovenian;eslovenu;slowenisch;lengua slovena;idioma esloveno;lenghe slovene;sloveniana linguo;gagana slovene;slofeneg;isluwinya simi;sloven dili;sloveeni;lingua eslovena;bahasa slovenia;bahasa slovene;lingua slovena;slovence;স�?লোভেনীয় ভাষা;slovene language;sloveanish;sloveeni keel;욬로베니아어;სლ�?ვენური ენ�?;sloveensk;gjuha sllovene;sloveneg;slovenie leid;sloweens;סל�?וועניש;スロベニア語;סלובנית;slowenische sproch','http://dbpedia.org/resource/Slovene_language','Slovenian'),('uig','ug','uighur;uigur;uiguurin kieli;ouigoureg;uyghur;uyghur language;உய�?க�?ர�? ம௚ழி;uigurisk;उइग�?र भाषा;维�?�尔语;ภาษาอุย�?ูร๜;wikang uighur;uiguru valoda;उईघर भाषा;bahasa uighur;위구르어;tiếng uyghur;ウイグル語;lingua uigura;ཡུལ་གོར་ས�?ད�?;uygurca;uigurisch;idioma uigur;ujgura lingvo;uyg‘ur til;ᚡይᜉር᚛;uygur simi;bahasa uyghur;উইগ�?র ভাষা;�?ויגור;oeigoers;uiguriska','http://dbpedia.org/resource/Uyghur_language','Uyghur'),('kan','kn','�?�纳达语;kannada;kannada dili;कन�?नड़ भाषा;kanara;કન�?નડ ભાષા;കന�?നഡ;kannadeg;kannada language;lingua cannadica;kanada jezik;bahasa kannada;kannada nyelv;kanara lingvo;lingua kannada;कन�?नड भाषा;칸나다어;fiteny kannada;कन�?नड;కన�?నడ భాష;ภาษา�?ันนาดา;কন�?নড় ভাষা;カンペダ語;কন�?নড় ঠার;ಕನ�?ನಡ;kannada simi;კ�?ნ�?დ�?','http://dbpedia.org/resource/Kannada_language','Kannada'),('cym','cy','welsh;limba gallesa;welsh leid;kamri simi;�?尔士语;wikang gales;galce;walisisch;bahasa wales;velska;웨�?�욤어;kymrisch;walisisk;lengua galleise;cymraeg;ウェールズ語;ওয়েল�?“শ�?“ ভাষা;welsk;gallois;lingua gallisa;ולשית;kimra lingvo;lingua valisica;lingua galesa;bretnish;lingua cambrica;walijsko godka;kymriska;lingua gallese;wallies;walsana linguo;kymri;kembraeg;cuimris;walisesch;kiwelisi;walesi nyelv;welsh language;kembrek;galesera;an bhreatnais','http://dbpedia.org/resource/Welsh_language','Welsh'),('que','qu','quechua;quechuan languages;quechuan (family);quechuan;qhichwa rimaykuna;克丘亞語系;quechua (famille);lenguas quechuas;quechuan language','http://dbpedia.org/resource/Quechuan_(family)','Quechuan'),('kor','ko','קורי�?נית;korean language;�?鲜语;koreek;koreano;koreaans;gjuha koreane;ᑯᕆ�?�ᑞ�?ᑦ/kuriatituq;lingua coreana;�?鮮語;corean;bahasa korea;க௚ரிய ம௚ழி;wikang koreano;�?鮮話;კ�?რეული ენ�?;korean;isikoriya;ಕ೚ರಿಯಾದ ಭಾಷೆ;koreanska;koreansk;ภาษาเ�?าหลี;koreanisch;lengua coreana;idioma coreano;koreana linguo;korea lingvo;koreya dili;koreaneg;কোরীয় ভাষা;कोरियन भाषा;ឪ឵ឥ;ພາສາເ�?ົາຫຼີ;한국어;korean kieli;कोरियायी भाषा और साहित�?य;koreai nyelv;koreera;kuriya simi;korece;korea keel;korejski jezik;coreanu;ᚮሪይ᚛','http://dbpedia.org/resource/Korean_language','Korean'),('hbs','sh','serbocroata;সার�?বো-ক�?রোয়েশীয়-বসনীয় ভাষা;serbokroaatsch;세르보�?�로아횸어;serbo-croatian language;serbokroata lingvo;idioma serbocroata;servokroatisch;srpskohrvatski jezik;bahasa serbo-croatia;lingua serbocroatica;sirbu hurwat rimay;serb-xorvat dili;serbo-croatian;serbo-kroaziera;סרבו-קרו�?טית;serbocroat;serbo-croate;serbia-horvaadi keel;serbokroatisk;lingua serbocroata;serwo-kroaties;bahasa serbo-kroasia;セルビア・クロア�?ア語;serbokroaatin kieli;lingua serbo-croata;塞尔维亚-克罗地亚语;kiserbokroatia;serbokroatisch;სერბულ-ხ�?რვ�?ტული ენ�?;serbo-croateg;gjuha serbokroate;srpsko-hrvatski jezik;serbokroatiska','http://dbpedia.org/resource/Serbo-Croatian_language','Serbo-Croatian'),('som','so','सोमाली भाषा;somali;bahasa somali;somaliana linguo;somala lingvo;somalieg;ს�?მ�?ლი ენ�?;af-soomaali;솜�?리어;somalice;sumali simi;சோமாலி ம௚ழி;somalin kieli;ሶማ�?᚛;somaliera;索馬釜語;somali language;somalisch;lingua somala;ภาษาโซมาเลีย;somaliska;ソマリ語;সোমালি ভাষা','http://dbpedia.org/resource/Somali_language','Somali'),('kom','kv','komi;科米語;코미어;syrjensk;komi-linguo;komin kieli;komigiella;ภาษาโคมิ;komieg-zirieg;zurjeens;idioma komi;lingua komi;komi nyelv;kom;komi language;komish;komi keel','http://dbpedia.org/resource/Komi_language','Komi'),('kin','rw','kinyarwanda;rwanda;�?�旺达语;rwanda language;rwandum;bahasa kinyarwanda;rwanda simi;rwanda jezik;ruandan kieli;lingua kinyarwanda;ルワンダ語;ruanda lingvo;키�?르완다어;কিনিয়ারোয়ান�?ডা ভাষা','http://dbpedia.org/resource/Rwanda_language','Rwanda language'),('ava','av','avarisk;bahasa avar;avar language;avaars;avariska;avareg;avarska;avar dili;awar dili;abararazda;아바르어;阿瓦尔语;avar;アヴァル語;avaarin kieli;avaric;awarisch;avarca;lingua avara','http://dbpedia.org/resource/Avar_language','Avaric'),('zho','zh','中国語;kinesiska;wikang tsino;漢語;ภาษาจีน;chinois;汉语;རྒྱ་ས�?ད�?;chinees;ചൈനീസ�? ഭാഷ;tsino;txinera;bahasa cina;chinese languages;idioma chino;gjuha kineze;lingua chinesa;चीनी भाषा;inintsik;pagsasao nga intsik;chiniana linguo;se-china;langues chinoises;lingua cinese;hiina keel;চীনা ভাষা;சீன ம௚ழி;isishayina;ჩინური ენ�?;kineski jezik;chinesischn;kichina;chinese;jugbau;chinek;中文;kineserisut;ພາສາຈີນ;kinesisk;intsik;basa cina;lingua sinica;chinese talen;lengas siniticas;vahgun;lengua cineise;bahasa tionghoa;kiinan kieli;tiếng trung quốc;שפות סיניות;chinu simi;중국어;ಚೀನಿ ಭಾಷೆ;chinese language;chinese bhasa;l:漢語;כינעזיש','http://dbpedia.org/resource/Chinese_language','Chinese'),('mon','mn','mongola lingui;mungul rimaykuna;mongolian languages;mongoles, langues;lingue mongoliche;ieithoedd mongolaidd;蒙�?�语�?;mongolian;mongoolse talen;mongolischn;mongolic languages;langues mongoles;luengas mongols;モンゴル諸語;langue mongoles;mongol;mongolian language;mongolic language;mongolilaiset kielet','http://dbpedia.org/resource/Mongolian_languages','Mongolic languages'),('oji','oj','ojibwa','','Ojibwa'),('kik','ki','キクユ語;gikouyoueg;kikuyu;gikuyum;kuja lingvo;gikuyu language;kikuyu simi;গিক�?য়�? ভাষা;gikuyu;idioma kikuyu','http://dbpedia.org/resource/Gikuyu_language','Kikuyu; Gikuyu'),('abk','ab','abkhaz;pinulongang abhaso;アブ�?ズ語;abhaski jezik;阿布哈兹语;abchaseg;�?ფხ�?ზური ენ�?;আবখাজ ভাষা;abchasisch;abkhaz language;abkhazian;idioma abhasio;idioma abjasio;abhaz dili;abkhaze;abhaasin kieli;�?בחזית;abc\'hazeg;abhazca;abchazisch;abkhazera;압하욤어;abchaziska;bahasa abkhaz;lingua abcasa;abkhasisk;lingua abkhaza','http://dbpedia.org/resource/Abkhaz_language','Abkhazian'),('hau','ha','hausa;haoussa;hausa jezik;ஹவ�?சா ம௚ழி;lenga hausa;hausan kieli;ה�?וסה;lingua hausa;hausa linguo;হাউসা ভাষা;haousaeg;हड़सा भाषा;idioma hausa;�?ウサ語;hawsa simi;haussa;bahasa hausa;ჰ�?უს�?;豪�?�语;hausa language','http://dbpedia.org/resource/Hausa_language','Hausa'),('asm','as','असमिया;asamski jezik;asameg;bahasa assam;asama lingvo;অসমীয়া ভাষা;assamesiska;�?ס�?מית;�?ს�?მური ენ�?;lingua assamica;assamesisk;ಅಸ�?ಸಾಮಿ;assamese language;アッサム語;assamais;आसामी भाषा;அசாமிய ம௚ழி;অসমীয়া ভাষা আৰ�? লিপি;assamesisch;lingua assamese;assami;આસામી�? ભાષા;fiteny assamey;ആസ�?സാമീസ�?;assamese;ภาษาอัสสัม;অসমীয়া ঠার;asam simi;아삼어;assamees;assameg;阿�?�姆语;అస�?సామీ భాష','http://dbpedia.org/resource/Assamese_language','Assamese'),('por','pt','portugaleg;portugees;葡�?�牙語;प�?र�?तगाली भाषा;�?�르투갈어;portugalsko godka;portugisisk;portegeesk;portugeesche spraak;bahasa portugis;portugali keel;�?ルトガル語;portekizce;portyngalek;portugala lingvo;portugalin kieli;portugisesch;portuguese;lingua portughese;პ�?რტუგ�?ლიური ენ�?;basa portugis;portuguese bhasa;portuqal dili;lingua portughisa;isiputukezi;portugal tili;portugalski jezik;portugais;פורטוגזית;portuges;portugalana linguo;போர�?த�?த�?க�?கீச ம௚ழி;limba portughesa;lengua portugheise;lingua portugese;葡�?�牙语;gjuha portugeze;portagailis;portuguese language;portugalu kalba;lingua portugaisa;portiwgaleg;kimputulukesi;portugiesisch;luenga portuguesa;lingua lusitana;kireno;পর�?ত�?গিজ ভাষা;wikang portuges;portugisiska;portuguese leid;pinulongang portuges;lingua portuguesa;ಪೋರ�?ಚ�?ಗೀಯ ಭಾಷೆ;lingua portoghese;purtuyis simi;פ�?רטוגעזיש;lenghe portughese;ภาษาโปรตุเ�?ส;portugese','http://dbpedia.org/resource/Portuguese_language','Portuguese'),('ces','cs','czesko godka;idioma checo;an tseicis;lingua tscheca;bahasa ceska;चेक भाषा;tchekeg;lengua ceca;chiku simi;செக�? ம௚ழி;czech leid;kicheki;tjekkisk;tsjechies;체코어;tsieceg;tsjechisch;tsjekkisk;czech language;lenga ceca;চেক ভাষা;lingua ceca;sheckish;cseh nyelv;chex tili;tschechisch;tcheke;checu;lingua bohemica;טשעכיש;seacais;chekek;ಚೆಕ�? ಭಾಷೆ;�?�克语;tsjechysk;lingua checa;bahasa czech;tsjeggies;tjeckiska;txekiera;txec;lingua chec;צ\'כית;chekiana linguo;�?ェコ語;czech;lenghe ceche;ჩეხური ენ�?;ภาษาเบ็�?','http://dbpedia.org/resource/Czech_language','Czech'),('snd','sd','סינדהי;சிந�?தி ம௚ழி;सिंधी भाषा;সিন�?ধি ভাষা;sindhi;ภาษาสินธี;സിന�?ധി ഭാഷ;idioma sindhi;sindeg;sindi simi;sindhi bhasa;sindhi language;シンド語;sindera;lingua sindhi;bahasa sindhi;신디어;lingua sindhuica;信德语;bahasa sindh;სინდჰური ენ�?;sinda lingvo','http://dbpedia.org/resource/Sindhi_language','Sindhi'),('glg','gl','გ�?ლისიური ენ�?;ガリシア語;kigalicia;galicien;갈리시아어;lingua gallaica;luenga gallega;galegogiella;galeegi keel;gallec;lingua galiziana;lia-galegu;lingua gallecian;galicisk;gallegu;galiciska;idioma gallego;galligu simi;reo galicia;gjuha galiciane;galician language;गॅलिशियन भाषा;galisieg;galisu kalba;galicies;galega lingvo;lingua galega;lengua galissiann-a;galiciai nyelv;galicisch;galizeg;ภาษา�?าลิเซีย;galeeshish;galician;גליסית;wikang galisyano;galego;galiziera;galicijski jezik;嚠釜西亞語;galisisk;galijek;galizian;bahasa galicia;lenga galissian-a;गैलिशियन भाषा','http://dbpedia.org/resource/Galician_language','Galician'),('cos','co','korsikansk;lengua corsa;lingua corsa;corso;corsican language;korseg;korsische spraak;科西嘉语;corsicagiella;corsu;korsisch;cors;korsikaans;bahasa korsika;korsikanska;korsika lingvo;korsikan kieli;corsican;korsikaca;corse;コルシカ語;korsikan tili;코르시카어;corsicaans;limba corsicana;idioma corso;bahasa corsica;korzikai nyelv;corseg;korsika keel;korsikera','http://dbpedia.org/resource/Corsican_language','Corsican'),('spa','es','ᓯ�?��?�ᓂᑞ�?ᑦ/sipainititut;castiliaans;spainyie leid;lingwa spanjola;スペイン語;naakaii bizaad;spanish;സ�?പാനിഷ�?“ ഭാഷ;西�?�牙语;espagnol;ภาษาสเปน;སེ་པན་ས�?ད�?;bahasa spanyol;spanisch;hispaniana linguo;lingua hispanica;bahasa sepanyol;lenghe spagnole;स�?पेनी भाषा;שפ�?ניש;kastilla simi;kihispania;fiteny espaniola;ஞச�?ப�?பானியம�?;spaansk;kastilla aru;espanjan kieli;bahsa seupanyo;hispana lingvo;schbanisch;spaans;西�?�牙話;l:西�?�牙語;kinatsila;spangbe;lingua espaniol;wikang kastila;স�?পেনীয় ভাষা;spanish bhasa;spagnoleg;basa spanyol;lingua spagnola;spansk;sanbau;spanish language;gjuha spanjolle;ესპ�?ნური ენ�?;isispanish;lenga spagneula;西�?�牙語;sipanishi;gagana spaniolo;limba ispagnola;spanyol nyelv;spanum;spaansche spraak;ಸ�?ಪ�?ಯಾನಿಷ�? ಭಾಷೆ;sbaeneg;욤힘�?�어;lingua castilyana;hispaania keel;�??�?��?�;castilian;spaainish;lispanyoli;spanska;reo paniora;spoans;castila;స�?పానిష�? భాష;castilyan;spuenesch;स�?पॅनिश भाषा;tok spen;spaynek;castellanu;pagsasao nga espaniol;स�?पेनिश भाषा;ספרדית;gaztelania;tataramon na espanyol;hispanic','http://dbpedia.org/resource/Spanish_language','Spanish; Castilian'),('bis','bi','bislama lingvo;bislama;bichlamar;比斯拉马语;ビスラマ語;bichelamar;ภาษาบิสลามา;lingua bislama;비욬�?�마;bahasa bislama','http://dbpedia.org/resource/Bislama','Bislama'),('vie','vi','lingua vietnamica;vietnamees;ベトペム語;vietnamesiska;vietnamca;bahasa vietnam;l:趚�?�語;趚�?�语;ภาษาเวียดนาม;lingua vietnamita;וייטנ�?מית;vietnamera;vietnamana linguo;vietnamita;ვიეტნ�?მური ენ�?;vietnamesisk;idioma vietnamita;vjetnama lingvo;witnam simi;वियतनामी भाषा;vietnamese language;vietnamien;gjuha vietnameze;vijetnamski jezik;vietnamese;베횸남어;vietnami nyelv;tiếng việt;wikang biyetnames;vietnamin kieli;vietnamesisch','http://dbpedia.org/resource/Vietnamese_language','Vietnamese'),('div','dv','lingua dhivehi;dhivehi;திவெயி ம௚ழி;divehi;ภาษาดิเวฮิ;महल�?;idioma dhivehi;ディベヒ語;divehin kieli;lingua maldiviana;dhivehi language;bahasa divehi;迪维帜语;디베히어;maldivian;mahla lingvo;diveheg;maldivien;महल भाषा','http://dbpedia.org/resource/Dhivehi_language','Maldivian'),('urd','ur','urduca;urdu;lingua urdu;�?ורדו;উর�?দ�? ঠার;ourdou;wrdw;urdu dili;urdugiella;urdu jezik;உர�?த�?;우르�?어;limba urdu;urdu simi;lengua urdu;উর�?দ�? ভাষা;tiếng urdu;ఉర�?దూ భాష;urduo;bahasa urdu;pinulongang urdu;उर�?दू भाषा;ordo;उर�?दू;urdu nyelv;ourdoueg;ಉರ�?ದೂ;fiteny urdu;ઉર�?દ�? ભાષા;ურდუ ენ�?;an urdais;ウルドゥー語;乜尔都语;oerdoe;ഉർദ�?;ภาษาอูรดู','http://dbpedia.org/resource/Urdu','Urdu'),('vol','vo','沃拉普克语;volapyk;ヴォラピュク;볼�?�퓜�?�;volapuque;volapik;volapiukas;fiteny volapioky;volapuko;וול�?פיק;volapuk;ቮላ�?��?ᚭ;ภาษาโวลาปุ�?','http://dbpedia.org/resource/Volapük','Volapük'),('jav','jv','bahasa banyumasan;basa banyumasan;javanese;ภาษาบันยูมาซัน;banyumasan;banjumasa lingvo;ባᚙማሳᚕ;�?ニュマス語;javanais;banyumasan language','http://dbpedia.org/resource/Banyumasan_language','Javanese'),('mya','my','burmai nyelv;बर�?मेली भाषा;ภาษาพม่า;lingua birmanica;idioma birmano;缅甸语;birma lingvo;bahasa myanmar;বর�?মী ভাষা;বর�?মী ঠার;bahasa burma;burmaca;ビルマ語;burmese language;འབར་མའི་ས�?ད�?;lingua birmana;မြန်မာဘာသာစကား;birman;burman kieli;birmaans;tiếng myanma;버마어;ბირმული ენ�?;burmesisk;birmanu simi;burmese;बर�?मी भाषा;birmanisch;burmesiska;birmanisk;birma keel;burmeg','http://dbpedia.org/resource/Burmese_language','Burmese'),('nep','ne','བལ་པོའི་ས�?ད�?;నేపాలీ భాష;nepalees;নেপাল ভাষা;nepala lingvo;nepaleg;নেপালি ঠার;nepali;尼泚尔语;nepalesisk;�?パール語;lengua nepaleise;नेपाली भाषा;നേപ�?പാളി ഭാഷ;nepalin kieli;lingua nepalese;ภาษาเนปาล;네휔어;nepali keel;நேபாளி ம௚ழி;नेपाली;nepal leid;nipali simi;खे�? भाषा;lingua nepalensis;nepalska;an neipeailis;nepali language;bahasa nepali','http://dbpedia.org/resource/Nepali_language','Nepali'),('bul','bg','bulgar;tiếng bungary;bulgarian;bulgaarsk;பல�?கேரிய ம௚ழி;bulgareg;bulgariera;bulgarsk;bulgariana linguo;tok balgeria;kibulgaria;basa bulgaria;gjuha bullgare;bugarski jezik;bulgaaria keel;bulgaars;bulgarisch;bulgara lingvo;bulgarian kieli;ბულგ�?რული ენ�?;lingua bulgara;lengua bulgara;bahasa bulgaria;bulgarie leid;idioma bulgaro;bulgarya simi;bolqar dili;ภาษาบัล�?�?เรีย;wikang bulgaro;בולגרית;bulgarian language;lingua bulgarica;불가리아어;ব�?লগেরীয় ভাষা;bulgare;bulgaro;bulgeyrish;bulgarek;ブルガリア語;bwlgareg;�?嚠利亚语;bulgarca;bulgariska;ब�?ल�?गारियन भाषा;isi-bulgaria','http://dbpedia.org/resource/Bulgarian_language','Bulgarian'),('fao','fo','faroece;faroese;lingua faruisa;farski jezik;ფ�?რერული ენ�?;feroa lingvo;फ़रोइस भाषा;faroese language;lingua faroese;faroeera;bahasa faroe;farer tili;法罗语;lingua faroesa;faaroish;lingua feroesa;lingua faroensis;�?�ሮ᚛;ফারোয়েজীয় ভাষা;faerana linguo;faroyek;フェロー語;힘로어;faeroeg;fearagiella','http://dbpedia.org/resource/Faroese_language','Faroese'),('ndo','ng','ンドンガ語;ndonga;ndonga lingvo;ndonga language;lingua ndonga;bahasa ndonga;idioma ndonga','http://dbpedia.org/resource/Ndonga_language','Ndonga'),('kat','ka','georgian language;seoirsis;ג�?ורגית;georgian kieli;lingua georgiana;georgisch;georgiska;gruzijski jezik;kartul simi;グルジア語;georgian;georgisk;lingua xeorxiana;basa georgia;kartvela lingvo;gruzijski;ქ�?რთული ენ�?;bahasa georgia;gurciki;格�?�?�亚语;wikang heyorhiyano;idioma georgiano;그루지야어;georgiera;சியார�?சிய ம௚ழி;ภาษาจอร๜เจีย;gruusia keel;jorjieg;pinulongang heyorhiyano','http://dbpedia.org/resource/Georgian_language','Georgian'),('sag','sg','bahasa sango;sango language;sango;�?고어;lengua sango;sangoa lingvo;サンゴ語;sangoeg;idioma sango','http://dbpedia.org/resource/Sango_language','Sango'),('ara','ar','gjuha arabe;kiarabu;아�?어;arapski jezik;lingua arabe;arabek;carabi;ພາສາອາຣັບ;arabeg;arabe;arabisk;arabiska;fiteny arabo;arap tili;lingua araba;arraabish;arabic;bahsa arab;arabi;arabu kalba;अरबी;阿拉伯语;arap dili;erabek;basa arab;liarabi;arabisch;അറബി ഭാഷ;arabiera;अरबी भाषा;arabiana linguo;tiếng Ả rập;আরবি ভাষা;阿拉伯語;ዓረብ᚛;�?რ�?ბული ენ�?;araabsche spraak;அரப�? ம௚ழி;arabo;kilabu;arabya simi;araabia kiil;�?ר�?ביש;arab nyelv;ਅਰਬੀ ਭਾਸ਼ਾ;idioma arabe;an araibis;lingua arabica;阿剜伯話;bahasa arab;arabysk;araabia keel;అరబ�?బీ భాష;inarabo;arabian kieli;wikang arabe;l:阿拉伯語;arab tili;arabiko;arabic language;arbii bhasa;arab;アラビア語;erebki;ภาษาอาหรับ;ערבית;araba lingvo;�?�ᕋᕕ/aravi;arabies;ཨ་རབ་ས�?ད�?;ಅರಬ�?ಬೀ ಭಾಷೆ','http://dbpedia.org/resource/Arabic_language','Arabic'),('dan','da','danski jezik;kidenmark;�?�마�?�어;deens;danois;dansk;デンマーク語;bahasa denmark;lingua danica;danska;dana lingvo;דנית;丹麦语;danimarka dili;डॅनिश भाषा;an danmhairgis;danish;danish language;deensk;dan tili;danu kalba;dens leid;ภาษาเดนมาร๜�?;danvargish;daniera;qallunaatut;დ�?ნიური ენ�?;tok denmak;gjuha daneze;डेनिश भाषा;tanskan kieli;lingua danese;daneg;ডেনীয় ভাষা;daniana linguo;taani keel;danek;danca;isidenishi;lingua dinamarquesa;dan simi','http://dbpedia.org/resource/Danish_language','Danish'),('lit','lt','litaus;lingua lituana;litvanca;ภาษาลิทัวเนีย;litva dili;leedu keel;litova lingvo;리투아니아어;ლიტვური ენ�?;lengua lituana;litauiska;litausche spraak;lituwa simi;litauisk;lithywanek;lituanian;litvanski jezik;litaanish;litavski jezik;ליט�?ית;talitwanit;tok lituwenia;lithuanian language;idioma lituano;lituanien;bahasa lituavi;litousk;litouws;lietoviu kalba;लिथ�?�?नियन भाषा;litewsko godka;isi-lithuanian;lituaniana linguo;lituanu;liettuan kieli;gjuha lituane;pinulongang litwano;liettuvagiella;lituaniera;lithuanie leid;リトアニア語;lithyuanyan;litauisch;lithuanian;bahasa lithuania;tiếng litva;立陶宛语;lenga lituan-a','http://dbpedia.org/resource/Lithuanian_language','Lithuanian'),('fin','fi','ffinneg;finnsche spraak;finnek;finngbe;fins;फ़िनिश भाषा;finsk;finna lingvo;finnisch;somu valoda;finska;suomen kieli;finnlynnish;lingua fillannisa;finneg;bahasa suomi;finnois;fince;lingua finlandaisa;finlandana linguo;finski jezik;finnish leid;an fhionlainnis;phinis simi;finlandiera;fin dili;ფინური ენ�?;lingua finesa;pinulongang pines;finnish;soome keel;பின�?னிய ம௚ழி;fin tili;פינית;finnska;lingua finnica;フィンランド語;lingua finlandese;fionnais;蚬兰语;finn nyelv;lingua finnese;gjuha finlandeze;bahasa finland;핀란드어;tiếng phần lan;ফিনীয় ভাষা;suomagiella;finnish language;lengua finlandesa','http://dbpedia.org/resource/Finnish_language','Finnish'),('nau','na','나우루어;瑙�?语;lingua nauruana;dorerin naoero;ペウル語;ᚓ�?ሩ᚛;naura lingvo;nauruaans;nauruan;bahasa nauru;naurun kieli;lengua naureise;ภาษานาอูรู;nauruca;nauriska;נ�?ורית;lingua nauruaisa;naoeroeg;nauruan language;idioma nauruano;nauru;nauru keel;naurisk;nauruisch','http://dbpedia.org/resource/Nauruan_language','Nauruan language'),('swe','sv','स�?वीडिश भाषा;lingua svedese;swadish leid;욤웨�?�어;swedeg;zweeds;ruotsin kieli;suec;ᔅᕗ�?ᔅᑭ�?ᑦ;idioma sueco;swedgbe;lingua suecica;sveda lingvo;lingua svedaisa;স�?য়েডীয় ভাষা;roodsi kiil;sweedsk;rootsi keel;zviedru valoda;svenska;suediera;スウェーデン語;pinulongang sweko;svedeg;isiswidishi;suwiri simi;svensk;suecu;bahasa swedia;swedek;瑞典語;lengua svedeise;kiswidi;schwedisch;ภาษาสวีเดน;gjuha suedeze;suediana linguo;bahasa sweden;swedish;suainis;שבדית;an tsualainnis;შვედური ენ�?;swedish language;lingua sueca;soolynnish;svedes;sweeds;�?��?��?�;שוועדיש','http://dbpedia.org/resource/Swedish_language','Swedish'),('zul','zu','zulum;zulu;bahasa zulu;zulua lingvo;zoulou;lingua zulu;kizulu;zoeloe;줄루어;limba zulu;zulu language;isizulu;swlw;zulun kieli;zulu jezik;zuluera;ச�?ல�? ம௚ழி;zulu simi;জ�?ল�? ভাষা;祖�?语;ズールー語;gjuha zulu','http://dbpedia.org/resource/Zulu_language','Zulu'),('roh','rm','kirumanj;lenghe romanze;romanche;reto-romaans;reto-roemaans;roumancho;lingua romancia;erromantxera;romanx;retoromaani;ሮማᚕሽ;რეტ�?რ�?მ�?ნული ენ�?;로맜욈어;உரோமாஞ�?ச�? ம௚ழி;罗曼什语;romansh language;roumantche;romansch;roumantsh;romans nyelv;bahasa romansh;retoromansk;romancica lingua;רומ�?נש;romaunsch;rumancc;rumanch;romansh;rumantsch dal grischun;romanch;ロマンシュ語;lingua rhetoroman','http://dbpedia.org/resource/Romansh_language','Romansh'),('ewe','ee','ewe;evu valoda;エウェ語;ewe language;ewen kieli;eweeg;bahasa ewe;idioma ewe;ევე;fiteny eve;lingua ewe;eve kalba;evea lingvo','http://dbpedia.org/resource/Ewe_language','Ewe'),('rus','ru','russian language;רוסיש;உர�?சிய ம௚ழி;orosz nyelv;俄语;krievu valoda;russi bhasa;rusiana linguo;俄文;rusko godka;ruski;rus;russ’sche spraak;rus tili;rushan;ruski jezik;russies;fiteny rosy;russisch;bahasa rusia;vene keel;lingua russa;rinuso;lingua russica;errusiera;lingua rusa;basa rusia;russian;ruiseis;רוסית;rusu simi;isirashiya;rusikani chhib;rusianeg;lengua russa;ರಷ�?ಯಾದ ಭಾಷೆ;russo;rwseg;russe;rus dili;russysk;tok rasia;kirusi;रूसी भाषा;lenga russa;ロシア語;rinusyan;ภาษารัสเซีย;rusa lingvo;gjuha ruse;რუსული ენ�?;tiếng nga;russek;ryska;wikang ruso;russisk;ཨུ་རུ་སུའི་ས�?ད�?;idioma ruso;俄語;lang ris;rosu kalba;l:俄語;റഷ�?യൻ ഭാഷ;rooshish;ruso;rukybau;lingua russe;vinne kiil;russesch;rusu;roushie leid;रशियन भाषा;ਰੂਸੀ ਭਾਸ਼ਾ;র�?শ ভাষা','http://dbpedia.org/resource/Russian_language','Russian'),('toi','to','tonga (zambia);chitonga;tonga;tonga (tonga islands);tonga language','',NULL),('ton','to','tonga (zambia);chitonga;tonga;tonga (tonga islands);tonga language','http://dbpedia.org/resource/Tonga_language_(Zambia)','Tonga'),('cre','cr','cree','','Cree'),('tah','ty','idioma tahitiano;reo tahiti;tahitian;lengua tahitiann-a;tahityan;tahiti simi;tahitien;tahitiska;tahitieg;tahitiaans;타히티어;tahitisk;tahitian language;tahitianisch;tahitin kieli;タヒ�?語','http://dbpedia.org/resource/Tahitian_language','Tahitian'),('her','hz','otjiherero;herero;bahasa herero;herera lingvo;herero dili;ヘレロ語;herero language;हीरीरो भाषा;ภาษาเฮเรโร;limba herero;lingua herero;herereg;hereron kieli;idioma herero','http://dbpedia.org/resource/Herero_language','Herero'),('grn','gn','guarani','','Guarani'),('twi','tw','twi;idioma twi;トウィ語;த�?வி ம௚ழி;lingua twi;twi jezik;twieg;契維語;akana lingvo;bahasa twi','http://dbpedia.org/resource/Twi','Twi'),('ibo','ig','igbo language;igbon kieli;lenga igbo;igbo;lingua ibo;idioma igbo;igboeg;bahasa igbo;ইগবো ভাষা;asụsụ igbo;ibo;igbo simi;イボ語;ibo-linguo;会�?�語;igba lingvo;இக�?போ ம௚ழி;lingua igbo','http://dbpedia.org/resource/Igbo_language','Igbo'),('san','sa','sanskrit;sanskrito;sanskrit simi;sanscrit;梵文;sanskryt;संस�?कृतीकानी छीब;sanskrity;szanszkrit nyelv;basa sangsakerta;bahasa sanskerta;サンスクリット;संस�?कृत;sanscrito;梵語;సంస�?కృతమ�?;sanskriti keel;sanskriet;סנסקריט;সংস�?কৃত ভাষা;সংস�?কৃত;산욤�?�리횸어;lingua sanscrita;wikang sanskrito;fan-vun;sanskritas;sanskrt;સંસ�?કૃત ભાષા;ሳᚕስᚭሪት;kisanskrit;ལེགས་སྦྱར་ས�?ད�?;sansgrit;சமச�?கிர�?தம�?;ภาษาสันส�?ฤต;संस�?कृत भाषा;sinanskrit;sanskrytek;pinulongang sanskrito;sanskrita linguo;संस�?�?कृत भाषा;basa sangskreta;ს�?ნსკრიტი;sanscrait;sanskrita kalba;梵语;സംസ�?കൃതം;ಸಂಸ�?ಕೃತ;sanskrits;tiếng phạn;संस�?कृतम�?','http://dbpedia.org/resource/Sanskrit','Sanskrit'),('cor','kw','kornisch;কর�?নিশ ভাষা;cornish language;ᚮርᚕ᚛;cornish;korniska;kornisk;korni keel;bahasa kernowek;cornisch;korni nyelv;lingua cornubica;koornsche spraak;kornies;コーンウォール語;an choirnis;kernewek;康瓦爾語;kornubiera;kornvala lingvo;cornic;kerneveureg;korni;קורנית;bahasa cornish;cornish leid;cernyweg;cornique;lingua cornica;콘월어;kornysk;kornu valoda','http://dbpedia.org/resource/Cornish_language','Cornish'),('iku','iu','inuktitut','','Inuktitut'),('fij','fj','fijian language;�?浞语;fidjien;phiyi simi;fidschi;fiji dili;kaiviti bhasa;フィジー語;idioma fiyiano;lingua figiana;fijiansk;fijianska;fijian;fijisch;lengua fijann-a','http://dbpedia.org/resource/Fijian_language','Fijian'),('bel','be','wit-russies;belorusa lingvo;lenga bielorussa;בעל�?רוסיש;bahasa belarus;idioma bielorruso;belarusz nyelv;বেলার�?শীয় ভাষা;idioma belorruso;lingua bielorussa;reo belarus;valgevene keel;bjeloruski jezik;belarooshish;bielorrusiera;gjuha bjelloruse;ಬೆಲಾರೂಸ�?“ನ ಭಾಷೆ;벨�?�루욤어;vitryska;bielorusiana linguo;baltkrievu valoda;lengua bielorussa;lingua ruthenica alba;pinulongang byeloruso;wittruss\'sch;בל�?רוסית;belarusian language;bealaruisis;lingua bielorrusa;wit-russisch;白俄罗斯语;ภาษาเบลารุส;belarusian;ბელ�?რუსული ენ�?;godu kalba;bielorrusu;belarussek;kibelarus;witrussisch;ベラルーシ語;bilurusu simi;hviterussisk','http://dbpedia.org/resource/Belarusian_language','Belarusian'),('aka','ak','akan','','Akan'),('oss','os','�?სური ენ�?;ossetic;idioma osetio;osetki;ossetish;oset tili;osseta;ossetisk;ossetiska;ossetic language;奧塞梯語;oseteg;lengua osseta;bahasa ossetia;ภาษาออสเซติ�?;pinulongang osetyo;オセット語;ossetian;오세횸어;osseetin kieli;ossetisch;oseta lingvo;lingua osseta;usit simi;ஒசேத�?திய ம௚ழி;ᚢሮᚕ᚛','http://dbpedia.org/resource/Ossetic_language','Ossetic'),('ven','ve','ヴェンダ語;tshivenda;gjuha venda;vendeg;vendan kieli;venda;lingua venda;idioma venda;vendum;venda language;tshivenḓa','http://dbpedia.org/resource/Venda_language','Venda'),('bod','bo','tibetan','','Tibetan'),('est','et','estonien;estonian;estnisch','','Estonian'),('tam','ta','tamilski jezik;tamoul;bahasa tamil;tiếng tamil;tamil;타밀어;तमिल भाषा;tamil language;lingua tamil;tamil bhasa;தமிழ�?;tamilek;ತಮಿಳ�?;tamilsk;തമിഴ�?;tamili keel;lingua tamulica;tamilera;तमिळ�?“;तमिळ भाषा;tamil simi;tamileg;tamil nyelv;ภาษาทมิฬ;tinamil;basa tamil;gjuha tamile;tamilu valoda;tamilikani chhib;泰米尔语;তামিল ভাষা;tamilce;idioma tamil;tamila lingvo;ტ�?მილური ენ�?;తమిళ భాష;kitamil;lengua tamil;טמילית','http://dbpedia.org/resource/Tamil_language','Tamil'),('sqi','sq','albanais;albanaises, langues;albanian language;albanian languages;langue albanaises;albanian','http://dbpedia.org/resource/Albanian_languages','Albanian'),('mlg','mg','malagasi simi;lingua malgascia;lingua malagasy;malagasy;gassisk;মালাগাসি ভাষা;bahasa malagasi;�?�?�가시어;malagasy language;plateaumalagasi;malgaix;ภาษามาลา�?าซี;malagassi keel;lingua malgaxe;马拉嚠斯语;malagasieg;malgache;idioma malgache;መለᜋሲ;gjuha madagaskare;tamalgacit;malagassiska;lengua malagascia;מלגשית;マダガスカル語;malagassi;malagasish;fiteny malagasy;मलगासी;malgaxe;மலகாசி ம௚ழி;malagasa lingvo','http://dbpedia.org/resource/Malagasy_language','Malagasy'),('kur','ku','kurdish;kurde','','Kurdish'),('kon','kg','kongo language;kongoeg;kikongo;llingua congo;刚果语;kongum;kongo;konga lingvo;kisikongo;コンゴ語;bahasa kongo;kongo simi;콩고어;কঙ�?গো ভাষা;kicongo;kongon kieli;kongo dili;kongo kalba','http://dbpedia.org/resource/Kongo_language','Kongo'),('kas','ks','കശ�?മീരി ഭാഷ;kashmiri;kachmireg;idioma cachemir;lingua kashmiri;কাশ�?মীরি ভাষা;kasjmiri;카욈미르어;ಕಾಶ�?ಮೀರಿ;કાશ�?મીરી ભાષા;kashmirisk;lingua casmirica;कश�?मीरी;克什米爾語;kashmiri language;ภาษา�?คบเมียร๜;काश�?मिरी भाषा;ᚫሽሚር᚛;कश�?मीरी भाषा;bahasa kashmiri;காஷ�?மீரி ம௚ழி','http://dbpedia.org/resource/Kashmiri_language','Kashmiri'),('kal','kl','kalaallisut;그린란드어;groenlands;lingua groenlandese;grenlandski jezik;gronlanda lingvo;lingua groenlannisa;ग�?रीनलैंडिक भाषा;greenlandic;greenlynnish;groenlandais;lingua groenlandica;kalalit simi;lingua grenlandesa;greenlandic language;ภาษา�?ะลาลลิซุต;bahasa kalaallisut;格陵兰语;გრენლ�?ნდიური ენ�?;גרינלנדית;グリーンランド語;groenlandiera','http://dbpedia.org/resource/Greenlandic_language','Kalaallisut; Greenlandic'),('jpn','ja','gjuha japoneze;जपानी भाषा;basa jepang;japuonu kalba;tajaponit;nihonek;japonezy;hinapon;japans;isijaphani;iapanais;yapon dili;hapon;jaapani keel;wikang hapones;japaneg;japannees;जापानी भाषा;ი�?პ�?ნური ენ�?;lingua japonese;japoniana linguo;japanska;ಜಪಾನಿ ಭಾಷೆ;japonca;�?�본어;japanisch;日语;japanski jezik;יפנית;জাপানি ভাষা;日本語;japanese;japanese bhasa;hinapones;japansk;japana lingvo;japonais;ஜப�?பானிய ம௚ழி;japanese leid;japoniera;bahasa jepun;bahasa jepang;japanesch;nihun simi;日文;ဂျပန်ဘာသာစကား;י�?פ�?ניש;ཉི་ཧོང་ས�?ད�?;lingua giapponese;japanin kieli;lingua giappunisa;hapones;kijapani;lingua iaponica;shapaanish;ponbau;ภาษา�?ี่ปุ่น;bahsa jeupun;lingua xaponesa;lingua japonesa;japanese language;ജാപ�?പനീസ�? ഭാഷ;tiếng nhật;yapon tele','http://dbpedia.org/resource/Japanese_language','Japanese'),('dzo','dz','bahasa jongkha;dzongkha language;dzongkha keel;འབྲུག་པའི་ས�?ད�?;dzongkha;ஜ௚ங�?கா ம௚ழி;lingua dzongkha;dzongke;जोंगखा;宗喀語;종카어;རྫོང་�?་;dzonka lingvo;dzongkhan kieli;boutaneg;ภาษาซองคา;bahasa dzongkha;ゾンカ語;dzonkha simi;דזונגקה;dzongka;butaanish','http://dbpedia.org/resource/Dzongkha_language','Dzongkha'),('pan','pa','panjabi;punjabi;pendjabi','','Punjabi'),('chu','cu','oldkirkeslavisk;staroslavenski jezik;oudkerkslavisch;church slavonic;altkirchenslawisch;ou kerkslawies;vieux-slave;ძველი სლ�?ვური ენ�?;ภาษาโบสถ๜สลาโวนิ�?โบราณ;church slavic;muinaiskirkkoslaavi;lingua slavonica antiqua;hen slafoneg eglwysig;old church slavonic;old slavonic;antic eslau;bahasa gereja slavonia lama;�?�代教会スラヴ語;�?�教會斯拉夫語;eslavon;סל�?בית כנסייתית עתיקה;old bulgarian;fornkyrkoslaviska;antico slavo ecclesiastico;gammelkirkeslavisk','http://dbpedia.org/resource/Old_Church_Slavonic','Old Slavonic'),('sin','si','singhalais;singalesisk;sinhala simi;싱할�?�어;lingua singalese;sinhala;sinhala language;singalees;bahasa sinhala;ภาษาสิงหล;सिंहला भाषा;सिंहली भाषा;fiteny singalesa;cingalais;සිංහල භ�?ෂ�?ව;சிங�?களம�?;シン�?ラ語;sinhali;singhalesisch;sinhaleg;সিংহলি ভাষা;lingua singhalensis;sinhalese;seylanca;lengua singaleise;singalesiska;僧伽罗语;sinhala lingvo','http://dbpedia.org/resource/Sinhala_language','Sinhalese'),('mal','ml','lingua malabarica;ಮಲಯಾಳಂ;malayalam;malajalam;ภาษามาลายาลัม;malayalameg;मलयाळम�?“;מל�?י�?ל�?�?;malajalam jezik;马拉雅拉姆语;మలయాళ భాష;მ�?ლ�?ი�?ლ�?მი;মালয়ালম ভাষা;bahasa malayalam;मलयालम भाषा;�?�?�얄랜어;ማላያላ�?;lingua malayalam;મલયાલમ ભાષા;malajala lingvo;മലയാളം;malayalam simi;மலையாளம�?;fiteny malayalam;马拉雅�?�语;マラヤーラム語','http://dbpedia.org/resource/Malayalam','Malayalam'),('ell','el','nygresk;nygrekiska;יוונית מודרנית;tayunanit;griego moderno;grego moderno;grec modern;grec moderne;greek, modern (1453-);neegreeksche spraak;आध�?निक यूनानी भाषा;lingua neograeca;modern grieks;neugriechisch;lingua greca moderna;modern greek;modern greek (1453-)','http://dbpedia.org/resource/Modern_Greek','Modern Greek'),('ron','ro','lingua dacoromanica;rwmaneg;romanian language;roumanek;rumunski jezik;lingua rumena;lengua romenn-a;rumano;רומעניש;bahasa romania;roemeens;루마니아어;rumunikani chhib;romanian kieli;roemeensk;roumen;romanian;rumensk;roumin;உர�?மானிய ம௚ழி;idioma rumano;fiteny romana;rumence;რუმინული ენ�?;roman tili;kiromania;rumanya simi;bahasa rumania;ルーマニア語;rumanu;moldovan;errumaniera;ภาษาโรมาเนีย;moldavian;gjuha rumune;র�?মানীয় ভাষা;roumain;rumeenia keel;lingua romanian;羅馬尼亞語;rumunjski jezik;rumaniana linguo;pinulongang rumano;lingua romanesa;רומנית;rumana lingvo;roumaneg;tiếng romana;romaanish;limba romuna','http://dbpedia.org/resource/Romanian_language','Romanian; Moldavian; Moldovan'),('lin','ln','lingala;�?갈�?�어;林嚠拉语;lingala lingvo;ሚᚕᜋላ;リンガラ語;lingala language;lingala simi;lingua lingala;idioma lingala;dingala;lingalum;bahasa lingala','http://dbpedia.org/resource/Lingala_language','Lingala'),('ssw','ss','swati;svazia lingvo;siswati;bahasa swati;swazi;lengua siswati;lingua swati;idioma suazi;swazin kieli;swatum;swati language;swatieg;swasi simi;욤와티어;スワジ語','http://dbpedia.org/resource/Swati_language','Swati'),('orm','om','galla;oromo','','Oromo'),('pol','pl','polsk;poloniana linguo;polsko godka;pwyleg;leh tili;polacu;poloniera;lengua pulacca;lengyel nyelv;波兰语;pools;gjuha polake;lengua polacca;idioma polaco;poola keel;wikang polako;polynnish;�?ーランド語;polnisch;poljski jezik;�?�란드어;lingua polacca;lia-polaku;פויליש;lingua pulacca;poolsch;ཕོ་ལན་ས�?ད�?;פולנית;tiếng ba lan;bahasa poland;kipoland;पोलिश भाषा;bahasa polski;ภาษาโป�?ลนด๜;পোলীয় ভাষা;polska;polonais;polskkagiella;basa polski;poalsk;போலிய ம௚ழி;pols;puolan kieli;tok polan;lingua polonica;polish language;isipholisi;fiteny poloney;lingua polonese;პ�?ლ�?ნური ენ�?;an pholainnis;pola lingvo;poloneg;lingwa pollakka;polish;polnesch;polyak dili;polonek;pulaku simi;lingua polaca','http://dbpedia.org/resource/Polish_language','Polish'),('khm','km','central khmer;khmer central','','Central Khmer'),('yor','yo','yoruba;yoruba language;idioma yoruba;约�?巴语;ი�?რუბ�?;lingua yoruba;ioruba;yorouba;yoruba simi;joruba lingvo;ইয়োর�?বা ভাষা;bahasa yoruba;joruba;joruban kieli;ய௚ரூபா ம௚ழி;lenga yoruba;ヨル�?語;yoroubeg','http://dbpedia.org/resource/Yoruba_language','Yoruba'),('mri','mi','maoru valoda;maori;毛利语;maorisk;maoriera;maori nyelv;maorieg;maorin kieli;maoori keel;mawri simi;მ�?�?რული ენ�?;mawori;maorais;maoria lingvo;lingua maoriana;ማዖሪ ቋᚕቋ;lengua maori;マオリ語;מ�?ורית;maorisch;limba maori;bahasa maori;마오리어','http://dbpedia.org/resource/M�?ori_language','Maori'),('epo','eo','lingua esperantu;esperanto;�?욤힘란토;inesperanto;�?�ᓯ�?�ᕋ�?�?/isipirantu;lingua esperantica;�?स�?पेरान�?तो;quốc tế ngữ;世畜語;speranto;�?ספרנטו;bahasa esperanto;esperanteg;lenga esperanto;ภาษาเอสเปรันโต;ესპერ�?ნტ�?;esperanto simi;lingua esperanto;エスペラント;ഞസ�?പെരാന�?തോ;sprantais;kiseperanto;�?स�?पेरांतो;kiesperanto;עספער�?נט�?;l:世畜語;esperanto leid;�?ਸਪੇਰਾਨਤੋ;gjuha esperanto;ᚤስ�?�ራᚕቶ;esperanto tili;esperantos;世畜语','http://dbpedia.org/resource/Esperanto','Esperanto'),('wol','wo','wolof','','Wolof'),('bre','br','bretoni;bretona lingvo;bretonisch;bretonski jezik;ბრეტ�?ნული ენ�?;bretonsk;breton;breatannais;lenghe bretone;bahasa breton;bretonisk;lingua armoricana;burton;breton dili;bretonek;lingua bretona;bretonca;bretun;ברטונית;luenga bretona;breton language;breton nyelv;lingua bretoa;ภาษาเบรอตง;brezhoneg;bretainiera;britaanish;bretonska;lenga breton-a;britun simi;bretons;llydaweg;븜르타뉴어;布列塔尼语;bretoens;breton leid;gjuha bretoneze;ブルトン語;lengua bretone;bretonagiella;lingua bretone;brettonish','http://dbpedia.org/resource/Breton_language','Breton'),('wln','wa','waols;ภาษาวัลลูน;valonski jezik;walloneg;waals;valona lingvo;wallonisch;wallon;wallounesch;tiếng wallon;walloonish;瓦龙语;vallonsk;walloon language;valon;valloni;wallonek;walloon;wallonies;woals;walon;vallonska;ולונית;bahasa walloon;lengua vallone;valonca;lingua vallone;왈롱어;valoiera;walloonsch;ワロン語','http://dbpedia.org/resource/Walloon_language','Walloon'),('srp','sr','crnogorski jezik;몬템네그로어;montenegrin language;montenegron kieli;lingua montinigrina;モンテ�?グロ語;מונטנגרית;serbian;montenegrijns;serbe;lenga montneigrin-a;montenegrinsk;lingua montenegrina;lengua montenegrina;gjuha malazeze;montenegrinisch;montenegrinska;gagana montenegro;蒙特內哥羅語','http://dbpedia.org/resource/Montenegrin_language','Serbian'),('xho','xh','코사어;科�?�语;xhosa;idioma xhosa;xhosa simi;kosa lingvo;xhosa language;bahasa xhosa;xosa-linguo;isixhosa;xosa;コサ語;xhosaeg;lingua xhosa;fiteny xhosa;gjuha xhosa;kosum;xhosan kieli','http://dbpedia.org/resource/Xhosa_language','Xhosa'),('tir','ti','idioma tigrinya;ት�?ር᚛;तिग�?रिन�?या भाषा;lengua tigrinn-a;tigraja lingvo;תיגרינית;tigrinya language;tigrinya;tigrigna;tigrinya nyelv;lingua tigrina;tigrinya dili;tigrinja jezik;tigrinja;ティグリニャ語;tigrinyera;tigrigneg;bahasa tigrinya;�??格利尼亞語;তিগ�?রিনিয়া ভাষা;ภาษาที�?รินยา;tigrinska;limba tigrinya;티그리�?어;kitigrinya;ት�?ር᚛ �?�ደ�?','http://dbpedia.org/resource/Tigrinya_language','Tigrinya')
;";
mysql_query($fill_language);


$create_language_level = "CREATE TABLE `language_level` (
  `ilr_level` int(1) unsigned NOT NULL,
  `eng` set('elementary','basic','extremely limited','limited','limited working','fair','modest','competent','professional working','working knowledge','good','very good','full professional','fluent','expert','native','bilingual','mother tongue','excellent') NOT NULL,
  `deu` set('elementar','grundkenntnisse','einfach','basiswissen','schulkenntnisse','erweiterte grundkenntnisse','selbst�ndig','begrenzt','angemessen','mittlere Kenntnisse','gut','flie�end','konversationssicher','kompetent','verhandlungssicher','sehr gut','muttersprache','muttersprachlich') NOT NULL,
  `spa` set('nociones','elemental','basico','limitada','acceso','plataforma','limitada de trabajo','umbral','independiente','bueno','muy bueno','profesional de trabajo','dominio\nfluido','profesional plena','maestr�a','nativa','biling�e','idioma materno') NOT NULL,
  `por` set('elementar','b�sico','limitada','iniciante','profissional limitada','intermedi�rio','independente','profissional','bom','muito bom','proficiente','fluente','profissional pleno','dom�nio pleno','\nnativa','bil�ng�e','l�ngua materna') NOT NULL,
  `fra` set('introductif','d�couverte','seuil','ind�pendant','bon','tr�s bon','autonome','ma�trise','bilingue') NOT NULL,
  `labels` text,
  PRIMARY KEY (`ilr_level`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
mysql_query($create_language_level);

$fill_language_level = "INSERT INTO `language_level` VALUES (1,'elementary,basic,extremely limited,limited','elementar,grundkenntnisse,einfach,basiswissen,schulkenntnisse','nociones,elemental,basico,limitada,acceso,plataforma','elementar,b�sico,limitada,iniciante','introductif,d�couverte',NULL),(2,'limited working,fair,modest,competent','erweiterte grundkenntnisse,selbst�ndig,begrenzt,angemessen,mittlere Kenntnisse','limitada de trabajo,umbral,independiente','profissional limitada,intermedi�rio,independente','seuil,ind�pendant',NULL),(3,'professional working,working knowledge,good,very good','gut,flie�end,konversationssicher','bueno,muy bueno,profesional de trabajo','profissional,bom,muito bom,proficiente','bon,tr�s bon,autonome',NULL),(4,'full professional,fluent,expert,excellent','kompetent,verhandlungssicher,sehr gut','profesional plena,maestr�a','fluente,profissional pleno,dom�nio pleno','ma�trise',NULL),(5,'native,bilingual,mother tongue','muttersprache,muttersprachlich','nativa,biling�e,idioma materno','bil�ng�e,l�ngua materna','bilingue',NULL);
";
mysql_query($fill_language_level);

$create_TODO_job_required_languages = "CREATE TABLE IF NOT EXISTS `TODO_job_required_languages` (
  `job_ID` int(255) NOT NULL,
  `language_name` varchar(255) NULL,
  `language_level_name` varchar(255) NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
mysql_query($create_TODO_job_required_languages);

$query_statistics_before = "SELECT * FROM `statistics` WHERE ID = (SELECT MAX(ID) FROM statistics)";
$statistics = mysql_query($query_statistics_before);
$statistics_a = mysql_fetch_array($statistics);

//aktuellste Seite

$ch = curl_init();
for ($t=$time_start;$t <=$time_end;$t +=86400){
    $date = date("d-m-Y",$t);

curl_setopt($ch, CURLOPT_URL, 'http://ec.europa.eu/euraxess/index.cfm/jobs/jobsPerDay/'.$date);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//curl_setopt($ch, CURLOPT_USERAGENT, '[Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.2) Gecko/20070219 Firefox/2.0.0.2")]');
//establishing connection
$data_jobs = curl_exec($ch);
$data_jobs = utf8_decode($data_jobs);

// get number of results

$pattern_results = ("/<div class=\"subtitle\">\s*([\d]*)/s");
preg_match_all($pattern_results, $data_jobs, $pattern_results_match);
$results = intval($pattern_results_match[1][0]);
$number_of_pages = ceil($results/15);
$ids_array[$date] = array($number_of_pages);
// get ids

for ($i=1; $i <=$number_of_pages; $i +=1){
    $ch2 = curl_init();
    $url = 'http://ec.europa.eu/euraxess/index.cfm/jobs/jobsPerDay/'.$date.'/page/'.$i;
    curl_setopt($ch2, CURLOPT_URL, $url);
curl_setopt($ch2, CURLOPT_HEADER, 0);
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
//curl_setopt($ch, CURLOPT_USERAGENT, '[Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.2) Gecko/20070219 Firefox/2.0.0.2")]');
//establishing connection
$data_ids = curl_exec($ch2);
$data_ids = utf8_decode($data_ids);
$pattern_ids = ("/<a href=\"index.cfm\/jobs\/jobDetails\/([\d]*)/s");
preg_match_all($pattern_ids, $data_ids, $pattern_ids_match);
$ids = array($pattern_ids_match[1]);

$ids_array[$date] = array_merge($ids_array[$date], $ids);
}
}
foreach ($ids_array as $promotion_date =>$id){
    for ($j=1;$j<=$id[0];$j +=1){
        foreach ($id[$j] as $job_id){

            $original_url = 'http://ec.europa.eu/euraxess/index.cfm/jobs/jobDetails/'.$job_id;
            $ch3 = curl_init();
curl_setopt($ch3, CURLOPT_URL, $original_url);
curl_setopt($ch3, CURLOPT_HEADER, 0);
curl_setopt($ch3, CURLOPT_RETURNTRANSFER, 1);
//curl_setopt($ch, CURLOPT_USERAGENT, '[Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.2) Gecko/20070219 Firefox/2.0.0.2")]');
//establishing connection
$data = curl_exec($ch3);
$data = utf8_decode($data);
//matching title

//check if image is in title
$pattern_image = ("/<div[^>]*><strong>[^>]*>[^>]*>\s*<h1>\s*<i[^>]*>\s*/s");
$imagecheck = preg_match_all($pattern_image, $data, $image_check);

if($imagecheck==1){
$pattern_title = ("/<div[^>]*><strong>[^>]*>[^>]*>\s*<h1>\s*<i[^>]*>\s*([^<]*)\n/s");
preg_match_all($pattern_title, $data, $pattern_title_match);
$title = $pattern_title_match[1][0];
    
}
else
    {
$pattern_title = ("/<div[^>]*><strong>[^>]*>[^>]*>\s*<h1>\s*<?i?[^>]*?>?\s*([^<]*)\n/s");
preg_match_all($pattern_title, $data, $pattern_title_match);
$title = $pattern_title_match[1][0];
}

//matching summary
$pattern_summary = ("/<\/button>\s*<\/div>\s*<p>([^~]*?)<\/p>/s");
preg_match_all($pattern_summary, $data, $pattern_summary_match);
$summary = $pattern_summary_match[1];

//matching description
$pattern_description = ("/<h2>Description<\/h2>\s*<p>([^~]*?)<\/p>/s");
preg_match_all($pattern_description, $data, $pattern_description_match);
$description = $pattern_description_match[1];

//matching research_fields
$pattern_research_fields = ("/<h3>Research Fields<\/h3>\s*<p>\s*([^~]*?<\/p>)/s");
preg_match_all($pattern_research_fields, $data, $pattern_research_fields_match);
$pattern_research_fields2 = ("/\s*([^\<]*)\s?<[^>]*>/s");
preg_match_all($pattern_research_fields2, $pattern_research_fields_match[1][0] , $research_fields);
//// dividing research fields (if possible)
//$pattern_divide = ("/([^\s]*)\s*-\s*([^\n]*)<b/s");
//preg_match_all($pattern_divide, $pattern_research_fields_match[1][0], $divide_match);
//
//if(isset($divide_match[1][0])==TRUE){
//    $research_fields = $divide_match;
//}
//
//

//matching career_stage
$pattern_career_stage= ("/<h3>Career Stage<\/h3>\s*<p>\s*([^~]*?)<\/p>/s");
preg_match_all($pattern_career_stage, $data, $pattern_career_stage_match);
$pattern_career_stage2 = ("/\s*([^<]*)<[^>]*>/s");
preg_match_all($pattern_career_stage2, $pattern_career_stage_match[1][0] , $career_stage);

foreach ($career_stage[1] as $key =>$value)
{
$career_stage[$key] = rtrim($value);
}


//matching benefit
$pattern_benefit = ("/<h3>Benefits<\/h3>\s*<p>([^~]*?)<\/p>/s");
preg_match_all($pattern_benefit, $data, $pattern_benefit_match);
$benefit = $pattern_benefit_match[1];

//matching comment
$pattern_comment = ("/<h2>Comment[^<]*<\/h2>\s*<p>([^~]*?)<\/p>/s");
preg_match_all($pattern_comment, $data, $pattern_comment_match);
$comment = $pattern_comment_match[1];

//matching language
$pattern_language = ("/<th[^>]*>Language<[^>]*>\s*<td>\s*([^\n]*)\s*/s");
preg_match_all($pattern_language, $data, $pattern_language_match);
$language = $pattern_language_match[1];

foreach ($language as $key =>$value)
{
    $language_trim = rtrim($value);
    $language[$key] = ucfirst(strtolower($language_trim));
}

//matching language_level
$pattern_language_level = ("/<th[^>]*>Language Level<[^>]*>\s*<td>\s*([^\n]*)\s*/s");
preg_match_all($pattern_language_level, $data, $pattern_language_level_match);
$language_level = $pattern_language_level_match[1];

foreach ($language_level as $key =>$value)
{
$language_level_trim = rtrim($value);
$language_level[$key] = strtolower($language_level_trim);
}

//matching degree
$pattern_degree = ("/<th[^>]*>Degree<[^>]*>\s*<td>\s*([^\n]*)/s");
preg_match_all($pattern_degree, $data, $pattern_degree_match);
$degree = $pattern_degree_match[1];

foreach ($degree as $key =>$value)
{
$degree[$key] = rtrim($value);
}

//matching degree_field
$pattern_degree_field = ("/<th[^>]*>Degree Field<[^>]*>\s*<td>\s*([^\n]*)/s");
preg_match_all($pattern_degree_field, $data, $pattern_degree_field_match);
$degree_field = $pattern_degree_field_match[1];

foreach ($degree_field as $key =>$value)
{
$degree_field[$key] = rtrim($value);
}

//matching research_experience
$pattern_research_experience = ("/<th[^>]*>Main Research Field<[^>]*>\s*<td>\s*([^\n]*)/s");
preg_match_all($pattern_research_experience, $data, $pattern_research_experience_match);
$research_experience = $pattern_research_experience_match[1];

foreach ($research_experience as $key =>$value)
{
$research_experience[$key] = rtrim($value);
}

//matching research_sub_experience
$pattern_research_sub_experience = ("/<th[^>]*>Research Sub Field<[^>]*>\s*<td>\s*([^\n]*)/s");
preg_match_all($pattern_research_sub_experience, $data, $pattern_research_sub_experience_match);
$research_sub_experience = $pattern_research_sub_experience_match[1];

foreach ($research_sub_experience as $key =>$value)
{
$research_sub_experience[$key] = rtrim($value);
}

//matching research_years_experience
$pattern_research_years_experience = ("/<th[^>]*>Years of Research Experience<[^>]*>\s*<td>\s*([^\n]*)\s*/s");
preg_match_all($pattern_research_years_experience, $data, $pattern_research_years_experience_match);
$research_years_experience = $pattern_research_years_experience_match[1];

foreach ($research_years_experience as $key =>$value)
{
$research_years_experience[$key] = rtrim($value);
}

//matching additional_requirements
$pattern_requirements = ("/<caption>Additional[^>]*>\s*<[^>]*>\s*([^~]*)?<\/tbody>/s");
preg_match_all($pattern_requirements, $data, $pattern_requirements_match);
$pattern_requirements2 = ("/<td[^>]*>([^~]*?)<\/td>/s");
preg_match_all($pattern_requirements2, $pattern_requirements_match[1][0] , $requirements);

//matching job_id
$pattern_job_id = ("/Job ID<[^>]*>\s*<p[^>]*>([^<]*)<\/p>/s");
preg_match_all($pattern_job_id, $data, $pattern_job_id_match);
$job_id = $pattern_job_id_match[1];

//matching contract_type
$pattern_contract_type = ("/Type of Contract<[^>]*>\s*<p[^>]*>\s*([^\n]*)\s*<\/p>/s");
preg_match_all($pattern_contract_type, $data, $pattern_contract_type_match);
$contract_type = $pattern_contract_type_match[1];

foreach ($contract_type as $key =>$value)
{
$contract_type[$key] = rtrim($value);
}

//matching status
$pattern_status = ("/Status<[^>]*>\s*<p[^>]*>\s*([^\n]*)\s*<\/p>/s");
preg_match_all($pattern_status, $data, $pattern_status_match);
$status = $pattern_status_match[1];

foreach ($status as $key =>$value)
{
$status[$key] = rtrim($value);
}

//matching hours_per_week
$pattern_hours_per_week = ("/Hours Per Week<[^>]*>\s*<p[^>]*>\s*([^\n]*)\s*<\/p>/s");
preg_match_all($pattern_hours_per_week, $data, $pattern_hours_per_week_match);
$hours_per_week = $pattern_hours_per_week_match[1];

foreach ($hours_per_week as $key =>$value)
{
$hours_per_week[$key] = rtrim($value);
}

//matching company / institute
$pattern_company = ("/Company\/Institute<[^>]*>\s*<p[^>]*>\s*([^\n]*)\s*<\/p>/s");
preg_match_all($pattern_company, $data, $pattern_company_match);
$company = $pattern_company_match[1];

foreach ($company as $key =>$value)
{
$company[$key] = rtrim($value);
}

//matching country
$pattern_country = ("/Country<[^>]*>\s*<p[^>]*>\s*([^\n]*)\s*<\/p>/s");
preg_match_all($pattern_country, $data, $pattern_country_match);
$country = $pattern_country_match[1];

foreach ($country as $key =>$value)
{
$country_trim = rtrim($value);
$country[$key] = ucfirst(strtolower($country_trim));
}

//matching Community language
$pattern_community_language = ("/Community Language<[^>]*>\s*<p[^>]*>\s*([^\n]*)\s*<\/p>/s");
preg_match_all($pattern_community_language, $data, $pattern_community_language_match);
$community_language = $pattern_community_language_match[1];

foreach ($community_language as $key =>$value)
{
$community_language[$key] = rtrim($value);
}

//matching state_province
$pattern_state_province = ("/Province<[^>]*>\s*<p[^>]*>\s*([^\n]*)\s*<\/p>/s");
preg_match_all($pattern_state_province, $data, $pattern_state_province_match);
$state_province = $pattern_state_province_match[1];

foreach ($state_province as $key =>$value)
{
$state_province[$key] = rtrim($value);
}

//matching city
$pattern_city = ("/City<[^>]*>\s*<p[^>]*>\s*([^\n]*)\s*<\/p>/s");
preg_match_all($pattern_city, $data, $pattern_city_match);
$city = $pattern_city_match[1];

foreach ($city as $key =>$value)
{
$city[$key] = rtrim($value);
}

//matching postal_code
$pattern_postal_code = ("/Postal Code<[^>]*>\s*<p[^>]*>\s*([^\n]*)\s*<\/p>/s");
preg_match_all($pattern_postal_code, $data, $pattern_postal_code_match);
$postal_code = $pattern_postal_code_match[1];

//matching street
$pattern_street = ("/Street<[^>]*>\s*<p[^>]*>\s*([^\n]*)\s*<\/p>/s");
preg_match_all($pattern_street, $data, $pattern_street_match);
$street = $pattern_street_match[1];

//matching framework_programme
$pattern_framework_programme = ("/Marie Curie Actions<[^>]*>\s*<p[^>]*>\s*([^\n]*)\s*<\/p>/s");
preg_match_all($pattern_framework_programme, $data, $pattern_framework_programme_match);
$framework_programme = $pattern_framework_programme_match[1];

//matching Sesam agreement Number
$pattern_sesame = ("/SESAM Agreement Number<[^>]*>\s*<p[^>]*>\s*([^\n]*)\s*<\/p>/s");
preg_match_all($pattern_sesame, $data, $pattern_sesame_match);
$sesame = $pattern_sesame_match[1];


//matching company_info
$pattern_company_info = ("/Company\/Institute<[^>]*>\s*<d[^>]*>([^<]*)<[^>]*>\s*<[^>]*>\s*([^~]*?)<\/p>/s");
preg_match_all($pattern_company_info, $data, $pattern_company_info_match);

// Check if company_name is already set, if not use the one from pattern_company_info_match

if(array_key_exists('0',$company)==FALSE)
{
    $company = $pattern_company_info_match[1];
}


// get phone_number(s)
$pattern_phone_number = ("/phone\s*([^<]*)/s");
preg_match_all($pattern_phone_number, $pattern_company_info_match[2][0], $pattern_phone_number_match);
//get fax_number
$pattern_fax_number = ("/fax\s*([^<]*)/s");
preg_match_all($pattern_fax_number, $pattern_company_info_match[2][0], $pattern_fax_number_match);
//get email
$pattern_email = ("/email\s<[^>]*>([^<]*)</s");
preg_match_all($pattern_email, $pattern_company_info_match[2][0], $pattern_email_match);
//get website
$pattern_website = ("/<A[^>]*>(.*)/s");
preg_match_all($pattern_website, $pattern_company_info_match[2][0], $pattern_website_match);

foreach ($pattern_phone_number_match as $phone_number){
$pattern_company_info_match_clean = str_replace($phone_number, "", $pattern_company_info_match[2][0]);
}
$pattern_company_info_match_clean = str_replace($pattern_fax_number_match[0], "", $pattern_company_info_match_clean);
$pattern_company_info_match_clean = str_replace($pattern_email_match[0], "", $pattern_company_info_match_clean);
$pattern_company_info_match_clean = str_replace($pattern_website_match[0], "", $pattern_company_info_match_clean);
$company_info = $pattern_company_info_match_clean;

//matching Job Starting Date
$pattern_starting_date = ("/Envisaged Job Starting Date<[^>]*>\s*<p[^>]*>\s*([^\n]*)\s*<\/p>/s");
preg_match_all($pattern_starting_date, $data, $pattern_starting_date_match);
$starting_date_wrong_format = $pattern_starting_date_match[1];
preg_match_all("/(\d*)\/(\d*)\/(\d*)/s",$starting_date_wrong_format[0],$starting_date_right_format);
$starting_date = ($starting_date_right_format[3][0]."-".$starting_date_right_format[2][0]."-".$starting_date_right_format[1][0]);

//matching Application Deadline
$pattern_application_deadline = ("/Application Deadline<[^>]*>\s*<p[^>]*>\s*([^\n]*)\s*<\/p>/s");
preg_match_all($pattern_application_deadline, $data, $pattern_application_deadline_match);
$application_deadline_wrong_format = $pattern_application_deadline_match[1];
preg_match_all("/(\d*)\/(\d*)\/(\d*)/s",$application_deadline_wrong_format[0],$application_deadline_right_format);
$application_deadline = ($application_deadline_right_format[3][0]."-".$application_deadline_right_format[2][0]."-".$application_deadline_right_format[1][0]);


//matching Application E-Mail
$pattern_application_email = ("/Application e-mail<[^>]*>\s*<p[^>]*>\s*<[^>]*>([^<]*)</s");
preg_match_all($pattern_application_email, $data, $pattern_application_email_match);
$application_email = $pattern_application_email_match[1];

//matching application website
$pattern_application_website = ("/Application website<[^>]*>\s*<p[^>]*>\s*<[^>]*>([^<]*)</s");
preg_match_all($pattern_application_website, $data, $pattern_application_website_match);
$application_website = $pattern_application_website_match[1];

//how_to_apply

$pattern_how_to_apply = ("/How To Apply<[^>]*>\s*<p[^>]*>\s*<[^\"]*\"([^\"]*)\"/s");
preg_match_all($pattern_how_to_apply, $data, $pattern_how_to_apply_match);
$how_to_apply = $pattern_how_to_apply_match[1];

if ($how_to_apply == 'small')
{
    $pattern_how_to_apply = ("/How To Apply<[^>]*>\s*<p[^>]*>\s*<[^>]*><[^\"]*\"([^\"]*)\"/s");
    preg_match_all($pattern_how_to_apply, $data, $pattern_how_to_apply_match);
    $how_to_apply = $pattern_how_to_apply_match[1];
}


//date posted

preg_match_all("/(\d*)\-(\d*)\-(\d*)/s",$promotion_date,$date_posted_right_format);
$date_posted = ($date_posted_right_format[3][0]."-".$date_posted_right_format[2][0]."-".$date_posted_right_format[1][0]);

//Clean the data

//remove whitespaces of website

$website = preg_replace("/\s*/s","",$pattern_website_match[1][0]);
$search = array("<br />", "/a>","</A>","<br/>","'");
$replace = array(" \n ","",""," \n "."\'");

//search for empty arrays only in research fields
foreach($research_fields[1] as $key => $value) {
    $research_fields[1][$key] = rtrim($value);
  if($value == "") {
    unset($research_fields[1][$key]);
  }
}


$values = array($title,$summary,$description,$research_fields[1],$career_stage,$benefit,$comment,$language,$language_level,$degree,$degree_level,$degree_field,$research_experience,$research_sub_experience,$research_years_experience,$requirements[1],$job_id,$contract_type,$status,$hours_per_week,$company,$country,$community_language,$state_province,$city,$postal_code,$street,$framework_programme,$sesame,$pattern_phone_number_match[1],$pattern_fax_number_match[1],$pattern_email_match[1],$website,$company_info,$starting_date,$application_deadline,$application_email,$application_website,$how_to_apply);
foreach($values as $key =>$value ){
    $values[$key] = str_replace($search, $replace, $value); 
}

//Connect to local database
mysql_connect(localhost,$username,$password) or die("Unable to connect to database");
mysql_select_db($database) or die("Unable to select database");



$query = "INSERT INTO job (ID,title, summary, description,status,hours_per_week,application_deadline,comment_website,benefits,sesame_agreement_number,application_starting_date,years_of_experience,research_experience,research_sub_experience,application_email,application_website,how_to_apply,date_posted,original_url)
VALUES('".$values[16][0]."','$values[0]','".$values[1][0]."','".$values[2][0]."','".$values[18][0]."','".$values[19][0]."','".$values[35]."','".$values[6][0]."','".$values[5][0]."','".$values[28][0]."','".$values[34]."','".$values[14][0]."','".$values[12][0]."','".$values[13][0]."','".$values[36][0]."','".$values[37][0]."','".$values[38][0]."','".$date_posted."','".$original_url."')
";
mysql_query($query);
echo mysql_errno() . ": " . mysql_error()."in Job_ID:".$values[16][0]. "\n";


//fill table company

//proof if company is already in the database
$query1 = "SELECT ID FROM company WHERE name = '".$values[20][0]."' AND website = '".$values[32]."'
    ";
$select_company = mysql_query($query1);
$select_company_a = mysql_fetch_array($select_company);

if ($values[20][0] !="" AND $select_company_a==FALSE){
$query2 = "INSERT INTO company (name,address,fax,email,website,country,state,city,postal_code,street,community_language)
    VALUES('".$values[20][0]."','".$values[33]."','".$values[30][0]."','".$values[31][0]."','".$values[32]."','".$values[21][0]."','".$values[23][0]."','".$values[24][0]."','".$values[25][0]."','".$values[26][0]."','".$values[22][0]."')
";
mysql_query($query2);
$company_id  = mysql_insert_id();
echo mysql_errno() . ": " . mysql_error()."in Company_ID:".$company_id. "\n";
}
 else {
    $company_id = $select_company_a[ID];
}

$query3 = "UPDATE job SET company_ID = '$company_id' WHERE ID = '".$values[16][0]."'
";
mysql_query($query3);

//phone number in schleife
foreach ($values[29] as $phone_number){
    $query41 = "SELECT phone_number FROM company_phone WHERE phone_number = '".$phone_number."' AND company_ID = '".$company_id."'
    ";
$select_phone_number = mysql_query($query41);
$select_phone_number_a = mysql_fetch_array($select_phone_number);

if ($select_phone_number_a==FALSE){
$query4 = " INSERT INTO company_phone (company_ID,phone_number)
    VALUES ('$company_id','$phone_number')";
mysql_query($query4);
}
}
//requirements in Schleife

foreach ($values[15] as $requirement){
    $query5 = "INSERT INTO job_requirements(job_ID,requirement)
        VALUES ('".$values[16][0]."','$requirement')";
    mysql_query($query5);
}


//career_stage
foreach ($values[4] as $career_stage){
$query6 = "SELECT ID FROM career_stage
    WHERE name ='$career_stage'
    ";
$select_career = mysql_query($query6);
$select_career_a = mysql_fetch_array($select_career);

if ($select_career_a==FALSE){
    $query7 = "INSERT INTO career_stage (name)
        VALUES ('$career_stage')
        ";
    mysql_query($query7);
    $career_stage_ID  = mysql_insert_id();
    $query8 = "INSERT INTO job_career_stage (job_ID,career_stage_ID)
        VALUES('".$values[16][0]."','$career_stage_ID')
        ";
    mysql_query($query8);
}
else{
    $query9 = "INSERT INTO job_career_stage (job_ID,career_stage_ID)
        VALUES('".$values[16][0]."','$select_career_a[ID]')
        ";
    mysql_query($query9);   
}
}

//contract_type
if ($values[17][0]!=NULL){
$query10 = "SELECT ID FROM contract_type
    WHERE name ='".$values[17][0]."'
    
";
$select_contract = mysql_query($query10);
$select_contract_a = mysql_fetch_array($select_contract);

if ($select_contract_a==FALSE){
    $query11 = "INSERT INTO contract_type (name)
        VALUES ('".$values[17][0]."')
        ";
    mysql_query($query11);
    $contract_type_ID  = mysql_insert_id();
    $query12 = "UPDATE job SET contract_type_ID  = '$contract_type_ID' WHERE ID = '".$values[16][0]."'";
    mysql_query($query12);
}
else{
    $query13 = "UPDATE job SET contract_type_ID  = '$select_contract_a[ID]' WHERE ID = '".$values[16][0]."'"
        ;
    mysql_query($query13);   
}
}
//degree
    $count = 0;
foreach ($values[9] as $degree) {

$query14 = "SELECT ID FROM degree
    WHERE name ='$degree'
    ";
$select_degree = mysql_query($query14);
$select_degree_a = mysql_fetch_array($select_degree);

if ($select_degree_a==FALSE){
    $query15 = "INSERT INTO degree (name)
        VALUES ('$degree')
        ";
    mysql_query($query15);
    $degree_ID  = mysql_insert_id();
}
else
{
    $degree_ID = $select_degree_a[ID];
}
$query16 = "SELECT ID FROM degree_field
    WHERE name ='".$values[11][$count]."'
    
";
$select_degree_field = mysql_query($query16);
$select_degree_field_a = mysql_fetch_array($select_degree_field);
if ($select_degree_field_a==FALSE){
    $query17 = "INSERT INTO degree_field (name)
        VALUES ('".$values[11][$count]."')
    ";
    mysql_query($query17);
    $degree_field_ID  = mysql_insert_id();
}
else
{
    $degree_field_ID = $select_degree_field_a[ID];
}
if ($values[10]!=NULL){
$query18 = "SELECT ID FROM degree_level
    WHERE name ='".$values[10][$count]."'
    
";
$select_degree_level = mysql_query($query18);
$select_degree_level_a = mysql_fetch_array($select_degree_level);
if ($select_degree_level_a==FALSE){
    $query19 = "INSERT INTO degree_level (name)
        VALUES ('".$values[10][$count]."')
    ";
    mysql_query($query19);
    $degree_level_ID  = mysql_insert_id();
}
else
{
    $degree_level_ID = $select_degree_level_a[ID];
}
}
$query20 = "INSERT INTO job_degree (job_ID, degree_ID, degree_level_ID, degree_field_ID)
    VALUES ('".$values[16][0]."','$degree_ID','$degree_level_ID','$degree_field_ID')
";
mysql_query($query20);
$count ++;
}

//framework programme

if ($values[27][0]!=NULL){
$query21= "SELECT ID FROM framework_programme
    WHERE name ='".$values[27][0]."'
    
";
$select_framework_programme = mysql_query($query21);
$select_framework_programme_a = mysql_fetch_array($select_framework_programme);

if ($select_framework_programme_a==FALSE){
    $query22 = "INSERT INTO framework_programme (name)
        VALUES ('".$values[27][0]."')
    ";
    mysql_query($query22);
    $framework_programme_ID  = mysql_insert_id();
    $query23 = "UPDATE job SET framework_programme_ID  = '$framework_programme_ID ' WHERE ID = '".$values[16][0]."'";
    mysql_query($query23);
}
else{
    $query24 = "UPDATE job SET framework_programme_ID  = '$select_framework_programme_a[ID]' WHERE ID = '".$values[16][0]."'"
        ;
    mysql_query($query24);   
}
}

//language

$count = 0;
foreach ($values[7] as $language) {

$query25 = "SELECT iso639p3 FROM language
    WHERE labels LIKE '%$language%'
    ";
$select_language = mysql_query($query25);
$select_language_a = mysql_fetch_array($select_language);

if ($select_language_a==FALSE){
        echo "Language ".$language." not known \n";
    $language_ID = "";
}
else
{
    $language_ID = $select_language_a[iso639p3];
}
$query27 = "SELECT ilr_level FROM language_level WHERE FIND_IN_SET('".$values[8][$count]."',eng)>0
";
$select_language_level = mysql_query($query27);
$select_language_level_a = mysql_fetch_array($select_language_level);
if ($select_language_level_a==FALSE){
    echo "Language_Level ".$values[8][$count]." not known \n";
    $language_level_ID = "";
}
else
{
    $language_level_ID = $select_language_level_a[ilr_level];
}

if ($language_ID !="" AND $language_level_ID !=""){
    
$query28 = "INSERT INTO job_required_languages (job_ID, language_iso639p3, language_ilr_level)
    VALUES ('".$values[16][0]."','$language_ID','$language_level_ID')
";
mysql_query($query28);
}
else
{
    $query29 = "INSERT INTO TODO_job_required_languages (job_ID, language_name, language_level_name)
    VALUES ('".$values[16][0]."','$language','".$values[8][$count]."')
";
mysql_query($query29);
}
$count ++;
unset($language_ID);
unset($language_level_ID);
}

//research fields

foreach ($values[3] as $research_field){
$query30 = "SELECT ID FROM research_field
    WHERE name ='$research_field'
    
";
$select_research_field = mysql_query($query30);
$select_research_field_a = mysql_fetch_array($select_research_field);

if ($select_research_field_a==FALSE){
    $query31 = "INSERT INTO research_field (name)
        VALUES ('$research_field')
        ";
    mysql_query($query31);
    $research_field_ID  = mysql_insert_id();
    $query32 = "INSERT INTO job_research_fields VALUES ('".$values[16][0]."','$research_field_ID')";
    mysql_query($query32);
}
else{
    $query33 = "INSERT INTO job_research_fields VALUES ('".$values[16][0]."',$select_research_field_a[ID])
    ";
    mysql_query($query33);   
}
}
    }
    }
}
// Count actual number of rows

$count_1 = "SELECT COUNT(ID) FROM career_stage";
$count_2 = "SELECT COUNT(ID) FROM company";
$count_3 = "SELECT COUNT(company_ID) FROM company_phone";
$count_4 = "SELECT COUNT(ID) FROM contract_type";
$count_5 = "SELECT COUNT(ID) FROM degree";
$count_6 = "SELECT COUNT(ID) FROM degree_field";
$count_7 = "SELECT COUNT(ID) FROM degree_level";
$count_8 = "SELECT COUNT(ID) FROM framework_programme";
$count_9 = "SELECT COUNT(ID) FROM job";
$count_10 = "SELECT COUNT(job_ID) FROM job_career_stage";
$count_11 = "SELECT COUNT(job_ID) FROM job_degree";
$count_12 = "SELECT COUNT(job_ID) FROM job_required_languages";
$count_13 = "SELECT COUNT(job_ID) FROM job_requirements";
$count_14 = "SELECT COUNT(job_ID) FROM job_research_fields";
$count_15 = "SELECT COUNT(iso639p3) FROM language";
$count_16 = "SELECT COUNT(ilr_level) FROM language_level";
$count_17 = "SELECT COUNT(ID) FROM research_field";
$count_18 = "SELECT COUNT(job_ID) FROM todo_job_required_languages";

$count_array = array($count_1,$count_2,$count_3,$count_4,$count_5,$count_6,$count_7,$count_8,$count_9,$count_10,$count_11,$count_12,$count_13,$count_14,$count_15,$count_16,$count_17,$count_18);
//Connect to local database
mysql_connect(localhost,$username,$password) or die("Unable to connect to database");
mysql_select_db($database) or die("Unable to select database");


foreach ($count_array as $count)
{
    $count_rows = mysql_query($count);
    $count_rows_a = mysql_fetch_array($count_rows);
    $query_array = array_merge($query_array,$count_rows_a);
}

$query_statistics = "INSERT INTO statistics(timestamp,career_stage,company,company_phone,contract_type,degree,degree_field,degree_level,framework_programme,job,job_career_stage,job_degree,job_required_languages,job_requirements,job_research_fields,language,language_level,research_field,todo_job_required_languages)
    VALUES ('".$date."','".$query_array[0]."','".$query_array[1]."','".$query_array[2]."','".$query_array[3]."','".$query_array[4]."','".$query_array[5]."','".$query_array[6]."','".$query_array[7]."','".$query_array[8]."','".$query_array[9]."','".$query_array[10]."','".$query_array[11]."','".$query_array[12]."','".$query_array[13]."','".$query_array[14]."','".$query_array[15]."','".$query_array[16]."','".$query_array[17]."')
        ";
mysql_query($query_statistics);
 $r = 2;
for ($s=0;$s<=17;$s+=1){

    $affected = $query_array[$s]-$statistics_a[$r];
    array_push($affected_array,$affected);
    $r ++;
}

echo "affected rows (career stage): ".$affected_array[0]."\n";
echo "affected rows (company): ".$affected_array[1]."\n";
echo "affected rows (company phone): ".$affected_array[2]."\n";
echo "affected rows (contract type): ".$affected_array[3]."\n";
echo "affected rows (degree): ".$affected_array[4]."\n";
echo "affected rows (degree field): ".$affected_array[5]."\n";
echo "affected rows (degree level): ".$affected_array[6]."\n";
echo "affected rows (framework programme): ".$affected_array[7]."\n";
echo "affected rows (job): ".$affected_array[8]."\n";
echo "affected rows (job career stage): ".$affected_array[9]."\n";
echo "affected rows (job degree): ".$affected_array[10]."\n";
echo "affected rows (job required language): ".$affected_array[11]."\n";
echo "affected rows (job requirements): ".$affected_array[12]."\n";
echo "affected rows (job research field): ".$affected_array[13]."\n";
echo "affected rows (language): ".$affected_array[14]."\n";
echo "affected rows (language level): ".$affected_array[15]."\n";
echo "affected rows (research fields): ".$affected_array[16]."\n";
echo "affected rows (todo job required language): ".$affected_array[17]."\n";


mysql_close();
?>