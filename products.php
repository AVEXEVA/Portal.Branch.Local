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
	  		|| $My_Privileges['Admin']['User_Privilege']  < 4
	  		|| $My_Privileges['Admin']['Group_Privilege'] < 4){
				?><?php require('../404.html');?><?php }
    else {
		sqlsrv_query($NEI,"
			INSERT INTO Portal.dbo.Activity([User], [Date], [Page]) 
			VALUES(?,?,?)
		;",array($_SESSION['User'],date("Y-m-d H:i:s"), "units.php"));
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Peter D. Speranza">    <title>Nouveau Texas | Portal</title>    
    <?php require(PROJECT_ROOT."css/index.php");?>
    <?php require(PROJECT_ROOT.'js/index.php');?>
</head>
<body onload='finishLoadingPage();'>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require(PROJECT_ROOT.'php/element/navigation/index2.php');?>
        <?php require(PROJECT_ROOT.'php/element/loading.php');?>
        <div id="page-wrapper" class='content'>
			<div class="panel panel-primary">
				<div class="panel-heading"><h3><?php $Icons->Unit();?> Products</h3></div>
				<div class="panel-body">
					<table id='Table_Products' class='display' cellspacing='0' width='100%'>
						<thead>
							<th></th>
							<th></th>
							<th></th>
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
    </div>
    <!-- Bootstrap Core JavaScript -->
    <script src="https://www.nouveauelevator.com/vendor/bootstrap/js/bootstrap.min.js"></script>

    <!-- Metis Menu Plugin JavaScript -->
    <script src="https://www.nouveauelevator.com/vendor/metisMenu/metisMenu.js"></script>    

    <?php require(PROJECT_ROOT.'js/datatables.php');?>
    <script src="cgi-bin/js/jquery.dataTables.yadcf.js"></script>
    <!-- Custom Theme JavaScript -->
    <script src="../dist/js/sb-admin-2.js"></script>

    <!--Moment JS Date Formatter-->
    <script src="../dist/js/moment.js"></script>

    <!-- JQUERY UI Javascript -->
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

    <!-- Custom Date Filters-->
    <script src="../dist/js/filters.js"></script>
    <script>
	var Editor_Products = new $.fn.dataTable.Editor({
		ajax: "php/post/Products.php?ID=<?php echo $_GET['ID'];?>",
		table: "#Table_Products",
		idSrc: "ID",
		formOptions: {
			inline: {
				submit: "allIfChanged"
			}
		},
		fields : [{
			label: "ID",
			name: "ID"
		},{
			label: "Name",
			name: "Name"
		},{
			label: "Description",
			name: "Description",
			type:"textarea"
		},{
			label: "Type",
			name: "Type",
			type: "select",
			options: [<?php
				$r = sqlsrv_query($NEI,"
					SELECT Product_Type.ID   AS Value,
						   Product_Type.Name AS Label
					FROM   Portal.dbo.Product_Type
					ORDER BY Product_Type.Name ASC
				;");
				$Types = array();
				//$Types = array("{label:'Uncategorized', value:'1'}");
				if($r){while($Type = sqlsrv_fetch_array($r)){
					$Types[] = '{' . "label: '{$Type['Label']}', value:'{$Type['Value']}'" . '}'
				;}}
				echo implode(",",$Types);
			?>]
		},{
			label:"Manufacturer",
			name:"Manufacturer"
		},{
			label:"Model",
			name:"Model"
		},{
			label:"Model Number",
			name:"Model_Number"
		},{
			label:"Notes",
			name:"Noes",
			type:"textarea"
		}]
	});
	Editor_Products.field('ID').disable();
	var Table_Products = $('#Table_Products').DataTable( {
		"ajax": "cgi-bin/php/get/Products.php",
		"columns": [
			{ 
				"data": "ID",
				"visible":false
			},{ 
				"data": "Name"
			},{ 
				"data": "Type",
				render:function(data){
					switch(data){
						<?php 
						$r = sqlsrv_query($NEI,"
							SELECT Product_Type.ID   AS ID,
								   Product_Type.Name AS Name
							FROM   Portal.dbo.Product_Type
						;");
						if($r){while($array = sqlsrv_fetch_array($r)){?>case '<?php echo $array['ID'];?>':return '<?php echo $array['Name'];?>';<?php }}?>
					}
				}
			},{ 
				"data": "Description",
				"visible":false
			},{ 
				"data": "Manufacturer"
			},{ 
				"data": "Model"
			},{ 
				"data": "Model_Number"
			},{
				"data" : "Notes",
				"visible":false
			},{
				"data" : "Verified"
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
			},{ 
				extend: "create", 
				editor: Editor_Products
			},{ 
				extend: "edit",   
				editor: Editor_Products 
			},{ 
				extend: "remove", 
				editor: Editor_Products 
			},{ 
				extend: "edit",   
				editor:Editor_Products, 
				text:"Edit Survey Sheet"
			},{ 
				text:"View",
			  	action:function(e,dt,node,config){
					var data = Table_Products.rows({selected:true}).data()[0];
				  	document.location.href = 'product.php?ID=' + data.ID;
			  	}
			},{ 
				text : "Preview",
				action:function(e,dt,node,config){
					$("tr.selected").each(function(){
						var tr = $(this);
						var row = Table_Products.row( tr );

						if ( row.child.isShown() ) {
							row.child.hide();
							tr.removeClass('shown');
						}
						else {
							row.child( format(row.data()) ).show();
							tr.addClass('shown');
						}
					});
				}
			}
		],
		<?php require('cgi-bin/js/datatableOptions.php');?>
	} );
	yadcf.init(Table_Units,[
		{   column_number:0,
			filter_type:"auto_complete",
			filter_default_label:"ID"},
		{   column_number:1,
			filter_type:"auto_complete",
			filter_default_label:"State"},
		{   column_number:2,
			filter_type:"auto_complete",
			filter_default_label:"Label"},
		{   column_number:3,
			filter_default_label:"Type"},
		{   column_number:4,
			filter_default_label:"Status",
			filter_match_mode:"exact"},
		{   column_number:5,
			filter_default_label:"Customer"},
		{   column_number:6,
			filter_default_label:"Location"}
		
	]);
	function format ( d ) {
		return "<div>"+
			"<div>"+
				"<div class='column' style='width:45%;vertical-align:top;'>"+
					"<div><b>Description</b></div>"+
					"<div><pre>"+d.Description+"</div>"+
				"</div>"+
				"<div class='column' style='width:45%;vertical-align:top;'>"+
					"<div><b>Notes</b></div>"+
					"<div><pre>"+d.Notes+"</div>"+
				"</div>"+
			"</div>"+
		'</div>'+
		"<div><a href='product.php?ID="+d.ID+"' target='_blank'>View Product</a></div>"
	}
	</script>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=units.php';</script></head></html><?php }?>