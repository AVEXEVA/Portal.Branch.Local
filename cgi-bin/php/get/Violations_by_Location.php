<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( ); 
    require( '/var/www/nouveautexas.com/html/portal/cgi-bin/php/index.php' );
}
if( isset( $_SESSION[ 'User' ], $_SESSION[ 'Hash' ] ) ){
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
    if( isset($Privileges['Location']) 
        && $Privileges['Location']['User_Privilege'] >= 4 
        && $Privileges['Location']['Group_Privilege'] >= 4 
        && $Privileges['Location']['Other_Privilege'] >= 4){$Privileged = TRUE;}
    elseif($Privileges['Location']['User_Privilege'] >= 4 && is_numeric($_GET['ID'])){
        $r = sqlsrv_query(  
            $NEI,
            "   SELECT  Count( Ticket.ID ) AS Count 
                FROM    (
                            SELECT  Ticket.ID,
                                    Ticket.Location,
                                    Ticket.Field,
                                    Sum( Ticket.Count ) AS Count
                            FROM (
                                (
                                    SELECT      TicketO.ID,
                                                TicketO.LID AS Location,
                                                TicketO.fWork AS Field,
                                                Count( TicketO.ID ) AS Count
                                    FROM        TicketO
                                    GROUP BY    TicketO.ID,
                                                TicketO.LID,
                                                TicketO.fWork
                                ) UNION ALL (
                                    SELECT      TicketD.ID,
                                                TicketD.Loc AS Location,
                                                TicketD.fWork AS Field, 
                                                Count( TicketD.ID ) AS Count
                                    FROM        TicketD
                                    GROUP BY    TicketD.ID,
                                                TicketD.Loc,
                                                TicketD.fWork
                                )
                            ) AS Ticket
                            GROUP BY    Ticket.ID,
                                        Ticket.Location,
                                        Ticket.Field
                        ) AS Ticket
                        LEFT JOIN Emp AS Employee ON Ticket.Field = Employee.fWork
                WHERE   Employee.ID = ?
                        AND Ticket.Location = ?;",
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
            	print json_encode( array( 'data' => array( ) ) );
    } else {
		$data = array();
		if(isset($Privileges['Location']) && $Privileges['Location']['Other_Privilege'] >= 4){
			$r = sqlsrv_query(
				$NEI,
				"	SELECT Violation.ID      AS ID,
						   Violation.Name    AS Name,
						   Violation.fDate   AS Date,
						   Violation.Status  AS Status,
						   Violation.Remarks AS Description
					FROM   Violation
						   LEFT JOIN Elev ON Violation.Elev = Elev.ID
						   LEFT JOIN Loc  ON Elev.Loc       = Loc.Loc
					WHERE  Loc.Loc = ?;",
				array(
					$_GET[ 'ID' ]
				)
			);
		} else {
			$r = sqlsrv_query(
				$NEI,
				"	SELECT Violation.ID      AS ID,
						   Violation.Name    AS Name,
						   Violation.fDate   AS Date,
						   Violation.Status  AS Status,
						   Violation.Remarks AS Description
					FROM   Violation
						   LEFT JOIN Elev ON Violation.Elev = Elev.ID
						   LEFT JOIN Loc  ON Elev.Loc       = Loc.Loc
					WHERE  Loc.Loc = ?
						   AND (	Violation.Status    = 'Open' 
						   		OR 	Violation.Status = 'Job Created');",
				array(
					$_GET[ 'ID' ]
				)
			);
		}
		if( $result ){ while( $row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC ) ){ $data[ ] = $row; } }
		print json_encode(
			array(
				'data' => $data
			)
		);
    }
}?>