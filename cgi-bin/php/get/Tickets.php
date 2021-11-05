<?php
session_start( [ 'read_and_close' => true ] );
require('index.php');
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
	 if( isset($My_Privileges['Ticket'])
        && (
				$My_Privileges['Ticket']['User_Privilege'] >= 4
			||	$My_Privileges['Ticket']['Group_Privilege'] >= 4
			||	$My_Privileges['Ticket']['Other_Privilege'] >= 4)){
            $Privileged = True;}
    if(!isset($Connection['ID']) || !$Privileged){print json_encode(array('data'=>array()));}
	else {
        if(isset($_GET['Mechanic']) && !$Field && is_numeric($_GET['Mechanic']) && $My_Privileges['Ticket']['Other_Privilege'] >= 4){$Mechanic = $_GET['Mechanic'];}
        elseif($My_Privileges['Ticket']['User_Privilege'] >= 4) {$Mechanic = is_numeric($_SESSION['User']) ? $_SESSION['User'] : -1;}
        if($Mechanic > 0){

            $Call_Sign = "";
            $r = sqlsrv_query($NEI,"select * from Emp where ID = '" . $Mechanic . "'");
            $array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
            $Call_Sign = $array['CallSign'];
            $Alias = $array['fFirst'][0] . $array['Last'];
            $Employee_ID = $array['fWork'];
            while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){}
            //GET TICKETS
            if($_GET['Start_Date'] > 0){$Start_Date = DateTime::createFromFormat('m/d/Y', $_GET['Start_Date'])->format("Y-m-d 00:00:00.000");}
            else{$Start_Date = DateTime::createFromFormat('m/d/Y',"01/01/2017")->format("Y-m-d 00:00:00.000");}

            if($_GET['End_Date'] > 0){$End_Date = DateTime::createFromFormat('m/d/Y', $_GET['End_Date'])->format("Y-m-d 23:59:59.999");}
            else{$End_Date = DateTime::createFromFormat('m/d/Y',"01/01/3000")->format("Y-m-d 23:59:59.999");}

			$Today = DateTime::createFromFormat('m/d/Y', date("m/d/Y"))->format("Y-m-d 00:00:00.000");

			if($Start_Date <= $Today && $Today <= $End_Date){$Today = "'1' = '1'";}
			else {$Today = "'1' = '2'";}

			$r = sqlsrv_query($NEI,"
				SELECT Tickets.*,
					   Loc.ID            AS Account,
					   Loc.Tag           AS Tag,
					   Loc.Tag           AS Location,
					   Loc.Address       AS Address,
					   Loc.Address       AS Street,
					   Loc.City          AS City,
					   Loc.State         AS State,
					   Loc.Zip           AS Zip,
					   Job.ID            AS Job_ID,
             Job.fDesc         AS Job_Description,
             OwnerWithRol.ID   AS Owner_ID,
             OwnerWithRol.Name AS Customer,
             Elev.Unit         AS Unit_Label,
             Elev.State        AS Unit_State,
             Emp.fFirst        AS Worker_First_Name,
             Emp.Last          AS Worker_Last_Name,
					   JobType.Type      AS Job_Type
				FROM (
						(SELECT TicketO.ID       AS ID,
								TicketO.fDesc       AS Description,
								TicketDPDA.DescRes  AS Resolution,
								TicketO.CDate       AS Created,
								TicketO.DDate       AS Dispatched,
								TicketO.EDate       AS Date,
								TicketO.TimeSite    AS On_Site,
								TicketO.TimeComp    AS Completed,
								TicketO.Who 	      AS Caller,
								TicketO.fBy         AS Reciever,
								TicketO.Level       AS Level,
								TicketO.Cat         AS Category,
								TicketO.LID         AS Location,
								TicketO.Job         AS Job,
								TicketO.LElev       AS Unit,
								TicketO.Owner       AS Owner,
								TicketO.fWork       AS Mechanic,
								TickOStatus.Type    AS Status,
								TicketDPDA.Total    AS Total,
								TicketDPDA.Reg      AS Regular,
								TicketDPDA.OT       AS Overtime,
								TicketDPDA.DT       AS Doubletime,
                0                   AS ClearPR,
                TicketO.Assigned    AS Assigned
						 FROM   nei.dbo.TicketO
						        LEFT JOIN TickOStatus ON TicketO.Assigned = TickOStatus.Ref
                    LEFT JOIN nei.dbo.TicketDPDA ON TicketDPDA.ID = TicketO.ID
						 WHERE  TicketO.fWork     =  ?
						        AND (
										(TicketO.EDate >= ?
								     		AND TicketO.EDate <= ?)
									OR
										({$Today}
										AND
											(TickOStatus.Type = 'On Site'
										    	OR TickOSTatus.Type = 'En Route'
											)
										)
								)
						)
						UNION ALL
						(SELECT TicketD.ID       AS ID,
								TicketD.fDesc    AS Description,
								TicketD.DescRes  AS Resolution,
								TicketD.CDate    AS Created,
								TicketD.DDate    AS Dispatched,
								TicketD.EDate    AS Date,
								TicketD.TimeSite AS On_Site,
								TicketD.TimeComp AS Completed,
								TicketD.Who 	 AS Caller,
								TicketD.fBy      AS Reciever,
								TicketD.Level    AS Level,
								TicketD.Cat      AS Category,
								TicketD.Loc      AS Location,
								TicketD.Job      AS Job,
								TicketD.Elev     AS Unit,
								Loc.Owner        AS Owner,
								TicketD.fWork    AS Mechanic,
								'Completed'      AS Status,
								TicketD.Total    AS Total,
								TicketD.Reg      AS Regular,
								TicketD.OT       AS Overtime,
								TicketD.DT       AS Doubletime,
                TicketD.ClearPR  AS ClearPR,
                4                AS Assigned
						 FROM   nei.dbo.TicketD
						 		LEFT JOIN nei.dbo.Loc ON TicketD.Loc = Loc.Loc
						 WHERE  TicketD.fWork     =  ?
						        AND TicketD.EDate >= ?
								AND TicketD.EDate <= ?
						)
						UNION ALL
						(SELECT TicketDArchive.ID       AS ID,
								TicketDArchive.fDesc    AS Description,
								TicketDArchive.DescRes  AS Resolution,
								TicketDArchive.CDate    AS Created,
								TicketDArchive.DDate    AS Dispatched,
								TicketDArchive.EDate    AS Date,
								TicketDArchive.TimeSite AS On_Site,
								TicketDArchive.TimeComp AS Completed,
								TicketDArchive.Who 	    AS Caller,
								TicketDArchive.fBy      AS Reciever,
								TicketDArchive.Level    AS Level,
								TicketDArchive.Cat      AS Category,
								TicketDArchive.Loc      AS Location,
								TicketDArchive.Job      AS Job,
								TicketDArchive.Elev     AS Unit,
								Loc.Owner               AS Owner,
								TicketDArchive.fWork    AS Mechanic,
								'Completed'             AS Status,
								TicketDArchive.Total    AS Total,
								TicketDArchive.Reg      AS Regular,
								TicketDArchive.OT       AS Overtime,
								TicketDArchive.DT       AS Doubletime,
                TicketDArchive.ClearPR  AS ClearPR,
                4                       AS Assigned
						 FROM   nei.dbo.TicketDArchive
						 		LEFT JOIN nei.dbo.Loc ON TicketDArchive.Loc = Loc.Loc
						 WHERE  TicketDArchive.fWork     =  ?
						        AND TicketDArchive.EDate >= ?
								AND TicketDArchive.EDate <= ?
						)
					) AS Tickets
					LEFT JOIN nei.dbo.Loc          ON Tickets.Location = Loc.Loc
					LEFT JOIN nei.dbo.Job          ON Tickets.Job      = Job.ID
					LEFT JOIN nei.dbo.Elev         ON Tickets.Unit     = Elev.ID
					LEFT JOIN nei.dbo.OwnerWithRol ON Tickets.Owner    = OwnerWithRol.ID
					LEFT JOIN Emp          ON Tickets.Mechanic = Emp.fWork
					LEFT JOIN nei.dbo.JobType      ON Job.Type         = JobType.ID
        WHERE Tickets.Assigned >= 4
			",array($Employee_ID,$Start_Date,$End_Date,$Employee_ID,$Start_Date,$End_Date,$Employee_ID,$Start_Date,$End_Date),array("Scrollable"=>SQLSRV_CURSOR_KEYSET));
			$data = array();
			$row_count = sqlsrv_num_rows( $r );
      $statuses = array(
        0=>'Open',
        1=>'Assigned',
        2=>'En Route',
        3=>'On Site',
        4=>'Completed',
        5=>'On Hold',
        6=>'Reviewing'
      );
    	if($r){
				while($i < $row_count){
					$Ticket = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
					if(is_array($Ticket) && $Ticket != array()){
            $Ticket['Status'] = $statuses[$Ticket['Assigned']];
						$data[] = $Ticket;
					}
					$i++;
				}
			}
      print json_encode(array('data'=>$data));
    }
  }
}?>