<?php
if(session_id() == '' || !isset($_SESSION) ){
  session_start( [ 'read_and_close' => true ] );
  require('/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/bin/php/index.php');
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
function distance($lat1, $lon1, $lat2, $lon2, $unit) {
  if (($lat1 == $lat2) && ($lon1 == $lon2)) {
    return 0;
  }
  else {
    $theta = $lon1 - $lon2;
    $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
    $dist = acos($dist);
    $dist = rad2deg($dist);
    $miles = $dist * 60 * 1.1515;
    $unit = strtoupper($unit);

    if ($unit == "K") {
      return ($miles * 1.609344);
    } else if ($unit == "N") {
      return ($miles * 0.8684);
    } else {
      return $miles;
    }
  }
}
if(isset($_SESSION['User'],$_SESSION['Hash'])){
  $r = $database->query(null,
    "  SELECT *
       FROM   Connection
	     WHERE  Connection.Connector = ?
	            AND Connection.Hash  = ?
    ;",array($_SESSION['User'],$_SESSION['Hash']));
  $My_Connection = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
  $r = $database->query(null,
    "SELECT *,
	          Emp.fFirst AS First_Name,
		        Emp.Last   AS Last_Name
	   FROM   Emp
     WHERE  Emp.ID = ?
    ;",array($_SESSION['User']));
  $My_User = sqlsrv_fetch_array($r);
  $r = $database->query(null,
    " SELECT *
	    FROM   Privilege
	      WHERE  Privilege.User_ID = ?
     ;",array($_SESSION['User']));
  $My_Privileges = array();
  if($r){while($My_Privilege = sqlsrv_fetch_array($r)){$My_Privileges[$My_Privilege['Access_Table']] = $My_Privilege;}}
  if(!isset($My_Connection['ID']) || !isset($My_Privileges['Map']) || $My_Privileges['Map']['User_Privilege']  < 4 || $My_Privileges['Map']['Group_Privilege'] < 4 || $My_Privileges['Map']['Other_Privilege'] < 4){require('../404.html');}
  else {
  	$database->query(null,
      "   INSERT INTO Activity([User], [Date], [Page])
  		     VALUES(?,?,?)
  	   ;",array($_SESSION['User'],date("Y-m-d H:i:s"), "map.php"));?><!DOCTYPE html>
<html lang="en">
<head>
    <?php require( bin_meta . 'index.php');?>
    <title>Nouveau Elevator Portal</title>
    <?php require( bin_css . 'index.php');?>
    <?php require( bin_js . 'index.php');?>
    <!--ToolTipster-->
    <!--<link rel="stylesheet" type="text/css" href="bin/libraries/tooltipster/tooltipster.bundle.min.css" />
    <style>
      .tooltipster-base {background-color:white;position:absolute;}
      .row.shadower {
        padding-top:5px;
        padding-bottom:5px;
      }
      </style>
    <script type="text/javascript" src="bin/libraries/tooltipster/tooltipster.bundle.min.js"></script>-->
    <link rel='stylesheet' type='text/css' href='https://cdn.jsdelivr.net/qtip2/3.0.3/jquery.qtip.min.css' />
    <style>
    .tooltipTicket {
      width:650px;
    }
    .tooltipTicket_popup .row{
      padding-top:5px;
      padding-bottom:5px;
    }
    .qtip { max-width: none !important; }
    </style>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/qtip2/3.0.3/basic/jquery.qtip.min.js"></script>

</head>
<body onload='finishLoadingPage();'>
<div id='container'>
  <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
    <?php require( bin_php . 'element/navigation/index.php');?>
    <?php require( bin_php . 'element/loading.php');?>
    <div id="page-wrapper" class='content' style='margin-right:0px !important;'>
      <div class="row" style='height:100%;'>
          <div class="col-lg-12">
              <div class="panel panel-primary">
                  <div class="panel-heading">
                    <div class='row'>
                      <div class='col-xs-2'><h3>Divisional Map</h3></div>
                      <div class='col-xs-1'><button onClick="document.location.href='map3.php';" style='color:black;height:30px;'>Breadcrumb Map</button></div>
                      <div class='col-xs-1'><button onClick="codeAddress(prompt('What address would you like to center on?'));" style='width:100%;color:black;'>Center Address</button></div>
                      <div class='col-xs-2'><select name='Employee' style='color:black !important;' onChange='zoomUser(this);'>
                        <option value=''>Select User to Center</option>
                        <?php $r = $database->query(null,"SELECT * FROM Emp WHERE Emp.Status = 0 ORDER BY Emp.Last, Emp.fFirst ASC;");
                        if($r){while($row = sqlsrv_fetch_array($r)){
                          ?><option value='<?php echo $row['ID'];?>'><?php echo $row['Last'] . ', ' . $row['fFirst'];?></option><?php
                        }}?>
                      </select></div>
                      <div class='col-xs-2'><select name='Employee' style='color:black !important;' onChange='breadcrumbUser(this);'>
                        <option value=''>Select User to Breadcrumb</option>
                        <?php $r = $database->query(null,"SELECT * FROM Emp WHERE Emp.Status = 0 ORDER BY Emp.Last, Emp.fFirst ASC;");
                        if($r){while($row = sqlsrv_fetch_array($r)){
                          ?><option value='<?php echo $row['ID'];?>'><?php echo $row['Last'] . ', ' . $row['fFirst'];?></option><?php
                        }}?>
                      </select></div>
                      <div class='col-xs-2'>
                        <button onClick='takeServiceCall();' style='float:right;height:30px;color:black;'>Take Service Call</button>
                      </div>
                      <div class='col-xs-2'>
                        &nbsp;
                      </div>
                    </div>
                  <div class='panel-heading'>
                    <div class='row'>
                      <div class='col-xs-1' style='background-color:white;color:black;' onClick='clearMarkers();'>Toggle</div>
                      <div class='col-xs-1' onClick='showDivision1();' style='background-color:white;color:black;border:1px solid black;'>Division #1</div>
                      <div class='col-xs-1' onClick='showDivision2();' style='background-color:white;color:black;border:1px solid black;'>Division #2</div>
                      <div class='col-xs-1' onClick='showDivision3();' style='background-color:white;color:black;border:1px solid black;'>Division #3</div>
                      <div class='col-xs-1' onClick='showDivision4();' style='background-color:white;color:black;border:1px solid black;'>Division #4</div>
                      <div class='col-xs-1' onClick='showModernization();' style='background-color:white;color:black;border:1px solid black;'>Modernization</div>
                      <div class='col-xs-1' onClick='showEscalator();' style='background-color:white;color:black;border:1px solid black;'>Escalator</div>
                      <div class='col-xs-1' onClick='showFiremen();' style='background-color:white;color:black;border:1px solid black;'>Firemen</div>
                      <div class='col-xs-1' onClick='showRepair();' style='background-color:white;color:black;border:1px solid black;'>Repair</div>
                      <div class='col-xs-1' onClick='showTesting();' style='background-color:white;color:black;border:1px solid black;'>Testing</div>
                    </div>
                    <div class='row'>
                      <div class='col-xs-1' style='background-color:white;color:black;' onClick='clearMarkers();'>&nbsp;</div>
                      <div class='col-xs-1' onClick='showDivision1();' style='background-color:white;color:black;border:1px solid black;'><?php
                        $r = $database->query(null,
                          " SELECT  Count(*) AS Count
                            FROM    TicketO
                                    LEFT JOIN Emp ON TicketO.fWork = Emp.fWork
                                    LEFT JOIN tblWork ON 'A' + convert(varchar(10),Emp.ID) + ',' = tblWork.Members
                            WHERE   TicketO.Assigned = 3
                                    AND tblWork.Super LIKE '%DIVISION 1%'
                          ;");
                        $row =  $r ? sqlsrv_fetch_array($r) : null;
                        echo is_array($row) ? $row['Count'] : null;
                      ?> personnel</div>
                      <div class='col-xs-1' onClick='showDivision2();' style='background-color:white;color:black;border:1px solid black;'><?php
                        $r = $database->query(null,
                          " SELECT  Count(*) AS Count
                            FROM    TicketO
                                    LEFT JOIN Emp ON TicketO.fWork = Emp.fWork
                                    LEFT JOIN tblWork ON 'A' + convert(varchar(10),Emp.ID) + ',' = tblWork.Members
                            WHERE   TicketO.Assigned = 3
                                    AND tblWork.Super LIKE '%DIVISION 2%'
                          ;");
                        $row =  $r ? sqlsrv_fetch_array($r) : null;
                        echo is_array($row) ? $row['Count'] : null;
                      ?> personnel</div>
                      <div class='col-xs-1' onClick='showDivision3();' style='background-color:white;color:black;border:1px solid black;'><?php
                        $r = $database->query(null,
                          " SELECT  Count(*) AS Count
                            FROM    TicketO
                                    LEFT JOIN Emp ON TicketO.fWork = Emp.fWork
                                    LEFT JOIN tblWork ON 'A' + convert(varchar(10),Emp.ID) + ',' = tblWork.Members
                            WHERE   TicketO.Assigned = 3
                                    AND tblWork.Super LIKE '%DIVISION 3%'
                          ;");
                        $row =  $r ? sqlsrv_fetch_array($r) : null;
                        echo is_array($row) ? $row['Count'] : null;
                      ?> personnel</div>
                      <div class='col-xs-1' onClick='showDivision4();' style='background-color:white;color:black;border:1px solid black;'><?php
                        $r = $database->query(null,
                          " SELECT  Count(*) AS Count
                            FROM    TicketO
                                    LEFT JOIN Emp ON TicketO.fWork = Emp.fWork
                                    LEFT JOIN tblWork ON 'A' + convert(varchar(10),Emp.ID) + ',' = tblWork.Members
                            WHERE   TicketO.Assigned = 3
                                    AND tblWork.Super LIKE '%DIVISION 4%'
                          ;");
                        $row =  $r ? sqlsrv_fetch_array($r) : null;
                        echo is_array($row) ? $row['Count'] : null;
                      ?> personnel</div>
                      <div class='col-xs-1' onClick='showModernization();' style='background-color:white;color:black;border:1px solid black;'><?php
                        $r = $database->query(null,
                          " SELECT  Count(*) AS Count
                            FROM    TicketO
                                    LEFT JOIN Emp ON TicketO.fWork = Emp.fWork
                                    LEFT JOIN tblWork ON 'A' + convert(varchar(10),Emp.ID) + ',' = tblWork.Members
                            WHERE   TicketO.Assigned = 3
                                    AND tblWork.Super LIKE '%Modernization%'
                          ;");
                        $row =  $r ? sqlsrv_fetch_array($r) : null;
                        echo is_array($row) ? $row['Count'] : null;
                      ?> personnel</div>
                      <div class='col-xs-1' onClick='showEscalator();' style='background-color:white;color:black;border:1px solid black;'><?php
                        $r = $database->query(null,
                          " SELECT  Count(*) AS Count
                            FROM    TicketO
                                    LEFT JOIN Emp ON TicketO.fWork = Emp.fWork
                                    LEFT JOIN tblWork ON 'A' + convert(varchar(10),Emp.ID) + ',' = tblWork.Members
                            WHERE   TicketO.Assigned = 3
                                    AND tblWork.Super LIKE '%Escalator%'
                          ;");
                        $row =  $r ? sqlsrv_fetch_array($r) : null;
                        echo is_array($row) ? $row['Count'] : null;
                      ?> personnel</div>
                      <div class='col-xs-1' onClick='showFiremen();' style='background-color:white;color:black;border:1px solid black;'><?php
                        $r = $database->query(null,
                          " SELECT  Count(*) AS Count
                            FROM    TicketO
                                    LEFT JOIN Emp ON TicketO.fWork = Emp.fWork
                                    LEFT JOIN tblWork ON 'A' + convert(varchar(10),Emp.ID) + ',' = tblWork.Members
                            WHERE   TicketO.Assigned = 3
                                    AND tblWork.Super LIKE '%Firemen%'
                          ;");
                        $row =  $r ? sqlsrv_fetch_array($r) : null;
                        echo is_array($row) ? $row['Count'] : null;
                      ?> personnel</div>
                      <div class='col-xs-1' onClick='showRepair();' style='background-color:white;color:black;border:1px solid black;'><?php
                        $r = $database->query(null,
                          " SELECT  Count(*) AS Count
                            FROM    TicketO
                                    LEFT JOIN Emp ON TicketO.fWork = Emp.fWork
                                    LEFT JOIN tblWork ON 'A' + convert(varchar(10),Emp.ID) + ',' = tblWork.Members
                            WHERE   TicketO.Assigned = 3
                                    AND tblWork.Super LIKE '%Repair%'
                          ;");
                        $row =  $r ? sqlsrv_fetch_array($r) : null;
                        echo is_array($row) ? $row['Count'] : null;
                      ?> personnel</div>
                      <div class='col-xs-1' onClick='showTesting();' style='background-color:white;color:black;border:1px solid black;'><?php
                        $r = $database->query(null,
                          " SELECT  Count(*) AS Count
                            FROM    TicketO
                                    LEFT JOIN Emp ON TicketO.fWork = Emp.fWork
                                    LEFT JOIN tblWork ON 'A' + convert(varchar(10),Emp.ID) + ',' = tblWork.Members
                            WHERE   TicketO.Assigned = 3
                                    AND tblWork.Super LIKE '%Testing%'
                          ;");
                        $row =  $r ? sqlsrv_fetch_array($r) : null;
                        echo is_array($row) ? $row['Count'] : null;
                      ?> personnel</div>
                    </div>
                    <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
                    <div class='row'>
                      <div class='col-xs-1' style='background-color:white;color:black;'>LEGEND:</div>
                      <div class='col-xs-1' style='background-color:white;color:black;'>People (Pins):</div>
                      <div class='col-xs-2' style='background-color:green;color:black;'>GPS TIME <= 30 MINS</div>
                      <div class='col-xs-1' style='background-color:yellow;color:black;'>GPS TIME <= 2 HRS</div>
                      <div class='col-xs-1' style='background-color:orange;color:black;'>GPS TIME <= 9 HRS</div>
                      <div class='col-xs-1' style='background-color:brown;color:black;'>9 HRS <= GPS TIME</div>
                    </div>
                    <div class='row'>
                      <div class='col-xs-1' style='background-color:white;color:black;'>&nbsp;</div>
                      <div class='col-xs-1' style='background-color:white;color:black;'>Service Calls (Flags):</div>
                      <div class='col-xs-2' style='background-color:red;color:white;'>Unanswered Entrapments</div>
                      <div class='col-xs-2' style='background-color:purple;color:white;'>Answered Entrapments</div>
                      <div class='col-xs-2' style='background-color:blue;color:white;'>Unanswered Shutdowns</div>
                      <div class='col-xs-2' style='background-color:cyan;color:black;'>Answered Shutdowns</div>
                    </div>
                  </div>
                  <div class="panel-body">
                    <div class='row'>
                      <div class='col-xs-9'><div id="map" style='height:675px;width:100%;'></div></div>
                      <div class='col-xs-3'>
                        <div id='Feed_Title'><h3>Timeline</h3></div>
                        <div id='Feed' style='min-height:625px;max-height:625px;height:625px !important;overflow-y:scroll;margin-top:15px;'>&nbsp;</div>
                      </div>
                    </div>
                  </div>
              </div>
          </div>
      </div>
    </div>
  </div>
</div>
<!-- Bootstrap Core JavaScript -->


<!-- JQUERY UI Javascript -->


<?php
    $r = $database->query(null,
      " SELECT  Top 1 *
        FROM    GPS
        WHERE   GPS.Employee_ID = ?
        ORDER BY GPS.Time_Stamp DESC
      ;", array($_GET['Mechanic']));
    $asdf = sqlsrv_fetch_array($r);?>
<!-- Map Icons -->
<link rel="stylesheet" type="text/css" href="bin/libraries/map-icons-master/dist/css/map-icons.css">
<script type="text/javascript" src="bin/libraries/map-icons-master/dist/js/map-icons.js"></script>

<!--<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.23.0/locale/es-us.js"></script>-->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>
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
  .map-icon-label .map-icon.Modernization {color:black !important;}
  .map-icon-label .map-icon.New-GPS {color:green !important;}
  .map-icon-label .map-icon.Old-GPS {color:yellow !important;}
  .map-icon-label .map-icon.Ancient-GPS {color:red !important;}
</style>
<script type="text/javascript">
var LookUp_Address = null;
var LookUp_User = null;
var marker = new Array();
var shutdowns = new Array();
var marker_set_Division1 = new Array();
var marker_set_Division2 = new Array();
var marker_set_Division2c = new Array();
var marker_set_Divison2 = new Array();
var marker_set_Division3 = new Array();
var marker_set_Division4 = new Array();
var marker_set_Firemen = new Array();
var marker_set_Repair = new Array();
var marker_set_Escalator = new Array();
var marker_set_Testing = new Array();
var marker_set_Modernization = new Array();
var marker_set_Gwz = new Array();
var marker_set_Office = new Array();
var marker_set_Warehouse = new Array();
var marker_set_ = new Array();
var Timeline_Supervisor = '';
var TIMELINE = new Array();
var REFRESH_DATETIME = '<?php echo date("Y-m-d H:i:s",strtotime('-300 minutes'));?>';
var exceptions = new Array();
function jsUcfirst(string)
{
    return string.charAt(0).toUpperCase() + string.slice(1);
}
function showModernization(){
  for (i in marker){marker[i].setMap(marker_set_Modernization.includes(i) ? map : null);}
  for (i in shutdowns){shutdowns[i].setMap(null);}
  for (i in entrapments){entrapments[i].setMap(null);}
  Timeline_Supervisor = 'Modernization'
  $("#Feed").html("");
  REFRESH_DATETIME = '<?php echo date("Y-m-d H:i:s",strtotime('-300 minutes'));?>';
  TIMELINE = new Array();
  getTimeline();
}
function showDivision1(){
  for (i in marker){marker[i].setMap(marker_set_Division1.includes(i) ? map : null);}
  for (i in shutdowns){
    if(shutdowns_division[i] == 'DIVISION #1'){
      shutdowns[i].setMap(map);
    }
    else {shutdowns[i].setMap(null);}
  }
  for (i in entrapments){
    if(entrapments_division[i] == 'DIVISION #1'){
      entrapments[i].setMap(map);
    }
    else {entrapments[i].setMap(null);}
  }
  Timeline_Supervisor = 'Division 1';
  $("#Feed").html("");
  REFRESH_DATETIME = '<?php echo date("Y-m-d H:i:s",strtotime('-300 minutes'));?>';
  TIMELINE = new Array();
  getTimeline();
}
function showDivision2(){
  for (i in marker){marker[i].setMap(marker_set_Division2.includes(i) ? map : null);}
  for (i in shutdowns){
    if(shutdowns_division[i] == 'DIVISION #2'){
      shutdowns[i].setMap(map);
    }
    else {shutdowns[i].setMap(null);}
  }
  for (i in entrapments){
    if(entrapments_division[i] == 'DIVISION #2'){
      entrapments[i].setMap(map);
    }
    else {entrapments[i].setMap(null);}
  }
  Timeline_Supervisor = 'Division 2';
  $("#Feed").html("");
  REFRESH_DATETIME = '<?php echo date("Y-m-d H:i:s",strtotime('-300 minutes'));?>';
  TIMELINE = new Array();
  getTimeline();
}
function showDivision3(){
  for (i in marker){marker[i].setMap(marker_set_Division3.includes(i) ? map : null);}
  for (i in shutdowns){
    if(shutdowns_division[i] == 'DIVISION #3'){
      shutdowns[i].setMap(map);
    }
    else {shutdowns[i].setMap(null);}
  }
  for (i in entrapments){
    if(entrapments_division[i] == 'DIVISION #3'){
      entrapments[i].setMap(map);
    }
    else {entrapments[i].setMap(null);}
  }
  Timeline_Supervisor = 'Division 3';
  $("#Feed").html("");
  REFRESH_DATETIME = '<?php echo date("Y-m-d H:i:s",strtotime('-300 minutes'));?>';
  TIMELINE = new Array();
  getTimeline();
}
function showDivision4(){
  for (i in marker){marker[i].setMap(marker_set_Division4.includes(i) ? map : null);}
  for (i in shutdowns){
    if(shutdowns_division[i] == 'DIVISION #4'){
      shutdowns[i].setMap(map);
    }
    else {shutdowns[i].setMap(null);}
  }
  for (i in entrapments){
    if(entrapments_division[i] == 'DIVISION #4'){
      entrapments[i].setMap(map);
    }
    else {entrapments[i].setMap(null);}
  }
  Timeline_Supervisor = 'Division 4'
  $("#Feed").html("");
  REFRESH_DATETIME = '<?php echo date("Y-m-d H:i:s",strtotime('-300 minutes'));?>';
  TIMELINE = new Array();
  getTimeline();
}
function showFiremen(){
  for (i in marker){marker[i].setMap(marker_set_Firemen.includes(i) ? map : null);}
  for (i in shutdowns){shutdowns[i].setMap(null);}
  for (i in entrapments){entrapments[i].setMap(null);}
  Timeline_Supervisor = 'Firemen';
  $("#Feed").html("");
  REFRESH_DATETIME = '<?php echo date("Y-m-d H:i:s",strtotime('-300 minutes'));?>';
  TIMELINE = new Array();
  getTimeline();
}
function showTesting(){
  for (i in marker){marker[i].setMap(marker_set_Testing.includes(i) ? map : null);}
  for (i in shutdowns){shutdowns[i].setMap(null);}
  for (i in entrapments){entrapments[i].setMap(null);}
  Timeline_Supervisor = 'Testing';
  $("#Feed").html("");
  REFRESH_DATETIME = '<?php echo date("Y-m-d H:i:s",strtotime('-300 minutes'));?>';
  TIMELINE = new Array();
  getTimeline();
}
function showEscalator(){
  for (i in marker){marker[i].setMap(marker_set_Escalator.includes(i) ? map : null);}
  for (i in shutdowns){shutdowns[i].setMap(null);}
  for (i in entrapments){entrapments[i].setMap(null);}
  Timeline_Supervisor = 'Escalator';
  $("#Feed").html("");
  REFRESH_DATETIME = '<?php echo date("Y-m-d H:i:s",strtotime('-300 minutes'));?>';
  TIMELINE = new Array();
  getTimeline();
}
function showRepair(){
  for (i in marker){marker[i].setMap(marker_set_Repair.includes(i) ? map : null);}
  for (i in shutdowns){shutdowns[i].setMap(null);}
  for (i in entrapments){entrapments[i].setMap(null);}
  Timeline_Supervisor = 'Repair';
  $("#Feed").html("");
  REFRESH_DATETIME = '<?php echo date("Y-m-d H:i:s",strtotime('-300 minutes'));?>';
  TIMELINE = new Array();
  getTimeline();
}
var map;
var directionsDisplay1;
var directionsService1;
function renderMap(){
  var latlng = {lat:<?php echo isset($_GET['Latitude']) ? $_GET['Latitude'] : 40.7831;?>, lng:<?php echo isset($_GET['Longitude']) ? $_GET['Longitude'] : -73.9712;?>};
var myOptions = {
  zoom: <?php echo isset($_GET['Latitude'], $_GET['Longitude']) ? 18 : 10;?>,
  center: latlng
};
map = new google.maps.Map(document.getElementById("map"), myOptions);
  directionsService1 = new google.maps.DirectionsService;
  directionsDisplay1 = new google.maps.DirectionsRenderer({
    map: map
  });
  <?php if(isset($_GET['Mechanic'])){?>
  <?php if(isset($_GET['Mechanic'])){?>geocoder2 = new google.maps.Geocoder();
  geocoder2.geocode({
      'address': '<?php echo $asdf['Latitude'] . ' ' . $asdf['Longitude'];?>'
  }, function(results, status) {
      if (status == google.maps.GeocoderStatus.OK) {
        var LookUp_Mechanic = new google.maps.Marker({
          map: map,
          position: new google.maps.LatLng(results[0].geometry.location.lat(),results[0].geometry.location.lng()),
          icon: {
            path:mapIcons.shapes.SQUARE_PIN,
            fillColor:'#00CCBB',
            fillOpacity:0,
            strokeColor:'green',
            strokeWeight:0
          },
          zIndex:99999999,
          id:'LookUp_Mechanic',
          icon:flagSymbol('black')
        });
        var LookUp_A = new google.maps.LatLng(<?php echo $_GET['Latitude'];?>, <?php echo $_GET['Longitude'];?>);
        var LookUp_M = new google.maps.LatLng(results[0].geometry.location.lat(),results[0].geometry.location.lng());
        calculateAndDisplayRoute(directionsService1, directionsDisplay1, LookUp_M, LookUp_A);
      }
    });
  <?php }?>
  <?php }?>
  $(document).ready(function(){
    getGPS();
    setInterval(getGPS, 15000);
    <?php if(isset($_GET['Latitude'],$_GET['Longitude']) && isset($_GET['Locate'])){?>codeAddress('<?php echo $_GET['Latitude'];?> <?php echo $_GET['Longitude'];?>');<?php }?>
  });
}
function rad(x) {return x*Math.PI/180;}
function next_Nearest(t){
	$('.popup').remove();
	exceptions.push(t);
	<?php if(isset($_GET['Latitude'],$_GET['Longitude'])){?>find_closest_marker(<?php echo $_GET['Latitude'];?>, <?php echo $_GET['Longitude'];?>);<?php }?>
}
function previous_Nearest(t){
	$('.popup').remove();
	exceptions.pop();
	<?php if(isset($_GET['Latitude'],$_GET['Longitude'])){?>find_closest_marker(<?php echo $_GET['Latitude'];?>, <?php echo $_GET['Longitude'];?>);<?php }?>
}
var Ticket_ID;
var EID;
function find_closest_marker(lat, lng ) {
    var R = 6371; // radius of earth in km
    var distances = [];
    var closest = 99999999999999;
    var nearest;
    var xlat;
    var xlng;
    var t;
    marker.forEach(function(item){
    	if(!exceptions.includes(item.title)){
	        var mlat = item.position.lat();
	        var mlng = item.position.lng();
	        var title = item.title;
	        var dLat  = rad(mlat - lat);
	        var dLong = rad(mlng - lng);
	        var a = Math.sin(dLat/2) * Math.sin(dLat/2) +
	            Math.cos(rad(lat)) * Math.cos(rad(lat)) * Math.sin(dLong/2) * Math.sin(dLong/2);
	        var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
	        var d = R * c;
	        if (d < closest && d >= 0 && d != NaN) {
	            closest = d;
	            xlat = mlat;
	            xlng = mlng;
	            t = title;
              Ticket_ID = item.Ticket_ID;
              EID = item.Employee_ID;
	        }
	    }
    });
    map.setCenter(new google.maps.LatLng(xlat, xlng));
    map.setZoom(20);
    <?php if(isset($_GET['Latitude'],$_GET['Longitude'])){?>calculateAndDisplayRoute(directionsService1, directionsDisplay1, new google.maps.LatLng(xlat, xlng), new google.maps.LatLng(<?php echo $_GET['Latitude'] . ',' . $_GET['Longitude'];?>));<?php }?>
    var service = new google.maps.DistanceMatrixService();
		service.getDistanceMatrix(
		  {
		    origins: [new google.maps.LatLng(xlat, xlng)],
		    <?php if(isset($_GET['Latitude'],$_GET['Longitude'])){?>destinations: [new google.maps.LatLng(<?php echo $_GET['Latitude'] . ',' . $_GET['Longitude'];?>)],<?php }?>
		    travelMode: 'WALKING',
		    unitSystem: google.maps.UnitSystem.IMPERIAL,
		    avoidHighways: false,
		    avoidTolls: false,
		  }, function(response, status){
		  	//var response = JSON.parse(response);
		  	if (status !== google.maps.DistanceMatrixStatus.OK) {
	            console.log('Error:', status);
	        } else {
            $.ajax({
              url:"bin/php/tooltip/GPS.php",
              method:"GET",
              data:{
                ID:EID,
                popup:false
              },
              success:function(code){
                var ticket = code;
                $(".popup").remove();
                var mech = t.split(' - ')[0];
                var time = t.split(' - ')[1];
                $("body").append('<div class="popup directions" style="font-size:20px !important;width:700px !important;left:unset;right:1% !important;height:auto;">' + "<div class='panel-primary'><div class='panel-heading'>Directions Information<div style='float:right;' onClick='close_this(this);'>Close</div></div><div class='panel-body'><div class='row'><div class='col-xs-3'>Mechanic:</div><div class='col-xs-9'>" + mech + "</div></div><div class='row'><div class='col-xs-3'>GPS Stamp</div><div class='col-xs-9'>" + moment(new Date(time)).format('MM/DD/YYYY hh:mm A') + "</div></div><div class='row'><div class='col-xs-3'>Duration:</div><div class='col-xs-9'>" + response.rows[0].elements[0].duration.text + "</div><div class='row'><div class='col-xs-6'><button onClick='previous_Nearest(\"" + t + "\");' style='width:100%;'>Previous Nearest</button></div><div class='col-xs-6'><button onClick='next_Nearest(\"" + t + "\");' style='width:100%;'>Next Nearest</button></div></div><div class='row'><div class='col-xs-12' style='font-size:12px !important;background-color:white !important;color:black !important;'>" + ticket + "</div></div></div></div>");
              }
            });
	        }
		  });
}
function close_this(link){
	$(link).parent().parent().parent().remove();
}
function calculateAndDisplayRoute(directionsService2, directionsDisplay2, pointA, pointB) {
  directionsService2.route({
    origin: pointA,
    destination: pointB,
    travelMode: google.maps.TravelMode.DRIVING
  }, function(response, status) {
    if (status == 'OK') {
      directionsDisplay2.setDirections(response);
    } else {
      window.alert('Directions request failed due to ' + status);
    }
  });
}
function pinSymbol(color) {
    return {
        path: 'M 0,0 C -2,-20 -10,-22 -10,-30 A 10,10 0 1,1 10,-30 C 10,-22 2,-20 0,0 z M -2,-30 a 2,2 0 1,1 4,0 2,2 0 1,1 -4,0',
        fillColor: color,
        fillOpacity: 1,
        strokeColor: '#000',
        strokeWeight: 2,
        scale: 1,
   };
}
function flagSymbol(color) {
    return {
        path: 'M 0,0 -1,-2 V -43 H 1 V -2 z M 1,-40 H 30 V -20 H 1 z',
        fillColor: color,
        fillOpacity: 1,
        strokeColor: '#000',
        strokeWeight: 2,
        scale: 1,
   };
}
var GETTING_MODERNIZATIONS = 0;
var modernizations_division = new Array();
var modernizations = new Array();
function getModernizations(){
  if(GETTING_MODERNIZATIONS == 0){
    GETTING_MODERNIZATIONS = 1;
    $.ajax({
      url:"bin/php/get/getModernizations.php",
      method:"GET",
      success:function(json){
        var GPS_Data = JSON.parse(json);
        for(i in modernizations){
          if(GPS_Data[i]){
            GPS_Data[i]['Updated'] = 1;
          } else {
            modernizations[i].setMap(null);
          }
        }
        for(i in GPS_Data){
          if(GPS_Data[i]['Updated']){}
          else {
            modernizations[i] = new google.maps.Marker({
              map: map,
              position: new google.maps.LatLng(GPS_Data[i].Latitude, GPS_Data[i].Longitude),
              title: GPS_Data[i].Title,
              icon: {
                path:mapIcons.shapes.SQUARE_PIN,
                fillColor:'#00CCBB',
                fillOpacity:0,
                strokeColor:'black',
                strokeWeight:0
              },
              id:i,
              zIndex:99999999,
              icon:flagSymbol(GPS_Data[i].Serviced == '1' ? 'green' : 'green')
            });
          }
        }
        for(i in GPS_Data){
          modernizations_division[i] = GPS_Data[i]['Division'];
          if(modernizations[i]){
            modernizations[i].setPosition(new google.maps.LatLng(GPS_Data[i].Latitude, GPS_Data[i].Longitude));
            modernizations[i].setTitle(GPS_Data[i].Title);
            modernizations[i].setIcon(flagSymbol(GPS_Data[i].Serviced == '1' ? 'green' : 'green'));
          } else {
            modernizations[i] = new google.maps.Marker({
              map: map,
              position: new google.maps.LatLng(GPS_Data[i].Latitude, GPS_Data[i].Longitude),
              title: GPS_Data[i].Title,
              icon: {
                path:mapIcons.shapes.SQUARE_PIN,
                fillColor:'#00CCBB',
                fillOpacity:0,
                strokeColor:'black',
                strokeWeight:0
              },
              id:i,
              icon:flagSymbol(GPS_Data[i].Serviced == '1' ? 'cyan' : 'blue')
            });
          }
        }
        GETTING_MODERNIZATIONS = 0;
      }
    });
  }
}
var GETTING_SHUTDOWNS = 0;
var shutdowns_division = new Array();
function getShutdowns(){
  if(GETTING_SHUTDOWNS == 0){
    GETTING_SHUTDOWNS = 1;
    $.ajax({
      url:"bin/php/get/getShutdowns.php",
      method:"GET",
      success:function(json){
        var GPS_Data = JSON.parse(json);
        for(i in shutdowns){
          if(GPS_Data[i]){
            GPS_Data[i]['Updated'] = 1;
          } else {
            shutdowns[i].setMap(null);
          }
        }
        for(i in GPS_Data){
          if(GPS_Data[i]['Updated']){}
          else {
            shutdowns[i] = new google.maps.Marker({
              map: map,
              position: new google.maps.LatLng(GPS_Data[i].Latitude, GPS_Data[i].Longitude),
              title: GPS_Data[i].Title,
              icon: {
                path:mapIcons.shapes.SQUARE_PIN,
                fillColor:'#00CCBB',
                fillOpacity:0,
                strokeColor:'black',
                strokeWeight:0
              },
              id:i,
              icon:flagSymbol(GPS_Data[i].Serviced == '1' ? 'cyan' : 'blue')
            });
          }
        }
        for(i in GPS_Data){
          shutdowns_division[i] = GPS_Data[i]['Division'];
          if(shutdowns[i]){
            shutdowns[i].setPosition(new google.maps.LatLng(GPS_Data[i].Latitude, GPS_Data[i].Longitude));
            shutdowns[i].setTitle(GPS_Data[i].Title);
            shutdowns[i].setIcon(flagSymbol(GPS_Data[i].Serviced == '1' ? 'cyan' : 'blue'));
          } else {
            shutdowns[i] = new google.maps.Marker({
              map: map,
              position: new google.maps.LatLng(GPS_Data[i].Latitude, GPS_Data[i].Longitude),
              title: GPS_Data[i].Title,
              icon: {
                path:mapIcons.shapes.SQUARE_PIN,
                fillColor:'#00CCBB',
                fillOpacity:0,
                strokeColor:'black',
                strokeWeight:0
              },
              id:i,
              icon:flagSymbol(GPS_Data[i].Serviced == '1' ? 'cyan' : 'blue')
            });
          }
        }
        GETTING_SHUTDOWNS = 0;
      }
    });
  }
}
var GETTING_ENTRAPMENTS = 0;
var entrapments = new Array();
var entrapments_division = new Array();
function getEntrapments(){
  if(GETTING_ENTRAPMENTS == 0){
    GETTING_ENTRAPMENTS = 1;
    $.ajax({
      url:"bin/php/get/getEntrapments.php",
      method:"GET",
      success:function(json){
        var GPS_Data = JSON.parse(json);
        for(i in entrapments){
          if(GPS_Data[i]){
            GPS_Data[i]['Updated'] = 1;
          } else {
            entrapments[i].setMap(null);
          }
        }
        for(i in GPS_Data){
          entrapments_division[i] = GPS_Data[i]['Division'];
          if(GPS_Data[i]['Updated']){}
          else {
            entrapments[i] = new google.maps.Marker({
              map: map,
              position: new google.maps.LatLng(GPS_Data[i].Latitude, GPS_Data[i].Longitude),
              zIndex:99999999,
              title: GPS_Data[i].Title,
              icon: {
                path:mapIcons.shapes.SQUARE_PIN,
                fillColor:'#00CCBB',
                fillOpacity:0,
                strokeColor:'black',
                strokeWeight:0
              },
              id:i,
              icon:flagSymbol(GPS_Data[i].Serviced == '1' ? 'purple' : 'red')
            });
          }
        }
        for(i in GPS_Data){
          if(entrapments[i]){
            entrapments[i].setPosition(new google.maps.LatLng(GPS_Data[i].Latitude, GPS_Data[i].Longitude));
            entrapments[i].setTitle(GPS_Data[i].Title);
            entrapments[i].setIcon(flagSymbol(GPS_Data[i].Serviced == '1' ? 'purple' : 'red'));
          } else {
            entrapments[i] = new google.maps.Marker({
              map: map,
              position: new google.maps.LatLng(GPS_Data[i].Latitude, GPS_Data[i].Longitude),
              zIndex:99999999,
              title: GPS_Data[i].Title,
              icon: {
                path:mapIcons.shapes.SQUARE_PIN,
                fillColor:'#00CCBB',
                fillOpacity:0,
                strokeColor:'black',
                strokeWeight:0
              },
              id:i,
              icon:flagSymbol(GPS_Data[i].Serviced == '1' ? 'purple' : 'red')
            });
          }
        }
        GETTING_entrapments = 0;
      }
    });
  }
}
function NOW() {

    var date = new Date();
    var aaaa = date.getFullYear();
    var gg = date.getDate();
    var mm = (date.getMonth() + 1);

    if (gg < 10)
        gg = "0" + gg;

    if (mm < 10)
        mm = "0" + mm;

    var cur_day = aaaa + "-" + mm + "-" + gg;

    var hours = date.getHours()
    var minutes = date.getMinutes()
    var seconds = date.getSeconds();

    if (hours < 10)
        hours = "0" + hours;

    if (minutes < 10)
        minutes = "0" + minutes;

    if (seconds < 10)
        seconds = "0" + seconds;

    return cur_day + " " + hours + ":" + minutes + ":" + seconds;

}
var GETTING_TIMELINE = 0;
function getTimeline(){
  var TEMP_REFRESH_DATETIME = REFRESH_DATETIME;
  REFRESH_DATETIME = NOW();
  if(GETTING_TIMELINE == 0){
    GETTING_TIMELINE = 1;
    $.ajax({
      url:"bin/php/get/Timeline.php?REFRESH_DATETIME=" + TEMP_REFRESH_DATETIME + "&Supervisor=" + Timeline_Supervisor,
      method:"GET",
      error:function(XMLHttpRequest, textStatus, errorThrown){
        GETTING_TIMELINE = 0;
      },
      success:function(json){
        var ticketData = JSON.parse(json);
        for(i in ticketData){
          if(TIMELINE[i]){}
          else {
            $("#Feed").prepend("<div rel='" + ticketData[i].Entity_ID + "' class='row toolesttipster' id='timeline_" + ticketData[i].Entity + "_" + ticketData[i].Entity_ID + "'><div class='col-xs-12'>"
              + "<div class='row'><div class='col-xs-12'>" + '<?php \singleton\fontawesome::getInstance( )->Ticket(1);?> ' + ticketData[i].Entity + ' #' + ticketData[i].Entity_ID + "</div></div>"
              + "<div class='row'><div class='col-xs-12'>" + '<?php \singleton\fontawesome::getInstance( )->Calendar(1);?> ' + ticketData[i].Action + " @ " + ticketData[i].Time_Stamp + '</div></div>'
              + "<div class='row'><div class='col-xs-12'>" + '<?php \singleton\fontawesome::getInstance( )->Calendar(1);?> ' + ticketData[i].Location_Tag + '</div></div>'
              + "<div class='row'><div class='col-xs-12'>"  + '<?php \singleton\fontawesome::getInstance( )->User(1);?> ' + ticketData[i].Employee_Name + '</div></div>'
            +  "</div></div>"
            + "<div class='row'><div class='col-xs-12'>&nbsp;</div></div>");
            $("#timeline_" + ticketData[i].Entity + "_" + ticketData[i].Entity_ID).on('click',function(){
              $.ajax({
                url:"bin/php/tooltip/Ticket.php",
                method:"GET",
                data:{
                  ID:$(this).attr('rel')
                },
                success:function(code){
                  $(".popup").remove();
                  $("body").append(code);
                }
              });
            });
            /*$("#timeline_" + ticketData[i].Entity + "_" + ticketData[i].Entity_ID).qtip({
              content: {
                 text: 'Loading...', // The text to use whilst the AJAX request is loading
                 ajax: {
                     url: 'bin/php/tooltip/Ticket.php?ID=' + ticketData[i].Entity_ID, // URL to the local file
                     type: 'GET', // POST or GET
                     data: {
                       ID: $(this).attr('rel')
                     },
                     success: function(data, status) {
                        this.set('content.text', data);
                    }
                 }
             },
             position: {
                  my: 'right center',
                  at: 'left center',
                  target: $("#timeline_" + ticketData[i].Entity + "_" + ticketData[i].Entity_ID),
                  effect: false,
                  viewport: $('#page-wrapper'),
                  adjust: {
                      method: 'shift'
                  }
              },
              style: {
                classes: 'tooltipTicket qtip-shadow',
                width: 750/*, // Overrides width set by CSS (but no max-width!)
                height: 750 // Overrides height set by CSS (but no max-height!)*/

              //}
            //});*/
            TIMELINE[i] = ticketData[i];
          }
        }
        GETTING_TIMELINE = 0;
      }
    });
  }
}
var GETTING_GPS = 0;
var GOT_DIRECTIONS = 0;
function getGPS(){
  getShutdowns();
  getEntrapments();
  getTimeline();
  getModernizations();
  if(GETTING_GPS == 0){
    GETTING_GPS = 1;
    $.ajax({
      url:"bin/php/get/getGPS.php",
      method:"GET",
      success:function(json){
        var GPS_Data = JSON.parse(json);
        for(i in GPS_Data){
          if(marker[i] && marker[i]['Color'] && marker[i]['Color'] == 'black'){
            var Color = 'black';
          } else if(moment().diff(moment(GPS_Data[i].Time_Stamp,'YYYY-MM-DD HH:mm:ss'), 'minutes') < 30){
            var ClassName = 'New-GPS';
            var Color = 'green';
          } else if(moment().diff(moment(GPS_Data[i].Time_Stamp,'YYYY-MM-DD HH:mm:ss'), 'minutes') < 120) {
            var ClassName = 'Old-GPS';
            var Color = 'yellow';
          } else if(moment().diff(moment(GPS_Data[i].Time_Stamp,'YYYY-MM-DD HH:mm:ss'), 'minutes') < 550) {
            var ClassName = 'Ancient-GPS';
            var Color = 'orange';
          } else {
            var ClassName ='Dead-GPS';
            var Color = 'brown';
          }
          if(marker[i]){
            marker[i].setPosition(new google.maps.LatLng(GPS_Data[i].Latitude, GPS_Data[i].Longitude));
            marker[i].setTitle(GPS_Data[i].Title);
            marker[i].setIcon(pinSymbol(Color));
            marker[i]['Color'] = Color;
            marker[i]['Employee_ID'] = i;
            marker[i]['Ticket_ID'] = GPS_Data[i].Ticket_ID;
          } else {
            marker[i] = new google.maps.Marker({
              map: map,
              position: new google.maps.LatLng(GPS_Data[i].Latitude, GPS_Data[i].Longitude),
              title: GPS_Data[i].Title,
              icon: {
                path:mapIcons.shapes.SQUARE_PIN,
                fillColor:'#00CCBB',
                fillOpacity:0,
                strokeColor:'black',
                strokeWeight:0
              },
              id:i,
              Color:Color,
              Employee_ID:i,
              Ticket_ID:GPS_Data[i].Ticket_ID,
              icon:pinSymbol(Color)
            });
          }
          marker[i].addListener('dblclick', function() {
            $.ajax({
              url:"bin/php/tooltip/GPS.php",
              method:"GET",
              data:{
                ID:this['Employee_ID']
              },
              success:function(code){
                $(".popup").remove();
                $("body").append(code);
              }
            });
          });
          eval("marker_set_" + jsUcfirst(GPS_Data[i]['Supervisor'].replace(/ /g,'').replace(/-/g,'').toLowerCase()) + ".push('" + i + "');");
        }
        GETTING_GPS = 0;
        if(GOT_DIRECTIONS == 0){setTimeout(function(){<?php if(isset($_GET['Latitude'],$_GET['Longitude']) && isset($_GET['Nearest'])){?>find_closest_marker(<?php echo $_GET['Latitude'];?>, <?php echo $_GET['Longitude'];?>);<?php }?>},100);GOT_DIRECTIONS = 1;}
      }
    });
  }
}
function codeAddress(address) {
    geocoder = new google.maps.Geocoder();
    geocoder.geocode({
        'address': address
    }, function(results, status) {
        if (status == google.maps.GeocoderStatus.OK) {
          map.setCenter(results[0].geometry.location);
          map.setZoom(18);
          if(LookUp_Address != null){LookUp_Address.setMap(null);}
          LookUp_Address = new google.maps.Marker({
            map: map,
            position: new google.maps.LatLng(results[0].geometry.location.lat(),results[0].geometry.location.lng()),
            icon: {
              path:mapIcons.shapes.SQUARE_PIN,
              fillColor:'#00CCBB',
              fillOpacity:0,
              strokeColor:'black',
              strokeWeight:0
            },
            zIndex:99999999,
            id:'LookUp_Address',
            icon:flagSymbol('black')
          });
        }
    });

}
function takeServiceCall(){
  $.ajax({
    url:"bin/php/element/map/Service_Call.php",
    method:"GET",
    success:function(code){
      $("body").append(code);
    }
  });
}
function zoomUser(link){
  var val = $(link).val();
  for ( i in marker ){
    if(marker[i].id == val){
      var latlng = new google.maps.LatLng(marker[i].getPosition().lat(), marker[i].getPosition().lng());
      map.setCenter(marker[i].getPosition());
      map.setZoom(15);
      if(LookUp_User != null){
        marker[LookUp_User].setIcon(pinSymbol(marker[LookUp_User]['Color']));
      }
      marker[i].setIcon(pinSymbol('black'));
      marker[i]['Color'] = 'black';
      LookUp_User = i;
    }
  }
}
function breadcrumbUser(link){
  var val = $(link).val();
  document.location.href='map3.php?ID=' + val;
}
var toggle = 0;
function setMapOnAll(mapped) {
  for ( i in marker )
    marker[i].setMap(mapped);
  for ( i in shutdowns )
    shutdowns[i].setMap(mapped);
  for ( i in entrapments )
    entrapments[i].setMap(mapped);
  //marker = new Array();
}
function clearMarkers() {
  setMapOnAll(toggle == 0 ? null : map);
  toggle = toggle == 0 ? 1 : 0;
  Timeline_Supervisor = ''
  $("#Feed").html("");
  REFRESH_DATETIME = '<?php echo date("Y-m-d H:i:s",strtotime('-300 minutes'));?>';
  TIMELINE = new Array();
  getTimeline();
}
$(document).on('click',function(e){
	if($(e.target).closest('.popup:not([class*="directions"])').length === 0){
		$('.popup:not([class*="directions"])').fadeOut(300);
		$('.popup:not([class*="directions"])').remove();
	}
});
</script>
<style>
.popup {
  position:absolute;
  top:10%;
  left:10%;
  width:80%;
  height:80%;
  background-color:#3d3d3d;
  border:3px solid black;
}
</style>
<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAJwGnwOrNUvlYnmB5sdJGkXy8CQsTA46g&callback=renderMap"></script>
<script type='text/javascript' src='https://maps.googleapis.com/maps/api/directions/json?origin=43.65077%2C-79.378425&destination=43.63881%2C-79.42745&key=AIzaSyAJwGnwOrNUvlYnmB5sdJGkXy8CQsTA46g'></script>

</body>
<?php
  }
} else {?><html><head><script>document.location.href='../login.php?Forward=map.php?Type=Live';</script></head></html><?php }
?>
