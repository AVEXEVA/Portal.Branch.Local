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
        ||  !isset( $Privileges[ 'Territory' ] )
        || 	!check( privilege_read, level_group, $Privileges[ 'Territory' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
        \singleton\database::getInstance( )->query(
          null,
          " INSERT INTO Activity([User], [Date], [Page] )
            VALUES( ?, ?, ? );",
          array(
            $_SESSION[ 'Connection' ][ 'User' ],
            date('Y-m-d H:i:s'),
            'contracts.php'
        )
      );
?><!DOCTYPE html>
<html lang='en'>
<head>
  <title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
     <?php  $_GET[ 'Bootstrap' ] = '5.1';?>
     <?php  $_GET[ 'Entity_CSS' ] = 1;?>
     <?php	require( bin_meta . 'index.php');?>
     <?php	require( bin_css  . 'index.php');?>
     <?php  require( bin_js   . 'index.php');?>
</head>
<body onload='finishLoadingPage();'>
	<div id='wrapper'>
    <?php require( bin_php . 'element/navigation.php' );?>
    <?php require( bin_php.  'element/loading.php' );?>
    <div id='page-wrapper' class='content'>
			<div class="card card-full card-primary border-0">
				<div class='card-heading'><h4><?php \singleton\fontawesome::getInstance( )->Contract( );?> Territories</h4></div>
				<div class="form-mobile card-body bg-dark text-white"><form method='GET' action='territories.php'>
					<div class='row'><div class='col-12'>&nbsp;</div></div>
           <div class='row'>
                <div class='col-4'>ID</div>
    						<div class='col-8'><input type='text' name='ID' placeholder='ID' class='redraw' value='<?php echo isset( $_GET[ 'ID' ] ) ? $_GET[ 'ID' ] : null;?>' /></div>
            </div>
              <div class='row'>
              <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Customer();?>Location</div>
              <div class='col-8'><input type='text' name='Location' placeholder='Location' class='redraw' value='<?php echo isset( $_GET[ 'Location' ] ) ? $_GET[ 'Location' ] : null;?>' /></div>
            </div>
            <div class='row'>
              <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Location();?>Unit</div>
              <div class='col-8'><input type='text' name='Unit' placeholder='Unit' class='redraw' vlaue='<?php echo isset( $_GET[ 'Unit' ] ) ? $_GET[ 'Unit' ] : null;?>' /></div>
            </div>
            <div class='row'>
              <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Job();?>Leads</div>
              <div class='col-8'><input type='text' name='Leads' placeholder='Leads' class='redraw' value='<?php echo isset( $_GET[ 'Leads' ] ) ? $_GET[ 'Leads' ] : null;?>' /></div>
            </div>
            <div class='row'>
              <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Job();?>Proposals</div>
              <div class='col-8'><input type='text' name='Proposal' placeholder='Proposal' class='redraw' value='<?php echo isset( $_GET[ 'Proposal' ] ) ? $_GET[ 'Proposal' ] : null;?>' /></div>
            </div>
            <div class='row'>
              <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Job();?>Collections</div>
              <div class='col-8'><input type='text' name='Collection' placeholder='Collection' class='redraw' value='<?php echo isset( $_GET[ 'Collection' ] ) ? $_GET[ 'Collection' ] : null;?>' /></div>
            </div>
            <div class='row'>
              <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Job();?>Invoices</div>
              <div class='col-8'><input type='text' name='Invoices' placeholder='Invoices' class='redraw' value='<?php echo isset( $_GET[ 'Invoice' ] ) ? $_GET[ 'Invoice' ] : null;?>' /></div>
            </div>
          </form></div>
        <div class="card-body bg-dark">
					<table id='Table_Territories' class='display' cellspacing='0' width='100%'>
						<thead><tr class='text-center'>
							<th class='text-white border border-white' title='ID'>ID</th>
							<th class='text-white border border-white' title='Customer'>Location</th>
							<th class='text-white border border-white' title='Location'>Unit</th>
							<th class='text-white border border-white' title='Job'>Leads</th>
              <th class='text-white border border-white' title='Job'>Proposals</th>
              <th class='text-white border border-white' title='Job'>Collection</th>
              <th class='text-white border border-white' title='Job'>Invoices</th>
			    </tr><tr class='form-desktop'>
							<th class='text-white border border-white' title='ID'><input class='redraw' type='text' name='ID' placeholder='ID' value='<?php echo isset( $_GET[ 'ID' ] ) ? $_GET[ 'ID' ] : null;?>' /></th>
							<th class='text-white border border-white' title='Location'><input class='redraw' type='text' name='Location' placeholder='Location' value='<?php echo isset( $_GET[ 'Location' ] ) ? $_GET[ 'Location' ] : null;?>' /></th>
							<th class='text-white border border-white' title='Unit'><input class='redraw' type='text' name='Unit' placeholder='Unit' value='<?php echo isset( $_GET[ 'Unit' ] ) ? $_GET[ 'Unit' ] : null;?>' /></th>
							<th class='text-white border border-white' title='Lead'><input class='redraw' type='text' name='Lead' placeholder='Lead' value='<?php echo isset( $_GET[ 'Lead' ] ) ? $_GET[ 'Lead' ] : null;?>' /></th>
              <th class='text-white border border-white' title='Proposal'><input class='redraw' type='text' name='Proposal' placeholder='Proposal' value='<?php echo isset( $_GET[ 'Proposal' ] ) ? $_GET[ 'Proposal' ] : null;?>' /></th>
              <th class='text-white border border-white' title='Collection'><input class='redraw' type='text' name='Collection' placeholder='Collection' value='<?php echo isset( $_GET[ 'Collection' ] ) ? $_GET[ 'Collection' ] : null;?>' /></th>
              <th class='text-white border border-white' title='Invoice'><input class='redraw' type='text' name='Invoice' placeholder='Invoice' value='<?php echo isset( $_GET[ 'Invoice' ] ) ? $_GET[ 'Invoice' ] : null;?>' /></th>
          </th>
          </tr></thead>
        </table>
		   </div>
    </div>
  </div>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=contracts.php';</script></head></html><?php }?>
