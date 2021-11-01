<?php
session_start();
require('../../../php/index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Elevator'){
        sqlsrv_query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "unit.php"));
        $r= sqlsrv_query($NEI,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID= ?",array($_SESSION['User']));
        $My_User = sqlsrv_fetch_array($r);
        $Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
        $r = sqlsrv_query($Portal,"
            SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
            FROM   Portal.dbo.Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
        $My_Privileges = array();
        while($My_Privilege = sqlsrv_fetch_array($r)){$My_Privileges[$My_Privilege['Access_Table']] = $My_Privilege;}
        $Privileged = FALSE;
        if(isset($My_Privileges['Unit']) && $My_Privileges['Unit']['User_Privilege'] >= 4 && $My_Privileges['Unit']['Group_Privilege'] >= 4 && $My_Privileges['Unit']['Other_Privilege'] >= 4){$Privileged = TRUE;}
        elseif(isset($My_Privileges['Unit']) && $My_Privileges['Unit']['User_Privilege'] >= 4 && $My_Privileges['Unit']['Group_Privilege'] >= 4){
            $r = sqlsrv_query($NEI,"
				SELECT * 
				FROM   TicketO 
					   LEFT JOIN nei.dbo.Loc  ON TicketO.LID   = Loc.Loc
					   LEFT JOIN nei.dbo.Elev ON Loc.Loc       = Elev.Loc
					   LEFT JOIN nei.dbo.Emp  ON TicketO.fWork = Emp.fWork
				WHERE  Emp.ID      = ?
					   AND Elev.ID = ?
            ;",array($_SESSION['User'],$_GET['ID']));
            $r2 = sqlsrv_query($NEI,"
				SELECT * 
				FROM   TicketD 
					   LEFT JOIN nei.dbo.Loc  ON TicketD.Loc   = Loc.Loc
					   LEFT JOIN nei.dbo.Elev ON Loc.Loc       = Elev.Loc
					   LEFT JOIN nei.dbo.Emp  ON TicketD.fWork = Emp.fWork
				WHERE  Emp.ID      = ?
					   AND Elev.ID = ?
            ;",array($_SESSION['User'],$_GET['ID']));
            $r3 = sqlsrv_query($NEI,"
				SELECT * 
				FROM   TicketDArchive
					   LEFT JOIN nei.dbo.Loc  ON TicketDArchive.Loc   = Loc.Loc
					   LEFT JOIN nei.dbo.Elev ON Loc.Loc              = Elev.Loc
					   LEFT JOIN nei.dbo.Emp  ON TicketDArchive.fWork = Emp.fWork
				WHERE  Emp.ID      = ?
					   AND Elev.ID = ?
            ;",array($_SESSION['User'],$_GET['ID']));
            $r = sqlsrv_fetch_array($r);
            $r2 = sqlsrv_fetch_array($r2);
            $r3 = sqlsrv_fetch_array($r2);
            $Privileged = (is_array($r) || is_array($r2) || is_array($r3)) ? TRUE : FALSE;
        }
    } elseif($_SESSION['Branch'] == 'Customer' && is_numeric($_GET['ID'])){
        $SQL_Result = sqlsrv_query($NEI,"
            SELECT Elev.Owner 
            FROM   nei.dbo.Elev 
            WHERE  Elev.ID        = ? 
			       AND Elev.Owner = ?
        ;",array($_GET['ID'],$_SESSION['Branch_ID']));
        if($SQL_Result){
            $sql = sqlsrv_fetch_array($SQL_Result);
            if($sql){$Privileged = true;}
        }
    }
    if(!isset($array['ID'])  || !$Privileged || !(is_numeric($_GET['ID']) || is_numeric($_POST['ID']))){?><html><head><script>document.location.href="../login.php?Forward=unit<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {
        $r = sqlsrv_query($NEI,
            "SELECT TOP 1
                Elev.ID,
                Elev.Unit           AS Unit,
                Elev.State          AS State,
                Elev.Cat            AS Category,
                Elev.Type           AS Type,
                Elev.Building       AS Building,
                Elev.Since          AS Since,
                Elev.Last           AS Last,
                Elev.Price          AS Price,
                Elev.fDesc          AS Description,
                Loc.Loc             AS Location_ID,
                Loc.ID              AS Name,
                Loc.Tag             AS Tag,
                Loc.Tag             AS Location_Tag,
                Loc.Address         AS Street,
                Loc.City            AS City,
                Loc.State           AS Location_State,
                Loc.Zip             AS Zip,
                Loc.Route           AS Route,
                Zone.Name           AS Zone,
                OwnerWithRol.Name   AS Customer_Name,
                OwnerWithRol.ID     AS Customer_ID,
                Emp.ID AS Route_Mechanic_ID,
                Emp.fFirst AS Route_Mechanic_First_Name,
                Emp.Last AS Route_Mechanic_Last_Name
            FROM 
                Elev
                LEFT JOIN nei.dbo.Loc           ON Elev.Loc = Loc.Loc
                LEFT JOIN nei.dbo.Zone          ON Loc.Zone = Zone.ID
                LEFT JOIN nei.dbo.OwnerWithRol  ON Loc.Owner = OwnerWithRol.ID
                LEFT JOIN nei.dbo.Route ON Loc.Route = Route.ID
                LEFT JOIN nei.dbo.Emp ON Route.Mech = Emp.fWork
            WHERE
                Elev.ID = ?
		;",array($_GET['ID']));
        $Unit = sqlsrv_fetch_array($r);
        $data = $Unit;
        $r2 = sqlsrv_query($NEI,"
            SELECT *
            FROM   ElevTItem
            WHERE  ElevTItem.ElevT    = 1
                   AND ElevTItem.Elev = ?
        ;",array($_GET['ID']));
        if($r2){while($array = sqlsrv_fetch_array($r2)){$Unit[$array['fDesc']] = $array['Value'];}}
?>
<div class='tab-pane fade in' id='tables-collections-pills'>
	<div class="panel panel-primary" style='margin-bottom:0px;'>
		<div class="panel-body">
			<div class="row">
				<div class='col-md-12' >
					<div class="panel panel-primary">
						<!--<div class="panel-heading"><h3><i class="fa fa-bell fa-fw"></i> Collections Table</h3></div>-->
						<div class="panel-body white-background BankGothic shadow">
							<div id='Form_Collection'>
								<div class="panel panel-primary">
									<div class="panel-heading" style='position:fixed;width:750px;z-index:999;'><h2 style='display:block;'>Location Form</h2></div>
									<div class="panel-body white-background BankGothic shadow" style='padding-top:100px;'>
										<div style='display:block !important;'>
											<fieldset >
												<legend>Collection Information</legend>
												<editor-field name='Invoice'></editor-field>
												<editor-field name='Dated'></editor-field>
												<editor-field name='Due'></editor-field>
												<editor-field name='Original'></editor-field>
												<editor-field name='Balance'></editor-field>
											</fieldset>
										</div>
									</div>
								</div>
							</div>
							<table id='Table_Collections' class='display' cellspacing='0' width='100%'>
								<thead><tr>
									<th></th>
									<th>Invoice #</th>
									<th>Date</th>
									<th>Due</th>
									<th>Original</th>
									<th>Balance</th>
								</tr></thead>
							</table>
						</div>
					</div>
				</div>
				<script>
					function formatCollection ( d ) {
						return '<table cellpadding="5" cellspacing="0" border="0" style="padding-left:50px;">'+
							'<tr>'+
								'<td>Location:</td>'+
								'<td>'+d.Tag+'</td>'+
							'</tr>'+
							'<tr>'+
								'<td>Description:</td>'+
								'<td>'+d.Description+'</td>'+
							'</tr>'+
							'<tr>'+
								'<td><a href="invoice.php?ID='+d.Invoice+'"  target="_blank"><?php $Icons->Collection();?>View Invoice</a></td>'+
							'</tr>'+
						'</table>';
					}
					var Editor_Collections = new $.fn.dataTable.Editor({
						ajax: "php/post/Collection.php?ID=<?php echo $_GET['ID'];?>",
						table: "#Table_Collections",
						template: '#Form_Collection',
						formOptions: {
							inline: {
								submit: "allIfChanged"
							}
						},
						idSrc: "Invoice",
						fields : [{
							label: "Invoice",
							name: "Invoice"
						},{
							label:"Dated",
							name:"Dated",
							type:"datetime"
						},{
							label:"Due",
							name:"Due",
							type:"datetime"
						},{
							label:"Original",
							name:"Original"
						},{
							label:"Balance",
							name:"Balance"
						}]
					});
					Editor_Collections.field('Invoice').disable();
					//Editor_Collections.field('Invoice').hide();
					/*$('#Table_Collections').on( 'click', 'tbody td:not(:first-child)', function (e) {
						Editor_Collections.inline( this );
					} );*/
					var Table_Collections = $('#Table_Collections').DataTable( {
						"ajax": {
							"url":"cgi-bin/php/get/Collections_by_Location.php?ID=<?php echo $_GET['ID'];?>",
							"dataSrc":function(json){if(!json.data){json.data = [];}return json.data;}
						},
						"scrollX": true,
						"columns": [
							{
								"className":      'details-control',
								"orderable":      false,
								"data":           null,
								"defaultContent": ''
							},{ 
								"data": "Invoice"
							},{ 
								"data": "Dated",
								render: function(data){return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}
							},{ 
								"data": "Due",
								render: function(data){return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}
							},{ 
								"data": "Original", className:"sum"
							},{ 
								"data": "Balance", className:"sum"
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
							},/*{ 	
								extend : "create", 
								editor : Editor_Collections 
							},{ 
								extend : "edit",   
								editor : Editor_Collections 
							},{ 
								extend : "remove", 
								editor : Editor_Collections 
							},*/{ 
								text : "View",
								action:function(e,dt,node,config){
									document.location.href = 'invoice.php?ID=' + $("#Table_Collections tbody tr.selected td:nth-child(2)").html();
								}
							},{ 
								text : "Preview",
								action:function(e,dt,node,config){
									$("tr.selected").each(function(){
										var tr = $(this);
										var row = Table_Collections.row( tr );

										if ( row.child.isShown() ) {
											row.child.hide();
											tr.removeClass('shown');
										}
										else {
											row.child( formatCollection(row.data()) ).show();
											tr.addClass('shown');
										}
									});
								}
							}
						],
						<?php require('../../../js/datatableOptions.php');?>
					} );
					<?php if(!$Mobile){?>
						yadcf.init(Table_Collections,[
							{   column_number:1,
								filter_type:"auto_complete"},
							{   column_number:2,
								filter_type: "range_date",
								date_format: "mm/dd/yyyy",
								filter_delay: 500},
							{   column_number:3,
								filter_type: "range_date",
								date_format: "mm/dd/yyyy",
								filter_delay: 500},
							{   column_number:4,
								filter_type: "range_number_slider",
								filter_delay: 500},
							{   column_number:5,
								filter_type: "range_number_slider",
								filter_delay: 500}
						]);
						stylizeYADCF();
					<?php }?>
					$("Table#Table_Collections").on("draw.dt",function(){hrefProposals();});
					$("Table#Table_Collections").on("draw.dt",function(){
						if(!expandCollectionButton){$("Table#Table_Collections tbody tr:not(.shown) td:first-child").each(function(){$(this).click();});} 
						else {$("Table#Table_Collections tbody tr.shown td:first-child").each(function(){$(this).click();});}
					});
				</script>
				<?php /*<div class='col-md-12'>
					<div class="panel panel-primary">
						<div class="panel-heading"><h3><i class="fa fa-bell fa-fw"></i> </h3></div>
						<div class="panel-body white-background BankGothic shadow">
						</div>
					</div>
				</div>*/?>
			</div>
		</div>
	</div>
</div>
<script>
$(document).ready(function(){
	$("#loading-sub-pills").removeClass("active");
	$("#tables-collections-pills").addClass('active');
});
</script>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=customer<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?><!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Untitled Document</title>
</head>

<body>
</body>
</html>