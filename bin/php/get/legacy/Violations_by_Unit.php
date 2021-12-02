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
		SELECT Privilege.Access, 
			   Privilege.Owner, 
			   Privilege.Group, 
			   Privilege.Other
		FROM   Privilege
		WHERE  Privilege.User_ID = ?
	;",array($_SESSION['User']));
	$My_Privileges = array();
	while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access']] = $array2;}
	$Privileged = False;
	if( isset($My_Privileges['Violation']) 
	   	&& $My_Privileges['Violation']['Other'] >= 4){
			$Privileged = True;} 
	elseif(isset($My_Privileges['Violation']) 
		&& $My_Privileges['Violation']['Group'] >= 4
		&& is_numeric($_GET['ID'])){
			$r = $database->query(null,"
				SELECT Elev.Loc AS Location_ID
				FROM   nei.dbo.Elev
				WHERE  Elev.ID = ?
			;", array($_GET['ID']));
			$Location_ID = sqlsrv_fetch_array($r)['Location_ID'];
            $r = $database->query(null,"
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
    elseif(isset($My_Privileges['Unit'])
        && $My_Privileges['Unit']['Owner'] >= 4
        && is_numeric($_GET['ID'])){
            $r = $database->query(null,"
                SELECT Tickets.ID
                FROM 
                (
                    (
                        SELECT TicketO.ID
                        FROM   nei.dbo.TicketO
                        WHERE  TicketO.LElev       = ?
                               AND TicketO.fWork = ? 
                    ) 
                    UNION ALL
                    (
                        SELECT TicketD.ID
                        FROM   nei.dbo.TicketD
                        WHERE  TicketD.Elev       = ?
                               AND TicketD.fWork = ? 
                    )
                    UNION ALL
                    (
                        SELECT TicketDArchive.ID
                        FROM   nei.dbo.TicketDArchive
                        WHERE  TicketDArchive.Elev       = ?
                               AND TicketDArchive.fWork = ? 
                    )
                ) AS Tickets
            ;", array($_GET['ID'], $My_User['fWork'],$_GET['ID'], $My_User['fWork'],$_GET['ID'], $My_User['fWork']));
            $Privileged = is_array(sqlsrv_fetch_array($r)) ? True : False;}
    if(!isset($Connection['ID'])  || !is_numeric($_GET['ID']) || !$Privileged){print json_encode(array('data'=>array()));}
    else {
		$data = array();
		if(isset($My_Privileges['Unit']) && $My_Privileges['Unit']['Other'] >= 4){
			$r = $database->query(null,"
				SELECT Violation.ID      AS ID,
					   Violation.Name    AS Name,
					   Violation.fDate   AS Date,
					   Violation.Status  AS Status,
					   Violation.Remarks AS Description
				FROM   nei.dbo.Violation
					   LEFT JOIN nei.dbo.Elev ON Violation.Elev = Elev.ID
					   LEFT JOIN nei.dbo.Loc  ON Elev.Loc       = Loc.Loc
				WHERE  Loc.Loc = ?
			;",array($_GET['ID']));
		} else {
			$r = $database->query(null,"
				SELECT Violation.ID      AS ID,
					   Violation.Name    AS Name,
					   Violation.fDate   AS Date,
					   Violation.Status  AS Status,
					   Violation.Remarks AS Description
				FROM   nei.dbo.Violation
					   LEFT JOIN nei.dbo.Elev ON Violation.Elev = Elev.ID
					   LEFT JOIN nei.dbo.Loc  ON Elev.Loc       = Loc.Loc
				WHERE  Loc.Loc = ?
					   AND (Violation.Status    = 'Open' 
					   		OR Violation.Status = 'Job Created')
			;",array($_GET['ID']));
		}
		if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}}
		print json_encode(array('data'=>$data));
    }
}?>