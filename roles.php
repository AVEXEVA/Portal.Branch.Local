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
	   	|| !isset($My_Privileges['Admin']) 
	  		|| $My_Privileges['Admin']['User_Privilege']  < 4
	  		|| $My_Privileges['Admin']['Group_Privilege'] < 4
	  	    || $My_Privileges['Admin']['Other_Privilege'] < 4){
				?><?php require('../404.html');?><?php }
    else {
		$database->query(null,"
			INSERT INTO Portal.dbo.Activity([User], [Date], [Page]) 
			VALUES(?,?,?)
		;",array($_SESSION['User'],date("Y-m-d H:i:s"), "privileges.php"));
$r = $database->query(null,"
    SELECT 
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
/*$User = sqlsrv_fetch_array($r);
$Call_Sign = $array['CallSign'];
$Alias = $array['fFirst'][0] . $array['Last'];
$Employee_ID = $array['fWork'];*/
while($a= sqlsrv_fetch_array($r)){}
//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\
//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\
//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\//\\
?><!DOCTYPE html>
<html lang="en">
<head>
    <?php require( bin_meta . 'index.php');?>    
    <title>Nouveau Texas | Portal</title>    
    <?php require( bin_css . 'index.php');?>
    <?php require( bin_js . 'index.php');?>
</head>
<body onload='finishLoadingPage();'>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require( bin_php . 'element/navigation/index.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id="page-wrapper" class='content'>
            <div class='panel panel-primary'>
                <div class='panel-heading'><h1>Security Privileges</h1></div>
                <div class='panel-body'>
                    <table id='Table_Roles' class='display' cellspacing='0' width='100%'>
                        <thead>
                            <th>ID</th>
                            <th>Name</th>
                        </thead>
                    </table>
                </div>
            </div> 
        </div>
    </div>
    
    
    <?php require(PROJECT_ROOT.'js/datatables.php');?>
    
    
    <script>
        function hrefRoles(){$("#Table_Roles tbody tr").each(function(){$(this).on('click',function(){document.location.href="role.php?User_ID=" + $(this).children(":first-child").html();});});}
        $(document).ready(function() {
            var table_roles = $('#Table_Roles').DataTable( {
                "ajax": {
                    "url":"bin/php/get/Roles.php",
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
                    hrefRoles();
                    $("input[type='search'][aria-controls='Table_Roles']").on('keyup',function(){hrefRoles();});       
                    $('#Table_Roles').on( 'page.dt', function () {setTimeout(function(){hrefRoles();},100);});
                    $("#Table_Roles th").on("click",function(){setTimeout(function(){hrefRoles();},100);});
                    
                }   

            } );
        } );
    </script>
</body>
</html>
 <?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=profile.php';</script></head></html><?php }?>
