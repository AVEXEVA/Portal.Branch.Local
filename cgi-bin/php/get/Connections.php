<?php 
session_start( [ 'read_and_close' => true ] );
require('index.php');
setlocale(LC_MONETARY, 'en_US');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"
        SELECT * 
        FROM   Connection 
        WHERE  Connection.Connector = ? 
               AND Connection.Hash = ?
    ;", array($_SESSION['User'],$_SESSION['Hash']));
    $Connection = sqlsrv_fetch_array($r);
    $My_User    = sqlsrv_query($NEI,"
        SELECT Emp.*, 
               Emp.fFirst AS First_Name, 
               Emp.Last   AS Last_Name 
        FROM   Emp
        WHERE  Emp.ID = ?
    ;", array($_SESSION['User']));
    $My_User = sqlsrv_fetch_array($My_User); 
    $My_Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
    $r = sqlsrv_query($Portal,"
        SELECT Privilege.Access_Table, 
               Privilege.User_Privilege, 
               Privilege.Group_Privilege, 
               Privilege.Other_Privilege
        FROM   Privilege
        WHERE  Privilege.User_ID = ?
    ;",array($_SESSION['User']));
    $My_Privileges = array();
    while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
    $Privileged = False;
    if( isset($My_Privileges['Connection']) 
        && $My_Privileges['Connection']['Other_Privilege'] >= 4){
            $Privileged = True;}
    if(!isset($Connection['ID'])  || !$Privileged){print json_encode(array('data'=>array()));}
    else {
        $data = array();
        $r = sqlsrv_query($NEI,"
            SELECT   Connection.ID          AS ID,
                     Connection.Timestamped AS TimeStamped,
                     Emp.fFirst             AS First_Name,
                     Emp.Last               AS Last_Name,
                     Emp.ID                 AS User_ID
            FROM     Connection
                     LEFT JOIN Emp ON Connection.Connector = Emp.ID
            ORDER BY Connection.Timestamped
        ;");
        if($r){while($array = sqlsrv_fetch_array($r)){$data[] = $array;}}
        print json_encode(array('data'=>$data));    
    }
}?>