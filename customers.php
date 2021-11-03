<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( );
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/cgi-bin/php/index.php' );
}
if(isset($_SESSION[ 'User'],$_SESSION[ 'Hash' ])){
    //Connection
    $result = sqlsrv_query(
        $NEI,
        "   SELECT  *
            FROM    Connection
            WHERE       Connection.Connector = ?
                    AND Connection.Hash  = ?;",
        array(
            $_SESSION[ 'User' ],
            $_SESSION[ 'Hash' ]
        )
    );
    $Connection = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC);
    //User
    $result = sqlsrv_query($NEI,
       "   SELECT *,
                  Emp.fFirst AS First_Name,
                  Emp.Last   AS Last_Name
           FROM   Emp
           WHERE  Emp.ID = ?
    ;",array($_SESSION[ 'User' ]
        )
    );
    $User = sqlsrv_fetch_array($result);
    //Privileges
    $result = sqlsrv_query($NEI,
     "   SELECT *
         FROM   Privilege
         WHERE  Privilege.User_ID = ?
    ;",array($_SESSION[ 'User' ]
        )
    );
    $Privileges = array();
    if($result){while($Privilege = sqlsrv_fetch_array($result)){$Privileges[$Privilege[ 'Access_Table' ]] = $Privilege;}}
    //SecurityWall
    if( !isset($Connection[ 'ID' ])
        || !isset($Privileges[ 'Customer' ])
            || $Privileges[ 'Customer' ][ 'User_Privilege' ]  < 4
            || $Privileges[ 'Customer' ][ 'Group_Privilege' ] < 4
            || $Privileges[ 'Customer' ][ 'Other_Privilege' ] < 4){
                ?><?php require('../404.html');?><?php }
    else {
        sqlsrv_query(
          $NEI,
          " INSERT INTO Activity([User], [Date], [Page] ) VALUES( ?, ?, ? );",
          array(
            $_SESSION['User'],
            date("Y-m-d H:i:s"),
            "customers.php"
        )
      );
?><!DOCTYPE html>
<html lang="en"style="min-height:100%;">
<head>
    <?php require(bin_meta.'index.php');?>
  <title>Nouveau Elevator Portal</title>
    <?php require(bin_css.'index.php');?>
    <?php require(bin_js.'index.php');?>
</head>
<body onload='finishLoadingPage();' style="min-height:100%;background-size:cover;background-color:rgba(255,255,255,.7);height:100%;">
    <div id='container' style='min-height:100%;height:100%;'>
        <div id="wrapper" style='height:100%;'>
            <?php require(PROJECT_ROOT.'php/element/navigation/index.php');?>
            <?php require(PROJECT_ROOT.'php/element/loading.php');?>
            <div id="page-wrapper" class='content' style='background-color:transparent !important;'>
                <div class="panel panel-primary">
                    <div class="panel-heading">
	                    <div class='row'>
	                        <div class='col-xs-10'><h4><?php $Icons->Customer( 1 );?> Customers</div>
	                        <div class='col-xs-2'><button style='width:100%;color:black;' onClick="$('#Filters').toggle();">+/-</button></div>
	                    </div>
	                </div>
					<div class="panel-body no-print" id='Filters' style='border-bottom:1px solid #1d1d1d;'>
	                    <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
	                    <div class='row'>
	                        <div class='col-xs-4'>Search:</div>
	                        <div class='col-xs-8'><input type='text' name='Search' placeholder='Search' onChange='redraw( );' /></div>
	                    </div>
	                    <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
	                    <div class='row'>
	                    	<div class='col-xs-4'>Name:</div>
	                    	<div class='col-xs-8'><input type='text' name='Name' placeholder='Name' onChange='redraw( );' /></div>
	                    </div>
	                    <div class='row'>
	                    	<div class='col-xs-4'>Status:</div>
	                    	<div class='col-xs-8'><select name='Status' onChange='redraw( );'>
			                	<option value=''>Select</option>
			                	<option value='0'>Active</option>
			                	<option value='1'>Inactive</option>
			                </select></div>
			            </div>
	                    <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
	                </div>
                    <div class="panel-body">
                        <table id='Table_Customers' class='display' cellspacing='0' width='100%'>
                            <thead>
                                <th title="ID">ID</th>
                                <th title='Name'>Name</th>
                                <th title='Status'>Status</th>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=customers.php';</script></head></html><?php }?>
