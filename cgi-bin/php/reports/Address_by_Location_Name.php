<?php 
session_start();
require('index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    $User = sqlsrv_query($NEI,"SELECT * FROM nei.dbo.Emp WHERE ID = ?",array($_GET['User']));
    $User = sqlsrv_fetch_array($User);
    $Field = ($User['Field'] == 1 && $User['Title'] != "OFFICE") ? True : False;
    if(!isset($array['ID'])  || !is_numeric($_GET['ID'])){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
		$data = array();
        $r = sqlsrv_query($NEI,"
            SELECT Loc.Loc      AS ID,
                   Loc.ID       AS Name,
                   Loc.Tag      AS Tag,
                   Loc.Address  AS Street,
                   Loc.City     AS City,
                   Loc.State    AS State,
                   Loc.Zip      AS Zip,
                   Route.Name   AS Route,
                   Zone.Name    AS Division,
                   Loc.Maint    AS Maintenance,
				   Terr.Name    AS Territory,
				   Loc.fLong    AS Longitude,
				   Loc.Latt     AS Latitude
				   
            FROM   nei.dbo.Loc
			       LEFT JOIN nei.dbo.Zone ON Zone.ID = Loc.Zone
				   LEFT JOIN nei.dbo.Route ON Route.ID = Loc.Route
				   LEFT JOIN nei.dbo.Terr ON Terr.ID = Loc.Terr
            WHERE  Loc.Tag = ?
        ;",array($_GET['ID']));
        if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}}
        print json_encode(array('data'=>utf8ize($data)));  }
}?>