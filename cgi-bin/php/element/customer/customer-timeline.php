<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( [ 'read_and_close' => true ] ); 
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/cgi-bin/php/index.php' );
}
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    //Connection
    $result = sqlsrv_query(
        $NEI,
        "   SELECT  * 
            FROM    Connection 
            WHERE       Connector = ? 
                    AND Hash = ?;",
        array(
            $_SESSION['User'],
            $_SESSION['Hash']
        )
    );
    $Connection = sqlsrv_fetch_array($result);
    //User
    $result = sqlsrv_query(
        $NEI,
        "   SELECT  *, 
                    fFirst AS First_Name, 
                    Last as Last_Name 
            FROM    Emp 
            WHERE   ID= ?;",
        array(
            $_SESSION[ 'User' ]
        )
    );
    $User   = sqlsrv_fetch_array( $result );
    //Privileges
    $result = sqlsrv_query($NEI,
        "   SELECT  Privilege.*
            FROM    Privilege
            WHERE   Privilege.User_ID = ?;",
        array(
            $_SESSION[ 'User' ]
        )
    );
    $Privileges = array();
    $Privileged = false;
    while( $Privilege = sqlsrv_fetch_array( $result ) ){ $Privileges[ $Privilege[ 'Access_Table' ] ] = $Privilege; }
    if(     isset($Privileges['Customer']) 
        &&  $Privileges[ 'Customer' ][ 'User_Privilege' ]  >= 4 
        &&  $Privileges[ 'Customer' ][ 'Group_Privilege' ] >= 4 
        &&  $Privileges[ 'Customer' ][ 'Other_Privilege' ] >= 4){
                $Privileged = true;}
    if(     !isset($Connection['ID'])  
        ||  !is_numeric($_GET['ID']) 
        || 	!$Privileged 
    ){ ?><html><head><script>document.location.href="../login.php?Forward=customer<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {
        sqlsrv_query(
            $NEI,
            "   INSERT INTO Activity( [User], [Date], [Page] ) VALUES( ?, ?, ? );",
            array(
                $_SESSION['User'],
                date("Y-m-d H:i:s"), 
                "customer/tickets.php"
            )
        );
        $result = sqlsrv_query(
            $NEI,
            "   SELECT  Customer.*                    
                FROM    (
                            SELECT  Owner.ID    AS ID,
                                    Rol.Name    AS Name,
                                    Rol.Address AS Street,
                                    Rol.City    AS City,
                                    Rol.State   AS State, 
                                    Rol.Zip     AS Zip,
                                    Owner.Status  AS Status,
                                    Rol.Website AS Website
                            FROM    Owner 
                                    LEFT JOIN Rol ON Owner.Rol = Rol.ID
                    ) AS Customer
                WHERE   Customer.ID = ?;",
            array(
                $_GET['ID']
            )
        );
        $Customer = sqlsrv_fetch_array($result);?>
<div class="panel panel-primary">
	<div class='panel-heading'><?php \singleton\fontawesome::getInstance( )->Blank( 1 );?> Timeline</div>
	<div class='panel-body' style='height:600px;overflow:auto;'>
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
						FROM     TicketO
								 LEFT JOIN Job ON TicketO.Job = Job.ID
						WHERE    Job.Owner = ?
								 AND TicketO.Assigned = 4)
						UNION ALL
						(SELECT  TicketD.ID,
								 TicketD.EDate,
								 TicketD.Level,
								 'TicketD' AS Object
						FROM     TicketD
								 LEFT JOIN Job ON TicketD.Job = Job.ID
						WHERE    Job.Owner = ?)
						UNION ALL
						(SELECT  TicketDArchive.ID,
								 TicketDArchive.EDate,
								 TicketDArchive.Level,
								 'TicketDArchive' AS Object
						FROM     TicketDArchive
								 LEFT JOIN Job ON TicketDArchive.Job = Job.ID
						WHERE    Job.Owner = ?)) AS Tickets
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
						FROM     TicketO
								 LEFT JOIN Job ON TicketO.Job = Job.ID
						WHERE    Job.Owner = ?
								 AND TicketO.Assigned = 4)
						UNION ALL
						(SELECT  TicketD.ID,
								 TicketD.CDate,
								 TicketD.Level,
								 'TicketD' AS Object
						FROM     TicketD
								 LEFT JOIN Job ON TicketD.Job = Job.ID
						WHERE    Job.Owner = ?)
						UNION ALL
						(SELECT  TicketDArchive.ID,
								 TicketDArchive.CDate,
								 TicketDArchive.Level,
								 'TicketDArchive' AS Object
						FROM     TicketDArchive
								 LEFT JOIN Job ON TicketDArchive.Job = Job.ID
						WHERE    Job.Owner = ?)) AS Tickets
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
				FROM   Job
				WHERE  Job.Owner = ?
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
				FROM   Job
				WHERE  Job.Owner = ?
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
				FROM   Estimate
					   LEFT JOIN Loc ON Loc.Loc = Estimate.LocID
				WHERE  Loc.Owner = ?
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
				FROM   Trans
					   LEFT JOIN Invoice ON Trans.Ref = Invoice.Ref
					   LEFT JOIN Job     ON Job.ID    = Invoice.Job
				WHERE  Job.Owner = ?
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
				FROM   Invoice
					   LEFT JOIN Job ON Job.ID = Invoice.Job
					   AND Invoice.fDate >= dateadd(month,-6,getdate())
				WHERE  Job.Owner = ?
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
				FROM   Violation
					   LEFT JOIN Loc ON Loc.Loc = Violation.Loc
				WHERE  Loc.Owner = ?
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
						FROM   Job 	
							   LEFT JOIN Elev  ON Job.Elev       = Elev.ID
							   LEFT JOIN Loc   ON Job.Loc        = Loc.Loc
							   LEFT JOIN Zone  ON Loc.Zone       = Zone.ID
							   LEFT JOIN Route ON Loc.Route      = Route.ID
							   LEFT JOIN Emp   ON Route.Mech     = Emp.fWork
						WHERE  ((Job.fDesc LIKE '%CAT%' AND Job.fDesc LIKE '%DEF%')
							   OR Job.fDesc LIKE '%PVT%')
							   AND Job.Status = 0
							   AND Job.Owner = ?)
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
						FROM   Violation
							   LEFT JOIN Elev  ON Violation.Elev = Elev.ID
							   LEFT JOIN Loc   ON Violation.Loc  = Loc.Loc
							   LEFT JOIN Zone  ON Loc.Zone       = Zone.ID
							   LEFT JOIN Route ON Loc.Route      = Route.ID
							   LEFT JOIN Emp   ON Route.Mech     = Emp.fWork
							   LEFT JOIN Job   ON Violation.Job  = Job.ID
						WHERE  Violation.Remarks LIKE '%DUE: [0123456789][0123456789]/[0123456789][0123456789]/[0123456789][0123456789]%'
							   AND Violation.Status <> 'Dismissed'
							   AND Violation.ID     <> 0
							   AND Loc.Owner = ?
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
				FROM   Contract
					   LEFT JOIN Job ON Job.ID = Contract.Job
				WHERE  Job.Owner = ?
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
				FROM   OpenAR
					   LEFT JOIN Invoice ON Invoice.Ref = OpenAR.Ref
					   LEFT JOIN Job     ON Job.ID      = Invoice.Job
					   AND OpenAR.Due >= dateadd(month,-6,getdate())
				WHERE  Job.Owner = ?
				ORDER BY Job.fDate DESC
			;",array($_GET['ID']));
			if($SQL_Overdue_Invoices){while($Invoice = sqlsrv_fetch_array($SQL_Overdue_Invoices)){
				if(!isset($Timeline[date('Y-m-d',strtotime($Invoice['Date']))])){$Timeline[date('Y-m-d',strtotime($Invoice['Date']))] = array();}
				$Timeline[date('Y-m-d',strtotime($Invoice['Date']))][] = $Invoice; 
			}}
			krsort($Timeline);
			if(count($Timeline) > 0){foreach($Timeline as $Date=>$DayTimeline){
				?><div class='col-md-12' style='background-color:#252525;color:white;'>
					<h3 style='text-align:center;'><?php echo date('m/d/Y',strtotime($Date));?></h3>
				</div><?php
				foreach($DayTimeline as $Instance){
					//$Instance['Date'] = date('m/d/Y',strtotime($Instance['Date']));
					if(substr($Instance['Object'],0,6) == 'Ticket' && $Instance['Field'] == 'Completed'){
						?><div class='col-md-12'><a href='ticket.php?ID=<?php echo $Instance['ID'];?>'><?php $Instance['Date'];?> <?php \singleton\fontawesome::getInstance( )->Ticket();?> Completed <?php if($Instance['Level'] == 1){?>Service Call <?php }elseif($Instance['Level'] == 10){?>Preventative Maintenance <?php }?>Ticket #<?php echo $Instance['ID'];?></a></div><?php
					} elseif(substr($Instance['Object'],0,6) == 'Ticket' && $Instance['Field'] == 'Created'){
						?><div class='col-md-12'><a href='ticket.php?ID=<?php echo $Instance['ID'];?>'><?php $Instance['Date'];?> <?php \singleton\fontawesome::getInstance( )->Ticket();?> Created <?php if($Instance['Level'] == 1){?>Service Call <?php }elseif($Instance['Level'] == 10){?>Preventative Maintenance <?php }?>Ticket #<?php echo $Instance['ID'];?></a></div><?php
					} elseif(substr($Instance['Object'],0,3) == 'Job' && $Instance['Field'] == 'Created'){
						?><div class='col-md-12'><a href='job.php?ID=<?php echo $Instance['ID'];?>'><?php $Instance['Date'];?> <?php \singleton\fontawesome::getInstance( )->Job();?> Created Job #<?php echo $Instance['ID'];?></a></div><?php
					} elseif(substr($Instance['Object'],0,3) == 'Job' && $Instance['Field'] == 'Completed'){
						?><div class='col-md-12'><a href='job.php?ID=<?php echo $Instance['ID'];?>'><?php $Instance['Date'];?> <?php \singleton\fontawesome::getInstance( )->Job();?> Completed Job #<?php echo $Instance['ID'];?></a></div><?php
					} elseif(substr($Instance['Object'],0,8) == 'Proposal' && $Instance['Field'] == 'Created'){
						?><div class='col-md-12'><a href='proposal.php?ID=<?php echo $Instance['ID'];?>'><?php $Instance['Date'];?> <?php \singleton\fontawesome::getInstance( )->Proposal();?> Created Proposal #<?php echo $Instance['ID'];?></a></div><?php
					} elseif(substr($Instance['Object'],0,7) == 'Invoice' && $Instance['Field'] == 'Created'){
						?><div class='col-md-12'><a href='invoice.php?ID=<?php echo $Instance['ID'];?>'><?php $Instance['Date'];?> <?php \singleton\fontawesome::getInstance( )->Invoice();?> Created Invoice #<?php echo $Instance['ID'];?></a></div><?php
					} elseif(substr($Instance['Object'],0,10) == 'Violation' && $Instance['Field'] == 'Created'){
						?><div class='col-md-12'><a href='violation.php?ID=<?php echo $Instance['ID'];?>'><?php $Instance['Date'];?> <?php \singleton\fontawesome::getInstance( )->Violation();?> Created Violation #<?php echo $Instance['ID'];?></a></div><?php
					} elseif($Instance['Object'] == 'Transaction' && $Instance['Field'] == 'Paid'){
						?><div class='col-md-12'><a href='transaction.php?ID=<?php echo $Instance['ID'];?>'><?php $Instance['Date'];?> <?php \singleton\fontawesome::getInstance( )->Invoice();?> Paid Invoice #<?php echo $Instance['Ref'];?></a></div><?php
					} elseif(substr($Instance['Object'],0,10) == 'Violation' && $Instance['Field'] == 'Overdue'){
						?><div class='col-md-12'><a href='violation.php?ID=<?php echo $Instance['ID'];?>'><?php $Instance['Date'];?> <?php \singleton\fontawesome::getInstance( )->Violation();?> Overdue Violation #<?php echo $Instance['ID'];?></a></div><?php
					} elseif(substr($Instance['Object'],0,10) == 'Job' && $Instance['Field'] == 'Overdue'){
						?><div class='col-md-12'><a href='job.php?ID=<?php echo $Instance['Job'];?>'><?php $Instance['Date'];?> <?php \singleton\fontawesome::getInstance( )->Violation();?> Overdue Violation Job #<?php echo $Instance['Job'];?></a></div><?php
					} elseif($Instance['Object'] == 'Contract' && $Instance['Field'] == 'Starts'){
						?><div class='col-md-12'><a href='contract.php?ID=<?php echo $Instance['ID'];?>'><?php $Instance['Date'];?> <?php \singleton\fontawesome::getInstance( )->Contract();?> Contract Starts Job #<?php echo $Instance['ID'];?></a></div><?php
					} elseif($Instance['Object'] == 'Contract' && $Instance['Field'] == 'Billed'){
						?><div class='col-md-12'><a href='contract.php?ID=<?php echo $Instance['ID'];?>'><?php $Instance['Date'];?> <?php \singleton\fontawesome::getInstance( )->Contract();?> Contract Billed Job #<?php echo $Instance['ID'];?></a></div><?php
					} elseif(substr($Instance['Object'],0,10) == 'OpenAR' && $Instance['Field'] == 'Overdue'){
						?><div class='col-md-12'><a href='invoice.php?ID=<?php echo $Instance['ID'];?>'><?php $Instance['Date'];?> <?php \singleton\fontawesome::getInstance( )->Violation();?> Overdue Invoice #<?php echo $Instance['ID'];?></a></div><?php
					}
				}
			}}
		?></div>
	</div>
</div>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>