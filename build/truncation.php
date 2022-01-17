<?php
$db   = 'Portal';
\singleton\database::getInstance()->changeDefault( 'Portal' );
\singleton\database::getInstance()->query( $db, "TRUNCATE TABLE [Connection];" );
\singleton\database::getInstance()->query( $db, "TRUNCATE TABLE [Database];" );
\singleton\database::getInstance()->query( $db, "TRUNCATE TABLE [Privilege];" );
\singleton\database::getInstance()->query( $db, "TRUNCATE TABLE [User];" );
?>
