<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( [ 'read_and_close' => true ] ); 
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = $database->query(null,"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    $User = $database->query(null,"SELECT * FROM Emp WHERE ID = ?",array($_GET['User']));
    $User = sqlsrv_fetch_array($User);
    $Field = ($User['Field'] == 1 && "OFFICE" != $User['Title']) ? True : False;
    $r = $database->query(null,"
            SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
            FROM   Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
    $My_Privileges = array();
    while($array2 = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$My_Privileges[$array2['Access_Table']] = $array2;}
    $Privileged = false;
    if(isset($My_Privileges['Sales_Admin']) && $My_Privileges['Sales_Admin']['Other_Privilege'] >= 4){$Privileged = true;}
    if(isset($_SESSION['Branch_ID']) && $_SESSION['Branch_ID'] == $_GET['ID']){$Privileged = true;}
    if(!isset($array['ID']) || !$Privileged){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
		$data = array();
    $Start = isset($_GET['Start']) && strlen($_GET['Start']) > 0 ? date('Y-m-d H:i:s', strtotime($_GET['Start'])) : '2017-03-30 00:00:00.000';
    $End = isset($_GET['End']) && strlen($_GET['End']) > 0 ? date('Y-m-d H:i:s', strtotime($_GET['End'])) : '2065-12-01 00:00:00.000';
    $Active_SQL = isset($_GET['Active']) && $_GET['Active'] == 1 ? "AND Loc.Maint = 1" : NULL;
    $r = $database->query(null,
    "SELECT  Locations.*,
            CASE WHEN Locations.Profit = 0 OR (Locations.Labor_Sum + Locations.Materials_Sum + ABS(Locations.Overhead)) = 0 THEN 0
            ELSE
              CASE WHEN Locations.Invoices_Sum < 0 THEN Locations.Profit_with_Overhead_without_Bills / (Locations.Labor_Sum + Locations.Materials_Sum + ABS(Locations.Overhead))
              ELSE Locations.Profit_with_Overhead_without_Bills / (Locations.Labor_Sum + Locations.Materials_Sum + ABS(Locations.Overhead)) END
            END * 100 AS Cost_Margin,
            CASE WHEN Locations.Invoices_Sum = 0 THEN 0
            ELSE
              CASE WHEN Locations.Invoices_Sum < 0 THEN Locations.Profit / Locations.Invoices_Sum * -1
              ELSE Locations.Profit / Locations.Invoices_Sum END
            END * 100 AS Profit_Percentage_Raw,
            CASE WHEN Locations.Invoices_Sum = 0 THEN 0
            ELSE
              CASE WHEN Locations.Invoices_Sum < 0 THEN Locations.Profit_with_Overhead_without_Bills / Locations.Invoices_Sum * -1
              ELSE Locations.Profit_with_Overhead_without_Bills / Locations.Invoices_Sum END
            END * 100 AS Profit_Percentage
    FROM (
      SELECT  Job.Owner AS Customer_ID,
              CASE WHEN Loc.Maint = 1 THEN 'Maintained' ELSE '' END AS Active,
              CASE WHEN Loc.Custom3 <> '' THEN Loc.Custom3 ELSE Rol.Name END AS Customer_Name,
              Rol.Name AS Customer_N,
              Job.Loc AS Location_ID,
              Loc.Tag AS Location_Name,
              Terr.Name AS Territory_Name,
              Route.Name AS Route_Name,
              CASE WHEN MIN(Invoices.Sum) IS NULL THEN 0 ELSE MIN(Invoices.Sum) END AS Invoices_Sum,
              CASE WHEN MIN(Labor.Sum) IS NULL THEN 0 ELSE MIN(Labor.Sum) END AS Labor_Sum,
              CASE WHEN MIN(Materials.Sum) IS NULL THEN 0 ELSE MIN(Materials.Sum) END AS Materials_Sum,
              CASE WHEN MIN(Bills.Sum) IS NULL THEN 0 ELSE MIN(Bills.Sum) END AS Bills_Sum,
              CASE WHEN MIN(Invoices.Sum) IS NULL THEN 0 ELSE MIN(Invoices.Sum) END - (CASE WHEN MIN(Labor.Sum) IS NULL THEN 0 ELSE MIN(Labor.Sum) END + CASE WHEN MIN(Materials.Sum) IS NULL THEN 0 ELSE MIN(Materials.Sum) END) AS Profit,
              ABS((CASE WHEN MIN(Invoices.Sum) IS NULL THEN 0 ELSE MIN(Invoices.Sum) END * .14)) AS Overhead,
              (CASE WHEN MIN(Invoices.Sum) IS NULL THEN 0 ELSE MIN(Invoices.Sum) END - (CASE WHEN MIN(Labor.Sum) IS NULL THEN 0 ELSE MIN(Labor.Sum) END + CASE WHEN MIN(Materials.Sum) IS NULL THEN 0 ELSE MIN(Materials.Sum) END)) - ABS((CASE WHEN MIN(Invoices.Sum) IS NULL THEN 0 ELSE MIN(Invoices.Sum) END * .14)) AS Profit_with_Overhead,
              (CASE WHEN MIN(Invoices.Sum) IS NULL THEN 0 ELSE MIN(Invoices.Sum) END - (CASE WHEN MIN(Labor.Sum) IS NULL THEN 0 ELSE MIN(Labor.Sum) END + CASE WHEN MIN(Materials.Sum) IS NULL THEN 0 ELSE MIN(Materials.Sum) END + CASE WHEN MIN(Bills.Sum) IS NULL THEN 0 ELSE MIN(Bills.Sum) END)) - ABS((CASE WHEN MIN(Invoices.Sum) IS NULL THEN 0 ELSE MIN(Invoices.Sum) END * .14)) AS Profit_with_Overhead_without_Bills
      FROM    Job
              LEFT JOIN Loc ON Job.Loc = Loc.Loc
              LEFT JOIN Terr ON Loc.Terr = Terr.ID
              LEFT JOIN Route ON Loc.Route = Route.ID
              LEFT JOIN (
                SELECT    Job.Owner,
                          Job.Loc,
                          SUM(Invoice.Amount) AS Sum
                FROM      Invoice
                          LEFT JOIN Job ON Invoice.Job = Job.ID
                WHERE     Invoice.fDate >= ?
                          AND Invoice.fDate < ?
                          AND (
                            (
                                (Job.Type    =   2 OR Job.Type = 3)
                                AND Job.Status  <>  0
                                AND Job.fDate >= ?
                                AND Job.fDate < ?
                            )
                            OR  (Job.Type <>  2 AND Job.Type <> 3)
                          )
                GROUP BY  Job.Owner, Job.Loc
              ) AS Invoices ON Invoices.Loc = Job.Loc AND Invoices.Owner = Job.Owner
              LEFT JOIN (
                (SELECT    Job.Owner,
                          Job.Loc,
                          Sum(JobI.Amount) AS Sum
                FROM      JobI
                          LEFT JOIN Job ON JobI.Job = Job.ID
                WHERE     JobI.fDate >= ?
                          AND JobI.fDate < ?
                          AND JobI.Type   = 1
                          AND JobI.Labor  = 1
                          AND (
                            (
                                (Job.Type    =   2 OR Job.Type = 3)
                                AND Job.Status  <>  0
                                AND Job.fDate >= ?
                                AND Job.fDate < ?
                            )
                            OR  (Job.Type <>  2 AND Job.Type <> 3)
                          )
                GROUP BY  Job.Owner, Job.Loc
              )
              UNION ALL
              (SELECT
                       Job.Owner,
                       Job.Loc,
										 Sum(Job_Labor.[TOTAL COST])  AS Sum
								  FROM   OwnerWithRol           AS Customer
										 LEFT JOIN Job          AS Job       ON Customer.ID       = Job.Owner
                     LEFT JOIN Loc AS Location ON Job.Loc = Location.Loc
										 LEFT JOIN Paradox.dbo.JOBLABOR AS Job_Labor ON Job.ID            = CASE WHEN (SELECT CONVERT(int,
    CASE WHEN LEN(Job_Labor.[JOB #]) <= 11 THEN
      CASE WHEN Job_Labor.[JOB #] NOT LIKE N'%[^-0-9]%' THEN
        CASE WHEN CONVERT(bigint, Job_Labor.[JOB #]) BETWEEN -2147483648 AND 2147483647
             THEN Job_Labor.[JOB #]
        END
      END
    END)) IS NOT NULL THEN Job_Labor.[JOB #] ELSE 0 END
								  WHERE  Job_Labor.[WEEK ENDING] >= ? AND Job_Labor.[WEEK ENDING] < ?
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
								  GROUP BY Job.Owner, Job.Loc)
              ) AS Labor ON Labor.Loc = Job.Loc AND Labor.Owner = Job.Owner
              LEFT JOIN (
                SELECT    Job.Owner,
                          Job.Loc,
                          Sum(JobI.Amount) AS Sum
                FROM      JobI
                          LEFT JOIN Job ON JobI.Job = Job.ID
                WHERE     (
                            JobI.Labor <> 1
                            OR JobI.Labor = ''
                            OR JobI.Labor = 0
                            OR JobI.Labor = ' '
                            OR JobI.Labor IS NULL
                          )
                          AND JobI.Type = 1
                          AND JobI.fDate >= ?
                          AND JobI.fDate < ?
                          AND (
                            (
                                (Job.Type    =   2 OR Job.Type = 3)
                                AND Job.Status  <>  0
                                AND Job.fDate >= ?
                                AND Job.fDate < ?
                            )
                            OR  (Job.Type <>  2 AND Job.Type <> 3)
                          )
                GROUP BY  Job.Owner, Job.Loc
              ) AS Materials ON Materials.Loc = Job.Loc AND Materials.Owner = Job.Owner
              LEFT JOIN (
                SELECT    Job.Loc AS Loc,
                          Job.Owner,
                          Sum(OpenAR.Balance) AS Sum
                FROM      OpenAR
                          LEFT JOIN Invoice AS Inv ON OpenAR.Ref = Inv.Ref
                          LEFT JOIN Job ON Inv.Job = Job.ID
                WHERE     (
                            (
                                (Job.Type    =   2 OR Job.Type = 3)
                                AND Job.Status  <>  0
                                AND Job.fDate >= ?
                                AND Job.fDate < ?
                            )
                            OR  (Job.Type <>  2 AND Job.Type <> 3)
                          )
                GROUP BY  Job.Loc, Job.Owner
              ) AS Bills ON Bills.Loc = Job.Loc AND Bills.Owner = Job.Owner
              LEFT JOIN Owner ON Owner.ID = Invoices.Owner OR Owner.ID = Labor.Owner OR Owner.ID = Materials.Owner
              LEFT JOIN Rol ON Owner.Rol = Rol.ID
        WHERE (Invoices.Sum <> 0 OR Labor.Sum <> 0 OR Materials.Sum <> 0)
              AND Loc.Loc NOT IN (8626, 5164, 5156, 6293, 5166, 8671, 5167, 8726, 8684, 8652, 8614, 8848, 8644, 8997)
              {$Active_SQL}
        GROUP BY Job.Owner, Loc.Maint, Loc.Custom3, Rol.Name, Job.Loc, Loc.Tag, Terr.Name, Route.Name
      ) AS Locations
    ;", array($Start, $End, $Start, $End, $Start, $End, $Start, $End, $Start, $End, $Start, $End, $Start, $End, $Start, $End), array("Scrollable"=>SQLSRV_CURSOR_KEYSET));
    $row_count = sqlsrv_num_rows($r);
    $i = 0;
    $amount = 0;
		if($r){
      while($i < $row_count){
      $Customer = sqlsrv_fetch_array($r);
      $Customer['Grade'] = $Customer['Profit'] >= 350000 ? .35 + (($Customer['Profit_Percentage'] / 85.0) * .65) : (($Customer['Profit'] / 350000) * .35) + (($Customer['Profit_Percentage'] / 85.0) * .65);
      $amount += $Customer['Profit_with_Overhead_without_Bills'];
      foreach($Customer as $key=>$value){
        $Customer[$key] = is_numeric($value) && !in_array($key,array('Cost_Margin', 'Profit_Percentage', 'Grade', 'Location_ID', 'Route_Name')) ? money_format('%.2n', $value) : $value;
      }
      $data[] = $Customer;
      $i++;
    }}
		print json_encode(array('data'=>$data));
	}
}?>
