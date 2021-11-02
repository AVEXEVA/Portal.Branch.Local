<?php
if(session_id() == '' || !isset($_SESSION) ){
  session_start();
  require('/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/cgi-bin/php/index.php');
}
if(TRUE){
  require('map5.php');
} else {
$_GET['Type'] = isset($_GET['Type']) ? $_GET['Type'] : 'Live';
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
	   	|| !isset($My_Privileges['Map'])
	  		|| $My_Privileges['Map']['User_Privilege']  < 4
	  		|| $My_Privileges['Map']['Group_Privilege'] < 4
	  	    || $My_Privileges['Map']['Other_Privilege'] < 4){
				?><?php require('../404.html');?><?php }
    else {
		sqlsrv_query($NEI,"
			INSERT INTO Portal.dbo.Activity([User], [Date], [Page])
			VALUES(?,?,?)
		;",array($_SESSION['User'],date("Y-m-d H:i:s"), "map.php"));
?><!DOCTYPE html>
<html lang="en">
<head>
    <?php require(PROJECT_ROOT.'php/meta.php');?>
    <title>Nouveau Texas | Portal</title>
    <?php require(PROJECT_ROOT."css/index.php");?>
    <?php require(PROJECT_ROOT.'js/index.php');?>
</head>
<body onload='finishLoadingPage();'>
  <div id='container'>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require(PROJECT_ROOT.'php/element/navigation/index2.php');?>
        <?php require(PROJECT_ROOT.'php/element/loading.php');?>
        <div id="page-wrapper" class='content' style='margin-right:0px !important;'>
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-primary">
                        <div class="panel-heading"><h3>Map</h3></div>
                        <div class='panel-heading'>
                          <div class='row'>
                            <div class='col-xs-1' style='background-color:white;color:black;' onClick='clearMarkers();'>Toggle</div>
                            <div class='col-xs-1' onClick='showDivision1();' style='background-color:magenta;color:black;'>Division #1</div>
                            <div class='col-xs-1' onClick='showDivision2();' style='background-color:green;color:white;'>Division #2</div>
                            <div class='col-xs-1' onClick='showDivision3();' style='background-color:blue;color:white;'>Division #3</div>
                            <div class='col-xs-1' onClick='showDivision4();' style='background-color:teal;color:white;'>Division #4</div>
                            <div class='col-xs-1' onClick='showModernization();' style='background-color:black;color:white;'>Modernization</div>
                            <div class='col-xs-1' onClick='showEscalator();' style='background-color:brown;color:white;'>Escalator</div>
                            <div class='col-xs-1' onClick='showFiremen();' style='background-color:red;color:white;'>Firemen</div>
                            <div class='col-xs-1' onClick='showRepair();' style='background-color:purple;color:white;'>Repair</div>
                            <div class='col-xs-1' onClick='showTesting();' style='background-color:orange;color:black;'>Testing</div>
                          </div>
                          <div class='row'>
                            <div class='col-xs-1' style='background-color:white;color:black;' onClick='clearMarkers();'>&nbsp;</div>
                            <div class='col-xs-1' onClick='showDivision1();' style='background-color:magenta;color:black;'><?php
                              $r = sqlsrv_query($NEI,
                                " SELECT  Count(*) AS Count
                                  FROM    nei.dbo.TicketO
                                          LEFT JOIN Emp ON TicketO.fWork = Emp.fWork
                                          LEFT JOIN nei.dbo.tblWork ON 'A' + convert(varchar(10),Emp.ID) + ',' = tblWork.Members
                                  WHERE   TicketO.Assigned = 3
                                          AND tblWork.Super LIKE '%DIVISION 1%'
                                ;");
                              $row =  $r ? sqlsrv_fetch_array($r) : null;
                              echo is_array($row) ? $row['Count'] : null;
                            ?> personnel</div>
                            <div class='col-xs-1' onClick='showDivision2();' style='background-color:green;color:white;'><?php
                              $r = sqlsrv_query($NEI,
                                " SELECT  Count(*) AS Count
                                  FROM    nei.dbo.TicketO
                                          LEFT JOIN Emp ON TicketO.fWork = Emp.fWork
                                          LEFT JOIN nei.dbo.tblWork ON 'A' + convert(varchar(10),Emp.ID) + ',' = tblWork.Members
                                  WHERE   TicketO.Assigned = 3
                                          AND tblWork.Super LIKE '%DIVISION 2%'
                                ;");
                              $row =  $r ? sqlsrv_fetch_array($r) : null;
                              echo is_array($row) ? $row['Count'] : null;
                            ?> personnel</div>
                            <div class='col-xs-1' onClick='showDivision3();' style='background-color:blue;color:white;'><?php
                              $r = sqlsrv_query($NEI,
                                " SELECT  Count(*) AS Count
                                  FROM    nei.dbo.TicketO
                                          LEFT JOIN Emp ON TicketO.fWork = Emp.fWork
                                          LEFT JOIN nei.dbo.tblWork ON 'A' + convert(varchar(10),Emp.ID) + ',' = tblWork.Members
                                  WHERE   TicketO.Assigned = 3
                                          AND tblWork.Super LIKE '%DIVISION 3%'
                                ;");
                              $row =  $r ? sqlsrv_fetch_array($r) : null;
                              echo is_array($row) ? $row['Count'] : null;
                            ?> personnel</div>
                            <div class='col-xs-1' onClick='showDivision4();' style='background-color:teal;color:white;'><?php
                              $r = sqlsrv_query($NEI,
                                " SELECT  Count(*) AS Count
                                  FROM    nei.dbo.TicketO
                                          LEFT JOIN Emp ON TicketO.fWork = Emp.fWork
                                          LEFT JOIN nei.dbo.tblWork ON 'A' + convert(varchar(10),Emp.ID) + ',' = tblWork.Members
                                  WHERE   TicketO.Assigned = 3
                                          AND tblWork.Super LIKE '%DIVISION 4%'
                                ;");
                              $row =  $r ? sqlsrv_fetch_array($r) : null;
                              echo is_array($row) ? $row['Count'] : null;
                            ?> personnel</div>
                            <div class='col-xs-1' onClick='showModernization();' style='background-color:black;color:white;'><?php
                              $r = sqlsrv_query($NEI,
                                " SELECT  Count(*) AS Count
                                  FROM    nei.dbo.TicketO
                                          LEFT JOIN Emp ON TicketO.fWork = Emp.fWork
                                          LEFT JOIN nei.dbo.tblWork ON 'A' + convert(varchar(10),Emp.ID) + ',' = tblWork.Members
                                  WHERE   TicketO.Assigned = 3
                                          AND tblWork.Super LIKE '%Modernization%'
                                ;");
                              $row =  $r ? sqlsrv_fetch_array($r) : null;
                              echo is_array($row) ? $row['Count'] : null;
                            ?> personnel</div>
                            <div class='col-xs-1' onClick='showEscalator();' style='background-color:brown;color:white;'><?php
                              $r = sqlsrv_query($NEI,
                                " SELECT  Count(*) AS Count
                                  FROM    nei.dbo.TicketO
                                          LEFT JOIN Emp ON TicketO.fWork = Emp.fWork
                                          LEFT JOIN nei.dbo.tblWork ON 'A' + convert(varchar(10),Emp.ID) + ',' = tblWork.Members
                                  WHERE   TicketO.Assigned = 3
                                          AND tblWork.Super LIKE '%Escalator%'
                                ;");
                              $row =  $r ? sqlsrv_fetch_array($r) : null;
                              echo is_array($row) ? $row['Count'] : null;
                            ?> personnel</div>
                            <div class='col-xs-1' onClick='showFiremen();' style='background-color:red;color:white;'><?php
                              $r = sqlsrv_query($NEI,
                                " SELECT  Count(*) AS Count
                                  FROM    nei.dbo.TicketO
                                          LEFT JOIN Emp ON TicketO.fWork = Emp.fWork
                                          LEFT JOIN nei.dbo.tblWork ON 'A' + convert(varchar(10),Emp.ID) + ',' = tblWork.Members
                                  WHERE   TicketO.Assigned = 3
                                          AND tblWork.Super LIKE '%Firemen%'
                                ;");
                              $row =  $r ? sqlsrv_fetch_array($r) : null;
                              echo is_array($row) ? $row['Count'] : null;
                            ?> personnel</div>
                            <div class='col-xs-1' onClick='showRepair();' style='background-color:purple;color:white;'><?php
                              $r = sqlsrv_query($NEI,
                                " SELECT  Count(*) AS Count
                                  FROM    nei.dbo.TicketO
                                          LEFT JOIN Emp ON TicketO.fWork = Emp.fWork
                                          LEFT JOIN nei.dbo.tblWork ON 'A' + convert(varchar(10),Emp.ID) + ',' = tblWork.Members
                                  WHERE   TicketO.Assigned = 3
                                          AND tblWork.Super LIKE '%Repair%'
                                ;");
                              $row =  $r ? sqlsrv_fetch_array($r) : null;
                              echo is_array($row) ? $row['Count'] : null;
                            ?> personnel</div>
                            <div class='col-xs-1' onClick='showTesting();' style='background-color:orange;color:black;'><?php
                              $r = sqlsrv_query($NEI,
                                " SELECT  Count(*) AS Count
                                  FROM    nei.dbo.TicketO
                                          LEFT JOIN Emp ON TicketO.fWork = Emp.fWork
                                          LEFT JOIN nei.dbo.tblWork ON 'A' + convert(varchar(10),Emp.ID) + ',' = tblWork.Members
                                  WHERE   TicketO.Assigned = 3
                                          AND tblWork.Super LIKE '%Testing%'
                                ;");
                              $row =  $r ? sqlsrv_fetch_array($r) : null;
                              echo is_array($row) ? $row['Count'] : null;
                            ?> personnel</div>
                          </div>
                        </div>
                        <div class='panel-heading'>
                          <div class='row'>
                            <div class='col-xs-3'><button onClick="codeAddress(prompt('What address would you like to center on?'));" style='width:100%;color:black;'>Center on Address</button></div>
                            <div class='col-xs-3'><select name='Employee' style='color:black !important;' onChange='zoomUser(this);'>
                              <option value=''>Select</option>
                              <?php $r = sqlsrv_query($NEI,"SELECT * FROM Emp WHERE Emp.Status = 0 ORDER BY Emp.Last, Emp.fFirst ASC;");
                              if($r){while($row = sqlsrv_fetch_array($r)){
                                ?><option value='<?php echo $row['ID'];?>'><?php echo $row['Last'] . ', ' . $row['fFirst'];?></option><?php
                              }}?>
                            </select></div>
                          </div>
                        </div>

                        <div class='panel-heading' style='color:black;background-color:white;'><button onClick="document.location.href='map.php?Type=Live';">Live View</button><button onClick="document.location.href='map.php?Type=1D';">24 Hour View</button><button onClick="document.location.href='map.php?Type=2D';">48 Hour View</button></div>
                        <div class="panel-body"><div id="map" style='height:675px;width:100%;'></div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap Core JavaScript -->
    <script src="https://www.nouveauelevator.com/vendor/bootstrap/js/bootstrap.min.js"></script>

    <!-- JQUERY UI Javascript -->
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?sensor=false&key=AIzaSyBzxfjkN8x4t6TcuynQhk3cfo2AkXmHGiY"></script>
    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/geocode/json?key=AIzaSyBzxfjkN8x4t6TcuynQhk3cfo2AkXmHGiY"></script>
  	<!-- Map Icons -->
  	<link rel="stylesheet" type="text/css" href="cgi-bin/libraries/map-icons-master/dist/css/map-icons.css">
  	<script type="text/javascript" src="cgi-bin/libraries/map-icons-master/dist/js/map-icons.js"></script>
    <style>
    .map-icon-label .map-icon {
      font-size: 24px;
      color: #000;
      line-height: 48px;
      text-align: center;
      white-space: nowrap;
      padding:0px;
      margin:0px;
      }
      .map-icon-label .map-icon.Division1 {color:magenta !important;}
      .map-icon-label .map-icon.Division2 {color:green !important;}
      .map-icon-label .map-icon.Division3 {color:blue !important;}
      .map-icon-label .map-icon.Division4 {color:teal !important;}
      .map-icon-label .map-icon.Modernization {color:black !important;}
      .map-icon-label .map-icon.Escalator {color:brown !important;}
      .map-icon-label .map-icon.Firemen {color:red !important;}
      .map-icon-label .map-icon.Repair {color:purple !important;}
      .map-icon-label .map-icon.Testing {color:orange !important;}
      .map-icon-label .map-icon.map-icon-location {color:black !important;}
    </style>
    <script type="text/javascript">
    var latlng = new google.maps.LatLng(40.7831, -73.9712);
    var myOptions = {
      zoom: 10,
      center: latlng,
      mapTypeId: google.maps.MapTypeId.ROADMAP
    };
    var marker = new Array();
    var marker_set_Division1 = new Array();
    var marker_set_Division2 = new Array();
    var marker_set_Division3 = new Array();
    var marker_set_Division4 = new Array();
    var marker_set_Firemen = new Array();
    var marker_set_Repair = new Array();
    var marker_set_Escalator = new Array();
    var marker_set_Testing = new Array();
    var marker_set_Modernization = new Array();
    function showModernization(){for (i in marker){marker[i].setMap(marker_set_Modernization.includes(i) ? map : null);}}
    function showDivision1(){for (i in marker){marker[i].setMap(marker_set_Division1.includes(i) ? map : null);}}
    function showDivision2(){for (i in marker){marker[i].setMap(marker_set_Division2.includes(i) ? map : null);}}
    function showDivision3(){for (i in marker){marker[i].setMap(marker_set_Division3.includes(i) ? map : null);}}
    function showDivision4(){for (i in marker){marker[i].setMap(marker_set_Division4.includes(i) ? map : null);}}
    function showFiremen(){for (i in marker){marker[i].setMap(marker_set_Firemen.includes(i) ? map : null);}}
    function showTesting(){for (i in marker){marker[i].setMap(marker_set_Testing.includes(i) ? map : null);}}
    function showEscalator(){for (i in marker){marker[i].setMap(marker_set_Escalator.includes(i) ? map : null);}}
    function showRepair(){for (i in marker){marker[i].setMap(marker_set_Repair.includes(i) ? map : null);}}
    var map = new google.maps.Map(document.getElementById("map"),
        myOptions);
          function initialize() {



        <?php
        if($_GET['Type']       == 'Live'){
            $Start_Date            = date("Y-m-d H:i:s",strtotime('-1 week'));
            $End_Date              = date("Y-m-d 23:59:59.999",strtotime('now'));
        } elseif($_GET['Type'] == '1D') {
            $Start_Date            = new DateTime('now');
            $Start_Date            = $Start_Date->sub(new DateInterval('P1D'))->format("Y-m-d 00:00:00.000");
            $End_Date              = new DateTime('now');
            $End_Date              = $End_Date->format("Y-m-d 23:59:59.999");
        } elseif($_GET['Type'] == '2D') {
            $Start_Date            = new DateTime('now');
            $Start_Date            = $Start_Date->sub(new DateInterval('P2D'))->format("Y-m-d 00:00:00.000");
            $End_Date              = new DateTime('now');
            $End_Date              = $End_Date->format("Y-m-d 23:59:59.999");
        }
        $r = sqlsrv_query($NEI,
        "   SELECT
                TechLocation.*,
                Emp.fFirst AS First_Name,
                Emp.Last   AS Last_Name,
                Emp.fWork,
				        Emp.fWork  AS Employee_Work_ID,
                Emp.ID as Employee_ID,
                tblWork.Super AS Super,
                TicketO.TimeSite AS TimeSite
            FROM
                TechLocation
                LEFT JOIN Emp ON TechLocation.TechID = Emp.fWork
                LEFT JOIN nei.dbo.tblWork ON 'A' + convert(varchar(10),Emp.ID) + ',' = tblWork.Members
                LEFT JOIN nei.dbo.TicketO ON TechLocation.TicketID = TicketO.ID
            WHERE
                DateTimeRecorded >= ?
                AND DateTimeRecorded <= ?
        ;",array($Start_Date,$End_Date));
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
            $Elapsed = round((strtotime(date("H:i:s")) - strtotime(date("H:i:s",strtotime($GPS_Location['TimeSite'])))) / (60*60),2);
            $colors = array(
              'Division 1'=>'yellow',
              'Division 2'=>'green',
              'Division 3'=>'blue',
              'Division 4'=>'lightblue',
              'Firemen' => 'red',
              'Repair'=>'purple',
              'Testing'=>'orange',
              'Modernization'=>'black',
              'Escalator'=>'brown'
            );
            if(isset($array['On site time'])){
                $GPS_Location = $array['On site time'];
                $r = sqlsrv_query($Portal,
                  "SELECT Max(Phone_GPS.[Timestamp]) AS TimeStamp,
                          Phone_GPS.ID AS ID,
                          Phone_GPS.Latitude AS Latitude,
                          Phone_GPS.Longitude AS Longitude,
                          Phone_GPS.Phone AS IMEI
                   FROM   Portal.dbo.Phone_GPS
                          LEFT JOIN Portal.dbo.Employee_Phone ON Phone_GPS.Phone = Employee_Phone.IMEI
                   WHERE  Employee_Phone.Employee_ID = ?
                   GROUP BY Phone_GPS.ID, Phone_GPS.Latitude, Phone_GPS.Longitude, Phone_GPS.[TimeStamp], Phone_GPS.Phone
                   ORDER BY Phone_GPS.[TimeStamp] DESC
                  ;",array($GPS_Location['Employee_ID']));
                if($r){
                  $row = sqlsrv_fetch_array($r);
                  if(is_array($row)){
                    $GPS_Location['Latitude'] = $row['Latitude'];
                    $GPS_Location['Longitude'] = $row['Longitude'];
                    $GPS_Location['DateTimeRecorded'] = $row['TimeStamp'];
                  }
                }
                ?>
                marker[<?php echo $key;?>] = new mapIcons.Marker({
                  map: map,
                  position: new google.maps.LatLng(<?php echo $GPS_Location['Latitude'];?>,<?php echo $GPS_Location['Longitude'];?>),
                  title: '<?php echo $GPS_Location['First_Name'] . " " . $GPS_Location['Last_Name'];?> -- <?php echo date("m/d/Y H:i:s",strtotime($GPS_Location['DateTimeRecorded']));?> -- <?php echo $GPS_Location['TicketID'];?>',
                  icon: {
                    path:mapIcons.shapes.SQUARE_PIN,
                    fillColor:'#00CCBB',
                    fillOpacity:0,
                    strokeColor:'<?php echo $colors[$GPS_Location['Super']];?>',
                    strokeWeight:0
                  },
                  id:"<?php echo $GPS_Location['Employee_ID'];?>",
                  map_icon_label:'<span class="map-icon map-icon-walking <?php echo str_replace(' ','',ucwords(strtolower($GPS_Location['Super'])));?>"></span>'
                });
                marker[<?php echo $key;?>].addListener('click', function() {
                    $.ajax({
                      url:"get_ticket_popup.php",
                      method:"GET",
                      data:{ 'ID' : '<?php echo $GPS_Location['TicketID'];?>'},
                      success:function(code){
                        $("body").append(code);
                      }
                  });
                });
                <?php if(in_array(ucwords(strtolower($GPS_Location['Super'])),array('Division 1','Division 2', 'Division 3', 'Division 4', 'Modernization', 'Firemen', 'Testing', 'Repair','Escalator'))){?>
                  marker_set_<?php echo str_replace(' ','',ucwords(strtolower($GPS_Location['Super'])));?>.push('<?php echo $key;?>');
                <?php }?>
            <?php }
            if(isset($array['Completed time'])){
                $GPS_Location = $array['Completed time'];
                ?>
                marker[<?php echo $key;?>] = new google.maps.Marker({
                  position: {lat:<?php echo $GPS_Location['Latitude'];?>,lng:<?php echo $GPS_Location['Longitude'];?>},
                  map: map,
                  title: '<?php echo $GPS_Location['First_Name'] . " " . $GPS_Location['Last_Name'];?> -- <?php echo date("m/d/Y H:i:s",strtotime($GPS_Location['DateTimeRecorded']));?> -- <?php echo $GPS_Location['TicketID'];?>',
                  Icon: 'https://vps9073.inmotionhosting.com/~skeera6/portal/images/GoogleMapsMarkers/<?php if($GPS_Location['ActionGroup'] == 'Completed time'){?>green_MarkerC<?php }
                  elseif($GPS_Location['ActionGroup'] == 'On site time'){?>yellow_MarkerO<?php }
                elseif($GPS_Location['ActionGroup'] == 'General'){?>paleblue_MarkerG<?php }?>.png'
                });
                marker[<?php echo $key;?>].addListener('click', function() {
                    $.ajax({
                      url:"get_ticket_popup.php",
                      method:"GET",
                      data:{ 'ID' : '<?php echo $GPS_Location['TicketID'];?>'},
                      success:function(code){
                        $("body").append(code);
                      }
                  });
                });
            <?php }
        }?>}
        function zoomUser(link){
          var val = $(link).val();
          for ( i in marker ){
            if(marker[i].id == val){
              var latlng = new google.maps.LatLng(marker[i].getPosition().lat(), marker[i].getPosition().lng());
              var myOptions = {
                  zoom: 17,
                  center: latlng,
                  mapTypeId: google.maps.MapTypeId.ROADMAP
              }
              map = new google.maps.Map(document.getElementById("map"), myOptions);
              <?php foreach($GPS as $key=>$array){
                $Elapsed = round((strtotime(date("H:i:s")) - strtotime(date("H:i:s",strtotime($GPS_Location['TimeSite'])))) / (60*60),2);
                $colors = array(
                  'Division 1'=>'yellow',
                  'Division 2'=>'green',
                  'Division 3'=>'blue',
                  'Division 4'=>'lightblue',
                  'Firemen' => 'red',
                  'Repair'=>'purple',
                  'Testing'=>'orange',
                  'Modernization'=>'pink',
                  'Escalator'=>'pink'
                );
                  if($_GET['Type'] == 'Live' && isset($array['Completed time'])){continue;}
                  if($key == "General"){continue;}
                  if(isset($array['On site time'])){
                      $GPS_Location = $array['On site time'];
                      $r = sqlsrv_query($Portal,
                        "SELECT Max(Phone_GPS.[Timestamp]) AS TimeStamp,
                                Phone_GPS.ID AS ID,
                                Phone_GPS.Latitude AS Latitude,
                                Phone_GPS.Longitude AS Longitude,
                                Phone_GPS.Phone AS IMEI
                         FROM   Portal.dbo.Phone_GPS
                                LEFT JOIN Portal.dbo.Employee_Phone ON Phone_GPS.Phone = Employee_Phone.IMEI
                         WHERE  Employee_Phone.Employee_ID = ?
                         GROUP BY Phone_GPS.ID, Phone_GPS.Latitude, Phone_GPS.Longitude, Phone_GPS.[TimeStamp], Phone_GPS.Phone
                         ORDER BY Phone_GPS.[TimeStamp] DESC
                        ;",array($GPS_Location['Employee_ID']));
                      if($r){
                        $row = sqlsrv_fetch_array($r);
                        if(is_array($row)){
                          $GPS_Location['Latitude'] = $row['Latitude'];
                          $GPS_Location['Longitude'] = $row['Longitude'];
                          $GPS_Location['DateTimeRecorded'] = $row['TimeStamp'];
                        }
                      }
                      ?>
                      marker[<?php echo $key;?>] = new mapIcons.Marker({
                        map: map,
                        position: new google.maps.LatLng(<?php echo $GPS_Location['Latitude'];?>,<?php echo $GPS_Location['Longitude'];?>),
                        title: '<?php echo $GPS_Location['First_Name'] . " " . $GPS_Location['Last_Name'];?> -- <?php echo date("m/d/Y H:i:s",strtotime($GPS_Location['DateTimeRecorded']));?> -- <?php echo $GPS_Location['TicketID'];?>',
                        icon: {
                          path:mapIcons.shapes.SQUARE_PIN,
                          fillColor:'#00CCBB',
                          fillOpacity:0,
                          strokeColor:'',
                          strokeWeight:0
                        },
                        id:"<?php echo $GPS_Location['Employee_ID'];?>",
                        map_icon_label:'<span class="map-icon map-icon-walking <?php echo str_replace(' ','',ucwords(strtolower($GPS_Location['Super'])));?>"></span>'
                      });
                      marker[<?php echo $key;?>].addListener('click', function() {
                        $.ajax({
                          url:"get_ticket_popup.php",
                          method:"GET",
                          data:{ 'ID' : '<?php echo $GPS_Location['TicketID'];?>'},
                          success:function(code){$("body").append(code);}
                        });
                      });
                  <?php }
                }?>
            }
          }
        }
        function codeAddress(address) {
            geocoder = new google.maps.Geocoder();
            geocoder.geocode({
                'address': address
            }, function(results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    var myOptions = {
                        zoom: 17,
                        center: results[0].geometry.location,
                        mapTypeId: google.maps.MapTypeId.ROADMAP
                    }
                    map = new google.maps.Map(document.getElementById("map"), myOptions);


                    marker[0] = new google.maps.Marker({
                        map: map,
                        position: results[0].geometry.location
                    });
                    <?php foreach($GPS as $key=>$array){
                      $Elapsed = round((strtotime(date("H:i:s")) - strtotime(date("H:i:s",strtotime($GPS_Location['TimeSite'])))) / (60*60),2);
                      $colors = array(
                        'Division 1'=>'yellow',
                        'Division 2'=>'green',
                        'Division 3'=>'blue',
                        'Division 4'=>'lightblue',
                        'Firemen' => 'red',
                        'Repair'=>'purple',
                        'Testing'=>'orange',
                        'Modernization'=>'pink',
                        'Escalator'=>'pink'
                      );
                        if($_GET['Type'] == 'Live' && isset($array['Completed time'])){continue;}
                        if($key == "General"){continue;}
                        if(isset($array['On site time'])){
                            $GPS_Location = $array['On site time'];
                            $r = sqlsrv_query($Portal,
                              "SELECT Max(Phone_GPS.[Timestamp]) AS TimeStamp,
                                      Phone_GPS.ID AS ID,
                                      Phone_GPS.Latitude AS Latitude,
                                      Phone_GPS.Longitude AS Longitude,
                                      Phone_GPS.Phone AS IMEI
                               FROM   Portal.dbo.Phone_GPS
                                      LEFT JOIN Portal.dbo.Employee_Phone ON Phone_GPS.Phone = Employee_Phone.IMEI
                               WHERE  Employee_Phone.Employee_ID = ?
                               GROUP BY Phone_GPS.ID, Phone_GPS.Latitude, Phone_GPS.Longitude, Phone_GPS.[TimeStamp], Phone_GPS.Phone
                               ORDER BY Phone_GPS.[TimeStamp] DESC
                              ;",array($GPS_Location['Employee_ID']));
                            if($r){
                              $row = sqlsrv_fetch_array($r);
                              if(is_array($row)){
                                $GPS_Location['Latitude'] = $row['Latitude'];
                                $GPS_Location['Longitude'] = $row['Longitude'];
                                $GPS_Location['DateTimeRecorded'] = $row['TimeStamp'];
                              }
                            }
                            ?>
                            marker[<?php echo $key;?>] = new mapIcons.Marker({
                              map: map,
                              id:"<?php echo $GPS_Location['Employee_ID'];?>",
                              title: '<?php echo $GPS_Location['First_Name'] . " " . $GPS_Location['Last_Name'];?> -- <?php echo date("m/d/Y H:i:s",strtotime($GPS_Location['DateTimeRecorded']));?> -- <?php echo $GPS_Location['TicketID'];?>',
                              position: new google.maps.LatLng(<?php echo $GPS_Location['Latitude'];?>,<?php echo $GPS_Location['Longitude'];?>),
                              icon: {
                                path:mapIcons.shapes.SQUARE_PIN,
                                fillColor:'#00CCBB',
                                fillOpacity:0,
                                strokeColor:'',
                                strokeWeight:0
                              },
                              map_icon_label:'<span class="map-icon map-icon-walking <?php echo str_replace(' ','',ucwords(strtolower($GPS_Location['Super'])));?>"></span>'
                            });
                            marker[<?php echo $key;?>].addListener('click', function() {
                              $.ajax({
                                url:"get_ticket_popup.php",
                                method:"GET",
                                data:{ 'ID' : '<?php echo $GPS_Location['TicketID'];?>'},
                                success:function(code){$("body").append(code);}
                              });
                            });
                        <?php }
                      }?>
                }
            });

        }
        <?php /*
        $r = sqlsrv_query($NEI,"SELECT * FROM nei.dbo.Loc WHERE Loc.Maint = 1;");
        if($r){while($row = sqlsrv_fetch_array($r)){
          ?>marker[<?php echo $key;?>] = new mapIcons.Marker({
            map: map,
            position: new google.maps.LatLng(<?php echo $row['Latt'];?>,<?php echo $row['fLong'];?>),
            icon: {
              path:mapIcons.shapes.SQUARE_PIN,
              fillColor:'#00CCBB',
              fillOpacity:0,
              strokeColor:'',
              strokeWeight:0
            },
            map_icon_label:'<span class="map-icon map-icon-local-government map-icon-location"></span>'
          });
        }}*/
        ?>
        var toggle = 0;
        function setMapOnAll(mapped) {
          for ( i in marker )
            marker[i].setMap(mapped);
          //marker = new Array();
        }
        function clearMarkers() {
          setMapOnAll(toggle == 0 ? null : map);
          toggle = toggle == 0 ? 1 : 0;
        }
        function showMarkers(){

        }</script>
        <script>

          </script>
          <script>
          $("div#container").on('click',function(e){
            if($(e.target).closest('.popup').length === 0 && $(e.target).closest('td').length === 0){
              $('.popup').fadeOut(300);
              $('.popup').remove();
            }
          });
          </script>

            <style>
              .popup {
                position:absolute;
                z-index:99;
                left:20%;
                right:20%;
                top:20%;
                /*bottom:20%;*/
                /*height:60%;*/
                width:60%;
                background-color:#2d2d2d !important;
                padding:0px;
                max-height:600px;
                overflow-y:scroll;
              }
            </style>
          </div>
</body>
</html>
<?php
}
    } else {?><html><head><script>document.location.href='../login.php?Forward=map.php?Type=Live';</script></head></html><?php }
} ?>
