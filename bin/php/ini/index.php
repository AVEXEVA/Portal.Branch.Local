<?php 
$errorlevel=error_reporting();
error_reporting($errorlevel & ~E_NOTICE);
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('date.timezone','America/New_York');
setlocale(LC_MONETARY, 'en_US');
?>