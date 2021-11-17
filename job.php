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
        ||  !isset( $Privileges[ 'Job' ] )
        || 	!check( privilege_read, level_group, $Privileges[ 'Job' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
        \singleton\database::getInstance( )->query(
          null,
          " INSERT INTO Activity([User], [Date], [Page] )
            VALUES( ?, ?, ? );",
          array(
            $_SESSION[ 'Connection' ][ 'User' ],
            date('Y-m-d H:i:s'),
            'job.php'
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
        ||  !isset( $Privileges[ 'Job' ] )
        || 	!check( privilege_read, level_group, $Privileges[ 'Job' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
        \singleton\database::getInstance( )->query(
          null,
          " INSERT INTO Activity([User], [Date], [Page] )
            VALUES( ?, ?, ? );",
          array(
            $_SESSION[ 'Connection' ][ 'User' ],
            date('Y-m-d H:i:s'),
            'job.php'
        )
      );
			$Location_ID = sqlsrv_fetch_array($result)['Location_ID'];
			$result = \singleton\database::getInstance( )->query(
      	null,
				'	SELECT 	Tickets.ID
					FROM 	(
								(
									SELECT 	TicketO.ID,
											TicketO.fWork,
											TicketO.LID AS Location
									FROM   	TicketO
								) UNION ALL (
									SELECT 	TicketD.ID,
											TicketD.fWork,
											TicketD.Loc AS Location
									FROM   	TicketD
								)
							) AS Tickets
							LEFT JOIN Emp ON Tickets.fWork = Emp.fWork
					WHERE  		Tickets.Location = ?
							AND Emp.ID 			 = ?;',
				array(
					$Location_ID,
					$_SESSION[ 'User' ]
				)
			);
			$Privileged = is_array( sqlsrv_fetch_array( $result ) );
	} elseif(
			isset( $Privileges[ 'Job' ] )
		&& 	$Privileges[ 'Job' ][ 'Owner' ] >= 4
		&& 	is_numeric( $_GET[ 'ID' ] )
	){		$result = \singleton\database::getInstance( )->query(
    null,
				'	SELECT 	Tickets.ID
					FROM  	(
								(
									SELECT 	TicketO.ID,
											TicketO.Job,
											TicketO.fWork
									FROM   	TicketO
								) UNION ALL (
									SELECT 	TicketD.ID,
											TicketD.Job,
											TicketD.fWork
									FROM   	TicketD
								)
							) AS Tickets
							LEFT JOIN Emp ON Tickets.fWork = Emp.fWork
					WHERE 		Tickets.Job = ?
							AND Emp.ID      = ?;',
				array(
					$_GET[ 'ID' ],
					$_SESSION[ 'User' ]
				)
			);
			$Privileged = is_array( sqlsrv_fetch_array( $result ) );
	}
    if(		!isset($Connection[ 'ID' ])
    	|| 	!is_numeric($_GET[ 'ID' ])
    	|| 	!$Privileged){
    		require('401.html');
   	} else {
    	\singleton\database::getInstance( )->query(
    		'	INSERT INTO Activity([User], [Date], [Page])
    			VALUES(?,?,?);',
    		array(
    			$_SESSION[ 'User' ],
    			date( 'Y-m-d H:i:s' ),
    			'job.php?ID=' . $_GET[ 'ID' ]
    		)
    	);
       	$result = \singleton\database::getInstance( )->query(
        	null,
       		'	SELECT 	TOP 1
                		Job.ID                AS ID,
                		Job.fDesc             AS Name,
                		Job.fDate             AS Start_Date,
		                Job.BHour             AS Budgeted_Hours,
       			        JobType.Type          AS Type,
						        Job.Remarks 		      AS Remarks,
      		          Loc.Loc               AS Location_ID,
          		      Loc.ID                AS Location_Name,
                		Loc.Tag               AS Location_Tag,
                		Loc.Address           AS Location_Street,
                		Loc.City              AS Location_City,
                		Loc.State             AS Location_State,
                		Loc.Zip               AS Location_Zip,
                		Loc.Route             AS Route,
                		Zone.Name             AS Division,
                		Owner.ID              AS Customer_ID,
                		OwnerRol.Name     	  AS Customer_Name,
               	 		Owner.Status       	  AS Customer_Status,
                		Owner.Elevs    		    AS Customer_Elevators,
                		OwnerRol.Address      AS Customer_Street,
                		OwnerRol.City         AS Customer_City,
                		OwnerRol.State        AS Customer_State,
                		OwnerRol.Zip          AS Customer_Zip,
                		OwnerRol.Contact      AS Customer_Contact,
                		OwnerRol.Remarks      AS Customer_Remarks,
                		OwnerRol.Email        AS Customer_Email,
                		OwnerRol.Cellular     AS Customer_Cellular,
                		Elev.ID               AS Unit_ID,
                		Elev.Unit             AS Unit_Label,
                		Elev.State            AS Unit_State,
                		Elev.Cat              AS Unit_Category,
                		Elev.Type             AS Unit_Type,
                		Emp.fFirst            AS Mechanic_First_Name,
                		Emp.Last              AS Mechanic_Last_Name,
                		Route.ID              AS Route_ID,
        						Violation.ID          AS Violation_ID,
        						Violation.fdate       AS Violation_Date,
        						Violation.Status      AS Violation_Status,
        						Violation.Remarks     AS Violation_Remarks
            	FROM 	Job
                		LEFT JOIN Loc           	ON Job.Loc      = Loc.Loc
                		LEFT JOIN Zone          	ON Loc.Zone     = Zone.ID
                		LEFT JOIN JobType       	ON Job.Type     = JobType.ID
                		LEFT JOIN OwnerWithRol  	ON Job.Owner    = OwnerWithRol.ID
                		LEFT JOIN Elev          	ON Job.Elev     = Elev.ID
                		LEFT JOIN Route         	ON Loc.Route    = Route.ID
                		LEFT JOIN Emp           	ON Emp.fWork    = Route.Mech
						LEFT JOIN Violation     	ON Job.ID       = Violation.Job
						LEFT JOIN Owner 			ON Owner.ID 	= Loc.Owner
						LEFT JOIN Rol AS OwnerRol 	ON OwnerRol.ID  = Owner.Rol
            	WHERE 	Job.ID = ?;',
           array(
           	$_GET[ 'ID' ]
           )
       );
       $Job = sqlsrv_fetch_array($result);
?>?><!DOCTYPE html>
<html lang="en">
<head>
    <title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
    <?php
    	$_GET[ 'Bootstrap' ] = '5.1';
    	require( bin_meta . 'index.php');
    	require( bin_css  . 'index.php');
    	require( bin_js   . 'index.php');
    ?><style>
    	.link-page {
    		font-size : 14px;
    	}
    </style>
</head>
<body>
  <div id="wrapper">
    <?php require( bin_php . 'element/navigation.php');?>
    <div id="page-wrapper" class='content'>
      <div class='card card-primary border-0'>
        <div class='card-heading'><h4><?php \singleton\fontawesome::getInstance( )->Job();?> Job: <?php echo $Job[ 'Name' ];?></h4></div>
        <div class='card-body'>
          <div class='card-columns'>
            <div class='card card-primary border-0'>
              <div class='card-heading'>Information</div>
              <div class='card-body'>
                <div class='row g-0'>
                  <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Job(1);?> ID</div>
                  <div class='col-8'><?php echo $Job['ID'];?></div>
                </div>
                <div class='row g-0'>
                  <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Name</div>
                  <div class='col-8'><?php echo $Job['Name'];?></div>
                </div>
                <div class='row g-0'>
                  <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Start Date</div>
                  <div class='col-8'><?php echo date("m/d/Y",strtotime($Job['Start_Date']));?></div>
                </div>
                <div class='row g-0'>
                  <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Timetable</div>
                  <div class='col-8'><?php echo strlen($Job['Budgeted_Hours']) > 0 ? $Job['Budgeted_Hours'] : "Null";?> hrs</div>
                </div>
                <div class='row g-0'>
                  <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Type</div>
                  <div class='col-8'><?php echo $Job['Type'];?></div>
                </div>
                <div class='row g-0'>
                  <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Notes</div>
                  <div class='col-8'><pre><?php echo $Job['Remarks'];?></pre></div>
                </div>
              </div>
            </div>
            <div class='card card-primary border-0'>
              <div class='card-heading'>Location</div>
              <div class='card-body'>
                <div class='row g-0' style='border-bottom:3px ;padding-top:10px;padding-bottom:10px;'>
                  <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Location(1);?> Name:</div>
                  <div class='col-8'><?php echo $Job['Location_Name'];?></div>
                </div>
                <div class='row g-0'>
                  <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Address(1);?> Street:</div>
                  <div class='col-8'><?php echo $Job['Location_Street'];?></div>
                </div>
                <div class='row g-0'>
                  <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> City:</div>
                  <div class='col-8'><?php echo $Job['Location_City'];?></div>
                </div>
                <div class='row g-0'>
                  <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> State:</div>
                  <div class='col-8'><?php echo $Job['Location_State'];?></div>
                </div>
                <div class='row g-0'>
                  <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Zip:</div>
                  <div class='col-8'><?php echo $Job['Location_Zip'];?></div>
                </div>
              </div>
            </div>
            <div class='card card-primary border-0'>
              <div class='card-heading'>Customer</div>
              <div class='card-body'>
                <div class='row g-0'>
                  <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Customer(1);?> Customer </div>
                  <div class='col-8'><?php echo $Job['Customer_Street'];?></div>
                </div>
                <div class='row g-0'>
                  <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Address</div>
                  <div class='col-8'><?php echo $Job['Customer_Name'];?></div>
                </div>
                <div class='row g-0'>
                  <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> City</div>
                  <div class='col-8'><?php echo $Job['Customer_City'];?></div>
                </div>
                <div class='row g-0'>
                  <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> State</div>
                  <div class='col-8'><?php echo $Job['Customer_State'];?></div>
                </div>
                <div class='row g-0'>
                  <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Zip</div>
                  <div class='col-8'><?php echo $Job['Customer_Zip'];?></div>
                </div>
                <div class='row g-0'>
                  <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Status</div>
                  <div class='col-8'><?php echo $Job['Customer_Status'] == 0 ? "Active" : "Unactive";?></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
  <?php require('bin/js/flotcharts.php');?>
</div>
</body>
</html>
<?php
    }
} else {require('404.html');}?>
