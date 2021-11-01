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
<div class='tab-pane fade in' id='tables-jobs-pills'>
	<div class="panel panel-primary" style='margin-bottom:0px;'>
		<div class="panel-body">
			<div class="row">
				<div class='col-md-12' >
					<div class="panel panel-primary">
						<div class="panel-heading"><h3><i class="fa fa-bell fa-fw"></i> Jobs Table</h3></div>
						<div class="panel-body white-background BankGothic shadow">
							<table id='Table_Jobs' class='display' cellspacing='0' width='100%'>
								<thead>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
								</thead>
							</table>
						</div>
					</div>
				</div>
				<script>
				var Editor_Jobs = new $.fn.dataTable.Editor({
					ajax: "php/post/Job.php?ID=<?php echo $_GET['ID'];?>",
					table: "#Table_Jobs",
					formOptions: {
						inline: {
							submit: "allIfChanged"
						}
					},
					idSrc: "ID",
					fields : [{
						label: "ID",
						name: "ID"
					},{
						label: "Name",
						name: "Name"
					},{
						label: "Location",
						name: "Location",
						type: "select",
						options: [<?php
							$r = sqlsrv_query($NEI,"
								SELECT   Loc.Tag
								FROM     nei.dbo.Loc
								WHERE    Loc.Owner = ?
								ORDER BY Loc.Tag ASC
							;",array($_GET['ID']));
							$Tags = array();
							if($r){while($Tag = sqlsrv_fetch_array($r)){
								$Tag['Tag'] = str_replace("'","",$Tag['Tag']);
								$Tags[] = '{' . "label: '{$Tag['Tag']}', value:'{$Tag['Tag']}'" . '}';
							}}
							echo implode(",",$Tags);
						?>]
					},{
						label: "Type",
						name: "Type",
						type: "select",
						options: [<?php
							$r = sqlsrv_query($NEI,"
								SELECT   JobType.ID,
										 JobType.Type
								FROM     nei.dbo.JobType
								ORDER BY JobType.Type ASC
							;");
							$Types = array();
							if($r){while($Type = sqlsrv_fetch_array($r)){$Types[] = '{' . "label: '{$Type['Type']}', value:'{$Type['Type']}'" . '}';}}
							echo implode(",",$Types);
						?>]
					},{
						label: "Date",
						name: "Date",
						type:"datetime"
					},{
						label: "Status",
						name: "Status",
						type: "select",
						options: [<?php
							$r = sqlsrv_query($NEI,"
								SELECT   Job_Status.ID,
										 Job_Status.Status
								FROM     nei.dbo.Job_Status
								ORDER BY Job_Status.ID ASC
							;");
							$Statuses = array();
							if($r){while($Status = sqlsrv_fetch_array($r)){
								$Statuses[] = '{' . "label: '{$Status['Status']}', value:'{$Status['Status']}'" . '}';
							}}
							echo implode(",",$Statuses);
						?>]
					}]
				});
				Editor_Jobs.field('ID').disable();
				/*$('#Table_Jobs').on( 'click', 'tbody td:not(:first-child)', function (e) {
					Editor_Jobs.inline( this );
				} );*/
				var Table_Jobs = $('#Table_Jobs').DataTable( {
					"ajax": {
						"url":"cgi-bin/php/get/Jobs_by_Customer.php?ID=<?php echo $_GET['ID'];?>",
						"dataSrc":function(json){
							if(!json.data){json.data = [];}
							return json.data;}
					},
					"columns": [
						{ 
							"data": "ID" 
						},{ 
							"data": "Name"
						},{ 
							"data": "Location"
						},{ 
							"data": "Type"
						},{ 
							"data": "Date",
							render: function(data){return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}
						},{ 
							"data": "Status"
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
						},
						/*{ extend: "create", editor: Editor_Jobs },
						{ extend: "edit",   editor: Editor_Jobs },
						{ 
							extend: "remove", 
						 	editor: Editor_Jobs, 
						 	formButtons: [
								'Delete',
								{ text: 'Cancel', action: function () { this.close(); } }
							]
						},*/
						{ text:"View",
						  action:function(e,dt,node,config){
							  document.location.href = 'job.php?ID=' + $("#Table_Locations tbody tr.selected td:first-child").html();
						  }
						}
					],
					<?php require('../../../js/datatableOptions.php');?>
				} );
				//$("Table#Table_Jobs").on("draw.dt",function(){hrefJobs();});
				<?php if(!$Mobile){?>
					yadcf.init(Table_Jobs,[
						{   column_number:0,
							filter_type:"auto_complete",
							filter_default_label:"ID"},
						{   column_number:1,
							filter_type:"auto_complete",
							filter_default_label:"Name"},
						{   column_number:2,
							filter_default_label:"Location"},
						{   column_number:3,
							filter_default_label:"Type"},
						{   column_number:4,
							filter_type: "range_date",
							date_format: "mm/dd/yyyy",
							filter_delay: 500},
						{   column_number:5,
							filter_default_label:"Status"}
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
	$("#tables-jobs-pills").addClass('active');
});
</script>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=customer<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>