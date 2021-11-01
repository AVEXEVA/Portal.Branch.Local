<?php
session_start();
require('index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    $User = sqlsrv_query($NEI,"SELECT * FROM nei.dbo.Emp WHERE ID = ?",array($_GET['User']));
    $User = sqlsrv_fetch_array($User);
    $Field = ($User['Field'] == 1 && "OFFICE" != $User['Title']) ? True : False;
    $r = sqlsrv_query($Portal,"
            SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
            FROM   Portal.dbo.Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
    $My_Privileges = array();
    while($array2 = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$My_Privileges[$array2['Access_Table']] = $array2;}
    $Privileged = false;
    if(isset($My_Privileges['Admin']) && $My_Privileges['Admin']['Other_Privilege'] >= 4){$Privileged = true;}
    if(!isset($array['ID']) || !$Privileged){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
		$data = array();
        $r = sqlsrv_query($NEI,"
			SELECT Location.*,
				CASE WHEN Location.Revenue = 0
						THEN 0
						ELSE
							CASE WHEN Location.Profit < 0 AND Location.Revenue < 0
								THEN ((Location.Profit / Location.Revenue) * -1)
								ELSE (Location.Profit / Location.Revenue)
							END
					END * 100 AS Profit_Percentage
			FROM (SELECT  *,
				    Location.Revenue - (Location.Labor + Location.Material) AS Profit
				FROM (SELECT Location.Tag AS Location,
					   CASE WHEN Location_Revenue.Revenue IS NULL
							THEN 0
							ELSE Location_Revenue.Revenue END
							AS Revenue,
					   CASE WHEN Location_Material.Material IS NULL
							THEN 0
							ELSE Location_Material.Material END
							AS Material,
					   CASE WHEN Location_Paradox_Labor.Paradox_Labor IS NULL
							THEN CASE WHEN Location_TS_Labor.TS_Labor  IS NULL
								THEN 0
								ELSE Location_TS_Labor.TS_Labor END
							ELSE CASE WHEN Location_TS_Labor.TS_Labor IS NULL
								THEN Location_Paradox_Labor.Paradox_Labor
								ELSE Location_Paradox_Labor.Paradox_Labor + Location_TS_Labor.TS_Labor END END AS Labor
				FROM   nei.dbo.Loc AS Location
					   LEFT JOIN (SELECT
                        Location.ID AS ID,
										 Sum(Amount) AS Revenue
								  FROM   nei.dbo.OwnerWithRol      AS Customer
										 LEFT JOIN nei.dbo.Loc     AS Location ON Customer.ID = Location.Owner
										 LEFT JOIN nei.dbo.Invoice AS Invoice  ON Invoice.Loc = Location.Loc
								  WHERE  Invoice.fDate >= '2016-01-01 00:00:00.000'
								         AND Invoice.fDate < '2020-01-01 00:00:00.000'
								  GROUP BY Location.ID
								 ) AS Location_Revenue ON Location_Revenue.ID = Location.ID
					   LEFT JOIN (SELECT
                        Location.ID AS ID,
										 Sum(Job_Labor.[TOTAL COST])  AS Paradox_Labor
								  FROM   nei.dbo.OwnerWithRol           AS Customer
										 LEFT JOIN nei.dbo.Job          AS Job       ON Customer.ID       = Job.Owner
                      LEFT JOIN nei.dbo.Loc AS Location ON Job.Loc = Location.Loc
										 LEFT JOIN Paradox.dbo.JOBLABOR AS Job_Labor ON Job.ID            = Job_Labor.[JOB #]
								  WHERE  Job_Labor.[WEEK ENDING] >= '2016-01-01 00:00:00.000'
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
								  GROUP BY Location.ID) AS Location_Paradox_Labor ON Location_Paradox_Labor.ID = Location.ID
					   LEFT JOIN (SELECT Location.ID AS ID,
										 Sum(Job_Item.Amount)   AS TS_Labor
								  FROM   nei.dbo.OwnerWithRol   AS Customer
										 LEFT JOIN nei.dbo.Job  AS Job      ON Customer.ID = Job.Owner
                     LEFT JOIN nei.dbo.Loc AS Location ON Job.Loc = Location.Loc
										 LEFT JOIN nei.dbo.JobI AS Job_Item ON Job.ID      = Job_Item.Job
								  WHERE  Job_Item.fDate >= '2017-03-30 00:00:00.000'
								  		 AND Job_Item.fDate < '2020-01-01 00:00:00.000'
										 AND Job_Item.Type  = 1
										 AND Job_Item.Labor = 1
								  GROUP BY Location.ID) AS Location_TS_Labor ON Location_TS_Labor.ID = Location.ID
					   LEFT JOIN (SELECT Location.ID AS ID,
										 Sum(Job_Item.Amount) AS Material
								  FROM   nei.dbo.OwnerWithRol AS Customer
										 LEFT JOIN nei.dbo.Job AS Job ON Job.Owner = Customer.ID
                     LEFT JOIN nei.dbo.Loc AS Location ON Job.Loc = Location.Loc
										 LEFT JOIN nei.dbo.JobI AS Job_Item ON Job.ID = Job_Item.Job
								  WHERE  (Job_Item.Labor <> 1
										  OR Job_Item.Labor = ''
										  OR Job_Item.Labor = 0
										  OR Job_Item.Labor = ' '
										  OR Job_Item.Labor IS NULL)
										 AND Job_Item.Type = 1
										 AND Job_Item.fDate >= '2016-01-01 00:00:00.000'
										 AND Job_Item.fDate < '2020-01-01 00:00:00.000'
								  GROUP BY Location.ID) AS Location_Material ON Location_Material.ID = Location.ID
						) AS Location
				) AS Location
			WHERE Location.Profit <> 0
		;");
		if( ($errors = sqlsrv_errors() ) != null) {
			foreach( $errors as $error ) {
				echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
				echo "code: ".$error[ 'code']."<br />";
				echo "message: ".$error[ 'message']."<br />";
			}
		}
		if($r){while($Customer = sqlsrv_fetch_array($r)){$data[] = $Customer;}}
		print json_encode(array('data'=>$data));
	}
}?>
