<?php
session_start();
require('../../../php/index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    $r = sqlsrv_query($NEI,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID= ?",array($_SESSION['User']));
    $My_User = sqlsrv_fetch_array($r);
    $Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
    $r = sqlsrv_query($NEI,"
            SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
            FROM   Portal.dbo.Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
    $My_Privileges = array();
    while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
    $Privileged = FALSE;
    if(isset($My_Privileges['Route']) && $My_Privileges['Route']['User_Privilege'] >= 4 && $My_Privileges['Route']['Group_Privilege'] >= 4 && $My_Privileges['Route']['Other_Privilege'] >= 4){$Privileged = TRUE;}
    else {
        if(is_numeric($_GET['ID'])){
                $r = sqlsrv_query($NEI,
                "SELECT
                    Route.ID        AS  ID,
                    Route.Name      AS  Route,
                    Emp.fFirst      AS  First_Name,
                    Emp.Last        AS  Last_Name,
                    Emp.ID          AS  Employee_ID,
                    Emp.fWork       AS  fWork
                FROM
                    Route
                    LEFT JOIN nei.dbo.Emp   ON  Route.Mech = Emp.fWork
                WHERE
                    Route.ID        =   '{$_GET['ID']}'");
            $Route = sqlsrv_fetch_array($r);
            if($My_Privileges['Route']['User_Privilege'] >= 4 && $_SESSION['User'] == $Route['Employee_ID']){$Privileged = TRUE;}
        }
    }
    sqlsrv_query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "route.php"));
    if(!isset($array['ID'])  || !$Privileged || !is_numeric($_GET['ID'])){?><html><head><script>document.location.href="../login.php?Forward=route<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {
        $r = sqlsrv_query($NEI,
            "SELECT
                Route.ID             AS ID,
                Route.Name           AS Route,
                Route.Name           AS Route_Name,
                Route.ID             AS Route_ID,
                Emp.fFirst           AS First_Name,
                Emp.Last             AS Last_Name,
                Emp.ID               AS Employee_ID,
                Emp.fFirst           AS Employee_First_Name,
                Emp.Last             AS Employee_Last_Name,
                Emp.fWork            AS fWork,
                Emp.ID               AS Route_Mechanic_ID,
                Emp.fFirst           AS Route_Mechanic_First_Name,
                Emp.Last             AS Route_Mechanic_Last_Name,
                Rol.Phone            AS Route_Mechanic_Phone_Number,
                Portal.Email         AS Route_Mechanic_Email
            FROM
                Route
                LEFT JOIN nei.dbo.Emp          ON  Route.Mech = Emp.fWork
                LEFT JOIN nei.dbo.Rol          ON Emp.Rol    = Rol.ID
                LEFT JOIN Portal.dbo.Portal    ON Emp.ID     = Portal.Branch_ID AND Portal.Branch = 'Nouveau Elevator'
            WHERE
                Route.ID        =   ?
        ;",array($_GET['ID']));
        $Route = sqlsrv_fetch_array($r);
?>
<div class="panel panel-primary" style='margin-bottom:0px;'>
	<!--<div class='panel-heading' style='color:black;background-color:white;'>-->
	<div class="panel-body">
		<div id="map" style='height:675px;width:100%;'></div>
	</div>
</div>
<script>
$(document).ready(function(){
	//$("#loading-sub-pills").removeClass("active");
	//$("#operations-map-pills").addClass('active');
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
	$r = sqlsrv_query($NEI,"
	   SELECT Loc.Latt     AS Latitude,
            Loc.Long     AS Longitude,
            Loc.Tag      AS Name,
            Loc.Loc      AS ID
     FROM   nei.dbo.Loc
     WHERE  Loc.Route = ?
	;",array($_GET['ID']);
  if($r){while($row = sqlsrv_fetch_array($r)){
    ?>marker[<?php echo $row['ID'];?>] = new google.maps.Marker({
		  position: {lat:<?php echo $row['Latitude'];?>,lng:<?php echo $GPS_Location['Longitude'];?>},
		  map: map,
		  title: '<?php echo $row['Name'];?>',
		  Icon: 'http://vps9073.inmotionhosting.com/~skeera6/portal/images/GoogleMapsMarkers/paleblue_MarkerG.png'
		})
		marker[<?php echo $key?>].addListener('click',function(){
			document.location.href='location.php?ID=<?php echo $GPS_Location['ID'];?>';
		});<?php
  }}?>
</script>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
