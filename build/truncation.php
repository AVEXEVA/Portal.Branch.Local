<?php
//Statics
$db   = 'Portal';
$sql  = "TRUNCATE TABLE ?;";
$tables = array(
  'Connection',
  'Privilege',
  'User'
);
\singleton\database::getInstance()->query( $db, "TRUNCATE TABLE [Connection];" );
\singleton\database::getInstance()->query( $db, "TRUNCATE TABLE [Privilege];" );
\singleton\database::getInstance()->query( $db, "TRUNCATE TABLE [User];" );
?>
