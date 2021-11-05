<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( [ 'read_and_close' => true ] ); 
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/cgi-bin/php/index.php' );
}
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    //Connection
    $result = $database->query(
        null, 
        "   SELECT  * 
            FROM    Connection 
            WHERE       Connector = ? 
                    AND Hash = ?;",
        array(
            $_SESSION['User'],
            $_SESSION['Hash']
        )
    );
    $Connection = sqlsrv_fetch_array($result);
    //User
    $result = $database->query(
        null,
        "   SELECT  *, 
                    fFirst AS First_Name, 
                    Last as Last_Name 
            FROM    Emp 
            WHERE   ID= ?;",
        array(
            $_SESSION[ 'User' ]
        )
    );
    $User   = sqlsrv_fetch_array( $result );
    //Privileges
    $result = $database->query(null,
        "   SELECT  Privilege.*
            FROM    Privilege
            WHERE   Privilege.User_ID = ?;",
        array(
            $_SESSION[ 'User' ]
        )
    );
    $Privileges = array();
    $Privileged = false;
    while( $Privilege = sqlsrv_fetch_array( $result ) ){ $Privileges[ $Privilege[ 'Access_Table' ] ] = $Privilege; }
    if(     isset($Privileges['Customer']) 
        &&  $Privileges[ 'Customer' ][ 'User_Privilege' ]  >= 4 
        &&  $Privileges[ 'Customer' ][ 'Group_Privilege' ] >= 4 
        &&  $Privileges[ 'Customer' ][ 'Other_Privilege' ] >= 4){
                $Privileged = true;}
    if(     !isset($Connection['ID'])  
        ||  !is_numeric($_GET['ID']) 
        || !$Privileged 
    ){ print json_encode( array( 'data' => array( ) ) ); }
    else {
        $r = $database->query(null,"
			SELECT Tickets.*,
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
				   Elev.Unit                   AS Unit_Label,
				   Elev.State                  AS Unit_State,
				   Elev.fDesc				   AS Unit_Description,
				   Elev.Type 				   AS Unit_Type,
				   Emp.fFirst                  AS First_Name,
				   Emp.Last                    AS Last_Name,
				   Emp.fFirst + ' ' + Emp.Last AS Mechanic,
				   'Unknown'                   AS ClearPR,
				   JobType.Type                AS Job_Type
			FROM (
					(SELECT TicketO.ID       AS ID,
							TicketO.fDesc    AS Description,
							''               AS Resolution,
							TicketO.CDate    AS Created,
							TicketO.DDate    AS Dispatched,
							TicketO.EDate    AS Worked,
							TicketO.TimeSite AS On_Site,
							TicketO.TimeComp AS Completed,
							TicketO.Who 	 AS Caller,
							TicketO.fBy      AS Reciever,
							TicketO.Level    AS Level,
							TicketO.Cat      AS Category,
							TicketO.LID      AS Location,
							TicketO.Job      AS Job,
							TicketO.LElev    AS Unit,
							TicketO.Owner    AS Owner,
							TicketO.fWork    AS Mechanic,
							TickOStatus.Type AS Status,
							0                AS Total,
							0                AS Regular,
							0                AS Overtime,
							0                AS Doubletime
					 FROM   TicketO
							LEFT JOIN TickOStatus ON TicketO.Assigned = TickOStatus.Ref
					 WHERE  TicketO.Owner = ?
					)
					UNION ALL
					(SELECT TicketD.ID       AS ID,
							TicketD.fDesc    AS Description,
							TicketD.DescRes  AS Resolution,
							TicketD.CDate    AS Created,
							TicketD.DDate    AS Dispatched,
							TicketD.EDate    AS Worked,
							TicketD.TimeSite AS On_Site,
							TicketD.TimeComp AS Completed,
							TicketD.Who 	 AS Caller,
							TicketD.fBy      AS Reciever,
							TicketD.Level    AS Level,
							TicketD.Cat      AS Category,
							TicketD.Loc      AS Location,
							TicketD.Job      AS Job,
							TicketD.Elev     AS Unit,
							Loc.Owner        AS Owner,
							TicketD.fWork    AS Mechanic,
							'Completed'      AS Status,
							TicketD.Total    AS Total,
							TicketD.Reg      AS Regular,
							TicketD.OT       AS Overtime,
							TicketD.DT       AS Doubletime
					 FROM   TicketD
							LEFT JOIN Loc ON TicketD.Loc = Loc.Loc
					 WHERE  Loc.Owner = ?
					)
				) AS Tickets
				LEFT JOIN Loc          ON Tickets.Location = Loc.Loc
				LEFT JOIN Job          ON Tickets.Job      = Job.ID
				LEFT JOIN Elev         ON Tickets.Unit     = Elev.ID
				LEFT JOIN OwnerWithRol ON Tickets.Owner    = OwnerWithRol.ID
				LEFT JOIN Emp          ON Tickets.Mechanic = Emp.fWork
				LEFT JOIN JobType      ON Job.Type         = JobType.ID
				LEFT JOIN Zone 		   ON Zone.ID          = Loc.Zone
				LEFT JOIN Route		   ON Route.ID		   = Loc.Route
			WHERE Tickets.Owner = ?
			ORDER BY Tickets.ID DESC
		",array($_GET['ID'],$_GET['ID'],$_GET['ID'],$_GET['ID']),array("Scrollable"=>SQLSRV_CURSOR_KEYSET));
		$data = array();
		$row_count = sqlsrv_num_rows( $r );
		if($r){
			$i = 0;
			while($i < $row_count){
				$Ticket = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
				if(is_array($Ticket) && $Ticket != array()){
					$Tags = array();
					if(strpos($Ticket['Description'],"s/d") || strpos($Ticket['Description'],"S/D") || strpos($Ticket['Description'],"shutdown")){
						$Tags[] = "Shutdown";
					}
					if($Ticket['Level'] == 10){
						$Tags[] = "Maintenance";
					}
					if($Ticket['Level'] == 1){
						$Tags[] = "Service Call";
					}
					$Ticket['Tags'] = implode(", ",$Tags);
					$data[] = $Ticket;
				}
				$i++;
			}
		}
		print json_encode(array('data'=>$data));
	}
} elseif(isset($_SESSION['Customer'],$_SESSION['Hash'])){
  $r = $database->query(null,"
    SELECT *
    FROM   Connection
    WHERE  Connection.Connector = ?
         AND Connection.Hash = ?
  ;", array($_SESSION['Customer'],$_SESSION['Hash']));
    $Connection = sqlsrv_fetch_array($r);
  if(isset($Connection['ID'])){
    $r = $database->query(null,
    " SELECT Top 100
           Tickets.*,
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
           Elev.Unit                   AS Unit_Label,
           Elev.State                  AS Unit_State,
           Elev.fDesc				   AS Unit_Description,
           Elev.Type 				   AS Unit_Type,
           Emp.fFirst                  AS First_Name,
           Emp.Last                    AS Last_Name,
           Emp.fFirst + ' ' + Emp.Last AS Mechanic,
           'Unknown'                   AS ClearPR,
           JobType.Type                AS Job_Type
      FROM (
          (SELECT Top 100 TicketO.ID       AS ID,
              TicketO.fDesc    AS Description,
              ''               AS Resolution,
              TicketO.CDate    AS Created,
              TicketO.DDate    AS Dispatched,
              TicketO.EDate    AS Worked,
              TicketO.TimeSite AS On_Site,
              TicketO.TimeComp AS Completed,
              TicketO.Who 	 AS Caller,
              TicketO.fBy      AS Reciever,
              TicketO.Level    AS Level,
              TicketO.Cat      AS Category,
              TicketO.LID      AS Location,
              TicketO.Job      AS Job,
              TicketO.LElev    AS Unit,
              TicketO.Owner    AS Owner,
              TicketO.fWork    AS Mechanic,
              TickOStatus.Type AS Status,
              0                AS Total,
              0                AS Regular,
              0                AS Overtime,
              0                AS Doubletime
           FROM   TicketO
              LEFT JOIN TickOStatus ON TicketO.Assigned = TickOStatus.Ref
           WHERE  TicketO.Owner = ?
          )
          UNION ALL
          (SELECT Top 100 TicketD.ID       AS ID,
              TicketD.fDesc    AS Description,
              TicketD.DescRes  AS Resolution,
              TicketD.CDate    AS Created,
              TicketD.DDate    AS Dispatched,
              TicketD.EDate    AS Worked,
              TicketD.TimeSite AS On_Site,
              TicketD.TimeComp AS Completed,
              TicketD.Who 	 AS Caller,
              TicketD.fBy      AS Reciever,
              TicketD.Level    AS Level,
              TicketD.Cat      AS Category,
              TicketD.Loc      AS Location,
              TicketD.Job      AS Job,
              TicketD.Elev     AS Unit,
              Loc.Owner        AS Owner,
              TicketD.fWork    AS Mechanic,
              'Completed'      AS Status,
              TicketD.Total    AS Total,
              TicketD.Reg      AS Regular,
              TicketD.OT       AS Overtime,
              TicketD.DT       AS Doubletime
           FROM   TicketD
              LEFT JOIN Loc ON TicketD.Loc = Loc.Loc
           WHERE  Loc.Owner = ?
          )
        ) AS Tickets
        LEFT JOIN Loc          ON Tickets.Location = Loc.Loc
        LEFT JOIN Job          ON Tickets.Job      = Job.ID
        LEFT JOIN Elev         ON Tickets.Unit     = Elev.ID
        LEFT JOIN OwnerWithRol ON Tickets.Owner    = OwnerWithRol.ID
        LEFT JOIN Emp          ON Tickets.Mechanic = Emp.fWork
        LEFT JOIN JobType      ON Job.Type         = JobType.ID
        LEFT JOIN Zone 		   ON Zone.ID          = Loc.Zone
        LEFT JOIN Route		   ON Route.ID		   = Loc.Route
      WHERE Tickets.Owner = ?
      ORDER BY Tickets.ID DESC
    ",array($_SESSION['Customer'],$_SESSION['Customer'],$_SESSION['Customer'],$_SESSION['Customer']),array("Scrollable"=>SQLSRV_CURSOR_KEYSET));
    $data = array();
    $row_count = sqlsrv_num_rows( $r );
    if($r){
      $i = 0;
      while($i < $row_count){
        $Ticket = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
        if(is_array($Ticket) && $Ticket != array()){
          $Tags = array();
          if(strpos($Ticket['Description'],"s/d") || strpos($Ticket['Description'],"S/D") || strpos($Ticket['Description'],"shutdown")){
            $Tags[] = "Shutdown";
          }
          if($Ticket['Level'] == 10){
            $Tags[] = "Maintenance";
          }
          if($Ticket['Level'] == 1){
            $Tags[] = "Service Call";
          }
          $Ticket['Tags'] = implode(", ",$Tags);
          $data[] = $Ticket;
        }
        $i++;
      }
    }
    print json_encode(array('data'=>$data));
  }
}?>
