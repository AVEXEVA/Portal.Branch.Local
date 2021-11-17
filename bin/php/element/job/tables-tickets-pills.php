<?php 
session_start( [ 'read_and_close' => true ] );
require('../../../php/index.php');
require('../../../php/class/Customer.php');
setlocale(LC_MONETARY, 'en_US');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = $database->query(null,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Elevator'){
    	$My_User = $database->query(null,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID = ?",array($_SESSION['User']));
        $My_User = sqlsrv_fetch_array($My_User); 
        $Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
        $r = $database->query($Portal,"
            SELECT Access, Owner, Group, Other
            FROM   Portal.dbo.Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
        $My_Privileges = array();
        while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access']] = $array2;}
        $Privileged = FALSE;
        if(isset($My_Privileges['Job']) && $My_Privileges['Job']['Owner'] >= 4 && $My_Privileges['Job']['Group'] >= 4 && $My_Privileges['Job']['Other'] >= 4){$Privileged = TRUE;}
        elseif($My_Privileges['Job']['Owner'] >= 4 && is_numeric($_GET['ID'])){
            $r = $database->query(  null,"SELECT * FROM nei.dbo.TicketO WHERE TicketO.Job='{$_GET['ID']}' AND fWork='{$My_User['fWork']}'");
            $r2 = $database->query( null,"SELECT * FROM nei.dbo.TicketD WHERE TicketD.Job='{$_GET['ID']}' AND fWork='{$My_User['fWork']}'");
            $r3 = $database->query( null,"SELECT * FROM nei.dbo.TicketDArchive WHERE TicketDArchive.Job='{$_GET['ID']}' AND fWork='{$My_User['fWork']}'");
            $r = sqlsrv_fetch_array($r);
            $r2 = sqlsrv_fetch_array($r2);
			$r3 = sqlsrv_fetch_array($r3);
            $Privileged = (is_array($r) || is_array($r2) || is_array($r3)) ? TRUE : FALSE;
		}
    } elseif($_SESSION['Branch'] == 'Customer' && $_SESSION['Branch_ID'] == $_GET['ID']){$Privileged = TRUE;}
    //
    if(!isset($array['ID'])  || !is_numeric($_GET['ID']) || !$Privileged || !is_numeric($_GET['ID'])){?><html><head><script>document.location.href="../login.php?Forward=job<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {
        $r = $database->query(null,
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
<div class='tab-pane fade in' id='tables-tickets-pills'>
	<div class="panel panel-primary" style='margin-bottom:0px;'>
		<div class="panel-body">
			<div class="row">
				<div class='col-md-12' >
					<div class="panel panel-primary">
						<!--<div class="panel-heading"><h3><i class="fa fa-bell fa-fw"></i> Tickets Table</h3></div>-->
						<div class="panel-body white-background BankGothic shadow">
							<div id='Form_Ticket'>
								<div class="panel panel-primary">
									<div class="panel-heading" style='position:fixed;width:750px;z-index:999;'><h2 style='display:block;'>Location Form</h2></div>
									<div class="panel-body white-background BankGothic shadow" style='padding-top:100px;'>
										<div style='display:block !important;'>
											<fieldset >
												<legend>Ticket Information</legend>
												<editor-field name='ID'></editor-field>
												<editor-field name='Status'></editor-field>
												<editor-field name='Mechanic'></editor-field>
												<editor-field name='Created'></editor-field>
												<editor-field name='Dispatched'></editor-field>
												<editor-field name='Worked'></editor-field>
												<editor-field name='Total'></editor-field>
												<editor-field name='Description'></editor-field>
												<editor-field name='Resolution'></editor-field>
											</fieldset>
											<fieldset>
												<legend>Location</legend>
												<editor-field name='Location'></editor-field>
												<editor-field name='Street'></editor-field>
												<editor-field name='City'></editor-field>
												<editor-field name='State'></editor-field>
												<editor-field name='Zip'></editor-field>
												<editor-field name='Route'></editor-field>
												<editor-field name='Division'></editor-field>
												<editor-field name='Maintenance'></editor-field>
											</fieldset>
											<fieldset>
												<legend>Unit</legend>
												<editor-field name='Unit_State'></editor-field>
												<editor-field name='Unit_Label'></editor-field>
												<editor-field name='Unit_Type'></editor-field>
												<editor-field name='Unit_Description'></editor-field>
											</fieldset>
										</div>
									</div>
								</div>
							</div>
							<table id='Table_Tickets' class='display' cellspacing='0' width='100%'>
								<thead>
									<th></th>
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
				</div>
				<script>
				var Editor_Tickets = new $.fn.dataTable.Editor({
					ajax: "php/post/Ticket.php?ID=<?php echo $_GET['ID'];?>",
					table: "#Table_Tickets",
					template: '#Form_Ticket',
					formOptions: {
						inline: {
							submit: "allIfChanged"
						}
					},
					idSrc: "ID",
					fields : [{
						label: "ID",
						name: "ID"
					},{
						label: "Location",
						name: "Location",
						type:"select",
						options: [<?php
							$r = $database->query(null,"
								SELECT   Loc.Tag AS Location
								FROM     nei.dbo.Loc
								WHERE    Loc.Owner = ?
								GROUP BY Loc.Tag
								ORDER BY Loc.Tag ASC
							;",array($_GET['ID']));
							$Locations = array();
							if($r){while($Location = sqlsrv_fetch_array($r)){$Locations[] = '{' . "label: '{$Location['Location']}', value:'{$Location['Location']}'" . '}';}}
							echo implode(",",$Locations);
						?>]
					},{
						label: "Mechanic",
						name: "Mechanic",
						type: "select",
						options: [<?php 
							$r = $database->query(null,"
								SELECT   Emp.fFirst + ' ' + Emp.Last AS Mechanic
								FROM     nei.dbo.Emp
								WHERE    Emp.Field = 1
								GROUP BY Emp.fFirst + ' ' + Emp.Last
								ORDER BY Emp.fFirst + ' ' + Emp.Last ASC
							;");
							$Statuses = array();
							if($r){while($Status = sqlsrv_fetch_array($r)){$Statuses[] = '{' . "label: '{$Status['Mechanic']}', value:'{$Status['Mechanic']}'" . '}';}}
							echo implode(",",$Statuses);
						?>]
					},{
						label: "Worked",
						name: "Worked",
						type: "datetime"
					},{
						label: "Status",
						name: "Status",
						type: "select",
						options: [<?php
							$r = $database->query(null,"
								SELECT   TickOStatus.Type AS Status
								FROM     nei.dbo.TickOStatus
								GROUP BY TickOStatus.Type
								ORDER BY TickOStatus.Type ASC
							;");
							$Statuses = array();
							if($r){while($Status = sqlsrv_fetch_array($r)){$Statuses[] = '{' . "label: '{$Status['Status']}', value:'{$Status['Status']}'" . '}';}}
							echo implode(",",$Statuses);
						?>]
					},{
						label:"Total Hours",
						name:"Total"
					},{
						label:"Street",
						name:"Street"
					},{
						label:"City",
						name:"City"
					},{
						label:"State",
						name:"State"
					},{
						label:"Zip",
						name:"Zip"
					},{
						label:"Route",
						name:"Route"
					},{
						label:"Division",
						name:"Division"
					},{
						label:"Maintenance",
						name:"Maintenance",
						type:"radio",
						options: [
							{label: "Not Maintained", value:0},
							{label: "Maintained", value:1}
						]
					},{
						label:"Description",
						name:"Description",
						type:"textarea"
					},{
						label:"Resolution",
						name:"Resolution",
						type:"textarea"
					},{
						label:"State",
						name:"Unit_State",
						type:"select",
						options: [<?php
							$r = $database->query(null,"
								SELECT Elev.State AS State
								FROM   nei.dbo.Elev
									   LEFT JOIN nei.dbo.Loc ON Elev.Loc = Loc.Loc
								WHERE  Loc.Owner = ?
							;",array($_GET['ID']));
							$Units = array();
							if($r){while($Unit = sqlsrv_fetch_array($r)){$Units[] = '{' . "label: '{$Unit['State']}', value:'{$Unit['State']}'" . '}';}}
							echo implode(",",$Units);
						?>]
					},{
						label:"Label",
						name:"Unit_Label"
					},{
						label:"Description",
						name:"Unit_Description",
						type:"textarea"
					},{
						label:"Type",
						name:"Unit_Type"
					}]
				});
				Editor_Tickets.field('ID').disable();
				Editor_Tickets.field('Street').disable();
				Editor_Tickets.field('City').disable();
				Editor_Tickets.field('State').disable();
				Editor_Tickets.field('Zip').disable();
				Editor_Tickets.field('Route').disable();
				Editor_Tickets.field('Division').disable();
				Editor_Tickets.field('Maintenance').disable();
				Editor_Tickets.field('Unit_Label').disable();
				Editor_Tickets.field('Unit_Description').disable();
				Editor_Tickets.field('Unit_Type').disable();
				Editor_Tickets.dependent('Status',function( val ) {
					if(val === 'Open' || val === 'Assigned'){
						return { enable : ['Mechanic', 'Total', 'Status', 'Location'], hide : ['Resolution'] };
					} else if(val === 'Completed'){
						return { disable : ['Location', 'Mechanic', 'Date',  'Total'], show : ['Resolution'] }
					} else {
						return { disable : ['Mechanic','Resolution'] };
					}
							
				});
				$(Editor_Tickets.field('Location').node()).on('change',function(){
					var dName = Editor_Tickets.field('Location').val();
					$.ajax({
						url:'php/get/Location_by_Name.php',
						method:"GET",
						dataType: "json",
						data: { Name : dName},
						success:function( code ){
							var Location = eval(code);
							Editor_Tickets.field('Street').set( Location.data[0].Street );
							Editor_Tickets.field('City').set( Location.data[0].City );
							Editor_Tickets.field('State').set( Location.data[0].State );
							Editor_Tickets.field('Zip').set( Location.data[0].Zip );
							Editor_Tickets.field('Route').set( Location.data[0].Route );
							Editor_Tickets.field('Division').set( Location.data[0].Division );
							Editor_Tickets.field('Maintenance').set( Location.data[0].Maintenance );
							$.ajax({
								url:'php/get/Unit_States_by_Location.php?ID=' + Location.data[0].ID,
								method:"GET",
								dataType:"JSON",
								success:function(code){
									var States = eval(code);
									Editor_Tickets.field('Unit_State').update(States);
								}
							});
						}
					});
				});
				$(Editor_Tickets.field('Unit_State').node()).on('change',function(){
					var dState = Editor_Tickets.field('Unit_State').val();
					$.ajax({
						url:'php/get/Unit_by_State.php',
						method:"GET",
						dataType: "json",
						data: { State : dState},
						success:function( code ){
							var Unit = eval(code);
							Editor_Tickets.field('Unit_Label').set( Unit.data[0].Label );
							Editor_Tickets.field('Unit_Type').set( Unit.data[0].Type );
							Editor_Tickets.field('Unit_Description').set( Unit.data[0].Description );
							/*Editor_Tickets.field('Zip').set( Unit.data[0].Zip );
							Editor_Tickets.field('Route').set( Unit.data[0].Route );
							Editor_Tickets.field('Division').set( Unit.data[0].Division );
							Editor_Tickets.field('Maintenance').set( Unit.data[0].Maintenance );*/
						}
					});
				});
				/*$('#Table_Tickets').on( 'click', 'tbody td:not(:first-child)', function (e) {
					Editor_Tickets.inline( this );
				} );*/
				var Table_Tickets = $('#Table_Tickets').DataTable( {
					"ajax": {
						"url":"bin/php/get/Tickets_by_Job.php?ID=<?php echo $_GET['ID'];?>",
						"dataSrc":function(json){if(!json.data){json.data = [];}return json.data;}
					},
					"columns": [
						{
							"className":      'details-control',
							"orderable":      false,
							"data":           null,
							"defaultContent": ''
						},{ 
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
						},/*{ 	
							extend: "create", 
						 	editor: Editor_Tickets 
						},{ 
							extend: "edit",   
							editor: Editor_Tickets 
						},{ 
							extend : "remove", 
							editor : Editor_Tickets 
						},*/{ 
							text : "View",
							action:function(e,dt,node,config){
								document.location.href = 'ticket.php?ID=' + $("#Table_Tickets tbody tr.selected td:nth-child(2)").html();
						  	}
						},{ 
							text : "Preview",
							action:function(e,dt,node,config){
								$("tr.selected").each(function(){
									var tr = $(this);
									var row = Table_Tickets.row( tr );

									if ( row.child.isShown() ) {
										row.child.hide();
										tr.removeClass('shown');
									}
									else {
										row.child( format(row.data()) ).show();
										tr.addClass('shown');
									}
								});
						  	}
						}
					],
					<?php require('../../../js/datatableOptions.php');?>
				} );
				/*$('#Table_Tickets tbody').on('click', 'td.details-control', function () {
					var tr = $(this).closest('tr');
					var row = Table_Tickets.row( tr );

					if ( row.child.isShown() ) {
						row.child.hide();
						tr.removeClass('shown');
					}
					else {
						row.child( format(row.data()) ).show();
						tr.addClass('shown');
					}
				} );*/
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
				function format ( d ) {
					return "<div>"+
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
								"<div class='Created'><div class='label1'>Created:</div><div class='data'>"+d.CDate+"</div></div>"+
								"<div class='Dispatched'><div class='label1'>Dispatched:</div><div class='data'>"+d.EDate+"</div></div>"+
								"<div class='Type'><div class='label1'>Type:</div><div class='data'>"+d.Job_Type+"</div></div>"+
								"<div class='Level'><div class='label1'>Level:</div><div class='data'>"+d.Level+"</div></div>"+
								"<div class='Category'><div class='label1'>Category:</div><div class='data'>"+d.Category+"</div></div>"+
							"</div>"+
							"<div class='column'>"+
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
					"<div><a href='ticket.php?ID="+d.ID+"' target='_blank'>View Ticket</a></div>"
				}
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
	$("#tables-tickets-pills").addClass('active');
});
</script>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=customer<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>