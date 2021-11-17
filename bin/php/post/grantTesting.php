<?php 
session_start( [ 'read_and_close' => true ] );
require('../get/index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = $database->query(null,"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    $User = $database->query(null,"SELECT * FROM Emp WHERE ID = ?",array($_GET['User']));
    $User = sqlsrv_fetch_array($User);
    $Field = ($User['Field'] == 1 && $User['Title'] != "OFFICE") ? True : False;
    $r = $database->query(null,"
        SELECT Privilege.*
        FROM   Privilege
        WHERE 
            User_ID='{$_SESSION['User']}'
            AND Access='Admin'
            AND Owner='7'
            AND Group='7'
            AND Other='7'
    ;");
    $Admin = sqlsrv_fetch_array($r);
    if(!isset($array['ID'])  || !is_array($Admin)){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
        $r1 = $database->query(null,"
			SELECT Privilege.* 
			FROM   Privilege
			WHERE  User_ID='{$_POST['User_ID']}'
		;");
		$My_Privileges = array('Testing');
		$Update_Privileges = array();
		while($array = sqlsrv_fetch_array($r1)){
			if(in_array($array['Access'],$My_Privileges)){
				if(($key = array_search($array['Access'], $My_Privileges)) !== false) {
					unset($My_Privileges[$key]);
					$Update_Privileges[] = $array['Access'];
				}
			}
		}
		foreach($My_Privileges as $Privilege){
			$database->query(null,"
				INSERT INTO Privilege(User_ID,Access,Owner,Group,Other)
				VALUES({$_POST['User_ID']},'{$Privilege}',6,4,4)
			;");
		}
		foreach($Update_Privileges as $Privilege){
			$database->query(null,"
				UPDATE Privilege
				SET 
					Owner='6',
					Group='4',
					Other='4'
				WHERE 
					User_ID='{$_POST['User_ID']}'
					AND Access='{$Privilege}'
			;");
		}
    }
}?>