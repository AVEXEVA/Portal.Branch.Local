<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( [ 'read_and_close' => true ] ); 
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/cgi-bin/php/index.php' );
}
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    //Connection
    $Connection = sqlsrv_query(
        $NEI,
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
    $User = sqlsrv_query(
        $NEI,
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
    $r = sqlsrv_query(
        $NEI,
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
        $r = sqlsrv_query(  
            $NEI,
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
        sqlsrv_query(
            $NEI,
            "   INSERT INTO Activity([User], [Date], [Page]) 
                VALUES(?,?,?);",
            array(
                $_SESSION['User'],
                date('Y-m-d H:i:s'), 
                'location-feed.php?ID=' . $_GET[ 'ID' ]
            )
        );
        $ID = $_GET['ID'];
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
        $Location = sqlsrv_fetch_array($r);
        $data = $Location;
        $job_result = sqlsrv_query($NEI,"
            SELECT Job.ID AS ID
            FROM   Job
            WHERE  Job.Loc = ?
        ;",array($_GET['ID']));
        if($job_result){
            $Jobs = array();
            $dates = array();
            $totals = array();
            while($array = sqlsrv_fetch_array($job_result)){$Jobs[] = "[JOBLABOR].[JOB #]='{$array['ID']}'";}
            $SQL_Jobs = implode(" OR ",$Jobs);
        }?>
	<div class="panel panel-primary">
		<!--<div class="panel-heading"><h4>Worker Feed</h4></div>-->
		<div class="panel-body ">
			<table id='Table_Worker_Feed' class='display' cellspacing='0' width='100%' style='font-size:12px;'>
				<thead><tr>
					<th>Status</th>
					<th>Created</th>
					<th>Scheduled</th>
					<th>Mechanic</th>
				</tr></thead>
			</table>
		</div>
	</div>
	<script>
	var Table_Worker_Feed = $('#Table_Worker_Feed').DataTable( {
		"ajax": {
				"url": "php/get/Worker_Feed_by_Location.php?ID=<?php echo $_GET['ID'];?>",
				"dataSrc":function(json){if(!json.data){json.data = [];}return json.data;}
		},
		"columns": [
			{
				"data" : "Status"
			},{
				"data" : "Created",
				render: function(data){if(!data){return null;}else{return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}}
			},{
				"data" : "Scheduled",
				render: function(data){if(!data){return null;}else{return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}}
			},{
				"data" : "Mechanic"
			}
		],
		"buttons":[
			<?php if(!isMobile()){?>{
				extend: 'collection',
				text: 'Export',
				buttons: [
					'copy',
					'excel',
					'csv',
					'pdf',
					'print'
				]
			},{
				text : "Preview",
				action:function(e,dt,node,config){
					$("tr.selected").each(function(){
						var tr = $(this);
						var row = Table_Worker_Feed.row( tr );

						if ( row.child.isShown() ) {
							row.child.hide();
							tr.removeClass('shown');
						}
						else {
							row.child( formatTicket(row.data()) ).show();
							tr.addClass('shown');
						}
					});
				}
			},{ text:"View Job",
			  action:function(e,dt,node,config){
				  var data = Table_Worker_Feed.rows({selected:true}).data()[0];
				  window.open('job.php?ID=' + data.ID, '_blank');
			  }
			},{ text:"View Unit",
			  action:function(e,dt,node,config){
				  var data = Table_Worker_Feed.rows({selected:true}).data()[0];
				  if(data.Unit_ID > 0){
					window.open('unit.php?ID=' + data.Unit_ID, '_blank');
				  }
			  }
			},{ text:"View Ticket",
			  action:function(e,dt,node,config){
				  var data = Table_Worker_Feed.rows({selected:true}).data()[0];
				  window.open('ticket.php?ID=' + data.Ticket_ID, '_blank');
			  }
			}<?php }?>
		],
		<?php require('../../../js/datatableOptions.php');?>,
		"scrollY" : "300px",
		"scrollCollapse":true,
		"searching":false,
		"paging":false

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

	<?php }?>
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
	</script>


</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
