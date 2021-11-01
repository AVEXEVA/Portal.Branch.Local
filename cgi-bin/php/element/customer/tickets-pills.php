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
            SELECT 
                Job.ID AS ID
            FROM 
                Job 
            WHERE 
                Job.Owner = '{$_GET['ID']}'
        ;");
        if($job_result){
            $Jobs = array();
            $dates = array();
            $totals = array();
            while($array = sqlsrv_fetch_array($job_result)){$Jobs[] = "[JOBLABOR].[JOB #]='{$array['ID']}'";}
            $SQL_Jobs = implode(" OR ",$Jobs);
        }?>
<div class="tab-pane fade in" id="tickets-pills">
	<div class='input-group'>
		<button class='form-control' onClick="expandTickets(this);">Expand Tickets</button>
	</div>
	<table id='Table_Tickets' class='display' cellspacing='0' width='100%'>
		<thead>
			<th></th>
			<th></th>
			<th></th>
			<th></th>
			<th></th>
			<th></th>
			<th></th>                                            
			<th>Hours</th>
		</thead>
	</table>
	</br>
	<h3>Past Thirty Days Ticket Activity</h3>
	<div id='map' style='width:100%;height:650px;'></div>
	</br>
	<div class='row'><div class="col-lg-12">
		<div class="panel panel-primary">
			<div class="panel-heading">Ticket Activity</div>
			<div class="panel-body"><div class="flot-chart"><div class="flot-chart-content" id="flot-placeholder-tickets"></div></div></div>
		</div>  
	</div></div>
	<div class='row'><div class="col-lg-12">
		<div class="panel panel-primary">
			<div class="panel-heading">Service Call Activity</div>
			<div class="panel-body"><div class="flot-chart"><div class="flot-chart-content" id="flot-placeholder-service-calls"></div></div></div>
		</div>
	</div></div>
</div>
<script>

function initialize() {
	//var latlng = new google.maps.LatLng(40.7128, -74.0060);
	var latlng = {lat: 40.7130, lng:-74.0060};
	var myOptions = {
	  zoom: 8,
	  center: latlng
	};
	var map = new google.maps.Map(document.getElementById("map"),myOptions);
	var marker = new Array();
	<?php 
	$Start_Date            = date('Y-m-d H:i:s', strtotime('-30 days'));
	$End_Date              = date('Y-m-d H:i:s', strtotime('now'));
	$r = sqlsrv_query($NEI,"
	SELECT 
		TechLocation.*,
		Emp.fFirst AS First_Name,
		Emp.Last,
		Emp.fWork,
		Emp.ID as Employee_ID
	FROM
		TechLocation
		LEFT JOIN nei.dbo.Emp ON TechLocation.TechID = Emp.fWork
		LEFT JOIN TicketD ON TechLocation.TicketID = TicketD.ID
		LEFT JOIN nei.dbo.Loc ON TicketD.Loc = Loc.Loc
	WHERE
		TechLocation.DateTimeRecorded >= ?
		AND TechLocation.DateTimeRecorded <= ?
		AND Loc.Owner = ?
		AND TechLocation.TicketID <> '1797285'
;",array($Start_Date,$End_Date,$_GET['ID']));
$GPS_Locations = array("General"=>array());
while($array = sqlsrv_fetch_array($r)){
	if(!isset($GPS_Locations[$array['TicketID']])){$GPS_Locations[$array['TicketID']] = array("General"=>array());}
	if($array['ActionGroup'] == "General"){$GPS_Locations['General'][$array['ID']] = $array;}
	elseif(in_array($array['ActionGroup'],array("On site time","Completed time"))){$GPS_Locations[$array['TicketID']][$array['ActionGroup']] = $array;}
}
$GPS = $GPS_Locations;
$Now_Location = array();
foreach($GPS_Locations as $key=>$GPS_Location){
	if($key == "General"){continue;}
	if(!isset($GPS_Location['Completed time'])){$Now = $GPS_Location['On site time'];break;}
}
$GPS = $GPS_Locations;
foreach($GPS_Locations["General"] as $ID=>$General_Location){
	if(strtotime($General_Location['DateTimeRecorded']) >= strtotime($Now_Location['DateTimeRecorded'])){$GPS[$Now_Location['TicketID']]['General'][$General_Location['ID']] = $General_Location;}
	else {
		$Temp = $GPS_Locations;
		unset($Temp['General']);
		foreach($Temp as $key=>$value){
			if(strtotime($value['On site time']['DateTimeRecorded']) <= strtotime($General_Location['DateTimeRecorded']) && strtotime($value['Completed time']) >= strtotime($General_Location['DateTimeRecorded'])){$GPS[$key]['General'][$General_Location['ID']] = $General_Location;unset($GPS['General']);break;}
		}
	}
}
//var_dump($GPS);
foreach($GPS as $key=>$array){
	if($_GET['Type'] == 'Live' && isset($array['Completed time'])){continue;}
	if($key == "General"){continue;}
	if(isset($array['On site time'])){
		$GPS_Location = $array['On site time'];
		?>
		marker[<?php echo $key;?>] = new google.maps.Marker({
		  position: {lat:<?php echo $GPS_Location['Latitude'];?>,lng:<?php echo $GPS_Location['Longitude'];?>},
		  map: map,
		  title: '<?php echo $GPS_Location['fFirst'] . " " . $GPS_Location['Last'];?> -- <?php echo $GPS_Location['DateTimeRecorded'];?> -- <?php echo $GPS_Location['TicketID'];?>',
		  Icon: 'http://vps9073.inmotionhosting.com/~skeera6/portal/images/GoogleMapsMarkers/<?php if($GPS_Location['ActionGroup'] == 'Completed time'){?>green_MarkerC<?php }
		  elseif($GPS_Location['ActionGroup'] == 'On site time'){?>yellow_MarkerO<?php }
		elseif($GPS_Location['ActionGroup'] == 'General'){?>paleblue_MarkerG<?php }?>.png'
		})
		marker[<?php echo $key?>].addListener('click',function(){
			document.location.href='ticket.php?ID=<?php echo $GPS_Location['TicketID'];?>';
		});
	<?php }
	if(isset($array['Completed time'])){
		$GPS_Location = $array['Completed time'];
		?>
		marker[<?php echo $key;?>] = new google.maps.Marker({
		  position: {lat:<?php echo $GPS_Location['Latitude'];?>,lng:<?php echo $GPS_Location['Longitude'];?>},
		  map: map,
		  title: '<?php echo $GPS_Location['fFirst'] . " " . $GPS_Location['Last'];?> -- <?php echo $GPS_Location['DateTimeRecorded'];?> -- <?php echo $GPS_Location['TicketID'];?>',
		  Icon: 'http://vps9073.inmotionhosting.com/~skeera6/portal/images/GoogleMapsMarkers/<?php if($GPS_Location['ActionGroup'] == 'Completed time'){?>green_MarkerC<?php }
		  elseif($GPS_Location['ActionGroup'] == 'On site time'){?>yellow_MarkerO<?php }
		elseif($GPS_Location['ActionGroup'] == 'General'){?>paleblue_MarkerG<?php }?>.png'
		});
		marker[<?php echo $key?>].addListener('click',function(){
			document.location.href='ticket.php?ID=<?php echo $GPS_Location['TicketID'];?>';
		});
	<?php }
	foreach($array['General'] as $k=>$GPS_Location){?>
		marker[<?php echo $k;?>] = new google.maps.Marker({
		  position: {lat:<?php echo $GPS_Location['Latitude'];?>,lng:<?php echo $GPS_Location['Longitude'];?>},
		  map: map,
		  title: '<?php echo $GPS_Location['fFirst'] . " " . $GPS_Location['Last'];?> -- <?php echo $GPS_Location['DateTimeRecorded'];?> -- <?php echo $GPS_Location['TicketID'];?>',
		  Icon: 'http://vps9073.inmotionhosting.com/~skeera6/portal/images/GoogleMapsMarkers/<?php if($GPS_Location['ActionGroup'] == 'Completed time'){?>green_MarkerC<?php }
		  elseif($GPS_Location['ActionGroup'] == 'On site time'){?>yellow_MarkerO<?php }
		elseif($GPS_Location['ActionGroup'] == 'General'){?>paleblue_MarkerG<?php }?>.png'
		});
		marker[<?php echo $k?>].addListener('click',function(){
			document.location.href='ticket.php?ID=<?php echo $GPS_Location['TicketID'];?>';
		});
	<?php }
	}?>google.maps.event.trigger(map, 'resize');
}
var expandTicketButton = true;
function expandTickets(link){
	$("Table#Table_Tickets tbody tr td:first-child").each(function(){
		$(this).click();
	});
	if(expandTicketButton){
		$(link).html("Collapse Tickets");
	} else {
		$(link).html("Expand Tickets");
	}
	expandTicketButton = !expandTicketButton;
}
function hrefTickets(){hrefRow("Table_Tickets","ticket");}
$(document).ready(function(){
    $("Table#Table_Tickets").on("draw.dt",function(){hrefTickets();});
		var Table_Tickets = $('#Table_Tickets').DataTable( {
			"ajax": {
				"url":"cgi-bin/php/get/Tickets_by_Customer.php?ID=<?php echo $_GET['ID'];?>",
				"dataSrc":function(json){if(!json.data){json.data = [];}return json.data;}
			},
			"columns": [
				{
					"className":      'details-control',
					"orderable":      false,
					"data":           null,
					"defaultContent": ''
				},
				{ "data": "ID" },
				{ "data": "Tag"},
				{ "data": "Worker_First_Name"},
				{ "data": "Worker_Last_Name"},
				{ 
					"data": "EDate",
					render: function(data){return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}
				},
				{ "data": "Status"},
				{ 
					"data": "Total",
					"defaultContent":"0"
				},
				{
					"data":"Unit_State",
					"visible":false,
					"searchable":true
				},
				{
					"data":"Unit_Label",
					"visible":false,
					"searchable":true
				}
			],
			"lengthMenu":[[10,25,50,100,500,-1,0],[10,25,50,100,500,"All","None"]],
			"order": [[1, 'asc']],
			"language":{
				"loadingRecords":""
			},
			"initComplete":function(){
				$("#loading-pills").removeClass("active");
				$("#tickets-pills").addClass('active');
			}
		} );
		$('#Table_Tickets tbody').on('click', 'td.details-control', function () {
			var tr = $(this).closest('tr');
			var row = Table_Tickets.row( tr );

			if ( row.child.isShown() ) {
				row.child.hide();
				tr.removeClass('shown');
			}
			else {
				row.child( format(row.data()) ).show();
				tr.addClass('shown');
			}
		} );
		<?php if(!$Mobile){?>
		yadcf.init(Table_Tickets,[
			{   column_number:1,
				filter_type:"auto_complete",
				filter_default_label:"ID"},
			{   column_number:2,
				filter_default_label:"Location"},
			{   column_number:3,
				filter_default_label:"First Name"},
			{   column_number:4,
				filter_default_label:"Last Name"},
			{   column_number:5,
				filter_type: "range_date",
				date_format: "mm/dd/yyyy",
				filter_delay: 500},
			{   column_number:6,
				filter_default_label:"Status"},
			{   column_number:7,
				filter_type: "range_number_slider",
				filter_delay: 500}
		]);
		stylizeYADCF();<?php }?>
		var expandTicketButton = true;
		$("Table#Table_Tickets").on("draw.dt",function(){
			if(!expandTicketButton){$("Table#Table_Tickets tbody tr:not(.shown) td:first-child").each(function(){$(this).click();});} 
			else {$("Table#Table_Tickets tbody tr.shown td:first-child").each(function(){$(this).click();});}
		});
		setTimeout(function(){initialize()},1000);
		$("#yadcf-filter--Table_Tickets-1").attr("size","7");
});
</script>
<?php require(PROJECT_ROOT."js/chart/tickets_this_year_for_customer.php");?>
<?php require(PROJECT_ROOT."js/chart/service_calls_this_year_for_customer.php");?>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=customer<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>