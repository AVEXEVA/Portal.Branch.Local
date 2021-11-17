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
        ||  !isset( $Privileges[ 'Job' ] )
        || 	!check( privilege_read, level_group, $Privileges[ 'Job' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
        \singleton\database::getInstance( )->query(
          null,
          " INSERT INTO Activity([User], [Date], [Page] )
            VALUES( ?, ?, ? );",
          array(
            $_SESSION[ 'Connection' ][ 'User' ],
            date('Y-m-d H:i:s'),
            'job.php'
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
        ||  !isset( $Privileges[ 'Route' ] )
        || 	!check( privilege_read, level_group, $Privileges[ 'Route' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
        \singleton\database::getInstance( )->query(
          null,
          " INSERT INTO Activity([User], [Date], [Page] )
            VALUES( ?, ?, ? );",
          array(
            $_SESSION[ 'Connection' ][ 'User' ],
            date('Y-m-d H:i:s'),
            'route.php'
        )
      );
      if(     !isset($array['ID'])
          ||  !$Privileged
          || !is_numeric($_GET['ID'])){
            ?><html><head><script>document.location.href="../login.php?Forward=route<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html> <?php }
      else {
          $r = \singleton\database::getInstance( )->query(
            null,
              "SELECT
                  Route.ID             AS ID,
                  Route.Name           AS Route,
                  Route.Name           AS Route_Name,
                  Route.ID             AS Route_ID,
                  Emp.fFirst           AS First_Name,
                  Emp.Last             AS Last_Name,
                  Emp.ID               AS Employee_ID,
                  Emp.fFirst           AS Employee_First_Name,
                  Emp.Last             AS Employee_Last_Name,
                  Emp.fWork            AS fWork,
                  Emp.ID               AS Route_Mechanic_ID,
                  Emp.fFirst           AS Route_Mechanic_First_Name,
                  Emp.Last             AS Route_Mechanic_Last_Name,
                  Rol.Phone            AS Route_Mechanic_Phone_Number
              FROM
                  Route
                  LEFT JOIN Emp   ON  Route.Mech = Emp.fWork
                  LEFT JOIN Rol          ON Emp.Rol    = Rol.ID
              WHERE
                  Route.ID        =   ?
          ;",array(
              $_GET['ID']));
          $Route = sqlsrv_fetch_array($r);
?><!DOCTYPE html>
<html lang="en">
<head>
	<title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
	<?php
		$_GET[ 'Bootstrap' ] = '5.1';
		$_GET[ 'Entity_CSS' ] = 1;
	?>
	<?php require( bin_meta . 'index.php' );?>
    <?php require( bin_css  . 'index.php' );?>
    <?php require( bin_js   . 'index.php' );?>
    <script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=AIzaSyCNrTryEaTEDRz-XDSg890ajL_JRPnLgzc"></script>
</head>
<body>
    <div id="wrapper">
        <?php require(PROJECT_ROOT.'php/element/navigation.php');?>
        <div id="page-wrapper" class='content' style='height:100%;overflow-y:scroll;'>
          <div class='card card-primary'>
            <div class='card-heading'>
              <div class='row g-0 px-3 py-2'>
                <div class='col-6'>
                  <h5><?php \singleton\fontawesome::getInstance( )->Route( 1 );?><a href='Route.php?<?php
                    echo http_build_query( is_array( $_SESSION[ 'Tables' ][ 'Routes' ][ 0 ] ) ? $_SESSION[ 'Tables' ][ 'Routes' ][ 0 ] : array( ) );
                  ?>'>Route</a>: <span><?php
                    echo is_null( $Customer[ 'ID' ] )
                      ? 'New'
                      : $Customer[ 'Name' ];
                  ?></span></h5>
                KN,B </div>
                <div class='col-2'></div>
                <div class='col-2'>
                  <div class='row g-0'>
                    <div class='col-4'>
                      <button
                        class='form-control rounded'
                        onClick="document.location.href='customer.php';"
                      >Create</button>
                    </div>
                    <div class='col-4'>
                      <button
                        class='form-control rounded'
                        onClick="document.location.href='customer.php?ID=<?php echo $Customer[ 'ID' ];?>';"
                      >Refresh</button>
                    </div>
                  </div>
                </div>
                <div class='col-2'>
                  <div class='row g-0'>
                    <div class='col-4'><button class='form-control rounded' onClick="document.location.href='customer.php?ID=<?php echo !is_null( $Customer[ 'ID' ] ) ? array_keys( $_SESSION[ 'Tables' ][ 'Route' ], true )[ array_search( $Customer[ 'ID' ], array_keys( $_SESSION[ 'Tables' ][ 'Route' ], true ) ) - 1 ] : null;?>';">Previous</button></div>
                    <div class='col-4'><button class='form-control rounded' onClick="document.location.href='customers.php?<?php echo http_build_query( is_array( $_SESSION[ 'Tables' ][ 'Route' ][ 0 ] ) ? $_SESSION[ 'Tables' ][ 'Route' ][ 0 ] : array( ) );?>';">Table</button></div>
                    <div class='col-4'><button class='form-control rounded' onClick="document.location.href='customer.php?ID=<?php echo !is_null( $Customer[ 'ID' ] )? array_keys( $_SESSION[ 'Tables' ][ 'Route' ], true )[ array_search( $Customer[ 'ID' ], array_keys( $_SESSION[ 'Tables' ][ 'Route' ], true ) ) + 1 ] : null;?>';">Next</button></div>
                  </div>
                  <div class='card-body'>
                    <div class='card-columns'>
                    <div class='card card-primary border-0'>
                      <div class='card-heading'>Routes</div>
                      <div class='card-body'>
                        <div class='row g-0'>
                          <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Job(1);?> Faults</div>
                          <div class='col-8'><?php echo $Route[ 'Faults' ];?></div>
                        </div>
                        <div class='row g-0'>
                          <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Location</div>
                          <div class='col-8'><?php echo $Route[ 'Location_Name' ];?></div>
                        </div>
                        <div class='row g-0'>
                          <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Units</div>
                          <div class='col-8'><?php echo $Route[ 'Unit' ];?></div>
                        </div>
                        <div class='row g-0'>
                          <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Violations</div>
                          <div class='col-8'><?php echo $Route[ 'Violation' ];?> hrs</div>
                        </div>
                        <div class='row g-0'>
                          <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> User</div>
                          <div class='col-8'><?php echo $Route[ 'User' ];?></div>
                        </div>
                        <div class='row g-0'>
                          <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Date(1);?> Date</div>
                          <div class='col-8'><?php echo $Route[ 'Date' ];?></div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </body>
  </html>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=route<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
