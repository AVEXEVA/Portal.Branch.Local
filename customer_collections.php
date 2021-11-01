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
    if(!isset($My_Connection['ID']) 
	   	|| !isset($My_Privileges['Invoice'])
	  		|| $My_Privileges['Invoice']['User_Privilege']  < 4
	  		|| $My_Privileges['Invoice']['Group_Privilege'] < 4
	  		|| $My_Privileges['Invoice']['Other_Privilege'] < 4){
				?><?php require('../404.html');?><?php }
    else {
		sqlsrv_query($NEI,"
			INSERT INTO Portal.dbo.Activity([User], [Date], [Page]) 
			VALUES(?,?,?)
		;",array($_SESSION['User'],date("Y-m-d H:i:s"), "customer_collections.php"));
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Peter D. Speranza">
    <title>Nouveau Texas | Portal</title>
    <?php require(PROJECT_ROOT."css/index.php");?>
    <?php require(PROJECT_ROOT.'js/index.php');?>
</head>
<body>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require(PROJECT_ROOT.'php/element/navigation/index2.php');?>
        <?php require(PROJECT_ROOT.'php/element/loading.php');?>
        <div id="page-wrapper" class='content'>
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-primary">
                        <div class="panel-heading"><h3><?php $Icons->Collection();?>Collections</h3></div>
                        <div class="panel-body">
                            <table id='Table_Collections' class='display' cellspacing='0' width='100%'>
                                <thead>
                                    <th>Customer</th>
                                    <th>Balance</th>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap Core JavaScript -->
    <script src="../vendor/bootstrap/js/bootstrap.min.js"></script>

    <!-- Metis Menu Plugin JavaScript -->
    <script src="../vendor/metisMenu/metisMenu.js"></script>    
	<?php require('cgi-bin/js/datatables.php');?>
    <!-- Custom Theme JavaScript -->
    <script src="../dist/js/sb-admin-2.js"></script>

    <!-- JQUERY UI Javascript -->
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

    <!-- Custom Date Filters-->
    <script src="../dist/js/filters.js"></script>
    <script>
        function hrefCollection(){hrefRow("Table_Collections","invoice");}

        function formatCollection ( d ) {
            // `d` is the original data object for the row
            return '<table cellpadding="5" cellspacing="0" border="0" style="padding-left:50px;">'+
                '<tr>'+
                    '<td>Description:</td>'+
                    '<td>'+d.Description+'</td>'+
                '</tr>'+
                '<tr>'+
                    '<td><a href="invoice.php?ID='+d.Invoice+'"><?php $Icons->Collection();?>View Invoice</a></td>'+
                '</tr>'+
            '</table>';
        }
        $(document).ready(function() {
            var Table_Collections = $('#Table_Collections').DataTable( {
                "ajax": {
                    "url":"php/get/Collections_by_Customers.php",
                    "dataSrc":function(json){
                        if(!json.data){json.data = [];}
                        return json.data;
                    }
                },
                "columns": [
                    { "data" : "Customer"},
                    { "data" : "Balance", render:function(data){return data.toLocaleString();}}
                ],
                "order": [[1, 'asc']],
                "language":{"loadingRecords":""},
                "initComplete":function(){finishLoadingPage();},
                "lengthMenu":[[10,25,50,100,500,-1,0],[10,25,50,100,500,"All","None"]],
                "scrollX":true
            } );
            <?php if(!$Mobile){?>
            yadcf.init(Table_Collections,[
                {   column_number:0},
                {   column_number:1,
                    filter_type: "range_number_slider"}
            ]);<?php }?>
        } );
    </script>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=collections.php';</script></head></html><?php }?>