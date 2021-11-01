 <?php 
session_start();
require('../../../php/index.php');

if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Elevator'){
    	sqlsrv_query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "customer.php"));
        $r= sqlsrv_query($NEI,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID= ?",array($_SESSION['User']));
        $My_User = sqlsrv_fetch_array($r);
        $Field = ($My_User['Field'] == 1 && $My_User['Title'] != 'OFFICE') ? True : False;
        $r = sqlsrv_query($Portal,"
            SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
            FROM   Portal.dbo.Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
        $My_Privileges = array();
        while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
        $Privileged = FALSE;
        if(isset($My_Privileges['Customer']) && $My_Privileges['Customer']['User_Privilege'] >= 4 && $My_Privileges['Customer']['Group_Privilege'] >= 4 && $My_Privileges['Customer']['Other_Privilege'] >= 4){
        	sqlsrv_query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "customer.php"));
        	$Privileged = TRUE;}
        elseif($My_Privileges['Customer']['User_Privilege'] >= 4 && $My_Privileges['Ticket']['Group_Privilege'] >= 4 ){
        	sqlsrv_query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "customer.php"));
            $r = sqlsrv_query(  $NEI,"
                SELECT TicketO.ID AS ID 
                FROM nei.dbo.TicketO LEFT JOIN nei.dbo.Loc ON TicketO.LID = Loc.Loc
                WHERE Loc.Owner = ?;",array($_GET['ID']));
            $r2 = sqlsrv_query(  $NEI,"
                SELECT TicketD.ID AS ID 
                FROM nei.dbo.TicketD LEFT JOIN nei.dbo.Loc ON TicketD.Loc = Loc.Loc
                WHERE Loc.Owner = ?;",array($_GET['ID']));
            $Privileged = (is_array(sqlsrv_fetch_array($r)) || is_array(sqlsrv_fetch_array($r2))) ? TRUE : FALSE;}
    } elseif($_SESSION['Branch'] == 'Customer' && $_SESSION['Branch_ID'] == $_GET['ID']){$Privileged = TRUE;}
    //
    if(!isset($array['ID'])  || !is_numeric($_GET['ID']) || !$Privileged || !is_numeric($_GET['ID'])){?><html><head><script>document.location.href="../login.php?Forward=customer<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {
        $r = sqlsrv_query($NEI,
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
        $job_result = sqlsrv_query($NEI,"
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
<div class='tab-pane fade in' id='tables-proposals-pills'>
	<div class="panel panel-primary" style='margin-bottom:0px;'>
		<div class="panel-body">
			<div class="row">
				<div class='col-md-12' >
					<div class="panel panel-primary">
						<div class="panel-heading"><h3><i class="fa fa-bell fa-fw"></i> Proposals Table</h3></div>
						<div class="panel-body white-background BankGothic shadow">
							<div id='Form_Proposal'>
								<div class="panel panel-primary">
									<div class="panel-heading" style='position:fixed;width:750px;z-index:999;'><h2 style='display:block;'>Location Form</h2></div>
									<div class="panel-body white-background BankGothic shadow" style='padding-top:100px;'>
										<div style='display:block !important;'>
											<fieldset >
												<legend>Proposal Information</legend>
												<editor-field name='ID'></editor-field>
												<editor-field name='fDate'></editor-field>
												<editor-field name='Contact'></editor-field>
												<editor-field name='Location'></editor-field>
												<editor-field name='Title'></editor-field>
												<editor-field name='Cost'></editor-field>
												<editor-field name='Price'></editor-field>
											</fieldset>
										</div>
									</div>
								</div>
							</div>
							<table id='Table_Proposals' class='display' cellspacing='0' width='100%'>
								<thead>
									<th title='ID of the Proposal'>ID</th>
									<th title='Date of the Proposal'>Date</th>
									<th title='Proposal Contact'>Contact</th>
									<th title='Location of the Proposal'>Location</th>
									<th title='Title of the Proposal'>Title</th>
									<?php if($_SESSION['Branch'] != 'Customer'){?><th title="Proposed Cost">Cost</th><?php }?>
									<th title='Proposed Amount'>Price</th>
								</thead>
							</table>
						</div>
					</div>
				</div>
				<script>
				var Editor_Proposals = new $.fn.dataTable.Editor({
					ajax: "php/post/Collection.php?ID=<?php echo $_GET['ID'];?>",
					table: "#Table_Proposals",
					template: '#Form_Proposal',
					formOptions: {
						inline: {
							submit: "allIfChanged"
						}
					},
					idSrc: "ID",
					fields : [{
						label: "Date",
						name: "fDate",
						type:"datetime"
					},{
						label:"ID",
						name:"ID"
					},{
						label:"Contact",
						name:"Contact"
					},{
						label:"Location",
						name:"Location",
						type:"select",
						options: [<?php
							$r = sqlsrv_query($NEI,"
								SELECT   Loc.Tag AS Location
								FROM     nei.dbo.Loc
								WHERE    Loc.Owner = ?
								GROUP BY Loc.Tag
								ORDER BY Loc.Tag ASC
							;",array($_GET['ID']));
							$Locations = array();
							if($r){while($Location = sqlsrv_fetch_array($r)){$Locations[] = '{' . "label: '{$Location['Location']}', value:'{$Location['Location']}'" . '}';}}
							echo implode(",",$Locations);
						?>]
					},{
						label:"Title",
						name:"Title"
					},{
						label:"Cost",
						name:"Cost"
					},{
						label:"Price",
						name:"Price"
					}]
				});
				Editor_Proposals.field('ID').disable();
				//Editor_Collections.field('Invoice').hide();
				/*$('#Table_Proposals').on( 'click', 'tbody td:not(:first-child)', function (e) {
					Editor_Proposals.inline( this );
				} );*/
				var Table_Proposals = $('#Table_Proposals').DataTable( {
					"ajax": "cgi-bin/php/get/Proposals_by_Customer.php?ID=<?php echo $_GET['ID'];?>",
					"columns": [
						{ "data": "ID" },
						{ 
							"data": "fDate",
							"defaultContent":"Undated",
							render: function(data) {return data.substring(0,10);}
						},
						{ "data": "Contact"},
						{ "data": "Location"},
						{ "data": "Title"},
						<?php if($_SESSION['Branch'] != 'Customer'){?>{ "data": "Cost"},<?php }?>
						{ "data": "Price"}

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
						},/*{ 	
							extend : "create", 
							editor : Editor_Proposals 
						},{ 
							extend : "edit",   
							editor : Editor_Proposals 
						},{ 
							extend : "remove", 
							editor : Editor_Proposals 
						},*/{ 
							text : "View",
							action:function(e,dt,node,config){
								document.location.href = 'proposal.php?ID=' + $("#Table_Proposals tbody tr.selected td:nth-child(2)").html();
							}
						}
					],
					<?php require('../../../js/datatableOptions.php');?>
				} );
				</script>
				<?php /*<div class='col-md-12'>
					<div class="panel panel-primary">
						<div class="panel-heading"><h3><i class="fa fa-bell fa-fw"></i> </h3></div>
						<div class="panel-body white-background BankGothic shadow">
						</div>
					</div>
				</div>*/?>
			</div>
		</div>
	</div>
</div>
<script>
$(document).ready(function(){
	$("#loading-sub-pills").removeClass("active");
	$("#tables-proposals-pills").addClass('active');
});
</script>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=customer<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?><!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Untitled Document</title>
</head>

<body>
</body>
</html>