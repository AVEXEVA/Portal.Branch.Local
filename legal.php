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
	if($r){while($My_Privilege = sqlsrv_fetch_array($r)){$My_Privileges[$My_Privilege['Access_Table']] = $My_Privilege;}}
    if(	!isset($My_Connection['ID']) 
	   	|| !isset($My_Privileges['Legal'])
	  		|| $My_Privileges['Legal']['User_Privilege']  < 4
	  		|| $My_Privileges['Legal']['Group_Privilege'] < 4
	  	    || $My_Privileges['Legal']['Other_Privilege'] < 4){
				?><?php require('../404.html');?><?php }
    else {
		$database->query(null,"
			INSERT INTO Portal.dbo.Activity([User], [Date], [Page]) 
			VALUES(?,?,?)
		;",array($_SESSION['User'],date("Y-m-d H:i:s"), "legal.php"));
?><!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">    <title>Nouveau Texas | Portal</title>    
    <?php require( bin_css . 'index.php');?>
    <?php require( bin_js . 'index.php');?>
</head>
<body>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require( bin_php . 'element/navigation/index.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id="page-wrapper" class='content'>
            <div class="col-lg-12">
                    <div class="panel panel-primary">
                        <div class="panel-heading"><h3>Legal</h3></div>
                        <div class="panel-body">
                            <table id='Table_Legal' class='display' cellspacing='0' width='100%'>
                                <thead>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Location</th>
                                    <th>Type</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </thead>
                               <tfooter>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Location</th>
                                    <th>Type</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tfooter>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap Core JavaScript -->
    

    <!-- Metis Menu Plugin JavaScript -->
    

    <!-- Morris Charts JavaScript -->
    <!--<script src="https://www.nouveauelevator.com/vendor/raphael/raphael.min.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/morrisjs/morris.min.js"></script>
    <script src="../data/morris-data.php"></script>-->

    <?php require(PROJECT_ROOT.'js/datatables.php');?>
    
    <!-- Custom Theme JavaScript -->
    

    <!--Moment JS Date Formatter-->
    

    <!-- JQUERY UI Javascript -->
    

    <!-- Custom Date Filters-->
    
    <script>
        function refresh_get(){document.location.href="jobs.php?Start_Date=" + $("input[name='Start_Date']").val() + "&End_Date=" + $("input[name='End_Date']").val() + "&Job_Type=" + $("select[name='Job_Type']").val() + "&Job_Status=" + $("select[name='Job_Status']").val();}
        function hrefLegal(){hrefRow("Table_Legal","job");}
        $(document).ready(function() {
            $("input[name='Start_Date']").datepicker({});
            $("input[name='End_Date']").datepicker({});
            var Table_Legal = $('#Table_Legal').DataTable( {
                "ajax": {
                    "url":"bin/php/get/Legal.php",
                    "dataSrc":function(json){
                        if(!json.data){json.data = [];}
                        return json.data;}
                },
                "columns": [
                    { "data": "ID" },
                    { "data": "Name"},
                    { "data": "Location"},
                    { "data": "Type"},
                    { 
                        "data": "Finished_Date",
                        render: function(data){return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}
                    },
                    { "data": "Status"}
                ],
                "lengthMenu":[[10,25,50,100,500,-1],[10,25,50,100,500,"All"]],
                "order": [[1, 'asc']],
                "language":{"loadingRecords":""},
                "initComplete":function(){
                    hrefLegal();
                    finishLoadingPage();
                }
            } );
            <?php if(!$Mobile){?>
            yadcf.init(Table_Legal,[
                {   column_number:0,
                    filter_type:"auto_complete"},
                {   column_number:1,
                    filter_type:"auto_complete"},
                {   column_number:2},
                {   column_number:3},
                {   column_number:4,
                    filter_type: "range_date",
                    date_format: "mm/dd/yyyy",
                    filter_delay: 500},
                {   column_number:5}
            ]);
            stylizeYADCF();<?php }?>
            $("Table#Table_Legal").on("draw.dt",function(){hrefLegal();});
        } );
    </script>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=jobs.php';</script></head></html><?php }?>