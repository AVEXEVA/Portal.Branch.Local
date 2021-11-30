<?php
//~By Peter D. Speranza
$serverName = "172.16.12.44";
$ConnectionOptions = array(
    "Database" => "Demo",
    "Uid" => "sa",
    "PWD" => "SQLABC!23456",
    'ReturnDatesAsStrings'=>true
);
$demo = sqlsrv_connect($serverName, $ConnectionOptions);
?>
