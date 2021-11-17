<?php 
session_start( [ 'read_and_close' => true ] );
require('index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = $database->query(null,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    $r = $database->query($Portal,"
        SELECT Owner, Group, Other
        FROM   Portal.dbo.Privilege
        WHERE User_ID = ? AND Access='Job'
    ;",array($_SESSION['User']));
    $My_Privileges = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    if(!isset($array['ID']) || !is_array($My_Privileges)){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
        $r = $database->query(null,"
            SELECT JobI.fDate  AS Date,
                   JobI.Ref    AS ID,
                   JobI.fDesc  AS Description,
                   JobI.Amount AS Amount,
                   Job.fDesc   AS Job,
                   Loc.Tag     AS Location
            FROM   nei.dbo.JobI
                   LEFT JOIN nei.dbo.Job ON JobI.Job = Job.ID
                   LEFT JOIN nei.dbo.Loc ON Job.Loc  = Loc.Loc
            WHERE  (JobI.Labor <> 1
                    OR JobI.Labor = ''
                    OR JobI.Labor = 0
                    OR JobI.Labor = ' '
                    OR JobI.Labor IS NULL)
                   AND JobI.Type = 1
        ;");
        $data = array();
        if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}}
        print json_encode(array('data'=>$data));
    }
}