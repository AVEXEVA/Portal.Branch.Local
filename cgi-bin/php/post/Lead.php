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
        if(isset($My_Privileges['Location']) && $My_Privileges['Location']['User_Privilege'] >= 6 && $My_Privileges['Location']['Group_Privilege'] >= 6 && $My_Privileges['Location']['Other_Privilege'] >= 6){$Privileged = TRUE;}
    }
    if(!$Privileged || count($_POST) == 0){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
		if(isset($_POST['action']) && $_POST['action'] == 'edit'){
			if(isset($_POST['data']) && count($_POST['data']) > 0){
				$data = array();
				foreach($_POST['data'] as $ID=>$Lead){
					$r = $database->query(null,"SELECT OwnerWithRol.ID AS ID FROM nei.dbo.OwnerWithRol WHERE OwnerWithRol.Name = ?;",array($Lead['Customer']));
					if($r){$Customer_ID = sqlsrv_fetch_Array($r)['ID'];}
					$database->query(null,"
						UPDATE nei.dbo.Lead
						SET    Lead.fDesc   = ?,
							   Lead.Address = ?,
							   Lead.City    = ?,
							   Lead.State   = ?,
							   Lead.Zip     = ?,
							   Lead.Owner   = ?
						WHERE  Lead.ID = ?
					;", array($Lead['Name'], $Lead['Street'], $Lead['City'], $Lead['State'], $Lead['Zip'], $Customer_ID, $ID));
					$Lead['ID'] = intval($Lead['ID']);
					$data[] = $Lead;
				}
				print json_encode(array('data'=>$data));
			}
		} elseif(isset($_POST['action']) && $_POST['action'] == 'create'){
			if(isset($_POST['data']) && count($_POST['data']) > 0){
				$data = array();
				foreach($_POST['data'] as $ID=>$Lead){
					$data[] = $Lead;
					if(TRUE){continue;}
					$r = $database->query(null,"SELECT OwnerWithRol.ID AS ID FROM nei.dbo.OwnerWithRol WHERE OwnerWithRol.Name = ?;",array($Lead['Customer']));
					if($r){$Route_ID = sqlsrv_fetch_Array($r)['ID'];}
					$resource = $database->query(null,"SELECT Max(Lead.ID) AS ID FROM nei.dbo.Lead;");
					$Lead_Primary_Key = sqlsrv_fetch_array($resource)['ID'];
					$Lead_Primary_Key++;
					$database->query(null,"INSERT INTO nei.dbo.Rol(ID, fDesc, RolType, Rol, Fax, Remarks, Type, fLong, Latt, GeoLock, Since, Last, EN, Cellular, Country, Contact, EMail,Website) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",array($Lead_Primary_Key, $Lead['Tag'], "NY", $Lead['Contact_Phone'], $Lead['Contact_Fax'],"",4,0,0,0,'2018-01-22 00:00:00.000','2018-01-22 00:00:00.000',1,"(","United States",$LocLeadation['Contact_Name'],$Lead['Contact_Email'],$Lead['Contact_Website']));
					if( ($errors = sqlsrv_errors() ) != null) {
						foreach( $errors as $error ) {
							echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
							echo "code: ".$error[ 'code']."<br />";
							echo "message: ".$error[ 'message']."<br />";
						}
					}
					$resource = $database->query(null,"SELECT Max(Loc.Loc) AS Loc FROM nei.dbo.Loc;");
					$Location_Primary_Key = sqlsrv_fetch_array($resource)['Loc'];
					$Location_Primary_Key++;
					$resource = $database->query(null,"
						INSERT INTO nei.dbo.Loc(Loc, Owner, Terr, ID, Tag, Address, City, State, Zip, Route, Zone, Maint, GeoLock, sTax, InUse, Elevs, Status, Balance, Rol, fLong, Latt, Job, Type, Billing, Markup1, Markup2, Markup3, Markup4, Markup5, Terms, Country, idRolCustomContact, DispAlertType, Email, PrintInvoice,PriceL, PaidNumb, PaidDays, WriteOff, Credit,DispAlert)
						VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
					;SELECT SCOPE_IDENTITY();", array($Location_Primary_Key, $_GET['ID'], $Territory_ID, $Location['Name'], $Location['Tag'], $Location['Street'], $Location['City'], $Location['State'], $Location['Zip'], $Route_ID, $Division_ID, $Location['Maintenance'],0,8009,0,0,0,'.00',$Rolodex_Primary_Key,$Location['Latitude'],$Location['Longitude'],0,'Non-Contract',0,0,0,0,0,0,3,'United States',0,0,0,0,1,0,0,'.00',0,0));
					if( ($errors = sqlsrv_errors() ) != null) {
						foreach( $errors as $error ) {
							echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
							echo "code: ".$error[ 'code']."<br />";
							echo "message: ".$error[ 'message']."<br />";
						}
					}
					$Location['ID'] = $Location_Primary_Key;
					
				}
				print json_encode(array('data'=>$data));
			}
		} elseif(isset($_POST['action']) && $_POST['action'] == 'removeX'){
			if(isset($_POST['data']) && count($_POST['data']) > 0){
				foreach($_POST['data'] as $ID=>$Location){
					$resource = $database->query(null,"SELECT Loc.Rol as Rolodex_ID FROM nei.dbo.Loc WHERE Loc = ?;",array($ID));
					if($resource){
						$Rolodex_ID = sqlsrv_fetch_array($resource)['Rolodex_ID'];
						if(is_numeric($Rolodex_ID) && $Rolodex_ID > 0){
							$database->query(null,"DELETE FROM nei.dbo.Rol WHERE Rol.ID = ?",array($Rolodex_ID));
						}
					}
					$database->query(null,"DELETE FROM nei.dbo.Loc WHERE Loc.Loc = ?",array($ID));
				}
				print json_encode(array('data'=>array()));
			}
		}
    }
}?>