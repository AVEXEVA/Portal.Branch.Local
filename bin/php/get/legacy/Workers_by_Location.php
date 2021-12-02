<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( [ 'read_and_close' => true ] ); 
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
if(isset($_SESSION['User'],$_SESSION['Hash'])){
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
    if( isset($Privileges['Location']) 
        && $Privileges['Location']['Owner'] >= 4 
        && $Privileges['Location']['Group'] >= 4 
        && $Privileges['Location']['Other'] >= 4){$Privileged = TRUE;}
    elseif($Privileges['Location']['Owner'] >= 4 && is_numeric($_GET['ID'])){
        $r = $database->query(  
            null,
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
        ||  !is_numeric( $_GET[ 'ID' ] ) ){ json_encode( array( 'data' => array( ) ) ); }
    else {
        $data = array();
        $result = $database->query(null,
        	"	SELECT 		Emp.ID,
					   		Emp.fFirst AS First_Name,
					   		Emp.Last   AS Last_Name
				FROM  		(
								(
									SELECT   	TicketO.LID AS Location,
												TicketO.fWork
									FROM     	TicketO
									GROUP BY 	TicketO.LID,
											TicketO.fWork
									) UNION ALL (
									SELECT   	TicketD.Loc AS Location,
												TicketD.fWork
									FROM     	TicketD
									WHERE    	NOT ( TicketD.DescRes LIKE '%Voided%' )
								)
							) AS Workers
							LEFT JOIN Emp ON Workers.fWork = Emp.fWork 
				WHERE 		Workers.Location = ?
				GROUP BY 	Emp.ID,
							Emp.fFirst,
							Emp.Last;",
			array(
				$_GET['ID']
			)
		);
        if( $result ){ while( $row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC ) ){ $data[ ] = $row; } }
        print json_encode(
        	array(
        		'data' => $data
        	)
        );
	}
}?>