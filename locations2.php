<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( [
		'read_and_close' => true
	] ); 
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/cgi-bin/php/index.php' );
}
if( isset( $_SESSION[ 'User' ], $_SESSION[ 'Hash' ] ) ){
    $r = sqlsrv_query(
    	$NEI,
    	"	SELECT *
			FROM   Connection
			WHERE  Connection.Connector = ?
		       	   AND Connection.Hash  = ?;",
		array(
			$_SESSION['User'],
			$_SESSION['Hash']
		)
	);
    $Connection = sqlsrv_fetch_array( $r, SQLSRV_FETCH_ASSOC );
    $r = sqlsrv_query(
    	$NEI,
    	"	SELECT 	*,
		       		Emp.fFirst AS First_Name,
			   		Emp.Last   AS Last_Name
			FROM   	Emp
			WHERE  	Emp.ID = ?;",
		array(
			$_SESSION[ 'User' ]
		)
	);
    $User = sqlsrv_fetch_array( $r );
	$r = sqlsrv_query(
		$NEI,
		"	SELECT *
			FROM   Privilege
			WHERE  Privilege.User_ID = ?;",
		array(
			$_SESSION['User']
		)
	);
	$Privileges = array( );
	if( $r ){ while($Privilege = sqlsrv_fetch_array($r)){$Privileges[$Privilege[ 'Access_Table' ] ] = $Privilege; } }
    if(	!isset( $Connection[ 'ID' ] )
	   	|| !isset($Privileges[ 'Location' ] )
	  		|| $Privileges[ 'Location' ][ 'User_Privilege' ]  < 4
	  		|| $Privileges[ 'Location' ][ 'Group_Privilege' ] < 4){
				?><?php require('../404.html');?><?php }
    else {
		sqlsrv_query(
			$NEI,
			"	INSERT INTO Activity([User], [Date], [Page])
				VALUES(?,?,?);",
			array(
				$_SESSION['User'],
				date("Y-m-d H:i:s"),
				'Locations.php'
			)
		);
?><!DOCTYPE html>
<html lang="en">
<head>
    <title>Nouveau Elevator Portal</title>
    <?php $_GET[ 'Bootstrap' ] = '5.1';?>
    <?php require( bin_meta . 'index.php');?>
    <?php require( bin_css . 'index.php');?>
    <?php require( bin_js  . 'index.php' );?>
</head>
<body onload='finishLoadingPage();'>
    <div id="wrapper">
        <?php require( bin_php . 'element/navigation/index.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id="page-wrapper" class='content'>
            <div class="card card-full card-primary border-0">
                <div class="card-heading"><h4><?php $Icons->Location( 1 );?> Locations</h4></div>
                <div class="card-body bg-dark">
					<table id='Table_Locations' class='display' cellspacing='0' width='100%'>
						<thead><tr>
							<th class='text-white border border-white' title='ID'>ID</th>
							<th class='text-white border border-white' title='Name'>Name</th>
							<th class='text-white border border-white' title='Customer'>Customer</th>
							<th class='text-white border border-white' title='Type'>Type</th>
							<th class='text-white border border-white' title='Division'>Division</th>
							<th class='text-white border border-white' title='Route'>Route</th>
							<th class='text-white border border-white' title='Street'>Street</th>
							<th class='text-white border border-white' title='City'>City</th>
							<th class='text-white border border-white' title='State'>State</th>
							<th class='text-white border border-white' title='Zip'>Zip</th>
							<th class='text-white border border-white' title='Units'>Units</th>
							<th class='text-white border border-white' title='Maintained'>Maintained</th>
							<th class='text-white border border-white' title='Status'>Status</th>
						</tr><tr>
							<th class='text-white border border-white' title='ID'><input class='redraw form-control' type='text' name='ID' /></th>
							<th class='text-white border border-white' title='Name'><input class='redraw form-control' type='text' name='Name' /></th>
							<th class='text-white border border-white' title='Customer'><input class='redraw form-control' type='text' name='Customer' /></th>
							<th class='text-white border border-white' title='Type'><select class='redraw form-control' name='Type'>
								<option value=''>Select</option>
								<?php 
									$result = sqlsrv_query(
										$NEI,
										" 	SELECT 		Elev.Building 
											FROM 		Elev 
											WHERE 		Elev.Building NOT IN ( '', ' ' )
											GROUP BY 	Elev.Building
											ORDER BY 	Elev.Building ASC;"
									);
									if( $result ){while( $row = sqlsrv_fetch_array( $result ) ){?><option value='<?php echo $row['Building'];?>'><?php echo $row['Building'];?></option><?php } }
								?>
							</select></th>
							<th class='text-white border border-white' title='Division'><select class='redraw form-control' name='Division'>
								<option value=''>Select</option>
								<?php 
									$result = sqlsrv_query(
										$NEI,
										" 	SELECT 		Zone.ID,
														Zone.Name 
											FROM 		Zone ;"
									);
									if( $result ){while( $row = sqlsrv_fetch_array( $result ) ){?><option value='<?php echo $row['ID'];?>'><?php echo $row['Name'];?></option><?php } }
								?>
							</select></th>
							<th class='text-white border border-white' title='Route'><select class='redraw form-control' name='Route'>
								<option value=''>Select</option>
								<?php 
									$result = sqlsrv_query(
										$NEI,
										" 	SELECT 		Route.ID,
														Route.Name,
														Employee.fFirst + ' ' + Employee.Last AS Mechanic 
											FROM 		Route
														LEFT JOIN Emp AS Employee ON Route.Mech = Employee.fWork
											WHERE 		Employee.fFirst + ' ' + Employee.Last <> 'D D'
											ORDER BY 	Route.Name + ' - ' + Employee.fFirst + ' ' + Employee.Last ASC;"
									);
									if( $result ){while( $row = sqlsrv_fetch_array( $result ) ){?><option value='<?php echo $row['ID'];?>'><?php echo $row[ 'Name' ];?> - <?php echo $row['Mechanic'];?></option><?php } }
								?>
							</select></th>
							<th class='text-white border border-white' title='Street'><input class='redraw form-control' type='text' name='Street' /></th>
							<th class='text-white border border-white' title='City'><input class='redraw form-control' type='text' name='City' /></th>
							<th class='text-white border border-white' title='State'><input class='redraw form-control' type='text' name='State' /></th>
							<th class='text-white border border-white' title='Zip'><input class='redraw form-control' type='text' name='Zip' /></th>
							<th class='text-white border border-white' title='Units'><input disabled class='redraw form-control' type='text' name='Units' /></th>
							<th class='text-white border border-white' title='Maintained'><select class='redraw form-control' name='Status'>
			                	<option value=''>Select</option>
			                	<option value='0'>Active</option>
			                	<option value='1'>Inactive</option>
			                </select></th>
							<th class='text-white border border-white' title='Status'><select class='redraw form-control' name='Maintained'>
			                	<option value=''>Select</option>
			                	<option value='1'>Active</option>
			                	<option value='0'>Inactive</option>
			                </select></th>
						</tr></thead>
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
