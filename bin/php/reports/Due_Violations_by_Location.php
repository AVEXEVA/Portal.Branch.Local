<?php 
session_start( [ 'read_and_close' => true ] );
require('index.php');
setlocale(LC_MONETARY, 'en_US');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = $database->query(null,"
        SELECT * 
        FROM   nei.dbo.Connection 
        WHERE  Connection.Connector = ? 
               AND Connection.Hash = ?
    ;", array($_SESSION['User'],$_SESSION['Hash']));
    $Connection = sqlsrv_fetch_array($r);
    $My_User    = $database->query(null,"
        SELECT Emp.*, 
               Emp.fFirst AS First_Name, 
               Emp.Last   AS Last_Name 
        FROM   nei.dbo.Emp
        WHERE  Emp.ID = ?
    ;", array($_SESSION['User']));
    $My_User = sqlsrv_fetch_array($My_User); 
    $My_Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
    $r = $database->query($Portal,"
        SELECT Privilege.Access, 
               Privilege.Owner, 
               Privilege.Group, 
               Privilege.Other
        FROM   Portal.dbo.Privilege
        WHERE  Privilege.User_ID = ?
    ;",array($_SESSION['User']));
    $My_Privileges = array();
    while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access']] = $array2;}
    $Privileged = False;
    if( isset($My_Privileges['Location']) 
        && $My_Privileges['Location']['Other'] >= 4
	  	&& $My_Privileges['Violation']['Other'] >= 4){
            $Privileged = True;} 
    elseif(isset($My_Privileges['Location']) 
        && $My_Privileges['Location']['Group'] >= 4
		&& $My_Privleges['Violation']['Group'] >= 4
        && is_numeric($_GET['ID'])){
            $r = $database->query(null,"
                SELECT Tickets.ID
                FROM 
                (
                    (
                        SELECT TicketO.ID
                        FROM   nei.dbo.TicketO
                        WHERE  TicketO.LID     = ?
                               AND TicketO.fWork = ? 
                    ) 
                    UNION ALL
                    (
                        SELECT TicketD.ID
                        FROM   nei.dbo.TicketD
                        WHERE  TicketD.Loc     = ?
                               AND TicketD.fWork = ? 
                    )
                    UNION ALL
                    (
                        SELECT TicketDArchive.ID
                        FROM   nei.dbo.TicketDArchive
                        WHERE  TicketDArchive.Loc     = ?
                               AND TicketDArchive.fWork = ? 
                    )
                ) AS Tickets
            ;", array($_GET['ID'], $My_User['fWork'], $_GET['ID'], $My_User['fWork'], $_GET['ID'], $My_User['fWork']));
            $Privileged = is_array(sqlsrv_fetch_array($r)) ? True : False;}
    elseif(isset($My_Privileges['Location'])
        && $My_Privileges['Location']['Owner'] >= 4
        && is_numeric($_GET['ID'])){
            $r = $database->query(null,"
                SELECT Tickets.ID
                FROM 
                (
                    (
                        SELECT TicketO.ID
                        FROM   nei.dbo.TicketO
                        WHERE  TicketO.LID     = ?
                               AND TicketO.fWork = ? 
                    ) 
                    UNION ALL
                    (
                        SELECT TicketD.ID
                        FROM   nei.dbo.TicketD
                        WHERE  TicketD.Loc     = ?
                               AND TicketD.fWork = ? 
                    )
                    UNION ALL
                    (
                        SELECT TicketDArchive.ID
                        FROM   nei.dbo.TicketDArchive
                        WHERE  TicketDArchive.Loc     = ?
                               AND TicketDArchive.fWork = ? 
                    )
                ) AS Tickets
            ;", array($_GET['ID'], $My_User['fWork'], $_GET['ID'], $My_User['fWork'], $_GET['ID'], $My_User['fWork']));
            $Privileged = is_array(sqlsrv_fetch_array($r)) ? True : False;}
    if(!isset($Connection['ID'])  || !is_numeric($_GET['ID']) || !$Privileged){print json_encode(array('data'=>array()));}
    else {
        $data = array();
        if($My_Privileges['Owner'] >= 4 && $My_Privileges['Group'] >= 4 && $My_Privileges['Other'] >= 4){
            $r = $database->query(null,"
				SELECT *
				FROM
					((SELECT 0					 	   AS ID,
						   Job.fDesc	               AS Name,
						   ''						   AS fDate,
						   'Job Created'   			   AS Status,
						   Loc.Tag                     AS Location,
						   Elev.State                  AS Unit,
						   Job.Custom1                 AS Division,
						   Emp.fFirst + ' ' + Emp.Last AS Mechanic,
						   Job.ID 			           AS Job,
						   Job.Custom16 			   AS Due_Date,
						   '' 						   AS Remarks
					FROM   nei.dbo.Job 	
						   LEFT JOIN nei.dbo.Elev  ON Job.Elev       = Elev.ID
						   LEFT JOIN nei.dbo.Loc   ON Job.Loc        = Loc.Loc
						   LEFT JOIN nei.dbo.Zone  ON Loc.Zone       = Zone.ID
						   LEFT JOIN nei.dbo.Route ON Loc.Route      = Route.ID
						   LEFT JOIN nei.dbo.Emp   ON Route.Mech     = Emp.fWork
					WHERE  ((Job.fDesc LIKE '%CAT%' AND Job.fDesc LIKE '%DEF%')
						   OR Job.fDesc LIKE '%PVT%')
						   AND Job.Status = 0
						   AND Job.Loc = ?)
					UNION ALL
					(SELECT Violation.ID               AS ID,
						   Violation.Name              AS Name,
						   Violation.fdate             AS fDate,
						   Violation.Status            AS Status,
						   Loc.Tag                     AS Location,
						   Elev.State                  AS Unit,
						   Zone.Name                   AS Division,
						   Emp.fFirst + ' ' + Emp.Last AS Mechanic,
						   Violation.Job 			   AS Job,
						   SUBSTRING(Violation.Remarks,CHARINDEX('DUE: ',Violation.Remarks)+5,8) AS Due_Date,
						   '' 						   AS Remarks
					FROM   nei.dbo.Violation
						   LEFT JOIN nei.dbo.Elev  ON Violation.Elev = Elev.ID
						   LEFT JOIN nei.dbo.Loc   ON Violation.Loc  = Loc.Loc
						   LEFT JOIN nei.dbo.Zone  ON Loc.Zone       = Zone.ID
						   LEFT JOIN nei.dbo.Route ON Loc.Route      = Route.ID
						   LEFT JOIN nei.dbo.Emp   ON Route.Mech     = Emp.fWork
						   LEFT JOIN nei.dbo.Job   ON Violation.Job  = Job.ID
					WHERE  Violation.Remarks LIKE '%DUE: [0123456789][0123456789]/[0123456789][0123456789]/[0123456789][0123456789]%'
						   AND Violation.Status <> 'Dismissed'
						   AND Violation.ID     <> 0
						   AND Loc.Loc = ?
						   AND (Violation.Job = 0
								OR 
								(Violation.Job > 0
								AND Job.Status = 0)))) AS Violations
            ;",array($_GET['ID'],$_GET['ID']));
            if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){
				if($array['Due_Date'] == '' || is_null($array['Due_Date'])){
					$array['Due_Date'] = '01/01/99';
				} else {
					if($array['ID'] > 0){
					} else {
						$array['Due_Date'] = substr($array['Due_Date'],0,5) . "." .substr($array['Due_Date'],8,2);
					}
					$array['Due_Date'] = str_replace(".","-",$array['Due_Date']);
				}
                unset($array['Remarks']);
                $data[] = $array;
            }}
			$data2 = array();
			if(count($data) > 0){
				foreach($data as $array){
					$data2[$array['Job']] = $array;
				}
				$data = array();
				foreach($data2 as $Job=>$array){
					$data[] = $array;
				}
			}
        } else {
            $SQL_Units = array();
            if($My_Privileges['Group'] >= 4){
                $r = $database->query(null,"SELECT LElev AS Unit FROM nei.dbo.TicketO LEFT JOIN nei.dbo.Emp ON TicketO.fWork = Emp.fWork WHERE Emp.ID = ?;",array($_SESSION['User']));
                if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$SQL_Units[] = "Violation.Elev='{$array['Unit']}'";}}
                $r = $database->query(null,"SELECT Elev AS Unit FROM nei.dbo.TicketD LEFT JOIN nei.dbo.Emp ON TicketD.fWork = Emp.fWork WHERE Emp.ID = ?;",array($_SESSION['User']));
                if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$SQL_Units[] = "Violation.Elev='{$array['Unit']}'";}}
            }
            if($My_Privileges['Owner'] >= 4){
                $r = $database->query(null,"
                    SELECT Elev.ID AS Unit
                    FROM   nei.dbo.Elev
                           LEFT JOIN nei.dbo.Loc   ON Elev.Loc   = Loc.Loc
                           LEFT JOIN nei.dbo.Route ON Loc.Route  = Route.ID
                           LEFT JOIN nei.dbo.Emp   ON Route.Mech = Emp.fWork
                    WHERE  Emp.ID = ?
                ;",array($_SESSION['User']));
                if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$SQL_Units[] = "Violation.Elev='{$array['Unit']}'";}}
            }
            $SQL_Units = array_unique($SQL_Units);
            if(count($SQL_Units) > 0){
                $SQL_Units = implode(' OR ',$SQL_Units);
                $r = $database->query(null,"
                    SELECT Violation.ID     AS ID,
                           Violation.Name   AS Name,
                           Violation.fdate  AS fDate,
                           Violation.Status AS Status,
                           Loc.Tag          AS Location,
                           Elev.Unit        AS Unit
                    FROM   nei.dbo.Violation
                           LEFT JOIN nei.dbo.Elev ON Violation.Elev = Elev.ID
                           LEFT JOIN nei.dbo.Loc  ON Elev.Loc       = Loc.Loc
                    WHERE {$SQL_Units}
                ;");
                if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}}
            }
        }
        print json_encode(array('data'=>$data));
    }
}?>
