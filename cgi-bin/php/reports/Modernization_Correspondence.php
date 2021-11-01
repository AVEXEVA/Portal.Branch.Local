<?php 
session_start();
require('index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    $r = sqlsrv_query($Portal,"
        SELECT User_Privilege, Group_Privilege, Other_Privilege
        FROM   Portal.dbo.Privilege
        WHERE  User_ID = ? AND Access_Table='Job'
    ;",array($_SESSION['User']));
    $My_Privileges = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    if(!isset($array['ID']) || !is_array($My_Privileges) || !is_numeric($_GET['ID'])){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
        $data = array();
        if($My_Privileges['User_Privilege'] >= 4 && $My_Privileges['Group_Privilege'] >= 4 && $My_Privileges['Other_Privilege'] >= 4){
            $r = sqlsrv_query($Portal,"
                SELECT * 
                FROM   Portal.dbo.Mod_Correspondence
                WHERE  Mod_Correspondence.Modernization = ?
            ;",array($_GET['ID']));
            if($r){while($array = sqlsrv_fetch_array($r)){$data[] = $array;}}
        }
        print json_encode(array('data'=>utf8ize($data)));	
    }
}