<?php
require("cgi-bin/php/index.php");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
//location works
//if(!class_exists("web_page_locations")){require(PROJECT_ROOT."php/classes/web/page/locations.php");}
//new web_page_locations();
if(!class_exists("web_page_units")){require(PROJECT_ROOT."php/classes/web/page/units.php");}
new web_page_units();
?>