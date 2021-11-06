<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( [ 'read_and_close' => true ] ); 
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/bin/php/index.php' );
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
        $data = array();
        $r = $database->query(null,"
            SELECT Jobs.*
            FROM 
            ( 
                (
                    SELECT
                        Job.ID            AS  ID,
                        Job.fDesc         AS  Name,
                        JobType.Type      AS  Type,
                        Job.fDate         AS  Date,
                        CASE    WHEN Job.Status = 0 THEN 'Open'
                                WHEN Job.Status = 1 THEN 'Closed'
                                WHEN Job.Status = 2 THEN 'Hold'
                                WHEN Job.Status = 3 THEN 'Completed'
                        END AS Status,
                        Loc.Tag           AS  Location
                    FROM 
                        TicketO
                        LEFT JOIN Job        ON TicketO.Job    = Job.ID
                        LEFT JOIN Loc        ON TicketO.LID    = Loc.Loc
                        LEFT JOIN JobType    ON Job.Type      = JobType.ID
                    WHERE 
                        Loc.Owner   =  ?
                        AND Job.Type  <> 9 
                        AND Job.Type  <> 12
                )
                UNION ALL
                (
                    SELECT
                        Job.ID            AS  ID,
                        Job.fDesc         AS  Name,
                        JobType.Type      AS  Type,
                        Job.fDate         AS  Date,
                        CASE    WHEN Job.Status = 0 THEN 'Open'
                                WHEN Job.Status = 1 THEN 'Closed'
                                WHEN Job.Status = 2 THEN 'Hold'
                                WHEN Job.Status = 3 THEN 'Completed'
                        END AS Status,
                        Loc.Tag           AS  Location
                    FROM  
                        TicketD 
                        LEFT JOIN Job        ON TicketD.Job    = Job.ID
                        LEFT JOIN Loc        ON TicketD.Loc    = Loc.Loc
                        LEFT JOIN JobType    ON Job.Type       = JobType.ID
                    WHERE
                        Loc.Owner  = ?
                )   
            ) AS Jobs
            GROUP BY Jobs.ID, Jobs.Name, Jobs.Type, Jobs.Date, Jobs.Status, Jobs.Location
        ;",array($_GET['ID'],$_GET['ID']));
        if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}}
        print json_encode(array('data'=>$data));
    }
}?>