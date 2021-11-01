<?php 
session_start();
require('index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    $User = sqlsrv_query($NEI,"SELECT * FROM nei.dbo.Emp WHERE ID = ?",array($_GET['User']));
    $User = sqlsrv_fetch_array($User);
    $Field = ($User['Field'] == 1 && $User['Title'] != "OFFICE") ? True : False;
    if(!isset($array['ID'],$_GET['State']) || strlen($_GET['State']) == 0){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
		$r = sqlsrv_query($NEI,"
			SELECT Elev.ID     AS ID,
				   Elev.State  AS State, 
				   Elev.Unit   AS Label,
				   Elev.Type   AS Type,
				   Loc.Tag     AS Location,
				   Elev.Status AS Status,
				   Elev.fDesc  AS Description
			FROM   nei.dbo.Elev
				   LEFT JOIN nei.dbo.Loc ON Loc.Loc = Elev.Loc
			WHERE  Elev.State = ?
		;",array($_GET['State']));
		$data = array();
		if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){
			$Unit = $array;
			$r2 = sqlsrv_query($NEI,"
				SELECT *
				FROM   nei.dbo.ElevTItem
				WHERE  ElevTItem.ElevT    = 1
					   AND ElevTItem.Elev = ?
			;",array($Unit['ID']));
			if($r2){while($array2 = sqlsrv_fetch_array($r2,SQLSRV_FETCH_ASSOC)){$Unit[$array2['fDesc']] = $array2['Value'];}}
			$r3 = sqlsrv_query($NEI,"
				SELECT *
				FROM   nei.dbo.ElevTItem
				WHERE  ElevTItem.ElevT    = 1
					   AND ElevTItem.Elev = ?
			;",array(0));
			if($r3){while($array3 = sqlsrv_fetch_array($r3,SQLSRV_FETCH_ASSOC)){if(!isset($Unit[$array3['fDesc']])){$Unit[$array3['fDesc']] = '';}}}
			$data[] = $Unit;
		}}
		print json_encode(array('data'=>$data));   
	}
}