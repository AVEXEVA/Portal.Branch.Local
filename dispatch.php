<?php 
session_start( [ 'read_and_close' => true ] );
require('bin/php/index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = $database->query(null,"
		SELECT * 
		FROM   Connection 
		WHERE  Connection.Connector = ? 
		       AND Connection.Hash  = ?
	;",array($_SESSION['User'],$_SESSION['Hash']));
    $My_Connection = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    $r = $database->query(null,"
		SELECT *,
		       Emp.fFirst AS First_Name,
			   Emp.Last   AS Last_Name
		FROM   Emp 
		WHERE  Emp.ID = ?
	;",array($_SESSION['User']));
    $My_User = sqlsrv_fetch_array($r);
	$r = $database->query(null,"
		SELECT * 
		FROM   Privilege 
		WHERE  Privilege.User_ID = ?
	;",array($_SESSION['User']));
	$My_Privileges = array();
	if($r){while($My_Privilege = sqlsrv_fetch_array($r)){$My_Privileges[$My_Privilege['Access_Table']] = $My_Privilege;}}
    if(	!isset($My_Connection['ID']) 
	   	|| !isset($My_Privileges['Ticket'])
	  		|| $My_Privileges['Ticket']['User_Privilege']  < 4
	  		|| $My_Privileges['Ticket']['Group_Privilege'] < 4
	  		|| $My_Privileges['Ticket']['Other_Privilege'] < 4){
				?><?php require('../404.html');?><?php }
    else {
		$database->query(null,"
			INSERT INTO Portal.dbo.Activity([User], [Date], [Page]) 
			VALUES(?,?,?)
		;",array($_SESSION['User'],date("Y-m-d H:i:s"), "dispatch.php"));
if(isset($_GET['Supervisors'])){$Selected_Supervisors = explode(',',$_GET['Supervisors']);}
if((isset($_GET['Supervisors']) && count($Selected_Supervisors) == 0) || !isset($_GET['Supervisors']) || $_GET['Supervisors'] == '' || $_GET['Supervisors'] == 'All'){$SQL_Supervisors = "'1' = '1'";}
else {
    $SQL_Supervisors = "";
    $Supervisors_SQL = array();
    foreach($Selected_Supervisors as $key=>$Selected_Supervisor){$Supervisors_SQL[$key] = "tblWork.Super = '" . $Selected_Supervisor . "'";}
    $SQL_Supervisors = "(" . implode(" OR ",$Supervisors_SQL) . ")"; 
}
if(isset($_GET['Mechanics'])){
$Selected_Mechanics = explode(",",$_GET['Mechanics']);
}
if((isset($_GET['Mechanics']) && count($Selected_Mechanics) == 0) || !isset($_GET['Mechanics']) || $_GET['Mechanics'] == '' || $_GET['Mechanics'] == null){$SQL_Selected_Mechanics = "'1' = '1'";}
else {
    $SQL_Selected_Mechanics = "";
    $Selected_Mechanics_SQL = array();
    foreach($Selected_Mechanics as $key=>$Selected_Mechanic){$Selected_Mechanics_SQL[$key] = "TicketO.fWork = '" . $Selected_Mechanic . "'";}
    $SQL_Selected_Mechanics = "(" . implode(" OR ",$Selected_Mechanics_SQL) . ")";
}
$r = $database->query(null,"select Emp.*, tblWork.Super from Emp LEFT JOIN nei.dbo.tblWork ON 'A' + convert(varchar(10),Emp.ID) + ',' = tblWork.Members where Field='1' AND Emp.Status='0';");
$Mechanics = array();
while($array = sqlsrv_fetch_array($r)){$Mechanics[] = $array;}

//GET TICKETS
if(isset($_GET['Start_Date']) && $_GET['Start_Date'] > 0){$Start_Date = DateTime::createFromFormat('m/d/Y', $_GET['Start_Date'])->format("Y-m-d 00:00:00.000");}
else{
    $Start_Date = new DateTime('now');
    $Start_Date = $Start_Date->format("Y-m-d 00:00:00.000");}

if(isset($_GET['End_Date']) && $_GET['End_Date'] > 0){$End_Date = DateTime::createFromFormat('m/d/Y', $_GET['End_Date'])->format("Y-m-d 23:59:59.999");}
else{
    $End_Date = new DateTime('now');
    $End_Date = $End_Date->format("Y-m-d 23:59:59.999");}

if(!isset($_GET['Location_Tag']) || $_GET['Location_Tag'] == "All" || $_GET['Location_Tag'] == ""){$Location_Tag = "' OR '1'='1";}
else {$Location_Tag = addslashes($_GET['Location_Tag']);}

if(!isset($_GET['Status']) || $_GET['Status'] == 'All' || $_GET['Status'] == ""){$Status = "' OR '1'='1";}
else{$Status = $_GET['Status'];}

if($End_Date < date('Y-m-d 00:00:00.000')){$Closeout = " AND TickOStatus.Type='Completed'";}
else {$Clouseout = '';}?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php require( bin_meta . 'index.php');?>    
    <title>Nouveau Texas | Portal</title>    
    <?php require( bin_css . 'index.php');?>
    <?php require( bin_js . 'index.php');?>
</head>
<body onload=''>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require(PROJECT_ROOT.'php/element/navigation/index.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id="page-wrapper" class='content'>
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-primary">
                        <div class="panel-heading"><h3>Dispatch<div style='float:right'><button onClick='refresh_get();' style='color:black;'>Refresh</button></div></h3></div>
                        <div class="panel-heading" style='background-color:white;color:black;'>
                           
							
                                    <div class='row'>
                                        <div class='col-xs-6' style='text-align:right;'> 
                                            <label for='Supers' style='text-align:right;'>Departments(s):</label>
                                        </div>
                                        <div class='col-xs-6'>
                                            <?php $Supervisors = (isset($_GET['Supervisors'])) ? (strpos($_GET['Supervisors'], ',') !== false) ? split(',',$_GET['Supervisors']) : array($_GET['Supervisors']) : array();?>
                                            <select id='Departments' name='Departments' multiple='multiple' size='8' style='max-width:200%;'>
                                                <?php 
                                                if(!is_array($Supervisors)){$Supervisors = array($Supervisors);}?>
                                                <option value='Division 1' <?php if(in_array('Division 1',$Supervisors) || !isset($_GET['Supervisors']) || $_GET['Supervisors'] == 'undefined'){?>selected='selected'<?php }?>>Division 1</option>
                                                <option value='Division 2' <?php if(in_array('Division 2',$Supervisors)){?>selected='selected'<?php }?>>Division 2</option>
                                                <option value='Division 3' <?php if(in_array('Division 3',$Supervisors)){?>selected='selected'<?php }?>>Division 3</option>
                                                <option value='Division 4' <?php if(in_array('Division 4',$Supervisors)){?>selected='selected'<?php }?>>Division 4</option>
                                                <option value='Modernization' <?php if(in_array('Modernization',$Supervisors)){?>selected='selected'<?php }?>>Modernization</option>
                                                <option value='Repair' <?php if(in_array('Repair',$Supervisors)){?>selected='selected'<?php }?>>Repair</option>
                                                <option value='Escalator' <?php if(in_array('Escalator',$Supervisors)){?>selected='selected'<?php }?>>Escalator</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class='col-md-4'>
                                    <div class='row'>
                                        <div class='col-xs-6'>
                                            <label class='date' for="filter_start_date">Start Date:</label>
                                        </div>
                                        <div class='col-xs-6'>
                                            <input class='start_date' size='10' name='filter_start_date' value='<?php echo DateTime::createFromFormat('Y-m-d H:i:s.000', $Start_Date)->format("m/d/Y");?>' />
                                        </div>
                                    </div>
                                    <div class='row'>
                                        <div class='col-xs-6'>
                                            <label class='date' for="filter_end_date">End Date:</label>
                                        </div>
                                        <div class='col-xs-6'>
                                            <input class='end_date' size='10' name='filter_end_date'  value='<?php echo DateTime::createFromFormat('Y-m-d H:i:s.999', $End_Date)->format("m/d/Y");?>'/><br />
                                        </div>
                                    </div>
                                    <div class='row'>
                                        <div class='col-xs-4'>
                                            <label class='tag' for='filter_today'>Quick Dates</label>
                                        </div>
                                        <div class='col-xs-8'><?php require(PROJECT_ROOT.'php/element/button/dates.php');?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="panel-body">
                            <table width="100%" class="table table-striped table-bordered table-hover" id="Table_Tickets">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>First Name</th>
                                        <th>Last Name</th>
                                        <th>Location</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                        <th>Scheduled</th>
                                        <th>Hours</th>
                                    </tr>
                                </thead>
                                <tfooter>
                                    <tr>
                                        <th>ID</th>
                                        <th>First Name</th>
                                        <th>Last Name</th>
                                        <th>Location</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                        <th>Scheduled</th>
                                        <th>Hours</th>
                                    </tr>
                                </tfooter>
                            </table>
                        </div>
                    </div>
               
            <div class='row'>
                <div class='col-md-12'>
                    <div id="map" style='height:500px;overflow:visible;width:100%;'></div>
                </div>
            </div>
    </div>
    
    
    
    <?php require('bin/js/datatables.php');?>
    
	
    <script>  
        function hrefTickets(){
            $("#Table_Tickets tbody tr").each(function(){
                $(this).on('click',function(){
                    document.location.href="ticket.php?ID=" + $(this).children(":first-child").html();
                });
             });
        } 
        $(document).ready(function() {
            var Table_Tickets = $('#Table_Tickets').DataTable({
                "responsive": true,
                "ajax":"bin/php/get/Dispatch.php?Supervisors=" + $("select[name='Departments']").val() + '&Mechanics=' + $("select[name='Mechanics']").val() + "&Start_Date=" + $("input[name='filter_start_date']").val() + "&End_Date=" + $("input[name='filter_end_date']").val(),
                "columns": [
                    {"data" : "ID"},
                    {"data" : "fFirst"},
                    {"data" : "Last"},
                    {"data" : "Tag"},
                    {"data" : "fDesc"},
                    {"data" : "Status"},
                    {"data" : "EDate"},
                    { 
                        "data": "Total",
                        "defaultContent":"0"
                    }
                ],
                "scrollX":true,
                "lengthMenu":[[10,25,50,100,500,-1],[10,25,50,100,500,"All"]],
                "initComplete":function(){
                    finishLoadingPage();
                }
            });
            $("Table#Table_Tickets").on("draw.dt",function(){hrefTickets();});
            <?php if(!$Mobile){?>
            yadcf.init(Table_Tickets,[
                {   column_number:0,
                    filter_type:"auto_complete"},
                {   column_number:1},
                {   column_number:2},
                {   column_number:3},
                {   column_number:4,
                    filter_type: "auto_complete"},
                {   column_number:5},
                {   column_number:6,
                    filter_type: "range_date",
                    date_format: "mm/dd/yyyy",
                    filter_delay: 500},
                {   column_number:7,
                    filter_type: "range_number_slider",
                    filter_delay: 500}
            ]);
            stylizeYADCF();<?php }?>
        });
    </script>
    <script>
        function refresh_get(){
            var Supervisors = $("select[name='Departments']").val();
            var Mechanics = $("select[name='Mechanics']").val();
            var Start_Date = $("input[name='filter_start_date']").val();
            var End_Date = $("input[name='filter_end_date']").val();
            document.location.href='dispatch.php?Supervisors=' + Supervisors + '&Mechanics=' + Mechanics + "&Start_Date=" + Start_Date + "&End_Date=" + End_Date;
        }
    </script>
    <script>       
        $(document).ready(function(){
            $("input.start_date").datepicker({onSelect:function(dateText, inst){refresh_get();}});
            $("input.end_date").datepicker({onSelect:function(dateText, inst){refresh_get();}});
            $("#Mechanics").html($("#Mechanics option").sort(function (a, b) {return a.text == b.text ? 0 : a.text < b.text ? -1 : 1}));
            $("#Departments").html($("#Departments option").sort(function (a, b) {return a.text == b.text ? 0 : a.text < b.text ? -1 : 1}));
        });
    </script>

    <!-- Filters-->
    
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='login.php?Forward=dispatch.php';</script></head></html><?php }?>