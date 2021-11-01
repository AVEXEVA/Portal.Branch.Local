<?php 
session_start();
require('../../../php/index.php');
setlocale(LC_MONETARY, 'en_US');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Elevator'){
        $r= sqlsrv_query($NEI,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID= ?",array($_SESSION['User']));
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
        if(isset($My_Privileges['Invoice']) && $My_Privileges['Invoice']['User_Privilege'] >= 6 && $My_Privileges['Invoice']['Group_Privilege'] >= 4 && $My_Privileges['Invoice']['Other_Privilege'] >= 4){$Privileged = TRUE;}
        else {
            //NEEDS TO INCLUDE SECURITY FOR OTHER PRIVILEGE
        }
    } elseif($_SESSION['Branch'] == 'Customer' && is_numeric($_GET['ID'])){
            $r =  sqlsrv_query( $NEI,"SELECT Ref FROM nei.dbo.Invoice LEFT JOIN nei.dbo.Loc ON Invoice.Loc = Loc.Loc WHERE Invoice.Ref='{$_GET['ID']}' AND Loc.Owner = '{$_SESSION['Branch_ID']}';");
            $Privileged = $r ? TRUE : FALSE;
    }
    sqlsrv_query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "invoice.php"));
    if(!isset($array['ID']) || !$Privileged){?><html><head><script></script></head></html><?php }
    else {
?>
    <script>
		$(document).ready(function(){
			<?php
		  	$r = sqlsrv_query($NEI,"
				SELECT OpenAR.Ref
				FROM   nei.dbo.OpenAR
				       LEFT JOIN nei.dbo.Invoice ON OpenAR.Ref           = Invoice.Ref
					   LEFT JOIN nei.dbo.Job ON Job.ID                   = Invoice.Job
					   LEFT JOIN nei.dbo.Loc ON Job.Loc                  = Loc.Loc
					   LEFT JOIN nei.dbo.OwnerWithRol ON OwnerWithRol.ID = Loc.Owner
					   LEFT JOIN nei.dbo.Terr ON Terr.ID = Loc.Terr
				WHERE  Terr.Name = 'Robert Speranza'
				       AND Loc.Custom1 = 'Collector 5'
					   AND OpenAR.fDate < '2018-02-15 00:00:00.000'
			;",array('Robert Speranza'));
		  	if($r){while($array = sqlsrv_fetch_array($r)){?>
			$.ajax({
				url:"short-invoice.php?ID=<?php echo $array['Ref'];?>",
				method:"GET",
				success:function(code){$("div.content").append(code);}
			});
			<?php }}?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php require(PROJECT_ROOT.'php/meta.php');?>    
    <title>Nouveau Elevator Portal</title>    
    <?php require(PROJECT_ROOT."css/index.php");?>
    <?php require(PROJECT_ROOT.'js/index.php');?>
</head>
<body onload='finishLoadingPage();'>
    <div id="wrapper" style='overflow:auto !important;' class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require(PROJECT_ROOT.'php/element/navigation/index2.php');?>
        <?php require(PROJECT_ROOT.'php/element/loading.php');?>
        <div id="page-wrapper" class='content' style='overflow:auto !important;<?php if(isset($_SESSION['Branch']) && $_SESSION['Branch'] == 'Customer'){?>margin:0px !important;<?php }?>'>
        </div>
    </div>
    <script src="../vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="../vendor/metisMenu/metisMenu.js"></script>
    <?php require('cgi-bin/js/datatables.php');?>
    <script src="../dist/js/sb-admin-2.js"></script>
    <script src="../dist/js/moment.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

    <!-- Custom Date Filters-->
    <script src="../dist/js/filters.js"></script>

    <!-- Morris Charts JavaScript -->
    <script src="../vendor/raphael/raphael.min.js"></script>
    <script src="../vendor/morrisjs/morris.min.js"></script>
		});
	</script>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=invoice<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>