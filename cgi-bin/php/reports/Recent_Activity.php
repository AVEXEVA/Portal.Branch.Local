<?php
session_start();
require('index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    $User = sqlsrv_query($NEI,"SELECT * FROM nei.dbo.Emp WHERE ID = ?",array($_GET['User']));
    $User = sqlsrv_fetch_array($User);
    $Field = ($User['Field'] == 1 && "OFFICE" != $User['Title']) ? True : False;
    if(!isset($array['ID'])  || !in_array($_SESSION['User'], array(895,250))){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
        $data = array();
        $r = sqlsrv_query($NEI,"
            SELECT   Top 5000
				     Emp.fFirst    AS First_Name,
				     Emp.Last      AS Last_Name,
				     Activity.Date AS Date,
				     Activity.Page AS Page
			FROM     Portal.dbo.Activity
				     LEFT JOIN nei.dbo.Emp ON Activity.[User] = Emp.ID
			ORDER BY Activity.Date DESC
        ;");
        if($r){while($array = sqlsrv_fetch_array($r)){$data[] = $array;}}
        print json_encode(array('data'=>$data));
    }
}?>
