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
        if(isset($My_Privileges['Admin']) && $My_Privileges['Admin']['User_Privilege'] >= 6 && $My_Privileges['Admin']['Group_Privilege'] >= 6 && $My_Privileges['Admin']['Other_Privilege'] >= 6){$Privileged = TRUE;}
    }
    if(!$Privileged || count($_POST) == 0){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
		if(isset($_POST['action']) && $_POST['action'] == 'edit'){
			if(isset($_POST['data']) && count($_POST['data']) > 0){
				$data = array();
				foreach($_POST['data'] as $ID=>$Product){
					$r = sqlsrv_query("
						UPDATE Portal.dbo.Product 
						SET    Product.Type = ?
						WHERE  Product.ID   = ? 
					;",array($Product['Type'], $Product['ID']));
					$Product['ID'] = intval($Product['ID']);
					$data[] = $Product;
				}
				print json_encode(array("data"=>$data));
			}
		} elseif(isset($_POST['action']) && $_POST['action'] == 'create'){
			if(isset($_POST['data']) && count($_POST['data']) > 0){
				$data = array();
				foreach($_POST['data'] as $ID=>$Product){
					$data[] = $Product;
				}
				print json_encode(array("data"=>$data));
			}
		}
    }
}?>