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
	   	|| !isset($My_Privileges['Admin'])
	  		|| $My_Privileges['Admin']['User_Privilege']  != 7
	  		|| $My_Privileges['Admin']['Group_Privilege'] != 7
	  		|| $My_Privileges['Admin']['Other_Privilege'] != 7){
				?><?php require('../404.html');?><?php }
    else {
		sqlsrv_query($NEI,"
			INSERT INTO Portal.dbo.Activity([User], [Date], [Page])
			VALUES(?,?,?)
		;",array($_SESSION['User'],date("Y-m-d H:i:s"), "customers.php"));
?><!DOCTYPE html>
<html lang="en">
<head>
    <?php require(PROJECT_ROOT.'php/meta.php');?>
    <title>Nouveau Texas | Portal</title>    <?php require(PROJECT_ROOT."css/index.php");?>
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
                        <div class="panel-heading"><h3><?php $Icons->Chart();?> Overhead Cost</h3></div>
                        <div class="panel-body">
							<div id='Form_Overhead_Cost'>
								<div class="panel panel-primary">
									<div class="panel-heading" style='position:fixed;width:750px;z-index:999;'><h2 style='display:block;'>Overhead Cost</h2></div>
									<div class="panel-body white-background BankGothic shadow" style='padding-top:100px;'>
										<div style='display:block !important;'>
											<fieldset >
												<legend>Details</legend>
												<editor-field name='ID'></editor-field>
												<editor-field name='Type'></editor-field>
												<editor-field name='Start'></editor-field>
												<editor-field name='End'></editor-field>
												<editor-field name='Rate'></editor-field>
											</fieldset>
										</div>
									</div>
								</div>
							</div>
                            <table id='Table_Overhead_Cost' class='display' cellspacing='0' width='100%'>
                                <thead>
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
            </div>
        </div>
    </div>
    <script src="https://www.nouveauelevator.com/vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/metisMenu/metisMenu.js"></script>
    <?php require(PROJECT_ROOT.'js/datatables.php');?>
    <script src="../dist/js/sb-admin-2.js"></script>
    <script src="../dist/js/moment.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

    <!-- Custom Date Filters-->
    <script src="../dist/js/filters.js"></script>
    <script>
        var Editor_Overhead_Cost = new $.fn.dataTable.Editor({
			ajax: "cgi-bin/php/post/Overhead_Cost.php",
			table: "#Table_Overhead_Cost",
			template: '#Form_Overhead_Cost',
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
				label: "Type",
				name: "Type"
			},{
				label: "Start",
				name: "Start",
				type:"datetime"
			},{
				label: "End",
				name: "End",
				type:"datetime"
			},{
				label: "Rate",
				name: "Rate"
			}]
		});
		Editor_Overhead_Cost.field('ID').disable();
		var Table_Overhead_Cost = $('#Table_Overhead_Cost').DataTable( {
			"ajax": {
				"url":"cgi-bin/php/get/Overhead_Cost.php",
				"dataSrc":function(json){if(!json.data){json.data = [];}return json.data;}
			},
			"columns": [
				{
					"data": "ID"
				},{
					"data": "Type"
				},{
					"data": "Start"
				},{
					"data": "End"
				},{
					"data": "Rate"
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
				{ extend: "create", editor: Editor_Overhead_Cost },
				{ extend: "edit",   editor: Editor_Overhead_Cost },
				{
					extend: "remove",
					editor: Editor_Overhead_Cost,
					formButtons: [
						'Delete',
						{ text: 'Cancel', action: function () { this.close(); } }
					]
				}
			],
			<?php require('cgi-bin/js/datatableOptions.php');?>
		} );
    </script>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=customers.php';</script></head></html><?php }?>
