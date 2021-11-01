<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( ); 
    require( '/var/www/nouveautexas.com/html/portal/cgi-bin/php/index.php' );
}
if( isset( $_SESSION[ 'User' ], $_SESSION[ 'Hash' ] ) ){
    //Establish Connection
    $result = sqlsrv_query(
        $NEI,
        "   SELECT  * 
            FROM    Connection 
            WHERE       Connection.Connector = ? 
                    AND Connection.Hash = ?;", 
        array(
            $_SESSION[ 'User' ],
            $_SESSION[ 'Hash' ]
        )
    );
    $Connection = sqlsrv_fetch_array( $result );
    //Establish User
    $result    = sqlsrv_query(
        $NEI,
        "   SELECT  Emp.*, 
                    Emp.fFirst AS First_Name, 
                    Emp.Last   AS Last_Name 
            FROM    Emp
            WHERE   Emp.ID = ?;", 
        array(
            $_SESSION[ 'User' ]
        )
    );
    $User = sqlsrv_fetch_array( $User ); 
    //Establish Privileges
    $result = sqlsrv_query(
        $NEI,
        "   SELECT  Privilege.Access_Table, 
                    Privilege.User_Privilege, 
                    Privilege.Group_Privilege, 
                    Privilege.Other_Privilege
            FROM    Privilege
            WHERE   Privilege.User_ID = ?;",
        array(
            $_SESSION[ 'User' ]
        )
    );
    $Privileges = array();
    while( $Privilege = sqlsrv_fetch_array( $result ) ){ $Privileges[ $Privilege[ 'Access_Table' ] ] = $Privilege; }
    $Privileged = False;
    if(     isset( $Privileges[ 'Location' ] ) 
        &&  $Privileges[ 'Location' ][ 'Other_Privilege' ]  >= 4
	  	&&  $Privileges[ 'Violation' ][ 'Other_Privilege' ] >= 4){
                $Privileged = True;} 
    elseif( isset($Privileges[ 'Location']) 
        &&  $Privileges[ 'Location' ][ 'Group_Privilege']  >= 4
		&&  $Privileges[ 'Violation' ][ 'Group_Privilege'] >= 4
        &&  is_numeric( $_GET[ 'ID' ] ) ){
            $result = sqlsrv_query(
                $NEI,
                "   SELECT  Tickets.ID
                    FROM 
                            (
                                (
                                    SELECT  TicketO.ID,
                                            TicketO.fWork
                                            TicketO.LID AS Location
                                    FROM    TicketO
                                    WHERE       TicketO.LID     = ?
                                            AND TicketO.fWork = ? 
                                ) 
                                UNION ALL
                                (
                                    SELECT  TicketD.ID,
                                            TicketD.fWork,
                                            TicketD.Loc AS Location
                                    FROM    TicketD
                                )
                            ) AS Tickets
                            LEFT JOIN Emp ON Tickets.fWork = Emp.fWork 
                    WHERE       Tickets.Location = ?
                            AND Emp.ID = ?;", 
                array(
                    $_GET['ID'], 
                    $_SESSION[ 'User' ]
                )
            );
            $Privileged = is_array( sqlsrv_fetch_array( $result ) ) ? true : false;
    }
    if(     !isset( $Connection[ 'ID' ] )  
        ||  !is_numeric( $_GET[ 'ID' ] ) 
        ||  !$Privileged ){
                print json_encode(
                    array(
                        'data'=>array( )
                    )
                );
    } else {
        $data = array();
        if($Privileges['User_Privilege'] >= 4 && $Privileges['Group_Privilege'] >= 4 && $Privileges['Other_Privilege'] >= 4){
            $r = sqlsrv_query($NEI,"
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
					FROM   Job 	
						   LEFT JOIN Elev  ON Job.Elev       = Elev.ID
						   LEFT JOIN Loc   ON Job.Loc        = Loc.Loc
						   LEFT JOIN Zone  ON Loc.Zone       = Zone.ID
						   LEFT JOIN Route ON Loc.Route      = Route.ID
						   LEFT JOIN Emp   ON Route.Mech     = Emp.fWork
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
					FROM   Violation
						   LEFT JOIN Elev  ON Violation.Elev = Elev.ID
						   LEFT JOIN Loc   ON Violation.Loc  = Loc.Loc
						   LEFT JOIN Zone  ON Loc.Zone       = Zone.ID
						   LEFT JOIN Route ON Loc.Route      = Route.ID
						   LEFT JOIN Emp   ON Route.Mech     = Emp.fWork
						   LEFT JOIN Job   ON Violation.Job  = Job.ID
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
            if($Privileges['Group_Privilege'] >= 4){
                $r = sqlsrv_query($NEI,"SELECT LElev AS Unit FROM TicketO LEFT JOIN Emp ON TicketO.fWork = Emp.fWork WHERE Emp.ID = ?;",array($_SESSION['User']));
                if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$SQL_Units[] = "Violation.Elev='{$array['Unit']}'";}}
                $r = sqlsrv_query($NEI,"SELECT Elev AS Unit FROM TicketD LEFT JOIN Emp ON TicketD.fWork = Emp.fWork WHERE Emp.ID = ?;",array($_SESSION['User']));
                if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$SQL_Units[] = "Violation.Elev='{$array['Unit']}'";}}
            }
            if($Privileges['User_Privilege'] >= 4){
                $r = sqlsrv_query($NEI,"
                    SELECT Elev.ID AS Unit
                    FROM   Elev
                           LEFT JOIN Loc   ON Elev.Loc   = Loc.Loc
                           LEFT JOIN Route ON Loc.Route  = Route.ID
                           LEFT JOIN Emp   ON Route.Mech = Emp.fWork
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
                    FROM   Violation
                           LEFT JOIN Elev ON Violation.Elev = Elev.ID
                           LEFT JOIN Loc  ON Elev.Loc       = Loc.Loc
                    WHERE {$SQL_Units}
                ;");
                if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}}
            }
        }
        print json_encode(array('data'=>$data));
    }
}?>
