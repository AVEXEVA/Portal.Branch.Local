<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [
        'read_and_close' => true
    ] );
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/bin/php/index.php' );
}
if( isset( $_SESSION[ 'User' ], $_SESSION[ 'Hash' ] ) ){
  $result = \singleton\database::getInstance()->query(
    null,
    " SELECT  *
      FROM    Connection
      WHERE       Connection.Connector = ?
              AND Connection.Hash  = ?;",
    array(
      $_SESSION[ 'User' ],
      $_SESSION[ 'Hash' ]
    )
  );
  $Connection = sqlsrv_fetch_array( $result );
  //User
  $result = \singleton\database::getInstance()->query(
    null,
    " SELECT  *,
              Emp.fFirst AS First_Name,
              Emp.Last   AS Last_Name
      FROM    Emp
      WHERE   Emp.ID = ?;",
    array(
      $_SESSION[ 'User' ]
    )
  );
  $User = sqlsrv_fetch_array( $result );
  //Privileges
  $result = \singleton\database::getInstance()->query(
    null,
    " SELECT  Privilege.Access_Table,
              Privilege.User_Privilege,
              Privilege.Group_Privilege,
              Privilege.Other_Privilege
      FROM    Privilege
      WHERE   Privilege.User_ID = ?;",
    array(
      $_SESSION[ 'User' ]
    )
  );
  $Privileges = array();
  if( $result ){while( $Privilege = sqlsrv_fetch_array( $result ) ){ $Privileges[ $Privilege[ 'Access_Table' ] ] = $Privilege; } }
  if(     !isset( $Connection[ 'ID' ] )
      ||  !isset($Privileges[ 'Contract' ])
      ||  $Privileges[ 'Contract' ][ 'User_Privilege' ]  < 4
      ||  $Privileges[ 'Contract' ][ 'Group_Privilege' ] < 4
      ||  $Privileges[ 'Contract' ][ 'Other_Privilege' ] < 4
  ){
      ?><?php require( '../404.html' );?><?php
  } else {
    \singleton\database::getInstance()->query(
      null,
      " INSERT INTO Activity( [User], [Date], [Page] )
        VALUES( ?, ?, ? );",
      array(
        $_SESSION[ 'User' ],
        date( 'Y-m-d H:i:s' ),
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
    <title>Nouveau Texas | Portal</title>
    <?php require( bin_css . 'index.php');?>
    <?php require( bin_js . 'index.php');?>
</head>
<body onload='finishLoadingPage();' style='background-color:#1d1d1d;'>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require(bin_php.'element/navigation/index.php');?>
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
