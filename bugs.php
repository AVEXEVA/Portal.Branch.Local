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
        ||  !isset( $Privileges[ 'Customer' ] )
        || 	!check( privilege_read, level_group, $Privileges[ 'Bugs' ] )
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
    <?php $_GET[ 'Bootstrap' ] = '5.1';?>
    <?php require( bin_meta . 'index.php' );?>
    <?php require( bin_css  . 'index.php');?>
    <?php require( bin_js   . 'index.php' );?>
</head>
<body onload='finishLoadingPage();'>
    <div id='wrapper'>
        <?php require( bin_php . 'element/navigation.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id='page-wrapper' class='content'>
          <div class='card card-full card-primary'>
            <div class='card-heading'><h4><?php \singleton\fontawesome::getInstance( )->Customer( 1 );?> Bugs</h4></div>
            <div class='card-body bg-dark'>
                <table id='Table_Bugs' class='display' cellspacing='0' width='100%'>
                    <thead><tr>
                        <th class='text-white border border-white'>ID</th>
                        <th class='text-white border border-white'>Name</th>
                        <th class='text-white border border-white'>Description</th>
                        <th class='text-white border border-white'>Severity</th>
                        <th class='text-white border border-white'>Suggestion</th>
                        <th class='text-white border border-white'>Resolution</th>
                        <th class='text-white border border-white'>Fixed</th>
                    </tr><tr>
                        <th class='text-white border border-white'><input class='redraw form-control' type='text' name='ID' placeholder='ID' value='<?php echo isset( $_GET[ 'ID' ] ) ? $_GET[ 'ID' ] : null;?>' /></th>
                        <th class='text-white border border-white'><input class='redraw form-control' type='text' name='Name' placeholder='Name' value='<?php echo isset( $_GET[ 'Name' ] ) ? $_GET[ 'Name' ] : null;?>' /></th>
                        <th class='text-white border border-white'><input class='redraw form-control' type='text' name='Description' placeholder='Description' value='<?php echo isset( $_GET[ 'Description' ] ) ? $_GET[ 'Description' ] : null;?>' /></th>
                        <th class='text-white border border-white'><input class='redraw form-control' type='text' name='Severity' placeholder='Severity' value='<?php echo isset( $_GET[ 'Severity' ] ) ? $_GET[ 'Severity' ] : null;?>' /></th>
                        <th class='text-white border border-white'><input class='redraw form-control' type='text' name='Suggestion' placeholder='Suggestion' value='<?php echo isset( $_GET[ 'Suggestion' ] ) ? $_GET[ 'Suggestion' ] : null;?>' /></th>
                        <th class='text-white border border-white'><input class='redraw form-control' type='text' name='Resolution' placeholder='Resolution' value='<?php echo isset( $_GET[ 'Resolution' ] ) ? $_GET[ 'Resolution' ] : null;?>' /></th>
                        <th class='text-white border border-white'><input class='redraw form-control' type='text' name='Fixed' placeholder='Fixed' value='<?php echo isset( $_GET[ 'Fixed' ] ) ? $_GET[ 'Fixed' ] : null;?>' /></th>
                    </tr></thead>
                </table>
            </div>
        </div>
    </div>
    <script>
    var Table_Bugs = $('#Table_Bugs').DataTable( {
        dom            : "<'row'<'col-sm-3 search'><'col-sm-9'B>><'row'<'col-sm-12't>>",
        processing     : true,
        serverSide     : true,
        searching      : false,
        lengthChange   : false,
        scrollResize   : true,
        scrollY        : 100,
        scroller       : true,
        scrollCollapse : true,
        paging         : true,
        orderCellsTop  : true,
        autoWidth      : true,
        ajax       : {
            url : 'bin/php/get/Bugs.php',
            data : function( d ){
                d = {
                    start : d.start,
                    length : d.length,
                    order : {
                    column : d.order[0].column,
                    dir : d.order[0].dir
                }
                };
                d.ID = $('input[name="ID"]').val( );
                d.Name = $('input[name="Name"]').val( );
                d.Description = $('input[name="Description"]').val( );
                d.Severity = $('input[name="Severity"]').val( );
                d.Suggestion = $('input[name="Suggestion"]').val( );
                d.Resolution = $('input[name="Resolution"]').val( );
                d.Fixed = $('input[name="Fixed"]').val( );
                return d;
            }
        },
        columns: [
            {
                data    : 'ID'
            },{
                data    : 'Name'
            },{
                data    : 'Description'
            },{
                data    : 'Severity'
            },{
                data    : 'Suggestion'
            },{
                data    : 'Resolution'
            },{
                data    : 'Fixed'
            }
        ]
    } );
    </script>
</body>
</html>
 <?php
    }
} else {?><script>document.location.href='../login.php?Forward=bugs.php&<?php echo http_build_query( $_GET );?>';</script><?php }?>
