<?php 
session_start( [ 'read_and_close' => true ] );
require('index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = $database->query(null,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    $r = $database->query($Portal,"
        SELECT Owner, Group, Other
        FROM   Portal.dbo.Privilege
        WHERE  User_ID = ? AND Access='Job'
    ;",array($_SESSION['User']));
    $My_Privileges = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    if(!isset($array['ID']) || !is_array($My_Privileges) || !is_numeric($_GET['ID'])){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
        $data = array();
        if($My_Privileges['Owner'] >= 4 && $My_Privileges['Group'] >= 4 && $My_Privileges['Other'] >= 4){
            $r = $database->query($Portal,"
                SELECT * 
                FROM   Portal.dbo.Mod_Correspondence
                WHERE  Mod_Correspondence.Modernization = ?
            ;",array($_GET['ID']));
            if($r){while($array = sqlsrv_fetch_array($r)){$data[] = $array;}}
        }
        print json_encode(array('data'=>utf8ize($data)));	
    }
}