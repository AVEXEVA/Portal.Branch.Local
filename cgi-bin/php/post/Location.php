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
        if(isset($My_Privileges['Location']) && $My_Privileges['Location']['User_Privilege'] >= 6 && $My_Privileges['Location']['Group_Privilege'] >= 6 && $My_Privileges['Location']['Other_Privilege'] >= 6){$Privileged = TRUE;}
    }
    if(!$Privileged || count($_POST) == 0){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
		if(isset($_POST['action']) && $_POST['action'] == 'edit'){
			if(isset($_POST['data']) && count($_POST['data']) > 0){
				$data = array();
				foreach($_POST['data'] as $ID=>$Location){
					$r = sqlsrv_query($NEI,"SELECT Route.ID AS ID FROM nei.dbo.Route WHERE Route.Name = ?;",array($Location['Route']));
					if($r){$Route_ID = sqlsrv_fetch_Array($r)['ID'];}
					$r = sqlsrv_query($NEI,"SELECT Zone.ID  AS ID FROM nei.dbo.Zone WHERE Zone.Name = ?;",array($Location['Division']));
					if($r){$Division_ID = sqlsrv_fetch_Array($r)['ID'];}
					sqlsrv_query($NEI,"
						UPDATE nei.dbo.Loc
						SET    Loc.ID       = ?,
							   Loc.Tag      = ?,
							   Loc.Address  = ?,
							   Loc.City     = ?,
							   Loc.State    = ?,
							   Loc.Zip      = ?,
							   Loc.Route    = ?,
							   Loc.Zone     = ?,
							   Loc.Maint    = ?
						WHERE  Loc.Loc      = ?
					;", array($Location['Name'], $Location['Tag'], $Location['Street'], $Location['City'], $Location['State'], $Location['Zip'], $Route_ID, $Division_ID, $Location['Maintenance'], $ID));
					$Location['ID'] = intval($Location['ID']);
					sqlsrv_query($NEI,"
						UPDATE nei.dbo.Rol
						SET    Rol.Contact  = ?,
							   Rol.Phone    = ?,
							   Rol.Fax      = ?,
							   Rol.EMail    = ?,
							   Rol.Website  = ?
						WHERE  Rol.Name     = ?
						       AND Rol.Type = 4
					;", array($Location['Contact_Name'], $Location['Contact_Phone'], $Location['Contact_Fax'], $Location['Contact_Email'], $Location['Contact_Website'], $Location['Tag']));
					if( ($errors = sqlsrv_errors() ) != null) {
						foreach( $errors as $error ) {
							echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
							echo "code: ".$error[ 'code']."<br />";
							echo "message: ".$error[ 'message']."<br />";
						}
					}
					$data[] = $Location;
				}
				print json_encode(array('data'=>$data));
			}
		} elseif(isset($_POST['action']) && $_POST['action'] == 'create'){
			if(isset($_POST['data']) && count($_POST['data']) > 0){
				foreach($_POST['data'] as $ID=>$Location){
					$r = sqlsrv_query($NEI,"SELECT Route.ID AS ID FROM nei.dbo.Route WHERE Route.Name = ?;",array($Location['Route']));
					if($r){$Route_ID = sqlsrv_fetch_Array($r)['ID'];}
					$r = sqlsrv_query($NEI,"SELECT Zone.ID  AS ID FROM nei.dbo.Zone WHERE Zone.Name = ?;",array($Location['Division']));
					if($r){$Division_ID = sqlsrv_fetch_Array($r)['ID'];}
					$r = sqlsrv_query($NEI,"SELECT Terr.ID  AS ID FROM nei.dbo.Terr WHERE Terr.Name = ?;",array($Location['Territory']));
					if($r){$Territory_ID = sqlsrv_fetch_Array($r)['ID'];}
					$resource = sqlsrv_query($NEI,"SELECT Max(Rol.ID) AS ID FROM nei.dbo.Rol;");
					$Rolodex_Primary_Key = sqlsrv_fetch_array($resource)['ID'];
					$Rolodex_Primary_Key++;
					sqlsrv_query($NEI,"INSERT INTO nei.dbo.Rol(ID, Name, State, Phone, Fax, Remarks, Type, fLong, Latt, GeoLock, Since, Last, EN, Cellular, Country, Contact, EMail,Website) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",array($Rolodex_Primary_Key, $Location['Tag'], "NY", $Location['Contact_Phone'], $Location['Contact_Fax'],"",4,0,0,0,'2018-01-22 00:00:00.000','2018-01-22 00:00:00.000',1,"(","United States",$Location['Contact_Name'],$Location['Contact_Email'],$Location['Contact_Website']));
					if( ($errors = sqlsrv_errors() ) != null) {
						foreach( $errors as $error ) {
							echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
							echo "code: ".$error[ 'code']."<br />";
							echo "message: ".$error[ 'message']."<br />";
						}
					}
					$resource = sqlsrv_query($NEI,"SELECT Max(Loc.Loc) AS Loc FROM nei.dbo.Loc;");
					$Location_Primary_Key = sqlsrv_fetch_array($resource)['Loc'];
					$Location_Primary_Key++;
					$resource = sqlsrv_query($NEI,"
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
					print json_encode(array('data'=>$Location));
				}
			}
		} elseif(isset($_POST['action']) && $_POST['action'] == 'removeX'){
			if(isset($_POST['data']) && count($_POST['data']) > 0){
				foreach($_POST['data'] as $ID=>$Location){
					$resource = sqlsrv_query($NEI,"SELECT Loc.Rol as Rolodex_ID FROM nei.dbo.Loc WHERE Loc = ?;",array($ID));
					if($resource){
						$Rolodex_ID = sqlsrv_fetch_array($resource)['Rolodex_ID'];
						if(is_numeric($Rolodex_ID) && $Rolodex_ID > 0){
							sqlsrv_query($NEI,"DELETE FROM nei.dbo.Rol WHERE Rol.ID = ?",array($Rolodex_ID));
						}
					}
					sqlsrv_query($NEI,"DELETE FROM nei.dbo.Loc WHERE Loc.Loc = ?",array($ID));
				}
				print json_encode(array('data'=>array()));
			}
		}
    }
}?>