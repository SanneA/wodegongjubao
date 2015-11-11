CREATE TABLE mwc_access (
  aid int(11) NOT NULL AUTO_INCREMENT,
  goupId int(11) NOT NULL DEFAULT 0,
  pageId int(11) NOT NULL DEFAULT 0,
  server int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (aid)
)
ENGINE = INNODB
AUTO_INCREMENT = 1
AVG_ROW_LENGTH = 125
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE mwc_admin (
  id int(11) NOT NULL AUTO_INCREMENT,
  name varchar(20) NOT NULL,
  pwd varchar(50) NOT NULL,
  lastdate datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  nick varchar(20) NOT NULL,
  access smallint(6) NOT NULL DEFAULT 0,
  sacc varchar(20) DEFAULT NULL,
  server smallint(6) NOT NULL DEFAULT 0,
  umail varchar(255) DEFAULT NULL,
  PRIMARY KEY (id)
)
ENGINE = INNODB
AUTO_INCREMENT = 1
AVG_ROW_LENGTH = 16384
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE mwc_files (
  id int(11) NOT NULL AUTO_INCREMENT,
  fnmae varchar(255) NOT NULL,
  fpoints int(11) NOT NULL DEFAULT 0,
  downloads int(11) NOT NULL DEFAULT 0,
  ftype int(11) NOT NULL DEFAULT 0,
  link text DEFAULT NULL,
  desrciption text NULL,
  PRIMARY KEY (id)
)
ENGINE = INNODB
AUTO_INCREMENT = 1
AVG_ROW_LENGTH = 254
CHARACTER SET utf8
COLLATE utf8_general_ci
COMMENT = 'таблица для хранения информации о загруженных файлах';

CREATE TABLE mwc_group (
  id int(11) NOT NULL AUTO_INCREMENT,
  g_name varchar(255) NOT NULL,
  PRIMARY KEY (id)
)
ENGINE = INNODB
AUTO_INCREMENT = 1
AVG_ROW_LENGTH = 780
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE mwc_logs (
  col_LogID int(11) NOT NULL AUTO_INCREMENT,
  col_ErrNum smallint(6) DEFAULT 0,
  col_msg text NOT NULL,
  col_mname varchar(30) DEFAULT NULL,
  col_createTime datetime DEFAULT NULL,
  PRIMARY KEY (col_LogID)
)
ENGINE = INNODB
AUTO_INCREMENT = 1
AVG_ROW_LENGTH = 162
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE mwc_menu (
  id int(11) NOT NULL AUTO_INCREMENT,
  mtitle varchar(255) NOT NULL,
  mtype smallint(6) NOT NULL DEFAULT 0,
  link text NOT NULL,
  server smallint(6) NOT NULL DEFAULT 0,
  modul varchar(255) NOT NULL,
  col_Seq int(11) DEFAULT 0 COMMENT 'очередность',
  PRIMARY KEY (id)
)
ENGINE = INNODB
AUTO_INCREMENT = 1
AVG_ROW_LENGTH = 744
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE mwc_menu_type (
  id int(11) NOT NULL AUTO_INCREMENT,
  ttitle varchar(255) NOT NULL,
  tbuild varchar(100) DEFAULT NULL,
  PRIMARY KEY (id)
)
ENGINE = INNODB
AUTO_INCREMENT = 1
AVG_ROW_LENGTH = 2730
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE mwc_news (
  nid int(11) NOT NULL AUTO_INCREMENT,
  autothor varchar(20) NOT NULL,
  indate timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  ntitle varchar(255) NOT NULL,
  ntype smallint(6) NOT NULL DEFAULT 0,
  news text NOT NULL,
  ntags varchar(255) NOT NULL,
  views int(11) NOT NULL DEFAULT 0,
  likes int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (nid)
)
ENGINE = INNODB
AUTO_INCREMENT = 1
AVG_ROW_LENGTH = 16384
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE mwc_pages (
  id int(11) NOT NULL AUTO_INCREMENT,
  pname varchar(25) NOT NULL,
  ptitle varchar(100) NOT NULL,
  ppath varchar(100) NOT NULL,
  caching smallint(6) NOT NULL DEFAULT 0,
  chonline smallint(6) NOT NULL DEFAULT 0,
  ison smallint(6) NOT NULL DEFAULT 0,
  server smallint(6) NOT NULL DEFAULT 0,
  descr varchar(255) DEFAULT NULL COMMENT 'для сео описание страницы',
  mname varchar(255) DEFAULT NULL COMMENT 'название файла модели',
  mpath varchar(255) DEFAULT NULL COMMENT 'папка, где хранится файл модели',
  tbuild varchar(100) DEFAULT NULL COMMENT 'название билда',
  PRIMARY KEY (id)
)
ENGINE = INNODB
AUTO_INCREMENT = 1
AVG_ROW_LENGTH = 655
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE mwc_plugins (
  pid int(11) NOT NULL AUTO_INCREMENT,
  pname varchar(255) NOT NULL,
  pstate smallint(6) NOT NULL DEFAULT 0,
  pcache smallint(6) NOT NULL DEFAULT 0,
  pserver smallint(6) NOT NULL DEFAULT 0,
  seq smallint(6) NOT NULL DEFAULT 0,
  tbuild varchar(100) DEFAULT NULL COMMENT 'название билда',
  mname varchar(255) DEFAULT NULL COMMENT 'название модуля',
  PRIMARY KEY (pid)
)
ENGINE = INNODB
AUTO_INCREMENT = 1
AVG_ROW_LENGTH = 5461
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE mwc_pluginsaccess (
  col_paID int(11) NOT NULL AUTO_INCREMENT,
  col_pluginID int(11) DEFAULT NULL,
  col_groupID int(11) DEFAULT NULL,
  PRIMARY KEY (col_paID)
)
ENGINE = INNODB
AUTO_INCREMENT = 1
AVG_ROW_LENGTH = 2048
CHARACTER SET utf8
COLLATE utf8_general_ci;


INSERT INTO `mwc_group` (`id`, `g_name`) VALUES 
(1, 'globalAdmin'),
(2, 'g_guest'),
(3, 'g_admins'),
(4, 'g_all'),
(5, 'g_user');

INSERT INTO `mwc_plugins` (`pid`, `pname`, `pstate`, `pcache`, `pserver`, `mname`,`tbuild`) VALUES
(1, 'login', 2, 0, 0,'m_login','admin'),
(2, 'admgroup', 2, 0, 0,'m_admgroup','admin'),
(3, 'adminmenu', 2, 600, 0,'m_menu','admin'),
(4, 'selserver', 2, 0, 0,'m_selserver','admin');

INSERT INTO `mwc_pluginsaccess` (`col_paID`, `col_pluginID`, `col_groupID`) VALUES 
(1, 1, 4),
(2, 2, 1),
(3, 2, 3),
(4, 3, 1),
(5, 3, 3),
(6, 4, 1),
(7, 4, 3);

INSERT INTO `mwc_pages` (`id`,`pname`,`ptitle`,`ppath`,`caching`,`ison`,`server`,`mname`,`mpath`,`tbuild`) VALUES
(1, 'anews', 'auto_title1','controllers',0,1,0,'m_anews','models','admin'),
(2, 'cconfigs', 'auto_title2','controllers',0,1,0,'m_cconfigs','models','admin'),
(3, 'ammanager', 'auto_title3','controllers',0,1,0,'m_ammanager','models','admin'),
(4, 'apman', 'auto_title4','controllers',0,1,0,'m_apman','models','admin'),
(5, 'agroup', 'auto_title5','controllers',0,1,0,'m_agroup','models','admin'),
(6, 'aaddmenu', 'auto_title6','controllers',0,1,0,'m_aaddmenu','models','admin'),
(7, 'lmanage', 'auto_title7','controllers',0,1,0,'m_lmanage','models','admin'),
(8, 'acontrol', 'auto_title8','controllers',0,1,0,'m_acontrol','models','admin'),
(9, 'admin', 'auto_title9','controllers',0,1,0,'m_admin','models','admin'),
(10, 'logs', 'auto_title11','controllers',0,1,0,'m_logs','models','admin');

INSERT INTO `mwc_access` (`aid`,`pageId`,`goupId`,`server`) VALUES 
(1,1,1,0),
(2,1,3,0),
(3,2,1,0),
(4,2,3,0),
(5,3,1,0),
(6,3,3,0),
(7,4,1,0),
(8,4,3,0),
(9,5,1,0),
(10,5,3,0),
(11,6,1,0),
(12,6,3,0),
(13,7,1,0),
(14,7,3,0),
(15,8,1,0),
(16,8,3,0),
(17,9,1,0),
(18,9,3,0),
(19,10,1,0),
(20,10,3,0);

INSERT INTO `mwc_menu_type` (`id`,`ttitle`,`tbuild`) VALUES
(2,'adminmenu','admin');

INSERT INTO `mwc_menu` (`id`,`mtitle`,`mtype`,`link`,`server`,`modul`) VALUES 
(1,'auto_title1',2,'?p=anews',0,'anews'),
(2,'auto_title2',2,'?p=cconfigs',0,'cconfigs'),
(3,'auto_title3',2,'?p=ammanager',0,'ammanager'),
(4,'auto_title4',2,'?p=apman',0,'apman'),
(5,'auto_title5',2,'?p=agroup',0,'agroup'),
(6,'auto_title6',2,'?p=aaddmenu',0,'aaddmenu'),
(7,'auto_title8',2,'?p=acontrol',0,'acontrol'),
(8,'auto_title7',2,'?p=lmanage',0,'lmanage');
