<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/cgi-bin/php/index.php' );
}
if(isset($_SESSION[ 'User' ],
         $_SESSION[ 'Hash' ] ) ) {
        $result = sqlsrv_query(
          $NEI,
          " SELECT  *
    		    FROM    Connection
    		    WHERE       Connection.Connector = ?
    		            AND Connection.Hash  = ?;",
          array(
            $_SESSION[ 'User' ],
            $_SESSION[ 'Hash' ]
          )
        );
        $Connection = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC);
        $result = sqlsrv_query(
          $NEI,
        "   SELECT    *,
    		           Emp.fFirst AS First_Name,
    			         Emp.Last   AS Last_Name
    		    FROM   Emp
    		    WHERE  Emp.ID = ?;",
        array(
            $_SESSION[ 'User' ]
        )
    );
      $User = sqlsrv_fetch_array($result);
    	$result = sqlsrv_query(
          $NEI,
      "     SELECT    *
    		    FROM   Privilege
    		    WHERE  Privilege.User_ID = ?;",
        array($_SESSION[ 'User' ]
        )
    );
	$Privileges = array();
	if($result){while($Privilege = sqlsrv_fetch_array($result)){$Privileges[$Privilege[ 'Access_Table' ]] = $Privilege;}}
    if(	!isset($Connection[ 'ID' ])
	   	|| !isset($Privileges[ 'Admin' ])
	  		|| $Privileges[ 'Admin' ][ 'User_Privilege' ]  < 4
	  		|| $Privileges[ 'Admin' ][ 'Group_Privilege' ] < 4
	  		|| $Privileges[ 'Admin'][ 'Other_Privilege' ] < 4){
				?><?php require('../404.html');?><?php }
    else {
		sqlsrv_query(
      $NEI,
      '   INSERT INTO Activity([User], [Date], [Page])
			    VALUES(?, ?, ?);',
    array($_SESSION[ 'User' ],
          date('Y-m-d H:i:s'),
                'leads.php')
      );
?><!DOCTYPE html>
<html lang='en'>
<head>
    <title>Nouveau Texas | Portal</title>
    <?php require(bin_css.'index.php');?>
    <?php require(bin_js.'index.php');?>
    <?php require(bin_meta.'index.php');?>
</head>
<body onload='finishLoadingPage();'>
    <div id='wrapper' class='<?php echo isset($_SESSION[ 'Toggle_Menu' ]) ? $_SESSION[ 'Toggle_Menu' ] : null;?>'>
        <?php require( bin_php . 'element/navigation/index.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id='page-wrapper' class='content'>
			<div class='panel panel-primary'>
				<div class='panel-heading'><h3><?php \singleton\fontawesome::getInstance( )->Customer();?> Leads</h3></div>
				<div class='panel-body'>
					<div id='Form_Lead'>
						<div class='panel panel-primary'>
							<div class='panel-heading' style='position:fixed;width:750px;z-index:999;'><h2 style='display:block;'>Location Form</h2></div>
							<div class='panel-body white-background BankGothic shadow' style='padding-top:100px;'>
								<div style='display:block !important;'>
									<fieldset >
										<legend>Names</legend>
										<editor-field name='ID'></editor-field>
										<editor-field name='Name'></editor-field>
										<editor-field name='Address'></editor-field>
										<editor-field name='City'></editor-field>
										<editor-field name='State'></editor-field>
										<editor-field name='Zip'></editor-field>
										<editor-field name='Customer'></editor-field>
									</fieldset>
								</div>
							</div>
						</div>
					</div>
					<table id='Table_Leads' class='display' cellspacing='0' width='100%' style='font-size:12px;'>
						<thead>
							<th title='ID'></th>
							<th title='Name'></th>
							<th title='Address'></th>
							<th title='City'></th>
							<th title='State'></th>
							<th title='Zip'></th>
							<th title='Owner'></th>
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
} else {?><html><head><script>document.location.href='../login.php?Forward=customers.php';</script></head></html><?php }?>
