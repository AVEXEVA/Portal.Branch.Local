<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( [ 'read_and_close' => true ] ); 
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/cgi-bin/php/index.php' );
}
if( isset($_SESSION[ 'User' ], $_SESSION[ 'Hash' ] ) ){
    //Connection
    $Connection = sqlsrv_query(
        $NEI,
        "   SELECT  Connection.* 
            FROM    Connection 
            WHERE   Connection.Connector = ? 
                    AND Connection.Hash = ?;",
        array(
            $_SESSION['User'],
            $_SESSION['Hash']
        )
    );
    $Connection = sqlsrv_fetch_array($Connection);

    //User
    $User = sqlsrv_query(
        $NEI,
        "   SELECT  Emp.*, 
                    Emp.fFirst  AS First_Name, 
                    Emp.Last    AS Last_Name 
            FROM    Emp 
            WHERE   Emp.ID = ?;",
        array(
            $_SESSION['User']
        )
    );
    $User = sqlsrv_fetch_array($User);

    //Privileges
    $r = sqlsrv_query(
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
    while($Privilege = sqlsrv_fetch_array($r)){$Privileges[$Privilege['Access_Table']] = $Privilege;}
    $Privileged = FALSE;
    if( isset($Privileges['Unit']) 
        && $Privileges['Unit']['User_Privilege'] >= 4 
        && $Privileges['Unit']['Group_Privilege'] >= 4 
        && $Privileges['Unit']['Other_Privilege'] >= 4){$Privileged = TRUE;}
    elseif($Privileges['Unit']['User_Privilege'] >= 4 && is_numeric($_GET['ID'])){
        $r = sqlsrv_query(  
            $NEI,
            "   SELECT  Count( Ticket.Count ) AS Count 
                FROM    (
                            SELECT  Ticket.Unit,
                                    Ticket.Field,
                                    Sum( Ticket.Count ) AS Count
                            FROM (
                                (
                                    SELECT      TicketO.LElev AS Unit,
                                                TicketO.fWork AS Field,
                                                Count( TicketO.ID ) AS Count
                                    FROM        TicketO
                                    GROUP BY    TicketO.LElev,
                                                TicketO.fWork
                                ) UNION ALL (
                                    SELECT      TicketD.Elev AS Unit,
                                                TicketD.fWork AS Field, 
                                                Count( TicketD.ID ) AS Count
                                    FROM        TicketD
                                    GROUP BY    TicketD.Elev,
                                                TicketD.fWork
                                )
                            ) AS Ticket
                            GROUP BY    Ticket.Unit,
                                        Ticket.Field
                        ) AS Ticket
                        LEFT JOIN Emp AS Employee ON Ticket.Field = Employee.fWork
                WHERE   Employee.ID = ?
                        AND Ticket.Unit = ?;",
            array( 
                $_SESSION[ 'User' ],
                $_GET[ 'ID' ]
            )
        );
        $Tickets = 0;
        if ( $r ){ $Tickets = sqlsrv_fetch_array( $r )[ 'Count' ]; }
        $Privileged =  $Tickets > 0 ? true : false;
    }
    if(     !isset( $Connection[ 'ID' ] )  
        ||  !$Privileged 
        ||  !is_numeric( $_GET[ 'ID' ] ) ){
            ?><html><head><script>document.location.href="../login.php?Forward=unit<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {
        $r = sqlsrv_query($NEI,"
			SELECT Tickets.*,
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
				   Elev.Unit         AS Unit_Label,
				   Elev.State        AS Unit_State,
				   Emp.fFirst        AS Worker_First_Name,
				   Emp.Last          AS Worker_Last_Name,
				   Emp.fFirst + ' ' + Emp.Last AS Mechanic,
				   'Unknown'         AS ClearPR,
				   JobType.Type      AS Job_Type,
				   Tickets.Date      AS Worked,
				   Route.Name        AS Route,
				   Zone.Name         AS Division
			FROM (
					(
						SELECT TicketO.ID       AS ID,
							TicketO.fDesc    AS Description,
							''               AS Resolution,
							TicketO.CDate    AS Created,
							TicketO.DDate    AS Dispatched,
							TicketO.EDate    AS Date,
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
							0                AS Doubletime,
							TicketO.fBy      AS Taken_By
					 FROM   TicketO
							LEFT JOIN TickOStatus ON TicketO.Assigned = TickOStatus.Ref
					 WHERE  TicketO.LElev = ?
					) UNION ALL(
						SELECT TicketD.ID       AS ID,
							TicketD.fDesc    AS Description,
							TicketD.DescRes  AS Resolution,
							TicketD.CDate    AS Created,
							TicketD.DDate    AS Dispatched,
							TicketD.EDate    AS Date,
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
							TicketD.DT       AS Doubletime,
							TicketD.fBy      AS Taken_By
					 FROM   TicketD
							LEFT JOIN Loc ON TicketD.Loc = Loc.Loc
					 WHERE  TicketD.Elev = ?
					)
				) AS Tickets
				LEFT JOIN Loc          ON Tickets.Location = Loc.Loc
				LEFT JOIN Job          ON Tickets.Job      = Job.ID
				LEFT JOIN Elev         ON Tickets.Unit     = Elev.ID
				LEFT JOIN OwnerWithRol ON Tickets.Owner    = OwnerWithRol.ID
				LEFT JOIN Emp          ON Tickets.Mechanic = Emp.fWork
				LEFT JOIN JobType      ON Job.Type         = JobType.ID
				LEFT JOIN Route        ON Loc.Route        = Route.ID
				LEFT JOIN Zone         ON Loc.Zone         = Zone.ID
		",array($_GET['ID'],$_GET['ID'],$_GET['ID']),array("Scrollable"=>SQLSRV_CURSOR_KEYSET));
		$data = array();
		$row_count = sqlsrv_num_rows( $r );
		if($r){
			while($i < $row_count){
				$Ticket = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
				if(is_array($Ticket) && $Ticket != array()){
					//Tags
					$Tags = array();
					if(strpos($Ticket['Description'],"s/d") !== false || strpos($Ticket['Description'],"S/D") !== false || strpos($Ticket['Description'],"shutdown") !== false){
						$Tags[] = "Shutdown";
					}
					if($Ticket['Level'] == 10){
						$Tags[] = "Maintenance";
					}
					if($Ticket['Level'] == 1){
						$Tags[] = "Service Call";
					}
					$Ticket['Tags'] = count($Tags) > 0 ? implode(", ",$Tags) : null;

					//On Site / Completed Time
					if($Ticket['On_Site'] == NULL || $Ticket['On_Site'] == ''){
						$Ticket['On_Site'] = 'None';
					} else {
						$Ticket['On_Site'] = date("H:i:s",strtotime($Ticket['On_Site']));
					}
					if($Ticket['Completed'] == NULL || $Ticket['Completed'] == ''){
						$Ticket['Completed'] = 'None';
					} else {
						$Ticket['Completed'] = date("H:i:s",strtotime($Ticket['Completed']));
					}
					if($Ticket['Created'] == NULL || $Ticket['Created'] == ''){
						$Ticket['Created'] = 'None';
					} else {
						$Ticket['Created'] = date("m/d/Y H:i:s",strtotime($Ticket['Created']));
					}
					if($Ticket['Dispatched'] == NULL || $Ticket['Dispatched'] == ''){
						$Ticket['Dispatched'] = 'None';
					} else {
						$Ticket['Dispatched'] = date("m/d/Y H:i:s",strtotime($Ticket['Dispatched']));
					}
					$data[] = $Ticket;
				}
				$i++;
			}
		}
		print json_encode(array('data'=>$data));
    }
}
sqlsrv_close($NEI);
?>
