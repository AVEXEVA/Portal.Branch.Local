<?php 
session_start( [ 'read_and_close' => true ] );
require('bin/php/index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = $database->query(null,"
		SELECT * 
		FROM   Connection 
		WHERE  Connection.Connector = ? 
		       AND Connection.Hash  = ?
	;",array($_SESSION['User'],$_SESSION['Hash']));
    $My_Connection = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    $r = $database->query(null,"
		SELECT *,
		       Emp.fFirst AS First_Name,
			   Emp.Last   AS Last_Name
		FROM   Emp 
		WHERE  Emp.ID = ?
	;",array($_SESSION['User']));
    $My_User = sqlsrv_fetch_array($r);
	$r = $database->query(null,"
		SELECT * 
		FROM   Privilege 
		WHERE  Privilege.User_ID = ?
	;",array($_SESSION['User']));
	$My_Privileges = array();
	if($r){while($My_Privilege = sqlsrv_fetch_array($r)){$My_Privileges[$My_Privilege['Access_Table']] = $My_Privilege;}}
    if(	!isset($My_Connection['ID']) 
	   	|| !isset($My_Privileges['Location'])
	  		|| $My_Privileges['Location']['User_Privilege']  < 4
	  		|| $My_Privileges['Location']['Group_Privilege'] < 4){
				?><?php require('../404.html');?><?php }
    else {
		$database->query(null,"
			INSERT INTO Portal.dbo.Activity([User], [Date], [Page]) 
			VALUES(?,?,?)
		;",array($_SESSION['User'],date("Y-m-d H:i:s"), "outstanding_jobs.php"));
?><!DOCTYPE html>
<html lang="en">
<head>
    <?php require( bin_meta . 'index.php');?>    
    <title>Nouveau Texas | Portal</title>    
    <?php require( bin_css . 'index.php');?>
    <?php require( bin_js . 'index.php');?>
</head>
<body onload='finishLoadingPage();'>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require( bin_php . 'element/navigation/index.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id="page-wrapper" class='content'>
			<div class="panel panel-primary">
				<div class="panel-heading"><h3>Outstanding Jobs</div>
				<div class="panel-body">
					<div id='Form_Outstanding_Job'>
						<div class="panel panel-primary">
							<div class="panel-heading" style='position:fixed;width:750px;z-index:999;'><h2 style='display:block;'>Location Form</h2></div>
							<div class="panel-body white-background BankGothic shadow" style='padding-top:100px;'>
								<div style='display:block !important;'>
									<fieldset >
										<legend>Job</legend>
										<editor-field name='ID'></editor-field>
										<editor-field name='Name'></editor-field>
										<editor-field name='Type'></editor-field>
										<editor-field name='Status'></editor-field>
										<editor-field name='Date'></editor-field>
										<editor-field name='Customer'></editor-field>
										<editor-field name='Location'></editor-field>
										<editor-field name='Description'></editor-field>
									</fieldset>
									<fieldset>
										<legend>Contact</legend>
										<editor-field name='Contact'></editor-field>
										<editor-field name='Phone'></editor-field>
										<editor-field name='Email'></editor-field>
									</fieldset>
								</div>
							</div>
						</div>
					</div>
					<table id='Table_Outstanding_Jobs' class='display' cellspacing='0' width='100%'>
						<thead>
							<th title="Job's ID"></th>
							<th title="Job's Name"></th>
							<th title="Job's Type"></th>
							<th title="Job's Customer"></th>
							<th title="Job's Location"></th>
							<th title="Job's Outstanding"></th>
							<th></th>
							<th></th>
							<th></th>
							<th></th>
						</thead>
					</table>
				</div>
			</div>
        </div>
    </div>
    <!-- Bootstrap Core JavaScript -->
    

    <!-- Metis Menu Plugin JavaScript -->
    

    <?php require(PROJECT_ROOT.'js/datatables.php');?>
    
    <!-- Custom Theme JavaScript -->
    

    <!--Moment JS Date Formatter-->
    

    <!-- JQUERY UI Javascript -->
    

    <!-- Custom Date Filters-->
    
    <script>
		var Editor_Outstanding_Jobs = new $.fn.dataTable.Editor({
			ajax: "php/post/Outstanding_Job.php?ID=<?php echo $_GET['ID'];?>",
			table: "#Table_Outstanding_Jobs",
			template: '#Form_Outstanding_Job',
			formOptions: {
				inline: {
					submit: "allIfChanged"
				}
			},
			idSrc: "ID",
			fields : [{
				"label":"ID",
				"name":"ID"
			},{
				"label":"Name",
				name:"Name"
			},{
				"label":"Type",
				name:"Type",
				type:"select",
				options: [<?php
					$r = $database->query(null,"
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
				"label":"Date",
				name:"Date"
			},{
				"label":"Location",
				name:"Location"
			},{
				"label":"Customer",
				name:"Customer"
			},{
				"label":"Description",
				name:"Description"
			},{
				"label":"Contact",
				name:"Contact"
			},{
				"label":"Phone Number",
				name:"Contact_Phone_Number"
			},{
				"label":"Email",
				name:"Contact_Email"
			},{
				label: "Status",
				name: "Status",
				type: "select",
				options: [<?php
					$r = $database->query(null,"
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
		Editor_Outstanding_Jobs.field('ID').disable();
		Editor_Outstanding_Jobs.field('ID').hide();
		$('#Table_Outstanding_Jobs').on( 'click', 'tbody td:not(:first-child)', function (e) {
			Editor_Outstanding_Jobs.inline( this );
		} );
        var Table_Outstanding_Jobs = $('#Table_Outstanding_Jobs').DataTable( {
			"ajax": "bin/php/get/Outstanding_Jobs.php",
			"columns": [
				{ 
					"data": "ID"
				},{ 
					"data": "Name"
				},{ 
					"data": "Type"
				},{
					"data":"Date",
					render: function(data){return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}
				},{ 
					"data": "Location"
				},{ 
					"data": "Customer"
				},{ 
					"data": "Notes"
				},{ 
					"data": "Contact"
				},{ 
					"data": "Contact_Phone_Number"
				},{ 
					"data": "Contact_Email"
				}, {
					"data": "Status",
					"visible":false
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
				{ extend: "create", editor: Editor_Outstanding_Jobs },
				{ extend: "edit",   editor: Editor_Outstanding_Jobs },
				{ 
					extend: "remove", 
					editor: Editor_Outstanding_Jobs, 
					formButtons: [
						'Delete',
						{ text: 'Cancel', action: function () { this.close(); } }
					]
				},
				{ text:"View",
				  action:function(e,dt,node,config){
					  document.location.href = 'job.php?ID=' + $("#Table_Outstanding_Jobs tbody tr.selected td:first-child").html();
				  }
				}
			],
			<?php require('bin/js/datatableOptions.php');?>
		} );
		yadcf.init(Table_Outstanding_Jobs,[
		{   column_number:0,
			filter_type:"auto_complete",
			filter_default_label:"ID"},
		{   column_number:1,
			filter_type:"auto_complete",
			filter_default_label:"Name"},
		{   column_number:2,
			filter_default_label:"Type"},
		{   column_number:3,
			filter_type: "range_date"},
		{   column_number:4,
			filter_default_label:"Location",
			filter_type:"auto_complete"},
		{   column_number:5,
			filter_default_label:"Customer",
			filter_type:"auto_complete"},
		{   column_number:6,
			filter_default_label:"Description",
			filter_type:"auto_complete"},
		{   column_number:7,
			filter_default_label:"Contact",
			filter_type:"auto_complete"},
		{   column_number:8,
			filter_default_label:"Phone",
			filter_type:"auto_complete"},
		{   column_number:9,
			filter_default_label:"Email",
			filter_type:"auto_complete"}
	]);
	$(document).ready(function(){
		$("#yadcf-filter--Table_Outstanding_Jobs-0").attr("size","6");
		$("#yadcf-filter--Table_Outstanding_Jobs-1").attr("size","10");
	});
	$("#yadcf-filter--Table_Jobs-4").attr("size","6");
    </script>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=locations.php';</script></head></html><?php }?>