<?php 
$errorlevel=error_reporting();
error_reporting($errorlevel & ~E_NOTICE);
ini_set('display_errors', 'Off');
ini_set('date.timezone','America/New_York');
setlocale(LC_MONETARY, 'en_US');
?>