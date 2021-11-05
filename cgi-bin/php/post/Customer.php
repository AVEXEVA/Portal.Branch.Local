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
        if(isset($My_Privileges['Location']) && $My_Privileges['Location']['User_Privilege'] >= 6 && $My_Privileges['Location']['Group_Privilege'] >= 6 && $My_Privileges['Location']['Other_Privilege'] >= 6){$Privileged = TRUE;}
    }
    if(!$Privileged || count($_POST) == 0){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
		if(isset($_POST['action']) && $_POST['action'] == 'edit'){
			if(isset($_POST['data']) && count($_POST['data']) > 0){
				$data = array();
				foreach($_POST['data'] as $ID=>$Customer){
					sqlsrv_query($NEI,"
						UPDATE nei.dbo.OwnerWithRol
						SET    OwnerWithRol.Name    = ?,
							   OwnerWithRol.Address = ?,
							   OwnerWithRol.City    = ?,
							   OwnerWithRol.State   = ?,
							   OwnerWithRol.Zip     = ?,
							   OwnerWithRol.Contact = ?,
							   OwnerWithRol.Phone   = ?,
							   OwnerWithRol.Fax     = ?,
							   OwnerWithRol.Website = ?
						WHERE  OwnerWithRol.ID      = ?
					;", array());
					$resource = sqlsrv_query($NEI,"
						SELECT OwnerWithRol.Rol AS Rolodex
						FROM   OwnerWithRol
						WHERE  OwnerWithRol.ID = ?
					;",array($ID));
					$Rolodex_ID = sqlsrv_fetch_array($resource)['Rolodex'];
					sqlsrv_query($NEI,"
						UPDATE nei.dbo.Rol
						SET    Rol.Name    = ?,
							   Rol.Address = ?,
							   Rol.City    = ?,
							   Rol.State   = ?,
							   Rol.Zip     = ?,
							   Rol.Contact = ?,
							   Rol.Phone   = ?,
							   Rol.Fax     = ?,
							   Rol.Website = ?
						WHERE  Rol.ID      = ?
					;",array($Rolodex_ID));
					sqlsrv_query($NEI,";", array());
					$Location['ID'] = intval($Location['ID']);
					$data[] = $Location;
				}
				print json_encode(array('data'=>$data));
			}
		} elseif(isset($_POST['action']) && $_POST['action'] == 'create'){
			if(isset($_POST['data']) && count($_POST['data']) > 0){
				foreach($_POST['data'] as $ID=>$Customer){
					$resource = sqlsrv_query($NEI,"SELECT Max(Rol.ID) AS ID FROM nei.dbo.Rol;");
					$Rolodex_Primary_Key = sqlsrv_fetch_array($resource)['ID'];
					$Rolodex_Primary_Key++;
					sqlsrv_query($NEI,"INSERT INTO nei.dbo.Rol(ID, Name, State, Phone, Fax, Remarks, Type, fLong, Latt, GeoLock, Since, Last, EN, Cellular, Country, Contact, EMail,Website) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",array($Rolodex_Primary_Key, $Customer['Customer_Name'], "NY", $Customer['Contact_Phone'], $Customer['Contact_Fax'],"",4,0,0,0,'2018-01-22 00:00:00.000','2018-01-22 00:00:00.000',1,"(","United States",$Customer['Contact_Name'],$Customer['Contact_Email'],$Customer['Contact_Website']));
					$resource = sqlsrv_query($NEI,"SELECT Max(Owner.ID) AS ID FROM nei.dbo.Owner;");
					$Owner_Primary_Key = sqlsrv_fetch_array($resource)['ID'];
					$Owner_Primary_Key++;
					$resource = sqlsrv_query($NEI,"
						INSERT INTO nei.dbo.Owner()
						VALUES()
					;SELECT SCOPE_IDENTITY();", array($Owner_Primary_Key, $_GET['ID'], $Territory_ID, $Location['Name'], $Location['Tag'], $Location['Street'], $Location['City'], $Location['State'], $Location['Zip'], $Route_ID, $Division_ID, $Location['Maintenance'],0,8009,0,0,0,'.00',$Rolodex_Primary_Key,$Location['Latitude'],$Location['Longitude'],0,'Non-Contract',0,0,0,0,0,0,3,'United States',0,0,0,0,1,0,0,'.00',0,0));
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