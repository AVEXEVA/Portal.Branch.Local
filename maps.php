<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
if( isset( $_SESSION[ 'Connection' ][ 'User' ], $_SESSION[ 'Connection' ][ 'Hash' ] ) ){
  //Connection
    $result = \singleton\database::getInstance( )->query(
      'Portal',
      " SELECT  [Connection].[ID]
        FROM    dbo.[Connection]
        WHERE       [Connection].[User] = ?
                AND [Connection].[Hash] = ?;",
      array(
        $_SESSION[ 'Connection' ][ 'User' ],
        $_SESSION[ 'Connection' ][ 'Hash' ]
      )
    );
    $Connection = sqlsrv_fetch_array($result);
    //User
	$result = \singleton\database::getInstance( )->query(
		null,
		" SELECT  Emp.fFirst  AS First_Name,
		          Emp.Last    AS Last_Name,
		          Emp.fFirst + ' ' + Emp.Last AS Name,
		          Emp.Title AS Title,
		          Emp.Field   AS Field
		  FROM  Emp
		  WHERE   Emp.ID = ?;",
		array(
		  	$_SESSION[ 'Connection' ][ 'User' ]
		)
	);
	$User   = sqlsrv_fetch_array( $result );
	//Privileges
	$Access = 0;
	$Hex = 0;
	$result = \singleton\database::getInstance( )->query(
		'Portal',
		"   SELECT  [Privilege].[Access],
                    [Privilege].[Owner],
                    [Privilege].[Group],
                    [Privilege].[Department],
                    [Privilege].[Database],
                    [Privilege].[Server],
                    [Privilege].[Other],
                    [Privilege].[Token],
                    [Privilege].[Internet]
		  FROM      dbo.[Privilege]
		  WHERE     Privilege.[User] = ?;",
		array(
		  	$_SESSION[ 'Connection' ][ 'User' ],
		)
	);
    $Privileges = array();
    if( $result ){while( $Privilege = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC ) ){

        $key = $Privilege['Access'];
        unset( $Privilege[ 'Access' ] );
        $Privileges[ $key ] = implode( '', array(
        	dechex( $Privilege[ 'Owner' ] ),
        	dechex( $Privilege[ 'Group' ] ),
        	dechex( $Privilege[ 'Department' ] ),
        	dechex( $Privilege[ 'Database' ] ),
        	dechex( $Privilege[ 'Server' ] ),
        	dechex( $Privilege[ 'Other' ] ),
        	dechex( $Privilege[ 'Token' ] ),
        	dechex( $Privilege[ 'Internet' ] )
        ) );
    }}
    if( 	!isset( $Connection[ 'ID' ] )
        ||  !isset( $Privileges[ 'Map' ] )
        || 	!check( privilege_read, level_group, $Privileges[ 'Map' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
        \singleton\database::getInstance( )->query(
          null,
          " INSERT INTO Activity([User], [Date], [Page] )
            VALUES( ?, ?, ? );",
          array(
            $_SESSION[ 'Connection' ][ 'User' ],
            date('Y-m-d H:i:s'),
            'maps.php'
        )
      );
?><!DOCTYPE html>
<html lang="en">
<head>
  <title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
     <?php  $_GET[ 'Bootstrap' ] = '5.1';?>
     <?php  $_GET[ 'Entity_CSS' ] = 1;?>
     <?php	require( bin_meta . 'index.php');?>
     <?php	require( bin_css  . 'index.php');?>
     <?php  require( bin_js   . 'index.php');?>
</head>
<body onload='finishLoadingPage();'>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require( bin_php . 'element/navigation.php');?>
        <?php require( bin_php . 'element/loading.php');?>
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


    <!-- Metis Menu Plugin JavaScript -->


    <!-- Morris Charts JavaScript -->
    <!--<script src="https://www.nouveauelevator.com/vendor/raphael/raphael.min.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/morrisjs/morris.min.js"></script>
    <script src="../data/morris-data.php"></script>-->

    <?php require(PROJECT_ROOT.'js/datatables.php');?>
    <!-- Custom Theme JavaScript -->


    <!--Moment JS Date Formatter-->


    <!-- JQUERY UI Javascript -->


    <!-- Custom Date Filters-->

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
        $r = $database->query(null,"
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
