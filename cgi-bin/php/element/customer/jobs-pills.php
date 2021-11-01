 <?php 
session_start();
require('../../../php/index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Elevator'){
    	sqlsrv_query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "customer.php"));
        $r= sqlsrv_query($NEI,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID= ?",array($_SESSION['User']));
        $My_User = sqlsrv_fetch_array($r);
        $Field = ($User['Field'] == 1 && $User['Title'] != 'OFFICE') ? True : False;
        $r = sqlsrv_query($Portal,"
            SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
            FROM   Portal.dbo.Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
        $My_Privileges = array();
        while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
        $Privileged = FALSE;
        if(isset($My_Privileges['Customer']) && $My_Privileges['Customer']['User_Privilege'] >= 4 && $My_Privileges['Customer']['Group_Privilege'] >= 4 && $My_Privileges['Customer']['Other_Privilege'] >= 4){
        	sqlsrv_query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "customer.php"));
        	$Privileged = TRUE;}
        elseif($My_Privileges['Customer']['User_Privilege'] >= 4 && $My_Privileges['Ticket']['Group_Privilege'] >= 4 ){
        	sqlsrv_query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "customer.php"));
            $r = sqlsrv_query(  $NEI,"
                SELECT TicketO.ID AS ID 
                FROM nei.dbo.TicketO LEFT JOIN nei.dbo.Loc ON TicketO.LID = Loc.Loc
                WHERE Loc.Owner = ?;",array($_GET['ID']));
            $r2 = sqlsrv_query(  $NEI,"
                SELECT TicketD.ID AS ID 
                FROM nei.dbo.TicketD LEFT JOIN nei.dbo.Loc ON TicketD.Loc = Loc.Loc
                WHERE Loc.Owner = ?;",array($_GET['ID']));
            $Privileged = (is_array(sqlsrv_fetch_array($r)) || is_array(sqlsrv_fetch_array($r2))) ? TRUE : FALSE;}
    } elseif($_SESSION['Branch'] == 'Customer' && $_SESSION['Branch_ID'] == $_GET['ID']){$Privileged = TRUE;}
    //
    if(!isset($array['ID'])  || !is_numeric($_GET['ID']) || !$Privileged || !is_numeric($_GET['ID'])){?><html><head><script>document.location.href="../login.php?Forward=customer<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {
        $r = sqlsrv_query($NEI,
            "SELECT TOP 1
                    OwnerWithRol.ID      AS Customer_ID,
                    OwnerWithRol.Name    AS Name,
                    OwnerWithRol.Address AS Street,
                    OwnerWithRol.City    AS City,
                    OwnerWithRol.State   AS State,
                    OwnerWithRol.Zip     AS Zip,
                    OwnerWithRol.Status  AS Status
            FROM    OwnerWithRol
            WHERE   OwnerWithRol.ID = '{$_GET['ID']}'");
        $Customer = sqlsrv_fetch_array($r);
        $job_result = sqlsrv_query($NEI,"
            SELECT Job.ID AS ID
            FROM   Job 
            WHERE  Job.Owner = '{$_GET['ID']}'
        ;");
        if($job_result){
            $Jobs = array();
            $dates = array();
            $totals = array();
            while($array = sqlsrv_fetch_array($job_result)){$Jobs[] = "[JOBLABOR].[JOB #]='{$array['ID']}'";}
            $SQL_Jobs = implode(" OR ",$Jobs);
        }?>
<div class="tab-pane fade in" id="jobs-pills">
	<table id='Table_Jobs' class='display' cellspacing='0' width='100%'>
		<thead>
			<th></th>
			<th></th>
			<th></th>
			<th></th>
			<th></th>
			<th></th>
		</thead>
	</table>
	<div class="col-lg-6">
		<div class="panel panel-primary">
			<div class="panel-heading">Ticket Hours by Job Type</div>
			<div class="panel-body"><div class="flot-chart"><div class="flot-chart-content" id="flot-pie-chart-ticket-jobs"></div></div></div></div></div>
	<div class="col-lg-6">
		<div class="panel panel-primary">
			<div class="panel-heading">Ticket Hours by Job Type</div>
			<div class="panel-body"><div class="flot-chart"><div class="flot-chart-content" id="flot-placeholder-bar-graph-job-type-hours"></div></div></div></div>  </div>
</div>
<script>
var Editor_Jobs = new $.fn.dataTable.Editor({
	ajax: "php/post/Job.php?ID=<?php echo $_GET['ID'];?>",
	table: "#Table_Jobs",
	idSrc: "ID",
	fields : [{
		label: "ID",
		name: "ID"
	},{
		label: "Name",
		name: "Name"
	},{
		label: "Tag",
		name: "Tag"
	},{
		label: "Street",
		name: "Street"
	},{
		label: "City",
		name: "City",
		type: "select",
		options: [<?php
			$r = sqlsrv_query($NEI,"
				SELECT   Loc.City
				FROM     nei.dbo.Loc
				WHERE    Loc.City <> ''
						 AND Loc.City <> ?
				GROUP BY Loc.City
				ORDER BY Loc.City ASC
			;",array("DON'T USE THIS CODE"));
			$Cities = array();
			if($r){while($City = sqlsrv_fetch_array($r)){$Cities[] = '{' . "label: '{$City['City']}', value:'{$City['City']}'" . '}';}}
			echo implode(",",$Cities);
		?>]
	},{
		label: "State",
		name: "State",
		type: "select",
		options: [<?php
			$r = sqlsrv_query($NEI,"
				SELECT   Loc.State
				FROM     nei.dbo.Loc
				WHERE    Loc.State <> ''
				GROUP BY Loc.State
				ORDER BY Loc.State ASC
			;");
			$States = array();
			if($r){while($State = sqlsrv_fetch_array($r)){$States[] = '{' . "label: '{$State['State']}', value:'{$State['State']}'" . '}';}}
			echo implode(",",$States);
		?>]
	},{
		label: "Zip",
		name: "Zip"
	},{
		label: "Route",
		name: "Route",
		type: "select",
		options: [<?php
			$r = sqlsrv_query($NEI,"
				SELECT   Route.Name
				FROM     nei.dbo.Route
				GROUP BY Route.Name
				ORDER BY Route.Name ASC
			;");
			$States = array();
			if($r){while($State = sqlsrv_fetch_array($r)){$States[] = '{' . "label: '{$State['Name']}', value:'{$State['Name']}'" . '}';}}
			echo implode(",",$States);
		?>]
	},{
		label: "Division",
		name: "Division",
		type: "select",
		options: [<?php
			$r = sqlsrv_query($NEI,"
				SELECT   Zone.Name
				FROM     nei.dbo.Zone
				GROUP BY Zone.Name
				ORDER BY Zone.Name ASC
			;");
			$States = array();
			if($r){while($State = sqlsrv_fetch_array($r)){$States[] = '{' . "label: '{$State['Name']}', value:'{$State['Name']}'" . '}';}}
			echo implode(",",$States);
		?>]
	},{
		label:"Maintenance",
		name:"Maintenance",
		type:"radio",
		options: [
			{label: "Not Maintained", value:0},
			{label: "Maintained", value:1}
		]
	},{
		label:"Territory",
		name:"Territory",
		type:"select",
		options: [<?php 
			$r = sqlsrv_query($NEI,"
				SELECT Terr.Name 
				FROM   nei.dbo.Terr
				GROUP BY Terr.Name
				ORDER BY Terr.Name ASC
			;");
			$Territories = array();
			if($r){while($Territory = sqlsrv_fetch_array($r)){$Territories[] = '{' . "label: '{$Territory['Name']}', value:'{$Territory['Name']}'" . '}';}}
			echo implode(",",$Territories);
		?>]
	}]
});
Editor_Locations.field('ID').disable();
var Table_Jobs = $('#Table_Jobs').DataTable( {
	"ajax": {
		"url":"cgi-bin/php/get/Jobs_by_Customer.php?ID=<?php echo $_GET['ID'];?>",
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
	"buttons":[
		'copy',
		'csv',
		'excel',
		'pdf',
		'print',
		{ extend: "create", editor: Editor_Jobs },
		{ extend: "edit",   editor: Editor_Jobs },
		{ extend: "remove", editor: Editor_Jobs },
		{ text:"View",
		  action:function(e,dt,node,config){
			  document.location.href = 'job.php?ID=' + $("#Table_Locations tbody tr.selected td:first-child").html();
		  }
		}
	],
	<?php require('../../../js/datatableOptions.php');?>
} );
$("Table#Table_Jobs").on("draw.dt",function(){hrefJobs();});
<?php if(!$Mobile){?>
	yadcf.init(Table_Jobs,[
		{   column_number:0,
			filter_type:"auto_complete",
			filter_default_label:"ID"},
		{   column_number:1,
			filter_type:"auto_complete",
			filter_default_label:"Name"},
		{   column_number:2,
			filter_default_label:"Location"},
		{   column_number:3,
			filter_default_label:"Type"},
		{   column_number:4,
			filter_type: "range_date",
			date_format: "mm/dd/yyyy",
			filter_delay: 500},
		{   column_number:5,
			filter_default_label:"Status"}
	]);
	stylizeYADCF();
<?php }?>
</script>
<?php require(PROJECT_ROOT.'js/pie/tickets_by_job_type_for_customer.php');?>
<?php require(PROJECT_ROOT.'js/bar/tickets_by_job_type_for_customer.php');?>

<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=customer<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>