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
	<!--<div class="panel-heading"><h3><i class="fa fa-bell fa-fw"></i> Locations Table</h3></div>-->
	<div class="panel-body white-background BankGothic shadow">
		<table id='Table_Customers' class='display' cellspacing='0' width='100%' style='font-size:12px;'>
			<thead>
				<th title="Location's ID">ID</th>
				<th title="Location's Name State ID">Name</th>
				<th title="Location's Street">Profit</th>
				<th title="Location's Tag">%</th>
				<th title="Location's City">Revenue</th>
				<th title="Location's State">Materials</th>
				<th title="Location's Zip">Labor</th>
			</thead>
		</table>
	</div>
</div>
</div>
<script>
var Table_Customers = $('#Table_Customers').DataTable( {
"ajax": "cgi-bin/php/get/Customers_by_Territory.php?ID=<?php echo $_GET['ID'];?>",
"columns": [
	{
		"data": "ID",
		"className":"hidden"
	},{
		"data": "Name"
	},{
		"data": "Profit",
		render:function(data){
				return "$" + parseFloat(data).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');

		}
	},{
		"data": "Profit_Percentage",
		render:function(data){
			return data.toFixed(1);
		}
	},{
		"data": "Revenue",
		render:function(data){
			if(data != null && data.length > 0 && !isNaN(data)){return  "$" + parseFloat(data).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');}
			else {return null;}
		}
	},{
		"data": "Material",
		render:function(data){
			if(data != null && data.length > 0 && !isNaN(data)){return "$" + parseFloat(data).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');}
			else {return null;}
		}
	},{
		"data": "Labor",
		render:function(data){
			if(data != null && data.length > 0 && !isNaN(data)){return "$" + parseFloat(data).toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');}
			else {return null;}
		}
	}
],
"buttons":[
	<?php if(isset($My_Privleges['Export'])){?>{
		extend: 'collection',
		text: 'Export',
		buttons: [
			'copy',
			'excel',
			'csv',
			'pdf',
			'print'
		]
	},<?php }?>
	/*{ extend: "create", editor: Editor_Locations },
	{ extend: "edit",   editor: Editor_Locations },
	{
		extend: "remove",
	 	editor: Editor_Locations,
	 	formButtons: [
			'Delete',
			{ text: 'Cancel', action: function () { this.close(); } }
		]
	},*/
],
"searching":false,
<?php require('../../../js/datatableOptions.php');?>
} );
function hrefCustomers(){hrefRow("Table_Customers","customer");}
$("Table#Table_Customers").on("draw.dt",function(){hrefCustomers();});
</script>

<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
