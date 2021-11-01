<?php
session_start();
require('../../../../cgi-bin/php/index.php');
setlocale(LC_MONETARY, 'en_US');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    $My_User = sqlsrv_query($NEI,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID = ?",array($_SESSION['User']));
    $My_User = sqlsrv_fetch_array($My_User);
    $Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
    $r = sqlsrv_query($NEI,"
        SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
        FROM   Privilege
        WHERE  User_ID = ?
    ;",array($_SESSION['User']));
    $My_Privileges = array();
    while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
    $Privileged = FALSE;
    if(isset($My_Privileges['Location']) && $My_Privileges['Location']['User_Privilege'] >= 4 && $My_Privileges['Location']['Group_Privilege'] >= 4 && $My_Privileges['Location']['Other_Privilege'] >= 4){$Privileged = TRUE;}
    elseif($My_Privileges['Location']['User_Privilege'] >= 4 && is_numeric($_GET['ID'])){
        $r = sqlsrv_query(  $NEI,"
        SELECT  *
        FROM    TicketO
        WHERE   TicketO.LID='{$_GET['ID']}'
                AND fWork='{$My_User['fWork']}'");
        $r2 = sqlsrv_query( $NEI,"
        SELECT  *
        FROM    TicketD
        WHERE   TicketD.Loc='{$_GET['ID']}'
                AND fWork='{$My_User['fWork']}'");
        $r3 = sqlsrv_query( $NEI,"
        SELECT  *
        FROM    TicketDArchive
        WHERE   TicketDArchive.Loc='{$_GET['ID']}'
                AND fWork='{$My_User['fWork']}'");
        $r = sqlsrv_fetch_array($r);
        $r2 = sqlsrv_fetch_array($r2);
        $r3 = sqlsrv_fetch_array($r3);
        $Privileged = (is_array($r) || is_array($r2) || is_array($r3)) ? TRUE : FALSE;
    }
    sqlsrv_query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "location.php"));
    if(!isset($array['ID'])  || !$Privileged || !is_numeric($_GET['ID'])){?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {
        $r = sqlsrv_query($NEI,
            "SELECT TOP 1
                    Loc.Loc              AS Location_ID,
                    Loc.ID               AS Location_Name,
                    Loc.Tag              AS Location_Tag,
                    Loc.Address          AS Location_Street,
                    Loc.City             AS Location_City,
                    Loc.State            AS Location_State,
                    Loc.Zip              AS Location_Zip,
                    Loc.Balance          as Location_Balance,
                    Zone.Name            AS Zone,
                    Loc.Route            AS Route_ID,
                    Emp.ID               AS Route_Mechanic_ID,
                    Emp.fFirst           AS Route_Mechanic_First_Name,
                    Emp.Last             AS Route_Mechanic_Last_Name,
                    Rol.Phone            AS Route_Mechanic_Phone_Number,
                    Loc.Owner            AS Customer_ID,
                    OwnerWithRol.Name    AS Customer_Name,
                    OwnerWithRol.Balance AS Customer_Balance,
                    OwnerWithROl.Address AS Customer_Street,
                    OwnerWithRol.City    AS Customer_City,
                    OwnerWithRol.State   AS Customer_State,
                    OwnerWithRol.Zip     AS Customer_Zip,
                    OwnerWithRol.Contact AS Customer_Contact,
                    Terr.Name            AS Territory_Domain,
                    Terr.Name            AS Territory_Name,
                    Loc.Custom8          AS Resident_Mechanic
            FROM    Loc
                    LEFT JOIN Zone         ON Loc.Zone   = Zone.ID
                    LEFT JOIN Route        ON Loc.Route  = Route.ID
                    LEFT JOIN Emp          ON Route.Mech = Emp.fWork
                    LEFT JOIN OwnerWithRol ON Loc.Owner  = OwnerWithRol.ID
                    LEFT JOIN Terr         ON Terr.ID    = Loc.Terr
                    LEFT JOIN Rol          ON Emp.Rol    = Rol.ID
            WHERE	Loc.Loc = ?
        ;",array($_GET['ID']));
        $Location = sqlsrv_fetch_array($r);
        $data = $Location;
?>
<div class="panel panel-primary" id='location-information'>
	<div class='panel-body' style='font-size:16px;padding:5px;'>
		<div class='row shadower' style='padding-top:10px;padding-bottom:10px;'>
			<?php
			//echo "Hi! " . $My_User['ID'];
			$Start_Date = date('Y-m') . "-01 00:00:00.000";
			if(date('m')==2) {
				$end = 28;
			}
			else { $end = 30;}
			$End_Date = date('Y-m') . "-" . $end . " 23:59:59.999";
			$r = sqlsrv_query($NEI,"
			SELECT Tickets.*,
				   Loc.ID                      AS Customer,
				   Loc.Tag                     AS Location,
				   Loc.Address                 AS Address,
				   Loc.Address                 AS Street,
				   Loc.City                    AS City,
				   Loc.State                   AS State,
				   Loc.Zip                     AS Zip,
				   Route.Name 		           AS Route,
				   Zone.Name 		           AS Division,
				   Loc.Maint 		           AS Maintenance,
				   Job.ID                      AS Job_ID,
				   Job.fDesc                   AS Job_Description,
				   OwnerWithRol.ID             AS Owner_ID,
				   OwnerWithRol.Name           AS Customer,
				   Elev.Unit                   AS Unit_Label,
				   Elev.State                  AS Unit_State,
				   Elev.fDesc				   AS Unit_Description,
				   Elev.Type 				   AS Unit_Type,
				   Emp.fFirst                  AS First_Name,
				   Emp.Last                    AS Last_Name,
				   Emp.fFirst + ' ' + Emp.Last AS Mechanic,
				   'Unknown'                   AS ClearPR,
				   JobType.Type                AS Job_Type
			FROM (
					(SELECT TicketO.ID       AS ID,
							TicketO.fDesc    AS Description,
							''               AS Resolution,
							TicketO.CDate    AS Created,
							TicketO.DDate    AS Dispatched,
							TicketO.EDate    AS Scheduled,
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
					 FROM   TicketO
							LEFT JOIN TickOStatus ON TicketO.Assigned = TickOStatus.Ref
					)
				) AS Tickets
				LEFT JOIN Loc          ON Tickets.Location = Loc.Loc
				LEFT JOIN Job          ON Tickets.Job      = Job.ID
				LEFT JOIN Elev         ON Tickets.Unit     = Elev.ID
				LEFT JOIN OwnerWithRol ON Tickets.Owner    = OwnerWithRol.ID
				LEFT JOIN Emp          ON Tickets.Mechanic = Emp.fWork
				LEFT JOIN JobType      ON Job.Type         = JobType.ID
				LEFT JOIN Zone 		   ON Zone.ID          = Loc.Zone
				LEFT JOIN Route		   ON Route.ID		   = Loc.Route
			WHERE Emp.ID = ?
			ORDER BY Tickets.ID DESC
		",array($_GET['ID']),array("Scrollable"=>SQLSRV_CURSOR_KEYSET));
		$data = array();
		$row_count = sqlsrv_num_rows( $r );
		if($r){
			while($i < $row_count){
				$Ticket = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
				if(is_array($Ticket) && $Ticket != array()){
					$Tags = array();
					if(strpos($Ticket['Description'],"s/d") || strpos($Ticket['Description'],"S/D") || strpos($Ticket['Description'],"shutdown")){
						$Tags[] = "Shutdown";
					}
					if($Ticket['Level'] == 10){
						$Tags[] = "Maintenance";
					}
					if($Ticket['Level'] == 1){
						$Tags[] = "Service Call";
					}
					$Ticket['Tags'] = implode(", ",$Tags);
					$data[] = $Ticket;
				}
				$i++;
			}

		}
		$row_count = sqlsrv_query($NEI,"
					SELECT Count(Tickets.ID) AS Open_Tickets
					FROM (SELECT ID FROM TicketO  WHERE TicketO.Assigned = '0' ) AS Tickets


		",array($_GET['ID']));
		//echo $r ? number_format(sqlsrv_fetch_array($row_count)['Open_Tickets']) : 0;

		$row_count2 =  sqlsrv_query($NEI,"
                    SELECT Count(Tickets.ID) AS Open_Tickets
                    FROM   (
                                (SELECT ID FROM TicketO  WHERE TicketO.Assigned = '0' )
                            ) AS Tickets

                ;",array($_GET['ID']));
		//echo $r ? number_format(sqlsrv_fetch_array($row_count2)['Open_Tickets']) : 0;
			   $open_tickets = number_format(sqlsrv_fetch_array($row_count2)['Open_Tickets']);

		$row_count3 =  sqlsrv_query($NEI,"
                    SELECT Count(Tickets.ID) AS Assigned_Tickets
                    FROM   (
                                (SELECT ID FROM TicketO  WHERE TicketO.Assigned = '1')
                            ) AS Tickets
                ;",array($_GET['ID']));
		//echo $r ? number_format(sqlsrv_fetch_array($row_count3)['Assigned_Tickets']) : 0;
				$assigned_tickets =  number_format(sqlsrv_fetch_array($row_count3)['Assigned_Tickets']);

		$r = sqlsrv_query($NEI,"
                    SELECT Tickets, Count(*) AS Count_of_Tickets
                    FROM   TicketO
                     GROUP BY TicketO.fWork
                ;",array($_GET['ID'],$_GET['ID'],$_GET['ID']));
                //echo $r ? number_format(sqlsrv_fetch_array($r)['Count_of_Tickets']) : 0;
				$total = number_format(sqlsrv_fetch_array($r)['Count_of_Tickets']);

			//echo (($open_tickets + $assigned_tickets) / $total );
	?>

			<div class='col-xs-4'><?php $Icons->Location(1);?> Name:</div>
			<div class='col-xs-8'><?php echo $Location['Location_Name'];?></div>
			<div class='col-xs-4'><?php $Icons->Blank(1);?> Tag:</div>
			<div class='col-xs-8'><?php echo $Location['Location_Tag'];?></div>
        </div>
        <div class='row shadower' style='padding-top:10px;padding-bottom:10px;'>
			<div class='col-xs-4'><?php $Icons->Address(1);?> Street:</div>
			<div class='col-xs-8'><?php echo strlen($Location['Location_Street']) ? $Location['Location_Street'] : "&nbsp;";?></div>
			<div class='col-xs-4'><?php $Icons->Blank(1);?> City:</div>
			<div class='col-xs-8'><?php echo strlen($Location['Location_City']) ? $Location['Location_City'] : "&nbsp;";?></div>
			<div class='col-xs-4'><?php $Icons->Blank(1);?> State:</div>
			<div class='col-xs-8'><?php echo strlen($Location['Location_State']) ? $Location['Location_State'] : "&nbsp;";?></div>
			<div class='col-xs-4'><?php $Icons->Blank(1);?> Zip:</div>
			<div class='col-xs-8'><?php echo strlen($Location['Location_Zip']) ? $Location['Location_Zip'] : "&nbsp;";?></div>
        </div>
        <div class='row shadower' style='padding-top:10px;padding-bottom:10px;'>
            <div class='col-xs-4'><?php $Icons->Territory(1);?> Territory:</div>
            <div class='col-xs-8'><?php echo isset($Location['Territory_Name']) && $Location['Territory_Name'] != '' ? $Location['Territory_Name'] : "&nbsp;";?></div>
			<?php if(isset($My_Privileges['Invoice']) && $My_Privileges['Invoice']['Other_Privilege'] >= 4){?><div class='col-xs-4'><?php $Icons->Collection(1);?> Balance:</div>
            <div class='col-xs-8'><?php echo isset($Location['Location_Balance']) && $Location['Location_Balance'] != '' ? money_format('%.2n',$Location['Location_Balance']) : "&nbsp;";?></div><?php }?>
        </div>
        <div class='row shadower' style='padding-top:10px;padding-bottom:10px;'>
			<div class='col-xs-4'><?php $Icons->Route();?> Route:</div>
            <div class='col-xs-8'><?php if($My_Privileges['Route']['Other_Privilege'] >= 4 || $My_User['ID'] == $Location['Route_Mechanic_ID']){?><a href="route.php?ID=<?php echo $Location['Route_ID'];?>"><?php }?><?php echo proper($Location["Route_Mechanic_First_Name"] . " " . $Location["Route_Mechanic_Last_Name"]);?><?php if($My_Privileges['Route']['Other_Privilege'] >= 4 || $My_User['ID'] == $Location['Route_Mechanic_ID']){?></a><?php }?>
			</div>
			<div class='col-xs-4'><?php $Icons->Resident(1);?> Resident:</div>
            <div class='col-xs-8'><?php echo isset($Location['Resident_Mechanic']) && $Location['Resident_Mechanic'] != '' ? proper($Location['Resident_Mechanic']) : "No";?></div>
            <?php if(isset($Location['Route_Mechanic_Phone_Number']) && strlen($Location['Route_Mechanic_Phone_Number']) > 0){?>
			<div class='col-xs-4'><?php $Icons->Phone();?> Phone:</div>
			<?php $number = $Location['Route_Mechanic_Phone_Number'];?>
			<div class='col-xs-8'><a href="tel:<?php echo $number;?>"><?php echo $number;?></a></div><?php }?>
			<?php /*<?php if(strlen($Location['Route_Mechanic_Email']) > 0){?><div class='col-xs-4'><?php $Icons->Email(1);?> Email:</div>
            <div class='col-xs-8'><a href="mailto:<?php echo $Location['Route_Mechanic_Email'];?>"><?php echo $Location['Route_Mechanic_Email'];?></a></div><?php }?>*/?>
			<div class='col-xs-4'><?php $Icons->Division(1);?> Division:</div>
            <div class='col-xs-8'><?php if($My_Privileges['Ticket']['Other_Privilege'] >= 4){?><a href="dispatch.php?Supervisors=Division%201&Mechanics=undefined&Start_Date=07/13/2017&End_Date=07/13/2017"><?php }?><?php echo proper($Location["Zone"]);?><?php if($My_Privileges['Ticket']['Other_Privilege'] >= 4){?></a><?php }?></div>
		</div>
        <div class='row shadower' style='padding-top:10px;padding-bottom:10px;'>
            <div class='col-xs-4'><?php $Icons->Unit(1);?> Units</div>
            <div class='col-xs-8'><?php
                $r = sqlsrv_query($NEI,"SELECT Count(ID) AS Count_of_Elevators FROM Elev WHERE Loc='{$_GET['ID']}';");
                echo $r ? number_format(sqlsrv_fetch_array($r)['Count_of_Elevators']) : 0;
            ?></div>
            <div class='col-xs-4'><?php $Icons->Job(1);?> Jobs</div>
            <div class='col-xs-8'><?php
                $r = sqlsrv_query($NEI,"SELECT Count(ID) AS Count_of_Jobs FROM Job WHERE Loc='{$_GET['ID']}' ;");
                echo $r ? number_format(sqlsrv_fetch_array($r)['Count_of_Jobs']) : 0;
            ?></div>
            <div class='col-xs-4'><?php $Icons->Violation(1);?> Violations</div>
            <div class='col-xs-8'><?php
                $r = sqlsrv_query($NEI,"SELECT Count(ID) AS Count_of_Jobs FROM Violation WHERE Loc='{$_GET['ID']}';");
                echo $r ? number_format(sqlsrv_fetch_array($r)['Count_of_Jobs']) : 0;
            ?></div>
            <div class='col-xs-4'><?php $Icons->Ticket(1);?> Tickets</div>
            <div class='col-xs-8'><?php
                $r = sqlsrv_query($NEI,"
                    SELECT Count(Tickets.ID) AS Count_of_Tickets
                    FROM   (
                                (SELECT ID FROM TicketO WHERE TicketO.LID = ?)
                                UNION ALL
                                (SELECT ID FROM TicketD WHERE TicketD.Loc = ?)
                                UNION ALL
                                (SELECT ID FROM TicketDArchive WHERE TicketDArchive.Loc = ?)
                            ) AS Tickets
                ;",array($_GET['ID'],$_GET['ID'],$_GET['ID']));
                echo $r ? number_format(sqlsrv_fetch_array($r)['Count_of_Tickets']) : 0;
            ?></div>
            <div class='col-xs-4'><?php $Icons->Proposal(1);?> Proposals</div>
            <div class='col-xs-8'><?php
                $r = sqlsrv_query($NEI,"
                    SELECT Count(Estimate.ID) AS Count_of_Tickets
                    FROM   Estimate
                    WHERE  Estimate.LocID = ?
                ;",array($_GET['ID']));
                echo $r ? number_format(sqlsrv_fetch_array($r)['Count_of_Tickets']) : 0;
            ?></div>
            <?php if(isset($My_Privileges['Invoice']) && $My_Privileges['Invoice']['Other'] >= 4){?>
            <div class='col-xs-4'><?php $Icons->Invoice(1);?>Collections</div>
            <div class='col-xs-8'><?php
                $r = sqlsrv_query($NEI,"
                    SELECT Count(Ref) AS Count_of_Invoices
                    FROM   OpenAR
                    WHERE  Loc='{$_GET['ID']}' AND Invoice.Status = 1;
                ;",array($_GET['ID']));
                echo $r ? number_format(sqlsrv_fetch_array($r)['Count_of_Invoices']) : 0;
            ?></div><?php }?>
			<?php if(isset($My_Privileges['Legal']) && $My_Privileges['Legal'] >=4 ) {?>
            <div class='col-xs-4'><?php $Icons->Legal(1);?> Lawsuits</div>
            <div class='col-xs-8'><?php
                $r = sqlsrv_query($NEI,"
                    SELECT Count(ID) AS Count_of_Legal_Jobs
                    FROM   Job
                    WHERE  Job.Loc = ?
                           AND (Job.Type = 9
                             OR Job.Type = 12)
                ;",array($_GET['ID']));
                echo $r ? number_format(sqlsrv_fetch_array($r)['Count_of_Legal_Jobs']) : 0;

            ?></div>


			<?php }?>
        </div>
	</div>
</div>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
