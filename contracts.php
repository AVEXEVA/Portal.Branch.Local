<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( );
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/cgi-bin/php/index.php' );
}
if( isset( $_SESSION[ 'User' ], $_SESSION[ 'Hash' ] ) ){
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
    $Connection = sqlsrv_fetch_array( $result );
    //User
    $result = sqlsrv_query(
        $NEI,
        "   SELECT  *,
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
	$result = sqlsrv_query(
        $NEI,
        "   SELECT  *
            FROM    Privilege
            WHERE   Privilege.User_ID = ?;",
        array(
            $_SESSION['User']
        )
    );
	$Privileges = array();
	if( $result ){while( $Privilege = sqlsrv_fetch_array( $result ) ){ $Privileges[ $Privilege[ 'Access_Table' ] ] = $Privilege; } }
    if(	!isset( $Connection[ 'ID' ] )
	   	|| !isset($Privileges[ 'Contracts' ])
	  		|| $Privileges[ 'Contracts' ][ 'User_Privilege' ]  < 4
	  		|| $Privileges[ 'Contracts' ][ 'Group_Privilege' ] < 4
        || $Privileges[ 'Contracts' ][ 'Other_Privilege' ] < 4){
				?><?php require( '../404.html' );?><?php }
    else {
  		sqlsrv_query(
          $NEI,
          "   INSERT INTO Activity([User], [Date], [Page])
              VALUES( ?, ?, ? );",
          array(
              $_SESSION['User'],
              date( 'Y-m-d H:i:s' ),
              'contracts.php'
          )
      );
?><!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <meta name='description' content=''>
    <meta name='author' content='Peter D. Speranza'>
    <title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
    <?php $_GET[ 'Bootstrap' ] = '5.1';?>
    <?php require('cgi-bin/css/index.php');?>
    <?php require('cgi-bin/js/index.php');?>
</head>
<body onload='finishLoadingPage();'>
  <div id='wrapper'>
    <?php require( 'cgi-bin/php/element/navigation/index.php' );?>
    <?php require( 'cgi-bin/php/element/loading.php' );?>
    <div id='page-wrapper' class='content'>
			<div class='panel panel-primary'>
				<div class='panel-heading'><h4><?php $Icons->Contract( );?> Contracts</h4></div>
				<div class='panel-body no-print' id='Filters' style='border-bottom:1px solid #1d1d1d;'>
          <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
          <div class='form-group row'>
              <label class='col-auto'>Search:</label>
              <div class='col-auto'><input type='text' name='Search' placeholder='Search' onChange='redraw( );' /></div>
          </div>
          <div class='form-group row'><div class='col-xs-12'>&nbsp;</div></div>
          <div class='form-group row'>
          	<label class='col-auto'>ID:</label>
          	<div class='col-auto'><input type='text' name='ID' placeholder='ID' onChange='redraw( );' /></div>
          </div>
          <div class='form-group row'>
          	<label class='col-auto'>Customer:</label>
          	<div class='col-auto'><input type='text' name='Customer' placeholder='Customer' onChange='redraw( );' /></div>
          </div>
          <div class='form-group row'>
          	<label class='col-auto'>Location:</label>
          	<div class='col-auto'><input type='text' name='Location' placeholder='Location' onChange='redraw( );' /></div>
          </div>
          <div class='form-group row'>
          	<label class='col-auto'>Start:</label>
          	<div class='col-auto'><input type='text' name='Start_Date' placeholder='Start Date' onChange='redraw( );' /></div>
          </div>
          <div class='form-group row'>
          	<label class='col-auto'>End:</label>
          	<div class='col-auto'><input type='text' name='End_Date' placeholder='End Date' onChange='redraw( );' /></div>
          </div>
          <div class='form-group row'>
          	<label class='col-auto'>Cycle:</label>
          	<div class='col-auto'><select name='Cycle' onChange='redraw( );'>
          		<option value=''>Select</option>
          		<option value='0'>Monthly</option>
          		<option value='1'>Bi-Monthly</option>
          		<option value='2'>Quarterly</option>
          		<option value='3'>Trimester</option>
          		<option value='4'>Semi-Annually</option>
          		<option value='5'>Annually</option>
          		<option value='6'>Never</option>
          	</select></div>
          </div>
          <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
        </div>
				<div class='panel-body'>
					<table id='Table_Contracts' class='display' cellspacing='0' width='100%' style='font-size:12px;'>
						<thead><tr>
							<th>ID</th>
							<th>Customer</th>
							<th>Location</th>
							<th>Job</th>
							<th>Start</th>
							<th>Amount</th>
							<th>Length</th>
							<th>Cycle</th>
							<th>End</th>
							<th>Esc. Factor</th>
							<th>Esc. Date</th>
							<th>Esc. Type</th>
							<th>Esc. Cycle</th>
							<th>Link</th>
							<th>Remarks</th>
						</tr></thead>
					</table>
				</div>
      </div>
    </div>
  </div>
  <!-- Bootstrap Core JavaScript -->
<?php require( 'cgi-bin/js/datatables.php' );?>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=contracts.php';</script></head></html><?php }?>
