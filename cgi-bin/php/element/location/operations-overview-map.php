 <?php 
session_start( [ 'read_and_close' => true ] );
require('../../../../cgi-bin/php/index.php');

if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = $database->query(null,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Elevator'){
    	$My_User = $database->query(null,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID = ?",array($_SESSION['User']));
        $My_User = sqlsrv_fetch_array($My_User); 
        $Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
        $r = $database->query($Portal,"
            SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
            FROM   Portal.dbo.Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
        $My_Privileges = array();
        while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
        $Privileged = FALSE;
        if(isset($My_Privileges['Location']) && $My_Privileges['Location']['User_Privilege'] >= 4 && $My_Privileges['Location']['Group_Privilege'] >= 4 && $My_Privileges['Location']['Other_Privilege'] >= 4){$Privileged = TRUE;}
        elseif($My_Privileges['Location']['User_Privilege'] >= 4 && is_numeric($_GET['ID'])){
            $r = $database->query(  null,"SELECT * FROM nei.dbo.TicketO WHERE TicketO.LID='{$_GET['ID']}' AND fWork='{$My_User['fWork']}'");
            $r2 = $database->query( null,"SELECT * FROM nei.dbo.TicketD WHERE TicketD.Loc='{$_GET['ID']}' AND fWork='{$My_User['fWork']}'");
            $r3 = $database->query( null,"SELECT * FROM nei.dbo.TicketDArchive WHERE TicketDArchive.Loc='{$_GET['ID']}' AND fWork='{$My_User['fWork']}'");
            $r = sqlsrv_fetch_array($r);
            $r2 = sqlsrv_fetch_array($r2);
			$r3 = sqlsrv_fetch_array($r3);
            $Privileged = (is_array($r) || is_array($r2) || is_array($r3)) ? TRUE : FALSE;
        }
    } elseif($_SESSION['Branch'] == 'Customer' && $_SESSION['Branch_ID'] == $_GET['ID']){$Privileged = TRUE;}
    //
    if(!isset($array['ID'])  || !is_numeric($_GET['ID']) || !$Privileged || !is_numeric($_GET['ID'])){?><html><head><script>document.location.href="../login.php?Forward=customer<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {
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
					LEFT JOIN nei.dbo.Zone         ON Loc.Zone   = Zone.ID
					LEFT JOIN nei.dbo.Route        ON Loc.Route  = Route.ID
					LEFT JOIN nei.dbo.Emp          ON Route.Mech = Emp.fWork
					LEFT JOIN nei.dbo.OwnerWithRol ON Loc.Owner  = OwnerWithRol.ID
					LEFT JOIN Terr         		   ON Terr.ID    = Loc.Terr
			WHERE
					Loc.Loc = ?
		;",array($_GET['ID']));
		$Location = sqlsrv_fetch_array($r);?>
<div class='tab-pane fade in active' id='operations-overview-pills'>
	<div class="panel panel-primary" style='margin-bottom:0px;'>
		<div class="panel-heading"><h4>Map</h4></div>
		<div class="panel-body">
			<div id='operations-overview-map'></div>
		</div>
	</div>
</div>
<script>
$(document).ready(function(){
	$("#loading-sub-pills").removeClass("active");
	$("#operations-overview-pills").addClass('active');
});
</script>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>