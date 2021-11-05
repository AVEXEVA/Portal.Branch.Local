<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/cgi-bin/php/index.php' );
}
if( isset( $_SESSION[ 'User' ], $_SESSION[ 'Hash' ] ) ){
    $result = sqlsrv_query(
        $NEI,
        "   SELECT  *
            FROM    Connection
            WHERE       Connection.Connector = ?
                    AND Connection.Hash  = ?;",
        array(
            $_SESSION[ 'User' ],
            $_SESSION[ 'Hash' ]
        )
    );
    $Connection = sqlsrv_fetch_array( $result );
    //User
    $result = sqlsrv_query(
        $NEI,
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
    $result = sqlsrv_query(
        $NEI,
        "   SELECT  *
            FROM    Privilege
            WHERE   Privilege.User_ID = ?;",
        array(
            $_SESSION['User']
        )
    );
    $Privileges = array();
    if( $result ){while( $Privilege = sqlsrv_fetch_array( $result ) ){ $Privileges[ $Privilege[ 'Access_Table' ] ] = $Privilege; } }
    if( !isset( $Connection[ 'ID' ] )
        || !isset($Privileges[ 'Admin' ])
            || $Privileges[ 'Admin' ][ 'User_Privilege' ]  < 4
            || $Privileges[ 'Admin' ][ 'Group_Privilege' ] < 4
        || $Privileges[ 'Admin' ][ 'Other_Privilege' ] < 4){
                ?><?php require( '../404.html' );?><?php }
    else {
        sqlsrv_query(
          $NEI,
          "   INSERT INTO Activity([User], [Date], [Page])
              VALUES( ?, ?, ? );",
          array(
              $_SESSION['User'],
              date( 'Y-m-d H:i:s' ),
              'birthdays.php'
          )
      );
?><!DOCTYPE html>
<html lang='en'>
<head>
    <title>Nouveau Texas | Portal</title>
    <?php require( bin_meta . 'index.php' );?>
    <?php require( bin_css  . 'index.php' );?>
    <?php require( bin_js   . 'index.php' );?>
</head>
<body onload='finishLoadingPage();'>
    <div id='wrapper'>
        <?php require( bin_php . 'element/navigation/index.php'); ?>
        <?php require( bin_php . 'element/loading.php'); ?>
        <div id='page-wrapper' class='content'>
			<div class='panel panel-primary'>
				<div class='panel-heading'><h4><?php $Icons->Birthday( 1 ) ;?> Birthdays</h4></div>
				<div class='panel-body'>
					<table id='Table_Birthdays' class='display' cellspacing='0' width='100%'>
						<thead>
							<th>Name</th>
							<th>Birthday</th>
						</thead>
					</table>
				</div>
			</div>
        </div>
    </div>
    <script src='https://www.nouveauelevator.com/vendor/bootstrap/js/bootstrap.min.js'></script>
    <script src='https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js'></script>
    <?php require( bin_js . 'datatables.php' ); ?>
    <script>
        var Table_Birthdays = $('#Table_Birthdays').DataTable( {
            dom : 'tp',
            ajax : {
                url : 'cgi-bin/php/reports/Birthdays.php',
                dataSrc : function( json ){
                    if( !json.data ){ json.data = [ ]; }
                    return json.data;
                }
            },
            columns : [
                { 
                    data : 'Name' 
                },{ 
                    data : 'Birthday',
				    render: function( data ){
                        if( data ){ return data.substr(5,2) + '/' + data.substr(8,2) + '/' + data.substr(0,4); } 
                        else { return null; }
                    }
				}
            ]
        } );
    </script>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=birthdays.php';</script></head></html><?php }?>
