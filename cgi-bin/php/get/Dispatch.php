<?php 
session_start( [ 'read_and_close' => true ] );
require('index.php');
setlocale(LC_MONETARY, 'en_US');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"
        SELECT * 
        FROM   Connection 
        WHERE  Connection.Connector = ? 
               AND Connection.Hash = ?
    ;", array($_SESSION['User'],$_SESSION['Hash']));
    $Connection = sqlsrv_fetch_array($r);
    $My_User    = sqlsrv_query($NEI,"
        SELECT Emp.*, 
               Emp.fFirst AS First_Name, 
               Emp.Last   AS Last_Name 
        FROM   Emp
        WHERE  Emp.ID = ?
    ;", array($_SESSION['User']));
    $My_User = sqlsrv_fetch_array($My_User); 
    $My_Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
    $r = sqlsrv_query($Portal,"
        SELECT Privilege.Access_Table, 
               Privilege.User_Privilege, 
               Privilege.Group_Privilege, 
               Privilege.Other_Privilege
        FROM   Privilege
        WHERE  Privilege.User_ID = ?
    ;",array($_SESSION['User']));
    $My_Privileges = array();
    while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
    $Privileged = False;
    if( isset($My_Privleges['Ticket']) 
	  	&& $My_Privileges['Ticket']['Other_Privilege'] >= 4){
            $Privileged = True;}
    if(!isset($Connection['ID'])  || !is_numeric($_GET['ID']) || !$Privileged){print json_encode(array('data'=>array()));}
    else {
		$Selected_Supervisors = explode(',',$_GET['Supervisors']);
		if(count($Selected_Supervisors) == 0 || !isset($_GET['Supervisors']) || $_GET['Supervisors'] == '' || $_GET['Supervisors'] == 'All'){$SQL_Supervisors = "'1' = '1'";}
		else {
		    $SQL_Supervisors = "";
		    $Supervisors_SQL = array();
		    foreach($Selected_Supervisors as $key=>$Selected_Supervisor){$Supervisors_SQL[$key] = "tblWork.Super = '" . $Selected_Supervisor . "'";}
		    $SQL_Supervisors = "(" . implode(" OR ",$Supervisors_SQL) . ")"; 
		}
		$Selected_Mechanics = explode(",",$_GET['Mechanics']);

		if(count($Selected_Mechanics) == 0 || !isset($_GET['Mechanics']) || $_GET['Mechanics'] == '' || $_GET['Mechanics'] == null || $Selected_Mechanics[0] == 'undefined'){$SQL_Selected_Mechanics = "'1' = '1'";}
		else {
		    $SQL_Selected_Mechanics = "";
		    $Selected_Mechanics_SQL = array();
		    foreach($Selected_Mechanics as $key=>$Selected_Mechanic){$Selected_Mechanics_SQL[$key] = "TicketO.fWork = '" . $Selected_Mechanic . "'";}
		    $SQL_Selected_Mechanics = "(" . implode(" OR ",$Selected_Mechanics_SQL) . ")";
		}
		$r = sqlsrv_query($NEI,"
			SELECT Emp.*, 
				   tblWork.Super 
			FROM   Emp 
				   LEFT JOIN nei.dbo.tblWork ON 'A' + convert(varchar(10),Emp.ID) + ',' = tblWork.Members 
			WHERE  Field          = 1
				   AND Emp.Status = 0
		;");
		$Mechanics = array();
		if($r){
			while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$Mechanics[] = $array;}
		}	
		//GET TICKETS
		if($_GET['Start_Date'] > 0){$Start_Date = DateTime::createFromFormat('m/d/Y', $_GET['Start_Date'])->format("Y-m-d 00:00:00.000");}
		else{
		    $Start_Date = new DateTime('first day of this month');
		    $Start_Date = $Start_Date->format("Y-m-d 00:00:00.000");}

		if($_GET['End_Date'] > 0){$End_Date = DateTime::createFromFormat('m/d/Y', $_GET['End_Date'])->format("Y-m-d 23:59:59.999");}
		else{
		    $End_Date = new DateTime('last day of this month');
		    $End_Date = $End_Date->format("Y-m-d 23:59:59.999");}

		if(!isset($_GET['Location_Tag']) || $_GET['Location_Tag'] == "All" || $_GET['Location_Tag'] == ""){$Location_Tag = "' OR '1'='1";}
		else {$Location_Tag = addslashes($_GET['Location_Tag']);}

		if(!isset($_GET['Status']) || $_GET['Status'] == 'All' || $_GET['Status'] == ""){$Status = "' OR '1'='1";}
		else{$Status = $_GET['Status'];}

		if($End_Date < date('Y-m-d 00:00:00.000')){$Closeout = " AND TickOStatus.Type='Completed'";}
		else {$Clouseout = '';}

		$r = sqlsrv_query($NEI,"
			SELECT TicketO.*, 
				   Loc.Tag 			 AS Tag, 
				   Loc.Address 		 AS Address, 
				   Loc.City 		 AS City, 
				   Loc.State 		 AS State, 
				   Loc.Zip 			 AS Zip, 
				   Job.ID 			 AS Job_ID, 
				   Job.fDesc 		 AS Job_Description, 
				   OwnerWithRol.ID 	 AS Owner_ID, 
				   OwnerWithRol.Name AS Customer, 
				   JobType.Type 	 AS Job_Type, 
				   Elev.Unit 		 AS Unit_Label, 
				   Elev.State 		 AS Unit_State, 
				   TickOStatus.Type  AS Status, 
				   Emp.CallSign 	 AS CallSign, 
				   Emp.fFirst 		 AS fFirst, 
				   Emp.Last 		 AS Last
			FROM 
				nei.dbo.TicketO 
				LEFT JOIN nei.dbo.Loc 		   ON TicketO.LID = Loc.Loc 
				LEFT JOIN nei.dbo.Job 		   ON TicketO.Job = Job.ID
				LEFT JOIN nei.dbo.OwnerWithRol ON TicketO.Owner = OwnerWithRol.ID
				LEFT JOIN nei.dbo.JobType 	   ON Job.Type = JobType.ID
				LEFT JOIN nei.dbo.Elev 		   ON TicketO.LElev = Elev.ID
				LEFT JOIN nei.dbo.TickOStatus  ON TicketO.Assigned = TickOStatus.Ref
				LEFT JOIN Emp 		   ON TicketO.fWork = Emp.fWork
				LEFT JOIN nei.dbo.tblWork 	   ON 'A' + convert(varchar(10),Emp.ID) + ',' = tblWork.Members 
			WHERE 
				{$SQL_Supervisors}
				AND (
					(EDate >= ? AND EDate <= ?) 
					OR Assigned=3 OR Assigned=1) 
				AND (Tag = '{$Location_Tag}') 
				AND Emp.Status = 0
		;",array($Start_Date,$End_Date));
		$Tickets = array();
		if($r){
			while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){
			    $Tickets[$array['ID']] = $array;
			}
		}
		$Selected_Mechanics = explode(",",$_GET['Mechanics']);

		if(count($Selected_Mechanics) == 0 || !isset($_GET['Mechanics']) || $_GET['Mechanics'] == '' || $_GET['Mechanics'] == null || $Selected_Mechanics[0] == 'undefined'){$SQL_Selected_Mechanics = "'1' = '1'";}
		else {
		    $SQL_Selected_Mechanics = "";
		    $Selected_Mechanics_SQL = array();
		    foreach($Selected_Mechanics as $key=>$Selected_Mechanic){$Selected_Mechanics_SQL[$key] = "TicketD.fWork = '" . $Selected_Mechanic . "'";}
		    $SQL_Selected_Mechanics = "(" . implode(" OR ",$Selected_Mechanics_SQL) . ")";
		}
	    $r = sqlsrv_query($NEI,"
	    	SELECT 
	    		TicketD.CDate 	  AS CDate, 
	    		TicketD.ID 		  AS ID, 
	    		TicketD.EDate 	  AS EDate, 
	    		TicketD.fWork	  AS fWork, 
	    		TicketD.Job 	  AS Job, 
	    		TicketD.Loc 	  AS Loc, 
	    		TicketD.fDesc 	  AS fDesc, 
	    		TicketD.DescRes   AS DescRes, 
	    		TicketD.ClearPR   AS ClearPR, 
	    		TicketD.Total 	  AS Total, 
	    		Loc.Tag  		  AS Tag,
	    		Loc.Address 	  AS Address,
	    		Loc.City 		  AS City,
	    		Loc.State 		  AS State,
	    		Loc.Zip 		  AS Zip,
	    		Job.ID 			  AS Job_ID, 
	    		Job.fDesc 		  AS Job_Description, 
	    		OwnerWithRol.ID   AS Owner_ID, 
	    		OwnerWithRol.Name AS Customer, 
	    		JobType.Type 	  AS Job_Type, 
	    		Elev.Unit 		  AS Unit_Label, 
	    		Elev.State 		  AS Unit_State, 
	    		Emp.fFirst 		  AS fFirst, 
	    		Emp.Last 		  AS Last, 
	    		Emp.CallSign 	  AS CallSign
	    	FROM
	    		nei.dbo.TicketD 
	    		LEFT JOIN nei.dbo.Loc 		   ON TicketD.Loc = Loc.Loc
	    		LEFT JOIN nei.dbo.Job 		   ON TicketD.Job = Job.ID 
	    		LEFT JOIN nei.dbo.OwnerWithRol ON Loc.Owner = OwnerWithRol.ID
	    		LEFT JOIN nei.dbo.JobType  	   ON Job.Type = JobType.ID
	    		LEFT JOIN nei.dbo.Elev 		   ON TicketD.Elev = Elev.ID
	    		LEFT JOIN Emp 	 	   ON TicketD.fWork = Emp.fWork
	    		LEFT JOIN nei.dbo.tblWork  	   ON 'A' + convert(varchar(10),Emp.ID) + ',' = tblWork.Members 
	    	WHERE 
				{$SQL_Supervisors} 
	    		AND EDate 	   >= ? 
	    		AND EDate 	   <= ?
	    		AND (Tag 	   =  '{$Location_Tag}') 
	    		AND Emp.Status =  0
		;",array($Start_Date,$End_Date));
	    if($r){
		    while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){
		        $Tickets[$array['ID']] = $array;
		        $Tickets[$array['ID']]['Status'] = "Completed";
		    }
		}
		$r = sqlsrv_query($NEI,"
	    	SELECT 
	    		TicketDArchive.CDate 	AS CDate, 
	    		TicketDArchive.ID 		AS ID, 
	    		TicketDArchive.EDate 	AS EDate, 
	    		TicketDArchive.fWork	AS fWork, 
	    		TicketDArchive.Job 		AS Job, 
	    		TicketDArchive.Loc 		AS Loc, 
	    		TicketDArchive.fDesc 	AS fDesc, 
	    		TicketDArchive.DescRes 	AS DescRes, 
	    		TicketDArchive.ClearPR 	AS ClearPR, 
	    		TicketDArchive.Total 	AS Total, 
	    		Loc.Tag  				AS Tag,
	    		Loc.Address 			AS Address,
	    		Loc.City 				AS City,
	    		Loc.State 				AS State,
	    		Loc.Zip 				AS Zip,
	    		Job.ID 					AS Job_ID, 
	    		Job.fDesc 				AS Job_Description, 
	    		OwnerWithRol.ID 		AS Owner_ID, 
	    		OwnerWithRol.Name 		AS Customer, 
	    		JobType.Type 			AS Job_Type, 
	    		Elev.Unit 				AS Unit_Label, 
	    		Elev.State 				AS Unit_State, 
	    		Emp.fFirst 				AS fFirst, 
	    		Emp.Last 				AS Last, 
	    		Emp.CallSign 			AS CallSign,
				'Completed'				AS Status
	    	FROM
	    		nei.dbo.TicketDArchive 
	    		LEFT JOIN nei.dbo.Loc 			ON TicketDArchive.Loc = Loc.Loc
	    		LEFT JOIN nei.dbo.Job 			ON TicketDArchive.Job = Job.ID
	    		LEFT JOIN nei.dbo.OwnerWithRol 	ON Loc.Owner = OwnerWithRol.ID
	    		LEFT JOIN nei.dbo.JobType 		ON Job.Type = JobType.ID
	    		LEFT JOIN nei.dbo.Elev 			ON TicketDArchive.Elev = Elev.ID
	    		LEFT JOIN Emp 			ON TicketDArchive.fWork = Emp.fWork
	    		LEFT JOIN nei.dbo.tblWork 		ON 'A' + convert(varchar(10),Emp.ID) + ',' = tblWork.Members 
	    	WHERE 
				{$SQL_Supervisors} 
	    		AND EDate      >= ?
	    		AND EDate      <= ?
	    		AND (Tag       =  '{$Location_Tag}') 
	    		AND Emp.Status =  0
		;",array($Start_Date,$End_Date));
	    if($r){
		    while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){
		        $Tickets[$array['ID']] = $array;
		    }
		}
        $Tickets2 = array();
        foreach($Tickets as $Ticket){
        	$Ticket['fFirst'] = ucwords(strtolower(($Ticket['fFirst'])));
        	$Ticket['Last'] = ucwords(strtolower(($Ticket['Last'])));
        	$Tickets2[] = $Ticket;
        }
        echo json_encode(array('data'=>$Tickets2));
	}
}?>
