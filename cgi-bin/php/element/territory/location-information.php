<?php 
session_start();
require('../../../php/index.php');
setlocale(LC_MONETARY, 'en_US');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Elevator'){
        $My_User = sqlsrv_query($NEI,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID = ?",array($_SESSION['User']));
        $My_User = sqlsrv_fetch_array($My_User); 
        $Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
        $r = sqlsrv_query($Portal,"
            SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
            FROM   Portal.dbo.Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
        $My_Privileges = array();
        while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
        $Privileged = FALSE;
        if(isset($My_Privileges['Territory']) && $My_Privileges['Territory']['User_Privilege'] >= 4 && $My_Privileges['Territory']['Group_Privilege'] >= 4 && $My_Privileges['Territory']['Other_Privilege'] >= 4){$Privileged = TRUE;}
    }
	if(is_numeric($_GET['ID'])){sqlsrv_query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "territory.php?ID=" . $_GET['ID']));}
    if(!isset($array['ID'])  || !$Privileged || !is_numeric($_GET['ID'])){?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {
        $r = sqlsrv_query($NEI,
            "SELECT TOP 1
                    Terr.ID   AS Territory_ID,
					Terr.Name AS Territory_Name
			FROM    nei.dbo.Terr
			WHERE   Terr.ID = ?
        ;",array($_GET['ID']));
        $Territory = sqlsrv_fetch_array($r);
        ?>
<div class="panel panel-primary" id='location-information'>
	<div class='panel-body white-background' style='font-size:16px;padding:5px;'>
		<div class='row shadower' style='padding-top:10px;padding-bottom:10px;'>
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
            <?php if(isset($Location['Route_Mechanic_Phone_Number']) && strlen($Location['Route_Mechanic_Phone_Number']) > 0){?><div class='col-xs-4'><?php $Icons->Phone();?> Phone:</div>
			<div class='col-xs-8'><?php echo $Location['Route_Mechanic_Phone_Number'];?></div><?php }?>
			<?php if(strlen($Location['Route_Mechanic_Email']) > 0){?><div class='col-xs-4'><?php $Icons->Email(1);?> Email:</div>
            <div class='col-xs-8'><?php echo $Location['Route_Mechanic_Email'];?></div><?php }?>
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
                $r = sqlsrv_query($NEI,"SELECT Count(ID) AS Count_of_Jobs FROM Job WHERE Loc='{$_GET['ID']}';");
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
                    FROM   nei.dbo.Estimate
                    WHERE  Estimate.LocID = ?
                ;",array($_GET['ID']));
                echo $r ? number_format(sqlsrv_fetch_array($r)['Count_of_Tickets']) : 0;
            ?></div>
            <div class='col-xs-4'><?php $Icons->Invoice(1);?> Invoices</div>
            <div class='col-xs-8'><?php 
                $r = sqlsrv_query($NEI,"
                    SELECT Count(Ref) AS Count_of_Invoices 
                    FROM   nei.dbo.Invoice 
                    WHERE  Loc='{$_GET['ID']}';
                ;",array($_GET['ID']));
                echo $r ? number_format(sqlsrv_fetch_array($r)['Count_of_Invoices']) : 0;
            ?></div>
			<?php if(isset($My_Privileges[Legal]) && $My_Privileges[Legal]>=4 ) {?>
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
            ?></div><?php }?>
        </div>
	</div>
</div>		
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>