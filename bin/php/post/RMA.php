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
		if(isset($_POST['action']) && $_POST['action'] == 'edit'){
			if(isset($_POST['data']) && count($_POST['data']) > 0){
				$data = array();
				foreach($_POST['data'] as $ID=>$RMA){
					$r = $database->query(null,"SELECT Loc.Loc AS ID FROM nei.dbo.Loc WHERE Loc.Tag = ?;",array($RMA['Location']));
					if($r){$Location_ID = sqlsrv_fetch_Array($r)['ID'];}
					$database->query(null,"
						UPDATE Portal.dbo.RMA
						SET    RMA.Name        = ?,
							   RMA.Date        = ?,
							   RMA.RMA         = ?,
							   RMA.Received    = ?,
							   RMA.Returned    = ?,
							   RMA.Tracking    = ?,
							   RMA.PO          = ?,
							   RMA.Link        = ?,
							   RMA.Status      = ?,
							   RMA.Description = ?,
							   RMA.Location    = ?
						WHERE  RMA.ID          = ?
					;", array($RMA['Name'], $RMA['Date'], $RMA['RMA'], $RMA['Received'], $RMA['Returned'], $RMA['Tracking'], $RMA['PO'], $RMA['Link'],$RMA['Status'],$RMA['Description'],$Location_ID, $ID));
					$data[] = $RMA;
				}
				print json_encode(array('data'=>$data));
			}
		} elseif(isset($_POST['action']) && $_POST['action'] == 'create'){
			if(isset($_POST['data']) && count($_POST['data']) > 0){
				foreach($_POST['data'] as $ID=>$RMA){
					$r = $database->query(null,"SELECT Loc.Loc AS ID FROM nei.dbo.Loc WHERE Loc.Tag = ?;",array($RMA['Location']));
					if($r){$Location_ID = sqlsrv_fetch_Array($r)['ID'];}
					$database->query(null,"
						INSERT INTO Portal.dbo.RMA(Name, Date, RMA, Received, Returned, Tracking, PO, Link, Status, Description, Location,Address)
						VALUES(?,?,?,?,?,?,?,?,?,?,?,?)
					;",array($RMA['Name'], $RMA['Date'], $RMA['RMA'], $RMA['Received'], $RMA['Returned'], $RMA['Tracking'], $RMA['PO'], $RMA['Link'], $RMA['Status'], $RMA['Description'], $Location_ID,' '));
					$data[] = $RMA;
				}
				print json_encode(array('data'=>$data));
			}
		} elseif(isset($_POST['action']) && $_POST['action'] == 'remove'){
			if(isset($_POST['data']) && count($_POST['data']) > 0){
				foreach($_POST['data'] as $ID=>$RMA){
					$database->query(null,"DELETE FROM Portal.dbo.RMA WHERE RMA.ID = ?;",array($ID));
				}
				print json_encode(array('data'=>array()));
			}
		}
    }
}?>