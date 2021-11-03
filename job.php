<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( );
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/cgi-bin/php/index.php' );
}
if( isset( $_SESSION[ 'User' ], $_SESSION[ 'Hash' ] ) ){
	//Connection
    $result = sqlsrv_query(
    	$NEI,
    	'	SELECT 	*
			FROM   	Connection
			WHERE  		Connection.Connector = ?
			   		AND Connection.Hash = ?;',
		array(
			$_SESSION[ 'User' ],
			$_SESSION[ 'Hash' ]
		)
	);
    $Connection = sqlsrv_fetch_array($result);
    //User
	$result    = sqlsrv_query(
		$NEI,
		'	SELECT 	Emp.*,
			   		  Emp.fFirst AS First_Name,
			   		  Emp.Last   AS Last_Name
			FROM   	Emp
			WHERE  	Emp.ID = ?;',
		array(
			$_SESSION[ 'User' ]
		)
	);
	$User = sqlsrv_fetch_array( $result );
	$result = sqlsrv_query(
		$NEI,
		'	SELECT 	Privilege.Access_Table,
			   		  Privilege.User_Privilege,
			   		  Privilege.Group_Privilege,
			   		  Privilege.Other_Privilege
			FROM   	Privilege
			WHERE  	Privilege.User_ID = ?;',
		array(
			$_SESSION[ 'User' ]
		)
	);
	$Privileges = array();
	$Privileged = False;
	if( result ){ while( $row = sqlsrv_fetch_array($result )){ $Privileges[ $row[ 'Access_Table' ] ] = $row; } }
	if( 	isset( $Privileges[ 'Job' ] )
		  && 	$Privileges[ 'Job' ][ 'User_Privilege' ] >= 4
		  && 	$Privileges[ 'Job' ][ 'Group_Privilege' ] >= 4
	  	&& 	$Privileges[ 'Job' ][ 'Other_Privilege' ] >= 4
	  	&& 	is_numeric( $_GET[ 'ID' ] )
	  ){	$Privileged = True;
	} elseif(
			isset( $Privileges[ 'Job' ] )
		  && 	$Privileges[ 'Job' ][ 'User_Privilege' ] >= 4
		  && 	$Privileges[ 'Job' ][ 'Group_Privilege' ] >= 4
		  && 	is_numeric( $_GET[ 'ID' ] )
	){		$result = sqlsrv_query(
				$NEI,
				'	SELECT Job.Loc AS Location_ID
					FROM   Job
					WHERE  Job.ID = ?;',
				array(
					$_GET[ 'ID' ]
				)
			);
			$Location_ID = sqlsrv_fetch_array($result)['Location_ID'];
			$result = sqlsrv_query(
				$NEI,
				'	SELECT 	Tickets.ID
					FROM 	(
								(
									SELECT 	TicketO.ID,
											TicketO.fWork,
											TicketO.LID AS Location
									FROM   	TicketO
								) UNION ALL (
									SELECT 	TicketD.ID,
											TicketD.fWork,
											TicketD.Loc AS Location
									FROM   	TicketD
								)
							) AS Tickets
							LEFT JOIN Emp ON Tickets.fWork = Emp.fWork
					WHERE  		Tickets.Location = ?
							AND Emp.ID 			 = ?;',
				array(
					$Location_ID,
					$_SESSION[ 'User' ]
				)
			);
			$Privileged = is_array( sqlsrv_fetch_array( $result ) );
	} elseif(
			isset( $Privileges[ 'Job' ] )
		&& 	$Privileges[ 'Job' ][ 'User_Privilege' ] >= 4
		&& 	is_numeric( $_GET[ 'ID' ] )
	){		$result = sqlsrv_query(
				$NEI,
				'	SELECT 	Tickets.ID
					FROM  	(
								(
									SELECT 	TicketO.ID,
											TicketO.Job,
											TicketO.fWork
									FROM   	TicketO
								) UNION ALL (
									SELECT 	TicketD.ID,
											TicketD.Job,
											TicketD.fWork
									FROM   	TicketD
								)
							) AS Tickets
							LEFT JOIN Emp ON Tickets.fWork = Emp.fWork
					WHERE 		Tickets.Job = ?
							AND Emp.ID      = ?;',
				array(
					$_GET[ 'ID' ],
					$_SESSION[ 'User' ]
				)
			);
			$Privileged = is_array( sqlsrv_fetch_array( $result ) );
	}
    if(		!isset($Connection[ 'ID' ])
    	|| 	!is_numeric($_GET[ 'ID' ])
    	|| 	!$Privileged){
    		require('401.html');
   	} else {
    	sqlsrv_query(
    		$NEI,
    		'	INSERT INTO Activity([User], [Date], [Page])
    			VALUES(?,?,?);',
    		array(
    			$_SESSION[ 'User' ],
    			date( 'Y-m-d H:i:s' ),
    			'job.php?ID=' . $_GET[ 'ID' ]
    		)
    	);
       	$result = sqlsrv_query(
       		$NEI,
       		'	SELECT 	TOP 1
                		Job.ID                AS Job_ID,
                		Job.fDesc             AS Job_Name,
                		Job.fDate             AS Job_Start_Date,
		                Job.BHour             AS Job_Budgeted_Hours,
       			        JobType.Type          AS Job_Type,
						        Job.Remarks 		      AS Job_Remarks,
      		          Loc.Loc               AS Location_ID,
          		      Loc.ID                AS Location_Name,
                		Loc.Tag               AS Location_Tag,
                		Loc.Address           AS Location_Street,
                		Loc.City              AS Location_City,
                		Loc.State             AS Location_State,
                		Loc.Zip               AS Location_Zip,
                		Loc.Route             AS Route,
                		Zone.Name             AS Division,
                		Owner.ID              AS Customer_ID,
                		OwnerRol.Name     	  AS Customer_Name,
               	 		Owner.Status       	  AS Customer_Status,
                		Owner.Elevs    		    AS Customer_Elevators,
                		OwnerRol.Address      AS Customer_Street,
                		OwnerRol.City         AS Customer_City,
                		OwnerRol.State        AS Customer_State,
                		OwnerRol.Zip          AS Customer_Zip,
                		OwnerRol.Contact      AS Customer_Contact,
                		OwnerRol.Remarks      AS Customer_Remarks,
                		OwnerRol.Email        AS Customer_Email,
                		OwnerRol.Cellular     AS Customer_Cellular,
                		Elev.ID               AS Unit_ID,
                		Elev.Unit             AS Unit_Label,
                		Elev.State            AS Unit_State,
                		Elev.Cat              AS Unit_Category,
                		Elev.Type             AS Unit_Type,
                		Emp.fFirst            AS Mechanic_First_Name,
                		Emp.Last              AS Mechanic_Last_Name,
                		Route.ID              AS Route_ID,
        						Violation.ID          AS Violation_ID,
        						Violation.fdate       AS Violation_Date,
        						Violation.Status      AS Violation_Status,
        						Violation.Remarks     AS Violation_Remarks
            	FROM 	Job
                		LEFT JOIN Loc           	ON Job.Loc      = Loc.Loc
                		LEFT JOIN Zone          	ON Loc.Zone     = Zone.ID
                		LEFT JOIN JobType       	ON Job.Type     = JobType.ID
                		LEFT JOIN OwnerWithRol  	ON Job.Owner    = OwnerWithRol.ID
                		LEFT JOIN Elev          	ON Job.Elev     = Elev.ID
                		LEFT JOIN Route         	ON Loc.Route    = Route.ID
                		LEFT JOIN Emp           	ON Emp.fWork    = Route.Mech
						LEFT JOIN Violation     	ON Job.ID       = Violation.Job
						LEFT JOIN Owner 			ON Owner.ID 	= Loc.Owner
						LEFT JOIN Rol AS OwnerRol 	ON OwnerRol.ID  = Owner.Rol
            	WHERE 	Job.ID = ?;',
           array(
           	$_GET[ 'ID' ]
           )
       );
       $Job = sqlsrv_fetch_array($result);
?><!DOCTYPE html>
<html lang='en'style='min-height:100%;height:100%;webkit-background-size: cover;-moz-background-size: cover;-o-background-size: cover;background-size: cover;height:100%;'>
<head>
    <?php require(bin_meta.'index.php');?>
	<title>Nouveau Texas | Portal</title>
    <?php require(bin_css.'index.php');?>
    <?php require(bin_js.'index.php');?>
	<style>
		.panel {background-color:transparent !important;}
		.panel > div.panel-body.white-background {background-color:rgba(255,255,255,.7) !important;}
		.nav-tabs > li:not(.active) {background-color:rgba(255,255,255,.6) !important;}
	</style>
</head>
<body onload='finishLoadingPage();' style='min-height:100%;background-size:cover;background-color:#1d1d1d;height:100%;color:white;'>
    <div id='container' style='min-height:100%;height:100%;'>
    <div id='wrapper' class='<?php echo isset($_SESSION[ 'Toggle_Menu' ]) ? $_SESSION[ 'Toggle_Menu' ] : null;?>' style='height:100%;'>
        <?php require(PROJECT_ROOT.'php/element/navigation/index.php');?>
        <?php require(PROJECT_ROOT.'php/element/loading.php');?>
        <script type='text/javascript' src='http://maps.googleapis.com/maps/api/js?key=AIzaSyCNrTryEaTEDRz-XDSg890ajL_JRPnLgzc'></script>
      <div id='page-wrapper' class='content'>
		<h4 style='margin:0px;padding:10px;background-color:whitesmoke;border-bottom:1px solid darkgray;'><a href='job.php?ID=<?php echo $_GET[ 'ID' ];?>'><?php $Icons->Job();?> Job: <?php echo $Job[ 'Job_Name' ];?></a></h4>
			<div class ='Screen-Tabs shadower' style='margin: 0;border-bottom:3px solid black !important;'>
				<div class='row'>
					<div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick='someFunction(this,'job-information.php?ID=<?php echo $_GET[ 'ID' ];?>');'>
							<div class='nav-icon'><?php $Icons->Information(3);?></div>
							<div class ='nav-text'>Information</div>
					</div>
					<?php if(isset($Privileges[ 'Customer' ]) && $Privileges[ 'Customer' ][ 'User_Privilege' ] >= 4){
						?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick='document.location.href='customer.php?ID=<?php echo $Job[ 'Customer_ID' ];?>''>
								<div class='nav-icon'><?php $Icons->Customer(3);?></div>
								<div class ='nav-text'>Customer</div>
						</div><?php }?>
					<?php if(isset($Privileges[ 'Job' ]) && $Privileges[ 'Job' ][ 'User_Privilege' ] >= 4){
					?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick='someFunction(this,'job-code.php?ID=<?php echo $_GET[ 'ID' ];?>');'>
							<div class='nav-icon'><?php $Icons->Job(3);?></div>
							<div class ='nav-text'>Code</div>
					</div><?php }?>
					<?php if(isset($Privileges[ 'Collection' ]) && $Privileges[ 'Collection' ][ 'User_Privilege' ] >= 4){
					?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick='someFunction(this,'job-collections.php?ID=<?php echo $_GET[ 'ID' ];?>');'>
							<div class='nav-icon'><?php $Icons->Collection(3);?></div>
							<div class ='nav-text'>Collections</div>
					</div><?php }?>
					<?php if(isset($Privileges[ 'Contract' ]) && $Privileges[ 'Contract' ][ 'User_Privilege' ] >= 4){
					?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick='someFunction(this,'job-contracts.php?ID=<?php echo $_GET[ 'ID' ];?>');'>
							<div class='nav-icon'><?php $Icons->Contract(3);?></div>
							<div class ='nav-text'>Contracts</div>
					</div><?php }?>
					<div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick='someFunction(this,'job-feed.php?ID=<?php echo $_GET[ 'ID' ];?>');'>
							<div class='nav-icon'><?php $Icons->Activities(3);?></div>
							<div class ='nav-text'>Feed</div>
					</div>
					<?php if(isset($Privileges[ 'Time' ]) && $Privileges[ 'Time' ][ 'Group_Privilege' ] >= 4){
					?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick='someFunction(this,'job-hours.php?ID=<?php echo $_GET[ 'ID' ];?>');'>
							<div class='nav-icon'><?php $Icons->Payroll(3);?></div>
							<div class ='nav-text'>Hours</div>
					</div><?php }?>
					<?php if(isset($Privileges[ 'Invoice' ]) && $Privileges[ 'Invoice' ][ 'User_Privilege' ] >= 4){
					?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick='someFunction(this,'job-invoices.php?ID=<?php echo $_GET[ 'ID' ];?>');'>
							<div class='nav-icon'><?php $Icons->Invoice(3);?></div>
							<div class ='nav-text'>Invoices</div>
					</div><?php }?>
					<?php if(isset($Privileges[ 'Legal' ]) && $Privileges[ 'Legal' ][ 'User_Privilege' ] >= 4 && false){
					?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick='someFunction(this,'job-legal.php?ID=<?php echo $_GET[ 'ID' ];?>');'>
							<div class='nav-icon'><?php $Icons->Legal(3);?></div>
							<div class ='nav-text'>Legal</div>
					</div><?php }?>
					<?php if(isset($Privileges[ 'Location' ]) && $Privileges[ 'Location' ][ 'User_Privilege' ] >= 4){
					?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick='document.location.href ='location.php?ID=<?php echo $Job[ 'Location_ID' ];?>';'>
							<div class='nav-icon'><?php $Icons->Location(3);?></div>
							<div class ='nav-text'>Location</div>
					</div><?php }?>
					<?php if(isset($Privileges[ 'Log' ]) && $Privileges[ 'Log' ][ 'User_Privilege' ] >= 4){
					?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick='someFunction(this,'job-log.php?ID=<?php echo $_GET[ 'ID' ];?>');'>
							<div class='nav-icon'><?php $Icons->Job(3);?></div>
							<div class ='nav-text'>Log</div>
					</div><?php }?>
					<?php /*if(isset($Privileges['Job']) && $Privileges['Job']['User_Privilege'] >= 4){
					?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick='someFunction(this,'job-maintenance.php?ID=<?php echo $_GET['ID'];?>');'>
							<div class='nav-icon'><?php $Icons->Maintenance(3);?></div>
							<div class ='nav-text'>Maintenance</div>
					</div><?php }*/?>
					<?php /*if(isset($Privileges['Map']) && $Privileges['Map']['User_Privilege'] >= 4){
					?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick='someFunction(this,'job-map.php?ID=<?php echo $_GET['ID'];?>');'>
							<div class='nav-icon'><?php $Icons->Map(3);?></div>
							<div class ='nav-text'>Map</div>
					</div><?php }*/?>
					<?php /*if(isset($Privileges['Job']) && $Privileges['Job']['User_Privilege'] >= 4){
					?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick='someFunction(this,'job-modernization.php?ID=<?php echo $_GET['ID'];?>');'>
							<div class='nav-icon'><?php $Icons->Modernization(3);?></div>
							<div class ='nav-text'>Modernization</div>
					</div><?php }*/?>
					<?php if(isset($Privileges[ 'Finances' ]) && $Privileges[ 'Finances' ][ 'User_Privilege' ] >= 4){
					?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick='someFunction(this,'job-pnl.php?ID=<?php echo $_GET[ 'ID' ];?>');'>
							<div class='nav-icon'><?php $Icons->Customer(3);?></div>
							<div class ='nav-text'>P&L</div>
					</div><?php }?>
					<?php if(isset($Privileges[ 'Proposal' ]) && $Privileges[ 'Proposal' ][ 'User_Privilege' ] >= 4){
					?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick='someFunction(this,'job-proposals.php?ID=<?php echo $_GET[ 'ID' ];?>');'>
							<div class='nav-icon'><?php $Icons->Proposal(3);?></div>
							<div class ='nav-text'>Proposals</div>
					</div><?php }?>
					<?php /*if(isset($Privileges['Repair']) && $Privileges['Repair']['User_Privilege'] >= 4){
					?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick='someFunction(this,'job-repair.php?ID=<?php echo $_GET['ID'];?>');'>
							<div class='nav-icon'><?php $Icons->Repair(3);?></div>
							<div class ='nav-text'>Repair</div>
					</div><?php }*/?>
					<?php /*if(isset($Privileges['Job']) && $Privileges['Job']['User_Privilege'] >= 4){
					?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick='someFunction(this,'job-testing.php?ID=<?php echo $_GET['ID'];?>');'>
							<div class='nav-icon'><?php $Icons->Testing(3);?></div>
							<div class ='nav-text'>Testing</div>
					</div><?php }*/?>
					<?php if(isset($Privileges[ 'Ticket' ]) && $Privileges[ 'Ticket' ][ 'User_Privilege' ] >= 4){
					?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick='someFunction(this,'job-tickets.php?ID=<?php echo $_GET[ 'ID' ];?>');'>
							<div class='nav-icon'><?php $Icons->Ticket(3);?></div>
							<div class ='nav-text'>Tickets</div>
					</div><?php }?>
					<?php if(isset($Privileges[ 'Time' ]) && $Privileges[ 'Time' ][ 'Group_Privilege' ] >= 4){
					?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick='someFunction(this,'job-timeline.php?ID=<?php echo $_GET[ 'ID' ];?>');'>
							<div class='nav-icon'><?php $Icons->History(3);?></div>
							<div class ='nav-text'>Timeline</div>
					</div><?php }?>
					<?php if(isset($Privileges[ 'Unit' ]) && $Privileges[ 'Unit' ][ 'Group_Privilege' ] >= 4 && is_numeric($Job[ 'Unit_ID' ]) && $Job[ 'Unit_ID' ] > 0){
					?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick='document.location.href='unit.php?ID=<?php echo $Job['Unit_ID'];?>';'>
							<div class='nav-icon'><?php $Icons->Unit(3);?></div>
							<div class ='nav-text'>Unit</div>
					</div><?php } elseif(isset($Privileges[ 'Unit' ]) && $Privileges[ 'Unit' ][ 'Group_Privilege' ] >= 4){
					?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick='someFunction(this,'job-units.php?ID=<?php echo $_GET[ 'ID' ];?>');'>
							<div class='nav-icon'><?php $Icons->Unit(3);?></div>
							<div class ='nav-text'>Units</div>
					</div><?php }?>
					<?php
					$result = sqlsrv_query(
            $NEI,
            '   SELECT Violation.ID
                FROM   Violation
                WHERE  Violation.Job = ?',
            array($_GET[ 'ID' ]
          )
        );
					if($result){
						$Violation = sqlsrv_fetch_array($result)[ 'ID' ];
						if($Violation && $Violation > 0){
							if(isset($Privileges[ 'Violation' ])
                && $Privileges[ 'Violation' ][ 'User_Privilege' ] >= 4){
						?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick='document.location.href='violation.php?ID=<?php echo $Violation;?>';'>
								<div class='nav-icon'><?php $Icons->Violation(3);?></div>
								<div class ='nav-text'>Violation</div>
						</div><?php
							}
						}
					}?>
					<?php if(isset($Privileges[ 'User' ])
                  && $Privileges[ 'User' ][ 'User_Privilege' ] >= 4){
					?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick='someFunction(this,'job-workers.php?ID=<?php echo $_GET[ 'ID' ];?>');'>
							<div class='nav-icon'><?php $Icons->Users(3);?></div>
							<div class ='nav-text'>Workers</div>
					   </div><?php }?>
				  </div>
			  </div>
			<div class='container-content'></div>
		</div>
  <?php require('cgi-bin/js/flotcharts.php');?>
</div>
</body>
</html>
<?php
    }
} else {require('404.html');}?>
