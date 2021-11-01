 <?php 
session_start();
require('../../../php/index.php');

if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Elevator'){
    	sqlsrv_query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "customer.php"));
        $r= sqlsrv_query($NEI,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID= ?",array($_SESSION['User']));
        $My_User = sqlsrv_fetch_array($r);
        $Field = ($My_User['Field'] == 1 && $My_User['Title'] != 'OFFICE') ? True : False;
        $r = sqlsrv_query($Portal,"
            SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
            FROM   Portal.dbo.Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
        $My_Privileges = array();
        while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
        $Privileged = FALSE;
        if(isset($My_Privileges['Customer']) && $My_Privileges['Customer']['User_Privilege'] >= 4 && $My_Privileges['Customer']['Group_Privilege'] >= 4 && $My_Privileges['Customer']['Other_Privilege'] >= 4){
        	sqlsrv_query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "customer.php"));
        	$Privileged = TRUE;}
        elseif($My_Privileges['Customer']['User_Privilege'] >= 4 && $My_Privileges['Ticket']['Group_Privilege'] >= 4 ){
        	sqlsrv_query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "customer.php"));
            $r = sqlsrv_query(  $NEI,"
                SELECT TicketO.ID AS ID 
                FROM nei.dbo.TicketO LEFT JOIN nei.dbo.Loc ON TicketO.LID = Loc.Loc
                WHERE Loc.Owner = ?;",array($_GET['ID']));
            $r2 = sqlsrv_query(  $NEI,"
                SELECT TicketD.ID AS ID 
                FROM nei.dbo.TicketD LEFT JOIN nei.dbo.Loc ON TicketD.Loc = Loc.Loc
                WHERE Loc.Owner = ?;",array($_GET['ID']));
            $Privileged = (is_array(sqlsrv_fetch_array($r)) || is_array(sqlsrv_fetch_array($r2))) ? TRUE : FALSE;}
    } elseif($_SESSION['Branch'] == 'Customer' && $_SESSION['Branch_ID'] == $_GET['ID']){$Privileged = TRUE;}
    //
    if(!isset($array['ID'])  || !is_numeric($_GET['ID']) || !$Privileged || !is_numeric($_GET['ID'])){?><html><head><script>document.location.href="../login.php?Forward=customer<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {
        $r = sqlsrv_query($NEI,
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
        $job_result = sqlsrv_query($NEI,"
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
<div class='tab-pane fade in' id='accounting-collections-pills'>
	<div class="panel panel-primary" style='margin-bottom:0px;'>
		<div class="panel-body">
			<div class="row">
				<div class='col-md-6'>
					<div class='row'>
						<div class="col-lg-6 col-md-6">
							<div class="panel panel-primary">
								<div class="panel-heading">
									<div class="row">
										<div class="col-xs-3">
											<i class="fa fa-dollar fa-3x"></i>
										</div>
										<div class="col-xs-9 text-right">
											<div class="col-xs-9 text-right">
											<div class="medium"><?php 
												$r = sqlsrv_query($NEI,"
													SELECT Sum(OpenAR.Balance) AS Count_of_Outstanding_Invoices 
													FROM   nei.dbo.OpenAR
														   LEFT JOIN nei.dbo.Loc ON OpenAR.Loc = Loc.Loc
													WHERE  Loc.Owner = ?
												;",array($_GET['ID']));
												echo $r ? substr(money_format('%.2n',sqlsrv_fetch_array($r)['Count_of_Outstanding_Invoices']),0) : 0;?>
											</div>
											<div>Needs Collection</div></div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class='col-md-12'>
							<div class="panel panel-primary">
								<div class="panel-heading"><h3><?php $Icons->Territory();?>Collectable Invoices</h3></div>
								<div class='panel-body white-background BankGothic shadow'>
									<table id='Table_Collectable_Invoices' class='display' cellspacing='0' width='100%'>
										<thead><tr>
											<th></th>
											<th>Invoice #</th>
											<th>Date</th>
											<th>Due</th>
											<th>Original</th>
											<th>Balance</th>
										</tr></thead>
										<tfoot>
										  <tr>
											  <th></th>
											  <th>Page Sum</th>
											  <th></th>
											  <th></th>
											  <th></th>
											  <th></th>
										  </tr>
									  </tfoot>
									</table>		
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class='col-md-6'>
					<div class='row'>
						<div class='col-md-12'>
							<div class="panel panel-primary">
								<div class="panel-heading"><h3><i class="fa fa-bell fa-fw"></i> Open AR by Period Intervals</h3></div>
								<div class="panel-body BankGothic white-background shadow">
									<div id="flot-placeholder-open-ar-by-customer" style="width:100%;height:500px;display:inline-block;"></div>
									<?php require('../../../js/bar/open_ar_by_customer.php');?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script>
var Table_Collectable_Invoices = $('#Table_Collectable_Invoices').DataTable( {
	"ajax": {
		"url":"cgi-bin/php/get/Collections_by_Customer.php?ID=<?php echo $_GET['ID'];?>",
		"dataSrc":function(json){if(!json.data){json.data = [];}return json.data;}
	},
	"scrollX": true,
	"columns": [
		{
			"className":      'details-control',
			"orderable":      false,
			"data":           null,
			"defaultContent": ''
		},
		{ "data": "Invoice" },
		{ "data": "Dated",
			render: function(data){return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}},
		{ "data": "Due",
			render: function(data){return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}},
		{ "data": "Original", className:"sum"},
		{ "data": "Balance", className:"sum"}
	],
	"order": [[1, 'asc']],
	"language":{
		"loadingRecords":""
	},
	//"paging":false,
	/*"dom":"Bfrtip",
	"buttons":['copy','csv','excel','pdf','print'/*,"pageLength"*///],*/
	"footerCallback": function(row, data, start, end, display) {
		var api = this.api();

		api.columns('.sum', { page: 'current' }).every(function () {
			var sum = api
				.cells( null, this.index(), { page: 'current'} )
				.render('display')
				.reduce(function (a, b) {
					var x = parseFloat(a) || 0;
					var y = parseFloat(b) || 0;
					return x + y;
				}, 0);
			$(this.footer()).html(sum);
		});
	},
	"initComplete":function(){}
} );
$('#Table_Collectable_Invoices tbody').on('click', 'td.details-control', function () {
	var tr = $(this).closest('tr');
	var row = Table_Collectable_Invoices.row( tr );

	if ( row.child.isShown() ) {
		row.child.hide();
		tr.removeClass('shown');
	}
	else {
		row.child( formatCollection(row.data()) ).show();
		tr.addClass('shown');
	}
} );
<?php if(!$Mobile){?>
	yadcf.init(Table_Collectable_Invoices,[
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
$("Table#Table_Collectable_Invoices").on("draw.dt",function(){hrefProposals();});
$("Table#Table_Collectable_Invoices").on("draw.dt",function(){
	if(!expandCollectionButton){$("Table#Table_Collectable_Invoices tbody tr:not(.shown) td:first-child").each(function(){$(this).click();});} 
	else {$("Table#Table_Collectable_Invoices tbody tr.shown td:first-child").each(function(){$(this).click();});}
});
$(document).ready(function(){
	$("#loading-sub-pills").removeClass("active");
	$("#accounting-collections-pills").addClass('active');
});
</script>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=customer<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>