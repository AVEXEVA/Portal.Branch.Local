<?php
$db   = 'Development';
$sql  = file_get_contents( 'architecture.sql' );
\singleton\database::getInstance()->query( $db, $sql );
?>
