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
    <?php $_GET[ 'Bootstrap' ] = '5.1';?>
    <?php require( bin_meta . 'index.php');?>
    <?php require( bin_css . 'index.php');?>
    <?php require( bin_js  . 'index.php');?>
</head>
<body onload='finishLoadingPage();'>
  	<div id='wrapper'>
	    <?php require( 'bin/php/element/navigation.php' );?>
	    <?php require( 'bin/php/element/loading.php' );?>
	    <div id='page-wrapper' class='content'>
			<div class='card card-full card-primary border-0'>
				<div class='card-heading bg-white text-black'><h4><?php \singleton\fontawesome::getInstance( )->Contract( );?> Contracts</h4></div>
				<div class='card-body bg-dark'>
					<div id='Form_Contract'>
						<div class='card'>
							<div class='card-heading'></div>
							<div class='card-body'>
								<div style='display:block !important;'>
									<fieldset >
										<legend>Contract</legend>
										<editor-field name='ID'></editor-field>
										<editor-field name='Customer'></editor-field>
										<editor-field name='Location'></editor-field>
										<editor-field name='Job'></editor-field>
										<editor-field name='Start_Date'></editor-field>
										<editor-field name='End_Date'></editor-field>
										<editor-field name='Amount'></editor-field>
										<editor-field name='Cycle'></editor-field>
										<editor-field name='Escalation_Factor'></editor-field>
										<editor-field name='Escalation_Date'></editor-field>
										<editor-field name='Escalation_Type'></editor-field>
										<editor-field name='Escalation_Cycle'></editor-field>
										<editor-field name='Escalation_Link'></editor-field>
										<editor-field name='Escalation_Remarks'></editor-field>
									</fieldset>
								</div>
							</div>
						</div>
					</div>
					<table id='Table_Contracts' class='display' cellspacing='0' width='100%'>
						<thead class='text-white border border-white'><tr>
							<th class='text-white border border-white'>ID</th>
							<th class='text-white border border-white'>Customer</th>
							<th class='text-white border border-white'>Location</th>
							<th class='text-white border border-white'>Job</th>
							<th class='text-white border border-white'>Start</th>
							<th class='text-white border border-white'>End</th>
							<th class='text-white border border-white'>Length</th>
							<th class='text-white border border-white'>Amount</th>
							<th class='text-white border border-white'>Cycle</th>
							<th class='text-white border border-white'>Esc. Factor</th>
							<th class='text-white border border-white'>Esc. Date</th>
							<th class='text-white border border-white'>Esc. Type</th>
							<th class='text-white border border-white'>Esc. Cycle</th>
							<th class='text-white border border-white'>Link</th>
							<th class='text-white border border-white'>Remarks</th>
			            </tr>
			            <tr>
							<th class='text-white border border-white'><input class='redraw' type='text' name='ID' placeholder='ID' value='<?php echo isset( $_GET[ 'ID' ] ) ? $_GET[ 'ID' ] : null;?>' /></th>
							<th class='text-white border border-white'><input class='redraw' type='text' name='Customer' placeholder='Customer' value='<?php echo isset( $_GET[ 'Customer' ] ) ? $_GET[ 'Customer' ] : null;?>' /></th>
							<th class='text-white border border-white'><input class='redraw' type='text' name='Location' placeholder='Location' value='<?php echo isset( $_GET[ 'Location' ] ) ? $_GET[ 'Location' ] : null;?>' /></th>
							<th class='text-white border border-white'><input class='redraw' type='text' name='Job' placeholder='Job' value='<?php echo isset( $_GET[ 'Job' ] ) ? $_GET[ 'Job' ] : null;?>' /></th>
							<th class='text-white border border-white'><input class='redraw date' type='text' name='Start' placeholder='Start' value='<?php echo isset( $_GET[ 'Start' ] ) ? $_GET[ 'Start' ] : null;?>' /></th>
							<th class='text-white border border-white'><input class='redraw date' type='text' name='End' placeholder='End' value='<?php echo isset( $_GET[ 'End' ] ) ? $_GET[ 'End' ] : null;?>' /></th>
							<th class='text-white border border-white'><input class='redraw' type='text' name='Length' placeholder='Length' value='<?php echo isset( $_GET[ 'Length' ] ) ? $_GET[ 'Length' ] : null;?>' /></th>
							<th class='text-white border border-white'><input class='redraw' type='text' name='Amount' placeholder='Amount' value='<?php echo isset( $_GET[ 'Amount' ] ) ? $_GET[ 'Amount' ] : null;?>' /></th>
							<th class='text-white border border-white'><input class='redraw' type='text' name='Cycle' placeholder='Cycle' value='<?php echo isset( $_GET[ 'Cycle' ] ) ? $_GET[ 'Cycle' ] : null;?>' /></th>
							<th class='text-white border border-white'><input class='redraw' type='text' name='Esc. Factor' placeholder='Esc. Factor' value='<?php echo isset( $_GET[ 'Esc. Factor' ] ) ? $_GET[ 'Esc. Factor' ] : null;?>' /></th>
							<th class='text-white border border-white'><input class='redraw' type='text' name='Esc. Date' placeholder='Esc. Date' value='<?php echo isset( $_GET[ 'Esc. Date' ] ) ? $_GET[ 'Esc. Date' ] : null;?>' /></th>
							<th class='text-white border border-white'><input class='redraw' type='text' name='Esc. Type' placeholder='Esc. Type' value='<?php echo isset( $_GET[ 'Esc. Type' ] ) ? $_GET[ 'Esc. Type' ] : null;?>' /></th>
							<th class='text-white border border-white'><input class='redraw' type='text' name='Esc. Cycle' placeholder='Esc. Cycle' value='<?php echo isset( $_GET[ 'Esc. Cycle' ] ) ? $_GET[ 'Esc. Cycle' ] : null;?>' /></th>
							<th class='text-white border border-white'><input class='redraw' type='text' name='Link' placeholder='Link' value='<?php echo isset( $_GET[ 'Link' ] ) ? $_GET[ 'Link' ] : null;?>' /></th>
							<th class='text-white border border-white'><input class='redraw' type='text' name='Remarks' placeholder='Remarks' value='<?php echo isset( $_GET[ 'Remarks' ] ) ? $_GET[ 'Remarks' ] : null;?>' /></th>
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
} else {?><html><head><script>document.location.href='../login.php?Forward=contracts.php';</script></head></html><?php }?>
