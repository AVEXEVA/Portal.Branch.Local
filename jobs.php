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
        ||  !isset( $Privileges[ 'Job' ] )
        ||  !check( privilege_read, level_group, $Privileges[ 'Job' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
        \singleton\database::getInstance( )->query(
          null,
          " INSERT INTO Activity([User], [Date], [Page] )
            VALUES( ?, ?, ? );",
          array(
            $_SESSION[ 'Connection' ][ 'User' ],
            date('Y-m-d H:i:s'),
            'jobs.php'
        )
      );
?><!DOCTYPE html>
<html lang='en'>
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
<body onload='finishLoadingPage();'>
    <div id='wrapper'>
        <?php require( bin_php . 'element/navigation.php'); ?>
        <div id='page-wrapper' class='content'>
    			<div class='card card-full card-primary'>
    				<div class='card-heading'><h4><?php \singleton\fontawesome::getInstance( )->Job( 1 );?> Jobs</h4></div>
    				<div class='card-body bg-dark'>
    					<table id='Table_Jobs' class='display' cellspacing='0' width='100%'>
    						<thead><tr class='text-align:center;'>
                      <th class='text-white border border-white' title='ID'>ID</th>
                      <th class='text-white border border-white' title='Name'>Name</th>
                      <th class='text-white border border-white' title='Date'>Date</th>
                      <th class='text-white border border-white' title='Customer'>Customer</th>
                      <th class='text-white border border-white' title='Location'>Location</th>
                      <th class='text-white border border-white' title='Type'>Type</th>
                      <th class='text-white border border-white' title='Status'>Status</th>
                      <th class='text-white border border-white' title='Tickets'>Tickets</th>
                      <th class='text-white border border-white' title='Invoices'>Invoices</th>
                  </tr><tr>
                      <th class='text-white border border-white' title='ID'><input class='redraw form-control' type='text' name='ID' placeholder='ID' value='<?php echo isset( $_GET[ 'ID' ] ) ? $_GET[ 'ID' ] : null;?>' /></th>
                      <th class='text-white border border-white' title='Name'><input class='redraw form-control' type='text'name='Name' placeholder='Name' value='<?php echo isset( $_GET[ 'Name' ] ) ? $_GET[ 'Name' ] : null;?>' /></th>
                      <th class='border border-white'><div class='row g-0'>
                          <div class='col-12'><input class='redraw date' type='text' name='Date_Start' placeholder='Date Start' value='<?php echo isset( $_GET[ 'Date_Start' ] ) ? $_GET[ 'Date_Start' ] : null;?>' /></div>
                          <div class='col-12'><input class='redraw date' type='text' name='Date_End' placeholder='Date End' value='<?php echo isset( $_GET[ 'Date_End' ] ) ? $_GET[ 'Date_End' ] : null;?>' /></div>
                      </div></th>
                      <th class='text-white border border-white' title='Customer'><input class='redraw form-control' type='text'name='Customer' placeholder='Customer' value='<?php echo isset( $_GET[ 'Customer' ] ) ? $_GET[ 'Customer' ] : null;?>' /></th>
                      <th class='text-white border border-white' title='Location'><input class='redraw form-control' type='text'name='Location' placeholder='Location' value='<?php echo isset( $_GET[ 'Location' ] ) ? $_GET[ 'Location' ] : null;?>' /></th>
                      <th class='text-white border border-white' title='Type'><select class='redraw form-control' name='Type'>
                          <option value=''>Select</option>
                          <?php
                              $result = \singleton\database::getInstance( )->query(
                                  null,
                                  "   SELECT  JobType.ID,
                                              JobType.Type
                                      FROM    JobType
                                      WHERE   Type <> 'LAWSUITS';",
                                  array( )
                              );
                              if( $result ){while ($row = sqlsrv_fetch_array( $result ) ){?><option value='<?php echo $row['ID'];?>'><?php echo $row['Type'];?></option><?php }}
                          ?>
                      </select></th>
                      <th class='text-white border border-white' title='Status'><select class='form-control' name='Status'>
                        <option value=''>Select</option>
                        <option value='0' <?php echo isset( $_GET[ 'Status' ] ) && $_GET[ 'Status' ] == 0 ? 'selected' : null;?>>Disabled</option>
                        <option value='1' <?php echo isset( $_GET[ 'Status' ] ) && $_GET[ 'Status' ] == 1 ? 'selected' : null;?>>Enabled</option>
                      </select></th>
                      <th class='text-white border border-white' title='Tickets'><input class='form-control' type='text'name='Tickets' placeholder='Tickets' value='<?php echo isset( $_GET[ 'Tickets' ] ) ? $_GET[ 'Tickets' ] : null;?>' /></th>
                      <th class='text-white border border-white' title='Invoices'><input class='form-control' type='text'name='Invoices' placeholder='Invoices' value='<?php echo isset( $_GET[ 'Invoices' ] ) ? $_GET[ 'Invoices' ] : null;?>' /></th>
                  </tr></thead>
    				   </table>
    			    </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=jobs.php';</script></head></html><?php }?>
