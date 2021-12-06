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
    if( 	!isset( $Connection[ 'ID' ] )
        ||  !isset( $Privileges[ 'Customer' ] )
        || 	!check( privilege_delete, level_group, $Privileges[ 'Customer' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
        \singleton\database::getInstance( )->query(
          null,
          " INSERT INTO Activity([User], [Date], [Page] )
            VALUES( ?, ?, ? );",
          array(
            $_SESSION[ 'Connection' ][ 'User' ],
            date('Y-m-d H:i:s'),
            'post/territory.php'
        )
      );
		if(isset($_POST['action']) && $_POST['action'] == 'edit'){
			if(isset($_POST['data']) && count($_POST['data']) > 0){
				$data = array();
				foreach($_POST['data'] as $ID=>$Territory){
					$database->query(null,"
						UPDATE dbo.OwnerWithRol
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
					$resource = $database->query(null,"
						SELECT OwnerWithRol.Rol AS Rolodex
						FROM   OwnerWithRol
						WHERE  OwnerWithRol.ID = ?
					;",array($ID));
					$Rolodex_ID = sqlsrv_fetch_array($resource)['Rolodex'];
					$database->query(null,"
						UPDATE dbo.Rol
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
					$database->query(null,";", array());
					$Location['ID'] = intval($Location['ID']);
					$data[] = $Location;
				}
				print json_encode(array('data'=>$data));
			}
		} elseif(isset($_POST['action']) && $_POST['action'] == 'create'){
			if(isset($_POST['data']) && count($_POST['data']) > 0){
				foreach($_POST['data'] as $ID=>$Territory){
					$resource = $database->query(null,"SELECT Max(Rol.ID) AS ID FROM nei.dbo.Rol;");
					$Rolodex_Primary_Key = sqlsrv_fetch_array($resource)['ID'];
					$Rolodex_Primary_Key++;
					$database->query(null,"INSERT INTO nei.dbo.Rol(ID, Name, State, Phone, Fax, Remarks, Type, fLong, Latt, GeoLock, Since, Last, EN, Cellular, Country, Contact, EMail,Website) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",array($Rolodex_Primary_Key, $Territory['Customer_Name'], "NY", $Territory['Contact_Phone'], $Territory['Contact_Fax'],"",4,0,0,0,'2018-01-22 00:00:00.000','2018-01-22 00:00:00.000',1,"(","United States",$Territory['Contact_Name'],$Territory['Contact_Email'],$Territory['Contact_Website']));
					$resource = $database->query(null,"SELECT Max(Owner.ID) AS ID FROM nei.dbo.Owner;");
					$Owner_Primary_Key = sqlsrv_fetch_array($resource)['ID'];
					$Owner_Primary_Key++;
					$resource = $database->query(null,"
						INSERT INTO dbo.Owner()
						VALUES()
					;SELECT SCOPE_IDENTITY();", array($Owner_Primary_Key, $_GET['ID'], $Territory_ID, $Location['Name'], $Location['Tag'], $Location['Street'], $Location['City'], $Location['State'], $Location['Zip'], $Route_ID, $Division_ID, $Location['Maintenance'],0,8009,0,0,0,'.00',$Rolodex_Primary_Key,$Location['Latitude'],$Location['Longitude'],0,'Non-Contract',0,0,0,0,0,0,3,'United States',0,0,0,0,1,0,0,'.00',0,0));
					$Location['ID'] = $Location_Primary_Key;
					print json_encode(array('data'=>$Location));
				}
			}
		} elseif(isset($_POST['action']) && $_POST['action'] == 'delete'){
			if(isset($_POST['data']) && count($_POST['data']) > 0){
				foreach($_POST['data'] as $ID){
					$resource = $database->query(null,"SELECT [Owner].[Rol] as Rolodex_ID FROM [Owner] WHERE [ID] = ?;",array($ID));
					if($resource){
						$Rolodex_ID = sqlsrv_fetch_array($resource)['Rolodex_ID'];
						if(is_numeric($Rolodex_ID) && $Rolodex_ID > 0){
							$database->query(null,"DELETE FROM dbo.Rol WHERE Rol.ID = ?;",array($Rolodex_ID));
						}
					}
					$database->query(null,"DELETE FROM [Owner] WHERE [Owner].[ID] = ?;",array($ID));
				}
				print json_encode(array('data'=>array()));
			}
		}
    }
}?>
