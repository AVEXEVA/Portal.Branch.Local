<?php 
session_start();
require('../../../php/index.php');
require('../../../php/class/Customer.php');
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
                OwnerWithRol.ID       AS Customer_ID,
                OwnerWithRol.Name     AS Customer_Name,
                OwnerWithRol.Status   AS Customer_Status,
                OwnerWithRol.Elevs    AS Customer_Elevators,
                OwnerWithRol.Address  AS Customer_Street,
                OwnerWithRol.City     AS Customer_City,
                OwnerWithRol.State    AS Customer_State,
                OwnerWithRol.Zip      AS Customer_Zip,
                OwnerWithRol.Contact  AS Customer_Contact,
                OwnerWithRol.Remarks  AS Customer_Remarks,
                OwnerWithRol.Email    AS Customer_Email,
                OwnerWithRol.Cellular AS Customer_Cellular,
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
            FROM 
                Job 
                LEFT JOIN nei.dbo.Loc           ON Job.Loc      = Loc.Loc
                LEFT JOIN nei.dbo.Zone          ON Loc.Zone     = Zone.ID
                LEFT JOIN nei.dbo.JobType       ON Job.Type     = JobType.ID
                LEFT JOIN nei.dbo.OwnerWithRol  ON Job.Owner    = OwnerWithRol.ID
                LEFT JOIN nei.dbo.Elev          ON Job.Elev     = Elev.ID
                LEFT JOIN nei.dbo.Route         ON Loc.Route    = Route.ID
                LEFT JOIN nei.dbo.Emp           ON Emp.fWork    = Route.Mech
				LEFT JOIN nei.dbo.Violation     ON Job.ID       = Violation.Job
            WHERE
                Job.ID = ?
        ;",array($_GET['ID']));
        $Job = sqlsrv_fetch_array($r);?>
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
							"url":"cgi-bin/php/get/Collections_by_Job.php?ID=<?php echo $_GET['ID'];?>",
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
							}*/,{ 
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