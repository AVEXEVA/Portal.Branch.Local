<?php
$db   = 'Portal';
$sql  = "CREATE DATABASE [Portal]];";
\singleton\database::getInstance()->query( $db, $sql );
?>
