<?php 
session_start();
require('../../../php/index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Elevator'){
        sqlsrv_query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "ticket.php"));
        $r = sqlsrv_query($NEI,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID= ?",array($_SESSION['User']));
        $My_User = sqlsrv_fetch_array($r);
        $Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
        $r = sqlsrv_query($Portal,"
            SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
            FROM   Portal.dbo.Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
        $My_Privileges = array();
        while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
        $Privileged = FALSE;
        if(isset($My_Privileges['Ticket']) && $My_Privileges['Ticket']['User_Privilege'] >= 4 && $My_Privileges['Ticket']['Group_Privilege'] >= 4 && $My_Privileges['Location']['Other_Privilege'] >= 4){$Privileged = TRUE;}
        elseif($My_Privileges['Ticket']['Group_Privilege'] >= 4){
            $r = sqlsrv_query(  $NEI,"SELECT LID FROM nei.dbo.TicketO WHERE TicketO.ID='{$_GET['ID']}'");
            $r2 = sqlsrv_query( $NEI,"SELECT Loc FROM nei.dbo.TicketD WHERE TicketD.ID='{$_GET['ID']}'");
            $r3 = sqlsrv_query( $NEI,"SELECT Loc FROM nei.dbo.TicketDArchive WHERE TicketDArchive.ID='{$_GET['ID']}'");
            $r = sqlsrv_fetch_array($r);
            $r2 = sqlsrv_fetch_array($r2);
            $r3 = sqlsrv_fetch_array($r3);
            $Location = NULL;
            if(is_array($r)){$Location = $r['LID'];}
            elseif(is_array($r2)){$Location = $r2['Loc'];}
            elseif(is_array($r3)){$Location = $r3['Loc'];}
            if(!is_null($Location)){
                $r = sqlsrv_query(  $NEI,"SELECT ID FROM nei.dbo.TicketO WHERE TicketO.LID='{$Location}' AND fWork='{$My_User['fWork']}'");
                $r2 = sqlsrv_query( $NEI,"SELECT ID FROM nei.dbo.TicketD WHERE TicketD.Loc='{$Location}' AND fWork='{$My_User['fWork']}'");
                $r3 = sqlsrv_query( $NEI,"SELECT ID FROM nei.dbo.TicketDArchive WHERE TicketDArchive.Loc='{$Location}' AND fWork='{$My_User['fWork']}'");
                if($r || $r2 || $r3){
                    if($r){$a = sqlsrv_fetch_array($r);}
                    if($r2){$a2 = sqlsrv_fetch_array($r2);}
                    if($r3){$a3 = sqlsrv_fetch_array($r3);}
                    if($a || $a2 || $a3){
                        $Privileged = true;
                    }
                }
            }
            if(!$Privileged){
                if($My_Privileges['Ticket']['User_Privilege'] >= 4 && is_numeric($_GET['ID'])){
                    $r = sqlsrv_query(  $NEI,"SELECT ID FROM nei.dbo.TicketO WHERE TicketO.ID='{$_GET['ID']}' AND fWork='{$User['fWork']}'");
                    $r2 = sqlsrv_query( $NEI,"SELECT ID FROM nei.dbo.TicketD WHERE TicketD.ID='{$_GET['ID']}' AND fWork='{$User['fWork']}'");
                    $r3 = sqlsrv_query( $NEI,"SELECT ID FROM nei.dbo.TicketDArchive WHERE TicketDArchive.ID='{$_GET['ID']}' AND fWork='{$User['fWork']}'");
                    if($r || $r2 || $r3){
                        if($r){$a = sqlsrv_fetch_array($r);}
                        if($r2){$a2 = sqlsrv_fetch_array($r2);}
                        if($r3){$a3 = sqlsrv_fetch_array($r3);}
                        if($a || $a2 || $a3){
                            $Privileged = true;
                        }
                    }
                }
            }
        }
    } elseif($_SESSION['Branch'] == 'Customer' && is_numeric($_GET['ID'])){
            $r  = sqlsrv_query( $NEI,"SELECT Loc.Loc FROM nei.dbo.TicketO        LEFT JOIN nei.dbo.Loc ON TicketO.LID        = Loc.Loc WHERE TicketO.ID=?        AND Loc.Owner = ?;",array($_GET['ID'],$_SESSION['Branch_ID']));
            $r2 = sqlsrv_query( $NEI,"SELECT Loc.Loc FROM nei.dbo.TicketD        LEFT JOIN nei.dbo.Loc ON TicketD.Loc        = Loc.Loc WHERE TicketD.ID=?        AND Loc.Owner = ?;",array($_GET['ID'],$_SESSION['Branch_ID']));
            $r3 = sqlsrv_query( $NEI,"SELECT Loc.Loc FROM nei.dbo.TicketDArchive LEFT JOIN nei.dbo.Loc ON TicketDArchive.Loc = Loc.Loc WHERE TicketDArchive.ID=? AND Loc.Owner = ?;",array($_GET['ID'],$_SESSION['Branch_ID']));
            if($r || $r2 || $r3){
                if($r){$a = sqlsrv_fetch_array($r);}else{$a = false;}
                if($r2){$a2 = sqlsrv_fetch_array($r2);}else{$a2 = false;}
                if($r3){$a3 = sqlsrv_fetch_array($r3);}else{$a3 = false;}
                if($a || $a2 || $a3){
                    $Privileged = true;
                }
            }
    }
    
    if(!isset($array['ID'])  || !$Privileged){?><html><head></head></html><?php }
    else {
$Ticket = null;
if(isset($_GET['ID']) && is_numeric($_GET['ID'])){
    $r = sqlsrv_query($NEI,"
            SELECT 
                TicketO.*, 
                Loc.Tag             AS Tag, 
                Loc.Loc              AS Location_ID,
                Loc.Address         AS Address, 
                Loc.City            AS City, 
                Loc.State           AS State, 
                Loc.Zip             AS Zip, 
                Job.ID              AS Job_ID, 
                Job.fDesc           AS Job_Description, 
                OwnerWithRol.ID     AS Owner_ID, 
                OwnerWithRol.ID     AS Customer_ID, 
                OwnerWithRol.Name   AS Customer, 
                JobType.Type        AS Job_Type,
                Elev.ID             AS Unit_ID,
                Elev.Unit           AS Unit_Label, 
                Elev.State          AS Unit_State, 
                Elev.Type           AS Unit_Type,
                Zone.Name           AS Division, 
                TicketPic.PicData   AS PicData,
                TickOStatus.Type    AS Status, 
                Emp.ID              AS Employee_ID, 
                Emp.fFirst          AS First_Name, 
                Emp.Last            AS Last_Name,
                Emp.Title           AS Role
            FROM
                nei.dbo.TicketO
                LEFT JOIN nei.dbo.Loc           ON TicketO.LID      = Loc.Loc
                LEFT JOIN nei.dbo.Job           ON TicketO.Job      = Job.ID
                LEFT JOIN nei.dbo.OwnerWithRol  ON Loc.Owner    = OwnerWithRol.ID
                LEFT JOIN nei.dbo.JobType       ON Job.Type         = JobType.ID
                LEFT JOIN nei.dbo.Elev          ON TicketO.LElev    = Elev.ID
                LEFT JOIN nei.dbo.Zone          ON Loc.Zone         = Zone.ID
                LEFT JOIN nei.dbo.TickOStatus   ON TicketO.Assigned = TickOStatus.Ref
                LEFT JOIN nei.dbo.Emp           ON TicketO.fWork    = Emp.fWork
                LEFT JOIN nei.dbo.TicketPic     ON TicketO.ID       = TicketPic.TicketID 
            WHERE
                TicketO.ID=?;",array($_GET['ID']));
    $Ticket = sqlsrv_fetch_array($r);
    $Ticket['Loc'] = $Ticket['LID'];
    $Ticket['Status'] = ($Ticket['Status'] == 'Completed') ? "Reviewing" : $Ticket['Status'];
    if($Ticket['ID'] == "" || $Ticket['ID'] == 0 || !isset($Ticket['ID'])){
        $r = sqlsrv_query($NEI,"
            SELECT
                TicketD.*, 
                Loc.Tag             AS Tag, 
                Loc.Loc              AS Location_ID,
                Loc.Address         AS Address, 
                Loc.City            AS City, 
                Loc.State           AS State, 
                Loc.Zip             AS Zip, 
                Job.ID              AS Job_ID, 
                Job.fDesc           AS Job_Description, 
                OwnerWithRol.ID     AS Owner_ID, 
                OwnerWithRol.ID     AS Customer_ID,
                OwnerWithRol.Name   AS Customer, 
                JobType.Type        AS Job_Type, 
                Elev.ID             AS Unit_ID,
                Elev.Unit           AS Unit_Label, 
                Elev.State          AS Unit_State,
                Elev.Type           AS Unit_Type,
                Zone.Name           AS Division, 
                TicketPic.PicData   AS PicData, 
                Emp.ID              AS Employee_ID, 
                Emp.fFirst          AS First_Name, 
                Emp.Last            AS Last_Name,
                Emp.Title           AS Role,
				'Completed'         AS Status
            FROM
                nei.dbo.TicketD
                LEFT JOIN nei.dbo.Loc           ON TicketD.Loc      = Loc.Loc
                LEFT JOIN nei.dbo.Job           ON TicketD.Job      = Job.ID
                LEFT JOIN nei.dbo.OwnerWithRol  ON Loc.Owner        = OwnerWithRol.ID
                LEFT JOIN nei.dbo.JobType       ON Job.Type         = JobType.ID
                LEFT JOIN nei.dbo.Elev          ON TicketD.Elev     = Elev.ID
                LEFT JOIN nei.dbo.Zone          ON Loc.Zone         = Zone.ID
                LEFT JOIN nei.dbo.Emp           ON TicketD.fWork    = Emp.fWork
                LEFT JOIN nei.dbo.TicketPic     ON TicketD.ID       = TicketPic.TicketID
            WHERE
                TicketD.ID = ?;",array($_GET['ID']));
        $Ticket = sqlsrv_fetch_array($r);
    }
    if($Ticket['ID'] == "" || $Ticket['ID'] == 0 || !isset($Ticket['ID'])){
        $r = sqlsrv_query($NEI,"
            SELECT
                TicketDArchive.*, 
                Loc.Tag             AS Tag,
                Loc.Loc              AS Location_ID, 
                Loc.Address         AS Address,
				Loc.Loc             AS Location_Loc,
                Loc.City            AS City, 
                Loc.State           AS State, 
                Loc.Zip             AS Zip, 
                Job.ID              AS Job_ID, 
                Job.fDesc           AS Job_Description, 
                OwnerWithRol.ID     AS Owner_ID, 
                OwnerWithRol.ID     AS Customer_ID,
                OwnerWithRol.Name   AS Customer, 
                JobType.Type        AS Job_Type, 
                Elev.ID             AS Unit_ID,
                Elev.Unit           AS Unit_Label, 
                Elev.State          AS Unit_State,
                Elev.Type           AS Unit_Type,
                Zone.Name           AS Division, 
                TicketPic.PicData   AS PicData, 
                Emp.ID              AS Employee_ID, 
				Emp.ID              AS User_ID,
                Emp.fFirst          AS First_Name, 
                Emp.Last            AS Last_Name,
                Emp.Title           AS Role,
				'Completed'         AS Status
            FROM
                nei.dbo.TicketDArchive 
                LEFT JOIN nei.dbo.Loc           ON TicketDArchive.Loc = Loc.Loc
                LEFT JOIN nei.dbo.Job           ON TicketDArchive.Job = Job.ID
                LEFT JOIN nei.dbo.OwnerWithRol  ON Loc.Owner = OwnerWithRol.ID
                LEFT JOIN nei.dbo.JobType       ON Job.Type = JobType.ID
                LEFT JOIN nei.dbo.Elev          ON TicketDArchive.Elev = Elev.ID 
                LEFT JOIN nei.dbo.Zone          ON Loc.Zone = Zone.ID
                LEFT JOIN nei.dbo.Emp           ON TicketDArchive.fWork = Emp.fWork
                LEFT JOIN nei.dbo.TicketPic     ON TicketDArchive.ID = TicketPic.TicketID
            WHERE
                TicketDArchive.ID = ?;",array($_GET['ID']));
        $Ticket = sqlsrv_fetch_array($r);
    }?>
<div class="panel panel-primary" style='margin-bottom:0px;'>
    <!--<div class='panel-heading' style='color:black;background-color:white;'>-->
    <div class="panel-body">
        <div id="map" style='height:675px;width:100%;'></div>
    </div>
</div>
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
    SELECT TOP 100 TechLocation.* 
    FROM   nei.dbo.TechLocation
    WHERE  TicketID = '" . $Ticket['ID'] . "';");
$GPS_Locations = array();
while($array = sqlsrv_fetch_array($r)){$GPS_Locations[] = $array;}
if(count($GPS_Locations) > 0){foreach($GPS_Locations as $key=>$GPS_Location){?>
    marker[<?php echo $key;?>] = new google.maps.Marker({
      position: {lat:<?php echo substr($GPS_Location['Latitude'],0,7);?>,lng:<?php echo substr($GPS_Location['Longitude'],0,8);?>},
      map: map,
      title: '<?php echo $GPS_Location['ActionGroup'];?>'
    });
<?php }}?>}</script>
<script>
$(document).ready(function(){
    initialize();
});
</script>

<?php
        }
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=ticket<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>