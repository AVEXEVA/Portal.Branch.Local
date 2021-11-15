<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( [ 'read_and_close' => true ] ); 
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    //Connection
    $Connection = $database->query(
        null,
        "   SELECT  Connection.* 
            FROM    Connection 
            WHERE   Connection.Connector = ? 
                    AND Connection.Hash = ?;",
        array(
            $_SESSION['User'],
            $_SESSION['Hash']
        )
    );
    $Connection = sqlsrv_fetch_array($Connection);

    //User
    $User = $database->query(
        null,
        "   SELECT  Emp.*, 
                    Emp.fFirst  AS First_Name, 
                    Emp.Last    AS Last_Name 
            FROM    Emp 
            WHERE   Emp.ID = ?;",
        array(
            $_SESSION['User']
        )
    );
    $User = sqlsrv_fetch_array($User);

    //Privileges
    $r = $database->query(
        null,
        "   SELECT  Privilege.Access_Table, 
                    Privilege.User_Privilege, 
                    Privilege.Group_Privilege, 
                    Privilege.Other_Privilege
            FROM    Privilege
            WHERE   Privilege.User_ID = ?;",
        array(
            $_SESSION[ 'User' ]
        )
    );
    $Privileges = array();
    while($Privilege = sqlsrv_fetch_array($r)){$Privileges[$Privilege['Access_Table']] = $Privilege;}
    $Privileged = FALSE;
    if( isset($Privileges['Location']) 
        && $Privileges['Location']['User_Privilege'] >= 4 
        && $Privileges['Location']['Group_Privilege'] >= 4 
        && $Privileges['Location']['Other_Privilege'] >= 4){$Privileged = TRUE;}
    elseif($Privileges['Location']['User_Privilege'] >= 4 && is_numeric($_GET['ID'])){
        $r = $database->query(  
            null,
            "   SELECT  Count( Ticket.ID ) AS Count 
                FROM    (
                            SELECT  Ticket.ID,
                                    Ticket.Location,
                                    Ticket.Field,
                                    Sum( Ticket.Count ) AS Count
                            FROM (
                                (
                                    SELECT      TicketO.ID,
                                                TicketO.LID AS Location,
                                                TicketO.fWork AS Field,
                                                Count( TicketO.ID ) AS Count
                                    FROM        TicketO
                                    GROUP BY    TicketO.ID,
                                                TicketO.LID,
                                                TicketO.fWork
                                ) UNION ALL (
                                    SELECT      TicketD.ID,
                                                TicketD.Loc AS Location,
                                                TicketD.fWork AS Field, 
                                                Count( TicketD.ID ) AS Count
                                    FROM        TicketD
                                    GROUP BY    TicketD.ID,
                                                TicketD.Loc,
                                                TicketD.fWork
                                )
                            ) AS Ticket
                            GROUP BY    Ticket.ID,
                                        Ticket.Location,
                                        Ticket.Field
                        ) AS Ticket
                        LEFT JOIN Emp AS Employee ON Ticket.Field = Employee.fWork
                WHERE   Employee.ID = ?
                        AND Ticket.Location = ?;",
            array( 
                $_SESSION[ 'User' ],
                $_GET[ 'ID' ]
            )
        );
        $Tickets = 0;
        if ( $r ){ $Tickets = sqlsrv_fetch_array( $r )[ 'Count' ]; }
        $Privileged =  $Tickets > 0 ? true : false;
    }
    if(     !isset( $Connection[ 'ID' ] )  
        ||  !$Privileged 
        ||  !is_numeric( $_GET[ 'ID' ] ) ){
            ?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {
        $database->query(
            null,
            "   INSERT INTO Activity([User], [Date], [Page]) 
                VALUES(?,?,?);",
            array(
                $_SESSION['User'],
                date('Y-m-d H:i:s'), 
                'location-feed.php?ID=' . $_GET[ 'ID' ]
            )
        );
        $ID = $_GET['ID'];
        $r = $database->query(null,
            "SELECT TOP 1
                    Loc.Loc              AS Location_ID,
                    Loc.ID               AS Name,
                    Loc.Tag              AS Tag,
                    Loc.Address          AS Street,
                    Loc.City             AS City,
                    Loc.State            AS State,
                    Loc.Zip              AS Zip,
                    Loc.Balance          as Location_Balance,
                    Zone.Name            AS Zone,
                    Loc.Route            AS Route_ID,
                    Emp.ID               AS Route_Mechanic_ID,
                    Emp.fFirst           AS Route_Mechanic_First_Name,
                    Emp.Last             AS Route_Mechanic_Last_Name,
                    Loc.Owner            AS Customer_ID,
                    OwnerWithRol.Name    AS Customer_Name,
                    OwnerWithRol.Balance AS Customer_Balance,
                    Terr.Name            AS Territory_Domain/*,
                    Sum(SELECT Location.ID FROM Loc AS Location WHERE Location.Owner='Loc.Owner') AS Customer_Locations*/
            FROM    Loc
                    LEFT JOIN Zone         ON Loc.Zone   = Zone.ID
                    LEFT JOIN Route        ON Loc.Route  = Route.ID
                    LEFT JOIN Emp          ON Route.Mech = Emp.fWork
                    LEFT JOIN OwnerWithRol ON Loc.Owner  = OwnerWithRol.ID
                    LEFT JOIN Terr         		   ON Terr.ID    = Loc.Terr
            WHERE
                    Loc.Loc = ?
        ;",array($_GET['ID']));
        $Location = sqlsrv_fetch_array($r);
        $data = $Location;?>
<div class="panel panel-primary" style='margin-bottom:0px;'>
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
			TechLocation
			LEFT JOIN Emp ON TechLocation.TechID = Emp.fWork
			INNER JOIN TicketD ON TicketD.ID = TechLocation.TicketID
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
			TechLocation
			LEFT JOIN  Emp ON TechLocation.TechID = Emp.fWork
			INNER JOIN TicketDArchive ON TicketDArchive.ID = TechLocation.TicketID
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
