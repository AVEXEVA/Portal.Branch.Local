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
	   	|| !isset($My_Privileges['Contract'])
	  		|| $My_Privileges['Contract']['User_Privilege']  < 4
	  		|| $My_Privileges['Contract']['Group_Privilege'] < 4){require('../404.html');}
    else {
		sqlsrv_query($NEI,
      "INSERT INTO Portal.dbo.Activity([User], [Date], [Page])
			VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "contracts.php"));
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Peter D. Speranza">
    <title>Nouveau Texas | Portal</title>
    <?php require( bin_css . 'index.php');?>
    <?php require( bin_js . 'index.php');?>
</head>
<body onload='finishLoadingPage();' style='background-color:#1d1d1d;'>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require(PROJECT_ROOT.'php/element/navigation/index.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id="page-wrapper" class='content'>
			<div class="panel panel-primary">
				<div class="panel-heading"><h4><?php \singleton\fontawesome::getInstance( )->Contract();?> Contracts</h4></div>
        <div class='panel-body'><form id='Manage_Contract_Item'>
          <div class='row'>
            <div class='col-xs-1'>Territory</div>
            <div class='col-xs-11'><select name='Territory'>
              <option value=''>Select</option>
              <?php
                $r = sqlsrv_query($NEI,"SELECT * FROM nei.dbo.Terr;");
                if($r){while($row = sqlsrv_fetch_array($r)){?><option value='<?php echo $row['ID'];?>'><?php echo $row['Name'];?></option><?php }}
              ?>
            </select></div>
          </div>
          <div class='row'>
            <div class='col-xs-1'>Customer</div>
            <div class='col-xs-11'><input type='text' value='' name='Customer' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-1'>Location</div>
            <div class='col-xs-11'><input type='text' value='' name='Customer' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-1'>Unit</div>
            <div class='col-xs-11'><input type='text' value='' name='Unit' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-2'><select name='Elevator_Part'>
              <option value=''>Select</option>
              <?php
                $r = sqlsrv_query($NEI,"SELECT * FROM Portal.dbo.Category_Elevator_Part;");
                if($r){while($row = sqlsrv_fetch_array($r)){?><option value='<?php echo $row['ID'];?>'><?php echo $row['Name'];?></option><?php }}
              ?>
            </select></div>
            <div class='col-xs-2'><select name='Condition'>
              <option value=''>Select</option>
              <?php
                $r = sqlsrv_query($NEI,"SELECT * FROM Portal.dbo.Category_Violation_Condition;");
                if($r){while($row = sqlsrv_fetch_array($r)){?><option value='<?php echo $row['ID'];?>'><?php echo $row['Name'];?></option><?php }}
              ?>
            </select></div>
            <div class='col-xs-2'><select name='Remedy'>
              <option value=''>Select</option>
              <?php
                $r = sqlsrv_query($NEI,"SELECT * FROM Portal.dbo.Category_Remedy;");
                if($r){while($row = sqlsrv_fetch_array($r)){?><option value='<?php echo $row['ID'];?>'><?php echo $row['Name'];?></option><?php }}
              ?>
            </select></div>
            <div class='col-xs-6'><button type='button' onClick='contract_item_covered();'>Cover Contract Item for Criteria</button></div>
          </div>
        </form></div>
        <script>
          function contract_item_covered(){
            var contractData = new FormData($('form#Manage_Contract_Item')[0]);
            $.ajax({
              url:"cgi-bin/php/post/cover_contract_item.php",
              cache: false,
              processData: false,
              contentType: false,
              data: contractData,
              timeout:15000,
              error:function(XMLHttpRequest, textStatus, errorThrown){
                alert('Your ticket did not save. Please check your internet.')
              },
              method:"POST",
              success:function(code){
                //document.location.href='contract_items.php';
              }
            });
          }
        </script>
				<div class="panel-body">
					<table id='Table_Contract_Items' class='display' cellspacing='0' width='100%' style='font-size:12px;'>
						<thead>
							<th>Contract</th>
							<th>Customer</th>
							<th>Location</th>
							<th>Unit</th>
							<th>Elevator Part</th>
							<th>Condition</th>
							<th>Remedy</th>
							<th>Covered</th>
						</thead>
					</table>
				</div>
            </div>
        </div>
    </div>
    <!-- Bootstrap Core JavaScript -->
    
    <!-- JQUERY UI Javascript -->
    
    <?php require('cgi-bin/js/datatables.php');?>
    <script>
    var Table_Contract_Items = null;
      $(document).ready(function(){
          Table_Contract_Items = $('#Table_Contract_Items').DataTable( {
              "ajax": {
                  "url":"cgi-bin/php/get/contract_category_items.php",
                  "data": function ( d ) {
                     return $.extend( {}, d, {
                       "Territory": $("input[name='Start']").val(),
                       "Customer": $("input[name='Customer']").val(),
                       "Location": $("input[name='Location']").val(),
                       "Unit": $("input[name='Unit']").val()
                     } );
                   },
                  "dataSrc":function(json){
                    if(!json.data){json.data = [];}
                    return json.data;
                  }
              },
              /*"processing":true,
              "serverSide":true,*/
              "columns": [
                  { "data": "Contract"},
                  { "data": "Customer"},
                  { "data": "Location"},
                  { "data": "Unit"},
                  { "data": "Unit_Part"},
                  { "data": "Unit_Part_Condition"},
                  { "data": "Unit_Part_Remedy"},
                  { "data": "Unit_Part_Covered"}
              ],
              "order": [[1, 'desc']],
              "lengthMenu":[[10,25,50,100,500,-1],[10,25,50,100,500,"All"]],
              "language":{"loadingRecords":""},
              "initComplete":function(){},
          } );
      });
      function refresh(){
        Table_Escalations.draw();
      }
    </script>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=contracts.php';</script></head></html><?php }?>
