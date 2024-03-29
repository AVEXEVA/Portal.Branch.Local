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
        $_SESSION[ 'Connection' ][ 'User' ]
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
    $database->query(
      null,
      " INSERT INTO Activity([User], [Date], [Page])
        VALUES(?,?,?);",
      array(
        $_SESSION[ 'Connection' ][ 'User' ],
        date("Y-m-d H:i:s"),
        'Locations.php'
      )
    );
?><!DOCTYPE html>
<html lang="en">
<head>
    <title>Nouveau Elevator Portal</title>
    <?php $_GET[ 'Bootstrap' ] = '5.1';?>
    <?php require( bin_meta . 'index.php');?>
    <?php require( bin_css . 'index.php');?>
    <style>

    </style>
    <?php require( bin_js  . 'index.php' );?>
</head>
<body onload='finishLoadingPage();'>
  <div id="wrapper">
    <?php require( bin_php . 'element/navigation.php');?>
    <?php require( bin_php . 'element/loading.php');?>
    <div id="page-wrapper" class='content'>
      <div class="card card-full card-primary border-0">
        <div class="card-heading"><h4><?php \singleton\fontawesome::getInstance( )->Location( 1 );?> Locations</h4></div>
        <div class="form-mobile card-body bg-dark text-white"><form method='GET' action='locations.php'>
          <div class='row'><div class='col-12'>&nbsp;</div></div>
          <div class='row'>
              <div class='col-4'>Search:</div>
              <div class='col-8'><input type='text' name='Search' placeholder='Search' onChange='redraw( );' /></div>
          </div>
          <div class='row'><div class='col-12'>&nbsp;</div></div>
          <div class='row'>
            <div class='col-4'>Name:</div>
            <div class='col-8'><input type='text' name='Name' placeholder='Name' onChange='redraw( );' value='<?php echo $_GET[ 'Name' ];?>' /></div>
          </div>

          <div class='row'><div class='col-12'>&nbsp;</div></div>
          <div class='row'>
            <div class='col-12'><input type='submit' value='Submit' /></div>
          </div>
        </form></div>
        <div class="card-body bg-dark">
          <table id='Table_Privileges' class='display' cellspacing='0' width='100%'>
              <thead>
                  <th title="Employee Work ID">Work ID</th>
                  <th title="Employee's First Name">Last Name</th>
                  <th title="Employee's First Name">First Name</th>
                  <th title="Employee's Beta Privelege">Beta Access</th>
              </thead>
             <tfooter>
                  <th title="Employee Work ID">Work ID</th>
                  <th title="Employee's First Name">Last Name</th>
                  <th title="Employee's First Name">First Name</th>
                  <th title="Employee's Beta Privelege">Beta Access</th>
              </tfooter>
          </table>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
<?php
    }
} else {?><script>document.location.href='../login.php?Forward=privileges.php&<?php echo http_build_query( $_GET );?>';</script><?php }?>