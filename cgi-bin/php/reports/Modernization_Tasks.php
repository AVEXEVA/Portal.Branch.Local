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
    if(!isset($array['ID'],$_GET['ID']) || !is_array($My_Privileges) || !is_numeric($_GET['ID'])){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
        $data = array();
		$r = sqlsrv_query($Portal,"
			SELECT *
			FROM   Portal.dbo.Mod_Tasks
				   LEFT JOIN Portal.dbo.Tasks ON Mod_Tasks.Task = Tasks.ID
			WHERE  Mod_Tasks.Modernization = ?
		;",array($_GET['ID']));
		if($r){while($array = sqlsrv_fetch_array($r)){
			$array['Buttons'] = "<button onClick='deleteModernizationTask(this);'>Delete</button>";
			$data[] = $array;
		}}
        print json_encode(array('data'=>$data));
    }
}?>