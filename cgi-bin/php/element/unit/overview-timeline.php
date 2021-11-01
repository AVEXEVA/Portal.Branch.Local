<?php
session_start();
require('../../../php/index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Elevator'){
        sqlsrv_query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "unit.php"));
        $r= sqlsrv_query($NEI,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID= ?",array($_SESSION['User']));
        $My_User = sqlsrv_fetch_array($r);
        $Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
        $r = sqlsrv_query($Portal,"
            SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
            FROM   Portal.dbo.Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
        $My_Privileges = array();
        while($My_Privilege = sqlsrv_fetch_array($r)){$My_Privileges[$My_Privilege['Access_Table']] = $My_Privilege;}
        $Privileged = FALSE;
        if(isset($My_Privileges['Unit']) && $My_Privileges['Unit']['User_Privilege'] >= 4 && $My_Privileges['Unit']['Group_Privilege'] >= 4 && $My_Privileges['Unit']['Other_Privilege'] >= 4){$Privileged = TRUE;}
        elseif(isset($My_Privileges['Unit']) && $My_Privileges['Unit']['User_Privilege'] >= 4 && $My_Privileges['Unit']['Group_Privilege'] >= 4){
            $r = sqlsrv_query($NEI,"
				SELECT * 
				FROM   TicketO 
					   LEFT JOIN nei.dbo.Loc  ON TicketO.LID   = Loc.Loc
					   LEFT JOIN nei.dbo.Elev ON Loc.Loc       = Elev.Loc
					   LEFT JOIN nei.dbo.Emp  ON TicketO.fWork = Emp.fWork
				WHERE  Emp.ID      = ?
					   AND Elev.ID = ?
            ;",array($_SESSION['User'],$_GET['ID']));
            $r2 = sqlsrv_query($NEI,"
				SELECT * 
				FROM   TicketD 
					   LEFT JOIN nei.dbo.Loc  ON TicketD.Loc   = Loc.Loc
					   LEFT JOIN nei.dbo.Elev ON Loc.Loc       = Elev.Loc
					   LEFT JOIN nei.dbo.Emp  ON TicketD.fWork = Emp.fWork
				WHERE  Emp.ID      = ?
					   AND Elev.ID = ?
            ;",array($_SESSION['User'],$_GET['ID']));
            $r3 = sqlsrv_query($NEI,"
				SELECT * 
				FROM   TicketDArchive
					   LEFT JOIN nei.dbo.Loc  ON TicketDArchive.Loc   = Loc.Loc
					   LEFT JOIN nei.dbo.Elev ON Loc.Loc              = Elev.Loc
					   LEFT JOIN nei.dbo.Emp  ON TicketDArchive.fWork = Emp.fWork
				WHERE  Emp.ID      = ?
					   AND Elev.ID = ?
            ;",array($_SESSION['User'],$_GET['ID']));
            $r = sqlsrv_fetch_array($r);
            $r2 = sqlsrv_fetch_array($r2);
            $r3 = sqlsrv_fetch_array($r2);
            $Privileged = (is_array($r) || is_array($r2) || is_array($r3)) ? TRUE : FALSE;
        }
    } elseif($_SESSION['Branch'] == 'Customer' && is_numeric($_GET['ID'])){
        $SQL_Result = sqlsrv_query($NEI,"
            SELECT Elev.Owner 
            FROM   nei.dbo.Elev 
            WHERE  Elev.ID        = ? 
			       AND Elev.Owner = ?
        ;",array($_GET['ID'],$_SESSION['Branch_ID']));
        if($SQL_Result){
            $sql = sqlsrv_fetch_array($SQL_Result);
            if($sql){$Privileged = true;}
        }
    }
    if(!isset($array['ID'])  || !$Privileged || !(is_numeric($_GET['ID']) || is_numeric($_POST['ID']))){?><html><head><script>document.location.href="../login.php?Forward=unit<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {
       $r = sqlsrv_query($NEI,
			"SELECT TOP 1
					Loc.Loc              AS Location_ID,
					Loc.ID               AS Name,
					Loc.Tag              AS Tag,
					Loc.Address          AS Street,
					Loc.City             AS City,
					Loc.State            AS State,
					Loc.Zip              AS Zip,
					Loc.Balance          as Location_Balance,
					Zone.Name            AS Zone,
					Loc.Route            AS Route_ID,
					Emp.ID               AS Route_Mechanic_ID,
					Emp.fFirst           AS Route_Mechanic_First_Name,
					Emp.Last             AS Route_Mechanic_Last_Name,
					Loc.Owner            AS Customer_ID,
					OwnerWithRol.Name    AS Customer_Name,
					OwnerWithRol.Balance AS Customer_Balance,
					Terr.Name            AS Territory_Domain/*,
					Sum(SELECT Location.ID FROM Loc AS Location WHERE Location.Owner='Loc.Owner') AS Customer_Locations*/
			FROM    Loc
					LEFT JOIN nei.dbo.Zone         ON Loc.Zone   = Zone.ID
					LEFT JOIN nei.dbo.Route        ON Loc.Route  = Route.ID
					LEFT JOIN nei.dbo.Emp          ON Route.Mech = Emp.fWork
					LEFT JOIN nei.dbo.OwnerWithRol ON Loc.Owner  = OwnerWithRol.ID
					LEFT JOIN Terr         		   ON Terr.ID    = Loc.Terr
			WHERE
					Loc.Loc = ?
		;",array($_GET['ID']));
		$Location = sqlsrv_fetch_array($r);?>
<div class='row' style='font-size:20px;'><?php 
$Timeline = array();
$SQL_Completed_Tickets = sqlsrv_query($NEI,"
	SELECT Tickets.ID,
		   Tickets.EDate  AS Date,
		   Tickets.Object AS Object,
		   'Completed'    AS Field,
		   Tickets.Level  AS Level
	FROM   ((SELECT  TicketO.ID,
					 TicketO.EDate,
					 TicketO.Level,
					 'TicketO' AS Object
			FROM     nei.dbo.TicketO
					 LEFT JOIN nei.dbo.Job ON TicketO.Job = Job.ID
			WHERE    TicketO.LElev = ?
					 AND TicketO.Assigned = 4)
			UNION ALL
			(SELECT  TicketD.ID,
					 TicketD.EDate,
					 TicketD.Level,
					 'TicketD' AS Object
			FROM     nei.dbo.TicketD
					 LEFT JOIN nei.dbo.Job ON TicketD.Job = Job.ID
			WHERE    TicketD.Elev = ?)
			UNION ALL
			(SELECT  TicketDArchive.ID,
					 TicketDArchive.EDate,
					 TicketDArchive.Level,
					 'TicketDArchive' AS Object
			FROM     nei.dbo.TicketDArchive
					 LEFT JOIN nei.dbo.Job ON TicketDArchive.Job = Job.ID
			WHERE    TicketDArchive.Elev = ?)) AS Tickets
	WHERE Tickets.EDate >= dateadd(month,-6,getdate())
	ORDER BY Tickets.EDate DESC
;",array($_GET['ID'],$_GET['ID'],$_GET['ID']));
if($SQL_Completed_Tickets){while($Ticket = sqlsrv_fetch_array($SQL_Completed_Tickets)){
	if(!isset($Timeline[date('Y-m-d',strtotime($Ticket['Date']))])){$Timeline[date('Y-m-d',strtotime($Ticket['Date']))] = array();}
	$Timeline[date('Y-m-d',strtotime($Ticket['Date']))][] = $Ticket; 
}}
$SQL_Created_Tickets = sqlsrv_query($NEI,"
	SELECT Tickets.ID,
		   Tickets.CDate  AS Date,
		   Tickets.Object AS Object,
		   'Created'      AS Field,
		   Tickets.Level  AS Level
	FROM   ((SELECT  TicketO.ID,
					 TicketO.CDate,
					 TicketO.Level,
					 'TicketO' AS Object
			FROM     nei.dbo.TicketO
					 LEFT JOIN nei.dbo.Job ON TicketO.Job = Job.ID
			WHERE    TicketO.LElev = ?
					 AND TicketO.Assigned = 4)
			UNION ALL
			(SELECT  TicketD.ID,
					 TicketD.CDate,
					 TicketD.Level,
					 'TicketD' AS Object
			FROM     nei.dbo.TicketD
					 LEFT JOIN nei.dbo.Job ON TicketD.Job = Job.ID
			WHERE    TicketD.Elev = ?)
			UNION ALL
			(SELECT  TicketDArchive.ID,
					 TicketDArchive.CDate,
					 TicketDArchive.Level,
					 'TicketDArchive' AS Object
			FROM     nei.dbo.TicketDArchive
					 LEFT JOIN nei.dbo.Job ON TicketDArchive.Job = Job.ID
			WHERE    TicketDArchive.Elev = ?)) AS Tickets
	WHERE Tickets.CDate >= dateadd(month,-6,getdate())
	ORDER BY Tickets.ID DESC
;",array($_GET['ID'],$_GET['ID'],$_GET['ID']));
if($SQL_Created_Tickets){while($Ticket = sqlsrv_fetch_array($SQL_Created_Tickets)){
	if(!isset($Timeline[date('Y-m-d',strtotime($Ticket['Date']))])){$Timeline[date('Y-m-d',strtotime($Ticket['Date']))] = array();}
	$Timeline[date('Y-m-d',strtotime($Ticket['Date']))][] = $Ticket; 
}}
$SQL_Completed_Jobs = sqlsrv_query($NEI,"
	SELECT Job.ID,
		   Job.CloseDate AS Date,
		   'Job'         AS Object,
		   'Completed'   AS Field
	FROM   nei.dbo.Job
	WHERE  Job.Elev = ?
		   AND Job.CloseDate <> ''
	ORDER BY Job.CloseDate DESC
;",array($_GET['ID']));
if($SQL_Completed_Jobs){while($Job = sqlsrv_fetch_array($SQL_Completed_Jobs)){
	//echo $Job['Date'];
	if(date("Y-m-d H:i:s", strtotime("-6 months")) > $Job['Date']){continue;}
	if(!isset($Timeline[date('Y-m-d',strtotime($Job['Date']))])){$Timeline[date('Y-m-d',strtotime($Job['Date']))] = array();}
	$Timeline[date('Y-m-d',strtotime($Job['Date']))][] = $Job; 
}}
$SQL_Created_Jobs = sqlsrv_query($NEI,"
	SELECT Job.ID,
		   Job.fDate  AS Date,
		   'Job'      AS Object,
		   'Created'  AS Field
	FROM   nei.dbo.Job
	WHERE  Job.Elev = ?
		   AND Job.fDate >= dateadd(month,-6,getdate())
	ORDER BY Job.ID DESC
;",array($_GET['ID']));
if($SQL_Created_Jobs){while($Job = sqlsrv_fetch_array($SQL_Created_Jobs)){
	if(!isset($Timeline[date('Y-m-d',strtotime($Job['Date']))])){$Timeline[date('Y-m-d',strtotime($Job['Date']))] = array();}
	$Timeline[date('Y-m-d',strtotime($Job['Date']))][] = $Job; 
}}

$SQL_Created_Estimates = sqlsrv_query($NEI,"
	SELECT Estimate.ID,
		   Estimate.fDate  AS Date,
		   'Proposal'      AS Object,
		   'Created'       AS Field
	FROM   nei.dbo.Estimate
		   LEFT JOIN nei.dbo.Loc ON Loc.Loc = Estimate.LocID
		   LEFT JOIN nei.dbo.Job ON Job.ID  = Estimate.Job
	WHERE  Job.Elev
		   AND Estimate.fDate >= dateadd(month,-6,getdate())
	ORDER BY Estimate.fDate DESC
;",array($_GET['ID']));
if($SQL_Created_Estimates){while($Estimate = sqlsrv_fetch_array($SQL_Created_Estimates)){
	if(!isset($Timeline[date('Y-m-d',strtotime($Estimate['Date']))])){$Timeline[date('Y-m-d',strtotime($Estimate['Date']))] = array();}
	$Timeline[date('Y-m-d',strtotime($Estimate['Date']))][] = $Estimate; 
}}
$SQL_Paid_Invoices = sqlsrv_query($NEI,"
	SELECT Trans.ID      AS ID,
		   Trans.fDate   AS Date,
		   'Transaction' AS Object,
		   'Paid'        AS Field,
		   Trans.Ref     AS Ref
	FROM   nei.dbo.Trans
		   LEFT JOIN nei.dbo.Invoice ON Trans.Ref = Invoice.Ref
		   LEFT JOIN nei.dbo.Job     ON Job.ID    = Invoice.Job
	WHERE  Job.Elev = ?
		   AND Trans.Type = 1
		   AND Trans.fDate >= dateadd(month,-6,getdate())
;",array($_GET['ID']));

if($SQL_Paid_Invoices){while($Payment = sqlsrv_fetch_array($SQL_Paid_Invoices)){

	if(!isset($Timeline[date('Y-m-d',strtotime($Payment['Date']))])){$Timeline[date('Y-m-d',strtotime($Payment['Date']))] = array();}
	$Timeline[date('Y-m-d',strtotime($Payment['Date']))][] = $Payment; 
}}
$SQL_Created_Invoices = sqlsrv_query($NEI,"
	SELECT Invoice.Ref   AS ID,
		   Invoice.fDate AS Date,
		   'Invoice'     AS Object,
		   'Created'     AS Field
	FROM   nei.dbo.Invoice
		   LEFT JOIN nei.dbo.Job ON Job.ID = Invoice.Job
		   AND Invoice.fDate >= dateadd(month,-6,getdate())
	WHERE  Job.Elev = ?
	ORDER BY Job.fDate DESC
;",array($_GET['ID']));
if($SQL_Created_Invoices){while($Invoice = sqlsrv_fetch_array($SQL_Created_Invoices)){
	if(!isset($Timeline[date('Y-m-d',strtotime($Invoice['Date']))])){$Timeline[date('Y-m-d',strtotime($Invoice['Date']))] = array();}
	$Timeline[date('Y-m-d',strtotime($Invoice['Date']))][] = $Invoice; 
}}


$SQL_Created_Violation = sqlsrv_query($NEI,"
	SELECT Violation.ID    AS ID,
		   Violation.fdate AS Date,
		   'Violation'     AS Object,
		   'Created'       AS Field
	FROM   nei.dbo.Violation
		   LEFT JOIN nei.dbo.Loc ON Loc.Loc       = Violation.Loc
		   LEFT JOIN nei.dbo.Job ON Violation.Job = Job.ID
	WHERE  Job.Elev = ?
		   AND Violation.fDate >= dateadd(month,-6,getdate())
	ORDER BY Violation.fDate DESC
;",array($_GET['ID']));
if($SQL_Created_Violation){while($Violation = sqlsrv_fetch_array($SQL_Created_Violation)){
	if(!isset($Timeline[date('Y-m-d',strtotime($Violation['Date']))])){$Timeline[date('Y-m-d',strtotime($Violation['Date']))] = array();}
	$Timeline[date('Y-m-d',strtotime($Violation['Date']))][] = $Violation; 
}}
$SQL_Overdue_Violations = sqlsrv_query($NEI,"
	SELECT *,
		   Violations.Due_Date  AS Date,
		   'Overdue'            AS Field
		FROM
			((SELECT 0					 	   AS ID,
				   Job.fDesc	               AS Name,
				   ''						   AS fDate,
				   'Job Created'   			   AS Status,
				   Loc.Tag                     AS Location,
				   Elev.State                  AS Unit,
				   Job.Custom1                 AS Division,
				   Emp.fFirst + ' ' + Emp.Last AS Mechanic,
				   Job.ID 			           AS Job,
				   Job.Custom16 			   AS Due_Date,
				   '' 						   AS Remarks,
				   'Job'                       AS Object
			FROM   nei.dbo.Job 	
				   LEFT JOIN nei.dbo.Elev  ON Job.Elev       = Elev.ID
				   LEFT JOIN nei.dbo.Loc   ON Job.Loc        = Loc.Loc
				   LEFT JOIN nei.dbo.Zone  ON Loc.Zone       = Zone.ID
				   LEFT JOIN nei.dbo.Route ON Loc.Route      = Route.ID
				   LEFT JOIN nei.dbo.Emp   ON Route.Mech     = Emp.fWork
			WHERE  ((Job.fDesc LIKE '%CAT%' AND Job.fDesc LIKE '%DEF%')
				   OR Job.fDesc LIKE '%PVT%')
				   AND Job.Status = 0
				   AND Job.Elev = ?)
			UNION ALL
			(SELECT Violation.ID               AS ID,
				   Violation.Name              AS Name,
				   Violation.fdate             AS fDate,
				   Violation.Status            AS Status,
				   Loc.Tag                     AS Location,
				   Elev.State                  AS Unit,
				   Zone.Name                   AS Division,
				   Emp.fFirst + ' ' + Emp.Last AS Mechanic,
				   Violation.Job 			   AS Job,
				   SUBSTRING(Violation.Remarks,CHARINDEX('DUE: ',Violation.Remarks)+5,8) AS Due_Date,
				   '' 						   AS Remarks,
				   'Violation'                 AS Object
			FROM   nei.dbo.Violation
				   LEFT JOIN nei.dbo.Elev  ON Violation.Elev = Elev.ID
				   LEFT JOIN nei.dbo.Loc   ON Violation.Loc  = Loc.Loc
				   LEFT JOIN nei.dbo.Zone  ON Loc.Zone       = Zone.ID
				   LEFT JOIN nei.dbo.Route ON Loc.Route      = Route.ID
				   LEFT JOIN nei.dbo.Emp   ON Route.Mech     = Emp.fWork
				   LEFT JOIN nei.dbo.Job   ON Violation.Job  = Job.ID
			WHERE  Violation.Remarks LIKE '%DUE: [0123456789][0123456789]/[0123456789][0123456789]/[0123456789][0123456789]%'
				   AND Violation.Status <> 'Dismissed'
				   AND Violation.ID     <> 0
				   AND Job.Elev = ?
				   AND (Violation.Job = 0
						OR 
						(Violation.Job > 0
						AND Job.Status = 0)))) AS Violations
;",array($_GET['ID'],$_GET['ID'],$_GET['ID']));
if($SQL_Overdue_Violations){while($Violation = sqlsrv_fetch_array($SQL_Overdue_Violations)){
	if(date('Y-m-d') < '20'. substr($Violation['Date'],6,2) . '-' .substr($Violation['Date'],0,2) . '-' . substr($Violation['Date'],3,2)){continue;}
	if(!isset($Timeline['20'. substr($Violation['Date'],6,2) . '-' .substr($Violation['Date'],0,2) . '-' . substr($Violation['Date'],3,2)])){$Timeline['20'. substr($Violation['Date'],6,2) . '-' .substr($Violation['Date'],0,2) . '-' . substr($Violation['Date'],3,2)] = array();}
	$Timeline['20'. substr($Violation['Date'],6,2) . '-' .substr($Violation['Date'],0,2) . '-' . substr($Violation['Date'],3,2)][] = $Violation; 

}}
$SQL_Contract_Starts = sqlsrv_query($NEI,"
	SELECT Contract.Job    AS ID,
		   Contract.BStart AS Date,
		   'Contract'      AS Object,
		   'Starts'        AS Field
	FROM   nei.dbo.Contract
		   LEFT JOIN nei.dbo.Job ON Job.ID = Contract.Job
	WHERE  Job.Elev = ?
		   AND Contract.BStart >= dateadd(month,-6,getdate())
	ORDER BY Contract.BStart DESC
;",array($_GET['ID']));
if($SQL_Contract_Starts){while($Contract = sqlsrv_fetch_array($SQL_Contract_Starts)){
	if(!isset($Timeline[date('Y-m-d',strtotime($Contract['Date']))])){$Timeline[date('Y-m-d',strtotime($Contract['Date']))] = array();}
	$Timeline[date('Y-m-d',strtotime($Contract['Date']))][] = $Contract; 
	$Now                  = new DateTime(date('Y-m-d'));
	$Date                 = new DateTime(date('Y-m-d',strtotime($Contract['Date'])));
	$oneDayDateInterval   = new DateInterval('P1D');
	$oneMonthDateInterval = new DateInterval('P1M');
	$Contract['Field'] = 'Billed';
	while($Now->format('Y-m-d') >= $Date->format('Y-m-d')){
		if(!isset($Timeline[$Date->format('Y-m-d')])){$Timeline[$Date->format('Y-m-d')] = array();}
		$Timeline[$Date->format('Y-m-d')][] = $Contract;
		while($Date->format('d') != 1){$Date->sub($oneDayDateInterval);}
		$Date->add($oneMonthDateInterval);
	}
}}
$SQL_Overdue_Invoices = sqlsrv_query($NEI,"
	SELECT OpenAR.Ref AS ID,
		   OpenAR.Due AS Date,
		   'OpenAR'   AS Object,
		   'Overdue'  AS Field
	FROM   nei.dbo.OpenAR
		   LEFT JOIN nei.dbo.Invoice ON Invoice.Ref = OpenAR.Ref
		   LEFT JOIN nei.dbo.Job     ON Job.ID      = Invoice.Job
		   AND OpenAR.Due >= dateadd(month,-6,getdate())
	WHERE  Job.Elev = ?
	ORDER BY Job.fDate DESC
;",array($_GET['ID']));
if($SQL_Overdue_Invoices){while($Invoice = sqlsrv_fetch_array($SQL_Overdue_Invoices)){
	if(!isset($Timeline[date('Y-m-d',strtotime($Invoice['Date']))])){$Timeline[date('Y-m-d',strtotime($Invoice['Date']))] = array();}
	$Timeline[date('Y-m-d',strtotime($Invoice['Date']))][] = $Invoice; 
}}
krsort($Timeline);
if(count($Timeline) > 0){foreach($Timeline as $Date=>$DayTimeline){
	?>
	<div class='panel panel-primary'>
	<div class='col-md-12' style='background-color:#252525;color:white;'>
		<h3 style='text-align:center;'><?php echo date('m/d/Y',strtotime($Date));?></h3>
	</div><?php
	foreach($DayTimeline as $Instance){
		//$Instance['Date'] = date('m/d/Y',strtotime($Instance['Date']));
		if(substr($Instance['Object'],0,6) == 'Ticket' && $Instance['Field'] == 'Completed'){
			?><div class='col-md-12'><a href='ticket.php?ID=<?php echo $Instance['ID'];?>'><?php $Instance['Date'];?> <?php $Icons->Ticket();?> Completed <?php if($Instance['Level'] == 1){?>Service Call <?php }elseif($Instance['Level'] == 10){?>Preventative Maintenance <?php }?>Ticket #<?php echo $Instance['ID'];?></a></div><?php
		} elseif(substr($Instance['Object'],0,6) == 'Ticket' && $Instance['Field'] == 'Created'){
			?><div class='col-md-12'><a href='ticket.php?ID=<?php echo $Instance['ID'];?>'><?php $Instance['Date'];?> <?php $Icons->Ticket();?> Created <?php if($Instance['Level'] == 1){?>Service Call <?php }elseif($Instance['Level'] == 10){?>Preventative Maintenance <?php }?>Ticket #<?php echo $Instance['ID'];?></a></div><?php
		} elseif(substr($Instance['Object'],0,3) == 'Job' && $Instance['Field'] == 'Created'){
			?><div class='col-md-12'><a href='job.php?ID=<?php echo $Instance['ID'];?>'><?php $Instance['Date'];?> <?php $Icons->Job();?> Created Job #<?php echo $Instance['ID'];?></a></div><?php
		} elseif(substr($Instance['Object'],0,3) == 'Job' && $Instance['Field'] == 'Completed'){
			?><div class='col-md-12'><a href='job.php?ID=<?php echo $Instance['ID'];?>'><?php $Instance['Date'];?> <?php $Icons->Job();?> Completed Job #<?php echo $Instance['ID'];?></a></div><?php
		} elseif(substr($Instance['Object'],0,8) == 'Proposal' && $Instance['Field'] == 'Created'){
			?><div class='col-md-12'><a href='proposal.php?ID=<?php echo $Instance['ID'];?>'><?php $Instance['Date'];?> <?php $Icons->Proposal();?> Created Proposal #<?php echo $Instance['ID'];?></a></div><?php
		} elseif(substr($Instance['Object'],0,7) == 'Invoice' && $Instance['Field'] == 'Created'){
			?><div class='col-md-12'><a href='invoice.php?ID=<?php echo $Instance['ID'];?>'><?php $Instance['Date'];?> <?php $Icons->Invoice();?> Created Invoice #<?php echo $Instance['ID'];?></a></div><?php
		} elseif(substr($Instance['Object'],0,10) == 'Violation' && $Instance['Field'] == 'Created'){
			?><div class='col-md-12'><a href='violation.php?ID=<?php echo $Instance['ID'];?>'><?php $Instance['Date'];?> <?php $Icons->Violation();?> Created Violation #<?php echo $Instance['ID'];?></a></div><?php
		} elseif($Instance['Object'] == 'Transaction' && $Instance['Field'] == 'Paid'){
			?><div class='col-md-12'><a href='transaction.php?ID=<?php echo $Instance['ID'];?>'><?php $Instance['Date'];?> <?php $Icons->Invoice();?> Paid Invoice #<?php echo $Instance['Ref'];?></a></div><?php
		} elseif(substr($Instance['Object'],0,10) == 'Violation' && $Instance['Field'] == 'Overdue'){
			?><div class='col-md-12'><a href='violation.php?ID=<?php echo $Instance['ID'];?>'><?php $Instance['Date'];?> <?php $Icons->Violation();?> Overdue Violation #<?php echo $Instance['ID'];?></a></div><?php
		} elseif(substr($Instance['Object'],0,10) == 'Job' && $Instance['Field'] == 'Overdue'){
			?><div class='col-md-12'><a href='job.php?ID=<?php echo $Instance['Job'];?>'><?php $Instance['Date'];?> <?php $Icons->Violation();?> Overdue Violation Job #<?php echo $Instance['Job'];?></a></div><?php
		} elseif($Instance['Object'] == 'Contract' && $Instance['Field'] == 'Starts'){
			?><div class='col-md-12'><a href='contract.php?ID=<?php echo $Instance['ID'];?>'><?php $Instance['Date'];?> <?php $Icons->Contract();?> Contract Starts Job #<?php echo $Instance['ID'];?></a></div><?php
		} elseif($Instance['Object'] == 'Contract' && $Instance['Field'] == 'Billed'){
			?><div class='col-md-12'><a href='contract.php?ID=<?php echo $Instance['ID'];?>'><?php $Instance['Date'];?> <?php $Icons->Contract();?> Contract Billed Job #<?php echo $Instance['ID'];?></a></div><?php
		} elseif(substr($Instance['Object'],0,10) == 'OpenAR' && $Instance['Field'] == 'Overdue'){
			?><div class='col-md-12'><a href='violation.php?ID=<?php echo $Instance['ID'];?>'><?php $Instance['Date'];?> <?php $Icons->Violation();?> Overdue Invoice #<?php echo $Instance['ID'];?></a></div><?php
		}
	}
}}
?></div>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>