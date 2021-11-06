<?php 
session_start( [ 'read_and_close' => true ] );
require('../../../../bin/php/index.php');
setlocale(LC_MONETARY, 'en_US');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = $database->query(null,"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    $My_User = $database->query(null,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID = ?",array($_SESSION['User']));
    $My_User = sqlsrv_fetch_array($My_User);
    $Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
    $r = $database->query($Portal,"
        SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
        FROM   Privilege
        WHERE  User_ID = ?
    ;",array($_SESSION['User']));
    $My_Privileges = array();
    while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
    $Privileged = FALSE;
    if(isset($My_Privileges['Location']) && $My_Privileges['Location']['User_Privilege'] >= 4 && $My_Privileges['Location']['Group_Privilege'] >= 4 && $My_Privileges['Location']['Other_Privilege'] >= 4){$Privileged = TRUE;}
    elseif($My_Privileges['Location']['User_Privilege'] >= 4 && is_numeric($_GET['ID'])){
        $r = $database->query(  null,"
		SELECT 	*
		FROM 	TicketO
		WHERE 	TicketO.LID='{$_GET['ID']}'
				AND fWork='{$My_User['fWork']}'");
        $r2 = $database->query( null,"
		SELECT 	*
		FROM 	TicketD
		WHERE 	TicketD.Loc='{$_GET['ID']}'
				AND fWork='{$My_User['fWork']}'");
        $r3 = $database->query( null,"
		SELECT 	*
		FROM 	TicketDArchive
		WHERE 	TicketDArchive.Loc='{$_GET['ID']}'
				AND fWork='{$My_User['fWork']}'");
        $r = sqlsrv_fetch_array($r);
        $r2 = sqlsrv_fetch_array($r2);
		$r3 = sqlsrv_fetch_array($r3);
        $Privileged = (is_array($r) || is_array($r2) || is_array($r3)) ? TRUE : FALSE;
    }
    $database->query(null,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "location.php"));
    if(!isset($array['ID'])  || !$Privileged || !is_numeric($_GET['ID'])){?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {
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
        $data = $Location;
        $job_result = $database->query(null,"
            SELECT Job.ID AS ID
            FROM   Job
            WHERE  Job.Loc = ?
        ;",array($_GET['ID']));
        if($job_result){
            $Jobs = array();
            $dates = array();
            $totals = array();
            while($array = sqlsrv_fetch_array($job_result)){$Jobs[] = "[JOBLABOR].[JOB #]='{$array['ID']}'";}
            $SQL_Jobs = implode(" OR ",$Jobs);
        }?>
<div class="panel panel-primary">
	<!--<div class="panel-heading">
		<i class="fa fa-bell fa-fw"></i> Service Call Feed
	</div>-->
	<div class="panel-body ">
		<table id='Table_Service_Call_Feed' class='display' cellspacing='0' width='100%' style="font-size: 8px">
			<thead><tr>
				<th>ID</th>
				<th>Created</th>
				<th>Scheduled</th>
				<th>Mechanic</th>
				<th>Unit</th>
				<th>Details</th>
				<th>Status</th>
			</tr></thead>
		</table>
	</div>
</div>
<script>
	var Table_Service_Call_Feed = $('#Table_Service_Call_Feed').DataTable( {
		"ajax": {
				"url": "bin/php/reports/Service_Call_Feed_by_Location.php?ID=<?php echo $_GET['ID'];?>",
		},
		"columns": [
			{
				"data" : "Ticket_ID",
				"className" : "hidden",
			},{
				"data" : "Created",
				render: function(data){return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}
			},{
				"data" : "Scheduled",
				render: function(data){return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}
			},{
				"data" : "Mechanic"
			},{
				"data" : "Unit"
			},{
				"data" : "Description"
			},{
				"data" : "Status"
			}
		],
		"searching":false,
		"paging":false

	} );
	function hrefTickets(){hrefRow("Table_Service_Call_Feed","ticket");}
	$("Table#Table_Service_Call_Feed").on("draw.dt",function(){hrefTickets();});
	<?php /*if(!isMobile()){?>$('#Table_Service_Call_Feed tbody').on('click', 'td.details-control', function () {
		var tr = $(this).closest('tr');
		var row = Table_Service_Call_Feed.row( tr );

		if ( row.child.isShown() ) {
			row.child.hide();
			tr.removeClass('shown');
		}
		else {
			row.child( formatTicket(row.data()) ).show();
			tr.addClass('shown');
		}
	} );<?php } else {?>
	 $('#Table_Service_Call_Feed tbody').on('click', 'td', function () {
		var tr = $(this).closest('tr');
		var row = Table_Service_Call_Feed.row( tr );

		if ( row.child.isShown() ) {
			row.child.hide();
			tr.removeClass('shown');
		}
		else {
			row.child( formatTicket(row.data()) ).show();
			tr.addClass('shown');
		}
	} );
	<?php }*/?>
</script>
<?php

    }
} else {?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
