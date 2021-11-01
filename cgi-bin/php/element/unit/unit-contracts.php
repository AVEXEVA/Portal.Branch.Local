<?php
session_start();
require('../../../php/index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Texas'){
        sqlsrv_query($NEI,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "unit.php"));
        $r= sqlsrv_query($NEI,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID= ?",array($_SESSION['User']));
        $My_User = sqlsrv_fetch_array($r);
        $Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
        $r = sqlsrv_query($NEI,"
            SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
            FROM   Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
        $My_Privileges = array();
        while($My_Privilege = sqlsrv_fetch_array($r)){$My_Privileges[$My_Privilege['Access_Table']] = $My_Privilege;}
        $Privileged = FALSE;
        if(isset($My_Privileges['Unit']) && $My_Privileges['Unit']['User_Privilege'] >= 4 && $My_Privileges['Unit']['Group_Privilege'] >= 4 && $My_Privileges['Unit']['Other_Privilege'] >= 4){$Privileged = TRUE;}
        elseif(isset($My_Privileges['Unit']) && $My_Privileges['Unit']['User_Privilege'] >= 4 && $My_Privileges['Unit']['Group_Privilege'] >= 4){
			$r = sqlsrv_query($NEI,"
				SELECT Elev.Loc AS Location_ID
				FROM   Elev
				WHERE  Elev.ID = ?
			;",array($_GET['ID'] ));
			$Location_ID = sqlsrv_fetch_array($r)['Location_ID'];
            $r = sqlsrv_query($NEI,"
			SELECT Tickets.*
			FROM 
			(
				(
					SELECT TicketO.ID
					FROM   TicketO 
						   LEFT JOIN Loc  ON TicketO.LID   = Loc.Loc
						   LEFT JOIN Elev ON Loc.Loc       = Elev.Loc
						   LEFT JOIN Emp  ON TicketO.fWork = Emp.fWork
					WHERE  Emp.ID      = ?
						   AND Loc.Loc = ?
				)
				UNION ALL
				(
					SELECT TicketD.ID
					FROM   TicketD 
						   LEFT JOIN Loc  ON TicketD.Loc   = Loc.Loc
						   LEFT JOIN Elev ON Loc.Loc       = Elev.Loc
						   LEFT JOIN Emp  ON TicketD.fWork = Emp.fWork
					WHERE  Emp.ID      = ?
						   AND Loc.Loc = ?
				)

			) AS Tickets
           	;", array($_SESSION['User'], $Location_ID, $_SESSION['User'], $Location_ID, $_SESSION['User'], $Location_ID));
            $r = sqlsrv_fetch_array($r);
            $Privileged = is_array($r) ? TRUE : FALSE;
        }
    }
    if(!isset($array['ID'])  || !$Privileged || !is_numeric($_GET['ID'])){?><html><head><script>document.location.href="../login.php?Forward=unit<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {
        if(count($_POST) > 0){
            fixArrayKey($_POST);
            foreach($_POST as $key=>$value){
				if($key == 'Price'){continue;}
				if($key == 'Type'){continue;}
                sqlsrv_query($NEI,"
                    UPDATE ElevTItem
                    SET    ElevTItem.Value     = ?
                    WHERE  ElevTItem.Elev      = ?
                           AND ElevTItem.ElevT = 1
                           AND ElevTItem.fDesc = ?
                ;",array($value,$_GET['ID'],$key));
            }
			if(isset($_POST['Price'])){
				sqlsrv_query($NEI,"
					UPDATE Elev
					SET    Elev.Price = ?
					WHERE  Elev.ID    = ?
				;",array($_POST['Price'],$_GET['ID']));
			}
			if(isset($_POST['Type'])){
				sqlsrv_query($NEI,"
					UPDATE Elev
					SET    Elev.Type = ?
					WHERE  Elev.ID    = ?
				;",array($_POST['Type'],$_GET['ID']));
			}
        }
        $r = sqlsrv_query($NEI,
            "SELECT TOP 1
                Elev.ID,
                Elev.Unit           AS Unit,
                Elev.State          AS State,
                Elev.Cat            AS Category,
                Elev.Type           AS Type,
                Elev.Building       AS Building,
                Elev.Since          AS Since,
                Elev.Last           AS Last,
                Elev.Price          AS Price,
                Elev.fDesc          AS Description,
                Loc.Loc             AS Location_ID,
                Loc.ID              AS Name,
                Loc.Tag             AS Tag,
                Loc.Tag             AS Location_Tag,
                Loc.Address         AS Street,
                Loc.City            AS City,
                Loc.State           AS Location_State,
                Loc.Zip             AS Zip,
                Loc.Route           AS Route,
                Zone.Name           AS Zone,
                OwnerWithRol.Name   AS Customer_Name,
                OwnerWithRol.ID     AS Customer_ID,
                Emp.ID AS Route_Mechanic_ID,
                Emp.fFirst AS Route_Mechanic_First_Name,
                Emp.Last AS Route_Mechanic_Last_Name
            FROM 
                Elev
                LEFT JOIN Loc           ON Elev.Loc = Loc.Loc
                LEFT JOIN Zone          ON Loc.Zone = Zone.ID
                LEFT JOIN OwnerWithRol  ON Loc.Owner = OwnerWithRol.ID
                LEFT JOIN Route ON Loc.Route = Route.ID
                LEFT JOIN Emp ON Route.Mech = Emp.fWork
            WHERE
                Elev.ID = ?
		;",array($_GET['ID']));
        $Unit = sqlsrv_fetch_array($r);
        $unit = $Unit;
        $data = $Unit;
        $r2 = sqlsrv_query($NEI,"
            SELECT *
            FROM   ElevTItem
            WHERE  ElevTItem.ElevT    = 1
                   AND ElevTItem.Elev = ?
        ;",array($_GET['ID']));
        if($r2){while($array = sqlsrv_fetch_array($r2)){$Unit[$array['fDesc']] = $array['Value'];}}
?><!DOCTYPE html>
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
		"ajax": "cgi-bin/php/get/Contracts_by_Location.php?ID=<?php echo $_GET['ID'];?>",
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
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>