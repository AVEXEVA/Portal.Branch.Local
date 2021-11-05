<?php
session_start( [ 'read_and_close' => true ] );
require('../../../php/index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = $database->query(null,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Elevator'){
        $database->query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "unit.php"));
        $r= $database->query(null,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID= ?",array($_SESSION['User']));
        $My_User = sqlsrv_fetch_array($r);
        $Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
        $r = $database->query($Portal,"
            SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
            FROM   Portal.dbo.Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
        $My_Privileges = array();
        while($My_Privilege = sqlsrv_fetch_array($r)){$My_Privileges[$My_Privilege['Access_Table']] = $My_Privilege;}
        $Privileged = FALSE;
        if(isset($My_Privileges['Unit']) && $My_Privileges['Unit']['User_Privilege'] >= 4 && $My_Privileges['Unit']['Group_Privilege'] >= 4 && $My_Privileges['Unit']['Other_Privilege'] >= 4){$Privileged = TRUE;}
        elseif(isset($My_Privileges['Unit']) && $My_Privileges['Unit']['User_Privilege'] >= 4 && $My_Privileges['Unit']['Group_Privilege'] >= 4){
            $r = $database->query(null,"
				SELECT * 
				FROM   TicketO 
					   LEFT JOIN nei.dbo.Loc  ON TicketO.LID   = Loc.Loc
					   LEFT JOIN nei.dbo.Elev ON Loc.Loc       = Elev.Loc
					   LEFT JOIN nei.dbo.Emp  ON TicketO.fWork = Emp.fWork
				WHERE  Emp.ID      = ?
					   AND Elev.ID = ?
            ;",array($_SESSION['User'],$_GET['ID']));
            $r2 = $database->query(null,"
				SELECT * 
				FROM   TicketD 
					   LEFT JOIN nei.dbo.Loc  ON TicketD.Loc   = Loc.Loc
					   LEFT JOIN nei.dbo.Elev ON Loc.Loc       = Elev.Loc
					   LEFT JOIN nei.dbo.Emp  ON TicketD.fWork = Emp.fWork
				WHERE  Emp.ID      = ?
					   AND Elev.ID = ?
            ;",array($_SESSION['User'],$_GET['ID']));
            $r3 = $database->query(null,"
				SELECT * 
				FROM   TicketDArchive
					   LEFT JOIN nei.dbo.Loc  ON TicketDArchive.Loc   = Loc.Loc
					   LEFT JOIN nei.dbo.Elev ON Loc.Loc              = Elev.Loc
					   LEFT JOIN nei.dbo.Emp  ON TicketDArchive.fWork = Emp.fWork
				WHERE  Emp.ID      = ?
					   AND Elev.ID = ?
            ;",array($_SESSION['User'],$_GET['ID']));
            $r = sqlsrv_fetch_array($r);
            $r2 = sqlsrv_fetch_array($r2);
            $r3 = sqlsrv_fetch_array($r2);
            $Privileged = (is_array($r) || is_array($r2) || is_array($r3)) ? TRUE : FALSE;
        }
    } elseif($_SESSION['Branch'] == 'Customer' && is_numeric($_GET['ID'])){
        $SQL_Result = $database->query(null,"
            SELECT Elev.Owner 
            FROM   nei.dbo.Elev 
            WHERE  Elev.ID        = ? 
			       AND Elev.Owner = ?
        ;",array($_GET['ID'],$_SESSION['Branch_ID']));
        if($SQL_Result){
            $sql = sqlsrv_fetch_array($SQL_Result);
            if($sql){$Privileged = true;}
        }
    }
    if(!isset($array['ID'])  || !$Privileged || !(is_numeric($_GET['ID']) || is_numeric($_POST['ID']))){?><html><head><script>document.location.href="../login.php?Forward=unit<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {
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
                LEFT JOIN nei.dbo.Loc           ON Elev.Loc = Loc.Loc
                LEFT JOIN nei.dbo.Zone          ON Loc.Zone = Zone.ID
                LEFT JOIN nei.dbo.OwnerWithRol  ON Loc.Owner = OwnerWithRol.ID
                LEFT JOIN nei.dbo.Route ON Loc.Route = Route.ID
                LEFT JOIN nei.dbo.Emp ON Route.Mech = Emp.fWork
            WHERE
                Elev.ID = ?
		;",array($_GET['ID']));
        $Unit = sqlsrv_fetch_array($r);
        $data = $Unit;
        $r2 = $database->query(null,"
            SELECT *
            FROM   ElevTItem
            WHERE  ElevTItem.ElevT    = 1
                   AND ElevTItem.Elev = ?
        ;",array($_GET['ID']));
        if($r2){while($array = sqlsrv_fetch_array($r2)){$Unit[$array['fDesc']] = $array['Value'];}}
?>
<div class='tab-pane fade in' id='tables-units-pills'>
	<div class="panel panel-primary" style='margin-bottom:0px;'>
		<div class="panel-body">
			<div class="row">
				<div class='col-md-12' >
					<div class="panel panel-primary">
						<!--<div class="panel-heading"><h3><i class="fa fa-bell fa-fw"></i> Units Table</h3></div>-->
						<div class="panel-body white-background BankGothic shadow">
							<div id='Form_Unit_Survey_Sheet'>
								<div class="panel panel-primary">
									<div class="panel-heading" style='position:fixed;width:750px;z-index:999;'><h2 style='display:block;'>Survey Sheet</h2></div>
									<div class="panel-body white-background BankGothic shadow" style='padding-top:100px;'>
										<div style='display:block !important;'>
											<fieldset >
												<legend>Basic</legend>
												<editor-field name='Price'></editor-field>
												<editor-field name='Type'></editor-field>
												<editor-field name='Hours Allocations'></editor-field>
												<editor-field name='Capacity'></editor-field>
												<editor-field name='Car Speed'></editor-field>
												<editor-field name='# of Openings'></editor-field>
												<editor-field name='# of landings'></editor-field>
											</fieldset>
											<fieldset>
												<legend>Hoist Cables</legend>
												<editor-field name='Hoist Cable Quantity'></editor-field>
												<editor-field name='Hoist Cable Length'></editor-field>
												<editor-field name='Hoist Cable Diameter'></editor-field>
												<editor-field name='Hoist Cable Roping Type'></editor-field>
												<editor-field name='Hoist Cable Material Type'></editor-field>
												<editor-field name='Type of Shackle'></editor-field>
											</fieldset>
										</div>
										<div style='display:block !important;'>
											<fieldset>
												<legend>Governor</legend>
												<editor-field name='Governor Cable Length'></editor-field>
												<editor-field name='Governor Cable Diameter'></editor-field>
												<editor-field name='Governor Cable Material Type'></editor-field>
											</fieldset>
											<fieldset>
												<legend>Controller</legend>
												<editor-field name='Controller Manufacturer'></editor-field>
												<editor-field name='Controller Model'></editor-field>
												<editor-field name='Controller Serial'></editor-field>
												<editor-field name='Controller Manufacturer Job No #.'></editor-field>
											</fieldset>
										</div>
										<div style='display:block !important;'>
											<fieldset>
												<legend>Motor Room</legend>
												<editor-field name='Motor Room Location'></editor-field>
												<editor-field name='Machine Type'></editor-field>
												<editor-field name='Machine Location'></editor-field>
												<editor-field name='Machine Make'></editor-field>
												<editor-field name='Machine Model #'></editor-field>
												<editor-field name='Machine Serial No #.'></editor-field>
											</fieldset>
											<fieldset class='Car_Governor'>
												<legend>Car Governor</legend>
												<editor-field name='Car Governor Manufacturer'></editor-field>
												<editor-field name='Car Governor Model'></editor-field>
												<editor-field name='Car Governor Serial #'></editor-field>
											</fieldset>
										</div>
									</div>
								</div>
							</div>
							<table id='Table_Units' class='display' cellspacing='0' width='100%'>
								<thead>
									<th title="Unit's ID"></th>
									<th title='Unit State ID'></th>
									<th title="Unit's Label"></th>
									<th title="Type of Unit"></th>
									<th title="Unit's Location"></th>
									<?php
									$r = $database->query(null,"
										SELECT ElevTItem.*
										FROM   nei.dbo.ElevTItem
										WHERE  ElevTItem.ElevT    = 1
											   AND ElevTItem.Elev = ?
									;",array(0));
									$Columns = array();
									$Values = array();
									if($r){while($array = sqlsrv_fetch_array($r)){
										?><th></th><?php 
									}}
								?>
								</thead>
							   <tfooter><th title="Unit's ID">ID</th><th title='Unit State ID'>State</th><th title="Unit's Label">Unit</th><th title="Type of Unit">Type</th><th title="Unit's Location">Location</th></tfooter>
							</table>
						</div>
					</div>
				</div>
				<script>
				//function hrefUnits(){hrefRow("Table_Units","unit");}
				var Editor_Units = new $.fn.dataTable.Editor({
					ajax: "php/post/Unit.php?ID=<?php echo $_GET['ID'];?>",
					table: "#Table_Units",
					idSrc: "ID",
					formOptions: {
						inline: {
							submit: "allIfChanged"
						}
					},
					fields : [{
						label: "ID",
						name: "ID"
					},{
						label: "State",
						name: "State"
					},{
						label: "Unit",
						name: "Unit"
					},{
						label: "Type",
						name: "Type",
						type: "select",
						options: [<?php
							$r = $database->query(null,"
								SELECT   Elev.Type
								FROM     nei.dbo.Elev
								WHERE    Elev.Type <> ''
								GROUP BY Elev.Type
								ORDER BY Elev.Type ASC
							;");
							$Types = array();
							if($r){while($Type = sqlsrv_fetch_array($r)){
								$Type['Type'] = str_replace("'","",$Type['Type']);
								$Types[] = '{' . "label: '{$Type['Type']}', value:'{$Type['Type']}'" . '}'
							;}}
							echo implode(",",$Types);
						?>]
					},{
						label:"Status",
						name:"Status",
						type:"radio",
						options: [
							{label: "Not Active", value:0},
							{label: "Active", value:1}
						]
					},{
						label:"Description",
						name:"Description",
						type:"textarea"
					},{
						label: "Location",
						name: "Location",
						type: "select",
						options: [<?php
							$r = $database->query(null,"
								SELECT   Loc.Tag
								FROM     nei.dbo.Loc
								WHERE    Loc.Loc = ?
								ORDER BY Loc.Tag ASC
							;",array($_GET['ID']));
							$Tags = array();
							if($r){while($Tag = sqlsrv_fetch_array($r)){
								$Tag['Tag'] = str_replace("'","",$Tag['Tag']);
								$Tags[] = '{' . "label: '{$Tag['Tag']}', value:'{$Tag['Tag']}'" . '}';
							}}
							echo implode(",",$Tags);
						?>]
					},<?php
						$r = $database->query(null,"
							SELECT ElevTItem.*
							FROM   nei.dbo.ElevTItem
							WHERE  ElevTItem.ElevT    = 1
								   AND ElevTItem.Elev = ?
						;",array(0));
						$Columns = array();
						$Values = array();
						if($r){while($array = sqlsrv_fetch_array($r)){
							$Columns[] = '{' . "label:'" . $array['fDesc'] . "',name:'" . $array['fDesc'] ."',type:'hidden'" . '}';
							$Values[$array['fDesc']] = $array['Value'];
						}}
						echo implode(",",$Columns);
					?>]
				});
				var Editor_Unit_Survey_Sheet = new $.fn.dataTable.Editor({
					ajax: "php/post/Survey_Sheet.php?ID=<?php echo $_GET['ID'];?>",
					table: "#Table_Units",
					template: '#Form_Unit_Survey_Sheet',
					idSrc: "ID",
					fields : [{
						label: "ID",
						name: "ID",
						type: "hidden"
					},{
						label: "State",
						name: "State",
						type: "hidden"
					},{
						label: "Unit",
						name: "Unit",
						type: "hidden"
					},{
						label: "Type",
						name: "Type",
					},{
						label: "Location",
						name: "Location",
						type: "hidden"
					},<?php
						$r = $database->query(null,"
							SELECT ElevTItem.*
							FROM   nei.dbo.ElevTItem
							WHERE  ElevTItem.ElevT    = 1
								   AND ElevTItem.Elev = ?
						;",array(0));
						$Columns = array();
						$Values = array();
						if($r){while($array = sqlsrv_fetch_array($r)){
							$Columns[] = '{' . "label:'" . $array['fDesc'] . "',name:'" . $array['fDesc'] ."'" . '}';
							$Values[$array['fDesc']] = $array['Value'];
						}}
						echo implode(",",$Columns);
					?>]
				});
				Editor_Units.field('ID').disable();
				Editor_Units.field('Location').disable();
				/*$('#Table_Units').on( 'click', 'tbody td:not(:first-child)', function (e) {
					Editor_Units.inline( this );
				} );*/
				<?php 
				if(count($Values) > 0){foreach($Values as $Field=>$Value){?>Editor_Units.val('<?php echo $Field;?>','<?php echo $Value;?>');<?php }}?>
				var Table_Units = $('#Table_Units').DataTable( {
					"ajax": "cgi-bin/php/get/Units_by_Location.php?ID=<?php echo $_GET['ID'];?>",
					"columns": [
						{ "data": "ID" },
						{ "data": "State"},
						{ "data": "Unit"},
						{ "data": "Type"},
						{ "data": "Location"},
						{ 
							"data": "Status",
							render:function(data){
								switch(data){
									case 0:return 'Inactive';
									case 1:return 'Active';
									case 2:return 'Demolished';
									case 3:return 'XXX';
									case 4:return 'YYY';
									case 5:return 'ZZZ';
									case 6:return 'AAA';
									default:return 'Error';
								}
							}
						}
						<?php if(count($Values) > 0){foreach($Values as $Field=>$Value){?>,{"data" :"<?php echo $Field;?>", "visible":false}<?php }}?>
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
						/*{ extend: "create", editor: Editor_Units },
						{ extend: "edit",   editor: Editor_Units },
						{ extend: "remove", editor: Editor_Units },*/
						/*{
							extend: "selected",
							text: 'Duplicate',
							action: function ( e, dt, node, config ) {
								// Start in edit mode, and then change to create
								Editor_Units
									.edit( Table_Units.rows( {selected: true} ).indexes(), {
										title: 'Duplicate record',
										buttons: 'Create from existing'
									} )
									.mode( 'create' );
							}
						},
						{ extend: "edit",   editor:Editor_Unit_Survey_Sheet, text:"Edit Survey Sheet"},*/
						{ text:"View",
						  action:function(e,dt,node,config){
							  document.location.href = 'unit.php?ID=' + $("#Table_Units tbody tr.selected td:first-child").html();
						  }
						}
					],
					<?php require('../../../js/datatableOptions.php');?>
				} );
				//$("Table#Table_Units").on("draw.dt",function(){hrefUnits();});
				yadcf.init(Table_Units,[
					{   column_number:0,
						filter_type:"auto_complete",
						filter_default_label:"ID"},
					{   column_number:1,
						filter_type:"auto_complete",
						filter_default_label:"State"},
					{   column_number:2,
						filter_type:"auto_complete",
						filter_default_label:"Label"},
					{   column_number:3,
						filter_default_label:"Type"},
					{   column_number:4,
						filter_default_label:"Location"}
				]);
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
	$("#tables-units-pills").addClass('active');
});
</script>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=customer<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>