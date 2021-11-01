<?php
//~By Peter D. Speranza
$serverName = "172.16.12.45";
$ConnectionOptions = array(
    "Database" => "Paradox",
    "Uid" => "sa",
    "PWD" => "SQLABC!23456",
    'ReturnDatesAsStrings'=>true
);
$Paradox = sqlsrv_connect($serverName, $ConnectionOptions);
?>
