<?php 
session_start();
require('../../index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    $Privileged = FALSE;
    if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Elevator'){
        $r = sqlsrv_query($NEI,"SELECT * FROM nei.dbo.Emp WHERE ID = ?",array($_GET['User']));
        $My_User = sqlsrv_fetch_array($r);
        $Field = ($User['Field'] == 1 && $User['Title'] != "OFFICE") ? True : False;
        $r = sqlsrv_query($Portal,"
            SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
            FROM   Portal.dbo.Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
        $My_Privileges = array();
        while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
        $Privileged = FALSE;
        if(isset($My_Privileges['Job']) && $My_Privileges['Job']['User_Privilege'] >= 4 && $My_Privileges['Job']['Group_Privilege'] >= 4 && $My_Privileges['Job']['Other_Privilege'] >= 4){$Privileged = TRUE;}
    }
    if(!$Privileged || !isset($_GET['ID'])){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {

        $r = sqlsrv_query($Portal,"
                SELECT * 
                FROM Mod_Equipment
                WHERE 
                    Mod_Equipment.ID = '{$_GET['ID']}'
        ;");
        if($r){$Modernization_Equipment = sqlsrv_fetch_array($r);}
        if($Modernization_Equipment['Submitted'] == "1900-01-01 00:00:00.000"){$Modernization_Equipment['Submitted'] = '';}
        if($Modernization_Equipment['Purchased'] == "1900-01-01 00:00:00.000"){$Modernization_Equipment['Purchased'] = '';}
        if($Modernization_Equipment['Drawings_Received'] == "1900-01-01 00:00:00.000"){$Modernization_Equipment['Drawings_Received'] = '';}
        if($Modernization_Equipment['Drawings_Reviewed'] == "1900-01-01 00:00:00.000"){$Modernization_Equipment['Drawings_Reviewed'] = '';}
        if($Modernization_Equipment['Warehoused'] == "1900-01-01 00:00:00.000"){$Modernization_Equipment['Warehoused'] = '';}
        $r = sqlsrv_query($NEI,"
                SELECT 
                    Modernization.*,
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
                FROM 
                    (((Portal.dbo.Modernization
                    LEFT JOIN nei.dbo.Emp           ON  Modernization.Supervisor = Emp.ID)
                    LEFT JOIN nei.dbo.Job           ON  Modernization.Job = Job.ID)
                    LEFT JOIN nei.dbo.Loc           ON  Job.Loc = Loc.Loc)
                    LEFT JOIN nei.dbo.Elev          ON  Elev.ID = Modernization.Unit
                WHERE 
                    Modernization.ID = '{$Modernization_Equipment['Modernization']}'
            ;");
        $Modernization = sqlsrv_fetch_array($r);
        $r2 = sqlsrv_query($Portal,"
            SELECT Mod_Tracker.Time_Stamp, Mod_Status.Title
            FROM 
                Mod_Tracker
                LEFT JOIN Mod_Status ON Mod_Tracker.Status = Mod_Status.ID
            WHERE Mod_Tracker.Modernization='{$Modernization['ID']}'
            ORDER BY 1 DESC");
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
            FROM 
                ((((Elev
                LEFT JOIN nei.dbo.Loc           ON Elev.Loc = Loc.Loc)
                LEFT JOIN nei.dbo.Zone          ON Loc.Zone = Zone.ID)
                LEFT JOIN nei.dbo.OwnerWithRol  ON Loc.Owner = OwnerWithRol.ID)
                LEFT JOIN nei.dbo.Route ON Loc.Route = Route.ID)
                LEFT JOIN nei.dbo.Emp ON Route.Mech = Emp.fWork
            WHERE
                Elev.ID = '{$Modernization['Unit_ID']}'");
        $Unit = sqlsrv_fetch_array($r);
        $data = $Unit;

        $r2 = sqlsrv_query($NEI,"
            SELECT 
                *
            FROM ElevTItem
            WHERE 
                ElevTItem.ElevT = '1'
                AND ElevTItem.Elev='{$Modernization['Unit_ID']}'
        ;");
        if($r2){
            while($array = sqlsrv_fetch_array($r2)){
                $Unit[$array['fDesc']] = $array['Value'];
            }
        }

        ?>
        <script>
            $(document).ready(function(){
                $("input[name='Submitted']").datepicker({
                    onSelect:function(dateText, inst){
                        
                    }
                });
                $("input[name='Purchased']").datepicker({
                    onSelect:function(dateText, inst){
                        
                    }
                });
                $("input[name='Drawings_Received']").datepicker({
                    onSelect:function(dateText, inst){
                        
                    }
                });
                $("input[name='Drawings_Reviewed']").datepicker({
                    onSelect:function(dateText, inst){
                        
                    }
                });
                $("input[name='Warehoused']").datepicker({
                    onSelect:function(dateText, inst){
                        
                    }
                });
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
                <form id='modernization'><input type='hidden' name='ID' value='<?php echo $Modernization['ID'];?>' /><table>
                    <tr>
                        <td style='text-align:right;'><label for='Location'>Location:&nbsp;</label></td>
                        <td><input type='text' disabled='disabled' value='<?php echo $Modernization['Location'];?>' size='35' /></td>
                    </tr>
                    <tr>
                        <td style='text-align:right;'><label for='Job'>Job:&nbsp;</label></td>
                        <td id='tdJob'><input type='text' name='job_name' disabled='disabled' value='<?php echo $Modernization['Job'] . " :: " . $Modernization['Name']?>' size='35' /><input type='hidden' name='Job' value='<?php echo $Modernization['Job'];?>' /></td>
                    </tr>
                    <tr>
                        <td style='text-align:right;'><label for='Unit'>Unit:&nbsp;</label></td>
                        <td id='tdUnit'><input type='text' disabled='disabled' value='<?php echo $Modernization['Unit'];?>' size='35' /><input type='hidden' value='<?php echo $Modernization['Unit_ID'];?>' name='Unit' /></td>
                    </tr>
                    <tr>
                        <td style='text-align:right;'><label for='Status'>Status:&nbsp;</label></td>
                        <td id='tdStatus'><input type='text' name='Status' value='<?php echo $Modernization['Status'];?>' disabled='disabled'  /></td>
                    </tr>
                    <tr>   
                        <td style='text-align:right;'><label for='Supervisor'>Supervisor:&nbsp;</label></td>
                        <td id='tdSupervisor'><input type='text' value='<?php echo $Modernization['Supervisor'];?>' disabled='disabled' /></td>
                    </tr>
                    <tr>    
                        <td style='text-align:right;'><label for='Removed'>Removed:&nbsp;</label></td>
                        <td><input type='text'  disabled='disabled' name='Removed' value='<?php echo ("1900-01-01" == $Modernization['Removed'] || $Modernization['Removed'] == '') ? '' : DateTime::createFromFormat('Y-m-d',$Modernization['Removed'])->format("m/d/Y");?>' size='35' /></td>
                    </tr>
                    <tr>    
                        <td style='text-align:right;'><label for='Returned'>Returned:&nbsp;</label></td>
                        <td><input type='text' disabled='disabled' name='Returned' value='<?php echo ("1900-01-01" == $Modernization['Returned'] || $Modernization['Returned'] == '') ? '' :DateTime::createFromFormat('Y-m-d',$Modernization['Returned'])->format("m/d/Y");?>' size='35' /></td>
                    </tr>
                    <tr>    
                        <td style='text-align:right;'><label for='EBN'>EBN:&nbsp;</label></td>
                        <td><input type='text' disabled='disabled' name='EBN' value='<?php echo $Modernization['EBN'];?>' size='35' /></td>
                    </tr>
                    <tr>    
                        <td style='text-align:right;'><label for='Budget_Hours'>Budgeted Hours:&nbsp;</label></td>
                        <td><input type='text' disabled='disabled' name='Budget_Hours' value='<?php echo $Modernization['Budget_Hours'];?>' /></td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td></td>
                    </tr>
                </table></form>
            </div>
            <div class='col-md-3'>
                <form id='modernization_equipment'><input type='hidden' name='ID' value='<?php echo $_GET['ID'];?>' />
                    <table>
                        <tr><td>Equipment:</td><td><input type='text' name='Equipment' value='<?php echo $Modernization_Equipment['Equipment'];?>' /></td></tr>
                        <tr><td>Description:</td><td><textarea name='Description'><?php echo $Modernization_Equipment['Description'];?></textarea></td></tr>
                        <tr><td>Quantity:</td><td><input type='text' name='Quantity' value='<?php echo $Modernization_Equipment['Quantity'];?>' /></td></tr>
                        <tr><td>Version:</td><td><input type='text' name='Version' value='<?php echo $Modernization_Equipment['Version'];?>' /></td></tr>
                        <tr><td>In Care Of:</td><td><input type='text' name='In_Care_Of' value='<?php echo $Modernization_Equipment['In_Care_Of'];?>' /></td></tr>
                        <tr><td>Subcontractor:</td><td><input type='text' name='Subcontractor' value='<?php echo $Modernization_Equipment['Subcontractor'];?>' /></td></tr>
                        <tr><td>Submitted:</td><td><input type='text' name='Submitted' value='<?php echo strlen($Modernization_Equipment['Submitted']) > 0 ? substr($Modernization_Equipment['Submitted'],5,2). '/' . substr($Modernization_Equipment['Submitted'],8,2). '/'. substr($Modernization_Equipment['Submitted'],0,4) : null;?>' /></td></tr>
                        <tr><td>Purchased:</td><td><input type='text' name='Purchased' value='<?php echo strlen($Modernization_Equipment['Purchased']) > 0 ? substr($Modernization_Equipment['Purchased'],5,2). '/' . substr($Modernization_Equipment['Purchased'],8,2). '/'. substr($Modernization_Equipment['Purchased'],0,4) : null;?>' /></td></tr>
                        <tr><td>PO:</td><td><input type='text' name='PO' value='<?php echo $Modernization_Equipment['PO'];?>' /></td></tr>
                        <tr><td>Status:</td>
                            <td id='tdSupervisor'><select name='Status'>
                                <option value='In Engineering' <?php if($Modernization_Equipment['Status'] == 'In Engineering'){?>selected='selected'<?php }?>>In Engineering</option>
                                <option value='In Purchasing' <?php if($Modernization_Equipment['Status'] == 'In Purchasing'){?>selected='selected'<?php }?>>In Purchasing</option>
                                <option value='In Shop' <?php if($Modernization_Equipment['Status'] == 'In Shop'){?>selected='selected'<?php }?>>In Shop</option>
                                <option value='Delivered' <?php if($Modernization_Equipment['Status'] == 'Delivered'){?>selected='selected'<?php }?>>Delivered</option>
                                <option value='Installed' <?php if($Modernization_Equipment['Status'] == 'Installed'){?>selected='selected'<?php }?>>Installed</option>
                        </select></td>
                        </tr>
                        <tr><td>Drawings Received:</td><td><input type='text' name='Drawings_Received' value='<?php echo strlen($Modernization_Equipment['Drawings_Received']) > 0 ? substr($Modernization_Equipment['Drawings_Received'],5,2) . '/'. substr($Modernization_Equipment['Drawings_Received'],8,2). '/'. substr($Modernization_Equipment['Drawings_Received'],0,4) : null;?>' /></td></tr>
                        <tr><td>Drawings Reviewed:</td><td><input type='text' name='Drawings_Reviewed' value='<?php echo strlen($Modernization_Equipment['Drawings_Reviewed']) > 0 ? substr($Modernization_Equipment['Drawings_Reviewed'],5,2) . '/'. substr($Modernization_Equipment['Drawings_Reviewed'],8,2). '/'. substr($Modernization_Equipment['Drawings_Reviewed'],0,4) : null;?>' /></td></tr>
                        <tr><td>Warehoused:</td><td><input type='text' name='Warehoused' value='<?php echo strlen($Modernization_Equipment['Warehoused']) > 0 ? substr($Modernization_Equipment['Warehoused'],5,2) . '/'. substr($Modernization_Equipment['Warehoused'],8,2). '/'. substr($Modernization_Equipment['Warehoused'],0,4) : null;?>' /></td></tr>
                        <tr><td>Notes:</td><td><textarea name='Notes'><?php echo $Modernization_Equipment['Notes'];?></textarea></td></tr>
                    </table>
                </form>
                <button onClick='updateEquipment();'>Update</button>
                <button onClick='cloneEquipment();'>Clone</button>
            </div>
        </div>
        <hr />
        <div class='row'>
            <div class='col-md-12'>
                <h3 style='text-align:center;'>Correspondence</h3>
                <table id='Table_Modernization_Correspondence' class='display' cellspacing='0' width='100%'>
                    <thead>
                        <th>ID</th>
                        <th>Recipient</th>
                        <th>Sender</th>
                        <th>Notes</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th>Returned</th>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
        <Script>
            $(document).ready(function() {
                var Table_Modernization_Correspondence = $('#Table_Modernization_Correspondence').DataTable( {
                    "ajax": {
                        "url":"php/get/Modernization_Correspondence.php?ID=<?php echo $_GET['ID'];?>",
                        "dataSrc":function(json){
                            if(!json.data){json.data = [];}
                            return json.data;
                        } 
                    },
                    "columns": [
                        {
                            "data": "ID",
                            "className":"hidden"
                        },
                        { "data": "Recipient"},
                        { "data": "Sender" },
                        { "data": "Notes"},
                        { "data": "Status"},
                        { "data": "Submitted"},
                        { "data": "Returned"}
                    ],
                    "order": [[1, 'asc']],
                    "lengthMenu":[[10,25,50,100,500,-1,0],[10,25,50,100,500,"All","None"]],
                    "language":{"loadingRecords":""}, 
                    "initComplete":function(){}
                } );
                $("Table#Table_Modernization_Correspondence").on("draw.dt",function(){
                    $("tr.new").remove();
                    $("#Table_Modernization_Correspondence tbody").prepend("<tr class='new'><td colspan='6' style='background-color:#3d3d3d;color:white;text-align:center;' onClick='popupAddCorrespondence();'>Add New Correspondence</td></tr>");
                    $("tr[role='row']").on("dblclick",function(){editRow(this);});
                });
                $("Table#Table_Modernization_Correspondence").on("click","tr",function(){
                    $(".selected").toggleClass("selected");
                    $(this).toggleClass("selected");
                });
            });
        </Script>
        <?php 
    }
}?>