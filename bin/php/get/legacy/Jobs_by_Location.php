<?php 
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( [ 'read_and_close' => true ] ); 
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
setlocale(LC_MONETARY, 'en_US');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = $database->query(null,"
        SELECT * 
        FROM   Connection 
        WHERE  Connection.Connector = ? 
               AND Connection.Hash = ?
    ;", array($_SESSION['User'],$_SESSION['Hash']));
    $Connection = sqlsrv_fetch_array($r);
    $My_User    = $database->query(null,"
        SELECT Emp.*, 
               Emp.fFirst AS First_Name, 
               Emp.Last   AS Last_Name 
        FROM   Emp
        WHERE  Emp.ID = ?
    ;", array($_SESSION['User']));
    $My_User = sqlsrv_fetch_array($My_User); 
    $My_Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
    $r = $database->query(null,"
        SELECT Privilege.Access, 
               Privilege.Owner, 
               Privilege.Group, 
               Privilege.Other
        FROM   Privilege
        WHERE  Privilege.User_ID = ?
    ;",array($_SESSION['User']));
    $My_Privileges = array();
    while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access']] = $array2;}
    $Privileged = False;
    if( isset($My_Privileges['Location'], $My_Privileges['Job']) 
        && $My_Privileges['Location']['Other'] >= 4
	  	&& $My_Privileges['Job']['Other'] >= 4){
            $Privileged = True;} 
    elseif(isset($My_Privileges['Location'], $My_Privileges['Job']) 
        && $My_Privileges['Location']['Group'] >= 4
		&& $My_Privileges['Job']['Group'] >= 4
        && is_numeric($_GET['ID'])){
            $Location_ID = $_GET['ID'];
            $r = $database->query(null,"
                SELECT Tickets.ID
                FROM 
                (
                    (
                        SELECT TicketO.ID
                        FROM   TicketO
                        WHERE  TicketO.LID       = ?
                               AND TicketO.fWork = ? 
                    ) 
                    UNION ALL
                    (
                        SELECT TicketD.ID
                        FROM   TicketD
                        WHERE  TicketD.Loc       = ?
                               AND TicketD.fWork = ? 
                    )
                ) AS Tickets
            ;", array($Location_ID, $My_User['fWork'],$Location_ID, $My_User['fWork']));
            $Privileged = is_array(sqlsrv_fetch_array($r)) ? True : False;}
    elseif(isset($My_Privileges['Location'], $My_Privileges['Location'])
        && $My_Privileges['Location']['Owner'] >= 4
		&& $My_Privileges['Job']['Owner'] >= 4
        && is_numeric($_GET['ID'])){
            $r = $database->query(null,"
                SELECT Tickets.ID
                FROM 
                (
                    (
                        SELECT TicketO.ID
                        FROM   TicketO
                        WHERE  TicketO.LID       = ?
                               AND TicketO.fWork = ? 
                    ) 
                    UNION ALL
                    (
                        SELECT TicketD.ID
                        FROM   TicketD
                        WHERE  TicketD.Loc       = ?
                               AND TicketD.fWork = ? 
                    )
                ) AS Tickets 
            ;", array($_GET['ID'], $My_User['fWork'],$_GET['ID'], $My_User['fWork']));
            $Privileged = is_array(sqlsrv_fetch_array($r)) ? True : False;}
    if(!isset($Connection['ID'])  || !is_numeric($_GET['ID']) || !$Privileged){print json_encode(array('data'=>array()));}
    else {
        $data = array();
        $r = $database->query(
            null,
            "   SELECT Jobs.*
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
                            TicketO.LID   =  ?
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
                            TicketD.Loc  = ?
                            AND Job.Type <> 9 
                            AND Job.Type <> 12
                    )   
                ) AS Jobs
                GROUP BY Jobs.ID, Jobs.Name, Jobs.Type, Jobs.Date, Jobs.Status, Jobs.Location;",
            array(
                $_GET['ID'],
                $_GET['ID'] 
            ) 
        );
        if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}}
        print json_encode(array('data'=>$data));
    }
}?>