<?php
$start = $_GET['start'];
$end = $_GET['end'];
$hours = substr($end,11,2) - substr($start,11,2);
$minutes = substr($end,13,2) - substr($start,13,2);
echo ($hours * 1) + ($minutes / 60);
?>
