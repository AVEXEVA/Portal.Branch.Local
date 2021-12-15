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
    <?php  
        $_GET[ 'Bootstrap' ] = '5.1';
        $_GET[ 'Entity_CSS' ] = 1;
        require( bin_meta . 'index.php');
        require( bin_css  . 'index.php');
        require( bin_js   . 'index.php');
    ?>
</head>
<body>
	<div id='wrapper'>
        <?php require( bin_php . 'element/navigation.php' );?>
        <div id='page-wrapper' class='content'>
			<div class="card card-full card-primary border-0">
				<div class='card-heading'><h4><?php \singleton\fontawesome::getInstance( )->Contract( );?> Contracts</h4></div>
				<div class="mobile card-body bg-dark text-white">
                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'ID', isset( $_GET[ 'ID' ] ) ? $_GET[ 'ID' ] : null, false, false, false, 'redraw' );?>
                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Customer', isset( $_GET[ 'Customer' ] ) ? $_GET[ 'Customer' ] : null, false, false, false, 'redraw' );?>
                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Location', isset( $_GET[ 'Location' ] ) ? $_GET[ 'Location' ] : null, false, false, false, 'redraw' );?>
                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Job', isset( $_GET[ 'Job' ] ) ? $_GET[ 'Job' ] : null, false, false, false, 'redraw' );?>
                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Start_Date', isset( $_GET[ 'Start_Date' ] ) ? $_GET[ 'Start_Date' ] : null, false, false, false, 'redraw' );?>
                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'End_Date', isset( $_GET[ 'End_Date' ] ) ? $_GET[ 'End_Date' ] : null, false, false, false, 'redraw' );?>
                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Amount', isset( $_GET[ 'Amount' ] ) ? $_GET[ 'Amount' ] : null, false, false, false, 'redraw' );?>
                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Cycle', isset( $_GET[ 'Cycle' ] ) ? $_GET[ 'Cycle' ] : null, false, false, false, 'redraw' );?>
                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Esc. Factor', isset( $_GET[ 'Esc. Factor' ] ) ? $_GET[ 'Esc. Factor' ] : null, false, false, false, 'redraw' );?>
                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Esc. Date', isset( $_GET[ 'Esc. Date' ] ) ? $_GET[ 'Esc. Date' ] : null, false, false, false, 'redraw' );?>
                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Esc. Type', isset( $_GET[ 'Esc. Type' ] ) ? $_GET[ 'Esc. Type' ] : null, false, false, false, 'redraw' );?>
                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Esc. Cycle', isset( $_GET[ 'Esc. Cycle' ] ) ? $_GET[ 'Esc. Cycle' ] : null, false, false, false, 'redraw' );?>
                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Remarks', isset( $_GET[ 'Remarks' ] ) ? $_GET[ 'Remarks' ] : null, false, false, false, 'redraw' );?>
                </div>
                <div class="card-body bg-dark">
					<table id='Table_Contracts' class='display' cellspacing='0' width='100%'>
						<thead class='text-white border border-white'><tr><?php
                            \singleton\table::getInstance( )->th( 'ID', 'ID' );
                            \singleton\table::getInstance( )->th( 'Customer', 'Customer' );
                            \singleton\table::getInstance( )->th( 'Location', 'Location' );
                            \singleton\table::getInstance( )->th( 'Job', 'Job' );
                            \singleton\table::getInstance( )->th( 'Start', 'Start' );
                            \singleton\table::getInstance( )->th( 'End', 'End' );
                            \singleton\table::getInstance( )->th( 'Length', 'Length' );
                            \singleton\table::getInstance( )->th( 'Amount', 'Amount' );
                            \singleton\table::getInstance( )->th( 'Cycle', 'Cycle' );
                            \singleton\table::getInstance( )->th( 'Esc. Factor', 'Esc. Factor' );
                            \singleton\table::getInstance( )->th( 'Esc. Date', 'Esc. Date' );
                            \singleton\table::getInstance( )->th( 'Esc. Type', 'Esc. Type' );
                            \singleton\table::getInstance( )->th( 'Esc. Cycle', 'Esc. Cycle' );
                            \singleton\table::getInstance( )->th( 'Link', 'Link' );
                            \singleton\table::getInstance( )->th( 'Remarks', 'Remarks' );
            			?></tr><tr class='form-desktop'><?php
                            \singleton\table::getInstance( )->th_input( 'ID', isset( $_GET[ 'ID' ] ) ? $_GET[ 'ID' ] : null );
                            \singleton\table::getInstance( )->th_input( 'Customer', isset( $_GET[ 'Customer' ] ) ? $_GET[ 'Customer' ] : null );
                            \singleton\table::getInstance( )->th_input( 'Location', isset( $_GET[ 'Location' ] ) ? $_GET[ 'Location' ] : null );
                            \singleton\table::getInstance( )->th_input( 'Job', isset( $_GET[ 'Job' ] ) ? $_GET[ 'Job' ] : null );
                            \singleton\table::getInstance( )->th_input( 'Start', isset( $_GET[ 'Start' ] ) ? $_GET[ 'Start' ] : null );
                            \singleton\table::getInstance( )->th_input( 'End', isset( $_GET[ 'End' ] ) ? $_GET[ 'End' ] : null );
                            \singleton\table::getInstance( )->th_input( 'Length', isset( $_GET[ 'Length' ] ) ? $_GET[ 'Length' ] : null );
                            \singleton\table::getInstance( )->th_input( 'Amount', isset( $_GET[ 'Amount' ] ) ? $_GET[ 'Amount' ] : null );
                            \singleton\table::getInstance( )->th_input( 'Cycle', isset( $_GET[ 'Cycle' ] ) ? $_GET[ 'Cycle' ] : null );
                            \singleton\table::getInstance( )->th_input( 'Esc. Factor', isset( $_GET[ 'Esc. Factor' ] ) ? $_GET[ 'Esc. Factor' ] : null );
                            \singleton\table::getInstance( )->th_input( 'Esc. Date', isset( $_GET[ 'Esc. Date' ] ) ? $_GET[ 'Esc. Date' ] : null );
                            \singleton\table::getInstance( )->th_input( 'Esc. Type', isset( $_GET[ 'Esc. Type' ] ) ? $_GET[ 'Esc. Type' ] : null );
                            \singleton\table::getInstance( )->th_input( 'Esc. Cycle', isset( $_GET[ 'Esc. Cycle' ] ) ? $_GET[ 'Esc. Cycle' ] : null );
                            \singleton\table::getInstance( )->th_input( 'Link', isset( $_GET[ 'Link' ] ) ? $_GET[ 'Link' ] : null );
                            \singleton\table::getInstance( )->th_input( 'Remarks', isset( $_GET[ 'Remarks' ] ) ? $_GET[ 'Remarks' ] : null );
                        ?></tr></thead>
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
