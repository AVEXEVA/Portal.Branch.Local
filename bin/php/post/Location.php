<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
if( isset( $_SESSION[ 'Connection' ][ 'User' ], $_SESSION[ 'Connection' ][ 'Hash' ] ) ){
  //Connection
    $result = \singleton\database::getInstance( )->query(
      'Portal',
      " SELECT  [Connection].[ID]
        FROM    dbo.[Connection]
        WHERE       [Connection].[User] = ?
                AND [Connection].[Hash] = ?;",
      array(
        $_SESSION[ 'Connection' ][ 'User' ],
        $_SESSION[ 'Connection' ][ 'Hash' ]
      )
    );
    $Connection = sqlsrv_fetch_array($result);
    //User
  $result = \singleton\database::getInstance( )->query(
    null,
    " SELECT  Emp.fFirst  AS First_Name,
              Emp.Last    AS Last_Name,
              Emp.fFirst + ' ' + Emp.Last AS Name,
              Emp.Title AS Title,
              Emp.Field   AS Field
      FROM  Emp
      WHERE   Emp.ID = ?;",
    array(
        $_SESSION[ 'Connection' ][ 'User' ]
    )
  );
  $User   = sqlsrv_fetch_array( $result );
  //Privileges
  $Access = 0;
  $Hex = 0;
  $result = \singleton\database::getInstance( )->query(
    'Portal',
    "   SELECT  [Privilege].[Access],
                    [Privilege].[Owner],
                    [Privilege].[Group],
                    [Privilege].[Department],
                    [Privilege].[Database],
                    [Privilege].[Server],
                    [Privilege].[Other],
                    [Privilege].[Token],
                    [Privilege].[Internet]
      FROM      dbo.[Privilege]
      WHERE     Privilege.[User] = ?;",
    array(
        $_SESSION[ 'Connection' ][ 'User' ],
    )
  );
    $Privileges = array();
    if( $result ){while( $Privilege = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC ) ){

        $key = $Privilege['Access'];
        unset( $Privilege[ 'Access' ] );
        $Privileges[ $key ] = implode( '', array(
          dechex( $Privilege[ 'Owner' ] ),
          dechex( $Privilege[ 'Group' ] ),
          dechex( $Privilege[ 'Department' ] ),
          dechex( $Privilege[ 'Database' ] ),
          dechex( $Privilege[ 'Server' ] ),
          dechex( $Privilege[ 'Other' ] ),
          dechex( $Privilege[ 'Token' ] ),
          dechex( $Privilege[ 'Internet' ] )
        ) );
    }}
    if(   !isset( $Connection[ 'ID' ] )
        ||  !isset( $Privileges[ 'Location' ] )
        ||  !check( privilege_read, level_group, $Privileges[ 'Location' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
    		if(isset($_POST['action']) && $_POST['action'] == 'edit'){
    			if(isset($_POST['data']) && count($_POST['data']) > 0){
    				$data = array();
    				foreach($_POST['data'] as $ID=>$Location){
    					$result = $database->query(null,"SELECT Route.ID AS ID FROM nei.dbo.Route WHERE Route.Name = ?;",array($Location['Route']));
    					if($result){$Route_ID = sqlsrv_fetch_Array($result)['ID'];}
    					$result = $database->query(null,"SELECT Zone.ID  AS ID FROM nei.dbo.Zone WHERE Zone.Name = ?;",array($Location['Division']));
    					if($result){$Division_ID = sqlsrv_fetch_Array($result)['ID'];}
    					$database->query(null,"
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
					$database->query(null,"
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
					$result = $database->query(null,"SELECT Route.ID AS ID FROM nei.dbo.Route WHERE Route.Name = ?;",array($Location['Route']));
					if($result){$Route_ID = sqlsrv_fetch_Array($result)['ID'];}
					$result = $database->query(null,"SELECT Zone.ID  AS ID FROM nei.dbo.Zone WHERE Zone.Name = ?;",array($Location['Division']));
					if($result){$Division_ID = sqlsrv_fetch_Array($result)['ID'];}
					$result = $database->query(null,"SELECT Terr.ID  AS ID FROM nei.dbo.Terr WHERE Terr.Name = ?;",array($Location['Territory']));
					if($result){$Territory_ID = sqlsrv_fetch_Array($result)['ID'];}
					$resource = $database->query(null,"SELECT Max(Rol.ID) AS ID FROM nei.dbo.Rol;");
					$Rolodex_Primary_Key = sqlsrv_fetch_array($resource)['ID'];
					$Rolodex_Primary_Key++;
					$database->query(null,"INSERT INTO nei.dbo.Rol(ID, Name, State, Phone, Fax, Remarks, Type, fLong, Latt, GeoLock, Since, Last, EN, Cellular, Country, Contact, EMail,Website) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",array($Rolodex_Primary_Key, $Location['Tag'], "NY", $Location['Contact_Phone'], $Location['Contact_Fax'],"",4,0,0,0,'2018-01-22 00:00:00.000','2018-01-22 00:00:00.000',1,"(","United States",$Location['Contact_Name'],$Location['Contact_Email'],$Location['Contact_Website']));
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
					print json_encode(array('data'=>$Location));
				}
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
