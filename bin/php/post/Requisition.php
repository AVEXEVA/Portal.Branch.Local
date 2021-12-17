<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( );
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
  if(     !isset( $Connection[ 'ID' ] )
      ||  !isset( $Privileges[ 'Requisition' ] )
      ||  !check( privilege_read, level_group, $Privileges[ 'Requisition' ] )
  ){ ?><?php print json_encode( array( 'data' => array( ) ) ); ?><?php }
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
					$resource = $database->query(null,
            " SELECT Loc.Loc AS Location_ID
						  FROM   Loc
						  WHERE  Loc.Tag = ?
					;",array($Requisition['Location']));
					if($resource){$Location_ID = sqlsrv_fetch_array($resource)['Location_ID'];}
					$resource = $database->query(null,"
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
					$resource = $database->query(null,"
						SELECT Elev.ID AS Unit_ID
						FROM   nei.dbo.Elev
						WHERE  Elev.State = ?
					;",array($Requisition['Unit']));
					if($resource){$Unit_ID = sqlsrv_fetch_array($resource)['Unit_ID'];}
					$resource = $database->query(null,"
						INSERT INTO Portal.dbo.Requisition([User], Customer, Location, Job, Unit, Notes)
						VALUES(?,?,?,?,?,?)
					;",array($_SESSION['User'], $Customer_ID, $Location_ID, $Job_ID, $Unit_ID, $Requisition['Notes']));
					$resource = $database->query(null,"SELECT Max(Requisition.ID) AS ID FROM Portal.dbo.Requisition;");
					$Requisition_Primary_Key = sqlsrv_fetch_array($resource)['ID'];
					$Requisition['ID'] = $Requisition_Primary_Key;
					$resource = $database->query(null,"
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
