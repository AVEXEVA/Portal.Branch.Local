<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( ); 
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/bin/php/index.php' );
}
if(     !isset( $_SESSION[ 'User' ], $_SESSION[ 'Connection' ] )
    ||  !connection( 'Demo', \singleton\database::getInstance( ), $_SESSION[ 'Connection' ][ 'Branch_ID' ], $_SESSION[ 'Connection' ][ 'Hash' ] ) ){
        header( 'Location: https://beta.nouveauelevator.com/login.php' );
        exit; }

$_SESSION[ 'Cards' ] = isset( $_SESSION[ 'Cards' ] ) ? $_SESSION[ 'Cards' ] : array( );

if( isset( $_POST[ 'Card' ] ) ){
	$_SESSION[ 'Cards' ][ $_POST[ 'Card' ] ] = isset( $_SESSION[ 'Cards' ][ $_POST[ 'Card' ] ] ) 
		? !$_SESSION[ 'Cards' ][ $_POST[ 'Card' ] ] 
		: 0;
};?>