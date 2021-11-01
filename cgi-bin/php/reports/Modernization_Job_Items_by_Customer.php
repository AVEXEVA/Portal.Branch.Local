<?php 
session_start();
require('index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    $User = sqlsrv_query($NEI,"SELECT * FROM nei.dbo.Emp WHERE ID = ?",array($_GET['User']));
    $User = sqlsrv_fetch_array($User);
    $Field = ($User['Field'] == 1 && $User['Title'] != "OFFICE") ? True : False;
    $r = sqlsrv_query($Portal,"
            SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
            FROM   Portal.dbo.Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
    $My_Privileges = array();
    while($array2 = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$My_Privileges[$array2['Access_Table']] = $array2;}
    $Privileged = FALSE;
    if(isset($My_Privileges['Job']) && $My_Privileges['Job']['User_Privilege'] >= 4 && $My_Privileges['Invoice']['Group_Privilege'] >= 4 && $My_Privileges['Job']['Other_Privilege'] >= 4){$Privileged = TRUE;}
    elseif(isset($_GET['ID']) && $My_Privileges['Job']['Group_Privilege'] >= 4 && is_numeric($_GET['ID'])){
        //ADD SECURITY
    }
    if(!isset($array['ID'],$_GET['ID']) || !is_numeric($_GET['ID']) || !$Privileged){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
    
        $r = sqlsrv_query($NEI,"
            SELECT JobI.fDate   AS Date,
                   JobI.Ref     AS Ref,
                   JobI.fDesc   AS Item,
                   JobI.Amount  AS Amount,
                   JobI.TransID AS Transaction_ID,
                   JobI.Type    AS Type,
                   JobI.Labor   AS Labor,
                   JobI.Billed  AS Billed,
                   JobI.Invoice AS Invoice,
				   Job.fDesc    AS Job
            FROM   nei.dbo.JobI
				   LEFT JOIN nei.dbo.Job ON JobI.Job = Job.ID
            WHERE  Job.Owner = ?
				   AND Job.Type = 2
        ;",array($_GET['ID']));
        $data = array();
        if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}}
        print json_encode(array('data'=>$data));
    }
}?>