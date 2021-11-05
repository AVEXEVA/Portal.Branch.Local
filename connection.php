<?php 
session_start( [ 'read_and_close' => true ] );
require('cgi-bin/php/index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"
		SELECT * 
		FROM   Connection 
		WHERE  Connection.Connector = ? 
		       AND Connection.Hash  = ?
	;",array($_SESSION['User'],$_SESSION['Hash']));
    $My_Connection = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    $r = sqlsrv_query($NEI,"
		SELECT *,
		       Emp.fFirst AS First_Name,
			   Emp.Last   AS Last_Name
		FROM   Emp 
		WHERE  Emp.ID = ?
	;",array($_SESSION['User']));
    $My_User = sqlsrv_fetch_array($r);
	$r = sqlsrv_query($NEI,"
		SELECT * 
		FROM   Privilege 
		WHERE  Privilege.User_ID = ?
	;",array($_SESSION['User']));
	$My_Privileges = array();
	if($r){while($My_Privilege = sqlsrv_fetch_array($r)){$My_Privileges[$My_Privilege['Access_Table']] = $My_Privilege;}}
    if(	!isset($My_Connection['ID']) 
	   	|| !isset($_GET['ID'])
	        || !is_numeric($_GET['ID'])
	   	|| !isset($My_Privileges['Admin'])
	  		|| $My_Privileges['Admin']['User_Privilege']  < 4
	  		|| $My_Privileges['Admin']['Group_Privilege'] < 4
	  		|| $My_Privileges['Admin']['Other_Privilege'] < 4){
				?><?php require('../404.html');?><?php }
    else {
		sqlsrv_query($NEI,"
			INSERT INTO Portal.dbo.Activity([User], [Date], [Page]) 
			VALUES(?,?,?)
		;",array($_SESSION['User'],date("Y-m-d H:i:s"), "connection.php?ID=" . $_GET['ID']));
?><!DOCTYPE html>
<html lang="en">
<head>
    <?php require( bin_meta . 'index.php');?>    
    <title>Nouveau Texas | Portal</title>    
    <?php require( bin_css . 'index.php');?>
    <?php require( bin_js . 'index.php');?>
</head>
<body onload=''>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require( bin_php . 'element/navigation/index.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id="page-wrapper" class='content'>
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-primary">
                        <div class="panel-heading"><h3><?php \singleton\fontawesome::getInstance( )->Connection();?>Connections</h3></div>
                        <div class="panel-body">
                            <table id='Table_Connections' class='display' cellspacing='0' width='100%'>
                                <thead>
                                    <th>ID</th>
                                    <th>Page</th>
                                    <th>Timestamp</th>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    
    <?php require(PROJECT_ROOT.'js/datatables.php');?>
    
    
    <script>
        $(document).ready(function() {
            var Table_Connections = $('#Table_Connections').DataTable( {
                "ajax": {
                    "url":"cgi-bin/php/get/Connection.php?ID=<?php echo $_GET['ID'];?>",
                    "dataSrc":function(json){if(!json.data){json.data = [];}return json.data;}
                },
                "columns": [
                    { "data": "ID"},
                    { "data": "Page"},
                    { "data": "Date"}
                ],
                "order": [[1, 'asc']],
                "language":{
                    "loadingRecords":""
                },
                "lengthMenu":[[10,25,50,100,500,-1],[10,25,50,100,500,"All"]],
                "initComplete":function(){
                    $("tr[role='row']>th:nth-child(3)").click().click();
                    finishLoadingPage();
                }   

            } );
        } );
    </script>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=job<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>