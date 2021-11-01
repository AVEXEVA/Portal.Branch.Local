<?php 
session_start();
require('cgi-bin/php/index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    if(!isset($_SESSION['Branch'])|| $_SESSION['Branch'] == 'Nouveau Elevator'){
        sqlsrv_query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "ticket.php"));
        $r = sqlsrv_query($NEI,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID= ?",array($_SESSION['User']));
        $My_User = sqlsrv_fetch_array($r);
        $Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
        $r = sqlsrv_query($Portal,"
            SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
            FROM   Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
        $My_Privileges = array();
        while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
        $Privileged = FALSE;
	}
    if(isset($My_Privileges['Ticket']) && $My_Privileges['Ticket']['User_Privilege'] >= 4 && $My_Privileges['Ticket']['Group_Privilege'] >= 4 && $My_Privileges['Location']['Other_Privilege'] >= 4){$Privileged = TRUE;}
	if(!isset($array['ID']) && !$Privileged && !is_numeric($_GET['ID'])){}
	else {
		$r = sqlsrv_query($NEI,"
			SELECT Loc.*
			FROM   nei.dbo.Elev
				   LEFT JOIN nei.dbo.Loc ON Elev.Loc = Loc.Loc
			WHERE  Elev.ID = ?
		;",array($_GET['ID']));
		$Location = sqlsrv_fetch_array($r);
		$r = sqlsrv_query($NEI,"
			SELECT Elev.*
			FROM   nei.dbo.Elev
			WHERE  Elev.ID = ?
		;",array($_GET['ID']));
		$Unit = sqlsrv_fetch_array($r);
		$r = sqlsrv_query($NEI,"
			SELECT Tickets.*,
				   Emp.fFirst + ' ' + Emp.Last AS CallSign
			FROM   
			(
				(
					SELECT TicketD.ID,
						   TicketD.EDate,
						   TicketD.fDesc,
						   TicketD.DescRes,
						   TicketD.fWork,
						   TicketD.Level
					FROM   nei.dbo.TicketD
					WHERE  TicketD.Elev = ?
				)
				UNION ALL
				(
					SELECT TicketDArchive.ID,
						   TicketDArchive.EDate,
						   TicketDArchive.fDesc,
						   TicketDArchive.DescRes,
						   TicketDArchive.fWork,
						   TicketDArchive.Level
					FROM   nei.dbo.TicketDArchive
					WHERE  TicketDArchive.Elev = ?
				)
			) AS Tickets
					LEFT JOIN Emp ON Tickets.fWork = Emp.fWork
			WHERE Tickets.EDate >= '2014-11-11 00:00:00.000' 
				  AND Tickets.EDate <= '2015-11-10 23:59:59.999'
				  AND (Tickets.Level = 1)
		;",array($_GET['ID'],$_GET['ID']));
		if( ($errors = sqlsrv_errors() ) != null) {
        foreach( $errors as $error ) {
            echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
            echo "code: ".$error[ 'code']."<br />";
            echo "message: ".$error[ 'message']."<br />";
        }
    }
		$Tickets = array();
		if($r){while($Ticket = sqlsrv_fetch_array($r)){
			$Tickets[] = $Ticket;
		}}
		//var_dump($Tickets);
		?><html>
			<head></head>
			<body>
				<h4>COMPLETED TICKET LISTING BY ACCOUNT/UNIT</h4>
				<div style='text-align:right;'>
					<div>Nouveau Elevator Industries Inc.</div>
					<div>47-55 37th Street</div>
					<div>LIC, NY 11101</div>
				</div>
				<div>For the period starting 11/11/2014 and ending 11/11/2015</div>
				<div>Printed On 8/8/2017 By PSPERANZA</div>
				<div>
				<div style="font-size: 15px;margin-bottom: : 0; margin-top: 10px;"><b><u><?php echo $Location['ID'] . " - " . $Location['Tag'];?></b></u></div>
				<div style="font-size: 15px; margin-top: 0; margin-bottom: 20px;"><b><?php echo $Unit['Unit'] . " - - Public - " . $Unit['Type'];?>
				</b></div>
				<style>
					table, th, td { font-size: 15px;}
					td {vertical-align: text-top; padding-bottom: 2.5em;}
				</style>
				<table>
					<thead>
						<tr>
							<col width="100">
							<col width="100">
							<col width="100">
							<col width="150">
							<col width="350">
							<col width="350">
							<th style="text-align: left"><u>Tick#</u></th>
							<th style="text-align: left"><u>Date</u></th>
							<th style="text-align: left"><u>Type</u></th>
							<th style="text-align: left"><u>Mechanic</u></th>
							<th style="text-align: left"><u>Description</u></th>
							<th style="text-align: left"><u>Resolution</u></th>
						</tr>
					</thead>
					<tbody>
				<?php
					if(is_array($Tickets) && count($Tickets) > 0){foreach($Tickets as $Ticket){?>
						<tr>
							<td><?php echo $Ticket['ID'];?></td>
							<td><?php echo date("m/d/Y",strtotime($Ticket['EDate']));?></td>
							<td>Maintenance</td>
							<td><?php echo $Ticket['CallSign'];?></td>
							<td><?php echo $Ticket['fDesc'];?></td>
							<td><?php echo $Ticket['DescRes'];?></td>
						</tr>
					<?php }}
				?>
					</tbody>
				</table>
			</body>
		</html><?php
	}
}