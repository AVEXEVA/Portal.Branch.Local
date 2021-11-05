<?php 
session_start( [ 'read_and_close' => true ] );
require('../../../php/index.php');
setlocale(LC_MONETARY, 'en_US');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = $database->query(null,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Elevator'){
        $r= $database->query(null,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID= ?",array($_SESSION['User']));
        $My_User = sqlsrv_fetch_array($r);
        $Field = ($User['Field'] == 1 && $User['Title'] != "OFFICE") ? True : False;
        $r = $database->query($Portal,"
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
            $r =  $database->query( null,"SELECT Ref FROM nei.dbo.Invoice LEFT JOIN nei.dbo.Loc ON Invoice.Loc = Loc.Loc WHERE Invoice.Ref='{$_GET['ID']}' AND Loc.Owner = '{$_SESSION['Branch_ID']}';");
            $Privileged = $r ? TRUE : FALSE;
    }
    $database->query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "invoice.php"));
    if(!isset($array['ID']) || !$Privileged){?><html><head><script></script></head></html><?php }
    else {
?>
    <script>
		$(document).ready(function(){
			<?php
		  	$r = $database->query(null,"
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
    <?php require( bin_meta . 'index.php');?>    
    <title>Nouveau Elevator Portal</title>    
    <?php require( bin_css . 'index.php');?>
    <?php require( bin_js . 'index.php');?>
</head>
<body onload='finishLoadingPage();'>
    <div id="wrapper" style='overflow:auto !important;' class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require( bin_php . 'element/navigation/index.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id="page-wrapper" class='content' style='overflow:auto !important;<?php if(isset($_SESSION['Branch']) && $_SESSION['Branch'] == 'Customer'){?>margin:0px !important;<?php }?>'>
        </div>
    </div>
    
    
    <?php require('cgi-bin/js/datatables.php');?>
    
    
    

    <!-- Custom Date Filters-->
    

    <!-- Morris Charts JavaScript -->
    <script src="https://www.nouveauelevator.com/vendor/raphael/raphael.min.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/morrisjs/morris.min.js"></script>
		});
	</script>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=invoice<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>