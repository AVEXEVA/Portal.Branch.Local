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
	   	|| !isset($My_Privileges['Executive'])
	  		|| $My_Privileges['Executive']['User_Privilege']  < 4
	  		|| $My_Privileges['Executive']['Group_Privilege'] < 4
	  	    || $My_Privileges['Executive']['Other_Privilege'] < 4){
				?><?php require('../404.html');?><?php }
    else {
		sqlsrv_query($NEI,"
			INSERT INTO Portal.dbo.Activity([User], [Date], [Page])
			VALUES(?,?,?)
		;",array($_SESSION['User'],date("Y-m-d H:i:s"), "escalation_report.php"));
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
<body onload="finishLoadingPage();">
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require(PROJECT_ROOT.'php/element/navigation/index2.php');?>
        <?php require(PROJECT_ROOT.'php/element/loading.php');?>
        <div id="page-wrapper" class='content'>
          <div class='panel-primary'>
            <div class='panel-heading'>Escalation Report</div>
            <div class='panel-body'>
              <div class='row'>
                <div class='col-xs-1'>Start:</div>
                <div class='col-xs-11'><input type='text' name='Start' /></div>
              </div>
              <div class='row'>
                <div class='col-xs-1'>End:</div>
                <div class='col-xs-11'><input type='text' name='End' /></div>
              </div>
              <div class='row'>
                <div class='col-xs-12'><button onClick='refresh();'>Refresh</button></div>
              </div>
            </div>
            <div class='panel-body'>
        			<table id='Table_Escalations' class='display' cellspacing='0' width='100%'>
        				<thead>
        					<th>Customer</th>
        					<th>Location</th>
        					<th>Start</th>
        					<th>Length</th>
                  <th>End</th>
                  <th>Amount</th>
                  <th>BCycle</th>
                  <th>Escalated</th>
                  <th>Esc Cycle</th>
                  <th>Customer Profit</th>
                  <th>Location Profit</th>
                  <th>L. Net Income</th>
                  <th>L. Revenue</th>
                  <th>L. Labor</th>
                  <th>L. Materials</th>
                  <th>Loc Profit %</th>
                  <th>Loc Mod Profit</th>
        				</thead>
        			</table>
            </div>
          </div>
        </div>
    </div>
	<!-- Bootstrap Core JavaScript -->
    <script src="../vendor/bootstrap/js/bootstrap.min.js"></script>

    <!-- Metis Menu Plugin JavaScript -->
    <script src="../vendor/metisMenu/metisMenu.js"></script>

    <?php require('cgi-bin/js/datatables.php');?>
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
      var Table_Escalations = null;
        $(document).ready(function(){
            Table_Escalations = $('#Table_Escalations').DataTable( {
                "ajax": {
                    "url":"cgi-bin/php/reports/Escalation_Report.php",
                    "data": function ( d ) {
                       return $.extend( {}, d, {
                         "Start": $("input[name='Start']").val(),
                         "End": $("input[name='End']").val()
                       } );
                     },
                    "dataSrc":function(json){
                      if(!json.data){json.data = [];}
                      return json.data;
                    }
                },
                "processing":true,
                "serverSide":true,
                "columns": [
                    { "data": "Customer_Name"},
                    { "data": "Location_Name"},
                    { "data": "Contract_Start"},
                    { "data": "Contract_Length"},
                    { "data": "Contract_End"},
                    {
                      "data": "Contract_Amount",
                      "type":"num-fmt",
                      render: $.fn.dataTable.render.number( ',', '.', 2 )
                    },
                    {
                      "data": "Contract_BCycle"
                    },
                    { "data": "Contract_Escalated"},
                    { "data": "Contract_Escalation_Cycle"},
                    { "data": "Customer_Profit",
                      render: $.fn.dataTable.render.number( ',', '.', 2 )
                    },
                    {
                      "data": "Location_Profit",
                      "type":"num-fmt",
                      render: $.fn.dataTable.render.number( ',', '.', 2 )
                    },
                    {
                      "data": "Location_Net_Income",
                      "type":"num-fmt",
                      render: $.fn.dataTable.render.number( ',', '.', 2 )
                    },
                    {
                      "data": "Location_Revenue",
                      "type":"num-fmt",
                      render: $.fn.dataTable.render.number( ',', '.', 2 )
                    },
                    {
                      "data": "Location_Labor",
                      "type":"num-fmt",
                      render: $.fn.dataTable.render.number( ',', '.', 2 )
                    },
                    {
                      "data": "Location_Material",
                      "type":"num-fmt",
                      render: $.fn.dataTable.render.number( ',', '.', 2 )
                    },
                    { "data": "Location_Profit_Percentage",
                    "type":"num-fmt",
                    render:$.fn.dataTable.render.number( ',', '.', 2 )
                  },
                    { "data": "Location_Modernization_Profit",
                    "type":"num-fmt",
                    render: $.fn.dataTable.render.number( ',', '.', 2 )
                  }
          					/*{ "data": "Profit",render: function(data){return data.toLocaleString();}},
          					{ "data": "Profit_Percentage"},
          					{ "data": "Revenue",render: function(data){return data.toLocaleString();}},
          					{ "data": "Material",render: function(data){return data.toLocaleString();}},
          					{ "data": "Labor",render: function(data){return data.toLocaleString();}}*/
                ],
                "order": [[1, 'desc']],
                "lengthMenu":[[10,25,50,100,500,-1],[10,25,50,100,500,"All"]],
                "language":{"loadingRecords":""},
                "initComplete":function(){finishLoadingPage();},
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
} else {?><html><head><script>document.location.href='../login.php?Forward=units.php';</script></head></html><?php }?>
