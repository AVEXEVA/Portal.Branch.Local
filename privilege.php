<?php
session_start( [ 'read_and_close' => true ] );
require('bin/php/index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = $database->query(null,"
        SELECT *
        FROM   Connection
        WHERE  Connection.Connector = ?
               AND Connection.Hash  = ?
    ;",array($_SESSION['User'],$_SESSION['Hash']));
    $My_Connection = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    $r = $database->query(null,"
        SELECT *,
               Emp.fFirst AS First_Name,
               Emp.Last   AS Last_Name
        FROM   Emp
        WHERE  Emp.ID = ?
    ;",array($_SESSION['User']));
    $My_User = sqlsrv_fetch_array($r);
    $r = $database->query(null,"
        SELECT *
        FROM   Privilege
        WHERE  Privilege.User_ID = ?
    ;",array($_SESSION['User']));
    $My_Privileges = array();
    if($r){while($My_Privilege = sqlsrv_fetch_array($r)){$My_Privileges[$My_Privilege['Access_Table']] = $My_Privilege;}}
    if( !isset($My_Connection['ID'])
        || !(isset($_GET['User_ID']) || isset($_POST['User_ID']))
        || !isset($My_Privileges['Admin'])
            || $My_Privileges['Admin']['User_Privilege']  < 4
            || $My_Privileges['Admin']['Group_Privilege'] < 4
            || $My_Privileges['Admin']['Other_Privilege'] < 4){
                ?><?php require('../404.html');?><?php }
    else {
        $database->query(null,"
            INSERT INTO Portal.dbo.Activity([User], [Date], [Page])
            VALUES(?,?,?)
        ;",array($_SESSION['User'],date("Y-m-d H:i:s"), "privilege.php?User_ID=" . $_GET['User_ID']));
$Selected_User_ID = isset($_GET['User_ID']) ? $_GET['User_ID'] : $_POST['User_ID'];
if(isset($_POST['User_ID'])){
    if(isset($_POST['Type']) && $_POST['Type'] == 'Update'){
        $r = $database->query($Portal,"
            UPDATE Portal
            SET
                Email = ?,
                Password = ?
            WHERE Branch_ID = ?
                  AND Branch='Nouveau Texas';
        ",array($_POST['Email'],$_POST['Password'],$_POST['User_ID']));
    } elseif(isset($_POST['Type']) && $_POST['Type'] == 'Insert'){
        $r = $database->query($Portal,"
            INSERT INTO Portal(Email, Password, Verified, Branch, Branch_ID)
            VALUES(?,?,1,'Nouveau Texas',?);
        ",array($_POST['Email'],$_POST['Password'],$_POST['User_ID']));
    }
    $_GET['User_ID'] = $_POST['User_ID'];
}
$r = $database->query(null,"SELECT * FROM Emp WHERE ID='{$Selected_User_ID}'");
$Selected_User = sqlsrv_fetch_array($r);
?><!DOCTYPE html>
<html lang="en">
<head>
    <?php require( bin_meta . 'index.php');?>
    <title>Nouveau Texas | Portal</title>
    <?php require( bin_css . 'index.php');?>
    <?php require( bin_js . 'index.php');?>
</head>
<body onload=''>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require(PROJECT_ROOT.'php/element/navigation/index.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id="page-wrapper" class='content'>
            <div class="row" onClick='document.location.href="privileges.php";'>
                <div class="col-lg-12">
                    <h1 class="page-header"><?PHP echo proper($Selected_User['fFirst'] . " " . $Selected_User['Last']);?> Security Privileges</h1>
                </div>
            </div>
            <div class="row">
                <div class='col-md-4'>
                    <div class='panel panel-red'>
                        <div class='panel-heading'>User Details</div>
                        <div class='panel-body'>
                            <div class='row'>
                                <div class='col-xs-3'><b>Title:</b></div>
                                <div class='col-xs-9'><?php echo $Selected_User['Title'];?></div>
                            </div>
                            <div class='row'>
                                <div class='col-xs-3'><b>Field:</b></div>
                                <div class='col-xs-9'><?php echo ($Selected_User['Field'] == 1) ? "Enabled" : "Disabled";?></div>
                            </div>
                            <div class='row'>
                                <div class='col-xs-3'><b>SSN:</b></div>
                                <div class='col-xs-9'><?php echo substr($Selected_User['SSN'],-4,4);?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class='col-md-4'>
                    <div class='panel panel-blue'>
                        <div class='panel-heading'>
                            Account Settings
                        </div>
                        <div class='panel-body'>
                            <?php
                                $r = $database->query($Portal,"
                                    SELECT *
                                    FROM Portal
                                    WHERE
                                        Portal.Branch_ID = ?
                                        AND Portal.Branch = 'Nouveau Texas'
                                ",array($_GET['User_ID']));
                                if($r){$data = sqlsrv_fetch_array($r);}
                                if(!is_null($data)){?>
                                    <div>Account Available</div>
                                    <div>
                                        <form action="privilege.php" method="POST">
                                            <table>
                                                <input type='hidden' name='User_ID' value='<?php echo isset($_GET['User_ID']) ? $_GET['User_ID'] : $_POST['User_ID'];?>' />
                                                <input type='hidden' name='Type' value='Update' />
                                                <tr><td>Email</td><td><input type='Email' name='Email' value="<?php echo $data['Email'];?>" /></td></tr>
                                                <tr><td>Password</td><td><input type='Password' name='Password' value="<?php echo $data['Password'];?>" /></td></tr>
                                                <tr><td><input type='submit' value='Update User'></td></tr>
                                            </table>
                                        </form>
                                    </div>
                                <?php } else {?>
                                    <div>No Account Available.</div>
                                    <div>
                                        <form action="privilege.php" method="POST">
                                            <table>
                                                <input type='hidden' name='User_ID' value='<?php echo $_GET['User_ID'];?>' />
                                                <input type='hidden' name='Type' value='Insert' />
                                                <tr><td>Email</td><td><input type='Email' name='Email' /></td></tr>
                                                <tr><td>Password</td><td><input type='Password' name='Password' /></td></tr>
                                                <tr><td><button onClick="submitNewUser();">Make User</button></td></tr>
                                            </table>
                                        </form>
                                    </div><?php
                                }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class='row'>
                <div class='col-lg-12'>&nbsp;</div>
                <div class='col-lg-12'><form id="grantPrivileges" action="bin/php/post/grantPrivileges.php">
                    GRANT <?php echo proper($Selected_User['fFirst'] . " " . $Selected_User['Last']);?> TABLE
                    <select name='Access_Table'>
                    <?php $r = $database->query($Portal,"SELECT Privileges.Access_Table FROM   Privilege;");
                    $Access_Tables = array();
                    while($array = sqlsrv_fetch_array($r)){$Access_Tables[] = $array['Access_Table'];}
                    $Access_Tables = array_unique($Access_Tables);
                    foreach($Access_Tables as $Table){?><option value='<?php echo $Table;?>'><?php echo $Table;?></option><?php }?>
                    </select>
                    &nbsp; User <select name="User_Privilege"><?php for($i = 0; $i <= 7; $i++){?><option value="<?php echo $i;?>"><?php echo $i;?></option><?php }?></select>
                    &nbsp; Group <select name="Group_Privilege"><?php for($i = 0; $i <= 7; $i++){?><option value="<?php echo $i;?>"><?php echo $i;?></option><?php }?></select>
                    &nbsp; Other <select name="Other_Privilege"><?php for($i = 0; $i <= 7; $i++){?><option value="<?php echo $i;?>"><?php echo $i;?></option><?php }?></select>
                    <button onClick="grantPrivileges();" type='button' style='color:black;'>Grant Privileges</button>
                </form></div>
                <script>
                function grantPrivileges(){
                    var form = $("form#grantPrivileges");
                    var url = form.attr("action");
                    var formData = {};
                    $(form).find("select[name]").each(function (index, node) {
                        formData[node.name] = node.value;
                    });
                    formData['User_ID'] = <?php echo strlen($_GET['ID']) > 0 ? $_GET['ID'] : "''";?>;
                    $.post(url, formData).done(function (data) {});
                }
                </script>
                <div class='col-lg-12'>&nbsp;</div>
                <div class='col-lg-12' style='color: black !important;'>
                    <button onClick="grantBeta();" type='button'>Grant Beta Access</button> 
                    <button onClick="grantSalesAdmin();" type='button'>Grant Beta Access</button>
                    <button onClick="grantField();" type='button'>Grant Field Access</button>
                    <button onClick="grantDispatch();" type='button'>Grant Dispatch Access</button>
                    <button onClick="grantOffice();" type='button'>Grant Office Access</button>
                    <button onClick="grantSalesAdmin();" type='button'>Grant Sales Admin Access</button>
                    <button onClick="grantModernization();" type='button'>Grant Modernization Access</button>
                    <button onClick="grantMaintenance();" type='button'>Grant Maintenance Access</button>
                    <button onClick="grantRepair();" type='button'>Grant Repair Access</button>
                    <button onClick="grantTesting();" type='button'>Grant Testing Access</button>
                    <button onClick="grantPurchasing();" type='button'>Grant Purchasing Access</button>
                    <button onClick="grantSurveySheet();" type='button'>Grant Survey Sheet Access</button>
                    <button onClick="grantAdmin();" type='button'>Grant Admin Access</button>
                    <button onClick="grantFinances();" type='button'>Grant Finances Access</button>
                    <button onClick="grantRequisition();" type='button'>Grant Requisition Access</button>
                </div>
                <div class='col-lg-12'>&nbsp;</div>
                <div class='col-lg-12'>
                    <button onClick="removePrivileges();" type='button' style='color:black;'>Remove All Access</button>
                </div>
                <div class='col-lg-12'>&nbsp;</div>
                <script>
                function alterUserPrivileges(post_url){
                    $.ajax({
                        url:post_url,
                        data:'User_ID=<?php echo $Selected_User['ID'];?>',
                        type:"POST",
                        success:function(code){document.location.href='privilege.php?User_ID=<?php echo $Selected_User['ID'];?>';}
                    });
                }
                function grantBeta(){alterUserPrivileges("bin/php/post/grantBeta.php");}
                function grantSalesAdmin(){alterUserPrivileges("bin/php/post/grantSalesAdmin.php");}
                function grantModernization(){alterUserPrivileges("bin/php/post/grantModernization.php",);}
                function grantMaintenance(){alterUserPrivileges("bin/php/post/grantMaintenance.php",);}
                function grantRepair(){alterUserPrivileges("bin/php/post/grantRepair.php",);}
                function grantTesting(){alterUserPrivileges("bin/php/post/grantTesting.php",);}
                function grantAdmin(){alterUserPrivileges("bin/php/post/grantAdmin.php",);}
                function grantSurveySheet(){alterUserPrivileges("bin/php/post/grantSurveySheet.php",);}
                function grantField(){alterUserPrivileges("bin/php/post/grantField.php");}
                function grantDispatch(){alterUserPrivileges("bin/php/post/grantDispatch.php");}
                function grantOffice(){alterUserPrivileges("bin/php/post/grantOffice.php");}
                function removePrivileges(){alterUserPrivileges("bin/php/post/removePrivileges.php");}
                function grantFinances(){alterUserPrivileges("bin/php/post/grantFinances.php");}
                function grantPurchasing(){alterUserPrivileges("bin/php/post/grantPurchasing.php");}
                function grantRequisition(){alterUserPrivileges("bin/php/post/grantRequisition.php");}
                </script>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">Employees</div>
                        <div class="panel-body">
                            <table id='Privileges_Table' class='display' cellspacing='0' width='100%'>
                                <thead>
                                    <th title="Access Table">Access Table</th>
                                    <th title="User Privilege">User Privilege</th>
                                    <th title="Group Privilege">Group Privilege</th>
                                    <th title="Other Privilege">Other Privilege</th>
                                </thead>
                               <tfooter>
                                    <th title="Access Table">Access Table</th>
                                    <th title="User Privilege">User Privilege</th>
                                    <th title="Group Privilege">Group Privilege</th>
                                    <th title="Other Privilege">Other Privilege</th>
                                </tfooter>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    
    <?php require(PROJECT_ROOT.'js/datatables.php');?>
    
    
    <script>
        $(document).ready(function() {
            var table = $('#Privileges_Table').DataTable( {
                "ajax": {
                    "url":"bin/php/get/Privilege.php?ID=<?php echo $Selected_User_ID;?>",
                    "dataSrc":function(json){if(!json.data){json.data = [];}return json.data;}
                },
                "columns": [
                    { "data": "Access_Table"},
                    { "data": "User_Privilege"},
                    { "data": "Group_Privilege"},
                    { "data": "Other_Privilege"}
                ],
                "order": [[1, 'asc']],
                "language":{"loadingRecords":""},
                "initComplete":function(){finishLoadingPage();}
            } );
        } );
    </script>
</body>
</html>
 <?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=profile.php';</script></head></html><?php }?>
