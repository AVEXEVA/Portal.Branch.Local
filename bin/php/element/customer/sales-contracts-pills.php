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
        $Field = ($My_User['Field'] == 1 && $My_User['Title'] != 'OFFICE') ? True : False;
        $r = $database->query($Portal,"
            SELECT Access, Owner, Group, Other
            FROM   Portal.dbo.Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
        $My_Privileges = array();
        while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access']] = $array2;}
        $Privileged = FALSE;
        if(isset($My_Privileges['Customer']) && $My_Privileges['Customer']['Owner'] >= 4 && $My_Privileges['Customer']['Group'] >= 4 && $My_Privileges['Customer']['Other'] >= 4){
        	$database->query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "customer.php"));
        	$Privileged = TRUE;}
        elseif($My_Privileges['Customer']['Owner'] >= 4 && $My_Privileges['Ticket']['Group'] >= 4 ){
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
<div class='tab-pane fade in' id='sales-contracts-pills'>
	<div class="panel panel-primary" style='margin-bottom:0px;'>
		<div class="panel-body">
			<div class='row'>
				<div class='col-md-6'>
					<div class='row'>
						<div class='col-md-12'>
							<div class="panel panel-primary">
								<div class="panel-heading"><h3><?php \singleton\fontawesome::getInstance( )->Territory();?>Active Contracts</h3></div>
								<div class='panel-body white-background BankGothic shadow'>
									<table id='Table_Sales_Contracts_2' class='display' cellspacing='0' width='100%'>
										<thead><tr>
											<th title=''>Location</th>
											<th title=''>Amount</th>
											<th title=''>Start</th>
											<th title=''>Cycle</th>
											<th title=''>Months</th>
											<th title=''>Link</th>
										</tr></thead>
									</table>
								</div>
							</div>
							<script>
							function hrefContract(Link){
								var hyperlink = $(Link).children("td:last-child").html();
								if(hyperlink == ''){
									var hyperlink = prompt("No Hyperlink. What is the hyperlink? Please enter nothing if you don't have one.");
									$.ajax({
										url:"bin/php/post/add_hyperlink_to_contract.php",
										data:"Link=" + hyperlink,
										method:"POST",
										success:function(){
											$(Link).children("td:last-child").html(hyperlink);
										}
									})
								} else {
									window.open(hyperlink,"_blank");	
								}
							}
							var Table_Sales_Contracts_2 = $('#Table_Sales_Contracts_2').DataTable( {
								"ajax": "bin/php/get/Contracts_by_Customer.php?ID=<?php echo $_GET['ID'];?>",
								"columns": [
									{ "data": "Location"},
									{ "data": "Amount"},
									{ "data": "Billing_Start",render: function(data){return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}},
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
									{ "data": "Billing_Length"},
									{ "data": "Link"}
								],
								"order": [[1, 'asc']],
								"language":{
									"loadingRecords":""
								},
								"initComplete":function(){
								}

							} );

							$("Table#Table_Sales_Contracts_2").on("draw.dt",function(){
								$(this).children("tbody").children("tr").each(function(){
									$(this).on('click',function(){
										hrefContract(this);
									});
								});
							});
							</script>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script>
$(document).ready(function(){
	$("#loading-sub-pills").removeClass("active");
	$("#sales-contracts-pills").addClass('active');
});
</script>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=customer<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>