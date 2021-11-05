<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/cgi-bin/php/index.php' );
}
if(isset($_SESSION[ 'User' ],
         $_SESSION[ 'Hash' ] ) ) {
        $result = sqlsrv_query(
          $NEI,
        '   SELECT  *
    		FROM    Connection
    		WHERE       Connection.Connector = ?
    		            AND Connection.Hash  = ?;',
        array(
            $_SESSION[ 'User' ],
            $_SESSION[ 'Hash' ]
        )
    );
        $Connection = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC);
        $result = sqlsrv_query(
          $NEI,
        '   SELECT    *,
    		           Emp.fFirst AS First_Name,
    			         Emp.Last   AS Last_Name
    		    FROM   Emp
    		    WHERE  Emp.ID = ?;',
        array(
            $_SESSION[ 'User' ]
        )
    );
      $User = sqlsrv_fetch_array($result);
    	$result = sqlsrv_query(
          $NEI,
      '     SELECT    *
    		    FROM   Privilege
    		    WHERE  Privilege.User_ID = ?;',
        array($_SESSION[ 'User' ]
        )
    );
	$My_Privileges = array();
	if($r){while($My_Privilege = sqlsrv_fetch_array($r)){$My_Privileges[$My_Privilege[ 'Access_Table' ]] = $My_Privilege;}}
    if(	!isset($My_Connection[ 'ID' ])
	   	|| !isset($My_Privileges[ 'Job' ])
	  		|| $My_Privileges[ 'Job' ][ 'User_Privilege' ]  < 4
	  		|| $My_Privileges[ 'Job' ][ 'Group_Privilege' ] < 4){
				?><?php require('../404.html');?><?php }
    else {
		sqlsrv_query(
      $NEI,
      '   INSERT INTO Activity([User], [Date], [Page])
			    VALUES(?, ?, ?);',
    array($_SESSION[ 'User' ],
        date('Y-m-d H:i:s'),
              'jobs.php')
      );
if(isMobile() || true){
?><!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <meta name='description' content=''>
    <meta name='author' content=''>
	<title>Nouveau Elevator Portal</title>
    <?php require(bin_css.'index.php');?>
    <?php require(bin_js.'index.php');?>
</head>
<body onload='finishLoadingPage();' style='background-color:#1d1d1d;'>
    <div id='wrapper' class='<?php echo isset($_SESSION[ 'Toggle_Menu' ]) ? $_SESSION[ 'Toggle_Menu' ] : null;?>'>
        <?php require(PROJECT_ROOT.'php/element/navigation/index.php');?>
        <?php require(PROJECT_ROOT.'php/element/loading.php');?>
        <div id='page-wrapper' class='content'>
			<div class='panel panel-primary'>
				<div class='panel-heading'>
                    <div class='row'>
                        <div class='col-xs-10'><h4><?php $Icons->Job( 1 );?> Jobs</div>
                        <div class='col-xs-2'><button style='width:100%;color:black;' onClick='$('#Filters').toggle();'>+/-</button></div>
                    </div>
                </div>
				<div class='panel-body no-print' id='Filters' style='border-bottom:1px solid #1d1d1d;'>
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
                    	<div class='col-xs-4'>Customer:</div>
                    	<div class='col-xs-8'><input type='text' name='Customer' placeholder='Customer' onChange='redraw( );' /></div>
                    </div>
                    <div class='row'>
                    	<div class='col-xs-4'>Location:</div>
                    	<div class='col-xs-8'><input type='text' name='Location' placeholder='Location' onChange='redraw( );' /></div>
                    </div>
                    <div class='row'>
                    	<div class='col-xs-4'>Type:</div>
                    	<div class='col-xs-8'><input type='text' name='Type' placeholder='Type' onChange='redraw( );' /></div>
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
    				<div class='panel-body'>
    					<table id='Table_Jobs' class='display' cellspacing='0' width='100%'>
    						<thead><tr>
    							<th title='ID'>ID</th>
    							<th title='Name'>Name</th>
    							<th title='Customer'>Customer</th>
    							<th title='Location'>Location</th>
    							<th title='Type'>Type</th>
    							<th title='Status'>Status</th>
						 </tr></thead>
					</table>
				</div>
      </div>
    </div>
  </div>
</body>
</html>
<?php
} else {
  $_GET['processing'] = 1;
  require('../beta/jobs.php');
}
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=jobs.php';</script></head></html><?php }?>
