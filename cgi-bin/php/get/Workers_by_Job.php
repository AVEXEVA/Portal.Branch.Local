<?php 
session_start( [ 'read_and_close' => true ] );
require('index.php');
setlocale(LC_MONETARY, 'en_US');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"
		SELECT * 
		FROM   Connection 
		WHERE  Connection.Connector = ? 
			   AND Connection.Hash = ?
	;", array($_SESSION['User'],$_SESSION['Hash']));
    $Connection = sqlsrv_fetch_array($r);
	$My_User    = sqlsrv_query($NEI,"
		SELECT Emp.*, 
			   Emp.fFirst AS First_Name, 
			   Emp.Last   AS Last_Name 
		FROM   Emp
		WHERE  Emp.ID = ?
	;", array($_SESSION['User']));
	$My_User = sqlsrv_fetch_array($My_User); 
	$My_Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
	$r = sqlsrv_query($Portal,"
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
	if( isset($My_Privileges['Job']) 
	   	&& $My_Privileges['Job']['Other_Privilege'] >= 4){
			$Privileged = True;} 
	elseif(isset($My_Privileges['Job']) 
		&& $My_Privileges['Job']['Group_Privilege'] >= 4
		&& is_numeric($_GET['ID'])){
			$r = sqlsrv_query($NEI,"
				SELECT Job.Loc AS Location_ID
				FROM   nei.dbo.Job
				WHERE  Job.ID = ?
			;", array($_GET['ID']));
			$Location_ID = sqlsrv_fetch_array($r)['Location_ID'];
			$r = sqlsrv_query($NEI,"
				SELECT Tickets.ID
				FROM 
				(
					(
						SELECT TicketO.ID
						FROM   nei.dbo.TicketO
						WHERE  TicketO.LID       = ?
						       AND TicketO.fWork = ? 
					) 
					UNION ALL
					(
						SELECT TicketD.ID
						FROM   nei.dbo.TicketD
						WHERE  TicketD.Loc       = ?
						       AND TicketD.fWork = ? 
					)
					UNION ALL
					(
						SELECT TicketDArchive.ID
						FROM   nei.dbo.TicketDArchive
						WHERE  TicketDArchive.Loc       = ?
						       AND TicketDArchive.fWork = ? 
					)
				) AS Tickets
			;", array($Location_ID, $My_User['fWork'],$Location_ID, $My_User['fWork'],$Location_ID, $My_User['fWork']));
			$Privileged = is_array(sqlsrv_fetch_array($r)) ? True : False;}
	elseif(isset($My_Privileges['Job'])
		&& $My_Privileges['Job']['User_Privilege'] >= 4
		&& is_numeric($_GET['ID'])){
			$r = sqlsrv_query($NEI,"
				SELECT Tickets.ID
				FROM 
				(
					(
						SELECT TicketO.ID
						FROM   nei.dbo.TicketO
						WHERE  TicketO.Job       = ?
						       AND TicketO.fWork = ? 
					) 
					UNION ALL
					(
						SELECT TicketD.ID
						FROM   nei.dbo.TicketD
						WHERE  TicketD.Job       = ?
						       AND TicketD.fWork = ? 
					)
					UNION ALL
					(
						SELECT TicketDArchive.ID
						FROM   nei.dbo.TicketDArchive
						WHERE  TicketDArchive.Job       = ?
						       AND TicketDArchive.fWork = ? 
					)
				) AS Tickets
			;", array($_GET['ID'], $My_User['fWork'],$_GET['ID'], $My_User['fWork'],$_GET['ID'], $My_User['fWork']));
			$Privileged = is_array(sqlsrv_fetch_array($r)) ? True : False;}
    if(!isset($Connection['ID'])  || !is_numeric($_GET['ID']) || !$Privileged){?><html><head><script>document.location.href="../login.php?Forward=job<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {
        $data = array();
        $r = sqlsrv_query($NEI,"
			SELECT Workers.ID,
				   Workers.First_Name,
				   Workers.Last_Name,
				   Sum(Workers.Regular) AS Regular,
				   Sum(Workers.Overtime)      AS Overtime,
				   Sum(Workers.Doubletime)      AS Doubletime,
				   Sum(Workers.Total)   AS Total
			FROM 
			(
				(
					SELECT   Emp.ID             AS ID,
							 Emp.fFirst         AS First_Name,
							 Emp.Last           AS Last_Name,
							 0   AS Regular,
							 0    AS Overtime,
							 0    AS Doubletime,
							 0 AS Total 
					FROM     nei.dbo.TicketO
							 LEFT JOIN nei.dbo.Loc          ON  TicketO.LID      = Loc.Loc
							 LEFT JOIN nei.dbo.Job          ON  TicketO.Job      = Job.ID
							 LEFT JOIN nei.dbo.OwnerWithRol ON  TicketO.Owner    = OwnerWithRol.ID
							 LEFT JOIN nei.dbo.JobType      ON  Job.Type         = JobType.ID
							 LEFT JOIN nei.dbo.Elev         ON  TicketO.LElev    = Elev.ID
							 LEFT JOIN nei.dbo.TickOStatus  ON  TicketO.Assigned = TickOStatus.Ref
							 LEFT JOIN Emp          ON  TicketO.fWork    = Emp.fWork
					WHERE    TicketO.Job = ?
					GROUP BY Emp.ID, 
							 Emp.fFirst, 
							 Emp.Last
				)
				UNION ALL
				(
					SELECT   Emp.ID             AS ID,
							 Emp.fFirst         AS First_Name,
							 Emp.Last           AS Last_Name,
							 Sum(TicketD.Reg)   AS Regular,
							 Sum(TicketD.OT)    AS Overtime,
							 Sum(TicketD.DT)    AS Doubletime,
							 Sum(TicketD.Total) AS Total
					FROM     nei.dbo.TicketD
							 LEFT JOIN nei.dbo.Loc          ON TicketD.Loc   = Loc.Loc
							 LEFT JOIN nei.dbo.Job          ON TicketD.Job   = Job.ID 
							 LEFT JOIN nei.dbo.OwnerWithRol ON Loc.Owner     = OwnerWithRol.ID
							 LEFT JOIN nei.dbo.JobType      ON Job.Type      = JobType.ID
							 LEFT JOIN nei.dbo.Elev         ON TicketD.Elev  = Elev.ID
							 LEFT JOIN Emp          ON TicketD.fWork = Emp.fWork
					WHERE    TicketD.Job = ?
							 AND NOT (TicketD.DescRes LIKE '%Voided%')
					GROUP BY Emp.ID, 
							 Emp.fFirst, 
							 Emp.Last
				)
				UNION ALL  
				(
					SELECT   Emp.ID                    AS ID,
							 Emp.fFirst                AS First_Name,
							 Emp.Last                  AS Last_Name,
							 Sum(TicketDArchive.Reg)   AS Regular,
							 Sum(TicketDArchive.OT)    AS Overtime,
							 Sum(TicketDArchive.DT)    AS Doubletime,
							 Sum(TicketDArchive.Total) AS Total
					FROM     nei.dbo.TicketDArchive 
							 LEFT JOIN nei.dbo.Loc          ON TicketDArchive.Loc   = Loc.Loc
							 LEFT JOIN nei.dbo.Job          ON TicketDArchive.Job   = Job.ID
							 LEFT JOIN nei.dbo.OwnerWithRol ON Loc.Owner            = OwnerWithRol.ID
							 LEFT JOIN nei.dbo.JobType      ON Job.Type             = JobType.ID
							 LEFT JOIN nei.dbo.Elev         ON TicketDArchive.Elev  = Elev.ID
							 LEFT JOIN Emp          ON TicketDArchive.fWork = Emp.fWork
					WHERE    TicketDArchive.Job = ?
							 AND NOT (TicketDArchive.DescRes LIKE '%Voided%')
					GROUP BY Emp.ID, 
							 Emp.fFirst, 
							 Emp.Last
				)
			) AS Workers
			GROUP BY Workers.ID,
					 Workers.First_Name,
					 Workers.Last_Name
        ;",array($_GET['ID'], $_GET['ID'], $_GET['ID']));
		if( ($errors = sqlsrv_errors() ) != null) {
        foreach( $errors as $error ) {
            echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
            echo "code: ".$error[ 'code']."<br />";
            echo "message: ".$error[ 'message']."<br />";
        }
    }
        if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}}
        print json_encode(array('data'=>$data));
	}
}?>