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
    else {?>
        <div class='popup' style='position:absolute;z-index:999;width:100%;height:100%;background-color:rgba(0,0,0,.5);top:0;left:0;' rel='add_modernization_equipment'>
            <div style='position:absolute;z-index:9999;background-color:white;left:450px;top:100px;min-width:300px;'>
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-primary">
                        <div class='panel-heading' style='background-color:black;'><h3>Track New Task<span onClick="removePopup('add_modernization_equipment');" style='float:right;'>X</span></h3></div>
                        <div class='panel-body'>
                            <form action="modernization_tracker.php" method="POST" id='Add_Modernization_Task' enctype="multipart/form-data" > 
                                <input type='hidden' name='Modernization' value='<?php echo $_GET['ID'];?>' />
                                <table>
                                    <tr>
                                        <td>Task:</td>
                                        <td><select name='Task'>
                                            <?php $r = sqlsrv_query($NEI,"SELECT * FROM Portal.dbo.Tasks ORDER BY Tasks.Name ASC;");
		  									if($r){while($Task = sqlsrv_fetch_array($r)){?><option value='<?php echo $Task['ID'];?>'><?php echo $Task['Name'];?></option><?php }}?>
                                        </select></td></tr>
                                    <tr><td>Status</td>
                                        <td><select name='Status'>
                                            <option value="0%">0%</option>
											<option value="25%">25%</option>
											<option value="50%">50%</option>
											<option value="75%">75%</option>
											<option value="Complete">Complete</option>
                                        </select></td></tr>
                                </table>
                            </form>
                            <Button onClick="addModernizationTask();">Add</Button>
                        </div>
                        <script>
                        function addModernizationTask(){
                            var formdata = $("form#Add_Modernization_Task").serialize();
                            $.ajax({
                                url:"cgi-bin/php/post/addModernizationTask.php?ID=<?php echo $_GET['ID'];?>",
                                method:"POST",
                                data:formdata,
                                success:function(code){
                                    $(".popup").remove();
                                    $.ajax({
                                        url:"cgi-bin/php/element/modernization/modernization_tracker.php?ID=<?php echo $_GET['ID'];?>",
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