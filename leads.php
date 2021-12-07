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
        ||  !isset( $Privileges[ 'Lead' ] )
        || 	!check( privilege_read, level_group, $Privileges[ 'Lead' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
        \singleton\database::getInstance( )->query(
          null,
          " INSERT INTO Activity([User], [Date], [Page] )
            VALUES( ?, ?, ? );",
          array(
            $_SESSION[ 'Connection' ][ 'User' ],
            date('Y-m-d H:i:s'),
            'leads.php'
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
    <?php require( bin_php . 'element/navigation.php');?>
    <?php require( bin_php . 'element/loading.php');?>
    <div id='page-wrapper' class='content'>
			<div class='card card-full card-primary border-0'>
				<div class='card-heading'><h3><?php \singleton\fontawesome::getInstance( )->Customer();?> Leads</h3></div>
				<div class='card-body bg-dark'>
          <table id='Table_Leads' class='display' cellspacing='0' width='100%'>
					<thead><tr>
              <th class='text-white border border-white' title='ID'><?php \singleton\fontawesome::getInstance( )->Proposal();?>ID</th>
              <th class='text-white border border-white' title='Name'><?php \singleton\fontawesome::getInstance( )->Customer();?>Name</th>
              <th class='text-white border border-white' title='Type'><?php \singleton\fontawesome::getInstance( )->Note();?>Type</th>
              <th class='text-white border border-white' title='Customer'><?php \singleton\fontawesome::getInstance( )->Customer();?>Customer</th>
              <th class='text-white border border-white' title='Address'><?php \singleton\fontawesome::getInstance( )->Address();?>Address</th>
              <th class='text-white border border-white' title='Contact'><?php \singleton\fontawesome::getInstance( )->User();?>Contact</th>
              <th class='text-white border border-white' title='Probability'><?php \singleton\fontawesome::getInstance( )->Pnl();?>Probability</th>
              <th class='text-white border border-white' title='Level'><?php \singleton\fontawesome::getInstance( )->Activities();?>Level</th>
              <th class='text-white border border-white' title='Status'><?php \singleton\fontawesome::getInstance( )->Update();?>Status</th>
            </tr><tr class='desktop'>
              <th class='text-white border border-white' title='ID'><input class='redraw form-control' type='text' name='ID' placeholder='ID' value='<?php echo isset( $_GET[ 'ID' ] ) ? $_GET[ 'ID' ] : null;?>' /></th>
              <th class='text-white border border-white' title='Name'><input class='redraw form-control' type='text' name='Name' placeholder='Name' value='<?php echo isset( $_GET[ 'Name' ] ) ? $_GET[ 'Name' ] : null;?>' /></th>
              <th class='text-white border border-white' title='Type'><input class='redraw form-control' type='text' name='Type' placeholder='Type' value='<?php echo isset( $_GET[ 'Type' ] ) ? $_GET[ 'Type' ] : null;?>' /></th>
              <th class='text-white border border-white' title='Customer'><div><input type='text' autocomplete='off' class='redraw form-control' name='Customer' value='<?php echo isset( $_GET[ 'Customer' ] ) ? $_GET[ 'Customer' ] : null;?>' /></div>
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
              <th class='text-white border border-white' title='Address'><input class='redraw form-control' type='text' name='Address' placeholder='Address' value='<?php echo isset( $_GET[ 'Address' ] ) ? $_GET[ 'Address' ] : null;?>' /></th>
              <th class='text-white border border-white' title='Contact'><input class='redraw form-control' type='text' name='Contact' placeholder='Contact' value='<?php echo isset( $_GET[ 'Contact' ] ) ? $_GET[ 'Contact' ] : null;?>' /></th>
              <th class='text-white border border-white' title='Probability'><input class='redraw form-control' type='text' name='Probability' placeholder='Probability' value='<?php echo isset( $_GET[ 'Probability' ] ) ? $_GET[ 'Probability' ] : null;?>' /></th>
              <th class='text-white border border-white' title='Level'><input class='redraw form-control' type='text' name='Level' placeholder='Level' value='<?php echo isset( $_GET[ 'Level' ] ) ? $_GET[ 'Level' ] : null;?>' /></th>
              <th class='text-white border border-white' title='Status'><select class='redraw form-control' type='text' name='Status' placeholder='Status' value='<?php echo isset( $_GET[ 'Status' ] ) ? $_GET[ 'Status' ] : null;?>' /></th>
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
} else {?><html><head><script>document.location.href='../login.php?Forward=leads.php';</script></head></html><?php }?>
