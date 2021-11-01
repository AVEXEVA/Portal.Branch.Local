<?php 
session_start();
require('index.php');
set_time_limit ( 120 );
ini_set('memory_limit','1024M');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    $User = sqlsrv_query($NEI,"SELECT * FROM nei.dbo.Emp WHERE ID = ?",array($_GET['User']));
    $User = sqlsrv_fetch_array($User);
    $Field = ($User['Field'] == 1 && "OFFICE" != $User['Title']) ? True : False;
    if(!isset($array['ID'])){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
        $r = sqlsrv_query($NEI,"
            SELECT Job.ID            AS ID,
				   Job.fDesc         AS Name,
				   OwnerWithRol.Name AS Customer,
				   Loc.Tag           AS Location,
				   Estimate.Price    AS Estimate_Price,
				   JobType.Type      AS Type,
                   Job.fDate         AS Date,
                   Job_Status.Status AS Status
            FROM   nei.dbo.Job
                   LEFT JOIN nei.dbo.Loc          ON Job.Loc        = Loc.Loc
				   LEFT JOIN nei.dbo.OwnerWithRol ON Job.Owner      = OwnerWithRol.ID
				   LEFT JOIN nei.dbo.Estimate     ON Estimate.Job   = Job.ID
				   LEFT JOIN nei.dbo.JobType      ON Job.Type       = JobType.ID
                   LEFT JOIN nei.dbo.Job_Status   ON Job.Status + 1 = Job_Status.ID
			WHERE  Job.Type       <>       12
				   AND Job.Type   <>       9
				   AND Job.Type   <>       0
				   AND Job.Type   <>       2
				   AND Job.Type   <>       20
				   AND Job.Status <>       1
				   AND Job.Status <>       2
				   AND Job.Status <>       3
				   AND Job.fDesc  NOT LIKE '%DO NOT USE%'
        ;");
        $data = array();
		$Job = 0;
		$stmt = sqlsrv_prepare($NEI,"
			SELECT Sum(Invoice.Total) AS Invoice_Price,
				   Count(Invoice.Ref)  AS Invoices
			FROM   nei.dbo.Invoice
			WHERE  Invoice.Job = ?
		;",array(&$Job));
        if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){
			$Job = $array['ID'];
			if(sqlsrv_execute($stmt)){
				$array2 = sqlsrv_fetch_array($stmt,SQLSRV_FETCH_ASSOC);
				if(is_array($array2) && count($array2) > 0){
					if(intval($array2['Invoice_Price']) >= intval($array['Estimate_Price']) && intval($array2['Invoices']) > 0){
						$array['Billed_Amount'] = intval($array2['Invoice_Price']);
						$array['Contract_Amount'] = intval($array['Estimate_Price']);
						$data[] = $array;
					}
				}
			} else {
				continue;
			}
			
		}}
        print json_encode(array('data'=>$data));   }
}?>