<?php
session_start();
require('../../../php/index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Elevator'){
        $r= sqlsrv_query($NEI,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID= ?",array($_SESSION['User']));
        $My_User = sqlsrv_fetch_array($r);
        $Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
        $r = sqlsrv_query($Portal,"
            SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
            FROM   Portal.dbo.Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
        $My_Privileges = array();
        while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
        $Privileged = FALSE;
        if(isset($My_Privileges['Violation']) && $My_Privileges['Unit']['User_Privilege'] >= 4 && $My_Privileges['Violation']['Group_Privilege'] >= 4 && $My_Privileges['Violation']['Other_Privilege'] >= 0){$Privileged = TRUE;}
        else {
            //NEEDS TO INCLUDE SECURITY FOR OTHER PRIVILEGE
        }
    } elseif($_SESSION['Branch'] == 'Customer' && is_numeric($_GET['ID'])){
        $SQL_Result = sqlsrv_query($NEI,"
            SELECT Loc.Owner
            FROM Violation LEFT JOIN nei.dbo.Loc ON Violation.Loc = Loc.Loc
            WHERE Violation.ID='{$_GET['ID']}' AND Loc.Owner='{$_SESSION['Branch_ID']}'
        ;");
        if($SQL_Result){
            $sql = sqlsrv_fetch_array($SQL_Result);
            if($sql){$Privileged = true;}
        }
    }
    sqlsrv_query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "violation.php"));
    if(!isset($array['ID']) && is_numeric($_GET['ID'])  || !$Privileged){?><html><head><script>document.location.href="../login.php?Forward=violation<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {
        $ID = addslashes($_GET['ID']);
        $r = sqlsrv_query($NEI,
            "SELECT TOP 1
                Violation.*,
				Violation.Remarks       AS Remarks,
                Loc.Loc                 AS Location_ID,
                Loc.ID                  AS Location_Name,
                Loc.Tag                 AS Location_Tag,
                Loc.Address             AS Street,
                Loc.City                AS City,
                Loc.State               AS State,
                Loc.Zip                 AS Zip,
                Zone.Name               AS Zone,
                Route.ID                AS Route_ID,
                Route_Mechanic.fFirst   AS Route_Mechanic_First_Name,
                Route_Mechanic.Last     AS Route_Mechanic_Last_Name,
                TicketD.ID              AS TicketD_ID,
                TicketD.fDesc           AS Ticket_Description,
                TicketD.DescRes         AS Ticket_Resolution,
                Elev.ID                 AS Unit_ID,
                Elev.Unit               AS Unit_Label,
                Elev.State              AS Unit_State,
                Elev.Cat                AS Unit_Category,
                Elev.Type               AS Unit_Type,
                Elev.Since              AS Unit_Since,
                Elev.Last               AS Unit_Last,
                Elev.Price              AS Unit_Price,
                Elev.fDesc              AS Unit_Description,
                Job.ID                  AS Job_ID,
                Job.fDesc               AS Job_Description,
                Job.Status              AS Job_Status,
                Job.Remarks             AS Job_Remarks,
				JobType.Type 			AS Job_Type,
				Job.fDate 				AS Job_Date,
				Loc.Tag					AS Location_Tag,
				Loc.Loc					AS Location_ID,
				Loc.Address	 			AS Location_Street,
				Loc.City				AS Location_City,
				Loc.State				AS Location_State,
				Loc.Zip					AS Location_Zip,
				OwnerWithRol.ID			AS Customer_ID,
				OwnerWithRol.Name		AS Customer_Name
            FROM
                Violation
                LEFT JOIN nei.dbo.Loc                   ON Violation.Loc 	= Loc.Loc
                LEFT JOIN nei.dbo.Elev                  ON Violation.Elev 	= Elev.ID
                LEFT JOIN nei.dbo.Job                   ON Violation.Job 	= Job.ID
                LEFT JOIN TicketD               		ON Violation.Ticket = TicketD.ID
                LEFT JOIN nei.dbo.Zone                  ON Loc.Zone 		= Zone.ID
                LEFT JOIN nei.dbo.Route                 ON Loc.Route 		= Route.ID
                LEFT JOIN nei.dbo.Emp AS Route_Mechanic ON Route.Mech 		= Route_Mechanic.fWork
                LEFT JOIN nei.dbo.Emp AS Mechanic       ON TicketD.fWork 	= Mechanic.fWork
				LEFT JOIN nei.dbo.JobType 				ON Job.Type 		= JobType.ID
				LEFT JOIN nei.dbo.OwnerWithRol 			ON OwnerWithRol.ID 	= Loc.Owner
            WHERE
                Violation.ID = ?
		;",array($ID));
        $data = sqlsrv_fetch_array($r);
		$Violation = $data;
?>
<div class="panel panel-primary">
	<div class="panel-heading"> <i class="nav-icon"><?php $Icons -> Info(1)?></i>Basic Information</div>
	<div class='panel-body' style='padding:15px;'>
		<div style='font-size:24px;text-decoration:underline;'>
			<div class='col-xs-12'><?php $Icons->Violation();?> Violation #<?php echo $data['ID'];?></div>
			<div class='col-xs-12'><a href='location.php?ID=<?php echo $data['Location_ID'];?>'><?php $Icons->Location();?> <?php echo $data['Location_Tag'];?></a></div>
			<?php if(isset($Violation['Job_ID']) && is_int($Violation['Job_ID']) && $Violation['Job_ID'] >= 0){?><div class='col-xs-12'><a href='job.php?ID=<?php echo $data['Job_ID'];?>'><?php $Icons->Job();?> <?php echo $data['Job_Description'];?></a></div><?php }?>
			<div class='col-xs-12'><?php $Icons->Calendar_Plus();?> Created: <?php echo date("m/d/Y",strtotime($Violation['fdate']));?></div>
			<div class='col-xs-12'>
			<?php $Icons->Calendar();?> Due Date: <?php
				preg_match_all("/DUE: ((0?[13578]|10|12)(-|\/)(([1-9])|(0[1-9])|([12])([0-9]?)|(3[01]?))(-|\/)((19)([2-9])(\d{1})|(20)([01])(\d{1})|([8901])(\d{1}))|(0?[2469]|11)(-|\/)(([1-9])|(0[1-9])|([12])([0-9]?)|(3[0]?))(-|\/)((19)([2-9])(\d{1})|(20)([01])(\d{1})|([8901])(\d{1})))/",$data['Remarks'],$matches);
				$string = $matches[1][0];
				$string = str_replace("/","/",$string);
				$data['Due_Date'] = $string;
				echo strlen($data['Due_Date']) > 0 ? $data['Due_Date'] : 'Unknown';
			?>
			</div>
			<div class='col-xs-12'><span style='float:left;'><?php $Icons->Note();?></span><pre style='float:left;'><?php echo strlen($data["Remarks"]) ? proper($data['Remarks']) : "Unlisted";?></pre></div>
		</div>
	</div>
		<?php if(isset($Violation['Job_ID']) && is_int($Violation['Job_ID']) && $Violation['Job_ID'] >= 0){?>
	<div class="panel-heading"><i class="nav-icon"><?php $Icons -> Job(1)?></i>Job Information</div>
	<div class='panel-body' style='padding:15px;'>
    <div class='row'>
			<div class='col-xs-4' style='text-align:right;'>Job:</div>
			<div class='col-xs-8'><a href="<?php echo (strlen($Violation['Job_ID']) > 0) ? 'job.php?ID=' . $Violation['Job_ID'] : '#';?>"><?php echo strlen($data['Job_ID']) > 0 ? $data['Job_ID'] : "Unlisted";?></a></div>
			<div class='col-xs-4' style='text-align:right;'>Type:</div>
			<div class='col-xs-8'><?php echo strlen($data["Job_Type"]) ? proper($data['Job_Type']) : "Unlisted";?></div>
			<div class='col-xs-4' style='text-align:right;'>Description:</div>
			<div class='col-xs-8'><?php echo strlen($data['Job_Description']) > 0 ? $data['Job_Description'] : "Unlisted";?></div>
			<div class='col-xs-4' style='text-align:right;'>Date:</div>
			<div class='col-xs-8'><?php echo strlen($data["Job_Date"]) > 0 ? $data['Job_Date'] : "Unlisted";?></div>
			<div class='col-xs-4' style='text-align:right;'>Tickets:</div>
			<div class='col-xs-8'><?php
				if($data['Job_ID'] > 0){
					$r = sqlsrv_query($NEI,"
						SELECT Count(Tickets.ID) AS Count_of_Tickets
						FROM   ((SELECT TicketO.ID AS ID
								 FROM   nei.dbo.TicketO
								 WHERE  TicketO.Job = ?)
								UNION ALL
								(SELECT TicketD.ID AS ID
								 FROM   nei.dbo.TicketD
								 WHERE  TicketO.Job = ?)
								UNION ALL
								(SELECT TicketDArchive.ID AS ID
								 FROM   nei.dbo.TicketDArchive
								 WHERE  TicketDArchive.Job = ?)) AS Tickets
					;",array($data['Job_ID'],$data['Job_ID'],$data['Job_ID']));
					echo $r ? sqlsrv_fetch_array($r)['Count_of_Tickets'] : 0;
				} else {
					echo 0;
				}
			?></div>
  	</div>
  </div>
		<?php }?>
  <div class="panel-heading">
  	<i class="nav-icon"><?php $Icons -> Unit(1)?></i>
  	<a href="<?php echo (strlen($data['Unit_ID']) > 0) ? 'unit.php?ID=' . $Violation['Unit_ID'] : '#';?>" style="color: white">Unit Information</div>
  <div class='panel-body' style='padding:15px;'>
  		<div class='row'>
  			<div class='col-xs-4' style='text-align:right;'>Unit:</div>
  			<div class='col-xs-8'><a href="<?php echo (strlen($data['Unit_ID']) > 0) ? 'unit.php?ID=' . $Violation['Unit_ID'] : '#';?>"><?php echo strlen($data["Unit_Label"]) > 0 ? $data['Unit_Label'] : "Unlisted";?></a></div>
  		</div>
  		<div class='row'>
  			<div class='col-xs-4' style='text-align:right;'>State:</div>
  			<div class='col-xs-8'><a href="<?php echo (strlen($data['Unit_ID']) > 0) ? 'unit.php?ID=' . $Ticket['Unit_ID'] : '#';?>"><?php echo strlen($data["Unit_State"]) > 0 ? $data['Unit_State'] : "Unlisted";?></a></div>
  		</div>
  		<div class='row'>
  			<div class='col-xs-4' style='text-align:right;'>Type:</div>
  			<div class='col-xs-8'><a href="<?php echo (strlen($data['Unit_ID']) > 0) ? 'unit.php?ID=' . $Ticket['Unit_ID'] : '#';?>"><?php echo strlen($data["Unit_Type"]) > 0 ? proper($data['Unit_Type']) : "Unlisted";?></a></div>
  		</div>
  </div>
	<div class="panel-heading"><i class="fa fa-map fa-fw"></i> Location Details</div>
	<div class="panel-body" style='padding:15px;'>
		<div class='row'>
			<div class='col-xs-4' style='text-align:right;'>Customer:</div>
			<div class='col-xs-8'><?php if(!$Field){?><a href="<?php echo (strlen($data['Customer_ID']) > 0) ? 'customer.php?ID=' . $data['Customer_ID'] : '#';?>"><?php echo (strlen($data['Customer_Name']) > 0) ? $data["Customer_Name"] : 'Unlisted';?></a><?php } else {?><?php echo (strlen($data['Customer_Name']) > 0) ? $data["Customer_Name"] : 'Unlisted';?><?php }?></div>
		</div>
		<div class='row'>
			<div class='col-xs-4' style='text-align:right;'>Name:</div>
			<div class='col-xs-8'><a href="<?php echo (strlen($data['Location_ID']) > 0) ? 'location.php?ID=' . $data['Location_ID'] : '#';?>"><?php echo (strlen($data['Location_Tag']) > 0) ? $data["Location_Tag"] : 'Unlisted';?></a></div>
		</div>
		<div class='row'>
			<div class='col-xs-4' style='text-align:right;'>Street:</div>
			<div class='col-xs-8'><?php echo (strlen($data['Location_Street']) > 0) ? proper($data["Location_Street"]) : 'Unlisted';?></div>
		</div>
		<div class='row'>
			<div class='col-xs-4' style='text-align:right;'>City:</div>
			<div class='col-xs-8'><?php echo (strlen($data['Location_City']) > 0) ? proper($data["Location_City"]) : 'Unlisted';?></div>
		</div>
		<div class='row'>
			<div class='col-xs-4' style='text-align:right;'>State:</div>
			<div class='col-xs-8'><?php echo (strlen($data['Location_State']) > 0) ? $data["Location_State"] : 'Unlisted';?></div>
		</div>
		<div class='row'>
			<div class='col-xs-4' style='text-align:right;'>Zip:</div>
			<div class='col-xs-8'><?php echo (strlen($data['Location_Zip']) > 0) ? $data["Location_Zip"] : 'Unlisted';?></div>
		</div>
	</div>
</div>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=violation<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
