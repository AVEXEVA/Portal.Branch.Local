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
	   	|| !isset($My_Privileges['Ticket'])
	  		|| $My_Privileges['Ticket']['User_Privilege']  < 4
	  		|| $My_Privileges['Ticket']['Group_Privilege'] < 4){
				?><?php require('../404.html');?><?php }
    else {
		sqlsrv_query($NEI,"
			INSERT INTO Portal.dbo.Activity([User], [Date], [Page])
			VALUES(?,?,?)
		;",array($_SESSION['User'],date("Y-m-d H:i:s"), "support.php"));
    ?><!DOCTYPE html>
    <html lang="en">
    <head>
        <?php require( bin_meta . 'index.php');?>
        <title>Nouveau Texas | Portal</title>
        <?php require( bin_css . 'index.php');?>
        <?php require( bin_js . 'index.php');?>
    </head>
    <body onload='finishLoadingPage();'>
        <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
            <?php require( bin_php . 'element/navigation/index.php');?>
            <?php require( bin_php . 'element/loading.php');?>
            <div id="page-wrapper" class='content'>
        			<div class="panel panel-primary">
        				<div class="panel-heading"><h3><i class="fa fa-question-circle fa-1x fa-fw" aria-hidden="true"></i> Support</h3></div>
        				<div class="panel-body">
        					<table id='Table_Tickets' class='display' cellspacing='0' width='100%'>
        						<thead>
        							<th>ID</th>
        							<th>First Name</th>
        							<th>Last Name</th>
        							<th>Date</th>
        							<th>Status</th>
        							<th>On Site</th>
                      <th>Job</th>
                      <th>Location</th>
                      <th>Division</th>
        						</thead>
        					</table>
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
    
	<style>
        div.column {display:inline-block;vertical-align:top;}
        div.label1 {display:inline-block;font-weight:bold;width:150px;vertical-align:top;}
        div.data {display:inline-block;width:300px;vertical-align:top;}
    </style>
    <script>
      var Table_Tickets = $('#Table_Tickets').DataTable( {
  			"ajax": {
  				"url":"cgi-bin/php/reports/Support.php",
  				"dataSrc":function(json){if(!json.data){json.data = [];}return json.data;}
  			},
  			"lengthMenu":[[10,25,50,100,500,-1,0],[10,25,50,100,500,"All","None"]],
  			"columns": [
  				{ "data": "ID" },
  				{ "data": "Worker_First_Name"},
  				{ "data": "Worker_Last_Name"},
  				{
  					"data": "Date",
  					render: function(data){if(data != null){return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}else{return null;}}
  				},
  				{ "data": "Status"},
  				{
  					"data": "On_Site",
  					"defaultContent":"0"
  				}, {
            "data":"Job_Description"
          }, {
            "data":"Location"
          },{
            "data" :"Division"
          }
  			],
  			"order": [[5, 'asc']],
  			"language":{
  				"loadingRecords":""
  			},
  			"initComplete":function(){

  			}
  		} );
      function hrefTickets(){hrefRow("Table_Tickets","ticket");}
		$("Table#Table_Tickets").on("draw.dt",function(){hrefTickets();});
    </script>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=units.php';</script></head></html><?php }?>
