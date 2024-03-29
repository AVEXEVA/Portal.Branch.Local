<?php 
session_start( [ 'read_and_close' => true ] );
require('index.php');
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
    if( isset($My_Privileges['Route'], $My_Privileges['Location']) 
        && $My_Privileges['Route']['Other'] >= 4
        && $My_Privileges['Location']['Other'] >= 4){
            $Privileged = True;} 
    elseif(isset($My_Privileges['Route'], $My_Privileges['Location']) 
        && $My_Privileges['Route']['Group'] >= 4
        && $My_Privileges['Location']['Group'] >= 4
        && is_numeric($_GET['ID'])){
            $r = $database->query(null,"
                SELECT Locations.*
                FROM 
                (
                    (
                        SELECT Loc.Loc AS Location_ID
                        FROM   TicketO
                               LEFT JOIN Loc ON TicketO.LID = Loc.Loc
                               LEFT JOIN Emp ON TicketO.fWork = Emp.fWork
                        WHERE  Emp.ID = ?
                               AND Loc.Route = ?
                    )
                    UNION ALL
                    (
                        SELECT Loc.Loc AS Location_ID
                        FROM   TicketD
                               LEFT JOIN Loc ON TicketD.Loc = Loc.Loc
                               LEFT JOIN Emp ON TicketD.fWork = Emp.fWork
                        WHERE  Emp.ID = ?
                               AND Loc.Route = ?
                    )
                ) AS Locations
                GROUP BY Locations.Location_ID
            ;",array($_SESSION['User'], $_GET['ID'], $_SESSION['User'], $_GET['ID'], $_SESSION['User'], $_GET['ID']));
            if($r){if(is_array(sqlsrv_fetch_array($r))){$Privileged = True;}}}
    elseif(isset($My_Privileges['Route'], $My_Privileges['Location']) 
        && $My_Privileges['Route']['Owner'] >= 4
        && $My_Privileges['Location']['Owner'] >= 4
        && is_numeric($_GET['ID'])){
            $r = $database->query(null,"
                SELECT Route.ID AS Route_ID
                FROM   Route
                       LEFT JOIN Emp ON Route.Mech = Emp.fWork
                WHERE  Emp.ID = ?
            ;",array($_GET['ID']));
            if($r){
                $Route_ID = sqlsrv_fetch_array($r)['Route_ID'];
                if($Route_ID == $_GET['ID']){
                    $Privileged = True;}}}
    if(!isset($Connection['ID'])  || !is_numeric($_GET['ID']) || !$Privileged){print json_encode(array('data'=>array()));}
    else {                
        $r = $database->query(null,"
            SELECT Elev.ID    AS ID,
                   Elev.State AS State, 
                   Elev.Unit  AS Unit,
                   Elev.Type  AS Type, 
                   Loc.Tag    AS Location
            FROM Loc
                 LEFT JOIN Route ON Loc.Route = Route.ID
                 LEFT JOIN Elev  ON Elev.Loc  = Loc.Loc
            WHERE Loc.Route = ?
        ;",array($_GET['ID']));
        $data = array();
        if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}}
        print json_encode(array('data'=>$data));
    }
}?>