<?php 
session_start();
require('../../../php/index.php');

if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Elevator'){
    	sqlsrv_query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "customer.php"));
        $r= sqlsrv_query($NEI,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID= ?",array($_SESSION['User']));
        $My_User = sqlsrv_fetch_array($r);
        $Field = ($My_User['Field'] == 1 && $My_User['Title'] != 'OFFICE') ? True : False;
        $r = sqlsrv_query($Portal,"
            SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
            FROM   Portal.dbo.Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
        $My_Privileges = array();
        while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
        $Privileged = FALSE;
        if(isset($My_Privileges['Customer']) && $My_Privileges['Customer']['User_Privilege'] >= 4 && $My_Privileges['Customer']['Group_Privilege'] >= 4 && $My_Privileges['Customer']['Other_Privilege'] >= 4){
        	sqlsrv_query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "customer.php"));
        	$Privileged = TRUE;}
        elseif($My_Privileges['Customer']['User_Privilege'] >= 4 && $My_Privileges['Ticket']['Group_Privilege'] >= 4 ){
        	sqlsrv_query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "customer.php"));
            $r = sqlsrv_query(  $NEI,"
                SELECT TicketO.ID AS ID 
                FROM nei.dbo.TicketO LEFT JOIN nei.dbo.Loc ON TicketO.LID = Loc.Loc
                WHERE Loc.Owner = ?;",array($_GET['ID']));
            $r2 = sqlsrv_query(  $NEI,"
                SELECT TicketD.ID AS ID 
                FROM nei.dbo.TicketD LEFT JOIN nei.dbo.Loc ON TicketD.Loc = Loc.Loc
                WHERE Loc.Owner = ?;",array($_GET['ID']));
            $Privileged = (is_array(sqlsrv_fetch_array($r)) || is_array(sqlsrv_fetch_array($r2))) ? TRUE : FALSE;}
    } elseif($_SESSION['Branch'] == 'Customer' && $_SESSION['Branch_ID'] == $_GET['ID']){$Privileged = TRUE;}
    //
    if(!isset($array['ID'])  || !is_numeric($_GET['ID']) || !$Privileged || !is_numeric($_GET['ID'])){?><html><head><script>document.location.href="../login.php?Forward=customer<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {
        $r = sqlsrv_query($NEI,
            "SELECT TOP 1
                    OwnerWithRol.ID      AS Customer_ID,
                    OwnerWithRol.Name    AS Name,
                    OwnerWithRol.Address AS Street,
                    OwnerWithRol.City    AS City,
                    OwnerWithRol.State   AS State,
                    OwnerWithRol.Zip     AS Zip,
                    OwnerWithRol.Status  AS Status
            FROM    OwnerWithRol
            WHERE   OwnerWithRol.ID = '{$_GET['ID']}'");
        $Customer = sqlsrv_fetch_array($r);
        $job_result = sqlsrv_query($NEI,"
            SELECT 
                Job.ID AS ID
            FROM 
                Job 
            WHERE 
                Job.Owner = '{$_GET['ID']}'
        ;");
        if($job_result){
            $Jobs = array();
            $dates = array();
            $totals = array();
            while($array = sqlsrv_fetch_array($job_result)){$Jobs[] = "[JOBLABOR].[JOB #]='{$array['ID']}'";}
            $SQL_Jobs = implode(" OR ",$Jobs);
        }?>
<div class='tab-pane fade in active' id='tables-locations-pills'>
	<div class="panel panel-primary" style='margin-bottom:0px;'>
		<div class="panel-body">
			<div class="row">
				<div class='col-md-12' >
					<div class="panel panel-primary">
						<div class="panel-heading"><h3><i class="fa fa-bell fa-fw"></i> Locations Table</h3></div>
						<div class="panel-body white-background BankGothic shadow">
							<div id='Form_Location'>
								<div class="panel panel-primary">
									<div class="panel-heading" style='position:fixed;width:750px;z-index:999;'><h2 style='display:block;'>Location Form</h2></div>
									<div class="panel-body white-background BankGothic shadow" style='padding-top:100px;'>
										<div style='display:block !important;'>
											<fieldset >
												<legend>Names</legend>
												<editor-field name='ID'></editor-field>
												<editor-field name='Name'></editor-field>
												<editor-field name='Tag'></editor-field>
											</fieldset>
											<fieldset>
												<legend>Address</legend>
												<editor-field name='Street'></editor-field>
												<editor-field name='City'></editor-field>
												<editor-field name='State'></editor-field>
												<editor-field name='Zip'></editor-field>
												<editor-field name='Latitude'></editor-field>
												<editor-field name='Longitude'></editor-field>
											</fieldset>
											<fieldset>
												<legend>Contact</legend>
												<editor-field name='Contact_Name'></editor-field>
												<editor-field name='Contact_Phone'></editor-field>
												<editor-field name='Contact_Fax'></editor-field>
												<editor-field name='Contact_Cellular'></editor-field>
												<editor-field name='Contact_Email'></editor-field>
												<editor-field name='Contact_Website'></editor-field>
											</fieldset>
											<fieldset>
												<legend>Maintenance</legend>
												<editor-field name='Route'></editor-field>
												<editor-field name='Division'></editor-field>
												<editor-field name='Maintenance'></editor-field>
											</fieldset>
											<fieldset>
												<legend>Financials</legend>
												<editor-field name='Sales_Tax'></editor-field>
												<editor-field name='Collector'></editor-field>
											</fieldset>
											<fieldset>
												<legend>Sales</legend>
												<editor-field name='Territory'></editor-field>
											</fieldset>
										</div>
									</div>
								</div>
							</div>
							<table id='Table_Locations' class='display' cellspacing='0' width='100%'>
								<thead>
									<th title="Location's ID"></th>
									<th title="Location's Name State ID"></th>
									<th title="Location's Tag"></th>
									<th title="Location's Street"></th>
									<th title="Location's City"></th>
									<th title="Location's State"></th>
									<th title="Location's Zip"></th>
									<th title="Location's Route"></th>
									<th title="Location's Zone"></th>
									<th title="Location's Mainteniance"></th>
								</thead>
							</table>
						</div>
					</div>
				</div>
				<script>
				var Editor_Locations = new $.fn.dataTable.Editor({
					ajax: "php/post/Location.php?ID=<?php echo $_GET['ID'];?>",
					table: "#Table_Locations",
					template: '#Form_Location',
					formOptions: {
						inline: {
							submit: "allIfChanged"
						}
					},
					idSrc: "ID",
					fields : [{
						label: "Loc",
						name: "ID"
					},{
						label: "ID",
						name: "Name"
					},{
						label: "Tag",
						name: "Tag"
					},{
						label: "Street",
						name: "Street"
					},{
						label: "City",
						name: "City",
						type: "select",
						options: [<?php
							$r = sqlsrv_query($NEI,"
								SELECT   Loc.City
								FROM     nei.dbo.Loc
								WHERE    Loc.City <> ''
								         AND Loc.City <> ?
								GROUP BY Loc.City
								ORDER BY Loc.City ASC
							;",array("DON'T USE THIS CODE"));
							$Cities = array();
							if($r){while($City = sqlsrv_fetch_array($r)){$Cities[] = '{' . "label: '{$City['City']}', value:'{$City['City']}'" . '}';}}
							echo implode(",",$Cities);
						?>]
					},{
						label: "State",
						name: "State",
						type: "select",
						options: [<?php
							$r = sqlsrv_query($NEI,"
								SELECT   Loc.State
								FROM     nei.dbo.Loc
								WHERE    Loc.State <> ''
								GROUP BY Loc.State
								ORDER BY Loc.State ASC
							;");
							$States = array();
							if($r){while($State = sqlsrv_fetch_array($r)){$States[] = '{' . "label: '{$State['State']}', value:'{$State['State']}'" . '}';}}
							echo implode(",",$States);
						?>]
					},{
						label: "Zip",
						name: "Zip"
					},{
						label: "Route",
						name: "Route",
						type: "select",
						options: [<?php
							$r = sqlsrv_query($NEI,"
								SELECT   Route.Name
								FROM     nei.dbo.Route
								GROUP BY Route.Name
								ORDER BY Route.Name ASC
							;");
							$States = array();
							if($r){while($State = sqlsrv_fetch_array($r)){$States[] = '{' . "label: '{$State['Name']}', value:'{$State['Name']}'" . '}';}}
							echo implode(",",$States);
						?>]
					},{
						label: "Division",
						name: "Division",
						type: "select",
						options: [<?php
							$r = sqlsrv_query($NEI,"
								SELECT   Zone.Name
								FROM     nei.dbo.Zone
								GROUP BY Zone.Name
								ORDER BY Zone.Name ASC
							;");
							$States = array();
							if($r){while($State = sqlsrv_fetch_array($r)){$States[] = '{' . "label: '{$State['Name']}', value:'{$State['Name']}'" . '}';}}
							echo implode(",",$States);
						?>]
					},{
						label:"Maintenance",
						name:"Maintenance",
						type:"radio",
						options: [
							{label: "Not Maintained", value:0},
							{label: "Maintained", value:1}
						]
					},{
						label:"Territory",
						name:"Territory",
						type:"select",
						options: [<?php 
							$r = sqlsrv_query($NEI,"
								SELECT Terr.Name 
								FROM   nei.dbo.Terr
								GROUP BY Terr.Name
								ORDER BY Terr.Name ASC
							;");
							$Territories = array();
							if($r){while($Territory = sqlsrv_fetch_array($r)){$Territories[] = '{' . "label: '{$Territory['Name']}', value:'{$Territory['Name']}'" . '}';}}
							echo implode(",",$Territories);
						?>]
					},{
						label:"Longitude",
						name:"Longitude"
					},{
						label:"Latitude",
						name:"Latitude"
					},{
						label:"Sales Tax",
						name:"Sales_Tax",
						type:"select",
						options: [<?php 
							$r = sqlsrv_query($NEI,"
								SELECT Loc.sTax AS Sales_Tax
								FROM   nei.dbo.Loc
								GROUP BY Loc.sTax
								ORDER BY Loc.sTax
							;");
							$Sales_Taxes = array();
							if($r){while($Sales_Tax = sqlsrv_fetch_array($r)){$Sales_Taxes[] = '{' . "label: '{$Sales_Tax['Sales_Tax']}', value:'{$Sales_Tax['Sales_Tax']}'" . '}';}}
							echo implode(",",$Sales_Taxes);
						?>]
					},{
						label:"Collector",
						name:"Collector",
						type:"select",
						options:['',<?php 
							$r = sqlsrv_query($NEI,"
								SELECT Emp.fFirst + ' ' + Emp.Last AS Name
								FROM   nei.dbo.Emp
								WHERE  Emp.Custom3 = 'COLLECTOR'
									   And Emp.Status = 0
							;");
							$People = array();
							if($r){while($Person = sqlsrv_fetch_array($r)){$People[] = '{' . "label: " . '"' . "{$Person['Name']}" . '"' . ", value:" . '"' . "{$Person['Name']}" . '"' . '}';}}
							echo implode(",",$People);
						?>]
					},{
						label:"Name",
						name:"Contact_Name"
					},{
						label:"Phone",
						name:"Contact_Phone"
					},{
						label:"Fax",
						name:"Contact_Fax"
					},{
						label:"Cellular",
						name:"Contact_Cellular"
					},{
						label:"Email",
						name:"Contact_Email"
					},{
						label:"Website",
						name:"Contact_Website"
					}]
				});
				Editor_Locations.field('ID').disable();
				/*$('#Table_Locations').on( 'click', 'tbody td:not(:first-child)', function (e) {
					Editor_Locations.inline( this );
				} );*/
				var Table_Locations = $('#Table_Locations').DataTable( {
					"ajax": "cgi-bin/php/get/Locations_by_Customer.php?ID=<?php echo $_GET['ID'];?>",
					"columns": [
						{ 
							"data": "ID" 
						},{ 
							"data": "Name"
						},{ 
							"data": "Tag"
						},{ 
							"data": "Street"
						},{ 
							"data": "City"
						},{ 
							"data": "State"
						},{ 
							"data": "Zip"
						},{ 
							"data": "Route"
						},{ 
							"data": "Division"
						},{ 
							"data": "Maintenance",
						  	"render":function(data){
							  	if(data == '1'){return "Maintained";}
							  	else {return "Not Maintained";}
						  	}
						}
					],
					"buttons":[
						{
							extend: 'collection',
							text: 'Export',
							buttons: [
								'copy',
								'excel',
								'csv',
								'pdf',
								'print'
							]
						},
						/*{ extend: "create", editor: Editor_Locations },
						{ extend: "edit",   editor: Editor_Locations },
						{ 
							extend: "remove", 
						 	editor: Editor_Locations, 
						 	formButtons: [
								'Delete',
								{ text: 'Cancel', action: function () { this.close(); } }
							]
						},*/
						{ text:"View",
						  action:function(e,dt,node,config){
							  document.location.href = 'location.php?ID=' + $("#Table_Locations tbody tr.selected td:first-child").html();
						  }
						}
					],
					<?php require('../../../js/datatableOptions.php');?>
				} );
				yadcf.init(Table_Locations,[
					{   column_number:0,
						filter_type:"auto_complete",
						filter_default_label:"ID"},
					{   column_number:1,
						filter_type:"auto_complete",
						filter_default_label:"Name"},
					{   column_number:2,
						filter_type:"auto_complete",
						filter_default_label:"Tag"},
					{   column_number:3,
						filter_type:"auto_complete",
						filter_default_label:"Street"},
					{   column_number:4,
						filter_default_label:"City"},
					{   column_number:5,
						filter_default_label:"State"},
					{   column_number:6,
						filter_default_label:"Zip"},
					{   column_number:7,
						filter_default_label:"Route"},
					{   column_number:8,
						filter_default_label:"Zone"},
					{   column_number:9,
						filter_default_label:"Maintenance"}
				]);
				$("#yadcf-filter--Table_Locations-0").attr("size","6");

				</script>
				<?php /*<div class='col-md-12'>
					<div class="panel panel-primary">
						<div class="panel-heading"><h3><i class="fa fa-bell fa-fw"></i> </h3></div>
						<div class="panel-body white-background BankGothic shadow">
						</div>
					</div>
				</div>*/?>
			</div>
		</div>
	</div>
</div>
<script>
$(document).ready(function(){
	$("#loading-sub-pills").removeClass("active");
	$("#tables-locations-pills").addClass('active');
});
</script>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=customer<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?><!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Untitled Document</title>
</head>

<body>
</body>
</html>