<?php
session_start( [ 'read_and_close' => true ] );
require('bin/php/index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = \singleton\database::getInstance( )->query(
      null,
      "  SELECT *
  		   FROM   Connection
  		   WHERE  Connection.Connector = ?
  		   AND    Connection.Hash  = ?
	;",array($_SESSION['User'],$_SESSION['Hash']));
    $My_Connection = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    $r = $database->query(null,
      " SELECT *,
    		       Emp.fFirst AS First_Name,
    			     Emp.Last   AS Last_Name
    		FROM   Emp
    		WHERE  Emp.ID = ?
	;",array($_SESSION['User']));
    $My_User = sqlsrv_fetch_array($r);
	$r = \singleton\database::getInstance( )->query(
    null,
    " SELECT *
		  FROM   Privilege
		  WHERE  Privilege.User_ID = ?
	;",array($_SESSION['User']));
	$My_Privileges = array();
	if($r){while($My_Privilege = sqlsrv_fetch_array($r)){$My_Privileges[$My_Privilege['Access_Table']] = $My_Privilege;}}
    if(	!isset($My_Connection['ID'])
	   	|| !isset($My_Privileges['Legal'])
	  		|| $My_Privileges['Legal']['User_Privilege']  < 4
	  		|| $My_Privileges['Legal']['Group_Privilege'] < 4
	  	    || $My_Privileges['Legal']['Other_Privilege'] < 4){
				?><?php require('../404.html');?><?php }
    else {
		$database->query(null,
      " INSERT INTO Activity([User], [Date], [Page])
			  VALUES(?,?,?)
		;",array($_SESSION['User'],date("Y-m-d H:i:s"), "legal.php"));
?><!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
    <?php require( bin_css . 'index.php');?>
    <?php require( bin_js . 'index.php');?>
</head>
<body>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require( bin_php . 'element/navigation.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id="page-wrapper" class='content'>
            <div class="col-lg-12">
                    <div class="panel panel-primary">
                        <div class="panel-heading"><h3>Legal</h3></div>
                        <div class="panel-body">
                            <table id='Table_Legal' class='display' cellspacing='0' width='100%'>
                                <thead>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Location</th>
                                    <th>Type</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </thead>
                               <tfooter>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Location</th>
                                    <th>Type</th>
                                    <th>Date</th>
                                    <th>Status</th>
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
} else {?><html><head><script>document.location.href='../login.php?Forward=jobs.php';</script></head></html><?php }?>
