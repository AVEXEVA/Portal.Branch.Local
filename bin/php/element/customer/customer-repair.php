<?php 
session_start( [ 'read_and_close' => true ] );

require('../../../php/index.php');
setlocale(LC_MONETARY, 'en_US');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = $database->query(null,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Elevator'){
        $My_User = $database->query(null,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID = ?",array($_SESSION['User']));
        $My_User = sqlsrv_fetch_array($My_User); 
        $Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
        $r = $database->query($Portal,"
            SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
            FROM   Portal.dbo.Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
        $My_Privileges = array();
        while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
        $Privileged = FALSE;
        if(isset($My_Privileges['Customer']) && $My_Privileges['Customer']['Other_Privilege'] >= 4){$Privileged = TRUE;}
    }
    $database->query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "customer-repair.php"));
    if(!isset($array['ID'])  || !$Privileged || !is_numeric($_GET['ID'])){?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {
        $r = $database->query(null,
            "SELECT TOP 1
                    OwnerWithRol.ID      AS Customer_ID,
                    OwnerWithRol.Name    AS Customer_Name,
                    OwnerWithRol.Address AS Customer_Street,
                    OwnerWithRol.City    AS Customer_City,
                    OwnerWithRol.State   AS Customer_State,
                    OwnerWithRol.Zip     AS Customer_Zip,
                    OwnerWithRol.Status  AS Customer_Status,
                    OwnerWithRol.Website AS Customer_Website
            FROM    nei.dbo.OwnerWithRol
            WHERE   OwnerWithRol.ID = ?
        ;",array($_GET['ID']));
        $Customer = sqlsrv_fetch_array($r);?>
<div class="panel panel-primary">
	<div class='panel-body white-background BankGothic shadow'>
		<table id='Table_Active_Repair' class='display' cellspacing='0' width='100%'>
			<thead>
				<th>ID</th>
				<th>Name</th>
				<th>Date</th>
			</thead>
		</table>
	</div>
</div>
			
<script>
var Table_Active_Repair = $('#Table_Active_Repair').DataTable( {
	"ajax": "bin/php/reports/Active_Repair_by_Location.php?ID=<?php echo $_GET['ID'];?>",
	"columns": [
		{ "data": "ID"},
		{ "data": "Name" },
		{ "data": "Date",render: function(data){return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}}
	],
	"order": [[1, 'asc']],
	"language":{
		"loadingRecords":""
	},
	"initComplete":function(){},
	"seaching":false,
	"paging":false,
} );
</script>

<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>