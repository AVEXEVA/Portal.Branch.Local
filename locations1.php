<?php
session_start( [ 'read_and_close' => true ] );
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
	   	|| !isset($My_Privileges['Location'])
	  		|| $My_Privileges['Location']['User_Privilege']  < 4
	  		|| $My_Privileges['Location']['Group_Privilege'] < 4){
				?><?php require('../404.html');?><?php }
    else {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php require( bin_meta . 'index.php');?>
    <title>Nouveau Texas | Portal</title>
    <?php require( bin_css . 'index.php');?>
    <?php require( bin_js . 'index.php');?>
</head>
<body onload='finishLoadingPage();'>
    <div id="wrapper" class="">
        <?php require(PROJECT_ROOT.'php/element/navigation/index.php');?>
        <?php require(PROJECT_ROOT.'php/element/loading.php');?>
        <div id="page-wrapper" class='content'>
    			<div class="panel panel-primary">
    				<div class="panel-heading"><h4><div style='float:left;' onClick="document.location.href='home.php';"><?php $Icons->Location();?> Locations</div><div style='float:right;' onClick="document.location.href='location.php'"><?php $Icons->Add(1);?></div><div style='clear:both;'></div></h4></div>
    				<div class="panel-body">
    					<table id='Table_Locations' class='display' cellspacing='0' width='100%' style='font-size:12px;'>
    						<thead>
    							<th title="Location's ID"></th>
    							<th title="Location's Name State ID"></th>
    							<th title="Location's Tag"></th>
    							<th title="Location's Street"></th>
    							<th title="Location's City"></th>
    							<th title="Location's State"></th>
    							<th title="Location's Zip"></th>
    							<th title="Location's Route"></th>
    							<th title="Location's Zone"></th>
    							<th title="Location's Mainteniance"></th>
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
        var Table_Locations = $('#Table_Locations').DataTable( {
			"processing":true,
			"serverSide":true,
			"ajax": "cgi-bin/php/get/Locations.php",
			"order": [[ 1, "asc" ]],
			"columns": [
				{
					"className":"hidden"
				},{
				},{
					<?php if(isMobile()){?>"visible":false<?php } ?>
				},{
					<?php if(isMobile()){?>"visible":false<?php } ?>
				},{
					<?php if(isMobile()){?>"visible":false<?php } ?>
				},{
					<?php if(isMobile()){?>"visible":false<?php } ?>
				},{
					<?php if(isMobile()){?>"visible":false<?php } ?>
				},{
					<?php if(isMobile()){?>"visible":false<?php } ?>
				},{
					<?php if(isMobile()){?>"visible":false<?php } ?>
				},{
					<?php if(isMobile()){?>"visible":false,<?php } ?>
				  	"render":function(data){
					  if(data == '1'){return "Maintained"/*"<img src='images/icons/maintained.svg' style='width:25px;' />"*/;}
					  else {return "N/A"/*"<img src='images/icons/not-maintained.svg' style='width:25px;' />"*/;}
				  	}
				}
			],
			<?php if(!isMobile()){?>"buttons":[
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
				/*{ extend: "create", editor: Editor_Locations },
				{ extend: "edit",   editor: Editor_Locations },
				{
					extend: "remove",
					editor: Editor_Locations,
					formButtons: [
						'Delete',
						{ text: 'Cancel', action: function () { this.close(); } }
					]
				},*/
				{ text:"View",
				  action:function(e,dt,node,config){
					  var data = Table_Locations.rows({selected:true}).data()[0];
					  document.location.href = 'location.php?ID=' + data[0];
				  }
				}
			],<?php }?>
			"language":{
				"loadingRecords":"<div style='text-align:center;'><div class='sk-cube-grid' style='display:inline-block;position:relative;';><div class='sk-cube sk-cube1' style='background-color:#cc0000'></div><div class='sk-cube sk-cube2' style='background-color:#cc0000'></div><div class='sk-cube sk-cube3' style='background-color:#cc0000'></div><div class='sk-cube sk-cube4' style='background-color:#cc0000'></div><div class='sk-cube sk-cube5' style='background-color:#cc0000'></div><div class='sk-cube sk-cube6' style='background-color:#cc0000'></div><div class='sk-cube sk-cube7' style='background-color:#cc0000'></div><div class='sk-cube sk-cube8' style='background-color:#cc0000'></div><div class='sk-cube sk-cube9' style='background-color:#cc0000'></div></div><div class='sk-cube-grid' style='display:inline-block;position:relative;top:-45px;'><div class='sk-cube sk-cube1' style='background-color:#00007f'></div><div class='sk-cube sk-cube2' style='background-color:#00007f'></div><div class='sk-cube sk-cube3' style='background-color:#00007f'></div><div class='sk-cube sk-cube4' style='background-color:#00007f'></div><div class='sk-cube sk-cube5' style='background-color:#00007f'></div><div class='sk-cube sk-cube6' style='background-color:#00007f'></div><div class='sk-cube sk-cube7' style='background-color:#00007f'></div><div class='sk-cube sk-cube8' style='background-color:#00007f'></div><div class='sk-cube sk-cube9' style='background-color:#00007f'></div></div><div class='sk-cube-grid' style='display:inline-block;position:relative;top:-84px;'><div class='sk-cube sk-cube1' style='background-color:gold'></div><div class='sk-cube sk-cube2' style='background-color:gold'></div><div class='sk-cube sk-cube3' style='background-color:gold'></div><div class='sk-cube sk-cube4' style='background-color:gold'></div><div class='sk-cube sk-cube5' style='background-color:gold'></div><div class='sk-cube sk-cube6' style='background-color:gold'></div><div class='sk-cube sk-cube7' style='background-color:gold'></div><div class='sk-cube sk-cube8' style='background-color:gold'></div><div class='sk-cube sk-cube9' style='background-color:gold'></div></div></div><div style='font-size:72px;text-align:center;' class='BankGothic'>Nouveau Texas</div><div style='font-size:42px;text-align:center;'><i>Raising Your Life</i></div>"
			},
			"paging":true,
			<?php if(!isMobile()){?>"dom":"Bfrtip",<?php }?>
			"select":true,
			"initComplete":function(){
			},
			"scrollY" : "600px",
			"scrollCollapse":true,
			"lengthChange": false,
			"search":{
				"search":"<?php echo isset($_SESSION['Forward-Backward'],$_SESSION['Forward-Backward']['Locations']) ? $_SESSION['Forward-Backward']['Locations'] : '';?>"
			}
		} );
		<?php if(!isMobile()){?>yadcf.init(Table_Locations,[
		{   column_number:0,
			filter_type:"auto_complete",
			filter_default_label:"ID"},
		{   column_number:1,
			filter_type:"auto_complete",
			filter_default_label:"Tag"},
		{   column_number:2,
			//filter_type:"auto_complete",
			filter_default_label:"Customer"},
		{   column_number:3,
			filter_type:"auto_complete",
			filter_default_label:"Street"},
		{   column_number:4,
			filter_default_label:"City",
			filter_type:"auto_complete"},
		{   column_number:5,
			filter_default_label:"State"},
		{   column_number:6,
			filter_default_label:"Zip",
			filter_type:"auto_complete"},
		{   column_number:7,
			filter_default_label:"Route"},
		{   column_number:8,
			filter_default_label:"Zone"},
		{   column_number:9,
			filter_default_label:"Maintenance"}
	]);
	$("#yadcf-filter--Table_Locations-0").attr("size","6");<?php }?>
	$(".dataTables_filter input")
	    .unbind() // Unbind previous default bindings
	    .bind("input", function(e) { // Bind our desired behavior
	        // If the length is 3 or more characters, or the user pressed ENTER, search
	        if(this.value.length >= 3 || e.keyCode == 13) {
	            // Call the API search function
	            Table_Locations.search(this.value).draw();
	        }
	        // Ensure we clear the search if they backspace far enough
	        if(this.value == "") {
	            Table_Locations.search("").draw();
	        }
	        return;
	    });
	<?php if(isMobile()){?>
		function hrefLocations(){hrefRow("Table_Locations","location");}
		$("Table#Table_Locations").on("draw.dt",function(){hrefLocations();});
	<?php }?>
    </script>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=locations.php';</script></head></html><?php }?>
