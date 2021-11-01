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
?><div class="panel panel-primary" id='location-information'>
	<div class='panel-body white-background' style='font-size:16px;padding:5px;'>
		<div class='row shadower' style='padding-top:10px;padding-bottom:10px;'>
			<div class='col-xs-4'><?php $Icons->Territory(1);?> Name:</div>
			<div class='col-xs-8'><?php echo $Territory['Territory_Name'];?></div>
        </div>
        <div class='row shadower' style='padding-top:10px;padding-bottom:10px;'>
            <div class='col-xs-4'><?php $Icons->Unit(1);?> Units</div>
            <div class='col-xs-8'><?php
                $r = sqlsrv_query($NEI,"
					SELECT Count(Elev.ID) AS Count_of_Elevators
					FROM   nei.dbo.Elev
						   LEFT JOIN nei.dbo.Loc ON Elev.Loc = Loc.Loc
					WHERE  Loc.Terr = ?
						   AND Elev.Status = 0
						   AND Loc.Maint = 1
				;",array($_GET['ID']));
                echo $r ? number_format(sqlsrv_fetch_array($r)['Count_of_Elevators']) : 0;
            ?></div>
            <div class='col-xs-4'><?php $Icons->Job(1);?> Jobs</div>
            <div class='col-xs-8'><?php
                $r = sqlsrv_query($NEI,"
					SELECT Count(Job.ID) AS Count_of_Jobs
					FROM   nei.dbo.Job
						   LEFT JOIN nei.dbo.Loc ON Job.Loc = Loc.Loc
					WHERE Loc.Terr = ?
						  AND Job.Status = 0
				;",array($_GET['ID']));
                echo $r ? number_format(sqlsrv_fetch_array($r)['Count_of_Jobs']) : 0;
            ?></div>
            <div class='col-xs-4'><?php $Icons->Violation(1);?> Violations</div>
            <div class='col-xs-8'><?php
                $r = sqlsrv_query($NEI,"
					SELECT Count(Violation.ID) AS Count_of_Jobs
					FROM   nei.dbo.Violation
						   LEFT JOIN nei.dbo.Loc ON Violation.Loc = Loc.Loc
					WHERE Loc.Terr = ?
				;",array($_GET['ID']));
                echo $r ? number_format(sqlsrv_fetch_array($r)['Count_of_Jobs']) : 0;
            ?></div>
            <div class='col-xs-4'><?php $Icons->Ticket(1);?> Tickets</div>
            <div class='col-xs-8'><?php
                $r = sqlsrv_query($NEI,"
                    SELECT Count(Tickets.ID) AS Count_of_Tickets
                    FROM   (
                                (
									SELECT TicketO.ID
									FROM   nei.dbo.TicketO
										   LEFT JOIN nei.dbo.Loc ON TicketO.LID = Loc.Loc
									WHERE  Loc.Terr = ?
								)
                                UNION ALL
                                (
									SELECT TicketD.ID
									FROM   nei.dbo.TicketD
										   LEFT JOIN nei.dbo.Loc ON TicketD.Loc = Loc.Loc
									WHERE  Loc.Terr = ?
								)
                                UNION ALL
                                (
									SELECT TicketDArchive.ID
									FROM   nei.dbo.TicketDArchive
										   LEFT JOIN nei.dbo.Loc ON TicketDArchive.Loc = Loc.Loc
									WHERE  Loc.Terr = ?)
                            ) AS Tickets
                ;",array($_GET['ID'],$_GET['ID'],$_GET['ID']));
                echo $r ? number_format(sqlsrv_fetch_array($r)['Count_of_Tickets']) : 0;
            ?></div>
            <div class='col-xs-4'><?php $Icons->Proposal(1);?> Proposals</div>
            <div class='col-xs-8'><?php
                $r = sqlsrv_query($NEI,"
                    SELECT Count(Estimate.ID) AS Count_of_Tickets
                    FROM   nei.dbo.Estimate
						   LEFT JOIN nei.dbo.Loc ON Estimate.LocID = Loc.Loc
                    WHERE  Loc.Terr = ?
                ;",array($_GET['ID']));
                echo $r ? number_format(sqlsrv_fetch_array($r)['Count_of_Tickets']) : 0;
            ?></div>
            <div class='col-xs-4'><?php $Icons->Invoice(1);?> Invoices</div>
            <div class='col-xs-8'><?php
                $r = sqlsrv_query($NEI,"
                    SELECT Count(Invoice.Ref) AS Count_of_Invoices
                    FROM   nei.dbo.Invoice
						   LEFT JOIN nei.dbo.Loc ON Invoice.Loc = Loc.Loc
                    WHERE  Loc.Terr = ?;
                ;",array($_GET['ID']));
                echo $r ? number_format(sqlsrv_fetch_array($r)['Count_of_Invoices']) : 0;
            ?></div>
			<?php if(isset($My_Privileges[Legal]) && $My_Privileges[Legal]>=4 ) {?>
            <div class='col-xs-4'><?php $Icons->Legal(1);?> Lawsuits</div>
            <div class='col-xs-8'><?php
                $r = sqlsrv_query($NEI,"
                    SELECT Count(Job.ID) AS Count_of_Legal_Jobs
                    FROM   nei.dbo.Job
						   LEFT JOIN nei.dbo.Loc ON Job.Loc = Loc.Loc
                    WHERE  Loc.Terr = ?
                           AND (Job.Type = 9
                             OR Job.Type = 12)
                ;",array($_GET['ID']));
                echo $r ? number_format(sqlsrv_fetch_array($r)['Count_of_Legal_Jobs']) : 0;
            ?></div><?php }?>
			<?php if(isset($My_Privileges[Legal]) && $My_Privileges[Legal]>=4 ) {?>
			<div class='col-xs-4'><?php $Icons->Collection(1);?> Total Balance</div>
            <div class='col-xs-8'><?php
                $r = sqlsrv_query($NEI,"
                    SELECT Sum(Loc.Balance) AS Terr_Sum
                    FROM   nei.dbo.Loc
                    WHERE  Loc.Terr = ?
                ;",array($_GET['ID']));
                echo $r ?"$" . number_format(sqlsrv_fetch_array($r)['Terr_Sum']) : 0;
            ?></div><?php }?>
        </div>
	</div>
</div>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
