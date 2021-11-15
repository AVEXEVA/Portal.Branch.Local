<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = $database->query(null,"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Elevator'){
        $User = $database->query(null,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID = ?",array($_SESSION['User']));
        $User = sqlsrv_fetch_array($User);
        $Field = ($User['Field'] == 1 && $User['Title'] != "OFFICE") ? True : False;
        $r = $database->query($Portal,"
            SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
            FROM   Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
        $Privileges = array();
        while($array2 = sqlsrv_fetch_array($r)){$Privileges[$array2['Access_Table']] = $array2;}
        $Privileged = FALSE;
        if(isset($Privileges['Territory']) && $Privileges['Territory']['User_Privilege'] >= 4 && $Privileges['Territory']['Group_Privilege'] >= 4 && $Privileges['Territory']['Other_Privilege'] >= 4){$Privileged = TRUE;}
    }
    if(!isset($array['ID'])  || !$Privileged || !is_numeric($_GET['ID'])){
      ?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
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
	<!--<div class="panel-heading"><h3><i class="fa fa-bell fa-fw"></i> Locations Table</h3></div>-->
	<div class="panel-body white-background BankGothic shadow">
		<table id='Table_Customers' class='display' cellspacing='0' width='100%' style='font-size:12px;'>
			<thead>
				<th title="Location's ID">ID</th>
				<th title="Location's Name State ID">Name</th>
				<th title="Location's Street">Profit</th>
				<th title="Location's City">Modernization</th>
				<th title="Location's State">Liability</th>
			</thead>
		</table>
	</div>
</div>
</div>
<script>
var Table_Customers = $('#Table_Customers').DataTable( {
"ajax": "bin/php/get/Locations_by_Territory.php?ID=<?php echo $_GET['ID'];?>",
"columns": [
	{
		"data": "Location_ID",
		"className":"hidden"
	},{
		"data": "Location_Name"
	},{
			"data": "Profit",
		render:function(data){
			return data.toFixed(2);
		}
	},{
		"data" : "Modernizations",
		render:function(data){
			return data.toFixed(2);
		}
	},{
		"data" : "Legal",
		render:function(data){
			return data.toFixed(2);
		}
	}
],
"buttons":[
	<?php if(isset($My_Privleges['Export']) || TRUE){?>{
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
"drawCallback": function ( settings ) {
  hrefLocations(this.api());
},
<?php require('../../../js/datatableOptions.php');?>
} );
function hrefLocations(tbl){
  $("table#Table_Customers tbody tr").each(function(){
    $(this).on('click',function(){
      document.location.href='location.php?ID=' + tbl.row(this).data().Location_ID;
    });
  });
}
</script>

<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
