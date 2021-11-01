<?php 
session_start();
require('index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    if(!isset($array['ID'],$_GET['ID']) || !is_numeric($_GET['ID'])){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
		$data = array();
        $r = sqlsrv_query($NEI,"
            SELECT TicketO.ID        AS ID,
                   TicketO.fDesc     AS fDesc,
                   TicketO.CDate     AS CDate,
                   TicketO.EDate     AS EDate,
                   TicketO.Total     AS Total,
                   ''                AS DescRes,
                   TicketO.TimeSite  AS TimeSite,
                   TicketO.TimeComp  AS TimeComp,
                   TicketO.Who       AS Caller,
                   TicketO.fBy       AS Taken_By,
                   TicketO.Level     AS Level,
                   TicketO.Cat       AS Category,
                   Loc.ID            AS Account,
                   Loc.Tag           AS Tag,
                   Loc.Tag           AS Location,
                   Loc.Address       AS Address,
                   Loc.Address       AS Street,
                   Loc.City          AS City,
                   Loc.State         AS State,
                   Loc.Zip           AS Zip,
                   Job.ID            AS Job_ID,
                   Job.fDesc         AS Job_Description,
                   OwnerWithRol.ID   AS Owner_ID,
                   OwnerWithRol.Name AS Customer,
                   JobType.Type      AS Job_Type,
                   Elev.Unit         AS Unit_Label,
                   Elev.State        AS Unit_State,
                   TickOStatus.Type  AS Status,
                   Emp.fFirst        AS Worker_First_Name,
                   Emp.Last          AS Worker_Last_Name,
                   ''                AS Regular,
                   ''                AS Overtime,
                   ''                AS Doubletime,
                   ''                AS On_Site,
                   ''                AS Completed
            FROM   nei.dbo.TicketO 
                   LEFT JOIN nei.dbo.Loc               ON  TicketO.LID = Loc.Loc
                   LEFT JOIN nei.dbo.Job               ON  TicketO.Job = Job.ID 
                   LEFT JOIN nei.dbo.OwnerWithRol      ON  TicketO.Owner = OwnerWithRol.ID
                   LEFT JOIN nei.dbo.JobType           ON  Job.Type = JobType.ID
                   LEFT JOIN nei.dbo.Elev              ON  TicketO.LElev = Elev.ID
                   LEFT JOIN nei.dbo.TickOStatus       ON  TicketO.Assigned = TickOStatus.Ref
                   LEFT JOIN nei.dbo.Emp               ON  Emp.fWork = TicketO.fWork
            WHERE  TicketO.LID                 =   ?
        ;",array($_GET['ID']));
        $Tickets = array();
        if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}}
        $r = sqlsrv_query($NEI,"
            SELECT TicketD.ID        AS ID,
                   TicketD.fDesc     AS fDesc,
                   TicketD.CDate     AS CDate,
                   TicketD.EDate     AS EDate,
                   TicketD.Total     AS Total,
                   TicketD.DescRes   AS DescRes,
                   TicketD.TimeSite  AS TimeSite,
                   TicketD.TimeComp  AS TimeComp,
                   TicketD.Who       AS Caller,
                   TicketD.fBy       AS Taken_By,
                   TicketD.Level     AS Level,
                   TicketD.Cat       AS Category,
                   Loc.ID            AS Account,
                   Loc.Tag           AS Tag,
                   Loc.Tag           AS Location,
                   Loc.Address       AS Address,
                   Loc.Address       AS Street,
                   Loc.City          AS City,
                   Loc.State         AS State,
                   Loc.Zip           AS Zip,
                   Job.ID            AS Job_ID,
                   Job.fDesc         AS Job_Description,
                   OwnerWithRol.ID   AS Owner_ID,
                   OwnerWithRol.Name AS Customer,
                   JobType.Type      AS Job_Type,
                   Elev.Unit         AS Unit_Label,
                   Elev.State        AS Unit_State,
                   Emp.fFirst        AS Worker_First_Name,
                   Emp.Last          AS Worker_Last_Name,
                   TicketD.Reg       AS Regular,
                   TicketD.OT        AS Overtime,
                   TicketD.DT        AS Doubletime,
                   TicketD.TimeSite  AS On_Site,
                   TicketD.TimeComp  AS Completed
				   'Completed'       AS Status
            FROM   nei.dbo.TicketD 
                   LEFT JOIN nei.dbo.Loc      			ON  TicketD.Loc = Loc.Loc
                   LEFT JOIN nei.dbo.Job               ON  TicketD.Job = Job.ID
                   LEFT JOIN nei.dbo.OwnerWithRol      ON  Loc.Owner = OwnerWithRol.ID
                   LEFT JOIN nei.dbo.JobType           ON  Job.Type = JobType.ID
                   LEFT JOIN nei.dbo.Elev              ON  TicketD.Elev = Elev.ID
                   LEFT JOIN nei.dbo.Emp               ON  Emp.fWork = TicketD.fWork
            WHERE  TicketD.Loc = ?
                   AND NOT (TicketD.DescRes    LIKE    '%Voided%')
        ;",array($_GET['ID']));
        if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}}
        $r = sqlsrv_query($NEI,"
            SELECT TicketDArchive.ID       AS ID,
                   TicketDArchive.fDesc    AS fDesc,
                   TicketDArchive.CDate    AS CDate,
                   TicketDArchive.EDate    AS EDate,
                   TicketDArchive.Total    AS Total,
                   TicketDArchive.DescRes  AS DescRes,
                   TicketDArchive.TimeSite AS TimeSite,
                   TicketDArchive.TimeComp AS TimeComp,
                   TicketDArchive.Who      AS Caller,
                   TicketDArchive.fBy      AS Taken_By,
                   TicketDArchive.Level    AS Level,
                   TicketDArchive.Cat      AS Category,
                   Loc.ID                  AS Account,
                   Loc.Tag                 AS Tag,
                   Loc.Tag                 AS Location,
                   Loc.Address             AS Address,
                   Loc.Address             AS Street,
                   Loc.City                AS City,
                   Loc.State               AS State,
                   Loc.Zip                 AS Zip,
                   Job.ID                  AS Job_ID,
                   Job.fDesc               AS Job_Description,
                   OwnerWithRol.ID         AS Owner_ID,
                   OwnerWithRol.Name       AS Customer,
                   JobType.Type            AS Job_Type,
                   Elev.Unit               AS Unit_Label,
                   Elev.State              AS Unit_State,
                   Emp.fFirst              AS Worker_First_Name,
                   Emp.Last                AS Worker_Last_Name,
                   TicketDArchive.Reg      AS Regular,
                   TicketDArchive.OT       AS Overtime, 
                   TicketDArchive.DT       AS Doubletime,
                   TicketDArchive.TimeSite AS On_Site,
                   TicketDArchive.TimeComp AS Completed
				   'Completed' 			AS Status
            FROM   nei.dbo.TicketDArchive 
                   LEFT JOIN nei.dbo.Loc          ON  TicketDArchive.Loc = Loc.Loc
                   LEFT JOIN nei.dbo.Job          ON  TicketDArchive.Job = Job.ID 
                   LEFT JOIN nei.dbo.OwnerWithRol ON  Loc.Owner = OwnerWithRol.ID
                   LEFT JOIN nei.dbo.JobType      ON  Job.Type = JobType.ID
                   LEFT JOIN nei.dbo.Elev         ON  TicketDArchive.Elev = Elev.ID
                   LEFT JOIN nei.dbo.Emp          ON  Emp.fWork = TicketDArchive.fWork
            WHERE  TicketDArchive.Loc = ?
                   AND NOT (TicketDArchive.DescRes    LIKE    '%Voided%')
        ;",array($_GET['ID']));
        if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}}
        print json_encode(array('data'=>$data));   }
}?>