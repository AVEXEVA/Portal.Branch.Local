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
        $Field = ($User['Field'] == 1 && $User['Title'] != 'OFFICE') ? True : False;
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
<div class="tab-pane fade in active" id="units-pills">
    <table id='Table_Units' class='display' cellspacing='0' width='100%'>
        <thead>
            <th title="Unit's ID">ID</th>
            <th title='Unit State ID'>State</th>
            <th title="Unit's Label">Unit</th>
            <th title="Type of Unit">Type</th>
            <th title="Unit's Location">Location</th>
        </thead>
       <tfooter><th title="Unit's ID">ID</th><th title='Unit State ID'>State</th><th title="Unit's Label">Unit</th><th title="Type of Unit">Type</th><th title="Unit's Location">Location</th></tfooter>
    </table>
    <div class="col-lg-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                Unit Type
            </div>
            <!-- /.panel-heading -->
            <div class="panel-body">
                <div class="flot-chart">
                    <div class="flot-chart-content" id="flot-pie-chart-units-type"></div>
                </div>
            </div>
            <!-- /.panel-body -->
        </div>
        <!-- /.panel -->
    </div>
</div>
<?php require(PROJECT_ROOT.'js/pie/type_by_unit_for_customer.php');?>
<script>
function hrefUnits(){hrefRow("Table_Units","unit");}
$(document).ready(function(){
    var Table_Units = $('#Table_Units').DataTable( {
        "ajax": "cgi-bin/php/get/Units_by_Customer.php?ID=<?php echo $_GET['ID'];?>",
        "columns": [
            { "data": "ID" },
            { "data": "State"},
            { "data": "Unit"},
            { "data": "Type"},
            { "data": "Location"}
        ],
        "order": [[1, 'asc']],
        "language":{
            "loadingRecords":""
        },
        "initComplete":function(){
        }
    } );
	$("Table#Table_Units").on("draw.dt",function(){hrefUnits();});
    yadcf.init(Table_Units,[
        {   column_number:0,
            filter_type:"auto_complete"},
        {   column_number:1,
            filter_type:"auto_complete"},
        {   column_number:2,
            filter_type:"auto_complete"},
        {   column_number:3},
        {   column_number:4}
    ]);
});
</script>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=customer<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>