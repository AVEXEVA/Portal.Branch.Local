 <?php 
session_start();
require('cgi-bin/php/index.php');
$serverName = "172.16.12.45";
$connectionOptions = array(
    "Database" => "nei",
    "Uid" => "sa",
    "PWD" => "SQLABC!23456",
    'ReturnDatesAsStrings'=>true
);
//Establishes the connection
$conn = sqlsrv_connect($serverName, $connectionOptions);
$connectionOptions['Database'] = 'Portal';
$conn2 = sqlsrv_connect($serverName, $connectionOptions);
if(isset($_SESSION['User'],$_SESSION['Hash'])){

    $r = sqlsrv_query($conn,"SELECT * FROM Connection WHERE Connector = ? AND Hash= ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    $User = sqlsrv_query($conn,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID= ?",array($_SESSION['User']));
    $User = sqlsrv_fetch_array($User);
    $Field = ($User['Field'] == 1 && $User['Title'] != 'OFFICE') ? True : False;
    $r = sqlsrv_query($conn2,"
            SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
            FROM   Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
    $My_Privileges = array();
    while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
    $Privileged = FALSE;
    if(isset($My_Privileges['Ticket']) && $My_Privileges['Ticket']['User_Privilege'] >= 4 && $My_Privileges['Ticket']['Group_Privilege'] >= 4 && $My_Privileges['Ticket']['Other_Privilege'] >= 4){$Privileged = TRUE;}
    sqlsrv_query($conn2,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "activity.php"));
    if(!isset($array['ID'])  || !$Privileged){?><html><head><script>document.location.href='../login.php?Forward=dispatch.php';</script></head></html><?php }
    else {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php require(PROJECT_ROOT.'php/meta.php');?>
    <title>Nouveau Texas | Portal</title>    <?php require(PROJECT_ROOT."css/index.php");?>
    <?php require(PROJECT_ROOT.'js/index.php');?>
</head>
<body onload=''>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require(PROJECT_ROOT.'html/navigation.php');?>
        <?php require(PROJECT_ROOT.'php/element/loading.php');?>
        <div id="page-wrapper" class='content'>
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-primary">
                        <div class="panel-heading"><h3>Dispatch<div style='float:right'><button onClick='refresh_get();' style='color:black;'>Refresh</button></div></h3></div>
                        <div class="panel-heading" style='background-color:white;color:black;'>
                            <div class='row'>
                                <div class='col-xs-4'>
                                    <div class='row'>
                                        <div class='col-xs-4' style='text-align:right;'> 
                                            <label for='Supers' style='text-align:right;'>Departments(s):</label>
                                        </div>
                                        <div class='col-xs-8'>
                                            <?php $Supervisors = (isset($_GET['Supervisors'])) ? (strpos($_GET['Supervisors'], ',') !== false) ? split(',',$_GET['Supervisors']) : array($_GET['Supervisors']) : array();?>
                                            <select id='Departments' name='Departments' multiple='multiple' size='7' style='max-width:100%;'>
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
                                    </tr>
                                </tfooter>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class='row'>
                <div class='col-md-12'>
                    <div id="map" style='height:500px;overflow:visible;width:100%;'></div>
                </div>
            </div>
    </div>

    <!-- Bootstrap Core JavaScript -->
    <script src="https://www.nouveauelevator.com/vendor/bootstrap/js/bootstrap.min.js"></script>

    <!-- Metis Menu Plugin JavaScript -->
    <script src="https://www.nouveauelevator.com/vendor/metisMenu/metisMenu.js"></script>

    <!-- Custom Theme JavaScript -->
    <script src="../dist/js/sb-admin-2.js"></script>

    <?php require(PROJECT_ROOT.'js/datatables.php');?>
    <script src="cgi-bin/js/jquery.dataTables.yadcf.js"></script>

    <!--Moment JS Date Formatter-->
    <script src="../dist/js/moment.js"></script>
    <style>
    </style>
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
                "ajax":"cgi-bin/php/get/Dispatch.php?Supervisors=" + $("select[name='Departments']").val() + '&Mechanics=' + $("select[name='Mechanics']").val() + "&Start_Date=" + $("input[name='filter_start_date']").val() + "&End_Date=" + $("input[name='filter_end_date']").val(),
                "columns": [
                    {"data" : "ID"},
                    {"data" : "fFirst"},
                    {"data" : "Last"},
                    {"data" : "Tag"},
                    {"data" : "fDesc"},
                    {"data" : "Status"},
                    {"data" : "EDate"}
                ],
                "scrollX":true,
                "lengthMenu":[[10,25,50,100,500,-1],[10,25,50,100,500,"All"]],
                "initComplete":function(){
                    $("tr[role='row']>th:nth-child(5)").click().click();
                    hrefTickets();
                    $("input[type='search'][aria-controls='Table_Tickets']").on('keyup',function(){hrefTickets();});       
                    $('#Table_Tickets').on( 'page.dt', function () {setTimeout(function(){hrefTickets();},100);});
                    $("#Table_Tickets th").on("click",function(){setTimeout(function(){hrefTickets();},100);});
                    finishLoadingPage();
                }
            });
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
    <script src="../dist/js/filters.js"></script>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='login.php?Forward=dispatch.php';</script></head></html><?php }?>