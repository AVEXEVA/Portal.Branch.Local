<?php 
session_start();
require('../../index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    $Privileged = FALSE;
    if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Elevator'){
        $User         = sqlsrv_query($NEI,"SELECT * FROM Emp WHERE ID='{$User}'");
        $User         = sqlsrv_fetch_array($User);
        $Field        = ($User['Field'] == 1 && $User['Title'] != "OFFICE") ? True : False;
        $r            = sqlsrv_query($Portal,"
            SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
            FROM   Portal.dbo.Privilege
        	WHERE User_ID = '{$_SESSION['User']}'
        ;");
        $My_Privileges   = array();
        while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
        $Privileged   = FALSE;
        if(isset($My_Privileges['Job']) && $My_Privileges['Job']['User_Privilege'] >= 4 && $My_Privileges['Job']['Group_Privilege'] >= 4 && $My_Privileges['Job']['Other_Privilege'] >= 4){$Privileged = TRUE;}
    }
    if(!$Privileged || !isset($_GET['ID']) || !is_numeric($_GET['ID'])){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
        $r = sqlsrv_query($NEI,"
                SELECT Modernization.*,
                       Modernization.ID                                                                    AS  ID,
                       Job.ID                                                                              AS  Job,
                       Job.fDesc                                                                           AS  Name,
                       Loc.Tag                                                                             AS  Location,
                       Emp.fFirst + ' ' + Emp.Last                                                         AS  Supervisor,
                       Emp.ID                                                                              AS  Supervisor_ID,
                       Modernization.EBN                                                                   AS  EBN,
                       Modernization.Date_Removed                                                          AS  Removed,
                       Modernization.Actual_Return                                                         AS  Returned,
                       Elev.State                                                                          AS  Unit,
                       Elev.ID                                                                             AS  Unit_ID,
                       Modernization.Budget_Hours                                                          AS  Budget_Hours
                FROM   (((Portal.dbo.Modernization
                       LEFT JOIN nei.dbo.Emp           ON  Modernization.Supervisor = Emp.ID)
                       LEFT JOIN nei.dbo.Job           ON  Modernization.Job        = Job.ID)
                       LEFT JOIN nei.dbo.Loc           ON  Job.Loc                  = Loc.Loc)
                       LEFT JOIN nei.dbo.Elev          ON  Elev.ID                  = Modernization.Unit
                WHERE  Modernization.ID = ?
            ;",array($_GET['ID']));
        $Modernization = sqlsrv_fetch_array($r);
        $r2 = sqlsrv_query($Portal,"
            SELECT   Mod_Tracker.Time_Stamp, Mod_Status.Title
            FROM     Mod_Tracker
                     LEFT JOIN Mod_Status ON Mod_Tracker.Status = Mod_Status.ID
            WHERE    Mod_Tracker.Modernization = ?
            ORDER BY 1 DESC
		;",array($Modernization['ID']));
        if($r2){
            $Modernization['Status'] = sqlsrv_fetch_array($r2)['Title'];
        }
        $r = sqlsrv_query($NEI,
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
            FROM    Elev
                    LEFT JOIN nei.dbo.Loc           ON Elev.Loc = Loc.Loc
                    LEFT JOIN nei.dbo.Zone          ON Loc.Zone = Zone.ID
                    LEFT JOIN nei.dbo.OwnerWithRol  ON Loc.Owner = OwnerWithRol.ID
                    LEFT JOIN nei.dbo.Route ON Loc.Route = Route.ID
                    LEFT JOIN nei.dbo.Emp ON Route.Mech = Emp.fWork
            WHERE   Elev.ID = ?
		;",array($Modernization['Unit_ID']));
        $Unit = sqlsrv_fetch_array($r);
        $data = $Unit;

        $r2 = sqlsrv_query($NEI,"
            SELECT *
            FROM   ElevTItem
            WHERE  ElevTItem.ElevT = '1'
                   AND ElevTItem.Elev=?
        ;",array($Modernization['Unit_ID']));
        if($r2){while($array = sqlsrv_fetch_array($r2)){$Unit[$array['fDesc']] = $array['Value'];}}?>
        <script>
            $(document).ready(function(){
				$("input[name='Removed']").datepicker({onSelect:function(dateText, inst){}});
				$("input[name='Returned']").datepicker({onSelect:function(dateText, inst){}});
			});
			function lookupJobs(link){
				$.ajax({
					method:"GET",
					url:"cgi-bin/php/element/select_Job_by_Location.php?ID=" + $(link).val(),
					success:function(code){$("td#tdJob").html(code);}
				});
				$.ajax({
					method:"GET",
					url:"cgi-bin/php/element/select_Unit_by_Location.php?ID=" + $(link).val(),
					success:function(code){$("td#tdUnit").html(code);}
				});
			}
        </script>
        <div class='row'>
            <div class='col-md-3'>
                <form id='modernization'>
                <input type='hidden' name='ID' value='<?php echo $_GET['ID'];?>' />
                <table>
                    <tr>
                        <td style='text-align:left;'><label for='Location'>Location:&nbsp;</label></td>
                        <td><input type='text' disabled='disabled' value='<?php echo $Modernization['Location'];?>' size='35' /></td>
                    </tr>
                    <tr>
                        <td style='text-align:left;'><label for='Job'>Job:&nbsp;</label></td>
                        <td id='tdJob'><input type='text' name='job_name' disabled='disabled' value='<?php echo $Modernization['Job'] . " :: " . $Modernization['Name']?>' size='35' /><input type='hidden' name='Job' value='<?php echo $Modernization['Job'];?>' /></td>
                    </tr>
                    <tr>
                        <td style='text-align:left;'><label for='Unit'>Unit:&nbsp;</label></td>
                        <td id='tdUnit'><input type='text' disabled='disabled' value='<?php echo $Modernization['Unit'];?>' size='35' /><input type='hidden' value='<?php echo $Modernization['Unit_ID'];?>' name='Unit' </td>
                    </tr>
                    <tr>
                        <td style='text-align:left;'><label for='Status'>Status:&nbsp;</label></td>
                        <td id='tdStatus'><select name='Status'>
                            <?php 
                                $r = sqlsrv_query($Portal,"SELECT Mod_Status.ID AS ID, Mod_Status.Title AS Title FROM Mod_Status");
                                if($r){
                                    while($Status = sqlsrv_fetch_array($r)){
                                        ?><option value='<?php echo $Status['ID'];?>' <?php if($Modernization['Status'] == $Status['Title']){?>selected='selected'<?php }?>><?php echo $Status['Title'];?></option><?php 
                                    }
                                }?>
                        </select></td>
                    </tr>
                    <tr>   
                        <td style='text-align:left;'><label for='Supervisor'>Supervisor:&nbsp;</label></td>
                        <td id='tdSupervisor'><select name='Supervisor'>
                            <?php
                                $r = sqlsrv_query($NEI,"
                                    SELECT 
                                        Emp.ID AS Ref,
                                        Emp.fFirst + ' ' + Emp.Last AS Supervisor
                                    FROM 
                                        Portal.dbo.Mod_Supervisor 
                                        LEFT JOIN nei.dbo.Emp ON Mod_Supervisor.Employee_ID = stuff(Emp.Ref, 1, patindex('%[0-9]%', Emp.Ref)-1, '')
                                    WHERE
                                        Emp.Status = '0'
                                ");
                                $Supervisors = array();
                                while($Supervisor = sqlsrv_fetch_array($r)){
                                    if(isset($Supervisors[trim($Supervisor['Ref'])])){continue;}
                                    else{$Supervisors[trim($Supervisor['Ref'])] = $Supervisor;}
                                    ?><option value='<?php echo $Supervisor['Ref'];?>' <?php if($Supervisor['Ref'] == $Modernization['Supervisor_ID']){?>selected='selected'<?php }?>><?php echo $Supervisor['Supervisor'];?></option><?php 
                                }
                            ?>
                        </select></td>
                    </tr>
                    <tr>    
                        <td style='text-align:left;'><label for='Removed'>Removed:&nbsp;</label></td>
                        <td><input type='text' name='Removed' value='<?php echo ("1900-01-01" == $Modernization['Removed'] || $Modernization['Removed'] == '') ? '' : DateTime::createFromFormat('Y-m-d',$Modernization['Removed'])->format("m/d/Y");?>' size='35' /></td>
                    </tr>
                    <tr>    
                        <td style='text-align:left;'><label for='Returned'>Returned:&nbsp;</label></td>
                        <td><input type='text' name='Returned' value='<?php echo ("1900-01-01" == $Modernization['Returned'] || $Modernization['Returned'] == '') ? '' :DateTime::createFromFormat('Y-m-d',$Modernization['Returned'])->format("m/d/Y");?>' size='35' /></td>
                    </tr>
                    <tr>    
                        <td style='text-align:left;' ><label for='EBN'>EBN:&nbsp;</label></td>
                        <td style='position:relative;'><input type='text' ondblclick="hyperlinkInput(this);" name='EBN' value='<?php echo $Modernization['EBN'];?>' autocomplete='off' size='35' /></td>
                    </tr>
                    <tr>    
                        <td style='text-align:left;'><label for='Budget_Hours'>Budgeted Hours:&nbsp;</label></td>
                        <td><input type='text' name='Budget_Hours' value='<?php echo $Modernization['Budget_Hours'];?>' /></td>
                    </tr>
                    <tr>
                        <td style='text-align:left;'><label for='Hyperlink'>Contract:&nbsp;</label></td>
                        <td style='position:relative;'><input type='text' ondblclick="hyperlinkInput(this);" name='Hyperlink' value='<?php echo $Modernization['Hyperlink'];?>' autocomplete='off' size='35' /></td>
                    </tr>
                    <tr>
                        <td style='text-align:left;'><label for='Hyperlink'>Package:&nbsp;</label></td>
                        <td style='position:relative;'><input type='text' ondblclick="hyperlinkInput(this);" name='Package' value='<?php echo $Modernization['Package'];?>' autocomplete='off' size='35' /></td>
                    </tr>
                </table>
                </form>
            </div>
            <form id='Survey_Sheet'>
				<input type='hidden' name='Unit' value='<?php echo $Unit['ID'];?>' />
				<div class='col-md-3'>
					<table class='Survey_Sheet'>
						<tr><td><label for='Capacity'>Hours Allocations</label></td><td><input type='text' <?php if($Survey_Sheet){?>disabled='disabled='<?php }?> name='Hours Allocations' value="<?php echo $Unit['Hours Allocations'];?>" /></td></tr>
						<tr><td><label for='Capacity'>Capacity</label></td><td><input type='text' <?php if($Survey_Sheet){?>disabled='disabled='<?php }?> name='Capacity' value="<?php echo $Unit['Capacity'];?>" /></td></tr>
						<tr><td><label for='Capacity'>Car Speed</label></td><td><input type='text' <?php if($Survey_Sheet){?>disabled='disabled='<?php }?> name='Car Speed' value="<?php echo $Unit['Car Speed'];?>" /></td></tr>
						<tr><td><label for='Capacity'># of Openings</label></td><td><input type='text' <?php if($Survey_Sheet){?>disabled='disabled='<?php }?> name='# Of Openings' value="<?php echo $Unit['# Of Openings'];?>" /></td></tr>
						<tr><td><label for='Capacity'># of Landings</label></td><td><input type='text' <?php if($Survey_Sheet){?>disabled='disabled='<?php }?> name='# Of Landings' value="<?php echo $Unit['# Of Landings'];?>" /></td></tr>
						<tr><td><label for='Capacity'>Opening Types</label></td><td><input type='text' <?php if($Survey_Sheet){?>disabled='disabled='<?php }?> name='Opening Types' value="<?php echo $Unit['Opening Types'];?>" /></td></tr>
						<tr><td><label for='Capacity'>Door Monitoring</label></td>
							<td><input type='hidden' name='Door Monitoring' value='No' />
								<input type='checkbox' name='Door Monitoring' value='Yes' <?php if($Unit['Door Monitoring'] == 'Yes'){?>checked='checked'<?php }?> /></td>
						</tr>
						<tr><td><label for='Operation'>Operation:</label></td>
							<td><select name='Operation'>
									<option value=''>Please Select Option</option>
									<option value='Automatic' <?php if($Unit['Operation'] == 'Automatic'){?>selected='selected'<?php }?>>Automatic</option>
									<option value='Semi-Automatic' <?php if($Unit['Operation'] == 'Semi-Automatic'){?>selected='selected'<?php }?>>Semi-Automatic</option>
									<option value='Manual' <?php if($Unit['Operation'] == 'Manual'){?>selected='selected'<?php }?>>Manual</option>
								</select>
							</td>
						</tr>
					</table>
				</div>
				<div class='col-md-3'>
					<table class='Survey_Sheet'>
						<tr><td><label for='Capacity'>Motor Room Location</label></td><td><input type='text' <?php if($Survey_Sheet){?>disabled='disabled='<?php }?> name='Motor Room Location' value="<?php echo $Unit['Motor Room Location'];?>" /></td></tr>
						<tr><td><label for='Operation'>Machine Type:</label></td>
							<td><select name='Machine Type'>
									<option value=''>Please Select Option</option>
									<option value='Overhead Worm Gear Traction' <?php if($Unit['Machine Type'] == 'Overhead Worm Gear Traction'){?>selected='selected'<?php }?>>Overhead Worm Gear Traction</option>
									<option value='Basement Worm Gear' <?php if($Unit['Machine Type'] == 'Basement Worm Gear'){?>selected='selected'<?php }?>>Basement Worm Gear</option>
									<option value='Traction Gearless' <?php if($Unit['Machine Type'] == 'Traction Gearless'){?>selected='selected'<?php }?>>Traction Gearless</option>
									<option value='Oil Hydrualic' <?php if($Unit['Machine Type'] == 'Oil Hydrualic'){?>selected='selected'<?php }?>>Oil Hydrualic</option>
									<option value='Roped Hydraulic' <?php if($Unit['Machine Type'] == 'Roped Hydraulic'){?>selected='selected'<?php }?>>Roped Hydraulic</option>
									<option value='MRL' <?php if($Unit['Machine Type'] == 'MRL'){?>selected='selected'<?php }?>>MRL</option>
									<option value='Drum' <?php if($Unit['Machine Type'] == 'Drum'){?>selected='selected'<?php }?>>Drum</option>
									<option value='Other' <?php if($Unit['Machine Type'] == 'Other'){?>selected='selected'<?php }?>>Other</option>
								</select>
							</td>
						</tr>
						<tr><td><label for='Capacity'>Machine Location</label></td><td><input type='text' <?php if($Survey_Sheet){?>disabled='disabled='<?php }?> name='Machine Location' value="<?php echo $Unit['Machine Location'];?>" /></td></tr>
						<tr><td><label for='Capacity'>Machine Make</label></td><td><input type='text' <?php if($Survey_Sheet){?>disabled='disabled='<?php }?> name='Machine Make' value="<?php echo $Unit['Machine Make'];?>" /></td></tr>
						<tr><td><label for='Capacity'>Machine Model #</label></td><td><input type='text' <?php if($Survey_Sheet){?>disabled='disabled='<?php }?> name='Machine Model #' value="<?php echo $Unit['Machine Model #'];?>" /></td></tr>
						<tr><td><label for='Capacity'>Machine Serial No #.</label></td><td><input type='text' <?php if($Survey_Sheet){?>disabled='disabled='<?php }?> name='Machine Serial #' value="<?php echo $Unit['Machine Serial #'];?>" /></td></tr>
					</table>
				</div>
				<div class='col-md-3'>
					<table class='Survey_Sheet'>
						<tr><td><label for='Capacity'>Controller Manufacturer</label></td><td><input type='text' <?php if($Survey_Sheet){?>disabled='disabled='<?php }?> name='Controller Manufacturer' value="<?php echo $Unit['Controller Manufacturer'];?>" /></td></tr>
						<tr><td><label for='Capacity'>Controller Model</label></td><td><input type='text' <?php if($Survey_Sheet){?>disabled='disabled='<?php }?> name='Controller Model' value="<?php echo $Unit['Controller Model'];?>" /></td></tr>
						<tr><td><label for='Capacity'>Controller Serial</label></td><td><input type='text' <?php if($Survey_Sheet){?>disabled='disabled='<?php }?> name='Controller Serial #' value="<?php echo $Unit['Controller Serial #'];?>" /></td></tr>
						<tr><td><label for='Capacity'>Controller Manufacturer Job No #.</label></td><td><input type='text' <?php if($Survey_Sheet){?>disabled='disabled='<?php }?> name='Controller Manufacturer Job #' value="<?php echo $Unit['Controller Manufacturer Job #'];?>" /></td></tr>
						<tr><td><label for='Controller Prints'>Controller Prints</label></td>
							<td style='position:relative;'><input type='text' ondblclick="hyperlinkInput(this);" name='Controller Prints' value='<?php echo $Unit['Controller Prints'];?>' autocomplete='off' /></td>
						</tr>
					</table>
				</div>
				<div class='col-md-3'>
					<table class='Survey_Sheet'>
						<tr><td><label for='Capacity'>Car Governor Manufacturer</label></td><td><input type='text' <?php if($Survey_Sheet){?>disabled='disabled='<?php }?> name='Car Governor Manufacturer' value="<?php echo $Unit['Car Governor Manufacturer'];?>" /></td></tr>
						<tr><td><label for='Capacity'>Car Governor Model</label></td><td><input type='text' <?php if($Survey_Sheet){?>disabled='disabled='<?php }?> name='Car Governor Model' value="<?php echo $Unit['Car Governor Model'];?>" /></td></tr>
						<tr><td><label for='Capacity'>Car Governor Serial No #.</label></td><td><input type='text' <?php if($Survey_Sheet){?>disabled='disabled='<?php }?> name='Car Governor Serial #' value="<?php echo $Unit['Car Governor Serial #'];?>" /></td></tr>
					</table>
				</div>
				<div class='col-md-3'>
					<table class='Survey_Sheet'>
						<tr><td><label for='Capacity'>Hoist Cable Quantity</label></td><td><input type='text' <?php if($Survey_Sheet){?>disabled='disabled='<?php }?> name='Hoist Cable Quantity' value="<?php echo $Unit['Hoist Cable Quantity'];?>" /></td></tr>
						<tr><td><label for='Capacity'>Hoist Cable Length</label></td><td><input type='text' <?php if($Survey_Sheet){?>disabled='disabled='<?php }?> name='Hoist Cable Length' value="<?php echo $Unit['Hoist Cable Length'];?>" /></td></tr>
						<tr><td><label for='Capacity'>Hoist Cable Diameter</label></td><td><input type='text' <?php if($Survey_Sheet){?>disabled='disabled='<?php }?> name='Hoist Cable Diameter' value="<?php echo $Unit['Hoist Cable Diameter'];?>" /></td></tr>
						<tr><td><label for='Capacity'>Hoist Cable Roping Type</label></td><td><input type='text' <?php if($Survey_Sheet){?>disabled='disabled='<?php }?> name='Hoist Cable Roping Type' value="<?php echo $Unit['Hoist Cable Roping Type'];?>" /></td></tr>
						<tr><td><label for='Capacity'>Hoist Cable Material Type</label></td><td><input type='text' <?php if($Survey_Sheet){?>disabled='disabled='<?php }?> name='Hoist Cable Material Type' value="<?php echo $Unit['Hoist Cable Material Type'];?>" /></td></tr>
						<tr><td><label for='Capacity'>Type of Shackle</label></td><td><input type='text' <?php if($Survey_Sheet){?>disabled='disabled='<?php }?> name='Type Of Shackle' value="<?php echo $Unit['Type Of Shackle'];?>" /></td></tr>
					</table>
				</div>
				<div class='col-md-3'>
					<table class='Survey_Sheet'>
						<tr><td><label for='Capacity'>Governor Cable Length</label></td><td><input type='text' <?php if($Survey_Sheet){?>disabled='disabled='<?php }?> name='Governor Cable Length' value="<?php echo $Unit['Governor Cable Length'];?>" /></td></tr>
						<tr><td><label for='Capacity'>Governor Cable Diameter</label></td><td><input type='text' <?php if($Survey_Sheet){?>disabled='disabled='<?php }?> name='Governor Cable Diameter' value="<?php echo $Unit['Governor Cable Diameter'];?>" /></td></tr>
						<tr><td><label for='Capacity'>Governor Cable Material Type</label></td><td><input type='text' <?php if($Survey_Sheet){?>disabled='disabled='<?php }?> name='Governor Cable Material Type' value="<?php echo $Unit['Governor Cable Material Type'];?>" /></td></tr>
					</table>
				</div>
            </form>
        </div>
        <button OnClick='updateModernization();'>Update</button>
    	<table id='Table_Modernization_Equipment' class='display' cellspacing='0' width='100%'>
            <thead>
            	<th></th>
                <th>ID</th>
                <th>In Care Of</th>
                <th>Subcontractor</th>
                <th>Equipment</th>
                <th>Quantity</th>
                <th>Description</th>
                <th>Version</th>
                <th>Status</th>
            </thead>
            <tbody></tbody>
        </table>
		<table id='Table_Modernization_Tasks' class='display' cellspacing='0' width='100%'>
            <thead>
                <th>Task</th>
				<th>Status</th>
				<th></th>
            </thead>
            <tbody></tbody>
        </table>
        <script>
		function deleteModernizationTask(link){
			var Modernization = "<?php echo $_GET['ID'];?>";
			var Task_Name = ""
			
		}
		var EdittingStatus = 0;
        $(document).ready(function() {
            var Table_Modernization_Equipment = $('#Table_Modernization_Equipment').DataTable( {
                "ajax": {
                    "url":"php/get/Modernization_Equipment.php?ID=<?php echo $_GET['ID'];?>",
                    "dataSrc":function(json){
                        if(!json.data){json.data = [];}
                        return json.data;
                    } 
                },
                "columns": [
                	{
                        "className":      'details-control',
                        "orderable":      false,
                        "data":           null,
                        "defaultContent": ''
                    },
                    {
                        "data": "ID",
                        "className":"hidden"
                    },
                    { "data": "In_Care_Of"},
                    { "data": "Subcontractor" },
                    { "data": "Equipment"},
                    { "data": "Quantity"},
                    { "data": "Description"},
                    
                    { "data": "Version"},
                    { "data": "Status"}
                ],
                "order": [[1, 'asc']],
                "lengthMenu":[[10,25,50,100,500,-1,0],[10,25,50,100,500,"All","None"]],
                "language":{"loadingRecords":""}, 
                "rowGroup": {
                    dataSrc: 'Status',
                    startRender: null,
                    endRender: function ( rows, group ) {
                        return $('<tr/>');
                    },
                },
                drawCallback: function (settings) {
                    var api = this.api();
                    var rows = api.rows({ page: 'current' }).nodes();
                    var last = null;

                    api.column(8, { page: 'current' }).data().each(function (group, i) {

                        if (last !== group) {

                            $(rows).eq(i).before(
                                '<tr class="group"><td colspan="14" style="BACKGROUND-COLOR:#337ab7;font-weight:700;color:white;">' + group  + '</td></tr>'
                            );

                            last = group;
                        }
                    });
                },
                "initComplete":function(){}
            } );
            $("Table#Table_Modernization_Equipment").on("draw.dt",function(){
                //$("#Table_Modernizations tbody").prepend("<tr class='new'><td colspan='14' style='background-color:#4482cd;color:white;'>Add New Modernization</td></tr>");
                $("Table#Table_Modernization_Equipment tr[role='row']").on("dblclick",function(){
                    $.ajax({
                        url:"cgi-bin/php/element/modernization/modernization_equipment.php?ID=" + $(this).children("td:nth-child(2)").html(),
                        method:"GET",
                        success:function(code){
                            modernizationTracker(null,code);

                        }
                    });
                });
            });
            $("Table#Table_Modernization_Equipment").on("click","tr",function(){
                $(".selected").toggleClass("selected");
                $(this).toggleClass("selected");
            });
            $('#Table_Modernization_Equipment tbody').on('click', 'td.details-control', function () {
                var tr = $(this).closest('tr');
                var row = Table_Modernization_Equipment.row( tr );
         
                if ( row.child.isShown() ) {
                    row.child.hide();
                    tr.removeClass('shown');
                }
                else {
                    row.child( formatEquipment(row.data()) ).show();
                    tr.addClass('shown');
                }
            } );
            <?php if(!$Mobile){?>
            yadcf.init(Table_Modernization_Equipment,[
                {   column_number:2},
                {   column_number:3},
                {   column_number:8}
            ]);
            stylizeYADCF();
            <?php }?>
			var Table_Modernization_Tasks = $('#Table_Modernization_Tasks').DataTable( {
				altEditor: true,      // Enable altEditor ****
				dom: 'Bfrtip',
				buttons: [{
				  text: 'Add',
				  name: 'add'        // DO NOT change name
				},
				{
				  extend: 'selected', // Bind to Selected row
				  text: 'Edit',
				  name: 'edit'        // DO NOT change name
				},
				{
				  extend: 'selected', // Bind to Selected row
				  text: 'Delete',
				  name: 'delete'      // DO NOT change name
				}],
				"ajax": {
					"url":"php/get/Modernization_Tasks.php?ID=<?php echo $_GET['ID'];?>",
					"dataSrc":function(json){
						if(!json.data){json.data = [];}
						return json.data;
					} 
				},
				"columns": [
					{ "data": "Name",
					"type":"string"},
					{ "data": "Status",
						"className":"Status"},
					{ "data":"Buttons",
						"type":"html"}
				],
				"order": [[0, 'asc']],
				"lengthMenu":[[10,25,50,100,500,-1,0],[10,25,50,100,500,"All","None"]],
				"language":{"loadingRecords":""}, 
				"initComplete":function(){}
            } );
			$("Table#Table_Modernization_Tasks").on("draw.dt",function(){
				$("tr.new").remove();
				editableColumns('Table_Modernization_Tasks',2);
				/*$("#Table_Modernization_Tasks tbody").prepend("<tr class='new'><td colspan='2' style='background-color:#3d3d3d;color:white;text-align:center;' onClick='addNewTask();'>Add New Task</td></tr>");*/
				var Statuses = [
					"0%",
					"25%",
					"50%",
					"75%",
					"Complete"
				];
				
				$('#Table_Modernization_Tasks tbody').on('click', '.Status', function() {
					var row = this.parentElement;
					if (!$('#Table_Modernization_Tasks').hasClass("editing")) {
						$('#Table_Modernization_Tasks').addClass("editing");
						var data = Table_Modernization_Tasks.row(row).data();
						var $row = $(row);
						var thisStatus = $row.find("td:nth-child(2)");
						var thisStatusText = thisStatus.text();
						thisStatus.empty().append($("<select></select>", {
							"id": "Status_" + data[0],
							"class": "changeStatus"
						}).append(function() {
							var options = [];
							$.each(Statuses, function(k, v) {
								options.push($("<option></option>", {
									"text": v,
									"value": v
								}))
							})
							return options;
						}));
						$("#Status_" + data[0]).val(thisStatusText);
						$('.changeStatus').on("change", function() {passData(this);});	
					}
				});
			} );
		});
		function passData(link){
			var select = $(link);
			var tempData = select.val();
			var parent = select.parent();
			parent.empty().text(tempData);
			var ref = encodeURIComponent(parent.prev().text());
			var tempData = encodeURIComponent(tempData);
			$("#Table_Modernization_Tasks").removeClass("editing");
			$.ajax({
				url:"cgi-bin/php/post/updateModernizationTask.php",
				method:"POST",
				data:"Modernization=<?php echo $_GET['ID'];?>&Task_Name=" + ref + "&Status=" + tempData,
				success:function(code){}
			})
		}	
		function editableColumns(TableID,Column){
		}
		function addNewTask(){
			$.ajax({
				url:"cgi-bin/php/element/modernization/Add_Modernization_Task.php?ID=<?php echo $_GET['ID'];?>",
				success:function(code){
					$("body").append(code);
				}
			});
		}
        </script>
        <?php 
    }
}?>