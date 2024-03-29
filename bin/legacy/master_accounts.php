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
	   	|| !isset($My_Privileges['Customer'])
	  		|| $My_Privileges['Customer']['Owner']  < 4
	  		|| $My_Privileges['Customer']['Group'] < 4
	  	    || $My_Privileges['Customer']['Other'] < 4){
				?><?php require('../404.html');?><?php }
    else {
		$database->query(null,"
			INSERT INTO Portal.dbo.Activity([User], [Date], [Page]) 
			VALUES(?,?,?)
		;",array($_SESSION['User'],date("Y-m-d H:i:s"), "master_accounts.php"));
?><!DOCTYPE html>
<html lang="en">
<head>
    <?php require( bin_meta . 'index.php');?>    
    <title>Nouveau Texas | Portal</title>    
    <?php require( bin_css . 'index.php');?>
    <?php require( bin_js . 'index.php');?>
</head>
<body>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require( bin_php . 'element/navigation.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id="page-wrapper" class='content'>
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-primary">
                        <div class="panel-heading"><h3><?php \singleton\fontawesome::getInstance( )->Customer();?> Customers</h3></div>
                        <div class="panel-body">
                            <table id='Table_Customers' class='display' cellspacing='0' width='100%'>
                                <thead>
                                    <th title="Customer's ID">ID</th>
                                    <th title='Customer Name'>Name</th>
                                </thead>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    
    <?php require(PROJECT_ROOT.'js/datatables.php');?>
    
    
    

    <!-- Custom Date Filters-->
    
    <script>
        function hrefCustomers(){hrefRows("Table_Customers","master_account");}
        $(document).ready(function() {
            var Table_Customers = $('#Table_Customers').DataTable( {
                "ajax": {
                    "url":"bin/php/get/Master_Accounts.php",
                    "dataSrc":function(json){if(!json.data){json.data = [];}return json.data;}
                },
                "columns": [
                    { "data": "ID" },
                    { "data": "Name"}
                ],
                "order": [[1, 'asc']],
                "language":{"loadingRecords":""},
                "lengthMenu":[[10,25,50,100,500,-1],[10,25,50,100,500,"All"]],
                "initComplete":function(){finishLoadingPage();}
            } );
			$("Table#Table_Customers").on("draw.dt",function(){hrefCustomers();});
        } );
    </script>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=customers.php';</script></head></html><?php }?>