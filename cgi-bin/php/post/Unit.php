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
        if(isset($My_Privileges['Unit']) && $My_Privileges['Unit']['User_Privilege'] >= 6 && $My_Privileges['Unit']['Group_Privilege'] >= 6 && $My_Privileges['Unit']['Other_Privilege'] >= 6){$Privileged = TRUE;}
    }
    if(!$Privileged || count($_POST) == 0){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
		if(isset($_POST['action']) && $_POST['action'] == 'edit'){
			if(isset($_POST['data']) && count($_POST['data']) > 0){
				$data = array();
				foreach($_POST['data'] as $ID=>$Unit){
					$resource = sqlsrv_query($NEI,"
						SELECT Loc.Loc AS Location_ID
						FROM   nei.dbo.Loc
						WHERE  Loc.Tag = ?
					;",array($Unit['Location']));
					if($r){$Location_ID = sqlsrv_fetch_array($resource)['Location_ID'];}
					sqlsrv_query($NEI,"
						UPDATE nei.dbo.Elev
						SET    Elev.State   = ?,
						 	   Elev.Unit    = ?,
							   Elev.Loc     = ?,
							   Elev.Type    = ?,
							   Elev.fDesc   = ?,
							   Elev.Status  = ?
						WHERE  Elev.ID      = ?
					;", array($Unit['State'], $Unit['Unit'], $Location_ID, $Unit['Type'], $Unit['Description'], $Unit['Status'], $ID));
					$Unit['ID'] = intval($ID);
					$data[] = $Unit;
				}
				print json_encode(array("data"=>$data));
			}
		} elseif(isset($_POST['action']) && $_POST['action'] == 'create'){
			if(isset($_POST['data']) && count($_POST['data']) > 0){
				$data = array();
				foreach($_POST['data'] as $ID=>$Unit){
					$resource = sqlsrv_query($NEI,"
						SELECT Loc.Loc AS Location_ID
						FROM   nei.dbo.Loc
						WHERE  Loc.Tag = ?
					;",array($Unit['Location']));
					if($r){$Location_ID = sqlsrv_fetch_array($resource)['Location_ID'];}
					$resource = sqlsrv_query($NEI,"SELECT Max(Elev.ID) AS ID FROM nei.dbo.Elev;");
					$Unit_Primary_Key = sqlsrv_fetch_array($resource)['ID'];
					$Unit_Primary_Key++;
					$resource = sqlsrv_query($NEI,"
						INSERT INTO Elev(ID, Unit, State, Loc, Owner, Cat, Type, Status)
						VALUES(?,?,?,?,?,?,?,?)
					;",array($Unit_Primary_Key, $Unit['Unit'], $Unit['State'], $Location_ID, $_GET['ID'], 'Public', $Unit['Type'], $Unit['Status']));
					if( ($errors = sqlsrv_errors() ) != null) {
						foreach( $errors as $error ) {
							echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
							echo "code: ".$error[ 'code']."<br />";
							echo "message: ".$error[ 'message']."<br />";
						}
					}
					$Unit['ID'] = $Unit_Primary_Key;
					$data[] = $Unit;
				}
				print json_encode(array("data"=>$data));
			}
		}
    }
}?>