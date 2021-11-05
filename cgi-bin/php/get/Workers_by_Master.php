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
	   	&& $My_Privileges['User']['Other_Privilege'] >= 4
	  	&& $My_Privileges['Customer']['Other_Privlege'] >= 4){
			$Privileged = True;}
	$data = array();
        $r = $database->query(null,"
            SELECT   Emp.ID             AS  ID,
                     Emp.fFirst         AS  First_Name,
                     Emp.Last           AS  Last_Name,
                     Sum(TicketO.Reg)   AS  Regular,
                     Sum(TicketO.OT)    AS  Overtime,
                     Sum(TicketO.DT)    AS  Doubletime,
                     Sum(TicketO.Total) AS  Total 
            FROM     nei.dbo.TicketO 
                     LEFT JOIN nei.dbo.Loc               ON  TicketO.LID             = Loc.Loc
                     LEFT JOIN nei.dbo.Job               ON  TicketO.Job             = Job.ID
                     LEFT JOIN nei.dbo.OwnerWithRol      ON  TicketO.Owner           = OwnerWithRol.ID
                     LEFT JOIN nei.dbo.JobType           ON  Job.Type                = JobType.ID
                     LEFT JOIN nei.dbo.Elev              ON  TicketO.LElev           = Elev.ID
                     LEFT JOIN nei.dbo.TickOStatus       ON  TicketO.Assigned        = TickOStatus.Ref
                     LEFT JOIN Emp               ON  TicketO.fWork           = Emp.fWork
                     LEFT JOIN Portal.dbo.Master_Account ON  Master_Account.Customer = Loc.Owner
            WHERE    Master_Account.Master = ?
            GROUP BY Emp.ID, 
			         Emp.fFirst, 
					 Emp.Last
        ;",array($_GET['ID']));
        if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}}
        $r = $database->query(null,"
            SELECT   Emp.ID             AS ID,
                     Emp.fFirst         AS First_Name,
                     Emp.Last           AS Last_Name,
                     Sum(TicketD.Reg)   AS Regular,
                     Sum(TicketD.OT)    AS Overtime,
                     Sum(TicketD.DT)    AS Doubletime,
                     Sum(TicketD.Total) AS Total
            FROM     nei.dbo.TicketD 
                     LEFT JOIN nei.dbo.Loc               ON TicketD.Loc              = Loc.Loc
                     LEFT JOIN nei.dbo.Job               ON TicketD.Job              = Job.ID
                     LEFT JOIN nei.dbo.OwnerWithRol      ON Loc.Owner                = OwnerWithRol.ID
                     LEFT JOIN nei.dbo.JobType           ON Job.Type                 = JobType.ID
                     LEFT JOIN nei.dbo.Elev              ON TicketD.Elev             = Elev.ID
                     LEFT JOIN Emp               ON TicketD.fWork            = Emp.fWork
                     LEFT JOIN Portal.dbo.Master_Account ON  Master_Account.Customer = Loc.Owner
            WHERE    Master_Account.Master = ?
                     AND NOT (TicketD.DescRes    LIKE    '%Voided%')
            GROUP BY Emp.ID, 
			         Emp.fFirst, 
					 Emp.Last
        ;",array($_GET['ID']));
        if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}}
        $r = $database->query(null,"
            SELECT   Emp.ID                    AS ID,
                     Emp.fFirst                AS First_Name,
                     Emp.Last                  AS Last_Name,
                     Sum(TicketDArchive.Reg)   AS Regular,
                     Sum(TicketDArchive.OT)    AS Overtime,
                     Sum(TicketDArchive.DT)    AS Doubletime,
                     Sum(TicketDArchive.Total) AS Total
            FROM     nei.dbo.TicketDArchive 
                     LEFT JOIN nei.dbo.Loc               ON TicketDArchive.Loc       = Loc.Loc
                     LEFT JOIN nei.dbo.Job               ON TicketDArchive.Job       = Job.ID
                     LEFT JOIN nei.dbo.OwnerWithRol      ON Loc.Owner                = OwnerWithRol.ID
                     LEFT JOIN nei.dbo.JobType           ON Job.Type                 = JobType.ID
                     LEFT JOIN nei.dbo.Elev              ON TicketDArchive.Elev      = Elev.ID
                     LEFT JOIN Emp               ON TicketDArchive.fWork     = Emp.fWork
                     LEFT JOIN Portal.dbo.Master_Account ON  Master_Account.Customer = Loc.Owner
            WHERE    Master_Account.Master = ?
                     AND NOT (TicketDArchive.DescRes LIKE '%Voided%')
            GROUP BY Emp.ID, 
			         Emp.fFirst, 
					 Emp.Last
        ;",array($_GET['ID']));
        if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}}
        print json_encode(array('data'=>$data));   
	}
}?>