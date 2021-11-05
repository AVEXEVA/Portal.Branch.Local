<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( [ 'read_and_close' => true ] ); 
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/cgi-bin/php/index.php' );
}
if( isset( $_SESSION[ 'User' ], $_SESSION[ 'Hash' ] ) ){
	//Connection
    $result = sqlsrv_query(
    	$NEI,
    	"	SELECT 	*
			FROM   	Connection
			WHERE  		Connection.Connector = ?
			   		AND Connection.Hash = ?;", 
		array(
			$_SESSION[ 'User' ],
			$_SESSION[ 'Hash' ]
		)
	);
    $Connection = sqlsrv_fetch_array($result);
    //User
	$result    = sqlsrv_query(
		$NEI,
		"	SELECT 	Emp.*,
			   		Emp.fFirst AS First_Name,
			   		Emp.Last   AS Last_Name
			FROM   	Emp
			WHERE  	Emp.ID = ?;", 
		array(
			$_SESSION[ 'User' ]
		)
	);
	$User = sqlsrv_fetch_array( $result );
	$result = sqlsrv_query(
		$NEI,
		"	SELECT 	Privilege.Access_Table,
			   		Privilege.User_Privilege,
			   		Privilege.Group_Privilege,
			   		Privilege.Other_Privilege
			FROM   	Privilege
			WHERE  	Privilege.User_ID = ?;",
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
	){		$r = sqlsrv_query(
				$NEI,
				"	SELECT Job.Loc AS Location_ID
					FROM   Job
					WHERE  Job.ID = ?;", 
				array(
					$_GET[ 'ID' ]
				)
			);
			$Location_ID = sqlsrv_fetch_array($r)['Location_ID'];
			$result = sqlsrv_query(
				$NEI,
				"	SELECT 	Tickets.ID
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
							AND Emp.ID 			 = ?;", 
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
				"	SELECT 	Tickets.ID
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
							AND Emp.ID      = ?;",
				array(
					$_GET['ID'], 
					$_SESSION[ 'User' ]
				)
			);
			$Privileged = is_array( sqlsrv_fetch_array( $result ) );
	}
    if(		!isset($Connection['ID'])  
    	|| 	!is_numeric($_GET['ID']) 
    	|| 	!$Privileged){
    		require('401.html');
   	} else {
    	sqlsrv_query(
    		$NEI,
    		"	INSERT INTO Activity([User], [Date], [Page])
    			VALUES(?,?,?);",
    		array(
    			$_SESSION[ 'User' ],
    			date( 'Y-m-d H:i:s' ), 
    			'job.php?ID=' . $_GET['ID']
    		)
    	);
       	$r = sqlsrv_query(
       		$NEI,
       		"	SELECT 	TOP 1
                		Job.ID                AS Job_ID,
                		Job.fDesc             AS Job_Name,
                		Job.fDate             AS Job_Start_Date,
		                Job.BHour             AS Job_Budgeted_Hours,
       			        JobType.Type          AS Job_Type,
						Job.Remarks 		  AS Job_Remarks,
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
                		Owner.Elevs    		  AS Customer_Elevators,
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
            	WHERE 	Job.ID = ?;",
           array(
           	$_GET[ 'ID' ]
           )
       );
       $Job = sqlsrv_fetch_array($r);
?>
<div class='panel panel-primary' style='margin-bottom:0px;'>
	<div class='panel-body'>
		<div class='row'>
			<div class='col-md-12' >
				<div class='panel panel-primary'>
					<div class='panel-heading'><?php \singleton\fontawesome::getInstance( )->Unit( 1 );?> Units</div>
					<div class='panel-body'>
						<table id='Table_Units' class='display' cellspacing='0' width='100%' style='font-size:12px;'>
							<thead>
								<th title='ID'>ID</th>
								<th title='City ID'>City ID</th>
								<th title='Building ID'>Building ID</th>
								<th title='Type'>Type</th>
								<th title='Status'>Status</th>
							</thead>
						</table>
					</div>
				</div>
			</div>
			<script>
			<?php
			if(count($Values) > 0){foreach($Values as $Field=>$Value){?>Editor_Units.val('<?php echo $Field;?>','<?php echo $Value;?>');<?php }}?>
			var Table_Units = $('#Table_Units').DataTable( {
				ajax : 'cgi-bin/php/get/Units_by_Job.php?ID=<?php echo $_GET['ID'];?>',
				columns : [
					{
						'data': 'ID',
						'width':'10%'
					},{
						'data': 'State'
					},{
						'data': 'Unit'
					},{
						'data': 'Type'
					},{
						'data': 'Status',
						render:function(data){
							switch(data){
								case 0:return 'Inactive';
								case 1:return 'Active';
								case 2:return 'Demolished';
								default:return 'Error';
							}
						}
					}
				],
				buttons : [ ],
				language : {
					loadingRecords : "<div style='text-align:center;'><div class='sk-cube-grid' style='display:inline-block;position:relative;';><div class='sk-cube sk-cube1' style='background-color:#cc0000'></div><div class='sk-cube sk-cube2' style='background-color:#cc0000'></div><div class='sk-cube sk-cube3' style='background-color:#cc0000'></div><div class='sk-cube sk-cube4' style='background-color:#cc0000'></div><div class='sk-cube sk-cube5' style='background-color:#cc0000'></div><div class='sk-cube sk-cube6' style='background-color:#cc0000'></div><div class='sk-cube sk-cube7' style='background-color:#cc0000'></div><div class='sk-cube sk-cube8' style='background-color:#cc0000'></div><div class='sk-cube sk-cube9' style='background-color:#cc0000'></div></div><div class='sk-cube-grid' style='display:inline-block;position:relative;top:-45px;'><div class='sk-cube sk-cube1' style='background-color:#00007f'></div><div class='sk-cube sk-cube2' style='background-color:#00007f'></div><div class='sk-cube sk-cube3' style='background-color:#00007f'></div><div class='sk-cube sk-cube4' style='background-color:#00007f'></div><div class='sk-cube sk-cube5' style='background-color:#00007f'></div><div class='sk-cube sk-cube6' style='background-color:#00007f'></div><div class='sk-cube sk-cube7' style='background-color:#00007f'></div><div class='sk-cube sk-cube8' style='background-color:#00007f'></div><div class='sk-cube sk-cube9' style='background-color:#00007f'></div></div><div class='sk-cube-grid' style='display:inline-block;position:relative;top:-84px;'><div class='sk-cube sk-cube1' style='background-color:gold'></div><div class='sk-cube sk-cube2' style='background-color:gold'></div><div class='sk-cube sk-cube3' style='background-color:gold'></div><div class='sk-cube sk-cube4' style='background-color:gold'></div><div class='sk-cube sk-cube5' style='background-color:gold'></div><div class='sk-cube sk-cube6' style='background-color:gold'></div><div class='sk-cube sk-cube7' style='background-color:gold'></div><div class='sk-cube sk-cube8' style='background-color:gold'></div><div class='sk-cube sk-cube9' style='background-color:gold'></div></div></div><div style='font-size:72px;text-align:center;' class='BankGothic'>Nouveau Elevator</div><div style='font-size:42px;text-align:center;'><i>Raising Your Life</i></div>"
				},
				paging         : false,
				dom            : 'tp',
				select         : true,
				initComplete   : function( ){ },
				scrollY        : '600px',
				scrollCollapse : true
			} );
			function hrefUnits(){hrefRow('Table_Units','unit');}
			$('Table#Table_Units').on('draw.dt',function(){hrefUnits();});
			</script>
		</div>
	</div>
</div><?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
