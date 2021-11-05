<?php 
session_start( [ 'read_and_close' => true ] );
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
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $User = addslashes($_SESSION['User']);
    $Hash = addslashes($_SESSION['Hash']);
    $r = sqlsrv_query($conn,"SELECT * FROM Connection WHERE Connector='{$User}' AND Hash='{$Hash}'");
    $array = sqlsrv_fetch_array($r);
    $User = sqlsrv_query($conn,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID='{$User}'");
    $User = sqlsrv_fetch_array($User);
    $Field = ($User['Field'] == 1 && $User['Title'] != 'OFFICE') ? True : False;
    sqlsrv_query($conn2,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "directory.php"));
    if(!isset($array['ID']) || $Field ){?><html><head><script>document.location.href='../login.php?Forward=directory.php';</script></head></html><?php }
    else {
?><!DOCTYPE html>
<html lang="en">
<head>
    <?php require( bin_meta . 'index.php');?>
    <title>Nouveau Texas | Portal</title>
    <!-- CSS -->
    <?php require( bin_css . 'index.php');?>
    <!-- Portal Javascript-->
    <?php require( bin_js . 'index.php');?>
</head>
<body onload=''>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require(PROJECT_ROOT.'html/navigation.php');?>
        <?php require(PROJECT_ROOT.'php/element/loading.php');?>
        <div id="page-wrapper" class='content'>
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Employees Dashboard</h1>
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            Employees
                        </div>
                        <div class="panel-body">
                            <table id='Employees_Table' class='display' cellspacing='0' width='100%'>
                                <thead>
                                    <th title="Employee Work ID">Work ID</th>
                                    <th title="Employee's First Name">First Name</th>
                                    <th title="Employee's First Name">Last Name</th>
                                    <th title="Employee's Supervisor">Supervisor</th>
                                </thead>
                               <tfooter>
                                    <th title="Employee Work ID">Work ID</th>
                                    <th title="Employee's First Name">First Name</th>
                                    <th title="Employee's First Name">Last Name</th>
                                    <th title="Employee's Supervisor">Supervisor</th>
                                </tfooter>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        <!-- /#page-wrapper -->
        </div>
    </div>
    <!-- /#wrapper -->

    <!-- jQuery -->
    <script src="https://www.nouveauelevator.com/vendor/jquery/jquery.min.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="https://www.nouveauelevator.com/vendor/bootstrap/js/bootstrap.min.js"></script>

    <!-- Metis Menu Plugin JavaScript -->
    <script src="https://www.nouveauelevator.com/vendor/metisMenu/metisMenu.js"></script>

    <!-- DataTables JavaScript -->
    <script src="https://www.nouveauelevator.com/vendor/datatables/js/jquery.dataTables.min.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/datatables-plugins/dataTables.bootstrap.min.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/datatables-responsive/dataTables.responsive.js"></script>
    <!-- Custom Theme JavaScript -->
    <script src="../dist/js/sb-admin-2.js"></script>

    <!--Moment JS Date Formatter-->
    <script src="../dist/js/moment.js"></script>

    <!-- JQUERY UI Javascript -->
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

    <!-- Custom Date Filters-->
    <script src="../dist/js/filters.js"></script>
    <script>
        function hrefEmployees(){
            $("#Employees_Table tbody tr").each(function(){
                $(this).on('click',function(){
                    document.location.href="tickets.php?Mechanic=" + $(this).children(":first-child").html();
                });
             });
        }
        $(document).ready(function() {
            var table = $('#Employees_Table').DataTable( {
                "ajax": {
                    "url":"cgi-bin/php/get/Employees.php",
                    "dataSrc":function(json){
                        if(!json.data){
                            json.data = [];
                        }
                        return json.data;
                    }
                },
                "columns": [
                    { "data" : "ID"},
                    { "data" : "Last_Name"},
                    { "data" : "First_Name"},
                    { "data" : "Supervisor"}
                ],
                "order": [[1, 'asc']],
                "language":{
                    "loadingRecords":""
                },
                "initComplete":function(){
                    hrefEmployees();
                    $("input[type='search'][aria-controls='Employees_Table']").on('keyup',function(){hrefEmployees();});       
                    $('#Employees_Table').on( 'page.dt', function () {setTimeout(function(){hrefEmployees();},100);});
                    $("#Employees_Table th").on("click",function(){setTimeout(function(){hrefEmployees();},100);});
                    finishLoadingPage();
                }   

            } );
        } );
    </script>
</body>

</html>
<?php
    }
} else {?><html><head><script>document.location.href='login.php?Forward=directory.php';</script></head></html><?php }?>