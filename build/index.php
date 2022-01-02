<?php
/*********SCHEMA*******/
//require( 'database.php' );
//require( 'tables.php' );

/*********DATA*********/
require( 'truncation.php' );
require( 'user.php' );
require( 'privileges.php' );
require( 'connection.php' );

/*********REDIRECT*****/
header( 'Location: index.php' );
exit;
?>