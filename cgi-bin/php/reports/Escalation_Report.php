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
    $Start = isset($_GET['Start']) && strlen($_GET['Start']) > 0 ? date("Y-m-d 00:00:00.000",strtotime($_GET['Start'])) : "2013-01-01 00:00:00.000";
    $End = isset($_GET['End']) && strlen($_GET['End']) > 0 ? date("Y-m-d 00:00:00.000",strtotime($_GET['End'])) : date("Y-m-d H:i:s");
        $columns = array(
          0 => 'OwnerWithRol.Name',
          1 => 'Loc.Tag',
          2 => 'Contract.BStart',
          3 => 'Contract.Lenght',
          4 => 'Contract.BFinish',
          5 => 'Contract.BAmt',
          6 => 'Contract.BCycle',
          7 => 'Contract.EscLast',
          8 => 'Contract.BEscCycle',
          9 => 'Customer.Profit',
          10 => 'Location.Profit',
          11 => 'Location.Net_Income',
          12 => 'Location.Revenue',
          13 => 'Location.Labor',
          14 => 'Location.Material',
          15 => 'Location.Profit_Percentage',
          16 => 'Location_Mod.Profit'
        );
        $Overhead = isset($_GET['Overhead']) && is_numeric($_GET['Overhead']) ? $_GET['Overhead'] : .14;
        $Order_By = isset($columns[$_GET['order'][0]['column']]) ? $columns[$_GET['order'][0]['column']] : null;
        $Order_By = $Order_By != Null ? "ORDER BY " . $Order_By : null;
        $Order_By = $Order_By != Null && ($_GET['order'][0]['dir'] == 'asc' || $_GET['order'][0]['dir'] == 'desc') ? $Order_By . ' ' . $_GET['order'][0]['dir'] : null;
        $r = sqlsrv_query($NEI,
        "SELECT   OwnerWithRol.Name               AS  Customer_Name,
                  Loc.Tag                         AS  Location_Name,
                  Contract.BAmt                   AS  Contract_Amount,
                  Contract.BCycle                 AS  Contract_BCycle,
                  Contract.BStart                 AS  Contract_Start,
                  Contract.BFinish                AS  Contract_End,
                  Contract.BLenght                AS  Contract_Length,
                  Contract.EscLast                AS  Contract_Escalated,
                  Contract.BEscCycle              AS  Contract_Escalation_Cycle,
                  Customer.Profit                 AS  Customer_Profit,
                  Location.Profit                 AS  Location_Profit,
                  Location.Net_Income             AS  Location_Net_Income,
                  Location.Revenue                AS  Location_Revenue,
                  Location.Labor                  AS  Location_Labor,
                  Location.Material               AS  Location_Material,
                  Location.Profit_Percentage      AS  Location_Profit_Percentage,
                  Location_Mod.Profit             AS  Location_Modernization_Profit
         FROM     nei.dbo.Contract
                  LEFT JOIN nei.dbo.Loc ON Contract.Loc = Loc.Loc
                  LEFT JOIN nei.dbo.OwnerWithRol ON Contract.Owner = OwnerWithRol.ID
                  LEFT JOIN (
                    SELECT Customer.*,
              				CASE WHEN Customer.Revenue = 0
              						THEN 0
              						ELSE
              							CASE WHEN Customer.Net_Income < 0 AND Customer.Revenue < 0
              								THEN ((Customer.Net_Income / Customer.Revenue) * -1)
              								ELSE (Customer.Net_Income / Customer.Revenue)
              							END
              					END * 100 AS Net_Income_Percentage,
                      CASE WHEN Customer.Revenue = 0
                          THEN 0
                          ELSE
                            CASE WHEN Customer.Net_Income < 0 AND Customer.Revenue < 0
                              THEN ((Customer.Net_Income / Customer.Revenue) * -1)
                              ELSE (Customer.Net_Income / Customer.Revenue)
                            END
                      END * (100 - (100 * {$Overhead})) AS Profit_Percentage,
                      CASE WHEN Customer.Net_Income < 0
                        THEN Customer.Net_Income - ({$Overhead} * Customer.Net_Income * -1)
                        ELSE Customer.Net_Income - ({$Overhead} * Customer.Net_Income)
                      END AS Profit
              			FROM (SELECT  *,
              				    Customer.Revenue - (Customer.Labor + Customer.Material) AS Net_Income
              				FROM (SELECT Customer.Name AS Customer,
                                   Customer.ID   AS ID,
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
              								  WHERE  Invoice.fDate >= ?
              								         AND Invoice.fDate < ?
              								  GROUP BY Customer.ID
              								 ) AS Customer_Revenue ON Customer_Revenue.ID = Customer.ID
              					   LEFT JOIN (SELECT Customer.ID                  AS ID,
              										 Sum(Job_Labor.[TOTAL COST])  AS Paradox_Labor
              								  FROM   nei.dbo.OwnerWithRol           AS Customer
              										 LEFT JOIN nei.dbo.Job          AS Job       ON Customer.ID       = Job.Owner
              										 LEFT JOIN Paradox.dbo.JOBLABOR AS Job_Labor ON Job.ID            = Job_Labor.[JOB #]
              								  WHERE  Job_Labor.[WEEK ENDING] >= ?
                                     AND Job_Labor.[WEEK ENDING] < ?
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
              								  WHERE  Job_Item.fDate >= '2017-03-30 00:00:00.000'
                                   AND Job_Item.fDate >= ?
              								  	 AND Job_Item.fDate < ?
              										 AND Job_Item.Type  = 1
              										 AND Job_Item.Labor = 1
              								  GROUP BY Customer.ID) AS Customer_TS_Labor ON Customer_TS_Labor.ID = Customer.ID
              					   LEFT JOIN (SELECT Customer.ID AS ID,
              										 Sum(Job_Item.Amount) AS Material
              								  FROM   nei.dbo.OwnerWithRol AS Customer
              										 LEFT JOIN nei.dbo.Job AS Job ON Customer.ID = Job.Owner
              										 LEFT JOIN nei.dbo.JobI AS Job_Item ON Job.ID = Job_Item.Job
              								  WHERE  (Job_Item.Labor <> 1
              										  OR Job_Item.Labor = ''
              										  OR Job_Item.Labor = 0
              										  OR Job_Item.Labor = ' '
              										  OR Job_Item.Labor IS NULL)
              										 AND Job_Item.Type = 1
              										 AND Job_Item.fDate >= ?
              										 AND Job_Item.fDate < ?
              								  GROUP BY Customer.ID) AS Customer_Material ON Customer_Material.ID = Customer.ID
              						) AS Customer
              				) AS Customer
              			WHERE Customer.Net_Income <> 0
                  ) AS Customer ON Contract.Owner = Customer.ID
                  LEFT JOIN (
                    SELECT Location.*,
              				CASE WHEN Location.Revenue = 0
              						THEN 0
              						ELSE
              							CASE WHEN Location.Net_Income < 0 AND Location.Revenue < 0
              								THEN ((Location.Net_Income / Location.Revenue) * -1)
              								ELSE (Location.Net_Income / Location.Revenue)
              							END
              					END * 100 AS Net_Income_Percentage,
                        CASE WHEN Location.Revenue = 0
                            THEN 0
                            ELSE
                              CASE WHEN Location.Net_Income < 0 AND Location.Revenue < 0
                                THEN ((Location.Net_Income / Location.Revenue) * -1)
                                ELSE (Location.Net_Income / Location.Revenue)
                              END
                        END * (100 - (100 * {$Overhead})) AS Profit_Percentage,
                        CASE WHEN Location.Net_Income < 0
                          THEN Location.Net_Income - ({$Overhead} * Location.Net_Income * -1)
                          ELSE Location.Net_Income - ({$Overhead} * Location.Net_Income)
                        END AS Profit
              			FROM (SELECT  *,
              				    Location.Revenue - (Location.Labor + Location.Material) AS Net_Income
              				FROM (SELECT Location.Tag AS Location,
                                   Location.Loc   AS ID,
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
              					   LEFT JOIN (SELECT Location.Loc AS ID,
              										 Sum(Amount) AS Revenue
              								    FROM   nei.dbo.Loc      AS Location
              										 LEFT JOIN nei.dbo.Invoice AS Invoice  ON Invoice.Loc = Location.Loc
              								  WHERE  Invoice.fDate >= ?
              								         AND Invoice.fDate < ?
              								  GROUP BY Location.Loc
                              ) AS Location_Revenue ON Location_Revenue.ID = Location.Loc
              					   LEFT JOIN (SELECT Location.Loc                  AS ID,
              										 Sum(Job_Labor.[TOTAL COST])  AS Paradox_Labor
              								  FROM   nei.dbo.Loc           AS Location
              										 LEFT JOIN nei.dbo.Job          AS Job       ON Location.Loc       = Job.Loc
              										 LEFT JOIN Paradox.dbo.JOBLABOR AS Job_Labor ON Job.ID            = Job_Labor.[JOB #]
              								  WHERE  Job_Labor.[WEEK ENDING] >= ?
                                    AND Job_Labor.[WEEK ENDING] < ?
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
              								  GROUP BY Location.Loc) AS Location_Paradox_Labor ON Location_Paradox_Labor.ID = Location.Loc
              					   LEFT JOIN (SELECT Location.Loc AS ID,
              										 Sum(Job_Item.Amount)   AS TS_Labor
              								  FROM   nei.dbo.Loc   AS Location
              										 LEFT JOIN nei.dbo.Job  AS Job      ON Location.Loc = Job.Loc
              										 LEFT JOIN nei.dbo.JobI AS Job_Item ON Job.ID      = Job_Item.Job
              								  WHERE  Job_Item.fDate >= '2017-03-30 00:00:00.000'
                                   AND Job_Item.fDate >= ?
              								  		 AND Job_Item.fDate < ?
              										 AND Job_Item.Type  = 1
              										 AND Job_Item.Labor = 1
              								  GROUP BY Location.Loc) AS Location_TS_Labor ON Location_TS_Labor.ID = Location.Loc
              					   LEFT JOIN (SELECT Location.Loc AS ID,
              										 Sum(Job_Item.Amount) AS Material
              								  FROM   nei.dbo.Loc AS Location
              										 LEFT JOIN nei.dbo.Job AS Job ON Location.Loc = Job.Loc
              										 LEFT JOIN nei.dbo.JobI AS Job_Item ON Job.ID = Job_Item.Job
              								  WHERE  (Job_Item.Labor <> 1
              										  OR Job_Item.Labor = ''
              										  OR Job_Item.Labor = 0
              										  OR Job_Item.Labor = ' '
              										  OR Job_Item.Labor IS NULL)
              										 AND Job_Item.Type = 1
              										 AND Job_Item.fDate >= ?
              										 AND Job_Item.fDate < ?
              								  GROUP BY Location.Loc) AS Location_Material ON Location_Material.ID = Location.Loc
              						) AS Location
              				) AS Location
              			WHERE Location.Net_Income <> 0
                  ) AS Location ON Contract.Loc = Location.ID
                  LEFT JOIN (
                    SELECT Location.*,
              				CASE WHEN Location.Revenue = 0
              						THEN 0
              						ELSE
              							CASE WHEN Location.Net_Income < 0 AND Location.Revenue < 0
              								THEN ((Location.Net_Income / Location.Revenue) * -1)
              								ELSE (Location.Net_Income / Location.Revenue)
              							END
              					END * 100 AS Net_Income_Percentage,
                        CASE WHEN Location.Net_Income < 0
                          THEN Location.Net_Income - ({$Overhead} * Location.Net_Income * -1)
                          ELSE Location.Net_Income - ({$Overhead} * Location.Net_Income)
                        END AS Profit
              			FROM (SELECT  *,
              				    Location.Revenue - (Location.Labor + Location.Material) AS Net_Income
              				FROM (SELECT Location.Tag AS Location,
                                   Location.Loc   AS ID,
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
              					   LEFT JOIN (SELECT Location.Loc AS ID,
              										 Sum(Invoice.Amount) AS Revenue
              								    FROM   nei.dbo.Loc      AS Location
              										 LEFT JOIN nei.dbo.Invoice AS Invoice  ON Invoice.Loc = Location.Loc
                                   LEFT JOIN nei.dbo.Job ON Invoice.Job = Job.ID
              								  WHERE  Invoice.fDate >= ?
              								         AND Invoice.fDate < ?
                                       AND Job.Type = 2
              								  GROUP BY Location.Loc
                              ) AS Location_Revenue ON Location_Revenue.ID = Location.Loc
              					   LEFT JOIN (SELECT Location.Loc                  AS ID,
              										 Sum(Job_Labor.[TOTAL COST])  AS Paradox_Labor
              								  FROM   nei.dbo.Loc           AS Location
              										 LEFT JOIN nei.dbo.Job          AS Job       ON Location.Loc       = Job.Loc
              										 LEFT JOIN Paradox.dbo.JOBLABOR AS Job_Labor ON Job.ID            = Job_Labor.[JOB #]
              								  WHERE  Job_Labor.[WEEK ENDING] >= ?
                                    AND Job_Labor.[WEEK ENDING] < ?
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
                                   AND Job.Type = 2
              								  GROUP BY Location.Loc) AS Location_Paradox_Labor ON Location_Paradox_Labor.ID = Location.Loc
              					   LEFT JOIN (SELECT Location.Loc AS ID,
              										 Sum(Job_Item.Amount)   AS TS_Labor
              								  FROM   nei.dbo.Loc   AS Location
              										 LEFT JOIN nei.dbo.Job  AS Job      ON Location.Loc = Job.Loc
              										 LEFT JOIN nei.dbo.JobI AS Job_Item ON Job.ID      = Job_Item.Job
              								  WHERE  Job_Item.fDate >= '2017-03-30 00:00:00.000'
                                   AND Job_Item.fDate >= ?
              								  		 AND Job_Item.fDate < ?
              										 AND Job_Item.Type  = 1
              										 AND Job_Item.Labor = 1
                                   AND Job.Type = 2
              								  GROUP BY Location.Loc) AS Location_TS_Labor ON Location_TS_Labor.ID = Location.Loc
              					   LEFT JOIN (SELECT Location.Loc AS ID,
              										 Sum(Job_Item.Amount) AS Material
              								  FROM   nei.dbo.Loc AS Location
              										 LEFT JOIN nei.dbo.Job AS Job ON Location.Loc = Job.Loc
              										 LEFT JOIN nei.dbo.JobI AS Job_Item ON Job.ID = Job_Item.Job
              								  WHERE  (Job_Item.Labor <> 1
              										  OR Job_Item.Labor = ''
              										  OR Job_Item.Labor = 0
              										  OR Job_Item.Labor = ' '
              										  OR Job_Item.Labor IS NULL)
              										 AND Job_Item.Type = 1
              										 AND Job_Item.fDate >= ?
              										 AND Job_Item.fDate < ?
                                   AND Job.Type = 2
              								  GROUP BY Location.Loc) AS Location_Material ON Location_Material.ID = Location.Loc
              						) AS Location
              				) AS Location
              			WHERE Location.Net_Income <> 0
                  ) AS Location_Mod ON Contract.Loc = Location_Mod.ID
          WHERE   Contract.BFinish      >=  ?
                  AND Contract.EscLast  =   '2018-01-01 00:00:00.000'
                  AND Contract.BEscCycle =  12
          {$Order_By}
		;",array($Start,$End, $Start,$End, $Start,$End, $Start,$End, $Start,$End, $Start,$End, $Start,$End, $Start,$End,$Start,$End, $Start,$End, $Start,$End, $Start,$End, date('Y-m-d H:i:s',strtotime('now'))));
    if( ($errors = sqlsrv_errors() ) != null) {
        foreach( $errors as $error ) {
            echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
            echo "code: ".$error[ 'code']."<br />";
            echo "message: ".$error[ 'message']."<br />";
        }
    }
    $BCycles = array(
      0 =>  'Monthly',
      1 =>  'Bi-Monthly',
      2 =>  'Quarterly',
      3 =>  '3 Times / Year',
      4 =>  'Semi-Anually',
      5 =>  'Annually',
      6 =>  'Never'
    );
		if($r){while($Customer = sqlsrv_fetch_array($r)){
      $Customer['Contract_BCycle'] = isset($BCycles[$Customer['Contract_BCycle']]) ? $BCycles[$Customer['Contract_BCycle']] : 'None';
      $Customer['Contract_Start'] = date("m/d/Y",strtotime($Customer['Contract_Start']));
      $Customer['Contract_End'] = date("m/d/Y",strtotime($Customer['Contract_End']));
      $Customer['Contract_Escalated'] = date("m/d/Y",strtotime($Customer['Contract_Escalated']));
      $data[] = $Customer;
    }}
		print json_encode(array('data'=>$data));
	}
}?>
