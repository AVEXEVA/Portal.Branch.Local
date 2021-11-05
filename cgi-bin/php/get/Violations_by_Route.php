<?php 
session_start( [ 'read_and_close' => true ] );
require('index.php');
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
    if( isset($My_Privileges['Route'], $My_Privileges['Violation']) 
        && $My_Privileges['Route']['Other_Privilege'] >= 4
        && $My_Privileges['Violation']['Other_Privilege'] >= 4){
            $Privileged = True;} 
    elseif(isset($My_Privileges['Route'], $My_Privileges['Violation']) 
        && $My_Privileges['Route']['Group_Privilege'] >= 4
        && $My_Privileges['Violation']['Group_Privilege'] >= 4
        && is_numeric($_GET['ID'])){
            $r = sqlsrv_query($NEI,"
                SELECT Locations.*
                FROM 
                (
                    (
                        SELECT Loc.Loc AS Location_ID
                        FROM   nei.dbo.TicketO
                               LEFT JOIN nei.dbo.Loc ON TicketO.LID = Loc.Loc
                               LEFT JOIN Emp ON TicketO.fWork = Emp.fWork
                        WHERE  Emp.ID = ?
                               AND Loc.Route = ?
                    )
                    UNION ALL
                    (
                        SELECT Loc.Loc AS Location_ID
                        FROM   nei.dbo.TicketD
                               LEFT JOIN nei.dbo.Loc ON TicketD.Loc = Loc.Loc
                               LEFT JOIN Emp ON TicketD.fWork = Emp.fWork
                        WHERE  Emp.ID = ?
                               AND Loc.Route = ?
                    )
                    UNION ALL
                    (
                        SELECT Loc.Loc AS Location_ID
                        FROM   nei.dbo.TicketDArchive
                               LEFT JOIN nei.dbo.Loc ON TicketDArchive.Loc = Loc.Loc
                               LEFT JOIN Emp ON TicketDArchive.fWork = Emp.fWork
                        WHERE  Emp.ID = ?
                               AND Loc.Route = ?
                    )
                ) AS Locations
                GROUP BY Locations.Location_ID
            ;",array($_SESSION['User'], $_GET['ID'], $_SESSION['User'], $_GET['ID'], $_SESSION['User'], $_GET['ID']));
            if($r){if(is_array(sqlsrv_fetch_array($r))){$Privileged = True;}}}
    elseif(isset($My_Privileges['Route'], $My_Privileges['Violation']) 
        && $My_Privileges['Route']['User_Privilege'] >= 4
        && $My_Privileges['Violation']['User_Privilege'] >= 4
        && is_numeric($_GET['ID'])){
            $r = sqlsrv_query($NEI,"
                SELECT Route.ID AS Route_ID
                FROM   nei.dbo.Route
                       LEFT JOIN Emp ON Route.Mech = Emp.fWork
                WHERE  Emp.ID = ?
            ;",array($_GET['ID']));
            if($r){
                $Route_ID = sqlsrv_fetch_array($r)['Route_ID'];
                if($Route_ID == $_GET['ID']){
                    $Privileged = True;}}}
    if(!isset($Connection['ID'])  || !is_numeric($_GET['ID']) || !$Privileged){print json_encode(array('data'=>array()));}
    else {   
		$data = array();
		if(isset($My_Privileges['Route']) && $My_Privileges['Route']['Other_Privilege'] >= 4){
			$r = sqlsrv_query($NEI,"
				SELECT Violation.ID      AS ID,
					   Violation.Name    AS Name,
					   Violation.fDate   AS Date,
					   Violation.Status  AS Status,
					   Violation.Remarks AS Description
				FROM   nei.dbo.Violation
					   LEFT JOIN nei.dbo.Elev ON Violation.Elev = Elev.ID
					   LEFT JOIN nei.dbo.Loc  ON Elev.Loc       = Loc.Loc
				WHERE  Loc.Route = ?
			;",array($_GET['ID']));
		} else {
			$r = sqlsrv_query($NEI,"
				SELECT Violation.ID      AS ID,
					   Violation.Name    AS Name,
					   Violation.fDate   AS Date,
					   Violation.Status  AS Status,
					   Violation.Remarks AS Description
				FROM   nei.dbo.Violation
					   LEFT JOIN nei.dbo.Elev ON Violation.Elev = Elev.ID
					   LEFT JOIN nei.dbo.Loc  ON Elev.Loc       = Loc.Loc
				WHERE  Loc.Route = ?
					   AND (Violation.Status    = 'Open' 
					   		OR Violation.Status = 'Job Created')
			;",array($_GET['ID']));
		}
		if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}}
		print json_encode(array('data'=>$data));
    }
}?>