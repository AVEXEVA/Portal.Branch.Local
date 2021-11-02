<?php 
session_start();
require('cgi-bin/php/index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"
		SELECT * 
		FROM   Connection 
		WHERE  Connection.Connector = ? 
		       AND Connection.Hash  = ?
	;",array($_SESSION['User'],$_SESSION['Hash']));
    $My_Connection = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    $r = sqlsrv_query($NEI,"
		SELECT *,
		       Emp.fFirst AS First_Name,
			   Emp.Last   AS Last_Name
		FROM   Emp 
		WHERE  Emp.ID = ?
	;",array($_SESSION['User']));
    $My_User = sqlsrv_fetch_array($r);
	$r = sqlsrv_query($NEI,"
		SELECT * 
		FROM   Privilege 
		WHERE  Privilege.User_ID = ?
	;",array($_SESSION['User']));
	$My_Privileges = array();
	if($r){while($My_Privilege = sqlsrv_fetch_array($r)){$My_Privileges[$My_Privilege['Access_Table']] = $My_Privilege;}}
    if(	!isset($My_Connection['ID']) 
	   	|| !isset($My_Privileges['Violation'])
	  		|| $My_Privileges['Violation']['User_Privilege']  < 4
	  		|| $My_Privileges['Violation']['Group_Privilege'] < 4
	  		|| $My_Privileges['Violation']['Other_Privilege'] < 4){
				?><?php require('../404.html');?><?php }
    else {
		sqlsrv_query($NEI,"
			INSERT INTO Portal.dbo.Activity([User], [Date], [Page]) 
			VALUES(?,?,?)
		;",array($_SESSION['User'],date("Y-m-d H:i:s"), "due_violations.php"));
?><!DOCTYPE html>
<html lang="en">
<head>
    <?php require(PROJECT_ROOT.'php/meta.php');?>    
    <title>Nouveau Texas | Portal</title>    
    <?php require(PROJECT_ROOT."css/index.php");?>
    <?php require(PROJECT_ROOT.'js/index.php');?>
</head>
<body onload='finishLoadingPage();'>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require(PROJECT_ROOT.'php/element/navigation/index2.php');?>
        <?php require(PROJECT_ROOT.'php/element/loading.php');?>
        <div id="page-wrapper" class='content'>
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-primary">
                        <div class="panel-heading"><h3>Due Violations</h3></div>
						<style>.toggle-vis {cursor:pointer;}</style>
                        <div class="panel-body">
							<!--<div>
								Toggle column: <a class="toggle-vis" data-column="0">ID</a> - <a class="toggle-vis" data-column="1">Name</a> - <a class="toggle-vis" data-column="2">Location</a> - <a class="toggle-vis" data-column="3">Unit</a> - <a class="toggle-vis" data-column="4">Job</a> - <a class="toggle-vis" data-column="5">Due Date</a> - <a class="toggle-vis" data-column=
								"6">Status</a> - <a class="toggle-vis" data-column="7">Division</a> - <a class="toggle-vis" data-column="8">Route</a> - <a class="toggle-vis" data-column="9">Territory</a>
							</div>
							<br />-->
                            <table id='Table_Violations' class='display' cellspacing='0' width='100%'>
                                <thead>
                                    <th title='ID of the Violation'>ID</th>
                                    <th title='Name of the Violation'>Name</th>
                                    <th title='Location of the Violation'>Location</th>
                                    <th title="Violation's Unit">Unit</th>
									<th>Job</th>
                                    <th title="Date of the Violation">Due Date</th>
                                    <th title='Status of the Violation'>Status</th>
                                    <th>Division</th>
                                    <th>Route</th>
                                    <th>Territory</th>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://www.nouveauelevator.com/vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/metisMenu/metisMenu.js"></script>
    <?php require(PROJECT_ROOT.'js/datatables.php');?>
    <script src="cgi-bin/js/jquery.dataTables.yadcf.js"></script>
    <script src="../dist/js/sb-admin-2.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
	
	<script src="https://cdn.datatables.net/buttons/1.5.1/js/dataTables.buttons.min.js"></script>
	<script src="https://cdn.datatables.net/buttons/1.5.1/js/buttons.flash.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/pdfmake.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/vfs_fonts.js"></script>
	<script src="https://cdn.datatables.net/buttons/1.5.1/js/buttons.html5.min.js"></script>
	<script src="https://cdn.datatables.net/buttons/1.5.1/js/buttons.print.min.js"></script>
    <script>
        function hrefViolations(){hrefRow("Table_Violations","violation");}
		var Table_Violations = $('#Table_Violations').DataTable( {
			"ajax": {
				"url":"cgi-bin/php/reports/Due_Violations.php",
				"dataSrc":function(json){
					if(!json.data){json.data = [];}
					return json.data;}
			},
			"columns": [
				{ "data": "ID" },
				{ "data": "Name"},
				{ "data": "Location"},
				{ "data": "Unit"},
				{ "data": "Job"},
				{ "data": "Due_Date",render: function(data){return data.substr(0,2) + "/" + data.substr(3,2) + "/20" + data.substr(6,2);},"type":"date"},
				{ "data": "Status"},
				{ "data": "Division"},
				{ "data": "Mechanic"},
				{ "data": "Territory"}
			],
			"dom":"Bfrtip",
			"buttons":['copy','csv','excel','pdf','print',"pageLength"],
			<?php require('cgi-bin/js/datatableOptions.php');?>
		} );
		$("Table#Table_Violations").on("draw.dt",function(){hrefViolations();});
		<?php if(!$Mobile){?>
		yadcf.init(Table_Violations,[
			{   column_number:0,
				filter_type:"auto_complete"},
			{   column_number:1,
				filter_type:"auto_complete"},
			{   column_number:2},
			{   column_number:3,
				filter_type:"auto_complete"},
			{   column_number:4,
				filter_type:"auto_complete"},
			{   column_number:5,
				filter_type: "range_date",
				date_format: "mm/dd/yyyy",
				filter_delay: 500},
			{   column_number:6},
			{   column_number:7},
			{   column_number:8},
			{   column_number:9}
		]);
		stylizeYADCF();<?php }?>
		$('a.toggle-vis').on( 'click', function (e) {
			e.preventDefault();

			// Get the column API object
			var column = Table_Violations.column( $(this).attr('data-column') );

			// Toggle the visibility
			column.visible( ! column.visible() );
		} );
    </script>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=violations.php';</script></head></html><?php }?>