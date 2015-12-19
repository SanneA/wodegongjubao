SET ANSI_PADDING ON;

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
) ON [PRIMARY];

CREATE TABLE [dbo].[MWC_MMO_TOP](
	[col_mmoID] [int] IDENTITY(1,1) NOT NULL,
	[col_memb_id] [varchar](10) NOT NULL,
	[col_LastVote] [datetime] NOT NULL,
	[col_votes] [int] NOT NULL,
 CONSTRAINT [PK_MWC_MMO_TOP] PRIMARY KEY CLUSTERED
(
	[col_mmoID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY];


SET ANSI_PADDING OFF;
ALTER TABLE [dbo].[mwc_web_shop] ADD  CONSTRAINT [DF_mwc_web_shop_col_level]  DEFAULT ('0') FOR [col_level];
ALTER TABLE [dbo].[mwc_web_shop] ADD  CONSTRAINT [DF_mwc_web_shop_col_isOpt]  DEFAULT ('0') FOR [col_isOpt];
ALTER TABLE [dbo].[mwc_web_shop] ADD  CONSTRAINT [DF_mwc_web_shop_col_isExc]  DEFAULT ('0') FOR [col_isExc];
ALTER TABLE [dbo].[mwc_web_shop] ADD  CONSTRAINT [DF_mwc_web_shop_col_isAnc]  DEFAULT ('0') FOR [col_isAnc];
ALTER TABLE [dbo].[mwc_web_shop] ADD  CONSTRAINT [DF_mwc_web_shop_col_isSock]  DEFAULT ('0') FOR [col_isSock];
ALTER TABLE [dbo].[mwc_web_shop] ADD  CONSTRAINT [DF_mwc_web_shop_col_isSkill]  DEFAULT ('0') FOR [col_isSkill];
ALTER TABLE [dbo].[mwc_web_shop] ADD  CONSTRAINT [DF_mwc_web_shop_col_isHarmony]  DEFAULT ('0') FOR [col_isHarmony];
ALTER TABLE [dbo].[mwc_web_shop] ADD  CONSTRAINT [DF_mwc_web_shop_col_isMy]  DEFAULT ('0') FOR [col_isMy];

