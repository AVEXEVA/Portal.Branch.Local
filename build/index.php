<?php
/*********APP**********/
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( __DIR__ . '/../bin/php/index.php' );
}

/*********SCHEMA*******/
//require( 'database.php' );
//require( 'schema.php' );

/*********DATA*********/
require( 'truncation.php' );
require( 'user.php' );
require( 'privileges.php' );
require( 'connection.php' );

/*********REDIRECT*****/
header( 'Location: ../index.php' );
exit;
?>
