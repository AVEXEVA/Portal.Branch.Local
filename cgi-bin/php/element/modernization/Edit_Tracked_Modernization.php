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
    if(!$Privileged){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
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
                    Modernization.Job = {$_GET['Job']}
                    AND Elev.State = '{$_GET['Unit']}'
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

            //var_dump($array);
        }
        ?>
        <div class='popup' style='position:absolute;z-index:999;width:100%;height:100%;background-color:rgba(0,0,0,.5);top:0;left:0;' rel='editTrackedModernization'>
        <?php //var_dump($Modernization);?>
            <div style='position:absolute;z-index:9999;background-color:white;left:450px;top:100px;'>
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-primary">
                        <div class='panel-heading' style='background-color:black;'><h3>Track New Modernization<span onClick="removePopup('editTrackedModernization');" style='float:right;'>X</span></h3></div>
                        <div class='panel-body'>
                            <form action="modernization_tracker.php" method="POST"> 
                                <table>
                                    <tr>
                                        <td style='text-align:right;'><label for='Location'>Location:&nbsp;</label></td>
                                        <td><input type='text' disabled='disabled' value='<?php echo $Modernization['Location'];?>' size='100' />
                                    </tr>
                                    <tr>
                                        <td style='text-align:right;'><label for='Job'>Job:&nbsp;</label></td>
                                        <td id='tdJob'><input type='text' disabled='disabled' value='<?php echo $Modernization['Job'] . " :: " . $Modernization['Name']?>' size='100' /><input type='hidden' name='Job' value='<?php echo $Modernization['Job'];?>' /></td>
                                    </tr>
                                    <tr>
                                        <td style='text-align:right;'><label for='Unit'>Unit:&nbsp;</label></td>
                                        <td id='tdUnit'><input type='text' disabled='disabled' value='<?php echo $Modernization['Unit'];?>' size='100' /><input type='hidden' value='<?php echo $Modernization['Unit_ID'];?>' name='Unit' </td>
                                    </tr>
                                    <tr>
                                        <td style='text-align:right;'><label for='Status'>Status:&nbsp;</label></td>
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
                                        <td style='text-align:right;'><label for='Supervisor'>Supervisor:&nbsp;</label></td>
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
                                        <td style='text-align:right;'><label for='Removed'>Removed:&nbsp;</label></td>
                                        <td><input type='text' name='Removed' value='<?php echo ("1900-01-01" == $Modernization['Removed'] || $Modernization['Removed'] == '') ? '' : DateTime::createFromFormat('Y-m-d',$Modernization['Removed'])->format("m/d/Y");?>' size='100' /></td>
                                    </tr>
                                    <tr>    
                                        <td style='text-align:right;'><label for='Returned'>Returned:&nbsp;</label></td>
                                        <td><input type='text' name='Returned' value='<?php echo ("1900-01-01" == $Modernization['Returned'] || $Modernization['Returned'] == '') ? '' :DateTime::createFromFormat('Y-m-d',$Modernization['Returned'])->format("m/d/Y");?>' size='100' /></td>
                                    </tr>
                                    <tr>    
                                        <td style='text-align:right;'><label for='EBN'>EBN:&nbsp;</label></td>
                                        <td><input type='text' name='EBN' value='<?php echo $Modernization['EBN'];?>' size='100' /></td>
                                    </tr>
                                    <tr>    
                                        <td style='text-align:right;'><label for='Budget_Hours'>Budgeted Hours:&nbsp;</label></td>
                                        <td><input type='text' name='Budget_Hours' value='<?php echo $Modernization['Budget_Hours'];?>' /></td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td><input type='submit' value='Update' /></td>
                                    </tr>
                                </table>
                            </form>
                        </div>
                        <script>
                        $(document).ready(function(){
                            $("input[name='Removed']").datepicker({
                                onSelect:function(dateText, inst){
                                    
                                }
                            });
                            $("input[name='Returned']").datepicker({
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
                    </div>
                </div>
            </div></div>
        </div>
        <?php 
    }
}?>