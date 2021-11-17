<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( [ 'read_and_close' => true ] ); 
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
if( isset($_SESSION[ 'User' ], $_SESSION[ 'Hash' ] ) ){
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
        "   SELECT  Privilege.Access, 
                    Privilege.Owner, 
                    Privilege.Group, 
                    Privilege.Other
            FROM    Privilege
            WHERE   Privilege.User_ID = ?;",
        array(
            $_SESSION[ 'User' ]
        )
    );
    $Privileges = array();
    while($Privilege = sqlsrv_fetch_array($r)){$Privileges[$Privilege['Access']] = $Privilege;}
    $Privileged = FALSE;
    if( isset($Privileges['Unit']) 
        && $Privileges['Unit']['Owner'] >= 4 
        && $Privileges['Unit']['Group'] >= 4 
        && $Privileges['Unit']['Other'] >= 4){$Privileged = TRUE;}
    elseif($Privileges['Unit']['Owner'] >= 4 && is_numeric($_GET['ID'])){
        $r = $database->query(  
            null,
            "   SELECT  Count( Ticket.Count ) AS Count 
                FROM    (
                            SELECT  Ticket.Unit,
                                    Ticket.Field,
                                    Sum( Ticket.Count ) AS Count
                            FROM (
                                (
                                    SELECT      TicketO.LElev AS Unit,
                                                TicketO.fWork AS Field,
                                                Count( TicketO.ID ) AS Count
                                    FROM        TicketO
                                    GROUP BY    TicketO.LElev,
                                                TicketO.fWork
                                ) UNION ALL (
                                    SELECT      TicketD.Elev AS Unit,
                                                TicketD.fWork AS Field, 
                                                Count( TicketD.ID ) AS Count
                                    FROM        TicketD
                                    GROUP BY    TicketD.Elev,
                                                TicketD.fWork
                                )
                            ) AS Ticket
                            GROUP BY    Ticket.Unit,
                                        Ticket.Field
                        ) AS Ticket
                        LEFT JOIN Emp AS Employee ON Ticket.Field = Employee.fWork
                WHERE   Employee.ID = ?
                        AND Ticket.Unit = ?;",
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
            ?><html><head><script>document.location.href="../login.php?Forward=unit<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {
        $r = $database->query(null,
          " SELECT  TOP 1
                    Elev.ID,
                    Elev.Unit           AS Building_ID,
                    Elev.State          AS City_ID,
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
                    Owner.ID            AS Customer_ID,
                    OwnerRol.Name       AS Customer_Name,
                    OwnerRol.Contact    AS Customer_Contact,
                    OwnerRol.Address    AS Customer_Street,
                    OwnerRol.City       AS Customer_City,
                    OwnerRol.State      AS Customer_State,
                    Emp.ID              AS Route_Mechanic_ID,
                    Emp.fFirst          AS Route_Mechanic_First_Name,
                    Emp.Last            AS Route_Mechanic_Last_Name
            FROM    Elev
                    LEFT JOIN Loc               ON Elev.Loc     = Loc.Loc
                    LEFT JOIN Zone              ON Loc.Zone     = Zone.ID
                    LEFT JOIN OwnerWithRol      ON Loc.Owner    = OwnerWithRol.ID
                    LEFT JOIN Route             ON Loc.Route    = Route.ID
                    LEFT JOIN Emp               ON Route.Mech   = Emp.fWork
                    LEFT JOIN Owner             ON Loc.Owner    = Owner.ID 
                    LEFT JOIN Rol AS OwnerRol   ON OwnerRol.ID  = Owner.Rol
            WHERE   Elev.ID = ?;",
          array(
            $_GET[ 'ID' ]
          )
        );
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
?><style>
	table#Location_Hours tbody tr td, table#Location_Hours thead tr th {
		border:1px solid black;
		padding:3px;
	}
</style>
<table width="100%" class="" id="Location_Hours">
<thead>
	<tr>
		<th>Weeks</th>
		<th>Thu</th>
		<th>Fri</th>
		<th>Sat</th>
		<th>Sun</th>
		<th>Mon</th>
		<th>Tue</th>
		<th>Wed</th>
		<th>Total</th>
	</tr>
</thead>
<style>
.hoverGray:hover {
	background-color:#dfdfdf !important;
}
</style>
<tbody>
	<tr><td colspan='9'><?php require('../../../php/element/loading-active.php');?></td></tr>
	<tr style='cursor:pointer;' class="odd gradeX hoverGray">
		<?php $Today = date('l');
		$Date = date('Y-m-d');
		if($Today == 'Thursday'){$WeekOf = date('Y-m-d');}
		elseif($Today == 'Friday'){$WeekOf = date('Y-m-d', strtotime($Date . ' -1 days'));}
		elseif($Today == 'Saturday'){$WeekOf = date('Y-m-d', strtotime($Date . ' -2 days'));}
		elseif($Today == 'Sunday'){$WeekOf = date('Y-m-d', strtotime($Date . ' -3 days'));}
		elseif($Today == 'Monday'){$WeekOf = date('Y-m-d', strtotime($Date . ' -4 days'));}
		elseif($Today == 'Tuesday'){$WeekOf = date('Y-m-d', strtotime($Date . ' -5 days'));}
		elseif($Today == 'Wednesday'){$WeekOf = date('Y-m-d', strtotime($Date . ' -6 days'));}
		$WeekOf = date('Y-m-d',strtotime($WeekOf . ' +6 days'));?>
		<td class='WeekOf' rel='<?php echo $WeekOf;?>' onClick="refresh_this(this);"><?php
			echo $WeekOf;
		?></td><?php 
		$Today = date('l');
		if($Today == 'Thursday'){$Thursday = date('Y-m-d');}
		elseif($Today == 'Friday'){$Thursday = date('Y-m-d', strtotime($Date . ' -1 days'));}
		elseif($Today == 'Saturday'){$Thursday = date('Y-m-d', strtotime($Date . ' -2 days'));}
		elseif($Today == 'Sunday'){$Thursday = date('Y-m-d', strtotime($Date . ' -3 days'));}
		elseif($Today == 'Monday'){$Thursday = date('Y-m-d', strtotime($Date . ' -4 days'));}
		elseif($Today == 'Tuesday'){$Thursday = date('Y-m-d', strtotime($Date . ' -5 days'));}
		elseif($Today == 'Wednesday'){$Thursday = date('Y-m-d', strtotime($Date . ' -6 days'));}
		$r = $database->query(null,"
			SELECT Sum(Total) as Summed 
			FROM TicketD LEFT JOIN Job ON TicketD.Job = Job.ID 
			WHERE TicketD.Elev='" . $_GET['ID'] . "' and EDate >= '" . $Thursday . " 00:00:00.000' AND EDate <= '" . $Thursday . " 23:59:59.999'");?>
		<td class='Thursday' rel='<?php echo $Thursday;?>' onClick="refresh_this(this);"><?php
			echo sqlsrv_fetch_array($r)['Summed'];
		?></td>
		<?php 
		if($Today == 'Friday'){$Friday = date('Y-m-d');}
		elseif($Today == 'Saturday'){$Friday = date('Y-m-d', strtotime($Date . ' -1 days'));}
		elseif($Today == 'Sunday'){$Friday = date('Y-m-d', strtotime($Date . ' -2 days'));}
		elseif($Today == 'Monday'){$Friday = date('Y-m-d', strtotime($Date . ' -3 days'));}
		elseif($Today == 'Tuesday'){$Friday = date('Y-m-d', strtotime($Date . ' -4 days'));}
		elseif($Today == 'Wednesday'){$Friday = date('Y-m-d', strtotime($Date . ' -5 days'));}
		elseif($Today == 'Thursday'){$Friday = date('Y-m-d', strtotime($Date . ' +1 days'));}
		$r = $database->query(null,"
			SELECT Sum(Total) AS Summed 
			FROM TicketD
			WHERE TicketD.Elev='" . $_GET['ID'] . "' and EDate >= '" . $Friday . " 00:00:00.000' AND EDate <= '" . $Friday . " 23:59:59.999'");?>
		<td class='Friday' rel='<?php echo $Friday;?>' onClick="refresh_this(this);"><?php
			echo sqlsrv_fetch_array($r)['Summed'];
		?></td>
		<?php
		if($Today == 'Satuday'){$Saturday = date('Y-m-d');}
		elseif($Today == 'Sunday'){$Saturday = date('Y-m-d', strtotime($Date . ' -1 days'));}
		elseif($Today == 'Monday'){$Saturday = date('Y-m-d', strtotime($Date . ' -2 days'));}
		elseif($Today == 'Tuesday'){$Saturday = date('Y-m-d', strtotime($Date . ' -3 days'));}
		elseif($Today == 'Wednesday'){$Saturday = date('Y-m-d', strtotime($Date . ' -4 days'));}
		elseif($Today == 'Thursday'){$Saturday = date('Y-m-d', strtotime($Date . ' +2 days'));}
		elseif($Today == 'Friday'){$Saturday = date('Y-m-d', strtotime($Date . ' +1 days'));}
		$r = $database->query(null,"
			SELECT Sum(Total) AS Summed
			FROM TicketD
			WHERE TicketD.Elev='" . $_GET['ID'] . "' and EDate >= '" . $Saturday . " 00:00:00.000' AND EDate <= '" . $Saturday . " 23:59:59.999'");?>
		<td class='Saturday' rel='<?php echo $Saturday;?>' onClick="refresh_this(this);"><?php
			echo sqlsrv_fetch_array($r)['Summed'];
		?></td>
		<?php 
		if($Today == 'Sunday'){$Sunday = date('Y-m-d');}
		elseif($Today == 'Monday'){$Sunday = date('Y-m-d', strtotime($Date . ' -1 days'));}
		elseif($Today == 'Tuesday'){$Sunday = date('Y-m-d', strtotime($Date . ' -2 days'));}
		elseif($Today == 'Wednesday'){$Sunday = date('Y-m-d', strtotime($Date . ' -3 days'));}
		elseif($Today == 'Thursday'){$Sunday = date('Y-m-d', strtotime($Date . ' +3 days'));}
		elseif($Today == 'Friday'){$Sunday = date('Y-m-d', strtotime($Date . ' +2 days'));}
		elseif($Today == 'Saturday'){$Sunday = date('Y-m-d', strtotime($Date . ' +1 days'));}
		$r = $database->query(null,"
			SELECT Sum(Total) AS Summed 
			FROM TicketD LEFT JOIN Job ON TicketD.Job = Job.ID 
			WHERE TicketD.Elev='" . $_GET['ID'] . "' and EDate >= '" . $Sunday . " 00:00:00.000' AND EDate <= '" . $Sunday . " 23:59:59.999'");?>
		<td class='Sunday' rel='<?php echo $Sunday;?>' onClick="refresh_this(this);"><?php
			echo sqlsrv_fetch_array($r)['Summed'];
		?></td>
		<?php
		if($Today == 'Monday'){$Monday = date('Y-m-d');}
		elseif($Today == 'Tuesday'){$Monday = date('Y-m-d', strtotime($Date . ' -1 days'));}
		elseif($Today == 'Wednesday'){$Monday = date('Y-m-d', strtotime($Date . ' -2 days'));}
		elseif($Today == 'Thursday'){$Monday = date('Y-m-d', strtotime($Date . ' +4 days'));}
		elseif($Today == 'Friday'){$Monday = date('Y-m-d', strtotime($Date . ' +3 days'));}
		elseif($Today == 'Saturday'){$Monday = date('Y-m-d', strtotime($Date . ' +2 days'));}
		elseif($Today == 'Sunday'){$Monday = date('Y-m-d', strtotime($Date . ' +1 days'));}
		$r = $database->query(null,"
			SELECT Sum(Total) AS Summed
			FROM TicketD
			WHERE TicketD.Elev='" . $_GET['ID'] . "' and EDate >= '" . $Monday . " 00:00:00.000' AND EDate <= '" . $Monday . " 23:59:59.999'");?>
		<td class='Monday' rel='<?php echo $Monday;?>' onClick="refresh_this(this);"><?php
			echo sqlsrv_fetch_array($r)['Summed'];
		?></td>
		<?php 
		if($Today == 'Tuesday'){$Tuesday = date('Y-m-d');}
		elseif($Today == 'Wednesday'){$Tuesday = date('Y-m-d', strtotime($Date . ' -1 days'));}
		elseif($Today == 'Thursday'){$Tuesday = date('Y-m-d', strtotime($Date . ' +5 days'));}
		elseif($Today == 'Friday'){$Tuesday = date('Y-m-d', strtotime($Date . ' +4 days'));}
		elseif($Today == 'Saturday'){$Tuesday = date('Y-m-d', strtotime($Date . ' +3 days'));}
		elseif($Today == 'Sunday'){$Tuesday = date('Y-m-d', strtotime($Date . ' +2 days'));}
		elseif($Today == 'Monday'){$Tuesday = date('Y-m-d', strtotime($Date . ' +1 days'));}
		$r = $database->query(null,"
			SELECT Sum(Total) AS Summed
			FROM TicketD
			WHERE TicketD.Elev='" . $_GET['ID'] . "' and EDate >= '" . $Tuesday . " 00:00:00.000' AND EDate <= '" . $Tuesday . " 23:59:59.999'");?>
		<td class='Tuesday' rel='<?php echo $Tuesday;?>' onClick="refresh_this(this);"><?php
			echo sqlsrv_fetch_array($r)['Summed'];
		?></td>
		<?php 
		if($Today == 'Wednesday'){$Wednesday = date('Y-m-d');}	
		elseif($Today == 'Thursday'){$Wednesday = date('Y-m-d', strtotime($Date . ' +6 days'));}
		elseif($Today == 'Friday'){$Wednesday = date('Y-m-d', strtotime($Date . ' +5 days'));}
		elseif($Today == 'Saturday'){$Wednesday = date('Y-m-d', strtotime($Date . ' +4 days'));}
		elseif($Today == 'Sunday'){$Wednesday = date('Y-m-d', strtotime($Date . ' +3 days'));}
		elseif($Today == 'Monday'){$Wednesday = date('Y-m-d', strtotime($Date . ' +2 days'));}
		elseif($Today == 'Tuesday'){$Wednesday = date('Y-m-d', strtotime($Date . ' +1 days'));}
		$r = $database->query(null,"
			SELECT Sum(Total) AS Summed
			FROM TicketD LEFT JOIN Job ON TicketD.Job = Job.ID
			WHERE TicketD.Elev='" . $_GET['ID'] . "' and EDate >= '" . $Wednesday . " 00:00:00.000' AND EDate <= '" . $Wednesday . " 23:59:59.999'");?>
		<td class='Wednesday' rel='<?php echo $Wednesday;?>' onClick="refresh_this(this);"><?php
			echo sqlsrv_fetch_array($r)['Summed'];
		?></td>
		<td><?php
			$r = $database->query(null,"SELECT Sum(Total) AS Summed FROM TicketD LEFT JOIN Job ON TicketD.Job = Job.ID WHERE TicketD.Elev='" . $_GET['ID'] . "' and EDate >= '" . $Thursday . " 00:00:00.000' AND EDate <= '" . $Wednesday . " 23:59:59.999'");
			echo sqlsrv_fetch_array($r)['Summed'];
		?></td>
	</tr>
	<?php while($WeekOf > "2017-03-08 00:00:00.000"){?><tr style='cursor:pointer;' class="odd gradeX hoverGray">
		<?php $WeekOf = date('Y-m-d',strtotime($WeekOf . '-7 days')); ?>
		<td class='WeekOf' rel='<?php echo $WeekOf;?>' onClick="refresh_this(this);"><?php 
			echo $WeekOf;
		?></td>
		<?php 
		$Thursday = date('Y-m-d',strtotime($Thursday . '-7 days'));
			$r = $database->query(null,"SELECT Sum(Total) AS Summed FROM TicketD LEFT JOIN Job ON TicketD.Job = Job.ID WHERE TicketD.Elev='" . $_GET['ID'] . "' and EDate >= '" . $Thursday . " 00:00:00.000' AND EDate <= '" . $Thursday . " 23:59:59.999'");?>
		<td class='Thursday' rel='<?php echo $Thursday;?>' onClick="refresh_this(this);"><?php 
			echo sqlsrv_fetch_array($r)['Summed'];
		?></td>
		<?php $Friday = date('Y-m-d',strtotime($Friday . '-7 days'));
			$r = $database->query(null,"SELECT Sum(Total) AS Summed FROM TicketD LEFT JOIN Job ON TicketD.Job = Job.ID WHERE TicketD.Elev='" . $_GET['ID'] . "' and EDate >= '" . $Friday . " 00:00:00.000' AND EDate <= '" . $Friday . " 23:59:59.999'");?>
		<td class='Friday' rel='<?php echo $Friday;?>' onClick="refresh_this(this);"><?php 
			echo sqlsrv_fetch_array($r)['Summed'];
		?></td>
		<?php $Saturday = date('Y-m-d',strtotime($Saturday . '-7 days'));
			$r = $database->query(null,"SELECT Sum(Total) AS Summed FROM TicketD LEFT JOIN Job ON TicketD.Job = Job.ID WHERE TicketD.Elev='" . $_GET['ID'] . "' and EDate >= '" . $Saturday . " 00:00:00.000' AND EDate <= '" . $Saturday . " 23:59:59.999'");?>
		<td class='Saturday' rel='<?php echo $Saturday;?>' onClick="refresh_this(this);"><?php 
			echo sqlsrv_fetch_array($r)['Summed'];
		?></td>
		<?php $Sunday = date('Y-m-d',strtotime($Sunday . '-7 days'));
			$r = $database->query(null,"SELECT Sum(Total) AS Summed FROM TicketD LEFT JOIN Job ON TicketD.Job = Job.ID WHERE TicketD.Elev='" . $_GET['ID'] . "' and EDate >= '" . $Sunday . " 00:00:00.000' AND EDate <= '" . $Sunday . " 23:59:59.999'");?>
		<td class='Sunday' rel='<?php echo $Sunday;?>' onClick="refresh_this(this);"><?php 
			echo sqlsrv_fetch_array($r)['Summed'];
		?></td>
		<?php $Monday = date('Y-m-d',strtotime($Monday . '-7 days'));
			$r = $database->query(null,"SELECT Sum(Total) AS Summed FROM TicketD LEFT JOIN Job ON TicketD.Job = Job.ID WHERE TicketD.Elev='" . $_GET['ID'] . "' and EDate >= '" . $Monday . " 00:00:00.000' AND EDate <= '" . $Monday . " 23:59:59.999'");?>
		<td class='Monday' rel='<?php echo $Monday;?>' onClick="refresh_this(this);"><?php 
			echo sqlsrv_fetch_array($r)['Summed'];
		?></td>
		<?php $Tuesday = date('Y-m-d',strtotime($Tuesday . '-7 days'));
			$r = $database->query(null,"SELECT Sum(Total) AS Summed FROM TicketD LEFT JOIN Job ON TicketD.Job = Job.ID WHERE TicketD.Elev='" . $_GET['ID'] . "' and EDate >= '" . $Tuesday . " 00:00:00.000' AND EDate <= '" . $Tuesday . " 23:59:59.999'");?>
		<td class='Tuesday' rel='<?php echo $Tuesday;?>' onClick="refresh_this(this);"><?php 
			echo sqlsrv_fetch_array($r)['Summed'];
		?></td>
		<?php $Wednesday = date('Y-m-d',strtotime($Wednesday . '-7 days'));
			$r = $database->query(null,"SELECT Sum(Total) AS Summed FROM TicketD LEFT JOIN Job ON TicketD.Job = Job.ID WHERE TicketD.Elev='" . $_GET['ID'] . "' and EDate >= '" . $Wednesday . " 00:00:00.000' AND EDate <= '" . $Wednesday . " 23:59:59.999'");?>
		<td class='Wednesday' rel='<?php echo $Wednesday;?>' onClick="refresh_this(this);"><?php
			echo sqlsrv_fetch_array($r)['Summed'];
		?></td>
		<td><?php
			$r = $database->query(null,"SELECT Sum(Total) AS Summed FROM TicketD LEFT JOIN Job ON TicketD.Job = Job.ID WHERE TicketD.Elev='" . $_GET['ID'] . "' and EDate >= '" . $Thursday . " 00:00:00.000' AND EDate <= '" . $Wednesday . " 23:59:59.999'");
			echo sqlsrv_fetch_array($r)['Summed'];
		?></td>
	</tr><?php }?>
</tbody>
</table>
<script>$(document).ready(function(){$("table#Location_Hours>tbody>tr:first-child").remove();});</script>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>