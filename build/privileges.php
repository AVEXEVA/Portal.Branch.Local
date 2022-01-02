<?php
if( session_id( ) == '' || !isset($_SESSION)) {
  session_start( [ 'read_and_close' => true ] );
  require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
//Statics
$db   = 'Portal';
$sql  = " INSERT INTO [Privilege] ([User], [Access], [Owner], [Group], [Department], [Database], [Server], [Other], [Token], [Internet])
          VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$accesses = array(
  'Contact',
  'Collection',
  'Customer',
  'Division',
  'User',
  'Job',
  'Lead',
  'Location',
  'Proposal',
  'Requisition',
  'Route',
  'Ticket',
  'Territory',
  'Code',
  'Unit',
  'Violation',
  'Owner',
  'Group',
  'Department',
  'Database',
  'Server',
  'Other',
  'Token',
  'Internet'
  //etc
);
//Dynamic
foreach( $accesses as $access ){
  $parameters = array(
    1,
    $access,
    15,
    15,
    15,
    15,
    15,
    15,
    15,
    15
  );
  \singleton\database::getInstance()->query(
    $db,
    $sql,
    $parameters
  );
}
var_dump(sqlsrv_errors ( ) );
?>
