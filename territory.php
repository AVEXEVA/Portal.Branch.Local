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
    if(     !isset( $Connection[ 'ID' ] )
        ||  !isset( $Privileges[ 'Territory' ] )
        ||  !check( privilege_read, level_group, $Privileges[ 'Territory' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
         $ID = isset( $_GET[ 'ID' ] )
            ? $_GET[ 'ID' ]
            : (
                isset( $_POST[ 'ID' ] )
                    ? $_POST[ 'ID' ]
                    : null
                );
        $Email = isset( $_GET[ 'Email' ] )
            ? $_GET[ 'Email' ]
            : (
                isset( $_POST[ 'Email' ] )
                    ? $_POST[ 'Email' ]
                    : null
            );
        $result = $database->query(
            'Portal',
            "   SELECT  Top 1
                        *
                FROM    dbo.[User]
                WHERE   [User].[ID] = ?;",
          array(
            $ID,
            $Email
          )
        );
        $User =   (       empty( $ID )
                        &&    !empty( $Name )
                        &&    !$result
                      ) || (  empty( $ID )
                        &&    empty( $Name )
                      )  ? array(
            'ID' => null,
            'Email' => null
        ) : sqlsrv_fetch_array( $result );
        if( isset( $_POST ) && count( $_POST ) > 0 ){
            $User[ 'Email' ] = isset( $_POST[ 'Email' ] ) ? $_POST[ 'Email' ] : $User[ 'Email' ];
            if( empty( $_POST[ 'ID' ] ) ){
                $result = \singleton\database::getInstance( )->query(
                  'Portal',
                  " INSERT INTO dbo.[User]( Email )
                    VALUES( ? );
                    SELECT Max( ID ) FROM dbo.[User];",
                    array(
                        $_POST[ 'Email' ]
                    )
                );
                sqlsrv_next_result( $result );
                $User[ 'ID' ] = sqlsrv_fetch_array( $result )[ 0 ];
                header( 'Location: territory.php?ID=' . $User[ 'ID' ] );
                exit;
            } else {
                \singleton\database::getInstance( )->query(
                    'Portal',
                    "   UPDATE  dbo.[User]
                        SET     [User].[Email] = ?,
                        WHERE   [User].[ID] = ?;",
                    array(
                        $User[ 'Email' ],
                        $User[ 'ID' ]
                    )
                );
            }
        }
        $ID = isset( $_GET[ 'ID' ] )
  			? $_GET[ 'ID' ]
  			: (
  				isset( $_POST[ 'ID' ] )
  					? $_POST[ 'ID' ]
  					: null
  			);
      		$Name = isset( $_GET[ 'Name' ] )
          		? $_GET[ 'Name' ]
          		: (
          			isset( $_POST[ 'Name' ] )
          				? $_POST[ 'Name' ]
          				: null
        	);
    // sets $ID, $Name Variable and Posts ID and Name into $resultesult
          $result = \singleton\database::getInstance( )->query(
          	null,
              "	SELECT 	Top 1
              			   Territory.*
              	FROM    (
              				SELECT 	Terr.ID             AS ID,
                  						Terr.Name           AS Name,
                  						Terr.SMan           AS SMAN,
                          		Terr.SDesc          AS Description,
                              Terr.Remarks        AS Remarks,
                              Terr.Count          AS Count,
                              Terr.Symbol         AS Symbol,
                          		Terr.EN             AS EN,
        	                    Terr.Address        AS Address,
                              Terr.TFMID          AS TFMID,
                              Terr.TFMSource      AS TFMSource
  							    FROM    Terr
              		) AS      Territory
              	WHERE   	Territory.ID = ?
              			OR 	  Territory.Name = ?;",
              array(
              	$ID,
              	$Name
                    )
                );
                $Territory =   (  empty( $ID )
                             &&  !empty( $Name )
                             &&  !$result
                        )    || (empty( $ID )
                             &&  empty( $Name )
                        )    ? array(
          	'ID' => null,
            'Name' => null,
          	'SMan' => null,
          	'SDesc' => null,
          	'Remarks' => null,
          	'Count' => null,
          	'Symbol' => null,
          	'EN' => null,
          	'Address' => null,
            'TFMID' => null,
          	'TFMSource' => null,
          ) : sqlsrv_fetch_array($result);
  //Binds $ID, $Name, $Territory and query values into the $resultesult variable

          if( isset( $_POST ) && count( $_POST ) > 0 ){
            // if the $_Post is set and the count is null, select if available
          	$Territory[ 'ID' ] 		= isset( $_POST[ 'ID' ] ) 	 ? $_POST[ 'ID' ] 	 : $Territory[ 'ID' ];
    	      $Territory[ 'Name' ] 	= isset( $_POST[ 'Name' ] ) ? $_POST[ 'Name' ] : $Territory[ 'Name' ];
          	$Territory[ 'SMan' ] 		= isset( $_POST[ 'SMan' ] ) 	 ? $_POST[ 'SMan' ] 	 : $Territory[ 'SMan' ];
          	$Territory[ 'SDesc' ] 		= isset( $_POST[ 'SDesc' ] ) 	 ? $_POST[ 'SDesc' ] 	 : $Territory[ 'SDesc' ];
          	$Territory[ 'Remarks' ] 		= isset( $_POST[ 'Remarks' ] ) 	 ? $_POST[ 'Remarks' ] 	 : $Territory[ 'Remarks' ];
          	$Territory[ 'Count' ] = isset( $_POST[ 'Count' ] )  ? $_POST[ 'Count' ]  : $Territory[ 'Count' ];
          	$Territory[ 'Symbol' ] = isset( $_POST[ 'Symbol' ] )  ? $_POST[ 'Symbol' ]  : $Territory[ 'Symbol' ];
          	$Territory[ 'EN' ]     = isset( $_POST[ 'EN' ] ) 	   ? $_POST[ 'EN' ] 	   : $Territory[ 'EN' ];
          	$Territory[ 'Address' ] 	= isset( $_POST[ 'Address' ] ) 	 ? $_POST[ 'Address' ] 	 : $Territory[ 'Address' ];
          	$Territory[ 'TFMID' ] 	= isset( $_POST[ 'TFMID' ] ) 	 ? $_POST[ 'TFMID' ] 	 : $Territory[ 'TFMID' ];
          	$Territory[ 'TFMSource' ] = isset( $_POST[ 'TFMSource' ] )  ? $_POST[ 'TFMSource' ]  : $Territory[ 'TFMSource' ];
          	$Territory[ 'Address' ] 	= isset( $_POST[ 'Address' ] ) 	 ? $_POST[ 'Address' ] 	 : $Territory[ 'Address' ];
            $Territory[ 'Price' ] 	= isset( $_POST[ 'Price' ] ) 	 ? $_POST[ 'Price' ] 	 : $Territory[ 'Price' ];
          	if( in_array( $_POST[ 'ID' ], array( null, 0, '', ' ' ) ) ){
          		$result = \singleton\database::getInstance( )->query(
          			null,
          			"	DECLARE @MAXID INT;
          				SET @MAXID = CASE WHEN ( SELECT Max( ID ) FROM Terr ) IS NULL THEN 0 ELSE ( SELECT Max( ID ) FROM Terr ) END ;
          				INSERT INTO Terr(
                    ID,
          					Name,
          					SDesc,
                    Remarks,
                    Count,
                    Symbol,
                    EN,
                    Address,
          					TFMID,
                    TFMSource
          				)
          				VALUES( @MAXID + 1 , ?, ?, ?, ?, ?, ?, ?, ?, ? );
          				SELECT @MAXID + 1;",
          			array(
          				$Territory[ 'ID' ],
                  $Territory[ 'Name' ],
                  $Territory[ 'SDesc' ],
                  $Territory[ 'Remarks' ],
                  $Territory[ 'Count' ],
                  $Territory[ 'Symbol' ],
                  $Territory[ 'EN' ],
                  $Territory[ 'Address' ],
                  $Territory[ 'TFMID' ],
                  $Territory[ 'TFMSource' ],
                  $Territory[ 'Price' ]
          			)
          		);
          		sqlsrv_next_result( $result );
      //Update query to fill values for $Territory and appends to $resultesult for any updated colums
          		$Territory[ 'Rolodex' ] = sqlsrv_fetch_array( $result )[ 0 ];
  // finds any result with the value of 0/ null
  // query that inserts values into the $Territory [rolodex] variable datatable and appends it to the $resultesult variable
          		sqlsrv_next_result( $result );
          		$Territory[ 'ID' ] = sqlsrv_fetch_array( $result )[ 0 ];
  // Checks the $Territory[ID] for any fields that are null, if none exit,


            	header( 'Location: territory.php?ID=' . $Territory[ 'ID' ] );
          		exit;
          	} else {
          		\singleton\database::getInstance( )->query(
  	        		null,
  	        		"	UPDATE 	Territory
  	        			SET Territory.ID = ?,
  	        					Territory.Name = ?,
  	        					Territory.SMan = ?,
  	        					Territory.SDesc = ?,
  	        					Territory.Remarks = ?,
                      Territory.Count = ?,
                      Territory.Symbol = ?,
                      Territory.EN = ?,
                      Territory.Address = ?,
                      Territory.TFMID = ?,
                      Territory.TFMSource = ?,
  	        			WHERE 	Terr.ID = ?;",
  	        		array(
                  $Territory[ 'Name' ],
                  $Territory[ 'SMan' ],
                  $Territory[ 'SDesc' ],
                  $Territory[ 'Remarks' ],
                  $Territory[ 'Count' ],
                  $Territory[ 'Symbol' ],
                  $Territory[ 'EN' ],
                  $Territory[ 'Address' ],
                  $Territory[ 'TFMID' ],
                  $Territory[ 'TFMSource' ],
                  $Territory[ 'Price' ]
  	        		)
  	        	);
          	}
          }
?><!DOCTYPE html>
<html lang="en">
<head>
  <title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
     <?php  $_GET[ 'Bootstrap' ] = '5.1';?>
     <?php  $_GET[ 'Entity_CSS' ] = 1;?>
     <?php	require( bin_meta . 'index.php');?>
     <?php	require( bin_css  . 'index.php');?>
     <?php  require( bin_js   . 'index.php');?>
</head>
<body onload='finishLoadingPage();'>
  <div id="wrapper">
    <?php require(bin_php .'element/navigation.php');?>
    <div id="page-wrapper" class='content'>
      <div class='card card-primary'>
        <div class='card-heading'>
          <div class='row g-0 px-3 py-2'>
            <div class='col-12 col-lg-6'>
                <h5><?php \singleton\fontawesome::getInstance( )->Location( 1 );?><a href='territories.php?<?php
                  echo http_build_query( is_array( $_SESSION[ 'Tables' ][ 'Territories' ][ 0 ] ) ? $_SESSION[ 'Tables' ][ 'Territories' ][ 0 ] : array( ) );
                ?>'>Territories</a>: <span><?php
                  echo is_null( $Territory[ 'ID' ] )
                      ? 'New'
                      : '#' . $Territory[ 'ID' ];
                ?></span></h5>
            </div>
            <div class='col-6 col-lg-3'>
                <div class='row g-0'>
                  <div class='col-4'>
                    <button
                        class='form-control rounded'
                        onClick="document.location.href='territory.php';"
                      ><?php \singleton\fontawesome::getInstance( 1 )->Save( 1 );?><span class='desktop'> Save</span></button>
                  </div>
                  <div class='col-4'>
                      <button
                        class='form-control rounded'
                        onClick="document.location.href='territory.php?ID=<?php echo $User[ 'ID' ];?>';"
                      ><?php \singleton\fontawesome::getInstance( 1 )->Refresh( 1 );?><span class='desktop'> Refresh</span></button>
                  </div>
                  <div class='col-4'>
                      <button
                        class='form-control rounded'
                        onClick="document.location.href='territory.php';"
                      ><?php \singleton\fontawesome::getInstance( 1 )->Add( 1 );?><span class='desktop'> New</span></button>
                  </div>
              </div>
            </div>
            <div class='col-6 col-lg-3'>
                <div class='row g-0'>
                  <div class='col-4'><button class='form-control rounded' onClick="document.location.href='territory.php?ID=<?php echo !is_null( $User[ 'ID' ] ) ? array_keys( $_SESSION[ 'Tables' ][ 'Users' ], true )[ array_search( $User[ 'ID' ], array_keys( $_SESSION[ 'Tables' ][ 'Users' ], true ) ) - 1 ] : null;?>';"><?php \singleton\fontawesome::getInstance( 1 )->Previous( 1 );?><span class='desktop'> Previous</span></button></div>
                  <div class='col-4'><button class='form-control rounded' onClick="document.location.href='territories.php?<?php echo http_build_query( is_array( $_SESSION[ 'Tables' ][ 'Users' ][ 0 ] ) ? $_SESSION[ 'Tables' ][ 'Users' ][ 0 ] : array( ) );?>';"><?php \singleton\fontawesome::getInstance( 1 )->Table( 1 );?><span class='desktop'> Table</span></button></div>
                  <div class='col-4'><button class='form-control rounded' onClick="document.location.href='territory.php?ID=<?php echo !is_null( $User[ 'ID' ] )? array_keys( $_SESSION[ 'Tables' ][ 'Users' ], true )[ array_search( $User[ 'ID' ], array_keys( $_SESSION[ 'Tables' ][ 'Users' ], true ) ) + 1 ] : null;?>';"><?php \singleton\fontawesome::getInstance( 1 )->Next( 1 );?><span class='desktop'> Next</span></button></div>
                </div>
            </div>
          </div>
        </div>
      <div class='card-body bg-dark text-white'>
        <div class='card-columns'>
          <div class='card card-primary my-3'><form action='territory.php?ID=<?php echo $Territory[ 'ID' ];?>' method='POST'>
            <div class='card-heading position-relative' style='z-index:1;'>
              <div class='row g-0 px-3 py-2'>
                <div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Info( 1 );?><span>Locations</span></h5></div>
                <div class='col-2'>&nbsp;</div>
                <input type='hidden' value='<?php echo $User[ 'ID' ];?>' name='ID' />
              </div>
            </div>
          <!-- Second card headding that holds vio.php information and fontawesome icon, the POST call retrieves information from $Territory ID    -->
          <div class='card-body bg-dark text-white' <?php echo isset( $_SESSION[ 'Cards' ][ 'Infomation' ] ) && $_SESSION[ 'Cards' ][ 'Territories' ] == 0 ? "style='display:none;'" : null;?>>
            <input type='hidden' name='ID' value='<?php echo $Territory[ 'ID' ];?>' />
            <!-- Selector for status that has echos the Customer Status and checks the value 0/1 and assignes a color -Warning or -Success  -->
              <div class='col-6'>
              </div>
              <div class='col-2'>
          </div>
          <div class='row g-0'>
            <div class='col-4 border-bottom border-white my-auto'>Count:</div>
            <div class='col-8'><input type='text' class='form-control edit' name='Address' value='<?php echo $Territory['Count'];?>' /></div>
          </div>
            <div class='col-2'>&nbsp;</div>
          </div>
          <div class='row g-0'>
          </div>
        </div>
            <div class='card card-primary my-3'>
              <div class='card-heading'>
                <div class='row g-0 px-3 py-2'>
              <div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Unit( 1 );?><span>Units</span></h5></div>
            <div class='col-2'><button class='h-100 w-100' onClick="document.location.href='units.php?Territory=<?php echo $Territory[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
          </div>
          </div>
            <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Units' ] ) && $_SESSION[ 'Cards' ][ 'Units' ] == 0 ? "style='display:none;'" : null;?>>
              <?php
                $result = \singleton\database::getInstance( )->query(
                  null,
                  "	SELECT 	Count( Unit.ID ) AS Units
                    FROM   	Elev AS Unit
                          LEFT JOIN Loc AS Location ON Unit.Loc = Location.Loc
                    WHERE  	Location.Terr = ? ;",
                  array(
                    $Territory[ 'ID' ]
                  )
                );
              ?>
              <div class='row g-0'>
                  <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Unit(1);?> Type</div>
                  <div class='col-6'>&nbsp;</div>
                <div class='col-2'>&nbsp;</div>
              </div>
              <div class='row g-0'>
                <div class='col-1'>&nbsp;</div>
                  <div class='col-3 border-bottom border-white my-auto'>Elevators</div>
                  <div class='col-6'><input class='form-control' type='text' readonly name='Units' value='<?php
                  $result = \singleton\database::getInstance( )->query(
                    null,
                    "	SELECT 	Count( Unit.ID ) AS Units
                      FROM   	Elev AS Unit
                            LEFT JOIN Loc AS Location ON Unit.Loc = Location.Loc
                      WHERE  		Location.Terr = ?
                          AND Unit.Type = 'Elevator'
                  ;",array($Territory[ 'ID' ]));
                  //Selects the unit.ID as counts from Elev and adds it to $Territory[ID]
                  echo $result ? sqlsrv_fetch_array($result)['Units'] : 0;
                ?>' /></div>
                <div class='col-2'><button class='h-100 w-100' onClick="document.location.href='units.php?Customer=<?php echo $Territory[ 'Name' ];?>&Type=Elevator';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
              </div>
              <div class='row g-0'>
                <div class='col-1'>&nbsp;</div>
                  <div class='col-3 border-bottom border-white my-auto'>Escalators</div>
                  <div class='col-6'><input class='form-control' type='text' readonly name='Units' value='<?php
                  $result = \singleton\database::getInstance( )->query(null,
                    " SELECT 	Count( Unit.ID ) AS Units
                      FROM   	Elev AS Unit
                          LEFT JOIN Loc AS Location ON Unit.Loc = Location.Loc
                      WHERE  		Location.Terr = ?
                      AND Unit.Type = 'Escalator'
                  ;",array($Territory[ 'ID' ]));
                  echo $result ? sqlsrv_fetch_array($result)['Units'] : 0;
                ?>' /></div>
                <div class='col-2'><button class='h-100 w-100' onClick="document.location.href='units.php?Customer=<?php echo $Territory[ 'Name' ];?>&Type=Escalator';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
              </div>
              <div class='row g-0'>
                <div class='col-1'>&nbsp;</div>
                  <div class='col-3 border-bottom border-white my-auto'>Other</div>
                  <div class='col-6'><input class='form-control' type='text' readonly name='Units' value='<?php
                  $result = \singleton\database::getInstance( )->query(null,
                    " SELECT 	Count( Unit.ID ) AS Units
                      FROM   	Elev AS Unit
                          LEFT JOIN Loc AS Location ON Unit.Loc = Location.Loc
                      WHERE  		Location.Terr = ?
                        AND Unit.Type NOT IN ( 'Elevator', 'Escalator' )
                  ;",array($Territory[ 'ID' ]));
                  echo $result ? sqlsrv_fetch_array($result)['Units'] : 0;
                ?>' /></div>
                <div class='col-2'><button class='h-100 w-100' readonly onClick="document.location.href='units.php?Customer=<?php echo $Territory[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
            </div>
          </div>
        </div>
            <div class='card card-primary my-3'>
							<div class='card-heading'>
								<div class='row g-0 px-3 py-2'>
									<div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Ticket( 1 );?><span>Tickets</span></h5></div>
									<div class='col-2'></div>
								</div>
							</div>
							<div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Tickets' ] ) && $_SESSION[ 'Cards' ][ 'Tickets' ] == 0 ? "style='display:none;'" : null;?>>
								<div class='row'>
								    <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Ticket(1);?> Statuses</div>
								    <div class='col-6'>&nbsp;</div>
									<div class='col-2'>&nbsp;</div>
								</div>
								<div class='row g-0'><?php
										$result = \singleton\database::getInstance( )->query(
											null,
											"	SELECT Count( Tickets.ID ) AS Tickets
												FROM   (
															(
																SELECT 	TicketO.ID AS ID
																FROM   	TicketO
																	   	LEFT JOIN Loc AS Location ON TicketO.LID = Location.Loc
																WHERE  		Location.Terr = ?
																		AND TicketO.Assigned = 0
															)
														) AS Tickets;",
											array(
												$Territory[ 'ID' ]
											)
										);
									?><div class='col-1'>&nbsp;</div>
								    <div class='col-3 border-bottom border-white my-auto'>Open</div>
								    <div class='col-6'><input class='form-control' type='text' readonly name='Tickets' value='<?php
										echo $result ? sqlsrv_fetch_array($result)[ 'Tickets' ] : 0;
									?>' /></div>
									<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='tickets.php?Customer=<?php echo $Territory[ 'Name' ];?>&Status=0';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
								</div>
								<div class='row g-0'><?php
										$result = \singleton\database::getInstance( )->query(
											null,
											"	SELECT Count( Tickets.ID ) AS Tickets
												FROM   (
															(
																SELECT 	TicketO.ID AS ID
																FROM   	TicketO
																	   	LEFT JOIN Loc AS Location ON TicketO.LID = Location.Loc
																WHERE  		Location.Terr = ?
																		AND TicketO.Assigned = 1
															)
														) AS Tickets;",
											array(
												$Territory[ 'ID' ]
											)
										);
									?><div class='col-1'>&nbsp;</div>
								    <div class='col-3 border-bottom border-white my-auto'>Assigned</div>
								    <div class='col-6'><input class='form-control' type='text' readonly name='Tickets' value='<?php
										echo $result ? sqlsrv_fetch_array($result)[ 'Tickets' ] : 0;
									?>' /></div>
									<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='tickets.php?Customer=<?php echo $Territory[ 'Name' ];?>&Status=1';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
								</div>
								<div class='row g-0'><?php
										$result = \singleton\database::getInstance( )->query(
											null,
											"	SELECT Count( Tickets.ID ) AS Tickets
												FROM   (
															(
																SELECT 	TicketO.ID AS ID
																FROM   	TicketO
																	   	LEFT JOIN Loc AS Location ON TicketO.LID = Location.Loc
																WHERE  		Location.Terr = ?
																		AND TicketO.Assigned = 2
															)
														) AS Tickets;",
											array(
												$Territory[ 'ID' ]
											)
										);
									?><div class='col-1'>&nbsp;</div>
								    <div class='col-3 border-bottom border-white my-auto'>En Route</div>
								    <div class='col-6'><input class='form-control' type='text' readonly name='Tickets' value='<?php
										echo $result ? sqlsrv_fetch_array($result)[ 'Tickets' ] : 0;
									?>' /></div>
									<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='tickets.php?Customer=<?php echo $Territory[ 'Name' ];?>&Status=2';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
								</div>
								<div class='row g-0'><?php
										$result = \singleton\database::getInstance( )->query(
											null,
											"	SELECT Count( Tickets.ID ) AS Tickets
												FROM   (
															(
																SELECT 	TicketO.ID AS ID
																FROM   	TicketO
																	   	LEFT JOIN Loc AS Location ON TicketO.LID = Location.Loc
																WHERE  		Location.Terr = ?
																		AND TicketO.Assigned = 3
															)
														) AS Tickets;",
											array(
												$Territory[ 'ID' ]
											)
										);
									?><div class='col-1'>&nbsp;</div>
								    <div class='col-3 border-bottom border-white my-auto'>On Site</div>
								    <div class='col-6'><input class='form-control' type='text' readonly name='Tickets' value='<?php
										echo $result ? sqlsrv_fetch_array($result)[ 'Tickets' ] : 0;
									?>' /></div>
									<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='tickets.php?Customer=<?php echo $Territory[ 'Name' ];?>&Status=3';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
								</div>
								<div class='row g-0'><?php
										$result = \singleton\database::getInstance( )->query(
											null,
											"	SELECT Count( Tickets.ID ) AS Tickets
												FROM   (
															(
																SELECT 	TicketO.ID AS ID
																FROM   	TicketO
																	   	LEFT JOIN Loc AS Location ON TicketO.LID = Location.Loc
																WHERE  		Location.Terr = ?
																		AND TicketO.Assigned = 6
															)
														) AS Tickets;",
											array(
												$Territory[ 'ID' ]
											)
										);
									?>
                  <div class='col-1'>&nbsp;</div>
								    <div class='col-3 border-bottom border-white my-auto'>Review</div>
								    <div class='col-6'><input class='form-control' type='text' readonly name='Tickets' value='<?php
										echo $result ? sqlsrv_fetch_array($result)[ 'Tickets' ] : 0;
									?>' /></div>
									<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='tickets.php?Customer=<?php echo $Territory[ 'Name' ];?>&Status=6';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
								</div>
								<div class='row g-0'><?php
										$result = \singleton\database::getInstance( )->query(
											null,
											"	SELECT Count( Tickets.ID ) AS Tickets
												FROM   (
															(
																SELECT 	TicketO.ID AS ID
																FROM   	TicketO
																	   	LEFT JOIN Loc AS Location ON TicketO.LID = Location.Loc
																WHERE  		Location.Terr = ?
																		AND TicketO.Assigned = 4
															)
														) AS Tickets;",
											array(
												$Territory[ 'ID' ]
											)
										);
									?><div class='col-1'>&nbsp;</div>
								    <div class='col-3 border-bottom border-white my-auto'>Complete</div>
								    <div class='col-6'><input class='form-control' type='text' readonly name='Tickets' value='<?php
										echo $result ? sqlsrv_fetch_array($result)[ 'Tickets' ] : 0;
									?>' /></div>
									<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='tickets.php?Customer=<?php echo $Territory[ 'Name' ];?>&Status=4';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                </div>
              </div>
                </div>
                <div class='card card-primary my-3'>
                  <div class='card-heading'>
                    <div class='row g-0 px-3 py-2'>
                      <div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Collection( 1 );?><span>Collections</span></h5></div>
                      <div class='col-2'><button class='h-100 w-100' onClick="document.location.href='collections.php?Location=<?php echo $Territory [ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                    </div>
                  </div>
                  <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Collections' ] ) && $_SESSION[ 'Cards' ][ 'Collections' ] == 0 ? "style='display:none;'" : null;?>>
                    <div class='row g-0'>
                        <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Dollar(1);?> Balance</div>
                        <div class='col-6'><input class='form-control' type='text' readonly name='Balance' value='<?php
                        $result = \singleton\database::getInstance( )->query(null,
                          " SELECT Sum( OpenAR.Balance ) AS Balance
                            FROM   OpenAR
                               LEFT JOIN Loc AS Location ON OpenAR.Loc = Location.Loc
                            WHERE  Location.Terr = ?
                        ;",array($Territory [ 'ID' ]));
                        $Balance = $result ? sqlsrv_fetch_array($result)['Balance'] : 0;
                        echo money_format('%(n',$Balance);
                      ?>' /></div>
                      <div class='col-2'>&nbsp;</div>
                    </div>
                  </div>
                </div>
                <div class='card card-primary my-3'>
                  <div class='card-heading'>
                    <div class='row g-0 px-3 py-2'>
                      <div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Invoice( 1 );?><span>Invoices</span></h5></div>
                      <div class='col-2'><button class='h-100 w-100' onClick="document.location.href='invoices.php?Location=<?php echo $Territory [ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                    </div>
                  </div>
                  <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Admins' ] ) && $_SESSION[ 'Cards' ][ 'Admins' ] == 0 ? "style='display:none;'" : null;?>>
                    <div class='row g-0'>
                        <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Invoice( 1 );?> Invoices</div>
                        <div class='col-6'>&nbsp;</div>
                      <div class='col-2'>&nbsp;</div>
                    </div>
                    <?php if(isset($Privileges['Invoice']) ) {?>
                    <div class='row g-0'>
                      <div class='col-1'>&nbsp;</div>
                        <div class='col-3 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Invoice(1);?> Open</div>
                        <div class='col-6'><input class='form-control' type='text' readonly name='Collections' value='<?php
                        $result = \singleton\database::getInstance( )->query(null,"
                          SELECT Count( OpenAR.Ref ) AS Count
                          FROM   OpenAR
                               LEFT JOIN Loc AS Location ON OpenAR.Loc = Location.Loc
                          WHERE  Location.Terr = ?
                        ;",array($Territory [ 'ID' ]));
                        $Count = $result ? sqlsrv_fetch_array($result)['Count'] : 0;
                        echo $Count
                      ?>' /></div>
                      <div class='col-2'><button class='h-100 w-100' onClick="document.location.href='collections.php?Location=<?php echo $Territory [ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                    </div>
                    <?php }?>
                    <?php if(isset($Privileges['Invoice']) ) {?>
                    <div class='row g-0'>
                      <div class='col-1'>&nbsp;</div>
                        <div class='col-3 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Invoice(1);?> Closed</div>
                        <div class='col-6'><input class='form-control' type='text' readonly name='Collections' value='<?php
                        $result = \singleton\database::getInstance( )->query(null,"
                          SELECT 	Count( Invoice.Ref ) AS Count
                          FROM   	Invoice
                                LEFT JOIN Loc AS Location ON OpenAR.Loc = Location.Loc
                          WHERE  		Location.Terr = ?
                              AND Invoice.Ref NOT IN ( SELECT Ref FROM OpenAR )
                        ;",array($Territory [ 'ID' ]));
                        $Count = $result ? sqlsrv_fetch_array($result)['Count'] : 0;
                        echo $Count
                      ?>' /></div>
                      <div class='col-2'><button class='h-100 w-100' onClick="document.location.href='collections.php?Location=<?php echo $Territory [ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                    </div>
                    <?php }?>
                  </div>
                </div>
                  <div class='card card-primary my-3'>
          					<div class='card-heading'>
          						<div class='row g-0 px-3 py-2'>
          							<div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Proposal( 1 );?><span>Proposals</span></h5></div>
          							<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='proposals.php?Customer=<?php echo $Territory [ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
          						</div>
          					</div>
          					<div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Proposals' ] ) && $_SESSION[ 'Cards' ][ 'Proposals' ] == 0 ? "style='display:none;'" : null;?>>
          						<div class='row g-0'>
          						    <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Proposal(1);?> Status</div>
          						    <div class='col-6'>&nbsp;</div>
          							<div class='col-2'>&nbsp;</div>
          						</div>
          						<div class='row g-0'>
          							<div class='col-1'>&nbsp;</div>
          						    <div class='col-3 border-bottom border-white my-auto'>Open</div>
          						    <div class='col-6'><input class='form-control' type='text' readonly name='Proposals' value='<?php
          								$result = \singleton\database::getInstance( )->query(null,"
          									SELECT 	Count(Estimate.ID) AS Proposals
          									FROM   	Estimate
          										   	LEFT JOIN Loc AS Location ON Estimate.LocID = Location.Loc
          									WHERE  		Location.Terr = ?
          											AND Estimate.Status = 0
          								;",array($Territory [ 'ID' ]));
          								echo $result ? sqlsrv_fetch_array($result)['Proposals'] : 0;
          							?>' /></div>
          							<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='proposals.php?Customer=<?php echo $Territory [ 'Name' ];?>&Status=0';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
          						</div>
          						<div class='row g-0'>
          							<div class='col-1'>&nbsp;</div>
          						    <div class='col-3 border-bottom border-white my-auto'>Awarded</div>
          						    <div class='col-6'><input class='form-control' type='text' readonly name='Proposals' value='<?php
          								$result = \singleton\database::getInstance( )->query(null,"
          									SELECT 	Count(Estimate.ID) AS Proposals
          									FROM   	Estimate
          										   	LEFT JOIN Loc AS Location ON Estimate.LocID = Location.Loc
          									WHERE  		Location.Terr = ?
          											AND Estimate.Status = 4
          								;",array($Territory [ 'ID' ]));
          								echo $result ? sqlsrv_fetch_array($result)['Proposals'] : 0;
          							?>' /></div>
          							<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='proposals.php?Customer=<?php echo $Territory [ 'Name' ];?>&Status=4';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
          						</div>
          					</div>
          				</div>
              <div class='card card-primary my-3'><form action='customer.php?ID=<?php echo $Territory[ 'ID' ];?>' method='POST'>
                  <input type='hidden' name='ID' value='<?php echo $Territory[ 'ID' ];?>' />
                <div class='card-heading'>
                  <div class='row g-0 px-3 py-2'>
                    <div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Invoice( 1 );?><span>Leads</span></h5></div>
                    <div class='col-2'>&nbsp;</div>
                  </div>
                </div>
                <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Leads' ] ) && $_SESSION[ 'Cards' ][ 'Leads' ] == 0 ? "style='display:none;'" : null;?>>
              <div class='row g-0'>
                <div class='col-4 border-bottom border-white my-auto'>Date:</div>
                <div class='col-8'><input type='date' class='form-control edit' name='Date' value='<?php echo $Territory['Date'];?>' /></div>
              </div>
              <div class='row g-0'>
                <div class='col-4 border-bottom border-white my-auto'>Remarks:</div>
                <div class='col-8'><textarea type='text' class='form-control edit' name='Remarks' value='<?php echo $Territory['Remarks'];?>' /></textarea>
              </div>
            </div>
          </div>
        </div>
		 </div>
   </div>
 </div>
</div>
</form>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=territory<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
