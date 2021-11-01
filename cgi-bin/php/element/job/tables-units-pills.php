<?php 
session_start();
require('../../../php/index.php');
require('../../../php/class/Customer.php');
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
        if(isset($My_Privileges['Job']) && $My_Privileges['Job']['User_Privilege'] >= 4 && $My_Privileges['Job']['Group_Privilege'] >= 4 && $My_Privileges['Job']['Other_Privilege'] >= 4){$Privileged = TRUE;}
        elseif($My_Privileges['Job']['User_Privilege'] >= 4 && is_numeric($_GET['ID'])){
            $r = sqlsrv_query(  $NEI,"SELECT * FROM nei.dbo.TicketO WHERE TicketO.Job='{$_GET['ID']}' AND fWork='{$My_User['fWork']}'");
            $r2 = sqlsrv_query( $NEI,"SELECT * FROM nei.dbo.TicketD WHERE TicketD.Job='{$_GET['ID']}' AND fWork='{$My_User['fWork']}'");
            $r3 = sqlsrv_query( $NEI,"SELECT * FROM nei.dbo.TicketDArchive WHERE TicketDArchive.Job='{$_GET['ID']}' AND fWork='{$My_User['fWork']}'");
            $r = sqlsrv_fetch_array($r);
            $r2 = sqlsrv_fetch_array($r2);
			$r3 = sqlsrv_fetch_array($r3);
            $Privileged = (is_array($r) || is_array($r2) || is_array($r3)) ? TRUE : FALSE;
		}
    } elseif($_SESSION['Branch'] == 'Customer' && $_SESSION['Branch_ID'] == $_GET['ID']){$Privileged = TRUE;}
    //
    if(!isset($array['ID'])  || !is_numeric($_GET['ID']) || !$Privileged || !is_numeric($_GET['ID'])){?><html><head><script>document.location.href="../login.php?Forward=job<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {
        $r = sqlsrv_query($NEI,
            "SELECT TOP 1
                Job.ID                AS Job_ID,
                Job.fDesc             AS Job_Name,
                Job.fDate             AS Job_Start_Date,
                Job.BHour             AS Job_Budgeted_Hours,
                JobType.Type          AS Job_Type,
				Job.Remarks 		  AS Job_Remarks,
                Loc.Loc               AS Location_ID,
                Loc.ID                AS Location_Name,
                Loc.Tag               AS Location_Tag,
                Loc.Address           AS Location_Street,
                Loc.City              AS Location_City,
                Loc.State             AS Location_State,
                Loc.Zip               AS Location_Zip,
                Loc.Route             AS Route,
                Zone.Name             AS Division,
                OwnerWithRol.ID       AS Customer_ID,
                OwnerWithRol.Name     AS Customer_Name,
                OwnerWithRol.Status   AS Customer_Status,
                OwnerWithRol.Elevs    AS Customer_Elevators,
                OwnerWithRol.Address  AS Customer_Street,
                OwnerWithRol.City     AS Customer_City,
                OwnerWithRol.State    AS Customer_State,
                OwnerWithRol.Zip      AS Customer_Zip,
                OwnerWithRol.Contact  AS Customer_Contact,
                OwnerWithRol.Remarks  AS Customer_Remarks,
                OwnerWithRol.Email    AS Customer_Email,
                OwnerWithRol.Cellular AS Customer_Cellular,
                Elev.ID               AS Unit_ID,
                Elev.Unit             AS Unit_Label,
                Elev.State            AS Unit_State,
                Elev.Cat              AS Unit_Category,
                Elev.Type             AS Unit_Type,
                Emp.fFirst            AS Mechanic_First_Name,
                Emp.Last              AS Mechanic_Last_Name,
                Route.ID              AS Route_ID,
				Violation.ID          AS Violation_ID,
				Violation.fdate       AS Violation_Date,
				Violation.Status      AS Violation_Status,
				Violation.Remarks     AS Violation_Remarks
            FROM 
                Job 
                LEFT JOIN nei.dbo.Loc           ON Job.Loc      = Loc.Loc
                LEFT JOIN nei.dbo.Zone          ON Loc.Zone     = Zone.ID
                LEFT JOIN nei.dbo.JobType       ON Job.Type     = JobType.ID
                LEFT JOIN nei.dbo.OwnerWithRol  ON Job.Owner    = OwnerWithRol.ID
                LEFT JOIN nei.dbo.Elev          ON Job.Elev     = Elev.ID
                LEFT JOIN nei.dbo.Route         ON Loc.Route    = Route.ID
                LEFT JOIN nei.dbo.Emp           ON Emp.fWork    = Route.Mech
				LEFT JOIN nei.dbo.Violation     ON Job.ID       = Violation.Job
            WHERE
                Job.ID = ?
        ;",array($_GET['ID']));
        $Job = sqlsrv_fetch_array($r);?>
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
									$r = sqlsrv_query($NEI,"
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
							$r = sqlsrv_query($NEI,"
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
							$r = sqlsrv_query($NEI,"
								SELECT   Loc.Tag
								FROM     nei.dbo.Loc
								WHERE    Loc.Owner = ?
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
						$r = sqlsrv_query($NEI,"
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
						$r = sqlsrv_query($NEI,"
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
						},*/
						{ extend: "edit",   editor:Editor_Unit_Survey_Sheet, text:"Edit Survey Sheet"},
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