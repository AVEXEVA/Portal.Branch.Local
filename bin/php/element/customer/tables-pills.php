 <?php 
session_start( [ 'read_and_close' => true ] );
require('../../../php/index.php');

if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = $database->query(null,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Elevator'){
    	$database->query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "customer.php"));
        $r= $database->query(null,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID= ?",array($_SESSION['User']));
        $My_User = sqlsrv_fetch_array($r);
        $Field = ($My_User['Field'] == 1 && $My_User['Title'] != 'OFFICE') ? True : False;
        $r = $database->query($Portal,"
            SELECT Access, Owner, Group, Other
            FROM   Portal.dbo.Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
        $My_Privileges = array();
        while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access']] = $array2;}
        $Privileged = FALSE;
        if(isset($My_Privileges['Customer']) && $My_Privileges['Customer']['Owner'] >= 4 && $My_Privileges['Customer']['Group'] >= 4 && $My_Privileges['Customer']['Other'] >= 4){
        	$database->query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "customer.php"));
        	$Privileged = TRUE;}
        elseif($My_Privileges['Customer']['Owner'] >= 4 && $My_Privileges['Ticket']['Group'] >= 4 ){
        	$database->query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "customer.php"));
            $r = $database->query(  null,"
                SELECT TicketO.ID AS ID 
                FROM nei.dbo.TicketO LEFT JOIN nei.dbo.Loc ON TicketO.LID = Loc.Loc
                WHERE Loc.Owner = ?;",array($_GET['ID']));
            $r2 = $database->query(  null,"
                SELECT TicketD.ID AS ID 
                FROM nei.dbo.TicketD LEFT JOIN nei.dbo.Loc ON TicketD.Loc = Loc.Loc
                WHERE Loc.Owner = ?;",array($_GET['ID']));
            $Privileged = (is_array(sqlsrv_fetch_array($r)) || is_array(sqlsrv_fetch_array($r2))) ? TRUE : FALSE;}
    } elseif($_SESSION['Branch'] == 'Customer' && $_SESSION['Branch_ID'] == $_GET['ID']){$Privileged = TRUE;}
    //
    if(!isset($array['ID'])  || !is_numeric($_GET['ID']) || !$Privileged || !is_numeric($_GET['ID'])){?><html><head><script>document.location.href="../login.php?Forward=customer<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {
        $r = $database->query(null,
            "SELECT TOP 1
                    OwnerWithRol.ID      AS Customer_ID,
                    OwnerWithRol.Name    AS Name,
                    OwnerWithRol.Address AS Street,
                    OwnerWithRol.City    AS City,
                    OwnerWithRol.State   AS State,
                    OwnerWithRol.Zip     AS Zip,
                    OwnerWithRol.Status  AS Status
            FROM    OwnerWithRol
            WHERE   OwnerWithRol.ID = '{$_GET['ID']}'");
        $Customer = sqlsrv_fetch_array($r);
        $job_result = $database->query(null,"
            SELECT 
                Job.ID AS ID
            FROM 
                Job 
            WHERE 
                Job.Owner = '{$_GET['ID']}'
        ;");
        if($job_result){
            $Jobs = array();
            $dates = array();
            $totals = array();
            while($array = sqlsrv_fetch_array($job_result)){$Jobs[] = "[JOBLABOR].[JOB #]='{$array['ID']}'";}
            $SQL_Jobs = implode(" OR ",$Jobs);
        }?>
<div class="tab-pane fade in" id="tables-pills">
	<ul class="nav nav-tabs BankGothic">
		<li class=""><a href="#" onClick="asyncSubPage(this);" tab='tables-locations-pills' main='tables-pills'>Locations</a></li>
		<li class=""><a href="#" onClick="asyncSubPage(this);" tab='tables-units-pills' main='tables-pills' >Units</a></li>
		<li class=""><a href="#" onClick="asyncSubPage(this);" tab='tables-tickets-pills' main='tables-pills' >Tickets</a></li>
		<li class=""><a href="#" onClick="asyncSubPage(this);" tab='tables-jobs-pills' main='tables-pills' >Jobs</a></li>
		<li class=""><a href="#" onClick="asyncSubPage(this);" tab='tables-violations-pills' main='tables-pills' >Violations</a></li>
		<li class=""><a href="#" onClick="asyncSubPage(this);" tab='tables-contracts-pills' main='tables-pills' >Contracts</a></li>
		<li class=""><a href="#" onClick="asyncSubPage(this);" tab='tables-invoices-pills' main='tables-pills' >Invoices</a></li>
		<li class=""><a href="#" onClick="asyncSubPage(this);" tab='tables-proposals-pills' main='tables-pills' >Proposals</a></li>
		<li class=""><a href="#" onClick="asyncSubPage(this);" tab='tables-collections-pills' main='tables-pills' >Collections</a></li>
		<li class=""><a href="#" onClick="asyncSubPage(this);" tab='tables-log-pills' main='tables-pills' >Log</a></li>
	</ul>
	<div class="tab-content tables-pills" id="sub-tab-content">
		<div class='tab-pane fade in active' id='loading-sub-pills'>
			<?php require('../../../php/element/loading.php');?>
		</div>
	</div>
</div>
<script>
$(document).ready(function(){
	$("#loading-pills").removeClass("active");
	$("#tables-pills").addClass('active');
	$("a[tab='tables-locations-pills']").click();
});
</script>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=customer<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>