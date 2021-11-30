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
        ||  !isset( $Privileges[ 'Contract' ] )
        || 	!check( privilege_read, level_group, $Privileges[ 'Contract' ] )
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
				<div class='card-heading'><h4><?php \singleton\fontawesome::getInstance( )->Contract( );?> Contracts</h4></div>
				<div class="form-mobile card-body bg-dark text-white"><form method='GET' action='contracts.php'>
					<div class='row'><div class='col-12'>&nbsp;</div></div>
           <div class='row'>
                <div class='col-4'>ID</div>
    						<div class='col-8'><input type='text' name='ID' placeholder='ID' class='redraw' value='<?php echo isset( $_GET[ 'ID' ] ) ? $_GET[ 'ID' ] : null;?>' /></div>
            </div>
              <div class='row'>
              <div class='col-4'>Customer</div>
              <div class='col-8'><input type='text' name='Customer' placeholder='Customer' class='redraw' value='<?php echo isset( $_GET[ 'Customer' ] ) ? $_GET[ 'Customer' ] : null;?>' /></div>
            </div>
            <div class='row'>
              <div class='col-4'>Location</div>
              <div class='col-8'><input type='text' name='Location' placeholder='Location' class='redraw' vlaue='<?php echo isset( $_GET[ 'Location' ] ) ? $_GET[ 'Location' ] : null;?>' /></div>
            </div>
            <div class='row'>
              <div class='col-4'>Job</div>
              <div class='col-8'><input type='text' name='Job' placeholder='Job' class='redraw' value='<?php echo isset( $_GET[ 'Job' ] ) ? $_GET[ 'Job' ] : null;?>' /></div>
            </div>
            <div class='row'>
              <div class='col-4'>Start Date</div>
              <div class='col-8'><input type='text' name='Start_Date' placeholder='Start_Date' class='redraw' vlaue='<?php echo isset( $_GET[ 'Start_Date' ] ) ? $_GET[ 'Start_Date' ] : null;?>' /></div>
            </div>
            <div class='row'>
              <div class='col-4'>End Date</div>
              <div class='col-8'><input type='text' name='End_Date' placeholder='End_Date' class='redraw' value='<?php echo isset( $_GET['End_Date'] ) ? $_GET[ 'End_Date' ] : null;?>' /></div>
            </div>
            <div class='row'>
              <div class='col-4'>Amount</div>
              <div class='col-8'><input type='text' name='Amount' placeholder='Amount' class='redraw' value='<?php echo isset( $_GET['Amount'] ) ? $_GET[ 'Amount' ] : null;;?>' /></div>
            </div>
            <Div class='row'>
              <div class='col-4'>Cycle</div>
              <div class='col-8'><input type='text' name='Cycle' placeholder='Cycle' class='redraw' value='<?php echo isset( $_GET['Cycle'] ) ? $_GET[ 'Cycle'] : null;?>' /></div>
            </div>
            <div class='row'>
              <div class='col-4'>Esc. Factor</div>
              <div class='col-8'><input type='text' name='Esc. Factor' placeholder='Esc. Factor' class='redraw' value='<?php echo isset( $_GET['Esc. Factor'] ) ? $_GET[ 'Esc. Factor' ] : null;?>' /></div>
            </div>
            <div class='row'>
              <div class='col-4'>Esc. Date</div>
              <div class='col-8'><input type='text' name='Esc. Date' placeholder='Esc. Date' class='redraw' value='<?php echo isset( $_GET['Esc. Date'] ) ? $_GET[ 'Esc. Date' ] : null;?>' /></div>
            </div>
            <div class='row'>
              <div class='col-4'>Esc. Type</div>
              <div class='col-8'><input type='text' name='Esc. Type' placeholder='Esc. Type' class='redraw' value='<?php echo isset( $_GET['Esc. Type'] ) ? $_GET[ 'Esc. Type' ] : null;?>' /></div>
            </div>
            <div class='row'>
              <div class='col-4'>Esc. Cycle</div>
              <div class='col-8'><input type='text' name='Esc. Cycle' placeholder='Esc. Cycle' class='redraw' value='<?php echo isset( $_GET['Esc. Cycle'] ) ? $_GET[ 'Esc. Cycle' ] : null;?>' /></div>
            </div>
            <div class='row'>
              <div class='col-4'>Remarks</div>
              <div class='col-8'><input type='text' name='Remarks' placeholder='Remarks' class='redraw' value='<?php echo isset( $_GET['Remarks'] ) ? $_GET[ 'Remarks' ] : null;?>' /></div>
            </div>
          </form></div>
        <div class="card-body bg-dark">
					<table id='Table_Contracts' class='display' cellspacing='0' width='100%'>
						<thead><tr class='text-center'>
							<th class='text-white border border-white' title='ID'>ID</th>
							<th class='text-white border border-white' title='Customer'>Customer</th>
							<th class='text-white border border-white' title='Location'>Location</th>
							<th class='text-white border border-white' title='Job'>Job</th>
							<th class='text-white border border-white' title='Start'>Start</th>
							<th class='text-white border border-white' title='End'>End</th>
							<th class='text-white border border-white' title='Length'>Length</th>
							<th class='text-white border border-white' title='Amount'>Amount</th>
							<th class='text-white border border-white' title='Cycle'>Cycle</th>
							<th class='text-white border border-white' title='Esc. Factor'>Esc. Factor</th>
							<th class='text-white border border-white' title='Esc. Date'>Esc. Date</th>
							<th class='text-white border border-white' title='Esc. Type'>Esc. Type</th>
							<th class='text-white border border-white' title='Esc. Cycle'>Esc. Cycle</th>
							<th class='text-white border border-white' title='Link'>Link</th>
							<th class='text-white border border-white' title='Remarks'>Remarks</th>
			    </tr><tr class='form-desktop'>
							<th class='text-white border border-white' title='ID'><input class='redraw' type='text' name='ID' placeholder='ID' value='<?php echo isset( $_GET[ 'ID' ] ) ? $_GET[ 'ID' ] : null;?>' /></th>
							<th class='text-white border border-white' title='Customer'><input class='redraw' type='text' name='Customer' placeholder='Customer' value='<?php echo isset( $_GET[ 'Customer' ] ) ? $_GET[ 'Customer' ] : null;?>' /></th>
							<th class='text-white border border-white' title='Location'><input class='redraw' type='text' name='Location' placeholder='Location' value='<?php echo isset( $_GET[ 'Location' ] ) ? $_GET[ 'Location' ] : null;?>' /></th>
							<th class='text-white border border-white' title='Job'><input class='redraw' type='text' name='Job' placeholder='Job' value='<?php echo isset( $_GET[ 'Job' ] ) ? $_GET[ 'Job' ] : null;?>' /></th>
							<th class='text-white border border-white' title='Start'><input class='redraw date' type='text' name='Start' placeholder='Start' value='<?php echo isset( $_GET[ 'Start' ] ) ? $_GET[ 'Start' ] : null;?>' /></th>
							<th class='text-white border border-white' title='End'><input class='redraw date' type='text' name='End' placeholder='End' value='<?php echo isset( $_GET[ 'End' ] ) ? $_GET[ 'End' ] : null;?>' /></th>
							<th class='text-white border border-white' title='Length'><input class='redraw' type='text' name='Length' placeholder='Length' value='<?php echo isset( $_GET[ 'Length' ] ) ? $_GET[ 'Length' ] : null;?>' /></th>
							<th class='text-white border border-white' title='Amount'><input class='redraw' type='text' name='Amount' placeholder='Amount' value='<?php echo isset( $_GET[ 'Amount' ] ) ? $_GET[ 'Amount' ] : null;?>' /></th>
							<th class='text-white border border-white' title='Cycle'><input class='redraw' type='text' name='Cycle' placeholder='Cycle' value='<?php echo isset( $_GET[ 'Cycle' ] ) ? $_GET[ 'Cycle' ] : null;?>' /></th>
							<th class='text-white border border-white' title='Esc. Factor'><input class='redraw' type='text' name='Esc. Factor' placeholder='Esc. Factor' value='<?php echo isset( $_GET[ 'Esc. Factor' ] ) ? $_GET[ 'Esc. Factor' ] : null;?>' /></th>
							<th class='text-white border border-white' title='Esc. Date'><input class='redraw' type='text' name='Esc. Date' placeholder='Esc. Date' value='<?php echo isset( $_GET[ 'Esc. Date' ] ) ? $_GET[ 'Esc. Date' ] : null;?>' /></th>
							<th class='text-white border border-white' title='Esc. Type'><input class='redraw' type='text' name='Esc. Type' placeholder='Esc. Type' value='<?php echo isset( $_GET[ 'Esc. Type' ] ) ? $_GET[ 'Esc. Type' ] : null;?>' /></th>
							<th class='text-white border border-white' title='Esc. Cycle'><input class='redraw' type='text' name='Esc. Cycle' placeholder='Esc. Cycle' value='<?php echo isset( $_GET[ 'Esc. Cycle' ] ) ? $_GET[ 'Esc. Cycle' ] : null;?>' /></th>
							<th class='text-white border border-white' title='Link'><input class='redraw' type='text' name='Link' placeholder='Link' value='<?php echo isset( $_GET[ 'Link' ] ) ? $_GET[ 'Link' ] : null;?>' /></th>
							<th class='text-white border border-white' title='Remarks'><input class='redraw' type='text' name='Remarks' placeholder='Remarks' value='<?php echo isset( $_GET[ 'Remarks' ] ) ? $_GET[ 'Remarks' ] : null;?>' /></th>
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
