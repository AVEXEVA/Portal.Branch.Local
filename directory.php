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
if(isset($_SESSION[ 'User' ],$_SESSION[ 'Hash' ])){
    $User = addslashes($_SESSION[ 'User' ]);
    $Hash = addslashes($_SESSION[ 'Hash' ]);
    $r = sqlsrv_query(
      $conn,
      "   SELECT  *
          FROM Connection
          WHERE Connector='{$User}'
          AND Hash='{$Hash}'");
    $array = sqlsrv_fetch_array($r);
    $User = sqlsrv_query(
      $conn,
      "   SELECT    *, fFirst AS First_Name, Last as Last_Name
          FROM Emp WHERE ID='{$User}'");
    $User = sqlsrv_fetch_array($User);
    $Field = ($User[ 'Field' ] == 1 && $User[ 'Title' ] != 'OFFICE') ? True : False;
    sqlsrv_query(
      $conn2,
      "   INSERT INTO Activity([User], [Date], [Page])
          VALUES(?,?,?);",
    array(
      $_SESSION[ 'User' ],
          date("Y-m-d H:i:s"),
              "directory.php")
    );
    if(!isset($array[ 'ID' ]) || $Field ){?><html><head><script>document.location.href='../login.php?Forward=directory.php';</script></head></html><?php }
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
    <div id="wrapper" class="<?php echo isset($_SESSION[ 'Toggle_Menu' ]) ? $_SESSION[ 'Toggle_Menu' ] : null;?>">
        <?php require(bin_php.'html/navigation.php');?>
        <?php require(bin_php.'php/element/loading.php');?>
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
        </div>
    </div>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='login.php?Forward=directory.php';</script></head></html><?php }?>
