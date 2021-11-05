<?php 
session_start( [ 'read_and_close' => true ] );
require('../get/index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    $Privileged = FALSE;
    if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Elevator'){
        $r = sqlsrv_query($NEI,"SELECT * FROM Emp WHERE ID = ?",array($_GET['User']));
        $My_User = sqlsrv_fetch_array($r);
        $Field = ($User['Field'] == 1 && $User['Title'] != "OFFICE") ? True : False;
        $r = sqlsrv_query($Portal,"
            SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
            FROM   Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
        $My_Privileges = array();
        while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
        $Privileged = FALSE;
        if(isset($My_Privileges['Job']) && $My_Privileges['Job']['User_Privilege'] >= 4 && $My_Privileges['Job']['Group_Privilege'] >= 4 && $My_Privileges['Job']['Other_Privilege'] >= 4){$Privileged = TRUE;}
    }
    if(!$Privileged || count($_POST) == 0){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
		var_dump($_POST['Task_Name']);
		$Task_Name = urldecode($_POST['Task_Name']);
		$Status = urldecode($_POST['Status']);
		var_dump($Task_Name);
		$r = sqlsrv_query($Portal,"
			SELECT *
			FROM   Portal.dbo.Tasks 
			WHERE  Tasks.Name = ?
		;",array($Task_Name));
		if($r){
			var_dump($r);
			$Task = sqlsrv_fetch_array($r);
			var_dump($Task['ID']);
			sqlsrv_query($Portal,"
				UPDATE Portal.dbo.Mod_Tasks 
				SET    Status = ? 
				WHERE  Modernization = ? 
					   AND Task      = ?
			;",array($Status,$_POST['Modernization'],$Task['ID']));
		}
        
    }
}?>