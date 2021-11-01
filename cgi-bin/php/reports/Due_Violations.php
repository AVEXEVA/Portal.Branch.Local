<?php
session_start();
require('../index.php');
setlocale(LC_MONETARY, 'en_US');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"
        SELECT *
        FROM   nei.dbo.Connection
        WHERE  Connection.Connector = ?
               AND Connection.Hash = ?
    ;", array($_SESSION['User'],$_SESSION['Hash']));
    $Connection = sqlsrv_fetch_array($r);
    $My_User    = sqlsrv_query($NEI,"
        SELECT Emp.*,
               Emp.fFirst AS First_Name,
               Emp.Last   AS Last_Name
        FROM   nei.dbo.Emp
        WHERE  Emp.ID = ?
    ;", array($_SESSION['User']));
    $My_User = sqlsrv_fetch_array($My_User);
    $My_Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
    $r = sqlsrv_query($Portal,"
        SELECT Privilege.Access_Table,
               Privilege.User_Privilege,
               Privilege.Group_Privilege,
               Privilege.Other_Privilege
        FROM   Portal.dbo.Privilege
        WHERE  Privilege.User_ID = ?
    ;",array($_SESSION['User']));
    $My_Privileges = array();
    while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
    $Privileged = False;
    if( isset($My_Privileges['Violation'])
        && $My_Privileges['Violation']['Other_Privilege'] >= 4){
            $Privileged = True;}
    elseif(isset($My_Privileges['Violation'])
        && $My_Privileges['Violation']['Group_Privilege'] >= 4
        && is_numeric($_GET['ID'])){
            $r = sqlsrv_query($NEI,"
                SELECT Tickets.ID
                FROM
                (
                    (
                        SELECT TicketO.ID
                        FROM   nei.dbo.TicketO
                        WHERE  TicketO.Owner     = ?
                               AND TicketO.fWork = ?
                    )
                    UNION ALL
                    (
                        SELECT TicketD.ID
                        FROM   nei.dbo.TicketD
                        WHERE  TicketD.Owner     = ?
                               AND TicketD.fWork = ?
                    )
                    UNION ALL
                    (
                        SELECT TicketDArchive.ID
                        FROM   nei.dbo.TicketDArchive
                        WHERE  TicketDArchive.Owner     = ?
                               AND TicketDArchive.fWork = ?
                    )
                ) AS Tickets
            ;", array($_GET['ID'], $My_User['fWork'], $_GET['ID'], $My_User['fWork'], $_GET['ID'], $My_User['fWork']));
            $Privileged = is_array(sqlsrv_fetch_array($r)) ? True : False;}
    elseif(isset($My_Privileges['Violation'])
        && $My_Privileges['Violation']['User_Privilege'] >= 4
        && is_numeric($_GET['ID'])){
            $r = sqlsrv_query($NEI,"
                SELECT Tickets.ID
                FROM
                (
                    (
                        SELECT TicketO.ID
                        FROM   nei.dbo.TicketO
                        WHERE  TicketO.Owner     = ?
                               AND TicketO.fWork = ?
                    )
                    UNION ALL
                    (
                        SELECT TicketD.ID
                        FROM   nei.dbo.TicketD
                        WHERE  TicketD.Owner     = ?
                               AND TicketD.fWork = ?
                    )
                    UNION ALL
                    (
                        SELECT TicketDArchive.ID
                        FROM   nei.dbo.TicketDArchive
                        WHERE  TicketDArchive.Owner     = ?
                               AND TicketDArchive.fWork = ?
                    )
                ) AS Tickets
            ;", array($_GET['ID'], $My_User['fWork'], $_GET['ID'], $My_User['fWork'], $_GET['ID'], $My_User['fWork']));
            $Privileged = is_array(sqlsrv_fetch_array($r)) ? True : False;}
    if(!isset($Connection['ID'])  || !$Privileged){print json_encode(array('data'=>array()));}
    else {
        $data = array();
        if($My_Privileges['Violation']['User_Privilege'] >= 4 && $My_Privileges['Violation']['Group_Privilege'] >= 4 && $My_Privileges['Violation']['Other_Privilege'] >= 4){
            $r = sqlsrv_query($NEI,"
				SELECT *
				FROM
					((SELECT 0					 	   AS ID,
						   Job.fDesc	               AS Name,
						   ''						   AS fDate,
						   'Job Created'   			   AS Status,
						   Loc.Tag                     AS Location,
						   Elev.State                  AS Unit,
						   Zone.Name                  AS Division,
						   Emp.fFirst + ' ' + Emp.Last AS Mechanic,
						   Job.ID 			           AS Job,
						   Job.Custom16 			   AS Due_Date,
						   '' 						   AS Remarks,
                           Terr.Name                   AS Territory
					FROM   nei.dbo.Job
						   LEFT JOIN nei.dbo.Elev  ON Job.Elev       = Elev.ID
						   LEFT JOIN nei.dbo.Loc   ON Job.Loc        = Loc.Loc
						   LEFT JOIN nei.dbo.Zone  ON Loc.Zone       = Zone.ID
						   LEFT JOIN nei.dbo.Route ON Loc.Route      = Route.ID
						   LEFT JOIN nei.dbo.Emp   ON Route.Mech     = Emp.fWork
                           LEFT JOIN nei.dbo.Terr  ON Loc.Terr       = Terr.ID
					WHERE  ((Job.fDesc LIKE '%CAT%' AND Job.fDesc LIKE '%DEF%')
						   OR Job.fDesc LIKE '%PVT%')
						   AND Job.Status = 0)
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
						   '' 						   AS Remarks,
                           Terr.Name                   AS Territory
					FROM   nei.dbo.Violation
						   LEFT JOIN nei.dbo.Elev  ON Violation.Elev = Elev.ID
						   LEFT JOIN nei.dbo.Loc   ON Violation.Loc  = Loc.Loc
						   LEFT JOIN nei.dbo.Zone  ON Loc.Zone       = Zone.ID
						   LEFT JOIN nei.dbo.Route ON Loc.Route      = Route.ID
						   LEFT JOIN nei.dbo.Emp   ON Route.Mech     = Emp.fWork
						   LEFT JOIN nei.dbo.Job   ON Violation.Job  = Job.ID
                           LEFT JOIN nei.dbo.Terr  ON Loc.Terr       = Terr.ID
					WHERE  Violation.Remarks LIKE '%DUE: [0123456789][0123456789]/[0123456789][0123456789]/[0123456789][0123456789]%'
						   AND Violation.Status <> 'Dismissed'
						   AND Violation.ID     <> 0
						   AND (Violation.Job = 0
								OR
								(Violation.Job > 0
								AND Job.Status = 0)))) AS Violations
            ;",array());

      if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){
				if($array['Due_Date'] == '' || is_null($array['Due_Date'])){
					$array['Due_Date'] = '????-??-??';
          continue;
				} else {
            $array['Due_Date'] = str_replace('-','/',$array['Due_Date']);
            $array['Due_Date'] = str_replace(".","/",$array['Due_Date']);
            $date1 = new DateTime(date('Y-m-d',strtotime($array['Due_Date'])));
            $diff = $date1->diff(new DateTime(date('Y-m-d',strtotime('now'))));
            //$array['Days_Past_Due'] = $diff->m * 30;
            $array['Days_Past_Due'] = $diff->format("%a");
            //echo "\n" . date('Y-m-d',strtotime($array['Due_Date']));
            if(date('Y-m-d',strtotime('now')) <= date('Y-m-d',strtotime($array['Due_Date']))){
              $array['Penalty'] = 0;
            } elseif($array['Days_Past_Due'] < 29){
              $array['Penalty'] = 150;
            } elseif($array['Days_Past_Due'] < 59){
              $array['Penalty'] = 300;
            } elseif($array['Days_Past_Due'] < 89){
              $array['Penalty'] = 450;
            } elseif($array['Days_Past_Due'] < 128){
              $array['Penalty'] = 600;
            } elseif($array['Days_Past_Due'] < 148){
              $array['Penalty'] = 750;
            } elseif($array['Days_Past_Due'] < 178){
              $array['Penalty'] = 900;
            } elseif($array['Days_Past_Due'] < 208){
              $array['Penalty'] = 1050;
            } elseif($array['Days_Past_Due'] < 238){
              $array['Penalty'] = 1200;
            } elseif($array['Days_Past_Due'] < 267){
              $array['Penalty'] = 1350;
            } elseif($array['Days_Past_Due'] < 296){
              $array['Penalty'] = 1500;
            } elseif($array['Days_Past_Due'] < 326){
              $array['Penalty'] = 1650;
            } elseif($array['Days_Past_Due'] < 356){
              $array['Penalty'] = 1800;
            } else {
              $array['Penalty'] = 3000;
            }
            $array['Due_Date'] = date('Y-m-d',strtotime($array['Due_Date']));
            //echo "PENALTY:{$array['Penalty']}  DAYS:{$array['Days_Past_Due']}";
            //}

				}
        //unset($array['Remarks']);
        //$array['Penalty'] = isset($array['Penalty']) ? $array['Penalty'] : '-1';
        if(!isset($array['Penalty'])){
          continue;
        }
        $array['Due_Date'] = date("m/d/Y",strtotime($array['Due_Date']));
        $array['Days_Past_Due'] = isset($array['Days_Past_Due']) ? $array['Days_Past_Due'] : '-1';
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
            if($My_Privileges['Group_Privilege'] >= 4){
                $r = sqlsrv_query($NEI,"SELECT LElev AS Unit FROM nei.dbo.TicketO LEFT JOIN nei.dbo.Emp ON TicketO.fWork = Emp.fWork WHERE Emp.ID = ?;",array($_SESSION['User']));
                if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$SQL_Units[] = "Violation.Elev='{$array['Unit']}'";}}
                $r = sqlsrv_query($NEI,"SELECT Elev AS Unit FROM nei.dbo.TicketD LEFT JOIN nei.dbo.Emp ON TicketD.fWork = Emp.fWork WHERE Emp.ID = ?;",array($_SESSION['User']));
                if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$SQL_Units[] = "Violation.Elev='{$array['Unit']}'";}}
            }
            if($My_Privileges['User_Privilege'] >= 4){
                $r = sqlsrv_query($NEI,"
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
                $r = sqlsrv_query($NEI,"
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
