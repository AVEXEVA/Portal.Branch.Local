<?php
session_start();
ini_set('memory_limit','512M');
require('index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    if(!isset($array['ID'])){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
        $r = sqlsrv_query($NEI,"
				SELECT Tickets.ID        AS ID,
					   Tickets.*,
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
								TicketO.fDesc    AS Description,
								''               AS Resolution,
								TicketO.CDate    AS Created,
								TicketO.DDate    AS Dispatched,
								TicketO.EDate    AS Date,
								TicketO.TimeSite AS On_Site,
								TicketO.TimeComp AS Completed,
								TicketO.Who 	 AS Caller,
								TicketO.fBy      AS Reciever,
								TicketO.Level    AS Level,
								TicketO.Cat      AS Category,
								TicketO.LID      AS Location,
								TicketO.Job      AS Job,
								TicketO.LElev    AS Unit,
								TicketO.Owner    AS Owner,
								TicketO.fWork    AS Mechanic,
								TickOStatus.Type AS Status,
								0                AS Total,
								0                AS Regular,
								0                AS Overtime,
								0                AS Doubletime
						 FROM   nei.dbo.TicketO
						        LEFT JOIN nei.dbo.TickOStatus ON TicketO.Assigned = TickOStatus.Ref
						 WHERE  TicketO.Level = 1
						)
					) AS Tickets
					LEFT JOIN nei.dbo.Loc          ON Tickets.Location = Loc.Loc
					LEFT JOIN nei.dbo.Job          ON Tickets.Job      = Job.ID
					LEFT JOIN nei.dbo.Elev         ON Tickets.Unit     = Elev.ID
					LEFT JOIN nei.dbo.OwnerWithRol ON Tickets.Owner    = OwnerWithRol.ID
					LEFT JOIN nei.dbo.Emp          ON Tickets.Mechanic = Emp.fWork
					LEFT JOIN nei.dbo.JobType      ON Job.Type         = JobType.ID
		;",array(),array(
			"Scrollable"=>SQLSRV_CURSOR_KEYSET
		));
    
		$data = array();
        if($r){
			$row_count = sqlsrv_num_rows( $r );
			while($i < $row_count){
				$Ticket = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
				if(is_array($Ticket) && $Ticket != array()){
					$data[] = $Ticket;
				}
				$i++;
			}
		}
        print json_encode(array('data'=>$data));
    }
}
sqlsrv_close($NEI);
?>
