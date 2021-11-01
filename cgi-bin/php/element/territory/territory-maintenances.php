<?php 
session_start();
require('../../../php/index.php');
setlocale(LC_MONETARY, 'en_US');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Elevator'){
        $My_User = sqlsrv_query($NEI,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID = ?",array($_SESSION['User']));
        $My_User = sqlsrv_fetch_array($My_User); 
        $Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
        $r = sqlsrv_query($Portal,"
            SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
            FROM   Portal.dbo.Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
        $My_Privileges = array();
        while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
        $Privileged = FALSE;
        if(isset($My_Privileges['Territory']) && $My_Privileges['Territory']['User_Privilege'] >= 4 && $My_Privileges['Territory']['Group_Privilege'] >= 4 && $My_Privileges['Territory']['Other_Privilege'] >= 4){$Privileged = TRUE;}
    }
	if(is_numeric($_GET['ID'])){sqlsrv_query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "territory.php?ID=" . $_GET['ID']));}
    if(!isset($array['ID'])  || !$Privileged || !is_numeric($_GET['ID'])){?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {
        $r = sqlsrv_query($NEI,
            "SELECT TOP 1
                    Terr.ID   AS Territory_ID,
					Terr.Name AS Territory_Name
			FROM    nei.dbo.Terr
			WHERE   Terr.ID = ?
        ;",array($_GET['ID']));
        $Territory = sqlsrv_fetch_array($r);
?><div class="panel panel-primary">
    <div class='panel-body white-background BankGothic shadow'>
        <table id='Table_Required_Maintenance' class='display' cellspacing='0' width='100%'>
            <thead>
                <th>ID</th>
				<th>Location</th>
                <th>State</th>
                <th>Last Maintained</th>
                <th>Maintenance Mechanic</th>
            </thead>
        </table>    
    </div>
</div>
<script>
var Table_Required_Maintenance = $('#Table_Required_Maintenance').DataTable( {
    "ajax": {
        "url":"cgi-bin/php/reports/Maintenances_by_Territory.php?ID=<?php echo $_GET['ID'];?>",
        "dataSrc":function(json){if(!json.data){json.data = [];}return json.data;}
    },
    "columns": [
        { 
            "data": "ID", 
            "className" : "hidden" 
        },{
			"data": "Location"
		},{ 
            "data": "State"
        },{ 
            "data": "Last_Date",
            render: function(data){return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}
        },{ 
            "data": "Route"
        }
    ],
    "order": [[1, 'asc']],
    "language":{"loadingRecords":""},
    //"paging":false,
    "searching":false,
    "info":false,
    "lengthMenu":[[10,25,50,100,500,-1,0],[10,25,50,100,500,"All","None"]],
    "initComplete":function(){},
    "paging":false,
    "searching":false
} );
</script>
<style>
div.column {display:inline-block;vertical-align:top;}
div.label1 {display:inline-block;font-weight:bold;width:150px;vertical-align:top;}
div.data {display:inline-block;width:300px;vertical-align:top;}
.border-seperate {border-bottom:3px solid #333333;}
</style>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>