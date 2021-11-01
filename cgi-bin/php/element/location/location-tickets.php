<?php
session_start();

require('../../../../cgi-bin/php/index.php');
setlocale(LC_MONETARY, 'en_US');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Elevator'){
        $My_User = sqlsrv_query($NEI,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID = ?",array($_SESSION['User']));
        $My_User = sqlsrv_fetch_array($My_User);
        $Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
        $r = sqlsrv_query($Portal,"
            SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
            FROM   Portal.dbo.Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
        $My_Privileges = array();
        while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
        $Privileged = FALSE;
        if(isset($My_Privileges['Location']) && $My_Privileges['Location']['User_Privilege'] >= 4 && $My_Privileges['Location']['Group_Privilege'] >= 4 && $My_Privileges['Location']['Other_Privilege'] >= 4){$Privileged = TRUE;}
        elseif($My_Privileges['Location']['User_Privilege'] >= 4 && is_numeric($_GET['ID'])){
            $r = sqlsrv_query(  $NEI,"
			SELECT 	*
			FROM 	nei.dbo.TicketO
			WHERE 	TicketO.LID='{$_GET['ID']}'
					AND fWork='{$My_User['fWork']}'");
            $r2 = sqlsrv_query( $NEI,"
			SELECT 	*
			FROM 	nei.dbo.TicketD
			WHERE 	TicketD.Loc='{$_GET['ID']}'
					AND fWork='{$My_User['fWork']}'");
            $r3 = sqlsrv_query( $NEI,"
			SELECT 	*
			FROM 	nei.dbo.TicketDArchive
			WHERE 	TicketDArchive.Loc='{$_GET['ID']}'
					AND fWork='{$My_User['fWork']}'");
            $r = sqlsrv_fetch_array($r);
            $r2 = sqlsrv_fetch_array($r2);
			$r3 = sqlsrv_fetch_array($r3);
            $Privileged = (is_array($r) || is_array($r2) || is_array($r3)) ? TRUE : FALSE;
        }
    } elseif($_SESSION['Branch'] == 'Customer' && is_numeric($_GET['ID'])){
        $SQL_Result = sqlsrv_query($NEI,"
            SELECT Loc.Owner
            FROM Loc
            WHERE Loc.Loc='{$_GET['ID']}' AND Loc.Owner='{$_SESSION['Branch_ID']}'
        ;");
        if($SQL_Result){
            $sql = sqlsrv_fetch_array($SQL_Result);
            if($sql){
                $Privileged = true;
            }
        }
    }
    sqlsrv_query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "location.php"));
    if(!isset($array['ID'])  || !$Privileged || !is_numeric($_GET['ID'])){?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {
        $ID = $_GET['ID'];
        $r = sqlsrv_query($NEI,
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
        $Location = sqlsrv_fetch_array($r);
        $data = $Location;?>
<div class="panel panel-primary">
	<!--<div class="panel-heading"><h4><i class="fa fa-bell fa-fw"></i> Tickets Table</h4></div>-->
	<div class="panel-body  BankGothic shadow">
		<table id='Table_Tickets' class='display' cellspacing='0' width='100%' style="font-size: 12px">
			<thead>
				<th></th>
				<th></th>
				<th></th>
				<th></th>
				<th></th>
				<th></th>
				<th>Hours</th>
			</thead>
		</table>
	</div>
</div>

	<script>
	var Table_Tickets = $('#Table_Tickets').DataTable( {
		"ajax": {
			"url":"cgi-bin/php/get/Tickets_by_Location.php?ID=<?php echo $_GET['ID'];?>",
			"dataSrc":function(json){if(!json.data){json.data = [];}return json.data;}
		},
		"columns": [
			{
				"data": "ID"
			},{
				"data": "Location"
			},{
				"data": "Job_Description"
			},{
				"data": "Mechanic"
			},{
				"data": "Worked",
				render: function(data){if(data != null){return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}else{return null;}}
			},{
				"data": "Status"
			},{
				"data": "Total",
				"defaultContent":"0"
			},{
				"data":"Unit_State",
				"visible":false,
				"searchable":true
			},{
				"data":"Unit_Label",
				"visible":false,
				"searchable":true
			},{
				"data": "Street",
				"visible":false
			},{
				"data": "City",
				"visible":false
			},{
				"data": "State",
				"visible":false
			},{
				"data": "Zip",
				"visible":false
			},{
				"data": "Route",
				"visible":false
			},{
				"data": "Division",
				"visible":false
			},{
				"data": "Maintenance",
				"visible":false,
				"render":function(data){
				  if(data == '1'){return "Maintained";}
				  else {return "Not Maintained";}
				}
			},{
				"data":"Tags",
				"visible":false,
				"searchable":true
			}
		],
		"language":{
            "loadingRecords":"<div style='text-align:center;'><div class='sk-cube-grid' style='display:inline-block;position:relative;';><div class='sk-cube sk-cube1' style='background-color:#cc0000'></div><div class='sk-cube sk-cube2' style='background-color:#cc0000'></div><div class='sk-cube sk-cube3' style='background-color:#cc0000'></div><div class='sk-cube sk-cube4' style='background-color:#cc0000'></div><div class='sk-cube sk-cube5' style='background-color:#cc0000'></div><div class='sk-cube sk-cube6' style='background-color:#cc0000'></div><div class='sk-cube sk-cube7' style='background-color:#cc0000'></div><div class='sk-cube sk-cube8' style='background-color:#cc0000'></div><div class='sk-cube sk-cube9' style='background-color:#cc0000'></div></div><div class='sk-cube-grid' style='display:inline-block;position:relative;top:-45px;'><div class='sk-cube sk-cube1' style='background-color:#00007f'></div><div class='sk-cube sk-cube2' style='background-color:#00007f'></div><div class='sk-cube sk-cube3' style='background-color:#00007f'></div><div class='sk-cube sk-cube4' style='background-color:#00007f'></div><div class='sk-cube sk-cube5' style='background-color:#00007f'></div><div class='sk-cube sk-cube6' style='background-color:#00007f'></div><div class='sk-cube sk-cube7' style='background-color:#00007f'></div><div class='sk-cube sk-cube8' style='background-color:#00007f'></div><div class='sk-cube sk-cube9' style='background-color:#00007f'></div></div><div class='sk-cube-grid' style='display:inline-block;position:relative;top:-84px;'><div class='sk-cube sk-cube1' style='background-color:gold'></div><div class='sk-cube sk-cube2' style='background-color:gold'></div><div class='sk-cube sk-cube3' style='background-color:gold'></div><div class='sk-cube sk-cube4' style='background-color:gold'></div><div class='sk-cube sk-cube5' style='background-color:gold'></div><div class='sk-cube sk-cube6' style='background-color:gold'></div><div class='sk-cube sk-cube7' style='background-color:gold'></div><div class='sk-cube sk-cube8' style='background-color:gold'></div><div class='sk-cube sk-cube9' style='background-color:gold'></div></div></div><div style='font-size:72px;text-align:center;' class='BankGothic'>Nouveau Elevator</div><div style='font-size:42px;text-align:center;'><i>Raising Your Life</i></div>"
        },
        "paging":true,
        <?php if(!isMobile()){?>"dom":"Bfrtip",<?php }?>
        "select":true,
        "initComplete":function(){
        },
        "scrollY" : "600px",
        "scrollCollapse":true,
        "lengthChange": false
   	} );
	function format ( d ) {
		return "<div>"+
			"<div>"+
				"<div class='column'>"+
					"<div class='Account'><div class='label1'>Account:</div><div class='data'>"+d.Customer+"</div></div>"+
					"<div class='Location'><div class='label1'>Location:</div><div class='data'>"+d.Location+"</div></div>"+
					"<div class='Address'><div class='label1'>Address:</div><div class='data'>"+d.Street+"</div></div>"+
					"<div class='Address'><div class='label1'>&nbsp;</div><div class='data'>"+d.City+", "+d.City+" "+d.Zip+"</div></div>"+
					"<div class='Unit'><div class='label1'>Unit State:</div><div class='data'>"+d.Unit_State+"</div></div>"+
					"<div class='Caller'><div class='label1'>Caller:</div><div class='data'>"+d.Caller+"</div></div>"+
					"<div class='Taken_By'><div class='label1'>Taken By:</div><div class='data'>"+d.Taken_By+"</div></div>"+
				"</div>"+
				"<div class='column'>"+
					"<div class='Created hours'><div class='label1'>Created:</div><div class='data'>"+d.Created+"</div></div>"+
					"<div class='Dispatched hours'><div class='label1'>Dispatched:</div><div class='data'>"+d.Dispatched+"</div></div>"+
					"<div class='Type'><div class='label1'>Type:</div><div class='data'>"+d.Job_Type+"</div></div>"+
					"<div class='Level'><div class='label1'>Level:</div><div class='data'>"+d.Level+"</div></div>"+
					"<div class='Category'><div class='label1'>Category:</div><div class='data'>"+d.Category+"</div></div>"+
				"</div>"+
				"<div class='column'>"+
					"<div class='Regular hours'><div class='label1'>On Site:</div><div class='data'>"+d.On_Site+"</div></div>"+
					"<div class='Regular hours'><div class='label1'>Completed:</div><div class='data'>"+d.Completed+"</div></div>"+
					"<div class='Regular hours'><div class='label1'>Regular:</div><div class='data'>"+d.Regular+"</div></div>"+
					"<div class='OT hours'><div class='label1'>OT:</div><div class='data'>"+d.Overtime+"</div></div>"+
					"<div class='Doubletime hours'><div class='label1'>DT:</div><div class='data'>"+d.Doubletime+"</div></div>"+
					"<div class='Total hours'><div class='label1'>Total</div><div class='data'>"+d.Total+"</div></div>"+
				"</div>"+
			"</div>"+
			"<div>"+
				"<div class='column' style='width:45%;vertical-align:top;'>"+
					"<div><b>Scope of Work</b></div>"+
					"<div><pre>"+d.Description+"</div>"+
				"</div>"+
				"<div class='column' style='width:45%;vertical-align:top;'>"+
					"<div><b>Resolution</b></div>"+
					"<div><pre>"+d.Resolution+"</div>"+
				"</div>"+
			"</div>"+
		'</div>'+
		"<div><a href='ticket.php?ID="+d.ID+"' target='_blank'>View Ticket</a></div>";
	}
	function hrefTickets(){hrefRow("Table_Tickets","ticket");}
	$("Table#Table_Tickets").on("draw.dt",function(){hrefTickets();});
</script>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
