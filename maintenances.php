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
	   	|| !isset($My_Privileges['Unit'])
	  		|| $My_Privileges['Unit']['User_Privilege']  < 4
	  		|| $My_Privileges['Unit']['Group_Privilege'] < 4
	  	    || $My_Privileges['Unit']['Other_Privilege'] < 4){
				?><?php require('../404.html');?><?php }
    else {
		sqlsrv_query($NEI,"
			INSERT INTO Portal.dbo.Activity([User], [Date], [Page])
			VALUES(?,?,?)
		;",array($_SESSION['User'],date("Y-m-d H:i:s"), "maintenances.php"));
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
<body>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require( bin_php . 'element/navigation/index.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id="page-wrapper" class='content'>
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-primary">
                        <div class="panel-heading"><h3><?php \singleton\fontawesome::getInstance( )->Unit();?>Required Maintenance</h3></div>
                        <div class="panel-body">
                            <table id='Table_Units' class='display' cellspacing='0' width='100%'>
                                <thead>
                                    <th title="Unit's ID">ID</th>
                                    <th title='Unit State ID'>State</th>
                                    <th title="Unit's Label">Unit</th>
                                    <th title="Type of Unit">Type</th>
                                    <th title="Unit's Location">Location</th>
                                    <th>Route</th>
                                    <th>Division</th>
                                    <th>Worked On Last</th>
                                </thead>
                            </table>
                        </div>
                    </div>
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
        function hrefUnits(){
            $("#Table_Units tbody tr").each(function(){
                $(this).on('click',function(){
                    document.location.href="unit.php?ID=" + $(this).children(":first-child").html();
                });
             });
        }

        $(document).ready(function() {
            var Table_Units = $('#Table_Units').DataTable( {
                "ajax": {
                    "url":"cgi-bin/php/reports/Maintenances.php",
                    "dataSrc":function(json){if(!json.data){json.data = [];}return json.data;}
                },
                "columns": [
                    { "data": "ID" },
                    { "data": "State"},
                    { "data": "Unit"},
                    { "data": "Type"},
                    { "data": "Location"},
                    { "data": "Route"},
                    { "data": "Zone"},
                    { "data": "Last_Date",render: function(data){return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}}
                ],
                "order": [[1, 'asc']],
                "language":{"loadingRecords":""},
                "initComplete":function(){
                    setTimeout(function(){hrefUnits();},100);
                    finishLoadingPage();
                },
                "lengthMenu":[[10,25,50,100,500,-1,0],[10,25,50,100,500,"All","None"]]
            } );
            $("Table#Table_Units").on("draw.dt",function(){hrefUnits();});
            <?php if(!$Mobile){?>
            yadcf.init(Table_Units,[
                {   column_number:0,
                    filter_type:"auto_complete"},
                {   column_number:1,
                    filter_type:"auto_complete"},
                {   column_number:2,
                    filter_type:"auto_complete"},
                {   column_number:3},
                {   column_number:4},
                {   column_number:5},
                {   column_number:6},
                {   column_number:7,
                    filter_type: "range_date",
                    date_format: "mm/dd/yyyy",
                    filter_delay: 500}
            ]);
            stylizeYADCF();<?php }?>
        } );
    </script>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=units.php';</script></head></html><?php }?>
