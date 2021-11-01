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
	   	|| !isset($My_Privileges['Job'])
	  		|| $My_Privileges['Job']['User_Privilege']  < 4
	  		|| $My_Privileges['Job']['Group_Privilege'] < 4){
				?><?php require('../404.html');?><?php }
    else {
		sqlsrv_query($NEI,"
			INSERT INTO Portal.dbo.Activity([User], [Date], [Page])
			VALUES(?,?,?)
		;",array($_SESSION['User'],date("Y-m-d H:i:s"), "jobs.php"));

if(isMobile() || true){?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
	<title>Nouveau Texas | Portal</title>
    <?php require(PROJECT_ROOT."css/index.php");?>
    <?php require(PROJECT_ROOT.'js/index.php');?>
</head>
<body onload='finishLoadingPage();' style='background-color:#1d1d1d;'>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require(PROJECT_ROOT.'php/element/navigation/index.php');?>
        <?php require(PROJECT_ROOT.'php/element/loading.php');?>
        <div id="page-wrapper" class='content'>
				<div class="panel panel-primary">
					<div class="panel-heading"><h4><?php $Icons->Job();?>  Jobs</h4></div>
					<div class="panel-body">
						<table id='Table_Jobs' class='display' cellspacing='0' width='100%' <?php if(isMobile()){?>style='font-size:12px;'<?php }?>>
							<thead>
								<th><?php if(isMobile()){?>ID<?php }?></th>
								<th><?php if(isMobile()){?>Job<?php }?></th>
								<th></th>
								<th></th>
								<th></th>
							</thead>
						</table>
					</div>
				</div>
            </div>
        </div>
    </div>
    <script src="../vendor/bootstrap/js/bootstrap.js"></script>
    <script src="../vendor/metisMenu/metisMenu.js"></script>
    <?php require('cgi-bin/js/datatables.php');?>
    <script src="cgi-bin/js/jquery.dataTables.yadcf.js"></script>
    <script src="../dist/js/sb-admin-2.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script>
		var Table_Jobs = $('#Table_Jobs').DataTable( {
			"processing":true,
			"serverSide":true,
			"ajax": "cgi-bin/php/get/Jobs.php",
			"columns": [
				{
				},{
				},{
				},{
					render: function(data){if (data == null){return null;}else {return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}}
				},{
				}
			],<?php if(!isMobile()){?>
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
				{ text:"View",
				  action:function(e,dt,node,config){
					  var data = Table_Jobs.rows({selected:true}).data()[0];
					  document.location.href = 'job.php?ID=' + data[0];
				  }
				}
			],<?php }?>
			"language":{
				"loadingRecords":"<div style='text-align:center;'><div class='sk-cube-grid' style='display:inline-block;position:relative;';><div class='sk-cube sk-cube1' style='background-color:#cc0000'></div><div class='sk-cube sk-cube2' style='background-color:#cc0000'></div><div class='sk-cube sk-cube3' style='background-color:#cc0000'></div><div class='sk-cube sk-cube4' style='background-color:#cc0000'></div><div class='sk-cube sk-cube5' style='background-color:#cc0000'></div><div class='sk-cube sk-cube6' style='background-color:#cc0000'></div><div class='sk-cube sk-cube7' style='background-color:#cc0000'></div><div class='sk-cube sk-cube8' style='background-color:#cc0000'></div><div class='sk-cube sk-cube9' style='background-color:#cc0000'></div></div><div class='sk-cube-grid' style='display:inline-block;position:relative;top:-45px;'><div class='sk-cube sk-cube1' style='background-color:#00007f'></div><div class='sk-cube sk-cube2' style='background-color:#00007f'></div><div class='sk-cube sk-cube3' style='background-color:#00007f'></div><div class='sk-cube sk-cube4' style='background-color:#00007f'></div><div class='sk-cube sk-cube5' style='background-color:#00007f'></div><div class='sk-cube sk-cube6' style='background-color:#00007f'></div><div class='sk-cube sk-cube7' style='background-color:#00007f'></div><div class='sk-cube sk-cube8' style='background-color:#00007f'></div><div class='sk-cube sk-cube9' style='background-color:#00007f'></div></div><div class='sk-cube-grid' style='display:inline-block;position:relative;top:-84px;'><div class='sk-cube sk-cube1' style='background-color:gold'></div><div class='sk-cube sk-cube2' style='background-color:gold'></div><div class='sk-cube sk-cube3' style='background-color:gold'></div><div class='sk-cube sk-cube4' style='background-color:gold'></div><div class='sk-cube sk-cube5' style='background-color:gold'></div><div class='sk-cube sk-cube6' style='background-color:gold'></div><div class='sk-cube sk-cube7' style='background-color:gold'></div><div class='sk-cube sk-cube8' style='background-color:gold'></div><div class='sk-cube sk-cube9' style='background-color:gold'></div></div></div><div style='font-size:72px;text-align:center;' class='BankGothic'>Nouveau Elevator</div><div style='font-size:42px;text-align:center;'><i>Raising Your Life</i></div>"
			},
			"paging":true,
			<?php if(!isMobile()){?>"dom":"Bfrtip",<?php }?>
			"select":true,
			"initComplete":function(){
			},
			"scrollY" : "600px",
			"scrollCollapse":true,
			"lengthChange": false,
			"order": [[ 0, "DESC" ]]
		} );
		<?php if(isMobile()){
		?>$('#Table_Jobs tbody').on( 'click', 'tr', function () {
			window.location = 'job.php?ID=' + $(this).closest('tr').find('td:first-child').html();
		} );<?php }?>
		<?php if(!isMobile()){?>
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
		stylizeYADCF();<?php }?>
		$(document).ready(function(){
			$("input[aria-controls='Table_Jobs']").on("keypress",function(e){
				if(e.keyCode == 13){
					if(Number.isInteger(Number.parseInt($(this).val()))){
						document.location.href='job.php?ID=' + $(this).val();
					}
				}
			});
		})
    </script>
</body>
</html>
<?php
}
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=jobs.php';</script></head></html><?php }?>
