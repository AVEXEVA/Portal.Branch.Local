<?php
session_start( [ 'read_and_close' => true ] );
require('bin/php/index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = \singleton\database::getInstance( )->query(
    	null,
        " SELECT *
    	    FROM   Connection
    	    WHERE  Connection.Connector = ?
    	    AND    Connection.Hash  = ?
	;",array($_SESSION['User'],$_SESSION['Hash']));
    $Connection = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    $r = \singleton\database::getInstance( )->query(
    	null,
        " SELECT *,
		              Emp.fFirst AS First_Name,
			            Emp.Last   AS Last_Name
          FROM    Emp
    		  WHERE   Emp.ID = ?
	;",array($_SESSION['User']));
    $User = sqlsrv_fetch_array($r);
	$r = \singleton\database::getInstance( )->query(
    null,
      " SELECT *
		    FROM   Privilege
		    WHERE  Privilege.User_ID = ?
	;",array($_SESSION['User']));
	$Privileges = array();
	if($r){while($Privilege = sqlsrv_fetch_array($r)){$Privileges[$Privilege['Access_Table']] = $Privilege;}}
    if(	!isset($Connection['ID'])
	   	|| !isset($Privileges['Admin'])
	  		|| $Privileges['Admin']['User_Privilege']  < 4
	  		|| $Privileges['Admin']['Group_Privilege'] < 4
	  	    || $Privileges['Admin']['Other_Privilege'] < 4){
				?><?php require('../404.html');?><?php }
    else {
      \singleton\database::getInstance( )->query(
      	null,
          "   INSERT INTO Activity([User], [Date], [Page])
			        VALUES(?,?,?)
		;",array($_SESSION['User'],date("Y-m-d H:i:s"), "privileges.php"));
$r = \singleton\database::getInstance( )->query(
  null,
  "   SELECT
              Emp.*,
              Rol.*,
              Emp.Last            AS Last_Name,
              PRWage.Reg          AS Wage_Regular,
              PRWage.OT1          AS Wage_Overtime,
              PRWage.OT2          AS Wage_Double_Time
      FROM
              (Emp
              LEFT JOIN PRWage    ON Emp.WageCat  = PRWage.ID)
              LEFT JOIN Rol       ON Emp.Rol      = Rol.ID
      WHERE
              Emp.ID = '{$_SESSION['User']}'");
while($a= sqlsrv_fetch_array($r)){}
?><!DOCTYPE html>
<html lang="en">
<head>
<?php require( bin_meta . 'index.php');?>
<title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
<?php require( bin_css . 'index.php');?>
<?php require( bin_js . 'index.php');?>
</head>
<body onload=''>
    <div id="wrapper">
        <?php require( bin_php.'php/element/navigation.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id="page-wrapper" class='content'>
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Security Privileges</h1>
                </div>
            </div>
            <div class='row'>
                <div class='col-lg-12'>
                    <button onClick="grantField();">Grant Field Access</button>
                    <button onClick="grantOffice();">Grant Office Access</button>
                    <button onClick="grantDispatch();">Grant Dispatch Access</button>
                </div>
            </div>
            <script>
                function grantField(){$.post('bin/php/post/grantMassField.php', {}).done(function (data) {});}
                function grantOffice(){$.post('bin/php/post/grantMassOffice.php', {}).done(function (data) {});}
                function grantDispatch(){$.post('bin/php/post/grantMassDispatch.php',{}).done(function(data){});}
                </script>
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            Employees
                        </div>
                        <div class="panel-body">
                            <table id='Table_Privileges' class='display' cellspacing='0' width='100%'>
                                <thead>
                                    <th title="Employee Work ID">Work ID</th>
                                    <th title="Employee's First Name">Last Name</th>
                                    <th title="Employee's First Name">First Name</th>
                                    <th title="Employee's Beta Privelege">Beta Access</th>
                                </thead>
                               <tfooter>
                                    <th title="Employee Work ID">Work ID</th>
                                    <th title="Employee's First Name">Last Name</th>
                                    <th title="Employee's First Name">First Name</th>
                                    <th title="Employee's Beta Privelege">Beta Access</th>
                                </tfooter>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <?php require(PROJECT_ROOT.'js/datatables.php');?>


    <script>
        function hrefEmployees(){$("#Table_Privileges tbody tr").each(function(){$(this).on('click',function(){document.location.href="privilege.php?User_ID=" + $(this).children(":first-child").html();});});}
        $(document).ready(function() {
            var table = $('#Table_Privileges').DataTable( {
                "ajax": {
                    "url":"bin/php/get/Privileges.php",
                    "dataSrc":function(json){if(!json.data){json.data = [];}return json.data;}
                },
                "columns": [
                    { "data": "ID"},
                    { "data": "Last_Name"},
                    { "data": "First_Name"},
                    { "data": "Beta"}
                ],
                "order": [[1, 'asc']],
                "language":{
                    "loadingRecords":""
                },
                "initComplete":function(){
                    hrefEmployees();
                    $("input[type='search'][aria-controls='Table_Privileges']").on('keyup',function(){hrefEmployees();});
                    $('#Table_Privileges').on( 'page.dt', function () {setTimeout(function(){hrefEmployees();},100);});
                    $("#Table_Privileges th").on("click",function(){setTimeout(function(){hrefEmployees();},100);});
                    finishLoadingPage();
                }

            } );
        } );
    </script>
</body>
</html>
 <?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=profile.php';</script></head></html><?php }?>
