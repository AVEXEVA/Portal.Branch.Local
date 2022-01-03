<?php 
define( 'Portal', isset( $_GET[ 'Portal' ] ) ? $_GET[ 'Portal' ] : 'Portal' );
/*********APP**********/
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( __DIR__ . '/bin/php/index.php' );
}
require( 'build/index.php' );
?>