<?php 
session_start();
require('cgi-bin/php/index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"
		SELECT * 
		FROM   nei.dbo.Connection 
		WHERE  Connection.Connector = ? 
		       AND Connection.Hash  = ?
	;",array($_SESSION['User'],$_SESSION['Hash']));
    $My_Connection = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    $r = sqlsrv_query($NEI,"
		SELECT *,
		       Emp.fFirst AS First_Name,
			   Emp.Last   AS Last_Name
		FROM   nei.dbo.Emp 
		WHERE  Emp.ID = ?
	;",array($_SESSION['User']));
    $My_User = sqlsrv_fetch_array($r);
	$r = sqlsrv_query($NEI,"
		SELECT * 
		FROM   Portal.dbo.Privilege  
		WHERE  Privilege.User_ID = ?
	;",array($_SESSION['User']));
	$My_Privileges = array();
	if($r){while($My_Privilege = sqlsrv_fetch_array($r)){$My_Privileges[$My_Privilege['Access_Table']] = $My_Privilege;}}
	$Privileged = False;
	if(isset($My_Privileges['Territory']) 
	   && $My_Privileges['Territory']['Other_Privilege'] >= 4
	   && $My_Privileges['Job']['Other_Privilege'] >= 4)
	  ){$Privilegd = True;}
    if(	!isset($My_Connection['ID']) || !$Privileged || !is_numeric($_GET['ID'])){?><?php require('../404.html');?><?php }
    else {
		$r = sqlsrv_query($NEI,
            "SELECT TOP 1
                    Terr.ID   AS Territory_ID,
					Terr.Name AS Territory_Name
			FROM    nei.dbo.Terr
			WHERE   Terr.ID = ?
        ;",array($_GET['ID']));
        $Territory = sqlsrv_fetch_array($r);
		?><div class="panel panel-primary">
			<div class='panel-body white-background shadower'>
				<table id='Table_Modernizations' class='display' cellspacing='0' width='100%' style='font-size:12px;'>
					<thead>
						<th>Job</th>
						<th>Budget</th>
						<th>Hrs</th>
						<th>%</th>
						<th>Total Hrs</th>
						<th>Projected Date</th>
					</thead>
				</table>	
			</div>
		</div>
		<script>
			var Table_Modernizations = $("table#Table_Modernizations").DataTable({
				"ajax": {
					"url":"cgi-bin/php/reports/Modernizations_by_Territory.php?ID=<?php echo $_GET['ID'];?>",
					"dataSrc":function(json){if(!json.data){json.data = [];}return json.data;}
				},
				"columns": [
					{ 
						"data" : "Name"
					},{ 
						"data" : "Budgeted_Hours"
					},{ 
						"data" : "Worked_Hours"
					},{
						"data" : "Completion_Percentage"
					},{
						"data" : "Total Hours"
					},{
						"data" : "Projection_Date"
					}
				],
			});
		</script><?php
	}
}?>