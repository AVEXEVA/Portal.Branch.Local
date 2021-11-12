<?php
session_start( [ 'read_and_close' => true ] );
require('bin/php/index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = \singleton\database::getInstance( )->query(
    	null,
      " SELECT  *
        FROM  Connection
        WHERE Connector = ?
        AND   Hash = ?;",
      array($_SESSION['User'],$_SESSION['Hash']));
    $array        = sqlsrv_fetch_array($r);
    $User = \singleton\database::getInstance( )->query(
    	null,
      " SELECT *, fFirst
        AS        First_Name, Last as Last_Name
        FROM      Emp
        WHERE     ID= ?",
     array($_SESSION['User']));
    $User         = sqlsrv_fetch_array($User);
    $Field        = ($User['Field'] == 1 && $User['Title'] != 'OFFICE') ? True : False;
    $r            = \singleton\database::getInstance( )->query(
    	null,
      " SELECT  Access_Table,
                User_Privilege,
                Group_Privilege,
                Other_Privilege
        FROM    Privilege
        WHERE   User_ID = '{$_SESSION['User']}'
    ;");
    $Privileges   = array();
    while($array2 = sqlsrv_fetch_array($r)){$Privileges[$array2['Access_Table']] = $array2;}
    $Privileged   = FALSE;
    if(isset($Privileges['User'])
    && $Privileges['User']['User_Privilege'] >= 4
    && $Privileges['User']['Group_Privilege'] >= 4
    && $Privileges['User']['Other_Privilege'] >= 4){$Privileged = TRUE;}
    $database->\singleton\database::getInstance( )->query(
    	null,
      "   INSERT INTO Activity([User], [Date], [Page])
          VALUES(?,?,?);",
    array($_SESSION['User'],
    date      ("Y-m-d H:i:s"),
               "employees.php"));
    if(!isset($array['ID'])  || !$Privileged){?><html><head><script>document.location.href='../login.php?Forward=directory.php';</script></head></html><?php }
    else {
?><!DOCTYPE html>
<html lang="en">
<head>
    <title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
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
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='login.php?Forward=directory.php';</script></head></html><?php }?>
