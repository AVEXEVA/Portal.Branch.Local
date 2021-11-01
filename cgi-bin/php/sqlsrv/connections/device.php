<?php
//~By Peter D. Speranza
$serverName = "172.16.12.44";
$ConnectionOptions = array(
    "Database" => "Device",
    "Uid" => "sa",
    "PWD" => "SQLABC!23456",
    'ReturnDatesAsStrings'=>true
);
$device = sqlsrv_connect($serverName, $ConnectionOptions);
?>
