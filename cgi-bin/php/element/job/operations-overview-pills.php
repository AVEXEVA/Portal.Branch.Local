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
        if(isset($My_Privileges['Job']) && $My_Privileges['Job']['User_Privilege'] >= 4 && $My_Privileges['Job']['Group_Privilege'] >= 4 && $My_Privileges['Job']['Other_Privilege'] >= 4){$Privileged = TRUE;}
        elseif($My_Privileges['Job']['User_Privilege'] >= 4 && is_numeric($_GET['ID'])){
            $r = sqlsrv_query(  $NEI,"SELECT * FROM nei.dbo.TicketO WHERE TicketO.Job='{$_GET['ID']}' AND fWork='{$My_User['fWork']}'");
            $r2 = sqlsrv_query( $NEI,"SELECT * FROM nei.dbo.TicketD WHERE TicketD.Job='{$_GET['ID']}' AND fWork='{$My_User['fWork']}'");
            $r3 = sqlsrv_query( $NEI,"SELECT * FROM nei.dbo.TicketDArchive WHERE TicketDArchive.Job='{$_GET['ID']}' AND fWork='{$My_User['fWork']}'");
            $r = sqlsrv_fetch_array($r);
            $r2 = sqlsrv_fetch_array($r2);
			$r3 = sqlsrv_fetch_array($r3);
            $Privileged = (is_array($r) || is_array($r2) || is_array($r3)) ? TRUE : FALSE;
		}
    } elseif($_SESSION['Branch'] == 'Customer' && $_SESSION['Branch_ID'] == $_GET['ID']){$Privileged = TRUE;}
    //
    if(!isset($array['ID'])  || !is_numeric($_GET['ID']) || !$Privileged || !is_numeric($_GET['ID'])){?><html><head><script>document.location.href="../login.php?Forward=job<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
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
<div class='tab-pane fade in active' id='operations-overview-pills'>
	<div class="panel panel-primary" style='margin-bottom:0px;'>
		<div class="panel-body">
			<div class="row">
				<div class='col-md-6'>
					<div class='row' >
						<script src="cgi-bin/js/function/formatTicket.js"></script>
						<div class='col-md-12' >
							<div class="panel panel-primary">
								<div class="panel-heading" style='max-height:40px;'>Worker Feed</div>
								<div class="panel-body white-background">
									<table id='Table_Worker_Feed' class='display' cellspacing='0' width='100%'>
										<thead><tr>
											<th></th>
											<th>Created</th>
											<th>Scheduled</th>
											<th>Mechanic</th>
											<th>Unit</th>
											<th>Details</th>
											<th>Status</th>
										</tr></thead>
									</table>
									<script>
									var Table_Worker_Feed = $('#Table_Worker_Feed').DataTable( {
										"ajax": {
												"url": "cgi-bin/php/reports/Worker_Feed_by_Job.php?ID=<?php echo $_GET['ID'];?>",
												"dataSrc":function(json){if(!json.data){json.data = [];}return json.data;}
										},
										"columns": [
											{
												"className":      'details-control',
												"orderable":      false,
												"data":           null,
												"defaultContent": ''	
											},{ 
												"data" : "Created",
						 						render: function(data){return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}
											},{
												"data" : "Scheduled",
						 						render: function(data){return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}
											},{
												"data" : "Mechanic"
											},{
												"data" : "Unit"
											},{
												"data" : "Description"
											},{
												"data" : "Status"
											}
										],
										"lengthMenu":[[10,25,50,100,500,-1],[10,25,50,100,500,"All"]],
										"order": [[1, 'asc']],
										"language":{"loadingRecords":""},
										"initComplete":function(){finishLoadingPage();},
										"paging":false,
										"scrollY" : "300px",
										"scrollCollapse":true

									} );
									<?php if(!isMobile()){?>$('#Table_Worker_Feed tbody').on('click', 'td.details-control', function () {
										var tr = $(this).closest('tr');
										var row = Table_Worker_Feed.row( tr );

										if ( row.child.isShown() ) {
											row.child.hide();
											tr.removeClass('shown');
										}
										else {
											row.child( formatTicket(row.data()) ).show();
											tr.addClass('shown');
										}
									} );<?php } else {?>
									 $('#Table_Worker_Feed tbody').on('click', 'td', function () {
										var tr = $(this).closest('tr');
										var row = Table_Worker_Feed.row( tr );

										if ( row.child.isShown() ) {
											row.child.hide();
											tr.removeClass('shown');
										}
										else {
											row.child( formatTicket(row.data()) ).show();
											tr.addClass('shown');
										}
									} );
									<?php }?>
									</script>
								</div>
							</div>
						</div>
						<div class='col-md-12'>
							<div class="panel panel-primary">
								<div class="panel-heading">
									<i class="fa fa-bell fa-fw"></i> Service Call Feed
								</div>
								<div class="panel-body white-background">
									<table id='Table_Service_Call_Feed' class='display' cellspacing='0' width='100%'>
										<thead><tr>
											<th></th>
											<th>Created</th>
											<th>Scheduled</th>
											<th>Mechanic</th>
											<th>Unit</th>
											<th>Details</th>
											<th>Status</th>
										</tr></thead>
									</table>
									<script>
									var Table_Service_Call_Feed = $('#Table_Service_Call_Feed').DataTable( {
										"ajax": {
												"url": "cgi-bin/php/reports/Service_Call_Feed_by_Job.php?ID=<?php echo $_GET['ID'];?>",
												"dataSrc":function(json){if(!json.data){json.data = [];}return json.data;}
										},
										"columns": [
											{
												"className":      'details-control',
												"orderable":      false,
												"data":           null,
												"defaultContent": ''	
											},{ 
												"data" : "Created",
						 						render: function(data){return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}
											},{
												"data" : "Scheduled",
						 						render: function(data){return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}
											},{
												"data" : "Mechanic"
											},{
												"data" : "Unit"
											},{
												"data" : "Description"
											},{
												"data" : "Status"
											}
										],
										"lengthMenu":[[10,25,50,100,500,-1],[10,25,50,100,500,"All"]],
										"order": [[1, 'asc']],
										"language":{"loadingRecords":""},
										"initComplete":function(){finishLoadingPage();},
										"paging":false,
										"scrollY" : "300px",
										"scrollCollapse":true

									} );
									<?php if(!isMobile()){?>$('#Table_Service_Call_Feed tbody').on('click', 'td.details-control', function () {
										var tr = $(this).closest('tr');
										var row = Table_Service_Call_Feed.row( tr );

										if ( row.child.isShown() ) {
											row.child.hide();
											tr.removeClass('shown');
										}
										else {
											row.child( formatTicket(row.data()) ).show();
											tr.addClass('shown');
										}
									} );<?php } else {?>
									 $('#Table_Service_Call_Feed tbody').on('click', 'td', function () {
										var tr = $(this).closest('tr');
										var row = Table_Service_Call_Feed.row( tr );

										if ( row.child.isShown() ) {
											row.child.hide();
											tr.removeClass('shown');
										}
										else {
											row.child( formatTicket(row.data()) ).show();
											tr.addClass('shown');
										}
									} );
									<?php }?>
									</script>
								</div>
							</div>
						</div>
						<div class='col-md-12'>
							<div class="panel panel-primary">
								<div class="panel-heading">
									<i class="fa fa-bell fa-fw"></i> Job Hours
								</div>
								<div class="panel-body white-background shadow" style='height:500px;overflow-y:scroll;'>
									<div id='operations-overview-job-hours'><?php require('../../../php/element/loading-active.php');?></div>
									<script>
									$(document).ready(function(){
										$.ajax({
											url:"cgi-bin/php/element/job/operations-overview-job-hours.php?ID=<?php echo $_GET['ID'];?>",
											method:"GET",
											success:function(code){
												$("div#operations-overview-job-hours").html(code);
											}
										});
									});
									</script>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class='col-md-6'>
					<div class='row'>
						<div class='col-md-12'>
							<div class="panel panel-primary">
								<div class="panel-heading"><h3><i class="fa fa-bell fa-fw"></i> Job Overview</h3></div>
								<div class="panel-body BankGothic">
									<div class='row'>
										<div class="col-lg-4 col-md-2">
											<div class="panel panel-primary">
												<div class="panel-heading">
													<div class="row">
														<div class="col-xs-3">
															<i class="fa fa-suitcase fa-3x"></i>
														</div>
														<div class="col-xs-9 text-right">
															<div class="col-xs-9 text-right">
															<div class="medium"><?php 
																$Year = date("Y-01-01 00:00:00.000");
																$r = sqlsrv_query($NEI,"
																	SELECT Count(Job.ID) AS Jobs
																	FROM   nei.dbo.Job 
																	WHERE  Job.Loc = ?
																		   AND Job.Type <> 9 
																		   AND Job.Type <> 12
																;",array($_GET['ID']));
																echo $r ? sqlsrv_fetch_array($r)['Jobs'] : 0;?>
															</div>
															<div>Total Jobs</div></div>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="col-lg-4 col-md-2">
											<div class="panel panel-primary">
												<div class="panel-heading">
													<div class="row">
														<div class="col-xs-3">
															<i class="fa fa-cogs fa-3x"></i>
														</div>
														<div class="col-xs-9 text-right">
															<div class="col-xs-9 text-right">
															<div class="medium"><?php 
																$Year = date("Y-01-01 00:00:00.000");
																$r = sqlsrv_query($NEI,"
																	SELECT Count(Job.ID) AS Jobs
																	FROM   nei.dbo.Job 
																	WHERE  Job.Loc = ?
																		   AND Job.Type = 0
																;",array($_GET['ID']));
																echo $r ? sqlsrv_fetch_array($r)['Jobs'] : 0;?>
															</div>
															<div>Maintenance Jobs</div></div>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="col-lg-4 col-md-2">
											<div class="panel panel-primary">
												<div class="panel-heading">
													<div class="row">
														<div class="col-xs-3">
															<i class="fa fa-cogs fa-3x"></i>
														</div>
														<div class="col-xs-9 text-right">
															<div class="col-xs-9 text-right">
															<div class="medium"><?php 
																$Year = date("Y-01-01 00:00:00.000");
																$r = sqlsrv_query($NEI,"
																	SELECT Count(Job.ID) AS Jobs
																	FROM   nei.dbo.Job 
																	WHERE  Job.Loc = ?
																		   AND Job.Type = 2
																;",array($_GET['ID']));
																echo $r ? sqlsrv_fetch_array($r)['Jobs'] : 0;?>
															</div>
															<div>Modernization Jobs</div></div>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="col-lg-4 col-md-2">
											<div class="panel panel-primary">
												<div class="panel-heading">
													<div class="row">
														<div class="col-xs-3">
															<i class="fa fa-cogs fa-3x"></i>
														</div>
														<div class="col-xs-9 text-right">
															<div class="col-xs-9 text-right">
															<div class="medium"><?php 
																$Year = date("Y-01-01 00:00:00.000");
																$r = sqlsrv_query($NEI,"
																	SELECT Count(Job.ID) AS Jobs
																	FROM   nei.dbo.Job 
																	WHERE  Job.Loc = ?
																		   AND Job.Type = 6
																;",array($_GET['ID']));
																echo $r ? sqlsrv_fetch_array($r)['Jobs'] : 0;?>
															</div>
															<div>Repair Jobs</div></div>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="col-lg-4 col-md-2">
											<div class="panel panel-primary">
												<div class="panel-heading">
													<div class="row">
														<div class="col-xs-3">
															<i class="fa fa-warning fa-3x"></i>
														</div>
														<div class="col-xs-9 text-right">
															<div class="col-xs-9 text-right">
															<div class="medium"><?php 
																$Year = date("Y-01-01 00:00:00.000");
																$r = sqlsrv_query($NEI,"
																	SELECT Count(Job.ID) AS Jobs
																	FROM   nei.dbo.Job 
																	WHERE  Job.Loc = ?
																		   AND Job.Type = 8
																;",array($_GET['ID']));
																echo $r ? sqlsrv_fetch_array($r)['Jobs'] : 0;?>
															</div>
															<div>Testing Jobs</div></div>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="col-lg-4 col-md-2">
											<div class="panel panel-primary">
												<div class="panel-heading">
													<div class="row">
														<div class="col-xs-3">
															<i class="fa fa-warning fa-3x"></i>
														</div>
														<div class="col-xs-9 text-right">
															<div class="col-xs-9 text-right">
															<div class="medium"><?php 
																$Year = date("Y-01-01 00:00:00.000");
																$r = sqlsrv_query($NEI,"
																	SELECT Count(Job.ID) AS Jobs
																	FROM   nei.dbo.Job 
																	WHERE  Job.Loc      = ?
																		   AND Job.Type = 19
																;",array($_GET['ID']));
																echo $r ? sqlsrv_fetch_array($r)['Jobs'] : 0;?>
															</div>
															<div>Violation Jobs</div></div>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="col-lg-4 col-md-2">
											<div class="panel panel-primary">
												<div class="panel-heading">
													<div class="row">
														<div class="col-xs-3">
															<i class="fa fa-cogs fa-3x"></i>
														</div>
														<div class="col-xs-9 text-right">
															<div class="col-xs-9 text-right">
															<div class="medium"><?php 
																$Year = date("Y-01-01 00:00:00.000");
																$r = sqlsrv_query($NEI,"
																	SELECT Count(Job.ID) AS Jobs
																	FROM   nei.dbo.Job 
																	WHERE  Job.Loc = ?
																		   AND Job.Type = 4
																;",array($_GET['ID']));
																echo $r ? sqlsrv_fetch_array($r)['Jobs'] : 0;?>
															</div>
															<div>Other Jobs</div></div>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class='col-md-12' style=''>
									<div class="panel panel-primary"> 
										<div class="panel-heading"><h3>Active Jobs</h3></div>
										<div class='panel-body white-background shadow'>
											<table id='Table_Active_Jobs' class='display' cellspacing='0' width='100%'>
												<thead>
													<th>ID</th>
													<th>Name</th>
													<th>Type</th>
												</thead>
											</table>	
										</div>
									</div>
									<script>
									var Table_Active_Jobs = $('#Table_Active_Jobs').DataTable( {
										"ajax": "cgi-bin/php/reports/Active_Jobs_by_Location.php?ID=<?php echo $_GET['ID'];?>",
										"columns": [
											{ "data": "ID"},
											{ "data": "Name" },
											{ "data": "Type"}
										],
										"order": [[1, 'asc']],
										"language":{
											"loadingRecords":""
										},
										"initComplete":function(){
											$("#loading-pills").removeClass("active");
											$("#operations-pills").addClass('active');
										}
									} );
									</script>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script>
$(document).ready(function(){
	$("#loading-sub-pills").removeClass("active");
	$("#operations-overview-pills").addClass('active');
});
</script>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=customer<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>