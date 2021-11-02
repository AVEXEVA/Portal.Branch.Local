<?php 
session_start();
setlocale(LC_MONETARY, 'en_US');
require('cgi-bin/php/index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Elevator'){
        sqlsrv_query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "customer.php"));
        $r= sqlsrv_query($NEI,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID= ?",array($_SESSION['User']));
        $My_User = sqlsrv_fetch_array($r);
        $Field = ($User['Field'] == 1 && $User['Title'] != 'OFFICE') ? True : False;
        $r = sqlsrv_query($Portal,"
            SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
            FROM   Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
        $My_Privileges = array();
        while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
        $Privileged = FALSE;
        if(isset($My_Privileges['Customer']) && $My_Privileges['Customer']['User_Privilege'] >= 4 && $My_Privileges['Customer']['Group_Privilege'] >= 4 && $My_Privileges['Customer']['Other_Privilege'] >= 4){
            sqlsrv_query($Portal,"INShERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "customer.php"));
            $Privileged = TRUE;}
        elseif($My_Privileges['Customer']['User_Privilege'] >= 4 && $My_Privileges['Ticket']['Group_Privilege'] >= 4 ){
            sqlsrv_query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "customer.php"));
            $r = sqlsrv_query(  $NEI,"
                SELECT TicketO.ID AS ID 
                FROM nei.dbo.TicketO LEFT JOIN nei.dbo.Loc ON TicketO.LID = Loc.Loc
                WHERE Loc.Owner = ?;",array($_GET['ID']));
            $r2 = sqlsrv_query(  $NEI,"
                SELECT TicketD.ID AS ID 
                FROM nei.dbo.TicketD LEFT JOIN nei.dbo.Loc ON TicketD.Loc = Loc.Loc
                WHERE Loc.Owner = ?;",array($_GET['ID']));
            $Privileged = (is_array(sqlsrv_fetch_array($r)) || is_array(sqlsrv_fetch_array($r2))) ? TRUE : FALSE;}
    } elseif($_SESSION['Branch'] == 'Customer' && $_SESSION['Branch_ID'] == $_GET['ID']){$Privileged = TRUE;}
    //
    if(!isset($array['ID'])  || !is_numeric($_GET['ID']) || !$Privileged || !is_numeric($_GET['ID'])){?><html><head><script>document.location.href="../login.php?Forward=customer<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script>
<meta charset="utf-8">
</head></html><?php }
    else {
        $r = sqlsrv_query($NEI,
            "SELECT TOP 1
                    OwnerWithRol.ID      AS Customer_ID,
                    OwnerWithRol.Name    AS Name,
                    OwnerWithRol.Address AS Street,
                    OwnerWithRol.City    AS City,
                    OwnerWithRol.State   AS State,
                    OwnerWithRol.Zip     AS Zip,
                    OwnerWithRol.Status  AS Status
            FROM    nei.dbo.OwnerWithRol
                    LEFT JOIN Portal.dbo.Master_Account ON OwnerWithRol.ID = Master_Account.Customer
            WHERE   Master_Account.Master = ?
		;",array($_GET['ID']));
        $Customer = sqlsrv_fetch_array($r);
        $job_result = sqlsrv_query($NEI,"
            SELECT 
                Job.ID AS ID
            FROM 
                nei.dbo.Job 
                LEFT JOIN Portal.dbo.Master_Account ON Job.Owner = Master_Account.Customer
            WHERE 
                Master_Account.Master = ?
        ;",array($_GET['ID']));
        if($job_result){
            $Jobs = array();
            $dates = array();
            $totals = array();
            while($array = sqlsrv_fetch_array($job_result)){$Jobs[] = "[JOBLABOR].[JOB #]='{$array['ID']}'";}
            $SQL_Jobs = implode(" OR ",$Jobs);
        }?><!DOCTYPE html>
<html lang="en">
<head>
    <?php require(PROJECT_ROOT.'php/meta.php');?>    <title>Nouveau Texas | Portal</title>    
    <?php require(PROJECT_ROOT."css/index.php");?>
    <?php require(PROJECT_ROOT.'js/index.php');?>
    <style>
    </style>
</head>
<body onload='finishLoadingPage();'>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require(PROJECT_ROOT.'php/element/navigation/index2.php');?>
        <?php require(PROJECT_ROOT.'php/element/loading.php');?>
        <script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=AIzaSyCNrTryEaTEDRz-XDSg890ajL_JRPnLgzc"></script>
        <div id="page-wrapper" class='content' style='<?php if(isset($_SESSION['Branch']) && $_SESSION['Branch'] == 'Customer'){?>margin:0px !important;<?php }?>'>
            <div class='row'>
                <div class="col-lg-12">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <h3><?php $Icons->Customer();?>Account: <?php echo $Customer['Name'];?>
                                <?php if($Customer['Status'] == 0 && FALSE){?><img src='../Images/Icons/Inactive.png' style='height:35px;' /><?php }?>
                            </h3>
                        </div>
                        <div class="panel-body">
                            <ul class="nav nav-tabs">
                                <li class="active"><a href="#basic-pills" data-toggle="tab"><?php $Icons->Info();?>Basic</a></li>
                                <li class=''><a href="#locations-pills"  data-toggle="tab"><?php $Icons->Location();?>Locations</a></li>
                                <li class=''><a href="#units-pills"      data-toggle="tab"><?php $Icons->Unit();?>Units</a></li>
                                <li class=''><a href="#jobs-pills"       data-toggle="tab"><?php $Icons->Job();?>Jobs</a></li>
                                <li class='' onClick="setTimeout(function(){initialize()},1000);"><a href="#tickets-pills"    data-toggle="tab"><?php $Icons->Ticket();?>Tickets</a></li>
                                <li><a href="#maintenance-pills" data-toggle="tab"><?php $Icons->Maintenance();?> Maintenance</a></li>
                                <?php if((!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Elevator') && (isset($My_Privileges['Financials']) && $My_Privileges['Financials']['Other_Privilege'] >= 4)){?><li class=''><a href="#financials-pills" data-toggle="tab"><?php $Icons->Financial();?>Financials</a></li><?php }?>
                                <li class=''><a href="#violations-pills" data-toggle="tab"><?php $Icons->Violation();?>Violations</a></li>
                                <li class=''><a href="#workers-pills" data-toggle="tab"><?php $Icons->User();?>Workers</a></li>
                                <li class=''><a href="#contracts-pills"  data-toggle="tab"><?php $Icons->Contract();?>Contracts</a></li>
                                <li class=''><a href="#proposals-pills"  data-toggle="tab"><?php $Icons->Proposal();?>Proposals</a></li>
                                <li class=''><a href="#invoices-pills"   data-toggle="tab"><?php $Icons->Invoice();?>Invoices</a></li>
                                <li class=''><a href="#collections-pills"  data-toggle="tab"><?php $Icons->Collection();?>Collections</a></li>
                                <!--<?php if(isset($My_Privileges['Legal']) && $My_Privileges['Legal']['User_Privilege'] >= 4 && $My_Privileges['Legal']['Group_Privilege'] >= 4 && $My_Privileges['Legal']['Other_Privilege'] >= 4){?><li class=''><a href="#legal-pills"  data-toggle="tab"><?php $Icons->Legal();?>Legal</a></li><?php }?>-->
                            </ul>
                            <br />
                            <div class="tab-content">
                                <div class="tab-pane fade in active" id="basic-pills">
                                    <div class='row'>
                                        <div class='col-md-4'>
                                            <div class='row' style='padding-left:25px;'>
                                                <div class='row'><div class='col-xs-12'><b><u>Customer Contact</u></b></div></div>
                                                <div class='row'><div class='col-xs-12'><?php echo $Customer['Name'];?></div></div>
                                                <div class='row'><div class='col-xs-12'><?php echo $Customer["Street"];?></div></div>
                                                <div class='row'><div class='col-xs-12'><?php echo $Customer["City"];?> <?php echo $Customer["State"];?> <?php echo $Customer["Zip"];?></div></div>
                                            </div>
                                            <br />
                                            <div class='row' style='padding-left:25px;'>
                                                <div class='row'><div class='col-xs-12'><b><u>Customer Information</u></b></div></div>
                                                <div class='row'><div class='col-xs-12'><?php $Icons->Location();
                                                    $r = sqlsrv_query($NEI,"
                                                        SELECT Count(Loc.Loc) as Count_of_Locations
                                                        FROM nei.dbo.Loc LEFT JOIN Portal.dbo.Master_Account ON Loc.Owner = Master_Account.Customer
                                                        WHERE Master_Account.Master = ?
                                                    ;",array($_GET['ID']));
                                                    echo $r ? sqlsrv_fetch_array($r)['Count_of_Locations'] : 0;
                                                ?> <i>Locations</i></div></div>
                                                <div class='row'><div class='col-xs-12'><?php $Icons->Unit();
                                                    $r = sqlsrv_query($NEI,"
                                                        SELECT DISTINCT Count(Elev.ID) AS Count_of_Elevators 
                                                        FROM nei.dbo.Elev LEFT JOIN nei.dbo.Loc ON Elev.Loc = Loc.Loc LEFT JOIN Portal.dbo.Master_Account ON Loc.Owner = Master_Account.Customer
                                                        WHERE Master_Account.Master = ?;",array($_GET['ID']));
                                                    echo $r ? sqlsrv_fetch_array($r)['Count_of_Elevators'] : 0;?> <i>Units</i></div></div>
                                                <div class='row'><div class='col-xs-12'><?php $Icons->Job();
                                                    $r = sqlsrv_query($NEI,"
                                                        SELECT DISTINCT Count(Job.ID) AS Count_of_Jobs 
                                                        FROM nei.dbo.Job LEFT JOIN nei.dbo.Loc ON Loc.Loc = Job.Loc LEFT JOIN Portal.dbo.Master_Account ON Master_Account.Customer = Loc.Owner
                                                        WHERE Master_Account.Master = ?;",array($_GET['ID']));
                                                    echo $r ? sqlsrv_fetch_array($r)['Count_of_Jobs'] : 0;?> <i>Jobs</i></div></div>
                                                <div class='row'><div class='col-xs-12'><?php $Icons->Ticket();
                                                    $r = sqlsrv_query($NEI,"
                                                        SELECT Count(TicketD.ID) AS Count_of_Tickets 
                                                        FROM 
                                                            nei.dbo.TicketD 
                                                            LEFT JOIN nei.dbo.Loc               ON TicketD.Loc             = Loc.Loc
                                                            LEFT JOIN Portal.dbo.Master_Account ON Master_Account.Customer = Loc.Owner
                                                        WHERE 
                                                            Master_Account.Master = ?
                                                            AND NOT (TicketD.DescRes    LIKE    '%Voided%')
                                                            AND TicketD.Total > 0;",array($_GET['ID']));
                                                    $Count_of_Tickets = $r ? sqlsrv_fetch_array($r)['Count_of_Tickets'] : 0;
                                                    $r = sqlsrv_query($NEI,"
                                                        SELECT Count(TicketDArchive.ID) AS Count_of_Tickets 
                                                        FROM 
                                                            nei.dbo.TicketDArchive 
                                                            LEFT JOIN nei.dbo.Loc               ON TicketDArchive.Loc      = Loc.Loc
                                                            LEFT JOIN Portal.dbo.Master_Account ON Master_Account.Customer = Loc.Owner
                                                        WHERE 
                                                            Master_Account.Master = ?
                                                            AND NOT (TicketDArchive.DescRes    LIKE    '%Voided%');",array($_GET['ID']));
                                                    $Count_of_Tickets =  $r ? $Count_of_Tickets + sqlsrv_fetch_array($r)['Count_of_Tickets'] : $Count_of_Tickets;
                                                    $r = sqlsrv_query($NEI,"
                                                        SELECT Count(TicketO.ID) AS Count_of_Tickets 
                                                        FROM 
                                                            nei.dbo.TicketO 
                                                            LEFT JOIN nei.dbo.Loc               ON nei.dbo.TicketO.LID     = Loc.Loc
                                                            LEFT JOIN Portal.dbo.Master_Account ON Master_Account.Customer = Loc.Owner
                                                        WHERE 
                                                            Master_Account.Master = ?;",array($_GET['ID']));
                                                    echo $r ? $Count_of_Tickets + sqlsrv_fetch_array($r)['Count_of_Tickets'] : $Count_of_Tickets;?> <i>Tickets</i></div></div>
                                                <div class='row'><div class='col-xs-12'><?php $Icons->Invoice();
                                                    $r = sqlsrv_query($NEI,"
                                                        SELECT DISTINCT Count(Invoice.Ref) AS Count_of_Invoices 
                                                        FROM nei.dbo.Invoice LEFT JOIN nei.dbo.Loc ON Loc.Loc = Invoice.Loc LEFT JOIN Portal.dbo.Master_Account ON Master_Account.Customer = Loc.Owner
                                                        WHERE Master_Account.Master = ?;",array($_GET['ID']));
                                                    echo $r ? sqlsrv_fetch_array($r)['Count_of_Invoices'] : 0;?> <i>Invoices</i></div></div>
                                                <div class='row'><div class='col-xs-12'><?php $Icons->Proposal();
                                                    $r = sqlsrv_query($NEI,"
                                                        SELECT Count(Estimate.ID) AS Count_of_Proposals 
                                                        FROM (nei.dbo.Estimate LEFT JOIN nei.dbo.Job ON Estimate.Job = Job.ID) LEFT JOIN nei.dbo.Loc ON Job.Loc = Loc.Loc LEFT JOIN Portal.dbo.Master_Account ON Master_Account.Customer = Loc.Owner
                                                        WHERE Master_Account.Master = ?;",array($_GET['ID']));
                                                    echo $r ? sqlsrv_fetch_array($r)['Count_of_Proposals'] : 0;?> <i>Proposals</i></div></div>
                                                <div class='row'><div class='col-xs-12'><?php $Icons->Collection();
                                                    $r = sqlsrv_query($NEI,"
                                                        SELECT DISTINCT Count(OpenAR.TransID) AS Count_of_Outstanding_Invoices 
                                                        FROM nei.dbo.OpenAR LEFT JOIN nei.dbo.Loc ON Loc.Loc = OpenAR.Loc LEFT JOIN Portal.dbo.Master_Account ON Master_Account.Customer = Loc.Owner
                                                        WHERE Master_Account.Master = ?;",array($_GET['ID']));
                                                    echo $r ? sqlsrv_fetch_array($r)['Count_of_Outstanding_Invoices'] : 0;?> <i>Collectable Invoices</i></div></div>
                                            </div>
                                        </div>
                                        <style>table#Table_Worker_Feed tbody tr td, table#Table_Service_Call_Feed tbody tr td,table#Table_Service_Call_Feed tbody tr th {padding:3px;border:1.5px solid black;}</style>
                                        <div class='col-md-8'>
                                            <div class='row' >
                                                <div class='col-md-12' >
                                                	<div class="panel panel-default">
								                        <div class="panel-heading">
								                            <i class="fa fa-bell fa-fw"></i> Worker Feed
								                        </div>
								                        <div class="panel-body">
								                            <div id='Worker_Feed' style='border:3px solid #337ab7;'><table id='Table_Worker_Feed'><tbody>
			                                                    <?php 
			                                                    $r = sqlsrv_query($NEI,"
			                                                        SELECT 
			                                                            TicketO.*,
			                                                            TicketO.ID AS TicketID,
			                                                            Loc.*,
			                                                            Emp.*,
			                                                            Emp.fFirst AS First_Name,
			                                                            Emp.Last AS Last_Name
			                                                        FROM 
			                                                            (nei.dbo.TicketO 
			                                                            LEFT JOIN nei.dbo.Loc ON TicketO.LID = Loc.Loc)
			                                                            LEFT JOIN Emp ON TicketO.fWork = Emp.fWork
			                                                            LEFT JOIN Portal.dbo.Master_Account ON Master_Account.Customer = Loc.Owner
			                                                        WHERE 
			                                                            Master_Account.Master = ?
			                                                    ",array($_GET['ID']));
			                                                    if($r){while($Ticket = sqlsrv_fetch_array($r)){
			                                                        if(($Ticket['TimeRoute'] != '1899-12-30 00:00:00.000' && $Ticket['TimeRoute'] != '') && ($Ticket['TimeSite'] == '1899-12-30 00:00:00.000' || $Ticket['TimeSite'] == '')){?><tr><td><a href="ticket.php?ID=<?php echo $Ticket['TicketID'];?>"><?php echo $Ticket['TicketID'];?></a></td><td><?php echo $Ticket['fFirst'] . " " . $Ticket['Last'];?></td><td><?php echo date('h:i A',strtotime(substr($Ticket['TimeRoute'],10,99)));?></td><td> En Route</td><td><?php echo $Ticket['Tag'];?></td></tr><?php }
			                                                        elseif($Ticket['TimeSite'] != '1899-12-30 00:00:00.000' && $Ticket['TimeSite'] != '' && ($Ticket['TimeComp'] == '1899-12-30 00:00:00.000' || $Ticket['TimeComp'] == '')){?><tr><td><a href="ticket.php?ID=<?php echo $Ticket['TicketID'];?>"><?php echo $Ticket['TicketID'];?></a></td><td><?php echo $Ticket['fFirst'] . " " . $Ticket['Last'];?></td><td><?php echo date('h:i A',strtotime(substr($Ticket['TimeSite'],10,99)));?></td><td>On Site</td><td><?php echo $Ticket['Tag'];?></td></tr><?php }
			                                                    }} 
			                                                    else {?><h3>No Mechanics En Route / On Site</h3><?php }
			                                                    ?></tbody></table></div>
								                        </div>
								                    </div>
                                                </div>
                                            </div>
                                            <br />
                                            <div style=''>
	                                            <div class='row' >
	                                                <div class='col-md-12'>
	                                                	<div class="panel panel-default">
								                        <div class="panel-heading">
								                            <i class="fa fa-bell fa-fw"></i> Service Call Feed
								                        </div>
								                        <div class="panel-body">
								                        	<div id='Worker_Feed' style='border:3px solid #337ab7;'><table id='Table_Service_Call_Feed'>
			                                                	<thead><tr>
			                                                		<th>Ticket</th>
			                                                		<th>Created</th>
			                                                		<th>Worked / Scheduled</th>
			                                                		<th>Status</th>
			                                                		<th>Worker</th>
			                                                		<th>Location</th>
			                                                		<th>Unit</th>
			                                                	</tr></thead>
			                                                	<tbody>
			                                                    <?php 
			                                                    $r = sqlsrv_query($NEI,"
			                                                        SELECT 
			                                                            TicketO.*,
			                                                            TicketO.ID       AS TicketID,
			                                                            Loc.*,
			                                                            Emp.*,
			                                                            TickOStatus.Type AS AssignedType,
			                                                            Elev.*,
			                                                            Emp.fFirst AS First_Name,
			                                                            Emp.Last AS Last_Name
			                                                        FROM 
			                                                            nei.dbo.TicketO 
			                                                            LEFT JOIN nei.dbo.Loc               ON TicketO.LID             = Loc.Loc
			                                                            LEFT JOIN Emp               ON TicketO.fWork           = Emp.fWork
			                                                            LEFT JOIN Portal.dbo.Master_Account ON Master_Account.Customer = Loc.Owner
			                                                            LEFT JOIN nei.dbo.TickOStatus       ON TicketO.Assigned        = TickOStatus.Ref
			                                                            LEFT JOIN nei.dbo.Elev 				ON Elev.ID 				   = TicketO.LElev
			                                                        WHERE 
			                                                            Master_Account.Master = ?
			                                                            AND TicketO.Level = 1
			                                                    ",array($_GET['ID']));
			                                                    if($r){while($Ticket = sqlsrv_fetch_array($r)){?><tr>
			                                                    	<td><a href='ticket.php?ID=<?php echo $Ticket['TicketID'];?>'><?php echo $Ticket['TicketID'];?></a></td>
			                                                    	<td><?php echo $Ticket['CDate'];?></td>
			                                                    	<td><?php echo $Ticket['EDate'];?></td>
			                                                    	<td><b><?php echo $Ticket['AssignedType'];?></b></td>
			                                                    	<td><?php echo strlen($Ticket['First_Name'] . " " . $Ticket['Last_Name']) > 1 && isset($Ticket['First_Name'],$Ticket['Last_Name']) ? $Ticket['First_Name'] . " " . $Ticket['Last_Name'] : ' ';;?></td>
			                                                    	<td><?php echo $Ticket['Tag'];?></td>
			                                                    	<td><?php echo strlen($Ticket['State']) > 0 ? $Ticket['State'] : $Ticket['Unit'];?></td>
			                                                   	</tr><?php }} 
			                                                    else {?><h3>No Mechanics En Route / On Site</h3><?php }
			                                                    ?></tbody></table></div>
			                                               	</div>
			                                            </div>
	                                                	
	                                                </div>
	                                            </div>
	                                        </div>
                                        </div>
                                        <!--<div class='col-md-6'>
                                            <div class='row'>
                                                <div class='col-md-4'>
                                                    <style>
                                                    table#maintenance_hours td, table#maintenance_hours th {
                                                        padding:5px;
                                                        border:1px solid black;
                                                    }
                                                    </style>
                                                    <table id='maintenance_hours'>
                                                        <thead>
                                                            <th>Maintenance</th>
                                                            <th>Hours</th>
                                                        </thead>
                                                        <tbody>
                                                            <tr><td>Seven Days</td>
                                                                <td><?php 
                                                                $Start_Date = date('Y-m-d 00:00:00.000', strtotime('-7 days'));
                                                                $End_Date = date("Y-m-d H:i:s", strtotime('now'));
                                                                $r = sqlsrv_query($NEI,"
                                                                    SELECT SUM(TicketD.Total) AS SumTotal
                                                                    FROM (TicketD LEFT JOIN nei.dbo.Loc ON TicketD.Loc = Loc.Loc) LEFT JOIN nei.dbo.Job ON TicketD.Job = Job.ID
                                                                    WHERE 
                                                                        ? <= TicketD.EDate 
                                                                        AND TicketD.EDate <= ?
                                                                        AND Loc.Owner = ?
                                                                        AND Job.Type = '0'
                                                                ",array($Start_Date,$End_Date,$_GET['ID']));
                                                                if($r){echo sqlsrv_fetch_array($r)['SumTotal'];} 
                                                                else {echo "0.00";}
                                                                ?></tr>
                                                            <tr><td>This Month</td>
                                                                <td><?php 
                                                                $Start_Date = date('Y-m-01 00:00:00.000', strtotime('now'));
                                                                $End_Date = date("Y-m-d H:i:s", strtotime('now'));
                                                                $r = sqlsrv_query($NEI,"
                                                                    SELECT SUM(TicketD.Total) AS SumTotal
                                                                    FROM (TicketD LEFT JOIN nei.dbo.Loc ON TicketD.Loc = Loc.Loc) LEFT JOIN nei.dbo.Job ON TicketD.Job = Job.ID
                                                                    WHERE 
                                                                        ? <= TicketD.EDate 
                                                                        AND TicketD.EDate <= ?
                                                                        AND Loc.Owner = ?
                                                                        AND Job.Type = '0'
                                                                ",array($Start_Date,$End_Date,$_GET['ID']));
                                                                if($r){echo sqlsrv_fetch_array($r)['SumTotal'];} 
                                                                else {echo "0.00";}
                                                                ?></tr>
                                                            <tr><td>Last Month</td>
                                                                <td><?php 
                                                                $Start_Date = date('Y-m-d 00:00:00.000', strtotime("first day of last month"));
                                                                $End_Date = date("Y-m-d 23:59:59.999", strtotime("last day of last month"));
                                                                $r = sqlsrv_query($NEI,"
                                                                    SELECT SUM(TicketD.Total) AS SumTotal
                                                                    FROM (TicketD LEFT JOIN nei.dbo.Loc ON TicketD.Loc = Loc.Loc) LEFT JOIN nei.dbo.Job ON TicketD.Job = Job.ID
                                                                    WHERE 
                                                                        ? <= TicketD.EDate 
                                                                        AND TicketD.EDate <= ?
                                                                        AND Loc.Owner = ?
                                                                        AND Job.Type = '0'
                                                                ",array($Start_Date,$End_Date,$_GET['ID']));
                                                                if($r){echo sqlsrv_fetch_array($r)['SumTotal'];} 
                                                                else {echo "0.00";}
                                                                ?></tr>
                                                            <tr><td>This Year</td>
                                                                <td><?php 
                                                                $Start_Date = date('Y-01-01 00:00:00.000', strtotime('now'));
                                                                $End_Date = date("Y-m-d H:i:s", strtotime('now'));
                                                                $r = sqlsrv_query($NEI,"
                                                                    SELECT SUM(TicketD.Total) AS SumTotal
                                                                    FROM (TicketD LEFT JOIN nei.dbo.Loc ON TicketD.Loc = Loc.Loc) LEFT JOIN nei.dbo.Job ON TicketD.Job = Job.ID
                                                                    WHERE 
                                                                        ? <= TicketD.EDate 
                                                                        AND TicketD.EDate <= ?
                                                                        AND Loc.Owner = ?
                                                                        AND Job.Type = '0'
                                                                ",array($Start_Date,$End_Date,$_GET['ID']));
                                                                if($r){echo sqlsrv_fetch_array($r)['SumTotal'];} 
                                                                else {echo "0.00";}
                                                                ?></tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <div class='col-md-4'>
                                                    <style>
                                                    table#modernization_hours td, table#modernization_hours th {
                                                        padding:5px;
                                                        border:1px solid black;
                                                    }
                                                    </style>
                                                    <table id='modernization_hours'>
                                                        <thead>
                                                            <th>Modernization</th>
                                                            <th>Hours</th>
                                                        </thead>
                                                        <tbody>
                                                            <tr><td>Seven Days</td>
                                                                <td><?php 
                                                                $Start_Date = date('Y-m-d 00:00:00.000', strtotime('-7 days'));
                                                                $End_Date = date("Y-m-d H:i:s", strtotime('now'));
                                                                $r = sqlsrv_query($NEI,"
                                                                    SELECT SUM(TicketD.Total) AS Total
                                                                    FROM (TicketD LEFT JOIN nei.dbo.Loc ON TicketD.Loc = Loc.Loc) LEFT JOIN nei.dbo.Job ON TicketD.Job = Job.ID
                                                                    WHERE 
                                                                        ? <= TicketD.EDate 
                                                                        AND TicketD.EDate <= ?
                                                                        AND Loc.Owner = ?
                                                                        AND Job.Type = 2
                                                                ",array($Start_Date,$End_Date,$_GET['ID']));
                                                                if($r){echo sqlsrv_fetch_array($r)['Total'];} 
                                                                else {echo "0.00";}
                                                                ?></tr>
                                                            <tr><td>This Month</td>
                                                                <td><?php 
                                                                $Start_Date = date('Y-m-01 00:00:00.000', strtotime('now'));
                                                                $End_Date = date("Y-m-d H:i:s", strtotime('now'));
                                                                $r = sqlsrv_query($NEI,"
                                                                    SELECT SUM(TicketD.Total) AS Total
                                                                    FROM (TicketD LEFT JOIN nei.dbo.Loc ON TicketD.Loc = Loc.Loc) LEFT JOIN nei.dbo.Job ON TicketD.Job = Job.ID
                                                                    WHERE 
                                                                        ? <= TicketD.EDate 
                                                                        AND TicketD.EDate <= ?
                                                                        AND Loc.Owner = ?
                                                                        AND Job.Type = 2
                                                                ",array($Start_Date,$End_Date,$_GET['ID']));
                                                                if($r){echo sqlsrv_fetch_array($r)['Total'];} 
                                                                else {echo "0.00";}
                                                                ?></tr>
                                                            <tr><td>Last Month</td>
                                                                <td><?php 
                                                                $Start_Date = date('Y-m-d 00:00:00.000', strtotime("first day of last month"));
                                                                $End_Date = date("Y-m-d 23:59:59.999", strtotime('last day of last month'));
                                                                $r = sqlsrv_query($NEI,"
                                                                    SELECT SUM(TicketD.Total) AS Total
                                                                    FROM (TicketD LEFT JOIN nei.dbo.Loc ON TicketD.Loc = Loc.Loc) LEFT JOIN nei.dbo.Job ON TicketD.Job = Job.ID
                                                                    WHERE 
                                                                        ? <= TicketD.EDate 
                                                                        AND TicketD.EDate <= ?
                                                                        AND Loc.Owner = ?
                                                                        AND Job.Type = 2
                                                                ",array($Start_Date,$End_Date,$_GET['ID']));
                                                                if($r){echo sqlsrv_fetch_array($r)['Total'];} 
                                                                else {echo "0.00";}
                                                                ?></tr>
                                                            <tr><td>This Year</td>
                                                                <td><?php 
                                                                $Start_Date = date('Y-01-01 00:00:00.000', strtotime('now'));
                                                                $End_Date = date("Y-m-d H:i:s", strtotime('now'));
                                                                $r = sqlsrv_query($NEI,"
                                                                    SELECT SUM(TicketD.Total) AS Total
                                                                    FROM (TicketD LEFT JOIN nei.dbo.Loc ON TicketD.Loc = Loc.Loc) LEFT JOIN nei.dbo.Job ON TicketD.Job = Job.ID
                                                                    WHERE 
                                                                        ? <= TicketD.EDate 
                                                                        AND TicketD.EDate <= ?
                                                                        AND Loc.Owner = ?
                                                                        AND Job.Type = 2
                                                                ",array($Start_Date,$End_Date,$_GET['ID']));
                                                                if($r){echo sqlsrv_fetch_array($r)['Total'];} 
                                                                else {echo "0.00";}
                                                                ?></tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <div class='col-md-4'>
                                                    <style>
                                                    table#repair_hours td, table#repair_hours th {
                                                        padding:5px;
                                                        border:1px solid black;
                                                    }
                                                    </style>
                                                    <table id='repair_hours'>
                                                        <thead>
                                                            <th>Repair</th>
                                                            <th>Hours</th>
                                                        </thead>
                                                        <tbody>
                                                            <tr><td>Seven Days</td>
                                                                <td><?php 
                                                                $Start_Date = date('Y-m-d 00:00:00.000', strtotime('-7 days'));
                                                                $End_Date = date("Y-m-d H:i:s", strtotime('now'));
                                                                $r = sqlsrv_query($NEI,"
                                                                    SELECT SUM(TicketD.Total) AS Total
                                                                    FROM (TicketD LEFT JOIN nei.dbo.Loc ON TicketD.Loc = Loc.Loc) LEFT JOIN nei.dbo.Job ON TicketD.Job = Job.ID
                                                                    WHERE 
                                                                        ? <= TicketD.EDate 
                                                                        AND TicketD.EDate <= ?
                                                                        AND Loc.Owner = ?
                                                                        AND Job.Type = 6
                                                                ",array($Start_Date,$End_Date,$_GET['ID']));
                                                                if($r){echo sqlsrv_fetch_array($r)['Total'];} 
                                                                else {echo "0.00";}
                                                                ?></tr>
                                                            <tr><td>This Month</td>
                                                                <td><?php 
                                                                $Start_Date = date('Y-m-01 00:00:00.000', strtotime('now'));
                                                                $End_Date = date("Y-m-d H:i:s", strtotime('now'));
                                                                $r = sqlsrv_query($NEI,"
                                                                    SELECT SUM(TicketD.Total) AS Total
                                                                    FROM (TicketD LEFT JOIN nei.dbo.Loc ON TicketD.Loc = Loc.Loc) LEFT JOIN nei.dbo.Job ON TicketD.Job = Job.ID
                                                                    WHERE 
                                                                        ? <= TicketD.EDate 
                                                                        AND TicketD.EDate <= ?
                                                                        AND Loc.Owner = ?
                                                                        AND Job.Type = 6
                                                                ",array($Start_Date,$End_Date,$_GET['ID']));
                                                                if($r){echo sqlsrv_fetch_array($r)['Total'];} 
                                                                else {echo "0.00";}
                                                                ?></tr>
                                                            <tr><td>Last Month</td>
                                                                <td><?php 
                                                                $Start_Date = date('Y-m-d 00:00:00.000', strtotime("first day of last month"));
                                                                $End_Date = date("Y-m-d 23:59:59.999", strtotime('last day of last month'));
                                                                $r = sqlsrv_query($NEI,"
                                                                    SELECT SUM(TicketD.Total) AS Total
                                                                    FROM (TicketD LEFT JOIN nei.dbo.Loc ON TicketD.Loc = Loc.Loc) LEFT JOIN nei.dbo.Job ON TicketD.Job = Job.ID
                                                                    WHERE 
                                                                        ? <= TicketD.EDate 
                                                                        AND TicketD.EDate <= ?
                                                                        AND Loc.Owner = ?
                                                                        AND Job.Type = 6
                                                                ",array($Start_Date,$End_Date,$_GET['ID']));
                                                                if($r){echo sqlsrv_fetch_array($r)['Total'];} 
                                                                else {echo "0.00";}
                                                                ?></tr>
                                                            <tr><td>This Year</td>
                                                                <td><?php 
                                                                $Start_Date = date('Y-01-01 00:00:00.000', strtotime('now'));
                                                                $End_Date = date("Y-m-d H:i:s", strtotime('now'));
                                                                $r = sqlsrv_query($NEI,"
                                                                    SELECT SUM(TicketD.Total) AS Total
                                                                    FROM (TicketD LEFT JOIN nei.dbo.Loc ON TicketD.Loc = Loc.Loc) LEFT JOIN nei.dbo.Job ON TicketD.Job = Job.ID
                                                                    WHERE 
                                                                        ? <= TicketD.EDate 
                                                                        AND TicketD.EDate <= ?
                                                                        AND Loc.Owner = ?
                                                                        AND Job.Type = 6
                                                                ",array($Start_Date,$End_Date,$_GET['ID']));
                                                                if($r){echo sqlsrv_fetch_array($r)['Total'];} 
                                                                else {echo "0.00";}
                                                                ?></tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>-->
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="maintenance-pills">
                                    <ul class="nav nav-tabs">
                                        <li class="active"><a href="#maintenance-2-pills" data-toggle="tab">Routine Maintenance</a></li>
                                        <li><a href="#service-calls-pills" data-toggle="tab">Service Calls</a></li>
                                    </ul>
                                    <div class="tab-content">
                                        <div class="tab-pane fade in active" id="maintenance-2-pills">
                                            <div class='input-group'>
                                                <button class='form-control' onClick="expandMaintenance(this);">Expand Tickets</button>
                                            </div>
                                            <br />
                                            <table id='Table_Tickets_Maintenance' class='display' cellspacing='0' width='100%'>
                                                <thead>
                                                    <th></th>
                                                    <th title='ID of the Ticket'>ID</th>
                                                    <th title='Description of the Ticket'>First Name</th>
                                                    <th>Last Name</th>
                                                    <th title='Scheduled Work Time'>Date</th>
                                                    <th title='Status of the Ticket'>Status</th>                                            
                                                    <th title='Total Hours'>Hours</th>
                                                </thead>
                                            </table>
                                        </div>
                                        <div class="tab-pane fade" id="service-calls-pills">
                                            <div class='input-group'>
                                                <button class='form-control' onClick="expandServiceCalls(this);">Expand Tickets</button>
                                            </div>
                                            <br />
                                            <table id='Table_Tickets_Service_Calls' class='display' cellspacing='0' width='100%'>
                                                <thead>
                                                    <th></th>
                                                    <th title='ID of the Ticket'>ID</th>
                                                    <th title='Description of the Ticket'>First Name</th>
                                                    <th>Last Name</th>
                                                    <th title='Scheduled Work Time'>Date</th>
                                                    <th title='Status of the Ticket'>Status</th>                                            
                                                    <th title='Total Hours'>Hours</th>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade in" id="workers-pills">
                                    <table id="Table_Workers" class='display' cellspacing='0' width='100%'>
                                        <thead>
                                            <th>ID</th>
                                            <th>Last Name</th>
                                            <th>First Name</th>
                                            <?php if(isset($My_Privileges['Ticket']['Other_Privilege']) && $My_Privileges['Ticket']['Other_Privilege'] >= 4){?><th>Regular</th>
                                            <th>Overtime</th>
                                            <th>Doubletime</th>
                                            <th>Total</th><?php }?>
                                        </thead>
                                    </table>
                                </div>
                                <?php if((!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Elevator') && (isset($My_Privileges['Financials']) && $My_Privileges['Financials']['Other_Privilege'] >= 4)){?>
                                 <div class="tab-pane fade in" id="financials-pills">
                                    <ul class="nav nav-tabs">
                                        <li class="active"><a href="#financials-summary-pills" data-toggle="tab">Summary</a></li>
                                        <li><a href="#financials-location-analysis-pills" data-toggle="tab">Location Anaylsis</a></li>
                                        <li><a href="#financials-job-analysis-pills" data-toggle="tab">Job Anaylsis</a></li>
                                    </ul>
                                    <div class="tab-content">
                                        <div class="tab-pane fade in active" id="financials-summary-pills">
                                            <div class='row'><div class="col-lg-12">
                                                <div class="panel panel-red">
                                                    <div class="panel-heading">Profit</div>
                                                    <div class="panel-body">
                                                        <div class="flot-chart"><div class="flot-chart-content" id="flot-placeholder-profit"></div></div>
                                                    </div>
                                                </div>  
                                            </div></div>
                                            <style>
                                            table#Table_Profit thead tr th {
                                                text-align:center;
                                            }
                                            table#Table_Profit tbody td {
                                                padding:3px;
                                                text-align:center;
                                            }
                                            </style>
                                            <table id="Table_Profit" class="display" cellspacing='0' width='100%'>
                                                <thead style='border:1px solid black;'>
                                                    <th></th>
                                                    <th>2012</th>
                                                    <th>2013</th>
                                                    <th>2014</th>
                                                    <th>2015</th>
                                                    <th>2016</th>
                                                    <th>2017</th>
                                                    <th>3 Year</th>
                                                    <th>5 Year</th>
                                                </thead>
                                                <tbody style='border:1px solid black;'><?php if(isset($SQL_Jobs)){?>
                                                    <tr>
                                                        <td><b>Revenue</b></td>
                                                        <td><?php 
                                                            $r = sqlsrv_query($NEI,"
                                                                SELECT Sum(Amount) AS Total_Revenue_2012
                                                                FROM 
                                                                    Invoice
                                                                    LEFT JOIN nei.dbo.Loc ON Invoice.Loc = Loc.Loc
																	LEFT JOIN Portal.dbo.Master_Account ON Master_Account.Customer = Loc.Owner
                                                                WHERE Invoice.fDate >= '2012-01-01 00:00:00.000' AND Invoice.fDate < '2013-01-01 00:00:00.000' AND Master_Account.Master='{$_GET['ID']}'
                                                            ;");
                                                            $Total_Revenue_2012 = $r ? sqlsrv_fetch_array($r)['Total_Revenue_2012'] : 0;
                                                            echo substr(money_format('%i',$Total_Revenue_2012),4);
                                                        ?></td>
                                                        <td><?php 
                                                            $r = sqlsrv_query($NEI,"
                                                                SELECT Sum(Amount) AS Total_Revenue_2013
                                                                FROM 
                                                                    Invoice
                                                                    LEFT JOIN nei.dbo.Loc ON Invoice.Loc = Loc.Loc
																	LEFT JOIN Portal.dbo.Master_Account ON Master_Account.Customer = Loc.Owner
                                                                WHERE Invoice.fDate >= '2013-01-01 00:00:00.000' AND Invoice.fDate < '2014-01-01 00:00:00.000' AND Master_Account.Master='{$_GET['ID']}'
                                                            ;");
                                                            $Total_Revenue_2013 = $r ? sqlsrv_fetch_array($r)['Total_Revenue_2013'] : 0;
                                                            echo substr(money_format('%i',$Total_Revenue_2013),4);
                                                        ?></td>
                                                        <td><?php 
                                                            $r = sqlsrv_query($NEI,"
                                                                SELECT Sum(Amount) AS Total_Revenue_2014
                                                                FROM 
                                                                    Invoice
                                                                    LEFT JOIN nei.dbo.Loc ON Invoice.Loc = Loc.Loc
																	LEFT JOIN Portal.dbo.Master_Account ON Master_Account.Customer = Loc.Owner
                                                                WHERE Invoice.fDate >= '2014-01-01 00:00:00.000' AND Invoice.fDate < '2015-01-01 00:00:00.000' AND Master_Account.Master='{$_GET['ID']}'
                                                            ;");
                                                            $Total_Revenue_2014 = $r ? sqlsrv_fetch_array($r)['Total_Revenue_2014'] : 0;
                                                            echo substr(money_format('%i',$Total_Revenue_2014),4);
                                                        ?></td>
                                                        <td><?php 
                                                            $r = sqlsrv_query($NEI,"
                                                                SELECT Sum(Amount) AS Total_Revenue_2015
                                                                FROM 
                                                                    Invoice
                                                                    LEFT JOIN nei.dbo.Loc ON Invoice.Loc = Loc.Loc
																	LEFT JOIN Portal.dbo.Master_Account ON Master_Account.Customer = Loc.Owner
                                                                WHERE Invoice.fDate >= '2015-01-01 00:00:00.000' AND Invoice.fDate < '2016-01-01 00:00:00.000' AND Master_Account.Master='{$_GET['ID']}'
                                                            ;");
                                                            $Total_Revenue_2015 = $r ? sqlsrv_fetch_array($r)['Total_Revenue_2015'] : 0;
                                                            echo substr(money_format('%i',$Total_Revenue_2015),4);
                                                        ?></td>
                                                        <td><?php 
                                                            $r = sqlsrv_query($NEI,"
                                                                SELECT Sum(Amount) AS Total_Revenue_2016
                                                                FROM 
                                                                    Invoice
                                                                    LEFT JOIN nei.dbo.Loc ON Invoice.Loc = Loc.Loc
																	LEFT JOIN Portal.dbo.Master_Account ON Master_Account.Customer = Loc.Owner
                                                                WHERE Invoice.fDate >= '2016-01-01 00:00:00.000' AND Invoice.fDate < '2017-01-01 00:00:00.000' AND Master_Account.Master='{$_GET['ID']}'
                                                            ;");
                                                            $Total_Revenue_2016 = $r ? sqlsrv_fetch_array($r)['Total_Revenue_2016'] : 0;
                                                            echo substr(money_format('%i',$Total_Revenue_2016),4);
                                                        ?></td>
                                                        <td><?php 
                                                            $r = sqlsrv_query($NEI,"
                                                                SELECT Sum(Amount) AS Total_Revenue_2017
                                                                FROM 
                                                                    Invoice
                                                                    LEFT JOIN nei.dbo.Loc ON Invoice.Loc = Loc.Loc
																	LEFT JOIN Portal.dbo.Master_Account ON Master_Account.Customer = Loc.Owner
                                                                WHERE Invoice.fDate >= '2017-01-01 00:00:00.000' AND Invoice.fDate < '2018-01-01 00:00:00.000' AND Master_Account.Master='{$_GET['ID']}'
                                                            ;");
                                                            $Total_Revenue_2017 = $r ? sqlsrv_fetch_array($r)['Total_Revenue_2017'] : 0;
                                                            echo substr(money_format('%i',$Total_Revenue_2017),4);
                                                        ?></td>
                                                        <td><?php 
                                                            $r = sqlsrv_query($NEI,"
                                                                SELECT Sum(Amount) AS Total_Revenue_3_Year
                                                                FROM 
                                                                    Invoice
                                                                    LEFT JOIN nei.dbo.Loc ON Invoice.Loc = Loc.Loc
																	LEFT JOIN Portal.dbo.Master_Account ON Master_Account.Customer = Loc.Owner
                                                                WHERE Invoice.fDate >= '2015-01-01 00:00:00.000' AND Invoice.fDate < '2018-01-01 00:00:00.000' AND Master_Account.Master='{$_GET['ID']}'
                                                            ;");
                                                            $Total_Revenue_3_Year = $r ? sqlsrv_fetch_array($r)['Total_Revenue_3_Year'] : 0;
                                                            echo substr(money_format('%i',$Total_Revenue_3_Year),4);
                                                        ?></td>
                                                        <td><?php 
                                                            $r = sqlsrv_query($NEI,"
                                                                SELECT Sum(Amount) AS Total_Revenue_5_Year
                                                                FROM 
                                                                    Invoice
                                                                    LEFT JOIN nei.dbo.Loc ON Invoice.Loc = Loc.Loc
																	LEFT JOIN Portal.dbo.Master_Account ON Master_Account.Customer = Loc.Owner
                                                                WHERE Invoice.fDate >= '2013-01-01 00:00:00.000' AND Invoice.fDate < '2018-01-01 00:00:00.000' AND Master_Account.Master='{$_GET['ID']}'
                                                            ;");
                                                            $Total_Revenue_5_Year = $r ? sqlsrv_fetch_array($r)['Total_Revenue_5_Year'] : 0;
                                                            echo substr(money_format('%i',$Total_Revenue_5_Year),4);
                                                        ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td><b>Labor</b></td>
                                                        <td><?php 
                                                            $r = sqlsrv_query($NEI,"
                                                                SELECT 
                                                                    Sum(JobI.Amount) AS Total_Labor_2012
                                                                FROM 
                                                                    Portal.dbo.Master_Account
                                                                    LEFT JOIN nei.dbo.Loc ON Loc.Owner = Master_Account.Customer
                                                                    LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                                                    LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                                                WHERE 
                                                                    Master_Account.Master='{$_GET['ID']}'
                                                                    AND JobI.Type='1' AND JobI.Labor = '1'
                                                                    AND JobI.fDate >= '2012-01-01 00:00:00.000' AND JobI.fDate < '2013-01-01 00:00:00.000'
                                                            ;");
                                                            $Temp_Labor_2012 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2012'] : 0;
                                                            $r = sqlsrv_query($Paradox,"
                                                                SELECT 
                                                                    SUM([JOBLABOR].[TOTAL COST])     AS Total_Labor_2012
                                                                FROM 
                                                                    JOBLABOR
                                                                WHERE 
                                                                    ({$SQL_Jobs})
                                                                    AND convert(date,[WEEK ENDING]) >= '2012-01-01 00:00:00.000'
                                                                    AND convert(date,[WEEK ENDING]) < '2013-01-01 00:00:00.000'
                                                            ;");
                                                            $Total_Labor_2012 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2012'] : 0;
                                                            echo substr(money_format('%i',$Total_Labor_2012),4);
                                                        ?></td>
                                                        <td><?php 
                                                            $r = sqlsrv_query($NEI,"
                                                                SELECT 
                                                                    Sum(JobI.Amount) AS Total_Labor_2013
                                                                FROM 
                                                                    Portal.dbo.Master_Account
                                                                    LEFT JOIN nei.dbo.Loc ON Loc.Owner = Master_Account.Customer
                                                                    LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                                                    LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                                                WHERE 
                                                                    Master_Account.Master='{$_GET['ID']}'
                                                                    AND JobI.Type='1' AND JobI.Labor = '1'
                                                                    AND JobI.fDate >= '2013-01-01 00:00:00.000' AND JobI.fDate < '2014-01-01 00:00:00.000'
                                                            ;");
                                                            $Temp_Labor_2013 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2013'] : 0;
                                                            $r = sqlsrv_query($Paradox,"
                                                                SELECT 
                                                                    SUM([JOBLABOR].[TOTAL COST])     AS Total_Labor_2013
                                                                FROM 
                                                                    JOBLABOR
                                                                WHERE 
                                                                    ({$SQL_Jobs})
                                                                    AND convert(date,[WEEK ENDING]) >= '2013-01-01 00:00:00.000'
                                                                    AND convert(date,[WEEK ENDING]) < '2014-01-01 00:00:00.000'
                                                            ;");
                                                            $Total_Labor_2013 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2013'] : 0;
                                                            echo substr(money_format('%i',$Total_Labor_2013),4);
                                                        ?></td>
                                                        <td><?php 
                                                            $r = sqlsrv_query($NEI,"
                                                                SELECT 
                                                                    Sum(JobI.Amount) AS Total_Labor_2014
                                                                FROM 
                                                                    Portal.dbo.Master_Account
                                                                    LEFT JOIN nei.dbo.Loc ON Loc.Owner = Master_Account.Customer
                                                                    LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                                                    LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                                                WHERE 
                                                                    Master_Account.Master='{$_GET['ID']}'
                                                                    AND JobI.Type='1' AND JobI.Labor = '1'
                                                                    AND JobI.fDate >= '2014-01-01 00:00:00.000' AND JobI.fDate < '2015-01-01 00:00:00.000'
                                                            ;");
                                                            $Temp_Labor_2014 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2014'] : 0;
                                                            $r = sqlsrv_query($Paradox,"
                                                                SELECT 
                                                                    SUM([JOBLABOR].[TOTAL COST])     AS Total_Labor_2014
                                                                FROM 
                                                                    JOBLABOR
                                                                WHERE 
                                                                    ({$SQL_Jobs})
                                                                    AND convert(date,[WEEK ENDING]) >= '2014-01-01 00:00:00.000'
                                                                    AND convert(date,[WEEK ENDING]) < '2015-01-01 00:00:00.000'
                                                            ;");
                                                            $Total_Labor_2014 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2014'] : 0;
                                                            echo substr(money_format('%i',$Total_Labor_2014),4);
                                                        ?></td>
                                                        <td><?php 
                                                            $r = sqlsrv_query($NEI,"
                                                                SELECT 
                                                                    Sum(JobI.Amount) AS Total_Labor_2015
                                                                FROM 
                                                                    Portal.dbo.Master_Account
                                                                    LEFT JOIN nei.dbo.Loc ON Loc.Owner = Master_Account.Customer
                                                                    LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                                                    LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                                                WHERE 
                                                                    Master_Account.Master='{$_GET['ID']}'
                                                                    AND JobI.Type='1' AND JobI.Labor = '1'
                                                                    AND JobI.fDate >= '2015-01-01 00:00:00.000' AND JobI.fDate < '2016-01-01 00:00:00.000'
                                                            ;");
                                                            $Temp_Labor_2015 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2015'] : 0;
                                                            $r = sqlsrv_query($Paradox,"
                                                                SELECT 
                                                                    SUM([JOBLABOR].[TOTAL COST])     AS Total_Labor_2015
                                                                FROM 
                                                                    JOBLABOR
                                                                WHERE 
                                                                    ({$SQL_Jobs})
                                                                    AND convert(date,[WEEK ENDING]) >= '2015-01-01 00:00:00.000'
                                                                    AND convert(date,[WEEK ENDING]) < '2016-01-01 00:00:00.000'
                                                            ;");
                                                            $Total_Labor_2015 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2015'] : 0;
                                                            echo substr(money_format('%i',$Total_Labor_2015),4);
                                                        ?></td>
                                                        <td><?php 
                                                            $r = sqlsrv_query($NEI,"
                                                                SELECT 
                                                                    Sum(JobI.Amount) AS Total_Labor_2016
                                                                FROM 
                                                                    Portal.dbo.Master_Account
                                                                    LEFT JOIN nei.dbo.Loc ON Loc.Owner = Master_Account.Customer
                                                                    LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                                                    LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                                                WHERE 
                                                                    Master_Account.Master='{$_GET['ID']}'
                                                                    AND JobI.Type='1' AND JobI.Labor = '1'
                                                                    AND JobI.fDate >= '2016-01-01 00:00:00.000' AND JobI.fDate < '2017-01-01 00:00:00.000'
                                                            ;");
                                                            $Temp_Labor_2016 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2016'] : 0;
                                                            $r = sqlsrv_query($Paradox,"
                                                                SELECT 
                                                                    SUM([JOBLABOR].[TOTAL COST])     AS Total_Labor_2016
                                                                FROM 
                                                                    JOBLABOR
                                                                WHERE 
                                                                    ({$SQL_Jobs})
                                                                    AND convert(date,[WEEK ENDING]) >= '2016-01-01 00:00:00.000'
                                                                    AND convert(date,[WEEK ENDING]) < '2017-01-01 00:00:00.000'
                                                            ;");
                                                            $Total_Labor_2016 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2016'] : 0;
                                                            echo substr(money_format('%i',$Total_Labor_2016),4);
                                                        ?></td>
                                                        <td><?php 
                                                            $r = sqlsrv_query($NEI,"
                                                                SELECT 
                                                                    Sum(JobI.Amount) AS Total_Labor_2017
                                                                FROM 
                                                                    Portal.dbo.Master_Account
                                                                    LEFT JOIN nei.dbo.Loc ON Loc.Owner = Master_Account.Customer
                                                                    LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                                                    LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                                                WHERE 
                                                                    Master_Account.Master='{$_GET['ID']}'
                                                                    AND JobI.Type='1' AND JobI.Labor = '1'
                                                                    AND JobI.fDate >= '2017-01-01 00:00:00.000' AND JobI.fDate < '2018-01-01 00:00:00.000'
                                                            ;");
                                                            
                                                            $Temp_Labor_2017 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2017'] : 0;
                                                            $r = sqlsrv_query($Paradox,"
                                                                SELECT 
                                                                    SUM([JOBLABOR].[TOTAL COST])     AS Total_Labor_2017
                                                                FROM 
                                                                    JOBLABOR
                                                                WHERE 
                                                                    ({$SQL_Jobs})
                                                                    AND convert(date,[WEEK ENDING]) >= '2017-01-01 00:00:00.000'
                                                                    AND convert(date,[WEEK ENDING]) < '2017-03-30 00:00:00.000'
                                                            ;");
                                                            $Total_Labor_2017 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2017'] : 0;
                                                            $r = sqlsrv_query($NEI,"
                                                                SELECT 
                                                                    Sum(JobI.Amount) AS Total_Labor_2017
                                                                FROM 
                                                                    Portal.dbo.Master_Account
                                                                    LEFT JOIN nei.dbo.Loc ON Loc.Owner = Master_Account.Customer
                                                                    LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                                                    LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                                                WHERE 
                                                                    Master_Account.Master='{$_GET['ID']}'
                                                                    AND JobI.Type='1' AND JobI.Labor = '1'
                                                                    AND JobI.fDate >= '2017-03-30 00:00:00.000' AND JobI.fDate < '2018-01-01 00:00:00.000'
                                                            ");
                                                            $Total_Labor_2017 = $r ? $Total_Labor_2017 + sqlsrv_fetch_array($r)['Total_Labor_2017'] : $Total_Labor_3_Year;
                                                            echo substr(money_format('%i',$Total_Labor_2017),4);
                                                        ?></td>
                                                        <td><?php 
                                                            $r = sqlsrv_query($NEI,"
                                                                SELECT 
                                                                    Sum(JobI.Amount) AS Total_Labor_3_Year
                                                                FROM 
                                                                    Portal.dbo.Master_Account
                                                                    LEFT JOIN nei.dbo.Loc ON Loc.Owner = Master_Account.Customer
                                                                    LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                                                    LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                                                WHERE 
                                                                    Master_Account.Master='{$_GET['ID']}'
                                                                    AND JobI.Type='1' AND JobI.Labor = '1'
                                                                    AND JobI.fDate >= '2015-01-01 00:00:00.000' AND JobI.fDate < '2018-01-01 00:00:00.000'
                                                            ;");
                                                            $Temp_Labor_3_Year = $r ? sqlsrv_fetch_array($r)['Total_Labor_3_Year'] : 0;
                                                            $r = sqlsrv_query($Paradox,"
                                                                SELECT 
                                                                    SUM([JOBLABOR].[TOTAL COST])     AS Total_Labor_3_Year
                                                                FROM 
                                                                    JOBLABOR
                                                                WHERE 
                                                                    ({$SQL_Jobs})
                                                                    AND convert(date,[WEEK ENDING]) >= '2015-01-01 00:00:00.000'
                                                                    AND convert(date,[WEEK ENDING]) < '2017-03-30 00:00:00.000'
                                                            ;");
                                                            $Total_Labor_3_Year = $r ? sqlsrv_fetch_array($r)['Total_Labor_3_Year'] : 0;
                                                            $r = sqlsrv_query($NEI,"
                                                                SELECT 
                                                                    Sum(JobI.Amount) AS Total_Labor_3_Year
                                                                FROM 
                                                                    Portal.dbo.Master_Account
                                                                    LEFT JOIN nei.dbo.Loc ON Loc.Owner = Master_Account.Customer
                                                                    LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                                                    LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                                                WHERE 
                                                                    Master_Account.Master='{$_GET['ID']}'
                                                                    AND JobI.Type='1' AND JobI.Labor = '1'
                                                                    AND JobI.fDate >= '2017-03-30 00:00:00.000' AND JobI.fDate < '2018-01-01 00:00:00.000'
                                                            ");
                                                            $Total_Labor_3_Year = $r ? $Total_Labor_3_Year + sqlsrv_fetch_array($r)['Total_Labor_3_Year'] : $Total_Labor_3_Year;
                                                            echo substr(money_format('%i',$Total_Labor_3_Year),4);
                                                        ?></td>
                                                        <td><?php 
                                                            $r = sqlsrv_query($NEI,"
                                                                SELECT 
                                                                    Sum(JobI.Amount) AS Total_Labor_5_Year
                                                                FROM 
                                                                    Portal.dbo.Master_Account
                                                                    LEFT JOIN nei.dbo.Loc ON Loc.Owner = Master_Account.Customer
                                                                    LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                                                    LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                                                WHERE 
                                                                    Master_Account.Master='{$_GET['ID']}'
                                                                    AND JobI.Type='1' AND JobI.Labor = '1'
                                                                    AND JobI.fDate >= '2013-01-01 00:00:00.000' AND JobI.fDate < '2018-01-01 00:00:00.000'
                                                            ;");
                                                            $Temp_Labor_5_Year = $r ? sqlsrv_fetch_array($r)['Total_Labor_5_Year'] : 0;
                                                            $r = sqlsrv_query($Paradox,"
                                                                SELECT 
                                                                    SUM([JOBLABOR].[TOTAL COST])     AS Total_Labor_5_Year
                                                                FROM 
                                                                    JOBLABOR
                                                                WHERE 
                                                                    ({$SQL_Jobs})
                                                                    AND convert(date,[WEEK ENDING]) >= '2013-01-01 00:00:00.000'
                                                                    AND convert(date,[WEEK ENDING]) < '2017-03-30 00:00:00.000'
                                                            ;");
                                                            $Total_Labor_5_Year = $r ? sqlsrv_fetch_array($r)['Total_Labor_5_Year'] : 0;
                                                            $r = sqlsrv_query($NEI,"
                                                                SELECT 
                                                                    Sum(JobI.Amount) AS Total_Labor_5_Year
                                                                FROM 
                                                                    Portal.dbo.Master_Account
                                                                    LEFT JOIN nei.dbo.Loc ON Loc.Owner = Master_Account.Customer
                                                                    LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                                                    LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                                                WHERE 
                                                                    Master_Account.Master='{$_GET['ID']}'
                                                                    AND JobI.Type='1' AND JobI.Labor = '1'
                                                                    AND JobI.fDate >= '2017-03-30 00:00:00.000' AND JobI.fDate < '2018-01-01 00:00:00.000'
                                                            ");
                                                            $Total_Labor_5_Year = $r ? $Total_Labor_5_Year + sqlsrv_fetch_array($r)['Total_Labor_5_Year'] : $Total_Labor_5_Year;
                                                            echo substr(money_format('%i',$Total_Labor_5_Year),4);
                                                        ?></td>
                                                    </tr>
                                                    <tr style='border-bottom:1px solid black;'>
                                                        <td><b>Materials</b></td>
                                                        <td><?php 
                                                            $r = sqlsrv_query($NEI,"
                                                                SELECT 
                                                                    Sum(JobI.Amount) AS Total_Materials_2012
                                                                FROM 
                                                                    Portal.dbo.Master_Account
                                                                    LEFT JOIN nei.dbo.Loc ON Loc.Owner = Master_Account.Customer
                                                                    LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                                                    LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                                                WHERE 
                                                                    Master_Account.Master='{$_GET['ID']}'
                                                                    AND JobI.Type='1'
                                                                    AND JobI.fDate >= '2012-01-01 00:00:00.000' AND JobI.fDate < '2013-01-01 00:00:00.000'
                                                            ;");
                                                            $Total_Materials_2012 = $r ? sqlsrv_fetch_array($r)['Total_Materials_2012'] - $Temp_Labor_2012 : 0;
                                                            echo substr(money_format('%i',$Total_Materials_2012),4);
                                                        ?></td>
                                                        <td><?php 
                                                            $r = sqlsrv_query($NEI,"
                                                                SELECT 
                                                                    Sum(JobI.Amount) AS Total_Materials_2013
                                                                FROM 
                                                                    Portal.dbo.Master_Account
                                                                    LEFT JOIN nei.dbo.Loc ON Loc.Owner = Master_Account.Customer
                                                                    LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                                                    LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                                                WHERE 
                                                                    Master_Account.Master='{$_GET['ID']}'
                                                                    AND JobI.Type='1'
                                                                    AND JobI.fDate >= '2013-01-01 00:00:00.000' AND JobI.fDate < '2014-01-01 00:00:00.000'
                                                            ;");
                                                            $Total_Materials_2013 = $r ? sqlsrv_fetch_array($r)['Total_Materials_2013'] - $Temp_Labor_2013 : 0;
                                                            echo substr(money_format('%i',$Total_Materials_2013),4);
                                                        ?></td>
                                                        <td><?php 
                                                            $r = sqlsrv_query($NEI,"
                                                                SELECT 
                                                                    Sum(JobI.Amount) AS Total_Materials_2014
                                                                FROM 
                                                                    Portal.dbo.Master_Account
                                                                    LEFT JOIN nei.dbo.Loc ON Loc.Owner = Master_Account.Customer
                                                                    LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                                                    LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                                                WHERE 
                                                                    Master_Account.Master='{$_GET['ID']}'
                                                                    AND JobI.Type='1'
                                                                    AND JobI.fDate >= '2014-01-01 00:00:00.000' AND JobI.fDate < '2015-01-01 00:00:00.000'
                                                            ;");
                                                            $Total_Materials_2014 = $r ? sqlsrv_fetch_array($r)['Total_Materials_2014'] - $Temp_Labor_2014 : 0;
                                                            echo substr(money_format('%i',$Total_Materials_2014),4);
                                                        ?></td>
                                                        <td><?php 
                                                            $r = sqlsrv_query($NEI,"
                                                                SELECT 
                                                                    Sum(JobI.Amount) AS Total_Materials_2015
                                                                FROM 
                                                                    Portal.dbo.Master_Account
                                                                    LEFT JOIN nei.dbo.Loc ON Loc.Owner = Master_Account.Customer
                                                                    LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                                                    LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                                                WHERE 
                                                                    Master_Account.Master='{$_GET['ID']}'
                                                                    AND JobI.Type='1'
                                                                    AND JobI.fDate >= '2015-01-01 00:00:00.000' AND JobI.fDate < '2016-01-01 00:00:00.000'
                                                            ;");
                                                            $Total_Materials_2015 = $r ? sqlsrv_fetch_array($r)['Total_Materials_2015'] - $Temp_Labor_2015 : 0;
                                                            echo substr(money_format('%i',$Total_Materials_2015),4);
                                                        ?></td>
                                                        <td><?php 
                                                            $r = sqlsrv_query($NEI,"
                                                                SELECT 
                                                                    Sum(JobI.Amount) AS Total_Materials_2016
                                                                FROM 
                                                                    Portal.dbo.Master_Account
                                                                    LEFT JOIN nei.dbo.Loc ON Loc.Owner = Master_Account.Customer
                                                                    LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                                                    LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                                                WHERE 
                                                                    Master_Account.Master='{$_GET['ID']}'
                                                                    AND JobI.Type='1'
                                                                    AND JobI.fDate >= '2016-01-01 00:00:00.000' AND JobI.fDate < '2017-01-01 00:00:00.000'
                                                            ;");
                                                            $Total_Materials_2016 = $r ? sqlsrv_fetch_array($r)['Total_Materials_2016'] - $Temp_Labor_2016 : 0;
                                                            echo substr(money_format('%i',$Total_Materials_2016),4);
                                                        ?></td>
                                                        <td><?php 
                                                            $r = sqlsrv_query($NEI,"
                                                                SELECT 
                                                                    Sum(JobI.Amount) AS Total_Materials_2017
                                                                FROM 
																	Portal.dbo.Master_Account
                                                                    LEFT JOIN nei.dbo.Loc ON Loc.Owner = Master_Account.Customer
                                                                    LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                                                    LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                                                WHERE 
                                                                    Master_Account.Master='{$_GET['ID']}'
                                                                    AND JobI.Type='1'
                                                                    AND JobI.fDate >= '2017-01-01 00:00:00.000' AND JobI.fDate < '2018-01-01 00:00:00.000'
                                                            ;");
                                                            $Total_Materials_2017 = $r ? sqlsrv_fetch_array($r)['Total_Materials_2017'] - $Temp_Labor_2017 : 0;
                                                            echo substr(money_format('%i',$Total_Materials_2017),4);
                                                        ?></td>
                                                        <td><?php 
                                                            $r = sqlsrv_query($NEI,"
                                                                SELECT 
                                                                    Sum(JobI.Amount) AS Total_Materials_3_Year
                                                                FROM 
                                                                    Portal.dbo.Master_Account
                                                                    LEFT JOIN nei.dbo.Loc ON Loc.Owner = Master_Account.Customer
                                                                    LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                                                    LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                                                WHERE 
                                                                    Master_Account.Master='{$_GET['ID']}'
                                                                    AND JobI.Type='1'
                                                                    AND JobI.fDate >= '2015-01-01 00:00:00.000' AND JobI.fDate < '2018-01-01 00:00:00.000'
                                                            ;");
                                                            $Total_Materials_3_Year = $r ? sqlsrv_fetch_array($r)['Total_Materials_3_Year'] - $Temp_Labor_3_Year : 0;
                                                            echo substr(money_format('%i',$Total_Materials_3_Year),4);
                                                        ?></td>
                                                        <td><?php 
                                                            $r = sqlsrv_query($NEI,"
                                                                SELECT 
                                                                    Sum(JobI.Amount) AS Total_Materials_5_Year
                                                                FROM 
                                                                    Portal.dbo.Master_Account
                                                                    LEFT JOIN nei.dbo.Loc ON Loc.Owner = Master_Account.Customer
                                                                    LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                                                    LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                                                WHERE 
                                                                    Master_Account.Master='{$_GET['ID']}'
                                                                    AND JobI.Type='1'
                                                                    AND JobI.fDate >= '2013-01-01 00:00:00.000' AND JobI.fDate < '2018-01-01 00:00:00.000'
                                                            ;");
                                                            $Total_Materials_5_Year = $r ? sqlsrv_fetch_array($r)['Total_Materials_5_Year'] - $Temp_Labor_5_Year : 0;
                                                            echo substr(money_format('%i',$Total_Materials_5_Year),4);
                                                        ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td><b>Net Income</b></td>
                                                        <td><?php
                                                            $Total_Net_Income_2012 = $Total_Revenue_2012 - ($Total_Labor_2012 + $Total_Materials_2012);
                                                            echo substr(money_format('%i',$Total_Net_Income_2012),4,99);
                                                        ?></td>
                                                        <td><?php
                                                            $Total_Net_Income_2013 = $Total_Revenue_2013 - ($Total_Labor_2013 + $Total_Materials_2013);
                                                            echo substr(money_format('%i',$Total_Net_Income_2013),4,99);
                                                        ?></td>
                                                        <td><?php
                                                            $Total_Net_Income_2014 = $Total_Revenue_2014 - ($Total_Labor_2014 + $Total_Materials_2014);
                                                            echo substr(money_format('%i',$Total_Net_Income_2014),4,99);
                                                        ?></td>
                                                        <td><?php
                                                            $Total_Net_Income_2015 = $Total_Revenue_2015 - ($Total_Labor_2015 + $Total_Materials_2015);
                                                            echo substr(money_format('%i',$Total_Net_Income_2015),4,99);
                                                        ?></td>
                                                        <td><?php
                                                            $Total_Net_Income_2016 = $Total_Revenue_2016 - ($Total_Labor_2016 + $Total_Materials_2016);
                                                            echo substr(money_format('%i',$Total_Net_Income_2016),4,99);
                                                        ?></td>
                                                        <td><?php
                                                            $Total_Net_Income_2017 = $Total_Revenue_2017 - ($Total_Labor_2017 + $Total_Materials_2017);
                                                            echo substr(money_format('%i',$Total_Net_Income_2017),4,99);
                                                        ?></td>
                                                        <td><?php
                                                            $Total_Net_Income_3_Year = $Total_Revenue_3_Year - ($Total_Labor_3_Year + $Total_Materials_3_Year);
                                                            echo substr(money_format('%i',$Total_Net_Income_3_Year),4,99);
                                                        ?></td>
                                                        <td><?php
                                                            $Total_Net_Income_5_Year = $Total_Revenue_5_Year - ($Total_Labor_5_Year + $Total_Materials_5_Year);
                                                            echo substr(money_format('%i',$Total_Net_Income_5_Year),4,99);
                                                        ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td><b>Overhead %</b></td>
                                                        <td>16.08%</td>
                                                        <td>14.50%</td>
                                                        <td>17.70%</td>
                                                        <td>17.91%</td>
                                                        <td>15.20%</td>
                                                        <td>16.20%</td>
                                                        <td>Cumulative</td>
                                                        <td>Cumulative</td>
                                                    </tr>
                                                    <tr style='border-bottom:1px solid black;'>
                                                        <td><b>Overhead Cost</b></td>
                                                        <td><?php 
                                                            $Overhead_Cost_2012 = $Total_Revenue_2012 * .1608;
                                                            echo substr(money_format('%i',$Overhead_Cost_2012),4);
                                                        ?></td>
                                                        <td><?php 
                                                            $Overhead_Cost_2013 = $Total_Revenue_2013 * .1450;
                                                            echo substr(money_format('%i',$Overhead_Cost_2013),4);
                                                        ?></td>
                                                        <td><?php 
                                                            $Overhead_Cost_2014 = $Total_Revenue_2014 * .1770;
                                                            echo substr(money_format('%i',$Overhead_Cost_2014),4);
                                                        ?></td>
                                                        <td><?php 
                                                            $Overhead_Cost_2015 = $Total_Revenue_2015 * .1791;
                                                            echo substr(money_format('%i',$Overhead_Cost_2015),4);
                                                        ?></td>
                                                        <td><?php 
                                                            $Overhead_Cost_2016 = $Total_Revenue_2016 * .1520;
                                                            echo substr(money_format('%i',$Overhead_Cost_2016),4);
                                                        ?></td>
                                                        <td><?php 
                                                            $Overhead_Cost_2017 = $Total_Revenue_2017 * .1620;
                                                            echo substr(money_format('%i',$Overhead_Cost_2017),4);
                                                        ?></td>
                                                        <td><?php 
                                                            $Overhead_Cost_3_Year = $Overhead_Cost_2015 + $Overhead_Cost_2016 + $Overhead_Cost_2017;
                                                            echo substr(money_format('%i',$Overhead_Cost_3_Year),4);
                                                        ?></td>
                                                        <td><?php 
                                                            $Overhead_Cost_5_Year = $Overhead_Cost_2013 + $Overhead_Cost_2014 + $Overhead_Cost_3_Year;
                                                            echo substr(money_format('%i',$Overhead_Cost_5_Year),4);
                                                        ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td><b>Profit</b></td>
                                                        <td><?php 
                                                            $Total_Profit_2012 = $Total_Revenue_2012 - ($Total_Labor_2012 + $Total_Materials_2012 + $Overhead_Cost_2012);
                                                            echo substr(money_format('%i',$Total_Profit_2012),4);
                                                        ?></td>
                                                        <td><?php 
                                                            $Total_Profit_2013 = $Total_Revenue_2013 - ($Total_Labor_2013 + $Total_Materials_2013 + $Overhead_Cost_2013);
                                                            echo substr(money_format('%i',$Total_Profit_2013),4);
                                                        ?></td>
                                                        <td><?php 
                                                            $Total_Profit_2014 = $Total_Revenue_2014 - ($Total_Labor_2014 + $Total_Materials_2014 + $Overhead_Cost_2014);
                                                            echo substr(money_format('%i',$Total_Profit_2014),4);
                                                        ?></td>
                                                        <td><?php 
                                                            $Total_Profit_2015 = $Total_Revenue_2015 - ($Total_Labor_2015 + $Total_Materials_2015 + $Overhead_Cost_2015);
                                                            echo substr(money_format('%i',$Total_Profit_2015),4);
                                                        ?></td>
                                                        <td><?php 
                                                            $Total_Profit_2016 = $Total_Revenue_2016 - ($Total_Labor_2016 + $Total_Materials_2016 + $Overhead_Cost_2016);
                                                            echo substr(money_format('%i',$Total_Profit_2016),4);
                                                        ?></td>
                                                        <td><?php 
                                                            $Total_Profit_2017 = $Total_Revenue_2017 - ($Total_Labor_2017 + $Total_Materials_2017 + $Overhead_Cost_2017);
                                                            echo substr(money_format('%i',$Total_Profit_2017),4);
                                                        ?></td>
                                                        <td><?php 
                                                            $Total_Profit_3_Year = $Total_Revenue_3_Year - ($Total_Labor_3_Year + $Total_Materials_3_Year + $Overhead_Cost_3_Year);
                                                            echo substr(money_format('%i',$Total_Profit_3_Year),4);
                                                        ?></td>
                                                        <td><?php 
                                                            $Total_Profit_5_Year = $Total_Revenue_5_Year - ($Total_Labor_5_Year + $Total_Materials_5_Year + $Overhead_Cost_5_Year);
                                                            echo substr(money_format('%i',$Total_Profit_5_Year),4);
                                                        ?></td>
                                                    </tr>
													<tr><?php 
																	function percent($number){
																		return $number * 100 . '%';
																	}
																	
																	?>
                                                        <td><b>Profit</b></td>
                                                        <td><?php 
                                                            $Total_Profit_Percentage_2012 = $Total_Profit_2012 / $Total_Revenue_2012;
                                                            echo percent($Total_Profit_Percentage_2012);
                                                        ?></td>
                                                        <td><?php 
                                                            $Total_Profit_Percentage_2013 = $Total_Profit_2013 / $Total_Revenue_2013;
                                                            echo percent($Total_Profit_Percentage_2013);
                                                        ?></td>
                                                        <td><?php 
                                                            $Total_Profit_Percentage_2014 = $Total_Profit_2014 / $Total_Revenue_2014;
                                                            echo percent($Total_Profit_Percentage_2014);
                                                        ?></td>
                                                        <td><?php 
                                                            $Total_Profit_Percentage_2015 = $Total_Profit_2015 / $Total_Revenue_2015;
                                                            echo percent($Total_Profit_Percentage_2015);
                                                        ?></td>
                                                        <td><?php 
                                                            $Total_Profit_Percentage_2016 = $Total_Profit_2016 / $Total_Revenue_2016;
                                                            echo percent($Total_Profit_Percentage_2016);
                                                        ?></td>
                                                        <td><?php 
                                                            $Total_Profit_Percentage_2017 = $Total_Profit_2017 / $Total_Revenue_2017;
                                                            echo percent($Total_Profit_Percentage_2017);
                                                        ?></td>
                                                        <td><?php 
                                                           $Total_Profit_Percentage_3_Year = $Total_Profit_3_Year / $Total_Revenue_3_Year;
                                                            echo percent($Total_Profit_Percentage_3_Year);
                                                        ?></td>
                                                        <td><?php 
                                                            $Total_Profit_Percentage_5_Year = $Total_Profit_5_Year / $Total_Revenue_5_Year;
                                                            echo percent($Total_Profit_Percentage_5_Year);
                                                        ?></td>
                                                    </tr>
                                                </tbody><?php }?>
                                            </table>
                                            <br />
                                            <div class='row'><div class="col-lg-12">
                                                <div class="panel panel-blue">
                                                    <div class="panel-heading">Profit</div>
                                                    <div class="panel-body">
                                                        <div class="flot-chart"><div class="flot-chart-content" id="flot-placeholder-profit-billed-ratio"></div></div>
                                                    </div>
                                                </div>  
                                            </div></div>
                                        </div>
                                        <div class="tab-pane fade in" id="financials-job-analysis-pills">  
                                            <div id='Job_Analysis'>&nbsp;</div>
                                            <div class='input-group'>
                                                <button class='form-control' onClick="analyzeJobs();">Analyze Profit of Selected Jobs</button>
                                            </div>

                                            <br />
                                            <table id='Table_Jobs_for_Anaylsis' class='display' cellspacing='0' width='100%'>
                                                <thead>
                                                    <th>ID</th>
                                                    <th>Name</th>
                                                    <th>Type</th>
                                                    <th>Date</th>
                                                    <th>Status</th>
                                                </thead>
                                               <tfooter>
                                                    <th>ID</th>
                                                    <th>Name</th>
                                                    <th>Type</th>
                                                    <th>Date</th>
                                                    <th>Status</th>
                                                </tfooter>
                                            </table>
                                        </div>
                                        <div class="tab-pane fade in" id="financials-location-analysis-pills">  
                                            <div id='Location_Analysis'>&nbsp;</div>
                                            <div class='input-group'>
                                                <button class='form-control' onClick="analyzeLocations();">Analyze Profit of Selected Locations</button>
                                            </div>

                                            <br />
                                            <table id='Table_Locations_for_Anaylsis' class='display' cellspacing='0' width='100%'>
                                                <thead>
                                                    <th title="Location's ID">ID</th>
                                                    <th title="Location's Name State ID">Name</th>
                                                    <th title="Location's Tag">Tag</th>
                                                    <th title="Location's Street">Street</th>
                                                    <th title="Location's City">City</th>
                                                    <th title="Location's State">State</th>
                                                    <th title="Location's Zip">Zip</th>
                                                    <th title="Location's Route">Route</th>
                                                    <th title="Location's Zone">Zone</th>
                                                </thead>
                                               <tfooter><th title="Location's ID">ID</th><th title="Location's Name State ID">Name</th><th title="Location's Tag">Tag</th><th title="Location's Street">Street</th><th title="Location's City">City</th><th title="Location's State">State</th><th title="Location's Zip">Zip</th><th title="Location's Route">Route</th><th title="Location's Zone">Zone</th></tfooter>
                                            </table>
                                        </div>
                                    </div>
                                </div><?php }?>
                                <div class="tab-pane fade in" id="locations-pills">
                                    <table id='Table_Locations' class='display' cellspacing='0' width='100%'>
                                        <thead>
                                            <th title="Location's ID">ID</th>
                                            <th title="Location's Name State ID">Name</th>
                                            <th title="Location's Tag">Tag</th>
                                            <th title="Location's Street">Street</th>
                                            <th title="Location's City">City</th>
                                            <th title="Location's State">State</th>
                                            <th title="Location's Zip">Zip</th>
                                            <th title="Location's Route">Route</th>
                                            <th title="Location's Zone">Zone</th>
                                        </thead>
                                       <tfooter><th title="Location's ID">ID</th><th title="Location's Name State ID">Name</th><th title="Location's Tag">Tag</th><th title="Location's Street">Street</th><th title="Location's City">City</th><th title="Location's State">State</th><th title="Location's Zip">Zip</th><th title="Location's Route">Route</th><th title="Location's Zone">Zone</th></tfooter>
                                    </table>
                                </div>
                                <div class="tab-pane fade in" id="units-pills">
                                    <table id='Table_Units' class='display' cellspacing='0' width='100%'>
                                        <thead>
                                            <th title="Unit's ID">ID</th>
                                            <th title='Unit State ID'>State</th>
                                            <th title="Unit's Label">Unit</th>
                                            <th title="Type of Unit">Type</th>
                                            <th title="Unit's Location">Location</th>
                                        </thead>
                                       <tfooter><th title="Unit's ID">ID</th><th title='Unit State ID'>State</th><th title="Unit's Label">Unit</th><th title="Type of Unit">Type</th><th title="Unit's Location">Location</th></tfooter>
                                    </table>
                                    <?php /*<div class="col-lg-6">
                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                Unit Type
                                            </div>
                                            <div class="panel-body">
                                                <div class="flot-chart">
                                                    <div class="flot-chart-content" id="flot-pie-chart-units-type"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>*/?>
                                </div>
                                <div class="tab-pane fade" id="jobs-pills">
                                    <table id='Table_Jobs' class='display' cellspacing='0' width='100%'>
                                        <thead>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Location</th>
                                            <th>Type</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                        </thead>
                                       <tfooter><th>ID</th><th>Name</th><th>Location</th><th>Type</th><th>Date</th><th>Status</th></tfooter>
                                    </table>
                                    <!--<div class="col-lg-6">
                                        <div class="panel panel-red">
                                            <div class="panel-heading">Ticket Hours by Job Type</div>
                                            <div class="panel-body"><div class="flot-chart"><div class="flot-chart-content" id="flot-pie-chart-ticket-jobs"></div></div></div></div></div>
                                    <div class="col-lg-6">
                                        <div class="panel panel-blue">
                                            <div class="panel-heading">Ticket Hours by Job Type</div>
                                            <div class="panel-body"><div class="flot-chart"><div class="flot-chart-content" id="flot-placeholder-bar-graph-job-type-hours"></div></div></div></div>  </div>-->
                                </div>
                                <div class="tab-pane fade in" id="violations-pills">
                                    <table id='Table_Violations' class='display' cellspacing='0' width='100%'>
                                        <thead>
                                            <th title='ID of the Violation'>ID</th>
                                            <th title='Name of the Violation'>Name</th>
                                            <th title="Date of the Violation">Date</th>
                                            <th title='Status of the Violation'>Status</th>
                                            <th title='Description of the Violation'>Description</th>
                                        </thead>
                                       <tfooter><th title='ID of the Violation'>ID</th><th title='Name of the Violation'>Name</th><th title="Date of the Violation">Date</th><th title='Status of the Violation'>Status</th><th title='Description of the Violation'>Description</th></tfooter>
                                    </table>
                                </div>
                                <div class="tab-pane fade in" id="tickets-pills">
                                    <div class='input-group'>
                                        <button class='form-control' onClick="expandTickets(this);">Expand Tickets</button>
                                    </div>
                                    <table id='Table_Tickets' class='display' cellspacing='0' width='100%'>
                                        <thead>
                                            <th></th>
                                            <th title='ID of the Ticket'>ID</th>
                                            <th>Location</th>
                                            <th title='Description of the Ticket'>First Name</th>
                                            <th>Last Name</th>
                                            <th title='Scheduled Work Time'>Date</th>
                                            <th title='Status of the Ticket'>Status</th>                                            
                                            <th title='Total Hours'>Hours</th>
                                        </thead>
                                    </table>
                                    </br>
                                    <h3>Past Thirty Days Ticket Activity</h3>
                                    <div id='map' style='width:100%;height:650px;'></div>
                                    <!--</br>
                                    <div class='row'><div class="col-lg-12">
                                        <div class="panel panel-yellow">
                                            <div class="panel-heading">Ticket Activity</div>
                                            <div class="panel-body"><div class="flot-chart"><div class="flot-chart-content" id="flot-placeholder-tickets"></div></div></div></div>  
                                    </div></div>
                                    <div class='row'><div class="col-lg-12">
                                        <div class="panel panel-blue">
                                            <div class="panel-heading">Service Call Activity</div>
                                            <div class="panel-body">
                                                <div class="flot-chart"><div class="flot-chart-content" id="flot-placeholder-service-calls"></div></div></div></div></div></div>-->
                                </div>
                                <div class="tab-pane fade in" id="contracts-pills">
                                    <table id='Table_Contracts' class='display' cellspacing='0' width='100%'>
                                        <thead><tr>
                                            <th title=''>Location</th>
                                            <th title=''>Amount</th>
                                            <th title=''>Start</th>
                                            <th title=''>Cycle</th>
                                            <th title=''>Months</th></tr></thead><tfooter><tr><th title=''>Location</th><th title=''>Amount</th><th title=''>Start</th><th title=''>Cycle</th><th title=''>Months</th></tr></tfooter></table>
                                </div>
                                <div class="tab-pane fade in" id="invoices-pills">
                                    <table id='Table_Invoices' class='display' cellspacing='0' width='100%'>
                                        <thead>
                                            <th title='ID of the Invoice'>ID</th>
                                            <th title='Job of the Invoice'>Job</th>
                                            <th title='Location of the Invoice'>Location</th>
                                            <th title='Date of the Invoice'>Date</th>
                                            <th title='Description of the Invoice'>Description</th>
                                            <th title='Total Amount of Invoice'>Amount</th>
                                            <th>Status</th>
                                        </thead></table>
                                    <div class="col-lg-6">
                                        <div class="panel panel-default">
                                            <div class="panel-heading">Revenue by Job Type (This Year)</div>
                                            <div class="panel-body"><div class="flot-chart"><div class="flot-chart-content" id="flot-pie-chart"></div></div></div></div></div>
                                    <div class="col-lg-6">
                                        <div class="panel panel-default">
                                            <div class="panel-heading">Revenue by Location (This Year)</div>
                                            <div class="panel-body"><div class="flot-chart"><div class="flot-chart-content" id="flot-pie-chart-locations"></div></div></div></div></div>
                                </div>
                                <div class="tab-pane fade in" id="proposals-pills">
                                    <table id='Table_Proposals' class='display' cellspacing='0' width='100%'>
                                        <thead>
                                            <th title='Date of the Proposal'>Date</th>
                                            <th title='ID of the Proposal'>ID</th>
                                            <th title='Proposal Contact'>Contact</th>
                                            <th title='Location of the Proposal'>Location</th>
                                            <th title='Title of the Proposal'>Title</th>
                                            <?php if($_SESSION['Branch'] != 'Customer'){?><th title="Proposed Cost">Cost</th><?php }?>
                                            <th title='Proposed Amount'>Price</th>
                                            <th>Status</th>
                                        </thead></table>
                                </div>
                                <div class="tab-pane fade" id="collections-pills">
                                    <div class='input-group'>
                                        <button class='form-control' onClick="expandCollections(this);">Expand Collections</button>
                                    </div>
                                    <br />
                                    <table id='Table_Collections' class='display' cellspacing='0' width='100%'>
                                        <thead><tr>
                                            <th></th>
                                            <th>Invoice #</th>
                                            <th>Date</th>
                                            <th>Due</th>
                                            <th>Original</th>
                                            <th>Balance</th>
											<th>PO</th>
											<th>Location</th>
                                        </tr></thead>
                                        <tfoot>
                                          <tr>
                                              <th></th>
                                              <th>Page Sum</th>
                                              <th></th>
                                              <th></th>
                                              <th></th>
                                              <th></th>
                                          </tr>
                                      </tfoot>
                                    </table>
                                </div>
                               	<div class="tab-pane fade in" id="legal-pills">
                                    <table id='Table_Legal' class='display' cellspacing='0' width='100%'>
                                        <thead>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Type</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                        </thead>
                                       <tfooter>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Type</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                        </tfooter>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap Core JavaScript -->
    <script src="https://www.nouveauelevator.com/vendor/bootstrap/js/bootstrap.min.js"></script>

    <!-- Metis Menu Plugin JavaScript -->
    <script src="https://www.nouveauelevator.com/vendor/metisMenu/metisMenu.js"></script>

    <?php require(PROJECT_ROOT.'js/datatables.php');?>
    <script src="cgi-bin/js/jquery.dataTables.yadcf.js"></script>
    <!-- Custom Theme JavaScript -->
    <script src="../dist/js/sb-admin-2.js"></script>

    <!--Moment JS Date Formatter-->
    <script src="../dist/js/moment.js"></script>

    <!-- JQUERY UI Javascript -->
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

    <!-- Custom Date Filters-->
    <script src="../dist/js/filters.js"></script>
    <style>
    div.column {display:inline-block;vertical-align:top;}
    div.label1 {display:inline-block;font-weight:bold;width:150px;vertical-align:top;}
    div.data {display:inline-block;width:300px;vertical-align:top;}
    div#map * {
        overflow:visible;
    }
    </style>
    <script>
        function initialize() {
            var latlng = {lat: 40.7130, lng:-74.0060};
            var myOptions = {
              zoom: 8,
              center: latlng
            };
            var map = new google.maps.Map(document.getElementById("map"),myOptions);
            var marker = new Array();
            <?php 
            $Start_Date            = date('Y-m-d H:i:s', strtotime('-30 days'));
            $End_Date              = date('Y-m-d H:i:s', strtotime('now'));
            $r = sqlsrv_query($NEI,"
            SELECT 
                TechLocation.*,
                Emp.fFirst AS First_Name,
                Emp.Last,
                Emp.fWork,
                Emp.ID as Employee_ID
            FROM
                nei.dbo.TechLocation
                LEFT JOIN Emp ON TechLocation.TechID = Emp.fWork
                LEFT JOIN nei.dbo.TicketD ON TechLocation.TicketID = TicketD.ID
                LEFT JOIN nei.dbo.Loc ON TicketD.Loc = Loc.Loc
                LEFT JOIN Portal.dbo.Master_Account ON Master_Account.Customer = Loc.Owner
            WHERE
                TechLocation.DateTimeRecorded >= ?
                AND TechLocation.DateTimeRecorded <= ?
                AND Master_Account.Master = ?
                AND TechLocation.TicketID <> '1797285'
        ;",array($Start_Date,$End_Date,$_GET['ID']));
        $GPS_Locations = array("General"=>array());
        while($array = sqlsrv_fetch_array($r)){
            if(!isset($GPS_Locations[$array['TicketID']])){$GPS_Locations[$array['TicketID']] = array("General"=>array());}
            if($array['ActionGroup'] == "General"){$GPS_Locations['General'][$array['ID']] = $array;}
            elseif(in_array($array['ActionGroup'],array("On site time","Completed time"))){$GPS_Locations[$array['TicketID']][$array['ActionGroup']] = $array;}
        }
        $GPS = $GPS_Locations;
        $Now_Location = array();
        foreach($GPS_Locations as $key=>$GPS_Location){
            if($key == "General"){continue;}
            if(!isset($GPS_Location['Completed time'])){$Now = $GPS_Location['On site time'];break;}
        }
        $GPS = $GPS_Locations;
        foreach($GPS_Locations["General"] as $ID=>$General_Location){
            if(strtotime($General_Location['DateTimeRecorded']) >= strtotime($Now_Location['DateTimeRecorded'])){$GPS[$Now_Location['TicketID']]['General'][$General_Location['ID']] = $General_Location;}
            else {
                $Temp = $GPS_Locations;
                unset($Temp['General']);
                foreach($Temp as $key=>$value){
                    if(strtotime($value['On site time']['DateTimeRecorded']) <= strtotime($General_Location['DateTimeRecorded']) && strtotime($value['Completed time']) >= strtotime($General_Location['DateTimeRecorded'])){$GPS[$key]['General'][$General_Location['ID']] = $General_Location;unset($GPS['General']);break;}
                }
            }
        }
        foreach($GPS as $key=>$array){
            if($_GET['Type'] == 'Live' && isset($array['Completed time'])){continue;}
            if($key == "General"){continue;}
            if(isset($array['On site time'])){
                $GPS_Location = $array['On site time'];
                ?>
                marker[<?php echo $key;?>] = new google.maps.Marker({
                  position: {lat:<?php echo $GPS_Location['Latitude'];?>,lng:<?php echo $GPS_Location['Longitude'];?>},
                  map: map,
                  title: '<?php echo $GPS_Location['fFirst'] . " " . $GPS_Location['Last'];?> -- <?php echo $GPS_Location['DateTimeRecorded'];?> -- <?php echo $GPS_Location['TicketID'];?>',
                  Icon: 'http://vps9073.inmotionhosting.com/~skeera6/portal/images/GoogleMapsMarkers/<?php if($GPS_Location['ActionGroup'] == 'Completed time'){?>green_MarkerC<?php }
                  elseif($GPS_Location['ActionGroup'] == 'On site time'){?>yellow_MarkerO<?php }
                elseif($GPS_Location['ActionGroup'] == 'General'){?>paleblue_MarkerG<?php }?>.png'
                })
                marker[<?php echo $key?>].addListener('click',function(){
                    window.open('ticket.php?ID=<?php echo $GPS_Location['TicketID'];?>','_blank');
                });
            <?php }
            if(isset($array['Completed time'])){
                $GPS_Location = $array['Completed time'];
                ?>
                marker[<?php echo $key;?>] = new google.maps.Marker({
                  position: {lat:<?php echo $GPS_Location['Latitude'];?>,lng:<?php echo $GPS_Location['Longitude'];?>},
                  map: map,
                  title: '<?php echo $GPS_Location['fFirst'] . " " . $GPS_Location['Last'];?> -- <?php echo $GPS_Location['DateTimeRecorded'];?> -- <?php echo $GPS_Location['TicketID'];?>',
                  Icon: 'http://vps9073.inmotionhosting.com/~skeera6/portal/images/GoogleMapsMarkers/<?php if($GPS_Location['ActionGroup'] == 'Completed time'){?>green_MarkerC<?php }
                  elseif($GPS_Location['ActionGroup'] == 'On site time'){?>yellow_MarkerO<?php }
                elseif($GPS_Location['ActionGroup'] == 'General'){?>paleblue_MarkerG<?php }?>.png'
                });
                marker[<?php echo $key?>].addListener('click',function(){
                    window.open('ticket.php?ID=<?php echo $GPS_Location['TicketID'];?>','_blank');
                });
            <?php }
            foreach($array['General'] as $k=>$GPS_Location){?>
                marker[<?php echo $k;?>] = new google.maps.Marker({
                  position: {lat:<?php echo $GPS_Location['Latitude'];?>,lng:<?php echo $GPS_Location['Longitude'];?>},
                  map: map,
                  title: '<?php echo $GPS_Location['fFirst'] . " " . $GPS_Location['Last'];?> -- <?php echo $GPS_Location['DateTimeRecorded'];?> -- <?php echo $GPS_Location['TicketID'];?>',
                  Icon: 'http://vps9073.inmotionhosting.com/~skeera6/portal/images/GoogleMapsMarkers/<?php if($GPS_Location['ActionGroup'] == 'Completed time'){?>green_MarkerC<?php }
                  elseif($GPS_Location['ActionGroup'] == 'On site time'){?>yellow_MarkerO<?php }
                elseif($GPS_Location['ActionGroup'] == 'General'){?>paleblue_MarkerG<?php }?>.png'
                });
                marker[<?php echo $k?>].addListener('click',function(){
                    window.open('ticket.php?ID=<?php echo $GPS_Location['TicketID'];?>','_blank');
                });
            <?php }
            }?>google.maps.event.trigger(map, 'resize');
        }
        var selected = [];
        function hrefLocations(){hrefRow("Table_Locations","location");}
        function hrefTickets(){hrefRow("Table_Tickets","ticket");}
        function hrefInvoices(){hrefRow("Table_Invoices","invoice");}
        function hrefProposals(){}
        function hrefUnits(){hrefRow("Table_Units","unit");}
        function hrefJobs(){hrefRow("Table_Jobs","job");}
        function hrefLegal(){hrefRow("Table_Legal","job");}
        function hrefViolations(){hrefRow("Table_Violations","violation");}
        function hrefCollections(){hrefRow("Table_Collections","invoice");}
		function hrefEmployees(){/*NEEDS TO BE ADDED*/}
        function hrefContracts(){
            var table = "Table_Contracts";
            $("#" + table + " tbody tr").each(function(){$(this).on('click',function(){window.open("https://drive.google.com/drive/folders/0ByFVts4kTa6_eWZCZG5hSUZIU3M","_blank");});});
        }
        var expandTicketButton = true;
        function expandTickets(link){
            $("Table#Table_Tickets tbody tr td:first-child").each(function(){$(this).click();});
            if(expandTicketButton){$(link).html("Collapse Tickets");} 
			else {$(link).html("Expand Tickets");}
            expandTicketButton = !expandTicketButton;
        }
        var expandCollectionButton = true;
        function expandCollections(link){
            $("Table#Table_Collections tbody tr td:first-child").each(function(){$(this).click();});
            if(expandCollectionButton){$(link).html("Collapse Collections");} 
			else {$(link).html("Expand Collections");}
            expandCollectionButton = !expandCollectionButton;
        }
        var expandMaintenanceButton = true;
        function expandMaintenance(link){
            $("Table#Table_Tickets_Maintenance tbody tr td:first-child").each(function(){$(this).click();});
            if(expandMaintenanceButton){$(link).html("Collapse Tickets");} 
			else {$(link).html("Expand Tickets");}
			expandMaintenanceButton = !expandMaintenanceButton;
        }
        var expandServiceCallsButton = true;
        function expandServiceCalls(link){
            $("Table#Table_Tickets_Service_Calls tbody tr td:first-child").each(function(){$(this).click();});
            if(expandTicketButton){$(link).html("Collapse Tickets");} 
			else {$(link).html("Expand Tickets");}
            expandServiceCallsButton = !expandServiceCallsButton;
        }
        function analyzeJobs(){
            var Jobs_to_Analyze = [];
            $("Table#Table_Jobs_for_Anaylsis tbody tr.selected").each(function(){Jobs_to_Analyze.push($(this).children("td:first-child").html());});
            Jobs_to_Analyze = Jobs_to_Analyze.join(",");
            $.ajax({
                url:"js/chart/jobs_profit.php",
                data:"Jobs=" + Jobs_to_Analyze,
                success:function(code){$("div#Job_Analysis").html(code);}
            })
        }
        function analyzeLocations(){
            var Locations_to_Analyze = [];
            $("Table#Table_Locations_for_Anaylsis tbody tr.selected").each(function(){Locations_to_Analyze.push($(this).children("td:first-child").html());});
            Locations_to_Analyze = Locations_to_Analyze.join(",");
            $.ajax({
                url:"js/chart/locations_profit.php",
                data:"Locations=" + Locations_to_Analyze,
                success:function(code){$("div#Location_Analysis").html(code);}
            })
        }
        function standardizeTime(time){
            time = time.split(':');
            var hours = Number(time[0]);
            var minutes = Number(time[1]);
            var seconds = Number(time[2]);
            var timeValue;
            if (hours > 0 && hours <= 12){timeValue= "" + hours;} 
			else if (hours > 12){timeValue= "" + (hours - 12);}
            else if (hours == 0){timeValue= "12";}
            timeValue += (minutes < 10) ? ":0" + minutes : ":" + minutes;
            timeValue += (seconds < 10) ? ":0" + seconds : ":" + seconds;
            timeValue += (hours >= 12) ? " P.M." : " A.M.";
            return timeValue;
        }
        function format ( d ) {
            var TimeSite = ' ';
            var TimeComp = ' ';
            if(d.TimeComp){TimeComp = standardizeTime(d.TimeComp.substr(10,9));}
            if(d.TimeSite){TimeSite = standardizeTime(d.TimeSite.substr(10,9));}
            return "<div>"+
                "<div>"+
                    "<div class='column'>"+
                        "<div class='Account'><div class='label1'>Account:</div><div class='data'>"+d.Account+"</div></div>"+
                        "<div class='Location'><div class='label1'>Location:</div><div class='data'>"+d.Location+"</div></div>"+
                        "<div class='Address'><div class='label1'>Address:</div><div class='data'>"+d.Street+"</div></div>"+
                        "<div class='Address'><div class='label1'>&nbsp;</div><div class='data'>"+d.City+" ,"+d.City+" "+d.Zip+"</div></div>"+
                        "<div class='Caller'><div class='label1'>Caller:</div><div class='data'>"+d.Caller+"</div></div>"+
                        "<div class='Taken_By'><div class='label1'>Taken By:</div><div class='data'>"+d.Taken_By+"</div></div>"+
                    "</div>"+
                    "<div class='column'>"+
                        "<div class='Created'><div class='label1'>Created:</div><div class='data'>"+d.CDate+"</div></div>"+
                        "<div class='Dispatched'><div class='label1'>Dispatched:</div><div class='data'>"+d.EDate+"</div></div>"+
                        "<div class='Type'><div class='label1'>Type:</div><div class='data'>"+d.Job_Type+"</div></div>"+
                        "<div class='Level'><div class='label1'>Level:</div><div class='data'>"+d.Level+"</div></div>"+
                        "<div class='Category'><div class='label1'>Category:</div><div class='data'>"+d.Category+"</div></div>"+
                    "</div>"+
                    "<div class='column'>"+
                        "<div class='Regular'><div class='label1'>On Site:</div><div class='data'>"+TimeSite+"</div></div>"+
                        "<div class='Regular'><div class='label1'>Completed:</div><div class='data'>"+TimeComp+"</div></div>"+
                        "<div class='Regular'><div class='label1'>Regular:</div><div class='data'>"+d.Regular+"</div></div>"+
                        "<div class='OT'><div class='label1'>OT:</div><div class='data'>"+d.Overtime+"</div></div>"+
                        "<div class='Doubletime'><div class='label1'>DT:</div><div class='data'>"+d.Doubletime+"</div></div>"+
                        "<div class='Total'><div class='label1'>Total</div><div class='data'>"+d.Total+"</div></div>"+
                    "</div>"+
                "</div>"+
                "<div>"+
                    "<div class='column' style='width:45%;vertical-align:top;'>"+
                        "<div><b>Scope of Work</b></div>"+
                        "<div><pre>"+d.fDesc+"</div>"+
                    "</div>"+
                    "<div class='column' style='width:45%;vertical-align:top;'>"+
                        "<div><b>Resolution</b></div>"+
                        "<div><pre>"+d.DescRes+"</div>"+
                    "</div>"+
                "</div>"+
            '</div>'+
            "<div><a href='ticket.php?ID="+d.ID+"' target='_blank'>View Ticket</a></div>"
        }
        function formatCollection ( d ) {
            return '<table cellpadding="5" cellspacing="0" border="0" style="padding-left:50px;">'+
                '<tr>'+
                    '<td>Location:</td>'+
                    '<td>'+d.Tag+'</td>'+
                '</tr>'+
                '<tr>'+
                    '<td>Description:</td>'+
                    '<td>'+d.Description+'</td>'+
                '</tr>'+
                '<tr>'+
                    '<td><a href="invoice.php?ID='+d.Invoice+'"  target="_blank"><?php $Icons->Collection();?>View Invoice</a></td>'+
                '</tr>'+
            '</table>';
        }
        $(document).ready(function(){
            var Table_Locations = $('#Table_Locations').DataTable( {
                "ajax": "cgi-bin/php/get/Locations_by_Master.php?ID=<?php echo $_GET['ID'];?>",
                "columns": [
                    { "data": "ID" },
                    { "data": "Name"},
                    { "data": "Tag"},
                    { "data": "Street"},
                    { "data": "City"},
                    { "data": "State"},
                    { "data": "Zip"},
                    { "data": "Route"},
                    { "data": "Zone",
                        render: function(data){
                            if(data == '1'){return "Base";}
                            if(data == '2'){return "Division #1";}
                            if(data == '3'){return "Division #2";}
                            if(data == '4'){return "Division #4";}
                            if(data == '5'){return "Division #5";}
                            if(data == '6'){return "Repair";}
                        }
                    }
                ],
                "order": [[1, 'asc']],
                "language":{
                    "loadingRecords":""
                },
                "initComplete":function(){
                }

            } );
            $("Table#Table_Locations").on("draw.dt",function(){hrefLocations();});
            yadcf.init(Table_Locations,[
                {   column_number:0,
                    filter_type:"auto_complete"},
                {   column_number:1,
                    filter_type:"auto_complete"},
                {   column_number:2,
                    filter_type:"auto_complete"},
                {   column_number:3,
                    filter_type:"auto_complete"},
                {   column_number:4},
                {   column_number:5},
                {   column_number:6},
                {   column_number:7},
                {   column_number:8},
            ]);
            var Table_Tickets_Maintenance = $('#Table_Tickets_Maintenance').DataTable( {
                "ajax": {
                    "url":"cgi-bin/php/get/Tickets_by_Master_Maintenance.php?ID=<?php echo $_GET['ID'];?>",
                    "dataSrc":function(json){if(!json.data){json.data = [];}return json.data;}
                },
                "lengthMenu":[[10,25,50,100,500,-1,0],[10,25,50,100,500,"All","None"]],
                "columns": [
                    {
                        "className":      'details-control',
                        "orderable":      false,
                        "data":           null,
                        "defaultContent": ''
                    },
                    { "data": "ID" },
                    { "data": "Worker_First_Name"},
                    { "data": "Worker_Last_Name"},
                    { 
                        "data": "EDate",
                        render: function(data){if(data){return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);} else{return null;}}
                    },
                    { "data": "Status"},
                    { 
                        "data": "Total",
                        "defaultContent":"0"
                    },
                    {
                        "data":"Unit_State",
                        "visible":false,
                        "searchable":true
                    },
                    {
                        "data":"Unit_Label",
                        "visible":false,
                        "searchable":true
                    }
                ],
                "order": [[1, 'asc']],
                "language":{
                    "loadingRecords":""
                },
                "initComplete":function(){

                }
            } );
            $('#Table_Tickets_Maintenance tbody').on('click', 'td.details-control', function () {
                var tr = $(this).closest('tr');
                var row = Table_Tickets_Maintenance.row( tr );
         
                if ( row.child.isShown() ) {
                    row.child.hide();
                    tr.removeClass('shown');
                }
                else {
                    row.child( format(row.data()) ).show();
                    tr.addClass('shown');
                }
            } );
            <?php if(!$Mobile){?>
            yadcf.init(Table_Tickets_Maintenance,[
                {   column_number:1,
                    filter_type:"auto_complete"},
                {   column_number:2},
                {   column_number:3},
                {   column_number:4,
                    filter_type: "range_date",
                    date_format: "mm/dd/yyyy",
                    filter_delay: 500},
                {   column_number:5},
                {   column_number:6,
                    filter_type: "range_number_slider",
                    filter_delay: 500}
            ]);
            stylizeYADCF();<?php }?>
            $("Table#Table_Tickets_Maintenance").on("draw.dt",function(){
                if(!expandTicketButton){$("Table#Table_Tickets_Maintenance tbody tr:not(.shown) td:first-child").each(function(){$(this).click();});} 
                else {$("Table#Table_Tickets_Maintenance tbody tr.shown td:first-child").each(function(){$(this).click();});}
            });
            var Table_Tickets_Service_Calls = $('#Table_Tickets_Service_Calls').DataTable( {
                "ajax": {
                    "url":"cgi-bin/php/get/Tickets_by_Master_Service_Calls.php?ID=<?php echo $_GET['ID'];?>",
                    "dataSrc":function(json){if(!json.data){json.data = [];}return json.data;}
                },
                "lengthMenu":[[10,25,50,100,500,-1,0],[10,25,50,100,500,"All","None"]],
                "columns": [
                    {
                        "className":      'details-control',
                        "orderable":      false,
                        "data":           null,
                        "defaultContent": ''
                    },
                    { "data": "ID" },
                    { "data": "Worker_First_Name"},
                    { "data": "Worker_Last_Name"},
                    { 
                        "data": "EDate",
                        render: function(data){if(data){return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);} else{return null;}}
                    },
                    { "data": "Status"},
                    { 
                        "data": "Total",
                        "defaultContent":"0"
                    },
                    {
                        "data":"Unit_State",
                        "visible":false,
                        "searchable":true
                    },
                    {
                        "data":"Unit_Label",
                        "visible":false,
                        "searchable":true
                    }
                ],
                "order": [[1, 'asc']],
                "language":{
                    "loadingRecords":""
                },
                "initComplete":function(){

                }
            } );
            $('#Table_Tickets_Service_Calls tbody').on('click', 'td.details-control', function () {
                var tr = $(this).closest('tr');
                var row = Table_Tickets_Service_Calls.row( tr );
         
                if ( row.child.isShown() ) {
                    row.child.hide();
                    tr.removeClass('shown');
                }
                else {
                    row.child( format(row.data()) ).show();
                    tr.addClass('shown');
                }
            } );
            <?php if(!$Mobile){?>
            yadcf.init(Table_Tickets_Service_Calls,[
                {   column_number:1,
                    filter_type:"auto_complete"},
                {   column_number:2},
                {   column_number:3},
                {   column_number:4,
                    filter_type: "range_date",
                    date_format: "mm/dd/yyyy",
                    filter_delay: 500},
                {   column_number:5},
                {   column_number:6,
                    filter_type: "range_number_slider",
                    filter_delay: 500}
            ]);
            stylizeYADCF();<?php }?>
            $("Table#Table_Tickets_Service_Calls").on("draw.dt",function(){
                if(!expandTicketButton){$("Table#Table_Tickets_Service_Calls tbody tr:not(.shown) td:first-child").each(function(){$(this).click();});} 
                else {$("Table#Table_Tickets_Service_Calls tbody tr.shown td:first-child").each(function(){$(this).click();});}
            });
            var Table_Jobs = $('#Table_Jobs').DataTable( {
                "ajax": {
                    "url":"cgi-bin/php/get/Jobs_by_Master.php?ID=<?php echo $_GET['ID'];?>",
                    "dataSrc":function(json){
                        if(!json.data){json.data = [];}
                        return json.data;}
                },
                "columns": [
                    { "data": "ID" },
                    { "data": "Name"},
                    { "data": "Location"},
                    { "data": "Type"},
                    { 
                        "data": "Finished_Date",
                        render: function(data){return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}
                    },
                    { "data": "Status"}
                ],
                "order": [[1, 'asc']],
                "language":{"loadingRecords":""},
                "initComplete":function(){
                }
            } );
            <?php if(!$Mobile){?>
            yadcf.init(Table_Jobs,[
                {   column_number:1,
                    filter_type:"auto_complete"},
                {   column_number:2},
                {   column_number:3},
                {   column_number:4,
                    filter_type: "range_date",
                    date_format: "mm/dd/yyyy",
                    filter_delay: 500},
                {   column_number:5}
            ]);
            stylizeYADCF();<?php }?>
            $("Table#Table_Jobs").on("draw.dt",function(){hrefJobs();});
            var Table_Contracts = $('#Table_Contracts').DataTable( {
                "ajax": "cgi-bin/php/get/Contracts_by_Master.php?ID=<?php echo $_GET['ID'];?>",
                "columns": [
                    { "data": "Location"},
                    { "data": "Amount"},
                    { "data": "Billing_Start"},
                    { 
                        "data": "Billing_Cycle",
                        render:function(data){
                            switch(data){
                                case 0:return 'Monthly';
                                case 1:return 'Bi-Monthly';
                                case 2:return 'Quarterly';
                                case 3:return 'Trimester';
                                case 4:return 'Semi-Annualy';
                                case 5:return 'Annually';
                                case 6:return 'Never';}}
                    },
                    { "data": "Billing_Length"}
                ],
                "order": [[1, 'asc']],
                "language":{
                    "loadingRecords":""
                },
                "initComplete":function(){hrefContracts();}

            } );
            $("Table#Table_Contracts").on("draw.dt",function(){hrefContracts();});
            yadcf.init(Table_Contracts,[
                {   column_number:2,
                    filter_type: "range_date",
                    date_format: "mm/dd/yyyy",
                    filter_delay: 500},
                {   column_number:3}
            ]);
            var Table_Invoices = $('#Table_Invoices').DataTable( {
                "ajax": "cgi-bin/php/get/Invoices_by_Master.php?ID=<?php echo $_GET['ID'];?>",
                "columns": [
                    { "data" : "ID" },
                    { "data" : "Job"},
                    { "data" : "Location"},
                    { 
                        "data": "fDate",
                        render: function(data){if(data){return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);} else {return null;}}
                    },
                    { "data" : "Description"},
                    { "data" : "Total"},
                    { "data" : "Status",
                        render:function(data){
                            if(data == "0"){return "Open";}
                            else if(data == "1"){return "Paid"}
                            else {return "Partial";}
                        }
                    }
                ],
                "order": [[1, 'asc']],
                "language":{ 
                    "loadingRecords":""
                },
                "initComplete":function(){
                }

            } );
            $("Table#Table_Invoices").on("draw.dt",function(){hrefInvoices();});
            yadcf.init(Table_Invoices,[
                {   column_number:1},
                {   column_number:2},
                {   column_number:3,
                    filter_type: "range_date",
                    date_format: "mm/dd/yyyy",
                    filter_delay: 500},
                {   column_number:4,
                    filter_type:"auto_complete"},
                {   column_number:6}
            ]);
            var Table_Proposals = $('#Table_Proposals').DataTable( {
                "ajax": "cgi-bin/php/get/Proposals_by_Master.php?ID=<?php echo $_GET['ID'];?>",
                "columns": [
                    { 
                        "data": "fDate",
                        "defaultContent":"Undated",
                        render: function(data) {return data.substring(0,10);}
                    },
                    { "data": "ID" },
                    { "data": "Contact"},
                    { "data": "Location"},
                    { "data": "Title"},
                    <?php if($_SESSION['Branch'] != 'Customer'){?>{ "data": "Cost"},<?php }?>
                    { "data": "Price"},
                    { "data": "Status",
                        render:function(data) {
                            if(data == '1'){return "Open";}
                            if(data == '2'){return "Canceled";}
                            if(data == '3'){return "Withdrawn";}
                            if(data == '4'){return "Awarded";}
                            if(data == '5'){return "Disqualified";}
                            return "Open";
                        }
                    }

                ],
                "order": [[1, 'asc']],
                "language":{ 
                    "loadingRecords":""
                },
                "initComplete":function(){
                }
            } );
            $("Table#Table_Proposals").on("draw.dt",function(){hrefProposals();});
            <?php if(!$Mobile){?>
            yadcf.init(Table_Proposals,[
                {   column_number:0,
                    filter_type: "range_date",
                    date_format: "mm/dd/yyyy",
                    filter_delay: 500},
                {   column_number:2},
                {   column_number:3},
                {   column_number:4,
                    filter_type:"auto_complete"},
                {   column_number:7}
            ]);
            stylizeYADCF();
            <?php }?>
            var Table_Jobs_for_Anaylsis = $('#Table_Jobs_for_Anaylsis').DataTable( {
                "ajax": {
                    "url":"cgi-bin/php/get/Jobs_by_Master.php?ID=<?php echo $_GET['ID'];?>",
                    "dataSrc":function(json){
                        if(!json.data){json.data = [];}
                        return json.data;}
                },
                "columns": [
                    { "data": "ID" },
                    { "data": "Name"},
                    { "data": "Type"},
                    { 
                        "data": "Finished_Date",
                        render: function(data) {return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}
                    },
                    { "data": "Status"}
                ],
                "lengthMenu":[[10,25,50,100,500,-1,0],[10,25,50,100,500,"All","None"]],
                "order": [[1, 'asc']],
                "language":{"loadingRecords":""},
                "initComplete":function(){},
                "rowCallback": function( row, data ) {
                    if ( $.inArray(data.DT_RowId, selected) !== -1 ) {
                        $(row).addClass('selected');
                    }
                }
            } );
            $('#Table_Jobs_for_Anaylsis tbody').on('click', 'tr', function () {
                var id = this.id;
                var index = $.inArray(id, selected);
         
                if ( index === -1 ) {
                    selected.push( id );
                } else {
                    selected.splice( index, 1 );
                }
         
                $(this).toggleClass('selected');
            } );
            var Table_Locations_for_Anaylsis = $('#Table_Locations_for_Anaylsis').DataTable( {
                "ajax": {
                    "url":"cgi-bin/php/get/Locations_by_Master.php?ID=<?php echo $_GET['ID'];?>",
                    "dataSrc":function(json){
                        if(!json.data){json.data = [];}
                        return json.data;}
                },
                "columns": [
                    { "data": "ID" },
                    { "data": "Name"},
                    { "data": "Tag"},
                    { "data": "Street"},
                    { "data": "City"},
                    { "data": "State"},
                    { "data": "Zip"},
                    { "data": "Route"},
                    { "data": "Zone"}
                ],
                "lengthMenu":[[10,25,50,100,500,-1,0],[10,25,50,100,500,"All","None"]],
                "order": [[1, 'asc']],
                "language":{"loadingRecords":""},
                "initComplete":function(){
                },
                "rowCallback": function( row, data ) {
                    if ( $.inArray(data.DT_RowId, selected) !== -1 ) {
                        $(row).addClass('selected');
                    }
                }
            } );
            $('#Table_Locations_for_Anaylsis tbody').on('click', 'tr', function () {
                var id = this.id;
                var index = $.inArray(id, selected);
         
                if ( index === -1 ) {
                    selected.push( id );
                } else {
                    selected.splice( index, 1 );
                }
         
                $(this).toggleClass('selected');
            } );
            var Table_Units = $('#Table_Units').DataTable( {
                "ajax": "cgi-bin/php/get/Units_by_Master.php?ID=<?php echo $_GET['ID'];?>",
                "columns": [
                    { "data": "ID" },
                    { "data": "State"},
                    { "data": "Unit"},
                    { "data": "Type"},
                    { "data": "Location"}
                ],
                "order": [[1, 'asc']],
                "language":{
                    "loadingRecords":""
                },
                "initComplete":function(){
                }

            } );
            $("Table#Table_Units").on("draw.dt",function(){hrefUnits();});
            yadcf.init(Table_Units,[
                {   column_number:1,
                    filter_type:"auto_complete"},
                {   column_number:2,
                    filter_type:"auto_complete"},
                {   column_number:3},
                {   column_number:4}
            ]);
            var Table_Violations = $('#Table_Violations').DataTable( {
                "ajax": {
                    "url":"cgi-bin/php/get/Violations_by_Master.php?ID=<?php echo $_GET['ID'];?>",
                    "dataSrc":function(json){if(!json.data){json.data = [];}return json.data;}
                },
                "columns": [
                    { "data": "ID" },
                    { "data": "Name"},
                    { "data": "Date"},
                    { "data": "Status"},
                    { "data": "Description"}
                ],
                "order": [[1, 'asc']],
                "language":{"loadingRecords":""},
                "lengthMenu":[[10,25,50,100,500,-1,0],[10,25,50,100,500,"All","None"]],
                "initComplete":function(){}
            } );
            $("Table#Table_Violations").on("draw.dt",function(){hrefViolations();});
            <?php if(!$Mobile){?>
            yadcf.init(Table_Violations,[
                {   column_number:2,
                    filter_type: "range_date",
                    date_format: "mm/dd/yyyy",
                    filter_delay: 500},
                {   column_number:3},
                {   column_number:4,
                    filter_type:"auto_complete"}
            ]);
            stylizeYADCF();<?php }?>
            var Table_Tickets = $('#Table_Tickets').DataTable( {
                "ajax": {
                    "url":"cgi-bin/php/get/Tickets_by_Master.php?ID=<?php echo $_GET['ID'];?>",
                    "dataSrc":function(json){if(!json.data){json.data = [];}return json.data;}
                },
                "columns": [
                    {
                        "className":      'details-control',
                        "orderable":      false,
                        "data":           null,
                        "defaultContent": ''
                    },
                    { "data": "ID" },
                    { "data": "Tag"},
                    { "data": "Worker_First_Name"},
                    { "data": "Worker_Last_Name"},
                    { 
                        "data": "EDate",
                        render: function(data){if(data){return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);} else {return null;}}
                    },
                    { "data": "Status"},
                    { 
                        "data": "Total",
                        "defaultContent":"0"
                    },
                    {
                        "data":"Unit_State",
                        "visible":false,
                        "searchable":true
                    },
                    {
                        "data":"Unit_Label",
                        "visible":false,
                        "searchable":true
                    }
                ],
                "lengthMenu":[[10,25,50,100,500,-1,0],[10,25,50,100,500,"All","None"]],
                "order": [[1, 'asc']],
                "language":{
                    "loadingRecords":""
                },
                "initComplete":function(){

                }
            } );
            $('#Table_Tickets tbody').on('click', 'td.details-control', function () {
                var tr = $(this).closest('tr');
                var row = Table_Tickets.row( tr );
         
                if ( row.child.isShown() ) {
                    row.child.hide();
                    tr.removeClass('shown');
                }
                else {
                    row.child( format(row.data()) ).show();
                    tr.addClass('shown');
                }
            } );
            <?php if(!$Mobile){?>
            yadcf.init(Table_Tickets,[
                {   column_number:1,
                    filter_type:"auto_complete"},
                {   column_number:2},
                {   column_number:3},
                {   column_number:4},
                {   column_number:5,
                    filter_type: "range_date",
                    date_format: "mm/dd/yyyy",
                    filter_delay: 500},
                {   column_number:6},
                {   column_number:7,
                    filter_type: "range_number_slider",
                    filter_delay: 500}
            ]);
            stylizeYADCF();<?php }?>
            $("Table#Table_Tickets").on("draw.dt",function(){
                if(!expandTicketButton){$("Table#Table_Tickets tbody tr:not(.shown) td:first-child").each(function(){$(this).click();});} 
                else {$("Table#Table_Tickets tbody tr.shown td:first-child").each(function(){$(this).click();});}
            });
            var Table_Collections = $('#Table_Collections').DataTable( {
                "ajax": {
                    "url":"cgi-bin/php/get/Collections_by_Master.php?ID=<?php echo $_GET['ID'];?>",
                    "dataSrc":function(json){if(!json.data){json.data = [];}return json.data;}
                },
                "scrollX": true,
                "columns": [
                    {
                        "className":      'details-control',
                        "orderable":      false,
                        "data":           null,
                        "defaultContent": ''
                    },
                    { "data": "Invoice" },
                    { "data": "Dated",
                        render: function(data){return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}},
                    { "data": "Due",
                        render: function(data){return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}},
                    { "data": "Original", className:"sum"},
                    { "data": "Balance", className:"sum"},
					{ "data": "Purchase_Order"},
					{ "data": "Tag"}
                ],
				"buttons":[
					{
						extend: 'collection',
						text: 'Export',
						buttons: [
							'copy',
							'excel',
							'csv',
							'pdf',
							'print'
						]
					}
				],
                <?php require("js/datatableOptions.php");?>
            } );
            $('#Table_Collections tbody').on('click', 'td.details-control', function () {
                var tr = $(this).closest('tr');
                var row = Table_Collections.row( tr );
         
                if ( row.child.isShown() ) {
                    row.child.hide();
                    tr.removeClass('shown');
                }
                else {
                    row.child( formatCollection(row.data()) ).show();
                    tr.addClass('shown');
                }
            } );
            <?php if(!$Mobile){?>
            yadcf.init(Table_Collections,[
                {   column_number:1,
                    filter_type:"auto_complete"},
                {   column_number:2,
                    filter_type: "range_date",
                    date_format: "mm/dd/yyyy",
                    filter_delay: 500},
                {   column_number:3,
                    filter_type: "range_date",
                    date_format: "mm/dd/yyyy",
                    filter_delay: 500},
                {   column_number:4,
                    filter_type: "range_number_slider",
                    filter_delay: 500},
                {   column_number:5,
                    filter_type: "range_number_slider",
                    filter_delay: 500}
            ]);
            stylizeYADCF();<?php }?>
            $("Table#Table_Collections").on("draw.dt",function(){
                if(!expandCollectionButton){$("Table#Table_Collections tbody tr:not(.shown) td:first-child").each(function(){$(this).click();});} 
                else {$("Table#Table_Collections tbody tr.shown td:first-child").each(function(){$(this).click();});}
            });
            var Table_Workers = $('#Table_Workers').DataTable( {
                "ajax": {
                    "url":"cgi-bin/php/get/Workers_by_Master.php?ID=<?php echo $_GET['ID'];?>",
                    "dataSrc":function(json){if(!json.data){json.data = [];}return json.data;}
                },
                "columns": [
                    {   "data": "ID",
                        "visible":true},
                    { "data": "Last_Name" },
                    { "data": "First_Name"}<?php if(isset($My_Privileges['Ticket']['Other_Privilege']) && $My_Privileges['Ticket']['Other_Privilege'] >= 4){?>,
                    { "data": "Regular",className:"sum"},
                    { "data": "Overtime",className:"sum"},
                    { "data": "Doubletime",className:"sum"},
                    { "data": "Total",className:"sum"}<?php }?>
                ],
                "order": [[1, 'asc']],
                "language":{ 
                    "loadingRecords":""
                },
                "lengthMenu":[[10,25,50,100,500,-1,0],[10,25,50,100,500,"All","None"]],
                "initComplete":function(){
                    <?php if($_SESSION['Branch'] != 'Customer'){?>
                    $("table#Table_Workers tr[role='row']>th:nth-child(1)").click().click();
                    hrefEmployees();
                    $("input[type='search'][aria-controls='Table_Workers']").on('keyup',function(){hrefEmployees();});       
                    $('#Table_Workers').on( 'page.dt', function () {setTimeout(function(){hrefEmployees();},100);});
                    $("#Table_Workers th").on("click",function(){setTimeout(function(){hrefEmployees();},100);});
                    <?php }?>
                }<?php if(!$Field){?>,
                "footerCallback": function(row, data, start, end, display) {
                    var api = this.api();

                    api.columns('.sum', { page: 'current' }).every(function () {
                        var sum = api
                            .cells( null, this.index(), { page: 'current'} )
                            .render('display')
                            .reduce(function (a, b) {
                                var x = parseFloat(a) || 0;
                                var y = parseFloat(b) || 0;
                                return x + y;
                            }, 0);
                        $(this.footer()).html(sum);
                    });
                }<?php }?>
            } );
            <?php if(!$Mobile){?>
            yadcf.init(Table_Workers,[
                {   column_number:1,
                    filter_type:"auto_complete"},
                {   column_number:2,
                    filter_type:"auto_complete"}<?php if(isset($My_Privileges['Ticket']['Other_Privilege']) && $My_Privileges['Ticket']['Other_Privilege'] >= 4){?>,
                {   column_number:3,
                    filter_type: "range_number_slider",
                    filter_delay: 500},
                {   column_number:4,
                    filter_type: "range_number_slider",
                    filter_delay: 500},
                {   column_number:5,
                    filter_type: "range_number_slider",
                    filter_delay: 500},
                {   column_number:6,
                    filter_type: "range_number_slider",
                    filter_delay: 500}<?php }?>
            ]);
            stylizeYADCF();
            <?php }?>
            <?php if($My_Privileges['Legal']['Group_Privilege'] >= 4){?>
            var Table_Legal = $('#Table_Legal').DataTable( {
                "ajax": {
                    "url":"cgi-bin/php/get/Legal_by_Master.php?ID=<?php echo $_GET['ID'];?>",
                    "dataSrc":function(json){
                        if(!json.data){json.data = [];}
                        return json.data;}
                },
                "lengthMenu":[[10,25,50,100,500,-1,0],[10,25,50,100,500,"All","None"]],
                "columns": [
                    { "data": "ID" },
                    { "data": "Name"},
                    { "data": "Type"},
                    { 
                        "data": "Finished_Date",
                        render: function(data) {return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}
                    },
                    { "data": "Status"}
                ],
                "order": [[1, 'asc']],
                "language":{"loadingRecords":""},
                "initComplete":function(){hrefLegal();}
            } );
            $("Table#Table_Legal").on("draw.dt",function(){hrefLegal();});
            <?php if(!$Mobile){?>
            yadcf.init(Table_Legal,[
                {   column_number:0,
                    filter_type:"auto_complete"},
                {   column_number:1,
                    filter_type:"auto_complete"},
                {   column_number:2},
                {   column_number:3,
                    filter_type: "range_date",
                    date_format: "mm/dd/yyyy",
                    filter_delay: 500},
                {   column_number:4}
            ]);
            stylizeYADCF();
            <?php }?>
            <?php }?>
        });
    </script>
    <?php require('cgi-bin/js/flotcharts.php');?>
    <?php /*require(PROJECT_ROOT."js/chart/invoices_this_year_for_customer.php");*/?>
    <?php /*require(PROJECT_ROOT."js/chart/tickets_this_year_for_customer.php");?>
    <?php require(PROJECT_ROOT."js/chart/service_calls_this_year_for_customer.php");?>
    <?php if((!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Elevator') && (isset($My_Privileges['Financials']) && $My_Privileges['Financials']['Other_Privilege'] >= 4)){
        require(PROJECT_ROOT."js/chart/customer_profit.php");
    }?>
    <?php require(PROJECT_ROOT."js/pie/invoices_by_job_type_for_customer.php");?>
    <?php require(PROJECT_ROOT."js/pie/invoices_by_location_for_customer.php");?>
    <?php require(PROJECT_ROOT.'js/pie/type_by_unit_for_customer.php');?>
    <?php require(PROJECT_ROOT.'js/pie/tickets_by_job_type_for_customer.php');*/?>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=customer<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>