<?php 
session_start( [ 'read_and_close' => true ] );
require('index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = $database->query(null,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    $User = $database->query(null,"SELECT * FROM nei.dbo.Emp WHERE ID = ?",array($_GET['User']));
    $User = sqlsrv_fetch_array($User);
    $Field = ($User['Field'] == 1 && "OFFICE" != $User['Title']) ? True : False;
    $r = $database->query($Portal,"
            SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
            FROM   Portal.dbo.Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
    $My_Privileges = array();
    while($array2 = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$My_Privileges[$array2['Access_Table']] = $array2;}
    $Privileged = false;
    if(isset($My_Privileges['Unit']) && $My_Privileges['Unit']['User_Privilege'] >= 4){$Privileged = true;}
    else {$Privileged = true;}
    if(!isset($array['ID'])  || !$Privileged || !is_numeric($_GET['ID'])){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
		$data = array();
		$r = $database->query(null,"
			SELECT Job.fDesc AS Job
			FROM   nei.dbo.Job
			WHERE  Job.Loc = ?
		;",array($_GET['ID']));
		if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array['Job'];}}
		print json_encode($data);   
    }
}?>