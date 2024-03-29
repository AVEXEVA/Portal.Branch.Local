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
            SELECT Access, Owner, Group, Other
            FROM   Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
        $Privileges = array();
        while($array2 = sqlsrv_fetch_array($r)){$Privileges[$array2['Access']] = $array2;}
        $Privileged = FALSE;
        if(isset($Privileges['Territory']) && $Privileges['Territory']['Owner'] >= 4 && $Privileges['Territory']['Group'] >= 4 && $Privileges['Territory']['Other'] >= 4){$Privileged = TRUE;}
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
	<div class="panel-heading">
		<div class="row">
			<div class="col-xs-3">
				<i class="fa fa-dollar fa-3x"></i>
			</div>
			<div class="col-xs-9 text-right">
				<div class="col-xs-9 text-right">
				<div class="medium"><?php
					$r = $database->query(null,"
						SELECT Sum(OpenAR.Balance) AS Count_of_Outstanding_Invoices
						FROM   nei.dbo.OpenAR
							   LEFT JOIN nei.dbo.Loc ON OpenAR.Loc = Loc.Loc
						WHERE  Loc.Terr = ?
					;",array($_GET['ID']));
					echo $r ? substr(money_format('%.2n',sqlsrv_fetch_array($r)['Count_of_Outstanding_Invoices']),0) : 0;?>
				</div>
				<div>Needs Collection</div></div>
			</div>
		</div>
	</div>
</div>
<div class="panel panel-primary">
	<!--<div class="panel-heading"><h3><?php \singleton\fontawesome::getInstance( )->Territory();?>Collectable Invoices</h3></div>-->
	<div class='panel-body white-background BankGothic shadow'>
		<table id='Table_Collectable_Invoices' class='display' cellspacing='0' width='100%' style='font-size:12px;'>
			<thead><tr>
				<th>Invoice #</th>
				<th>Location</th>
				<th>Date</th>
				<th>Due</th>
				<th>Balance</th>
				<th>Location Balance</th>
			</tr></thead>
		</table>
	</div>
</div>

<script>
var Table_Collectable_Invoices = $('#Table_Collectable_Invoices').DataTable( {
	"ajax": {
		"url":"bin/php/get/Collections_by_Territory.php?ID=<?php echo $_GET['ID'];?>",
		"dataSrc":function(json){if(!json.data){json.data = [];}return json.data;}
	},
	"scrollX": true,
	"columns": [
		{
			"data": "Invoice" ,
			"className":"hidden"
		},{
			"data" : "Location"
		},{
			"data": "Dated",
			render: function(data){return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}
		},{
			"data": "Due",
			render: function(data){return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}
		},{
			"data": "Balance",
			className:"sum",
			render:function(data){return "$" + parseFloat(data).toLocaleString();}
		},{
			"data":"Total_Balance",
			className:"sum",
			render:function(data){return "$" + parseFloat(data).toLocaleString();}
		}
	],
	"order": [[0, 'asc']],
	"language":{
		"loadingRecords":""
	},
	"paging":false,
	"searching":false,
	"footerCallback": function(row, data, start, end, display) {
		var api = this.api();

		api.columns('.sum', { page: 'current' }).every(function () {
			var sum = api
				.cells( null, this.index(), { page: 'current'} )
				.render('display')
				.reduce(function (a, b) {
					var x = parseFloat(a) || 0;
					var y = parseFloat(b) || 0;
					return x + y;
				}, 0);
			$(this.footer()).html(sum);
		});
	},
	"initComplete":function(){}
} );
function hrefInvoices(){hrefRow("Table_Collectable_Invoices","invoice");}
$("Table#Table_Collectable_Invoices").on("draw.dt",function(){hrefInvoices();});
</script>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=territory<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
