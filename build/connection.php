<?php
if( session_id( ) == '' || !isset($_SESSION)) { session_destroy( ); }
session_start( );

$db = 'Portal';
$sql = "INSERT INTO [Connection]( [User], [Hash], [Created], [Timestamp], [IP], [Agent] )
        VALUES( ?, ?, ?, ?, ?, ?);
        SELECT SCOPE_IDENTITY( ) AS ID;";
$parameters = array(
  'User' => 1,
  'Hash' => hash( 'sha256', rand( 0, 9999999 ) . date( 'Y-m-d h:i:s' ) ),
  'Created' => date( 'Y-m-d h:i:s' ),
  'Timestamp' => date( 'Y-m-d h:i:s' ),
  'IP' => $_SERVER[ 'REMOTE_ADDR' ],
  'Agent' => $_SERVER[ 'HTTP_USER_AGENT' ]
);
$_SESSION[ 'Connection' ] = $parameters;

$result = \singleton\database::getInstance()->query(
  $db,
  $sql,
  array_values( $_SESSION[ 'Connection' ] )
);
sqlsrv_next_result( $result );
$_SESSION[ 'Connection' ][ 'ID' ] = sqlsrv_fetch_array( $result )[ 'ID' ];
$_SESSION[ 'Connection' ][ 'Branch' ] = 'Setup Mode';
session_write_close( );
?>