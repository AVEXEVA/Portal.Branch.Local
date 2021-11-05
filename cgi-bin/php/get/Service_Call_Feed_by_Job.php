<?php 
session_start( [ 'read_and_close' => true ] );
require('index.php');
setlocale(LC_MONETARY, 'en_US');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = $database->query(null,"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Elevator'){
    	$My_User = $database->query(null,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID = ?",array($_SESSION['User']));
        $My_User = sqlsrv_fetch_array($My_User); 
        $Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
        $r = $database->query($Portal,"
            SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
            FROM   Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
        $My_Privileges = array();
        while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
        $Privileged = FALSE;
        if(isset($My_Privileges['Job']) && $My_Privileges['Job']['User_Privilege'] >= 4 && $My_Privileges['Job']['Group_Privilege'] >= 4 && $My_Privileges['Job']['Other_Privilege'] >= 4){$Privileged = TRUE;}
        elseif($My_Privileges['Job']['User_Privilege'] >= 4 && is_numeric($_GET['ID'])){
            $r = $database->query(  null,"SELECT * FROM nei.dbo.TicketO WHERE TicketO.Job='{$_GET['ID']}' AND fWork='{$My_User['fWork']}'");
            $r2 = $database->query( null,"SELECT * FROM nei.dbo.TicketD WHERE TicketD.Job='{$_GET['ID']}' AND fWork='{$My_User['fWork']}'");
            $r3 = $database->query( null,"SELECT * FROM nei.dbo.TicketDArchive WHERE TicketDArchive.Job='{$_GET['ID']}' AND fWork='{$My_User['fWork']}'");
            $r = sqlsrv_fetch_array($r);
            $r2 = sqlsrv_fetch_array($r2);
			$r3 = sqlsrv_fetch_array($r3);
            $Privileged = (is_array($r) || is_array($r2) || is_array($r3)) ? TRUE : FALSE;
		}
    } elseif($_SESSION['Branch'] == 'Customer' && $_SESSION['Branch_ID'] == $_GET['ID']){$Privileged = TRUE;}
    //
    if(!isset($array['ID'])  || !is_numeric($_GET['ID']) || !$Privileged || !is_numeric($_GET['ID'])){?><html><head><script>document.location.href="../login.php?Forward=job<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {
        $data = array();
        $r = $database->query(null,"
			SELECT TicketO.ID                  AS ID,
				   TicketO.ID                  AS Ticket_ID,
				   TicketO.fDesc               AS Description,
				   ''                          AS Resolution,
				   TicketO.CDate               AS Created,
				   TicketO.DDate               AS Dispatched,
				   TicketO.EDate               AS Worked,
				   TicketO.EDate               AS Scheduled,
				   TicketO.TimeSite            AS On_Site,
				   TicketO.TimeComp            AS Completed,
				   TicketO.Who 	               AS Caller,
				   TicketO.fBy                 AS Reciever,
				   TicketO.Level               AS Level,
				   TicketO.Cat                 AS Category,
				   TicketO.LID                 AS Location,
				   TicketO.Job                 AS Job,
				   TicketO.LElev               AS Unit,
				   TicketO.Owner               AS Owner,
				   TicketO.fWork               AS Mechanic,
				   TickOStatus.Type            AS Status,
				   0                           AS Total,
				   0                           AS Regular,
				   0                           AS Overtime,
				   0                           AS Doubletime,
				   Loc.ID                      AS Customer,
				   Loc.Tag                     AS Location,
				   Loc.Address                 AS Address,
				   Loc.Address                 AS Street,
				   Loc.City                    AS City,
				   Loc.State                   AS State,
				   Loc.Zip                     AS Zip,
				   Route.Name 		           AS Route,
				   Zone.Name 		           AS Division,
				   Loc.Maint 		           AS Maintenance,
				   Job.ID                      AS Job_ID,
				   Job.fDesc                   AS Job_Description,
				   OwnerWithRol.ID             AS Owner_ID,
				   OwnerWithRol.Name           AS Customer,
				   Elev.ID 					   AS Unit_ID,
				   Elev.Unit                   AS Unit_Label,
				   Elev.State                  AS Unit_State,
				   Elev.fDesc				   AS Unit_Description,
				   Elev.Type 				   AS Unit_Type,
				   Emp.fFirst                  AS First_Name,
				   Emp.Last                    AS Last_Name,
				   Emp.fFirst + ' ' + Emp.Last AS Mechanic,
				   'Unknown'                   AS ClearPR,
				   JobType.Type                AS Job_Type
				   
			FROM   nei.dbo.TicketO
				   LEFT JOIN nei.dbo.TickOStatus  ON TicketO.Assigned = TickOStatus.Ref
				   LEFT JOIN nei.dbo.Loc          ON TicketO.LID      = Loc.Loc
				   LEFT JOIN nei.dbo.Job          ON TicketO.Job      = Job.ID
				   LEFT JOIN nei.dbo.Elev         ON TicketO.LElev    = Elev.ID
				   LEFT JOIN nei.dbo.OwnerWithRol ON TicketO.Owner    = OwnerWithRol.ID
				   LEFT JOIN Emp          ON TicketO.fWork    = Emp.fWork
				   LEFT JOIN nei.dbo.JobType      ON Job.Type         = JobType.ID
				   LEFT JOIN nei.dbo.Zone 		  ON Zone.ID          = Loc.Zone
				   LEFT JOIN nei.dbo.Route		  ON Route.ID		  = Loc.Route
			WHERE 
				TicketO.Job = ?
				AND TicketO.Level = 1
		",array($_GET['ID']));
		if($r){while($array = sqlsrv_fetch_array($r)){$data[] = $array;}}
        print json_encode(array('data'=>$data));   
	}
}?>