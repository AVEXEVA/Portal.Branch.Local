<?php
session_start();

require('../../../php/index.php');
setlocale(LC_MONETARY, 'en_US');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
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
    if(isset($My_Privileges['Customer']) && $My_Privileges['Customer']['User_Privilege'] >= 4 && $My_Privileges['Customer']['Group_Privilege'] >= 4 && $My_Privileges['Customer']['Other_Privilege'] >= 4){$Privileged = TRUE;}
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
    sqlsrv_query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "customer.php?ID=" . $_GET['ID']));
    if(!isset($array['ID'])  || !$Privileged || !is_numeric($_GET['ID'])){?><html><head><script>document.location.href="../login.php?Forward=customer<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {
        $r = sqlsrv_query($NEI,
            "SELECT TOP 1
                    OwnerWithRol.ID      AS Customer_ID,
                    OwnerWithRol.Name    AS Customer_Name,
                    OwnerWithRol.Address AS Customer_Street,
                    OwnerWithRol.City    AS Customer_City,
                    OwnerWithRol.State   AS Customer_State,
                    OwnerWithRol.Zip     AS Customer_Zip,
                    OwnerWithRol.Status  AS Customer_Status,
                    OwnerWithRol.Website AS Customer_Website
            FROM    nei.dbo.OwnerWithRol
            WHERE   OwnerWithRol.ID = ?
        ;",array($_GET['ID']));
        $Customer = sqlsrv_fetch_array($r);?>
	<div class="panel panel-primary">
		<div class="panel-body white-background">
			<style>
				div#Worker_Feed>div {
					margin:10px;
					padding:10px;
					background-color:black;
					color:white;
					border-radius:10px;

				}
			</style>
			<div id='Worker_Feed'>
				<?php
				$r = sqlsrv_query($NEI,"
					SELECT TicketO.*,
						   TicketO.ID AS TicketID,
						   Emp.*,
						   Emp.fFirst AS First_Name,
						   Emp.Last   AS Last_Name
					FROM   nei.dbo.TicketO
						   LEFT JOIN nei.dbo.Loc ON TicketO.LID   = Loc.Loc
						   LEFT JOIN nei.dbo.Emp ON TicketO.fWork = Emp.fWork
					WHERE  Loc.Owner                = ?
				;",array($_GET['ID']));
				if($r){
					$Triggered = FALSE;
					while($Ticket = sqlsrv_fetch_array($r)){
						if( ($Ticket['TimeRoute']       != '1899-12-30 00:00:00.000'
								&& $Ticket['TimeRoute'] != '')
							&& ($Ticket['TimeSite']     == '1899-12-30 00:00:00.000'
								|| $Ticket['TimeSite']  == '')
							){
								$Triggered = TRUE;
								?><div><u><a style='color:white;' href="user.php?ID=<?php echo $Ticket['TicketID'];?>"<?php echo proper($Ticket['fFirst'] . " " . $Ticket['Last']);?></u> is en route at <?php echo date('h:i A',strtotime(substr($Ticket['TimeRoute'],10,99)));?> working on Ticket #<a style='color:white;' href="ticket.php?ID=<?php echo $Ticket['TicketID'];?>"><?php echo $Ticket['TicketID'];?></a></div><?php }
						elseif($Ticket['TimeSite']    != '1899-12-30 00:00:00.000'
							   && $Ticket['TimeSite'] != ''
							   && ($Ticket['TimeComp']    == '1899-12-30 00:00:00.000'
								   || $Ticket['TimeComp'] == '')
							  ){
								$Triggered = TRUE;
								?><div><u><?php echo proper($Ticket['fFirst'] . " " . $Ticket['Last']);?></u> is on site at <?php echo date('h:i A',strtotime(substr($Ticket['TimeSite'],10,99)));?> working on Ticket #<a style='color:white;' href="ticket.php?ID=<?php echo $Ticket['TicketID'];?>"><?php echo $Ticket['TicketID'];?></a></div><?php }
						else {}
					}
					if(!$Triggered){?><h3>No Recent Worker Activity</h3><?php }
				}

				else {?><h3>No Mechanics En Route / On Site</h3><?php }
				?>
			</div>
		</div>
	</div>
</div>
	<style>
		.border-seperate {
			border-bottom:3px solid #333333;
		}
	</style>
	<script>
	var Table_Worker_Feed = $('#Table_Worker_Feed').DataTable( {
		"ajax": {
				"url": "php/get/Worker_Feed_by_Customer.php?ID=<?php echo $_GET['ID'];?>",
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
		"buttons":[
			{
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
			}
		],
		<?php require('../../../js/datatableOptions.php');?>,
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
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=customer<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
