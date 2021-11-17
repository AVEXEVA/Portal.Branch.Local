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
        ||  !isset( $Privileges[ 'Contact' ] )
        || 	!check( privilege_read, level_group, $Privileges[ 'Contact' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
        \singleton\database::getInstance( )->query(
          null,
          " INSERT INTO Activity([User], [Date], [Page] )
            VALUES( ?, ?, ? );",
          array(
            $_SESSION[ 'Connection' ][ 'User' ],
            date('Y-m-d H:i:s'),
            'customers.php'
        )
      );
?><!DOCTYPE html>
<html lang='en'>
<head>
    <title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
    <?php
    	$_GET[ 'Bootstrap' ] = '5.1';
      	require( bin_meta . 'index.php');
      	require( bin_css  . 'index.php');
      	require( bin_js   . 'index.php');
    ?>
</head>
<body onload='finishLoadingPage();'>
    <div id='wrapper'>
        <?php require( bin_php . 'element/navigation.php' ); ?>
        <?php require( bin_php . 'element/loading.php' ); ?>
        <div id='page-wrapper' class='content'>
          <div class='card card-primary text-white'>
            <div class='card-heading'><?php \singleton\fontawesome::getInstance( )->User( 1 );?> Contact: <?php echo $Contact['Contact'];?></div>
            <div class='card-body bg-dark'>
              <div class='row'>
          			<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Blank( 1 );?> Name:</div>
          			<div class='col-xs-8'><?php echo $Contact['Contact'];?></div>
              </div>
              <div class='row'>
          			<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Phone( 1 );?> Phone:</div>
          			<div class='col-xs-8'><?php echo $Contact['Phone'];?></div>
              </div>
              <div class='row'>
          			<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Email( 1 );?> Email:</div>
          			<div class='col-xs-8'><?php echo $Contact['Email'];?></div>
              </div>
              <div class='row'>
          			<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Blank( 1 );?> Fax:</div>
          			<div class='col-xs-8'><?php echo $Contact['Fax'];?></div>
              </div>
              <div class='row'>
          			<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Address( 1 );?> Street:</div>
          			<div class='col-xs-8'><?php echo $Contact['Address'];?></div>
              </div>
              <div class='row'>
          			<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Blank( 1 );?> City:</div>
          			<div class='col-xs-8'><?php echo $Contact['City'];?></div>
              </div>
              <div class='row'>
          			<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Blank( 1 );?> Zip:</div>
          			<div class='col-xs-8'><?php echo $Contact['Zip'];?></div>
              </div>
              <div class='row'>
          			<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Blank( 1 );?> State:</div>
          			<div class='col-xs-8'><?php echo $Contact['State'];?></div>
              </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=contact<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
