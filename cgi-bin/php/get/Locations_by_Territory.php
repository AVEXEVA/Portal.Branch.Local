<?php 
session_start( [ 'read_and_close' => true ] );
require('index.php');
setlocale(LC_MONETARY, 'en_US');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Elevator'){
        $My_User = sqlsrv_query($NEI,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID = ?",array($_SESSION['User']));
        $My_User = sqlsrv_fetch_array($My_User); 
        $Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
        $r = sqlsrv_query($Portal,"
            SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
            FROM   Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
        $My_Privileges = array();
        while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
        $Privileged = FALSE;
        if(isset($My_Privileges['Territory']) && $My_Privileges['Territory']['User_Privilege'] >= 4 && $My_Privileges['Territory']['Group_Privilege'] >= 4 && $My_Privileges['Territory']['Other_Privilege'] >= 4){$Privileged = TRUE;}
    }
    if(!isset($array['ID'])  || !$Privileged || !is_numeric($_GET['ID'])){?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {
		$r = sqlsrv_query($NEI,"
			SELECT Locations.Location_ID,
				   Locations.Location_Name,
				   Locations.Profit AS Profit,
				   Location_Modernizations.Profit AS Modernizations,
				   Location_Legal.Profit AS Legal,
					CASE WHEN Locations.Revenue = 0 
							THEN 0 
							ELSE 
								CASE WHEN Locations.Profit < 0 AND Locations.Revenue < 0 
									THEN ((Locations.Profit / Locations.Revenue) * -1)
									ELSE (Locations.Profit / Locations.Revenue)
								END
						END * 100 AS Profit_Percentage
			FROM (SELECT  *,
				    Locations.Revenue - (Locations.Labor + Locations.Material) AS Profit
				FROM (SELECT Location.Tag AS Location_Name,
							 Location.Loc AS Location_ID,
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
										 LEFT JOIN nei.dbo.Job     AS Job      ON Invoice.Job = Job.ID
								  WHERE  Invoice.fDate >= '2017-03-30 00:00:00.000'
										 AND Location.Terr = ?
										 AND Job.Type <> 2
										 AND Job.Type <> 12
										 AND Job.Type <> 9
								  GROUP BY Location.Loc
								 ) AS Location_Revenue ON Location_Revenue.ID = Location.Loc
					   LEFT JOIN (SELECT Location.Loc                 AS ID,
										 Sum(Job_Labor.[TOTAL COST])  AS Paradox_Labor
								  FROM   nei.dbo.Loc	              AS Location
										 LEFT JOIN nei.dbo.Job        AS Job         ON Location.Loc = Job.Loc
										 LEFT JOIN Paradox.dbo.JOBLABOR AS Job_Labor ON Job.ID            = Job_Labor.[JOB #]
								  WHERE  Location.Terr = ?
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
										 AND Job.Type <> 2
										 AND Job.Type <> 12
										 AND Job.Type <> 9
								  GROUP BY Location.Loc) AS Location_Paradox_Labor ON Location_Paradox_Labor.ID = Location.Loc
					   LEFT JOIN (SELECT Location.Loc AS ID,
										 Sum(Job_Item.Amount)   AS TS_Labor
								  FROM   nei.dbo.Loc		    AS Location
										 LEFT JOIN nei.dbo.Job  AS Job      ON Location.Loc = Job.Loc
										 LEFT JOIN nei.dbo.JobI AS Job_Item ON Job.ID      = Job_Item.Job
								  WHERE  Location.Terr = ?
								         AND Job_Item.fDate >= '2017-03-30 00:00:00.000'
										 AND Job_Item.Type  = 1
										 AND Job_Item.Labor = 1
										 AND Job.Type <> 2
										 AND Job.Type <> 12
										 AND Job.Type <> 9
								  GROUP BY Location.Loc) AS Location_TS_Labor ON Location_TS_Labor.ID = Location.Loc
					   LEFT JOIN (SELECT Location.Loc AS ID,
										 Sum(Job_Item.Amount) AS Material
								  FROM   nei.dbo.Loc AS Location
										 LEFT JOIN nei.dbo.Job AS Job ON Location.Loc = Job.Loc
										 LEFT JOIN nei.dbo.JobI AS Job_Item ON Job.ID = Job_Item.Job
								  WHERE  Location.Terr = ?
								          AND (Job_Item.Labor <> 1
										  OR Job_Item.Labor = ''
										  OR Job_Item.Labor = 0
										  OR Job_Item.Labor = ' '
										  OR Job_Item.Labor IS NULL)
										 AND Job_Item.Type = 1
										 AND Job_Item.fDate >= '2017-03-30 00:00:00.000'
										 AND Job.Type <> 2
										 AND Job.Type <> 12
										 AND Job.Type <> 9
								  GROUP BY Location.Loc) AS Location_Material ON Location_Material.ID = Location.Loc
						) AS Locations
				) AS Locations
			LEFT JOIN 
				(SELECT  *,
				    Locations.Revenue - (Locations.Labor + Locations.Material) AS Profit
				FROM (SELECT Location.Tag AS Location_Name,
							 Location.Loc AS Location_ID,
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
										 LEFT JOIN nei.dbo.Job     AS Job      ON Invoice.Job = Job.ID
								  WHERE  Invoice.fDate >= '2017-03-30 00:00:00.000'
										 AND Location.Terr = ?
										 AND Job.Type = 2
								  GROUP BY Location.Loc
								 ) AS Location_Revenue ON Location_Revenue.ID = Location.Loc
					   LEFT JOIN (SELECT Location.Loc                 AS ID,
										 Sum(Job_Labor.[TOTAL COST])  AS Paradox_Labor
								  FROM   nei.dbo.Loc	              AS Location
										 LEFT JOIN nei.dbo.Job        AS Job       ON Location.Loc       = Job.Loc
										 LEFT JOIN Paradox.dbo.JOBLABOR AS Job_Labor ON Job.ID            = Job_Labor.[JOB #]
								  WHERE  Location.Terr = ?
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
										 AND Job.Type = 2
								  GROUP BY Location.Loc) AS Location_Paradox_Labor ON Location_Paradox_Labor.ID = Location.Loc
					   LEFT JOIN (SELECT Location.Loc AS ID,
										 Sum(Job_Item.Amount)   AS TS_Labor
								  FROM   nei.dbo.Loc		    AS Location
										 LEFT JOIN nei.dbo.Job  AS Job      ON Location.Loc = Job.Owner
										 LEFT JOIN nei.dbo.JobI AS Job_Item ON Job.ID      = Job_Item.Job
								  WHERE  Location.Terr = ?
								         AND Job_Item.fDate >= '2017-03-30 00:00:00.000'
										 AND Job_Item.Type  = 1
										 AND Job_Item.Labor = 1
										 AND Job.Type = 2
								  GROUP BY Location.Loc) AS Location_TS_Labor ON Location_TS_Labor.ID = Location.Loc
					   LEFT JOIN (SELECT Location.Loc AS ID,
										 Sum(Job_Item.Amount) AS Material
								  FROM   nei.dbo.Loc AS Location
										 LEFT JOIN nei.dbo.Job AS Job ON Location.Loc = Job.Owner
										 LEFT JOIN nei.dbo.JobI AS Job_Item ON Job.ID = Job_Item.Job
								  WHERE  Location.Terr = ?
								          AND (Job_Item.Labor <> 1
										  OR Job_Item.Labor = ''
										  OR Job_Item.Labor = 0
										  OR Job_Item.Labor = ' '
										  OR Job_Item.Labor IS NULL)
										 AND Job_Item.Type = 1
										 AND Job_Item.fDate >= '2017-03-30 00:00:00.000'
										 AND Job.Type = 2
								  GROUP BY Location.Loc) AS Location_Material ON Location_Material.ID = Location.Loc
						) AS Locations
				) AS Location_Modernizations ON Locations.Location_ID = Location_Modernizations.Location_ID
			LEFT JOIN (SELECT  *,
				    Locations.Revenue - (Locations.Labor + Locations.Material) AS Profit
				FROM (SELECT Location.Tag AS Location_Name,
							 Location.Loc AS Location_ID,
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
										 LEFT JOIN nei.dbo.Job     AS Job      ON Invoice.Job = Job.ID
								  WHERE  Invoice.fDate >= '2017-03-30 00:00:00.000'
										 AND Location.Terr = ?
										 AND (Job.Type = 12
										 OR Job.Type = 9)
								  GROUP BY Location.Loc
								 ) AS Location_Revenue ON Location_Revenue.ID = Location.Loc
					   LEFT JOIN (SELECT Location.Loc                 AS ID,
										 Sum(Job_Labor.[TOTAL COST])  AS Paradox_Labor
								  FROM   nei.dbo.Loc	              AS Location
										 LEFT JOIN nei.dbo.Job        AS Job       ON Location.Loc       = Job.Loc
										 LEFT JOIN Paradox.dbo.JOBLABOR AS Job_Labor ON Job.ID            = Job_Labor.[JOB #]
								  WHERE  Location.Terr = ?
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
										 AND (Job.Type = 12
										 OR Job.Type = 9)
								  GROUP BY Location.Loc) AS Location_Paradox_Labor ON Location_Paradox_Labor.ID = Location.Loc
					   LEFT JOIN (SELECT Location.Loc AS ID,
										 Sum(Job_Item.Amount)   AS TS_Labor
								  FROM   nei.dbo.Loc		    AS Location
										 LEFT JOIN nei.dbo.Job  AS Job      ON Location.Loc = Job.Loc
										 LEFT JOIN nei.dbo.JobI AS Job_Item ON Job.ID      = Job_Item.Job
								  WHERE  Location.Terr = ?
								         AND Job_Item.fDate >= '2017-03-30 00:00:00.000'
										 AND Job_Item.Type  = 1
										 AND Job_Item.Labor = 1
										 AND (Job.Type = 12
										 OR Job.Type = 9)
								  GROUP BY Location.Loc) AS Location_TS_Labor ON Location_TS_Labor.ID = Location.Loc
					   LEFT JOIN (SELECT Location.Loc AS ID,
										 Sum(Job_Item.Amount) AS Material
								  FROM   nei.dbo.Loc AS Location
										 LEFT JOIN nei.dbo.Job AS Job ON Location.Loc = Job.Loc
										 LEFT JOIN nei.dbo.JobI AS Job_Item ON Job.ID = Job_Item.Job
								  WHERE  Location.Terr = ?
								          AND (Job_Item.Labor <> 1
										  OR Job_Item.Labor = ''
										  OR Job_Item.Labor = 0
										  OR Job_Item.Labor = ' '
										  OR Job_Item.Labor IS NULL)
										 AND Job_Item.Type = 1
										 AND Job_Item.fDate >= '2017-03-30 00:00:00.000'
										 AND (Job.Type = 12
										 OR Job.Type = 9)
								  GROUP BY Location.Loc) AS Location_Material ON Location_Material.ID = Location.Loc
						) AS Locations
				) AS Location_Legal ON Location_Legal.Location_ID = Locations.Location_ID
				WHERE Locations.Profit <> 0
			;",array($_GET['ID'],$_GET['ID'],$_GET['ID'],$_GET['ID'],$_GET['ID'],$_GET['ID'],$_GET['ID'],$_GET['ID'],$_GET['ID'],$_GET['ID'],$_GET['ID'],$_GET['ID']));
		
		$data = array();
		if($r){while($array = sqlsrv_fetch_array($r)){$data[] = $array;}}
		print json_encode(array("data"=>$data));
	}
}?>