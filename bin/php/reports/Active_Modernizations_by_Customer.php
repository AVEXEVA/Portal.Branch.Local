<?php 
session_start( [ 'read_and_close' => true ] );
require('index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = $database->query(null,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    $User = $database->query(null,"SELECT * FROM nei.dbo.Emp WHERE ID = ?",array($_GET['User']));
    $User = sqlsrv_fetch_array($User);
    $Field = ($User['Field'] == 1 && "OFFICE" != $User['Title']) ? True : False;
    if(!isset($array['ID'],$_GET['ID']) || !is_numeric($_GET['ID']) ){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
        $r = $database->query(null,"
            SELECT Job.ID            AS ID,
				   Job.fDesc         AS Name,
				   Job.fDate         AS Date,
				   Job_Status.Status AS Status
			FROM   nei.dbo.Job 
				   LEFT JOIN nei.dbo.JobType    ON Job.Type       = JobType.ID
				   LEFT JOIN nei.dbo.Job_Status ON Job.Status + 1 = Job_Status.ID
			WHERE  Job.Owner  = ?
				   AND Job.Status = 0
				   AND Job.Type   = 2
		;",array($_GET['ID']));
		if( ($errors = sqlsrv_errors() ) != null) {
        foreach( $errors as $error ) {
            echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
            echo "code: ".$error[ 'code']."<br />";
            echo "message: ".$error[ 'message']."<br />";
        }
    }
        $data = array();
        if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}}
        print json_encode(array('data'=>$data));   }
}?>