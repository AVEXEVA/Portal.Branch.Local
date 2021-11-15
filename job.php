<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
if( isset( $_SESSION[ 'User' ], $_SESSION[ 'Hash' ] ) ){
	//Connection
    $result = \singleton\database::getInstance( )->query(
    	null,
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
	$result    = \singleton\database::getInstance( )->query(
    null,
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
	$result = \singleton\database::getInstance( )->query(
    null,
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
	){		$result = \singleton\database::getInstance( )->query(
    null,
				'	SELECT Job.Loc AS Location_ID
					FROM   Job
					WHERE  Job.ID = ?;',
				array(
					$_GET[ 'ID' ]
				)
			);
			$Location_ID = sqlsrv_fetch_array($result)['Location_ID'];
			$result = \singleton\database::getInstance( )->query(
      	null,
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
	){		$result = \singleton\database::getInstance( )->query(
    null,
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
    	$database->query(
    		null,
    		'	INSERT INTO Activity([User], [Date], [Page])
    			VALUES(?,?,?);',
    		array(
    			$_SESSION[ 'User' ],
    			date( 'Y-m-d H:i:s' ),
    			'job.php?ID=' . $_GET[ 'ID' ]
    		)
    	);
       	$result = \singleton\database::getInstance( )->query(
        	null,
       		'	SELECT 	TOP 1
                		Job.ID                AS ID,
                		Job.fDesc             AS Name,
                		Job.fDate             AS Start_Date,
		                Job.BHour             AS Budgeted_Hours,
       			        JobType.Type          AS Type,
						        Job.Remarks 		      AS Remarks,
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
?>?><!DOCTYPE html>
<html lang="en">
<head>
    <title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
    <?php
    	$_GET[ 'Bootstrap' ] = '5.1';
    	require( bin_meta . 'index.php');
    	require( bin_css  . 'index.php');
    	require( bin_js   . 'index.php');
    ?><style>
    	.link-page {
    		font-size : 14px;
    	}
    </style>
</head>
<body onload='finishLoadingPage();'>
    <div id="wrapper">
        <?php require( bin_php . 'element/navigation.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id="page-wrapper" class='content'>
          <div class='card-deck'>
            <div class='card card-primary border-0'>
              <div class='card-hedding'><h4><?php \singleton\fontawesome::getInstance( )->Job();?> Job: <?php echo $Job[ 'Job_Name' ];?></h4></div>
              <div class='card-body'>
                <div class ='Screen-Tabs shadower' style='margin: 0;border-bottom:3px solid black !important;'>
          				<div class='row'>
          					<div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick=''>
          							<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Information(3);?></div>
          							<div class ='nav-text'>Information</div>
          					</div>
          					<?php if(isset($Privileges[ 'Customer' ]) && $Privileges[ 'Customer' ][ 'User_Privilege' ] >= 4){
          						?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='customer.php?ID=<?php echo $Job[ 'Customer_ID' ];?>';">
          								<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Customer(3);?></div>
          								<div class ='nav-text'>Customer</div>
          						</div><?php }?>
          					<?php if(isset($Privileges[ 'Collection' ]) && $Privileges[ 'Collection' ][ 'User_Privilege' ] >= 4){
          					?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='collections.php?Job=<?php echo $Job[ 'Name' ];?>';">
          							<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Collection(3);?></div>
          							<div class ='nav-text'>Collections</div>
          					</div><?php }?>
          					<?php if(isset($Privileges[ 'Contract' ]) && $Privileges[ 'Contract' ][ 'User_Privilege' ] >= 4){
          					?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='contracts.php?Job=<?php echo $Job[ 'Name' ];?>';">
          							<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Contract(3);?></div>
          							<div class ='nav-text'>Contracts</div>
          					</div><?php }?>
          					<div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='feed.php?Job=<?php echo $Job[ 'Name' ];?>';">
          							<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Activities(3);?></div>
          							<div class ='nav-text'>Feed</div>
          					</div>
          					<?php if(isset($Privileges[ 'Time' ]) && $Privileges[ 'Time' ][ 'Group_Privilege' ] >= 4){
          					?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='hours.php?Job=<?php echo $Job[ 'Name' ];?>';">
          							<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Payroll(3);?></div>
          							<div class ='nav-text'>Hours</div>
          					</div><?php }?>
          					<?php if(isset($Privileges[ 'Invoice' ]) && $Privileges[ 'Invoice' ][ 'User_Privilege' ] >= 4){
          					?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='invoices.php?Job=<?php echo $Job[ 'Name' ];?>';">
          							<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Invoice(3);?></div>
          							<div class ='nav-text'>Invoices</div>
          					</div><?php }?>
          					<?php if(isset($Privileges[ 'Location' ]) && $Privileges[ 'Location' ][ 'User_Privilege' ] >= 4){
          					?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='location.php?Job=<?php echo $Job[ 'Name' ];?>';">
          							<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Location(3);?></div>
          							<div class ='nav-text'>Location</div>
          					</div><?php }?>
          					<?php if(isset($Privileges[ 'Log' ]) && $Privileges[ 'Log' ][ 'User_Privilege' ] >= 4){
          					?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='log.php?Job=<?php echo $Job[ 'Name' ];?>';">
          							<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Job(3);?></div>
          							<div class ='nav-text'>Log</div>
          					</div><?php }?>
          					<?php if(isset($Privileges[ 'Finances' ]) && $Privileges[ 'Finances' ][ 'User_Privilege' ] >= 4){
          					?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='finances.php?Job=<?php echo $Job[ 'Name' ];?>';">
          							<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Customer(3);?></div>
          							<div class ='nav-text'>P&L</div>
          					</div><?php }?>
          					<?php if(isset($Privileges[ 'Proposal' ]) && $Privileges[ 'Proposal' ][ 'User_Privilege' ] >= 4){
          					?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='proposal.php?Job=<?php echo $Job[ 'Name' ];?>';">
          							<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Proposal(3);?></div>
          							<div class ='nav-text'>Proposals</div>
          					</div><?php }?>
          					<?php if(isset($Privileges[ 'Ticket' ]) && $Privileges[ 'Ticket' ][ 'User_Privilege' ] >= 4){
          					?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='ticket.php?Job=<?php echo $Job[ 'Name' ];?>';">
          							<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Ticket(3);?></div>
          							<div class ='nav-text'>Tickets</div>
          					</div><?php }?>
          					<?php if(isset($Privileges[ 'Time' ]) && $Privileges[ 'Time' ][ 'Group_Privilege' ] >= 4){
          					?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='time.php?Job=<?php echo $Job[ 'Name' ];?>';">
          							<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->History(3);?></div>
          							<div class ='nav-text'>Timeline</div>
          					</div><?php }?>
          					<?php if(isset($Privileges[ 'Unit' ]) && $Privileges[ 'Unit' ][ 'Group_Privilege' ] >= 4 && is_numeric($Job[ 'Unit_ID' ]) && $Job[ 'Unit_ID' ] > 0){
          					?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='units.php?Job=<?php echo $Job[ 'Name' ];?>';">
          							<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Unit(3);?></div>
          							<div class ='nav-text'>Unit</div>
          					</div><?php } elseif(isset($Privileges[ 'Unit' ]) && $Privileges[ 'Unit' ][ 'Group_Privilege' ] >= 4){
          					?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='units.php?Job=<?php echo $Job[ 'Name' ];?>';">
          							<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Unit(3);?></div>
          							<div class ='nav-text'>Units</div>
          					</div><?php }?>
          					<?php
          					$result = \singleton\database::getInstance( )->query(
                    	null,
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
          								<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Violation(3);?></div>
          								<div class ='nav-text'>Violation</div>
          						</div><?php
          							}
          						}
          					}?>
          					<?php if(isset($Privileges[ 'User' ])
                            && $Privileges[ 'User' ][ 'User_Privilege' ] >= 4){
          					?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick='someFunction(this,'job-workers.php?ID=<?php echo $_GET[ 'ID' ];?>');'>
          							<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Users(3);?></div>
          							<div class ='nav-text'>Workers</div>
          					   </div><?php }?>
          				  </div>
          			  </div>
              </div>
            </div>
			<div class='container-content'></div>
		</div>
    <div class='card card-primary border-0'>
      <div class='card-heading'>Information</div>
      <div class='card-body'>
        <div class='row g-0'>
          <div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Job(1);?> ID</div>
          <div class='col-xs-8'><?php echo $Job['Job_ID'];?></div>
        </div>
        <div class='row g-0'>
          <div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Name</div>
          <div class='col-xs-8'><?php echo $Job['Job_Name'];?></div>
        </div>
        <div class='row g-0'>
          <div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Start Date</div>
          <div class='col-xs-8'><?php echo date("m/d/Y",strtotime($Job['Job_Start_Date']));?></div>
        </div>
        <div class='row g-0'>
          <div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Timetable</div>
          <div class='col-xs-8'><?php echo strlen($Job['Job_Budgeted_Hours']) > 0 ? $Job['Job_Budgeted_Hours'] : "Null";?> hrs</div>
        </div>
        <div class='row g-0'>
          <div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Type</div>
          <div class='col-xs-8'><?php echo $Job['Job_Type'];?></div>
        </div>
        <div class='row g-0'>
          <div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Notes</div>
          <div class='col-xs-8'><pre><?php echo $Job['Job_Remarks'];?></pre></div>
        </div>
    </div>
  </div>
  <div class='card card-primary border-0'>
    <div class='card-heading'>Location</div>
    <div class='card-body'>
      <div class='row g-0' style='border-bottom:3px ;padding-top:10px;padding-bottom:10px;'>
        <div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Location(1);?> Name:</div>
        <div class='col-xs-8'><?php echo $Job['Location_Name'];?></div>
      </div>
      <div class='row g-0'>
        <div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Address(1);?> Street:</div>
        <div class='col-xs-8'><?php echo $Job['Location_Street'];?></div>
      </div>
      <div class='row g-0'>
        <div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> City:</div>
        <div class='col-xs-8'><?php echo $Job['Location_City'];?></div>
      </div>
      <div class='row g-0'>
        <div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> State:</div>
        <div class='col-xs-8'><?php echo $Job['Location_State'];?></div>
      </div>
      <div class='row g-0'>
        <div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Zip:</div>
        <div class='col-xs-8'><?php echo $Job['Location_Zip'];?></div>
      </div>
  </div>
  <div class='card card-primary border-0'>
    <div class='card-heading'>Customer</div>
    <div class='card-body'>
      <div class='row g-0'>
        <div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Customer(1);?> Customer </div>
        <div class='col-xs-8'><?php echo $Job['Customer_Street'];?></div>
      </div>
      <div class='row g-0'>
        <div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Address</div>
        <div class='col-xs-8'><?php echo $Job['Customer_Name'];?></div>
      </div>
      <div class='row g-0'>
        <div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> City</div>
        <div class='col-xs-8'><?php echo $Job['Customer_City'];?></div>
      </div>
      <div class='row g-0'>
        <div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> State</div>
        <div class='col-xs-8'><?php echo $Job['Customer_State'];?></div>
      </div>
      <div class='row g-0'>
        <div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Zip</div>
        <div class='col-xs-8'><?php echo $Job['Customer_Zip'];?></div>
      </div>
      <div class='row g-0'>
        <div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Status</div>
        <div class='col-xs-8'><?php echo $Job['Customer_Status'] == 0 ? "Active" : "Unactive";?></div>
      </div>
      </div>
  </div>
</div>
  <?php require('bin/js/flotcharts.php');?>
</div>
</body>
</html>
<?php
    }
} else {require('404.html');}?>
