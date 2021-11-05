<?php
session_start( [ 'read_and_close' => true ] );
ini_set('memory_limit','512M');
require('index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = $database->query(null,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    if(!isset($array['ID'])){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
        $hour_ago = date("1899-12-30 H:i:s", strtotime('-90 minutes'));
        $r = $database->query(null,"
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
					   JobType.Type      AS Job_Type,
             Zone.Name         AS Division
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
          LEFT JOIN nei.dbo.Zone         ON Loc.Zone         = Zone.ID
      WHERE Tickets.On_Site <= ?
            AND Tickets.Level = 1
            AND Tickets.Status = 'On Site'
            AND Tickets.Date >= ?
            AND Job.fDesc IS NOT NULL
            AND Zone.Name <> 'BASE'
		;",array($hour_ago, date("Y-m-d 00:00:00.000",strtotime('now'))),array(
			"Scrollable"=>SQLSRV_CURSOR_KEYSET
		));
    if( ($errors = sqlsrv_errors() ) != null) {
        foreach( $errors as $error ) {
            echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
            echo "code: ".$error[ 'code']."<br />";
            echo "message: ".$error[ 'message']."<br />";
        }
    }
		$data = array();
        if($r){
			$row_count = sqlsrv_num_rows( $r );
			while($i < $row_count){
				$Ticket = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
				if(is_array($Ticket) && $Ticket != array()){
          $Ticket['On_Site'] = date("H:i A",strtotime($Ticket['On_Site']));
					$data[] = $Ticket;
				}
				$i++;
			}
		}
        print json_encode(array('data'=>$data));
    }
}
sqlsrv_close(null);
?>
