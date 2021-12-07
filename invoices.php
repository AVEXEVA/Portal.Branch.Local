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
        ||  !isset( $Privileges[ 'Invoice' ] )
        || 	!check( privilege_read, level_group, $Privileges[ 'Invoice' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
        \singleton\database::getInstance( )->query(
          null,
          " INSERT INTO Activity([User], [Date], [Page] )
            VALUES( ?, ?, ? );",
          array(
            $_SESSION[ 'Connection' ][ 'User' ],
            date('Y-m-d H:i:s'),
            'invoices.php'
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
        <?php require(bin_php.'element/navigation.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id='page-wrapper' class='content'>
            <div class='card card-full card-primary bg-dark text-white'>
                <div class='card-heading'><h4><?php \singleton\fontawesome::getInstance( )->Invoice( 1 );?> Invoices</h4></div>
        				<div class='card-body form-mobile'><form action='invoices.php'>
                  <div class='row'><div class='col-12'>&nbsp;</div></div>
                  <div class='row'>
                      <div class='col-4'>Search:</div>
                      <div class='col-8'><input class='redraw form-control' type='text' name='Search' placeholder='Search' value='<?php echo isset( $_GET[ 'Search' ] ) ? $_GET[ 'Search' ] : null;?>' /></div>
                  </div>
                  <div class='row'><div class='col-12'>&nbsp;</div></div>
                  <div class='row'>
                    <div class='col-4'>Customer:</div>
                    <div class='col-8'><input class='redraw form-control' type='text' name='Search' placeholder='Search' value='<?php echo isset( $_GET[ 'Customer' ] ) ? $_GET[ 'Customer' ] : null;?>' /></div>
                  </div>
                  <div class='row'>
                    <div class='col-4'>Locaton:</div>
                    <div class='col-8'><input class='redraw form-control' type='text' name='Search' placeholder='Search' value='<?php echo isset( $_GET[ 'Locaton' ] ) ? $_GET[ 'Locaton' ] : null;?>' /></div>
                  </div>
                  <div class='row'>
                    <div class='col-4'>Job:</div>
                    <div class='col-8'><input class='redraw form-control' type='text' name='Search' placeholder='Search' value='<?php echo isset( $_GET[ 'Job' ] ) ? $_GET[ 'Job' ] : null;?>' /></div>
                  </div>
                  <div class='row'>
                    <div class='col-4'>Type:</div>
                    <div class='col-8'><input class='redraw form-control' type='text' name='Search' placeholder='Search' value='<?php echo isset( $_GET[ 'Type' ] ) ? $_GET[ 'Type' ] : null;?>' /></div>
                  </div>
                  <div class='row'>
                    <div class='col-4'>Date:</div>
                    <div class='col-8'><input class='redraw form-control' type='text' name='Search' placeholder='Search' value='<?php echo isset( $_GET[ 'Date' ] ) ? $_GET[ 'Date' ] : null;?>' /></div>
                  </div>
                  <div class='row'>
                    <div class='col-4'>Due:</div>
                    <div class='col-8'><input class='redraw form-control' type='text' name='Search' placeholder='Search' value='<?php echo isset( $_GET[ 'Due' ] ) ? $_GET[ 'Due' ] : null;?>' /></div>
                  </div>
                  <div class='row'>
                    <div class='col-4'>Original:</div>
                    <div class='col-8'><input class='redraw form-control' type='text' name='Search' placeholder='Search' value='<?php echo isset( $_GET[ 'Original' ] ) ? $_GET[ 'Original' ] : null;?>' /></div>
                  </div>
                  <div class='row'>
                    <div class='col-4'>Balance:</div>
                    <div class='col-8'><input class='redraw form-control' type='text' name='Search' placeholder='Search' value='<?php echo isset( $_GET[ 'Balance' ] ) ? $_GET[ 'Balance' ] : null;?>' /></div>
                  </div>
                  <div class='row'>
                    <div class='col-4'>Description:</div>
                    <div class='col-8'><input class='redraw form-control' type='text' name='Search' placeholder='Search' value='<?php echo isset( $_GET[ 'Description' ] ) ? $_GET[ 'Description' ] : null;?>' /></div>
                  </div>
                  <div class='row'><div class='col-12'>&nbsp;</div></div>
                </form></div>
                <div class='card-body'>
                    <table id='Table_Invoices' class='display' cellspacing='0' width='100%'>
                        <thead>
                          <tr>
                            <th class='text-white border border-white' title='Invoice#'><?php \singleton\fontawesome::getInstance( )->Invoice();?>Invoice #</th>
                            <th class='text-white border border-white' title='Customer'><?php \singleton\fontawesome::getInstance( )->Customer();?>Customer</th>
                            <th class='text-white border border-white' title='Location'><?php \singleton\fontawesome::getInstance( )->Location();?>Location</th>
                            <th class='text-white border border-white' title='Job'><?php \singleton\fontawesome::getInstance( )->Job();?>Job</th>
							              <th class='text-white border border-white' title='Type'><?php \singleton\fontawesome::getInstance( )->Note();?>Type</th>
                            <th class='text-white border border-white' title='Date'><?php \singleton\fontawesome::getInstance( )->Calendar();?>Date</th>
                            <th class='text-white border border-white' title='Due'><?php \singleton\fontawesome::getInstance( )->Dollar();?>Due</th>
                            <th class='text-white border border-white' title='Original'><?php \singleton\fontawesome::getInstance( )->Dollar();?>Original</th>
                            <th class='text-white border border-white' title='Balanace'><?php \singleton\fontawesome::getInstance( )->Dollar();?>Balance</th>
                            <th class='text-white border border-white' title='Description'><?php \singleton\fontawesome::getInstance( )->Description();?>Description</th>
                        </tr>
                        <tr class='form-desktop'>
                          <th><input class='redraw form-control' type='text' name='Invoice #' placeholder='Invoice #' value='<?php echo isset( $_GET[ 'Invoice #' ] ) ? $_GET[ 'Invoice #' ] : null;?>' /></th>
                          <th class='text-white border border-white' title='Customer'><div><input type='text' autocomplete='off' class='redraw form-control' name='Customer' placeholder='Customer' value='<?php echo isset( $_GET[ 'Customer' ] ) ? $_GET[ 'Customer' ] : null;?>' /></div>
                          <script>
                            $( 'input:visible[name="Customer"]' )
                                .typeahead({
                                    minLength : 4,
                                    hint: true,
                                    highlight: true,
                                    limit : 5,
                                    display : 'FieldValue',
                                    source: function( query, result ){
                                        $.ajax({
                                            url : 'bin/php/get/search/Customers.php',
                                            method : 'GET',
                                            data    : {
                                                search :  $('input:visible[name="Customer"]').val( )
                                            },
                                            dataType : 'json',
                                            beforeSend : function( ){
                                                abort( );
                                            },
                                            success : function( data ){
                                                result( $.map( data, function( item ){
                                                    return item.FieldValue;
                                                } ) );
                                            }
                                        });
                                    },
                                    afterSelect: function( value ){
                                        $( 'input:visible[name="Customer"]').val( value );
                                    }
                                }
                            );
                          </script></th>
                          <th><input class='redraw form-control' type='text' name='Location' placeholder='Location' value='<?php echo isset( $_GET[ 'Location' ] ) ? $_GET[ 'Location' ] : null;?>' /></th>
                          <th><input class='redraw form-control' type='text' name='Job' placeholder='Job' value='<?php echo isset( $_GET[ 'Job' ] ) ? $_GET[ 'Job' ] : null;?>' /></th>
                          <th><input class='redraw form-control' type='text' name='Type' placeholder='Type' value='<?php echo isset( $_GET[ 'Type' ] ) ? $_GET[ 'Type' ] : null;?>' /></th>
                          <th><input class='redraw form-control' type='text' name='Date' placeholder='Date' value='<?php echo isset( $_GET[ 'Date' ] ) ? $_GET[ 'Date' ] : null;?>' /></th>
                          <th><input class='redraw form-control' type='text' name='Due' placeholder='Due' value='<?php echo isset( $_GET[ 'Due' ] ) ? $_GET[ 'Due' ] : null;?>' /></th>
                          <th><input class='redraw form-control' type='text' name='Original' placeholder='Original' value='<?php echo isset( $_GET[ 'Original' ] ) ? $_GET[ 'Original' ] : null;?>' /></th>
                          <th><input class='redraw form-control' type='text' name='Balance' placeholder='Balance' value='<?php echo isset( $_GET[ 'Balance' ] ) ? $_GET[ 'Balance' ] : null;?>' /></th>
                          <th><input class='redraw form-control' type='text' name='Description' placeholder='Description' value='<?php echo isset( $_GET[ 'Description' ] ) ? $_GET[ 'Description' ] : null;?>' /></th>
                      </tr>
                     </thead>
                  </table>
              </div>
          </div>
      </div>
  </div>
</body>
</html>
<?php
    }
} else {?><script>document.location.href='../login.php?Forward=invoices.php&<?php echo http_build_query( $_GET );?>';</script><?php }?>
