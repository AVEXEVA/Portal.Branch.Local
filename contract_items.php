<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
if( isset( $_SESSION[ 'Connection' ][ 'User' ], $_SESSION[ 'Connection' ][ 'Hash' ] ) ){
  //Connection
    $result = \singleton\database::getInstance( )->query(
      'Portal',
      " SELECT  [Connection].[ID]
        FROM    dbo.[Connection]
        WHERE       [Connection].[User] = ?
                AND [Connection].[Hash] = ?;",
      array(
        $_SESSION[ 'Connection' ][ 'User' ],
        $_SESSION[ 'Connection' ][ 'Hash' ]
      )
    );
    $Connection = sqlsrv_fetch_array($result);
    //User
	$result = \singleton\database::getInstance( )->query(
		null,
		" SELECT  Emp.fFirst  AS First_Name,
		          Emp.Last    AS Last_Name,
		          Emp.fFirst + ' ' + Emp.Last AS Name,
		          Emp.Title AS Title,
		          Emp.Field   AS Field
		  FROM  Emp
		  WHERE   Emp.ID = ?;",
		array(
		  	$_SESSION[ 'Connection' ][ 'User' ]
		)
	);
	$User   = sqlsrv_fetch_array( $result );
	//Privileges
	$Access = 0;
	$Hex = 0;
	$result = \singleton\database::getInstance( )->query(
		'Portal',
		"   SELECT  [Privilege].[Access],
                    [Privilege].[Owner],
                    [Privilege].[Group],
                    [Privilege].[Department],
                    [Privilege].[Database],
                    [Privilege].[Server],
                    [Privilege].[Other],
                    [Privilege].[Token],
                    [Privilege].[Internet]
		  FROM      dbo.[Privilege]
		  WHERE     Privilege.[User] = ?;",
		array(
		  	$_SESSION[ 'Connection' ][ 'User' ],
		)
	);
    $Privileges = array();
    if( $result ){while( $Privilege = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC ) ){

        $key = $Privilege['Access'];
        unset( $Privilege[ 'Access' ] );
        $Privileges[ $key ] = implode( '', array(
        	dechex( $Privilege[ 'Owner' ] ),
        	dechex( $Privilege[ 'Group' ] ),
        	dechex( $Privilege[ 'Department' ] ),
        	dechex( $Privilege[ 'Database' ] ),
        	dechex( $Privilege[ 'Server' ] ),
        	dechex( $Privilege[ 'Other' ] ),
        	dechex( $Privilege[ 'Token' ] ),
        	dechex( $Privilege[ 'Internet' ] )
        ) );
    }}
    if( 	!isset( $Connection[ 'ID' ] )
        ||  !isset( $Privileges[ 'Contract' ] )
        || 	!check( privilege_read, level_group, $Privileges[ 'Contract' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
        \singleton\database::getInstance( )->query(
          null,
          " INSERT INTO Activity([User], [Date], [Page] )
            VALUES( ?, ?, ? );",
          array(
            $_SESSION[ 'Connection' ][ 'User' ],
            date('Y-m-d H:i:s'),
            'contract_items.php'
        )
      );
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Peter D. Speranza">
    <title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
       <?php  $_GET[ 'Bootstrap' ] = '5.1';?>
       <?php  $_GET[ 'Entity_CSS' ] = 1;?>
       <?php	require( bin_meta . 'index.php');?>
       <?php	require( bin_css  . 'index.php');?>
       <?php  require( bin_js   . 'index.php');?>
</head>
<body onload='finishLoadingPage();' style='background-color:#1d1d1d;'>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require(bin_php.'element/navigation.php');?>
        <?php require(bin_php.'php/element/loading.php');?>
        <div id="page-wrapper" class='content'>
			<div class="panel panel-primary">
				<div class="panel-heading"><h4><?php \singleton\fontawesome::getInstance( )->Contract();?> Contracts</h4></div>
        <div class='panel-body'><form id='Manage_Contract_Item'>
          <div class='row'>
            <div class='col-xs-1'>Territory</div>
            <div class='col-xs-11'><select name='Territory'>
              <option value=''>Select</option>
              <?php
                $r = $database->query(null,"SELECT * FROM nei.dbo.Terr;");
                if($r){while($row = sqlsrv_fetch_array($r)){?><option value='<?php echo $row['ID'];?>'><?php echo $row['Name'];?></option><?php }}
              ?>
            </select></div>
          </div>
          <div class='row'>
            <div class='col-xs-1'>Customer</div>
            <div class='col-xs-11'><input type='text' value='' name='Customer' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-1'>Location</div>
            <div class='col-xs-11'><input type='text' value='' name='Customer' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-1'>Unit</div>
            <div class='col-xs-11'><input type='text' value='' name='Unit' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-2'><select name='Elevator_Part'>
              <option value=''>Select</option>
              <?php
                $r = $database->query(null,"SELECT * FROM Portal.dbo.Category_Elevator_Part;");
                if($r){while($row = sqlsrv_fetch_array($r)){?><option value='<?php echo $row['ID'];?>'><?php echo $row['Name'];?></option><?php }}
              ?>
            </select></div>
            <div class='col-xs-2'><select name='Condition'>
              <option value=''>Select</option>
              <?php
                $r = $database->query(null,"SELECT * FROM Portal.dbo.Category_Violation_Condition;");
                if($r){while($row = sqlsrv_fetch_array($r)){?><option value='<?php echo $row['ID'];?>'><?php echo $row['Name'];?></option><?php }}
              ?>
            </select></div>
            <div class='col-xs-2'><select name='Remedy'>
              <option value=''>Select</option>
              <?php
                $r = $database->query(null,"SELECT * FROM Portal.dbo.Category_Remedy;");
                if($r){while($row = sqlsrv_fetch_array($r)){?><option value='<?php echo $row['ID'];?>'><?php echo $row['Name'];?></option><?php }}
              ?>
            </select></div>
            <div class='col-xs-6'><button type='button' onClick='contract_item_covered();'>Cover Contract Item for Criteria</button></div>
          </div>
        </form></div>
				<div class="panel-body">
					<table id='Table_Contract_Items' class='display' cellspacing='0' width='100%' style='font-size:12px;'>
						<thead>
							<th>Contract</th>
							<th>Customer</th>
							<th>Location</th>
							<th>Unit</th>
							<th>Elevator Part</th>
							<th>Condition</th>
							<th>Remedy</th>
							<th>Covered</th>
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
} else {?><html><head><script>document.location.href='../login.php?Forward=contracts.php';</script></head></html><?php }?>
