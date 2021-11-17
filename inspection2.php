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
        ||  !isset( $Privileges[ 'Inspection' ] )
        || 	!check( privilege_read, level_group, $Privileges[ 'Inspection' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
        \singleton\database::getInstance( )->query(
          null,
          " INSERT INTO Activity([User], [Date], [Page] )
            VALUES( ?, ?, ? );",
          array(
            $_SESSION[ 'Connection' ][ 'User' ],
            date('Y-m-d H:i:s'),
            'inspection2.php'
        )
      );
$serverName = "172.16.12.45";
$ConnectionOptions = array(
 "Database" => "Portal",
 "Uid" => "sa",
 "PWD" => "SQLABC!23456",
 'ReturnDatesAsStrings'=>true
);
$connection = sqlsrv_connect($serverName, $ConnectionOptions);
if(isset($_POST) && Count($_POST) > 0 ){
	$values = array_fill(0, count($_POST), '?');
	//echo count($_POST);
	$val = implode(',',$values);
	$parameters = array_values($_POST);
	//var_dump($_POST);
	$database->query($connection,
  "INSERT INTO Portal.dbo.Inspection(Company, [Date], Mechanic, License_Number, Start_Time, End_Time, Building_Address, Inspected_By, Elevator_Number, Device_Number, Category1, Category5, Signed, Violation_Elevator_Part1, Violation_Elevator_Part2, Violation_Elevator_Part3, Violation_Elevator_Part4, Violation_Elevator_Part5, Violation_Elevator_Part6, Violation_Elevator_Part7, Violation_Elevator_Part8, Violation_Elevator_Part9, Violation_Elevator_Part10, Violation_Condition1, Violation_Condition2, Violation_Condition3, Violation_Condition4, Violation_Condition5, Violation_Condition6, Violation_Condition7, Violation_Condition8, Violation_Condition9, Violation_Condition10, Violation_Suggested_Remedy1, Violation_Suggested_Remedy2, Violation_Suggested_Remedy3, Violation_Suggested_Remedy4, Violation_Suggested_Remedy5, Violation_Suggested_Remedy6, Violation_Suggested_Remedy7, Violation_Suggested_Remedy8, Violation_Suggested_Remedy9, Violation_Suggested_Remedy10, Pass, Fail, Retest, Pictures, Inspection_d, Code_Data_Plate, Controller, Machine, Speed, Last_Category1_Tag, Last_Category5_Tag, Comments, Runby, Normal_Up, Normal_down, Final_Up, Final_down, Car_Oil_Buffers, Cwt_Oil_Buffers, Governor_Switch, Plank_Switch, Slack_Rope_Switch, Governor_Cal, Maintenance_Elevator_Part1, Maintenance_Elevator_Part2, Maintenance_Elevator_Part3, Maintenance_Elevator_Part4, Maintenance_Elevator_Part5, Maintenance_Elevator_Part6, Maintenance_Elevator_Part7, Maintenance_Elevator_Part8, Maintenance_Elevator_Part9, Maintenance_Elevator_Part10, Maintenance_Condition1, Maintenance_Condition2, Maintenance_Condition3, Maintenance_Condition4, Maintenance_Condition5, Maintenance_Condition6, Maintenance_Condition7, Maintenance_Condition8, Maintenance_Condition9, Maintenance_Condition10, Maintenance_Suggested_Remedy1, Maintenance_Suggested_Remedy2, Maintenance_Suggested_Remedy3, Maintenance_Suggested_Remedy4, Maintenance_Suggested_Remedy5, Maintenance_Suggested_Remedy6, Maintenance_Suggested_Remedy7, Maintenance_Suggested_Remedy8, Maintenance_Suggested_Remedy9, Maintenance_Suggested_Remedy10, Run_Pressure, Work_Pressure, Relief_Pressure, Notes, Car_Safety_Set, Car_Slip_Traction, Car_Stall, Cwt_Safety_Test, Cwt_Slip_Traction, Cwt_Stall, Date_Form_Received, Directors_Initals, Directors_Changes, Date_ELV3_Created, Invoice_Number, Date_Invoice_Created, Retest_Notes, Retest_Billed, Retest_Dates) VALUES(?, ?, ?, ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);",
  array($_POST[ 'Company' ], $_POST['Date'], $_POST['Mechanic'], $_POST['License_Number'], $_POST['Start_Time'], $_POST['End_Time'], $_POST['Building_Address'], $_POST['Inspected_By'], $_POST['Elevator_Number'], $_POST['Device_Number'], $_POST['Category1'], $_POST['Category5'], $_POST['Signed'], $_POST['Violation_Elevator_Part1'], $_POST['Violation_Elevator_Part2'], $_POST['Violation_Elevator_Part3'], $_POST['Violation_Elevator_Part4'], $_POST['Violation_Elevator_Part5'], $_POST['Violation_Elevator_Part6'], $_POST['Violation_Elevator_Part7'], $_POST['Violation_Elevator_Part8'], $_POST['Violation_Elevator_Part9'], $_POST['Violation_Elevator_Part10'], $_POST['Violation_Condition1'], $_POST['Violation_Condition2'], $_POST['Violation_Condition3'], $_POST['Violation_Condition4'], $_POST['Violation_Condition5'], $_POST['Violation_Condition6'], $_POST['Violation_Condition7'], $_POST['Violation_Condition8'], $_POST['Violation_Condition9'], $_POST['Violation_Condition10'], $_POST['Violation_Suggested_Remedy1'], $_POST['Violation_Suggested_Remedy2'], $_POST['Violation_Suggested_Remedy3'], $_POST['Violation_Suggested_Remedy4'], $_POST['Violation_Suggested_Remedy5'], $_POST['Violation_Suggested_Remedy6'], $_POST['Violation_Suggested_Remedy7'], $_POST['Violation_Suggested_Remedy8'], $_POST['Violation_Suggested_Remedy9'], $_POST['Violation_Suggested_Remedy10'], $_POST['Pass'], $_POST['Fail'], $_POST['Retest'], $_POST['Pictures'], $_POST['Inspection'], $_POST['Code_Data_Plate'], $_POST['Controller'], $_POST['Machine'], $_POST['Speed'], $_POST['Last_Category1_Tag'], $_POST['Last_Category5_Tag'], $_POST['Comments'], $_POST['Runby'], $_POST['Normal_Up'], $_POST['Normal_down'], $_POST['Final_Up'], $_POST['Final_down'], $_POST['Car_Oil_Buffers'], $_POST['Cwt_Oil_Buffers'], $_POST['Governor_Switch'], $_POST['Plank_Switch'], $_POST['Slack_Rope_Switch'], $_POST['Governor_Cal'], $_POST['Maintenance_Elevator_Part1'], $_POST['Maintenance_Elevator_Part2'], $_POST['Maintenance_Elevator_Part3'], $_POST['Maintenance_Elevator_Part4'], $_POST['Maintenance_Elevator_Part5'], $_POST['Maintenance_Elevator_Part6'], $_POST['Maintenance_Elevator_Part7'], $_POST['Maintenance_Elevator_Part8'], $_POST['Maintenance_Elevator_Part9'], $_POST['Maintenance_Elevator_Part10'], $_POST['Maintenance_Condition1'], $_POST['Maintenance_Condition2'], $_POST['Maintenance_Condition3'], $_POST['Maintenance_Condition4'], $_POST['Maintenance_Condition5'], $_POST['Maintenance_Condition6'], $_POST['Maintenance_Condition7'], $_POST['Maintenance_Condition8'], $_POST['Maintenance_Condition9'], $_POST['Maintenance_Condition10'], $_POST['Maintenance_Suggested_Remedy1'], $_POST['Maintenance_Suggested_Remedy2'], $_POST['Maintenance_Suggested_Remedy3'], $_POST['Maintenance_Suggested_Remedy4'], $_POST['Maintenance_Suggested_Remedy5'], $_POST['Maintenance_Suggested_Remedy6'], $_POST['Maintenance_Suggested_Remedy7'], $_POST['Maintenance_Suggested_Remedy8'], $_POST['Maintenance_Suggested_Remedy9'], $_POST['Maintenance_Suggested_Remedy10'], $_POST['Run_Pressure'], $_POST['Work_Pressure'], $_POST['Relief_Pressure'], $_POST['Notes'], $_POST['Car_Safety_Set'], $_POST['Car_Slip_Traction'], $_POST['Car_Stall'], $_POST['Cwt_Safety_Test'], $_POST['Cwt_Slip_Traction'], $_POST['Cwt_Stall'], $_POST['Date_Form_Received'], $_POST['Directors_Initals'], $_POST['Directors_Changes'], $_POST['Date_ELV3_Created'], $_POST['Invoice_Number'], $_POST['Date_Invoice_Created'], $_POST['Retest_Notes'], $_POST['Retest_Billed'], $_POST['Retest_Dates']));
}
 /* if( ($errors = sqlsrv_errors() ) != null) {
  foreach( $errors as $error ) {
   echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
   echo "code: ".$error[ 'code']."<br />";
   echo "message: ".$error[ 'message']."<br />";
  }
 }*/
 /***************************************************Commands***********************************************************/
 $result = $database->query($connection, "
 	Select Inspection.*
 	From Portal.dbo.Inspection
 	Where Inspection.ID=?;",array($_GET['ID']));
 $data = sqlsrv_fetch_array($result);
 /*if( ($errors = sqlsrv_errors() ) != null) {
  foreach( $errors as $error ) {
   echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
   echo "code: ".$error[ 'code']."<br />";
   echo "message: ".$error[ 'message']."<br />";
  }
 }*.
 /****************************************************Commands**********************************************************/
 ?><html lang="en">
 <head>
     <?php require( bin_meta . 'index.php');?>
     <title>Nouveau Texas | Portal</title>
     <?php require( bin_css . 'index.php');?>
     <?php require( bin_js . 'index.php');?>
</head>
<body onload='finishLoadingPage();' style='background-color:#white !important;color:black !important;'>
 <?php require(bin_php.'element/navigation.php');?>
    <div id="page-wrapper" class='content'>
   <?php if(FALSE){?><div class='col-xs-12'><form style='height:100%;float:left;width:100%;' action='search.php' method='GET'><input name='Keyword' type='text' placeholder='Search' style='height:50px;color:black;width:100%;'/></form></div><?php }?>
	<div class='panel-white'>
		<div class='panel-body-1'>
			<form style='padding:3px;' action="#" name="myform" method="POST" onsubmit="alert('Successfully submitted');">
				<div class='boxed1'><div id='title'><strong>NOUVEAU ELEVATOR INSPECTION REPORT FROM</strong></div></div>
				<div class='boxed' style='padding:15px;	'>
					<div id='header'>
						<div class='row'>
							<div class='col-md-6 col-xs-12'>Company:<input type='text' name='Company' value='<?php echo $data['Company'];?>' 	size='20' /></div>
							<div class='col-md-6 col-xs-12'>Date:<input type='text' name='Date' value='<?php echo date('m-d-Y');?>' /></div>
						</div>
						<div class='row'>
							<div class='col-xs-12'>Mechanic:<input type='text' name='Mechanic' size='20' value='<?php echo $data['Mechanic'];?>' />
							</div>
							<div class='col-xs-12'>License:<input type='text' name='License_Number' size='20' value='<?php echo $data['License_Number'];?>'/>
							</div>
						</div>
						<div class='row'>
								<div class='col-xs-12'>Start:<input type='text' class="datepicker" name='Start_Time' size='20' value='<?php echo $data['Start_Time'];?>'/></div>
								<div class='col-xs-12'>End:<input type='text' class="datepicker" name='End_Time'  size='20' value='<?php echo $data['End_Time'];?>'/></div>
						</div>
					</div>
						<div id='report'>
							<div class='row'>
								<div class='col-xs-12'>Address: <input type='text' name='Building_Address' size='20' value='<?php echo $data['Building_Address'];?>'/></div>
								<div class='col-xs-12'>Inspector:<input type='text' name='Inspected_By' size='20' value='<?php echo $data['Inspected_By'];?>'/></div>
							</div>
							<div class='row'>
								<div class='col-xs-12'>Elevator #:<input type='text' name='Elevator_Number' size = '20' value='<?php echo $data['Elevator_Number'];?>'/></div>
								<div class='col-xs-12'>Device #:<input type='text' name='Device_Number' size = '20' value='<?php echo $data['Device_Number'];?>'/></div>
							</div>
							<div class = 'row'>
								<div class='col-xs-3'>Category 1:<input type='checkbox' name='Category1' <?php if(isset($data['Category1'])) echo "checked='checked'"; ?>'/></div>
								<div class='col-xs-3'>Category 5:<input type='checkbox' name='Category5' <?php if(isset($data['Category5'])) echo "checked='checked'"; ?>'/></div>
								<div class='col-xs-3'>Inspection Card Signed: </div>
								<div class='col-xs-1'>Yes<input type='radio' name='Signed' value='1' <?php if(isset($data['Signed']) && $data['Signed'] == '1') echo "checked='checked'"; ?> /></div>
								<div class='col-xs-1'>No<input type='radio' name='Signed' value='0' <?php if(isset($data['Signed']) && $data['Signed'] == '0') echo "checked='checked'"; ?> /></div>
							</div>
						</div>
					</div>
				</div>
				<br></br>
				<div class='boxed' style='padding:15px;'><strong>UNSATISFACTORY CONDITIONS</strong>
					<div class='row' style='padding:15px;'>
						<div class='col-lg-12'>
							<table class="tg" cellspacing='0' cellpadding = '0' >
							 <tr>
							 <th class="tg-us36">Elevator Part</th>
							 <th class="tg-us36"><input type='text1' name='Violation_Elevator_Part1' size='1' value='<?php echo $data['Violation_Elevator_Part1'];?>'/></th>
							 <th class="tg-us36"><input type='text1' name='Violation_Elevator_Part2' size='1' value='<?php echo $data['Violation_Elevator_Part2'];?>'/></th>
							 <th class="tg-us36"><input type='text1' name='Violation_Elevator_Part3' size='1' value='<?php echo $data['Violation_Elevator_Part3'];?>'/></th>
							 <th class="tg-us36"><input type='text1' name='Violation_Elevator_Part4' size='1' value='<?php echo $data['Violation_Elevator_Part4'];?>'/></th>
							 <th class="tg-us36"><input type='text1' name='Violation_Elevator_Part5' size='1' value='<?php echo $data['Violation_Elevator_Part5'];?>'/></th>
							 <th class="tg-us36"><input type='text1' name='Violation_Elevator_Part6' size='1' value='<?php echo $data['Violation_Elevator_Part6'];?>'/></th>
							 <th class="tg-us36"><input type='text1' name='Violation_Elevator_Part7' size='1' value='<?php echo $data['Violation_Elevator_Part7'];?>'/></th>
							 <th class="tg-us36"><input type='text1' name='Violation_Elevator_Part8' size='1' value='<?php echo $data['Violation_Elevator_Part8'];?>'/></th>
							 <th class="tg-us36"><input type='text1' name='Violation_Elevator_Part9' size='1' value='<?php echo $data['Violation_Elevator_Part9'];?>'/></th>
							 <th class="tg-us36"><input type='text1' name='Violation_Elevator_Part10' size='1' value='<?php echo $data['Violation_Elevator_Part10'];?>'/></th>
							 </tr>
							 <tr>
							 <td class="tg-us36">Violation Condition</td>
							 <td class="tg-us36"><input type='text1' name='Violation_Condition1' size='1' value='<?php echo $data['Violation_Condition1'];?>'/></td>
							 <td class="tg-us36"><input type='text1' name='Violation_Condition2' size='1' value='<?php echo $data['Violation_Condition2'];?>'/></td>
							 <td class="tg-us36"><input type='text1' name='Violation_Condition3' size='1' value='<?php echo $data['Violation_Condition3'];?>'/></td>
							 <td class="tg-us36"><input type='text1' name='Violation_Condition4' size='1' value='<?php echo $data['Violation_Condition4'];?>'/></td>
							 <td class="tg-us36"><input type='text1' name='Violation_Condition5' size='1' value='<?php echo $data['Violation_Condition5'];?>'/></td>
							 <td class="tg-us36"><input type='text1' name='Violation_Condition6' size='1' value='<?php echo $data['Violation_Condition6'];?>'/></td>
							 <td class="tg-us36"><input type='text1' name='Violation_Condition7' size='1' value='<?php echo $data['Violation_Condition7'];?>'/></td>
							 <td class="tg-us36"><input type='text1' name='Violation_Condition8' size='1' value='<?php echo $data['Violation_Condition8'];?>'/></td>
							 <td class="tg-us36"><input type='text1' name='Violation_Condition9' size='1' value='<?php echo $data['Violation_Condition9'];?>'/></td>
							 <td class="tg-us36"><input type='text1' name='Violation_Condition10' size='1' value='<?php echo $data['Violation_Condition10'];?>'/></td>
							 </tr>
							 <tr>
							 <td class="tg-us36">Suggested Remedy</td>
							 <td class="tg-us36"><input type='text1' name='Violation_Suggested_Remedy1' size='1' value='<?php echo $data['Violation_Suggested_Remedy1'];?>'/></td>
							 <td class="tg-us36"><input type='text1' name='Violation_Suggested_Remedy2' size='1' value='<?php echo $data['Violation_Suggested_Remedy2'];?>'/></td>
							 <td class="tg-us36"><input type='text1' name='Violation_Suggested_Remedy3' size='1' value='<?php echo $data['Violation_Suggested_Remedy3'];?>'/></td>
							 <td class="tg-us36"><input type='text1' name='Violation_Suggested_Remedy4' size='1' value='<?php echo $data['Violation_Suggested_Remedy4'];?>'/></td>
							 <td class="tg-us36"><input type='text1' name='Violation_Suggested_Remedy5' size='1' value='<?php echo $data['Violation_Suggested_Remedy5'];?>'/></td>
							 <td class="tg-us36"><input type='text1' name='Violation_Suggested_Remedy6' size='1' value='<?php echo $data['Violation_Suggested_Remedy6'];?>'/></td>
							 <td class="tg-us36"><input type='text1' name='Violation_Suggested_Remedy7' size='1' value='<?php echo $data['Violation_Suggested_Remedy7'];?>'/></td>
							 <td class="tg-us36"><input type='text1' name='Violation_Suggested_Remedy8' size='1' value='<?php echo $data['Violation_Suggested_Remedy8'];?>'/></td>
							 <td class="tg-us36"><input type='text1' name='Violation_Suggested_Remedy9' size='1' value='<?php echo $data['Violation_Suggested_Remedy9'];?>'/></td>
							 <td class="tg-us36"><input type='text1' name='Violation_Suggested_Remedy10' size='1' value='<?php echo $data['Violation_Suggested_Remedy10'];?>'/></td>
							 </tr>
							</table>
						</div>
					</div>
							<div class='row' style='padding:3px;'>
								<div class='col-xs-4'><strong>Test Results</strong></div>
								<div class='col-xs-4'>Pass <input type='radio' name='Pass' value='1' <?php if(isset($data['Pass']) && $data['Pass'] == '1') echo "checked='checked'"; ?> /></div>
								<div class='col-xs-4'>Fail <input type='radio' name='Pass' value='0' <?php if(isset($data['Pass']) && $data['Pass'] == '0') echo "checked='checked'"; ?> /></div>
							</div>
							<div class='row' style='padding:3px;'>
								<div class='col-xs-4'><strong>Re-Test</strong></div>
								<div class='col-xs-4'>Yes <input type='radio' name='Retest' value='1' <?php if(isset($data['Retest']) && $data['Retest'] == '1') echo "checked='checked'"; ?> /></div>
								<div class='col-xs-4'>No <input type='radio' name='Retest' value='0' <?php if(isset($data['Retest']) && $data['Retest'] == '0') echo "checked='checked'"; ?> /></div>
							</div>
							<div class='row' style='padding:3px;'>
								<div class='col-xs-4'><strong>Pictures</strong></div>
								<div class='col-xs-4'>Yes <input type='radio' name='Pictures' value='1' <?php if(isset($data['Pictures']) && $data['Signed'] == '1') echo "checked='checked'"; ?> /></div>
								<div class='col-xs-4'>No <input type='radio' name='Pictures' value='0' <?php if(isset($data['Pictures']) && $data['Pictures'] == '0') echo "checked='checked'"; ?> /></div>
							</div>
					<div class='row' align = ''>
						<div class='col-lg-12'>
							<strong>Inspection:</strong>
								 <input type='radio' <?php echo isset($data['Inspection_d']) && $data['Inspection_d'] == 'Escalator' ? 'checked' : '';?>  name='Inspection' value='Escalator'/> Escalator
								 <input type='radio' <?php echo isset($data['Inspection_d']) && $data['Inspection_d'] == 'Hydraulic' ? 'checked' : '';?>   name='Inspection' value='Hydraulic'/> Hydraulic
								 <input type='radio' <?php echo isset($data['Inspection_d']) && $data['Inspection_d'] == 'Drum' ? 'checked' : '';?>   name='Inspection' value='Drum'/> Drum
								 <input type='radio' <?php echo isset($data['Inspection_d']) && $data['Inspection_d'] == 'Traction' ? 'checked' : '';?>   name='Inspection' value='Traction'/> Traction
								 <input type='radio' <?php echo isset($data['Inspection_d']) && $data['Inspection_d'] == 'Dumbwaiter' ? 'checked' : '';?>   name='Inspection' value='Dumbwaiter'/> Dumbwaiter
								 <input type='radio' <?php echo isset($data['Inspection_d']) && $data['Inspection_d'] == 'Wheelchair Lift' ? 'checked' : '';?>   name='Inspection' value='Wheelchair Lift'/> Wheelchair Lift
						</div>
					</div>
					<div class='row'>
						<div class='col-lg-6'>
							<strong>Code Data Plate:</strong>
							<input type='text' name='Code_Data_Plate' size = '20' value='<?php echo $data['Code_Data_Plate'];?>'/>
						</div>
						<div class='col-lg-6'>
							<strong>Type:</strong>
							 <input type='radio' name='Code_Data_Plate_Type' value='<?php echo $data['Code_Data_Plate_Type'];?>'/> Roped Hydro.
							 <input type='radio' name='Code_Data_Plate_Type' value='<?php echo $data['Code_Data_Plate_Type'];?>'/> Basement
							 <input type='radio' name='Code_Data_Plate_Type' value='<?php echo $data['Code_Data_Plate_Type'];?>'/> Overhead
							 <input type='radio' name='Code_Data_Plate_Type' value='<?php echo $data['Code_Data_Plate_Type'];?>'/> Geared
							 <input type='radio' name='Code_Data_Plate_Type' value='<?php echo $data['Code_Data_Plate_Type'];?>'/> Gearless
						</div>
					</div>
					<div class='row' style='padding:3px;'>
						<div class='col-xs-4'>
							<strong>Controller:</strong>
							<input type='text' name='Controller' size = '20' value='<?php echo $data['Controller'];?>'/>
						</div>
						<div class='col-xs-4'>
							<strong>Machine:</strong>
							<input type = 'text' name='Machine' size = '20' value='<?php echo $data['Machine'];?>'/>
						</div>
						<div class='col-xs-4'>
							<strong>Capacity/Speed:</strong>
							<input type ='text' name='Speed' size = '20' value='<?php echo $data['Speed'];?>'/>
						</div>
					</div>
					<div class='row' style='padding:3px;'>
						<div class='col-lg-6' align='left'>
							<strong>LAST Category 1(On tag):</strong>
							<input type='text' name='Last_Category1_Tag' size = '20' value='<?php echo $data['Last_Category1_Tag'];?>'/>
						</div>
						<div class='col-lg-6'>
							<strong>LAST Category 5(On tag):</strong>
							<input type='text' name='Last_Category5_Tag' size = '20' value='<?php echo $data['Last_Category5_Tag'];?>'/>
						</div>
					</div>
					<div class='row' style='padding:1px;'>
						<div class='col-lg-6 '> <strong>Comments: (Part# info. and Floor # if shaft item)</strong> <textarea rows="5" cols="200" name='Comments'><?php echo $data['Comments'];?></textarea></div>
					</div>
					<div class='row'>
						<div class='col-xs-12 col-lg-4'>Runby: <input type='text' name='Runby' size = '20' value='<?php echo $data['Runby'];?>'/> in. </div>
					</div>
					<div class='row'>
						<div class='col-xs-4'><strong>TESTS</strong></div>
					</div>
					<div class='row' style = 'padding:1px;'>
						<div class='col-xs-4'>Up Direction</div>
						<div class='col-xs-4'>Normal<input type='radio' name='Normal_Up' value='1' <?php if(isset($data['Normal_Up']) && $data['Normal_Up'] == '1') echo "checked='checked'"; ?> /></div>
						<div class='col-xs-4'>Final<input type='radio' name='Normal_Up' value='0' <?php if(isset($data['Normal_Up']) && $data['Normal_Up'] == '0') echo "checked='checked'"; ?> /></div>
					</div>
					<div class='row' style = 'padding:1px;'>
						<div class='col-xs-4'>Down Direction</div>
						<div class='col-xs-4'>Normal<input type='radio' name='Normal_Down' value='1' <?php if(isset($data['Normal_Down']) && $data['Normal_Down'] == '1') echo "checked='checked'"; ?> /></div>
						<div class='col-xs-4'>Final<input type='radio' name='Normal_Down' value='0' <?php if(isset($data['Normal_Down']) && $data['Normal_Down'] == '0') echo "checked='checked'"; ?> /></div>
					</div>
					<div class='row' style = 'padding:1px;'>
						<div class='col-xs-4'>Oil Buffers</div>
						<div class='col-xs-4'>Car<input type='radio' name='Car_Oil_Buffers' value='1' <?php if(isset($data['Car_Oil_Buffers']) && $data['Car_Oil_Buffers'] == '1') echo "checked='checked'"; ?> /></div>
						<div class='col-xs-4'>Cwt<input type='radio' name='Car_Oil_Buffers' value='0' <?php if(isset($data['Car_Oil_Buffers']) && $data['Car_Oil_Buffers'] == '0') echo "checked='checked'"; ?> /></div>
					</div>
					<div class='row' style = 'padding:1px;'>
						<div class='col-xs-8'>Governor Switch</div>
						<div class='col-xs-2'><input type='checkbox' name='Governor_Switch' <?php if(isset($data['Governor_Switch'])) echo "checked='checked'"; ?>/></div>
					</div>
					<div class='row' style = 'padding:1px;'>
						<div class='col-xs-8'>Plank Switch</div>
						<div class='col-xs-2'><input type='checkbox' name='Plank_Switch' <?php if(isset($data['Plank_Switch'])) echo "checked='checked'"; ?>/></div>
					</div>
					<div class='row' style = 'padding:1px;'>
						<div class='col-xs-8'>Slack Rope Switch</div>
						<div class='col-xs-2'><input type='checkbox' name='Slack_Rope_Switch' <?php if(isset($data['Slack_Rope_Switch'])) echo "checked='checked'"; ?>/></div>
					</div>
					<div class='row'>
						<div class='col-xs-12'>Governor Cal:<input type='text' name='Governor_Cal' size = '20' value='<?php echo $data['Governor_Cal'];?>' /> FPM </div>
					</div>
				   <div class='space-print'><br></br><br></br><br></br><br></br></div>
					<div class='row' style='padding:15px;'>
						<div class='col-lg-12'>	<strong> MAINTENANCE ITEMS - For Customer / Contractor </strong>
							<table class="tg" align ='' >
							 	 <tr>
								 <th class="tg-us36">Elevator Part</th>
								 <th class="tg-us36"><input type='text1' name='Maintenance_Elevator_Part1' size='1'value='<?php echo $data['Maintenance_Condition1'];?>'/></th>
								 <th class="tg-us36"><input type='text1' name='Maintenance_Elevator_Part2' size='1'value='<?php echo $data['Maintenance_Condition2'];?>'/></th>
								 <th class="tg-us36"><input type='text1' name='Maintenance_Elevator_Part3' size='1'value='<?php echo $data['Maintenance_Condition3'];?>'/></th>
								 <th class="tg-us36"><input type='text1' name='Maintenance_Elevator_Part4' size='1'value='<?php echo $data['Maintenance_Condition4'];?>'/></th>
								 <th class="tg-us36"><input type='text1' name='Maintenance_Elevator_Part5' size='1'value='<?php echo $data['Maintenance_Condition5'];?>'/></th>
								 <th class="tg-us36"><input type='text1' name='Maintenance_Elevator_Part6' size='1'value='<?php echo $data['Maintenance_Condition6'];?>'/></th>
								 <th class="tg-us36"><input type='text1' name='Maintenance_Elevator_Part7' size='1'value='<?php echo $data['Maintenance_Condition7'];?>'/></th>
								 <th class="tg-us36"><input type='text1' name='Maintenance_Elevator_Part8' size='1'value='<?php echo $data['Maintenance_Condition8'];?>'/></th>
								 <th class="tg-us36"><input type='text1' name='Maintenance_Elevator_Part9' size='1'value='<?php echo $data['Maintenance_Condition9'];?>'/></th>
								 <th class="tg-us36"><input type='text1' name='Maintenance_Elevator_Part10' size='1'value='<?php echo $data['Maintenance_Condition10'];?>'/></th>
								</tr>
								<tr>
								 <td class="tg-us36">Maintenance Condition</td>
								 <td class="tg-us36"><input type='text1' name='Maintenance_Condition1' size='1'value='<?php echo $data['Maintenance_Condition1'];?>'/></td>
								 <td class="tg-us36"><input type='text1' name='Maintenance_Condition2' size='1'value='<?php echo $data['Maintenance_Condition2'];?>'/></td>
								 <td class="tg-us36"><input type='text1' name='Maintenance_Condition3' size='1'value='<?php echo $data['Maintenance_Condition3'];?>'/></td>
								 <td class="tg-us36"><input type='text1' name='Maintenance_Condition4' size='1'value='<?php echo $data['Maintenance_Condition4'];?>'/></td>
								 <td class="tg-us36"><input type='text1' name='Maintenance_Condition5' size='1'value='<?php echo $data['Maintenance_Condition5'];?>'/></td>
								 <td class="tg-us36"><input type='text1' name='Maintenance_Condition6' size='1'value='<?php echo $data['Maintenance_Condition6'];?>'/></td>
								 <td class="tg-us36"><input type='text1' name='Maintenance_Condition7' size='1'value='<?php echo $data['Maintenance_Condition7'];?>'/></td>
								 <td class="tg-us36"><input type='text1' name='Maintenance_Condition8' size='1'value='<?php echo $data['Maintenance_Condition8'];?>'/></td>
								 <td class="tg-us36"><input type='text1' name='Maintenance_Condition9' size='1'value='<?php echo $data['Maintenance_Condition9'];?>'/></td>
								 <td class="tg-us36"><input type='text1' name='Maintenance_Condition10' size='1'value='<?php echo $data['Maintenance_Condition10'];?>'/></td>
								 </tr>
								 <tr>
								 <td class="tg-us36">Suggested Remedy</td>
								 <td class="tg-us36"><input type='text1' name='Maintenance_Suggested_Remedy1' size='1' value='<?php echo $data['Maintenance_Suggested_Remedy1'];?>'/></td>
								 <td class="tg-us36"><input type='text1' name='Maintenance_Suggested_Remedy2' size='1' value='<?php echo $data['Maintenance_Suggested_Remedy2'];?>'/></td>
								 <td class="tg-us36"><input type='text1' name='Maintenance_Suggested_Remedy3' size='1' value='<?php echo $data['Maintenance_Suggested_Remedy3'];?>'/></td>
								 <td class="tg-us36"><input type='text1' name='Maintenance_Suggested_Remedy4' size='1' value='<?php echo $data['Maintenance_Suggested_Remedy4'];?>'/></td>
								 <td class="tg-us36"><input type='text1' name='Maintenance_Suggested_Remedy5' size='1' value='<?php echo $data['Maintenance_Suggested_Remedy5'];?>'/></td>
								 <td class="tg-us36"><input type='text1' name='Maintenance_Suggested_Remedy6' size='1' value='<?php echo $data['Maintenance_Suggested_Remedy6'];?>'/></td>
								 <td class="tg-us36"><input type='text1' name='Maintenance_Suggested_Remedy7' size='1' value='<?php echo $data['Maintenance_Suggested_Remedy7'];?>'/></td>
								 <td class="tg-us36"><input type='text1' name='Maintenance_Suggested_Remedy8' size='1' value='<?php echo $data['Maintenance_Suggested_Remedy8'];?>'/></td>
								 <td class="tg-us36"><input type='text1' name='Maintenance_Suggested_Remedy9' size='1' value='<?php echo $data['Maintenance_Suggested_Remedy9'];?>'/></td>
								 <td class="tg-us36"><input type='text1' name='Maintenance_Suggested_Remedy10' size='1' value='<?php echo $data['Maintenance_Suggested_Remedy10'];?>'/></td>
								</tr>
							</table>
						</div>
						<div class='col-lg-10' style='padding:15px;'>
							<strong>HYDRAULIC-Tests</strong>
							<div class='row'>
								<div class='col-xs-5'>Run Pressure</div>
								<div class='col-xs-4'><input type='text' name='Run_Pressure' size = '20' value='<?php echo $data['Run_Pressure'];?>'/></div>
							</div>
							<div class='row'>
								<div class='col-xs-5'>Work Pressure</div>
								<div class='col-xs-4'><input type='text' name='Work_Pressure' size = '20' value='<?php echo $data['Work_Pressure'];?>'/></div>
							</div>
							<div class='row'>
								<div class='col-xs-5'>Relief Pressure</div>
								<div class='col-xs-4'><input type='text' name='Relief_Pressure' size = '20' value='<?php echo $data['Relief_Pressure'];?>'/></div>
							</div>
							<div class='row'>
								<div class='col-xs-5'>Hydraulic Safety Set</div>
								<div class='col-xs-3'>Yes<input type='radio' name='Hydraulic_Pressure' value='1' <?php if(isset($data['Hydraulic_Pressure']) && $data['Hydraulic_Pressure'] == '1') echo "checked='checked'"; ?> /></div>
								<div class='col-xs-3'>No<input type='radio' name='Hydraulic_Pressure' value='0' <?php if(isset($data['Hydraulic_Pressure']) && $data['Hydraulic_Pressure'] == '0') echo "checked='checked'"; ?> /></div>
							</div>
						</div>
					</div>
						<div class = 'row' style='padding:1px;'>
							<div class='col-lg-8'>
								<div class='col-lg-4'>Notes For Customer:</div>
								<textarea name='Notes' rows="10" cols="100"><?php echo $data['Notes'];?></textarea>
							</div>
							<div class='col-lg-4' style='padding:15px;'>
								<div class='row'>
									<strong>TRACTION-Car Safety Test</strong>
								</div>
								<div class='row'>
									<div class='col-xs-4'>Safety Set</div>
									<div class='col-xs-4'>Yes<input type='radio'   name='Car_Safety_Set' value='0'<?php if(isset($data['Car_Safety_Set']) && $data['Car_Safety_Set'] == '1') echo "checked='checked'"; ?> /></div>
									<div class='col-xs-4'>No<input type='radio'   name='Car_Safety_Set' value='1'<?php if(isset($data['Car_Safety_Set']) && $data['Car_Safety_Set'] == '0') echo "checked='checked'"; ?> /></div>
								</div>
								<div class='row'>
									<div class='col-xs-4'>Slip Traction</div>
									<div class='col-xs-4'>Yes<input type='radio'   name='Car_Slip_Traction' value='1' <?php if(isset($data['Car_Slip_Traction']) && $data['Car_Slip_Traction'] == '1') echo "checked='checked'"; ?>/></div>
									<div class='col-xs-4'>No<input type='radio'   name='Car_Slip_Traction' value='0' <?php if(isset($data['Car_Slip_Traction']) && $data['Car_Slip_Traction'] == '0') echo "checked='checked'"; ?> /></div>
								</div>
								<div class='row'>
									<div class='col-xs-4'>Stall</div>
									<div class='col-xs-4'>Yes<input type='radio'   name='Car_Stall' value='1' <?php if(isset($data['Car_Stall']) && $data['Car_Stall'] == '1') echo "checked='checked'"; ?> /></div>
									<div class='col-xs-4'>No<input type='radio'   name='Car_Stall' value='0' <?php if(isset($data['Car_Stall']) && $data['Car_stall'] == '0') echo "checked='checked'"; ?> /></div>
								</div>
								<div class='row'>
									<strong>TRACTION-Cwt Safety Test</strong>
								</div>
								<div class='row'>
									<div class='col-xs-4'>Safety Set</div>
									<div class='col-xs-4'>Yes<input type='radio'  name='Cwt_Safety_Test' value='1' <?php if(isset($data['Cwt_Safety_Test']) && $data['Cwt_Safety_Test'] == '1') echo "checked='checked'"; ?> /></div>
									<div class='col-xs-4'>No<input type='radio'  name='Cwt_Safety_Test' value='0' <?php if(isset($data['Cwt_Safety_Test']) && $data['Cwt_Safety_Test'] == '0') echo "checked='checked'"; ?> /></div>
								</div>
								<div class='row'>
									<div class='col-xs-4'>Slip Traction</div>
									<div class='col-xs-4'>Yes<input type='radio'  name='Cwt_Slip_Traction' value='1' <?php if(isset($data['Cwt_Slip_Traction']) && $data['Cwt_Slip_Traction'] == '1') echo "checked='checked'"; ?>' /></div>
									<div class='col-xs-4'>No<input type='radio'  name='Cwt_Slip_Traction' value='0' <?php if(isset($data['Cwt_Slip_Traction']) && $data['Cwt_Slip_Traction'] == '0') echo "checked='checked'"; ?>' /></div>
								</div>
								<div class='row'>
									<div class='col-xs-4'>Stall</div>
									<div class='col-xs-4'>Yes<input type='radio' name='Cwt_Stall' value='1'<?php if(isset($data['Cwt_Stall']) && $data['Cwt_Stall'] == '1') echo "checked='checked'"; ?>' /></div>
									<div class='col-xs-4'>No<input type='radio' name='Cwt_Stall' value='0' <?php if(isset($data['Cwt_Stall']) && $data['Cwt_Stall'] == '0') echo "checked='checked'"; ?>' /></div>
								</div>
							</div>
						</div>
								<div class='row'>
									<div class='col-xs-8' align = 'center'><input type="submit" value="Submit"/></div>
									<!--<div class='col-xs-4' align = 'center'><input type="submit" value="Duplicate"/></div>-->
								</div>
					</div>
				</div>
				<br></br>
				<div class='boxed'>
					<div class = 'boxed2'><div class='col-lg-12'><strong>For Office Use Only</strong></div></div>
					<br></br>
					<div class='office' style='padding:15px;'>
						<div class='row'>
							<div class='col-xs-6'>Date Form Received:<input type='text' name='Date_Form_Received' size='20' value='<?php echo $data['Date_Form_Received'];?>'/></div>
							<div class='col-xs-6'>Directors Initials:<input type='text' name='Directors_Initals' size='20' value='<?php echo $data['Directors_Initals'];?>'/></div>
						</div>
						<div class='row'>
							<div class='col-xs-6'>Directors Changes:<input type='text' name='Directors_Changes' size='20' value='<?php echo $data['Directors_Changes'];?>'/></div>
						</div>
						<div class='row'>
							<div class='col-xs-6'>Date ELV3 Created: <input type='text' name='Date_ELV3_Created' size='20' value='<?php echo $data['Date_ELV3_Created'];?>'/> </div>
							<div class='col-xs-6'>Invoice #:<input type='text' name='Invoice_Number' size='20' value='<?php echo $data['Invoice_Number'];?>'/></div>
						</div>
						<div class='row'>
							<div class='col-xs-6'>Date Invoice Created: <input type='text' name='Date_Invoice_Created' size='20' value='<?php echo $data['Date_Invoice_Created'];?>'/></div>
							<div class='col-xs-6'>Retest Notes: <input type='text' name='Retest_Notes' size='20'  value='<?php echo $data['Retest_Notes'];?>'/></div>
						</div>
						<div class='row'>
							<div class='col-xs-6'>Retest Dates<input type='text' name='Retest_Dates' size='20'  value='<?php echo $data['Retest_Dates'];?>'/></div>
						</div>
					</div>
				</div>
				<div class='boxed1'>
					<div id='footer'>
							<strong>Nouveau Elevator</strong><?php echo date('m/d/Y');?>
					</div>
				</div>
			</form>
		</div>
	</div>
</body>
</html><?php
}
}?>
