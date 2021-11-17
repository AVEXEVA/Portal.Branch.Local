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
<div class='tab-pane fade in' id='tables-preventative-maintenance-pills'>
	<div class="panel panel-primary" style='margin-bottom:0px;'>
		<div class="panel-body">
			<div class="row">
				<div class='col-md-12' >
					<div class="panel panel-primary">
						<div class="panel-heading"><h3><?php \singleton\fontawesome::getInstance( )->Ticket();?> Preventative Maintenance</h3></div>
						<div class="panel-body white-background BankGothic shadow">
							<table id='Table_Tickets_Maintenance' class='display' cellspacing='0' width='100%'>
								<thead>
									<th></th>
									<th title='ID of the Ticket'>ID</th>
									<th title='Description of the Ticket'>First Name</th>
									<th>Last Name</th>
									<th title='Scheduled Work Time'>Date</th>
									<th title='Status of the Ticket'>Status</th>                                            
									<th title='Total Hours'>Hours</th>
								</thead>
							</table>
						</div>
					</div>
				</div>
				<script>
					var Table_Tickets_Maintenance = $('#Table_Tickets_Maintenance').DataTable( {
						"ajax": {
							"url":"bin/php/get/Tickets_by_Customer_Maintenance.php?ID=<?php echo $_GET['ID'];?>",
							"dataSrc":function(json){if(!json.data){json.data = [];}return json.data;}
						},
						"lengthMenu":[[10,25,50,100,500,-1,0],[10,25,50,100,500,"All","None"]],
						"columns": [
							{
								"className":      'details-control',
								"orderable":      false,
								"data":           null,
								"defaultContent": ''
							},
							{ "data": "ID" },
							{ "data": "Worker_First_Name"},
							{ "data": "Worker_Last_Name"},
							{ 
								"data": "Date",
								render: function(data){if(data != null){return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}else{return null;}}
							},
							{ "data": "Status"},
							{ 
								"data": "Total",
								"defaultContent":"0"
							},
							{
								"data":"Unit_State",
								"visible":false,
								"searchable":true
							},
							{
								"data":"Unit_Label",
								"visible":false,
								"searchable":true
							}
						],
						"order": [[1, 'asc']],
						"language":{
							"loadingRecords":""
						},
						"paging":false,
						"dom":"Bfrtip",
						"buttons":['copy','csv','excel','pdf','print'/*,"pageLength"*/],
						"initComplete":function(){

						}
					} );
					$('#Table_Tickets_Maintenance tbody').on('click', 'td.details-control', function () {
						var tr = $(this).closest('tr');
						var row = Table_Tickets_Maintenance.row( tr );

						if ( row.child.isShown() ) {
							row.child.hide();
							tr.removeClass('shown');
						}
						else {
							row.child( format(row.data()) ).show();
							tr.addClass('shown');
						}
					} );
					<?php if(!$Mobile){?>
					yadcf.init(Table_Tickets_Maintenance,[
						{   column_number:1,
							filter_type:"auto_complete"},
						{   column_number:2},
						{   column_number:3},
						{   column_number:4,
							filter_type: "range_date",
							date_format: "mm/dd/yyyy",
							filter_delay: 500},
						{   column_number:5},
						{   column_number:6,
							filter_type: "range_number_slider",
							filter_delay: 500}
					]);
					stylizeYADCF();<?php }?>
					$("Table#Table_Tickets_Maintenance").on("draw.dt",function(){
						if(!expandMaintenanceButton){$("Table#Table_Tickets_Maintenance tbody tr:not(.shown) td:first-child").each(function(){$(this).click();});} 
						else {$("Table#Table_Tickets_Maintenance tbody tr.shown td:first-child").each(function(){$(this).click();});}
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
	$("#tables-preventative-maintenance-pills").addClass('active');
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