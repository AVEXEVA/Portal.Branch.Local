<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
if( isset( $_SESSION[ 'Connection' ][ 'User' ], $_SESSION[ 'Connection' ][ 'Hash' ] ) ){
  //Connection
    $result = \singleton\database::getInstance( )->query(
      'Portal',
      " SELECT  [Connection].[ID]
        FROM    dbo.[Connection]
        WHERE       [Connection].[User] = ?
                AND [Connection].[Hash] = ?;",
      array(
        $_SESSION[ 'Connection' ][ 'User' ],
        $_SESSION[ 'Connection' ][ 'Hash' ]
      )
    );
    $Connection = sqlsrv_fetch_array($result);
    //User
	$result = \singleton\database::getInstance( )->query(
		null,
		" SELECT  Emp.fFirst  AS First_Name,
		          Emp.Last    AS Last_Name,
		          Emp.fFirst + ' ' + Emp.Last AS Name,
		          Emp.Title AS Title,
		          Emp.Field   AS Field
		  FROM  Emp
		  WHERE   Emp.ID = ?;",
		array(
		  	$_SESSION[ 'Connection' ][ 'User' ]
		)
	);
	$User   = sqlsrv_fetch_array( $result );
	//Privileges
	$Access = 0;
	$Hex = 0;
	$result = \singleton\database::getInstance( )->query(
		'Portal',
		"   SELECT  [Privilege].[Access],
                    [Privilege].[Owner],
                    [Privilege].[Group],
                    [Privilege].[Department],
                    [Privilege].[Database],
                    [Privilege].[Server],
                    [Privilege].[Other],
                    [Privilege].[Token],
                    [Privilege].[Internet]
		  FROM      dbo.[Privilege]
		  WHERE     Privilege.[User] = ?;",
		array(
		  	$_SESSION[ 'Connection' ][ 'User' ],
		)
	);
    $Privileges = array();
    if( $result ){while( $Privilege = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC ) ){

        $key = $Privilege['Access'];
        unset( $Privilege[ 'Access' ] );
        $Privileges[ $key ] = implode( '', array(
        	dechex( $Privilege[ 'Owner' ] ),
        	dechex( $Privilege[ 'Group' ] ),
        	dechex( $Privilege[ 'Department' ] ),
        	dechex( $Privilege[ 'Database' ] ),
        	dechex( $Privilege[ 'Server' ] ),
        	dechex( $Privilege[ 'Other' ] ),
        	dechex( $Privilege[ 'Token' ] ),
        	dechex( $Privilege[ 'Internet' ] )
        ) );
    }}
    if( 	!isset( $Connection[ 'ID' ] )
        ||  !isset( $Privileges[ 'Dashboard' ] )
        || 	!check( privilege_read, level_group, $Privileges[ 'Dashboard' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
        \singleton\database::getInstance( )->query(
          null,
          " INSERT INTO Activity([User], [Date], [Page] )
            VALUES( ?, ?, ? );",
          array(
            $_SESSION[ 'Connection' ][ 'User' ],
            date('Y-m-d H:i:s'),
            'dashboard.php'
        )
      );
?><!DOCTYPE html>
<html lang="en">
<head>
    <?php require( bin_meta . 'index.php');?>
    <title>Nouveau Texas | Portal</title>
    <?php require( bin_css . 'index.php');?>
    <?php require( bin_js . 'index.php');?>
</head>
<body onload='finishLoadingPage();'>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require( bin_php . 'element/navigation.php');?>
        <?php require( bin_php . 'element/loading.php');?>
		<style>
		<?php if($Mobile){?>
			.panel-body {padding:0px !important;}
		<?php }?>
		</style>
        <?php if(!isMobile()){?><div id="page-wrapper" class='content'>
            <div class="panel panel-primary" style='margin-bottom:0px;'>
				<div class="panel-heading"><?php
				$_GET['Mechanic'] = isset($_GET['Mechanic']) ? $_GET['Mechanic'] : $_SESSION['User'];
				if(is_numeric($_GET['Mechanic'])){$r = $database->query(null,"SELECT Emp.* FROM Emp WHERE Emp.ID='" . $_GET['Mechanic']. "';");$r = sqlsrv_fetch_array($r);$Mechanic = $r;}
						else {$Mechanic = $User;}?><h3><?php \singleton\fontawesome::getInstance( )->Dashboard();?>My Dashboard</h3></div>
                <div class="row">
					<div class='col-md-6'>
						<div class="panel panel-primary">
							<?php if(!$Mobile){?><div class="panel-heading">Attendance</div><?php }?>
							<div class='panel-body' style='<?php if(isMobile()){?>margin-top:6px;<?php }?>'>
								<?php
								$Date = date('Y-m-d');
								$Today = date('l');
								if($Today == 'Thursday'){$Thursday = date('Y-m-d');}
								elseif($Today == 'Friday'){$Thursday = date('Y-m-d', strtotime($Date . ' -1 days'));}
								elseif($Today == 'Saturday'){$Thursday = date('Y-m-d', strtotime($Date . ' -2 days'));}
								elseif($Today == 'Sunday'){$Thursday = date('Y-m-d', strtotime($Date . ' -3 days'));}
								elseif($Today == 'Monday'){$Thursday = date('Y-m-d', strtotime($Date . ' -4 days'));}
								elseif($Today == 'Tuesday'){$Thursday = date('Y-m-d', strtotime($Date . ' -5 days'));}
								elseif($Today == 'Wednesday'){$Thursday = date('Y-m-d', strtotime($Date . ' -6 days'));}
								if($Today == 'Wednesday'){$Wednesday = date('Y-m-d');}
								elseif($Today == 'Thursday'){$Wednesday = date('Y-m-d', strtotime($Date . ' +6 days'));}
								elseif($Today == 'Friday'){$Wednesday = date('Y-m-d', strtotime($Date . ' +5 days'));}
								elseif($Today == 'Saturday'){$Wednesday = date('Y-m-d', strtotime($Date . ' +4 days'));}
								elseif($Today == 'Sunday'){$Wednesday = date('Y-m-d', strtotime($Date . ' +3 days'));}
								elseif($Today == 'Monday'){$Wednesday = date('Y-m-d', strtotime($Date . ' +2 days'));}
								elseif($Today == 'Tuesday'){$Wednesday = date('Y-m-d', strtotime($Date . ' +1 days'));}
								$Thurs = date("m/d/Y",strtotime($Thursday));
								$Wedn = date("m/d/Y",strtotime($Wednesday));
								$Thursday = $Thursday . " 00:00:00.000";
								$Wednesday = $Wednesday . " 23:59:59.999";
								?>
								<div class="col-lg-4 col-md-4 col-xs-4" onClick="document.location.href='tickets.php?Start_Date=<?php echo $Thurs;?>&End_Date=<?php echo $Wedn;?>';">
									<div class="panel panel-primary"><div class="panel-heading">
										<div class="row">
											<?php if(!isMobile()){?><div class="col-xs-3">
												<i class="fa fa-ticket fa-5x"></i>
											</div><?php }?>
											<div class="col-xs-<?php if(isMobile()){?>12 text-center<?php } else {?>9 text-right<?php }?> " style='min-height:75px;'>
												<div class="<?php if(isMobile()){?>medium<?php } else {?>huge<?php }?>"><?php

													$r = $database->query(null,"
														SELECT
															(SELECT Count(*) AS Counts
															 FROM   TicketD
																	LEFT JOIN Emp ON TicketD.fWork = Emp.fWork
															 WHERE  Emp.ID = ?
																	AND TicketD.EDate >= ?
																	AND TicketD.EDate <= ?)
															 +
															(SELECT Count(*) AS Counts
															 FROM   TicketDArchive
																	LEFT JOIN Emp ON TicketDArchive.fWork = Emp.fWork
															 WHERE  Emp.ID = ?
																	AND TicketDArchive.EDate >= ?
																	AND TicketDArchive.EDate <= ?)
																AS Counter
													;",array($_SESSION['User'],$Thursday,$Wednesday,$_SESSION['User'],$Thursday,$Wednesday));
													$Count_of_Tickets = sqlsrv_fetch_array($r)['Counter'];
													echo $Count_of_Tickets;
												?></div>
												<div>Tickets This Week</div>
											</div>
										</div>
									</div></div>
								</div>
								<div class="col-lg-4 col-md-4 col-xs-4" onClick="document.location.href='time_sheet.php?Type=WeekOf&Date=<?php echo $Wedn;?>';">
									<div class="panel panel-primary">
										<div class="panel-heading">
											<div class="row">
												<?php if(!isMobile()){?><div class="col-xs-3">
													<i class="fa fa-clock-o fa-5x"></i>
												</div><?php }?>
												<div class="col-xs-<?php if(isMobile()){?>12 text-center<?php } else {?>9 text-right<?php }?> " style='min-height:75px;'>
													<div class="<?php if(isMobile()){?>medium<?php } else {?>huge<?php }?>"><?php
														$r = $database->query(null,"
															SELECT Sum(Total) AS Summed
															FROM   (SELECT Total, fWork, EDate FROM TicketD
																	UNION ALL
																	SELECT Total, fWork, EDate FROM TicketDArchive) AS Tickets
																	LEFT JOIN Emp ON Tickets.fWork = Emp.fWork
															WHERE   Emp.ID        =  ?
																	AND Tickets.EDate >= ?
																	AND Tickets.EDate <= ?
														;",array($_SESSION['User'],$Thursday,$Wednesday));
														$Sum_of_Ticket_Hours = round(sqlsrv_fetch_array($r)['Summed']);
														echo $Sum_of_Ticket_Hours;
													?></div>
													<div>Hours This Week</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="col-lg-4 col-md-4 col-xs-4" onClick="document.location.href='time_sheet.php?Type=WeekOf&Date=<?php echo $Wedn;?>';">
									<div class="panel panel-primary">
										<div class="panel-heading">
											<div class="row">
												<?php if(!isMobile()){?><div class="col-xs-3">
												<i class="fa fa-clock-o fa-5x"></i>
											</div><?php }?>
											<div class="col-xs-<?php if(isMobile()){?>12 text-center<?php } else {?>9 text-right<?php }?> " style='min-height:75px;'>
												<div class="<?php if(isMobile()){?>medium<?php } else {?>huge<?php }?>"><?php
														$ratio = $Sum_of_Ticket_Hours / $Count_of_Tickets;
														echo $ratio >= 0 ? round($ratio,1) : 0;
													?></div>
													<div>Hours / Tickets</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<?php
								$serverName = "172.16.12.45";
								nullectionOptions = array(
									"Database" => "ATTENDANCE",
									"Uid" => "sa",
									"PWD" => "SQLABC!23456",
									'ReturnDatesAsStrings'=>true
								);
								//Establishes the connection
								$c2 = sqlsrv_connect($serverName, nullectionOptions);
								$r = $database->query($c2,"select * from Employee where EmpID='" .$User['Ref'] . "'");
								$Attendance = sqlsrv_fetch_array($r);
								while($temp = sqlsrv_fetch_array($r));
								?>
								<div class="col-lg-3 col-md-3 col-xs-3" onClick="document.location.href='user.php';">
									<div class="panel panel-primary">
										<div class="panel-heading">
											<div class="row">
												<?php if(!isMobile()){?><div class="col-xs-3">
													<i class="fa fa-calendar-o fa-3x"></i>
												</div><?php }?>
												<div class="col-xs-<?php if(isMobile()){?>12 text-center<?php } else {?>9 text-right<?php }?> " style='min-height:75px;'>
													<div class="<?php if(isMobile()){?>medium<?php } else {?>huge<?php }?>"><?php
														echo $Attendance['SickAvail'];
													?></div>
													<div>Avail. Sick Days</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="col-lg-3 col-md-3 col-xs-3" onClick="document.location.href='user.php';">
									<div class="panel panel-primary">
										<div class="panel-heading">
											<div class="row">
												<?php if(!isMobile()){?><div class="col-xs-3">
													<i class="fa fa-calendar-o fa-3x"></i>
												</div><?php }?>
												<div class="col-xs-<?php if(isMobile()){?>12 text-center<?php } else {?>9 text-right<?php }?> " style='min-height:75px;'>
													<div class="<?php if(isMobile()){?>medium<?php } else {?>huge<?php }?>"><?php
														echo $Attendance['VacAvail'];
													?></div>
													<div>Avail. Vacation</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="col-lg-3 col-md-3 col-xs-3" onClick="document.location.href='user.php';">
									<div class="panel panel-primary">
										<div class="panel-heading">
											<div class="row">
												<?php if(!isMobile()){?><div class="col-xs-3">
													<i class="fa fa-calendar-o fa-3x"></i>
												</div><?php }?>
												<div class="col-xs-<?php if(isMobile()){?>12 text-center<?php } else {?>9 text-right<?php }?> " style='min-height:75px;'>
													<div class="<?php if(isMobile()){?>medium<?php } else {?>huge<?php }?>"><?php
														echo $Attendance['MedAvail'];
													?></div>
													<div>Avail. Medical</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="col-lg-3 col-md-3 col-xs-3" onClick="document.location.href='user.php';">
									<div class="panel panel-primary">
										<div class="panel-heading">
											<div class="row">
												<?php if(!isMobile()){?><div class="col-xs-3">
													<i class="fa fa-calendar-o fa-3x"></i>
												</div><?php }?>
												<div class="col-xs-<?php if(isMobile()){?>12 text-center<?php } else {?>9 text-right<?php }?> " style='min-height:75px;'>
													<div class="<?php if(isMobile()){?>medium<?php } else {?>huge<?php }?>"><?php
														echo $Attendance['LieuAvail'];
													?></div>
													<div>Avail. Lieu Days</div>
												</div>
											</div>
										</div>
									</div>
								</div>
					<?php if(!isMobile()){?></div>
						</div>
					</div>
					<div class='col-md-6'>
						<div class="panel panel-primary">
							<?php if(!$Mobile){?><div class="panel-heading">Main</div><?php }?>
							<div class='panel-body' style='<?php if(isMobile()){?>margin-top:6px;<?php }?>'><?php }?>
								<?php
								$r = $database->query(null,"
									SELECT Top 1
										   TicketO.ID AS ID
									FROM   nei.dbo.TicketO
										   LEFT JOIN Emp ON TicketO.fWork = Emp.fWork
									WHERE  (TicketO.Assigned    = 2
										    OR TicketO.Assigned = 3)
										   AND Emp.ID = ?
									ORDER BY EDate DESC
								;",array($_SESSION['User']));
								$Ticket = sqlsrv_fetch_array($r);
								if(is_array($Ticket)){
								?>
								<div class="col-lg-4 col-md-4 col-xs-4" onClick="document.location.href='ticket.php?ID=<?php echo $Ticket['ID'];?>';">
									<div class="panel panel-primary">
										<div class="panel-heading">
											<div class="row">
												<?php if(!isMobile()){?><div class="col-xs-3">
												<i class="fa fa-cogs fa-3x"></i>
											</div><?php }?>
											<div class="col-xs-<?php if(isMobile()){?>12 text-center<?php } else {?>9 text-right<?php }?> " style='min-height:75px;'>
												<div class="<?php if(isMobile()){?>small<?php } else {?>medium<?php }?>">
													<?php echo $Ticket['ID'];?>
												</div>
												<div>Active Ticket</div>
											</div>
										</div>
									</div></div>
								</div>
								<?php }?>
								<?php
								$r = $database->query(null,"
									SELECT Top 1
										   Tickets.Date AS Date,
										   Tickets.Unit AS Unit
									FROM
										(
											(SELECT
													EDate AS Date,
													LElev AS Unit
											 FROM   nei.dbo.TicketO
													LEFT JOIN Emp ON TicketO.fWork = Emp.fWork
											 WHERE  Emp.ID = ?
											        AND (  TicketO.Assigned = 2
														OR TicketO.Assigned = 3)
											)
											UNION ALL
											(SELECT EDate AS Date,
													Elev AS Unit
											 FROM   nei.dbo.TicketD
													LEFT JOIN Emp ON TicketD.fWork = Emp.fWork
											 WHERE  Emp.ID = ?
											)
											UNION ALL
											(SELECT EDate AS Date,
													Elev AS Unit
											 FROM   nei.dbo.TicketDArchive
													LEFT JOIN Emp ON TicketDArchive.fWork = Emp.fWork
											 WHERE  Emp.ID = ?
											)
										) AS Tickets
									ORDER BY Date DESC
								;",array($_SESSION['User'],$_SESSION['User'],$_SESSION['User']));
								$Unit = sqlsrv_fetch_array($r)['Unit'];
								?>
								<div class="col-lg-4 col-md-4 col-xs-4" onClick="document.location.href='unit.php?ID=<?php echo $Unit;?>';">
									<div class="panel panel-primary">
										<div class="panel-heading">
											<div class="row">
												<?php if(!isMobile()){?><div class="col-xs-3">
												<i class="fa fa-cogs fa-3x"></i>
											</div><?php }?>
											<div class="col-xs-<?php if(isMobile()){?>12 text-center<?php } else {?>9 text-right<?php }?> " style='min-height:75px;'>
												<div class="<?php if(isMobile()){?>small<?php } else {?>medium<?php }?>">
													<?php
													$r = $database->query(null,"SELECT * FROM Elev WHERE Elev.ID = ?;",array($Unit));
													$Unit = sqlsrv_fetch_array($r);
													echo strlen($Unit['State']) > 0 ? $Unit['State'] : $Unit['Unit'];;
													j?>
												</div>
												<div>Recent Unit</div>
											</div>
										</div>
									</div></div>
								</div>
								<?php
								$r = $database->query(null,"
									SELECT Top 1
										   Tickets.Date 	AS Date,
										   Tickets.Location AS Location
									FROM
										(
											(SELECT
													CASE WHEN TicketO.Assigned = 2 OR TicketO.Assigned = 3
														 THEN '2099-01-01 00:00:00.000'
														 ELSE EDate
													END AS Date,
													LID AS Location
											 FROM   nei.dbo.TicketO
													LEFT JOIN Emp ON TicketO.fWork = Emp.fWork
											 WHERE  Emp.ID = ?
											        AND (  TicketO.Assigned = 2
														OR TicketO.Assigned = 3)
											)
											UNION ALL
											(SELECT EDate AS Date,
													Loc AS Location
											 FROM   nei.dbo.TicketD
													LEFT JOIN Emp ON TicketD.fWork = Emp.fWork
											 WHERE  Emp.ID = ?
											)
											UNION ALL
											(SELECT EDate AS Date,
													Loc AS Location
											 FROM   nei.dbo.TicketDArchive
													LEFT JOIN Emp ON TicketDArchive.fWork = Emp.fWork
											 WHERE  Emp.ID = ?
											)
										) AS Tickets
								ORDER BY Date DESC
								;",array($_SESSION['User'],$_SESSION['User'],$_SESSION['User']));
								$Location = sqlsrv_fetch_array($r)['Location'];
								?>
								<div class="col-lg-8 col-md-8 col-xs-8" onClick="document.location.href='location.php?ID=<?php echo $Location;?>';">
									<div class="panel panel-primary">
										<div class="panel-heading">
											<div class="row">
												<?php if(!isMobile()){?><div class="col-xs-3">
												<i class="fa fa-cogs fa-5x"></i>
											</div><?php }?>
											<div class="col-xs-<?php if(isMobile()){?>12 text-center<?php } else {?>9 text-right<?php }?> " style='min-height:75px;'>
												<div class="<?php if(isMobile()){?>small<?php } else {?>medium<?php }?>">
													<?php
													$r = $database->query(null,"SELECT * FROM Loc WHERE Loc.Loc = ?;",array($Location));
													$Location = sqlsrv_fetch_array($r);
													echo proper($Location['Tag']);
													j?>
												</div>
												<div>Recent Location</div>
											</div>
										</div>
									</div></div>
								</div>
								<?php
								$r = $database->query(null,"
									SELECT Top 1
										   Tickets.Date AS Date,
										   Tickets.Job AS Job
									FROM
										(
											(SELECT
													CASE WHEN TicketO.Assigned = 2 OR TicketO.Assigned = 3
														 THEN '2099-01-01 00:00:00.000'
														 ELSE EDate
													END AS Date,
													Job AS Job
											 FROM   nei.dbo.TicketO
													LEFT JOIN Emp ON TicketO.fWork = Emp.fWork
											 WHERE  Emp.ID = ?
											        AND (  TicketO.Assigned = 2
														OR TicketO.Assigned = 3)
											)
											UNION ALL
											(SELECT EDate AS Date,
													Job AS Job
											 FROM   nei.dbo.TicketD
													LEFT JOIN Emp ON TicketD.fWork = Emp.fWork
											 WHERE  Emp.ID = ?
											)
											UNION ALL
											(SELECT EDate AS Date,
													Job AS Job
											 FROM   nei.dbo.TicketDArchive
													LEFT JOIN Emp ON TicketDArchive.fWork = Emp.fWork
											 WHERE  Emp.ID = ?
											)
										) AS Tickets
									ORDER BY Date DESC
								;",array($_SESSION['User'],$_SESSION['User'],$_SESSION['User']));
								$Job = sqlsrv_fetch_array($r)['Job'];
								?>
								<div class="col-lg-6 col-md-6 col-xs-6" onClick="document.location.href='job.php?ID=<?php echo $Job;?>';">
									<div class="panel panel-primary">
										<div class="panel-heading">
											<div class="row">
												<?php if(!isMobile()){?><div class="col-xs-3">
												<i class="fa fa-suitcase fa-3x"></i>
											</div><?php }?>
											<div class="col-xs-<?php if(isMobile()){?>12 text-center<?php } else {?>9 text-right<?php }?> " style='min-height:75px;'>
												<div class="<?php if(isMobile()){?>small<?php } else {?>medium<?php }?>">
													<?php
													$r = $database->query(null,"SELECT * FROM Job WHERE Job.ID = ?;",array($Job));
													$Job = sqlsrv_fetch_array($r);
													echo strlen($Job['fDesc']) > 0 ? $Job['fDesc'] : $Job['fDesc'];;
													j?>
												</div>
												<div>Recent Job</div>
											</div>
										</div>
									</div></div>
								</div>
							</div>
						</div>
					<?php if(!isMobile()){?></div>
						</div>
					</div>
					<div class='col-md-6'>
						<div class="panel panel-primary">
							<?php if(!$Mobile){?><div class="panel-heading">Main</div><?php }?>
							<div class='panel-body' style='<?php if(isMobile()){?>margin-top:6px;<?php }?>'><?php }?>
								<?php
								$Thursday = date('Y-m-d H:i:s',strtotime($Thursday . ' -7 days'));
								$Wednesday = date('Y-m-d H:i:s',strtotime($Wednesday . ' -7 days'));
								$Thurs = date("m/d/Y",strtotime($Thursday));
								$Wedn = date("m/d/Y",strtotime($Wednesday));
								?>
								<div class="col-lg-4 col-md-4 col-xs-4" onClick="document.location.href='tickets.php?Start_Date=<?php echo $Thurs;?>&End_Date=<?php echo $Wedn;?>';">
									<div class="panel panel-primary">
										<div class="panel-heading">
											<div class="row">
												<?php if(!isMobile()){?><div class="col-xs-3">
												<i class="fa fa-ticket fa-5x"></i>
											</div><?php }?>
											<div class="col-xs-<?php if(isMobile()){?>12 text-center<?php } else {?>9 text-right<?php }?> " style='min-height:75px;'>
												<div class="<?php if(isMobile()){?>medium<?php } else {?>huge<?php }?>">
													<?php
													$r = $database->query(null,"
														SELECT
															(SELECT Count(*) AS Counts
															 FROM   TicketD
																	LEFT JOIN Emp ON TicketD.fWork = Emp.fWork
															 WHERE  Emp.ID = ?
																	AND TicketD.EDate >= ?
																	AND TicketD.EDate <= ?)
															 +
															(SELECT Count(*) AS Counts
															 FROM   TicketDArchive
																	LEFT JOIN Emp ON TicketDArchive.fWork = Emp.fWork
															 WHERE  Emp.ID = ?
																	AND TicketDArchive.EDate >= ?
																	AND TicketDArchive.EDate <= ?)
																AS Counter
													;",array($_SESSION['User'],$Thursday,$Wednesday,$_SESSION['User'],$Thursday,$Wednesday));
													$Count_of_Tickets = sqlsrv_fetch_array($r)['Counter'];
													echo $Count_of_Tickets;
												?></div>
												<div>Tickets Last Week</div>
											</div>
										</div>
									</div></div>
								</div>
								<div class="col-lg-4 col-md-4 col-xs-4" onClick="document.location.href='time_sheet.php';">
									<div class="panel panel-primary">
										<div class="panel-heading">
											<div class="row">
												<?php if(!isMobile()){?><div class="col-xs-3">
												<i class="fa fa-clock-o fa-3x"></i>
											</div><?php }?>
											<div class="col-xs-<?php if(isMobile()){?>12 text-center<?php } else {?>9 text-right<?php }?> " style='min-height:75px;'>
												<div class="<?php if(isMobile()){?>medium<?php } else {?>huge<?php }?>"><?php
														$r = $database->query(null,"
															SELECT Sum(Total) AS Summed
															FROM   (SELECT Total, fWork, EDate FROM TicketD
																	UNION ALL
																	SELECT Total, fWork, EDate FROM TicketDArchive) AS Tickets
																	LEFT JOIN Emp ON Tickets.fWork = Emp.fWork
															WHERE   Emp.ID        =  ?
																	AND Tickets.EDate >= ?
																	AND Tickets.EDate <= ?
														;",array($_SESSION['User'],$Thursday,$Wednesday));
														$Sum_of_Ticket_Hours = round(sqlsrv_fetch_array($r)['Summed']);
														echo $Sum_of_Ticket_Hours;
													?></div>
													<div>Hours Last Week</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="col-lg-4 col-md-4 col-xs-4">
									<div class="panel panel-primary">
										<div class="panel-heading">
											<div class="row">
												<?php if(!isMobile()){?><div class="col-xs-3">
												<i class="fa fa-clock-o fa-5x"></i>
											</div><?php }?>
											<div class="col-xs-<?php if(isMobile()){?>12 text-center<?php } else {?>9 text-right<?php }?> " style='min-height:75px;'>
												<div class="<?php if(isMobile()){?>medium<?php } else {?>huge<?php }?>"><?php
														$ratio = $Sum_of_Ticket_Hours / $Count_of_Tickets;
														echo $ratio >= 0 ? round($ratio,1) : 0;
													?></div>
													<div>Hours / Tickets</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="col-lg-4 col-md-4 col-xs-4" onClick="document.location.href='http://www.nouveauelevator.com/portal/tickets.php?Start_Date=01/01/1980&End_Date=12/31/2017';">
									<div class="panel panel-primary">
										<div class="panel-heading">
											<div class="row">
												<?php if(!isMobile()){?><div class="col-xs-3">
													<i class="fa fa-ticket fa-5x"></i>
												</div><?php }?>
												<div class="col-xs-<?php if(isMobile()){?>12 text-center<?php } else {?>9 text-right<?php }?> " style='min-height:75px;'>
													<div class="<?php if(isMobile()){?>medium<?php } else {?>huge<?php }?>"><?php
														$r = $database->query(null,"
															SELECT
																(SELECT Count(*) AS Counts
																 FROM   TicketD
																		LEFT JOIN Emp ON TicketD.fWork = Emp.fWork
																 WHERE  Emp.ID = ?)
																 +
																(SELECT Count(*) AS Counts
																 FROM   TicketDArchive
																		LEFT JOIN Emp ON TicketDArchive.fWork = Emp.fWork
																 WHERE  Emp.ID = ?)
																	AS Counter
														;",array($_SESSION['User'],$_SESSION['User']));
														$Count_of_Tickets = sqlsrv_fetch_array($r)['Counter'];
														echo number_format($Count_of_Tickets);
													?></div>
													<div>Completed Tickets</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="col-lg-4 col-md-4 col-xs-4" onClick="document.location.href='time_sheet.php">
									<div class="panel panel-primary">
										<div class="panel-heading">
											<div class="row">
												<?php if(!isMobile()){?><div class="col-xs-3">
													<i class="fa fa-clock-o fa-5x"></i>
												</div><?php }?>
												<div class="col-xs-<?php if(isMobile()){?>12 text-center<?php } else {?>9 text-right<?php }?> " style='min-height:75px;'>
													<div class="<?php if(isMobile()){?>medium<?php } else {?>huge<?php }?>"><?php
														$r = $database->query(null,"
															SELECT Sum(Total) AS Summed
															FROM   (SELECT Total, fWork FROM TicketD
																	UNION ALL
																	SELECT Total, fWork FROM TicketDArchive) AS Tickets
																	LEFT JOIN Emp ON Tickets.fWork = Emp.fWork
															WHERE   Emp.ID = ?
														;",array($_SESSION['User']));
														if( ($errors = sqlsrv_errors() ) != null) {
															foreach( $errors as $error ) {
																echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
																echo "code: ".$error[ 'code']."<br />";
																echo "message: ".$error[ 'message']."<br />";
															}
														}
														$Sum_of_Ticket_Hours = round(sqlsrv_fetch_array($r)['Summed']);
														echo number_format($Sum_of_Ticket_Hours);
													?></div>
													<div>Total Hours</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="col-lg-4 col-md-4 col-xs-4" onClick="document.location.href='http://www.nouveauelevator.com/portal/tickets.php?Start_Date=01/01/1980&End_Date=12/31/2017';">
									<div class="panel panel-primary">
										<div class="panel-heading">
											<div class="row">
												<?php if(!isMobile()){?><div class="col-xs-3">
													<i class="fa fa-clock-o fa-5x"></i>
												</div><?php }?>
												<div class="col-xs-<?php if(isMobile()){?>12 text-center<?php } else {?>9 text-right<?php }?> " style='min-height:75px;'>
													<div class="<?php if(isMobile()){?>medium<?php } else {?>huge<?php }?>"><?php
														$ratio = $Sum_of_Ticket_Hours / $Count_of_Tickets;
														echo $ratio >= 0 ? round($ratio,1) : 0;
													?></div>
													<div>Hours / Tickets</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="col-lg-4 col-md-4 col-xs-4" onClick="document.location.href='jobs.php';">
									<div class="panel panel-primary">
										<div class="panel-heading">
											<div class="row">
												<?php if(!isMobile()){?><div class="col-xs-3">
													<i class="fa fa-suitcase fa-5x"></i>
												</div><?php }?>
												<div class="col-xs-<?php if(isMobile()){?>12 text-center<?php } else {?>9 text-right<?php }?> " style='min-height:75px;'>
													<div class="<?php if(isMobile()){?>medium<?php } else {?>huge<?php }?>"><?php
														$r = $database->query(null,"
															SELECT Count(Tickets.Job) AS Counter
															FROM
																(
																	(
																		SELECT   TicketO.Job AS Job
																		FROM     nei.dbo.TicketO
																				 LEFT JOIN Emp ON TicketO.fWork = Emp.fWork
																		WHERE    Emp.ID = ?
																		GROUP BY TicketO.Job
																	)
																	UNION
																	(
																		SELECT   TicketD.Job AS Job
																		FROM     nei.dbo.TicketD
																				 LEFT JOIN Emp ON TicketD.fWork = Emp.fWork
																		WHERE    Emp.ID = ?
																		GROUP BY TicketD.Job
																	)
																	UNION
																	(
																		SELECT   TicketDArchive.Job AS Job
																		FROM     nei.dbo.TicketDArchive
																				 LEFT JOIN Emp ON TicketDArchive.fWork = Emp.fWork
																		WHERE    Emp.ID = ?
																		GROUP BY TicketDArchive.Job
																	)

																) AS Tickets
														;",array($_SESSION['User'],$_SESSION['User'],$_SESSION['User']));
														$Jobs_Worked_On = sqlsrv_fetch_array($r)['Counter'];
														echo $Jobs_Worked_On;
													?></div>
													<div>Jobs Worked on</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="col-lg-4 col-md-4 col-xs-4" onClick="document.location.href='violations.php';">
									<div class="panel panel-primary">
										<div class="panel-heading">
											<div class="row">
												<?php if(!isMobile()){?><div class="col-xs-3">
													<i class="fa fa-warning fa-5x"></i>
												</div><?php }?>
												<div class="col-xs-<?php if(isMobile()){?>12 text-center<?php } else {?>9 text-right<?php }?> " style='min-height:75px;'>
													<div class="<?php if(isMobile()){?>medium<?php } else {?>huge<?php }?>"><?php
														$r = $database->query(null,"
															SELECT Count(Violation.ID) AS Counter
															FROM   nei.dbo.Violation
																   LEFT JOIN nei.dbo.Loc   ON Violation.Loc = Loc.Loc
																   LEFT JOIN nei.dbo.Route ON Loc.Route     = Route.ID
																   LEFT JOIN Emp   ON Route.Mech    = Emp.fWork
															WHERE  Emp.ID = ?
																   AND Violation.Status <> 'Dismissed'
														;",array($_SESSION['User']));
														$Open_Violations_On_Route = sqlsrv_fetch_array($r)['Counter'];
														echo $Open_Violations_On_Route;
													?></div>
													<div>Open Violations</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="col-lg-4 col-md-4 col-xs-4" onClick="document.location.href='violations.php';">
									<div class="panel panel-primary">
										<div class="panel-heading">
											<div class="row">
												<?php if(!isMobile()){?><div class="col-xs-3">
													<i class="fa fa-warning fa-5x"></i>
												</div><?php }?>
												<div class="col-xs-<?php if(isMobile()){?>12 text-center<?php } else {?>9 text-right<?php }?> " style='min-height:75px;'>
													<div class="<?php if(isMobile()){?>medium<?php } else {?>huge<?php }?>"><?php
														$r = $database->query(null,"
															SELECT Count(Violation.ID) AS Counter
															FROM   nei.dbo.Violation
																   LEFT JOIN nei.dbo.Loc   ON Violation.Loc = Loc.Loc
																   LEFT JOIN nei.dbo.Route ON Loc.Route     = Route.ID
																   LEFT JOIN Emp   ON Route.Mech    = Emp.fWork
															WHERE  Emp.ID = ?
																   AND Violation.Status = 'Dismissed'
														;",array($_SESSION['User']));
														$Open_Violations_On_Route = sqlsrv_fetch_array($r)['Counter'];
														echo $Open_Violations_On_Route;
													?></div>
													<div>Completed Violations</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="col-lg-4 col-md-4 col-xs-4" onClick="document.location.href='locations.php';">
									<div class="panel panel-primary">
										<div class="panel-heading">
											<div class="row">
												<?php if(!isMobile()){?><div class="col-xs-3">
													<i class="fa fa-map fa-5x"></i>
												</div><?php }?>
												<div class="col-xs-<?php if(isMobile()){?>12 text-center<?php } else {?>9 text-right<?php }?> " style='min-height:75px;'>
													<div class="<?php if(isMobile()){?>medium<?php } else {?>huge<?php }?>"><?php
														$r = $database->query(null,"
															SELECT Count(Tickets.Loc) AS Counter
															FROM
																(
																	(
																		SELECT   TicketO.LID AS Loc
																		FROM     nei.dbo.TicketO
																				 LEFT JOIN Emp ON TicketO.fWork = Emp.fWork
																		WHERE    Emp.ID = ?
																		GROUP BY TicketO.LID
																	)
																	UNION
																	(
																		SELECT   TicketD.Loc AS Loc
																		FROM     nei.dbo.TicketD
																				 LEFT JOIN Emp ON TicketD.fWork = Emp.fWork
																		WHERE    Emp.ID = ?
																		GROUP BY TicketD.Loc
																	)
																	UNION
																	(
																		SELECT   TicketDArchive.Loc AS Loc
																		FROM     nei.dbo.TicketDArchive
																				 LEFT JOIN Emp ON TicketDArchive.fWork = Emp.fWork
																		WHERE    Emp.ID = ?
																		GROUP BY TicketDArchive.Loc
																	)

																) AS Tickets
														;",array($_SESSION['User'],$_SESSION['User'],$_SESSION['User']));
														$Open_Violations_On_Route = sqlsrv_fetch_array($r)['Counter'];
														echo $Open_Violations_On_Route;
													?></div>
													<div>Locations Visited</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="col-lg-4 col-md-4 col-xs-4" onClick="document.location.href='units.php';">
									<div class="panel panel-primary">
										<div class="panel-heading">
											<div class="row">
												<?php if(!isMobile()){?><div class="col-xs-3">
													<i class="fa fa-cogs fa-5x"></i>
												</div><?php }?>
												<div class="col-xs-<?php if(isMobile()){?>12 text-center<?php } else {?>9 text-right<?php }?> " style='min-height:75px;'>
													<div class="<?php if(isMobile()){?>medium<?php } else {?>huge<?php }?>"><?php
														$r = $database->query(null,"
															SELECT Count(Tickets.Unit) AS Counter
															FROM
																(
																	(
																		SELECT   TicketO.LElev AS Unit
																		FROM     nei.dbo.TicketO
																				 LEFT JOIN Emp ON TicketO.fWork = Emp.fWork
																		WHERE    Emp.ID = ?
																		GROUP BY TicketO.LElev
																	)
																	UNION
																	(
																		SELECT   TicketD.Elev AS Unit
																		FROM     nei.dbo.TicketD
																				 LEFT JOIN Emp ON TicketD.fWork = Emp.fWork
																		WHERE    Emp.ID = ?
																		GROUP BY TicketD.Elev
																	)
																	UNION
																	(
																		SELECT   TicketDArchive.Elev AS Unit
																		FROM     nei.dbo.TicketDArchive
																				 LEFT JOIN Emp ON TicketDArchive.fWork = Emp.fWork
																		WHERE    Emp.ID = ?
																		GROUP BY TicketDArchive.Elev
																	)

																) AS Tickets
														;",array($_SESSION['User'],$_SESSION['User'],$_SESSION['User']));
														$Open_Violations_On_Route = sqlsrv_fetch_array($r)['Counter'];
														echo number_format($Open_Violations_On_Route);
													?></div>
													<div>Units Worked</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="col-lg-4 col-md-4 col-xs-4" onClick="document.location.href='units.php';">
									<div class="panel panel-primary">
										<div class="panel-heading">
											<div class="row">
												<?php if(!isMobile()){?><div class="col-xs-3">
													<i class="fa fa-cogs fa-5x"></i>
												</div><?php }?>
												<div class="col-xs-<?php if(isMobile()){?>12 text-center<?php } else {?>9 text-right<?php }?> " style='min-height:75px;'>
													<div class="<?php if(isMobile()){?>medium<?php } else {?>huge<?php }?>"><?php
														$r = $database->query(null,"
															SELECT Count(Tickets.Job) AS Maintenances
															FROM
																(
																	(
																		SELECT   TicketO.Job AS Job
																		FROM     nei.dbo.TicketO
																				 LEFT JOIN Emp ON TicketO.fWork = Emp.fWork
																				 LEFT JOIN nei.dbo.Job ON TicketO.Job   = Job.ID
																		WHERE    Emp.ID = ?
																				 AND Job.Type = 0
																		GROUP BY TicketO.Job
																	)
																	UNION
																	(
																		SELECT   TicketD.Job AS Job
																		FROM     nei.dbo.TicketD
																				 LEFT JOIN Emp ON TicketD.fWork = Emp.fWork
																				 LEFT JOIN nei.dbo.Job ON TicketD.Job   = Job.ID
																		WHERE    Emp.ID = ?
																				 AND Job.Type = 0
																		GROUP BY TicketD.Job
																	)
																	UNION
																	(
																		SELECT   TicketDArchive.Job AS Job
																		FROM     nei.dbo.TicketDArchive
																				 LEFT JOIN Emp ON TicketDArchive.fWork = Emp.fWork
																				 LEFT JOIN nei.dbo.Job ON TicketDArchive.Job   = Job.ID
																		WHERE    Emp.ID = ?
																				 AND Job.Type = 0
																		GROUP BY TicketDArchive.Job
																	)

																) AS Tickets
														;",array($_SESSION['User'],$_SESSION['User'],$_SESSION['User']));
														echo $r ? sqlsrv_fetch_array($r)['Maintenances'] : 0;
													?></div>
													<div>Maintenances</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="col-lg-4 col-md-4 col-xs-4" onClick="document.location.href='units.php';">
									<div class="panel panel-primary">
										<div class="panel-heading">
											<div class="row">
												<?php if(!isMobile()){?><div class="col-xs-3">
													<i class="fa fa-cogs fa-5x"></i>
												</div><?php }?>
												<div class="col-xs-<?php if(isMobile()){?>12 text-center<?php } else {?>9 text-right<?php }?> " style='min-height:75px;'>
													<div class="<?php if(isMobile()){?>medium<?php } else {?>huge<?php }?>"><?php
														$r = $database->query(null,"
															SELECT Count(Tickets.Job) AS Modernizations
															FROM
																(
																	(
																		SELECT   TicketO.Job AS Job
																		FROM     nei.dbo.TicketO
																				 LEFT JOIN Emp ON TicketO.fWork = Emp.fWork
																				 LEFT JOIN nei.dbo.Job ON TicketO.Job   = Job.ID
																		WHERE    Emp.ID = ?
																				 AND Job.Type = 2
																		GROUP BY TicketO.Job
																	)
																	UNION
																	(
																		SELECT   TicketD.Job AS Job
																		FROM     nei.dbo.TicketD
																				 LEFT JOIN Emp ON TicketD.fWork = Emp.fWork
																				 LEFT JOIN nei.dbo.Job ON TicketD.Job   = Job.ID
																		WHERE    Emp.ID = ?
																				 AND Job.Type = 2
																		GROUP BY TicketD.Job
																	)
																	UNION
																	(
																		SELECT   TicketDArchive.Job AS Job
																		FROM     nei.dbo.TicketDArchive
																				 LEFT JOIN Emp ON TicketDArchive.fWork = Emp.fWork
																				 LEFT JOIN nei.dbo.Job ON TicketDArchive.Job   = Job.ID
																		WHERE    Emp.ID = ?
																				 AND Job.Type = 2
																		GROUP BY TicketDArchive.Job
																	)

																) AS Tickets
														;",array($_SESSION['User'],$_SESSION['User'],$_SESSION['User']));
														echo $r ? sqlsrv_fetch_array($r)['Modernizations'] : 0;
													?></div>
													<div>Modernizations</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="col-lg-4 col-md-4 col-xs-4" onClick="document.location.href='units.php';">
									<div class="panel panel-primary">
										<div class="panel-heading">
											<div class="row">
												<?php if(!isMobile()){?><div class="col-xs-3">
													<i class="fa fa-cogs fa-5x"></i>
												</div><?php }?>
												<div class="col-xs-<?php if(isMobile()){?>12 text-center<?php } else {?>9 text-right<?php }?> " style='min-height:75px;'>
													<div class="<?php if(isMobile()){?>medium<?php } else {?>huge<?php }?>"><?php
														$r = $database->query(null,"
															SELECT Count(Tickets.Job) AS Maintenances
															FROM
																(
																	(
																		SELECT   TicketO.Job AS Job
																		FROM     nei.dbo.TicketO
																				 LEFT JOIN Emp ON TicketO.fWork = Emp.fWork
																				 LEFT JOIN nei.dbo.Job ON TicketO.Job   = Job.ID
																		WHERE    Emp.ID = ?
																				 AND Job.Type = 6
																		GROUP BY TicketO.Job
																	)
																	UNION
																	(
																		SELECT   TicketD.Job AS Job
																		FROM     nei.dbo.TicketD
																				 LEFT JOIN Emp ON TicketD.fWork = Emp.fWork
																				 LEFT JOIN nei.dbo.Job ON TicketD.Job   = Job.ID
																		WHERE    Emp.ID = ?
																				 AND Job.Type = 6
																		GROUP BY TicketD.Job
																	)
																	UNION
																	(
																		SELECT   TicketDArchive.Job AS Job
																		FROM     nei.dbo.TicketDArchive
																				 LEFT JOIN Emp ON TicketDArchive.fWork = Emp.fWork
																				 LEFT JOIN nei.dbo.Job ON TicketDArchive.Job   = Job.ID
																		WHERE    Emp.ID = ?
																				 AND Job.Type = 6
																		GROUP BY TicketDArchive.Job
																	)

																) AS Tickets
														;",array($_SESSION['User'],$_SESSION['User'],$_SESSION['User']));
														echo $r ? sqlsrv_fetch_array($r)['Maintenances'] : 0;
													?></div>
													<div>Repairs</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="col-lg-4 col-md-4 col-xs-4" onClick="document.location.href='units.php';">
								<div class="panel panel-primary">
									<div class="panel-heading">
										<div class="row">
											<?php if(!isMobile()){?><div class="col-xs-3">
												<i class="fa fa-bell fa-5x"></i>
											</div><?php }?>
											<div class="col-xs-<?php if(isMobile()){?>12 text-center<?php } else {?>9 text-right<?php }?> " style='min-height:75px;'>
												<div class="<?php if(isMobile()){?>medium<?php } else {?>huge<?php }?>"><?php
													$r = $database->query(null,"
														SELECT Tickets.Date
														FROM
															(
																(
																	SELECT   TicketO.EDate AS Date
																	FROM     nei.dbo.TicketO
																			 LEFT JOIN Emp ON TicketO.fWork = Emp.fWork
																	WHERE    Emp.ID = ?
																			 AND (TicketO.Assigned    = 2
																				  OR TicketO.Assigned = 3
																				  OR TicketO.Assigned = 4)
																)
																UNION ALL
																(
																	SELECT   TicketD.EDate AS Date
																	FROM     nei.dbo.TicketD
																			 LEFT JOIN Emp ON TicketD.fWork = Emp.fWork
																	WHERE    Emp.ID = ?
																)
																UNION ALL
																(
																	SELECT   TicketDArchive.EDate AS Date
																	FROM     nei.dbo.TicketDArchive
																			 LEFT JOIN Emp ON TicketDArchive.fWork = Emp.fWork
																	WHERE    Emp.ID = ?
																)

															) AS Tickets
													;",array($_SESSION['User'],$_SESSION['User'],$_SESSION['User']));

													$Attendance = array();
													if($r){while($Ticket = sqlsrv_fetch_array($r)){$Attendance[substr($Ticket['Date'],0,10)] = 1; }}
													$Days_Worked = 0;
													if(count($Attendance) > 0){
														foreach($Attendance as $Day){
															$Days_Worked += 1;
														}
													}
													echo number_format($Days_Worked);
												?></div>
												<div>Days Worked</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
                </div>
                <?php if(!isMobile()){?><div class='row'><div class="col-lg-12">
                    <div class="panel panel-primary">
                        <div class="panel-heading">Work Hours A Day</div>
                        <div class="panel-body">
                            <div class="flot-chart"><div class="flot-chart-content" id="flot-placeholder"></div></div>
                        </div>
                    </div>
                </div></div><?php }?>
            </div>
        </div><?php } else {?><div id="page-wrapper" class='content'>
			<div class='row'>
				<!--<div class='col-xs-12' style='background-color:whitesmoke;height:32px;font-size:24px;font-weight:bold;text-align:center;'>Home</div>-->
				<style>
					.dashboard-option {
						float:left;
						padding:2.5%;
						background-color:rgba(50,50,50,1);
						color:whitesmoke;
						padding-left:5%;
						padding-right:5%;
					}
					.dashboard-option:hover {
						background-color:rgba(205,205,205,1);
						color:black;
					}
					.dashboard-options {
						background-color:rgba(50,50,50,1);
					}
				</style>
				<script>
				function activateContent(subcontent){
					$("div#subcontent").children().hide();
					setTimeout(function(){$("div#" + subcontent).show()},100);;
				}
				</script>
				<div class='col-xs-12 dashboard-options'>
					<div class='dashboard-option' onClick="activateContent('my-calendar');">
						<i class='fa fa-2x fa-calendar'></i>
					</div>
					<div class='dashboard-option' onClick="activateContent('my-ticket');">
						<i class='fa fa-2x fa-ticket'></i>
					</div>
					<div class='dashboard-option' onClick="activateContent('my-tickets');">
						<i class='fa fa-2x fa-flag'></i>
					</div>
					<?php
					$r = $database->query(null,"SELECT * FROM nei.dbo.Route LEFT JOIN Emp ON Route.Mech = Emp.fWork WHERE Emp.ID = ?",array($_SESSION['User']));
					if($r){if(is_array(sqlsrv_fetch_array($r))){?>
					<div class='dashboard-option' onClick="activateContent('my-maintenance');">
						<i class='fa fa-2x fa-cogs'></i>
					</div>
					<div class='dashboard-option' onClick="activateContent('my-violations');">
						<i class='fa fa-2x fa-warning'></i>
					</div>
					<?php }}?>
					<?php
					$r = $database->query(null,"SELECT * FROM nei.dbo.Vehicle LEFT JOIN Emp ON Emp.fWork = Vehicle.fWork WHERE Emp.ID = ?;", array($_SESSION['User']));
					if($r){if(is_array(sqlsrv_fetch_array($r))){?>
					<div class='dashboard-option' onClick="document.location.href='inspection.php'">
						<i class='fa fa-2x fa-truck'></i>
					</div>
					<?php }}?>
					<div class='clear:both;'></div>
				</div>
				<div id='subcontent'>
					<div id='my-calendar' class='subcontent col-xs-12' style='margin:0px !important;padding:0px !important;'>
						<h4 style='text-align:center;background-color:whitesmoke;color:black;margin:0px;padding:10px;'>My Timesheet</h4>
						<style>
							#table-my-calendar thead th, #table-my-calendar tbody td {
								border:1px solid black;
							}
						</style>
						<table width="100%" class="" id="table-my-calendar" style='font-size:12px;'>
                                <thead>
                                    <tr>
                                        <th>Weeks</th>
                                        <th>Thu</th>
                                        <th>Fri</th>
                                        <th>Sat</th>
                                        <th>Sun</th>
                                        <th>Mon</th>
                                        <th>Tue</th>
                                        <th>Wed</th>
                                        <th>Total</th>
										<th>Expenses</th>
                                    </tr>
                                </thead>
                                <style>
                                .hoverGray:hover {
                                    background-color:#dfdfdf !important;
                                }
                                </style>
                                <tbody>
                                    <tr style='cursor:pointer;' class="odd gradeX hoverGray">
                                        <?php $Today = date('l');
										$Employee_ID = $My_User['fWork'];
                                        $Date = date('Y-m-d');
                                        if($Today == 'Thursday'){$WeekOf = date('Y-m-d');}
                                        elseif($Today == 'Friday'){$WeekOf = date('Y-m-d', strtotime($Date . ' -1 days'));}
                                        elseif($Today == 'Saturday'){$WeekOf = date('Y-m-d', strtotime($Date . ' -2 days'));}
                                        elseif($Today == 'Sunday'){$WeekOf = date('Y-m-d', strtotime($Date . ' -3 days'));}
                                        elseif($Today == 'Monday'){$WeekOf = date('Y-m-d', strtotime($Date . ' -4 days'));}
                                        elseif($Today == 'Tuesday'){$WeekOf = date('Y-m-d', strtotime($Date . ' -5 days'));}
                                        elseif($Today == 'Wednesday'){$WeekOf = date('Y-m-d', strtotime($Date . ' -6 days'));}
                                        $WeekOf = date('Y-m-d',strtotime($WeekOf . ' +6 days'));?>
                                        <td class='WeekOf' rel='<?php echo $WeekOf;?>' onClick="refresh_this(this);"><?php
                                            echo date("m/d",strtotime($WeekOf));
                                        ?></td><?php
                                        $Today = date('l');
                                        if($Today == 'Thursday'){$Thursday = date('Y-m-d');}
                                        elseif($Today == 'Friday'){$Thursday = date('Y-m-d', strtotime($Date . ' -1 days'));}
                                        elseif($Today == 'Saturday'){$Thursday = date('Y-m-d', strtotime($Date . ' -2 days'));}
                                        elseif($Today == 'Sunday'){$Thursday = date('Y-m-d', strtotime($Date . ' -3 days'));}
                                        elseif($Today == 'Monday'){$Thursday = date('Y-m-d', strtotime($Date . ' -4 days'));}
                                        elseif($Today == 'Tuesday'){$Thursday = date('Y-m-d', strtotime($Date . ' -5 days'));}
                                        elseif($Today == 'Wednesday'){$Thursday = date('Y-m-d', strtotime($Date . ' -6 days'));}
                                        $r = $database->query(null,"
                                            SELECT Sum(Total) as Summed
                                            FROM nei.dbo.TicketD
                                            WHERE fWork='" . $Employee_ID . "' and EDate >= '" . $Thursday . " 00:00:00.000' AND EDate <= '" . $Thursday . " 23:59:59.999'");?>
                                        <td class='Thursday' rel='<?php echo $Thursday;?>' onClick="refresh_this(this);"><?php
                                            echo sqlsrv_fetch_array($r)['Summed'];
                                        ?></td>
                                        <?php
                                        if($Today == 'Friday'){$Friday = date('Y-m-d');}
                                        elseif($Today == 'Saturday'){$Friday = date('Y-m-d', strtotime($Date . ' -1 days'));}
                                        elseif($Today == 'Sunday'){$Friday = date('Y-m-d', strtotime($Date . ' -2 days'));}
                                        elseif($Today == 'Monday'){$Friday = date('Y-m-d', strtotime($Date . ' -3 days'));}
                                        elseif($Today == 'Tuesday'){$Friday = date('Y-m-d', strtotime($Date . ' -4 days'));}
                                        elseif($Today == 'Wednesday'){$Friday = date('Y-m-d', strtotime($Date . ' -5 days'));}
                                        elseif($Today == 'Thursday'){$Friday = date('Y-m-d', strtotime($Date . ' +1 days'));}
                                        $r = $database->query(null,"
                                            SELECT Sum(Total) AS Summed
                                            FROM nei.dbo.TicketD
                                            WHERE fWork='" . $Employee_ID . "' and EDate >= '" . $Friday . " 00:00:00.000' AND EDate <= '" . $Friday . " 23:59:59.999'");?>
                                        <td class='Friday' rel='<?php echo $Friday;?>' onClick="refresh_this(this);"><?php
                                            echo sqlsrv_fetch_array($r)['Summed'];
                                        ?></td>
                                        <?php
                                        if($Today == 'Satuday'){$Saturday = date('Y-m-d');}
                                        elseif($Today == 'Sunday'){$Saturday = date('Y-m-d', strtotime($Date . ' -1 days'));}
                                        elseif($Today == 'Monday'){$Saturday = date('Y-m-d', strtotime($Date . ' -2 days'));}
                                        elseif($Today == 'Tuesday'){$Saturday = date('Y-m-d', strtotime($Date . ' -3 days'));}
                                        elseif($Today == 'Wednesday'){$Saturday = date('Y-m-d', strtotime($Date . ' -4 days'));}
                                        elseif($Today == 'Thursday'){$Saturday = date('Y-m-d', strtotime($Date . ' +2 days'));}
                                        elseif($Today == 'Friday'){$Saturday = date('Y-m-d', strtotime($Date . ' +1 days'));}
                                        $r = $database->query(null,"
                                            SELECT Sum(Total) AS Summed
                                            FROM nei.dbo.TicketD
                                            WHERE fWork='" . $Employee_ID . "' and EDate >= '" . $Saturday . " 00:00:00.000' AND EDate <= '" . $Saturday . " 23:59:59.999'");?>
                                        <td class='Saturday' rel='<?php echo $Saturday;?>' onClick="refresh_this(this);"><?php
                                            echo sqlsrv_fetch_array($r)['Summed'];
                                        ?></td>
                                        <?php
                                        if($Today == 'Sunday'){$Sunday = date('Y-m-d');}
                                        elseif($Today == 'Monday'){$Sunday = date('Y-m-d', strtotime($Date . ' -1 days'));}
                                        elseif($Today == 'Tuesday'){$Sunday = date('Y-m-d', strtotime($Date . ' -2 days'));}
                                        elseif($Today == 'Wednesday'){$Sunday = date('Y-m-d', strtotime($Date . ' -3 days'));}
                                        elseif($Today == 'Thursday'){$Sunday = date('Y-m-d', strtotime($Date . ' +3 days'));}
                                        elseif($Today == 'Friday'){$Sunday = date('Y-m-d', strtotime($Date . ' +2 days'));}
                                        elseif($Today == 'Saturday'){$Sunday = date('Y-m-d', strtotime($Date . ' +1 days'));}
                                        $r = $database->query(null,"
                                            SELECT Sum(Total) AS Summed
                                            FROM nei.dbo.TicketD

                                            WHERE fWork='" . $Employee_ID . "' and EDate >= '" . $Sunday . " 00:00:00.000' AND EDate <= '" . $Sunday . " 23:59:59.999'");?>
                                        <td class='Sunday' rel='<?php echo $Sunday;?>' onClick="refresh_this(this);"><?php
                                            echo sqlsrv_fetch_array($r)['Summed'];
                                        ?></td>
                                        <?php
                                        if($Today == 'Monday'){$Monday = date('Y-m-d');}
                                        elseif($Today == 'Tuesday'){$Monday = date('Y-m-d', strtotime($Date . ' -1 days'));}
                                        elseif($Today == 'Wednesday'){$Monday = date('Y-m-d', strtotime($Date . ' -2 days'));}
                                        elseif($Today == 'Thursday'){$Monday = date('Y-m-d', strtotime($Date . ' +4 days'));}
                                        elseif($Today == 'Friday'){$Monday = date('Y-m-d', strtotime($Date . ' +3 days'));}
                                        elseif($Today == 'Saturday'){$Monday = date('Y-m-d', strtotime($Date . ' +2 days'));}
                                        elseif($Today == 'Sunday'){$Monday = date('Y-m-d', strtotime($Date . ' +1 days'));}
                                        $r = $database->query(null,"
                                            SELECT Sum(Total) AS Summed
                                            FROM nei.dbo.TicketD
                                            WHERE fWork='" . $Employee_ID . "' and EDate >= '" . $Monday . " 00:00:00.000' AND EDate <= '" . $Monday . " 23:59:59.999'");?>
                                        <td class='Monday' rel='<?php echo $Monday;?>' onClick="refresh_this(this);"><?php
                                            echo sqlsrv_fetch_array($r)['Summed'];
                                        ?></td>
                                        <?php
                                        if($Today == 'Tuesday'){$Tuesday = date('Y-m-d');}
                                        elseif($Today == 'Wednesday'){$Tuesday = date('Y-m-d', strtotime($Date . ' -1 days'));}
                                        elseif($Today == 'Thursday'){$Tuesday = date('Y-m-d', strtotime($Date . ' +5 days'));}
                                        elseif($Today == 'Friday'){$Tuesday = date('Y-m-d', strtotime($Date . ' +4 days'));}
                                        elseif($Today == 'Saturday'){$Tuesday = date('Y-m-d', strtotime($Date . ' +3 days'));}
                                        elseif($Today == 'Sunday'){$Tuesday = date('Y-m-d', strtotime($Date . ' +2 days'));}
                                        elseif($Today == 'Monday'){$Tuesday = date('Y-m-d', strtotime($Date . ' +1 days'));}
                                        $r = $database->query(null,"
                                            SELECT Sum(Total) AS Summed
                                            FROM nei.dbo.TicketD
                                            WHERE fWork='" . $Employee_ID . "' and EDate >= '" . $Tuesday . " 00:00:00.000' AND EDate <= '" . $Tuesday . " 23:59:59.999'");?>
                                        <td class='Tuesday' rel='<?php echo $Tuesday;?>' onClick="refresh_this(this);"><?php
                                            echo sqlsrv_fetch_array($r)['Summed'];
                                        ?></td>
                                        <?php
                                        if($Today == 'Wednesday'){$Wednesday = date('Y-m-d');}
                                        elseif($Today == 'Thursday'){$Wednesday = date('Y-m-d', strtotime($Date . ' +6 days'));}
                                        elseif($Today == 'Friday'){$Wednesday = date('Y-m-d', strtotime($Date . ' +5 days'));}
                                        elseif($Today == 'Saturday'){$Wednesday = date('Y-m-d', strtotime($Date . ' +4 days'));}
                                        elseif($Today == 'Sunday'){$Wednesday = date('Y-m-d', strtotime($Date . ' +3 days'));}
                                        elseif($Today == 'Monday'){$Wednesday = date('Y-m-d', strtotime($Date . ' +2 days'));}
                                        elseif($Today == 'Tuesday'){$Wednesday = date('Y-m-d', strtotime($Date . ' +1 days'));}
                                        $r = $database->query(null,"
                                            SELECT Sum(Total) AS Summed
                                            FROM nei.dbo.TicketD
                                            WHERE fWork='" . $Employee_ID . "' and EDate >= '" . $Wednesday . " 00:00:00.000' AND EDate <= '" . $Wednesday . " 23:59:59.999'");?>
                                        <td class='Wednesday' rel='<?php echo $Wednesday;?>' onClick="refresh_this(this);"><?php
                                            echo sqlsrv_fetch_array($r)['Summed'];
                                        ?></td>
                                        <td><?php
                                            $r = $database->query(null,"SELECT Sum(Total) AS Summed FROM nei.dbo.TicketD WHERE fWork='" . $Employee_ID . "' and EDate >= '" . $Thursday . " 00:00:00.000' AND EDate <= '" . $Wednesday . " 23:59:59.999'");
                                            echo sqlsrv_fetch_array($r)['Summed'];
                                        ?></td>
										<td>$<?php
											$r = $database->query(null,"SELECT Sum(Zone) + Sum(Toll) + Sum(OtherE) AS Expenses FROM nei.dbo.TicketD WHERE fWork = ? AND EDate >= '" . $Thursday . " 00:00:00.000' AND EDate <= '" . $Wednesday . " 23:59:59.999'",array($Employee_ID));
											echo sqlsrv_fetch_array($r)['Expenses'];
										?></td>
                                    </tr>
                                    <?php while($WeekOf > "2017-03-08 00:00:00.000"){?><tr style='cursor:pointer;' class="odd gradeX hoverGray">
                                        <?php $WeekOf = date('Y-m-d',strtotime($WeekOf . '-7 days')); ?>
                                        <td class='WeekOf' rel='<?php echo $WeekOf;?>' onClick="refresh_this(this);"><?php
                                            echo date("m/d",strtotime($WeekOf));
                                        ?></td>
                                        <?php
                                        $Thursday = date('Y-m-d',strtotime($Thursday . '-7 days'));
                                            $r = $database->query(null,"SELECT Sum(Total) AS Summed FROM nei.dbo.TicketD WHERE fWork='" . $Employee_ID . "' and EDate >= '" . $Thursday . " 00:00:00.000' AND EDate <= '" . $Thursday . " 23:59:59.999'");?>
                                        <td class='Thursday' rel='<?php echo $Thursday;?>' onClick="refresh_this(this);"><?php
                                            echo sqlsrv_fetch_array($r)['Summed'];
                                        ?></td>
                                        <?php $Friday = date('Y-m-d',strtotime($Friday . '-7 days'));
                                            $r = $database->query(null,"SELECT Sum(Total) AS Summed FROM nei.dbo.TicketD WHERE fWork='" . $Employee_ID . "' and EDate >= '" . $Friday . " 00:00:00.000' AND EDate <= '" . $Friday . " 23:59:59.999'");?>
                                        <td class='Friday' rel='<?php echo $Friday;?>' onClick="refresh_this(this);"><?php
                                            echo sqlsrv_fetch_array($r)['Summed'];
                                        ?></td>
                                        <?php $Saturday = date('Y-m-d',strtotime($Saturday . '-7 days'));
                                            $r = $database->query(null,"SELECT Sum(Total) AS Summed FROM nei.dbo.TicketD WHERE fWork='" . $Employee_ID . "' and EDate >= '" . $Saturday . " 00:00:00.000' AND EDate <= '" . $Saturday . " 23:59:59.999'");?>
                                        <td class='Saturday' rel='<?php echo $Saturday;?>' onClick="refresh_this(this);"><?php
                                            echo sqlsrv_fetch_array($r)['Summed'];
                                        ?></td>
                                        <?php $Sunday = date('Y-m-d',strtotime($Sunday . '-7 days'));
                                            $r = $database->query(null,"SELECT Sum(Total) AS Summed FROM nei.dbo.TicketD WHERE fWork='" . $Employee_ID . "' and EDate >= '" . $Sunday . " 00:00:00.000' AND EDate <= '" . $Sunday . " 23:59:59.999'");?>
                                        <td class='Sunday' rel='<?php echo $Sunday;?>' onClick="refresh_this(this);"><?php
                                            echo sqlsrv_fetch_array($r)['Summed'];
                                        ?></td>
                                        <?php $Monday = date('Y-m-d',strtotime($Monday . '-7 days'));
                                            $r = $database->query(null,"SELECT Sum(Total) AS Summed FROM nei.dbo.TicketD WHERE fWork='" . $Employee_ID . "' and EDate >= '" . $Monday . " 00:00:00.000' AND EDate <= '" . $Monday . " 23:59:59.999'");?>
                                        <td class='Monday' rel='<?php echo $Monday;?>' onClick="refresh_this(this);"><?php
                                            echo sqlsrv_fetch_array($r)['Summed'];
                                        ?></td>
                                        <?php $Tuesday = date('Y-m-d',strtotime($Tuesday . '-7 days'));
                                            $r = $database->query(null,"SELECT Sum(Total) AS Summed FROM nei.dbo.TicketD WHERE fWork='" . $Employee_ID . "' and EDate >= '" . $Tuesday . " 00:00:00.000' AND EDate <= '" . $Tuesday . " 23:59:59.999'");?>
                                        <td class='Tuesday' rel='<?php echo $Tuesday;?>' onClick="refresh_this(this);"><?php
                                            echo sqlsrv_fetch_array($r)['Summed'];
                                        ?></td>
                                        <?php $Wednesday = date('Y-m-d',strtotime($Wednesday . '-7 days'));
                                            $r = $database->query(null,"SELECT Sum(Total) AS Summed FROM nei.dbo.TicketD WHERE fWork='" . $Employee_ID . "' and EDate >= '" . $Wednesday . " 00:00:00.000' AND EDate <= '" . $Wednesday . " 23:59:59.999'");?>
                                        <td class='Wednesday' rel='<?php echo $Wednesday;?>' onClick="refresh_this(this);"><?php
                                            echo sqlsrv_fetch_array($r)['Summed'];
                                        ?></td>
                                        <td><?php
                                            $r = $database->query(null,"SELECT Sum(Total) AS Summed FROM nei.dbo.TicketD WHERE fWork='" . $Employee_ID . "' and EDate >= '" . $Thursday . " 00:00:00.000' AND EDate <= '" . $Wednesday . " 23:59:59.999'");
                                            echo sqlsrv_fetch_array($r)['Summed'];
                                        ?></td>
										<td>$<?php
											$r = $database->query(null,"SELECT Sum(Zone) + Sum(Toll) + Sum(OtherE) AS Expenses FROM nei.dbo.TicketD WHERE fWork = ? AND EDate >= '" . $Thursday . " 00:00:00.000' AND EDate <= '" . $Wednesday . " 23:59:59.999'",array($Employee_ID));
											echo sqlsrv_fetch_array($r)['Expenses'];
										?></td>
                                    </tr><?php }?>
                                </tbody>
                            </table>
					</div>
					<div id='my-ticket' class='subcontent col-xs-12' style='margin:0px !important;padding:0px !important;display:none;'>
						<h4 style='text-align:center;background-color:whitesmoke;color:black;margin:0px;padding:10px;'>My Ticket</h4>
						<?php
							$r = $database->query(null,"
								SELECT Top 1
									   Tickets.*,
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
								FROM
									(
										(SELECT
												CASE WHEN TicketO.Assigned = 2 OR TicketO.Assigned = 3
													 THEN '2099-01-01 00:00:00.000'
													 ELSE EDate
												END 			 AS Order_Date,
												TicketO.ID       AS ID,
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
												TicketO.fWork    AS Mechanic,
												TickOStatus.Type AS Status,
												0                AS Total,
												0                AS Regular,
												0                AS Overtime,
												0                AS Doubletime
										 FROM   nei.dbo.TicketO
												LEFT JOIN Emp ON TicketO.fWork = Emp.fWork
												LEFT JOIN TickOStatus ON TicketO.Assigned = TickOStatus.Ref
										 WHERE  Emp.ID = ?
												AND (  TicketO.Assigned = 2
													OR TicketO.Assigned = 3)
										)
										UNION ALL
										(SELECT TicketD.EDate    AS Order_Date,
												TicketD.ID       AS ID,
												TicketD.fDesc    AS Description,
												TicketD.DescRes  AS Resolution,
												TicketD.CDate    AS Created,
												TicketD.DDate    AS Dispatched,
												TicketD.EDate    AS Worked,
												TicketD.TimeSite AS On_Site,
												TicketD.TimeComp AS Completed,
												TicketD.Who 	 AS Caller,
												TicketD.fBy      AS Reciever,
												TicketD.Level    AS Level,
												TicketD.Cat      AS Category,
												TicketD.Loc      AS Location,
												TicketD.Job      AS Job,
												TicketD.Elev     AS Unit,
												TicketD.fWork    AS Mechanic,
												'Completed'      AS Status,
												TicketD.Total    AS Total,
												TicketD.Reg      AS Regular,
												TicketD.OT       AS Overtime,
												TicketD.DT       AS Doubletime
										 FROM   nei.dbo.TicketD
												LEFT JOIN Emp ON TicketD.fWork = Emp.fWork
										 WHERE  Emp.ID = ?
										)
										UNION ALL
										(SELECT TicketDArchive.EDate    AS Order_Date,
												TicketDArchive.ID       AS ID,
												TicketDArchive.fDesc    AS Description,
												TicketDArchive.DescRes  AS Resolution,
												TicketDArchive.CDate    AS Created,
												TicketDArchive.DDate    AS Dispatched,
												TicketDArchive.EDate    AS Worked,
												TicketDArchive.TimeSite AS On_Site,
												TicketDArchive.TimeComp AS Completed,
												TicketDArchive.Who 	    AS Caller,
												TicketDArchive.fBy      AS Reciever,
												TicketDArchive.Level    AS Level,
												TicketDArchive.Cat      AS Category,
												TicketDArchive.Loc      AS Location,
												TicketDArchive.Job      AS Job,
												TicketDArchive.Elev     AS Unit,
												TicketDArchive.fWork    AS Mechanic,
												'Completed'             AS Status,
												TicketDArchive.Total    AS Total,
												TicketDArchive.Reg      AS Regular,
												TicketDArchive.OT       AS Overtime,
												TicketDArchive.DT       AS Doubletime
										 FROM   nei.dbo.TicketDArchive
												LEFT JOIN Emp ON TicketDArchive.fWork = Emp.fWork
										 WHERE  Emp.ID = ?
										)
									) AS Tickets
									LEFT JOIN nei.dbo.Loc          ON Tickets.Location = Loc.Loc
									LEFT JOIN nei.dbo.Job          ON Tickets.Job      = Job.ID
									LEFT JOIN nei.dbo.OwnerWithRol ON Job.Owner        = OwnerWithRol.ID
									LEFT JOIN nei.dbo.Elev         ON Tickets.Unit     = Elev.ID
									LEFT JOIN Emp          ON Tickets.Mechanic = Emp.fWork
									LEFT JOIN nei.dbo.JobType      ON Job.Type         = JobType.ID
									LEFT JOIN nei.dbo.Zone 		   ON Zone.ID          = Loc.Zone
									LEFT JOIN nei.dbo.Route		   ON Route.ID		   = Loc.Route
								ORDER BY Order_Date DESC
							;",array($_SESSION['User'],$_SESSION['User'],$_SESSION['User']));
							$Ticket = sqlsrv_fetch_array($r);
						?><div class='row'>
							<div class='col-xs-12' style='height:5px;'>&nbsp;</div>
							<div class='col-xs-12'><pre><?php echo 'Location: ' . $Ticket['Location'];?></pre></div>
							<div class='col-xs-12'><pre><?php echo 'Unit: ' . $Ticket['Unit_State'];?></pre></div>
							<div class='col-xs-12'><pre><?php echo 'Scheduled: ' . date("m/d/Y H:i:s",strtotime($Ticket['Scheduled']));?></pre></div>
							<div class='col-xs-12'><pre><?php echo 'Status: ' . $Ticket['Status'];?></pre></div>
							<div class='col-xs-12'><pre><?php echo "---Scope of Work---\n" . $Ticket['Description'];?></pre></div>
						</div>
					</div>
					<?php
					$r = $database->query(null,"SELECT * FROM nei.dbo.Route LEFT JOIN Emp ON Route.Mech = Emp.fWork WHERE Emp.ID = ?",array($_SESSION['User']));
					if($r){if(is_array(sqlsrv_fetch_array($r))){?>
					<div id='my-maintenance' class='subcontent col-xs-12' style='margin:0px !important;padding:0px !important;display:none;'>
						<h4 style='text-align:center;background-color:whitesmoke;color:black;margin:0px;padding:10px;'>Required Route Maintenance</h4>
						<table id='Table_Maintenances' class='display' cellspacing='0' width='100%' style='font-size:12px;'>
							<thead>
								<th>ID</th>
								<th title='Location'>Location</th>
								<th title='Unit'>Unit</th>
								<th title='State'>State</th>
								<th title="Last Maintenance">Maintained</th>
							</thead>
							<tbody></tbody>
						</table>
						<script>
						<?php
							$r = $database->query(null,"SELECT Route.ID AS ID FROM nei.dbo.Route LEFT JOIN Emp ON Route.Mech = Emp.fWork WHERE Emp.ID = ?",array($_SESSION['User']));
							$Route = sqlsrv_fetch_array($r)['ID'];
						?>
						$(document).ready(function(){
							var Table_Maintenances = $('#Table_Maintenances').DataTable( {
								"ajax": "bin/php/get/Maintenances_by_Route.php?ID=<?php echo $Route;?>",
								"columns": [
									{
										"data" : "ID",
										"className":"hidden"
									},{
										"data": "Location"
									},{
										"data": "Unit"
									},{
										"data": "State"
									},{
										"data": "Last_Date",
										"render": function(data){
											if(data === null || typeof data === 'undefined'){return '';}
											else {return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}}
									}
								],
								"order": [[1, 'asc']],
								"language":{
									"loadingRecords":""
								},
								paging:false,
								searching:false,
								"initComplete":function(){}

							} );
							<?php if(isMobile()){
							?>$('#Table_Maintenances tbody').on( 'click', 'tr', function () {
								window.location = 'unit.php?ID=' + $(this).closest('tr').find('td:first-child').html();
							} );<?php }?>
						});
						</script>

					</div>
					<div id='my-violations' class='subcontent col-xs-12' style='margin:0px !important;padding:0px !important;display:none;'>
						<h4 style='text-align:center;background-color:whitesmoke;color:black;margin:0px;padding:10px;'>Route Violations</h4>
						<table id='Table_Violations' class='display' cellspacing='0' width='100%' style='font-size:12px;'>
							<thead>
								<th title='ID of the Violation'>ID</th>
								<th>Job</th>
								<th title='Name of the Violation'>Name</th>
								<th title='Location of the Violation'>Location</th>
								<th title="Violation's Unit">Unit</th>
								<th title="Date of the Violation">Date</th>
								<th title='Status of the Violation'>Status</th>
							</thead>
							<tbody></tbody>
						</table>
						<script>
						$(document).ready(function(){
							var Table_Violations = $('#Table_Violations').DataTable( {
								"ajax": {
									"url":"bin/php/get/Violations_by_Route.php?ID=<?php echo $Route;?>",
									"dataSrc":function(json){
										if(!json.data){json.data = [];}
										return json.data;}
								},
								"columns": [
									{
										"data": "ID",
										"className":"hidden"
									},{
										"data": "Job",
										"visible":false,
										render:function(data){if(data === undefined){return '';}else{return data;}}
									},{
										"data": "Name"
									},{
										"data": "Location"
									},{
										"data": "Unit"
									},{
										"data": "fDate",
									  	render: function(data){
											if(data === null || typeof data === 'undefined'){return '';}
											else {return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}
										}
									},{
										"data": "Status"
									}
								],
								"order": [[1, 'asc']],
								"lengthMenu":[[10,25,50,100,500,-1],[10,25,50,100,500,"All"]],
								"language":{"loadingRecords":""},
								paging:false,
								searching:false,
								select:true,
								"initComplete":function(){finishLoadingPage();}
							} );
							<?php if(isMobile()){
							?>$('#Table_Violations tbody').on( 'click', 'tr', function () {
								window.location = 'violation.php?ID=' + $(this).closest('tr').find('td:first-child').html();
							} );<?php }?>
						});
						</script>
					</div><?php }}?>
					<div id='my-tickets' class='subcontent col-xs-12' style='margin:0px !important;padding:0px !important;display:none;'>
						<h4 style='text-align:center;background-color:whitesmoke;color:black;margin:0px;padding:10px;'>My Ticket Schedule</h4>
						<table id='Table_Tickets' class='display' cellspacing='0' width='100%' style='font-size:12px;'>
							<thead>
								<th>ID</th>
								<th>Location</th>
								<th>Unit</th>
								<th>Status</th>
								<th>Scheduled</th>
							</thead>
							<tbody></tbody>
						</table>
						<script>
						$(document).ready(function(){
							var Table_Tickets = $('#Table_Tickets').DataTable( {
								"ajax": {
									"url":"bin/php/get/Active_Tickets_by_Mechanic.php?ID=<?php echo $_SESSION['User'];?>",
									"dataSrc":function(json){
										if(!json.data){json.data = [];}
										return json.data;}
								},
								"columns": [
									{
										"data": "ID",
										"className":"hidden"
									},{
										"data" : "Location",
									},{
										"data" : "Unit_State"
									},{
										"data" : "Status"
									},{
										"data" : "Scheduled"
									}
								],
								"order": [[1, 'asc']],
								"lengthMenu":[[10,25,50,100,500,-1],[10,25,50,100,500,"All"]],
								"language":{"loadingRecords":""},
								paging:false,
								searching:false,
								select:true,
								"initComplete":function(){finishLoadingPage();}
							} );
							<?php if(isMobile()){
							?>$('#Table_Tickets tbody').on( 'click', 'tr', function () {
								window.location = 'ticket.php?ID=' + $(this).closest('tr').find('td:first-child').html();
							} );<?php }?>
						});
						</script>
					</div>
				</div>
			</div>
		</div><?php }?>
    </div>

    <?php require(PROJECT_ROOT.'js/datatables.php');?>

</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=dashboard.php';</script></head></html><?php }?>
