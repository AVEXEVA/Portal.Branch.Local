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
            SELECT Access, Owner, Group, Other
            FROM   Portal.dbo.Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
        $My_Privileges = array();
        while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access']] = $array2;}
        $Privileged = FALSE;
        if(isset($My_Privileges['Territory']) && $My_Privileges['Territory']['Owner'] >= 4 && $My_Privileges['Territory']['Group'] >= 4 && $My_Privileges['Territory']['Other'] >= 4){$Privileged = TRUE;}
    }
	if(is_numeric($_GET['ID'])){$database->query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "territory.php?ID=" . $_GET['ID']));}
    if(!isset($array['ID'])  || !$Privileged || !is_numeric($_GET['ID'])){?><html><head><script>document.location.href="../login.php?Forward=territory<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {
        $r = $database->query(null,
            "SELECT TOP 1
                    Terr.ID   AS Territory_ID,
					Terr.Name AS Territory_Name
			FROM    nei.dbo.Terr
			WHERE   Terr.ID = ?
        ;",array($_GET['ID']));
        $Territory = sqlsrv_fetch_array($r);
?><div class="panel panel-primary">
	<div class='panel-body white-background shadower'>
		<table id='Table_Open_Violations' class='display' cellspacing='0' width='100%' style='font-size:12px;'>
			<thead>
				<th>Violation</th>
				<th>Status</th>
				<th>Due Date</th>
			</thead>
		</table>	
	</div>
</div>
	<script>
	var Table_Open_Violations = $('#Table_Open_Violations').DataTable( {
	"ajax": {
		"url":"bin/php/reports/Due_Violations_by_Territory.php?ID=<?php echo $_GET['ID'];?>",
		"dataSrc":function(json){if(!json.data){json.data = [];}return json.data;}
	},
	"columns": [
		{ 
			"data": "Name"
		},{ 
			"data": "Status"
		},{ 
			"data": "Due_Date"
		}
	],
	"order": [[1, 'asc']],
	"language":{"loadingRecords":""},
	//"paging":false,
	"searching":false,
	"info":false,
	"paging":false,
	"initComplete":function(){}
	} );
	function hrefViolations(){hrefRow("Tabe_OpenViolations","violation");}
	$("Table#Tabe_Open_Violations").on("draw.dt",function(){hrefViolations();});

	</script>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=territory<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>