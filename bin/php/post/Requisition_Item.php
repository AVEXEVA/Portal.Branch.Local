<?php 
session_start( [ 'read_and_close' => true ] );
require('../get/index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = $database->query(null,"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    $Privileged = FALSE;
    if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Elevator'){
        $r = $database->query(null,"SELECT * FROM Emp WHERE ID = ?",array($_GET['User']));
        $My_User = sqlsrv_fetch_array($r);
        $Field = ($User['Field'] == 1 && $User['Title'] != "OFFICE") ? True : False;
        $r = $database->query($Portal,"
            SELECT Access, Owner, Group, Other
            FROM   Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
        $My_Privileges = array();
        while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access']] = $array2;}
        $Privileged = FALSE;
        if(isset($My_Privileges['Unit']) && $My_Privileges['Unit']['Owner'] >= 6 && $My_Privileges['Unit']['Group'] >= 6 && $My_Privileges['Unit']['Other'] >= 6){$Privileged = TRUE;}
    }
    if(!$Privileged || count($_POST) == 0){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
		if(isset($_POST['action']) && $_POST['action'] == 'edit'){
			if(isset($_POST['data']) && count($_POST['data']) > 0){
				$data = array();
				foreach($_POST['data'] as $ID=>$Requisition_Item){
					$Requisition_Item['ID'] = intval($ID);
					$data[] = $Unit;
				}
				print json_encode(array("data"=>$data));
			}
		} elseif(isset($_POST['action']) && $_POST['action'] == 'create'){
			if(isset($_POST['data']) && count($_POST['data']) > 0){
				$data = array();
				foreach($_POST['data'] as $ID=>$Requisition_Item){
					$Requisition_Item['ID'] = intval($ID);
					$r = $database->query(null,"
						INSERT INTO Portal.dbo.Requisition_Item(Product, Quantity, Requisition)
						VALUES(?,?,?)
					;",array($Requisition_Item['Product'],$Requisition_Item['Quantity'],$_GET['ID']));
					$r = $database->query(null,"SELECT Max(ID) AS ID FROM Portal.dbo.Item");
					$Requisition_Item['ID'] = sqlsrv_fetch_array($r)['ID'];
					$data[] = $Requisition_Item;
				}
				print json_encode(array("data"=>$data));
			}
		}
    }
}?>