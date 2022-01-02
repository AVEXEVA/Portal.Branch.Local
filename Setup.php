<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
$db = 'Portal';
$sql = "INSERT INTO [User] (Email, Password, Verified, Branch, Branch_Type, Branch_ID, Picture, Picture_Type)
VALUES ( ?, ?, ?, ?, ?, ?, ?, ?)";
$parameters = array(
  'admin@avexeva.com',
  '180PagesOfTutorials!',
  1,
  'Development',
  'Admin',
  null,
  array(
    null,
    SQLSRV_PARAM_IN,
    SQLSRV_PHPTYPE_STREAM(SQLSRV_ENC_BINARY),
    SQLSRV_SQLTYPE_VARBINARY('max')
  ),
  null
);
\singleton\database::getInstance()->query(
  $db, $sql, $parameters
);
var_dump(sqlsrv_errors ( ) );
?>
