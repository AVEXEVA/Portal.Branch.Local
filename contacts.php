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
            'contacts.php'
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
			<div class='card card-full card-primary border-0'>
				<div class='card-heading bg-white text-black'><h4><?php \singleton\fontawesome::getInstance( )->Users( );?> Contacts</h4></div>
				<div class="mobile card-body bg-dark text-white">
			        <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Search', isset( $_GET[ 'Search' ] ) ? $_GET[ 'Search' ] : null, false, false, false, 'redraw' );?>
			        <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'ID', isset( $_GET[ 'ID' ] ) ? $_GET[ 'ID' ] : null, false, false, false, 'redraw' );?>
			        <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Contact', isset( $_GET[ 'Contact' ] ) ? $_GET[ 'Contact' ] : null, false, false, false, 'redraw' );?>
			        <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Type', isset( $_GET[ 'Type' ] ) ? $_GET[ 'Type' ] : null, false, false, false, 'redraw' );?>
			        <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Position', isset( $_GET[ 'Position' ] ) ? $_GET[ 'Position' ] : null, false, false, false, 'redraw' );?>
			        <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Phone', isset( $_GET[ 'Phone' ] ) ? $_GET[ 'Phone' ] : null, false, false, false, 'redraw' );?>
			        <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Email', isset( $_GET[ 'Email' ] ) ? $_GET[ 'Email' ] : null, false, false, false, 'redraw' );?>
			        <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Street', isset( $_GET[ 'Street' ] ) ? $_GET[ 'Street' ] : null, false, false, false, 'redraw' );?>
			        <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'City', isset( $_GET[ 'City' ] ) ? $_GET[ 'City' ] : null, false, false, false, 'redraw' );?>
			        <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'State', isset( $_GET[ 'State' ] ) ? $_GET[ 'State' ] : null, false, false, false, 'redraw' );?>
			        <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Zip', isset( $_GET[ 'Zip' ] ) ? $_GET[ 'Zip' ] : null, false, false, false, 'redraw' );?>
		        </div>
				<div class='card-body bg-darker'>
					<table id='Table_Contacts' class='display' cellspacing='0' width='100%'>
						<thead><tr><?php 
							\singleton\table::getInstance( )->th( 'ID', 'ID' );
							\singleton\table::getInstance( )->th( 'Contact', 'Other' );
							\singleton\table::getInstance( )->th( 'Type', 'Other' );
							\singleton\table::getInstance( )->th( 'Name', 'Other' );
							\singleton\table::getInstance( )->th( 'Position', 'Other' );
							\singleton\table::getInstance( )->th( 'Phone', 'Phone' );
							\singleton\table::getInstance( )->th( 'Email', 'Email' );
							\singleton\table::getInstance( )->th( 'Address', 'Address' );
						?></tr>
			            <tr class='desktop'><?php
			            	\singleton\table::getInstance( )->th_input( 'ID', isset( $_GET[ 'ID' ] ) ? $_GET[ 'ID' ] : null );
			            	\singleton\table::getInstance( )->th_input( 'Contact', isset( $_GET[ 'Contact' ] ) ? $_GET[ 'Contact' ] : null );
			            	\singleton\table::getInstance( )->th_select( 'Type', isset( $_GET[ 'Type' ] ) ? $_GET[ 'Type' ] : null, array( 0 => 'Customer', 4 => 'Location', 5 => 'Customer' ) );
			            	\singleton\table::getInstance( )->th_input( 'Name', isset( $_GET[ 'Name' ] ) ? $_GET[ 'Contact' ] : null );
			            	\singleton\table::getInstance( )->th_input( 'Position', isset( $_GET[ 'Position' ] ) ? $_GET[ 'Position' ] : null );
			            	\singleton\table::getInstance( )->th_input( 'Phone', isset( $_GET[ 'Phone' ] ) ? $_GET[ 'Phone' ] : null );
			            	\singleton\table::getInstance( )->th_input( 'Email', isset( $_GET[ 'Email' ] ) ? $_GET[ 'Email' ] : null );
			            	\singleton\table::getInstance( )->th_input( 'Contact', isset( $_GET[ 'Address' ] ) ? $_GET[ 'Address' ] : null );
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
} else {?><html><head><script>document.location.href='../login.php?Forward=contacts.php';</script></head></html><?php }?>
