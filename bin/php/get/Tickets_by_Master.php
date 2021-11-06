<?php 
session_start( [ 'read_and_close' => true ] );
require('index.php');
setlocale(LC_MONETARY, 'en_US');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = $database->query(null,"
		SELECT * 
		FROM   Connection 
		WHERE  Connection.Connector = ? 
			   AND Connection.Hash = ?
	;", array($_SESSION['User'],$_SESSION['Hash']));
    $Connection = sqlsrv_fetch_array($r);
	$My_User    = $database->query(null,"
		SELECT Emp.*, 
			   Emp.fFirst AS First_Name, 
			   Emp.Last   AS Last_Name 
		FROM   Emp
		WHERE  Emp.ID = ?
	;", array($_SESSION['User']));
	$My_User = sqlsrv_fetch_array($My_User); 
	$My_Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
	$r = $database->query($Portal,"
		SELECT Privilege.Access_Table, 
			   Privilege.User_Privilege, 
			   Privilege.Group_Privilege, 
			   Privilege.Other_Privilege
		FROM   Privilege
		WHERE  Privilege.User_ID = ?
	;",array($_SESSION['User']));
	$My_Privileges = array();
	while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
	$Privileged = False;
	if( isset($My_Privileges['Location']) 
	   	&& $My_Privileges['Location']['Other_Privilege'] >= 4
	  	&& $My_Privileges['Ticket']['Other_Privlege'] >= 4){
			$Privileged = True;} 
    if(!isset($Connection['ID'])  || !is_numeric($_GET['ID']) || !$Privileged){?><html><head><script>document.location.href="../login.php?Forward=job<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {
		$data = array();
        $r = $database->query(null,"
            SELECT 
                TicketO.ID        AS ID,
                TicketO.fDesc     AS fDesc,
                TicketO.CDate     AS CDate,
                TicketO.EDate     AS EDate,
                TicketO.TimeSite  AS TimeSite,
                TicketO.TimeComp  AS TimeComp,
                '0'               AS Total,
                ''                AS DescRes,
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
                ''                AS Doubletime
            FROM
                nei.dbo.TicketO 
                LEFT JOIN nei.dbo.Loc               ON TicketO.LID             = Loc.Loc
                LEFT JOIN nei.dbo.Job               ON TicketO.Job             = Job.ID
                LEFT JOIN nei.dbo.OwnerWithRol      ON TicketO.Owner           = OwnerWithRol.ID
                LEFT JOIN nei.dbo.JobType           ON Job.Type                = JobType.ID
                LEFT JOIN nei.dbo.Elev              ON TicketO.LElev           = Elev.ID
                LEFT JOIN nei.dbo.TickOStatus       ON TicketO.Assigned        = TickOStatus.Ref
                LEFT JOIN Emp               ON Emp.fWork               = TicketO.fWork
                LEFT JOIN Portal.dbo.Master_Account ON Master_Account.Customer = Loc.Owner
            WHERE 
                Master_Account.Master = '{$_GET['ID']}'
        ;");
        if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}}
        $r = $database->query(null,"
            SELECT
                TicketD.ID        AS ID,
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
                TicketD.DT        AS Doubletime
            FROM
                nei.dbo.TicketD 
                LEFT JOIN nei.dbo.Loc               ON TicketD.Loc             = Loc.Loc
                LEFT JOIN nei.dbo.Job               ON TicketD.Job             = Job.ID
                LEFT JOIN nei.dbo.OwnerWithRol      ON Loc.Owner               = OwnerWithRol.ID
                LEFT JOIN nei.dbo.JobType           ON Job.Type                = JobType.ID
                LEFT JOIN nei.dbo.Elev              ON TicketD.Elev            = Elev.ID
                LEFT JOIN Emp               ON Emp.fWork               = TicketD.fWork
                LEFT JOIN Portal.dbo.Master_Account ON Master_Account.Customer = Loc.Owner
            WHERE
                Master_Account.Master = '{$_GET['ID']}'
                AND NOT (TicketD.DescRes    LIKE    '%Voided%')
                AND TicketD.Total > 0
        ;");
        if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}}
        $r = $database->query(null,"
            SELECT
                TicketDArchive.ID       AS ID,
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
                TicketDArchive.DT       AS Doubletime
            FROM
                nei.dbo.TicketDArchive 
                LEFT JOIN nei.dbo.Loc               ON TicketDArchive.Loc      = Loc.Loc
                LEFT JOIN nei.dbo.Job               ON TicketDArchive.Job      = Job.ID
                LEFT JOIN nei.dbo.OwnerWithRol      ON Loc.Owner               = OwnerWithRol.ID
                LEFT JOIN nei.dbo.JobType           ON Job.Type                = JobType.ID
                LEFT JOIN nei.dbo.Elev              ON TicketDArchive.Elev     = Elev.ID
                LEFT JOIN Emp               ON Emp.fWork               = TicketDArchive.fWork
                LEFT JOIN Portal.dbo.Master_Account ON Master_Account.Customer = Loc.Owner
            WHERE
                Master_Account.Master = '{$_GET['ID']}'
                AND NOT (TicketDArchive.DescRes    LIKE    '%Voided%')
        ;");
        if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}}
        print json_encode(array('data'=>$data));   }
}?>