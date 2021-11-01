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
        if(isset($My_Privileges['Customer']) && $My_Privileges['Customer']['Other_Privilege'] >= 4){$Privileged = TRUE;}
    }
    sqlsrv_query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "customer-service.php"));
    if(!isset($array['ID'])  || !$Privileged || !is_numeric($_GET['ID'])){?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
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
		<table id='Table_Service_Call_Feed' class='display' cellspacing='0' width='100%' style="font-size: 8px" >
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
	</div>
</div>
	<script>
		var Table_Service_Call_Feed = $('#Table_Service_Call_Feed').DataTable( {
			"ajax": {
					"url": "cgi-bin/php/reports/Service_Call_Feed_by_Location.php?ID=<?php echo $_GET['ID'];?>",
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
			"buttons":[],
			<?php require('../../../js/datatableOptions.php');?>,
			"scrollY" : "300px",
			"scrollCollapse":true,
			"searching":false

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
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>