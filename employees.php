<?php 
session_start( [ 'read_and_close' => true ] );
require('bin/php/index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = $database->query(null,"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array        = sqlsrv_fetch_array($r);
    $User = $database->query(null,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID= ?",array($_SESSION['User']));
    $User         = sqlsrv_fetch_array($User);
    $Field        = ($User['Field'] == 1 && $User['Title'] != 'OFFICE') ? True : False;
    $r            = $database->query($Portal,"
        SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
        FROM   Privilege
        WHERE User_ID = '{$_SESSION['User']}'
    ;");
    $My_Privileges   = array();
    while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
    $Privileged   = FALSE;
    if(isset($My_Privileges['User']) && $My_Privileges['User']['User_Privilege'] >= 4 && $My_Privileges['User']['Group_Privilege'] >= 4 && $My_Privileges['User']['Other_Privilege'] >= 4){$Privileged = TRUE;}
    $database->query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "employees.php"));
    if(!isset($array['ID'])  || !$Privileged){?><html><head><script>document.location.href='../login.php?Forward=directory.php';</script></head></html><?php }
    else {
?><!DOCTYPE html>
<html lang="en">
<head>
    <title>Nouveau Texas | Portal</title>    
    <?php 
        require( bin_meta . 'index.php' );
        require( bin_css  . 'index.php' );
        require( bin_js   . 'index.php' );
    ?>
</head>
<body onload='finishLoadingPage();'>
    <div id='wrapper'>
        <?php require( bin_php . 'element/navigation.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id='page-wrapper' class='content'>
            <div class='panel panel-primary'>
                <div class='panel-heading'><h4><?php \singleton\fontawesome::getInstance( )->User( 1 );?> Employees</h4></div>
                <div class='panel-body'>
                    <table id='Table_Employees' class='display' cellspacing='0' width='100%'>
                        <thead>
                            <th title='ID'>Work ID</th>
                            <th title='First Name'>First Name</th>
                            <th title='Last Name'>Last Name</th>
                            <th title='Supervisor'>Supervisor</th>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src='https://www.nouveauelevator.com/vendor/bootstrap/js/bootstrap.min.js'></script>
    <?php require( bin_js . 'index.php' );?>
    <script src='https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js'></script>
    <script>
        function hrefEmployees(){
            $('#Table_Employees tbody tr').each(function(){
                $(this).on('click',function(){
                    document.location.href='tickets.php?Mechanic=' + $(this).children(':first-child').html();
                });
             });
        }
        $(document).ready(function() {
            var table = $('#Table_Employees').DataTable( {
                ajax : {
                    url     : 'bin/php/get/Employees.php'
                },
                columns : [
                    { 
                        data : ID 
                    },{ 
                        data : 'Last_Name'
                    },{ 
                        data : 'First_Name'
                    },{ 
                        data : 'Supervisor'
                    }
                ],
                order : [ [1, 'asc' ] ],
                language : {
                    loadingRecords : ''
                },
                lengthMenu : [[10,25,50,100,500,-1],[10,25,50,100,500,'All']],
                initComplete : function( ){
                    hrefEmployees();
                    $("input[type='search'][aria-controls='Table_Employees']").on('keyup',function(){hrefEmployees();});       
                    $('#Table_Employees').on( 'page.dt', function () {setTimeout(function(){hrefEmployees();},100);});
                    $('#Table_Employees th').on('click',function(){setTimeout(function(){hrefEmployees();},100);});
                }   

            } );
        } );
    </script>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='login.php?Forward=directory.php';</script></head></html><?php }?>