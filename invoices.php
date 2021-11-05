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
	$Privileges = array();
  	if($result){while($Privilege = sqlsrv_fetch_array($result)){$Privileges[$Privilege[ 'Access_Table' ]] = $Privilege;}}
      if(!isset($Connection[ 'ID' ])
  	   	|| !isset($Privileges[ 'Invoice' ])
  	  		|| $Privileges[ 'Invoice' ][ 'User_Privilege' ]  < 4
  	  		|| $Privileges[ 'Invoice' ][ 'Group_Privilege' ] < 4
  	  		|| $Privileges[ 'Invoice' ][ 'Other_Privilege' ] < 4){
  				?><?php require('../404.html');?><?php }
      else {
		sqlsrv_query(
      $NEI,
   '    INSERT INTO Activity([User], [Date], [Page])
			  VALUES(?,?,?);',
      array($_SESSION[ 'User' ],
            date('Y-m-d H:i:s'),
                 'Invoices.php')
    );
?><!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <meta name='description' content=''>
    <meta name='author' content='Peter D. Speranza'>
    <title>Nouveau Texas | Portal</title>
    <?php require(bin_css.'index.php');?>
    <?php require(bin_js.'index.php');?>
</head>
<body onload='finishLoadingPage();' style='background-color:#1d1d1d;'>
    <div id='wrapper' class=''>
        <?php require(PROJECT_ROOT.'php/element/navigation/index.php');?>
        <?php require(PROJECT_ROOT.'php/element/loading.php');?>
        <div id='page-wrapper' class='content'>
            <div class='panel panel-primary'>
                <div class='panel-heading'>
                    <div class='row'>
                        <div class='col-xs-10'><h4><?php $Icons->Invoice( 1 );?> Invoices</div>
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
                    	<div class='col-xs-4'>Customer:</div>
                    	<div class='col-xs-8'><input type='text' name='Customer' placeholder='Customer' onChange='redraw( );' /></div>
                    </div>
                    <div class='row'>
                    	<div class='col-xs-4'>Location:</div>
                    	<div class='col-xs-8'><input type='text' name='Location' placeholder='Location' onChange='redraw( );' /></div>
                    </div>
                    <div class='row'>
                    	<div class='col-xs-4'>Job:</div>
                    	<div class='col-xs-8'><input type='text' name='Job' placeholder='Job' onChange='redraw( );' /></div>
                    </div>
                    <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
                </div>
                <div class='panel-body'>
                    <table id='Table_Invoices' class='display' cellspacing='0' width='100%' style='font-size:12px;'>
                        <thead>
                            <th>Invoice #</th>
                            <th>Customer</th>
                            <th>Location</th>
                            <th>Job</th>
							              <th>Type</th>
                            <th>Date</th>
                            <th>Due</th>
                            <th>Original</th>
                            <th>Balance</th>
                            <th>Description</th>
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
} else {?><html><head><script>document.location.href='../login.php?Forward=Invoices.php';</script></head></html><?php }?>
