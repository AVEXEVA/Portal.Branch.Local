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
        if(isset($My_Privileges['Location']) && $My_Privileges['Location']['User_Privilege'] >= 4 && $My_Privileges['Location']['Group_Privilege'] >= 4 && $My_Privileges['Location']['Other_Privilege'] >= 4){$Privileged = TRUE;}
        elseif($My_Privileges['Location']['User_Privilege'] >= 4 && is_numeric($_GET['ID'])){
            $r = sqlsrv_query(  $NEI,"
			SELECT 	*
			FROM 	nei.dbo.TicketO
			WHERE 	TicketO.LID='{$_GET['ID']}'
					AND fWork='{$My_User['fWork']}'");
            $r2 = sqlsrv_query( $NEI,"
			SELECT 	*
			FROM 	nei.dbo.TicketD
			WHERE 	TicketD.Loc='{$_GET['ID']}'
					AND fWork='{$My_User['fWork']}'");
            $r3 = sqlsrv_query( $NEI,"
			SELECT 	*
			FROM 	nei.dbo.TicketDArchive
			WHERE 	TicketDArchive.Loc='{$_GET['ID']}'
					AND fWork='{$My_User['fWork']}'");
            $r = sqlsrv_fetch_array($r);
            $r2 = sqlsrv_fetch_array($r2);
			$r3 = sqlsrv_fetch_array($r3);
            $Privileged = (is_array($r) || is_array($r2) || is_array($r3)) ? TRUE : FALSE;
        }
    } elseif($_SESSION['Branch'] == 'Customer' && is_numeric($_GET['ID'])){
        $SQL_Result = sqlsrv_query($NEI,"
            SELECT Loc.Owner
            FROM Loc
            WHERE Loc.Loc='{$_GET['ID']}' AND Loc.Owner='{$_SESSION['Branch_ID']}'
        ;");
        if($SQL_Result){
            $sql = sqlsrv_fetch_array($SQL_Result);
            if($sql){
                $Privileged = true;
            }
        }
    }
    sqlsrv_query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "location.php"));
    if(!isset($array['ID'])  || !$Privileged || !is_numeric($_GET['ID'])){?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {
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
