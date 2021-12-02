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
	if($r){while($My_Privilege = sqlsrv_fetch_array($r)){$My_Privileges[$My_Privilege['Access']] = $My_Privilege;}}
    if(	!isset($My_Connection['ID']) 
	   	|| !isset($My_Privileges['Job'])
	  		|| $My_Privileges['Job']['Owner']  < 4
	  		|| $My_Privileges['Job']['Group'] < 4){
				?><?php require('../404.html');?><?php }
    else {
		$database->query(null,"
			INSERT INTO Portal.dbo.Activity([User], [Date], [Page]) 
			VALUES(?,?,?)
		;",array($_SESSION['User'],date("Y-m-d H:i:s"), "job_tickets.php"));
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
        <?php //require( bin_php . 'element/navigation.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id="page-wrapper" class='content'>
			
				<div class="panel-body">
					<table id='Table_Billed_Jobs' class='display' cellspacing='0' width='100%'>
						<thead>
							<th>ID</th>
							<th>Name</th>
							<th>Type</th>
							<th>Status</th>
							<th>Customer</th>
							<th>Location</th>
							<th>Contract</th>
							<th>Billed</th>
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
		var Editor_Billed_Jobs = new $.fn.dataTable.Editor({
			ajax: "php/post/Job_Status.php?ID=<?php echo $_GET['ID'];?>",
			table: "#Table_Billed_Jobs",
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
		Editor_Billed_Jobs.field('ID').disable();
		$('#Table_Billed_Jobs').on( 'click', 'tbody td:not(:first-child)', function (e) {
			Editor_Billed_Jobs.inline( this );
		} );
        var Table_Billed_Jobs = $('#Table_Billed_Jobs').DataTable( {
			"ajax": "bin/php/reports/Billed_Jobs.php",
			"columns": [
				{ 
					"data": "ID"
				},{
					"data": "Name"
				},{
					"data": "Type"
				},{
					"data": "Status"
				},{ 
					"data": "Customer"
				},{ 
					"data": "Location"
				},{ 
					"data": "Contract_Amount"
				},{ 
					"data": "Billed_Amount"
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
				//{ extend: "edit", editor: Editor_Billed_Jobs },
				{ text:"View",
				  action:function(e,dt,node,config){
					  document.location.href = 'job.php?ID=' + $("#Table_Billed_Jobs tbody tr.selected td:first-child").html();
				  }
				}
			],
			<?php require('bin/js/datatableOptions.php');?>
		} );
    </script>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=locations.php';</script></head></html><?php }?>