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
INSERT INTO mwc_group (id, g_name) VALUES
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
