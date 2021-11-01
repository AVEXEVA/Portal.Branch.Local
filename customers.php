<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( ); 
    require( '/var/www/nouveautexas.com/html/portal/cgi-bin/php/index.php' );
}
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    //Connection
    $r = sqlsrv_query(
        $NEI,
        "   SELECT  *
            FROM    Connection
            WHERE       Connection.Connector = ?
                    AND Connection.Hash  = ?;",
        array(
            $_SESSION['User'],
            $_SESSION['Hash']
        )
    );
    $Connection = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    //User
    $r = sqlsrv_query($NEI,"
        SELECT *,
               Emp.fFirst AS First_Name,
               Emp.Last   AS Last_Name
        FROM   Emp
        WHERE  Emp.ID = ?
    ;",array($_SESSION['User']));
    $User = sqlsrv_fetch_array($r);
    //Privileges
    $r = sqlsrv_query($NEI,"
        SELECT *
        FROM   Privilege
        WHERE  Privilege.User_ID = ?
    ;",array($_SESSION['User']));
    $Privileges = array();
    if($r){while($Privilege = sqlsrv_fetch_array($r)){$Privileges[$Privilege['Access_Table']] = $Privilege;}}
    //SecurityWall
    if( !isset($Connection['ID'])
        || !isset($Privileges['Customer'])
            || $Privileges['Customer']['User_Privilege']  < 4
            || $Privileges['Customer']['Group_Privilege'] < 4
            || $Privileges['Customer']['Other_Privilege'] < 4){
                ?><?php require('../404.html');?><?php }
    else {
        sqlsrv_query($NEI,"
            INSERT INTO Activity([User], [Date], [Page])
            VALUES(?,?,?)
        ;",array($_SESSION['User'],date("Y-m-d H:i:s"), "customers.php"));
?><!DOCTYPE html>
<html lang="en"style="min-height:100%;">
<head>
    <?php require(PROJECT_ROOT.'php/meta.php');?>
    <title>Nouveau Elevator Portal</title>
    <?php require(PROJECT_ROOT."css/index.php");?>
    <?php require(PROJECT_ROOT.'js/index.php');?>
    <style>
        .panel {background-color:transparent !important;}
        .panel > div.panel-body.white-background {background-color:rgba(255,255,255,.7) !important;}
        .nav-tabs > li:not(.active) {background-color:rgba(255,255,255,.6) !important;}
        .panel-heading {font-family: 'BankGothic' !important;}
        .shadow {box-shadow:0px 5px 5px 0px;}
        @media print {
            div#wrapper {overflow:visible;}
        }
    </style>
</head>
<body onload='finishLoadingPage();' style="min-height:100%;background-size:cover;background-color:rgba(255,255,255,.7);height:100%;">
    <div id='container' style='min-height:100%;height:100%;'>
        <div id="wrapper" style='height:100%;'>
            <?php require(PROJECT_ROOT.'php/element/navigation/index.php');?>
            <?php require(PROJECT_ROOT.'php/element/loading.php');?>
            <div id="page-wrapper" class='content' style='background-color:transparent !important;'>
                <div class="panel panel-primary">
                    <div class="panel-heading">
	                    <div class='row'>
	                        <div class='col-xs-10'><h4><?php $Icons->Customer( 1 );?> Customers</div>
	                        <div class='col-xs-2'><button style='width:100%;color:black;' onClick="$('#Filters').toggle();">+/-</button></div>
	                    </div>
	                </div>
					<div class="panel-body no-print" id='Filters' style='border-bottom:1px solid #1d1d1d;'>
	                    <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
	                    <div class='row'>
	                        <div class='col-xs-4'>Search:</div>
	                        <div class='col-xs-8'><input type='text' name='Search' placeholder='Search' onChange='redraw( );' /></div>
	                    </div>
	                    <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
	                    <div class='row'>
	                    	<div class='col-xs-4'>Name:</div>
	                    	<div class='col-xs-8'><input type='text' name='Name' placeholder='Name' onChange='redraw( );' /></div>
	                    </div>
	                    <div class='row'>
	                    	<div class='col-xs-4'>Status:</div>
	                    	<div class='col-xs-8'><select name='Status' onChange='redraw( );'>
			                	<option value=''>Select</option>
			                	<option value='0'>Active</option>
			                	<option value='1'>Inactive</option>
			                </select></div>
			            </div>
	                    <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
	                </div>
                    <div class="panel-body">
                        <table id='Table_Customers' class='display' cellspacing='0' width='100%'>
                            <thead>
                                <th title="ID">ID</th>
                                <th title='Name'>Name</th>
                                <th title='Status'>Status</th>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="../vendor/bootstrap/js/bootstrap.min.js"></script>
    <?php require('cgi-bin/js/datatables.php');?>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script>
        var Table_Customers = $('#Table_Customers').DataTable( {
            ajax      : {
	            url : 'cgi-bin/php/get/Customers2.php',
	            data : function( d ){
	                d = {
	                    start : d.start,
	                    length : d.length,
	                    order : {
	                        column : d.order[0].column,
	                        dir : d.order[0].dir
	                    }
	                };
	                d.Search = $('input[name="Search"]').val( );
	                d.Name = $('input[name="Name"]').val( );
	                d.Status = $('select[name="Status"]').val( );
	                return d; 
	            }
	        },
            processing : true,
            serverSide : true,
            responsive : true,
            select 	   : true,
            scrollCollapse:true,
			lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
            "order": [[ 1, "asc" ]],
            "columns": [
                {
                	data : 'ID'
                },{
                	data : 'Name'
                },{
                	data : 'Status'
                }
            ],
            "language":{
                "loadingRecords":"<div style='text-align:center;'><div class='sk-cube-grid' style='display:inline-block;position:relative;';><div class='sk-cube sk-cube1' style='background-color:#cc0000'></div><div class='sk-cube sk-cube2' style='background-color:#cc0000'></div><div class='sk-cube sk-cube3' style='background-color:#cc0000'></div><div class='sk-cube sk-cube4' style='background-color:#cc0000'></div><div class='sk-cube sk-cube5' style='background-color:#cc0000'></div><div class='sk-cube sk-cube6' style='background-color:#cc0000'></div><div class='sk-cube sk-cube7' style='background-color:#cc0000'></div><div class='sk-cube sk-cube8' style='background-color:#cc0000'></div><div class='sk-cube sk-cube9' style='background-color:#cc0000'></div></div><div class='sk-cube-grid' style='display:inline-block;position:relative;top:-45px;'><div class='sk-cube sk-cube1' style='background-color:#00007f'></div><div class='sk-cube sk-cube2' style='background-color:#00007f'></div><div class='sk-cube sk-cube3' style='background-color:#00007f'></div><div class='sk-cube sk-cube4' style='background-color:#00007f'></div><div class='sk-cube sk-cube5' style='background-color:#00007f'></div><div class='sk-cube sk-cube6' style='background-color:#00007f'></div><div class='sk-cube sk-cube7' style='background-color:#00007f'></div><div class='sk-cube sk-cube8' style='background-color:#00007f'></div><div class='sk-cube sk-cube9' style='background-color:#00007f'></div></div><div class='sk-cube-grid' style='display:inline-block;position:relative;top:-84px;'><div class='sk-cube sk-cube1' style='background-color:gold'></div><div class='sk-cube sk-cube2' style='background-color:gold'></div><div class='sk-cube sk-cube3' style='background-color:gold'></div><div class='sk-cube sk-cube4' style='background-color:gold'></div><div class='sk-cube sk-cube5' style='background-color:gold'></div><div class='sk-cube sk-cube6' style='background-color:gold'></div><div class='sk-cube sk-cube7' style='background-color:gold'></div><div class='sk-cube sk-cube8' style='background-color:gold'></div><div class='sk-cube sk-cube9' style='background-color:gold'></div></div></div><div style='font-size:72px;text-align:center;' class='BankGothic'>Nouveau Elevator</div><div style='font-size:42px;text-align:center;'><i>Raising Your Life</i></div>"
            },
            "initComplete":function( ){ },
        } );
        function hrefCustomers(){hrefRows("Table_Customers","customer");}
        $("Table#Table_Customers").on("draw.dt",function(){hrefCustomers();});
    </script>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=customers.php';</script></head></html><?php }?>
