USE [Development]
GO
/****** Object:  Table [dbo].[ActivityQueue]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[ActivityQueue](
	[AQID] [int] IDENTITY(1,1) NOT NULL,
	[QTimeStamp] [datetime] NOT NULL,
	[QTID] [int] NOT NULL,
	[QSID] [int] NOT NULL,
	[TableID] [int] NOT NULL,
	[KeyFieldID] [int] NOT NULL,
	[RowID] [int] NOT NULL,
	[UserID] [int] NOT NULL,
	[QMessage] [varchar](max) NOT NULL,
	[QStateTimeStamp] [datetime] NOT NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
 CONSTRAINT [PK_AQID] PRIMARY KEY CLUSTERED 
(
	[AQID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[ActivityQueueState]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[ActivityQueueState](
	[QSID] [int] NOT NULL,
	[QSCode] [varchar](50) NOT NULL,
	[QSDescription] [varchar](300) NOT NULL,
	[QSComment] [varchar](max) NOT NULL,
	[QSModifiedBy] [int] NOT NULL,
	[QSModifiedOn] [datetime] NOT NULL,
	[QSInactive] [bit] NOT NULL,
	[QSDeleted] [bit] NOT NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
 CONSTRAINT [PK_AQS] PRIMARY KEY CLUSTERED 
(
	[QSID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[ActivityQueueType]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[ActivityQueueType](
	[QTID] [int] NOT NULL,
	[QTCode] [varchar](50) NOT NULL,
	[QTDescription] [varchar](300) NOT NULL,
	[QTComment] [varchar](max) NOT NULL,
	[QTModifiedBy] [int] NOT NULL,
	[QTModifiedOn] [datetime] NOT NULL,
	[QTInactive] [bit] NOT NULL,
	[QTDeleted] [bit] NOT NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
 CONSTRAINT [PK_AQT] PRIMARY KEY CLUSTERED 
(
	[QTID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Alarms]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Alarms](
	[ID] [int] NOT NULL,
	[Type] [char](10) NULL,
	[Record] [char](15) NULL,
	[fDate] [datetime] NULL,
	[fTime] [char](5) NULL,
	[Message] [text] NULL,
PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[APInvoiceStatus]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[APInvoiceStatus](
	[ID] [tinyint] NOT NULL,
	[Type] [varchar](50) NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[ARAgeCash]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[ARAgeCash](
	[ID] [int] IDENTITY(1,1) NOT FOR REPLICATION NOT NULL,
	[CDate] [datetime] NULL,
	[fDate] [datetime] NULL,
	[Ref] [int] NULL,
	[Loc] [int] NULL,
	[fDesc] [varchar](255) NULL,
	[Due] [datetime] NULL,
	[Amt] [numeric](30, 2) NULL,
	[fPrint] [tinyint] NULL,
	[JobType] [varchar](15) NULL,
PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Attendance]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Attendance](
	[ID] [int] IDENTITY(1,1) NOT NULL,
	[User] [int] NOT NULL,
	[Start] [datetime] NULL,
	[End] [datetime] NULL,
	[Start_Notes] [varchar](max) NULL,
	[End_Notes] [varchar](max) NULL,
PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Audit]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Audit](
	[AuditID] [int] IDENTITY(1,1) NOT FOR REPLICATION NOT NULL,
	[AuditCategoryID] [int] NOT NULL,
	[AuditDescr] [varchar](250) NOT NULL,
	[AuditComments] [varchar](max) NULL,
	[AuditSort] [int] NOT NULL,
	[IsRequired] [bit] NOT NULL,
	[ModifiedOn] [datetime] NOT NULL,
	[ModifiedBy] [int] NULL,
	[Inactive] [bit] NOT NULL,
	[Deleted] [bit] NOT NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
 CONSTRAINT [PK_Audit] PRIMARY KEY CLUSTERED 
(
	[AuditID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[AuditCategory]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[AuditCategory](
	[AuditCategoryID] [int] IDENTITY(1,1) NOT FOR REPLICATION NOT NULL,
	[AuditGroupID] [int] NOT NULL,
	[ParentAuditCategoryID] [int] NULL,
	[AuditCategoryCode] [varchar](50) NOT NULL,
	[AuditCategoryDescr] [varchar](250) NULL,
	[AuditCategoryComment] [varchar](max) NULL,
	[AuditCategorySort] [int] NULL,
	[IsRequired] [bit] NOT NULL,
	[ModifiedOn] [datetime] NOT NULL,
	[ModifiedBy] [int] NULL,
	[Inactive] [bit] NOT NULL,
	[Deleted] [bit] NOT NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
 CONSTRAINT [PK_AuditCategory] PRIMARY KEY CLUSTERED 
(
	[AuditCategoryID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[AuditDetail]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[AuditDetail](
	[AuditDetailID] [int] IDENTITY(1,1) NOT FOR REPLICATION NOT NULL,
	[AuditID] [int] NOT NULL,
	[UnitOfMeasureID] [int] NOT NULL,
	[AuditDetailDescr] [varchar](250) NOT NULL,
	[AuditDetailSort] [int] NOT NULL,
	[UseComments] [bit] NOT NULL,
	[IsDefault] [bit] NOT NULL,
	[IndividualDetail] [bit] NOT NULL,
	[IsNonValue] [bit] NOT NULL,
	[FlagPositive] [bit] NOT NULL,
	[FlagNegative] [bit] NOT NULL,
	[PicIsDrawing] [bit] NOT NULL,
	[InternalValue] [numeric](30, 2) NOT NULL,
	[ResultDefaultValue] [varchar](250) NULL,
	[DisplayMeasureLabel] [bit] NOT NULL,
	[MinValue] [numeric](30, 2) NULL,
	[MaxValue] [numeric](30, 2) NULL,
	[ModifiedOn] [datetime] NOT NULL,
	[ModifiedBy] [int] NULL,
	[Inactive] [bit] NOT NULL,
	[Deleted] [bit] NOT NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
	[ItemID] [int] NULL,
	[IsFlatRate] [bit] NOT NULL,
 CONSTRAINT [PK_AuditDetail_1] PRIMARY KEY CLUSTERED 
(
	[AuditDetailID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[AuditGroup]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[AuditGroup](
	[AuditGroupID] [int] IDENTITY(1,1) NOT FOR REPLICATION NOT NULL,
	[AuditGroupCode] [varchar](50) NOT NULL,
	[AuditGroupDescr] [varchar](250) NOT NULL,
	[AuditGroupComment] [varchar](max) NULL,
	[AuditGroupSort] [int] NOT NULL,
	[IsRequired] [bit] NOT NULL,
	[ModifiedOn] [datetime] NOT NULL,
	[ModifiedBy] [int] NULL,
	[Inactive] [bit] NOT NULL,
	[Deleted] [bit] NOT NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
	[AttachToQuote] [bit] NOT NULL,
	[TicketCategory] [varchar](20) NOT NULL,
 CONSTRAINT [PK_AuditGroup] PRIMARY KEY CLUSTERED 
(
	[AuditGroupID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[AuditResult]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[AuditResult](
	[ID] [int] IDENTITY(1,1) NOT NULL,
	[TableID] [int] NOT NULL,
	[RowID] [int] NOT NULL,
	[TicketID] [int] NOT NULL,
	[AuditID] [int] NOT NULL,
	[AuditDetailID] [int] NOT NULL,
	[UnitID] [int] NULL,
	[Tech] [varchar](50) NULL,
	[ScheduledDate] [datetime] NULL,
	[AccountName] [varchar](75) NULL,
	[Address] [varchar](255) NULL,
	[City] [varchar](50) NULL,
	[State] [varchar](2) NULL,
	[Phone] [varchar](28) NULL,
	[Zip] [varchar](10) NULL,
	[AuditGroupID] [int] NOT NULL,
	[AuditCategoryID] [int] NOT NULL,
	[AuditParentCategoryID01] [int] NULL,
	[AuditParentCategoryID02] [int] NULL,
	[AuditParentCategoryID03] [int] NULL,
	[AuditParentCategoryID04] [int] NULL,
	[AuditGroupDescr] [varchar](250) NULL,
	[AuditGroupCode] [varchar](50) NULL,
	[AuditCategoryDescr] [varchar](250) NULL,
	[AuditCategoryCode] [varchar](50) NULL,
	[AuditParentCategoryDescr01] [varchar](250) NULL,
	[AuditParentCategoryCode01] [varchar](50) NULL,
	[AuditParentCategoryDescr02] [varchar](250) NULL,
	[AuditParentCategoryCode02] [varchar](50) NULL,
	[AuditParentCategoryDescr03] [varchar](250) NULL,
	[AuditParentCategoryCode03] [varchar](50) NULL,
	[AuditParentCategoryDescr04] [varchar](250) NULL,
	[AuditParentCategoryCode04] [varchar](50) NULL,
	[AuditDescr] [varchar](250) NULL,
	[AuditGroupSort] [int] NOT NULL,
	[AuditCategorySort] [int] NOT NULL,
	[AuditSort] [int] NOT NULL,
	[AuditDetailSort] [int] NOT NULL,
	[IndividualDetail] [bit] NOT NULL,
	[AuditDetailDescr] [varchar](250) NULL,
	[UnitOfMeasureID] [int] NOT NULL,
	[UnitOfMeasureCode] [varchar](50) NULL,
	[DisplayLabel] [bit] NOT NULL,
	[UnitOfMeasureLabel] [varchar](50) NULL,
	[IsNumeric] [bit] NOT NULL,
	[IsBoolean] [bit] NOT NULL,
	[IsPic] [bit] NOT NULL,
	[IsDataList] [bit] NOT NULL,
	[DataListType] [int] NULL,
	[CustomDataList] [varchar](1500) NULL,
	[IsNonValue] [bit] NOT NULL,
	[FlagPositive] [bit] NOT NULL,
	[FlagNegative] [bit] NOT NULL,
	[InternalValue] [numeric](30, 2) NULL,
	[MinValue] [numeric](30, 2) NULL,
	[MaxValue] [numeric](30, 2) NULL,
	[StartTime] [datetime] NULL,
	[EndTime] [datetime] NULL,
	[ResultValue] [varchar](max) NULL,
	[ResultComments] [varchar](max) NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
 CONSTRAINT [PK_AuditResult] PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[AuditUnitOfMeasure]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[AuditUnitOfMeasure](
	[UnitOfMeasureID] [int] IDENTITY(1,1) NOT FOR REPLICATION NOT NULL,
	[UnitOfMeasureCode] [varchar](50) NOT NULL,
	[UnitOfMeasureDescr] [varchar](250) NULL,
	[UnitOfMeasureLabel] [varchar](50) NOT NULL,
	[IsNumeric] [bit] NOT NULL,
	[IsBoolean] [bit] NOT NULL,
	[IsPic] [bit] NOT NULL,
	[IsDataList] [bit] NOT NULL,
	[DataListType] [int] NOT NULL,
	[CustomDataList] [varchar](1500) NULL,
	[DisplayLabel] [bit] NOT NULL,
	[ModifiedOn] [datetime] NOT NULL,
	[ModifiedBy] [int] NULL,
	[Inactive] [bit] NOT NULL,
	[Deleted] [bit] NOT NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
 CONSTRAINT [PK_AuditUnitOfMeasure] PRIMARY KEY CLUSTERED 
(
	[UnitOfMeasureID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Bank]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Bank](
	[ID] [int] NOT NULL,
	[fDesc] [varchar](75) NULL,
	[Rol] [int] NULL,
	[NBranch] [varchar](20) NULL,
	[NAcct] [varchar](20) NULL,
	[NRoute] [varchar](20) NULL,
	[NextC] [int] NULL,
	[NextD] [int] NULL,
	[NextE] [int] NULL,
	[Rate] [numeric](30, 2) NULL,
	[CLimit] [numeric](30, 2) NULL,
	[Warn] [smallint] NOT NULL,
	[Recon] [numeric](30, 2) NULL,
	[Balance] [numeric](30, 2) NULL,
	[Status] [smallint] NULL,
	[InUse] [smallint] NOT NULL,
	[ACHFileHeaderStringA] [varchar](255) NULL,
	[ACHFileHeaderStringB] [varchar](255) NULL,
	[ACHFileHeaderStringC] [varchar](255) NULL,
	[ACHCompanyHeaderString1] [varchar](255) NULL,
	[ACHCompanyHeaderString2] [varchar](255) NULL,
	[ACHBatchControlString1] [varchar](255) NULL,
	[ACHBatchControlString2] [varchar](255) NULL,
	[ACHBatchControlString3] [varchar](255) NULL,
	[ACHFileControlString1] [varchar](255) NULL,
	[APACHCompanyID] [varchar](10) NULL,
	[APImmediateOrigin] [varchar](10) NULL,
	[BankType] [int] NULL,
	[ChartID] [int] NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[BankReconHistory]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[BankReconHistory](
	[HistoryId] [int] IDENTITY(1,1) NOT NULL,
	[fDate] [datetime] NOT NULL,
	[Time] [datetime] NOT NULL,
	[TransID] [int] NULL,
	[BankID] [int] NOT NULL,
	[Recon] [numeric](30, 2) NOT NULL,
	[Balance] [numeric](30, 2) NOT NULL,
	[Amount] [numeric](30, 2) NOT NULL,
	[fDesc] [nvarchar](max) NULL,
	[UserName] [nvarchar](50) NULL,
	[ServiceCharge] [decimal](18, 2) NULL,
	[Interest] [decimal](18, 2) NULL,
	[StatementBal] [decimal](18, 2) NULL,
 CONSTRAINT [PK_BankReconHistory] PRIMARY KEY CLUSTERED 
(
	[HistoryId] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[BankReconHistoryItems]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[BankReconHistoryItems](
	[HistoryItemId] [int] IDENTITY(1,1) NOT NULL,
	[FDate] [datetime] NOT NULL,
	[Time] [datetime] NOT NULL,
	[TransID] [int] NULL,
	[BankID] [int] NULL,
	[Type] [nvarchar](max) NULL,
	[Amount] [numeric](30, 2) NOT NULL,
	[Status] [nvarchar](max) NULL,
	[UserName] [nvarchar](50) NULL,
 CONSTRAINT [PK_BankReconHistoryItems] PRIMARY KEY CLUSTERED 
(
	[HistoryItemId] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[BCycle]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[BCycle](
	[Field] [varchar](15) NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Billing]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Billing](
	[Billing] [varchar](10) NOT NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Branch]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Branch](
	[ID] [int] NOT NULL,
	[Name] [varchar](75) NULL,
	[Manager] [varchar](50) NULL,
	[Address] [varchar](100) NULL,
	[City] [varchar](50) NULL,
	[State] [varchar](2) NULL,
	[Zip] [varchar](10) NULL,
	[Phone] [varchar](25) NULL,
	[Fax] [varchar](25) NULL,
	[CostCenter] [varchar](20) NULL,
	[InvRemarks] [varchar](8000) NULL,
	[Logo] [image] NULL,
	[LogoPath] [varchar](255) NULL,
	[BillRemit] [varchar](8000) NULL,
	[PORemit] [varchar](8000) NULL,
	[LocDTerr] [varchar](50) NULL,
	[LocDRoute] [varchar](50) NULL,
	[LocDZone] [varchar](50) NULL,
	[LocDStax] [varchar](50) NULL,
	[LocType] [varchar](50) NULL,
	[ARTerms] [varchar](50) NULL,
	[ADP] [varchar](3) NULL,
	[CB] [numeric](30, 2) NULL,
	[ARContact] [varchar](75) NULL,
	[OType] [varchar](50) NULL,
	[DArea] [varchar](3) NULL,
	[DState] [varchar](2) NULL,
	[MileRate] [numeric](30, 4) NULL,
	[PriceD1] [numeric](30, 4) NULL,
	[PriceD2] [numeric](30, 4) NULL,
	[PriceD3] [numeric](30, 4) NULL,
	[PriceD4] [numeric](30, 4) NULL,
	[PriceD5] [numeric](30, 4) NULL,
	[UTaxR] [tinyint] NULL,
	[UTax] [varchar](25) NULL,
	[MerchantServicesConfig] [varchar](max) NULL,
	[TFMID] [varchar](250) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
 CONSTRAINT [PK_Branch] PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[BRCompany]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[BRCompany](
	[ID] [int] NOT NULL,
	[Name] [varchar](75) NULL,
	[Manager] [varchar](50) NULL,
	[Address] [varchar](100) NULL,
	[City] [varchar](50) NULL,
	[State] [varchar](2) NULL,
	[Zip] [varchar](10) NULL,
	[Phone] [varchar](25) NULL,
	[Fax] [varchar](25) NULL,
	[CostCenter] [int] NULL,
	[InvRemarks] [varchar](1000) NULL,
	[Logo] [image] NULL,
	[LogoPath] [varchar](255) NULL,
	[BillRemit] [varchar](1000) NULL,
	[PORemit] [varchar](1000) NULL,
	[LocDTerr] [varchar](50) NULL,
	[LocDRoute] [varchar](50) NULL,
	[LocDZone] [varchar](50) NULL,
	[LocDStax] [varchar](50) NULL,
	[LocType] [varchar](50) NULL,
	[ARTerms] [varchar](50) NULL,
	[ChargeInt] [varchar](50) NULL,
	[ADP] [varchar](3) NULL,
	[CB] [numeric](30, 4) NULL,
	[ARContact] [varchar](75) NULL,
	[OType] [varchar](50) NULL,
	[DArea] [varchar](3) NULL,
	[DState] [varchar](2) NULL,
	[MileRate] [numeric](30, 4) NULL,
	[PriceD1] [numeric](30, 4) NULL,
	[PriceD2] [numeric](30, 4) NULL,
	[PriceD3] [numeric](30, 4) NULL,
	[PriceD4] [numeric](30, 4) NULL,
	[PriceD5] [numeric](30, 4) NULL,
	[UTaxR] [tinyint] NULL,
	[UTax] [varchar](25) NULL,
	[Company] [varchar](50) NULL,
PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[CallStats]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[CallStats](
	[fDate] [datetime] NOT NULL,
	[6] [smallint] NULL,
	[7] [smallint] NULL,
	[8] [smallint] NULL,
	[9] [smallint] NULL,
	[10] [smallint] NULL,
	[11] [smallint] NULL,
	[12] [smallint] NULL,
	[13] [smallint] NULL,
	[14] [smallint] NULL,
	[15] [smallint] NULL,
	[16] [smallint] NULL,
	[17] [smallint] NULL,
	[18] [smallint] NULL,
	[19] [smallint] NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[CallStats2]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[CallStats2](
	[ID] [int] IDENTITY(1,1) NOT FOR REPLICATION NOT NULL,
	[fDate] [datetime] NULL,
	[fHour] [tinyint] NULL,
	[fMinute] [tinyint] NULL,
PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Category]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Category](
	[ID] [int] IDENTITY(1,1) NOT NULL,
	[Type] [varchar](30) NULL,
	[Count] [smallint] NULL,
	[Remarks] [varchar](8000) NULL,
	[Color] [smallint] NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
 CONSTRAINT [PK_Category] PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Category_Test]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Category_Test](
	[ID] [int] IDENTITY(1,1) NOT NULL,
	[User] [int] NOT NULL,
	[Start] [datetime] NOT NULL,
	[End] [datetime] NOT NULL,
	[Location] [int] NOT NULL,
	[Unit] [int] NOT NULL,
	[Fire_Service_Test] [bit] NULL,
	[Modernization] [bit] NULL,
	[General_Comments] [varchar](max) NULL,
	[Internal_Comments] [varchar](max) NULL,
	[Witness_Name] [varchar](255) NULL,
	[Witness_License] [varchar](255) NULL,
	[Witness_Date] [datetime] NULL,
	[Inspector_Name] [varchar](255) NULL,
	[Inspector_License] [varchar](255) NULL,
	[Inspector_Date] [datetime] NULL,
	[Type] [varchar](255) NULL,
	[Violation] [int] NULL,
	[Ticket] [int] NULL,
	[Status] [varchar](255) NULL,
	[Printed] [bit] NULL,
	[Void] [bit] NULL,
	[Parent] [int] NULL,
	[Final] [bit] NULL,
	[LoadTest] [int] NULL,
	[Stage] [varchar](256) NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[CD]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[CD](
	[ID] [int] NOT NULL,
	[fDate] [datetime] NULL,
	[Ref] [int] NULL,
	[fDesc] [varchar](50) NULL,
	[Amount] [numeric](30, 2) NULL,
	[Bank] [int] NULL,
	[Type] [smallint] NULL,
	[Status] [smallint] NULL,
	[TransID] [int] NULL,
	[Vendor] [int] NULL,
	[French] [varchar](255) NULL,
	[Memo] [varchar](75) NULL,
	[VoidR] [varchar](75) NULL,
	[ACH] [tinyint] NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Chart]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Chart](
	[ID] [int] NOT NULL,
	[Acct] [varchar](15) NULL,
	[fDesc] [varchar](75) NULL,
	[Balance] [numeric](30, 2) NULL,
	[Type] [smallint] NULL,
	[Sub] [varchar](50) NULL,
	[Remarks] [varchar](8000) NULL,
	[Control] [smallint] NOT NULL,
	[InUse] [smallint] NOT NULL,
	[Detail] [smallint] NULL,
	[CAlias] [varchar](20) NULL,
	[Status] [smallint] NULL,
	[Sub2] [varchar](50) NULL,
	[DAT] [smallint] NULL,
	[Branch] [int] NULL,
	[CostCenter] [smallint] NULL,
	[AcctRoot] [varchar](15) NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
 CONSTRAINT [PK_Chart] PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[ChartA]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[ChartA](
	[Acct] [int] NULL,
	[Period] [int] NULL,
	[Credit] [numeric](30, 2) NULL,
	[Debit] [numeric](30, 2) NULL,
	[Amount] [numeric](30, 2) NULL,
	[Budget] [numeric](30, 2) NULL,
	[Budget2] [numeric](30, 2) NULL,
	[Budget3] [numeric](30, 2) NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Codes]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Codes](
	[ID] [int] IDENTITY(1,1) NOT NULL,
	[Code] [varchar](8) NOT NULL,
	[Text] [varchar](max) NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
 CONSTRAINT [PK_Codes] PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Comments]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Comments](
	[ID] [int] IDENTITY(1,1) NOT NULL,
	[Ref] [int] NULL,
	[Type] [int] NULL,
	[CDate] [datetime] NULL,
	[UserID] [varchar](50) NULL,
	[Comment] [varchar](max) NULL,
	[ExtraString] [varchar](max) NULL,
	[ExtraInt] [int] NULL,
PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[CommIndex]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[CommIndex](
	[fDate] [datetime] NULL,
	[Labor] [numeric](30, 2) NULL,
	[Mat] [numeric](30, 2) NULL,
	[EN] [int] NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[CompBids]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[CompBids](
	[ID] [int] NOT NULL,
	[EstID] [int] NULL,
	[RolID] [int] NULL,
	[Price] [numeric](30, 2) NULL,
	[Remarks] [varchar](75) NULL,
	[Awarded] [smallint] NOT NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[CompReport]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[CompReport](
	[Name] [varchar](50) NOT NULL,
	[fDesc] [varchar](8000) NULL,
	[RType] [smallint] NULL,
	[Type1] [smallint] NULL,
	[Type2] [smallint] NULL,
	[Type3] [smallint] NULL,
	[Type4] [smallint] NULL,
	[Type5] [smallint] NULL,
	[Type6] [smallint] NULL,
	[T1] [varchar](10) NULL,
	[T2] [varchar](10) NULL,
	[T3] [varchar](10) NULL,
	[T4] [varchar](10) NULL,
	[T5] [varchar](10) NULL,
	[T6] [varchar](10) NULL,
	[SDate1] [datetime] NULL,
	[SDate2] [datetime] NULL,
	[SDate3] [datetime] NULL,
	[SDate4] [datetime] NULL,
	[SDate5] [datetime] NULL,
	[SDate6] [datetime] NULL,
	[EDate1] [datetime] NULL,
	[EDate2] [datetime] NULL,
	[EDate3] [datetime] NULL,
	[EDate4] [datetime] NULL,
	[EDate5] [datetime] NULL,
	[EDate6] [datetime] NULL,
	[C31] [smallint] NULL,
	[C32] [smallint] NULL,
	[C41] [smallint] NULL,
	[C42] [smallint] NULL,
	[C51] [smallint] NULL,
	[C52] [smallint] NULL,
	[C61] [smallint] NULL,
	[C62] [smallint] NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Connection]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Connection](
	[ID] [int] IDENTITY(1,1) NOT FOR REPLICATION NOT NULL,
	[Connector] [int] NOT NULL,
	[Hash] [varchar](255) NOT NULL,
	[Timestamped] [varchar](255) NULL,
PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Contract]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Contract](
	[ID] [int] IDENTITY(1,1) NOT NULL,
	[Job] [int] NULL,
	[Loc] [int] NULL,
	[Owner] [int] NULL,
	[Review] [datetime] NULL,
	[Disc1] [numeric](30, 2) NULL,
	[Disc2] [numeric](30, 2) NULL,
	[Disc3] [numeric](30, 2) NULL,
	[Disc4] [numeric](30, 2) NULL,
	[Disc5] [numeric](30, 2) NULL,
	[Disc6] [numeric](30, 2) NULL,
	[DiscType] [smallint] NULL,
	[DiscRate] [numeric](30, 2) NULL,
	[BCycle] [smallint] NULL,
	[BStart] [datetime] NULL,
	[BLenght] [smallint] NULL,
	[BFinish] [datetime] NULL,
	[BAmt] [numeric](30, 2) NULL,
	[BEscType] [smallint] NULL,
	[BEscCycle] [smallint] NULL,
	[BEscFact] [numeric](30, 2) NULL,
	[SCycle] [smallint] NULL,
	[SType] [varchar](10) NULL,
	[SDay] [smallint] NULL,
	[SDate] [smallint] NULL,
	[STime] [datetime] NULL,
	[SWE] [smallint] NOT NULL,
	[SStart] [datetime] NULL,
	[Detail] [smallint] NULL,
	[Cycle] [smallint] NULL,
	[EscLast] [datetime] NOT NULL,
	[OldAmt] [numeric](30, 2) NULL,
	[WK] [smallint] NULL,
	[Skill] [varchar](25) NULL,
	[Status] [smallint] NULL,
	[Hours] [numeric](30, 2) NULL,
	[Hour] [numeric](30, 2) NULL,
	[Terms] [int] NULL,
	[OffService] [datetime] NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
	[sDay2] [smallint] NULL,
	[sDate2] [smallint] NULL,
	[sTime2] [datetime] NULL,
	[sWE2] [smallint] NULL,
 CONSTRAINT [PK_Contract] PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Control]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Control](
	[ID] [int] IDENTITY(1,1) NOT NULL,
	[Name] [varchar](75) NULL,
	[City] [varchar](50) NULL,
	[State] [varchar](2) NULL,
	[Zip] [varchar](10) NULL,
	[Phone] [varchar](20) NULL,
	[Fax] [varchar](20) NULL,
	[fLong] [float] NULL,
	[Latt] [float] NULL,
	[GeoLock] [smallint] NOT NULL,
	[YE] [smallint] NULL,
	[Version] [numeric](30, 2) NULL,
	[CDesc] [varchar](255) NULL,
	[Build] [real] NULL,
	[Minor] [smallint] NULL,
	[Address] [varchar](255) NULL,
	[AgeRemark] [varchar](255) NULL,
	[SDate] [varchar](50) NULL,
	[EDate] [varchar](50) NULL,
	[YDate] [varchar](50) NULL,
	[GSTreg] [varchar](20) NULL,
	[IDesc] [varchar](255) NULL,
	[PortalsID] [int] NULL,
	[PrContractRemark] [varchar](1000) NULL,
	[RepUser] [varchar](50) NULL,
	[RepTitle] [varchar](255) NULL,
	[Logo] [image] NULL,
	[LogoPath] [varchar](255) NULL,
	[ExeVersion_Min] [varchar](15) NULL,
	[ExeVersion_Max] [varchar](15) NULL,
	[MerchantServicesConfig] [varchar](max) NULL,
	[Website] [varchar](100) NULL,
	[gps] [bit] NULL,
	[VersionRevision] [int] NOT NULL,
	[ARInvoiceEmailText] [varchar](1000) NOT NULL,
	[TicketEmailText] [varchar](1000) NOT NULL,
	[TabletTicket] [tinyint] NOT NULL,
	[Email] [varchar](100) NULL,
	[EmailClient] [varchar](75) NULL,
	[EmailPort] [varchar](5) NULL,
	[DefaultCredential] [varchar](5) NOT NULL,
	[EnableSsl] [varchar](5) NOT NULL,
	[password] [varbinary](256) NULL,
	[UseTSPortal] [int] NOT NULL,
 CONSTRAINT [PK_Control] PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[CostCenter]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[CostCenter](
	[Center] [smallint] NOT NULL,
	[Label] [varchar](50) NULL,
	[Identifier] [varchar](50) NULL,
	[ID] [int] IDENTITY(1,1) NOT FOR REPLICATION NOT NULL,
	[CSID] [int] NULL,
PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[CostCenterTemp]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[CostCenterTemp](
	[ID] [int] NOT NULL,
	[fUser] [varchar](10) NULL,
	[fDesc] [varchar](50) NULL,
PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[CreditCard]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[CreditCard](
	[idCreditCard] [int] IDENTITY(1,1) NOT FOR REPLICATION NOT NULL,
	[HolderType] [bit] NOT NULL,
	[idHolder] [int] NOT NULL,
	[CardType] [varchar](10) NULL,
	[MaskedAccount] [varchar](20) NOT NULL,
	[idMerchantService] [int] NOT NULL,
	[NickName] [varchar](30) NULL,
 CONSTRAINT [PK_idCreditCard] PRIMARY KEY CLUSTERED 
(
	[idCreditCard] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[CreditCardTrans]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[CreditCardTrans](
	[idCreditCardTrans] [int] IDENTITY(1,1) NOT FOR REPLICATION NOT NULL,
	[TransState] [int] NOT NULL,
	[idTrans_Internal] [int] NULL,
	[idTrans_External] [varchar](20) NULL,
	[idBatch] [varchar](20) NULL,
	[RefNum] [varchar](50) NULL,
	[Note] [varchar](255) NULL,
	[AccountType] [bit] NULL,
	[idAccount] [int] NULL,
	[Amount] [money] NULL,
	[idBankAccount] [int] NULL,
	[idDep] [int] NULL,
	[DateCreated] [smalldatetime] NOT NULL,
	[DateSubmitted] [smalldatetime] NULL,
	[DateApproved] [datetime] NULL,
	[DateDeposited] [smalldatetime] NULL,
	[DateApplied] [smalldatetime] NULL,
	[ReceiptData] [text] NULL,
	[UserName] [varchar](50) NULL,
	[idBranch] [int] NULL,
 CONSTRAINT [PK_idCreditCardTrans] PRIMARY KEY CLUSTERED 
(
	[idCreditCardTrans] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[CType]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[CType](
	[Type] [varchar](10) NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Custom]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Custom](
	[Name] [varchar](50) NULL,
	[Label] [varchar](50) NULL,
	[Number] [int] NULL,
	[ID] [int] IDENTITY(1,1) NOT FOR REPLICATION NOT NULL,
PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[CustomContact]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[CustomContact](
	[idCustomContact] [int] IDENTITY(1,1) NOT FOR REPLICATION NOT NULL,
	[idRol] [int] NOT NULL,
	[Custom1] [varchar](50) NULL,
	[Custom2] [varchar](50) NULL,
	[Custom3] [varchar](50) NULL,
	[Custom4] [varchar](50) NULL,
	[Custom5] [varchar](50) NULL,
	[Custom6] [varchar](50) NULL,
	[Custom7] [varchar](50) NULL,
	[Custom8] [varchar](50) NULL,
	[Custom9] [varchar](50) NULL,
	[Custom10] [varchar](50) NULL,
 CONSTRAINT [PK_idCustomContact] PRIMARY KEY CLUSTERED 
(
	[idCustomContact] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[CustPortalUser]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[CustPortalUser](
	[ID] [int] IDENTITY(1,1) NOT NULL,
	[fUser] [varchar](50) NOT NULL,
	[Password] [varchar](20) NOT NULL,
	[Owner] [int] NOT NULL,
	[Status] [smallint] NULL,
	[Access] [int] NULL,
	[fStart] [date] NULL,
	[fEnd] [date] NULL,
	[Since] [date] NULL,
	[Last] [date] NULL,
	[Ticket] [smallint] NULL,
	[History] [smallint] NULL,
	[Invoice] [smallint] NULL,
	[Quote] [smallint] NULL,
	[Service] [smallint] NULL,
	[Approve] [smallint] NULL,
	[Request] [smallint] NULL,
	[Safety] [smallint] NULL,
	[Dispatch] [smallint] NULL,
PRIMARY KEY CLUSTERED 
(
	[fUser] ASC,
	[Password] ASC,
	[Owner] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, FILLFACTOR = 90) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Dep]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Dep](
	[Ref] [int] NOT NULL,
	[fDate] [datetime] NULL,
	[Bank] [int] NULL,
	[fDesc] [varchar](50) NULL,
	[Amount] [numeric](30, 2) NULL,
	[TransID] [int] NULL,
	[EN] [int] NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[DepApply]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[DepApply](
	[ID] [int] IDENTITY(1,1) NOT FOR REPLICATION NOT NULL,
	[Type] [tinyint] NULL,
	[TransID] [int] NULL,
	[Amount] [numeric](30, 2) NULL,
	[fDate] [datetime] NULL,
	[Status] [varchar](10) NULL,
PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Diagnostic]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Diagnostic](
	[ID] [int] IDENTITY(1,1) NOT NULL,
	[Category] [varchar](15) NULL,
	[EstTime] [numeric](30, 2) NULL,
	[Count] [int] NULL,
	[Type] [smallint] NULL,
	[fDesc] [varchar](255) NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
 CONSTRAINT [PK_Diagnostic] PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[DispAlertType]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[DispAlertType](
	[ID] [smallint] IDENTITY(1,1) NOT FOR REPLICATION NOT NULL,
	[Type] [varchar](15) NOT NULL,
	[Count] [smallint] NULL,
	[Color] [smallint] NULL,
	[Remarks] [varchar](255) NULL,
PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[DocType]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[DocType](
	[ID] [int] NOT NULL,
	[fDesc] [varchar](25) NULL,
PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Documents]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Documents](
	[ID] [int] IDENTITY(1,1) NOT FOR REPLICATION NOT NULL,
	[Screen] [varchar](20) NULL,
	[ScreenID] [int] NULL,
	[Line] [smallint] NULL,
	[fDesc] [varchar](255) NULL,
	[Filename] [varchar](75) NULL,
	[Path] [varchar](255) NULL,
	[Type] [smallint] NULL,
	[Remarks] [varchar](8000) NULL,
	[Custom1] [datetime] NULL,
	[Custom2] [datetime] NULL,
	[Custom3] [datetime] NULL,
	[Custom4] [datetime] NULL,
	[Custom5] [datetime] NULL,
	[Custom6] [tinyint] NULL,
	[Custom7] [tinyint] NULL,
	[Custom8] [tinyint] NULL,
	[Custom9] [tinyint] NULL,
	[Custom10] [tinyint] NULL,
	[Custom11] [varchar](75) NULL,
	[Custom12] [varchar](75) NULL,
	[Custom13] [varchar](75) NULL,
	[Custom14] [varchar](75) NULL,
	[Custom15] [varchar](75) NULL,
PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[DocuwareExp]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[DocuwareExp](
	[ID] [int] NOT NULL,
	[Loc] [int] NULL,
	[WorkOrder] [varchar](10) NULL,
	[JobType] [varchar](15) NULL,
	[Cat] [varchar](25) NULL,
	[Reg] [numeric](30, 2) NULL,
	[OT] [numeric](30, 2) NULL,
	[NT] [numeric](30, 2) NULL,
	[DT] [numeric](30, 2) NULL,
	[TT] [numeric](30, 2) NULL,
	[Total] [numeric](30, 2) NULL,
	[Mileage] [numeric](30, 2) NULL,
	[Zone] [numeric](30, 2) NULL,
	[Toll] [numeric](30, 2) NULL,
	[Other] [numeric](30, 2) NULL,
	[EDate] [datetime] NULL,
	[Worker] [varchar](100) NULL,
	[Who] [varchar](30) NULL,
	[fdesc] [text] NULL,
	[DescRes] [text] NULL,
	[Signature] [image] NULL,
	[Job] [int] NULL,
	[Unit] [varchar](50) NULL,
	[Invoice] [int] NULL,
PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Done]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Done](
	[ID] [int] NOT NULL,
	[Type] [smallint] NULL,
	[Rol] [int] NULL,
	[fDate] [datetime] NULL,
	[fTime] [datetime] NULL,
	[DateDone] [datetime] NULL,
	[TimeDone] [datetime] NULL,
	[Subject] [varchar](50) NULL,
	[Remarks] [text] NULL,
	[Result] [text] NULL,
	[Keyword] [varchar](10) NULL,
	[fUser] [varchar](50) NULL,
	[fBy] [varchar](50) NULL,
	[Duration] [numeric](30, 2) NULL,
	[Contact] [varchar](50) NULL,
	[Source] [varchar](25) NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Elev]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Elev](
	[ID] [int] NOT NULL,
	[Unit] [varchar](255) NULL,
	[State] [varchar](25) NULL,
	[Loc] [int] NULL,
	[Owner] [int] NULL,
	[Cat] [varchar](20) NULL,
	[Type] [varchar](20) NULL,
	[Building] [varchar](20) NULL,
	[Manuf] [varchar](50) NULL,
	[Remarks] [text] NULL,
	[Install] [datetime] NULL,
	[InstallBy] [varchar](25) NULL,
	[Since] [datetime] NULL,
	[Last] [datetime] NULL,
	[Price] [numeric](30, 2) NULL,
	[fGroup] [varchar](25) NULL,
	[fDesc] [varchar](255) NULL,
	[Serial] [varchar](50) NULL,
	[Template] [int] NULL,
	[Status] [smallint] NULL,
	[Week] [varchar](50) NULL,
	[Custom1] [varchar](255) NULL,
	[Custom2] [varchar](255) NULL,
	[Custom3] [varchar](255) NULL,
	[Custom4] [varchar](255) NULL,
	[Custom5] [varchar](255) NULL,
	[Custom6] [varchar](255) NULL,
	[Custom7] [varchar](255) NULL,
	[Custom8] [varchar](255) NULL,
	[Custom9] [varchar](255) NULL,
	[Custom10] [varchar](255) NULL,
	[Custom11] [varchar](255) NULL,
	[Custom12] [varchar](255) NULL,
	[Custom13] [varchar](255) NULL,
	[Custom14] [varchar](255) NULL,
	[Custom15] [varchar](255) NULL,
	[Custom16] [varchar](255) NULL,
	[Custom17] [varchar](255) NULL,
	[Custom18] [varchar](255) NULL,
	[Custom19] [varchar](255) NULL,
	[Custom20] [varchar](255) NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
 CONSTRAINT [PK_Elev] PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[ElevatorSpec]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[ElevatorSpec](
	[ID] [int] IDENTITY(1,1) NOT NULL,
	[ECat] [smallint] NULL,
	[EDesc] [varchar](20) NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
 CONSTRAINT [PK_ElevatorSpec] PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[ElevLog]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[ElevLog](
	[ID] [int] IDENTITY(1,1) NOT FOR REPLICATION NOT NULL,
	[Type] [smallint] NULL,
	[Loc] [int] NULL,
	[Job] [int] NULL,
	[Elev] [int] NULL,
	[Unit] [varchar](255) NULL,
	[PrevCount] [smallint] NULL,
	[fDate] [datetime] NULL,
PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[ElevT]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[ElevT](
	[ID] [int] NOT NULL,
	[fDesc] [varchar](50) NULL,
	[Count] [int] NULL,
	[Remarks] [varchar](8000) NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
 CONSTRAINT [PK_ElevT] PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[ElevTItem]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[ElevTItem](
	[ID] [int] NOT NULL,
	[ElevT] [int] NULL,
	[Elev] [int] NULL,
	[CustomID] [int] NULL,
	[fDesc] [varchar](50) NULL,
	[Line] [smallint] NULL,
	[Value] [varchar](50) NULL,
	[Format] [varchar](50) NULL,
	[fExists] [smallint] NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
 CONSTRAINT [PK_ElevTItem] PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[EmailNotification]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[EmailNotification](
	[NotificationId] [bigint] IDENTITY(1,1) NOT NULL,
	[ProcessType] [nvarchar](250) NOT NULL,
	[RefId] [nvarchar](10) NOT NULL,
	[SendOn] [datetime] NOT NULL,
	[Status] [smallint] NOT NULL,
	[SentBy] [nvarchar](25) NOT NULL,
 CONSTRAINT [PK_EmailNotification] PRIMARY KEY CLUSTERED 
(
	[NotificationId] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Emp]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Emp](
	[ID] [int] NOT NULL,
	[fFirst] [varchar](15) NULL,
	[Last] [varchar](25) NULL,
	[Middle] [varchar](15) NULL,
	[Name] [varchar](55) NULL,
	[Rol] [int] NULL,
	[SSN] [varchar](11) NULL,
	[Title] [varchar](50) NULL,
	[Sales] [smallint] NOT NULL,
	[Field] [smallint] NOT NULL,
	[Status] [smallint] NULL,
	[Pager] [varchar](100) NULL,
	[InUse] [smallint] NOT NULL,
	[PayPeriod] [smallint] NULL,
	[DHired] [datetime] NULL,
	[DFired] [datetime] NULL,
	[DBirth] [datetime] NULL,
	[DReview] [datetime] NULL,
	[DLast] [datetime] NULL,
	[FStatus] [smallint] NULL,
	[FAllow] [smallint] NULL,
	[FAdd] [numeric](30, 2) NULL,
	[SStatus] [smallint] NULL,
	[SAllow] [smallint] NULL,
	[SAdd] [numeric](30, 2) NULL,
	[CallSign] [varchar](50) NULL,
	[VRate] [numeric](30, 4) NULL,
	[VBase] [smallint] NULL,
	[VLast] [numeric](30, 2) NULL,
	[VThis] [numeric](30, 2) NULL,
	[Sick] [numeric](30, 2) NULL,
	[PMethod] [smallint] NULL,
	[PFixed] [smallint] NULL,
	[PHour] [numeric](30, 2) NULL,
	[LName] [smallint] NULL,
	[LStatus] [smallint] NULL,
	[LAllow] [smallint] NULL,
	[PRTaxE] [int] NULL,
	[State] [varchar](2) NULL,
	[Salary] [numeric](30, 2) NULL,
	[SalaryF] [smallint] NULL,
	[SalaryGL] [int] NULL,
	[fWork] [int] NULL,
	[NPaid] [smallint] NULL,
	[Balance] [numeric](30, 2) NULL,
	[PBRate] [numeric](30, 2) NULL,
	[FITYTD] [numeric](30, 2) NULL,
	[FICAYTD] [numeric](30, 2) NULL,
	[MEDIYTD] [numeric](30, 2) NULL,
	[FUTAYTD] [numeric](30, 2) NULL,
	[SITYTD] [numeric](30, 2) NULL,
	[LocalYTD] [numeric](30, 2) NULL,
	[BonusYTD] [numeric](30, 2) NULL,
	[HolH] [numeric](30, 2) NULL,
	[HolYTD] [numeric](30, 2) NULL,
	[VacH] [numeric](30, 2) NULL,
	[VacYTD] [numeric](30, 2) NULL,
	[ZoneH] [numeric](30, 2) NULL,
	[ZoneYTD] [numeric](30, 2) NULL,
	[ReimbYTD] [numeric](30, 2) NULL,
	[MileH] [numeric](30, 2) NULL,
	[MileYTD] [numeric](30, 2) NULL,
	[Race] [varchar](40) NULL,
	[Sex] [varchar](10) NULL,
	[Ref] [varchar](15) NULL,
	[ACH] [smallint] NULL,
	[ACHType] [smallint] NULL,
	[ACHRoute] [varchar](20) NULL,
	[ACHBank] [varchar](20) NULL,
	[Anniversary] [datetime] NULL,
	[Level] [int] NULL,
	[WageCat] [int] NULL,
	[DSenior] [datetime] NULL,
	[PDASerialNumber] [varchar](25) NULL,
	[PRWBR] [int] NULL,
	[StatusChange] [tinyint] NULL,
	[SCDate] [datetime] NULL,
	[SCReason] [varchar](2) NULL,
	[DemoChange] [tinyint] NULL,
	[Language] [varchar](2) NULL,
	[TicketD] [tinyint] NULL,
	[Custom1] [varchar](50) NULL,
	[Custom2] [varchar](50) NULL,
	[Custom3] [varchar](50) NULL,
	[Custom4] [varchar](50) NULL,
	[Custom5] [varchar](50) NULL,
	[DDType] [tinyint] NULL,
	[DDRate] [numeric](30, 4) NULL,
	[ACHType2] [tinyint] NULL,
	[ACHRoute2] [varchar](20) NULL,
	[ACHBank2] [varchar](20) NULL,
	[BillRate] [numeric](30, 4) NULL,
	[BMSales] [numeric](30, 4) NULL,
	[BMInvAve] [numeric](30, 4) NULL,
	[BMClosing] [numeric](30, 4) NULL,
	[BMBillEff] [numeric](30, 4) NULL,
	[BMProdEff] [numeric](30, 4) NULL,
	[BMAveTask] [numeric](18, 0) NULL,
	[BMCustom1] [numeric](18, 0) NULL,
	[BMCustom2] [numeric](18, 0) NULL,
	[BMCustom3] [numeric](18, 0) NULL,
	[BMCustom4] [int] NULL,
	[BMCustom5] [int] NULL,
	[TaxCodeNR] [varchar](10) NULL,
	[TaxCodeR] [varchar](10) NULL,
	[TechnicianBio] [image] NULL,
	[PayPortalPassword] [varchar](30) NULL,
	[SickRate] [numeric](30, 4) NULL,
	[SickAccrued] [numeric](30, 2) NULL,
	[SickUsed] [numeric](30, 2) NULL,
	[SickYTD] [numeric](30, 2) NULL,
	[VacAccrued] [numeric](30, 2) NULL,
	[SCounty] [int] NULL,
	[Under17] [int] NULL,
	[OverOr17] [int] NULL,
	[MultiJob] [int] NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
 CONSTRAINT [PK_Emp] PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[EscalationPrt]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[EscalationPrt](
	[ID] [varchar](15) NOT NULL,
	[Tag] [varchar](50) NULL,
	[Type] [varchar](50) NULL,
	[ECycle] [varchar](50) NULL,
	[BCycle] [varchar](50) NULL,
	[SCycle] [varchar](50) NULL,
	[EType] [varchar](50) NULL,
	[Action] [varchar](50) NULL,
	[LastEsc] [datetime] NULL,
	[NextEsc] [datetime] NULL,
	[PrvYear] [numeric](30, 2) NULL,
	[Total] [numeric](30, 2) NULL,
	[Current] [numeric](30, 2) NULL,
	[New] [numeric](30, 2) NULL,
	[CDate] [datetime] NULL,
	[EscFact] [int] NULL,
	[Job] [int] NULL,
	[BStart] [datetime] NULL,
	[TerritoryDesc] [varchar](50) NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Estimate]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Estimate](
	[ID] [int] NOT NULL,
	[RolID] [int] NULL,
	[Name] [varchar](75) NULL,
	[fDesc] [varchar](255) NULL,
	[fDate] [datetime] NULL,
	[BDate] [datetime] NULL,
	[Type] [smallint] NULL,
	[Status] [smallint] NULL,
	[EmpID] [int] NULL,
	[Template] [int] NULL,
	[Remarks] [varchar](8000) NULL,
	[LocID] [int] NULL,
	[Category] [varchar](75) NULL,
	[fFor] [varchar](50) NULL,
	[Cost] [numeric](30, 2) NULL,
	[Hours] [numeric](30, 2) NULL,
	[Labor] [numeric](30, 2) NULL,
	[SubTotal1] [numeric](30, 2) NULL,
	[Overhead] [numeric](30, 2) NULL,
	[Profit] [numeric](30, 2) NULL,
	[SubTotal2] [numeric](30, 2) NULL,
	[Price] [numeric](30, 2) NULL,
	[Job] [int] NULL,
	[Phone] [varchar](28) NULL,
	[Fax] [varchar](28) NULL,
	[Contact] [varchar](50) NULL,
	[EstTemplate] [tinyint] NULL,
	[STaxRate] [numeric](30, 4) NULL,
	[STax] [numeric](30, 4) NULL,
	[SExpense] [numeric](30, 4) NULL,
	[Quoted] [numeric](30, 4) NULL,
	[Phase] [smallint] NULL,
	[Probability] [smallint] NULL,
	[Custom1] [varchar](50) NULL,
	[Custom2] [varchar](50) NULL,
	[PO] [varchar](25) NULL,
	[Custom3] [varchar](255) NULL,
	[Custom4] [varchar](255) NULL,
	[Custom5] [varchar](255) NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[EstimateI]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[EstimateI](
	[ID] [int] NOT NULL,
	[Estimate] [int] NULL,
	[Line] [int] NULL,
	[fDesc] [varchar](150) NULL,
	[Quan] [numeric](30, 2) NULL,
	[Cost] [numeric](30, 2) NULL,
	[Price] [numeric](30, 2) NULL,
	[Hours] [numeric](30, 2) NULL,
	[Rate] [numeric](30, 2) NULL,
	[Labor] [numeric](30, 2) NULL,
	[Amount] [numeric](30, 2) NULL,
	[STax] [tinyint] NULL,
	[Code] [varchar](10) NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[FieldCollMethod]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[FieldCollMethod](
	[Ref] [tinyint] NOT NULL,
	[Type] [varchar](50) NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[FlatRates]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[FlatRates](
	[ID] [int] IDENTITY(1,1) NOT NULL,
	[Name] [varchar](25) NULL,
	[fDesc] [varchar](4000) NULL,
	[Part] [varchar](25) NULL,
	[PartQuan] [int] NULL,
	[PartList] [numeric](30, 2) NULL,
	[PartPrice] [numeric](30, 2) NULL,
	[Type] [smallint] NULL,
	[Cost] [numeric](30, 2) NULL,
	[Hours] [numeric](30, 2) NULL,
	[HoursA] [numeric](30, 2) NULL,
	[Price1] [numeric](30, 2) NULL,
	[Price2] [numeric](30, 2) NULL,
	[Price3] [numeric](30, 2) NULL,
	[Price4] [numeric](30, 2) NULL,
	[Price5] [numeric](30, 2) NULL,
	[fPrimary] [int] NOT NULL,
	[Price6] [numeric](30, 4) NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
 CONSTRAINT [PK_FlatRates] PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[FNS]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[FNS](
	[ID] [int] NOT NULL,
	[Name] [varchar](50) NULL,
	[Screen] [varchar](50) NULL,
	[SQL] [text] NULL,
	[Filter] [text] NULL,
	[Sort] [text] NULL,
	[FilterString] [text] NULL,
	[SortString] [text] NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[GL]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[GL](
	[ID] [int] NULL,
	[Acct] [varchar](15) NULL,
	[fDesc] [varchar](75) NULL,
	[Beginning] [numeric](30, 2) NULL,
	[Activity] [numeric](30, 2) NULL,
	[Ending] [numeric](30, 2) NULL,
	[Detail] [smallint] NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[GLA]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[GLA](
	[Ref] [int] NOT NULL,
	[fDate] [datetime] NULL,
	[Internal] [varchar](50) NULL,
	[fDesc] [varchar](8000) NULL,
	[Batch] [int] NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[GLARecur]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[GLARecur](
	[Ref] [int] NOT NULL,
	[fDate] [datetime] NULL,
	[Internal] [varchar](50) NULL,
	[fDesc] [varchar](8000) NULL,
	[Frequency] [smallint] NULL,
PRIMARY KEY CLUSTERED 
(
	[Ref] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[GLARecurI]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[GLARecurI](
	[ID] [int] IDENTITY(1,1) NOT FOR REPLICATION NOT NULL,
	[Ref] [int] NULL,
	[Line] [smallint] NULL,
	[fDesc] [varchar](255) NULL,
	[Amount] [numeric](30, 2) NULL,
	[Acct] [int] NULL,
	[Job] [int] NULL,
	[Phase] [int] NULL,
PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[GPSControl]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[GPSControl](
	[gpsaccount] [varchar](25) NULL,
	[gpsuser] [varchar](25) NULL,
	[gpspw] [varchar](25) NULL,
	[gpstimezone] [int] NULL,
	[gpscontactouse] [tinyint] NULL,
	[GPSTurnoffSSL] [int] NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[GPSOrders]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[GPSOrders](
	[vid] [int] NULL,
	[gpsno] [varchar](15) NULL,
	[orderid] [varchar](20) NULL,
	[ordertext] [varchar](500) NULL,
	[orderdate] [datetime] NULL,
	[ordertime] [datetime] NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Holiday]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Holiday](
	[Name] [varchar](25) NOT NULL,
	[fDate] [smalldatetime] NOT NULL,
	[OfficeOpen] [varchar](3) NOT NULL,
	[FieldOpen] [varchar](3) NOT NULL,
	[AllDay] [varchar](3) NOT NULL,
	[StartTime] [varchar](5) NULL,
	[EndTime] [varchar](5) NULL,
	[Remarks] [varchar](8000) NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[HumanRes]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[HumanRes](
	[ID] [int] IDENTITY(1,1) NOT FOR REPLICATION NOT NULL,
	[Loc] [int] NULL,
	[Route] [int] NULL,
	[Terr] [int] NULL,
	[Elev] [int] NULL,
	[Amount] [numeric](30, 2) NULL,
	[Hour] [numeric](30, 2) NULL,
 CONSTRAINT [PK_HumanRes_id] PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[IAdj]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[IAdj](
	[ID] [int] NOT NULL,
	[fDate] [datetime] NULL,
	[fDesc] [varchar](255) NULL,
	[Quan] [numeric](30, 2) NULL,
	[Amount] [numeric](30, 2) NULL,
	[Item] [int] NULL,
	[Batch] [int] NULL,
	[TransID] [int] NULL,
	[Acct] [int] NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[ICat]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[ICat](
	[Cat] [varchar](15) NOT NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Inv]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Inv](
	[ID] [int] NOT NULL,
	[Name] [varchar](30) NULL,
	[fDesc] [varchar](max) NULL,
	[Part] [varchar](50) NULL,
	[Status] [smallint] NULL,
	[SAcct] [int] NULL,
	[Measure] [varchar](10) NULL,
	[Tax] [smallint] NOT NULL,
	[Balance] [numeric](30, 2) NULL,
	[Price1] [numeric](30, 4) NULL,
	[Price2] [numeric](30, 4) NULL,
	[Price3] [numeric](30, 4) NULL,
	[Price4] [numeric](30, 4) NULL,
	[Price5] [numeric](30, 4) NULL,
	[Remarks] [varchar](8000) NULL,
	[Cat] [smallint] NULL,
	[LVendor] [int] NULL,
	[LCost] [numeric](30, 4) NULL,
	[AllowZero] [smallint] NOT NULL,
	[Type] [smallint] NULL,
	[InUse] [smallint] NOT NULL,
	[EN] [int] NULL,
	[Hand] [numeric](30, 2) NULL,
	[Aisle] [varchar](15) NULL,
	[fOrder] [numeric](30, 2) NULL,
	[Min] [numeric](30, 2) NULL,
	[Shelf] [varchar](15) NULL,
	[Bin] [varchar](15) NULL,
	[Requ] [numeric](30, 2) NULL,
	[Warehouse] [varchar](5) NULL,
	[Price6] [numeric](30, 4) NULL,
	[Committed] [numeric](30, 4) NULL,
	[datLastPurch] [datetime] NULL,
	[datLastUsed] [datetime] NULL,
	[USA] [bit] NOT NULL,
	[Coupon] [bit] NOT NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
 CONSTRAINT [PK_Inv] PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Invoice]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Invoice](
	[fDate] [datetime] NULL,
	[Ref] [int] NOT NULL,
	[fDesc] [text] NULL,
	[Amount] [numeric](30, 2) NULL,
	[STax] [numeric](30, 2) NULL,
	[Total] [numeric](30, 2) NULL,
	[TaxRegion] [varchar](25) NULL,
	[TaxRate] [numeric](30, 4) NULL,
	[TaxFactor] [numeric](30, 2) NULL,
	[Taxable] [numeric](30, 2) NULL,
	[Type] [smallint] NULL,
	[Job] [int] NULL,
	[Loc] [int] NULL,
	[Terms] [smallint] NULL,
	[PO] [varchar](25) NULL,
	[Status] [smallint] NULL,
	[Batch] [int] NULL,
	[Remarks] [text] NULL,
	[TransID] [int] NULL,
	[GTax] [numeric](30, 2) NULL,
	[Mech] [int] NULL,
	[Pricing] [smallint] NULL,
	[TaxRegion2] [varchar](25) NULL,
	[TaxRate2] [numeric](30, 4) NULL,
	[BillToOpt] [tinyint] NULL,
	[BillTo] [varchar](1000) NULL,
	[Custom1] [varchar](50) NULL,
	[Custom2] [varchar](50) NULL,
	[IDate] [datetime] NULL,
	[fUser] [varchar](50) NULL,
	[Custom3] [varchar](1000) NULL,
	[PSTOnlyAmount] [numeric](30, 2) NULL,
	[fieldcollmethod] [smallint] NULL,
	[fieldcollamount] [numeric](30, 2) NULL,
	[fieldcollref] [varchar](100) NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
	[EMailStatus] [int] NOT NULL,
	[SentBy] [nvarchar](50) NULL,
	[SentOn] [datetime] NULL,
 CONSTRAINT [PK_Invoice] PRIMARY KEY CLUSTERED 
(
	[Ref] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[InvoiceI]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[InvoiceI](
	[ID] [int] IDENTITY(1,1) NOT NULL,
	[Ref] [int] NOT NULL,
	[Line] [smallint] NOT NULL,
	[Acct] [int] NOT NULL,
	[Quan] [numeric](30, 2) NULL,
	[fDesc] [varchar](8000) NULL,
	[Price] [numeric](30, 4) NULL,
	[Amount] [numeric](30, 2) NULL,
	[STax] [smallint] NULL,
	[Job] [int] NULL,
	[JobItem] [int] NULL,
	[TransID] [int] NULL,
	[Measure] [varchar](15) NULL,
	[Disc] [numeric](30, 4) NULL,
	[PSTTax] [smallint] NULL,
	[savingsAmount] [numeric](30, 2) NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
	[OriginalPrice] [numeric](30, 4) NULL,
	[FlatRateID] [int] NULL,
	[USA] [bit] NOT NULL,
	[Coupon] [bit] NOT NULL,
 CONSTRAINT [PK_InvoiceI] PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[InvoiceLog]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[InvoiceLog](
	[ID] [int] IDENTITY(1,1) NOT NULL,
	[fDate] [datetime] NOT NULL,
	[Type] [smallint] NOT NULL,
	[Ref] [int] NOT NULL,
	[fUser] [varchar](50) NOT NULL,
	[Original] [numeric](30, 2) NULL,
	[Current] [numeric](30, 2) NULL,
	[Status] [int] NULL,
	[Quote] [int] NULL,
	[Amount] [numeric](30, 2) NULL,
	[Job] [int] NULL,
	[STax] [numeric](30, 2) NULL,
	[CurrentInvoiceDate] [date] NULL,
	[PreviousInvoiceDate] [date] NULL,
	[Ticket] [int] NULL,
 CONSTRAINT [PK_InvoiceLog] PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[InvoiceStatus]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[InvoiceStatus](
	[Ref] [tinyint] NOT NULL,
	[Type] [varchar](50) NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[InvParts]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[InvParts](
	[ItemID] [int] NULL,
	[Part] [varchar](50) NULL,
	[Supplier] [varchar](25) NULL,
	[Price] [numeric](30, 2) NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[IType]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[IType](
	[ID] [int] NOT NULL,
	[Type] [varchar](30) NULL,
	[Count] [int] NULL,
	[Remarks] [varchar](8000) NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
 CONSTRAINT [PK_IType] PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Job]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Job](
	[ID] [int] NOT NULL,
	[fDesc] [varchar](75) NULL,
	[Type] [smallint] NULL,
	[Loc] [int] NULL,
	[Owner] [int] NULL,
	[Elev] [int] NULL,
	[Status] [smallint] NULL,
	[PO] [varchar](25) NULL,
	[Remarks] [varchar](max) NULL,
	[Rev] [numeric](30, 2) NOT NULL,
	[Mat] [numeric](30, 2) NOT NULL,
	[Labor] [numeric](30, 2) NOT NULL,
	[Cost] [numeric](30, 2) NOT NULL,
	[Profit] [numeric](30, 2) NOT NULL,
	[Ratio] [numeric](30, 2) NOT NULL,
	[Reg] [numeric](30, 2) NOT NULL,
	[OT] [numeric](30, 2) NOT NULL,
	[DT] [numeric](30, 2) NOT NULL,
	[TT] [numeric](30, 2) NOT NULL,
	[Hour] [numeric](30, 2) NOT NULL,
	[BRev] [numeric](30, 2) NOT NULL,
	[BMat] [numeric](30, 2) NOT NULL,
	[BLabor] [numeric](30, 2) NOT NULL,
	[BCost] [numeric](30, 2) NOT NULL,
	[BProfit] [numeric](30, 2) NOT NULL,
	[BRatio] [numeric](30, 2) NOT NULL,
	[BHour] [numeric](30, 2) NOT NULL,
	[Template] [int] NULL,
	[fDate] [datetime] NULL,
	[Comm] [numeric](30, 2) NOT NULL,
	[WageC] [int] NULL,
	[NT] [numeric](30, 2) NOT NULL,
	[Post] [smallint] NULL,
	[EN] [int] NOT NULL,
	[Certified] [smallint] NOT NULL,
	[Apprentice] [smallint] NOT NULL,
	[UseCat] [smallint] NOT NULL,
	[UseDed] [smallint] NOT NULL,
	[BillRate] [numeric](30, 2) NOT NULL,
	[Markup] [numeric](30, 2) NOT NULL,
	[PType] [smallint] NOT NULL,
	[Charge] [smallint] NOT NULL,
	[Amount] [numeric](30, 2) NOT NULL,
	[GL] [int] NULL,
	[GLRev] [int] NULL,
	[GandA] [numeric](30, 2) NOT NULL,
	[OHLabor] [numeric](30, 2) NOT NULL,
	[LastOH] [numeric](30, 2) NOT NULL,
	[etc] [numeric](30, 2) NOT NULL,
	[ETCModifier] [numeric](30, 2) NOT NULL,
	[FP] [varchar](15) NULL,
	[fGroup] [varchar](25) NULL,
	[CType] [varchar](15) NULL,
	[Elevs] [int] NULL,
	[RateTravel] [numeric](30, 2) NULL,
	[RateOT] [numeric](30, 2) NULL,
	[RateNT] [numeric](30, 2) NULL,
	[RateDT] [numeric](30, 2) NULL,
	[RateMileage] [numeric](30, 2) NULL,
	[Custom1] [varchar](75) NULL,
	[Custom2] [varchar](75) NULL,
	[Custom3] [varchar](75) NULL,
	[Custom4] [varchar](75) NULL,
	[Custom5] [varchar](75) NULL,
	[Custom6] [varchar](75) NULL,
	[Custom7] [varchar](75) NULL,
	[Custom8] [varchar](75) NULL,
	[Custom9] [varchar](75) NULL,
	[Custom10] [varchar](75) NULL,
	[Custom11] [varchar](75) NULL,
	[Custom12] [varchar](75) NULL,
	[Custom13] [varchar](75) NULL,
	[Custom14] [varchar](75) NULL,
	[Custom15] [varchar](75) NULL,
	[CloseDate] [datetime] NULL,
	[SPHandle] [smallint] NOT NULL,
	[SRemarks] [varchar](max) NULL,
	[LCode] [int] NULL,
	[CreditCard] [tinyint] NULL,
	[Custom16] [varchar](75) NULL,
	[Custom17] [varchar](75) NULL,
	[Custom18] [varchar](75) NULL,
	[Custom19] [varchar](75) NULL,
	[Custom20] [varchar](75) NULL,
	[NCSLock] [tinyint] NULL,
	[Source] [varchar](20) NULL,
	[Audit] [tinyint] NULL,
	[AuditBy] [varchar](50) NULL,
	[AuditDate] [datetime] NULL,
	[Reopen] [tinyint] NULL,
	[fInt] [tinyint] NULL,
	[NCSClose] [datetime] NULL,
	[Comments] [varchar](max) NULL,
	[Level] [tinyint] NULL,
	[TFMCustom1] [varchar](75) NOT NULL,
	[TFMCustom2] [varchar](75) NOT NULL,
	[TFMCustom3] [varchar](75) NOT NULL,
	[TFMCustom4] [tinyint] NULL,
	[TFMCustom5] [tinyint] NULL,
	[TechAlert] [varchar](max) NOT NULL,
	[EstDate] [datetime] NULL,
	[DueDate] [datetime] NULL,
	[Tech] [varchar](100) NULL,
	[TechOrRoute] [int] NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
 CONSTRAINT [PK_Job] PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Job_Status]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Job_Status](
	[ID] [int] IDENTITY(1,1) NOT FOR REPLICATION NOT NULL,
	[Status] [varchar](255) NOT NULL,
PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[JobDed]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[JobDed](
	[ID] [int] NOT NULL,
	[Ded] [int] NULL,
	[Job] [int] NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[JobI]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[JobI](
	[ID] [int] IDENTITY(1,1) NOT NULL,
	[Job] [int] NULL,
	[Phase] [smallint] NULL,
	[fDate] [datetime] NULL,
	[Ref] [varchar](50) NULL,
	[fDesc] [varchar](50) NULL,
	[Amount] [numeric](30, 2) NULL,
	[TransID] [int] NULL,
	[Type] [smallint] NULL,
	[Labor] [smallint] NULL,
	[Billed] [int] NULL,
	[Invoice] [int] NULL,
	[UseTax] [bit] NULL,
	[APTicket] [int] NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
 CONSTRAINT [PK_JobI] PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[JobPComp]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[JobPComp](
	[ID] [int] IDENTITY(1,1) NOT FOR REPLICATION NOT NULL,
	[Job] [int] NULL,
	[fdate] [datetime] NULL,
	[Batch] [int] NULL,
	[PTDRev] [numeric](30, 2) NULL,
	[PComp] [numeric](30, 2) NULL,
	[JTDCost] [numeric](30, 2) NULL,
	[BCost] [numeric](30, 2) NULL,
	[BRev] [numeric](30, 2) NULL,
	[NCS] [numeric](30, 2) NULL,
	[PrevJTDRev] [numeric](30, 2) NULL,
	[GL] [int] NULL,
PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[JobT]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[JobT](
	[ID] [int] NOT NULL,
	[fDesc] [varchar](50) NULL,
	[Type] [smallint] NULL,
	[NRev] [smallint] NULL,
	[NDed] [smallint] NULL,
	[Count] [int] NULL,
	[Remarks] [varchar](8000) NULL,
	[InvExp] [int] NULL,
	[InvServ] [int] NULL,
	[Wage] [int] NULL,
	[CType] [varchar](15) NULL,
	[Status] [tinyint] NULL,
	[Charge] [tinyint] NULL,
	[Post] [tinyint] NULL,
	[fInt] [tinyint] NULL,
	[GLInt] [int] NULL,
	[JobClose] [tinyint] NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
 CONSTRAINT [PK_JobT] PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[JobTItem]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[JobTItem](
	[ID] [int] NOT NULL,
	[JobT] [int] NULL,
	[Job] [int] NULL,
	[Type] [smallint] NULL,
	[fDesc] [varchar](255) NULL,
	[Code] [varchar](10) NULL,
	[Actual] [numeric](30, 2) NULL,
	[Budget] [numeric](30, 2) NULL,
	[Line] [smallint] NULL,
	[Percent] [numeric](30, 2) NULL,
	[Comm] [numeric](30, 2) NOT NULL,
	[Stored] [numeric](30, 2) NULL,
	[Modifier] [numeric](30, 2) NOT NULL,
	[ETC] [numeric](30, 2) NOT NULL,
	[ETCMod] [numeric](30, 2) NOT NULL,
	[THours] [numeric](30, 2) NULL,
	[FC] [int] NULL,
	[Labor] [numeric](30, 2) NULL,
	[BHours] [numeric](30, 2) NULL,
	[GL] [int] NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
 CONSTRAINT [PK_JobTItem] PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[JobType]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[JobType](
	[ID] [smallint] IDENTITY(0,1) NOT NULL,
	[Type] [varchar](15) NULL,
	[Count] [smallint] NULL,
	[Color] [smallint] NULL,
	[Remarks] [varchar](255) NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
 CONSTRAINT [PK_JobType] PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[JobWageC]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[JobWageC](
	[ID] [int] NOT NULL,
	[WageC] [int] NULL,
	[Job] [int] NULL,
	[Reg] [numeric](30, 2) NULL,
	[OT] [numeric](30, 2) NULL,
	[DT] [numeric](30, 2) NULL,
	[TT] [numeric](30, 2) NULL,
	[NT] [numeric](30, 2) NULL,
	[GL] [int] NULL,
	[Fringe1] [numeric](30, 2) NULL,
	[Fringe2] [numeric](30, 2) NULL,
	[Fringe3] [numeric](30, 2) NULL,
	[Fringe4] [numeric](30, 2) NULL,
	[PF1] [smallint] NULL,
	[PF2] [smallint] NULL,
	[PF3] [smallint] NULL,
	[PF4] [smallint] NULL,
	[FringeGL] [int] NULL,
	[CReg] [numeric](30, 2) NULL,
	[COT] [numeric](30, 2) NULL,
	[CDT] [numeric](30, 2) NULL,
	[CTT] [numeric](30, 2) NULL,
	[CNT] [numeric](30, 2) NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[JStatus]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[JStatus](
	[Status] [varchar](50) NULL,
	[ID] [int] NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Labels]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Labels](
	[ID] [smallint] IDENTITY(1,1) NOT FOR REPLICATION NOT NULL,
	[Screen] [varchar](25) NULL,
	[Name] [varchar](25) NULL,
	[Label] [varchar](50) NULL,
PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[LDStatus]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[LDStatus](
	[Status] [varchar](10) NOT NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[LDType]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[LDType](
	[Type] [varchar](15) NOT NULL,
	[Count] [smallint] NULL,
	[Remarks] [text] NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Lead]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Lead](
	[ID] [int] NOT NULL,
	[fDesc] [varchar](75) NULL,
	[RolType] [smallint] NULL,
	[Rol] [int] NULL,
	[Type] [varchar](15) NULL,
	[Address] [varchar](50) NULL,
	[City] [varchar](50) NULL,
	[State] [varchar](2) NULL,
	[Zip] [varchar](10) NULL,
	[Owner] [int] NULL,
	[Status] [smallint] NULL,
	[Probability] [smallint] NULL,
	[Level] [smallint] NULL,
	[Revenue] [numeric](30, 2) NULL,
	[Cost] [numeric](30, 2) NULL,
	[Labor] [numeric](30, 2) NULL,
	[Profit] [numeric](30, 2) NULL,
	[Ratio] [numeric](30, 2) NULL,
	[Remarks] [varchar](8000) NULL,
	[Latt] [float] NULL,
	[fLong] [float] NULL,
	[GeoLock] [smallint] NOT NULL,
	[Country] [varchar](50) NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[ListConfig]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[ListConfig](
	[idListConfig] [int] IDENTITY(1,1) NOT FOR REPLICATION NOT NULL,
	[ListName] [nvarchar](20) NOT NULL,
	[ItemName] [nvarchar](50) NOT NULL,
	[ItemValue] [int] NULL,
	[ItemCode] [nvarchar](5) NULL,
	[ItemDesc] [nvarchar](255) NULL,
	[DestTable] [nvarchar](50) NULL,
	[DestField] [nvarchar](50) NULL,
	[IsDefault] [bit] NOT NULL,
	[StatusOrder] [int] NOT NULL,
	[ShowAlert] [bit] NOT NULL,
	[AlertDays] [smallint] NULL,
	[AlertColor] [int] NULL,
 CONSTRAINT [pk_ListConfig_idListConfig] PRIMARY KEY CLUSTERED 
(
	[idListConfig] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[LoadTest]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[LoadTest](
	[ID] [int] NOT NULL,
	[Name] [varchar](50) NULL,
	[Authority] [varchar](25) NULL,
	[Frequency] [smallint] NULL,
	[Remarks] [varchar](max) NULL,
	[Count] [smallint] NULL,
	[Level] [smallint] NULL,
	[Cat] [varchar](25) NULL,
	[fDesc] [varchar](1000) NULL,
	[NextDateCalcMode] [tinyint] NOT NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
 CONSTRAINT [PK_LoadTest] PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[LoadTestItem]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[LoadTestItem](
	[LID] [int] NOT NULL,
	[ID] [int] NULL,
	[Loc] [int] NULL,
	[Elev] [int] NULL,
	[Last] [datetime] NULL,
	[Next] [datetime] NULL,
	[Status] [smallint] NULL,
	[Ticket] [int] NULL,
	[Remarks] [varchar](max) NULL,
	[LastDue] [smalldatetime] NULL,
	[idRolCustomContact] [int] NOT NULL,
	[Custom1] [varchar](50) NULL,
	[Custom2] [varchar](50) NULL,
	[Custom3] [varchar](50) NULL,
	[Custom4] [varchar](50) NULL,
	[Custom5] [varchar](50) NULL,
	[Custom6] [varchar](50) NULL,
	[Custom7] [varchar](50) NULL,
	[Custom8] [varchar](50) NULL,
	[Custom9] [varchar](50) NULL,
	[Custom10] [varchar](50) NULL,
	[Custom11] [varchar](50) NULL,
	[Custom12] [varchar](50) NULL,
	[Custom13] [varchar](50) NULL,
	[Custom14] [varchar](50) NULL,
	[Custom15] [varchar](50) NULL,
	[Extra] [varchar](max) NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
 CONSTRAINT [PK_LoadTestItem] PRIMARY KEY CLUSTERED 
(
	[LID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Loc]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Loc](
	[Loc] [int] NOT NULL,
	[Owner] [int] NULL,
	[ID] [varchar](15) NULL,
	[Tag] [varchar](50) NULL,
	[Address] [varchar](255) NULL,
	[City] [varchar](50) NULL,
	[State] [varchar](2) NULL,
	[Zip] [varchar](10) NULL,
	[Elevs] [smallint] NULL,
	[Status] [smallint] NULL,
	[Balance] [numeric](30, 2) NULL,
	[Rol] [int] NULL,
	[fLong] [float] NULL,
	[Latt] [float] NULL,
	[GeoLock] [smallint] NOT NULL,
	[Route] [int] NOT NULL,
	[Zone] [int] NOT NULL,
	[PriceL] [smallint] NULL,
	[PaidNumb] [int] NULL,
	[PaidDays] [int] NULL,
	[WriteOff] [numeric](30, 2) NULL,
	[STax] [varchar](25) NOT NULL,
	[Maint] [smallint] NOT NULL,
	[Careof] [varchar](50) NULL,
	[Terr] [int] NOT NULL,
	[Custom1] [varchar](50) NULL,
	[Custom2] [varchar](50) NULL,
	[Custom3] [varchar](50) NULL,
	[Custom4] [varchar](50) NULL,
	[Custom5] [varchar](50) NULL,
	[Custom6] [varchar](50) NULL,
	[Custom7] [varchar](50) NULL,
	[Custom8] [varchar](50) NULL,
	[Custom9] [varchar](50) NULL,
	[Custom10] [varchar](50) NULL,
	[InUse] [smallint] NOT NULL,
	[Job] [int] NULL,
	[Remarks] [varchar](max) NULL,
	[WK] [smallint] NULL,
	[Type] [varchar](15) NULL,
	[Billing] [smallint] NULL,
	[Markup1] [real] NULL,
	[Markup2] [real] NULL,
	[Markup3] [real] NULL,
	[Markup4] [real] NULL,
	[Markup5] [real] NULL,
	[STax2] [varchar](25) NULL,
	[Credit] [tinyint] NULL,
	[CreditReason] [varchar](500) NULL,
	[Terms] [tinyint] NULL,
	[UTax] [varchar](25) NULL,
	[Custom11] [varchar](50) NULL,
	[Custom12] [varchar](50) NULL,
	[Custom13] [varchar](50) NULL,
	[Custom14] [varchar](50) NULL,
	[Custom15] [varchar](50) NULL,
	[DispAlert] [tinyint] NULL,
	[Country] [varchar](50) NULL,
	[ColRemarks] [varchar](8000) NULL,
	[MerchantServicesId] [int] NULL,
	[idCreditCardDefault] [int] NULL,
	[idRolCustomContact] [int] NOT NULL,
	[DispAlertType] [varchar](15) NULL,
	[Email] [tinyint] NOT NULL,
	[PrintInvoice] [bit] NOT NULL,
	[SalesRemarks] [varchar](8000) NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
	[EmailTicket] [bit] NULL,
	[PrintTicket] [bit] NULL,
 CONSTRAINT [PK_Loc] PRIMARY KEY CLUSTERED 
(
	[Loc] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[LocEsc]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[LocEsc](
	[Loc] [int] NULL,
	[fDate] [datetime] NULL,
	[Old] [numeric](30, 2) NULL,
	[fNew] [numeric](30, 2) NULL,
	[Job] [int] NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[LocType]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[LocType](
	[ID] [int] IDENTITY(1,1) NOT NULL,
	[Type] [varchar](15) NOT NULL,
	[Count] [int] NULL,
	[Remarks] [varchar](max) NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
 CONSTRAINT [PK_LocType] PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Log]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Log](
	[ID] [int] IDENTITY(1,1) NOT NULL,
	[fUser] [varchar](50) NULL,
	[Type] [smallint] NULL,
	[Remarks] [varchar](8000) NULL,
	[CreatedStamp] [datetime] NOT NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
	[fDate]  AS ([dbo].[DateOnly]([CreatedStamp])),
	[fTime]  AS ([dbo].[TimeOnly]([CreatedStamp])),
 CONSTRAINT [PK_Log] PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Log2]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Log2](
	[fUser] [varchar](50) NULL,
	[Screen] [varchar](50) NULL,
	[Ref] [int] NULL,
	[Field] [varchar](75) NULL,
	[OldVal] [varchar](1000) NULL,
	[NewVal] [varchar](1000) NULL,
	[CreatedStamp] [datetime] NOT NULL,
	[fDate]  AS (dateadd(day,(0),datediff(day,(0),[CreatedStamp]))) PERSISTED,
	[fTime]  AS (dateadd(day, -datediff(day,(0),[CreatedStamp]),[CreatedStamp])) PERSISTED
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[LType]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[LType](
	[ID] [int] IDENTITY(1,1) NOT NULL,
	[Type] [varchar](15) NOT NULL,
	[fDesc] [varchar](50) NULL,
	[MatCharge] [smallint] NOT NULL,
	[Reg] [int] NULL,
	[OT] [int] NULL,
	[DT] [int] NULL,
	[WReg] [int] NULL,
	[WOT] [int] NULL,
	[WDT] [int] NULL,
	[WCharge] [numeric](30, 2) NULL,
	[HReg] [int] NULL,
	[HOT] [int] NULL,
	[HDT] [int] NULL,
	[HCharge] [numeric](30, 2) NULL,
	[Count] [int] NULL,
	[Remarks] [varchar](8000) NULL,
	[Serv] [int] NULL,
	[LTest] [smallint] NULL,
	[NT] [int] NULL,
	[Travel] [smallint] NULL,
	[fOver] [smallint] NULL,
	[WNT] [int] NULL,
	[HNT] [int] NULL,
	[NonContract] [smallint] NULL,
	[Free] [smallint] NOT NULL,
	[FGL] [int] NULL,
	[EN] [int] NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
 CONSTRAINT [PK_LType] PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[MC]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[MC](
	[ID] [int] NOT NULL,
	[fDate] [datetime] NULL,
	[Ref] [int] NULL,
	[fDesc] [varchar](50) NULL,
	[Amount] [numeric](30, 2) NULL,
	[Bank] [int] NULL,
	[Type] [smallint] NULL,
	[Status] [smallint] NULL,
	[TransID] [int] NULL,
	[Batch] [int] NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[MCycle]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[MCycle](
	[Field] [varchar](15) NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[OpenAP]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[OpenAP](
	[Vendor] [int] NULL,
	[fDate] [datetime] NULL,
	[Due] [datetime] NULL,
	[Type] [smallint] NULL,
	[fDesc] [varchar](255) NULL,
	[Original] [numeric](30, 2) NULL,
	[Balance] [numeric](30, 2) NULL,
	[Selected] [numeric](30, 2) NOT NULL,
	[Disc] [numeric](30, 2) NOT NULL,
	[PJID] [int] NULL,
	[TRID] [int] NULL,
	[Ref] [varchar](50) NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[OpenAR]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[OpenAR](
	[Loc] [int] NULL,
	[fDate] [datetime] NULL,
	[Due] [datetime] NULL,
	[Type] [smallint] NULL,
	[Ref] [int] NULL,
	[fDesc] [varchar](8000) NULL,
	[Original] [numeric](30, 2) NULL,
	[Balance] [numeric](30, 2) NULL,
	[Selected] [numeric](30, 2) NULL,
	[TransID] [int] NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[OracleDropDown]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[OracleDropDown](
	[ID] [int] IDENTITY(1,1) NOT FOR REPLICATION NOT NULL,
	[Type] [varchar](25) NULL,
	[Name] [varchar](25) NULL,
	[EN] [int] NULL,
PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[OType]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[OType](
	[ID] [int] IDENTITY(1,1) NOT NULL,
	[Type] [varchar](15) NOT NULL,
	[Count] [int] NULL,
	[Remarks] [varchar](max) NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
 CONSTRAINT [PK_OType] PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Owner]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Owner](
	[ID] [int] NOT NULL,
	[Status] [smallint] NULL,
	[Locs] [smallint] NULL,
	[Elevs] [smallint] NULL,
	[Balance] [numeric](30, 2) NULL,
	[Type] [varchar](15) NULL,
	[Billing] [smallint] NULL,
	[Central] [int] NULL,
	[Rol] [int] NULL,
	[Internet] [smallint] NULL,
	[TicketO] [smallint] NULL,
	[TicketD] [smallint] NULL,
	[Ledger] [smallint] NULL,
	[Request] [smallint] NULL,
	[Password] [varchar](10) NULL,
	[fLogin] [varchar](15) NULL,
	[Statement] [smallint] NULL,
	[Custom1] [varchar](50) NULL,
	[Custom2] [varchar](50) NULL,
	[NeedsFullSync] [bit] NOT NULL,
	[MerchantServicesId] [int] NULL,
	[idCreditCardDefault] [int] NULL,
	[Approve] [smallint] NULL,
	[InvoiceO] [smallint] NULL,
	[Quote] [smallint] NULL,
	[QuoteX] [smallint] NULL,
	[Dispatch] [smallint] NULL,
	[Service] [smallint] NULL,
	[Pay] [smallint] NULL,
	[Safety] [smallint] NULL,
	[TicketEmail] [varchar](1000) NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
	[QuoteEmail] [nvarchar](255) NULL,
 CONSTRAINT [PK_Owner] PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Paid]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Paid](
	[PITR] [int] NULL,
	[fDate] [datetime] NULL,
	[Type] [smallint] NULL,
	[Line] [smallint] NULL,
	[fDesc] [varchar](255) NULL,
	[Original] [numeric](30, 2) NULL,
	[Balance] [numeric](30, 2) NULL,
	[Disc] [numeric](30, 2) NULL,
	[Paid] [numeric](30, 2) NULL,
	[TRID] [int] NULL,
	[Ref] [varchar](50) NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[PDATicket]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[PDATicket](
	[pdaticketid] [int] IDENTITY(1,1) NOT NULL,
	[pdarecordid] [char](10) NULL,
	[workorderno] [char](14) NULL,
	[branchno] [char](10) NULL,
	[routeno] [char](10) NULL,
	[employeeno] [char](10) NULL,
	[siteid] [char](10) NULL,
	[problem] [varchar](255) NULL,
	[workdesc] [varchar](255) NULL,
	[workdate] [varchar](25) NULL,
	[starttime] [char](11) NULL,
	[endtime] [char](11) NULL,
	[hoursst] [char](10) NULL,
	[hoursstt] [char](10) NULL,
	[hours15] [char](10) NULL,
	[hours15t] [char](10) NULL,
	[hours17] [char](10) NULL,
	[hours17t] [char](10) NULL,
	[hours20] [char](10) NULL,
	[hours20t] [char](10) NULL,
	[unittype] [char](1) NULL,
	[zone] [varchar](50) NULL,
	[subsist] [char](8) NULL,
	[cartage] [char](8) NULL,
	[miles] [char](8) NULL,
	[material] [char](8) NULL,
	[perdiem] [char](8) NULL,
	[toll] [char](8) NULL,
	[misc] [char](8) NULL,
	[scantime] [varchar](20) NULL,
	[pmdatatype] [char](4) NULL,
	[pmdata] [varchar](255) NULL,
	[locname] [varchar](50) NULL,
	[address] [varchar](50) NULL,
	[city] [varchar](50) NULL,
	[state] [char](2) NULL,
	[zip] [char](10) NULL,
	[accountno] [char](15) NULL,
	[faxnumber] [varchar](50) NULL,
	[errors] [varchar](50) NULL,
	[pmdatadone] [varchar](100) NULL,
	[checksum] [varchar](50) NULL,
	[workcode] [char](10) NULL,
	[serialno] [varchar](50) NULL,
	[option1] [char](1) NULL,
	[option2] [char](1) NULL,
	[option3] [char](1) NULL,
	[option4] [char](1) NULL,
	[option5] [char](1) NULL,
	[option6] [char](1) NULL,
	[option7] [char](1) NULL,
	[option8] [char](1) NULL,
	[option9] [char](1) NULL,
	[option10] [char](1) NULL,
	[option11] [char](1) NULL,
	[option12] [char](1) NULL,
	[option13] [char](1) NULL,
	[complete] [char](1) NULL,
	[workperfrm] [char](10) NULL,
	[enroute] [char](11) NULL,
	[onsite] [char](11) NULL,
	[timecomp] [char](11) NULL,
	[startmile] [char](9) NULL,
	[endmile] [char](9) NULL,
	[transfered] [bit] NOT NULL,
	[auditdate] [datetime] NOT NULL,
	[audituser] [varchar](50) NOT NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[PDATicketPartOrder]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[PDATicketPartOrder](
	[pdaticketpartorderid] [int] IDENTITY(1,1) NOT NULL,
	[pdaticketid] [int] NOT NULL,
	[partno] [varchar](25) NULL,
	[quantity] [char](5) NULL,
	[employeeno] [char](10) NULL,
	[description] [varchar](50) NULL,
	[warehouse] [char](5) NULL,
	[auditdate] [datetime] NOT NULL,
	[audituser] [varchar](50) NOT NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[PDATicketPartUsed]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[PDATicketPartUsed](
	[pdaticketpartusedid] [int] IDENTITY(1,1) NOT NULL,
	[pdaticketid] [int] NOT NULL,
	[partno] [varchar](25) NULL,
	[description] [varchar](50) NULL,
	[quantity] [char](5) NULL,
	[unitprice] [char](8) NULL,
	[extprice] [char](10) NULL,
	[discount] [char](3) NULL,
	[discamount] [char](10) NULL,
	[discprice] [char](10) NULL,
	[exchange] [char](1) NULL,
	[warehouse] [char](5) NULL,
	[auditdate] [datetime] NOT NULL,
	[audituser] [varchar](50) NOT NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[PDATicketSignature]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[PDATicketSignature](
	[ID] [int] IDENTITY(1,1) NOT NULL,
	[PDATicketID] [int] NOT NULL,
	[SignatureType] [char](1) NOT NULL,
	[Signature] [image] NULL,
	[SignatureText] [varchar](300) NOT NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Phone]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Phone](
	[ID] [int] NOT NULL,
	[Rol] [int] NULL,
	[fDesc] [varchar](50) NULL,
	[Phone] [varchar](22) NULL,
	[Fax] [varchar](22) NULL,
	[Title] [varchar](50) NULL,
	[Cell] [varchar](22) NULL,
	[Email] [varchar](50) NULL,
	[EmailRecInvoice] [bit] NULL,
	[EmailRecTicket] [bit] NULL,
	[EmailRecPO] [bit] NULL,
	[EmailRecQuote] [bit] NULL,
	[Remarks] [varchar](max) NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
 CONSTRAINT [PK_Phone] PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Photo]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Photo](
	[ID] [int] IDENTITY(1,1) NOT FOR REPLICATION NOT NULL,
	[Screen] [varchar](20) NULL,
	[ScreenID] [int] NULL,
	[Line] [smallint] NULL,
	[Photo] [image] NULL,
	[PhotoPath] [varchar](255) NULL,
PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[PJ]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[PJ](
	[ID] [int] NOT NULL,
	[fDate] [datetime] NULL,
	[Ref] [varchar](50) NULL,
	[fDesc] [varchar](8000) NULL,
	[Amount] [numeric](30, 2) NULL,
	[Vendor] [int] NULL,
	[Status] [smallint] NULL,
	[Batch] [int] NULL,
	[Terms] [smallint] NULL,
	[PO] [int] NULL,
	[TRID] [int] NULL,
	[Spec] [smallint] NULL,
	[IDate] [datetime] NOT NULL,
	[UseTax] [numeric](30, 2) NULL,
	[Disc] [numeric](30, 4) NULL,
	[Custom1] [varchar](50) NULL,
	[Custom2] [varchar](50) NULL,
	[ReqBy] [int] NULL,
	[VoidR] [varchar](75) NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[PJItem]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[PJItem](
	[TRID] [int] NULL,
	[Stax] [varchar](25) NULL,
	[Amount] [numeric](30, 2) NULL,
	[UseTax] [numeric](30, 4) NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[PJRecur]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[PJRecur](
	[ID] [int] NOT NULL,
	[fDate] [datetime] NULL,
	[Ref] [varchar](50) NULL,
	[fDesc] [varchar](8000) NULL,
	[Amount] [numeric](30, 2) NULL,
	[Vendor] [int] NULL,
	[IDate] [datetime] NULL,
	[Status] [smallint] NULL,
	[Frequency] [smallint] NULL,
	[Spec] [smallint] NULL,
	[DDate] [datetime] NULL,
PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[PJRecurI]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[PJRecurI](
	[ID] [int] NULL,
	[Line] [int] NULL,
	[Type] [smallint] NULL,
	[Acct] [int] NULL,
	[fDesc] [varchar](255) NULL,
	[Amount] [numeric](30, 2) NULL,
	[Job] [int] NULL,
	[Phase] [int] NULL,
	[Quan] [numeric](30, 2) NULL,
	[Price] [numeric](30, 4) NULL,
	[STax] [varchar](25) NULL,
	[UTax] [numeric](30, 2) NULL,
	[UseTax] [numeric](30, 2) NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[PMCheck_Labels]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[PMCheck_Labels](
	[vCat1Desc] [char](12) NULL,
	[vCat2Desc] [char](12) NULL,
	[vCat3Desc] [char](12) NULL,
	[vCat4Desc] [char](12) NULL,
	[vCat5Desc] [char](12) NULL,
	[vCat6Desc] [char](12) NULL,
	[vCat7Desc] [char](12) NULL,
	[vCat8Desc] [char](12) NULL,
	[vCat1Label0] [char](29) NULL,
	[vCat1Label1] [char](29) NULL,
	[vCat1Label2] [char](29) NULL,
	[vCat1Label3] [char](29) NULL,
	[vCat1Label4] [char](29) NULL,
	[vCat1Label5] [char](29) NULL,
	[vCat1Label6] [char](29) NULL,
	[vCat1Label7] [char](29) NULL,
	[vCat1Label8] [char](29) NULL,
	[vCat1Label9] [char](29) NULL,
	[vCat1Label10] [char](29) NULL,
	[vCat1Label11] [char](29) NULL,
	[vCat1Label12] [char](29) NULL,
	[vCat1Label13] [char](29) NULL,
	[vCat1Label14] [char](29) NULL,
	[vCat1Label15] [char](29) NULL,
	[vCat1Label16] [char](29) NULL,
	[vCat1Label17] [char](29) NULL,
	[vCat1Label18] [char](29) NULL,
	[vCat1Label19] [char](29) NULL,
	[vCat1Label20] [char](29) NULL,
	[vCat1Label21] [char](29) NULL,
	[vCat1Label22] [char](29) NULL,
	[vCat1Label23] [char](29) NULL,
	[vCat2Label0] [char](29) NULL,
	[vCat2Label1] [char](29) NULL,
	[vCat2Label2] [char](29) NULL,
	[vCat2Label3] [char](29) NULL,
	[vCat2Label4] [char](29) NULL,
	[vCat2Label5] [char](29) NULL,
	[vCat2Label6] [char](29) NULL,
	[vCat2Label7] [char](29) NULL,
	[vCat2Label8] [char](29) NULL,
	[vCat2Label9] [char](29) NULL,
	[vCat2Label10] [char](29) NULL,
	[vCat2Label11] [char](29) NULL,
	[vCat2Label12] [char](29) NULL,
	[vCat2Label13] [char](29) NULL,
	[vCat2Label14] [char](29) NULL,
	[vCat2Label15] [char](29) NULL,
	[vCat2Label16] [char](29) NULL,
	[vCat2Label17] [char](29) NULL,
	[vCat2Label18] [char](29) NULL,
	[vCat2Label19] [char](29) NULL,
	[vCat2Label20] [char](29) NULL,
	[vCat2Label21] [char](29) NULL,
	[vCat2Label22] [char](29) NULL,
	[vCat2Label23] [char](29) NULL,
	[vCat3Label0] [char](29) NULL,
	[vCat3Label1] [char](29) NULL,
	[vCat3Label2] [char](29) NULL,
	[vCat3Label3] [char](29) NULL,
	[vCat3Label4] [char](29) NULL,
	[vCat3Label5] [char](29) NULL,
	[vCat3Label6] [char](29) NULL,
	[vCat3Label7] [char](29) NULL,
	[vCat3Label8] [char](29) NULL,
	[vCat3Label9] [char](29) NULL,
	[vCat3Label10] [char](29) NULL,
	[vCat3Label11] [char](29) NULL,
	[vCat3Label12] [char](29) NULL,
	[vCat3Label13] [char](29) NULL,
	[vCat3Label14] [char](29) NULL,
	[vCat3Label15] [char](29) NULL,
	[vCat3Label16] [char](29) NULL,
	[vCat3Label17] [char](29) NULL,
	[vCat3Label18] [char](29) NULL,
	[vCat3Label19] [char](29) NULL,
	[vCat3Label20] [char](29) NULL,
	[vCat3Label21] [char](29) NULL,
	[vCat3Label22] [char](29) NULL,
	[vCat3Label23] [char](29) NULL,
	[vCat4Label0] [char](29) NULL,
	[vCat4Label1] [char](29) NULL,
	[vCat4Label2] [char](29) NULL,
	[vCat4Label3] [char](29) NULL,
	[vCat4Label4] [char](29) NULL,
	[vCat4Label5] [char](29) NULL,
	[vCat4Label6] [char](29) NULL,
	[vCat4Label7] [char](29) NULL,
	[vCat4Label8] [char](29) NULL,
	[vCat4Label9] [char](29) NULL,
	[vCat4Label10] [char](29) NULL,
	[vCat4Label11] [char](29) NULL,
	[vCat4Label12] [char](29) NULL,
	[vCat4Label13] [char](29) NULL,
	[vCat4Label14] [char](29) NULL,
	[vCat4Label15] [char](29) NULL,
	[vCat4Label16] [char](29) NULL,
	[vCat4Label17] [char](29) NULL,
	[vCat4Label18] [char](29) NULL,
	[vCat4Label19] [char](29) NULL,
	[vCat4Label20] [char](29) NULL,
	[vCat4Label21] [char](29) NULL,
	[vCat4Label22] [char](29) NULL,
	[vCat4Label23] [char](29) NULL,
	[vCat5Label0] [char](29) NULL,
	[vCat5Label1] [char](29) NULL,
	[vCat5Label2] [char](29) NULL,
	[vCat5Label3] [char](29) NULL,
	[vCat5Label4] [char](29) NULL,
	[vCat5Label5] [char](29) NULL,
	[vCat5Label6] [char](29) NULL,
	[vCat5Label7] [char](29) NULL,
	[vCat5Label8] [char](29) NULL,
	[vCat5Label9] [char](29) NULL,
	[vCat5Label10] [char](29) NULL,
	[vCat5Label11] [char](29) NULL,
	[vCat5Label12] [char](29) NULL,
	[vCat5Label13] [char](29) NULL,
	[vCat5Label14] [char](29) NULL,
	[vCat5Label15] [char](29) NULL,
	[vCat5Label16] [char](29) NULL,
	[vCat5Label17] [char](29) NULL,
	[vCat5Label18] [char](29) NULL,
	[vCat5Label19] [char](29) NULL,
	[vCat5Label20] [char](29) NULL,
	[vCat5Label21] [char](29) NULL,
	[vCat5Label22] [char](29) NULL,
	[vCat5Label23] [char](29) NULL,
	[vCat6Label0] [char](29) NULL,
	[vCat6Label1] [char](29) NULL,
	[vCat6Label2] [char](29) NULL,
	[vCat6Label3] [char](29) NULL,
	[vCat6Label4] [char](29) NULL,
	[vCat6Label5] [char](29) NULL,
	[vCat6Label6] [char](29) NULL,
	[vCat6Label7] [char](29) NULL,
	[vCat6Label8] [char](29) NULL,
	[vCat6Label9] [char](29) NULL,
	[vCat6Label10] [char](29) NULL,
	[vCat6Label11] [char](29) NULL,
	[vCat6Label12] [char](29) NULL,
	[vCat6Label13] [char](29) NULL,
	[vCat6Label14] [char](29) NULL,
	[vCat6Label15] [char](29) NULL,
	[vCat6Label16] [char](29) NULL,
	[vCat6Label17] [char](29) NULL,
	[vCat6Label18] [char](29) NULL,
	[vCat6Label19] [char](29) NULL,
	[vCat6Label20] [char](29) NULL,
	[vCat6Label21] [char](29) NULL,
	[vCat6Label22] [char](29) NULL,
	[vCat6Label23] [char](29) NULL,
	[vCat7Label0] [char](29) NULL,
	[vCat7Label1] [char](29) NULL,
	[vCat7Label2] [char](29) NULL,
	[vCat7Label3] [char](29) NULL,
	[vCat7Label4] [char](29) NULL,
	[vCat7Label5] [char](29) NULL,
	[vCat7Label6] [char](29) NULL,
	[vCat7Label7] [char](29) NULL,
	[vCat7Label8] [char](29) NULL,
	[vCat7Label9] [char](29) NULL,
	[vCat7Label10] [char](29) NULL,
	[vCat7Label11] [char](29) NULL,
	[vCat7Label12] [char](29) NULL,
	[vCat7Label13] [char](29) NULL,
	[vCat7Label14] [char](29) NULL,
	[vCat7Label15] [char](29) NULL,
	[vCat7Label16] [char](29) NULL,
	[vCat7Label17] [char](29) NULL,
	[vCat7Label18] [char](29) NULL,
	[vCat7Label19] [char](29) NULL,
	[vCat7Label20] [char](29) NULL,
	[vCat7Label21] [char](29) NULL,
	[vCat7Label22] [char](29) NULL,
	[vCat7Label23] [char](29) NULL,
	[vCat8Label0] [char](29) NULL,
	[vCat8Label1] [char](29) NULL,
	[vCat8Label2] [char](29) NULL,
	[vCat8Label3] [char](29) NULL,
	[vCat8Label4] [char](29) NULL,
	[vCat8Label5] [char](29) NULL,
	[vCat8Label6] [char](29) NULL,
	[vCat8Label7] [char](29) NULL,
	[vCat8Label8] [char](29) NULL,
	[vCat8Label9] [char](29) NULL,
	[vCat8Label10] [char](29) NULL,
	[vCat8Label11] [char](29) NULL,
	[vCat8Label12] [char](29) NULL,
	[vCat8Label13] [char](29) NULL,
	[vCat8Label14] [char](29) NULL,
	[vCat8Label15] [char](29) NULL,
	[vCat8Label16] [char](29) NULL,
	[vCat8Label17] [char](29) NULL,
	[vCat8Label18] [char](29) NULL,
	[vCat8Label19] [char](29) NULL,
	[vCat8Label20] [char](29) NULL,
	[vCat8Label21] [char](29) NULL,
	[vCat8Label22] [char](29) NULL,
	[vCat8Label23] [char](29) NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[PMCheckLists]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[PMCheckLists](
	[intTicketID] [int] NULL,
	[intLocID] [int] NULL,
	[vTech] [char](50) NULL,
	[vUnitID] [char](12) NULL,
	[vCat1Desc] [char](12) NULL,
	[vCat2Desc] [char](12) NULL,
	[vCat3Desc] [char](12) NULL,
	[vCat4Desc] [char](12) NULL,
	[vCat5Desc] [char](12) NULL,
	[vCat6Desc] [char](12) NULL,
	[vCat7Desc] [char](12) NULL,
	[vCat8Desc] [char](12) NULL,
	[vCat1Label0] [char](29) NULL,
	[vCat1Label1] [char](29) NULL,
	[vCat1Label2] [char](29) NULL,
	[vCat1Label3] [char](29) NULL,
	[vCat1Label4] [char](29) NULL,
	[vCat1Label5] [char](29) NULL,
	[vCat1Label6] [char](29) NULL,
	[vCat1Label7] [char](29) NULL,
	[vCat1Label8] [char](29) NULL,
	[vCat1Label9] [char](29) NULL,
	[vCat1Label10] [char](29) NULL,
	[vCat1Label11] [char](29) NULL,
	[vCat1Label12] [char](29) NULL,
	[vCat1Label13] [char](29) NULL,
	[vCat1Label14] [char](29) NULL,
	[vCat1Label15] [char](29) NULL,
	[vCat1Label16] [char](29) NULL,
	[vCat1Label17] [char](29) NULL,
	[vCat1Label18] [char](29) NULL,
	[vCat1Label19] [char](29) NULL,
	[vCat1Label20] [char](29) NULL,
	[vCat1Label21] [char](29) NULL,
	[vCat1Label22] [char](29) NULL,
	[vCat1Label23] [char](29) NULL,
	[vCat2Label0] [char](29) NULL,
	[vCat2Label1] [char](29) NULL,
	[vCat2Label2] [char](29) NULL,
	[vCat2Label3] [char](29) NULL,
	[vCat2Label4] [char](29) NULL,
	[vCat2Label5] [char](29) NULL,
	[vCat2Label6] [char](29) NULL,
	[vCat2Label7] [char](29) NULL,
	[vCat2Label8] [char](29) NULL,
	[vCat2Label9] [char](29) NULL,
	[vCat2Label10] [char](29) NULL,
	[vCat2Label11] [char](29) NULL,
	[vCat2Label12] [char](29) NULL,
	[vCat2Label13] [char](29) NULL,
	[vCat2Label14] [char](29) NULL,
	[vCat2Label15] [char](29) NULL,
	[vCat2Label16] [char](29) NULL,
	[vCat2Label17] [char](29) NULL,
	[vCat2Label18] [char](29) NULL,
	[vCat2Label19] [char](29) NULL,
	[vCat2Label20] [char](29) NULL,
	[vCat2Label21] [char](29) NULL,
	[vCat2Label22] [char](29) NULL,
	[vCat2Label23] [char](29) NULL,
	[vCat3Label0] [char](29) NULL,
	[vCat3Label1] [char](29) NULL,
	[vCat3Label2] [char](29) NULL,
	[vCat3Label3] [char](29) NULL,
	[vCat3Label4] [char](29) NULL,
	[vCat3Label5] [char](29) NULL,
	[vCat3Label6] [char](29) NULL,
	[vCat3Label7] [char](29) NULL,
	[vCat3Label8] [char](29) NULL,
	[vCat3Label9] [char](29) NULL,
	[vCat3Label10] [char](29) NULL,
	[vCat3Label11] [char](29) NULL,
	[vCat3Label12] [char](29) NULL,
	[vCat3Label13] [char](29) NULL,
	[vCat3Label14] [char](29) NULL,
	[vCat3Label15] [char](29) NULL,
	[vCat3Label16] [char](29) NULL,
	[vCat3Label17] [char](29) NULL,
	[vCat3Label18] [char](29) NULL,
	[vCat3Label19] [char](29) NULL,
	[vCat3Label20] [char](29) NULL,
	[vCat3Label21] [char](29) NULL,
	[vCat3Label22] [char](29) NULL,
	[vCat3Label23] [char](29) NULL,
	[vCat4Label0] [char](29) NULL,
	[vCat4Label1] [char](29) NULL,
	[vCat4Label2] [char](29) NULL,
	[vCat4Label3] [char](29) NULL,
	[vCat4Label4] [char](29) NULL,
	[vCat4Label5] [char](29) NULL,
	[vCat4Label6] [char](29) NULL,
	[vCat4Label7] [char](29) NULL,
	[vCat4Label8] [char](29) NULL,
	[vCat4Label9] [char](29) NULL,
	[vCat4Label10] [char](29) NULL,
	[vCat4Label11] [char](29) NULL,
	[vCat4Label12] [char](29) NULL,
	[vCat4Label13] [char](29) NULL,
	[vCat4Label14] [char](29) NULL,
	[vCat4Label15] [char](29) NULL,
	[vCat4Label16] [char](29) NULL,
	[vCat4Label17] [char](29) NULL,
	[vCat4Label18] [char](29) NULL,
	[vCat4Label19] [char](29) NULL,
	[vCat4Label20] [char](29) NULL,
	[vCat4Label21] [char](29) NULL,
	[vCat4Label22] [char](29) NULL,
	[vCat4Label23] [char](29) NULL,
	[vCat5Label0] [char](29) NULL,
	[vCat5Label1] [char](29) NULL,
	[vCat5Label2] [char](29) NULL,
	[vCat5Label3] [char](29) NULL,
	[vCat5Label4] [char](29) NULL,
	[vCat5Label5] [char](29) NULL,
	[vCat5Label6] [char](29) NULL,
	[vCat5Label7] [char](29) NULL,
	[vCat5Label8] [char](29) NULL,
	[vCat5Label9] [char](29) NULL,
	[vCat5Label10] [char](29) NULL,
	[vCat5Label11] [char](29) NULL,
	[vCat5Label12] [char](29) NULL,
	[vCat5Label13] [char](29) NULL,
	[vCat5Label14] [char](29) NULL,
	[vCat5Label15] [char](29) NULL,
	[vCat5Label16] [char](29) NULL,
	[vCat5Label17] [char](29) NULL,
	[vCat5Label18] [char](29) NULL,
	[vCat5Label19] [char](29) NULL,
	[vCat5Label20] [char](29) NULL,
	[vCat5Label21] [char](29) NULL,
	[vCat5Label22] [char](29) NULL,
	[vCat5Label23] [char](29) NULL,
	[vCat6Label0] [char](29) NULL,
	[vCat6Label1] [char](29) NULL,
	[vCat6Label2] [char](29) NULL,
	[vCat6Label3] [char](29) NULL,
	[vCat6Label4] [char](29) NULL,
	[vCat6Label5] [char](29) NULL,
	[vCat6Label6] [char](29) NULL,
	[vCat6Label7] [char](29) NULL,
	[vCat6Label8] [char](29) NULL,
	[vCat6Label9] [char](29) NULL,
	[vCat6Label10] [char](29) NULL,
	[vCat6Label11] [char](29) NULL,
	[vCat6Label12] [char](29) NULL,
	[vCat6Label13] [char](29) NULL,
	[vCat6Label14] [char](29) NULL,
	[vCat6Label15] [char](29) NULL,
	[vCat6Label16] [char](29) NULL,
	[vCat6Label17] [char](29) NULL,
	[vCat6Label18] [char](29) NULL,
	[vCat6Label19] [char](29) NULL,
	[vCat6Label20] [char](29) NULL,
	[vCat6Label21] [char](29) NULL,
	[vCat6Label22] [char](29) NULL,
	[vCat6Label23] [char](29) NULL,
	[vCat7Label0] [char](29) NULL,
	[vCat7Label1] [char](29) NULL,
	[vCat7Label2] [char](29) NULL,
	[vCat7Label3] [char](29) NULL,
	[vCat7Label4] [char](29) NULL,
	[vCat7Label5] [char](29) NULL,
	[vCat7Label6] [char](29) NULL,
	[vCat7Label7] [char](29) NULL,
	[vCat7Label8] [char](29) NULL,
	[vCat7Label9] [char](29) NULL,
	[vCat7Label10] [char](29) NULL,
	[vCat7Label11] [char](29) NULL,
	[vCat7Label12] [char](29) NULL,
	[vCat7Label13] [char](29) NULL,
	[vCat7Label14] [char](29) NULL,
	[vCat7Label15] [char](29) NULL,
	[vCat7Label16] [char](29) NULL,
	[vCat7Label17] [char](29) NULL,
	[vCat7Label18] [char](29) NULL,
	[vCat7Label19] [char](29) NULL,
	[vCat7Label20] [char](29) NULL,
	[vCat7Label21] [char](29) NULL,
	[vCat7Label22] [char](29) NULL,
	[vCat7Label23] [char](29) NULL,
	[vCat8Label0] [char](29) NULL,
	[vCat8Label1] [char](29) NULL,
	[vCat8Label2] [char](29) NULL,
	[vCat8Label3] [char](29) NULL,
	[vCat8Label4] [char](29) NULL,
	[vCat8Label5] [char](29) NULL,
	[vCat8Label6] [char](29) NULL,
	[vCat8Label7] [char](29) NULL,
	[vCat8Label8] [char](29) NULL,
	[vCat8Label9] [char](29) NULL,
	[vCat8Label10] [char](29) NULL,
	[vCat8Label11] [char](29) NULL,
	[vCat8Label12] [char](29) NULL,
	[vCat8Label13] [char](29) NULL,
	[vCat8Label14] [char](29) NULL,
	[vCat8Label15] [char](29) NULL,
	[vCat8Label16] [char](29) NULL,
	[vCat8Label17] [char](29) NULL,
	[vCat8Label18] [char](29) NULL,
	[vCat8Label19] [char](29) NULL,
	[vCat8Label20] [char](29) NULL,
	[vCat8Label21] [char](29) NULL,
	[vCat8Label22] [char](29) NULL,
	[vCat8Label23] [char](29) NULL,
	[vCat1Pass0] [tinyint] NULL,
	[vCat1Pass1] [tinyint] NULL,
	[vCat1Pass2] [tinyint] NULL,
	[vCat1Pass3] [tinyint] NULL,
	[vCat1Pass4] [tinyint] NULL,
	[vCat1Pass5] [tinyint] NULL,
	[vCat1Pass6] [tinyint] NULL,
	[vCat1Pass7] [tinyint] NULL,
	[vCat1Pass8] [tinyint] NULL,
	[vCat1Pass9] [tinyint] NULL,
	[vCat1Pass10] [tinyint] NULL,
	[vCat1Pass11] [tinyint] NULL,
	[vCat1Pass12] [tinyint] NULL,
	[vCat1Pass13] [tinyint] NULL,
	[vCat1Pass14] [tinyint] NULL,
	[vCat1Pass15] [tinyint] NULL,
	[vCat1Pass16] [tinyint] NULL,
	[vCat1Pass17] [tinyint] NULL,
	[vCat1Pass18] [tinyint] NULL,
	[vCat1Pass19] [tinyint] NULL,
	[vCat1Pass20] [tinyint] NULL,
	[vCat1Pass21] [tinyint] NULL,
	[vCat1Pass22] [tinyint] NULL,
	[vCat1Pass23] [tinyint] NULL,
	[vCat2Pass0] [tinyint] NULL,
	[vCat2Pass1] [tinyint] NULL,
	[vCat2Pass2] [tinyint] NULL,
	[vCat2Pass3] [tinyint] NULL,
	[vCat2Pass4] [tinyint] NULL,
	[vCat2Pass5] [tinyint] NULL,
	[vCat2Pass6] [tinyint] NULL,
	[vCat2Pass7] [tinyint] NULL,
	[vCat2Pass8] [tinyint] NULL,
	[vCat2Pass9] [tinyint] NULL,
	[vCat2Pass10] [tinyint] NULL,
	[vCat2Pass11] [tinyint] NULL,
	[vCat2Pass12] [tinyint] NULL,
	[vCat2Pass13] [tinyint] NULL,
	[vCat2Pass14] [tinyint] NULL,
	[vCat2Pass15] [tinyint] NULL,
	[vCat2Pass16] [tinyint] NULL,
	[vCat2Pass17] [tinyint] NULL,
	[vCat2Pass18] [tinyint] NULL,
	[vCat2Pass19] [tinyint] NULL,
	[vCat2Pass20] [tinyint] NULL,
	[vCat2Pass21] [tinyint] NULL,
	[vCat2Pass22] [tinyint] NULL,
	[vCat2Pass23] [tinyint] NULL,
	[vCat3Pass0] [tinyint] NULL,
	[vCat3Pass1] [tinyint] NULL,
	[vCat3Pass2] [tinyint] NULL,
	[vCat3Pass3] [tinyint] NULL,
	[vCat3Pass4] [tinyint] NULL,
	[vCat3Pass5] [tinyint] NULL,
	[vCat3Pass6] [tinyint] NULL,
	[vCat3Pass7] [tinyint] NULL,
	[vCat3Pass8] [tinyint] NULL,
	[vCat3Pass9] [tinyint] NULL,
	[vCat3Pass10] [tinyint] NULL,
	[vCat3Pass11] [tinyint] NULL,
	[vCat3Pass12] [tinyint] NULL,
	[vCat3Pass13] [tinyint] NULL,
	[vCat3Pass14] [tinyint] NULL,
	[vCat3Pass15] [tinyint] NULL,
	[vCat3Pass16] [tinyint] NULL,
	[vCat3Pass17] [tinyint] NULL,
	[vCat3Pass18] [tinyint] NULL,
	[vCat3Pass19] [tinyint] NULL,
	[vCat3Pass20] [tinyint] NULL,
	[vCat3Pass21] [tinyint] NULL,
	[vCat3Pass22] [tinyint] NULL,
	[vCat3Pass23] [tinyint] NULL,
	[vCat4Pass0] [tinyint] NULL,
	[vCat4Pass1] [tinyint] NULL,
	[vCat4Pass2] [tinyint] NULL,
	[vCat4Pass3] [tinyint] NULL,
	[vCat4Pass4] [tinyint] NULL,
	[vCat4Pass5] [tinyint] NULL,
	[vCat4Pass6] [tinyint] NULL,
	[vCat4Pass7] [tinyint] NULL,
	[vCat4Pass8] [tinyint] NULL,
	[vCat4Pass9] [tinyint] NULL,
	[vCat4Pass10] [tinyint] NULL,
	[vCat4Pass11] [tinyint] NULL,
	[vCat4Pass12] [tinyint] NULL,
	[vCat4Pass13] [tinyint] NULL,
	[vCat4Pass14] [tinyint] NULL,
	[vCat4Pass15] [tinyint] NULL,
	[vCat4Pass16] [tinyint] NULL,
	[vCat4Pass17] [tinyint] NULL,
	[vCat4Pass18] [tinyint] NULL,
	[vCat4Pass19] [tinyint] NULL,
	[vCat4Pass20] [tinyint] NULL,
	[vCat4Pass21] [tinyint] NULL,
	[vCat4Pass22] [tinyint] NULL,
	[vCat4Pass23] [tinyint] NULL,
	[vCat5Pass0] [tinyint] NULL,
	[vCat5Pass1] [tinyint] NULL,
	[vCat5Pass2] [tinyint] NULL,
	[vCat5Pass3] [tinyint] NULL,
	[vCat5Pass4] [tinyint] NULL,
	[vCat5Pass5] [tinyint] NULL,
	[vCat5Pass6] [tinyint] NULL,
	[vCat5Pass7] [tinyint] NULL,
	[vCat5Pass8] [tinyint] NULL,
	[vCat5Pass9] [tinyint] NULL,
	[vCat5Pass10] [tinyint] NULL,
	[vCat5Pass11] [tinyint] NULL,
	[vCat5Pass12] [tinyint] NULL,
	[vCat5Pass13] [tinyint] NULL,
	[vCat5Pass14] [tinyint] NULL,
	[vCat5Pass15] [tinyint] NULL,
	[vCat5Pass16] [tinyint] NULL,
	[vCat5Pass17] [tinyint] NULL,
	[vCat5Pass18] [tinyint] NULL,
	[vCat5Pass19] [tinyint] NULL,
	[vCat5Pass20] [tinyint] NULL,
	[vCat5Pass21] [tinyint] NULL,
	[vCat5Pass22] [tinyint] NULL,
	[vCat5Pass23] [tinyint] NULL,
	[vCat6Pass0] [tinyint] NULL,
	[vCat6Pass1] [tinyint] NULL,
	[vCat6Pass2] [tinyint] NULL,
	[vCat6Pass3] [tinyint] NULL,
	[vCat6Pass4] [tinyint] NULL,
	[vCat6Pass5] [tinyint] NULL,
	[vCat6Pass6] [tinyint] NULL,
	[vCat6Pass7] [tinyint] NULL,
	[vCat6Pass8] [tinyint] NULL,
	[vCat6Pass9] [tinyint] NULL,
	[vCat6Pass10] [tinyint] NULL,
	[vCat6Pass11] [tinyint] NULL,
	[vCat6Pass12] [tinyint] NULL,
	[vCat6Pass13] [tinyint] NULL,
	[vCat6Pass14] [tinyint] NULL,
	[vCat6Pass15] [tinyint] NULL,
	[vCat6Pass16] [tinyint] NULL,
	[vCat6Pass17] [tinyint] NULL,
	[vCat6Pass18] [tinyint] NULL,
	[vCat6Pass19] [tinyint] NULL,
	[vCat6Pass20] [tinyint] NULL,
	[vCat6Pass21] [tinyint] NULL,
	[vCat6Pass22] [tinyint] NULL,
	[vCat6Pass23] [tinyint] NULL,
	[vCat7Pass0] [tinyint] NULL,
	[vCat7Pass1] [tinyint] NULL,
	[vCat7Pass2] [tinyint] NULL,
	[vCat7Pass3] [tinyint] NULL,
	[vCat7Pass4] [tinyint] NULL,
	[vCat7Pass5] [tinyint] NULL,
	[vCat7Pass6] [tinyint] NULL,
	[vCat7Pass7] [tinyint] NULL,
	[vCat7Pass8] [tinyint] NULL,
	[vCat7Pass9] [tinyint] NULL,
	[vCat7Pass10] [tinyint] NULL,
	[vCat7Pass11] [tinyint] NULL,
	[vCat7Pass12] [tinyint] NULL,
	[vCat7Pass13] [tinyint] NULL,
	[vCat7Pass14] [tinyint] NULL,
	[vCat7Pass15] [tinyint] NULL,
	[vCat7Pass16] [tinyint] NULL,
	[vCat7Pass17] [tinyint] NULL,
	[vCat7Pass18] [tinyint] NULL,
	[vCat7Pass19] [tinyint] NULL,
	[vCat7Pass20] [tinyint] NULL,
	[vCat7Pass21] [tinyint] NULL,
	[vCat7Pass22] [tinyint] NULL,
	[vCat7Pass23] [tinyint] NULL,
	[vCat8Pass0] [tinyint] NULL,
	[vCat8Pass1] [tinyint] NULL,
	[vCat8Pass2] [tinyint] NULL,
	[vCat8Pass3] [tinyint] NULL,
	[vCat8Pass4] [tinyint] NULL,
	[vCat8Pass5] [tinyint] NULL,
	[vCat8Pass6] [tinyint] NULL,
	[vCat8Pass7] [tinyint] NULL,
	[vCat8Pass8] [tinyint] NULL,
	[vCat8Pass9] [tinyint] NULL,
	[vCat8Pass10] [tinyint] NULL,
	[vCat8Pass11] [tinyint] NULL,
	[vCat8Pass12] [tinyint] NULL,
	[vCat8Pass13] [tinyint] NULL,
	[vCat8Pass14] [tinyint] NULL,
	[vCat8Pass15] [tinyint] NULL,
	[vCat8Pass16] [tinyint] NULL,
	[vCat8Pass17] [tinyint] NULL,
	[vCat8Pass18] [tinyint] NULL,
	[vCat8Pass19] [tinyint] NULL,
	[vCat8Pass20] [tinyint] NULL,
	[vCat8Pass21] [tinyint] NULL,
	[vCat8Pass22] [tinyint] NULL,
	[vCat8Pass23] [tinyint] NULL,
	[vCat1Comment0] [varchar](max) NULL,
	[vCat1Comment1] [varchar](max) NULL,
	[vCat1Comment2] [varchar](max) NULL,
	[vCat1Comment3] [varchar](max) NULL,
	[vCat1Comment4] [varchar](max) NULL,
	[vCat1Comment5] [varchar](max) NULL,
	[vCat1Comment6] [varchar](max) NULL,
	[vCat1Comment7] [varchar](max) NULL,
	[vCat1Comment8] [varchar](max) NULL,
	[vCat1Comment9] [varchar](max) NULL,
	[vCat1Comment10] [varchar](max) NULL,
	[vCat1Comment11] [varchar](max) NULL,
	[vCat1Comment12] [varchar](max) NULL,
	[vCat1Comment13] [varchar](max) NULL,
	[vCat1Comment14] [varchar](max) NULL,
	[vCat1Comment15] [varchar](max) NULL,
	[vCat1Comment16] [varchar](max) NULL,
	[vCat1Comment17] [varchar](max) NULL,
	[vCat1Comment18] [varchar](max) NULL,
	[vCat1Comment19] [varchar](max) NULL,
	[vCat1Comment20] [varchar](max) NULL,
	[vCat1Comment21] [varchar](max) NULL,
	[vCat1Comment22] [varchar](max) NULL,
	[vCat1Comment23] [varchar](max) NULL,
	[vCat2Comment0] [varchar](max) NULL,
	[vCat2Comment1] [varchar](max) NULL,
	[vCat2Comment2] [varchar](max) NULL,
	[vCat2Comment3] [varchar](max) NULL,
	[vCat2Comment4] [varchar](max) NULL,
	[vCat2Comment5] [varchar](max) NULL,
	[vCat2Comment6] [varchar](max) NULL,
	[vCat2Comment7] [varchar](max) NULL,
	[vCat2Comment8] [varchar](max) NULL,
	[vCat2Comment9] [varchar](max) NULL,
	[vCat2Comment10] [varchar](max) NULL,
	[vCat2Comment11] [varchar](max) NULL,
	[vCat2Comment12] [varchar](max) NULL,
	[vCat2Comment13] [varchar](max) NULL,
	[vCat2Comment14] [varchar](max) NULL,
	[vCat2Comment15] [varchar](max) NULL,
	[vCat2Comment16] [varchar](max) NULL,
	[vCat2Comment17] [varchar](max) NULL,
	[vCat2Comment18] [varchar](max) NULL,
	[vCat2Comment19] [varchar](max) NULL,
	[vCat2Comment20] [varchar](max) NULL,
	[vCat2Comment21] [varchar](max) NULL,
	[vCat2Comment22] [varchar](max) NULL,
	[vCat2Comment23] [varchar](max) NULL,
	[vCat3Comment0] [varchar](max) NULL,
	[vCat3Comment1] [varchar](max) NULL,
	[vCat3Comment2] [varchar](max) NULL,
	[vCat3Comment3] [varchar](max) NULL,
	[vCat3Comment4] [varchar](max) NULL,
	[vCat3Comment5] [varchar](max) NULL,
	[vCat3Comment6] [varchar](max) NULL,
	[vCat3Comment7] [varchar](max) NULL,
	[vCat3Comment8] [varchar](max) NULL,
	[vCat3Comment9] [varchar](max) NULL,
	[vCat3Comment10] [varchar](max) NULL,
	[vCat3Comment11] [varchar](max) NULL,
	[vCat3Comment12] [varchar](max) NULL,
	[vCat3Comment13] [varchar](max) NULL,
	[vCat3Comment14] [varchar](max) NULL,
	[vCat3Comment15] [varchar](max) NULL,
	[vCat3Comment16] [varchar](max) NULL,
	[vCat3Comment17] [varchar](max) NULL,
	[vCat3Comment18] [varchar](max) NULL,
	[vCat3Comment19] [varchar](max) NULL,
	[vCat3Comment20] [varchar](max) NULL,
	[vCat3Comment21] [varchar](max) NULL,
	[vCat3Comment22] [varchar](max) NULL,
	[vCat3Comment23] [varchar](max) NULL,
	[vCat4Comment0] [varchar](max) NULL,
	[vCat4Comment1] [varchar](max) NULL,
	[vCat4Comment2] [varchar](max) NULL,
	[vCat4Comment3] [varchar](max) NULL,
	[vCat4Comment4] [varchar](max) NULL,
	[vCat4Comment5] [varchar](max) NULL,
	[vCat4Comment6] [varchar](max) NULL,
	[vCat4Comment7] [varchar](max) NULL,
	[vCat4Comment8] [varchar](max) NULL,
	[vCat4Comment9] [varchar](max) NULL,
	[vCat4Comment10] [varchar](max) NULL,
	[vCat4Comment11] [varchar](max) NULL,
	[vCat4Comment12] [varchar](max) NULL,
	[vCat4Comment13] [varchar](max) NULL,
	[vCat4Comment14] [varchar](max) NULL,
	[vCat4Comment15] [varchar](max) NULL,
	[vCat4Comment16] [varchar](max) NULL,
	[vCat4Comment17] [varchar](max) NULL,
	[vCat4Comment18] [varchar](max) NULL,
	[vCat4Comment19] [varchar](max) NULL,
	[vCat4Comment20] [varchar](max) NULL,
	[vCat4Comment21] [varchar](max) NULL,
	[vCat4Comment22] [varchar](max) NULL,
	[vCat4Comment23] [varchar](max) NULL,
	[vCat5Comment0] [varchar](max) NULL,
	[vCat5Comment1] [varchar](max) NULL,
	[vCat5Comment2] [varchar](max) NULL,
	[vCat5Comment3] [varchar](max) NULL,
	[vCat5Comment4] [varchar](max) NULL,
	[vCat5Comment5] [varchar](max) NULL,
	[vCat5Comment6] [varchar](max) NULL,
	[vCat5Comment7] [varchar](max) NULL,
	[vCat5Comment8] [varchar](max) NULL,
	[vCat5Comment9] [varchar](max) NULL,
	[vCat5Comment10] [varchar](max) NULL,
	[vCat5Comment11] [varchar](max) NULL,
	[vCat5Comment12] [varchar](max) NULL,
	[vCat5Comment13] [varchar](max) NULL,
	[vCat5Comment14] [varchar](max) NULL,
	[vCat5Comment15] [varchar](max) NULL,
	[vCat5Comment16] [varchar](max) NULL,
	[vCat5Comment17] [varchar](max) NULL,
	[vCat5Comment18] [varchar](max) NULL,
	[vCat5Comment19] [varchar](max) NULL,
	[vCat5Comment20] [varchar](max) NULL,
	[vCat5Comment21] [varchar](max) NULL,
	[vCat5Comment22] [varchar](max) NULL,
	[vCat5Comment23] [varchar](max) NULL,
	[vCat6Comment0] [varchar](max) NULL,
	[vCat6Comment1] [varchar](max) NULL,
	[vCat6Comment2] [varchar](max) NULL,
	[vCat6Comment3] [varchar](max) NULL,
	[vCat6Comment4] [varchar](max) NULL,
	[vCat6Comment5] [varchar](max) NULL,
	[vCat6Comment6] [varchar](max) NULL,
	[vCat6Comment7] [varchar](max) NULL,
	[vCat6Comment8] [varchar](max) NULL,
	[vCat6Comment9] [varchar](max) NULL,
	[vCat6Comment10] [varchar](max) NULL,
	[vCat6Comment11] [varchar](max) NULL,
	[vCat6Comment12] [varchar](max) NULL,
	[vCat6Comment13] [varchar](max) NULL,
	[vCat6Comment14] [varchar](max) NULL,
	[vCat6Comment15] [varchar](max) NULL,
	[vCat6Comment16] [varchar](max) NULL,
	[vCat6Comment17] [varchar](max) NULL,
	[vCat6Comment18] [varchar](max) NULL,
	[vCat6Comment19] [varchar](max) NULL,
	[vCat6Comment20] [varchar](max) NULL,
	[vCat6Comment21] [varchar](max) NULL,
	[vCat6Comment22] [varchar](max) NULL,
	[vCat6Comment23] [varchar](max) NULL,
	[vCat7Comment0] [varchar](max) NULL,
	[vCat7Comment1] [varchar](max) NULL,
	[vCat7Comment2] [varchar](max) NULL,
	[vCat7Comment3] [varchar](max) NULL,
	[vCat7Comment4] [varchar](max) NULL,
	[vCat7Comment5] [varchar](max) NULL,
	[vCat7Comment6] [varchar](max) NULL,
	[vCat7Comment7] [varchar](max) NULL,
	[vCat7Comment8] [varchar](max) NULL,
	[vCat7Comment9] [varchar](max) NULL,
	[vCat7Comment10] [varchar](max) NULL,
	[vCat7Comment11] [varchar](max) NULL,
	[vCat7Comment12] [varchar](max) NULL,
	[vCat7Comment13] [varchar](max) NULL,
	[vCat7Comment14] [varchar](max) NULL,
	[vCat7Comment15] [varchar](max) NULL,
	[vCat7Comment16] [varchar](max) NULL,
	[vCat7Comment17] [varchar](max) NULL,
	[vCat7Comment18] [varchar](max) NULL,
	[vCat7Comment19] [varchar](max) NULL,
	[vCat7Comment20] [varchar](max) NULL,
	[vCat7Comment21] [varchar](max) NULL,
	[vCat7Comment22] [varchar](max) NULL,
	[vCat7Comment23] [varchar](max) NULL,
	[vCat8Comment0] [varchar](max) NULL,
	[vCat8Comment1] [varchar](max) NULL,
	[vCat8Comment2] [varchar](max) NULL,
	[vCat8Comment3] [varchar](max) NULL,
	[vCat8Comment4] [varchar](max) NULL,
	[vCat8Comment5] [varchar](max) NULL,
	[vCat8Comment6] [varchar](max) NULL,
	[vCat8Comment7] [varchar](max) NULL,
	[vCat8Comment8] [varchar](max) NULL,
	[vCat8Comment9] [varchar](max) NULL,
	[vCat8Comment10] [varchar](max) NULL,
	[vCat8Comment11] [varchar](max) NULL,
	[vCat8Comment12] [varchar](max) NULL,
	[vCat8Comment13] [varchar](max) NULL,
	[vCat8Comment14] [varchar](max) NULL,
	[vCat8Comment15] [varchar](max) NULL,
	[vCat8Comment16] [varchar](max) NULL,
	[vCat8Comment17] [varchar](max) NULL,
	[vCat8Comment18] [varchar](max) NULL,
	[vCat8Comment19] [varchar](max) NULL,
	[vCat8Comment20] [varchar](max) NULL,
	[vCat8Comment21] [varchar](max) NULL,
	[vCat8Comment22] [varchar](max) NULL,
	[vCat8Comment23] [varchar](max) NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[PO]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[PO](
	[ID] [int] IDENTITY(1,1) NOT NULL,
	[PO] [int] NULL,
	[fDate] [datetime] NULL,
	[fDesc] [varchar](8000) NULL,
	[Amount] [numeric](30, 2) NOT NULL,
	[Vendor] [int] NULL,
	[Status] [smallint] NULL,
	[Due] [datetime] NULL,
	[ShipVia] [varchar](50) NULL,
	[Terms] [smallint] NULL,
	[FOB] [varchar](50) NULL,
	[ShipTo] [varchar](8000) NULL,
	[Approved] [smallint] NULL,
	[Custom1] [varchar](50) NULL,
	[Custom2] [varchar](50) NULL,
	[ApprovedBy] [varchar](25) NULL,
	[ReqBy] [int] NULL,
	[fBy] [varchar](50) NULL,
	[InUseby] [varchar](50) NOT NULL,
	[InUseTime] [datetime] NULL,
	[Ticket] [int] NOT NULL,
	[QuoteID] [int] NOT NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
 CONSTRAINT [PK_PO] PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[POItem]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[POItem](
	[ID] [int] IDENTITY(1,1) NOT NULL,
	[PO] [int] NULL,
	[Line] [smallint] NULL,
	[Quan] [numeric](30, 2) NOT NULL,
	[fDesc] [varchar](8000) NULL,
	[Price] [numeric](30, 4) NULL,
	[Amount] [numeric](30, 2) NOT NULL,
	[Job] [int] NULL,
	[Phase] [smallint] NULL,
	[Inv] [int] NULL,
	[GL] [int] NULL,
	[Freight] [numeric](30, 2) NULL,
	[Rquan] [numeric](30, 8) NULL,
	[Billed] [int] NULL,
	[Ticket] [int] NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
 CONSTRAINT [PK_POItem] PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Posting]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Posting](
	[Post] [varchar](50) NULL,
	[ID] [int] NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[POType]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[POType](
	[Status] [varchar](10) NOT NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[PRAccruedOn]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[PRAccruedOn](
	[Field] [varchar](15) NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[PRBasedOn]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[PRBasedOn](
	[Field] [varchar](15) NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[PRDed]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[PRDed](
	[ID] [int] NOT NULL,
	[fDesc] [varchar](50) NULL,
	[Type] [smallint] NULL,
	[ByW] [smallint] NULL,
	[BasedOn] [smallint] NULL,
	[AccruedOn] [smallint] NULL,
	[Count] [int] NULL,
	[EmpRate] [numeric](30, 4) NULL,
	[EmpTop] [numeric](30, 2) NULL,
	[EmpGL] [int] NULL,
	[CompRate] [numeric](30, 4) NULL,
	[CompTop] [numeric](30, 2) NULL,
	[CompGL] [int] NULL,
	[CompGLE] [int] NULL,
	[Paid] [smallint] NOT NULL,
	[Vendor] [int] NULL,
	[Balance] [numeric](30, 2) NULL,
	[InUse] [smallint] NOT NULL,
	[Remarks] [varchar](8000) NULL,
	[DedType] [smallint] NULL,
	[Reimb] [smallint] NULL,
	[Job] [smallint] NULL,
	[Box] [smallint] NULL,
	[Frequency] [int] NULL,
	[Process] [bit] NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[PRDedItem]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[PRDedItem](
	[Ded] [int] NULL,
	[Emp] [int] NULL,
	[BasedOn] [smallint] NULL,
	[AccruedOn] [smallint] NULL,
	[ByW] [smallint] NULL,
	[EmpRate] [numeric](30, 4) NOT NULL,
	[EmpTop] [numeric](30, 2) NOT NULL,
	[EmpGL] [int] NULL,
	[CompRate] [numeric](30, 4) NOT NULL,
	[CompTop] [numeric](30, 2) NOT NULL,
	[CompGL] [int] NULL,
	[CompGLE] [int] NULL,
	[InUse] [smallint] NOT NULL,
	[YTD] [numeric](30, 2) NULL,
	[YTDC] [numeric](30, 2) NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[PRDedType]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[PRDedType](
	[Field] [varchar](15) NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[PRHistory]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[PRHistory](
	[ID] [int] NULL,
	[Year] [smallint] NULL,
	[Wage] [numeric](30, 2) NULL,
	[Fit] [numeric](30, 2) NULL,
	[FicaW] [numeric](30, 2) NULL,
	[Fica] [numeric](30, 2) NULL,
	[MediW] [numeric](30, 2) NULL,
	[Medi] [numeric](30, 2) NULL,
	[State] [varchar](2) NULL,
	[StateW] [numeric](30, 2) NULL,
	[Sit] [numeric](30, 2) NULL,
	[State2] [varchar](2) NULL,
	[StateW2] [numeric](30, 2) NULL,
	[Sit2] [numeric](30, 2) NULL,
	[State3] [varchar](2) NULL,
	[StateW3] [numeric](30, 2) NULL,
	[Sit3] [numeric](30, 2) NULL,
	[F401KD] [varchar](10) NULL,
	[F401K] [numeric](30, 2) NULL,
	[StateID] [varchar](20) NULL,
	[StateID2] [varchar](20) NULL,
	[StateID3] [varchar](20) NULL,
	[FederalID] [varchar](20) NULL,
	[Box9] [numeric](30, 2) NULL,
	[Box10] [numeric](30, 2) NULL,
	[Box11] [numeric](30, 2) NULL,
	[Box12] [numeric](30, 2) NULL,
	[Box16] [numeric](30, 2) NULL,
	[Box16B] [numeric](30, 2) NULL,
	[Box17] [numeric](30, 2) NULL,
	[Box18] [numeric](30, 2) NULL,
	[Box19] [numeric](30, 2) NULL,
	[Box20] [numeric](30, 2) NULL,
	[Box21] [numeric](30, 2) NULL,
	[Box14] [numeric](30, 4) NULL,
	[Vac] [numeric](30, 2) NOT NULL,
	[HVac] [numeric](30, 2) NOT NULL,
	[HVacAccrued] [numeric](30, 2) NOT NULL,
	[Sick] [numeric](30, 2) NOT NULL,
	[HSick] [numeric](30, 2) NOT NULL,
	[HSickAccrued] [numeric](30, 2) NOT NULL,
	[VThis] [numeric](30, 2) NOT NULL,
	[VLast] [numeric](30, 2) NOT NULL,
	[VRate] [numeric](30, 2) NOT NULL,
	[Box52] [numeric](30, 2) NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Privilege]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Privilege](
	[ID] [int] IDENTITY(1,1) NOT NULL,
	[User_ID] [int] NOT NULL,
	[Access_Table] [varchar](255) NOT NULL,
	[User_Privilege] [int] NULL,
	[Group_Privilege] [int] NULL,
	[Other_Privilege] [int] NULL,
PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[PRLabor]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[PRLabor](
	[ID] [int] IDENTITY(1,1) NOT FOR REPLICATION NOT NULL,
	[EN] [int] NULL,
	[fDesc] [varchar](50) NULL,
	[WageGL] [int] NULL,
	[FringeGL] [int] NULL,
	[MileGL] [int] NULL,
	[ReimbGL] [int] NULL,
	[ZoneGL] [int] NULL,
PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Prob]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Prob](
	[Prob] [varchar](10) NOT NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Prospect]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Prospect](
	[ID] [int] NOT NULL,
	[Rol] [int] NULL,
	[Type] [varchar](20) NULL,
	[Level] [smallint] NULL,
	[Status] [smallint] NULL,
	[LDate] [datetime] NULL,
	[LTime] [datetime] NULL,
	[Source] [varchar](50) NULL,
	[Program] [smallint] NULL,
	[NDate] [datetime] NULL,
	[NTime] [datetime] NULL,
	[PriceL] [smallint] NULL,
	[Custom1] [varchar](50) NULL,
	[Custom2] [varchar](50) NULL,
	[Custom3] [varchar](50) NULL,
	[Custom4] [varchar](50) NULL,
	[Custom5] [varchar](50) NULL,
	[Terr] [int] NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
 CONSTRAINT [PK_Prospect] PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[PROther]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[PROther](
	[Cat] [smallint] NULL,
	[Emp] [int] NULL,
	[GL] [int] NULL,
	[Rate] [numeric](30, 4) NOT NULL,
	[FIT] [smallint] NOT NULL,
	[FICA] [smallint] NOT NULL,
	[MEDI] [smallint] NOT NULL,
	[FUTA] [smallint] NOT NULL,
	[SIT] [smallint] NOT NULL,
	[Vac] [smallint] NOT NULL,
	[WC] [smallint] NOT NULL,
	[Uni] [smallint] NOT NULL,
	[Sick] [smallint] NOT NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[PRPaidBy]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[PRPaidBy](
	[Field] [varchar](15) NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[PRReg]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[PRReg](
	[ID] [int] NOT NULL,
	[fDate] [datetime] NULL,
	[Ref] [int] NULL,
	[fDesc] [varchar](50) NULL,
	[EmpID] [int] NULL,
	[Bank] [int] NULL,
	[TransID] [int] NULL,
	[Reg] [numeric](30, 2) NULL,
	[YReg] [numeric](30, 2) NULL,
	[HReg] [numeric](30, 2) NULL,
	[HYReg] [numeric](30, 2) NULL,
	[OT] [numeric](30, 2) NULL,
	[YOT] [numeric](30, 2) NULL,
	[HOT] [numeric](30, 2) NULL,
	[HYOT] [numeric](30, 2) NULL,
	[DT] [numeric](30, 2) NULL,
	[YDT] [numeric](30, 2) NULL,
	[HDT] [numeric](30, 2) NULL,
	[HYDT] [numeric](30, 2) NULL,
	[TT] [numeric](30, 2) NULL,
	[YTT] [numeric](30, 2) NULL,
	[HTT] [numeric](30, 2) NULL,
	[HYTT] [numeric](30, 2) NULL,
	[Hol] [numeric](30, 2) NULL,
	[YHol] [numeric](30, 2) NULL,
	[HHol] [numeric](30, 2) NULL,
	[HYHol] [numeric](30, 2) NULL,
	[Vac] [numeric](30, 2) NULL,
	[YVac] [numeric](30, 2) NULL,
	[HVac] [numeric](30, 2) NULL,
	[HYVac] [numeric](30, 2) NULL,
	[Zone] [numeric](30, 2) NULL,
	[YZone] [numeric](30, 2) NULL,
	[Reimb] [numeric](30, 2) NULL,
	[YReimb] [numeric](30, 2) NULL,
	[Mile] [numeric](30, 2) NULL,
	[YMile] [numeric](30, 2) NULL,
	[HMile] [numeric](30, 2) NULL,
	[HYMile] [numeric](30, 2) NULL,
	[Bonus] [numeric](30, 2) NULL,
	[YBonus] [numeric](30, 2) NULL,
	[WFIT] [numeric](30, 2) NULL,
	[WFica] [numeric](30, 2) NULL,
	[WMedi] [numeric](30, 2) NULL,
	[WFuta] [numeric](30, 2) NULL,
	[WSit] [numeric](30, 2) NULL,
	[WVac] [numeric](30, 2) NULL,
	[WWComp] [numeric](30, 2) NULL,
	[WUnion] [numeric](30, 2) NULL,
	[FIT] [numeric](30, 2) NULL,
	[YFIT] [numeric](30, 2) NULL,
	[FICA] [numeric](30, 2) NULL,
	[YFICA] [numeric](30, 2) NULL,
	[MEDI] [numeric](30, 2) NULL,
	[YMEDI] [numeric](30, 2) NULL,
	[FUTA] [numeric](30, 2) NULL,
	[YFUTA] [numeric](30, 2) NULL,
	[SIT] [numeric](30, 2) NULL,
	[YSIT] [numeric](30, 2) NULL,
	[Local] [numeric](30, 2) NULL,
	[YLocal] [numeric](30, 2) NULL,
	[TOTher] [numeric](30, 2) NULL,
	[NT] [numeric](30, 2) NULL,
	[YTOTher] [numeric](30, 2) NULL,
	[TInc] [numeric](30, 2) NULL,
	[YNT] [numeric](30, 2) NULL,
	[HNT] [numeric](30, 2) NULL,
	[TDed] [numeric](30, 2) NULL,
	[HYNT] [numeric](30, 2) NULL,
	[Net] [numeric](30, 2) NULL,
	[State] [varchar](2) NULL,
	[VThis] [numeric](30, 2) NULL,
	[REIMJE] [numeric](30, 4) NULL,
	[WELF] [numeric](30, 4) NULL,
	[SDI] [numeric](30, 4) NULL,
	[401K] [numeric](30, 4) NULL,
	[GARN] [numeric](30, 4) NULL,
	[WeekNo] [int] NULL,
	[Remarks] [varchar](255) NULL,
	[ELast] [numeric](30, 4) NULL,
	[EThis] [numeric](30, 4) NULL,
	[CompMedi] [numeric](30, 2) NOT NULL,
	[WMediOverTH] [numeric](30, 2) NOT NULL,
	[Sick] [numeric](30, 2) NOT NULL,
	[YSick] [numeric](30, 2) NOT NULL,
	[WSick] [numeric](30, 2) NOT NULL,
	[HSick] [numeric](30, 2) NOT NULL,
	[HYSick] [numeric](30, 2) NOT NULL,
	[HSickAccrued] [numeric](30, 2) NOT NULL,
	[HYSickAccrued] [numeric](30, 2) NOT NULL,
	[HVacAccrued] [numeric](30, 2) NOT NULL,
	[HYVacAccrued] [numeric](30, 2) NOT NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[PRReg_Temp]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[PRReg_Temp](
	[ID] [int] NOT NULL,
	[fDate] [datetime] NULL,
	[Ref] [int] NULL,
	[fDesc] [varchar](50) NULL,
	[EmpID] [int] NULL,
	[Bank] [int] NULL,
	[TransID] [int] NULL,
	[Reg] [numeric](30, 2) NULL,
	[YReg] [numeric](30, 2) NULL,
	[HReg] [numeric](30, 2) NULL,
	[HYReg] [numeric](30, 2) NULL,
	[OT] [numeric](30, 2) NULL,
	[YOT] [numeric](30, 2) NULL,
	[HOT] [numeric](30, 2) NULL,
	[HYOT] [numeric](30, 2) NULL,
	[DT] [numeric](30, 2) NULL,
	[YDT] [numeric](30, 2) NULL,
	[HDT] [numeric](30, 2) NULL,
	[HYDT] [numeric](30, 2) NULL,
	[TT] [numeric](30, 2) NULL,
	[YTT] [numeric](30, 2) NULL,
	[HTT] [numeric](30, 2) NULL,
	[HYTT] [numeric](30, 2) NULL,
	[Hol] [numeric](30, 2) NULL,
	[YHol] [numeric](30, 2) NULL,
	[HHol] [numeric](30, 2) NULL,
	[HYHol] [numeric](30, 2) NULL,
	[Vac] [numeric](30, 2) NULL,
	[YVac] [numeric](30, 2) NULL,
	[HVac] [numeric](30, 2) NULL,
	[HYVac] [numeric](30, 2) NULL,
	[Zone] [numeric](30, 2) NULL,
	[YZone] [numeric](30, 2) NULL,
	[Reimb] [numeric](30, 2) NULL,
	[YReimb] [numeric](30, 2) NULL,
	[Mile] [numeric](30, 2) NULL,
	[YMile] [numeric](30, 2) NULL,
	[HMile] [numeric](30, 2) NULL,
	[HYMile] [numeric](30, 2) NULL,
	[Bonus] [numeric](30, 2) NULL,
	[YBonus] [numeric](30, 2) NULL,
	[WFIT] [numeric](30, 2) NULL,
	[WFica] [numeric](30, 2) NULL,
	[WMedi] [numeric](30, 2) NULL,
	[WFuta] [numeric](30, 2) NULL,
	[WSit] [numeric](30, 2) NULL,
	[WVac] [numeric](30, 2) NULL,
	[WWComp] [numeric](30, 2) NULL,
	[WUnion] [numeric](30, 2) NULL,
	[FIT] [numeric](30, 2) NULL,
	[YFIT] [numeric](30, 2) NULL,
	[FICA] [numeric](30, 2) NULL,
	[YFICA] [numeric](30, 2) NULL,
	[MEDI] [numeric](30, 2) NULL,
	[YMEDI] [numeric](30, 2) NULL,
	[FUTA] [numeric](30, 2) NULL,
	[YFUTA] [numeric](30, 2) NULL,
	[SIT] [numeric](30, 2) NULL,
	[YSIT] [numeric](30, 2) NULL,
	[Local] [numeric](30, 2) NULL,
	[YLocal] [numeric](30, 2) NULL,
	[TOTher] [numeric](30, 2) NULL,
	[NT] [numeric](30, 2) NULL,
	[YTOTher] [numeric](30, 2) NULL,
	[TInc] [numeric](30, 2) NULL,
	[YNT] [numeric](30, 2) NULL,
	[HNT] [numeric](30, 2) NULL,
	[TDed] [numeric](30, 2) NULL,
	[HYNT] [numeric](30, 2) NULL,
	[Net] [numeric](30, 2) NULL,
	[State] [varchar](2) NULL,
	[VThis] [numeric](30, 2) NULL,
	[CompMedi] [numeric](30, 2) NOT NULL,
	[WMediOverTH] [numeric](30, 2) NOT NULL,
	[Sick] [numeric](30, 2) NOT NULL,
	[YSick] [numeric](30, 2) NOT NULL,
	[WSick] [numeric](30, 2) NOT NULL,
	[HSick] [numeric](30, 2) NOT NULL,
	[HYSick] [numeric](30, 2) NOT NULL,
	[HSickAccrued] [numeric](30, 2) NOT NULL,
	[HYSickAccrued] [numeric](30, 2) NOT NULL,
	[HVacAccrued] [numeric](30, 2) NOT NULL,
	[HYVacAccrued] [numeric](30, 2) NOT NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[PRRegDItem]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[PRRegDItem](
	[CheckID] [int] NULL,
	[PRDID] [int] NULL,
	[Amount] [numeric](30, 2) NULL,
	[YAmount] [numeric](30, 2) NULL,
	[AmountC] [numeric](30, 2) NULL,
	[YAmountC] [numeric](30, 2) NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[PRRegDItem_Temp]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[PRRegDItem_Temp](
	[CheckID] [int] NULL,
	[PRDID] [int] NULL,
	[Amount] [numeric](30, 2) NULL,
	[YAmount] [numeric](30, 2) NULL,
	[AmountC] [numeric](30, 2) NULL,
	[YAmountC] [numeric](30, 2) NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[PRRegWItem]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[PRRegWItem](
	[CheckID] [int] NULL,
	[PRWID] [int] NULL,
	[Quan] [numeric](30, 2) NULL,
	[Rate] [numeric](30, 2) NULL,
	[Amount] [numeric](30, 2) NULL,
	[YQuan] [numeric](30, 2) NULL,
	[YAmount] [numeric](30, 2) NULL,
	[OQuan] [numeric](30, 2) NULL,
	[ORate] [numeric](30, 2) NULL,
	[OAmount] [numeric](30, 2) NULL,
	[OYQuan] [numeric](30, 2) NULL,
	[OYAmount] [numeric](30, 2) NULL,
	[DQuan] [numeric](30, 2) NULL,
	[DRate] [numeric](30, 2) NULL,
	[DAmount] [numeric](30, 2) NULL,
	[DYQuan] [numeric](30, 2) NULL,
	[DYAmount] [numeric](30, 2) NULL,
	[TQuan] [numeric](30, 2) NULL,
	[TRate] [numeric](30, 2) NULL,
	[TAmount] [numeric](30, 2) NULL,
	[TYQuan] [numeric](30, 2) NULL,
	[TYAmount] [numeric](30, 2) NULL,
	[NQuan] [numeric](30, 2) NULL,
	[NRate] [numeric](30, 2) NULL,
	[NAmount] [numeric](30, 2) NULL,
	[NYQuan] [numeric](30, 2) NULL,
	[NYAmount] [numeric](30, 2) NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[PRRegWItem_Temp]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[PRRegWItem_Temp](
	[CheckID] [int] NULL,
	[PRWID] [int] NULL,
	[Quan] [numeric](30, 2) NULL,
	[Rate] [numeric](30, 2) NULL,
	[Amount] [numeric](30, 2) NULL,
	[YQuan] [numeric](30, 2) NULL,
	[YAmount] [numeric](30, 2) NULL,
	[OQuan] [numeric](30, 2) NULL,
	[ORate] [numeric](30, 2) NULL,
	[OAmount] [numeric](30, 2) NULL,
	[OYQuan] [numeric](30, 2) NULL,
	[OYAmount] [numeric](30, 2) NULL,
	[DQuan] [numeric](30, 2) NULL,
	[DRate] [numeric](30, 2) NULL,
	[DAmount] [numeric](30, 2) NULL,
	[DYQuan] [numeric](30, 2) NULL,
	[DYAmount] [numeric](30, 2) NULL,
	[TQuan] [numeric](30, 2) NULL,
	[TRate] [numeric](30, 2) NULL,
	[TAmount] [numeric](30, 2) NULL,
	[TYQuan] [numeric](30, 2) NULL,
	[TYAmount] [numeric](30, 2) NULL,
	[NQuan] [numeric](30, 2) NULL,
	[NRate] [numeric](30, 2) NULL,
	[NAmount] [numeric](30, 2) NULL,
	[NYQuan] [numeric](30, 2) NULL,
	[NYAmount] [numeric](30, 2) NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[PRTCard]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[PRTCard](
	[EmpID] [int] NULL,
	[Line] [int] NULL,
	[fStart] [varchar](10) NULL,
	[fDesc] [varchar](255) NULL,
	[Reg] [numeric](30, 2) NULL,
	[OT] [numeric](30, 2) NULL,
	[NT] [numeric](30, 2) NULL,
	[DT] [numeric](30, 2) NULL,
	[Travel] [numeric](30, 2) NULL,
	[Miles] [numeric](30, 2) NULL,
	[Job] [int] NULL,
	[Phase] [int] NULL,
	[Zone] [numeric](30, 2) NULL,
	[Reimb] [numeric](30, 2) NULL,
	[Wage] [int] NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[PRWage]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[PRWage](
	[ID] [int] NOT NULL,
	[fDesc] [varchar](75) NULL,
	[Field] [smallint] NOT NULL,
	[Reg] [numeric](30, 4) NULL,
	[OT1] [numeric](30, 4) NULL,
	[OT2] [numeric](30, 4) NULL,
	[TT] [numeric](30, 4) NULL,
	[FIT] [smallint] NOT NULL,
	[FICA] [smallint] NOT NULL,
	[MEDI] [smallint] NOT NULL,
	[FUTA] [smallint] NOT NULL,
	[SIT] [smallint] NOT NULL,
	[Vac] [smallint] NOT NULL,
	[WC] [smallint] NOT NULL,
	[Uni] [smallint] NOT NULL,
	[Count] [int] NULL,
	[LCount] [int] NULL,
	[Remarks] [varchar](8000) NULL,
	[GL] [int] NULL,
	[NT] [numeric](30, 4) NULL,
	[MileageGL] [int] NULL,
	[ReimburseGL] [int] NULL,
	[ZoneGL] [int] NULL,
	[Globe] [smallint] NULL,
	[Status] [smallint] NULL,
	[CReg] [numeric](30, 4) NULL,
	[COT] [numeric](30, 4) NULL,
	[CDT] [numeric](30, 4) NULL,
	[CNT] [numeric](30, 4) NULL,
	[CTT] [numeric](30, 4) NULL,
	[Sick] [smallint] NOT NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
 CONSTRAINT [PK_PRWage] PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[PRWageBR]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[PRWageBR](
	[ID] [int] IDENTITY(1,1) NOT FOR REPLICATION NOT NULL,
	[fdesc] [varchar](75) NULL,
	[Reg] [numeric](30, 4) NULL,
	[OT1] [numeric](30, 4) NULL,
	[OT2] [numeric](30, 4) NULL,
	[TT] [numeric](30, 4) NULL,
	[NT] [numeric](30, 4) NULL,
	[Count] [int] NULL,
	[Remarks] [varchar](8000) NULL,
	[Status] [smallint] NULL,
	[EN] [int] NULL,
PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[PRWageItem]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[PRWageItem](
	[Wage] [int] NULL,
	[Emp] [int] NULL,
	[GL] [int] NULL,
	[Reg] [numeric](30, 4) NOT NULL,
	[OT] [numeric](30, 4) NOT NULL,
	[DT] [numeric](30, 4) NOT NULL,
	[TT] [numeric](30, 4) NOT NULL,
	[FIT] [smallint] NOT NULL,
	[FICA] [smallint] NOT NULL,
	[MEDI] [smallint] NOT NULL,
	[FUTA] [smallint] NOT NULL,
	[SIT] [smallint] NOT NULL,
	[Vac] [smallint] NOT NULL,
	[WC] [smallint] NOT NULL,
	[Uni] [smallint] NOT NULL,
	[InUse] [smallint] NOT NULL,
	[YTD] [numeric](30, 2) NOT NULL,
	[YTDH] [numeric](30, 2) NOT NULL,
	[OYTD] [numeric](30, 2) NOT NULL,
	[OYTDH] [numeric](30, 2) NOT NULL,
	[DYTD] [numeric](30, 2) NOT NULL,
	[DYTDH] [numeric](30, 2) NOT NULL,
	[TYTD] [numeric](30, 2) NOT NULL,
	[TYTDH] [numeric](30, 2) NOT NULL,
	[NT] [numeric](30, 4) NOT NULL,
	[NYTD] [numeric](30, 2) NOT NULL,
	[NYTDH] [numeric](30, 2) NOT NULL,
	[VacR] [varchar](40) NULL,
	[CReg] [numeric](30, 4) NULL,
	[COT] [numeric](30, 4) NULL,
	[CDT] [numeric](30, 4) NULL,
	[CNT] [numeric](30, 4) NULL,
	[CTT] [numeric](30, 4) NULL,
	[Status] [tinyint] NULL,
	[Sick] [smallint] NOT NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[PType]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[PType](
	[ID] [int] IDENTITY(1,1) NOT NULL,
	[Type] [varchar](30) NULL,
	[Count] [int] NULL,
	[Remarks] [varchar](8000) NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
 CONSTRAINT [PK_PType] PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[QStatus]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[QStatus](
	[ID] [int] NOT NULL,
	[Status] [varchar](25) NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
 CONSTRAINT [PK_QStatus] PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Quote]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Quote](
	[Ref] [int] NOT NULL,
	[fDate] [datetime] NULL,
	[fDesc] [text] NULL,
	[Amount] [numeric](30, 4) NULL,
	[STax] [numeric](30, 4) NULL,
	[Total] [numeric](30, 4) NULL,
	[TaxRegion] [varchar](25) NULL,
	[TaxRate] [numeric](30, 4) NULL,
	[TaxFactor] [numeric](30, 4) NULL,
	[Taxable] [numeric](30, 4) NULL,
	[Type] [smallint] NULL,
	[Job] [int] NULL,
	[LType] [smallint] NULL,
	[Loc] [int] NULL,
	[Rol] [int] NULL,
	[Terms] [smallint] NULL,
	[PO] [varchar](25) NULL,
	[Status] [smallint] NULL,
	[GTax] [numeric](30, 4) NULL,
	[Mech] [int] NULL,
	[Pricing] [smallint] NULL,
	[Invoice] [int] NULL,
	[TaxRegion2] [varchar](25) NULL,
	[TaxRate2] [numeric](30, 4) NULL,
	[Ticket] [int] NULL,
	[Nature] [smallint] NULL,
	[Template] [smallint] NULL,
	[Source] [varchar](20) NULL,
	[Custom1] [varchar](50) NULL,
	[Custom2] [varchar](50) NULL,
	[Custom3] [varchar](1000) NULL,
	[PSTOnlyAmount] [numeric](30, 2) NULL,
	[fComments] [varchar](max) NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
	[GroupMarkup] [numeric](30, 4) NOT NULL,
	[TFMMech] [int] NULL,
 CONSTRAINT [PK_Quote] PRIMARY KEY CLUSTERED 
(
	[Ref] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[QuoteI]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[QuoteI](
	[Ref] [int] NOT NULL,
	[Line] [smallint] NOT NULL,
	[Acct] [int] NULL,
	[Quan] [numeric](30, 4) NULL,
	[fDesc] [varchar](8000) NULL,
	[Price] [numeric](30, 4) NULL,
	[Amount] [numeric](30, 4) NULL,
	[STax] [smallint] NULL,
	[Measure] [varchar](15) NULL,
	[Phase] [int] NULL,
	[FlatRate] [varchar](25) NULL,
	[PSTTax] [smallint] NULL,
	[ManualMarkup] [numeric](30, 4) NOT NULL,
	[GroupMarkup] [numeric](30, 4) NOT NULL,
	[VendorID] [int] NOT NULL,
	[TSID] [int] IDENTITY(1,1) NOT NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
	[SavingsAmount] [numeric](30, 4) NULL,
	[OriginalPrice] [numeric](30, 4) NULL,
	[FlatRateID] [int] NULL,
	[USA] [bit] NOT NULL,
	[Coupon] [bit] NOT NULL,
	[Accepted] [bit] NOT NULL,
	[TurnDownReason] [varchar](500) NOT NULL,
 CONSTRAINT [PK_QuoteI] PRIMARY KEY CLUSTERED 
(
	[TSID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[ReportQueries]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[ReportQueries](
	[ID] [int] NULL,
	[Report] [varchar](255) NULL,
	[Query] [varchar](max) NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[ReviewStatus]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[ReviewStatus](
	[ID] [tinyint] IDENTITY(1,1) NOT FOR REPLICATION NOT NULL,
	[Type] [varchar](50) NULL,
	[Remarks] [text] NULL,
	[Count] [int] NULL,
 CONSTRAINT [PK__ReviewStatus__5F7E2DAC] PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Rol]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Rol](
	[ID] [int] NOT NULL,
	[Name] [varchar](255) NULL,
	[City] [varchar](50) NULL,
	[State] [varchar](2) NULL,
	[Zip] [varchar](10) NULL,
	[Phone] [varchar](28) NULL,
	[Fax] [varchar](28) NULL,
	[Contact] [varchar](50) NULL,
	[Remarks] [varchar](max) NULL,
	[Type] [smallint] NULL,
	[fLong] [float] NULL,
	[Latt] [float] NULL,
	[GeoLock] [smallint] NOT NULL,
	[Since] [datetime] NULL,
	[Last] [datetime] NULL,
	[Address] [varchar](255) NULL,
	[EN] [int] NULL,
	[EMail] [varchar](50) NULL,
	[Website] [varchar](50) NULL,
	[Cellular] [varchar](28) NULL,
	[Category] [varchar](15) NULL,
	[Position] [varchar](255) NULL,
	[Country] [varchar](50) NULL,
	[SalesRemarks] [varchar](8000) NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
 CONSTRAINT [PK_Rol] PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Route]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Route](
	[ID] [int] NOT NULL,
	[Name] [varchar](50) NULL,
	[Mech] [int] NULL,
	[Loc] [int] NULL,
	[Elev] [int] NULL,
	[Hour] [numeric](30, 2) NULL,
	[Amount] [numeric](30, 2) NULL,
	[Remarks] [varchar](8000) NULL,
	[Symbol] [smallint] NULL,
	[EN] [int] NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
 CONSTRAINT [PK_Route] PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[rpt_941]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[rpt_941](
	[RunDate] [smalldatetime] NOT NULL,
	[QuarterDate] [smalldatetime] NULL,
	[idBranch] [tinyint] NOT NULL,
	[Count_Emp] [smallint] NULL,
	[FIT_Wages] [money] NULL,
	[FIT_Withheld] [money] NULL,
	[FICA_Wages] [money] NULL,
	[FICA_Due] [money] NULL,
	[FICA_Withheld] [money] NULL,
	[MEDI_Wages] [money] NULL,
	[MEDI_Due] [money] NULL,
	[MEDI_Withheld] [money] NULL,
	[ADJ_Fractions] [money] NULL,
	[SUB_FICA_MEDI_Due] [money] NULL,
	[SUB_TAX_DueBeforeAdjustment] [money] NULL,
	[TOTAL_TAX_DueAfterAdjustment] [money] NULL,
	[TOTAL_Deposits] [money] NULL,
	[BALANCE_Due] [money] NULL,
	[TaxLiability_Month1] [money] NOT NULL,
	[TaxLiability_Month2] [money] NOT NULL,
	[TaxLiability_Month3] [money] NOT NULL,
	[ADJ_MonthsToQuarter] [money] NULL,
	[AdjustedMonth] [tinyint] NULL,
	[TaxLiability_Quarter] [money] NOT NULL,
	[MEDIOTH_Wages] [numeric](30, 2) NOT NULL,
	[MEDIOTH_Due] [numeric](30, 2) NOT NULL,
	[MEDIOTH_Withheld] [numeric](30, 2) NOT NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[rpt_941B]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[rpt_941B](
	[RunDate] [smalldatetime] NOT NULL,
	[idBranch] [int] NOT NULL,
	[PayDate] [smalldatetime] NULL,
	[Liability] [money] NULL,
	[MonthOfQuarter]  AS (case datepart(month,[PayDate])%(3) when (0) then (3) else datepart(month,[PayDate])%(3) end),
	[DayOfMonth]  AS (datepart(day,[PayDate]))
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[SalesSource]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[SalesSource](
	[TSID] [int] IDENTITY(1,1) NOT NULL,
	[ID] [varchar](20) NULL,
	[fDesc] [varchar](100) NOT NULL,
	[Type] [varchar](25) NOT NULL,
	[Status] [varchar](10) NULL,
	[Circulation] [int] NULL,
	[Frequency] [varchar](15) NULL,
	[UnitCost] [numeric](30, 2) NULL,
	[Hits] [int] NULL,
	[Customers] [int] NULL,
	[Income] [numeric](30, 2) NULL,
	[Remarks] [varchar](8000) NULL,
	[Since] [smalldatetime] NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
 CONSTRAINT [PK_SalesSource] PRIMARY KEY CLUSTERED 
(
	[TSID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[State]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[State](
	[Name] [varchar](2) NOT NULL,
	[fDesc] [varchar](20) NULL,
	[Country] [varchar](15) NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Status]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Status](
	[Status] [varchar](10) NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[STax]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[STax](
	[ID] [int] IDENTITY(1,1) NOT NULL,
	[Name] [varchar](25) NOT NULL,
	[fDesc] [varchar](75) NULL,
	[Rate] [numeric](30, 4) NULL,
	[State] [varchar](2) NULL,
	[Remarks] [varchar](8000) NULL,
	[Count] [int] NULL,
	[GL] [int] NOT NULL,
	[Type] [smallint] NULL,
	[UType] [smallint] NULL,
	[PSTReg] [varchar](20) NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
 CONSTRAINT [PK_STax] PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[SType]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[SType](
	[ID] [int] IDENTITY(1,1) NOT NULL,
	[Name] [varchar](10) NOT NULL,
	[fDesc] [varchar](50) NULL,
	[Type] [smallint] NULL,
	[Count] [int] NULL,
	[Remarks] [varchar](max) NULL,
	[W1] [varchar](100) NULL,
	[W2] [varchar](100) NULL,
	[W3] [varchar](100) NULL,
	[W4] [varchar](100) NULL,
	[W5] [varchar](100) NULL,
	[W6] [varchar](100) NULL,
	[W7] [varchar](100) NULL,
	[W8] [varchar](100) NULL,
	[W9] [varchar](100) NULL,
	[W10] [varchar](100) NULL,
	[W11] [varchar](100) NULL,
	[W12] [varchar](100) NULL,
	[W13] [varchar](100) NULL,
	[W14] [varchar](100) NULL,
	[W15] [varchar](100) NULL,
	[W16] [varchar](100) NULL,
	[W17] [varchar](100) NULL,
	[W18] [varchar](100) NULL,
	[W19] [varchar](100) NULL,
	[W20] [varchar](100) NULL,
	[W21] [varchar](100) NULL,
	[W22] [varchar](100) NULL,
	[W23] [varchar](100) NULL,
	[W24] [varchar](100) NULL,
	[W25] [varchar](100) NULL,
	[W26] [varchar](100) NULL,
	[W27] [varchar](100) NULL,
	[W28] [varchar](100) NULL,
	[W29] [varchar](100) NULL,
	[W30] [varchar](100) NULL,
	[W31] [varchar](100) NULL,
	[W32] [varchar](100) NULL,
	[W33] [varchar](100) NULL,
	[W34] [varchar](100) NULL,
	[W35] [varchar](100) NULL,
	[W36] [varchar](100) NULL,
	[W37] [varchar](100) NULL,
	[W38] [varchar](100) NULL,
	[W39] [varchar](100) NULL,
	[W40] [varchar](100) NULL,
	[W41] [varchar](100) NULL,
	[W42] [varchar](100) NULL,
	[W43] [varchar](100) NULL,
	[W44] [varchar](100) NULL,
	[W45] [varchar](100) NULL,
	[W46] [varchar](100) NULL,
	[W47] [varchar](100) NULL,
	[W48] [varchar](100) NULL,
	[W49] [varchar](100) NULL,
	[W50] [varchar](100) NULL,
	[W51] [varchar](100) NULL,
	[W52] [varchar](100) NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
 CONSTRAINT [PK_SType] PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[TaxDeposit]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[TaxDeposit](
	[Cat] [smallint] NULL,
	[Quarter] [smallint] NULL,
	[Year] [smallint] NULL,
	[Amount] [numeric](30, 2) NULL,
	[Batch] [int] NULL,
	[EN] [int] NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[TaxTable]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[TaxTable](
	[Tax] [varchar](50) NOT NULL,
	[ERRate] [numeric](30, 2) NULL,
	[ERCeiling] [numeric](30, 2) NULL,
	[EERate] [numeric](30, 2) NULL,
	[EECeiling] [numeric](30, 2) NULL,
	[Other] [numeric](30, 2) NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[tblOut]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[tblOut](
	[fOut] [varchar](15) NOT NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[tblUser]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[tblUser](
	[fUser] [varchar](50) NULL,
	[Password] [varchar](12) NULL,
	[Status] [smallint] NULL,
	[Access] [int] NULL,
	[fStart] [datetime] NULL,
	[fEnd] [datetime] NULL,
	[Since] [datetime] NULL,
	[Last] [datetime] NULL,
	[Remarks] [varchar](8000) NULL,
	[Owner] [varchar](6) NULL,
	[Location] [varchar](6) NULL,
	[Elevator] [varchar](6) NULL,
	[Invoice] [varchar](6) NULL,
	[Deposit] [varchar](6) NULL,
	[Apply] [varchar](6) NULL,
	[WriteOff] [varchar](6) NULL,
	[ProcessC] [varchar](6) NULL,
	[ProcessT] [varchar](6) NULL,
	[Interest] [varchar](6) NULL,
	[Collection] [varchar](6) NULL,
	[ARViewer] [varchar](6) NULL,
	[AROther] [varchar](6) NULL,
	[Vendor] [varchar](6) NULL,
	[Bill] [varchar](6) NULL,
	[BillSelect] [varchar](6) NULL,
	[BillPay] [varchar](6) NULL,
	[PO] [varchar](6) NULL,
	[APViewer] [varchar](6) NULL,
	[APOther] [varchar](6) NULL,
	[Chart] [varchar](6) NULL,
	[GLAdj] [varchar](6) NULL,
	[GLViewer] [varchar](6) NULL,
	[IReg] [varchar](6) NULL,
	[CReceipt] [varchar](6) NULL,
	[PJournal] [varchar](6) NULL,
	[YE] [varchar](6) NULL,
	[Service] [varchar](6) NULL,
	[Financial] [varchar](6) NULL,
	[Item] [varchar](6) NULL,
	[InvViewer] [varchar](6) NULL,
	[InvAdj] [varchar](6) NULL,
	[Job] [varchar](6) NULL,
	[JobViewer] [varchar](6) NULL,
	[JobTemplate] [varchar](6) NULL,
	[JobClose] [varchar](6) NULL,
	[JobResult] [varchar](6) NULL,
	[Dispatch] [varchar](6) NULL,
	[Ticket] [varchar](6) NULL,
	[Resolve] [varchar](6) NULL,
	[TestDate] [varchar](6) NULL,
	[TC] [varchar](6) NULL,
	[Human] [varchar](6) NULL,
	[DispReport] [varchar](6) NULL,
	[Violation] [varchar](6) NULL,
	[UserS] [varchar](6) NULL,
	[Control] [varchar](6) NULL,
	[Bank] [varchar](6) NULL,
	[BankRec] [varchar](6) NULL,
	[BankViewer] [varchar](6) NULL,
	[Manual] [varchar](6) NULL,
	[Log] [varchar](6) NULL,
	[Code] [varchar](6) NULL,
	[STax] [varchar](6) NULL,
	[Zone] [varchar](6) NULL,
	[Territory] [varchar](6) NULL,
	[Commodity] [varchar](6) NULL,
	[Employee] [varchar](6) NULL,
	[Crew] [varchar](6) NULL,
	[PRProcess] [varchar](6) NULL,
	[PRRemit] [varchar](6) NULL,
	[PRRegister] [varchar](6) NULL,
	[PRReport] [varchar](6) NULL,
	[Diary] [varchar](6) NULL,
	[TTD] [varchar](6) NULL,
	[Document] [varchar](6) NULL,
	[Phone] [varchar](6) NULL,
	[ToDo] [smallint] NULL,
	[Sales] [varchar](6) NULL,
	[ToDoC] [smallint] NULL,
	[EN] [int] NULL,
	[Proposal] [varchar](6) NULL,
	[Convert] [varchar](6) NULL,
	[POLimit] [numeric](30, 2) NULL,
	[FU] [varchar](6) NULL,
	[POApprove] [smallint] NULL,
	[Tool] [varchar](6) NULL,
	[Vehicle] [varchar](6) NULL,
	[Estimates] [varchar](6) NULL,
	[AwardEstimates] [varchar](6) NULL,
	[BidResults] [varchar](6) NULL,
	[Competitors] [varchar](6) NULL,
	[JobHours] [varchar](6) NULL,
	[ID] [int] IDENTITY(1,1) NOT FOR REPLICATION NOT NULL,
	[Totals] [tinyint] NULL,
	[fDate] [datetime] NULL,
	[PDA] [tinyint] NULL,
	[Tech] [tinyint] NULL,
	[ListsAdmin] [bit] NOT NULL,
	[MassResolvePDATickets] [bit] NOT NULL,
	[idWorker] [int] NULL,
	[CostCenters] [varchar](6) NULL,
	[CostCenterPayrollGL] [varchar](6) NULL,
	[tfmSystem] [smallint] NULL,
	[FinalReviewer] [bit] NOT NULL,
	[TechAlert] [varchar](6) NULL,
	[Licensed] [bit] NOT NULL,
	[LimitTerr] [smallint] NOT NULL,
	[AssignedTerr] [int] NOT NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
	[isSuper] [smallint] NULL,
	[isTFMSuper] [smallint] NULL,
PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[tblWork]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[tblWork](
	[ID] [int] NOT NULL,
	[fDesc] [varchar](50) NULL,
	[Type] [smallint] NULL,
	[Status] [smallint] NULL,
	[Address] [varchar](8000) NULL,
	[City] [varchar](50) NULL,
	[State] [varchar](2) NULL,
	[Zip] [varchar](10) NULL,
	[fLong] [float] NULL,
	[Latt] [float] NULL,
	[GeoLock] [smallint] NOT NULL,
	[Members] [varchar](100) NULL,
	[Car] [int] NULL,
	[Super] [varchar](50) NULL,
	[DBoard] [smallint] NULL,
	[EN] [int] NULL,
	[Activity] [char](30) NULL,
	[Job] [int] NULL,
	[datDisp] [datetime] NULL,
	[JobType] [tinyint] NULL,
	[fLevel] [tinyint] NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
 CONSTRAINT [PK_tblWork] PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[TechLocation]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[TechLocation](
	[ID] [int] IDENTITY(1,1) NOT FOR REPLICATION NOT NULL,
	[TicketID] [int] NULL,
	[TechID] [int] NULL,
	[ActionGroup] [nvarchar](250) NULL,
	[Action] [nvarchar](1000) NULL,
	[Latitude] [float] NULL,
	[Longitude] [float] NULL,
	[Altitude] [float] NULL,
	[Accuracy] [decimal](10, 2) NULL,
	[DateTimeRecorded] [datetime] NULL,
 CONSTRAINT [PK_TechLocation] PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[TechSupport]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[TechSupport](
	[ID] [int] NOT NULL,
	[Company] [varchar](100) NULL,
	[Address] [varchar](100) NULL,
	[City] [varchar](100) NULL,
	[State] [varchar](2) NULL,
	[Zip] [varchar](10) NULL,
	[Phone] [varchar](20) NULL,
	[Fax] [varchar](20) NULL,
	[UserN] [varchar](50) NULL,
	[Area] [varchar](25) NULL,
	[Screen] [varchar](50) NULL,
	[Doing] [text] NULL,
	[Type] [varchar](50) NULL,
	[fDesc] [text] NULL,
	[Manual] [varchar](50) NULL,
	[Web] [varchar](50) NULL,
	[Books] [varchar](255) NULL,
	[Version] [varchar](255) NULL,
	[Build] [varchar](50) NULL,
	[TETrans] [varchar](50) NULL,
	[TEUtility] [varchar](50) NULL,
	[Size] [varchar](50) NULL,
	[Kicked] [varchar](50) NULL,
	[RepPath] [varchar](255) NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Terr]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Terr](
	[ID] [int] NOT NULL,
	[Name] [varchar](25) NULL,
	[SMan] [int] NULL,
	[SDesc] [varchar](50) NULL,
	[Remarks] [varchar](8000) NULL,
	[Count] [int] NULL,
	[Symbol] [smallint] NULL,
	[EN] [int] NULL,
	[Address] [varchar](1000) NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
 CONSTRAINT [PK_Terr] PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[TestHistory]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[TestHistory](
	[idTestHistory] [int] IDENTITY(1,1) NOT NULL,
	[idTest] [int] NOT NULL,
	[StatusDate] [smalldatetime] NOT NULL,
	[UserName] [varchar](50) NOT NULL,
	[TestStatus] [varchar](50) NULL,
	[LastDate] [smalldatetime] NULL,
	[idTestStatus] [smallint] NULL,
	[ActualDate] [smalldatetime] NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[TFMConfig]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[TFMConfig](
	[ConfigID] [int] NOT NULL,
	[ConfigDescr] [varchar](50) NOT NULL,
	[ConfigGroup] [varchar](50) NOT NULL,
	[ConfigLabel] [varchar](50) NOT NULL,
	[ConfigValue] [varchar](max) NOT NULL,
	[ConfigType] [int] NOT NULL,
	[ConfigData] [varchar](2000) NULL,
	[ConfigToolTip] [varchar](8000) NOT NULL,
	[IsEditable] [bit] NOT NULL,
	[IsViewable] [bit] NOT NULL,
	[IsDate] [bit] NOT NULL,
	[IsLongText] [bit] NOT NULL,
	[IsMultiLine] [bit] NOT NULL,
	[NotGlobal] [bit] NOT NULL,
	[Sort] [int] NOT NULL,
	[ModifiedOn] [datetime] NOT NULL,
 CONSTRAINT [PK_TFMConfig] PRIMARY KEY CLUSTERED 
(
	[ConfigID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[TFMUserConfig]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[TFMUserConfig](
	[TechID] [int] NOT NULL,
	[ConfigID] [int] NOT NULL,
	[ConfigValue] [varchar](max) NOT NULL,
	[Licensed] [bit] NOT NULL,
	[TSID] [int] IDENTITY(1,1) NOT NULL,
 CONSTRAINT [PK_TFMUserConfig] PRIMARY KEY CLUSTERED 
(
	[TSID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[TFS_Quote]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[TFS_Quote](
	[TicketID] [int] NOT NULL,
	[Line] [int] NULL,
	[ItemType] [int] NULL,
	[fDesc] [varchar](4000) NULL,
	[Quan] [int] NULL,
	[Price1] [int] NULL,
	[Price2] [int] NULL,
	[Amount] [money] NULL,
	[STax] [money] NULL,
	[ItemID] [int] NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[TFSOptions]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[TFSOptions](
	[TFSRegistration] [varchar](50) NOT NULL,
	[bDisplayGrid] [bit] NULL,
	[NumOfTicketsDisplayed] [int] NOT NULL,
	[bViewChargeOption] [bit] NULL,
	[bMakeChargeDefault] [bit] NULL,
	[bViewInvoiceOption] [bit] NULL,
	[bMakeInvoiceDefault] [bit] NULL,
	[bShowCCOption] [bit] NULL,
	[bPrintOnSaveDefault] [bit] NULL,
	[bEMailOnSaveDefault] [bit] NULL,
	[bVerifyTicket] [bit] NULL,
	[vSigPath] [varchar](255) NULL,
	[bSelJob] [bit] NULL,
	[bAutoQuote] [bit] NULL,
	[bViewQuote] [bit] NULL,
	[bPresentBid] [bit] NULL,
	[bKeyboard] [bit] NULL,
	[bCBalance] [bit] NULL,
	[bCreateTicket] [bit] NULL,
	[bElevMode] [bit] NULL,
	[bEmailEnRoute] [bit] NULL,
	[vDispatchEmail] [varchar](50) NULL,
	[vEmailSubjectLine] [varchar](50) NULL,
	[vEmailMessage] [varchar](255) NULL,
	[bPermEmailUpdate] [bit] NULL,
	[bEmailUpdateTable] [int] NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[TicketD]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[TicketD](
	[ID] [int] NOT NULL,
	[CDate] [datetime] NULL,
	[DDate] [datetime] NULL,
	[EDate] [datetime] NULL,
	[fWork] [int] NULL,
	[Job] [int] NULL,
	[LType] [smallint] NOT NULL,
	[Loc] [int] NULL,
	[Elev] [int] NULL,
	[Type] [smallint] NULL,
	[Charge] [smallint] NULL,
	[fDesc] [varchar](max) NULL,
	[DescRes] [varchar](max) NULL,
	[ClearCheck] [smallint] NULL,
	[ClearPR] [smallint] NULL,
	[Total] [numeric](30, 2) NULL,
	[Reg] [numeric](30, 2) NOT NULL,
	[OT] [numeric](30, 2) NOT NULL,
	[DT] [numeric](30, 2) NOT NULL,
	[TT] [numeric](30, 2) NOT NULL,
	[Zone] [numeric](30, 2) NOT NULL,
	[Toll] [numeric](30, 2) NOT NULL,
	[OtherE] [numeric](30, 2) NOT NULL,
	[Status] [smallint] NULL,
	[Invoice] [int] NULL,
	[Level] [smallint] NULL,
	[Est] [numeric](30, 2) NULL,
	[Cat] [varchar](30) NULL,
	[Who] [varchar](30) NULL,
	[fBy] [varchar](50) NULL,
	[SMile] [int] NULL,
	[EMile] [int] NULL,
	[fLong] [float] NULL,
	[Latt] [float] NULL,
	[WageC] [int] NULL,
	[Phase] [smallint] NULL,
	[Car] [int] NULL,
	[CallIn] [smallint] NULL,
	[Mileage] [numeric](30, 2) NOT NULL,
	[NT] [numeric](30, 2) NOT NULL,
	[CauseID] [int] NULL,
	[CauseDesc] [varchar](255) NULL,
	[fGroup] [varchar](25) NULL,
	[PriceL] [int] NULL,
	[WorkOrder] [varchar](10) NULL,
	[TimeRoute] [datetime] NULL,
	[TimeSite] [datetime] NULL,
	[TimeComp] [datetime] NULL,
	[Source] [varchar](20) NULL,
	[Internet] [tinyint] NULL,
	[RBy] [varchar](50) NULL,
	[Custom1] [varchar](50) NULL,
	[Custom2] [varchar](50) NULL,
	[Custom3] [varchar](50) NULL,
	[Custom4] [varchar](50) NULL,
	[Custom5] [varchar](50) NULL,
	[CTime] [varchar](20) NULL,
	[DTime] [varchar](20) NULL,
	[ETime] [varchar](20) NULL,
	[BRemarks] [varchar](255) NULL,
	[WorkComplete] [smallint] NULL,
	[BReview] [smallint] NULL,
	[PRWBR] [int] NULL,
	[Custom6] [tinyint] NULL,
	[Custom7] [tinyint] NULL,
	[Custom8] [tinyint] NULL,
	[Custom9] [tinyint] NULL,
	[Custom10] [tinyint] NULL,
	[CPhone] [varchar](50) NULL,
	[RegTrav] [numeric](30, 2) NULL,
	[OTTrav] [numeric](30, 2) NULL,
	[DTTrav] [numeric](30, 2) NULL,
	[NTTrav] [numeric](30, 2) NULL,
	[Email] [tinyint] NULL,
	[idTestItem] [int] NULL,
	[idRolCustomContact] [int] NOT NULL,
	[Recommendations] [varchar](max) NULL,
	[downtime] [numeric](30, 2) NOT NULL,
	[Comments] [varchar](max) NULL,
	[PartsUsed] [varchar](max) NULL,
	[SignatureText] [varchar](300) NULL,
	[ResolveSource] [varchar](10) NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMCustom1] [varchar](50) NULL,
	[TFMCustom2] [varchar](50) NULL,
	[TFMCustom3] [varchar](50) NULL,
	[TFMCustom4] [tinyint] NULL,
	[TFMCustom5] [tinyint] NULL,
	[StartBreak] [datetime] NULL,
	[EndBreak] [datetime] NULL,
	[ManualInvoice] [varchar](50) NULL,
	[EMailStatus] [int] NOT NULL,
	[ResolvedBy] [nvarchar](50) NULL,
	[SentBy] [nvarchar](50) NULL,
	[SentOn] [datetime] NULL,
 CONSTRAINT [PK_TicketD] PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[TicketDArchive]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[TicketDArchive](
	[ID] [int] NOT NULL,
	[CDate] [datetime] NULL,
	[DDate] [datetime] NULL,
	[EDate] [datetime] NULL,
	[fWork] [int] NULL,
	[Job] [int] NULL,
	[Loc] [int] NULL,
	[Elev] [int] NULL,
	[Type] [smallint] NULL,
	[Charge] [smallint] NULL,
	[fDesc] [text] NULL,
	[DescRes] [text] NULL,
	[ClearCheck] [smallint] NULL,
	[ClearPR] [smallint] NULL,
	[Total] [numeric](30, 2) NULL,
	[Reg] [numeric](30, 2) NOT NULL,
	[OT] [numeric](30, 2) NOT NULL,
	[DT] [numeric](30, 2) NOT NULL,
	[TT] [numeric](30, 2) NOT NULL,
	[Zone] [numeric](30, 2) NOT NULL,
	[Toll] [numeric](30, 2) NOT NULL,
	[OtherE] [numeric](30, 2) NOT NULL,
	[Status] [smallint] NULL,
	[Invoice] [int] NULL,
	[Level] [smallint] NULL,
	[Est] [numeric](30, 2) NULL,
	[Cat] [varchar](30) NULL,
	[Who] [varchar](30) NULL,
	[fBy] [varchar](50) NULL,
	[SMile] [int] NULL,
	[EMile] [int] NULL,
	[fLong] [float] NULL,
	[Latt] [float] NULL,
	[WageC] [int] NULL,
	[Phase] [smallint] NULL,
	[Car] [int] NULL,
	[CallIn] [smallint] NULL,
	[Mileage] [numeric](30, 2) NOT NULL,
	[NT] [numeric](30, 2) NOT NULL,
	[CauseID] [int] NULL,
	[CauseDesc] [varchar](255) NULL,
	[fGroup] [varchar](25) NULL,
	[PriceL] [int] NULL,
	[WorkOrder] [varchar](10) NULL,
	[TimeRoute] [datetime] NULL,
	[TimeSite] [datetime] NULL,
	[TimeComp] [datetime] NULL,
	[Source] [varchar](20) NULL,
	[CTime] [varchar](20) NULL,
	[DTime] [varchar](20) NULL,
	[ETime] [varchar](20) NULL,
	[Internet] [tinyint] NULL,
	[RBy] [varchar](50) NULL,
	[Custom1] [varchar](50) NULL,
	[Custom2] [varchar](50) NULL,
	[Custom3] [varchar](50) NULL,
	[Custom4] [varchar](50) NULL,
	[Custom5] [varchar](50) NULL,
	[BRemarks] [varchar](255) NULL,
	[WorkComplete] [smallint] NULL,
	[BReview] [smallint] NULL,
	[PRWBR] [int] NULL,
	[CPhone] [varchar](50) NULL,
	[RegTrav] [numeric](30, 2) NULL,
	[OTTrav] [numeric](30, 2) NULL,
	[DTTrav] [numeric](30, 2) NULL,
	[NTTrav] [numeric](30, 2) NULL,
	[Custom6] [tinyint] NULL,
	[Custom7] [tinyint] NULL,
	[Custom8] [tinyint] NULL,
	[Custom9] [tinyint] NULL,
	[Custom10] [tinyint] NULL,
	[Email] [tinyint] NULL,
	[AID] [uniqueidentifier] NOT NULL,
	[idTestItem] [int] NULL,
	[idRolCustomContact] [int] NOT NULL,
	[Recommendations] [text] NULL,
	[downtime] [numeric](30, 2) NOT NULL,
	[Comments] [varchar](max) NULL,
	[PartsUsed] [varchar](max) NULL,
	[SignatureText] [varchar](300) NULL,
	[ResolveSource] [varchar](10) NULL,
	[TFMCustom1] [varchar](50) NULL,
	[TFMCustom2] [varchar](50) NULL,
	[TFMCustom3] [varchar](50) NULL,
	[TFMCustom4] [tinyint] NULL,
	[TFMCustom5] [tinyint] NULL,
	[ManualInvoice] [varchar](50) NULL,
	[StartBreak] [datetime] NULL,
	[EndBreak] [datetime] NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[TicketDPDA]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[TicketDPDA](
	[ID] [int] NOT NULL,
	[CDate] [datetime] NULL,
	[DDate] [datetime] NULL,
	[EDate] [datetime] NULL,
	[fWork] [int] NULL,
	[Job] [int] NULL,
	[LType] [smallint] NULL,
	[Loc] [int] NULL,
	[Elev] [int] NULL,
	[Type] [smallint] NULL,
	[Charge] [smallint] NULL,
	[fDesc] [varchar](max) NULL,
	[DescRes] [varchar](max) NULL,
	[ClearCheck] [smallint] NULL,
	[ClearPR] [smallint] NULL,
	[Total] [numeric](30, 2) NULL,
	[Reg] [numeric](30, 2) NULL,
	[OT] [numeric](30, 2) NULL,
	[DT] [numeric](30, 2) NULL,
	[TT] [numeric](30, 2) NULL,
	[Zone] [numeric](30, 2) NULL,
	[Toll] [numeric](30, 2) NULL,
	[OtherE] [numeric](30, 2) NULL,
	[Status] [smallint] NULL,
	[Invoice] [int] NULL,
	[Level] [smallint] NULL,
	[Est] [numeric](30, 2) NULL,
	[Cat] [varchar](30) NULL,
	[Who] [varchar](30) NULL,
	[fBy] [varchar](50) NULL,
	[SMile] [int] NULL,
	[EMile] [int] NULL,
	[fLong] [float] NULL,
	[Latt] [float] NULL,
	[WageC] [int] NULL,
	[Phase] [smallint] NULL,
	[Car] [int] NULL,
	[CallIn] [smallint] NULL,
	[Mileage] [numeric](30, 2) NULL,
	[NT] [numeric](30, 2) NULL,
	[CauseID] [int] NULL,
	[CauseDesc] [varchar](255) NULL,
	[fGroup] [varchar](25) NULL,
	[PriceL] [int] NULL,
	[WorkOrder] [varchar](10) NULL,
	[TimeRoute] [datetime] NULL,
	[TimeSite] [datetime] NULL,
	[TimeComp] [datetime] NULL,
	[Source] [varchar](20) NULL,
	[Internet] [tinyint] NULL,
	[RBy] [varchar](50) NULL,
	[Custom1] [varchar](50) NULL,
	[Custom2] [varchar](50) NULL,
	[Custom3] [varchar](50) NULL,
	[Custom4] [varchar](50) NULL,
	[Custom5] [varchar](50) NULL,
	[BRemarks] [varchar](255) NULL,
	[WorkComplete] [smallint] NULL,
	[BReview] [smallint] NULL,
	[PRWBR] [int] NULL,
	[Custom6] [tinyint] NULL,
	[Custom7] [tinyint] NULL,
	[Custom8] [tinyint] NULL,
	[Custom9] [tinyint] NULL,
	[Custom10] [tinyint] NULL,
	[RegTrav] [numeric](30, 2) NULL,
	[OTTrav] [numeric](30, 2) NULL,
	[DTTrav] [numeric](30, 2) NULL,
	[NTTrav] [numeric](30, 2) NULL,
	[Email] [tinyint] NULL,
	[idTestItem] [int] NULL,
	[idRolCustomContact] [int] NOT NULL,
	[Recommendations] [varchar](max) NULL,
	[downtime] [numeric](30, 2) NULL,
	[Comments] [varchar](max) NULL,
	[PartsUsed] [varchar](max) NULL,
	[SignatureText] [varchar](300) NULL,
	[ResolveSource] [varchar](10) NULL,
	[CTime] [varchar](20) NULL,
	[DTime] [varchar](20) NULL,
	[ETime] [varchar](20) NULL,
	[CPhone] [varchar](50) NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMCustom1] [varchar](50) NULL,
	[TFMCustom2] [varchar](50) NULL,
	[TFMCustom3] [varchar](50) NULL,
	[TFMCustom4] [tinyint] NULL,
	[TFMCustom5] [tinyint] NULL,
	[StartBreak] [datetime] NULL,
	[EndBreak] [datetime] NULL,
 CONSTRAINT [PK_TicketDPDA] PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[TicketF]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[TicketF](
	[ID] [int] IDENTITY(1,1) NOT NULL,
	[Ticket] [int] NULL,
	[FlatRate] [varchar](25) NULL,
	[PriceL] [tinyint] NULL,
	[Line] [tinyint] NULL,
	[QuoteItemID] [int] NOT NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
 CONSTRAINT [PK_TicketF] PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[TicketI]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[TicketI](
	[ID] [int] IDENTITY(1,1) NOT NULL,
	[Ticket] [int] NULL,
	[Line] [smallint] NULL,
	[Item] [int] NULL,
	[Quan] [numeric](30, 2) NULL,
	[fDesc] [varchar](255) NULL,
	[Charge] [smallint] NULL,
	[Amount] [numeric](30, 2) NULL,
	[Phase] [smallint] NULL,
	[FlatRateID] [int] NULL,
	[QuoteItemID] [int] NOT NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
 CONSTRAINT [PK_TicketI] PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[TicketIPDA]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[TicketIPDA](
	[ID] [int] IDENTITY(1,1) NOT NULL,
	[Ticket] [int] NULL,
	[Line] [smallint] NULL,
	[Item] [int] NULL,
	[Quan] [numeric](30, 2) NULL,
	[fDesc] [varchar](255) NULL,
	[Charge] [smallint] NULL,
	[Amount] [numeric](30, 2) NULL,
	[Phase] [smallint] NULL,
	[FlatRateID] [int] NULL,
	[QuoteItemID] [int] NOT NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
 CONSTRAINT [PK_TicketIPDA] PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[TicketO]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[TicketO](
	[ID] [int] NOT NULL,
	[CDate] [datetime] NULL,
	[DDate] [datetime] NULL,
	[EDate] [datetime] NULL,
	[Level] [tinyint] NULL,
	[Est] [numeric](30, 2) NULL,
	[fWork] [int] NULL,
	[DWork] [varchar](50) NULL,
	[Type] [smallint] NULL,
	[Cat] [varchar](30) NULL,
	[fDesc] [text] NULL,
	[Who] [varchar](30) NULL,
	[fBy] [varchar](50) NULL,
	[LType] [smallint] NULL,
	[LID] [int] NULL,
	[LElev] [int] NULL,
	[LDesc1] [varchar](50) NULL,
	[LDesc2] [varchar](50) NULL,
	[LDesc3] [varchar](255) NULL,
	[LDesc4] [varchar](100) NULL,
	[Nature] [smallint] NULL,
	[Job] [int] NULL,
	[Assigned] [smallint] NULL,
	[City] [varchar](50) NULL,
	[State] [varchar](2) NULL,
	[Zip] [varchar](10) NULL,
	[Owner] [int] NULL,
	[Route] [int] NULL,
	[Terr] [int] NULL,
	[fLong] [int] NULL,
	[Latt] [int] NULL,
	[CallIn] [smallint] NULL,
	[SpecType] [int] NULL,
	[SpecID] [int] NULL,
	[EN] [int] NULL,
	[Notes] [text] NULL,
	[fGroup] [varchar](25) NULL,
	[Source] [varchar](20) NULL,
	[High] [tinyint] NULL,
	[Confirmed] [tinyint] NULL,
	[Phone] [char](28) NULL,
	[Phone2] [char](28) NULL,
	[PriceL] [int] NULL,
	[Locked] [tinyint] NULL,
	[Custom1] [varchar](50) NULL,
	[Custom2] [varchar](50) NULL,
	[Custom3] [varchar](50) NULL,
	[Custom4] [varchar](50) NULL,
	[Custom5] [varchar](50) NULL,
	[WorkOrder] [varchar](10) NULL,
	[TimeRoute] [datetime] NULL,
	[TimeSite] [datetime] NULL,
	[TimeComp] [datetime] NULL,
	[Follow] [tinyint] NULL,
	[HandheldFieldsUpdated] [bit] NULL,
	[BRemarks] [varchar](255) NULL,
	[Custom6] [tinyint] NULL,
	[Custom7] [tinyint] NULL,
	[Custom8] [tinyint] NULL,
	[Custom9] [tinyint] NULL,
	[Custom10] [tinyint] NULL,
	[CPhone] [varchar](50) NULL,
	[SMile] [int] NULL,
	[EMile] [int] NULL,
	[idRolCustomContact] [int] NOT NULL,
	[gpsStatus] [varchar](20) NULL,
	[ResolveSource] [varchar](10) NULL,
	[TFMID] [varchar](100) NOT NULL,
	[Comments] [varchar](max) NOT NULL,
	[Internet] [tinyint] NOT NULL,
	[TFMCustom1] [varchar](50) NULL,
	[TFMCustom2] [varchar](50) NULL,
	[TFMCustom3] [varchar](50) NULL,
	[TFMCustom4] [tinyint] NULL,
	[TFMCustom5] [tinyint] NULL,
	[Recommendations] [nvarchar](max) NULL,
	[Resolution] [nvarchar](max) NULL,
	[PartsUsed] [nvarchar](max) NULL,
	[Total] [numeric](30, 2) NOT NULL,
	[Reg] [numeric](30, 2) NOT NULL,
	[OT] [numeric](30, 2) NOT NULL,
	[DT] [numeric](30, 2) NOT NULL,
	[Zone] [numeric](30, 2) NOT NULL,
	[Toll] [numeric](30, 2) NOT NULL,
	[OtherE] [numeric](30, 2) NOT NULL,
	[TT] [numeric](30, 2) NOT NULL,
	[Mileage] [numeric](30, 2) NOT NULL,
	[EmailContact] [nvarchar](50) NULL,
 CONSTRAINT [PK_TicketO] PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[TicketOArchive]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[TicketOArchive](
	[ID] [int] NOT NULL,
	[CDate] [datetime] NULL,
	[DDate] [datetime] NULL,
	[EDate] [datetime] NULL,
	[Level] [tinyint] NULL,
	[Est] [numeric](30, 2) NULL,
	[fWork] [int] NULL,
	[DWork] [varchar](50) NULL,
	[Type] [smallint] NULL,
	[Cat] [varchar](30) NULL,
	[fDesc] [text] NULL,
	[Who] [varchar](30) NULL,
	[fBy] [varchar](50) NULL,
	[LType] [smallint] NULL,
	[LID] [int] NULL,
	[LElev] [int] NULL,
	[LDesc1] [varchar](50) NULL,
	[LDesc2] [varchar](50) NULL,
	[LDesc3] [varchar](255) NULL,
	[LDesc4] [varchar](100) NULL,
	[Nature] [smallint] NULL,
	[Job] [int] NULL,
	[Assigned] [smallint] NULL,
	[City] [varchar](50) NULL,
	[State] [varchar](2) NULL,
	[Zip] [varchar](10) NULL,
	[Owner] [int] NULL,
	[Route] [int] NULL,
	[Terr] [int] NULL,
	[fLong] [float] NULL,
	[Latt] [float] NULL,
	[CallIn] [smallint] NULL,
	[SpecType] [int] NULL,
	[SpecID] [int] NULL,
	[EN] [int] NULL,
	[Notes] [text] NULL,
	[fGroup] [varchar](25) NULL,
	[Source] [varchar](20) NULL,
	[High] [tinyint] NULL,
	[Confirmed] [tinyint] NULL,
	[Phone] [char](28) NULL,
	[Phone2] [char](28) NULL,
	[PriceL] [int] NULL,
	[Locked] [tinyint] NULL,
	[Follow] [tinyint] NULL,
	[Custom1] [varchar](50) NULL,
	[Custom2] [varchar](50) NULL,
	[Custom3] [varchar](50) NULL,
	[Custom4] [varchar](50) NULL,
	[Custom5] [varchar](50) NULL,
	[WorkOrder] [varchar](10) NULL,
	[TimeRoute] [datetime] NULL,
	[TimeSite] [datetime] NULL,
	[TimeComp] [datetime] NULL,
	[HandheldFieldsUpdated] [bit] NULL,
	[AID] [uniqueidentifier] NOT NULL,
	[BRemarks] [varchar](255) NULL,
	[CPhone] [varchar](50) NULL,
	[Custom6] [tinyint] NULL,
	[Custom7] [tinyint] NULL,
	[Custom8] [tinyint] NULL,
	[Custom9] [tinyint] NULL,
	[Custom10] [tinyint] NULL,
	[SMile] [int] NULL,
	[EMile] [int] NULL,
	[idRolCustomContact] [int] NOT NULL,
	[gpsStatus] [varchar](20) NULL,
	[ResolveSource] [varchar](10) NULL,
	[Comments] [varchar](max) NULL,
	[Internet] [tinyint] NOT NULL,
	[TFMCustom1] [varchar](50) NULL,
	[TFMCustom2] [varchar](50) NULL,
	[TFMCustom3] [varchar](50) NULL,
	[TFMCustom4] [tinyint] NULL,
	[TFMCustom5] [tinyint] NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[TicketPic]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[TicketPic](
	[TicketPicID] [int] IDENTITY(1,1) NOT FOR REPLICATION NOT NULL,
	[TicketID] [int] NOT NULL,
	[PicData] [varchar](max) NOT NULL,
	[ModifiedOn] [datetime] NOT NULL,
	[PictureName] [varchar](25) NULL,
	[PictureComments] [varchar](max) NULL,
	[EmailPicture] [tinyint] NOT NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
 CONSTRAINT [PK_TicketPic] PRIMARY KEY CLUSTERED 
(
	[TicketPicID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[TickOStatus]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[TickOStatus](
	[Ref] [tinyint] NOT NULL,
	[Type] [varchar](50) NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
 CONSTRAINT [PK_TickOStatus] PRIMARY KEY CLUSTERED 
(
	[Ref] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[ToDo]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[ToDo](
	[ID] [int] IDENTITY(1,1) NOT NULL,
	[Type] [smallint] NULL,
	[Rol] [int] NULL,
	[fDate] [datetime] NULL,
	[fTime] [datetime] NULL,
	[DateDue] [datetime] NULL,
	[TimeDue] [datetime] NULL,
	[Subject] [varchar](50) NULL,
	[Remarks] [varchar](max) NULL,
	[Keyword] [varchar](10) NULL,
	[Level] [smallint] NULL,
	[fUser] [varchar](50) NULL,
	[fBy] [varchar](50) NULL,
	[Duration] [numeric](30, 2) NULL,
	[Contact] [varchar](50) NULL,
	[Source] [varchar](25) NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
 CONSTRAINT [PK_ToDo] PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Tool]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Tool](
	[ID] [int] NOT NULL,
	[Name] [varchar](50) NULL,
	[fDesc] [varchar](75) NULL,
	[Serial] [varchar](25) NULL,
	[Cat] [varchar](25) NULL,
	[fOut] [smallint] NULL,
	[Value] [numeric](30, 2) NULL,
	[Remarks] [varchar](8000) NULL,
	[Loc] [int] NULL,
	[LocDesc] [varchar](50) NULL,
	[EN] [int] NULL,
	[TID] [varchar](25) NULL,
	[Toolbox] [varchar](25) NULL,
	[Status] [smallint] NULL,
	[ReturnDate] [datetime] NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[ToolTF]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[ToolTF](
	[ID] [int] NULL,
	[fDate] [datetime] NULL,
	[fTime] [datetime] NULL,
	[Type] [smallint] NULL,
	[Loc] [int] NULL,
	[LocDesc] [varchar](50) NULL,
	[fBy] [varchar](50) NULL,
	[RBy] [varchar](50) NULL,
	[Remarks] [text] NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Trans]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Trans](
	[ID] [int] NOT NULL,
	[Batch] [int] NULL,
	[fDate] [datetime] NULL,
	[Type] [smallint] NULL,
	[Line] [smallint] NULL,
	[Ref] [int] NULL,
	[fDesc] [varchar](255) NULL,
	[Amount] [numeric](30, 2) NOT NULL,
	[Acct] [int] NULL,
	[AcctSub] [int] NULL,
	[Status] [varchar](10) NULL,
	[Sel] [smallint] NULL,
	[VInt] [int] NULL,
	[VDoub] [numeric](30, 2) NULL,
	[EN] [int] NULL,
	[strRef] [varchar](50) NULL,
	[PhaseType] [int] NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[TStatus]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[TStatus](
	[Status] [varchar](10) NOT NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Unavailable]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Unavailable](
	[ID] [int] NOT NULL,
	[fDate] [smalldatetime] NULL,
	[Worker] [int] NOT NULL,
	[fDesc] [varchar](50) NULL,
	[AllDay] [varchar](3) NOT NULL,
	[StartTime] [datetime] NULL,
	[EndTime] [datetime] NULL,
	[Remarks] [varchar](8000) NULL,
	[GroupID] [int] NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[uStatus]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[uStatus](
	[ID] [int] NOT NULL,
	[Status] [varchar](250) NULL,
PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = OFF, ALLOW_PAGE_LOCKS = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Vehicle]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Vehicle](
	[ID] [int] NOT NULL,
	[Name] [varchar](50) NULL,
	[Make] [varchar](20) NULL,
	[Model] [varchar](20) NULL,
	[Year] [smallint] NULL,
	[VIN] [varchar](30) NULL,
	[Mileage] [numeric](18, 0) NULL,
	[Color1] [varchar](15) NULL,
	[Color2] [varchar](15) NULL,
	[Type] [varchar](20) NULL,
	[OilD] [numeric](18, 0) NULL,
	[OilL] [datetime] NULL,
	[OilE] [numeric](18, 0) NULL,
	[TuneD] [numeric](18, 0) NULL,
	[TuneL] [datetime] NULL,
	[TuneE] [numeric](18, 0) NULL,
	[Value] [numeric](30, 2) NULL,
	[Door] [smallint] NULL,
	[HP] [smallint] NULL,
	[Cylinder] [smallint] NULL,
	[Diesel] [smallint] NOT NULL,
	[Weight] [smallint] NULL,
	[Max] [smallint] NULL,
	[RegExp] [datetime] NULL,
	[Items] [smallint] NULL,
	[Plate] [varchar](10) NULL,
	[State] [varchar](2) NULL,
	[Remarks] [varchar](8000) NULL,
	[EN] [int] NULL,
	[Status] [smallint] NULL,
	[fWork] [int] NULL,
	[Custom1] [varchar](50) NULL,
	[Custom2] [varchar](50) NULL,
	[Custom3] [varchar](50) NULL,
	[Custom4] [varchar](50) NULL,
	[Custom5] [varchar](50) NULL,
	[gpsname] [varchar](25) NULL,
	[gpsno] [varchar](15) NULL,
	[gpstype] [varchar](15) NULL,
	[gpsdesc] [varchar](25) NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[VehicleCare]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[VehicleCare](
	[ID] [int] NULL,
	[Mileage] [int] NULL,
	[fDate] [datetime] NULL,
	[Oil] [smallint] NULL,
	[Tune] [smallint] NULL,
	[Reg] [smallint] NULL,
	[Remarks] [varchar](8000) NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[VehStatus]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[VehStatus](
	[ID] [int] NULL,
	[Description] [varchar](20) NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Vendor]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Vendor](
	[ID] [int] NOT NULL,
	[Rol] [int] NULL,
	[Acct] [varchar](15) NULL,
	[Type] [varchar](15) NOT NULL,
	[Status] [smallint] NOT NULL,
	[Balance] [numeric](30, 2) NOT NULL,
	[CLimit] [numeric](30, 2) NULL,
	[1099] [smallint] NOT NULL,
	[FID] [varchar](15) NULL,
	[DA] [int] NULL,
	[Acct#] [varchar](25) NULL,
	[Terms] [smallint] NULL,
	[Disc] [numeric](30, 2) NULL,
	[Days] [smallint] NULL,
	[InUse] [smallint] NOT NULL,
	[Remit] [varchar](255) NULL,
	[OnePer] [smallint] NOT NULL,
	[DBank] [varchar](100) NULL,
	[Custom1] [varchar](50) NULL,
	[Custom2] [varchar](50) NULL,
	[Custom3] [varchar](50) NULL,
	[Custom4] [varchar](50) NULL,
	[Custom5] [varchar](50) NULL,
	[Custom6] [varchar](50) NULL,
	[Custom7] [varchar](50) NULL,
	[Custom8] [datetime] NULL,
	[Custom9] [datetime] NULL,
	[Custom10] [datetime] NULL,
	[ShipVia] [varchar](50) NULL,
	[BankAcctNo] [varchar](17) NULL,
	[RouteNo] [varchar](9) NULL,
	[TransCode] [tinyint] NULL,
	[intBox] [tinyint] NOT NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
	[MISC1099Rpt] [int] NOT NULL,
	[NEC1099Rpt] [int] NOT NULL,
 CONSTRAINT [PK_Vendor] PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Violation]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Violation](
	[ID] [int] IDENTITY(1,1) NOT NULL,
	[Loc] [int] NULL,
	[Elev] [int] NULL,
	[fdate] [datetime] NULL,
	[Status] [varchar](50) NULL,
	[Quote] [int] NULL,
	[Job] [int] NULL,
	[Ticket] [int] NULL,
	[Remarks] [text] NULL,
	[Custom1] [datetime] NULL,
	[Custom2] [datetime] NULL,
	[Custom3] [datetime] NULL,
	[Custom4] [datetime] NULL,
	[Custom5] [datetime] NULL,
	[Custom6] [datetime] NULL,
	[Custom7] [datetime] NULL,
	[Custom8] [datetime] NULL,
	[Custom9] [datetime] NULL,
	[Custom10] [datetime] NULL,
	[Custom11] [tinyint] NULL,
	[Custom12] [tinyint] NULL,
	[Custom13] [tinyint] NULL,
	[Custom14] [tinyint] NULL,
	[Custom15] [tinyint] NULL,
	[Custom16] [tinyint] NULL,
	[Custom17] [tinyint] NULL,
	[Custom18] [tinyint] NULL,
	[Custom19] [tinyint] NULL,
	[Custom20] [tinyint] NULL,
	[Custom21] [varchar](75) NULL,
	[Custom22] [varchar](75) NULL,
	[Custom23] [varchar](75) NULL,
	[Custom24] [varchar](75) NULL,
	[Custom25] [varchar](75) NULL,
	[Custom26] [varchar](75) NULL,
	[Custom27] [varchar](75) NULL,
	[Custom28] [varchar](75) NULL,
	[Custom29] [varchar](75) NULL,
	[Custom30] [varchar](75) NULL,
	[Name] [varchar](50) NULL,
	[Estimate] [int] NULL,
	[Remarks2] [varchar](8000) NULL,
	[idTestItem] [int] NULL,
	[Price] [numeric](30, 2) NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
 CONSTRAINT [PK_Violation] PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[VioStatus]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[VioStatus](
	[ID] [int] IDENTITY(1,1) NOT NULL,
	[Type] [varchar](50) NOT NULL,
	[Count] [smallint] NULL,
	[Remarks] [varchar](max) NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
 CONSTRAINT [PK_VioStatus] PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[VStatus]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[VStatus](
	[Status] [varchar](10) NOT NULL
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[VType]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[VType](
	[ID] [int] IDENTITY(1,1) NOT NULL,
	[Type] [varchar](15) NOT NULL,
	[Count] [smallint] NULL,
	[Remarks] [varchar](max) NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
 CONSTRAINT [PK_VType] PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Warehouse]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Warehouse](
	[TSID] [int] IDENTITY(1,1) NOT NULL,
	[ID] [varchar](5) NOT NULL,
	[Name] [varchar](25) NULL,
	[Type] [int] NULL,
	[Location] [int] NULL,
	[Remarks] [varchar](8000) NULL,
	[Count] [int] NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
 CONSTRAINT [PK_Warehouse] PRIMARY KEY CLUSTERED 
(
	[TSID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[ZipCode]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[ZipCode](
	[ID] [int] IDENTITY(1,1) NOT FOR REPLICATION NOT NULL,
	[Zip] [varchar](10) NULL,
	[City] [varchar](50) NULL,
	[State] [varchar](2) NULL,
PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Zone]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Zone](
	[ID] [int] NOT NULL,
	[Name] [varchar](50) NULL,
	[Surcharge] [numeric](30, 2) NULL,
	[Bonus] [numeric](30, 2) NULL,
	[Count] [int] NULL,
	[Remarks] [varchar](8000) NULL,
	[Price1] [numeric](30, 2) NULL,
	[Price2] [numeric](30, 2) NULL,
	[Price3] [numeric](30, 2) NULL,
	[Price4] [numeric](30, 2) NULL,
	[Price5] [numeric](30, 2) NULL,
	[IDistance] [numeric](30, 2) NULL,
	[ODistance] [numeric](30, 2) NULL,
	[Color] [smallint] NULL,
	[fDesc] [varchar](75) NULL,
	[Tax] [tinyint] NULL,
	[TFMID] [varchar](100) NOT NULL,
	[TFMSource] [varchar](10) NOT NULL,
 CONSTRAINT [PK_Zone] PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [Reporting].[Control]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [Reporting].[Control](
	[ControlID] [int] IDENTITY(1,1) NOT FOR REPLICATION NOT NULL,
	[UserID] [int] NULL,
	[Name] [varchar](255) NULL,
	[P13] [varchar](100) NULL,
	[PTB] [varchar](100) NULL,
	[PTBScope] [varchar](50) NULL,
	[PBS] [varchar](100) NULL,
	[PIS] [varchar](50) NULL,
	[PISC1] [varchar](50) NULL,
	[PISC2] [varchar](50) NULL,
	[PISC3] [varchar](50) NULL,
	[PISC4] [varchar](50) NULL,
	[PMC] [varchar](50) NULL,
	[PMCC1] [varchar](50) NULL,
	[PMCC2] [varchar](50) NULL,
	[PMCC3] [varchar](50) NULL,
	[PMCC4] [varchar](50) NULL,
	[PMCC5] [varchar](50) NULL,
	[PMCC6] [varchar](50) NULL,
	[Address] [varchar](255) NULL,
	[City] [varchar](50) NULL,
	[State] [varchar](2) NULL,
	[Zip] [varchar](10) NULL,
	[FedID] [varchar](15) NULL,
	[StateID] [varchar](15) NULL,
	[PCS] [varchar](50) NULL,
	[PCSC1] [varchar](50) NULL,
	[PCSC2] [varchar](50) NULL,
	[PCSC3] [varchar](50) NULL,
	[PCSC4] [varchar](50) NULL,
	[PCSC5] [varchar](50) NULL,
	[PCSC6] [varchar](50) NULL,
	[PCSC7] [varchar](50) NULL,
	[PCSC8] [varchar](50) NULL,
	[PCSC9] [varchar](50) NULL,
	[PCSC10] [varchar](50) NULL,
	[PCSC11] [varchar](50) NULL,
	[PCSC12] [varchar](50) NULL,
	[PCSC13] [varchar](50) NULL,
	[PCSC14] [varchar](50) NULL,
	[PCSC15] [varchar](50) NULL,
	[PCSC16] [varchar](50) NULL,
	[PCSC17] [varchar](50) NULL,
	[PCSC18] [varchar](50) NULL,
	[PCSC19] [varchar](50) NULL,
	[PCSC20] [varchar](50) NULL,
	[PCSC21] [varchar](50) NULL,
	[PCSC22] [varchar](50) NULL,
	[PCSC23] [varchar](50) NULL,
	[PCSC24] [varchar](50) NULL,
	[PCSC25] [varchar](50) NULL,
	[PCSC26] [varchar](50) NULL,
	[PCSC27] [varchar](50) NULL,
	[PCSC28] [varchar](50) NULL,
	[PCSC29] [varchar](50) NULL,
	[PCSC30] [varchar](50) NULL,
	[PCSC31] [varchar](50) NULL,
	[PCSC32] [varchar](50) NULL,
	[PCSC33] [varchar](50) NULL,
	[PCSC34] [varchar](50) NULL,
	[PSCS35] [varchar](50) NULL,
	[PCSC36] [varchar](50) NULL,
	[PCSC37] [varchar](50) NULL,
	[PCSC38] [varchar](50) NULL,
	[PCSC39] [varchar](50) NULL,
	[PCSC40] [varchar](50) NULL,
	[PCSC41] [varchar](50) NULL,
	[PCSC42] [varchar](50) NULL,
	[PCSC43] [varchar](50) NULL,
	[PCSC44] [varchar](50) NULL,
	[PCSC45] [varchar](50) NULL,
	[PCSC46] [varchar](50) NULL,
	[PCSC47] [varchar](50) NULL,
	[PCSC48] [varchar](50) NULL,
	[PCSC49] [varchar](50) NULL,
	[PSCS50] [varchar](50) NULL,
	[PCSC51] [varchar](50) NULL,
	[PCSC52] [varchar](50) NULL,
	[PCSC53] [varchar](50) NULL,
	[PCSC54] [varchar](50) NULL,
	[PCSC55] [varchar](50) NULL,
	[PCSC56] [varchar](50) NULL,
	[PCSC57] [varchar](50) NULL,
	[PCSC58] [varchar](50) NULL,
	[PCSC59] [varchar](50) NULL,
	[PCSC60] [varchar](50) NULL,
	[PCSC61] [varchar](50) NULL,
	[Title] [varchar](255) NULL,
 CONSTRAINT [PK_Control] PRIMARY KEY CLUSTERED 
(
	[ControlID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [Reporting].[PRCertified]    Script Date: 11/15/2021 11:34:42 AM ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [Reporting].[PRCertified](
	[PRCertifiedID] [int] IDENTITY(1,1) NOT FOR REPLICATION NOT NULL,
	[UserID] [int] NULL,
	[JobName] [varchar](75) NULL,
	[JobNo] [int] NULL,
	[LocID] [varchar](50) NULL,
	[LocTag] [varchar](255) NULL,
	[LocAddress] [varchar](255) NULL,
	[LocCityStZip] [varchar](255) NULL,
	[WageCat] [varchar](75) NULL,
	[Name] [varchar](255) NULL,
	[Address] [varchar](255) NULL,
	[CityStZip] [varchar](255) NULL,
	[SSN] [varchar](50) NULL,
	[Allowances] [int] NULL,
	[WeekEnd] [varchar](15) NULL,
	[OT1_1] [numeric](30, 2) NULL,
	[OT2_1] [numeric](30, 2) NULL,
	[OT3_1] [numeric](30, 2) NULL,
	[ST_1] [numeric](30, 2) NULL,
	[TT_1] [numeric](30, 2) NULL,
	[OT1_2] [numeric](30, 2) NULL,
	[OT2_2] [numeric](30, 2) NULL,
	[OT3_2] [numeric](30, 2) NULL,
	[ST_2] [numeric](30, 2) NULL,
	[TT_2] [numeric](30, 2) NULL,
	[OT1_3] [numeric](30, 2) NULL,
	[OT2_3] [numeric](30, 2) NULL,
	[OT3_3] [numeric](30, 2) NULL,
	[ST_3] [numeric](30, 2) NULL,
	[TT_3] [numeric](30, 2) NULL,
	[OT1_4] [numeric](30, 2) NULL,
	[OT2_4] [numeric](30, 2) NULL,
	[OT3_4] [numeric](30, 2) NULL,
	[ST_4] [numeric](30, 2) NULL,
	[TT_4] [numeric](30, 2) NULL,
	[OT1_5] [numeric](30, 2) NULL,
	[OT2_5] [numeric](30, 2) NULL,
	[OT3_5] [numeric](30, 2) NULL,
	[ST_5] [numeric](30, 2) NULL,
	[TT_5] [numeric](30, 2) NULL,
	[OT1_6] [numeric](30, 2) NULL,
	[OT2_6] [numeric](30, 2) NULL,
	[OT3_6] [numeric](30, 2) NULL,
	[ST_6] [numeric](30, 2) NULL,
	[TT_6] [numeric](30, 2) NULL,
	[OT1_7] [numeric](30, 2) NULL,
	[OT2_7] [numeric](30, 2) NULL,
	[OT3_7] [numeric](30, 2) NULL,
	[ST_7] [numeric](30, 2) NULL,
	[TT_7] [numeric](30, 2) NULL,
	[GrossST] [numeric](30, 2) NULL,
	[GrossTT] [numeric](30, 2) NULL,
	[GrossOT1] [numeric](30, 2) NULL,
	[GrossOT2] [numeric](30, 2) NULL,
	[GrossOT3] [numeric](30, 2) NULL,
	[GrossST_TT] [numeric](30, 2) NULL,
	[GrossOT] [numeric](30, 2) NULL,
	[GrossWeek] [numeric](30, 2) NULL,
	[FICA] [numeric](30, 2) NULL,
	[MEDI] [numeric](30, 2) NULL,
	[FICAMEDI] [numeric](30, 2) NULL,
	[FIT] [numeric](30, 2) NULL,
	[SIT] [numeric](30, 2) NULL,
	[Other] [numeric](30, 2) NULL,
	[TotalDed] [numeric](30, 2) NULL,
	[NetWeek] [numeric](30, 2) NULL,
	[REIMJE] [numeric](30, 2) NULL,
	[WELF] [numeric](30, 2) NULL,
	[SDI] [numeric](30, 2) NULL,
	[401K] [numeric](30, 2) NULL,
	[GARN] [numeric](30, 2) NULL,
	[Ref] [int] NULL,
	[WeekNo] [int] NULL,
	[CoName] [varchar](255) NULL,
	[NoActivity] [bit] NULL,
 CONSTRAINT [PK_PRCertified] PRIMARY KEY CLUSTERED 
(
	[PRCertifiedID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
GO
ALTER TABLE [dbo].[ActivityQueue] ADD  CONSTRAINT [DF_AQ_QTimeStamp]  DEFAULT (getdate()) FOR [QTimeStamp]
GO
ALTER TABLE [dbo].[ActivityQueue] ADD  CONSTRAINT [DF_AQ_QTID]  DEFAULT ((0)) FOR [QTID]
GO
ALTER TABLE [dbo].[ActivityQueue] ADD  CONSTRAINT [DF_AQ_QSID]  DEFAULT ((0)) FOR [QSID]
GO
ALTER TABLE [dbo].[ActivityQueue] ADD  CONSTRAINT [DF_AQ_TableID]  DEFAULT ((0)) FOR [TableID]
GO
ALTER TABLE [dbo].[ActivityQueue] ADD  CONSTRAINT [DF_AQ_KeyFieldID]  DEFAULT ((0)) FOR [KeyFieldID]
GO
ALTER TABLE [dbo].[ActivityQueue] ADD  CONSTRAINT [DF_AQ_RowID]  DEFAULT ((0)) FOR [RowID]
GO
ALTER TABLE [dbo].[ActivityQueue] ADD  CONSTRAINT [DF_AQ_UserID]  DEFAULT ((0)) FOR [UserID]
GO
ALTER TABLE [dbo].[ActivityQueue] ADD  CONSTRAINT [DF_AQ_QMessage]  DEFAULT ('') FOR [QMessage]
GO
ALTER TABLE [dbo].[ActivityQueue] ADD  CONSTRAINT [DF_AQ_QStateTimeStamp]  DEFAULT (getdate()) FOR [QStateTimeStamp]
GO
ALTER TABLE [dbo].[ActivityQueue] ADD  CONSTRAINT [DF_AQ_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[ActivityQueue] ADD  CONSTRAINT [DF_AQ_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[ActivityQueueState] ADD  CONSTRAINT [DF_AQS_QSComment]  DEFAULT ('') FOR [QSComment]
GO
ALTER TABLE [dbo].[ActivityQueueState] ADD  CONSTRAINT [DF_AQS_QSModifiedBy]  DEFAULT ((0)) FOR [QSModifiedBy]
GO
ALTER TABLE [dbo].[ActivityQueueState] ADD  CONSTRAINT [DF_AQS_QSModifiedOn]  DEFAULT (getdate()) FOR [QSModifiedOn]
GO
ALTER TABLE [dbo].[ActivityQueueState] ADD  CONSTRAINT [DF_AQS_QSInactive]  DEFAULT ((0)) FOR [QSInactive]
GO
ALTER TABLE [dbo].[ActivityQueueState] ADD  CONSTRAINT [DF_AQS_QSDeleted]  DEFAULT ((0)) FOR [QSDeleted]
GO
ALTER TABLE [dbo].[ActivityQueueState] ADD  CONSTRAINT [DF_AQS_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[ActivityQueueState] ADD  CONSTRAINT [DF_AQS_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[ActivityQueueType] ADD  CONSTRAINT [DF_AQType_QTComment]  DEFAULT ('') FOR [QTComment]
GO
ALTER TABLE [dbo].[ActivityQueueType] ADD  CONSTRAINT [DF_AQType_QTModifiedBy]  DEFAULT ((0)) FOR [QTModifiedBy]
GO
ALTER TABLE [dbo].[ActivityQueueType] ADD  CONSTRAINT [DF_AQType_QTModifiedOn]  DEFAULT (getdate()) FOR [QTModifiedOn]
GO
ALTER TABLE [dbo].[ActivityQueueType] ADD  CONSTRAINT [DF_AQType_QTInactive]  DEFAULT ((0)) FOR [QTInactive]
GO
ALTER TABLE [dbo].[ActivityQueueType] ADD  CONSTRAINT [DF_AQType_QTDeleted]  DEFAULT ((0)) FOR [QTDeleted]
GO
ALTER TABLE [dbo].[ActivityQueueType] ADD  CONSTRAINT [DF_AQType_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[ActivityQueueType] ADD  CONSTRAINT [DF_AQType_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[Audit] ADD  CONSTRAINT [DF_Audit_AuditSort]  DEFAULT ((0)) FOR [AuditSort]
GO
ALTER TABLE [dbo].[Audit] ADD  CONSTRAINT [DF_Audit_IsRequired]  DEFAULT ((0)) FOR [IsRequired]
GO
ALTER TABLE [dbo].[Audit] ADD  CONSTRAINT [DF_Audit_ModifiedOn]  DEFAULT (getdate()) FOR [ModifiedOn]
GO
ALTER TABLE [dbo].[Audit] ADD  CONSTRAINT [DF_Audit_Inactive]  DEFAULT ((0)) FOR [Inactive]
GO
ALTER TABLE [dbo].[Audit] ADD  CONSTRAINT [DF_Audit_Deleted]  DEFAULT ((0)) FOR [Deleted]
GO
ALTER TABLE [dbo].[Audit] ADD  CONSTRAINT [DF_Audit_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[Audit] ADD  CONSTRAINT [DF_Audit_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[AuditCategory] ADD  CONSTRAINT [DF_AuditCategory_AuditCategoryCode]  DEFAULT ('') FOR [AuditCategoryCode]
GO
ALTER TABLE [dbo].[AuditCategory] ADD  CONSTRAINT [DF_AuditCategory_IsRequired]  DEFAULT ((0)) FOR [IsRequired]
GO
ALTER TABLE [dbo].[AuditCategory] ADD  CONSTRAINT [DF_AuditCategory_ModifiedOn]  DEFAULT (getdate()) FOR [ModifiedOn]
GO
ALTER TABLE [dbo].[AuditCategory] ADD  CONSTRAINT [DF_AuditCategory_Inactive]  DEFAULT ((0)) FOR [Inactive]
GO
ALTER TABLE [dbo].[AuditCategory] ADD  CONSTRAINT [DF_AuditCategory_Deleted]  DEFAULT ((0)) FOR [Deleted]
GO
ALTER TABLE [dbo].[AuditCategory] ADD  CONSTRAINT [DF_AuditCategory_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[AuditCategory] ADD  CONSTRAINT [DF_AuditCategory_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[AuditDetail] ADD  CONSTRAINT [DF_AuditDetail_AuditDetailDescr]  DEFAULT ('') FOR [AuditDetailDescr]
GO
ALTER TABLE [dbo].[AuditDetail] ADD  CONSTRAINT [DF_AuditDetail_AuditDetailSort]  DEFAULT ((0)) FOR [AuditDetailSort]
GO
ALTER TABLE [dbo].[AuditDetail] ADD  CONSTRAINT [DF_AuditDetail_UseComments]  DEFAULT ((1)) FOR [UseComments]
GO
ALTER TABLE [dbo].[AuditDetail] ADD  CONSTRAINT [DF_AuditDetail_IsDefault]  DEFAULT ((0)) FOR [IsDefault]
GO
ALTER TABLE [dbo].[AuditDetail] ADD  CONSTRAINT [DF_AuditDetail_IndividualDetail]  DEFAULT ((0)) FOR [IndividualDetail]
GO
ALTER TABLE [dbo].[AuditDetail] ADD  CONSTRAINT [DF_AuditDetail_IsNonValue]  DEFAULT ((0)) FOR [IsNonValue]
GO
ALTER TABLE [dbo].[AuditDetail] ADD  CONSTRAINT [DF_AuditDetail_FlagPositive]  DEFAULT ((0)) FOR [FlagPositive]
GO
ALTER TABLE [dbo].[AuditDetail] ADD  CONSTRAINT [DF_AuditDetail_FlagNegative]  DEFAULT ((0)) FOR [FlagNegative]
GO
ALTER TABLE [dbo].[AuditDetail] ADD  CONSTRAINT [DF_AuditDetail_PicIsDrawing]  DEFAULT ((0)) FOR [PicIsDrawing]
GO
ALTER TABLE [dbo].[AuditDetail] ADD  CONSTRAINT [DF_AuditDetail_InternalValue]  DEFAULT ((0)) FOR [InternalValue]
GO
ALTER TABLE [dbo].[AuditDetail] ADD  CONSTRAINT [DF_AuditDetail_DisplayMeasureLabel]  DEFAULT ((1)) FOR [DisplayMeasureLabel]
GO
ALTER TABLE [dbo].[AuditDetail] ADD  CONSTRAINT [DF_AuditDetail_ModifiedOn]  DEFAULT (getdate()) FOR [ModifiedOn]
GO
ALTER TABLE [dbo].[AuditDetail] ADD  CONSTRAINT [DF_AuditDetail_Inactive]  DEFAULT ((0)) FOR [Inactive]
GO
ALTER TABLE [dbo].[AuditDetail] ADD  CONSTRAINT [DF_AuditDetail_Deleted]  DEFAULT ((0)) FOR [Deleted]
GO
ALTER TABLE [dbo].[AuditDetail] ADD  CONSTRAINT [DF_AuditDetail_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[AuditDetail] ADD  CONSTRAINT [DF_AuditDetail_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[AuditDetail] ADD  CONSTRAINT [DF_AuditDetail_IsFlatRate]  DEFAULT ((0)) FOR [IsFlatRate]
GO
ALTER TABLE [dbo].[AuditGroup] ADD  CONSTRAINT [DF_AuditGroup_AuditGroupCode]  DEFAULT ('') FOR [AuditGroupCode]
GO
ALTER TABLE [dbo].[AuditGroup] ADD  CONSTRAINT [DF_AuditGroup_AuditGroupSort]  DEFAULT ((0)) FOR [AuditGroupSort]
GO
ALTER TABLE [dbo].[AuditGroup] ADD  CONSTRAINT [DF_AuditGroup_IsRequired]  DEFAULT ((0)) FOR [IsRequired]
GO
ALTER TABLE [dbo].[AuditGroup] ADD  CONSTRAINT [DF_AuditGroup_ModifiedOn]  DEFAULT (getdate()) FOR [ModifiedOn]
GO
ALTER TABLE [dbo].[AuditGroup] ADD  CONSTRAINT [DF_AuditGroup_Inactive]  DEFAULT ((0)) FOR [Inactive]
GO
ALTER TABLE [dbo].[AuditGroup] ADD  CONSTRAINT [DF_AuditGroup_Deleted]  DEFAULT ((0)) FOR [Deleted]
GO
ALTER TABLE [dbo].[AuditGroup] ADD  CONSTRAINT [DF_AuditGroup_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[AuditGroup] ADD  CONSTRAINT [DF_AuditGroup_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[AuditGroup] ADD  CONSTRAINT [DF_AuditGroup_AttachToQuote]  DEFAULT ((0)) FOR [AttachToQuote]
GO
ALTER TABLE [dbo].[AuditGroup] ADD  CONSTRAINT [DF_Form_TicketCategory]  DEFAULT ('') FOR [TicketCategory]
GO
ALTER TABLE [dbo].[AuditResult] ADD  DEFAULT ((0)) FOR [TableID]
GO
ALTER TABLE [dbo].[AuditResult] ADD  DEFAULT ((0)) FOR [RowID]
GO
ALTER TABLE [dbo].[AuditResult] ADD  CONSTRAINT [DF_AuditResult_AuditGroupSort]  DEFAULT ((0)) FOR [AuditGroupSort]
GO
ALTER TABLE [dbo].[AuditResult] ADD  CONSTRAINT [DF_AuditResult_AuditCategorySort]  DEFAULT ((0)) FOR [AuditCategorySort]
GO
ALTER TABLE [dbo].[AuditResult] ADD  CONSTRAINT [DF_AuditResult_AuditSort]  DEFAULT ((0)) FOR [AuditSort]
GO
ALTER TABLE [dbo].[AuditResult] ADD  CONSTRAINT [DF_AuditResult_AuditDetailSort]  DEFAULT ((0)) FOR [AuditDetailSort]
GO
ALTER TABLE [dbo].[AuditResult] ADD  CONSTRAINT [DF_AuditResult_IndividualDetail]  DEFAULT ((0)) FOR [IndividualDetail]
GO
ALTER TABLE [dbo].[AuditResult] ADD  CONSTRAINT [DF_AuditResult_DisplayLabel]  DEFAULT ((1)) FOR [DisplayLabel]
GO
ALTER TABLE [dbo].[AuditResult] ADD  CONSTRAINT [DF_AuditResult_IsNumeric]  DEFAULT ((0)) FOR [IsNumeric]
GO
ALTER TABLE [dbo].[AuditResult] ADD  CONSTRAINT [DF_AuditResult_IsBoolean]  DEFAULT ((0)) FOR [IsBoolean]
GO
ALTER TABLE [dbo].[AuditResult] ADD  CONSTRAINT [DF_AuditResult_IsPic]  DEFAULT ((0)) FOR [IsPic]
GO
ALTER TABLE [dbo].[AuditResult] ADD  CONSTRAINT [DF_AuditResult_IsDataList]  DEFAULT ((0)) FOR [IsDataList]
GO
ALTER TABLE [dbo].[AuditResult] ADD  CONSTRAINT [DF_AuditResult_IsNonValue]  DEFAULT ((0)) FOR [IsNonValue]
GO
ALTER TABLE [dbo].[AuditResult] ADD  CONSTRAINT [DF_AuditResult_FlagPositive]  DEFAULT ((0)) FOR [FlagPositive]
GO
ALTER TABLE [dbo].[AuditResult] ADD  CONSTRAINT [DF_AuditResult_FlagNegative]  DEFAULT ((0)) FOR [FlagNegative]
GO
ALTER TABLE [dbo].[AuditResult] ADD  CONSTRAINT [DF_AuditResult_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[AuditResult] ADD  CONSTRAINT [DF_AuditResult_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[AuditUnitOfMeasure] ADD  CONSTRAINT [DF_AuditUnitOfMeasure_UnitOfMeasureCode]  DEFAULT ('') FOR [UnitOfMeasureCode]
GO
ALTER TABLE [dbo].[AuditUnitOfMeasure] ADD  CONSTRAINT [DF_AuditUnitOfMeasure_UnitOfMeasureLabel]  DEFAULT ('') FOR [UnitOfMeasureLabel]
GO
ALTER TABLE [dbo].[AuditUnitOfMeasure] ADD  CONSTRAINT [DF_AuditUnitOfMeasure_IsNumeric]  DEFAULT ((0)) FOR [IsNumeric]
GO
ALTER TABLE [dbo].[AuditUnitOfMeasure] ADD  CONSTRAINT [DF_AuditUnitOfMeasure_IsBoolean]  DEFAULT ((0)) FOR [IsBoolean]
GO
ALTER TABLE [dbo].[AuditUnitOfMeasure] ADD  CONSTRAINT [DF_AuditUnitOfMeasure_IsPic]  DEFAULT ((0)) FOR [IsPic]
GO
ALTER TABLE [dbo].[AuditUnitOfMeasure] ADD  CONSTRAINT [DF_AuditUnitOfMeasure_IsDataList]  DEFAULT ((0)) FOR [IsDataList]
GO
ALTER TABLE [dbo].[AuditUnitOfMeasure] ADD  CONSTRAINT [DF_AuditUnitOfMeasure_DataListType]  DEFAULT ((1)) FOR [DataListType]
GO
ALTER TABLE [dbo].[AuditUnitOfMeasure] ADD  CONSTRAINT [DF_AuditUnitOfMeasure_DisplayLabel]  DEFAULT ((0)) FOR [DisplayLabel]
GO
ALTER TABLE [dbo].[AuditUnitOfMeasure] ADD  CONSTRAINT [DF_AuditUnitOfMeasure_ModifiedOn]  DEFAULT (getdate()) FOR [ModifiedOn]
GO
ALTER TABLE [dbo].[AuditUnitOfMeasure] ADD  CONSTRAINT [DF_AuditUnitOfMeasure_Inactive]  DEFAULT ((0)) FOR [Inactive]
GO
ALTER TABLE [dbo].[AuditUnitOfMeasure] ADD  CONSTRAINT [DF_AuditUnitOfMeasure_Deleted]  DEFAULT ((0)) FOR [Deleted]
GO
ALTER TABLE [dbo].[AuditUnitOfMeasure] ADD  CONSTRAINT [DF_AuditUnitOfMeasure_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[AuditUnitOfMeasure] ADD  CONSTRAINT [DF_AuditUnitOfMeasure_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[Bank] ADD  DEFAULT ((0)) FOR [BankType]
GO
ALTER TABLE [dbo].[Bank] ADD  DEFAULT ((2)) FOR [ChartID]
GO
ALTER TABLE [dbo].[Branch] ADD  CONSTRAINT [DF_Branch_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[Branch] ADD  CONSTRAINT [DF_Branch_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[Category] ADD  CONSTRAINT [DF_Category_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[Category] ADD  CONSTRAINT [DF_Category_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[Chart] ADD  CONSTRAINT [DF_Chart_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[Chart] ADD  CONSTRAINT [DF_Chart_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[Codes] ADD  CONSTRAINT [DF_Codes_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[Codes] ADD  CONSTRAINT [DF_Codes_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[Contract] ADD  CONSTRAINT [DF_Contract_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[Contract] ADD  CONSTRAINT [DF_Contract_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[Control] ADD  DEFAULT ((0)) FOR [gps]
GO
ALTER TABLE [dbo].[Control] ADD  CONSTRAINT [DF_Control_VersionRevision]  DEFAULT ((0)) FOR [VersionRevision]
GO
ALTER TABLE [dbo].[Control] ADD  CONSTRAINT [DF_Control_ARInvoiceEmailText]  DEFAULT ('') FOR [ARInvoiceEmailText]
GO
ALTER TABLE [dbo].[Control] ADD  CONSTRAINT [DF_Control_TicketEmailText]  DEFAULT ('') FOR [TicketEmailText]
GO
ALTER TABLE [dbo].[Control] ADD  CONSTRAINT [DF_Control_TabletTicket]  DEFAULT ((0)) FOR [TabletTicket]
GO
ALTER TABLE [dbo].[Control] ADD  CONSTRAINT [sk2]  DEFAULT ('False') FOR [DefaultCredential]
GO
ALTER TABLE [dbo].[Control] ADD  CONSTRAINT [sk3]  DEFAULT ('False') FOR [EnableSsl]
GO
ALTER TABLE [dbo].[Control] ADD  DEFAULT ((0)) FOR [UseTSPortal]
GO
ALTER TABLE [dbo].[CreditCardTrans] ADD  CONSTRAINT [DF_CreditCardTrans_TransState]  DEFAULT ((0)) FOR [TransState]
GO
ALTER TABLE [dbo].[CreditCardTrans] ADD  CONSTRAINT [DF_CreditCardTrans_DateCreate]  DEFAULT (getdate()) FOR [DateCreated]
GO
ALTER TABLE [dbo].[CustPortalUser] ADD  DEFAULT ((0)) FOR [Owner]
GO
ALTER TABLE [dbo].[CustPortalUser] ADD  DEFAULT ((0)) FOR [Status]
GO
ALTER TABLE [dbo].[CustPortalUser] ADD  DEFAULT ((0)) FOR [Access]
GO
ALTER TABLE [dbo].[CustPortalUser] ADD  DEFAULT ((0)) FOR [Ticket]
GO
ALTER TABLE [dbo].[CustPortalUser] ADD  DEFAULT ((0)) FOR [History]
GO
ALTER TABLE [dbo].[CustPortalUser] ADD  DEFAULT ((0)) FOR [Invoice]
GO
ALTER TABLE [dbo].[CustPortalUser] ADD  DEFAULT ((0)) FOR [Quote]
GO
ALTER TABLE [dbo].[CustPortalUser] ADD  DEFAULT ((0)) FOR [Service]
GO
ALTER TABLE [dbo].[CustPortalUser] ADD  DEFAULT ((0)) FOR [Approve]
GO
ALTER TABLE [dbo].[CustPortalUser] ADD  DEFAULT ((0)) FOR [Request]
GO
ALTER TABLE [dbo].[CustPortalUser] ADD  DEFAULT ((0)) FOR [Safety]
GO
ALTER TABLE [dbo].[CustPortalUser] ADD  DEFAULT ((0)) FOR [Dispatch]
GO
ALTER TABLE [dbo].[Diagnostic] ADD  CONSTRAINT [DF_Diagnostic_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[Diagnostic] ADD  CONSTRAINT [DF_Diagnostic_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[Elev] ADD  CONSTRAINT [DF_Elev_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[Elev] ADD  CONSTRAINT [DF_Elev_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[ElevatorSpec] ADD  CONSTRAINT [DF_ElevatorSpec_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[ElevatorSpec] ADD  CONSTRAINT [DF_ElevatorSpec_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[ElevT] ADD  CONSTRAINT [DF_ElevT_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[ElevT] ADD  CONSTRAINT [DF_ElevT_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[ElevTItem] ADD  CONSTRAINT [DF_ElevTItem_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[ElevTItem] ADD  CONSTRAINT [DF_ElevTItem_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[Emp] ADD  CONSTRAINT [DF_Emp_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[Emp] ADD  CONSTRAINT [DF_Emp_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[FlatRates] ADD  CONSTRAINT [DF_FlatRates_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[FlatRates] ADD  CONSTRAINT [DF_FlatRates_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[Inv] ADD  CONSTRAINT [DF_Inv_USA]  DEFAULT ((0)) FOR [USA]
GO
ALTER TABLE [dbo].[Inv] ADD  CONSTRAINT [DF_Inv_Coupon]  DEFAULT ((0)) FOR [Coupon]
GO
ALTER TABLE [dbo].[Inv] ADD  CONSTRAINT [DF_Inv_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[Inv] ADD  CONSTRAINT [DF_Inv_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[Invoice] ADD  DEFAULT ((0)) FOR [PSTOnlyAmount]
GO
ALTER TABLE [dbo].[Invoice] ADD  CONSTRAINT [DF_Invoice_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[Invoice] ADD  CONSTRAINT [DF_Invoice_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[Invoice] ADD  DEFAULT ((0)) FOR [EMailStatus]
GO
ALTER TABLE [dbo].[InvoiceI] ADD  DEFAULT ((0)) FOR [PSTTax]
GO
ALTER TABLE [dbo].[InvoiceI] ADD  DEFAULT ((0)) FOR [savingsAmount]
GO
ALTER TABLE [dbo].[InvoiceI] ADD  CONSTRAINT [DF_InvoiceI_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[InvoiceI] ADD  CONSTRAINT [DF_InvoiceI_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[InvoiceI] ADD  CONSTRAINT [DF_InvoiceI_USA]  DEFAULT ((0)) FOR [USA]
GO
ALTER TABLE [dbo].[InvoiceI] ADD  CONSTRAINT [DF_InvoiceI_Coupon]  DEFAULT ((0)) FOR [Coupon]
GO
ALTER TABLE [dbo].[IType] ADD  CONSTRAINT [DF_IType_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[IType] ADD  CONSTRAINT [DF_IType_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[Job] ADD  CONSTRAINT [DF_Job_Comm]  DEFAULT ((0)) FOR [Comm]
GO
ALTER TABLE [dbo].[Job] ADD  CONSTRAINT [DF_Job_EN]  DEFAULT ((0)) FOR [EN]
GO
ALTER TABLE [dbo].[Job] ADD  CONSTRAINT [DF_Job_Certified]  DEFAULT ((0)) FOR [Certified]
GO
ALTER TABLE [dbo].[Job] ADD  CONSTRAINT [DF_Job_Apprentice]  DEFAULT ((0)) FOR [Apprentice]
GO
ALTER TABLE [dbo].[Job] ADD  CONSTRAINT [DF_Job_UseCat]  DEFAULT ((0)) FOR [UseCat]
GO
ALTER TABLE [dbo].[Job] ADD  CONSTRAINT [DF_Job_UseDed]  DEFAULT ((0)) FOR [UseDed]
GO
ALTER TABLE [dbo].[Job] ADD  CONSTRAINT [DF_Job_Billrate]  DEFAULT ((0)) FOR [BillRate]
GO
ALTER TABLE [dbo].[Job] ADD  CONSTRAINT [DF_Job_Markup]  DEFAULT ((0)) FOR [Markup]
GO
ALTER TABLE [dbo].[Job] ADD  CONSTRAINT [DF_Job_Ptype]  DEFAULT ((0)) FOR [PType]
GO
ALTER TABLE [dbo].[Job] ADD  CONSTRAINT [DF_Job_Charge]  DEFAULT ((0)) FOR [Charge]
GO
ALTER TABLE [dbo].[Job] ADD  CONSTRAINT [DF_Job_Amount]  DEFAULT ((0)) FOR [Amount]
GO
ALTER TABLE [dbo].[Job] ADD  CONSTRAINT [DF_Job_GandA]  DEFAULT ((0)) FOR [GandA]
GO
ALTER TABLE [dbo].[Job] ADD  CONSTRAINT [DF_Job_OHLabor]  DEFAULT ((0)) FOR [OHLabor]
GO
ALTER TABLE [dbo].[Job] ADD  CONSTRAINT [DF_Job_LastOH]  DEFAULT ((0)) FOR [LastOH]
GO
ALTER TABLE [dbo].[Job] ADD  CONSTRAINT [DF_Job_ETC]  DEFAULT ((0)) FOR [etc]
GO
ALTER TABLE [dbo].[Job] ADD  CONSTRAINT [DF_Job_ETCModifier]  DEFAULT ((0)) FOR [ETCModifier]
GO
ALTER TABLE [dbo].[Job] ADD  CONSTRAINT [DF_SPHandle]  DEFAULT ((0)) FOR [SPHandle]
GO
ALTER TABLE [dbo].[Job] ADD  CONSTRAINT [DF_Job_TFMCustom1]  DEFAULT ('') FOR [TFMCustom1]
GO
ALTER TABLE [dbo].[Job] ADD  CONSTRAINT [DF_Job_TFMCustom2]  DEFAULT ('') FOR [TFMCustom2]
GO
ALTER TABLE [dbo].[Job] ADD  CONSTRAINT [DF_Job_TFMCustom3]  DEFAULT ('') FOR [TFMCustom3]
GO
ALTER TABLE [dbo].[Job] ADD  CONSTRAINT [DF_Job_TechAlert]  DEFAULT ('') FOR [TechAlert]
GO
ALTER TABLE [dbo].[Job] ADD  CONSTRAINT [DF_Job_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[Job] ADD  CONSTRAINT [DF_Job_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[JobI] ADD  CONSTRAINT [DF_JobI_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[JobI] ADD  CONSTRAINT [DF_JobI_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[JobT] ADD  CONSTRAINT [DF_JobT_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[JobT] ADD  CONSTRAINT [DF_JobT_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[JobTItem] ADD  CONSTRAINT [DF_JobTItem_Comm]  DEFAULT ((0)) FOR [Comm]
GO
ALTER TABLE [dbo].[JobTItem] ADD  CONSTRAINT [DF_JobTItem_Modifier]  DEFAULT ((0)) FOR [Modifier]
GO
ALTER TABLE [dbo].[JobTItem] ADD  CONSTRAINT [DF_JobTItem_ETC]  DEFAULT ((0)) FOR [ETC]
GO
ALTER TABLE [dbo].[JobTItem] ADD  CONSTRAINT [DF_JobTItem_ETCMod]  DEFAULT ((0)) FOR [ETCMod]
GO
ALTER TABLE [dbo].[JobTItem] ADD  CONSTRAINT [DF_JobTItem_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[JobTItem] ADD  CONSTRAINT [DF_JobTItem_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[JobType] ADD  CONSTRAINT [DF_JobType_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[JobType] ADD  CONSTRAINT [DF_JobType_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[ListConfig] ADD  CONSTRAINT [DF_ListConfig_IsDefault]  DEFAULT ((0)) FOR [IsDefault]
GO
ALTER TABLE [dbo].[ListConfig] ADD  CONSTRAINT [DF_ListConfig_StatusOrder]  DEFAULT ((-1)) FOR [StatusOrder]
GO
ALTER TABLE [dbo].[ListConfig] ADD  CONSTRAINT [DF_ListConfig_ShowAlert]  DEFAULT ((0)) FOR [ShowAlert]
GO
ALTER TABLE [dbo].[LoadTest] ADD  CONSTRAINT [DF_LoadTest_Cat]  DEFAULT ('') FOR [Cat]
GO
ALTER TABLE [dbo].[LoadTest] ADD  CONSTRAINT [DF_LoadTest_fDesc]  DEFAULT ('') FOR [fDesc]
GO
ALTER TABLE [dbo].[LoadTest] ADD  CONSTRAINT [DF_LoadTest_NextDateCalcMode2]  DEFAULT ((0)) FOR [NextDateCalcMode]
GO
ALTER TABLE [dbo].[LoadTest] ADD  CONSTRAINT [DF_LoadTest_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[LoadTest] ADD  CONSTRAINT [DF_LoadTest_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[LoadTestItem] ADD  CONSTRAINT [DF_LoadTest_Custom1]  DEFAULT ('') FOR [Custom1]
GO
ALTER TABLE [dbo].[LoadTestItem] ADD  CONSTRAINT [DF_LoadTest_Custom2]  DEFAULT ('') FOR [Custom2]
GO
ALTER TABLE [dbo].[LoadTestItem] ADD  CONSTRAINT [DF_LoadTest_Custom3]  DEFAULT ('') FOR [Custom3]
GO
ALTER TABLE [dbo].[LoadTestItem] ADD  CONSTRAINT [DF_LoadTest_Custom4]  DEFAULT ('') FOR [Custom4]
GO
ALTER TABLE [dbo].[LoadTestItem] ADD  CONSTRAINT [DF_LoadTest_Custom5]  DEFAULT ('') FOR [Custom5]
GO
ALTER TABLE [dbo].[LoadTestItem] ADD  CONSTRAINT [DF_LoadTest_Custom6]  DEFAULT ('') FOR [Custom6]
GO
ALTER TABLE [dbo].[LoadTestItem] ADD  CONSTRAINT [DF_LoadTest_Custom7]  DEFAULT ('') FOR [Custom7]
GO
ALTER TABLE [dbo].[LoadTestItem] ADD  CONSTRAINT [DF_LoadTest_Custom8]  DEFAULT ('') FOR [Custom8]
GO
ALTER TABLE [dbo].[LoadTestItem] ADD  CONSTRAINT [DF_LoadTest_Custom9]  DEFAULT ('') FOR [Custom9]
GO
ALTER TABLE [dbo].[LoadTestItem] ADD  CONSTRAINT [DF_LoadTest_Custom10]  DEFAULT ('') FOR [Custom10]
GO
ALTER TABLE [dbo].[LoadTestItem] ADD  CONSTRAINT [DF_LoadTest_Custom11]  DEFAULT ('') FOR [Custom11]
GO
ALTER TABLE [dbo].[LoadTestItem] ADD  CONSTRAINT [DF_LoadTest_Custom12]  DEFAULT ('') FOR [Custom12]
GO
ALTER TABLE [dbo].[LoadTestItem] ADD  CONSTRAINT [DF_LoadTest_Custom13]  DEFAULT ('') FOR [Custom13]
GO
ALTER TABLE [dbo].[LoadTestItem] ADD  CONSTRAINT [DF_LoadTest_Custom14]  DEFAULT ('') FOR [Custom14]
GO
ALTER TABLE [dbo].[LoadTestItem] ADD  CONSTRAINT [DF_LoadTest_Custom15]  DEFAULT ('') FOR [Custom15]
GO
ALTER TABLE [dbo].[LoadTestItem] ADD  CONSTRAINT [DF_LoadTest_Extra]  DEFAULT ('') FOR [Extra]
GO
ALTER TABLE [dbo].[LoadTestItem] ADD  CONSTRAINT [DF_LoadTestItem_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[LoadTestItem] ADD  CONSTRAINT [DF_LoadTestItem_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[Loc] ADD  CONSTRAINT [DF_Loc_Route]  DEFAULT ((0)) FOR [Route]
GO
ALTER TABLE [dbo].[Loc] ADD  CONSTRAINT [DF_Loc_Zone]  DEFAULT ((0)) FOR [Zone]
GO
ALTER TABLE [dbo].[Loc] ADD  CONSTRAINT [DF_Loc_Terr]  DEFAULT ((0)) FOR [Terr]
GO
ALTER TABLE [dbo].[Loc] ADD  CONSTRAINT [DF_Loc_idRolCustomContact]  DEFAULT ((0)) FOR [idRolCustomContact]
GO
ALTER TABLE [dbo].[Loc] ADD  CONSTRAINT [DF_Loc_Email]  DEFAULT ((0)) FOR [Email]
GO
ALTER TABLE [dbo].[Loc] ADD  CONSTRAINT [DF_Loc_PrintInvoice]  DEFAULT ((0)) FOR [PrintInvoice]
GO
ALTER TABLE [dbo].[Loc] ADD  CONSTRAINT [DF_Loc_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[Loc] ADD  CONSTRAINT [DF_Loc_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[LocType] ADD  CONSTRAINT [DF_LocType_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[LocType] ADD  CONSTRAINT [DF_LocType_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[Log] ADD  CONSTRAINT [DF_Log_CreatedStamp]  DEFAULT (getdate()) FOR [CreatedStamp]
GO
ALTER TABLE [dbo].[Log] ADD  CONSTRAINT [DF_Log_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[Log] ADD  CONSTRAINT [DF_Log_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[Log2] ADD  CONSTRAINT [DF_Log2_CreatedStamp]  DEFAULT (getdate()) FOR [CreatedStamp]
GO
ALTER TABLE [dbo].[LType] ADD  CONSTRAINT [DF_LType_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[LType] ADD  CONSTRAINT [DF_LType_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[OpenAP] ADD  CONSTRAINT [DF_OpenAP_Selected]  DEFAULT ((0)) FOR [Selected]
GO
ALTER TABLE [dbo].[OpenAP] ADD  CONSTRAINT [DF_OpenAP_Disc]  DEFAULT ((0)) FOR [Disc]
GO
ALTER TABLE [dbo].[OType] ADD  CONSTRAINT [DF_OType_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[OType] ADD  CONSTRAINT [DF_OType_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[Owner] ADD  CONSTRAINT [DF_Owner_NeedsFullSync]  DEFAULT ((0)) FOR [NeedsFullSync]
GO
ALTER TABLE [dbo].[Owner] ADD  DEFAULT ((0)) FOR [Approve]
GO
ALTER TABLE [dbo].[Owner] ADD  DEFAULT ((0)) FOR [InvoiceO]
GO
ALTER TABLE [dbo].[Owner] ADD  DEFAULT ((0)) FOR [Quote]
GO
ALTER TABLE [dbo].[Owner] ADD  DEFAULT ((0)) FOR [QuoteX]
GO
ALTER TABLE [dbo].[Owner] ADD  DEFAULT ((0)) FOR [Dispatch]
GO
ALTER TABLE [dbo].[Owner] ADD  DEFAULT ((0)) FOR [Service]
GO
ALTER TABLE [dbo].[Owner] ADD  DEFAULT ((0)) FOR [Pay]
GO
ALTER TABLE [dbo].[Owner] ADD  DEFAULT ((0)) FOR [Safety]
GO
ALTER TABLE [dbo].[Owner] ADD  CONSTRAINT [DF_Owner_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[Owner] ADD  CONSTRAINT [DF_Owner_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[PDATicketSignature] ADD  CONSTRAINT [DF_PDATicketSignature_SignatureType]  DEFAULT ('') FOR [SignatureType]
GO
ALTER TABLE [dbo].[PDATicketSignature] ADD  CONSTRAINT [DF_PDATicketSignature_SignatureText]  DEFAULT ('') FOR [SignatureText]
GO
ALTER TABLE [dbo].[PDATicketSignature] ADD  CONSTRAINT [DF_PDATicketSignature_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[PDATicketSignature] ADD  CONSTRAINT [DF_PDATicketSignature_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[Phone] ADD  DEFAULT ((0)) FOR [EmailRecInvoice]
GO
ALTER TABLE [dbo].[Phone] ADD  DEFAULT ((0)) FOR [EmailRecTicket]
GO
ALTER TABLE [dbo].[Phone] ADD  DEFAULT ((0)) FOR [EmailRecPO]
GO
ALTER TABLE [dbo].[Phone] ADD  DEFAULT ((0)) FOR [EmailRecQuote]
GO
ALTER TABLE [dbo].[Phone] ADD  DEFAULT ('') FOR [Remarks]
GO
ALTER TABLE [dbo].[Phone] ADD  CONSTRAINT [DF_Phone_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[Phone] ADD  CONSTRAINT [DF_Phone_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[PJ] ADD  CONSTRAINT [DF_PJ_IDate]  DEFAULT (CONVERT([date],getdate(),0)) FOR [IDate]
GO
ALTER TABLE [dbo].[PO] ADD  CONSTRAINT [DF_PO_PO]  DEFAULT ((0)) FOR [PO]
GO
ALTER TABLE [dbo].[PO] ADD  CONSTRAINT [DF_PO_Amount]  DEFAULT ((0)) FOR [Amount]
GO
ALTER TABLE [dbo].[PO] ADD  DEFAULT ('') FOR [InUseby]
GO
ALTER TABLE [dbo].[PO] ADD  CONSTRAINT [DF_PO_Ticket]  DEFAULT ((0)) FOR [Ticket]
GO
ALTER TABLE [dbo].[PO] ADD  CONSTRAINT [DF_PO_QuoteID]  DEFAULT ((0)) FOR [QuoteID]
GO
ALTER TABLE [dbo].[PO] ADD  CONSTRAINT [DF_PO_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[PO] ADD  CONSTRAINT [DF_PO_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[POItem] ADD  CONSTRAINT [DF_POItem_Quan]  DEFAULT ((0)) FOR [Quan]
GO
ALTER TABLE [dbo].[POItem] ADD  CONSTRAINT [DF_POItem_Amount]  DEFAULT ((0)) FOR [Amount]
GO
ALTER TABLE [dbo].[POItem] ADD  CONSTRAINT [DF_POItem_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[POItem] ADD  CONSTRAINT [DF_POItem_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[PRDedItem] ADD  CONSTRAINT [DF_PRDedItem_EmpRate]  DEFAULT ((0)) FOR [EmpRate]
GO
ALTER TABLE [dbo].[PRDedItem] ADD  CONSTRAINT [DF_PRDedItem_EmpTop]  DEFAULT ((0)) FOR [EmpTop]
GO
ALTER TABLE [dbo].[PRDedItem] ADD  CONSTRAINT [DF_PRDedItem_CompRate]  DEFAULT ((0)) FOR [CompRate]
GO
ALTER TABLE [dbo].[PRDedItem] ADD  CONSTRAINT [DF_PRDedItem_CompTop]  DEFAULT ((0)) FOR [CompTop]
GO
ALTER TABLE [dbo].[PRHistory] ADD  DEFAULT ((0)) FOR [Vac]
GO
ALTER TABLE [dbo].[PRHistory] ADD  DEFAULT ((0)) FOR [HVac]
GO
ALTER TABLE [dbo].[PRHistory] ADD  DEFAULT ((0)) FOR [HVacAccrued]
GO
ALTER TABLE [dbo].[PRHistory] ADD  DEFAULT ((0)) FOR [Sick]
GO
ALTER TABLE [dbo].[PRHistory] ADD  DEFAULT ((0)) FOR [HSick]
GO
ALTER TABLE [dbo].[PRHistory] ADD  DEFAULT ((0)) FOR [HSickAccrued]
GO
ALTER TABLE [dbo].[PRHistory] ADD  DEFAULT ((0)) FOR [VThis]
GO
ALTER TABLE [dbo].[PRHistory] ADD  DEFAULT ((0)) FOR [VLast]
GO
ALTER TABLE [dbo].[PRHistory] ADD  DEFAULT ((0)) FOR [VRate]
GO
ALTER TABLE [dbo].[PRHistory] ADD  DEFAULT ((0)) FOR [Box52]
GO
ALTER TABLE [dbo].[Prospect] ADD  CONSTRAINT [DF_Prospect_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[Prospect] ADD  CONSTRAINT [DF_Prospect_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[PROther] ADD  CONSTRAINT [DF_PROther_Rate]  DEFAULT ((0)) FOR [Rate]
GO
ALTER TABLE [dbo].[PROther] ADD  DEFAULT ((0)) FOR [Sick]
GO
ALTER TABLE [dbo].[PRReg] ADD  DEFAULT ((0)) FOR [CompMedi]
GO
ALTER TABLE [dbo].[PRReg] ADD  DEFAULT ((0)) FOR [WMediOverTH]
GO
ALTER TABLE [dbo].[PRReg] ADD  DEFAULT ((0)) FOR [Sick]
GO
ALTER TABLE [dbo].[PRReg] ADD  DEFAULT ((0)) FOR [YSick]
GO
ALTER TABLE [dbo].[PRReg] ADD  DEFAULT ((0)) FOR [WSick]
GO
ALTER TABLE [dbo].[PRReg] ADD  DEFAULT ((0)) FOR [HSick]
GO
ALTER TABLE [dbo].[PRReg] ADD  DEFAULT ((0)) FOR [HYSick]
GO
ALTER TABLE [dbo].[PRReg] ADD  DEFAULT ((0)) FOR [HSickAccrued]
GO
ALTER TABLE [dbo].[PRReg] ADD  DEFAULT ((0)) FOR [HYSickAccrued]
GO
ALTER TABLE [dbo].[PRReg] ADD  DEFAULT ((0)) FOR [HVacAccrued]
GO
ALTER TABLE [dbo].[PRReg] ADD  DEFAULT ((0)) FOR [HYVacAccrued]
GO
ALTER TABLE [dbo].[PRReg_Temp] ADD  DEFAULT ((0)) FOR [CompMedi]
GO
ALTER TABLE [dbo].[PRReg_Temp] ADD  DEFAULT ((0)) FOR [WMediOverTH]
GO
ALTER TABLE [dbo].[PRReg_Temp] ADD  DEFAULT ((0)) FOR [Sick]
GO
ALTER TABLE [dbo].[PRReg_Temp] ADD  DEFAULT ((0)) FOR [YSick]
GO
ALTER TABLE [dbo].[PRReg_Temp] ADD  DEFAULT ((0)) FOR [WSick]
GO
ALTER TABLE [dbo].[PRReg_Temp] ADD  DEFAULT ((0)) FOR [HSick]
GO
ALTER TABLE [dbo].[PRReg_Temp] ADD  DEFAULT ((0)) FOR [HYSick]
GO
ALTER TABLE [dbo].[PRReg_Temp] ADD  DEFAULT ((0)) FOR [HSickAccrued]
GO
ALTER TABLE [dbo].[PRReg_Temp] ADD  DEFAULT ((0)) FOR [HYSickAccrued]
GO
ALTER TABLE [dbo].[PRReg_Temp] ADD  DEFAULT ((0)) FOR [HVacAccrued]
GO
ALTER TABLE [dbo].[PRReg_Temp] ADD  DEFAULT ((0)) FOR [HYVacAccrued]
GO
ALTER TABLE [dbo].[PRWage] ADD  DEFAULT ((0)) FOR [Sick]
GO
ALTER TABLE [dbo].[PRWage] ADD  CONSTRAINT [DF_PRWage_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[PRWage] ADD  CONSTRAINT [DF_PRWage_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[PRWageItem] ADD  CONSTRAINT [DF_PRWageItem_Reg]  DEFAULT ((0)) FOR [Reg]
GO
ALTER TABLE [dbo].[PRWageItem] ADD  CONSTRAINT [DF_PRWageItem_OT]  DEFAULT ((0)) FOR [OT]
GO
ALTER TABLE [dbo].[PRWageItem] ADD  CONSTRAINT [DF_PRWageItem_DT]  DEFAULT ((0)) FOR [DT]
GO
ALTER TABLE [dbo].[PRWageItem] ADD  CONSTRAINT [DF_PRWageItem_TT]  DEFAULT ((0)) FOR [TT]
GO
ALTER TABLE [dbo].[PRWageItem] ADD  CONSTRAINT [DF_PRWageItem_YTD]  DEFAULT ((0)) FOR [YTD]
GO
ALTER TABLE [dbo].[PRWageItem] ADD  CONSTRAINT [DF_PRWageItem_YTDH]  DEFAULT ((0)) FOR [YTDH]
GO
ALTER TABLE [dbo].[PRWageItem] ADD  CONSTRAINT [DF_PRWageItem_OYTD]  DEFAULT ((0)) FOR [OYTD]
GO
ALTER TABLE [dbo].[PRWageItem] ADD  CONSTRAINT [DF_PRWageItem_OYTDH]  DEFAULT ((0)) FOR [OYTDH]
GO
ALTER TABLE [dbo].[PRWageItem] ADD  CONSTRAINT [DF_PRWageItem_DYTD]  DEFAULT ((0)) FOR [DYTD]
GO
ALTER TABLE [dbo].[PRWageItem] ADD  CONSTRAINT [DF_PRWageItem_DYTDH]  DEFAULT ((0)) FOR [DYTDH]
GO
ALTER TABLE [dbo].[PRWageItem] ADD  CONSTRAINT [DF_PRWageItem_TYTD]  DEFAULT ((0)) FOR [TYTD]
GO
ALTER TABLE [dbo].[PRWageItem] ADD  CONSTRAINT [DF_PRWageItem_TYTDH]  DEFAULT ((0)) FOR [TYTDH]
GO
ALTER TABLE [dbo].[PRWageItem] ADD  CONSTRAINT [DF_PRWageItem_NT]  DEFAULT ((0)) FOR [NT]
GO
ALTER TABLE [dbo].[PRWageItem] ADD  CONSTRAINT [DF_PRWageItem_NYTD]  DEFAULT ((0)) FOR [NYTD]
GO
ALTER TABLE [dbo].[PRWageItem] ADD  CONSTRAINT [DF_PRWageItem_NYTDH]  DEFAULT ((0)) FOR [NYTDH]
GO
ALTER TABLE [dbo].[PRWageItem] ADD  DEFAULT ((0)) FOR [Sick]
GO
ALTER TABLE [dbo].[PType] ADD  CONSTRAINT [DF_PType_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[PType] ADD  CONSTRAINT [DF_PType_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[QStatus] ADD  CONSTRAINT [DF_QStatus_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[QStatus] ADD  CONSTRAINT [DF_QStatus_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[Quote] ADD  DEFAULT ((0)) FOR [PSTOnlyAmount]
GO
ALTER TABLE [dbo].[Quote] ADD  CONSTRAINT [DF_Quote_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[Quote] ADD  CONSTRAINT [DF_Quote_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[Quote] ADD  CONSTRAINT [DF_Quote_GroupMarkup]  DEFAULT ((0)) FOR [GroupMarkup]
GO
ALTER TABLE [dbo].[QuoteI] ADD  DEFAULT ((0)) FOR [PSTTax]
GO
ALTER TABLE [dbo].[QuoteI] ADD  CONSTRAINT [DF_QuoteI_ManualMarkup]  DEFAULT ((0)) FOR [ManualMarkup]
GO
ALTER TABLE [dbo].[QuoteI] ADD  CONSTRAINT [DF_QuoteI_GroupMarkup]  DEFAULT ((0)) FOR [GroupMarkup]
GO
ALTER TABLE [dbo].[QuoteI] ADD  CONSTRAINT [DF_QuoteI_VendorID]  DEFAULT ((0)) FOR [VendorID]
GO
ALTER TABLE [dbo].[QuoteI] ADD  CONSTRAINT [DF_QuoteI_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[QuoteI] ADD  CONSTRAINT [DF_QuoteI_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[QuoteI] ADD  CONSTRAINT [DF_QuoteI_USA]  DEFAULT ((0)) FOR [USA]
GO
ALTER TABLE [dbo].[QuoteI] ADD  CONSTRAINT [DF_QuoteI_Coupon]  DEFAULT ((0)) FOR [Coupon]
GO
ALTER TABLE [dbo].[QuoteI] ADD  CONSTRAINT [DF_QuoteI_Accepted]  DEFAULT ((1)) FOR [Accepted]
GO
ALTER TABLE [dbo].[QuoteI] ADD  CONSTRAINT [DF_QuoteI_TurnDownReason]  DEFAULT ('') FOR [TurnDownReason]
GO
ALTER TABLE [dbo].[Rol] ADD  CONSTRAINT [DF_Rol_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[Rol] ADD  CONSTRAINT [DF_Rol_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[Route] ADD  CONSTRAINT [DF_Route_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[Route] ADD  CONSTRAINT [DF_Route_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[rpt_941] ADD  CONSTRAINT [DF_PR941_CreatedStamp]  DEFAULT (getdate()) FOR [RunDate]
GO
ALTER TABLE [dbo].[rpt_941] ADD  CONSTRAINT [DF_PR941_TaxLiability_Month1]  DEFAULT ((0)) FOR [TaxLiability_Month1]
GO
ALTER TABLE [dbo].[rpt_941] ADD  CONSTRAINT [DF_PR941_TaxLiability_Month2]  DEFAULT ((0)) FOR [TaxLiability_Month2]
GO
ALTER TABLE [dbo].[rpt_941] ADD  CONSTRAINT [DF_PR941_TaxLiability_Month3]  DEFAULT ((0)) FOR [TaxLiability_Month3]
GO
ALTER TABLE [dbo].[rpt_941] ADD  DEFAULT ((0)) FOR [AdjustedMonth]
GO
ALTER TABLE [dbo].[rpt_941] ADD  CONSTRAINT [DF_PR941_TaxLiability_Quarter]  DEFAULT ((0)) FOR [TaxLiability_Quarter]
GO
ALTER TABLE [dbo].[rpt_941] ADD  DEFAULT ((0)) FOR [MEDIOTH_Wages]
GO
ALTER TABLE [dbo].[rpt_941] ADD  DEFAULT ((0)) FOR [MEDIOTH_Due]
GO
ALTER TABLE [dbo].[rpt_941] ADD  DEFAULT ((0)) FOR [MEDIOTH_Withheld]
GO
ALTER TABLE [dbo].[rpt_941B] ADD  CONSTRAINT [DF_PR941B_CreatedStamp]  DEFAULT (getdate()) FOR [RunDate]
GO
ALTER TABLE [dbo].[SalesSource] ADD  CONSTRAINT [DF_SalesSource_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[SalesSource] ADD  CONSTRAINT [DF_SalesSource_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[STax] ADD  CONSTRAINT [DF_STax_GL]  DEFAULT ((9)) FOR [GL]
GO
ALTER TABLE [dbo].[STax] ADD  CONSTRAINT [DF_STax_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[STax] ADD  CONSTRAINT [DF_STax_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[SType] ADD  CONSTRAINT [DF_SType_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[SType] ADD  CONSTRAINT [DF_SType_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[tblUser] ADD  CONSTRAINT [DF_tblUser_ListsAdmin]  DEFAULT ((0)) FOR [ListsAdmin]
GO
ALTER TABLE [dbo].[tblUser] ADD  DEFAULT ((0)) FOR [MassResolvePDATickets]
GO
ALTER TABLE [dbo].[tblUser] ADD  DEFAULT ((0)) FOR [FinalReviewer]
GO
ALTER TABLE [dbo].[tblUser] ADD  DEFAULT ((1)) FOR [Licensed]
GO
ALTER TABLE [dbo].[tblUser] ADD  DEFAULT ((0)) FOR [LimitTerr]
GO
ALTER TABLE [dbo].[tblUser] ADD  DEFAULT ((0)) FOR [AssignedTerr]
GO
ALTER TABLE [dbo].[tblUser] ADD  CONSTRAINT [DF_tblUser_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[tblUser] ADD  CONSTRAINT [DF_tblUser_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[tblUser] ADD  DEFAULT ((0)) FOR [isSuper]
GO
ALTER TABLE [dbo].[tblUser] ADD  DEFAULT ((0)) FOR [isTFMSuper]
GO
ALTER TABLE [dbo].[tblWork] ADD  CONSTRAINT [DF_tblWork_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[tblWork] ADD  CONSTRAINT [DF_tblWork_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[Terr] ADD  CONSTRAINT [DF_Terr_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[Terr] ADD  CONSTRAINT [DF_Terr_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[TestHistory] ADD  CONSTRAINT [DF_TestHistory_StatusDate]  DEFAULT (getdate()) FOR [StatusDate]
GO
ALTER TABLE [dbo].[TFMConfig] ADD  CONSTRAINT [DF_TFMConfig_ToolTip]  DEFAULT ('') FOR [ConfigToolTip]
GO
ALTER TABLE [dbo].[TFMConfig] ADD  CONSTRAINT [DF_TFMConfig_IsEditable]  DEFAULT ((1)) FOR [IsEditable]
GO
ALTER TABLE [dbo].[TFMConfig] ADD  CONSTRAINT [DF_TFMConfig_IsViewable]  DEFAULT ((1)) FOR [IsViewable]
GO
ALTER TABLE [dbo].[TFMConfig] ADD  CONSTRAINT [DF_TFMConfig_IsDate]  DEFAULT ((0)) FOR [IsDate]
GO
ALTER TABLE [dbo].[TFMConfig] ADD  CONSTRAINT [DF_TFMConfig_IsLongText]  DEFAULT ((0)) FOR [IsLongText]
GO
ALTER TABLE [dbo].[TFMConfig] ADD  CONSTRAINT [DF_TFMConfig_IsMultiLine]  DEFAULT ((0)) FOR [IsMultiLine]
GO
ALTER TABLE [dbo].[TFMConfig] ADD  CONSTRAINT [DF_TFMConfig_NotGlobal]  DEFAULT ((0)) FOR [NotGlobal]
GO
ALTER TABLE [dbo].[TFMConfig] ADD  CONSTRAINT [DF_TFMConfig_Sort]  DEFAULT ((0)) FOR [Sort]
GO
ALTER TABLE [dbo].[TFMConfig] ADD  CONSTRAINT [DF_TFMConfig_ModifiedOn]  DEFAULT (getdate()) FOR [ModifiedOn]
GO
ALTER TABLE [dbo].[TFMUserConfig] ADD  DEFAULT ((1)) FOR [Licensed]
GO
ALTER TABLE [dbo].[TicketD] ADD  CONSTRAINT [DF_TicketD_LType]  DEFAULT ((0)) FOR [LType]
GO
ALTER TABLE [dbo].[TicketD] ADD  CONSTRAINT [DF_TicketD_Reg]  DEFAULT ((0)) FOR [Reg]
GO
ALTER TABLE [dbo].[TicketD] ADD  CONSTRAINT [DF_TicketD_OT]  DEFAULT ((0)) FOR [OT]
GO
ALTER TABLE [dbo].[TicketD] ADD  CONSTRAINT [DF_TicketD_DT]  DEFAULT ((0)) FOR [DT]
GO
ALTER TABLE [dbo].[TicketD] ADD  CONSTRAINT [DF_TicketD_TT]  DEFAULT ((0)) FOR [TT]
GO
ALTER TABLE [dbo].[TicketD] ADD  CONSTRAINT [DF_TicketD_Zone]  DEFAULT ((0)) FOR [Zone]
GO
ALTER TABLE [dbo].[TicketD] ADD  CONSTRAINT [DF_TicketD_Toll]  DEFAULT ((0)) FOR [Toll]
GO
ALTER TABLE [dbo].[TicketD] ADD  CONSTRAINT [DF_TicketD_OtherE]  DEFAULT ((0)) FOR [OtherE]
GO
ALTER TABLE [dbo].[TicketD] ADD  CONSTRAINT [DF_TicketD_Mileage]  DEFAULT ((0)) FOR [Mileage]
GO
ALTER TABLE [dbo].[TicketD] ADD  CONSTRAINT [DF_TicketD_NT]  DEFAULT ((0)) FOR [NT]
GO
ALTER TABLE [dbo].[TicketD] ADD  CONSTRAINT [DF_TicketD_idRolCustomContact]  DEFAULT ((0)) FOR [idRolCustomContact]
GO
ALTER TABLE [dbo].[TicketD] ADD  CONSTRAINT [ticketd_downtime_0]  DEFAULT ((0)) FOR [downtime]
GO
ALTER TABLE [dbo].[TicketD] ADD  DEFAULT ('') FOR [ResolveSource]
GO
ALTER TABLE [dbo].[TicketD] ADD  CONSTRAINT [DF_TicketD_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[TicketD] ADD  CONSTRAINT [DF_TicketD_TFMCustom1]  DEFAULT ('') FOR [TFMCustom1]
GO
ALTER TABLE [dbo].[TicketD] ADD  CONSTRAINT [DF_TicketD_TFMCustom2]  DEFAULT ('') FOR [TFMCustom2]
GO
ALTER TABLE [dbo].[TicketD] ADD  CONSTRAINT [DF_TicketD_TFMCustom3]  DEFAULT ('') FOR [TFMCustom3]
GO
ALTER TABLE [dbo].[TicketD] ADD  CONSTRAINT [DF_TicketD_TFMCustom4]  DEFAULT ((0)) FOR [TFMCustom4]
GO
ALTER TABLE [dbo].[TicketD] ADD  CONSTRAINT [DF_TicketD_TFMCustom5]  DEFAULT ((0)) FOR [TFMCustom5]
GO
ALTER TABLE [dbo].[TicketD] ADD  DEFAULT ((0)) FOR [EMailStatus]
GO
ALTER TABLE [dbo].[TicketDPDA] ADD  CONSTRAINT [DF_TicketDPDA_LType]  DEFAULT ((0)) FOR [LType]
GO
ALTER TABLE [dbo].[TicketDPDA] ADD  CONSTRAINT [DF_TicketDPDA_Reg]  DEFAULT ((0)) FOR [Reg]
GO
ALTER TABLE [dbo].[TicketDPDA] ADD  CONSTRAINT [DF_TicketDPDA_OT]  DEFAULT ((0)) FOR [OT]
GO
ALTER TABLE [dbo].[TicketDPDA] ADD  CONSTRAINT [DF_TicketDPDA_DT]  DEFAULT ((0)) FOR [DT]
GO
ALTER TABLE [dbo].[TicketDPDA] ADD  CONSTRAINT [DF_TicketDPDA_TT]  DEFAULT ((0)) FOR [TT]
GO
ALTER TABLE [dbo].[TicketDPDA] ADD  CONSTRAINT [DF_TicketDPDA_Zone]  DEFAULT ((0)) FOR [Zone]
GO
ALTER TABLE [dbo].[TicketDPDA] ADD  CONSTRAINT [DF_TicketDPDA_Toll]  DEFAULT ((0)) FOR [Toll]
GO
ALTER TABLE [dbo].[TicketDPDA] ADD  CONSTRAINT [DF_TicketDPDA_OtherE]  DEFAULT ((0)) FOR [OtherE]
GO
ALTER TABLE [dbo].[TicketDPDA] ADD  CONSTRAINT [DF_TicketDPDA_Mileage]  DEFAULT ((0)) FOR [Mileage]
GO
ALTER TABLE [dbo].[TicketDPDA] ADD  CONSTRAINT [DF_TicketDPDA_NT]  DEFAULT ((0)) FOR [NT]
GO
ALTER TABLE [dbo].[TicketDPDA] ADD  CONSTRAINT [DF_TicketDPDA_idRolCustomContact]  DEFAULT ((0)) FOR [idRolCustomContact]
GO
ALTER TABLE [dbo].[TicketDPDA] ADD  CONSTRAINT [ticketdPDA_downtime_0]  DEFAULT ((0)) FOR [downtime]
GO
ALTER TABLE [dbo].[TicketDPDA] ADD  DEFAULT ('') FOR [ResolveSource]
GO
ALTER TABLE [dbo].[TicketDPDA] ADD  CONSTRAINT [DF_TicketDPDA_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[TicketDPDA] ADD  CONSTRAINT [DF_TicketDPDA_TFMCustom1]  DEFAULT ('') FOR [TFMCustom1]
GO
ALTER TABLE [dbo].[TicketDPDA] ADD  CONSTRAINT [DF_TicketDPDA_TFMCustom2]  DEFAULT ('') FOR [TFMCustom2]
GO
ALTER TABLE [dbo].[TicketDPDA] ADD  CONSTRAINT [DF_TicketDPDA_TFMCustom3]  DEFAULT ('') FOR [TFMCustom3]
GO
ALTER TABLE [dbo].[TicketDPDA] ADD  CONSTRAINT [DF_TicketDPDA_TFMCustom4]  DEFAULT ((0)) FOR [TFMCustom4]
GO
ALTER TABLE [dbo].[TicketDPDA] ADD  CONSTRAINT [DF_TicketDPDA_TFMCustom5]  DEFAULT ((0)) FOR [TFMCustom5]
GO
ALTER TABLE [dbo].[TicketF] ADD  CONSTRAINT [DF_TicketF_QuoteItemID]  DEFAULT ((0)) FOR [QuoteItemID]
GO
ALTER TABLE [dbo].[TicketF] ADD  CONSTRAINT [DF_TicketF_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[TicketF] ADD  CONSTRAINT [DF_TicketF_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[TicketI] ADD  CONSTRAINT [DF_TicketI_QuoteItemID]  DEFAULT ((0)) FOR [QuoteItemID]
GO
ALTER TABLE [dbo].[TicketI] ADD  CONSTRAINT [DF_TicketI_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[TicketI] ADD  CONSTRAINT [DF_TicketI_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[TicketIPDA] ADD  CONSTRAINT [DF_TicketIPDA_QuoteItemID]  DEFAULT ((0)) FOR [QuoteItemID]
GO
ALTER TABLE [dbo].[TicketIPDA] ADD  CONSTRAINT [DF_TicketIPDA_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[TicketIPDA] ADD  CONSTRAINT [DF_TicketIPDA_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[TicketO] ADD  CONSTRAINT [DF_TicketO_idRolCustomContact]  DEFAULT ((0)) FOR [idRolCustomContact]
GO
ALTER TABLE [dbo].[TicketO] ADD  DEFAULT ('') FOR [ResolveSource]
GO
ALTER TABLE [dbo].[TicketO] ADD  CONSTRAINT [DF_TicketO_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[TicketO] ADD  CONSTRAINT [DF_TicketO_Comments]  DEFAULT ('') FOR [Comments]
GO
ALTER TABLE [dbo].[TicketO] ADD  CONSTRAINT [DF_TicketO_Internet]  DEFAULT ((0)) FOR [Internet]
GO
ALTER TABLE [dbo].[TicketO] ADD  CONSTRAINT [DF_TicketO_TFMCustom1]  DEFAULT ('') FOR [TFMCustom1]
GO
ALTER TABLE [dbo].[TicketO] ADD  CONSTRAINT [DF_TicketO_TFMCustom2]  DEFAULT ('') FOR [TFMCustom2]
GO
ALTER TABLE [dbo].[TicketO] ADD  CONSTRAINT [DF_TicketO_TFMCustom3]  DEFAULT ('') FOR [TFMCustom3]
GO
ALTER TABLE [dbo].[TicketO] ADD  CONSTRAINT [DF_TicketO_TFMCustom4]  DEFAULT ((0)) FOR [TFMCustom4]
GO
ALTER TABLE [dbo].[TicketO] ADD  CONSTRAINT [DF_TicketO_TFMCustom5]  DEFAULT ((0)) FOR [TFMCustom5]
GO
ALTER TABLE [dbo].[TicketO] ADD  DEFAULT ((0)) FOR [Total]
GO
ALTER TABLE [dbo].[TicketO] ADD  DEFAULT ((0)) FOR [Reg]
GO
ALTER TABLE [dbo].[TicketO] ADD  DEFAULT ((0)) FOR [OT]
GO
ALTER TABLE [dbo].[TicketO] ADD  DEFAULT ((0)) FOR [DT]
GO
ALTER TABLE [dbo].[TicketO] ADD  DEFAULT ((0)) FOR [Zone]
GO
ALTER TABLE [dbo].[TicketO] ADD  DEFAULT ((0)) FOR [Toll]
GO
ALTER TABLE [dbo].[TicketO] ADD  DEFAULT ((0)) FOR [OtherE]
GO
ALTER TABLE [dbo].[TicketO] ADD  DEFAULT ((0)) FOR [TT]
GO
ALTER TABLE [dbo].[TicketO] ADD  DEFAULT ((0)) FOR [Mileage]
GO
ALTER TABLE [dbo].[TicketPic] ADD  CONSTRAINT [DF_TicketPic_ModifiedOn]  DEFAULT (getdate()) FOR [ModifiedOn]
GO
ALTER TABLE [dbo].[TicketPic] ADD  CONSTRAINT [DF_AuditGroup_TicketCategory]  DEFAULT ((0)) FOR [EmailPicture]
GO
ALTER TABLE [dbo].[TicketPic] ADD  CONSTRAINT [DF_TicketPic_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[TicketPic] ADD  CONSTRAINT [DF_TicketPic_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[TickOStatus] ADD  CONSTRAINT [DF_TickOStatus_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[TickOStatus] ADD  CONSTRAINT [DF_TickOStatus_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[ToDo] ADD  CONSTRAINT [DF_ToDo_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[ToDo] ADD  CONSTRAINT [DF_ToDo_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[Trans] ADD  CONSTRAINT [DF_Trans_Amount]  DEFAULT ((0)) FOR [Amount]
GO
ALTER TABLE [dbo].[Trans] ADD  DEFAULT ((0)) FOR [PhaseType]
GO
ALTER TABLE [dbo].[Vendor] ADD  CONSTRAINT [DF_Vendor_Type]  DEFAULT ('Overhead') FOR [Type]
GO
ALTER TABLE [dbo].[Vendor] ADD  CONSTRAINT [DF_Vendor_Status]  DEFAULT ((0)) FOR [Status]
GO
ALTER TABLE [dbo].[Vendor] ADD  CONSTRAINT [DF_Vendor_Balance]  DEFAULT ((0)) FOR [Balance]
GO
ALTER TABLE [dbo].[Vendor] ADD  CONSTRAINT [DF_Vendor_OnePer]  DEFAULT ((0)) FOR [OnePer]
GO
ALTER TABLE [dbo].[Vendor] ADD  CONSTRAINT [DF_Vendor_intBox]  DEFAULT ((7)) FOR [intBox]
GO
ALTER TABLE [dbo].[Vendor] ADD  CONSTRAINT [DF_Vendor_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[Vendor] ADD  CONSTRAINT [DF_Vendor_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[Vendor] ADD  DEFAULT ((0)) FOR [MISC1099Rpt]
GO
ALTER TABLE [dbo].[Vendor] ADD  DEFAULT ((0)) FOR [NEC1099Rpt]
GO
ALTER TABLE [dbo].[Violation] ADD  CONSTRAINT [DF_Violation_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[Violation] ADD  CONSTRAINT [DF_Violation_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[VioStatus] ADD  CONSTRAINT [DF_VioStatus_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[VioStatus] ADD  CONSTRAINT [DF_VioStatus_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[VType] ADD  CONSTRAINT [DF_VType_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[VType] ADD  CONSTRAINT [DF_VType_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[Warehouse] ADD  CONSTRAINT [DF_Warehouse_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[Warehouse] ADD  CONSTRAINT [DF_Warehouse_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[Zone] ADD  CONSTRAINT [DF_Zone_TFMID]  DEFAULT ('') FOR [TFMID]
GO
ALTER TABLE [dbo].[Zone] ADD  CONSTRAINT [DF_Zone_TFMSource]  DEFAULT ('') FOR [TFMSource]
GO
ALTER TABLE [dbo].[Audit]  WITH CHECK ADD  CONSTRAINT [FK_Audit_AuditCategory] FOREIGN KEY([AuditCategoryID])
REFERENCES [dbo].[AuditCategory] ([AuditCategoryID])
GO
ALTER TABLE [dbo].[Audit] CHECK CONSTRAINT [FK_Audit_AuditCategory]
GO
ALTER TABLE [dbo].[AuditCategory]  WITH CHECK ADD  CONSTRAINT [FK_AuditCategory_AuditCategory] FOREIGN KEY([ParentAuditCategoryID])
REFERENCES [dbo].[AuditCategory] ([AuditCategoryID])
GO
ALTER TABLE [dbo].[AuditCategory] CHECK CONSTRAINT [FK_AuditCategory_AuditCategory]
GO
ALTER TABLE [dbo].[AuditCategory]  WITH CHECK ADD  CONSTRAINT [FK_AuditCategory_AuditGroup] FOREIGN KEY([AuditGroupID])
REFERENCES [dbo].[AuditGroup] ([AuditGroupID])
GO
ALTER TABLE [dbo].[AuditCategory] CHECK CONSTRAINT [FK_AuditCategory_AuditGroup]
GO
ALTER TABLE [dbo].[AuditDetail]  WITH CHECK ADD  CONSTRAINT [FK_AuditDetail_Audit] FOREIGN KEY([AuditID])
REFERENCES [dbo].[Audit] ([AuditID])
GO
ALTER TABLE [dbo].[AuditDetail] CHECK CONSTRAINT [FK_AuditDetail_Audit]
GO
ALTER TABLE [dbo].[AuditDetail]  WITH CHECK ADD  CONSTRAINT [FK_AuditDetail_AuditUnitOfMeasure] FOREIGN KEY([UnitOfMeasureID])
REFERENCES [dbo].[AuditUnitOfMeasure] ([UnitOfMeasureID])
GO
ALTER TABLE [dbo].[AuditDetail] CHECK CONSTRAINT [FK_AuditDetail_AuditUnitOfMeasure]
GO
EXEC sys.sp_addextendedproperty @name=N'MS_Description', @value=N'tblUser.ID' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'Audit', @level2type=N'COLUMN',@level2name=N'ModifiedBy'
GO
EXEC sys.sp_addextendedproperty @name=N'MS_Description', @value=N'tblUser.ID' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'AuditCategory', @level2type=N'COLUMN',@level2name=N'ModifiedBy'
GO
EXEC sys.sp_addextendedproperty @name=N'MS_Description', @value=N'tblUser.ID' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'AuditDetail', @level2type=N'COLUMN',@level2name=N'ModifiedBy'
GO
EXEC sys.sp_addextendedproperty @name=N'MS_Description', @value=N'tblUser.ID' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'AuditGroup', @level2type=N'COLUMN',@level2name=N'ModifiedBy'
GO
EXEC sys.sp_addextendedproperty @name=N'MS_Description', @value=N'tblUser.ID' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'AuditUnitOfMeasure', @level2type=N'COLUMN',@level2name=N'ModifiedBy'
GO
