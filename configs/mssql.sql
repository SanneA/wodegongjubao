
IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[MWC_access]') AND type in (N'U'))
DROP TABLE [dbo].[MWC_access];

SET ANSI_NULLS OFF;

SET QUOTED_IDENTIFIER ON;

CREATE TABLE [dbo].[MWC_access](
	[aid] [int] IDENTITY(1,1) NOT NULL,
	[goupId] [int] NOT NULL,
	[pageId] [int] NOT NULL,
	[server] [int] NOT NULL
) ON [PRIMARY]
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF__MWC_admin__serve__4EDDB18F]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[MWC_admin] DROP CONSTRAINT [DF__MWC_admin__serve__4EDDB18F]
END;

IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[DF_MWC_admin_col_try]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[MWC_admin] DROP CONSTRAINT [DF_MWC_admin_col_try]
END;

IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[MWC_admin]') AND type in (N'U'))
DROP TABLE [dbo].[MWC_admin];


SET ANSI_NULLS ON;

SET QUOTED_IDENTIFIER ON;

SET ANSI_PADDING ON;

CREATE TABLE [dbo].[MWC_admin](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[name] [varchar](20) NULL,
	[pwd] [varchar](50) NULL,
	[lastdate] [datetime] NULL,
	[nick] [varchar](20) NULL,
	[access] [smallint] NULL,
	[sacc] [varchar](20) NULL,
	[umail] [varchar](255) NULL,
	[server] [smallint] DEFAULT ((0)) NULL,
	[col_try] [smallint] DEFAULT ((0)) NULL,
	[col_banTo] [datetime] NULL
) ON [PRIMARY];

SET ANSI_PADDING OFF;

IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[MWC_files]') AND type in (N'U'))
DROP TABLE [dbo].[MWC_files];

SET ANSI_NULLS OFF;
SET QUOTED_IDENTIFIER ON;

CREATE TABLE [dbo].[MWC_files](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[fnmae] [nvarchar](255) NULL,
	[fpoints] [smallint] DEFAULT ((0)) NULL,
	[downloads] [int] DEFAULT ((0)) NULL,
	[ftype] [smallint] DEFAULT ((0)) NULL,
	[link] [text] NULL,
	[desrciption] [ntext] NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]


IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[MWC_group]') AND type in (N'U'))
DROP TABLE [dbo].[MWC_group];
SET ANSI_NULLS OFF;
SET QUOTED_IDENTIFIER ON;
SET ANSI_PADDING OFF;
CREATE TABLE [dbo].[MWC_group](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[g_name] [varchar](255) NOT NULL
) ON [PRIMARY];
SET ANSI_PADDING OFF;

IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[MWC_logs]') AND type in (N'U'))
DROP TABLE [dbo].[MWC_logs]
SET ANSI_NULLS ON;
SET QUOTED_IDENTIFIER ON;
SET ANSI_PADDING ON;
CREATE TABLE [dbo].[MWC_logs](
	[col_LogID] [int] IDENTITY(1,1) NOT NULL,
	[col_ErrNum] [smallint] DEFAULT ((0)) NULL,
	[col_msg] [text] NOT NULL,
	[col_mname] [varchar](30) NULL,
	[col_createTime] [datetime] NULL,
	[tbuild] [varchar](255) NULL,
 CONSTRAINT [PK_MWC_logs] PRIMARY KEY CLUSTERED 
(
	[col_LogID] ASC
)WITH (PAD_INDEX  = OFF, STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS  = ON, ALLOW_PAGE_LOCKS  = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY];
SET ANSI_PADDING OFF


IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[MWC_menu]') AND type in (N'U'))
DROP TABLE [dbo].[MWC_menu];
SET ANSI_NULLS OFF;
SET QUOTED_IDENTIFIER ON;
SET ANSI_PADDING OFF;
CREATE TABLE [dbo].[MWC_menu](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[mtitle] [varchar](255) NULL,
	[mtype] [smallint] DEFAULT ((0)) NULL,
	[link] [text] NOT NULL,
	[server] [smallint] DEFAULT ((0)) NOT NULL,
	[modul] [varchar](255) NULL,
	[col_Seq] [int] DEFAULT ((0)) NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY];
SET ANSI_PADDING OFF;

IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[MWC_menu_type]') AND type in (N'U'))
DROP TABLE [dbo].[MWC_menu_type];
SET ANSI_NULLS OFF;
SET QUOTED_IDENTIFIER ON;
SET ANSI_PADDING OFF;

CREATE TABLE [dbo].[MWC_menu_type](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[ttitle] [varchar](255) NULL,
	[tbuild] [varchar](100) NULL
) ON [PRIMARY];

SET ANSI_PADDING OFF;

IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[MWC_news]') AND type in (N'U'))
DROP TABLE [dbo].[MWC_news];

CREATE TABLE [dbo].[MWC_news](
	[nid] [int] IDENTITY(1,1) NOT NULL,
	[autothor] [nvarchar](20) NULL,
	[indate] [datetime] DEFAULT (getdate()) NULL,
	[ntitle] [nvarchar](255) NULL,
	[ntype] [smallint]  DEFAULT ((0)) NULL,
	[news] [ntext] NULL,
	[ntags] [nvarchar](255) NULL,
	[views] [int] DEFAULT ((0)) NULL,
	[likes] [int] DEFAULT ((0)) NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]


IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[MWC_pages]') AND type in (N'U'))
DROP TABLE [dbo].[MWC_pages];

CREATE TABLE [dbo].[MWC_pages](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[pname] [varchar](50) NOT NULL,
	[ptitle] [varchar](100) NOT NULL,
	[ppath] [varchar](100) NOT NULL,
	[mname] [varchar](50)  NULL,
	[mpath] [varchar](100)  NULL,
	[caching] [smallint]  DEFAULT ((0))NOT NULL,
	[rating] [smallint] DEFAULT ((0)) NOT NULL,
	[ison] [smallint]  DEFAULT ((0)) NOT NULL,
	[server] [smallint] DEFAULT ((0)) NOT NULL,
	[tbuild][varchar](100) NULL,
 CONSTRAINT [PK_MWC_pages] PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX  = OFF, STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS  = ON, ALLOW_PAGE_LOCKS  = ON) ON [PRIMARY]
) ON [PRIMARY];
SET ANSI_PADDING OFF

IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[MWC_plugins]') AND type in (N'U'))
DROP TABLE [dbo].[MWC_plugins];

SET ANSI_NULLS OFF;
SET QUOTED_IDENTIFIER ON;
SET ANSI_PADDING OFF;
CREATE TABLE [dbo].[MWC_plugins](
	[pid] [int] IDENTITY(1,1) NOT NULL,
	[pname] [varchar](255) NULL,
	[mname] [varchar](255) NULL,
	[tbuild] [varchar](100) NULL,
	[seq] [smallint] DEFAULT ((0))   NULL ,
	[pstate] [smallint] DEFAULT ((0)) NULL,
	[pcache] [smallint] DEFAULT ((0)) NULL,
	[pserver] [smallint] DEFAULT ((0)) NULL
) ON [PRIMARY];
SET ANSI_PADDING OFF;


SET ANSI_NULLS OFF;
SET QUOTED_IDENTIFIER ON;
SET ANSI_PADDING OFF;
CREATE TABLE [dbo].[mwc_pluginsaccess](
	[col_paID] [int] IDENTITY(1,1) NOT NULL,
	[col_pluginID] [int] NULL,
	[col_groupID] [int] NULL
) ON [PRIMARY];
SET ANSI_PADDING OFF;

SET IDENTITY_INSERT [mwc_group] ON;
INSERT INTO .mwc_group (id, g_name) VALUES
(1, 'globalAdmin'),
(2, 'g_guest'),
(3, 'g_admins'),
(4, 'g_all'),
(5, 'g_user');
SET IDENTITY_INSERT [mwc_group] OFF;

SET IDENTITY_INSERT [mwc_plugins] ON;
INSERT INTO mwc_plugins (pid, pname, pstate, pcache, pserver, mname,tbuild) VALUES
(1, 'login', 2, 0, 0,'m_login','admin'),
(2, 'admgroup', 2, 0, 0,'m_admgroup','admin'),
(3, 'adminmenu', 2, 600, 0,'m_menu','admin'),
(4, 'selserver', 2, 0, 0,'m_selserver','admin');
SET IDENTITY_INSERT [mwc_plugins] OFF;

SET IDENTITY_INSERT [mwc_pluginsaccess] ON;
INSERT INTO mwc_pluginsaccess (col_paID, col_pluginID, col_groupID) VALUES
(1, 1, 4),
(2, 2, 1),
(3, 2, 3),
(4, 3, 1),
(5, 3, 3),
(6, 4, 1),
(7, 4, 3);
SET IDENTITY_INSERT [mwc_pluginsaccess] OFF;


SET IDENTITY_INSERT [mwc_pages] ON;
INSERT INTO mwc_pages (id,pname,ptitle,ppath,caching,ison,server,mname,mpath,tbuild) VALUES
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
SET IDENTITY_INSERT [mwc_pages] OFF;

SET IDENTITY_INSERT [mwc_access] ON;
INSERT INTO mwc_access (aid,pageId,goupId,server) VALUES
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
SET IDENTITY_INSERT [mwc_access] OFF;

SET IDENTITY_INSERT [mwc_menu_type] ON;
INSERT INTO mwc_menu_type (id,ttitle,tbuild) VALUES
(2,'adminmenu','admin');
SET IDENTITY_INSERT [mwc_menu_type] OFF;

SET IDENTITY_INSERT [mwc_menu] ON;
INSERT INTO mwc_menu (id,mtitle,mtype,link,server,modul,col_Seq) VALUES
(1,'auto_title1',2,'?p=anews',0,'anews',0),
(2,'auto_title2',2,'?p=cconfigs',0,'cconfigs',0),
(3,'auto_title3',2,'?p=ammanager',0,'ammanager',0),
(4,'auto_title4',2,'?p=apman',0,'apman',0),
(5,'auto_title5',2,'?p=agroup',0,'agroup',0),
(6,'auto_title6',2,'?p=aaddmenu',0,'aaddmenu',0),
(7,'auto_title8',2,'?p=acontrol',0,'acontrol',0),
(8,'auto_title7',2,'?p=lmanage',0,'lmanage',0);
SET IDENTITY_INSERT [mwc_menu] OFF;


-- muonline --

SET ANSI_PADDING ON;

CREATE TABLE [dbo].[mwc_downloads](
	[col_id] [int] IDENTITY(1,1) NOT NULL,
	[col_pik] [int] NULL,
	[col_desc] [ntext] NULL,
	[col_address] [ntext] NULL,
	[col_title] [ntext] NULL,
	[tbuild] [varchar](250) NULL,
 CONSTRAINT [PK_mwc_downloads] PRIMARY KEY CLUSTERED
(
	[col_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY];

SET ANSI_PADDING OFF;

SET IDENTITY_INSERT [mwc_plugins] ON;
INSERT INTO mwc_plugins (pid, pname, pstate, pcache, pserver, mname,tbuild,seq) VALUES
(5, 'login', 2, 0, 0,'m_login','muadmin',0),
(6, 'admgroup', 2, 0, 0,'m_admgroup','muadmin',0),
(7, 'adminmenu', 2, 600, 0,'m_menu','muadmin',0),
(8, 'mainmenu', 1, 600, 0,'muonlineMenu','muonline',0),
(9, 'login', 1, 0, 0,'m_login','muonline',0),
(10, 'qinfo', 1, 0, 0,'m_qinfo','muonline',2),
(11, 'acblock', 0, 0, 0,'m_acblock','muonline',0),
(12, 'eventblock', 0, 0, 0,'m_eventblock','muonline',0),
(13, 'usermenu', 1, 100, 0,'muonlineMenu','muonline',0),
(14, 'discus', 0, 0, 0,'m_discus','muonline',0),
(15, 'servertop', 1, 600, 0,'m_servertop','muonline',2),
(16, 'selserver', 2, 0, 0,'m_selserver','muadmin',0);
SET IDENTITY_INSERT [mwc_plugins] OFF;

SET IDENTITY_INSERT [mwc_pluginsaccess] ON;
INSERT INTO mwc_pluginsaccess (col_paID, col_pluginID, col_groupID) VALUES
(8, 5, 4),
(9, 6, 1),
(10, 6, 3),
(12, 7, 3),
(13, 7, 1),
(14, 8, 4),
(15, 9, 4),
(16, 10, 4),
(17, 11, 4),
(18, 12, 4),
(19, 13, 5),
(20, 14, 4),
(21, 15, 4),
(22, 16, 1),
(23, 16, 3);
SET IDENTITY_INSERT [mwc_pluginsaccess] OFF;

SET IDENTITY_INSERT [mwc_menu_type] ON;
INSERT INTO mwc_menu_type (id,ttitle,tbuild) VALUES
(3,'adminmenu','muadmin'),
(4,'mainmenu','muonline'),
(5,'usermenu','muonline');
SET IDENTITY_INSERT [mwc_menu_type] OFF;

SET IDENTITY_INSERT [mwc_menu] ON;
INSERT INTO mwc_menu (id,mtitle,mtype,link,server,modul,col_Seq) VALUES
(9,'auto_title1',3,'?p=anews',0,'anews',0),
(10,'auto_title2',3,'?p=cconfigs',0,'cconfigs',0),
(11,'auto_title3',3,'?p=ammanager',0,'ammanager',0),
(12,'auto_title4',3,'?p=apman',0,'apman',0),
(13,'auto_title5',3,'?p=agroup',0,'agroup',0),
(14,'auto_title6',3,'?p=aaddmenu',0,'aaddmenu',0),
(15,'auto_title8',3,'?p=acontrol',0,'acontrol',0),
(16,'auto_title7',3,'?p=lmanage',0,'lmanage',0),
(17,'auto_title1',4,'page/news.html',0,'news',1),
(19,'auto_title13',4,'page/register.html',0,'register',2),
(20,'auto_title16',5,'page/bank.html',0,'bank',0),
(21,'auto_title14',3,'?p=editchar',0,'editchars',4),
(22,'auto_title17',5,'page/freepoints.html',0,'freepoints',0),
(23,'auto_title19',4,'page/top100.html',0,'top100',3),
(24,'auto_title20',4,'page/topguild.html',0,'topguild',4),
(25,'auto_title15',3,'?p=iexport',0,'iexport',7),
(26,'auto_title22',5,'page/webshop.html',0,'webshop',4),
(27,'auto_title23',4,'page/downloads.html',0,'downloads',5),
(28,'auto_title12',3,'?p=downloads',0,'downloads',8),
(29,'auto_title22',4,'page/webmarket.html',0,'webmarket',4);
SET IDENTITY_INSERT [mwc_menu] OFF;

SET IDENTITY_INSERT [mwc_pages] ON;
INSERT INTO mwc_pages (id,pname,ptitle,ppath,caching,ison,server,mname,mpath,tbuild) VALUES
(11, 'anews', 'auto_title1','controllers',0,1,0,'m_anews','models','muadmin'),
(12, 'cconfigs', 'auto_title2','controllers',0,1,0,'m_cconfigs','models','muadmin'),
(13, 'ammanager', 'auto_title3','controllers',0,1,0,'m_ammanager','models','muadmin'),
(14, 'apman', 'auto_title4','controllers',0,1,0,'m_apman','models','muadmin'),
(15, 'agroup', 'auto_title5','controllers',0,1,0,'m_agroup','models','muadmin'),
(16, 'aaddmenu', 'auto_title6','controllers',0,1,0,'m_aaddmenu','models','muadmin'),
(17, 'lmanage', 'auto_title7','controllers',0,1,0,'m_lmanage','models','muadmin'),
(18, 'acontrol', 'auto_title8','controllers',0,1,0,'m_acontrol','models','muadmin'),
(19, 'admin', 'auto_title9','controllers',0,1,0,'m_admin','models','muadmin'),
(20, 'logs', 'auto_title11','controllers',0,1,0,'m_logs','models','muadmin'),
(21, 'editchars', 'auto_title14','controllers',0,1,0,'m_editchars','models','muadmin'),
(22, 'news', 'auto_title1','controllers',0,1,0,'m_news','models','muonline'),
(23, 'register', 'auto_title13','controllers',0,1,0,'m_register','models','muonline'),
(24, 'error', 'auto_title14','controllers',0,1,0,'m_error','models','muonline'),
(25, 'startpage', 'auto_title15','user_c',0,1,0,'m_startpage','user_m','muonline'),
(26, 'bank', 'auto_title16','user_c',0,1,0,'m_bank','user_m','muonline'),
(27, 'freepoints', 'auto_title17','user_c',0,1,0,'m_freepoints','user_m','muonline'),
(28, 'usercp', 'auto_title18','user_c',0,1,0,'m_usercp','user_m','muonline'),
(29, 'top100', 'auto_title19','controllers',600,1,0,'m_top100','models','muonline'),
(30, 'topguild', 'auto_title20','controllers',60,1,0,'m_topguild','models','muonline'),
(31, 'iexport', 'auto_title15','mu/c',0,1,0,'m_iexport','mu/m','muadmin'),
(32, 'item', 'auto_title21','controllers',0,1,0,'m_item','models','muonline'),
(33, 'webshop', 'auto_title22','user_c',0,1,0,'m_webshop','user_m','muonline'),
(34, 'webmarket', 'auto_title22','controllers',0,1,0,'m_webmarket','models','muonline'),
(35, 'downloads', 'auto_title12','controllers',0,1,0,'m_downloads','models','muadmin'),
(36, 'downloads', 'auto_title23','controllers',0,1,0,'m_downloads','models','muonline'),
(37, 'castle', 'auto_title24','controllers',0,1,0,'m_castle','models','muonline'),
(38, 'forgotpwd', 'auto_title25','controllers',0,1,0,'m_forgotpwd','models','muonline');

SET IDENTITY_INSERT [mwc_pages] OFF;

SET IDENTITY_INSERT [mwc_access] ON;
INSERT INTO mwc_access (aid,pageId,goupId,server) VALUES
(21,11,1,0),
(22,11,3,0),
(23,12,1,0),
(24,12,3,0),
(25,13,1,0),
(26,13,3,0),
(27,14,1,0),
(28,14,3,0),
(29,15,1,0),
(30,15,3,0),
(31,16,1,0),
(32,16,3,0),
(33,17,1,0),
(34,17,3,0),
(35,18,1,0),
(36,18,3,0),
(37,19,1,0),
(38,19,3,0),
(39,20,1,0),
(41,21,3,0),
(42,21,1,0),
(43,22,4,0),
(44,23,4,0),
(45,24,4,0),
(46,25,5,0),
(47,26,5,0),
(48,27,5,0),
(49,28,5,0),
(50,29,4,0),
(51,30,4,0),
(52,31,1,0),
(53,31,3,0),
(54,32,4,0),
(55,33,5,0),
(56,34,4,0),
(57,36,4,0),
(58,35,1,0),
(59,35,3,0),
(60,37,4,0);
SET IDENTITY_INSERT [mwc_access] OFF;