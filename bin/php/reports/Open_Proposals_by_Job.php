<?php 
session_start( [ 'read_and_close' => true ] );
require('index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = $database->query(null,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    $r = $database->query(null,"SELECT * FROM nei.dbo.Emp WHERE ID = ?",array($_GET['User']));
    $My_User = sqlsrv_fetch_array($r);
    $Field = ($User['Field'] == 1 && "OFFICE" != $User['Title']) ? True : False;
	$Privileged = False;
    if( isset($My_Privileges['Job']) 
        && $My_Privileges['Job']['Other'] >= 4){
            $Privileged = True;}
    if(!isset($array['ID'],$_GET['ID']) || !is_numeric($_GET['ID']) ){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
        $r = $database->query(null,"
            SELECT Estimate.ID    AS ID,
			       Estimate.fDesc AS Name,
				   Estimate.Name  AS Contact,
			       Estimate.fDate AS Date,
				   Estimate.Price AS Price
			FROM   nei.dbo.Estimate 
				   LEFT JOIN nei.dbo.Loc ON Estimate.LocID = Loc.Loc
			WHERE  Estimate.Job       = ?
			       AND Estimate.Status = 0
		;",array($_GET['ID']));
        $data = array();
        if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}}
        print json_encode(array('data'=>$data));   }
}?>