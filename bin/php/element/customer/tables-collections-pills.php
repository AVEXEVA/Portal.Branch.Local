 <?php 
session_start( [ 'read_and_close' => true ] );
require('../../../php/index.php');

if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = $database->query(null,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Elevator'){
    	$database->query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "customer.php"));
        $r= $database->query(null,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID= ?",array($_SESSION['User']));
        $My_User = sqlsrv_fetch_array($r);
        $Field = ($My_User['Field'] == 1 && $My_User['Title'] != 'OFFICE') ? True : False;
        $r = $database->query($Portal,"
            SELECT Access, Owner, Group, Other
            FROM   Portal.dbo.Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
        $My_Privileges = array();
        while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access']] = $array2;}
        $Privileged = FALSE;
        if(isset($My_Privileges['Customer']) && $My_Privileges['Customer']['Owner'] >= 4 && $My_Privileges['Customer']['Group'] >= 4 && $My_Privileges['Customer']['Other'] >= 4){
        	$database->query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "customer.php"));
        	$Privileged = TRUE;}
        elseif($My_Privileges['Customer']['Owner'] >= 4 && $My_Privileges['Ticket']['Group'] >= 4 ){
        	$database->query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "customer.php"));
            $r = $database->query(  null,"
                SELECT TicketO.ID AS ID 
                FROM nei.dbo.TicketO LEFT JOIN nei.dbo.Loc ON TicketO.LID = Loc.Loc
                WHERE Loc.Owner = ?;",array($_GET['ID']));
            $r2 = $database->query(  null,"
                SELECT TicketD.ID AS ID 
                FROM nei.dbo.TicketD LEFT JOIN nei.dbo.Loc ON TicketD.Loc = Loc.Loc
                WHERE Loc.Owner = ?;",array($_GET['ID']));
            $Privileged = (is_array(sqlsrv_fetch_array($r)) || is_array(sqlsrv_fetch_array($r2))) ? TRUE : FALSE;}
    } elseif($_SESSION['Branch'] == 'Customer' && $_SESSION['Branch_ID'] == $_GET['ID']){$Privileged = TRUE;}
    //
    if(!isset($array['ID'])  || !is_numeric($_GET['ID']) || !$Privileged || !is_numeric($_GET['ID'])){?><html><head><script>document.location.href="../login.php?Forward=customer<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {
        $r = $database->query(null,
            "SELECT TOP 1
                    OwnerWithRol.ID      AS Customer_ID,
                    OwnerWithRol.Name    AS Name,
                    OwnerWithRol.Address AS Street,
                    OwnerWithRol.City    AS City,
                    OwnerWithRol.State   AS State,
                    OwnerWithRol.Zip     AS Zip,
                    OwnerWithRol.Status  AS Status
            FROM    OwnerWithRol
            WHERE   OwnerWithRol.ID = '{$_GET['ID']}'");
        $Customer = sqlsrv_fetch_array($r);
        $job_result = $database->query(null,"
            SELECT 
                Job.ID AS ID
            FROM 
                Job 
            WHERE 
                Job.Owner = '{$_GET['ID']}'
        ;");
        if($job_result){
            $Jobs = array();
            $dates = array();
            $totals = array();
            while($array = sqlsrv_fetch_array($job_result)){$Jobs[] = "[JOBLABOR].[JOB #]='{$array['ID']}'";}
            $SQL_Jobs = implode(" OR ",$Jobs);
        }?>
<div class='tab-pane fade in' id='tables-collections-pills'>
	<div class="panel panel-primary" style='margin-bottom:0px;'>
		<div class="panel-body">
			<div class="row">
				<div class='col-md-12' >
					<div class="panel panel-primary">
						<div class="panel-heading"><h3><i class="fa fa-bell fa-fw"></i> Collections Table</h3></div>
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
								'<td><a href="invoice.php?ID='+d.Invoice+'"  target="_blank"><?php \singleton\fontawesome::getInstance( )->Collection();?>View Invoice</a></td>'+
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
							"url":"bin/php/get/Collections_by_Customer.php?ID=<?php echo $_GET['ID'];?>",
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
				</script>
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
} else {?><html><head><script>document.location.href="../login.php?Forward=customer<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>