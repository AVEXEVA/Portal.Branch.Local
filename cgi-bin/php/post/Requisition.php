<?php 
session_start();
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
        if(isset($My_Privileges['Unit']) && $My_Privileges['Unit']['User_Privilege'] >= 6 && $My_Privileges['Unit']['Group_Privilege'] >= 6 && $My_Privileges['Unit']['Other_Privilege'] >= 6){$Privileged = TRUE;}
    }
    if(!$Privileged || count($_POST) == 0){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
		if(isset($_POST['action']) && $_POST['action'] == 'edit'){
			if(isset($_POST['data']) && count($_POST['data']) > 0){
				$data = array();
				foreach($_POST['data'] as $ID=>$Requisition){
					$Requisition['ID'] = intval($ID);
					$data[] = $Unit;
				}
				print json_encode(array("data"=>$data));
			}
		} elseif(isset($_POST['action']) && $_POST['action'] == 'create'){
			if(isset($_POST['data']) && count($_POST['data']) > 0){
				$data = array();
				foreach($_POST['data'] as $ID=>$Requisition){
					$resource = sqlsrv_query($NEI,"
						SELECT Loc.Loc AS Location_ID
						FROM   nei.dbo.Loc
						WHERE  Loc.Tag = ?
					;",array($Requisition['Location']));
					if($resource){$Location_ID = sqlsrv_fetch_array($resource)['Location_ID'];}
					$resource = sqlsrv_query($NEI,"
						SELECT Job.ID AS Job_ID, 
						       Job.Owner AS Customer_ID
						FROM   nei.dbo.Job
						WHERE  Job.fDesc = ?
					;",array($Requisition['Job']));
					if($resource){
						$array = sqlsrv_fetch_array($resource);
						$Customer_ID = $array['Customer_ID'];
						$Job_ID = $array['Job_ID'];
					}
					$resource = sqlsrv_query($NEI,"
						SELECT Elev.ID AS Unit_ID
						FROM   nei.dbo.Elev
						WHERE  Elev.State = ?
					;",array($Requisition['Unit']));
					if($resource){$Unit_ID = sqlsrv_fetch_array($resource)['Unit_ID'];}
					$resource = sqlsrv_query($NEI,"
						INSERT INTO Portal.dbo.Requisition([User], Customer, Location, Job, Unit, Notes)
						VALUES(?,?,?,?,?,?)
					;",array($_SESSION['User'], $Customer_ID, $Location_ID, $Job_ID, $Unit_ID, $Requisition['Notes']));
					$resource = sqlsrv_query($NEI,"SELECT Max(Requisition.ID) AS ID FROM Portal.dbo.Requisition;");
					$Requisition_Primary_Key = sqlsrv_fetch_array($resource)['ID'];
					$Requisition['ID'] = $Requisition_Primary_Key;
					$resource = sqlsrv_query($NEI,"
						SELECT OwnerWithRol.Name AS Customer
						FROM   nei.dbo.OwnerWithRol
						WHERE  OwnerWithRol.ID = ?
					",array($Customer_ID));
					if($resource){
						$Customer = sqlsrv_fetch_array($resource)['Customer'];
					}
					$Requisition['Customer'] = $Customer;
					$Requisition['Status'] = "Open";
					$Requisition['Status_Date'] = "";
					$data[] = $Requisition;
				}
				print json_encode(array("data"=>$data));
			}
		}
    }
}?>