<?php 
session_start();
require('../../../php/index.php');
setlocale(LC_MONETARY, 'en_US');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Elevator'){
    	$My_User = sqlsrv_query($NEI,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID = ?",array($_SESSION['User']));
        $My_User = sqlsrv_fetch_array($My_User); 
        $Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
        $r = sqlsrv_query($Portal,"
            SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
            FROM   Portal.dbo.Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
        $My_Privileges = array();
        while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
        $Privileged = FALSE;
        if(isset($My_Privileges['Job']) && $My_Privileges['Job']['User_Privilege'] >= 4 && $My_Privileges['Job']['Group_Privilege'] >= 4 && $My_Privileges['Job']['Other_Privilege'] >= 4){$Privileged = TRUE;}
        elseif($My_Privileges['Job']['User_Privilege'] >= 4 && is_numeric($_GET['ID'])){
			$a = sqlsrv_query($NEI,"
				SELECT Job.Loc
				FROM nei.dbo.Job
				WHERE Job.ID = ?
			;",array($_GET['ID']));
			$loc = sqlsrv_fetch_array($a)['Loc'];
            $r = sqlsrv_query(  $NEI,"
				SELECT *
				FROM 		nei.dbo.Job
				LEFT JOIN 	nei.dbo.TicketO ON Job.ID = TicketO.Job
				WHERE 		TicketO.LID= ?
					AND 	TicketO.fWork= ?
			;",array($loc,$My_User['fWork']));
            $r2 = sqlsrv_query( $NEI,"
				SELECT *
				FROM 		nei.dbo.Job
				LEFT JOIN 	nei.dbo.TicketD ON Job.ID = TicketD.Job
				WHERE 		TicketD.Loc= ?
							AND TicketD.fWork= ? 
			;",array($loc,$My_User['fWork']));
			$r3 = sqlsrv_query( $NEI,"
				SELECT *
				FROM 		nei.dbo.Job
				LEFT JOIN 	nei.dbo.TicketDArchive ON Job.ID = TicketDArchive.Loc
				WHERE 		TicketDArchive.Loc= ?
							AND TicketDArchive.fWork= ?
			;",array($loc,$My_User['fWork']));
            $r = sqlsrv_fetch_array($r);
            $r2 = sqlsrv_fetch_array($r2);
			$r3 = sqlsrv_fetch_array($r3);
            $Privileged = (is_array($r) || is_array($r2) || is_array($r3)) ? TRUE : FALSE;
		}
    }
    //
    if(!isset($array['ID'])  || !is_numeric($_GET['ID']) || !$Privileged ){require("401.html");}
    else {
       $r = sqlsrv_query($NEI,"
			SELECT TOP 1
                Job.ID                AS Job_ID,
                Job.fDesc             AS Job_Name,
                Job.fDate             AS Job_Start_Date,
                Job.BHour             AS Job_Budgeted_Hours,
                JobType.Type          AS Job_Type,
				Job.Remarks 		  AS Job_Remarks,
                Loc.Loc               AS Location_ID,
                Loc.ID                AS Location_Name,
                Loc.Tag               AS Location_Tag,
                Loc.Address           AS Location_Street,
                Loc.City              AS Location_City,
                Loc.State             AS Location_State,
                Loc.Zip               AS Location_Zip,
                Loc.Route             AS Route,
                Zone.Name             AS Division,
                OwnerWithRol.ID       AS Customer_ID,
                OwnerWithRol.Name     AS Customer_Name,
                OwnerWithRol.Status   AS Customer_Status,
                OwnerWithRol.Elevs    AS Customer_Elevators,
                OwnerWithRol.Address  AS Customer_Street,
                OwnerWithRol.City     AS Customer_City,
                OwnerWithRol.State    AS Customer_State,
                OwnerWithRol.Zip      AS Customer_Zip,
                OwnerWithRol.Contact  AS Customer_Contact,
                OwnerWithRol.Remarks  AS Customer_Remarks,
                OwnerWithRol.Email    AS Customer_Email,
                OwnerWithRol.Cellular AS Customer_Cellular,
                Elev.ID               AS Unit_ID,
                Elev.Unit             AS Unit_Label,
                Elev.State            AS Unit_State,
                Elev.Cat              AS Unit_Category,
                Elev.Type             AS Unit_Type,
                Emp.fFirst            AS Mechanic_First_Name,
                Emp.Last              AS Mechanic_Last_Name,
                Route.ID              AS Route_ID,
				Violation.ID          AS Violation_ID,
				Violation.fdate       AS Violation_Date,
				Violation.Status      AS Violation_Status,
				Violation.Remarks     AS Violation_Remarks
            FROM 
                Job 
                LEFT JOIN nei.dbo.Loc           ON Job.Loc      = Loc.Loc
                LEFT JOIN nei.dbo.Zone          ON Loc.Zone     = Zone.ID
                LEFT JOIN nei.dbo.JobType       ON Job.Type     = JobType.ID
                LEFT JOIN nei.dbo.OwnerWithRol  ON Job.Owner    = OwnerWithRol.ID
                LEFT JOIN nei.dbo.Elev          ON Job.Elev     = Elev.ID
                LEFT JOIN nei.dbo.Route         ON Loc.Route    = Route.ID
                LEFT JOIN nei.dbo.Emp           ON Emp.fWork    = Route.Mech
				LEFT JOIN nei.dbo.Violation     ON Job.ID       = Violation.Job
            WHERE
                Job.ID = ?
        ;",array($_GET['ID']));
        $Job = sqlsrv_fetch_array($r);?>
<div class="panel panel-primary" style='margin-bottom:0px;'>
	<!--<div class='panel-heading' style='color:black;background-color:white;'>-->
	<div class="panel-body">
		<div id="map" style='height:675px;width:100%;'></div>
	</div>
</div>
<script>
$(document).ready(function(){
	$("#loading-sub-pills").removeClass("active");
	$("#operations-map-pills").addClass('active');
	initialize();
});
</script>
<style>
	.border-seperate { 
		border-bottom:3px solid #333333;
	}
</style>
<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=AIzaSyAycrIPh5udy_JLCQHLNlPup915Ro4gPuY"></script>
<script type="text/javascript">
	function initialize() {
	var latlng = new google.maps.LatLng(40.7831, -73.9712);
	var myOptions = {
	  zoom: 10,
	  center: latlng,
	  mapTypeId: google.maps.MapTypeId.ROADMAP
	};
	var map = new google.maps.Map(document.getElementById("map"),
		myOptions);
	var marker = new Array();
	<?php 
	if(isset($_GET['Type'])){
	if($_GET['Type']       == 'Live'){
		$Start_Date            = new DateTime('now');
		$Start_Date            = $Start_Date->format("Y-m-d 00:00:00.000");
		$End_Date              = new DateTime('now');
		$End_Date              = $End_Date->format("Y-m-d 23:59:59.999");
	} elseif($_GET['Type'] == '7D') {
		$Start_Date            = new DateTime('now');
		$Start_Date            = $Start_Date->sub(new DateInterval('P7D'))->format("Y-m-d 00:00:00.000");
		$End_Date              = new DateTime('now');
		$End_Date              = $End_Date->format("Y-m-d 23:59:59.999");
	} elseif($_GET['Type'] == '30D') {
		$Start_Date            = new DateTime('now');
		$Start_Date            = $Start_Date->sub(new DateInterval('P30D'))->format("Y-m-d 00:00:00.000");
		$End_Date              = new DateTime('now');
		$End_Date              = $End_Date->format("Y-m-d 23:59:59.999");
	}
	} else {
	$Start_Date            = new DateTime('now');
	$Start_Date            = $Start_Date->sub(new DateInterval('P365D'))->format("Y-m-d 00:00:00.000");
	$End_Date              = new DateTime('now');
	$End_Date              = $End_Date->format("Y-m-d 23:59:59.999");
	}
	$r = sqlsrv_query($NEI,"
	SELECT GPS.*
	FROM (
		(SELECT 
			TechLocation.*,
			Emp.fFirst AS First_Name,
			Emp.Last   AS Last_Name,
			Emp.fWork,
			Emp.fWork  AS Employee_Work_ID,
			Emp.ID as Employee_ID
		FROM
			nei.dbo.TechLocation
			LEFT JOIN nei.dbo.Emp ON TechLocation.TechID = Emp.fWork
			INNER JOIN nei.dbo.TicketO ON TicketO.ID = TechLocation.TicketID
		WHERE
			TechLocation.DateTimeRecorded >= ?
			AND TechLocation.DateTimeRecorded <= ?
			AND TicketO.LID = ?) 
		UNION ALL 
		(SELECT 
			TechLocation.*,
			Emp.fFirst AS First_Name,
			Emp.Last   AS Last_Name,
			Emp.fWork,
			Emp.fWork  AS Employee_Work_ID,
			Emp.ID as Employee_ID
		FROM
			nei.dbo.TechLocation
			LEFT JOIN nei.dbo.Emp ON TechLocation.TechID = Emp.fWork
			INNER JOIN nei.dbo.TicketD ON TicketD.ID = TechLocation.TicketID
		WHERE
			TechLocation.DateTimeRecorded >= ?
			AND TechLocation.DateTimeRecorded <= ?
			AND TicketD.Loc = ?)
		UNION ALL
		(SELECT 
			TechLocation.*,
			Emp.fFirst AS First_Name,
			Emp.Last   AS Last_Name,
			Emp.fWork,
			Emp.fWork  AS Employee_Work_ID,
			Emp.ID as Employee_ID
		FROM
			nei.dbo.TechLocation
			LEFT JOIN  nei.dbo.Emp ON TechLocation.TechID = Emp.fWork
			INNER JOIN nei.dbo.TicketDArchive ON TicketDArchive.ID = TechLocation.TicketID
		WHERE
			TechLocation.DateTimeRecorded >= ?
			AND TechLocation.DateTimeRecorded <= ?
			AND TicketDArchive.Loc = ?)) AS GPS
	;",array($Start_Date,$End_Date,$_GET['ID'],$Start_Date,$End_Date,$_GET['ID'],$Start_Date,$End_Date,$_GET['ID']));
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
	}?>}
</script>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>