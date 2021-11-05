<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( [
        'read_and_close' => true
    ] ); 
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/cgi-bin/php/index.php' );
}
if( isset( $_SESSION[ 'User' ], $_SESSION[ 'Hash' ] ) ){
    $result = $database->query(
        null,
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
            $_SESSION['User']
        )
    );
    $Privileges = array();
    if( $result ){while( $Privilege = sqlsrv_fetch_array( $result ) ){ $Privileges[ $Privilege[ 'Access_Table' ] ] = $Privilege; } }
    if( !isset( $Connection[ 'ID' ] )
        || !isset($Privileges[ 'Ticket' ])
            || $Privileges[ 'Ticket' ][ 'User_Privilege' ]  < 4
            || $Privileges[ 'Ticket' ][ 'Group_Privilege' ] < 4
            || $Privileges[ 'Ticket' ][ 'Other_Privilege' ] < 4
    ){      
        ?><?php require( '../404.html' );?><?php 
    } else {
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Peter D. Speranza">
    <title>Nouveau Elevator Portal</title>
    <?php $_GET[ 'Bootstrap' ] = '5.1';?>
    <?php require( bin_css . 'index.php');?>
    <?php require( bin_js . 'index.php');?>
</head>
<body onload='finishLoadingPage();'>
<?php 
$_GET[ 'Tickets' ] = isset( $_GET[ 'Tickets' ] ) ? explode( ',', $_GET[ 'Tickets' ] ) : array( );
if( isset( $_GET[ 'Tickets' ] ) && is_array( $_GET[ 'Tickets' ] ) && count( $_GET[ 'Tickets' ] ) > 0){ foreach( $_GET[ 'Tickets' ] as $Ticket_ID ){
  if( is_numeric( $Ticket_ID ) && $Ticket_ID > 0 ){
    $_GET[ 'ID' ] = $Ticket_ID;
    require( 'short-ticket.php' );  
  }
} }?>          
</body>
</html>
<?php
    }
}?>
