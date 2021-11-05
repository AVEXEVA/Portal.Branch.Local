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
	   	|| !isset($My_Privileges['Job'])
	  		|| $My_Privileges['Job']['User_Privilege']  < 4
	  		|| $My_Privileges['Job']['Group_Privilege'] < 4
	  	    || $My_Privileges['Job']['Other_Privilege'] < 4){
				?><?php require('../404.html');?><?php }
    else {
		sqlsrv_query($NEI,"
			INSERT INTO Portal.dbo.Activity([User], [Date], [Page])
			VALUES(?,?,?)
		;",array($_SESSION['User'],date("Y-m-d H:i:s"), "modernizations.php"));
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
<body onload='finishLoadingPage();'>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require( bin_php . 'element/navigation/index.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id="page-wrapper" class='content'>
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-primary">
                        <div class="panel-heading"><h3><?php $Icons->Unit();?>Modernizations</h3></div>
                        <div class="panel-body">
                            <table id='Table_Modernizations' class='display' cellspacing='0' width='100%'>
                                <thead>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th>Last Week Hours</th>
                                    <th>This Week Hours</th>
                                    <th>Total Hours</th>
									                  <th>Total OT+DT</th>
                                    <th>Budgeted Hours</th>
                                    <th>Balance</th>
                                </thead>
                                <tbody></tbody>
                                <tfoot>
									<tr>
										<th></th>
										<th>Page Sum</th>
										<th></th>
										<th></th>
										<th></th>
										<th></th>
										<th></th>
										<th></th>
										<th></th>
                                        <th></th>
									</tr>
								</tfoot>
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
        function hrefModernizations(){hrefRow("Table_Modernizations","job");}

        $(document).ready(function() {
            var Table_Modernizations = $('#Table_Modernizations').DataTable( {
                "ajax": {
                    "url":"cgi-bin/php/reports/Modernizations.php",
                    "dataSrc":function(json){
                        if(!json.data){json.data = [];}
                        return json.data;
                    }
                },
                "columns": [
                    {
						"data": "ID"
					},{
						"data": "Name"
					},{
						"data": "Location"
					},{
						"data": "Supervisor"
					},{
            "data" : "Project_Manager"
          },{
                        "data": "Finished_Date",
                        render: function(data){return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}
                    },{
						"data": "Last_Week", className:"sum"
					},{
						"data": "This_Week", className:"sum"
					},{
						"data": "Total_Hours", className:"sum"
					},{
						"data" : "OT_DT"
					},{
						"data": "Budgeted_Hours", className:"sum"
					},{
						"data": "Balance",className:"sum"
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
					}
				],
                <?php require('cgi-bin/js/datatableOptions.php');?>
            } );
            $("Table#Table_Modernizations").on("draw.dt",function(){hrefModernizations();});
            <?php if(!$Mobile){?>
            yadcf.init(Table_Modernizations,[
                {   column_number:0,
                    filter_type:"auto_complete",
					filter_default_label:"ID"},
                {   column_number:1,
                    filter_type:"auto_complete",
					filter_default_label:"Job"},
                {   column_number:2,
					filter_default_label:"Location"},
          {  	column_number:3,
    filter_default_label:"Supervisor"},
    {  	column_number:4,
filter_default_label:"Project Manager"},
                {   column_number:5,
                    filter_type: "range_date",
                    date_format: "mm/dd/yyyy",
                    filter_delay: 500}
            ]);
            <?php }?>
        } );
    </script>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=modernizations.php';</script></head></html><?php }?>
