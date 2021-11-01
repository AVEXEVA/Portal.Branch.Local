<?php 
session_start();
require('index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    $r = sqlsrv_query($Portal,"
        SELECT User_Privilege, Group_Privilege, Other_Privilege
        FROM   Portal.dbo.Privilege
        WHERE User_ID = ? AND Access_Table='Job'
    ;",array($_SESSION['User']));
    $My_Privileges = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    if(!isset($array['ID']) || !is_array($My_Privileges)){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
        $data = array();
        if($My_Privileges['User_Privilege'] >= 4 && $My_Privileges['Group_Privilege'] >= 4 && $My_Privileges['Other_Privilege'] >= 4){
            $r = sqlsrv_query($NEI,"
				SELECT Paid.fDesc   AS fDesc,
					   Trans.Amount AS Paid_Amount,
					   Trans.fDate  AS Paid_Date,
					   Trans.ID     AS ID,
					   Rol.Name     AS Vendor
				FROM   nei.dbo.Paid
					   LEFT JOIN nei.dbo.Trans  ON Paid.TRID   = Trans.ID
					   LEFT JOIN nei.dbo.PO     ON Trans.fDesc = PO.fDesc AND Paid.fDate = PO.fDate AND Paid.Original = PO.Amount
					   LEFT JOIN nei.dbo.POItem ON PO.PO   = POItem.PO
					   LEFT JOIN nei.dbo.Job    ON POItem.Job  = Job.ID
					   LEFT JOIN nei.dbo.Vendor ON PO.Vendor = Vendor.ID
					   LEFT JOIN nei.dbo.Rol    ON Vendor.Rol = Rol.ID
				WHERE  Job.Owner       =  2126
					   AND Job.Loc     <> 5862
					   AND Job.Loc     <> 5863
					   AND Job.Loc     <> 5864
					   AND POItem.Line =  1
				ORDER BY Trans.fDate DESC
			;");
			if( ($errors = sqlsrv_errors() ) != null) {
        foreach( $errors as $error ) {
            echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
            echo "code: ".$error[ 'code']."<br />";
            echo "message: ".$error[ 'message']."<br />";
        }
    }
			if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){
				$data[] = $array;
			}}
        }
        print json_encode(array('data'=>$data));
    }
}?>