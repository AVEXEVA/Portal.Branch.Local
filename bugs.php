<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/bin/php/index.php' );
}
if( isset( $_SESSION[ 'User' ], $_SESSION[ 'Hash' ] ) ){
    //Connection
    $result = $database->query(
        null,
        "   SELECT  *
		        FROM        Connection
            WHERE       Connection.Connector = ?
            AND         Connection.Hash  = ?;",
        array(
            $_SESSION[ 'User' ],
            $_SESSION[ 'Hash' ]
        )
    );
    $Connection = sqlsrv_fetch_array( $result );

    //User
    $result = $database->query(
        null,
        "   SELECT  *,
                    Emp.fFirst AS First_Name,
                    Emp.Last   AS Last_Name
            FROM    Emp
            WHERE   Emp.ID = ?;",
        array(
            $_SESSION[ 'User' ]
        )
    );
    $User = sqlsrv_fetch_array( $result );

    //Privileges
	$result = $database->query(
        null,
        "   SELECT  *
            FROM    Privilege
            WHERE   Privilege.User_ID = ?;",
        array(
            $_SESSION[ 'User' ]
        )
    );
	$Privileges = array();
	if( $result ){while( $Privilege = sqlsrv_fetch_array( $result ) ){ $Privileges[ $Privilege[ 'Access_Table' ] ] = $Privilege; } }
    if(	!isset( $Connection[ 'ID' ] )
	   	|| !isset($Privileges[ 'Admin' ])
	  		|| $Privileges[ 'Admin' ][ 'User_Privilege' ]  < 4
	  		|| $Privileges[ 'Admin' ][ 'Group_Privilege' ] < 4
        || $Privileges[ 'Admin' ][ 'Other_Privilege' ] < 4){
				?><?php require( '../404.html' );?><?php }
    else {
if(     count( $_POST ) > 0
    &&  is_numeric( $_POST[ 'Severity' ] )
    &&  strlen( $_POST[ 'Name' ] ) > 0
    && strlen( $_POST[ 'Description' ] ) > 0
) {
  $Name        = $_POST[ 'Name' ];
  $Severity    = $_POST[ 'Severity' ];
  $Description = $_POST[ 'Description' ];
  $Suggestion  = $_POST[ 'Suggestion' ];
  $Parameters  = array(
    $Name,
    $Severity,
    $Description,
    $Suggestion
  );
  $result = $database->query(
    $Portal,
    " INSERT INTO Bug( Name, Severity, Description, Suggestion )
      VALUES( ?, ?, ?, ? );",
    $Parameters
  );
}?><!DOCTYPE html>
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