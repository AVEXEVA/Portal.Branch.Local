 <?php 
session_start( [ 'read_and_close' => true ] );
require('../../../../bin/php/index.php');

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
        if(isset($My_Privileges['Location']) && $My_Privileges['Location']['Owner'] >= 4 && $My_Privileges['Location']['Group'] >= 4 && $My_Privileges['Location']['Other'] >= 4){$Privileged = TRUE;}
        elseif($My_Privileges['Location']['Owner'] >= 4 && is_numeric($_GET['ID'])){
            $r = $database->query(  null,"SELECT * FROM nei.dbo.TicketO WHERE TicketO.LID='{$_GET['ID']}' AND fWork='{$My_User['fWork']}'");
            $r2 = $database->query( null,"SELECT * FROM nei.dbo.TicketD WHERE TicketD.Loc='{$_GET['ID']}' AND fWork='{$My_User['fWork']}'");
            $r3 = $database->query( null,"SELECT * FROM nei.dbo.TicketDArchive WHERE TicketDArchive.Loc='{$_GET['ID']}' AND fWork='{$My_User['fWork']}'");
            $r = sqlsrv_fetch_array($r);
            $r2 = sqlsrv_fetch_array($r2);
			$r3 = sqlsrv_fetch_array($r3);
            $Privileged = (is_array($r) || is_array($r2) || is_array($r3)) ? TRUE : FALSE;
        }
    } elseif($_SESSION['Branch'] == 'Customer' && $_SESSION['Branch_ID'] == $_GET['ID']){$Privileged = TRUE;}
    //
    if(!isset($array['ID'])  || !is_numeric($_GET['ID']) || !$Privileged || !is_numeric($_GET['ID'])){?><html><head><script>document.location.href="../login.php?Forward=customer<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {
        $r = $database->query(null,
            "SELECT TOP 1
                    Loc.Loc              AS Location_ID,
                    Loc.ID               AS Name,
                    Loc.Tag              AS Tag,
                    Loc.Address          AS Street,
                    Loc.City             AS City,
                    Loc.State            AS State,
                    Loc.Zip              AS Zip,
                    Loc.Balance          as Location_Balance,
                    Zone.Name            AS Zone,
                    Loc.Route            AS Route_ID,
                    Emp.ID               AS Route_Mechanic_ID,
                    Emp.fFirst           AS Route_Mechanic_First_Name,
                    Emp.Last             AS Route_Mechanic_Last_Name,
                    Loc.Owner            AS Customer_ID,
                    OwnerWithRol.Name    AS Customer_Name,
                    OwnerWithRol.Balance AS Customer_Balance,
                    Terr.Name            AS Territory_Domain/*,
                    Sum(SELECT Location.ID FROM Loc AS Location WHERE Location.Owner='Loc.Owner') AS Customer_Locations*/
            FROM    Loc
                    LEFT JOIN nei.dbo.Zone         ON Loc.Zone   = Zone.ID
                    LEFT JOIN nei.dbo.Route        ON Loc.Route  = Route.ID
                    LEFT JOIN nei.dbo.Emp          ON Route.Mech = Emp.fWork
                    LEFT JOIN nei.dbo.OwnerWithRol ON Loc.Owner  = OwnerWithRol.ID
                    LEFT JOIN Terr         		   ON Terr.ID    = Loc.Terr
            WHERE
                    Loc.Loc = ?
        ;",array($_GET['ID']));
        $Location = sqlsrv_fetch_array($r);?>
<div class='tab-pane fade in' id='tables-contracts-pills'>
	<div class="panel panel-primary" style='margin-bottom:0px;'>
		<div class="panel-body">
			<div class="row">
				<div class='col-md-12' >
					<div class="panel panel-primary">
						<!--<div class="panel-heading"><h3><i class="fa fa-bell fa-fw"></i> Contracts Table</h3></div>-->
						<div class="panel-body white-background BankGothic shadow">
							<table id='Table_Contracts' class='display' cellspacing='0' width='100%'>
								<thead><tr>
									<th>Job</th>
									<th>Location</th>
									<th>Amount</th>
									<th>Start</th>
									<th>Review</th>
									<th>Cycle</th>
									<th>Months</th>
									<th>Link</th>
								</tr></thead>
							</table>
						</div>
					</div>
				</div>
				<script>
				var Editor_Contracts = new $.fn.dataTable.Editor({
					ajax: "php/post/Contract.php?ID=<?php echo $_GET['ID'];?>",
					table: "#Table_Contracts",
					idSrc: "Contract_Job",
					formOptions: {
						inline: {
							submit: "allIfChanged"
						}
					},
					fields : [{
						label: "Job",
						name: "Contract_Job"
					},{
						label: "Location",
						name: "Location"
					},{
						label: "Amount",
						name: "Contract_Amount"
					},{
						label: "Start",
						name: "Contract_Start"
					},{
						label: "Review",
						name: "Contract_Review"
					},{
						label: "Billing Cycle",
						name: "Contract_Billing_Cycle"
					},{
						label: "Length",
						name: "Contract_Length"
					},{
						label: "Link",
						name: "Link"
					}]
				});
				Editor_Contracts.field("Contract_Job").disable();
				Editor_Contracts.field("Location").disable();
				Editor_Contracts.field("Contract_Amount").disable();
				Editor_Contracts.field("Contract_Start").disable();
				Editor_Contracts.field("Contract_Review").disable();
				Editor_Contracts.field("Contract_Billing_Cycle").disable();
				Editor_Contracts.field("Contract_Length").disable();
				var Table_Contracts = $('#Table_Contracts').DataTable( {
					"ajax": "bin/php/get/Contracts_by_Location.php?ID=<?php echo $_GET['ID'];?>",
					"columns": [
						{ 
							"data": "Contract_Job",
						},{ 
							"data": "Location"
						},{ 
							"data": "Contract_Amount"
						},{ 
							"data": "Contract_Start",render: function(data){return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}
						},{ 
							"data": "Contract_Review",render: function(data){return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}
						},{ 
							"data": "Contract_Billing_Cycle",
							render:function(data){
								switch(data){
									case 0:return 'Monthly';
									case 1:return 'Bi-Monthly';
									case 2:return 'Quarterly';
									case 3:return 'Trimester';
									case 4:return 'Semi-Annualy';
									case 5:return 'Annually';
									case 6:return 'Never';
									default:return data;}}
						},{ 
							"data": "Contract_Length"
						},{ 
							"data": "Link"
						}
					],
					"buttons":[
						{
							extend: 'collection',
							text: 'Export',
							buttons: [
								'copy',
								'excel',
								'csv',
								'pdf',
								'print'
							]
						},
						/*{ extend: "create", editor: Editor_Contracts },
						{ extend: "edit",   editor: Editor_Contracts },
						{ extend: "remove", editor: Editor_Contracts },*/
						{
							extend: "selected",
							text: 'Duplicate',
							action: function ( e, dt, node, config ) {
								// Start in edit mode, and then change to create
								Editor_Contracts
									.edit( Table_Contracts.rows( {selected: true} ).indexes(), {
										title: 'Duplicate record',
										buttons: 'Create from existing'
									} )
									.mode( 'create' );
							}
						},
						{ text:"View",
						  action:function(e,dt,node,config){
							  var data = Table_Contracts.rows({selected:true}).data()[0];
							  document.location.href = data.Link;
							  
						  }
						}
					],
					<?php require('../../../js/datatableOptions.php');?>
				} );
				</script>
			</div>
		</div>
	</div>
</div>
<script>
$(document).ready(function(){
	$("#loading-sub-pills").removeClass("active");
	$("#tables-contracts-pills").addClass('active');
});
</script>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=customer<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>