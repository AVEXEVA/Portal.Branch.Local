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
	   	|| !isset($My_Privileges['Executive']) 
	  		|| $My_Privileges['Executive']['User_Privilege']  < 4
	  		|| $My_Privileges['Executive']['Group_Privilege'] < 4
	  	    || $My_Privileges['Executive']['Other_Privilege'] < 4){
				?><?php require('../404.html');?><?php }
    else {
		sqlsrv_query($NEI,"
			INSERT INTO Portal.dbo.Activity([User], [Date], [Page]) 
			VALUES(?,?,?)
		;",array($_SESSION['User'],date("Y-m-d H:i:s"), "overview.php"));
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Peter D. Speranza">    <title>Nouveau Texas | Portal</title>    
    <?php require( bin_css . 'index.php');?>
    <?php require( bin_js . 'index.php');?>
</head>
<body onload="finishLoadingPage();">
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require( bin_php . 'element/navigation/index.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id="page-wrapper" class='content'>
			<table id='Table_Customers' class='display' cellspacing='0' width='100%'>
				<thead>
					<th title=''>Customer</th>
					<th title=''>Profit</th>
					<th title=''>Profit %</th>
					<th title=''>Revenue</th>
					<th>Material</th>
					<th>Labor</th>
				</thead>
			</table>
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

        $(document).ready(function(){
            var Table_Customers = $('#Table_Customers').DataTable( {
                "ajax": {
                    "url":"cgi-bin/php/get/Profitability_2017.php",
                    "dataSrc":function(json){
                        if(!json.data){json.data = [];}
                        return json.data;}
                },
                "columns": [
                    { "data": "Customer"},
					{ "data": "Profit",render: function(data){return data.toLocaleString();}},
					{ "data": "Profit_Percentage",render: function(data){return data.toLocaleString();}},
					{ "data": "Revenue",render: function(data){return data.toLocaleString();}},
					{ "data": "Material",render: function(data){return data.toLocaleString();}},
					{ "data": "Labor",render: function(data){return data.toLocaleString();}}
                ],
                "order": [[1, 'desc']],
                "lengthMenu":[[10,25,50,100,500,-1],[10,25,50,100,500,"All"]],
                "language":{"loadingRecords":""},
                "initComplete":function(){finishLoadingPage();}
            } );
            $("Table#Table_Customers").on("draw.dt",function(){hrefViolations();});
            <?php if(!$Mobile){?>
            yadcf.init(Table_Customers,[
            ]);
            stylizeYADCF();<?php }?>
        });
    </script>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=units.php';</script></head></html><?php }?>