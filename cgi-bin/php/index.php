<?php
$errorlevel=error_reporting();
error_reporting($errorlevel & ~E_NOTICE);
ini_set('display_errors', 'Off');
define('PROJECT_ROOT',__DIR__ . '/../');
ini_set('date.timezone','America/Chicago');
setlocale(LC_MONETARY, 'en_US');
require(PROJECT_ROOT.'php/functions.php');
require(PROJECT_ROOT.'php/Icons.php');
require(PROJECT_ROOT.'php/Connections.php');
$Icons = new Icons();
$Mobile = isMobile();
?>
