<?php 
session_start( [ 'read_and_close' => true ] );
require('index.php');
setlocale(LC_MONETARY, 'en_US');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = $database->query(null,"
        SELECT * 
        FROM   Connection 
        WHERE  Connection.Connector = ? 
               AND Connection.Hash = ?
    ;", array($_SESSION['User'],$_SESSION['Hash']));
    $Connection = sqlsrv_fetch_array($r);
    $My_User    = $database->query(null,"
        SELECT Emp.*, 
               Emp.fFirst AS First_Name, 
               Emp.Last   AS Last_Name 
        FROM   Emp
        WHERE  Emp.ID = ?
    ;", array($_SESSION['User']));
    $My_User = sqlsrv_fetch_array($My_User); 
    $My_Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
    $r = $database->query($Portal,"
        SELECT Privilege.Access, 
               Privilege.Owner, 
               Privilege.Group, 
               Privilege.Other
        FROM   Privilege
        WHERE  Privilege.User_ID = ?
    ;",array($_SESSION['User']));
    $My_Privileges = array();
    while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access']] = $array2;}
    $Privileged = False;
    if( isset($My_Privileges['Invoice'],$My_Privileges['Customer']) 
        && $My_Privileges['Invoice']['Other'] >= 4
	    && $My_Privileges['Supervisor']['Other'] >= 4){
            $Privileged = True;}
    if(!isset($Connection['ID'])  || !is_numeric($_GET['ID']) || !$Privileged){print json_encode(array('data'=>array()));}
    else {
        $r2 = $database->query(null,"
            SELECT Terr.Name AS Territory,
				   Sum(OpenAR.Balance) AS Total_Past_Due
            FROM   nei.dbo.OpenAR
                   LEFT JOIN nei.dbo.Invoice ON OpenAR.Ref  = Invoice.Ref
				   LEFT JOIN nei.dbo.Job     ON Invoice.Job = Job.ID
				   LEFT JOIN nei.dbo.JobType ON JobType.ID  = Job.Type
				   LEFT JOIN nei.dbo.Loc     ON OpenAR.Loc  = Loc.Loc
				   LEFT JOIN nei.dbo.Terr    ON Loc.Terr    = Terr.ID
			GROUP BY Terr.Name
        ;");
        $data = array();
        if($r){while($array = sqlsrv_fetch_array($r2,SQLSRV_FETCH_ASSOC)){
			$Territory = $array;
			$r = $database->query(null,"
				SELECT Sum(OpenAR.Balance) AS Maintenance_Past_Due 
				FROM   nei.dbo.OpenAR
					   LEFT JOIN nei.dbo.Invoice ON OpenAR.Ref  = Invoice.Ref
					   LEFT JOIN nei.dbo.Job     ON Invoice.Job = Job.ID
					   LEFT JOIN nei.dbo.JobType ON JobType.ID  = Job.Type
					   LEFT JOIN nei.dbo.Loc     ON OpenAR.Loc  = Loc.Loc
					   LEFT JOIN nei.dbo.Terr    ON Loc.Terr    = Terr.ID
				WHERE  Terr.Name = ?
				       AND JobType.Type = 'Maintenance'
			;",array($Territory['Territory']));
			$Territory = array_merge($Territory,sqlsrv_fetch_array($r));
			$r = $database->query(null,"
				SELECT Sum(OpenAR.Balance) AS Modernization_Past_Due 
				FROM   nei.dbo.OpenAR
					   LEFT JOIN nei.dbo.Invoice ON OpenAR.Ref  = Invoice.Ref
					   LEFT JOIN nei.dbo.Job     ON Invoice.Job = Job.ID
					   LEFT JOIN nei.dbo.JobType ON JobType.ID  = Job.Type
					   LEFT JOIN nei.dbo.Loc     ON OpenAR.Loc  = Loc.Loc
					   LEFT JOIN nei.dbo.Terr    ON Loc.Terr    = Terr.ID
				WHERE  Terr.Name = ?
				       AND JobType.Type = 'Modernization'
					   AND Job.Custom18 NOT LIKE '%loan%'
			;",array($Territory['Territory']));
			$Territory = array_merge($Territory,sqlsrv_fetch_array($r));
			$r = $database->query(null,"
				SELECT Sum(OpenAR.Balance) AS Repair_Past_Due 
				FROM   nei.dbo.OpenAR
					   LEFT JOIN nei.dbo.Invoice ON OpenAR.Ref  = Invoice.Ref
					   LEFT JOIN nei.dbo.Job     ON Invoice.Job = Job.ID
					   LEFT JOIN nei.dbo.JobType ON JobType.ID  = Job.Type
					   LEFT JOIN nei.dbo.Loc     ON OpenAR.Loc  = Loc.Loc
					   LEFT JOIN nei.dbo.Terr    ON Loc.Terr    = Terr.ID
				WHERE  Terr.Name = ?
				       AND JobType.Type = 'NEW REPAIR'
			;",array($Territory['Territory']));
			$Territory = array_merge($Territory,sqlsrv_fetch_array($r));
			$r = $database->query(null,"
				SELECT Sum(OpenAR.Balance) AS Testing_Past_Due 
				FROM   nei.dbo.OpenAR
					   LEFT JOIN nei.dbo.Invoice ON OpenAR.Ref  = Invoice.Ref
					   LEFT JOIN nei.dbo.Job     ON Invoice.Job = Job.ID
					   LEFT JOIN nei.dbo.JobType ON JobType.ID  = Job.Type
					   LEFT JOIN nei.dbo.Loc     ON OpenAR.Loc  = Loc.Loc
					   LEFT JOIN nei.dbo.Terr    ON Loc.Terr    = Terr.ID
				WHERE  Terr.Name = ?
				       AND JobType.Type = 'Annual'
			;",array($Territory['Territory']));
			$Territory = array_merge($Territory,sqlsrv_fetch_array($r));
			$r = $database->query(null,"
				SELECT Sum(OpenAR.Balance) AS Violations_Past_Due 
				FROM   nei.dbo.OpenAR
					   LEFT JOIN nei.dbo.Invoice ON OpenAR.Ref  = Invoice.Ref
					   LEFT JOIN nei.dbo.Job     ON Invoice.Job = Job.ID
					   LEFT JOIN nei.dbo.JobType ON JobType.ID  = Job.Type
					   LEFT JOIN nei.dbo.Loc     ON OpenAR.Loc  = Loc.Loc
					   LEFT JOIN nei.dbo.Terr    ON Loc.Terr    = Terr.ID
				WHERE  Terr.Name = ?
				       AND JobType.Type = 'Violations'
			;",array($Territory['Territory']));
			
			//XCalls
			$Territory = array_merge($Territory,sqlsrv_fetch_array($r));
			$r = $database->query(null,"
				SELECT Sum(OpenAR.Balance) AS XCALL_Past_Due 
				FROM   nei.dbo.OpenAR
					   LEFT JOIN nei.dbo.Invoice ON OpenAR.Ref  = Invoice.Ref
					   LEFT JOIN nei.dbo.Job     ON Invoice.Job = Job.ID
					   LEFT JOIN nei.dbo.JobType ON JobType.ID  = Job.Type
					   LEFT JOIN nei.dbo.Loc     ON OpenAR.Loc  = Loc.Loc
					   LEFT JOIN nei.dbo.Terr    ON Loc.Terr    = Terr.ID
				WHERE  Terr.Name = ?
				       AND JobType.Type = 'XCALL'
			;",array($Territory['Territory']));
			$Territory = array_merge($Territory,sqlsrv_fetch_array($r));
			
			//Lawsuits
			if($Territory['Territory'] == 'Robert Speranza'){$Terr = 'RMS';}
			elseif($Territory['Territory'] == 'Donald Speranza'){$Terr = 'DJ';}
			elseif($Territory['Territory'] == 'Dean Speranza'){$Terr = 'DWS';}
			elseif($Territory['Territory'] == 'Steven Speranza'){$Terr = 'SS';}
			elseif($Territory['Territory'] == 'Frank Canale'){$Terr = 'FC';}
			elseif($Territory['Territory'] == 'Michael Hannan'){$Terr = 'MH';}
			elseif($Territory['Territory'] == 'George Ziugzda'){$Terr = 'GWZ';}
			else{$Terr = 'ASDF';}
			$r = $database->query(null,"
				SELECT Sum(OpenAR.Balance) AS Lawsuits_Past_Due 
				FROM   nei.dbo.OpenAR
					   LEFT JOIN nei.dbo.Invoice ON OpenAR.Ref  = Invoice.Ref
					   LEFT JOIN nei.dbo.Job     ON Invoice.Job = Job.ID
					   LEFT JOIN nei.dbo.JobType ON JobType.ID  = Job.Type
					   LEFT JOIN nei.dbo.Loc     ON OpenAR.Loc  = Loc.Loc
					   LEFT JOIN nei.dbo.Terr    ON Loc.Terr    = Terr.ID
				WHERE  (Terr.Name = ? AND JobType.Type = 'LAWSUITS')
					   OR (JobType.Type = 'LAWSUITS' AND Job.Custom8 = ?)
			;",array($Territory['Territory'],$Terr));
			$Territory = array_merge($Territory,sqlsrv_fetch_array($r));
			
			//Other
			$r = $database->query(null,"
				SELECT Sum(OpenAR.Balance) AS Other_Past_Due 
				FROM   nei.dbo.OpenAR
					   LEFT JOIN nei.dbo.Invoice ON OpenAR.Ref  = Invoice.Ref
					   LEFT JOIN nei.dbo.Job     ON Invoice.Job = Job.ID
					   LEFT JOIN nei.dbo.JobType ON JobType.ID  = Job.Type
					   LEFT JOIN nei.dbo.Loc     ON OpenAR.Loc  = Loc.Loc
					   LEFT JOIN nei.dbo.Terr    ON Loc.Terr    = Terr.ID
				WHERE  Terr.Name = ?
				       AND JobType.Type = 'Other'
			;",array($Territory['Territory']));
			
			//Billing Only
			$Territory = array_merge($Territory,sqlsrv_fetch_array($r));
			$r = $database->query(null,"
				SELECT Sum(OpenAR.Balance) AS Billing_Only_Past_Due 
				FROM   nei.dbo.OpenAR
					   LEFT JOIN nei.dbo.Invoice ON OpenAR.Ref  = Invoice.Ref
					   LEFT JOIN nei.dbo.Job     ON Invoice.Job = Job.ID
					   LEFT JOIN nei.dbo.JobType ON JobType.ID  = Job.Type
					   LEFT JOIN nei.dbo.Loc     ON OpenAR.Loc  = Loc.Loc
					   LEFT JOIN nei.dbo.Terr    ON Loc.Terr    = Terr.ID
				WHERE  Terr.Name = ?
				       AND JobType.Type = 'BILLING ONLY'
			;",array($Territory['Territory']));
			$Territory = array_merge($Territory,sqlsrv_fetch_array($r));
			
			//Loans
			$r = $database->query(null,"
				SELECT Sum(OpenAR.Balance) AS Loans_Past_Due 
				FROM   nei.dbo.OpenAR
					   LEFT JOIN nei.dbo.Invoice ON OpenAR.Ref  = Invoice.Ref
					   LEFT JOIN nei.dbo.Job     ON Invoice.Job = Job.ID
					   LEFT JOIN nei.dbo.JobType ON JobType.ID  = Job.Type
					   LEFT JOIN nei.dbo.Loc     ON OpenAR.Loc  = Loc.Loc
					   LEFT JOIN nei.dbo.Terr    ON Loc.Terr    = Terr.ID
				WHERE  Terr.Name = ?
				       AND OpenAR.fDesc LIKE '%loan%'
			;",array($Territory['Territory']));
			$Territory = array_merge($Territory,sqlsrv_fetch_array($r));
			
			
			$date = date("Y-m-d",strtotime('-90 days'));
			$r = $database->query(null,"
				SELECT Sum(OpenAR.Balance) AS Ninety_Days_Past_Due 
				FROM   nei.dbo.OpenAR
					   LEFT JOIN nei.dbo.Invoice ON OpenAR.Ref  = Invoice.Ref
					   LEFT JOIN nei.dbo.Job     ON Invoice.Job = Job.ID
					   LEFT JOIN nei.dbo.JobType ON JobType.ID  = Job.Type
					   LEFT JOIN nei.dbo.Loc     ON OpenAR.Loc  = Loc.Loc
					   LEFT JOIN nei.dbo.Terr    ON Loc.Terr    = Terr.ID
				WHERE  Terr.Name = ?
				       AND OpenAR.fDate <= ?
			;",array($Territory['Territory'],$date));
			$Territory = array_merge($Territory,sqlsrv_fetch_array($r));
			
			$date = date("Y-m-d",strtotime('-180 days'));
			$r = $database->query(null,"
				SELECT Sum(OpenAR.Balance) AS One_Eighty_Days_Past_Due 
				FROM   nei.dbo.OpenAR
					   LEFT JOIN nei.dbo.Invoice ON OpenAR.Ref  = Invoice.Ref
					   LEFT JOIN nei.dbo.Job     ON Invoice.Job = Job.ID
					   LEFT JOIN nei.dbo.JobType ON JobType.ID  = Job.Type
					   LEFT JOIN nei.dbo.Loc     ON OpenAR.Loc  = Loc.Loc
					   LEFT JOIN nei.dbo.Terr    ON Loc.Terr    = Terr.ID
				WHERE  Terr.Name = ?
				       AND OpenAR.fDate <= ?
			;",array($Territory['Territory'],$date));
			$Territory = array_merge($Territory,sqlsrv_fetch_array($r));
			
			$date = date("Y-m-d",strtotime('-365 days'));
			$r = $database->query(null,"
				SELECT Sum(OpenAR.Balance) AS Three_Sixty_Five_Days_Past_Due 
				FROM   nei.dbo.OpenAR
					   LEFT JOIN nei.dbo.Invoice ON OpenAR.Ref  = Invoice.Ref
					   LEFT JOIN nei.dbo.Job     ON Invoice.Job = Job.ID
					   LEFT JOIN nei.dbo.JobType ON JobType.ID  = Job.Type
					   LEFT JOIN nei.dbo.Loc     ON OpenAR.Loc  = Loc.Loc
					   LEFT JOIN nei.dbo.Terr    ON Loc.Terr    = Terr.ID
				WHERE  Terr.Name = ?
				       AND OpenAR.fDate <= ?
			;",array($Territory['Territory'],$date));
			$Territory = array_merge($Territory,sqlsrv_fetch_array($r));
			
			$date = date("Y-m-d",strtotime('-730 days'));
			$r = $database->query(null,"
				SELECT Sum(OpenAR.Balance) AS Two_Years_Past_Due 
				FROM   nei.dbo.OpenAR
					   LEFT JOIN nei.dbo.Invoice ON OpenAR.Ref  = Invoice.Ref
					   LEFT JOIN nei.dbo.Job     ON Invoice.Job = Job.ID
					   LEFT JOIN nei.dbo.JobType ON JobType.ID  = Job.Type
					   LEFT JOIN nei.dbo.Loc     ON OpenAR.Loc  = Loc.Loc
					   LEFT JOIN nei.dbo.Terr    ON Loc.Terr    = Terr.ID
				WHERE  Terr.Name = ?
				       AND OpenAR.fDate <= ?
			;",array($Territory['Territory'],$date));
			$Territory = array_merge($Territory,sqlsrv_fetch_array($r));
			
			$date = date("Y-m-d",strtotime('-1095 days'));
			$r = $database->query(null,"
				SELECT Sum(OpenAR.Balance) AS Three_Years_Past_Due 
				FROM   nei.dbo.OpenAR
					   LEFT JOIN nei.dbo.Invoice ON OpenAR.Ref  = Invoice.Ref
					   LEFT JOIN nei.dbo.Job     ON Invoice.Job = Job.ID
					   LEFT JOIN nei.dbo.JobType ON JobType.ID  = Job.Type
					   LEFT JOIN nei.dbo.Loc     ON OpenAR.Loc  = Loc.Loc
					   LEFT JOIN nei.dbo.Terr    ON Loc.Terr    = Terr.ID
				WHERE  Terr.Name        =  ?
				       AND OpenAR.fDate <= ?
			;",array($Territory['Territory'],$date));
			$Territory = array_merge($Territory,sqlsrv_fetch_array($r));
			
			$data[] = $Territory;
		}}
        print json_encode(array('data'=>$data));   
	}
}?>