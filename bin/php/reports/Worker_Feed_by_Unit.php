<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( [ 'read_and_close' => true ] ); 
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
if( isset($_SESSION[ 'User' ], $_SESSION[ 'Hash' ] ) ){
    //Connection
    $Connection = $database->query(
        null,
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
    $User = $database->query(
        null,
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
    $r = $database->query(
        null,
        "   SELECT  Privilege.Access, 
                    Privilege.Owner, 
                    Privilege.Group, 
                    Privilege.Other
            FROM    Privilege
            WHERE   Privilege.User_ID = ?;",
        array(
            $_SESSION[ 'User' ]
        )
    );
    $Privileges = array();
    while($Privilege = sqlsrv_fetch_array($r)){$Privileges[$Privilege['Access']] = $Privilege;}
    $Privileged = FALSE;
    if( isset($Privileges['Unit']) 
        && $Privileges['Unit']['Owner'] >= 4 
        && $Privileges['Unit']['Group'] >= 4 
        && $Privileges['Unit']['Other'] >= 4){$Privileged = TRUE;}
    elseif($Privileges['Unit']['Owner'] >= 4 && is_numeric($_GET['ID'])){
        $r = $database->query(  
            null,
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
        $data = array( );
        $r = $database->query(
        	null,
        	"	SELECT 	TicketO.ID                  AS ID,
					   	TicketO.ID                  AS Ticket_ID,
				   		TicketO.fDesc               AS Description,
					   	''                          AS Resolution,
					   	TicketO.CDate               AS Created,
					   	TicketO.DDate               AS Dispatched,
					   	TicketO.EDate               AS Worked,
					   	TicketO.EDate               AS Scheduled,
					   	TicketO.TimeSite            AS On_Site,
					   	TicketO.TimeComp            AS Completed,
					   	TicketO.Who 	            AS Caller,
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
					   	Loc.ID 						AS Customer,
					   	Loc.Tag                     AS Location,
					   	Loc.Address                 AS Address,
					   	Loc.Address                 AS Street,
					   	Loc.City                    AS City,
					   	Loc.State                   AS State,
					   	Loc.Zip                     AS Zip,
					   	Route.Name 		           	AS Route,
					   	Zone.Name 		           	AS Division,
					   	Loc.Maint 		           	AS Maintenance,
					   	Job.ID                      AS Job_ID,
					   	Job.fDesc                   AS Job_Description,
					   	OwnerWithRol.ID             AS Owner_ID,
					   	OwnerWithRol.Name           AS Customer,
					   	Elev.ID 					AS Unit_ID,
					   	Elev.Unit                   AS Unit_Label,
					   	Elev.State                  AS Unit_State,
					   	Elev.fDesc				   	AS Unit_Description,
					   	Elev.Type 				   	AS Unit_Type,
					   	Emp.fFirst                  AS First_Name,
					   	Emp.Last                    AS Last_Name,
					   	Emp.fFirst + ' ' + Emp.Last AS Mechanic,
					   	'Unknown'                   AS ClearPR,
					   	JobType.Type                AS Job_Type
				FROM   	TicketO
					   	LEFT JOIN TickOStatus  	ON TicketO.Assigned = TickOStatus.Ref
					   	LEFT JOIN Loc          	ON TicketO.LID      = Loc.Loc
					   	LEFT JOIN Job          	ON TicketO.Job      = Job.ID
					   	LEFT JOIN Elev         	ON TicketO.LElev    = Elev.ID
					   	LEFT JOIN OwnerWithRol 	ON TicketO.Owner    = OwnerWithRol.ID
					   	LEFT JOIN Emp          	ON TicketO.fWork    = Emp.fWork
					   	LEFT JOIN JobType      	ON Job.Type         = JobType.ID
					   	LEFT JOIN Zone 		  	ON Zone.ID          = Loc.Zone
					   	LEFT JOIN Route		  	ON Route.ID		  	= Loc.Route
				WHERE  		TicketO.LElev = ?
					   	AND (		TicketO.TimeComp IS NULL
					   			OR  TicketO.TimeComp = ''
					   			OR 	TicketO.TimeComp = '1899-12-30 00:00:00.000'
					   	);", 
			array(
				$_GET[ 'ID' ]
			)
		);
		if( $result ){ while( $row = sqlsrv_fetch_array( $result ) ){ $data[ ] = $row; } }
        print json_encode(
        	array(
        		'data' => $data
        	)
        );   
	}
}?>