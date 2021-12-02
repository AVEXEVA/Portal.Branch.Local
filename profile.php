<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
if( isset( $_SESSION[ 'Connection' ][ 'User' ], $_SESSION[ 'Connection' ][ 'Hash' ] ) ){
  //Connection
    $result = \singleton\database::getInstance( )->query(
      'Portal',
      " SELECT      s[Connection].[ID]
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
        ||  !isset( $Privileges[ 'User' ] )
        || 	!check( privilege_read, level_group, $Privileges[ 'User' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
        \singleton\database::getInstance( )->query(
          null,
          " INSERT INTO Activity([User], [Date], [Page] )
            VALUES( ?, ?, ? );",
          array(
            $_SESSION[ 'Connection' ][ 'User' ],
            date('Y-m-d H:i:s'),
            'profile.php'
        )
      );
$Mechanic = is_numeric($_SESSION[ 'Connection' ]['User']) ? $_SESSION[ 'Connection' ]['User'] : -1;
if($Mechanic > 0){
    $Call_Sign = "";
    $r = \singleton\database::getInstance( )->query(
    	null,
        "  SELECT
                Emp.*,
                Emp.Last as Last_Name,
                Emp.Last AS Last,
                Rol.*,
                PRWage.Reg as Wage_Regular,
                PRWage.OT1 as Wage_Overtime,
                PRWage.OT2 as Wage_Double_Time
           FROM
            (Emp LEFT JOIN PRWage ON Emp.WageCat = PRWage.ID)
            LEFT JOIN Rol ON Emp.Rol = Rol.ID
        WHERE Emp.ID = ?;",array($_SESSION[ 'Connection' ]['User']));
    $User = sqlsrv_fetch_array($r);
    while($a= sqlsrv_fetch_array($r)){}
}?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <?php$_GET[ 'Bootstrap' ] = '5.1';
         $_GET[ 'Entity_CSS' ] = 1;?>
    <?php require( bin_meta . 'index.php' );?>
    <?php require( bin_css  . 'index.php' );?>
    <?php require( bin_js   . 'index.php' );?>
    <title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
    <?php require('css/index.php');
    require('js/index.php');?>
</head>
<body>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <div id="page-wrapper">
			<div class='panel panel-primary'>
				<div class='panel-heading'><?php echo proper($User['fFirst'] . " " . $User['Last_Name']);?></div>
				<div class='panel-body'>
					<div class='row'>
						<div class='col-md-2 col-xs-4'><b>Name</b></div>
						<div class='col-md-10 col-xs-8'><?php echo proper($User['Last_Name']);?>, <?php echo proper($User['fFirst']);?> <?php echo proper($User['Middle']);?></div>
					</div>
					<div class='row'>
						<div class='col-md-2 col-xs-4'><b>Birthdate</b></div>
						<div class='col-md-10 col-xs-8'><?php echo substr($User['DBirth'],5,2) . "/" . substr($User['DBirth'],8,2) . "/" . substr($User['DBirth'],0,4);?></div>
					</div>
					<div class='row'>
						<div class='col-md-2 col-xs-4'><b>Email</b></div>
						<div class='col-md-10 col-xs-8'><?php echo strlen($User['EMail']) > 1 ? $User['EMail'] : "Unlisted";?></div>
					</div>
					<div class='row'>
						<div class='col-md-2 col-xs-4'><b>Phone</b></div>
						<div class='col-md-10 col-xs-8'><?php echo strlen($User['Phone']) > 1 ? $User['Phone'] : "Unlisted";?></div>
					</div>
					<div class='row'>
						<div class='col-md-2 col-xs-4'><b>Address</b></div>
						<div class='col-md-10 col-xs-8'><?php echo proper($User['Address']);?></div>
					</div>
					<div class='row'>
						<div class='col-md-2 col-xs-4'>
							<b>City</b>
						</div>
						<div class='col-md-10 col-xs-8'><?php echo proper($User['City']); ?></div>
					</div>
					<div class='row'>
						<div class='col-xs-4 col-md-2'>
							<b>State</b>
						</div>
						<div class='col-xs-8 col-md-10'><?php echo $User['State']?></div>
					</div>
					<div class='row'>
						<div class='col-md-2 col-xs-4'><b>Zip</b></div>
						<div class='col-md-10 col-xs-8'><?php echo $User['Zip']?></div>
					</div>
				</div>
				<div class='panel-heading'>Work Details</div>
				<div class='panel-body'>
					<div class='row'>
						<div class='col-md-2 col-xs-4'><b>Call Sign</b></div>
						<div class='col-md-10 col-xs-8'><?php echo strlen($User['CallSign']) > 1 ? $User['CallSign'] : "Unlisted";?></div>
					</div>
					<div class='row'>
						<div class='col-md-2 col-xs-4'><b>Hired</b></div>
						<div class='col-md-10 col-xs-8'><?php echo substr($User['DHired'],5,2) . "/" . substr($User['DHired'],8,2) . "/" . substr($User['DHired'],0,4);?></div>
					</div>
					<div class='row'>
						<div class='col-md-2 col-xs-4'><b>Title</b></div>
						<div class='col-md-10 col-xs-8'><?php echo proper($User['Title']);?></div>
					</div>
					<div class='row'>
						<div class='col-md-2 col-xs-4'><b>Wage</b></div>
						<div class='col-md-10 col-xs-8'>$<?php echo money_format('%i',$User['Wage_Regular']);?></div>
					</div>
					<div class='row'>
						<div class='col-md-2 col-xs-4'><b>Overtime</b></div>
						<div class='col-md-10 col-xs-8'>$<?php echo money_format('%i',$User['Wage_Overtime']);?></div>
					</div>
					<div class='row'>
						<div class='col-md-2 col-xs-4'><b>Doubletime</b></div>
						<div class='col-md-10 col-xs-8'>$<?php echo money_format('%i',$User['Wage_Double_Time']);?></div>
					</div>
				</div>
				<?php
					$serverName = "172.16.12.45";
					nullectionOptions = array(
						"Database" => "ATTENDANCE",
						"Uid" => "sa",
						"PWD" => "SQLABC!23456",
						'ReturnDatesAsStrings'=>true
					);
					//Establishes the connection
					$c2 = sqlsrv_connect($serverName, nullectionOptions);
					$r = $database->query($c2,"select * from Employee where EmpID= ?;",array($User['Ref']));
					$Attendance = sqlsrv_fetch_array($r);
					while($temp = sqlsrv_fetch_array($r));
				?>
				<div class='panel-heading'>Time Paid Off</div>
				<div class='panel-body'>
					<div class='row'>
						<div class='col-lg-3'>
							<?php if(strlen($Attendance['UnionDate']) > 1){?>
								<table spacing='3' style='width:100%;'>
									<thead>
										<th></th>
										<th style='text-align:center;'><b>Available</b></th>
										<th style='text-align:center;'><b>Allowed</b></th>
									</thead>
									<tbody>
                    <tr><td style='color:white !important;padding:5px;'><b>Sick Days</b></td><td style='text-align:center;color:white !important;padding:5px;'><?php echo $Attendance['SickAvail'];?></td><td style='text-align:center;color:white !important;padding:5px;'><?php echo $Attendance['SickAllowed'];?></td></tr>
                    <tr><td style='color:white !important;padding:5px;'><b>Vacation Days</b></td><td style='text-align:center;color:white !important;padding:5px;'><?php echo $Attendance['VacAvail'];?></td><td style='text-align:center;color:white !important;padding:5px;'><?php echo $Attendance['VacAllowed'];?></td></tr>
                    <tr><td style='color:white !important;padding:5px;'><b>Medical Days</b></td><td style='text-align:center;color:white !important;padding:5px;'><?php echo $Attendance['MedAvail'];?></td><td style='text-align:center;color:white !important;padding:5px;'><?php echo $Attendance['MedicalDayAllowed'];?></td></tr>
                    <tr><td style='color:white !important;padding:5px;'><b>Lieu Days</b></td><td style='text-align:center;color:white !important;padding:5px;'><?php echo $Attendance['LieuAvail'];?></td><td style='text-align:center;color:white !important;padding:5px;'><?php echo $Attendance['LieuDayAllowed'];?></td></tr>
									</tbody>
								</table><?php } else {?>
								<table spacing='3' style='width:100%;'>
									<thead>
										<th></th>
										<th style='text-align:center;'>Available</th>
										<th style='text-align:center;'>Allowed</th>
									</thead>
									<tbody>
										<tr><td><b>Hours</b></td><td style='text-align:center;'><?php echo $Attendance['NONUNIONHoursAvail'];?></td><td style='text-align:center;'><?php echo $Attendance['NONUNIONHoursAllowed'];?></td></tr>
									</tbody>
								</table>
								<?php }?>
							</div>
						</div>
					</div>
      </div>
  </div>
</div>
</div>
	<?php require('bin/js/dropdown-scroll.js');?>
</body>
</html>
 <?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=profile.php';</script></head></html><?php }?>
