<?php
session_start( [ 'read_and_close' => true ] );
require('../../../php/index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = $database->query(null,"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Texas'){
        $database->query(null,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "unit.php"));
        $r= $database->query(null,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID= ?",array($_SESSION['User']));
        $My_User = sqlsrv_fetch_array($r);
        $Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
        $r = $database->query(null,"
            SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
            FROM   Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
        $My_Privileges = array();
        while($My_Privilege = sqlsrv_fetch_array($r)){$My_Privileges[$My_Privilege['Access_Table']] = $My_Privilege;}
        $Privileged = FALSE;
        if(isset($My_Privileges['Unit']) && $My_Privileges['Unit']['User_Privilege'] >= 4 && $My_Privileges['Unit']['Group_Privilege'] >= 4 && $My_Privileges['Unit']['Other_Privilege'] >= 4){$Privileged = TRUE;}
        elseif(isset($My_Privileges['Unit']) && $My_Privileges['Unit']['User_Privilege'] >= 4 && $My_Privileges['Unit']['Group_Privilege'] >= 4){
			$r = $database->query(null,"
				SELECT Elev.Loc AS Location_ID
				FROM   Elev
				WHERE  Elev.ID = ?
			;",array($_GET['ID'] ));
			$Location_ID = sqlsrv_fetch_array($r)['Location_ID'];
            $r = $database->query(null,"
			SELECT Tickets.*
			FROM 
			(
				(
					SELECT TicketO.ID
					FROM   TicketO 
						   LEFT JOIN Loc  ON TicketO.LID   = Loc.Loc
						   LEFT JOIN Elev ON Loc.Loc       = Elev.Loc
						   LEFT JOIN Emp  ON TicketO.fWork = Emp.fWork
					WHERE  Emp.ID      = ?
						   AND Loc.Loc = ?
				)
				UNION ALL
				(
					SELECT TicketD.ID
					FROM   TicketD 
						   LEFT JOIN Loc  ON TicketD.Loc   = Loc.Loc
						   LEFT JOIN Elev ON Loc.Loc       = Elev.Loc
						   LEFT JOIN Emp  ON TicketD.fWork = Emp.fWork
					WHERE  Emp.ID      = ?
						   AND Loc.Loc = ?
				)

			) AS Tickets
           	;", array($_SESSION['User'], $Location_ID, $_SESSION['User'], $Location_ID, $_SESSION['User'], $Location_ID));
            $r = sqlsrv_fetch_array($r);
            $Privileged = is_array($r) ? TRUE : FALSE;
        }
    }
    if(!isset($array['ID'])  || !$Privileged || !is_numeric($_GET['ID'])){?><html><head><script>document.location.href="../login.php?Forward=unit<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {
        if(count($_POST) > 0){
            fixArrayKey($_POST);
            foreach($_POST as $key=>$value){
				if($key == 'Price'){continue;}
				if($key == 'Type'){continue;}
                $database->query(null,"
                    UPDATE ElevTItem
                    SET    ElevTItem.Value     = ?
                    WHERE  ElevTItem.Elev      = ?
                           AND ElevTItem.ElevT = 1
                           AND ElevTItem.fDesc = ?
                ;",array($value,$_GET['ID'],$key));
            }
			if(isset($_POST['Price'])){
				$database->query(null,"
					UPDATE Elev
					SET    Elev.Price = ?
					WHERE  Elev.ID    = ?
				;",array($_POST['Price'],$_GET['ID']));
			}
			if(isset($_POST['Type'])){
				$database->query(null,"
					UPDATE Elev
					SET    Elev.Type = ?
					WHERE  Elev.ID    = ?
				;",array($_POST['Type'],$_GET['ID']));
			}
        }
        $r = $database->query(null,
            "SELECT TOP 1
                Elev.ID,
                Elev.Unit           AS Unit,
                Elev.State          AS State,
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
                OwnerWithRol.Name   AS Customer_Name,
                OwnerWithRol.ID     AS Customer_ID,
                Emp.ID AS Route_Mechanic_ID,
                Emp.fFirst AS Route_Mechanic_First_Name,
                Emp.Last AS Route_Mechanic_Last_Name
            FROM 
                Elev
                LEFT JOIN Loc           ON Elev.Loc = Loc.Loc
                LEFT JOIN Zone          ON Loc.Zone = Zone.ID
                LEFT JOIN OwnerWithRol  ON Loc.Owner = OwnerWithRol.ID
                LEFT JOIN Route ON Loc.Route = Route.ID
                LEFT JOIN Emp ON Route.Mech = Emp.fWork
            WHERE
                Elev.ID = ?
		;",array($_GET['ID']));
        $Unit = sqlsrv_fetch_array($r);
        $unit = $Unit;
        $data = $Unit;
        $r2 = $database->query(null,"
            SELECT *
            FROM   ElevTItem
            WHERE  ElevTItem.ElevT    = 1
                   AND ElevTItem.Elev = ?
        ;",array($_GET['ID']));
        if($r2){while($array = sqlsrv_fetch_array($r2)){$Unit[$array['fDesc']] = $array['Value'];}}?>
<div class="panel panel-primary">
	<!--<div class="panel-heading"><h3><i class="fa fa-bell fa-fw"></i> Tickets Table</h3></div>-->
	<div class="panel-body">
		<table id='Table_Tickets' class='display' cellspacing='0' width='100%' style='font-size:12px;'>
			<thead>
				<th>ID</th>
				<th>Location</th>
				<th>Job</th>
				<th></th>
				<th>Mechanic</th>
				<th>Date</th>
				<th>Status</th>                                     
				<th>Hours</th>
			</thead>
		</table>
	</div>
</div>
<script>
var Table_Tickets = $('#Table_Tickets').DataTable( {
"ajax": {
	"url":"cgi-bin/php/reports/Maintenance_Tickets_by_Unit.php?ID=<?php echo $_GET['ID'];?>",
	"dataSrc":function(json){if(!json.data){json.data = [];}return json.data;}
},
"columns": [
	{
		"className":      'details-control',
		"orderable":      false,
		"data":           null,
		"defaultContent": ''
		<?php if(isMobile()){?>,"visible":false<?php }?>
	},{ 
		"data": "ID",
		"className":"hidden"
	},{ 
		"data": "Location",
		"className":"hidden"
	},{
		"data": "Job_Description",
		"className":"hidden"
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
"paging":false
} );
<?php if(!$Mobile){?>
yadcf.init(Table_Tickets,[
{   
	column_number:1,
	filter_type:"auto_complete",
	filter_default_label:"ID"
},{   
	column_number:2,
	filter_default_label:"Location"
},{   
	column_number:3,
	filter_default_label:"Job"
},{   
	column_number:4,
	filter_default_label:"Mechanic"
},{   
	column_number:5,
	filter_type: "range_date",
	date_format: "mm/dd/yyyy",
	filter_delay: 500
},{   
	column_number:6,
	filter_default_label:"Status"
},{   
	column_number:7,
	filter_type: "range_number_slider",
	filter_delay: 500
}
]);
//$("Table#Table_Tickets").on("draw.dt",function(){hrefTickets();});
stylizeYADCF();<?php }?>
var expandTicketButton = true;
$("Table#Table_Tickets").on("draw.dt",function(){
if(!expandTicketButton){$("Table#Table_Tickets tbody tr:not(.shown) td:first-child").each(function(){$(this).click();});} 
else {$("Table#Table_Tickets tbody tr.shown td:first-child").each(function(){$(this).click();});}
});
//setTimeout(function(){initialize()},1000);
$("#yadcf-filter--Table_Tickets-1").attr("size","7");
function hrefTickets(){hrefRow("Table_Tickets","ticket");}
$("Table#Table_Tickets").on("draw.dt",function(){hrefTickets();});
function format ( d ) {
return "<td><div>"+
	"<div>"+
		"<div class='column'>"+
			"<div class='Account'><div class='label1'>Account:</div><div class='data'>"+d.Customer+"</div></div>"+
			"<div class='Location'><div class='label1'>Location:</div><div class='data'>"+d.Location+"</div></div>"+
			"<div class='Address'><div class='label1'>Address:</div><div class='data'>"+d.Street+"</div></div>"+
			"<div class='Address'><div class='label1'>&nbsp;</div><div class='data'>"+d.City+" ,"+d.City+" "+d.Zip+"</div></div>"+
			"<div class='Unit'><div class='label1'>Unit State:</div><div class='data'>"+d.Unit_State+"</div></div>"+
			"<div class='Caller'><div class='label1'>Caller:</div><div class='data'>"+d.Caller+"</div></div>"+
			"<div class='Taken_By'><div class='label1'>Taken By:</div><div class='data'>"+d.Taken_By+"</div></div>"+
		"</div>"+
		"<div class='column'>"+
			"<div class='Created'><div class='label1'>Created:</div><div class='data'>"+d.Created+"</div></div>"+
			"<div class='Dispatched'><div class='label1'>Dispatched:</div><div class='data'>"+d.Dispatched+"</div></div>"+
			"<div class='Type'><div class='label1'>Type:</div><div class='data'>"+d.Job_Type+"</div></div>"+
			"<div class='Level'><div class='label1'>Level:</div><div class='data'>"+d.Level+"</div></div>"+
			"<div class='Category'><div class='label1'>Category:</div><div class='data'>"+d.Category+"</div></div>"+
		"</div>"+
		"<div class='column hours'>"+
			"<div class='Regular'><div class='label1'>On Site:</div><div class='data'>"+d.On_Site.substr(10,9)+"</div></div>"+
			"<div class='Regular'><div class='label1'>Completed:</div><div class='data'>"+d.Completed.substr(10,9)+"</div></div>"+
			"<div class='Regular'><div class='label1'>Regular:</div><div class='data'>"+d.Regular+"</div></div>"+
			"<div class='OT'><div class='label1'>OT:</div><div class='data'>"+d.Overtime+"</div></div>"+
			"<div class='Doubletime'><div class='label1'>DT:</div><div class='data'>"+d.Doubletime+"</div></div>"+
			"<div class='Total'><div class='label1'>Total</div><div class='data'>"+d.Total+"</div></div>"+
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
"<div><a href='ticket.php?ID="+d.ID+"' target='_blank'>View Ticket</a></div></td>"
}
	function hrefUnits(){hrefRow("Table_Tickets","ticket");}
	$("Table#Table_Tickets").on("draw.dt",function(){hrefUnits();});
</script><?php
		
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>