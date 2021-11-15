<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( [ 'read_and_close' => true ] ); 
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    //Connection
    $Connection = $database->query(
        null,
        "   SELECT  Connection.* 
            FROM    Connection 
            WHERE   Connection.Connector = ? 
                    AND Connection.Hash = ?;",
        array(
            $_SESSION['User'],
            $_SESSION['Hash']
        )
    );
    $Connection = sqlsrv_fetch_array($Connection);

    //User
    $User = $database->query(
        null,
        "   SELECT  Emp.*, 
                    Emp.fFirst  AS First_Name, 
                    Emp.Last    AS Last_Name 
            FROM    Emp 
            WHERE   Emp.ID = ?;",
        array(
            $_SESSION['User']
        )
    );
    $User = sqlsrv_fetch_array($User);

    //Privileges
    $r = $database->query(
        null,
        "   SELECT  Privilege.Access_Table, 
                    Privilege.User_Privilege, 
                    Privilege.Group_Privilege, 
                    Privilege.Other_Privilege
            FROM    Privilege
            WHERE   Privilege.User_ID = ?;",
        array(
            $_SESSION[ 'User' ]
        )
    );
    $Privileges = array();
    while($Privilege = sqlsrv_fetch_array($r)){$Privileges[$Privilege['Access_Table']] = $Privilege;}
    $Privileged = FALSE;
    if( isset($Privileges['Location']) 
        && $Privileges['Location']['User_Privilege'] >= 4 
        && $Privileges['Location']['Group_Privilege'] >= 4 
        && $Privileges['Location']['Other_Privilege'] >= 4){$Privileged = TRUE;}
    elseif($Privileges['Location']['User_Privilege'] >= 4 && is_numeric($_GET['ID'])){
        $r = $database->query(  
            null,
            "   SELECT  Count( Ticket.ID ) AS Count 
                FROM    (
                            SELECT  Ticket.ID,
                                    Ticket.Location,
                                    Ticket.Field,
                                    Sum( Ticket.Count ) AS Count
                            FROM (
                                (
                                    SELECT      TicketO.ID,
                                                TicketO.LID AS Location,
                                                TicketO.fWork AS Field,
                                                Count( TicketO.ID ) AS Count
                                    FROM        TicketO
                                    GROUP BY    TicketO.ID,
                                                TicketO.LID,
                                                TicketO.fWork
                                ) UNION ALL (
                                    SELECT      TicketD.ID,
                                                TicketD.Loc AS Location,
                                                TicketD.fWork AS Field, 
                                                Count( TicketD.ID ) AS Count
                                    FROM        TicketD
                                    GROUP BY    TicketD.ID,
                                                TicketD.Loc,
                                                TicketD.fWork
                                )
                            ) AS Ticket
                            GROUP BY    Ticket.ID,
                                        Ticket.Location,
                                        Ticket.Field
                        ) AS Ticket
                        LEFT JOIN Emp AS Employee ON Ticket.Field = Employee.fWork
                WHERE   Employee.ID = ?
                        AND Ticket.Location = ?;",
            array( 
                $_SESSION[ 'User' ],
                $_GET[ 'ID' ]
            )
        );
        $Tickets = 0;
        if ( $r ){ $Tickets = sqlsrv_fetch_array( $r )[ 'Count' ]; }
        $Privileged =  $Tickets > 0 ? true : false;
    }
    if(     !isset( $Connection[ 'ID' ] )  
        ||  !$Privileged 
        ||  !is_numeric( $_GET[ 'ID' ] ) ){
            ?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {
        $database->query(
            null,
            "   INSERT INTO Activity([User], [Date], [Page]) 
                VALUES(?,?,?);",
            array(
                $_SESSION['User'],
                date('Y-m-d H:i:s'), 
                'location-feed.php?ID=' . $_GET[ 'ID' ]
            )
        );
        $ID = $_GET['ID'];
        $r = $database->query(null,
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
                    LEFT JOIN Zone         ON Loc.Zone   = Zone.ID
                    LEFT JOIN Route        ON Loc.Route  = Route.ID
                    LEFT JOIN Emp          ON Route.Mech = Emp.fWork
                    LEFT JOIN OwnerWithRol ON Loc.Owner  = OwnerWithRol.ID
                    LEFT JOIN Terr         		   ON Terr.ID    = Loc.Terr
            WHERE
                    Loc.Loc = ?
        ;",array($_GET['ID']));
        $Location = sqlsrv_fetch_array($r);
        $data = $Location;?>
<div class="panel panel-primary">
	<!--<div class="panel-heading"><h3><?php \singleton\fontawesome::getInstance( )->Territory();?>Total Profit</h3></div>-->
	<div class='panel-body  BankGothic shadow' style='height:300px;'>
		<div class="flot-chart" style='height:300px;'><div class="flot-chart-content" id="flot-placeholder-profit" style='height:300px;'></div></div>
	</div>
</div>
<?php require('../../../js/chart/location_profit.php');?>
<div class="panel panel-primary">
	<!--<div class="panel-heading"><h3>Yearly Profit</h3></div>-->
	<div class='panel-body  shadow'>
		<table id="Table_Profit" class="display" cellspacing='0' width='100%' style='font-size:8px !important;'>
			<?php
			$resource = $database->query(null,"
				SELECT   Overhead_Cost.*
				FROM     Portal.dbo.Overhead_Cost
				ORDER BY Overhead_Cost.Type ASC
			;");
			$Overhead_Costs = array();
			if($resource){while($Overhead_Cost = sqlsrv_fetch_array($resource)){
				if($Overhead_Cost['Type'] == '2012'){continue;}
				if($Overhead_Cost['Type'] == '2013'){continue;}
				if($Overhead_Cost['Type'] == '2014'){continue;}
				if($Overhead_Cost['Type'] == '2015'){continue;}
				if($Overhead_Cost['Type'] == '7 Year'){continue;}
				$Overhead_Costs[] = $Overhead_Cost;}}?>
			<thead style='border-left:3px solid black;border-right:3px solid black;border-top:3px solid black;'>
				<th></th>
				<?php
					foreach($Overhead_Costs as $Overhead_Cost){
						?><th style='border:1px solid black;padding:3px;'><?php echo $Overhead_Cost['Type'];?></th><?php
					}
				?>
			</thead>
			<tbody style='border:3px solid black;color:white !important;'>
				<tr>
					<td style='border:1px solid black;padding:3px;'>Revenue</td>
					<?php
					foreach($Overhead_Costs as $key=>$Overhead_Cost){?><td style='border:1px solid black;padding:3px;'><?php
						$resource = $database->query(null,"
							SELECT Sum(Invoice.Amount) AS Revenue
							FROM   Invoice
								   LEFT JOIN Loc ON Invoice.Loc = Loc.Loc
							WHERE  Loc.Loc = ?
								   AND Invoice.fDate >= ?
								   AND Invoice.fDate < ?
						;",array($_GET['ID'],$Overhead_Cost['Start'],$Overhead_Cost['End']));
						$Overhead_Costs[$key]['Revenue'] = sqlsrv_fetch_array($resource)['Revenue'];
						echo money_format('%(n',$Overhead_Costs[$key]['Revenue']);
					?></td><?php }?>
				</tr>
				<tr>
					<td style='border:1px solid black;padding:3px;'>Labor</td>
					<?php
					foreach($Overhead_Costs as $key=>$Overhead_Cost){?><td style='border:1px solid black;padding:3px;'><?php
						//var_dump($Overhead_Cost);
						$resource = $database->query(null,"
							SELECT Sum(JobI.Amount) AS Labor
							FROM   Loc
								   LEFT JOIN Job  ON Loc.Loc = Job.Loc
								   LEFT JOIN JobI ON Job.ID  = JobI.Job
							WHERE  Job.Loc      =  ?
								   AND JobI.Type  =  1
								   AND JobI.Labor =  1
								   AND JobI.fDate >= ?
								   AND JobI.fDate <  ?
								   AND JobI.fDate >= '2017-03-30 00:00:00.000'
						;",array($_GET['ID'],$Overhead_Cost['Start'],$Overhead_Cost['End']));
						$Overhead_Costs[$key]['Labor'] = sqlsrv_fetch_array($resource)['Labor'];
						$resource = $database->query(null,"
							SELECT SUM([JOBLABOR].[TOTAL COST]) AS Labor
							FROM   Job as Job
								   LEFT JOIN Paradox.dbo.JOBLABOR AS JOBLABOR ON Job.ID = [JOBLABOR].[JOB #]
							WHERE  Job.Loc = ?
								   AND convert(date,[WEEK ENDING]) >= ?
								   AND convert(date,[WEEK ENDING]) < ?
								   AND convert(date,[WEEK ENDING]) < '2017-03-30 00:00:00.000'
								   AND [JOBLABOR].[jobAlpha] <> '1111'
								   AND [JOBLABOR].[JobAlpha] <> '2222'
								   AND [JOBLABOR].[JobAlpha] <> '3333'
								   AND [JOBLABOR].[JobAlpha] <> '4444'
								   AND [JOBLABOR].[JobAlpha] <> '5555'
								   AND [JOBLABOR].[JobAlpha] <> '6666'
								   AND [JOBLABOR].[JobAlpha] <> '2222'
								   AND [JOBLABOR].[JobAlpha] <> '7777'
								   AND [JOBLABOR].[JobAlpha] <> '8888'
								   AND [JOBLABOR].[JobAlpha] <> '9999'
						;",array($_GET['ID'],$Overhead_Cost['Start'],$Overhead_Cost['End']));
						$Overhead_Costs[$key]['Labor'] += sqlsrv_fetch_array($resource)['Labor'];
						echo money_format('%(n',$Overhead_Costs[$key]['Labor']);
					?></td><?php }?>
				</tr>
				<tr>
					<td style='border:1px solid black;padding:3px;'>Materials</td>
					<?php
					foreach($Overhead_Costs as $key=>$Overhead_Cost){?><td style='border:1px solid black;padding:3px;'><?php
						//var_dump($Overhead_Cost);
						$resource = $database->query(null,"
							SELECT Sum(JobI.Amount) AS Materials
							FROM   Loc
								   LEFT JOIN Job  ON Loc.Loc = Job.Loc
								   LEFT JOIN JobI ON Job.ID  = JobI.Job
							WHERE  Job.Loc      =  ?
								   AND JobI.Type  =  1
								   AND (
										JobI.Labor =  0
										OR JobI.Labor IS NULL
										OR JobI.Labor = ' ')
								   AND JobI.fDate >= ?
								   AND JobI.fDate <  ?
						;",array($_GET['ID'],$Overhead_Cost['Start'],$Overhead_Cost['End']));
						$Overhead_Costs[$key]['Materials'] = sqlsrv_fetch_array($resource)['Materials'];
						echo money_format('%(n',$Overhead_Costs[$key]['Materials']);
					?></td><?php }?>
				</tr>
				<tr style='border-top:3px solid black;'>
					<td style='border:1px solid black;padding:3px;'>Net Income</td>
					<?php
					foreach($Overhead_Costs as $key=>$Overhead_Cost){?><td style='border:1px solid black;padding:3px;'><?php
						$Overhead_Costs[$key]['Net_Income'] = $Overhead_Costs[$key]['Revenue'] - ($Overhead_Costs[$key]['Labor'] + $Overhead_Costs[$key]['Materials']);
						echo money_format('%(n',$Overhead_Costs[$key]['Net_Income']);
					?></td><?php }?>
				</tr>
				<tr>
					<td style='border:1px solid black;padding:3px;'>Overhead Rate</td>
					<?php
					foreach($Overhead_Costs as $key=>$Overhead_Cost){?><td style='border:1px solid black;padding:3px;'><?php
						echo $Overhead_Cost['Rate'] . '%';
					?></td><?php }?>
				</tr>
				<tr>
					<td style='border:1px solid black;padding:3px;'>Overhead Cost</td>
					<?php
					foreach($Overhead_Costs as $key=>$Overhead_Cost){?><td style='border:1px solid black;padding:3px;'><?php
						$Overhead_Costs[$key]['Overhead_Cost'] = $Overhead_Costs[$key]['Revenue'] * ($Overhead_Costs[$key]['Rate'] / 100);
						echo money_format('%(n',$Overhead_Costs[$key]['Overhead_Cost']);
					?></td><?php }?>
				</tr>
				<tr>
					<td style='border:1px solid black;padding:3px;'>Profit</td>
					<?php
					foreach($Overhead_Costs as $key=>$Overhead_Cost){?><td style='border:1px solid black;padding:3px;'><?php
						$Overhead_Costs[$key]['Profit'] = $Overhead_Costs[$key]['Net_Income'] - $Overhead_Costs[$key]['Overhead_Cost'];
						echo money_format('%(n',$Overhead_Costs[$key]['Profit']);
					?></td><?php }?>
				</tr>
			</tbody>
		</table>
	</div>
</div>
	</div>
			<!-- /.panel -->
        </div>
    </div>
	<style>
		.border-seperate {
			border-bottom:3px solid #333333;
		}
	</style>
    
    <?php require('bin/js/datatables.php');?>
    <!-- JQUERY UI Javascript -->
	
    <style>
    div.column {display:inline-block;vertical-align:top;}
    div.label1 {display:inline-block;font-weight:bold;width:150px;vertical-align:top;}
    div.data {display:inline-block;width:300px;vertical-align:top;}
    </style>
    <script src="https://www.nouveauelevator.com/vendor/flot/excanvas.min.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/flot/jquery.flot.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/flot/jquery.flot.pie.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/flot/jquery.flot.resize.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/flot/jquery.flot.time.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/flot/jquery.flot.categories.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/flot-tooltip/jquery.flot.tooltip.min.js"></script>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
