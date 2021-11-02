<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( ); 
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/cgi-bin/php/index.php' );
}
if(isset($_SESSION['User'],$_SESSION['Hash'])){
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
            "   SELECT  Sum( Ticket.Count ) AS Count 
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
            print json_encode( array( 'data' => array( ) ) ); 
    } else {
        $data = array();
        $r = sqlsrv_query(
            $NEI,
            "   SELECT      Job.ID,
                            Job.Name,
                            Job.Type,
                            Job.Date,
                            Job.Status
                FROM        (
                                (
                                    SELECT  Job.ID            AS  ID,
                                            Job.fDesc         AS  Name,
                                            JobType.Type      AS  Type,
                                            Job.fDate         AS  Date,
                                            CASE    WHEN Job.Status = 0 THEN 'Open'
                                                    WHEN Job.Status = 1 THEN 'Closed'
                                                    WHEN Job.Status = 2 THEN 'Hold'
                                                    WHEN Job.Status = 3 THEN 'Completed'
                                            END AS Status,
                                            TicketO.LElev     AS  Unit
                                    FROM    TicketO
                                            LEFT JOIN Job        ON TicketO.Job    = Job.ID
                                            LEFT JOIN Loc        ON TicketO.LID    = Loc.Loc
                                            LEFT JOIN JobType    ON Job.Type      = JobType.ID
                                ) UNION ALL (
                                    SELECT  Job.ID            AS  ID,
                                            Job.fDesc         AS  Name,
                                            JobType.Type      AS  Type,
                                            Job.fDate         AS  Date,
                                            CASE    WHEN Job.Status = 0 THEN 'Open'
                                                    WHEN Job.Status = 1 THEN 'Closed'
                                                    WHEN Job.Status = 2 THEN 'Hold'
                                                    WHEN Job.Status = 3 THEN 'Completed'
                                            END AS Status,
                                            TicketD.Elev      AS  Unit
                                    FROM    TicketD 
                                            LEFT JOIN Job        ON TicketD.Job    = Job.ID
                                            LEFT JOIN Loc        ON TicketD.Loc    = Loc.Loc
                                            LEFT JOIN JobType    ON Job.Type       = JobType.ID
                                )   
                            ) AS Job
                WHERE       Job.Unit = ?
                GROUP BY    Job.ID, 
                            Job.Name, 
                            Job.Type, 
                            Job.Date, 
                            Job.Status;",
            array( 
                $_GET[ 'ID' ]
            ) 
        );
        if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}}
        print json_encode(array('data'=>$data));
    }
}?>

