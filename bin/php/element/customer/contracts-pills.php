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
        $Field = ($User['Field'] == 1 && $User['Title'] != 'OFFICE') ? True : False;
        $r = $database->query($Portal,"
            SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
            FROM   Portal.dbo.Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
        $My_Privileges = array();
        while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
        $Privileged = FALSE;
        if(isset($My_Privileges['Customer']) && $My_Privileges['Customer']['User_Privilege'] >= 4 && $My_Privileges['Customer']['Group_Privilege'] >= 4 && $My_Privileges['Customer']['Other_Privilege'] >= 4){
        	$database->query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "customer.php"));
        	$Privileged = TRUE;}
        elseif($My_Privileges['Customer']['User_Privilege'] >= 4 && $My_Privileges['Ticket']['Group_Privilege'] >= 4 ){
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
<div class="tab-pane fade in" id="contracts-pills">
	<table id='Table_Contracts' class='display' cellspacing='0' width='100%'>
		<thead><tr>
			<th title=''>Location</th>
			<th title=''>Amount</th>
			<th title=''>Start</th>
			<th title=''>Cycle</th>
			<th title=''>Months</th>
		</tr></thead>
	</table>
</div>
<?php require(PROJECT_ROOT.'js/pie/type_by_unit_for_customer.php');?>
<script>
function hrefContracts(){
	var table = "Table_Contracts";
	$("#" + table + " tbody tr").each(function(){$(this).on('click',function(){window.open("https://drive.google.com/drive/folders/0ByFVts4kTa6_eWZCZG5hSUZIU3M","_blank");});});
}
$(document).ready(function(){
    var Table_Contracts = $('#Table_Contracts').DataTable( {
		"ajax": "bin/php/get/Contracts_by_Customer.php?ID=<?php echo $_GET['ID'];?>",
		"columns": [
			{ "data": "Location"},
			{ "data": "Amount"},
			{ "data": "Billing_Start"},
			{ 
				"data": "Billing_Cycle",
				render:function(data){
					switch(data){
						case 0:return 'Monthly';
						case 1:return 'Bi-Monthly';
						case 2:return 'Quarterly';
						case 3:return 'Trimester';
						case 4:return 'Semi-Annualy';
						case 5:return 'Annually';
						case 6:return 'Never';}}
			},
			{ "data": "Billing_Length"}
		],
		"order": [[1, 'asc']],
		"language":{
			"loadingRecords":""
		},
		"initComplete":function(){
			$("#loading-pills").removeClass("active");
			$("#contracts-pills").addClass('active');
		}

	} );
	$("Table#Table_Contracts").on("draw.dt",function(){hrefContracts();});
	
});
</script>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=customer<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>