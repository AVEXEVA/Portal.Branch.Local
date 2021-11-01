<?php 
session_start();
require('index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    $User = sqlsrv_query($NEI,"SELECT * FROM Emp WHERE ID = ?",array($_GET['User']));
    $User = sqlsrv_fetch_array($User);
    $Field = ($User['Field'] == 1 && $User['Title'] != "OFFICE") ? True : False;
    if(!isset($array['ID'])  || !is_numeric($_GET['ID'])){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
        $r = sqlsrv_query($NEI,"
            SELECT Estimate.ID    AS  ID,
                   Estimate.Name  AS  Contact,
                   Loc.Tag        AS  Location,
                   Estimate.fDesc AS  Title,
                   Estimate.fDate AS  fDate,
                   Estimate.Cost  AS  Cost,
                   Estimate.Price AS  Price
            FROM   nei.dbo.Estimate
                   LEFT JOIN nei.dbo.Loc ON Estimate.LocID = Loc.Loc
            WHERE  Loc.Owner = ?
		;",array($_GET['ID']));
        $data = array();
        if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}}
        print json_encode(array('data'=>$data));   }
}?>