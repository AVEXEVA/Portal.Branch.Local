<?php
session_start( [ 'read_and_close' => true ] );
require('../../../php/index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = $database->query(null,"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Texas'){
        $database->query(null,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "unit.php"));
        $r= $database->query(null,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID= ?",array($_SESSION['User']));
        $My_User = sqlsrv_fetch_array($r);
        $Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
        $r = $database->query(null,"
            SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
            FROM   Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
        $My_Privileges = array();
        while($My_Privilege = sqlsrv_fetch_array($r)){$My_Privileges[$My_Privilege['Access_Table']] = $My_Privilege;}
        $Privileged = FALSE;
        if(isset($My_Privileges['Unit']) && $My_Privileges['Unit']['User_Privilege'] >= 4 && $My_Privileges['Unit']['Group_Privilege'] >= 4 && $My_Privileges['Unit']['Other_Privilege'] >= 4){$Privileged = TRUE;}
        elseif(isset($My_Privileges['Unit']) && $My_Privileges['Unit']['User_Privilege'] >= 4 && $My_Privileges['Unit']['Group_Privilege'] >= 4){
			$r = $database->query(null,"
				SELECT Elev.Loc AS Location_ID
				FROM   Elev
				WHERE  Elev.ID = ?
			;",array($_GET['ID'] ));
			$Location_ID = sqlsrv_fetch_array($r)['Location_ID'];
            $r = $database->query(null,"
			SELECT Tickets.*
			FROM 
			(
				(
					SELECT TicketO.ID
					FROM   TicketO 
						   LEFT JOIN Loc  ON TicketO.LID   = Loc.Loc
						   LEFT JOIN Elev ON Loc.Loc       = Elev.Loc
						   LEFT JOIN Emp  ON TicketO.fWork = Emp.fWork
					WHERE  Emp.ID      = ?
						   AND Loc.Loc = ?
				)
				UNION ALL
				(
					SELECT TicketD.ID
					FROM   TicketD 
						   LEFT JOIN Loc  ON TicketD.Loc   = Loc.Loc
						   LEFT JOIN Elev ON Loc.Loc       = Elev.Loc
						   LEFT JOIN Emp  ON TicketD.fWork = Emp.fWork
					WHERE  Emp.ID      = ?
						   AND Loc.Loc = ?
				)

			) AS Tickets
           	;", array($_SESSION['User'], $Location_ID, $_SESSION['User'], $Location_ID, $_SESSION['User'], $Location_ID));
            $r = sqlsrv_fetch_array($r);
            $Privileged = is_array($r) ? TRUE : FALSE;
        }
    }
    if(!isset($array['ID'])  || !$Privileged || !is_numeric($_GET['ID'])){?><html><head><script>document.location.href="../login.php?Forward=unit<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {
        if(count($_POST) > 0){
            fixArrayKey($_POST);
            foreach($_POST as $key=>$value){
				if($key == 'Price'){continue;}
				if($key == 'Type'){continue;}
                $database->query(null,"
                    UPDATE ElevTItem
                    SET    ElevTItem.Value     = ?
                    WHERE  ElevTItem.Elev      = ?
                           AND ElevTItem.ElevT = 1
                           AND ElevTItem.fDesc = ?
                ;",array($value,$_GET['ID'],$key));
            }
			if(isset($_POST['Price'])){
				$database->query(null,"
					UPDATE Elev
					SET    Elev.Price = ?
					WHERE  Elev.ID    = ?
				;",array($_POST['Price'],$_GET['ID']));
			}
			if(isset($_POST['Type'])){
				$database->query(null,"
					UPDATE Elev
					SET    Elev.Type = ?
					WHERE  Elev.ID    = ?
				;",array($_POST['Type'],$_GET['ID']));
			}
        }
        $r = $database->query(null,
            "SELECT TOP 1
                Elev.ID,
                Elev.Unit           AS Unit,
                Elev.State          AS State,
                Elev.Cat            AS Category,
                Elev.Type           AS Type,
                Elev.Building       AS Building,
                Elev.Since          AS Since,
                Elev.Last           AS Last,
                Elev.Price          AS Price,
                Elev.fDesc          AS Description,
                Loc.Loc             AS Location_ID,
                Loc.ID              AS Name,
                Loc.Tag             AS Tag,
                Loc.Tag             AS Location_Tag,
                Loc.Address         AS Street,
                Loc.City            AS City,
                Loc.State           AS Location_State,
                Loc.Zip             AS Zip,
                Loc.Route           AS Route,
                Zone.Name           AS Zone,
                OwnerWithRol.Name   AS Customer_Name,
                OwnerWithRol.ID     AS Customer_ID,
                Emp.ID AS Route_Mechanic_ID,
                Emp.fFirst AS Route_Mechanic_First_Name,
                Emp.Last AS Route_Mechanic_Last_Name
            FROM 
                Elev
                LEFT JOIN Loc           ON Elev.Loc = Loc.Loc
                LEFT JOIN Zone          ON Loc.Zone = Zone.ID
                LEFT JOIN OwnerWithRol  ON Loc.Owner = OwnerWithRol.ID
                LEFT JOIN Route ON Loc.Route = Route.ID
                LEFT JOIN Emp ON Route.Mech = Emp.fWork
            WHERE
                Elev.ID = ?
		;",array($_GET['ID']));
        $Unit = sqlsrv_fetch_array($r);
        $unit = $Unit;
        $data = $Unit;
        $r2 = $database->query(null,"
            SELECT *
            FROM   ElevTItem
            WHERE  ElevTItem.ElevT    = 1
                   AND ElevTItem.Elev = ?
        ;",array($_GET['ID']));
        if($r2){while($array = sqlsrv_fetch_array($r2)){$Unit[$array['fDesc']] = $array['Value'];}}
?>
	<div class="panel panel-primary" style='margin-bottom:0px;'>
			<!--<button onClick="document.location.href='map.php?Type=Live';">Live View</button>
			<button onClick="document.location.href='map.php?Type=1D';">7 Day View</button>
			<button onClick="document.location.href='map.php?Type=2D';">30 Day View</button></div>-->
		<div class="panel-body">
			<div id="map" style='height:675px;width:100%;'></div>
		</div>
	</div>
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
$r = $database->query(null,"
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
			TechLocation
			LEFT JOIN Emp ON TechLocation.TechID = Emp.fWork
			INNER JOIN TicketO ON TicketO.ID = TechLocation.TicketID
		WHERE
			TechLocation.DateTimeRecorded >= ?
			AND TechLocation.DateTimeRecorded <= ?
			AND TicketO.LElev = ?) 
		UNION ALL 
		(SELECT 
			TechLocation.*,
			Emp.fFirst AS First_Name,
			Emp.Last   AS Last_Name,
			Emp.fWork,
			Emp.fWork  AS Employee_Work_ID,
			Emp.ID as Employee_ID
		FROM
			TechLocation
			LEFT JOIN Emp ON TechLocation.TechID = Emp.fWork
			INNER JOIN TicketD ON TicketD.ID = TechLocation.TicketID
		WHERE
			TechLocation.DateTimeRecorded >= ?
			AND TechLocation.DateTimeRecorded <= ?
			AND TicketD.Elev = ?)
		UNION ALL
		(SELECT 
			TechLocation.*,
			Emp.fFirst AS First_Name,
			Emp.Last   AS Last_Name,
			Emp.fWork,
			Emp.fWork  AS Employee_Work_ID,
			Emp.ID as Employee_ID
		FROM
			TechLocation
			LEFT JOIN  Emp ON TechLocation.TechID = Emp.fWork
			INNER JOIN TicketDArchive ON TicketDArchive.ID = TechLocation.TicketID
		WHERE
			TechLocation.DateTimeRecorded >= ?
			AND TechLocation.DateTimeRecorded <= ?
			AND TicketDArchive.Elev = ?)) AS GPS
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
}?>}</script>
	<script>
	$(document).ready(function(){
		$("#loading-sub-pills").removeClass("active");
		$("#operations-map-pills").addClass('active');
		initialize();
	});
	</script>
</div>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>