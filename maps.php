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
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require(PROJECT_ROOT.'php/element/navigation/index2.php');?>
        <?php require(PROJECT_ROOT.'php/element/loading.php');?>
        <div id="page-wrapper" class='content' style='margin-right:0px !important;'>
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-primary">
                        <div class="panel-heading"><h3>Map</h3></div>
                        <div class='panel-heading' style='color:black;background-color:white;'><button onClick="document.location.href='map.php?Type=Live';">Live View</button><button onClick="document.location.href='map.php?Type=1D';">24 Hour View</button><button onClick="document.location.href='map.php?Type=2D';">48 Hour View</button></div>
                        <div class="panel-body"><div id="map" style='height:675px;width:100%;'></div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap Core JavaScript -->
    <script src="../vendor/bootstrap/js/bootstrap.min.js"></script>

    <!-- Metis Menu Plugin JavaScript -->
    <script src="../vendor/metisMenu/metisMenu.js"></script>

    <!-- Morris Charts JavaScript -->
    <!--<script src="../vendor/raphael/raphael.min.js"></script>
    <script src="../vendor/morrisjs/morris.min.js"></script>
    <script src="../data/morris-data.php"></script>-->

    <?php require(PROJECT_ROOT.'js/datatables.php');?>
    <!-- Custom Theme JavaScript -->
    <script src="../dist/js/sb-admin-2.js"></script>

    <!--Moment JS Date Formatter-->
    <script src="../dist/js/moment.js"></script>

    <!-- JQUERY UI Javascript -->
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

    <!-- Custom Date Filters-->
    <script src="../dist/js/filters.js"></script>
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
        if($_GET['Type']       == 'Live'){
            $Start_Date            = new DateTime('now');
            $Start_Date            = $Start_Date->format("Y-m-d 00:00:00.000");
            $End_Date              = new DateTime('now');
            $End_Date              = $End_Date->format("Y-m-d 23:59:59.999");
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
        $r = sqlsrv_query($NEI,"
            SELECT
                TechLocation.*,
                Emp.fFirst AS First_Name,
                Emp.Last   AS Last_Name,
                Emp.fWork,
				Emp.fWork  AS Employee_Work_ID,
                Emp.ID as Employee_ID
            FROM
                TechLocation
                LEFT JOIN Emp ON TechLocation.TechID = Emp.fWork
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
            if(isset($array['On site time'])){
                $GPS_Location = $array['On site time'];
                ?>
                marker[<?php echo $key;?>] = new google.maps.Marker({
                  position: {lat:<?php echo $GPS_Location['Latitude'];?>,lng:<?php echo $GPS_Location['Longitude'];?>},
                  map: map,
                  title: '<?php echo $GPS_Location['First_Name'] . " " . $GPS_Location['Last_Name'];?> -- <?php echo date("m/d/Y H:i:s",strtotime($GPS_Location['DateTimeRecorded']));?> -- <?php echo $GPS_Location['TicketID'];?>',
                  Icon: 'https://vps9073.inmotionhosting.com/~skeera6/portal/images/GoogleMapsMarkers/<?php if($GPS_Location['ActionGroup'] == 'Completed time'){?>green_MarkerC<?php }
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
                  title: '<?php echo $GPS_Location['First_Name'] . " " . $GPS_Location['Last_Name'];?> -- <?php echo date("m/d/Y H:i:s",strtotime($GPS_Location['DateTimeRecorded']));?> -- <?php echo $GPS_Location['TicketID'];?>',
                  Icon: 'https://vps9073.inmotionhosting.com/~skeera6/portal/images/GoogleMapsMarkers/<?php if($GPS_Location['ActionGroup'] == 'Completed time'){?>green_MarkerC<?php }
                  elseif($GPS_Location['ActionGroup'] == 'On site time'){?>yellow_MarkerO<?php }
                elseif($GPS_Location['ActionGroup'] == 'General'){?>paleblue_MarkerG<?php }?>.png'
                });
                marker[<?php echo $key?>].addListener('click',function(){
                    document.location.href='ticket.php?ID=<?php echo $GPS_Location['TicketID'];?>';
                });
            <?php }
            /*foreach($array['General'] as $k=>$GPS_Location){?>
                marker[<?php echo $k;?>] = new google.maps.Marker({
                  position: {lat:<?php echo $GPS_Location['Latitude'];?>,lng:<?php echo $GPS_Location['Longitude'];?>},
                  map: map,
                  title: '<?php echo $GPS_Location['fFirst'] . " " . $GPS_Location['Last'];?> -- <?php echo $GPS_Location['DateTimeRecorded'];?> -- <?php echo $GPS_Location['TicketID'];?>',
                  Icon: 'http://vps9073.inmotionhosting.com/~skeera6/portal/images/GoogleMapsMarkers/<?php if($GPS_Location['ActionGroup'] == 'Completed time'){?>green_MarkerC<?php }
                  elseif($GPS_Location['ActionGroup'] == 'On site time'){?>yellow_MarkerO<?php }
                elseif($GPS_Location['ActionGroup'] == 'General'){?>paleblue_MarkerG<?php }?>.png'
                });
                marker[<?php echo $k?>].addListener('click',function(){
                    document.location.href='tickets.php?Mechanic=<?php echo $GPS_Location['Employee_ID'];?>';
                });
            <?php }*/
        }?>}</script>
            <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?sensor=false"></script>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=map.php?Type=Live';</script></head></html><?php }?>
