<?php 
session_start( [ 'read_and_close' => true ] );
require('index.php');
setlocale(LC_MONETARY, 'en_US');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = $database->query(null,"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Elevator'){
        $My_User = $database->query(null,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID = ?",array($_SESSION['User']));
        $My_User = sqlsrv_fetch_array($My_User); 
        $Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
        $r = $database->query($Portal,"
            SELECT Access, Owner, Group, Other
            FROM   Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
        $My_Privileges = array();
        while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access']] = $array2;}
        $Privileged = FALSE;
        if(isset($My_Privileges['Territory']) && $My_Privileges['Territory']['Owner'] >= 4 && $My_Privileges['Territory']['Group'] >= 4 && $My_Privileges['Territory']['Other'] >= 4){$Privileged = TRUE;}
    }
    if(!isset($array['ID'])  || !$Privileged || !is_numeric($_GET['ID'])){?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {
		$r = $database->query(null,"
			SELECT Customer.*, 
				   Customer.Customer AS Name,
				   Customer.Customer_ID AS ID,
				   Customer.Profit      AS Profit,
				CASE WHEN Customer.Revenue = 0 
						THEN 0 
						ELSE 
							CASE WHEN Customer.Profit < 0 AND Customer.Revenue < 0 
								THEN ((Customer.Profit / Customer.Revenue) * -1)
								ELSE (Customer.Profit / Customer.Revenue)
							END
					END * 100 AS Profit_Percentage
			FROM (SELECT  *,
				    Customer.Revenue - (Customer.Labor + Customer.Material) AS Profit
				FROM (SELECT Customer.Name AS Customer,
							 Customer.ID   AS Customer_ID,
					   CASE WHEN Customer_Revenue.Revenue IS NULL 
							THEN 0 
							ELSE Customer_Revenue.Revenue END 
							AS Revenue,
					   CASE WHEN Customer_Material.Material IS NULL 
							THEN 0 
							ELSE Customer_Material.Material END 
							AS Material,
					   CASE WHEN Customer_Paradox_Labor.Paradox_Labor IS NULL 
							THEN CASE WHEN Customer_TS_Labor.TS_Labor  IS NULL
								THEN 0
								ELSE Customer_TS_Labor.TS_Labor END
							ELSE CASE WHEN Customer_TS_Labor.TS_Labor IS NULL 
								THEN Customer_Paradox_Labor.Paradox_Labor 
								ELSE Customer_Paradox_Labor.Paradox_Labor + Customer_TS_Labor.TS_Labor END END AS Labor
				FROM   nei.dbo.OwnerWithRol AS Customer
					   LEFT JOIN (SELECT Customer.ID AS ID,
										 Sum(Amount) AS Revenue
								  FROM   nei.dbo.OwnerWithRol      AS Customer
										 LEFT JOIN nei.dbo.Loc     AS Location ON Customer.ID = Location.Owner
										 LEFT JOIN nei.dbo.Invoice AS Invoice  ON Invoice.Loc = Location.Loc
								  WHERE  Invoice.fDate >= '2017-01-01 00:00:00.000'
								         AND Invoice.fDate < '2018-01-01 00:00:00.000'
										 AND Location.Terr = ?
								  GROUP BY Customer.ID
								 ) AS Customer_Revenue ON Customer_Revenue.ID = Customer.ID
					   LEFT JOIN (SELECT Customer.ID                  AS ID,
										 Sum(Job_Labor.[TOTAL COST])  AS Paradox_Labor
								  FROM   nei.dbo.OwnerWithRol           AS Customer
										 LEFT JOIN nei.dbo.Job          AS Job       ON Customer.ID       = Job.Owner
										 LEFT JOIN Paradox.dbo.JOBLABOR AS Job_Labor ON Job.ID            = Job_Labor.[JOB #]
										 LEFT JOIN nei.dbo.Loc          AS Loc_1     ON Loc_1.Loc = Job.Loc
								  WHERE  Loc_1.Terr = ?
								  		 AND Job_Labor.[WEEK ENDING] >= '2017-01-01 00:00:00.000' 
								  		 AND Job_Labor.jobAlpha <> '9999'
										 AND Job_Labor.jobAlpha <> '8888'
										 AND Job_Labor.jobAlpha <> '7777'
										 AND Job_Labor.jobAlpha <> '6666'
										 AND Job_Labor.jobAlpha <> '5555'
										 AND Job_Labor.jobAlpha <> '4444'
										 AND Job_Labor.jobAlpha <> '3333'
										 AND Job_Labor.jobAlpha <> '2222'
										 AND Job_Labor.jobAlpha <> '1111'
										 AND Job_Labor.jobAlpha <> '0000'
								  GROUP BY Customer.ID) AS Customer_Paradox_Labor ON Customer_Paradox_Labor.ID = Customer.ID
					   LEFT JOIN (SELECT Customer.ID AS ID,
										 Sum(Job_Item.Amount)   AS TS_Labor
								  FROM   nei.dbo.OwnerWithRol   AS Customer
										 LEFT JOIN nei.dbo.Job  AS Job      ON Customer.ID = Job.Owner
										 LEFT JOIN nei.dbo.JobI AS Job_Item ON Job.ID      = Job_Item.Job
										 LEFT JOIN nei.dbo.Loc  AS Loc_2    ON Loc_2.Loc   = Job.Loc
								  WHERE  Loc_2.Terr = ?
								         AND Job_Item.fDate >= '2017-03-30 00:00:00.000'
								  		 AND Job_Item.fDate < '2018-01-01 00:00:00.000'
										 AND Job_Item.Type  = 1
										 AND Job_Item.Labor = 1
								  GROUP BY Customer.ID) AS Customer_TS_Labor ON Customer_TS_Labor.ID = Customer.ID
					   LEFT JOIN (SELECT Customer.ID AS ID,
										 Sum(Job_Item.Amount) AS Material
								  FROM   nei.dbo.OwnerWithRol AS Customer
										 LEFT JOIN nei.dbo.Job AS Job ON Customer.ID = Job.Owner
										 LEFT JOIN nei.dbo.JobI AS Job_Item ON Job.ID = Job_Item.Job
										 LEFT JOIN nei.dbo.Loc  AS Loc_3 ON Loc_3.Loc = Job.Loc
								  WHERE  Loc_3.Terr = ?
								          AND (Job_Item.Labor <> 1
										  OR Job_Item.Labor = ''
										  OR Job_Item.Labor = 0
										  OR Job_Item.Labor = ' '
										  OR Job_Item.Labor IS NULL)
										 AND Job_Item.Type = 1
										 AND Job_Item.fDate >= '2017-01-01 00:00:00.000'
										 AND Job_Item.fDate < '2018-01-01 00:00:00.000'
								  GROUP BY Customer.ID) AS Customer_Material ON Customer_Material.ID = Customer.ID
						) AS Customer
				) AS Customer
			WHERE Customer.Profit <> 0
		;",array($_GET['ID'],$_GET['ID'],$_GET['ID'],$_GET['ID']));
		if( ($errors = sqlsrv_errors() ) != null) {
        foreach( $errors as $error ) {
            echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
            echo "code: ".$error[ 'code']."<br />";
            echo "message: ".$error[ 'message']."<br />";
        }
    }
		$data = array();
		if($r){while($array = sqlsrv_fetch_array($r)){$data[] = $array;}}
		print json_encode(array("data"=>$data));
	}
}?>