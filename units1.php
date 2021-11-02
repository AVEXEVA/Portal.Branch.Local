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
	   	|| !isset($My_Privileges['Unit'])
	  		|| $My_Privileges['Unit']['User_Privilege']  < 4
	  		|| $My_Privileges['Unit']['Group_Privilege'] < 4){
				?><?php require('../404.html');?><?php }
    else {
		sqlsrv_query($NEI,"
			INSERT INTO Portal.dbo.Activity([User], [Date], [Page])
			VALUES(?,?,?)
		;",array($_SESSION['User'],date("Y-m-d H:i:s"), "units.php"));

if(isMobile() || true){?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Peter D. Speranza">    <title>Nouveau Connecticut | Portal</title>
    <?php require(PROJECT_ROOT."css/index.php");?>
    <?php require(PROJECT_ROOT.'js/index.php');?>
</head>
<body onload='finishLoadingPage();' style='background-color:#1d1d1d;'>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
      <?php require(PROJECT_ROOT.'php/element/navigation/index.php');?>
      <?php require(PROJECT_ROOT.'php/element/loading.php');?>
      <div id="page-wrapper" class='content'>
  			<div class="panel panel-primary">
  				<div class="panel-heading"><h4><div style='float:left;' onClick="document.location.href='home.php';"><?php $Icons->Unit();?> Units</div><div style='float:right;' onClick="document.location.href='unit.php'"><?php $Icons->Add(1);?></div><div style='clear:both;'></div></h4></div>
  				<div class="panel-body">
  					<table id='Table_Units' class='display' cellspacing='0' width='100%' style='font-size:12px;'>
  						<thead>
  							<th></th>
  							<th></th>
  							<th>State</th>
  							<th>Label</th>
  							<th>Type</th>
  							<th>Status</th>
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

    <!-- JQUERY UI Javascript -->
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

    <script>
	var Table_Units = $('#Table_Units').DataTable( {
		"ajax": "cgi-bin/php/get/Units.php",
		"processing":true,
		"serverSide":true,
		"columns": [
			{
				"className":"hidden"
			},{
				"className":"hidden"
			},{
				label: "State",
				name: "State",
        render:function(data){
          if(data == ''){return 'N/A';}
          return data;
        }
			},{
			},{
			},{
				render:function(data){
					switch(data){
						case 0:return 'Active';
						case 1:return 'Inactive';
						case 2:return 'Demolished';
						case 3:return 'Dismantled';
						case 4:return 'Removed';
						case 5:return 'No Jurisdiction';
						default:return 'Error';
					}
				}
			},{
        "className":"hidden"
      },{

      }
		],
		<?php /*"buttons":[
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
				  var data = Table_Units.rows({selected:true}).data()[0];
				  document.location.href = 'unit.php?ID=' + data.ID;//$("#Table_Units tbody tr.selected td:first-child").html();
			  }
			}
		],*/?>
		"language":{
			"loadingRecords":"<div style='text-align:center;'><div class='sk-cube-grid' style='display:inline-block;position:relative;';><div class='sk-cube sk-cube1' style='background-color:#cc0000'></div><div class='sk-cube sk-cube2' style='background-color:#cc0000'></div><div class='sk-cube sk-cube3' style='background-color:#cc0000'></div><div class='sk-cube sk-cube4' style='background-color:#cc0000'></div><div class='sk-cube sk-cube5' style='background-color:#cc0000'></div><div class='sk-cube sk-cube6' style='background-color:#cc0000'></div><div class='sk-cube sk-cube7' style='background-color:#cc0000'></div><div class='sk-cube sk-cube8' style='background-color:#cc0000'></div><div class='sk-cube sk-cube9' style='background-color:#cc0000'></div></div><div class='sk-cube-grid' style='display:inline-block;position:relative;top:-45px;'><div class='sk-cube sk-cube1' style='background-color:#00007f'></div><div class='sk-cube sk-cube2' style='background-color:#00007f'></div><div class='sk-cube sk-cube3' style='background-color:#00007f'></div><div class='sk-cube sk-cube4' style='background-color:#00007f'></div><div class='sk-cube sk-cube5' style='background-color:#00007f'></div><div class='sk-cube sk-cube6' style='background-color:#00007f'></div><div class='sk-cube sk-cube7' style='background-color:#00007f'></div><div class='sk-cube sk-cube8' style='background-color:#00007f'></div><div class='sk-cube sk-cube9' style='background-color:#00007f'></div></div><div class='sk-cube-grid' style='display:inline-block;position:relative;top:-84px;'><div class='sk-cube sk-cube1' style='background-color:gold'></div><div class='sk-cube sk-cube2' style='background-color:gold'></div><div class='sk-cube sk-cube3' style='background-color:gold'></div><div class='sk-cube sk-cube4' style='background-color:gold'></div><div class='sk-cube sk-cube5' style='background-color:gold'></div><div class='sk-cube sk-cube6' style='background-color:gold'></div><div class='sk-cube sk-cube7' style='background-color:gold'></div><div class='sk-cube sk-cube8' style='background-color:gold'></div><div class='sk-cube sk-cube9' style='background-color:gold'></div></div></div><div style='font-size:72px;text-align:center;' class='BankGothic'>Nouveau Connecticut</div><div style='font-size:42px;text-align:center;'><i>Raising Your Life</i></div>"
		},
		"paging":true,
		<?php if(!isMobile() && false){?>"dom":"Bfrtip",<?php }?>
		"select":true,
		"initComplete":function(){
		},
		"scrollY" : "600px",
		"scrollCollapse":true,
		"lengthChange": false,
		"order": [[ 1, "ASC" ]],
		"search":{
			"search":"<?php echo isset($_SESSION['Forward-Backward'],$_SESSION['Forward-Backward']['Units']) ? $_SESSION['Forward-Backward']['Units'] : '';?>"
		}
	} );
	<?php if(isMobile() || true){?>
		function hrefUnits(){hrefRow("Table_Units","unit");}
		$("Table#Table_Units").on("draw.dt",function(){hrefUnits();});
	<?php }?>
	</script>
</body>
</html>
<?php
} else {
  $_GET['processing'] = 1;
  require('../beta/units.php');
}
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=units.php';</script></head></html><?php }?>
