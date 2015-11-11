ALTER TABLE dbo.MEMB_INFO ADD MWCpoints int default ((5)) NOT NULL;
ALTER TABLE dbo.MEMB_INFO ADD mwc_credits bigint default ((0)) NOT NULL;
ALTER TABLE dbo.MEMB_INFO ADD mwc_tryes smallint default((0)) NULL;
ALTER TABLE dbo.MEMB_INFO ADD mwc_timeban datetime NULL;
ALTER TABLE dbo.MEMB_INFO ADD mwc_bankZ bigint default ((0)) NOT NULL;
ALTER TABLE dbo.Character ADD gr_res int default ((0)) NOT NULL;


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
(26,'auto_title22',5,'page/webshop.html',0,'webshop',4);
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
(34, 'webmarket', 'auto_title22','controllers',0,1,0,'m_webmarket','models','muonline');

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
(56,34,4,0);
SET IDENTITY_INSERT [mwc_access] OFF;



SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO


-- =============================================
-- Author:		epmak
-- Create date: 11.10.13
-- Description:	выбор нужной вещи из сундука
-- =============================================
CREATE PROCEDURE [dbo].[MWC_WHITEM64]
     @AccountID varchar(10),
     @ItemNumber smallint, -- начиная с 0
     @ItemSize smallint --32/64/?
AS
BEGIN
	declare @inv varbinary(7680);
	declare @vinv varchar(7680);
	declare @answ varchar(64),@curitm varchar(64);
	SET @answ = 'FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF';

	BEGIN TRANSACTION
	set @inv = (SELECT Items FROM warehouse WHERE AccountID = @AccountID);
	set @vinv = CONVERT(varchar(max),@inv,2);
	select @curitm =substring(@vinv,(@ItemNumber*@ItemSize)+1,@ItemSize)
	IF @@Error<>0
	    BEGIN
	       ROLLBACK TRANSACTION;
	       select @answ as item
	    END
	  ELSE
	    BEGIN
	      COMMIT TRANSACTION
	      select @curitm as item
	    END

END

GO



-- =============================================
-- Author:		epmak
-- Create date: 11.10.13
-- Description:	выбор нужной вещи из сундука
-- =============================================
CREATE PROCEDURE [dbo].[MWC_WHITEM32]
     @AccountID varchar(10),
     @ItemNumber smallint, -- начиная с 0
     @ItemSize smallint --32/64/?
AS
BEGIN
	declare @inv varbinary(3840);
	declare @vinv varchar(3840);
	declare @answ varchar(32),@curitm varchar(32);
	SET @answ = 'FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF';

	BEGIN TRANSACTION
	set @inv = (SELECT Items FROM warehouse WHERE AccountID = @AccountID);
	set @vinv = CONVERT(varchar(max),@inv,2);
	select @curitm =substring(@vinv,(@ItemNumber*@ItemSize)+1,@ItemSize)
	IF @@Error<>0
	    BEGIN
	       ROLLBACK TRANSACTION;
	       select @answ as item
	    END
	  ELSE
	    BEGIN
	      COMMIT TRANSACTION
	      select @curitm as item
	    END

END

GO

-- =============================================
-- Author:		<Author,,Name>
-- Create date: <Create Date,,>
-- Description:	<Description,,>
-- =============================================
CREATE PROCEDURE [dbo].[MWC_REPLACEWH32]
     @AccountID varchar(10),
     @ItemWhat varchar(32),
     @ItemTo varchar(32)
AS
BEGIN
    declare @res char(1);
	declare @inv varbinary(3840);
	declare @vinv varchar(3840);
	--set @res = (SELECT WHOpen FROM warehouse WHERE AccountID = @AccountID);
	set @res =(SELECT ConnectStat from MEMB_STAT WHERE memb___id = @AccountID);
	IF @res = 0
	BEGIN
	  set @inv = (SELECT Items FROM warehouse WHERE AccountID = @AccountID);
	  set @vinv = CONVERT(varchar(3840),@inv,2);
	  SET NOCOUNT ON;

	 -- BEGIN TRANSACTION
	  UPDATE warehouse SET Items = CONVERT(varbinary(3840),REPLACE(@vinv,@ItemWhat,@ItemTo),2) WHERE AccountID = @AccountID;
	--  IF @@Error<>0
	--    BEGIN
	 --      ROLLBACK TRANSACTION;
	--       SET @res = 1;
	--    END
	--  ELSE
	 --   BEGIN
	 --     COMMIT TRANSACTION
	  --  END
   END
   SELECT @res as statez;
END

GO

-- =============================================
-- Author:		<Author,,Name>
-- Create date: <Create Date,,>
-- Description:	<Description,,>
-- =============================================
CREATE PROCEDURE [dbo].[MWC_REPLACEWH64]
     @AccountID varchar(10),
     @ItemWhat varchar(64),
     @ItemTo varchar(64)
AS
BEGIN
    declare @res char(1);
	declare @inv varbinary(7680);
	declare @vinv varchar(7680);
	--set @res = (SELECT WHOpen FROM warehouse WHERE AccountID = @AccountID);
	set @res =(SELECT ConnectStat from MEMB_STAT WHERE memb___id = @AccountID);
	IF @res = 0
	BEGIN
	  set @inv = (SELECT Items FROM warehouse WHERE AccountID = @AccountID);
	  set @vinv = CONVERT(varchar(7680),@inv,2);
	  SET NOCOUNT ON;

	 -- BEGIN TRANSACTION
	  UPDATE warehouse SET Items = CONVERT(varbinary(7680),REPLACE(@vinv,@ItemWhat,@ItemTo),2) WHERE AccountID = @AccountID;
	--  IF @@Error<>0
	--    BEGIN
	 --      ROLLBACK TRANSACTION;
	--       SET @res = 1;
	--    END
	--  ELSE
	 --   BEGIN
	 --     COMMIT TRANSACTION
	  --  END
   END
   SELECT @res as statez;
END

GO


-- =============================================
-- Author:		epmnak
-- Create date: 30.10.2013
-- Description:	замена вещи по ид
-- =============================================
CREATE PROCEDURE [dbo].[MWC_REPLACEWHNUM32]
 @AccountID varchar(10), -- акк
 @ItemNum int,           -- номер вещи, на которую надо положить
 @ItemTo varchar(32)     -- что положить
     AS
BEGIN
    declare @res char(1);
	declare @vinv varchar(3840);
	 SET NOCOUNT ON;
	set @ItemNum = @ItemNum * 32 +1 ;
	set @res =(SELECT ConnectStat from MEMB_STAT WHERE memb___id = @AccountID);
	IF @res = 0
	BEGIN
	  set @vinv = CONVERT(varchar(3840),(SELECT Items FROM warehouse WHERE AccountID = @AccountID),2);

	 IF (LEN(@vinv)>@ItemNum)
	 BEGIN

		BEGIN TRANSACTION

			UPDATE warehouse SET Items = CONVERT(varbinary(3840),STUFF(@vinv,@ItemNum,32,@ItemTo),2) WHERE AccountID = @AccountID;

			IF @@Error<>0
			BEGIN
				ROLLBACK TRANSACTION;
				SET @res = 1;
			END
		ELSE
			BEGIN
				COMMIT TRANSACTION
			END
		END
	END
	ELSE
	BEGIN
		SET @res = 1;
	END
    SELECT @res as statez;
END

GO


-- =============================================
-- Author:		epmnak
-- Create date: 30.10.2013
-- Description:	замена вещи по ид
-- =============================================
CREATE PROCEDURE [dbo].[MWC_REPLACEWHNUM64]
 @AccountID varchar(10), -- акк
 @ItemNum int,           -- номер вещи, на которую надо положить
 @ItemTo varchar(64)     -- что положить
     AS
BEGIN
    declare @res char(1);
	declare @vinv varchar(7680);
	 SET NOCOUNT ON;
	set @ItemNum = @ItemNum * 64 +1 ;
	set @res =(SELECT ConnectStat from MEMB_STAT WHERE memb___id = @AccountID);
	IF @res = 0
	BEGIN
	  set @vinv = CONVERT(varchar(7680),(SELECT Items FROM warehouse WHERE AccountID = @AccountID),2);

	 IF (LEN(@vinv)>@ItemNum)
	 BEGIN

		BEGIN TRANSACTION

			UPDATE warehouse SET Items = CONVERT(varbinary(7680),STUFF(@vinv,@ItemNum,64,@ItemTo),2) WHERE AccountID = @AccountID;

			IF @@Error<>0
			BEGIN
				ROLLBACK TRANSACTION;
				SET @res = 1;
			END
		ELSE
			BEGIN
				COMMIT TRANSACTION
			END
		END
	END
	ELSE
	BEGIN
		SET @res = 1;
	END
    SELECT @res as statez;
END

GO


SET ANSI_PADDING ON
GO

CREATE TABLE [dbo].[mwc_web_shop](
	[col_shopID] [int] IDENTITY(1,1) NOT NULL,
	[col_itemID] [int] NULL,
	[col_idemGroup] [int] NULL,
	[col_Name] [varchar](50) NOT NULL,
	[col_hex] [varchar](64) NOT NULL,
	[col_serial] [varchar](8) NULL,
	[col_serial2] [varchar](8) NULL,
	[col_level] [smallint] NULL,
	[col_isOpt] [char](1) NULL,
	[col_isExc] [char](1) NULL,
	[col_isAnc] [char](1) NULL,
	[col_isSock] [char](1) NULL,
	[col_isSkill] [char](1) NULL,
	[col_isPVP] [char](1) NULL,
	[col_isHarmony] [char](1) NULL,
	[col_eq] [varchar](50) NULL,
	[col_prise] [bigint] NULL,
	[col_priseType] [smallint] NULL,
	[col_isMy] [char](1) NULL,
	[col_user] [varchar](10) NULL,
 CONSTRAINT [PK_mwc_web_shop] PRIMARY KEY CLUSTERED
(
	[col_shopID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]

GO

SET ANSI_PADDING OFF
GO

ALTER TABLE [dbo].[mwc_web_shop] ADD  CONSTRAINT [DF_mwc_web_shop_col_level]  DEFAULT ('0') FOR [col_level]
GO

ALTER TABLE [dbo].[mwc_web_shop] ADD  CONSTRAINT [DF_mwc_web_shop_col_isOpt]  DEFAULT ('0') FOR [col_isOpt]
GO

ALTER TABLE [dbo].[mwc_web_shop] ADD  CONSTRAINT [DF_mwc_web_shop_col_isExc]  DEFAULT ('0') FOR [col_isExc]
GO

ALTER TABLE [dbo].[mwc_web_shop] ADD  CONSTRAINT [DF_mwc_web_shop_col_isAnc]  DEFAULT ('0') FOR [col_isAnc]
GO

ALTER TABLE [dbo].[mwc_web_shop] ADD  CONSTRAINT [DF_mwc_web_shop_col_isSock]  DEFAULT ('0') FOR [col_isSock]
GO

ALTER TABLE [dbo].[mwc_web_shop] ADD  CONSTRAINT [DF_mwc_web_shop_col_isSkill]  DEFAULT ('0') FOR [col_isSkill]
GO

ALTER TABLE [dbo].[mwc_web_shop] ADD  CONSTRAINT [DF_mwc_web_shop_col_isHarmony]  DEFAULT ('0') FOR [col_isHarmony]
GO

ALTER TABLE [dbo].[mwc_web_shop] ADD  CONSTRAINT [DF_mwc_web_shop_col_isMy]  DEFAULT ('0') FOR [col_isMy]
GO








