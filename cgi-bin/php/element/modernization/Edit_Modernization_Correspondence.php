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
    if(!$Privileged || !is_numeric($_GET['ID'])){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
        $r = sqlsrv_query($Portal,"
            SELECT *
            FROM Mod_Correspondence 
            WHERE Mod_Correspondence.ID = ?
        ;",array($_GET['ID']));
        if($r){$Correspondence = sqlsrv_fetch_array($r);}
        if($Correspondence['Submitted'] == '1900-01-01 00:00:00.000'){$Correspondence['Submitted'] = '';}
        elseif(strlen($Correspondence['Submitted']) > 0){$Correspondence['Submitted'] = substr($Correspondence['Submitted'],5,2) . '/' . substr($Correspondence['Submitted'],8,2) . '/' . substr($Correspondence['Submitted'],0,4);}
        if($Correspondence['Returned'] == '1900-01-01 00:00:00.000'){$Correspondence['Returned'] = '';}
        elseif(strlen($Correspondence['Returned']) > 0){$Correspondence['Returned'] = substr($Correspondence['Returned'],5,2) . '/' . substr($Correspondence['Returned'],8,2) . '/' . substr($Correspondence['Returned'],0,4);}
        ?>
        <div class='popup' style='position:static;z-index:999;width:100%;height:100%;background-color:rgba(0,0,0,.5);top:0;left:0;' rel='Update_Modernization_Equipment_Correspondence'>
            <div style='position:absolute;z-index:9999;background-color:white;left:450px;top:100px;'>
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-primary">
                        <div class='panel-heading' style='background-color:black;'><h3>Track Correspondence<span onClick="removePopup('Update_Modernization_Equipment_Correspondence');" style='float:right;'>X</span></h3></div>
                        <div class='panel-body'>
                            <form action="modernization_tracker.php" method="POST" id='Edit_Modernization_Equipment_Correspondence' enctype="multipart/form-data" > 
                                <input type='hidden' name='ID' value='<?php echo $_GET['ID'];?>' />
                                <table>
                                    <tr>
                                        <td>Recipient:</td>
                                        <td><input type='text' name='Recipient' placeholder='Recipient' value="<?php echo $Correspondence['Recipient'];?>" /></td></tr>
                                    <tr><td>Sender</td>
                                        <td><input type='text' name='Sender' placeholder='Sender' value="<?php echo $Correspondence['Sender'];?>" /></td></tr>
                                    <tr><td>Notes</td>
                                        <td><textarea name='Notes'><?php echo $Correspondence['Notes'];?></textarea></td></tr>
                                    <tr><td>Status</td>
                                        <td><select name='Status'>
                                            <option value='Sent' <?php if($Correspondence['Sent'] == ''){?>selected='selected'<?php }?>>Sent</option>
                                            <option value='Returned' <?php if($Correspondence['Returned'] == ''){?>selected='selected'<?php }?>>Returned</option>
                                            <option value='Reviewed' <?php if($Correspondence['Reviewed'] == ''){?>selected='selected'<?php }?>>Reviewed</option>
                                        </select></td></tr>
                                    <tr><td>Submitted</td>
                                        <td><input type='text' name='Submitted' value="<?php echo $Correspondence['Submitted'];?>" /></td></tr>
                                    <tr><td>Returned</td>
                                        <td><input type='text' name='Returned' value="<?php echo $Correspondence['Returned'];?>" /></td></tr>
                                </table>
                            </form>
                            <Button onClick="updateModernizationEquipmentCorrespondence();">Update</Button>
                        </div>
                        <script>
                        $(document).ready(function(){
                            $("input[name='Submitted']").datepicker({
                                onSelect:function(dateText, inst){
                                    
                                }
                            });
                            $("input[name='Returned']").datepicker({
                                onSelect:function(dateText, inst){
                                    
                                }
                            });
                        });
                        function updateModernizationEquipmentCorrespondence(){
                            var formdata = $("form#Edit_Modernization_Equipment_Correspondence").serialize();
                            $.ajax({
                                url:"cgi-bin/php/post/updateModernizationEquipmentCorrespondence.php?ID=<?php echo $_GET['ID'];?>",
                                method:"POST",
                                data:formdata,
                                success:function(code){
                                    $(".popup").remove();
                                    $.ajax({
                                        url:"cgi-bin/php/element/modernization/modernization_equipment.php?ID=<?php echo $_GET['Ref'];?>",
                                        method:"GET",
                                        success:function(code){
                                            modernizationTracker(null,code);;
                                        }
                                    });
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