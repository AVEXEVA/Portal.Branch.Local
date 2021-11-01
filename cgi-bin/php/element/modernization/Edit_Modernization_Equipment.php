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

        $r = sqlsrv_query($Portal,"
                SELECT * 
                FROM Mod_Equipment 
                WHERE Mod_Equipment.ID = '{$_GET['ID']}'
            ;");
        $Equipment = sqlsrv_fetch_array($r);
        ?>
        <div class='popup' style='position:absolute;z-index:999;width:100%;height:100%;background-color:rgba(0,0,0,.5);top:0;left:0;' rel='add_modernization_equipment'>
            <div style='position:absolute;z-index:9999;background-color:white;left:450px;top:100px;'>
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-primary">
                        <div class='panel-heading' style='background-color:black;'><h3>Track New Modernization<span onClick="removePopup('add_modernization_equipment');" style='float:right;'>X</span></h3></div>
                        <div class='panel-body'>
                            <form action="modernization_tracker.php" method="POST" id='Add_Modernization_Equipment' enctype="multipart/form-data" > 
                                <input type='hidden' name='ID' value='<?php echo $_GET['ID'];?>' />
                                <table>
                                    <tr>
                                        <td>Equipment:</td>
                                        <td><input type='text' name='Equipment' placeholder='Equipment' value='<?php echo $Equipment['Equipment'];?>' /></td></tr>
                                    <tr><td>Quantity</td>
                                        <td><input type='text' name='Quantity' placeholder='Quantity' value='<?php echo $Equipment['Quantity'];?>'  /></td></tr>
                                    <tr><td>Submitted</td>
                                        <td><input type='text' name='Submitted' placeholder='Submitted' value='<?php echo $Equipment['Submitted'];?>'' /></td></tr>
                                    <tr><td>Purchased</td>
                                        <td><input type='text' name='Purchased' placeholder='Purchased' value='<?php echo $Equipment['Purchased'];?>' /></td></tr>.
                                    <tr><td>Status</td>
                                        <td><input type='text' name='Status' placeholder='Status' value='<?php echo $Equipment['Status'];?>' /></td></tr>
                                    <tr><td>PO</td>
                                        <td><input type='text' name='PO' placeholder='PO'  value='<?php echo $Equipment['PO'];?>' /></td></tr>
                                    <tr><td>Drawings Received</td>
                                        <td><input type='text' name='Drawings_Received' value='<?php echo $Equipment['Drawings_Received'];?>' /></td></tr>
                                    <tr><td>Drawings Reviewed</td>
                                        <td><input type='text' name='Drawings_Reviewed' value='<?php echo $Equipment['Drawings_Reviewed'];?>' /></td></tr>
                                    <tr><td>Description:</td>
                                        <td><textarea cols='40' rows='5' name='Description' value='<?php echo $Equipment['In_Care_Of'];?>' ></textarea></td></tr>
                                    <tr><td>In Care Of:</td>
                                        <td><input type='text' name='In_Care_Of' placeholder='In Care Of' value='<?php echo $Equipment['In_Care_Of'];?>' /></td></tr>
                                    <tr><td>Subcontractor:</td>
                                        <td><input type='text' name='Subcontractor' placeholder='Subcontractor' value='<?php echo $Equipment['Subcontractor'];?>' /></td></tr>
                                    <tr><td>Notes:</td>
                                        <td><textarea cols='40' name='Notes' rows='5''></textarea></td></tr>
                                    <tr><td>Warehoused:</td>
                                        <td><input type='text' name='Warehoused' /></td></tr>
                                </table>
                            </form>
                            <Button onClick="addModernizationEquipment();">Add</Button>
                        </div>
                        <script>
                        $(document).ready(function(){
                            $("input[name='Drawings_Received']").datepicker({
                                onSelect:function(dateText, inst){
                                    
                                }
                            });
                            $("input[name='Drawings_Reviewed']").datepicker({
                                onSelect:function(dateText, inst){
                                    
                                }

                            });
                            $("input[name='Submitted']").datepicker({
                                onSelect:function(dateText, inst){
                                    
                                }
                            });
                            $("input[name='Purchased']").datepicker({
                                onSelect:function(dateText, inst){
                                    
                                }
                            });
                            $("input[name='Warehoused']").datepicker({
                                onSelect:function(dateText, inst){
                                    
                                }
                            });
                        });
                        function ModernizationEquipment(){
                            var formdata = $("form#Add_Modernization_Equipment").serialize();
                            $.ajax({
                                url:"cgi-bin/php/post/editModernizationEquipment.php",
                                method:"POST",
                                data:formdata,
                                success:function(code){
                                    alert(code);
                                }
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