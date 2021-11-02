<?php 
//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\
//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\
//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\
session_start();
require('cgi-bin/php/index.php');
$serverName = "172.16.12.45";
$connectionOptions = array(
    "Database" => "nei",
    "Uid" => "sa",
    "PWD" => "SQLABC!23456",
    'ReturnDatesAsStrings'=>true
);
//Establishes the connection
$conn = sqlsrv_connect($serverName, $connectionOptions);
$connectionOptions['Database'] = 'Portal';
$conn2 = sqlsrv_connect($serverName, $connectionOptions);
$connectionOptions['Database'] = 'Portal';
$Portal = sqlsrv_connect($serverName, $connectionOptions);
if(isset($_SESSION['User'],$_SESSION['Hash'])){

    $r = sqlsrv_query($conn,"SELECT * FROM Connection WHERE Connector = ? AND Hash= ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    $User = sqlsrv_query($conn,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID= ?",array($_SESSION['User']));
    $User = sqlsrv_fetch_array($User);
    $Field = ($User['Field'] == 1 && $User['Title'] != "OFFICE") ? True : False;
    $r = sqlsrv_query($conn2,"
            SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
            FROM   Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
    $My_Privileges = array();
    while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
    $Privileged = FALSE;
    if(isset($My_Privileges['Admin']) && $My_Privileges['Admin']['User_Privilege'] >= 7 && $My_Privileges['Admin']['Group_Privilege'] >= 7 && $My_Privileges['Admin']['Other_Privilege'] >= 7){$Privileged = TRUE;}
    sqlsrv_query($conn2,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "bugs.php"));
    if(!isset($array['ID'])  || !$Privileged){?><html><head><script>document.location.href='../login.php?Forward=profile.php';</script></head></html><?php }
    else {
//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\
//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\
//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\
if(count($_POST) > 0 && is_numeric($_POST['Severity']) && strlen($_POST['Name']) > 0 && strlen($_POST['Description']) > 0){
$Name        = $_POST['Name'];
$Severity    = $_POST['Severity'];
$Description = $_POST['Description'];
$Suggestion  = $_POST['Suggestion'];
$Parameters  = array($Name,$Severity,$Description,$Suggestion);
$r = sqlsrv_query($Portal,"
    INSERT INTO Bug(Name, Severity, Description, Suggestion)
    VALUES(?,?,?,?)
;",$Parameters);
}
//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\
//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\
//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\
?><!DOCTYPE html>
<html lang="en">
<head>
    <?php require(PROJECT_ROOT.'php/meta.php');?>
    <title>Nouveau Texas | Portal</title>    <?php require(PROJECT_ROOT."css/index.php");?>
    <?php require(PROJECT_ROOT.'js/index.php');?>
</head>
<body onload='finishLoadingPage();'>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require(PROJECT_ROOT.'php/element/navigation/index2.php');?>
        <?php require(PROJECT_ROOT.'php/element/loading.php');?>
        <div id="page-wrapper" class='content'>
            <div class='row'>
                <div class="col-lg-12">
                    <div class="panel panel-primary">
                        <div class="panel-heading"><h2>Bugs</h2></div>
                        <div class='panel-body'>
                            <ul class="nav nav-tabs">
                                <li class="active"><a href="#bugs-pills" data-toggle="tab"><?php $Icons->Info();?> Bugs</a></li>
                                <li class=""><a href="#add-bug-pills" data-toggle="tab"><?php $Icons->Financial();?> Add Bugs</a></li>
                            </ul>
                            <br />
                            <div class="tab-content">
                                <div class="tab-pane fade in active" id="bugs-pills">
                                    <?php $r = sqlsrv_query($Portal,"
                                        SELECT 
                                            Bug.ID, 
                                            Bug.Name, 
                                            Bug.Description, 
                                            Bug.Resolution, 
                                            Bug.Fixed, 
                                            Bug.Suggestion, 
                                            Severity.Name AS Severity 
                                        FROM Bug LEFT JOIN Severity ON Bug.Severity = Severity.ID
                                    ;");
                                    if($r){while($Bug = sqlsrv_fetch_array($r)){?>
                                    <div class='row'>
                                        <div class='col-md-2'>Name:</div><div class='col-md-10'><?php echo $Bug['Name'];?></div>
                                        <div class='col-md-2'>Severity:</div><div class='col-md-10'><?php echo $Bug['Severity'];?></div>
                                        <div class='col-md-2'>Description:</div><div class='col-md-10'><?php echo $Bug['Description'];?></div>
                                        <div class='col-md-2'>Suggestion:</div><div class='col-md-10'><?php echo strlen($Bug['Suggestion']) > 0 ? $Bug['Suggestion'] : '&nbsp;';?></div>
                                        <div class='col-md-2'>Resolution:</div><div class='col-md-10'><?php echo strlen($Bug['Resolution']) > 0 ? $Bug['Resolution'] : '&nbsp;';?></div>
                                        <div class='col-md-2'>Fixed:</div><div class='col-md-10'><?php echo strlen($Bug['Fixed']) > 0 ? $Bug['Fixed'] : '&nbsp;';?></div>
                                    </div><hr /><?php }}?>
                                </div>
                                <div class='tab-pane fade in' id='add-bug-pills'>
                                    <form action="bugs.php" method='POST'>
                                        <div class='input-group'><label for='Name' class=''>Name&nbsp;</label><input class='form-control' name="Name" type="text" /></div>
                                        <div class='input-group'>
                                            <label for='Severity' class=''>Severity&nbsp;</label>
                                            <select name='Severity' class='form-control'><?php 
                                                $r = sqlsrv_query($Portal,"SELECT * FROM Severity;");
                                                if($r){while($Severity = sqlsrv_fetch_array($r)){?><option value='<?php echo $Severity['ID'];?>'><?php echo $Severity['Name'];?></option><?php }}
                                            ?></select>
                                        </div>
                                        <div class='input-group'><label for='Description' class=''>Description&nbsp;</label><textarea class='form-control' name='Description' cols='60' rows='5'></textarea></div>

                                        <div class='input-group'><label for='Suggestion' class=''>Suggestion&nbsp;</label><textarea class='form-control' name='Suggestion' cols='60' rows='5'></textarea></div>
                                        <hr />
                                        <div class='input-group'><input type='submit' value='Submit Bug' class='form-control' /></div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://www.nouveauelevator.com/vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/metisMenu/metisMenu.js"></script>
    <?php require(PROJECT_ROOT.'js/datatables.php');?>
    <script src="../dist/js/sb-admin-2.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
</body>
</html>
 <?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=profile.php';</script></head></html><?php }?>