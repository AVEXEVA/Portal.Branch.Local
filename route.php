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
        ||  !isset( $Privileges[ 'Route' ] )
        ||  !check( privilege_read, level_group, $Privileges[ 'Route' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
        \singleton\database::getInstance( )->query(
          null,
          " INSERT INTO Activity([User], [Date], [Page] )
            VALUES( ?, ?, ? );",
          array(
            $_SESSION[ 'Connection' ][ 'User' ],
            date('Y-m-d H:i:s'),
            'route.php'
        )
      );
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
      $result = \singleton\database::getInstance( )->query(
        null,
        "SELECT   Route.ID              AS ID,
                  Route.Name            AS Name,
                  Employee.ID           AS Employee_ID,
                  Employee.fFirst + ' ' + Employee.Last AS Employee_Name
          FROM    Route
                  LEFT JOIN Emp  AS Employee  ON  Route.Mech = Employee.fWork
          WHERE       Route.ID =   ?
                  OR  Route.Name = ?;",
        array(
          $ID,
          $Name
        )
      );
      $Route =   (          empty( $ID )
                      &&    !empty( $Name )
                      &&    !$result
                    ) || (  empty( $ID )
                      &&    empty( $Name )
                    )  ? array(
        'ID' => null,
        'Name' => null,
        'Employee_ID' => null,
        'Employee_Name' => null
      ) : sqlsrv_fetch_array($result);



      if( isset( $_POST ) && count( $_POST ) > 0 ){
        $Route[ 'Name' ]      = isset( $_POST[ 'Name' ] )    ? $_POST[ 'Name' ]    : $Route[ 'Name' ];
        $Route[ 'Employee_Name' ]      = isset( $_POST[ 'Employee' ] )    ? $_POST[ 'Employee' ]    : $Route[ 'Employee_Name' ];
        if( empty( $_POST[ 'ID' ] ) ){

          $result = \singleton\database::getInstance( )->query(
            null,
            " DECLARE @MAXID INT;
              DECLARE @fWork INT;
              SET @MAXID = CASE WHEN ( SELECT Max( ID ) FROM Route ) IS NULL THEN 0 ELSE ( SELECT Max( ID ) FROM Route ) END ;
              SET @fWork = ( SELECT Emp.fWork FROM Emp WHERE Emp.fFirst + ' ' + Emp.Last = ? );
              INSERT INTO Route(
                ID,
                Name,
                Mech
              )
              VALUES ( @MAXID + 1, ?, @fWork );
              SELECT @MAXID + 1;",
            array(
              $Route[ 'Employee_Name' ],
              $Route[ 'Name' ]
            )
          );
          sqlsrv_next_result( $result );
          $Route[ 'ID' ] = sqlsrv_fetch_array( $result )[ 0 ];

          header( 'Location: route.php?ID=' . $Route[ 'ID' ] );
          exit;
        } else {
          \singleton\database::getInstance( )->query(
            null,
            " DECLARE @fWork INT;
              SET @fWork = ( SELECT Emp.fWork FROM Emp WHERE Emp.fFirst + ' ' + Emp.Last = ? );
              UPDATE  Route
              SET     Route.Name = ?,
                      Route.Mech = @fWork
              WHERE   Route.ID = ?;",
            array(
              $Route[ 'Employee_Name' ],
              $Route[ 'Name' ],
              $Route[ 'ID' ]
            )
          );
        }
      }
$locations = \singleton\database::getInstance( )->query(

    null,
    "
	   SELECT Loc,Tag,fLong,Latt
     FROM   Loc
     WHERE  Loc.Route = ?
	;",array($_GET['ID'])
);
$locationArr = array();
$finalLoc= [];
if( $locations ) {
    while ($locationArr = sqlsrv_fetch_array($locations, SQLSRV_FETCH_ASSOC)) {


        $finalLoc[] = ['ID'=>$locationArr['Loc'],'Tag'=>$locationArr['Tag'],'Latitude'=>$locationArr['Latt'],'Longitude'=>$locationArr['fLong']];
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
	<title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
	<?php
		$_GET[ 'Bootstrap' ] = '5.1';
		$_GET[ 'Entity_CSS' ] = 1;
	?>
	<?php
    require( bin_meta . 'index.php' );
    require( bin_css  . 'index.php' );
    require( bin_js   . 'index.php' );
  ?><script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=AIzaSyCNrTryEaTEDRz-XDSg890ajL_JRPnLgzc"></script>
</head>
<body>
  <div id="wrapper">
    <?php require(PROJECT_ROOT.'php/element/navigation.php');?>
    <div id="page-wrapper" class='content' style='height:100%;overflow-y:scroll;'>
      <div class='card card-primary'>
        <div class='card-heading'>
          <div class='row g-0 px-3 py-2'>
            <div class='col-6'>
              <h5><?php \singleton\fontawesome::getInstance( )->Route( 1 );?><a href='routes.php?<?php
                echo http_build_query( is_array( $_SESSION[ 'Tables' ][ 'Routes' ][ 0 ] ) ? $_SESSION[ 'Tables' ][ 'Routes' ][ 0 ] : array( ) );
              ?>'>Route</a>: <span><?php
                echo is_null( $Route[ 'ID' ] )
                  ? 'New'
                  : $Route[ 'Name' ];
              ?></span></h5>
            </div>
            <div class='col-2'></div>
            <div class='col-2'>
              <div class='row g-0'>
                <div class='col-4'>
                  <button
                    class='form-control rounded'
                    onClick="document.location.href='route.php';"
                  >Create</button>
                </div>
                <div class='col-4'>
                  <button
                    class='form-control rounded'
                    onClick="document.location.href='route.php?ID=<?php echo $Route[ 'ID' ];?>';"
                  >Refresh</button>
                </div>
              </div>
            </div>
            <div class='col-2'>
              <div class='row g-0'>
                <div class='col-4'><button class='form-control rounded' onClick="document.location.href='route.php?ID=<?php echo !is_null( $Route[ 'ID' ] ) ? array_keys( $_SESSION[ 'Tables' ][ 'Routes' ], true )[ array_search( $Route[ 'ID' ], array_keys( $_SESSION[ 'Tables' ][ 'Routes' ], true ) ) - 1 ] : null;?>';">Previous</button></div>
                <div class='col-4'><button class='form-control rounded' onClick="document.location.href='routes.php?<?php echo http_build_query( is_array( $_SESSION[ 'Tables' ][ 'Routes' ][ 0 ] ) ? $_SESSION[ 'Tables' ][ 'Routes' ][ 0 ] : array( ) );?>';">Table</button></div>
                <div class='col-4'><button class='form-control rounded' onClick="document.location.href='route.php?ID=<?php echo !is_null( $Route[ 'ID' ] )? array_keys( $_SESSION[ 'Tables' ][ 'Routes' ], true )[ array_search( $Route[ 'ID' ], array_keys( $_SESSION[ 'Tables' ][ 'Routes' ], true ) ) + 1 ] : null;?>';">Next</button></div>
              </div>
            </div>
          </div>
        </div>
        <div class='card-body bg-dark text-white'>
            <div class='card-columns'>
                <div class='card card-primary my-3'> <form action='route.php?ID=<?php echo $Route[ 'ID' ];?>' method='POST'>
                        <input type='hidden' name='ID' value='<?php echo $Route[ 'ID' ];?>' />
                        <div class='card-heading'>
                            <div class='row g-0 px-3 py-2'>
                                <div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Info( 1 );?><span>Infomation</span></h5></div>
                                <div class='col-2'>&nbsp;</div>
                            </div>
                        </div>
                        <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Infomation' ] ) && $_SESSION[ 'Cards' ][ 'Infomation' ] == 0 ? "style='display:none;'" : null;?>>
                            <div class='row g-0'>
                                <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Customer(1);?>Name:</div>
                                <div class='col-8'><input type='text' class='form-control edit animation-focus' name='Name' value='<?php echo $Route['Name'];?>' /></div>
                            </div>
                            <div class='row g-0'>
                                <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->User(1);?> Employee:</div>
                                <div class='col-6'>
                                    <input type='text' autocomplete='off' class='form-control edit' name='Employee' value='<?php echo $Route[ 'Employee_Name' ];?>' />
                                    <script>
                                        $( 'input[name="Employee"]' )
                                            .typeahead({
                                                    minLength : 4,
                                                    hint: true,
                                                    highlight: true,
                                                    limit : 5,
                                                    display : 'FieldValue',
                                                    source: function( query, result ){
                                                        $.ajax({
                                                            url : 'bin/php/get/search/Employees.php',
                                                            method : 'GET',
                                                            data    : {
                                                                search :  $('input:visible[name="Employee"]').val( )
                                                            },
                                                            dataType : 'json',
                                                            beforeSend : function( ){
                                                                abort( );
                                                            },
                                                            success : function( data ){
                                                                result( $.map( data, function( item ){
                                                                    return item.FieldValue;
                                                                } ) );
                                                            }
                                                        });
                                                    },
                                                    afterSelect: function( value ){
                                                        $( 'input[name="Employee"]').val( value );
                                                        $( 'input[name="Employee"]').closest( 'form' ).submit( );
                                                    }
                                                }
                                            );
                                    </script>
                                </div>
                                <div class='col-2'><button class='h-100 w-100' type='button' <?php
                                    if( in_array( $Route[ 'Employee_ID' ], array( null, 0, '', ' ') ) ){
                                        echo "onClick=\"document.location.href='employees.php?Field=1';\"";
                                    } else {
                                        echo "onClick=\"document.location.href='employee.php?ID=" . $Route[ 'Employee_ID' ] . "';\"";
                                    }
                                    ?>><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                            </div>
                            <div class='card-footer'>
                                <div class='row'>
                                    <div class='col-12'><button class='form-control' type='submit'>Save</button></div>
                                </div>
                            </div>

                        </div>
                    </form></div>


                <?php if( count($finalLoc)>0 ){
                    ?>
                    <div class='card card-primary my-3'>
                        <div class='card-heading'>
                            <div class='row g-0 px-3 py-2'>
                                <div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Map( 1 );?><span>Map</span></h5></div>
                                <div class='col-2'>&nbsp;</div>
                            </div>
                        </div>
                        <div class='card-body bg-darker'>
                            <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB05GymhObM_JJaRCC3F4WeFn3KxIOdwEU"></script>
                            <script type="text/javascript">
                                var map;
                                function initialize() {
                                    map = new google.maps.Map(
                                        document.getElementById( 'location_map' ),
                                        {
                                            zoom: 1,
                                            center: new google.maps.LatLng( 0, 0 ),
                                            mapTypeId: google.maps.MapTypeId.ROADMAP
                                        }
                                    );
                                    /* Map Bound */
                                    var markers = [];
                                    <?php /* For Each Location Create a Marker. */
                                    foreach( $finalLoc as $location ){
                                    $name = $location['Tag'];
                                    $addr = $location['Tag'];
                                    $map_lat = $location['Latitude'];
                                    $map_lng = $location['Longitude'];
                                    if( !in_array( $map_lat, array( null, 0 ) ) && !in_array( $map_lng, array( null, 0 ) ) ){
                                    ?>

                                    markersNew = new google.maps.Marker({
                                        position: {
                                            lat: <?php echo $map_lat; ?>,
                                            lng: <?php echo $map_lng; ?>,
                                            title: '<?php echo $name; ?>',
                                        },
                                        map: map,
                                        title: '<?php echo $name; ?>',
                                        infoWindow: {
                                            content: '<p><?php echo $name; ?></p>'
                                        }
                                    });
                                    markers.push(markersNew);
                                    /* Set Bound Marker */
                                    var latlng = new google.maps.LatLng(<?php echo $map_lat; ?>, <?php echo $map_lng; ?>);
                                    bounds.push(latlng);
                                    /* Add Marker */
                                    map.addMarker({
                                        lat: <?php echo $map_lat; ?>,
                                        lng: <?php echo $map_lng; ?>,
                                        title: '<?php echo $name; ?>',
                                        infoWindow: {
                                            content: '<p><?php echo $name; ?></p>'
                                        }
                                    });
                                    <?php } } //end foreach locations ?>
                                }
                                $(document).ready(function(){ initialize(); });
                            </script>
                            <div class='card-body'>
                                <div id='location_map' class='map'>&nbsp;</div>
                            </div>
                        </div>
                    </div>
                <?php }?>
                <div class='card card-primary my-3'>
                    <div class='card-heading'>
                        <div class='row g-0 px-3 py-2'>
                            <div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Ticket( 1 );?><span>Tickets</span></h5></div>
                            <div class='col-2'><button class='h-100 w-100' type='button' onClick="document.location.href='tickets.php?Route=<?php echo $Route[ 'ID' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                        </div>
                    </div>
                    <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Tickets' ] ) && $_SESSION[ 'Cards' ][ 'Tickets' ] == 0 ? "style='display:none;'" : null;?>>
                        <div class='row g-0'>
                            <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Ticket(1);?> Statuses</div>
                            <div class='col-6'>&nbsp;</div>
                            <div class='col-2'>&nbsp;</div>
                        </div>
                        <div class='row g-0'>
                            <?php
                            $r = \singleton\database::getInstance( )->query(
                                null,
                                "	SELECT Count( Tickets.ID ) AS Tickets
												FROM   (
															(
																SELECT 	TicketO.ID AS ID
																FROM   	TicketO
																	   	LEFT JOIN Loc AS Location ON TicketO.LID = Location.Loc
																WHERE  		Location.Route = ?
																		AND TicketO.Assigned = 0
															)
														) AS Tickets;",
                                array(
                                    $Route[ 'ID' ]
                                )
                            );
                            ?>
                            <div class='col-1'>&nbsp;</div>
                            <div class='col-3 border-bottom border-white my-auto'>Unassigned</div>
                            <div class='col-6'><input class='form-control' type='text' readonly name='Tickets' value='<?php
                                echo $r ? sqlsrv_fetch_array($r)[ 'Tickets' ] : 0;
                                ?>' /></div>
                            <div class='col-2'><button class='h-100 w-100' onClick="document.location.href='tickets.php?Route=<?php echo $Route[ 'ID' ];?>&Status=0';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                        </div>
                        <div class='row g-0'>
                            <?php
                            $r = \singleton\database::getInstance( )->query(
                                null,
                                "	SELECT Count( Tickets.ID ) AS Tickets
												FROM   (
															(
																SELECT 	TicketO.ID AS ID
																FROM   	TicketO
																	   	LEFT JOIN Loc AS Location ON TicketO.LID = Location.Loc
																WHERE  		Location.Route = ?
																		AND TicketO.Assigned = 1
															)
														) AS Tickets;",
                                array(
                                    $Route[ 'ID' ]
                                )
                            );
                            ?><div class='col-1'>&nbsp;</div>
                            <div class='col-3 border-bottom border-white my-auto'>Assigned</div>
                            <div class='col-6'><input class='form-control' type='text' readonly name='Tickets' value='<?php
                                echo $r ? sqlsrv_fetch_array($r)[ 'Tickets' ] : 0;
                                ?>' /></div>
                            <div class='col-2'><button class='h-100 w-100' onClick="document.location.href='tickets.php?Route=<?php echo  $Route[ 'ID' ];?>&Status=1';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                        </div>
                        <div class='row g-0'>
                            <?php
                            $r = \singleton\database::getInstance( )->query(
                                null,
                                "	SELECT Count( Tickets.ID ) AS Tickets
												FROM   (
															(
																SELECT 	TicketO.ID AS ID
																FROM   	TicketO
																	   	LEFT JOIN Loc AS Location ON TicketO.LID = Location.Loc
																WHERE  		Location.Route = ?
																		AND TicketO.Assigned = 2
															)
														) AS Tickets;",
                                array(
                                    $Route[ 'ID' ]
                                )
                            );
                            ?><div class='col-1'>&nbsp;</div>
                            <div class='col-3 border-bottom border-white my-auto'>En Route</div>
                            <div class='col-6'><input class='form-control' type='text' readonly name='Tickets' value='<?php
                                echo $r ? sqlsrv_fetch_array($r)[ 'Tickets' ] : 0;
                                ?>' /></div>
                            <div class='col-2'><button class='h-100 w-100' onClick="document.location.href='tickets.php?Route=<?php echo  $Route[ 'ID' ];?>&Status=2';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                        </div>
                        <div class='row g-0'>
                            <?php
                            $r = \singleton\database::getInstance( )->query(
                                null,
                                "	SELECT Count( Tickets.ID ) AS Tickets
												FROM   (
															(
																SELECT 	TicketO.ID AS ID
																FROM   	TicketO
																	   	LEFT JOIN Loc AS Location ON TicketO.LID = Location.Loc
																WHERE  		Location.Route = ?
																		AND TicketO.Assigned = 3
															)
														) AS Tickets;",
                                array(
                                    $Route[ 'ID' ]
                                )
                            );
                            ?><div class='col-1'>&nbsp;</div>
                            <div class='col-3 border-bottom border-white my-auto'>On Site</div>
                            <div class='col-6'><input class='form-control' type='text' readonly name='Tickets' value='<?php
                                echo $r ? sqlsrv_fetch_array($r)[ 'Tickets' ] : 0;
                                ?>' /></div>
                            <div class='col-2'><button class='h-100 w-100' onClick="document.location.href='tickets.php?Route=<?php echo  $Route[ 'ID' ];?>&Status=3';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                        </div>
                        <div class='row g-0'>
                            <?php
                            $r = \singleton\database::getInstance( )->query(
                                null,
                                "	SELECT Count( Tickets.ID ) AS Tickets
												FROM   (
															(
																SELECT 	TicketO.ID AS ID
																FROM   	TicketO
																	   	LEFT JOIN Loc AS Location ON TicketO.LID = Location.Loc
																WHERE  		Location.Route = ?
																		AND TicketO.Assigned = 6
															)
														) AS Tickets;",
                                array(
                                    $Route[ 'ID' ]
                                )
                            );
                            ?><div class='col-1'>&nbsp;</div>
                            <div class='col-3 border-bottom border-white my-auto'>Reviewing</div>
                            <div class='col-6'><input class='form-control' type='text' readonly name='Tickets' value='<?php
                                echo $r ? sqlsrv_fetch_array($r)[ 'Tickets' ] : 0;
                                ?>' /></div>
                            <div class='col-2'><button class='h-100 w-100' onClick="document.location.href='tickets.php?Route=<?php echo  $Route[ 'ID' ];?>&Status=6';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                        </div>
                        <div class='row g-0'>
                            <?php
                            $r = \singleton\database::getInstance( )->query(
                                null,
                                "	SELECT Count( Tickets.ID ) AS Tickets
												FROM   (
															(
																SELECT 	TicketO.ID AS ID
																FROM   	TicketO
																	   	LEFT JOIN Loc AS Location ON TicketO.LID = Location.Loc
																WHERE  		Location.Route = ?
																		AND TicketO.Assigned = 4
															)
														) AS Tickets;",
                                array(
                                    $Route[ 'ID' ]
                                )
                            );
                            ?><div class='col-1'>&nbsp;</div>
                            <div class='col-3 border-bottom border-white my-auto'>Completed</div>
                            <div class='col-6'><input class='form-control' type='text' readonly name='Tickets' value='<?php
                                echo $r ? sqlsrv_fetch_array($r)[ 'Tickets' ] : 0;
                                ?>' /></div>
                            <div class='col-2'><button class='h-100 w-100' onClick="document.location.href='tickets.php?Route=<?php echo  $Route[ 'ID' ];?>&Status=4';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                        </div>
                    </div>
                </div>
                <div class='card card-primary my-3'>
                    <div class='card-heading'>
                        <div class='row g-0 px-3 py-2'>
                            <div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Violation( 1 );?><span>Violations</span></h5></div>
                            <div class='col-2'><button class='h-100 w-100' onClick="document.location.href='violations.php?Route=<?php echo $_GET['ID'];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                        </div>
                    </div>
                    <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Violations' ] ) && $_SESSION[ 'Cards' ][ 'Violations' ] == 0 ? "style='display:none;'" : null;?>>
                        <div class='row g-0'>
                            <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Violation(1);?><span>Violations</span></div>
                            <div class='col-6'>&nbsp;</div>
                            <div class='col-2'>&nbsp;</div>
                        </div>
                        <div class='row g-0'>


                            <?php
                            $result = \singleton\database::getInstance()->query(
                                null,
                                " SELECT Violation.Status FROM Violation GROUP BY Status;",
                            );
                            if( $result ){ while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC ) ){ ?>
                                <div class='col-1'>&nbsp;</div>
                                <div class='col-3 border-bottom border-white my-auto'><?php  echo $row['Status']; ?></div>
                                <div class='col-6'>
                                <?php
                                $r = \singleton\database::getInstance( )->query(null,"
											SELECT Count( Violation.ID ) AS Violations
											FROM   Violation
												   LEFT JOIN Loc AS Location ON Violation.Loc = Location.Loc
											WHERE  Location.Route = ?
													AND Violation.Status = ?
										;",array($_GET['ID'],$row['Status']));
                                echo $r ? sqlsrv_fetch_array($r)['Violations'] : 0;
                                ?>

                                <div class='col-2'><button class='h-100 w-100' onClick="document.location.href='violations.php?Route=<?php echo $_GET['ID'];?>&Status=<?php echo $row['Status'];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                                </div>
                                    <?php } ?>

                            <?php } ?>

                        </div>

                    </div>
                </div>
                <div class='card card-primary my-3'>
                    <div class='card-heading'>
                        <div class='row g-0 px-3 py-2'>
                            <div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Location( 1 );?><span>Location</span></h5></div>
                            <div class='col-2'><button class='h-100 w-100' type='button' onClick="document.location.href='loncations.php?Route=<?php echo $Route[ 'ID' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                        </div>
                    </div>
                    <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Location' ] ) && $_SESSION[ 'Cards' ][ 'Location' ] == 0 ? "style='display:none;'" : null;?>>
                        <div class='row g-0'>
                            <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Location(1);?> Summary Report</div>
                            <div class='col-6'>&nbsp;</div>
                            <div class='col-2'>&nbsp;</div>
                        </div>
                        <div class='row g-0'>
                            <?php
                            $currentMonth = date('Y-m').'-01';
                            $r = \singleton\database::getInstance( )->query(
                                null,
                                " SELECT  Loc
                                  FROM    Loc
                                          LEFT JOIN ( SELECT Max( Ticket ) FROM ( ( TicketO JOIN TicketDPDA ON TicketO.ID = TicketDPDA.ID ) UNION ALL ( TicketD ) ) ) AS LastTicket
                                  WHERE   LastTicket  < = ?;",
                                array(
                                    date( 'Y-m-01', strtotime( 'this month' ))
                                )
                            );
                            ?>
                            <div class='col-1'>&nbsp;</div>
                            <div class='col-3 border-bottom border-white my-auto'>Visited</div>
                            <div class='col-6'><input class='form-control' type='text' readonly name='Tickets' value='<?php
                                echo $r ? sqlsrv_fetch_array($r)[ 'Ticket' ] : 0;
                                ?>' /></div>
                            <div class='col-2'><button class='h-100 w-100' onClick="document.location.href='tickets.php?Route=<?php echo $Route[ 'ID' ];?>&Ticket_Last_Service_Start=<?php echo $currentMonth; ?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                        </div>
                        <div class='row g-0'>
                            <?php

                            $lastDayMonth = date('Y-m-d', strtotime( 'last day of last month' ));
                            $r = \singleton\database::getInstance( )->query(
                                null,
                                "SELECT Loc
                                FROM Loc
                                LEFT JOIN ( SELECT Max( Ticket ) FROM ( ( TicketO JOIN TicketDPDA ON TicketO.ID = TicketDPDA.ID ) UNION ALL ( TicketD ) ) ) AS LastTicket
                                WHERE LastTicket < = ?;",
                                array(
                                    $lastDayMonth
                                )
                            );

                            ?><div class='col-1'>&nbsp;</div>
                            <div class='col-3 border-bottom border-white my-auto'>To Do</div>
                            <div class='col-6'><input class='form-control' type='text' readonly name='Tickets' value='<?php
                                echo $r ? sqlsrv_fetch_array($r)[ 'Ticket' ] : 0;
                                ?>' /></div>
                            <div class='col-2'><button class='h-100 w-100' onClick="document.location.href='tickets.php?Route=<?php echo  $Route[ 'ID' ];?>&Status=1';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                        </div>

                    </div>
                </div>
                <div class='card card-primary my-3'>
                    <div class='card-heading'>
                        <div class='row g-0 px-3 py-2'>
                            <div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Unit( 1 );?><span>Units</span></h5></div>
                            <div class='col-2'><button class='h-100 w-100' type='button' onClick="document.location.href='units.php?Route=<?php echo $Route[ 'ID' ].'&Type=Elevator';?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                        </div>
                    </div>
                    <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Unit' ] ) && $_SESSION[ 'Cards' ][ 'Unit' ] == 0 ? "style='display:none;'" : null;?>>
                        <div class='row g-0'>
                            <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Unit(1);?> Summary Report</div>
                            <div class='col-6'>&nbsp;</div>
                            <div class='col-2'>&nbsp;</div>
                        </div>
                        <div class='row g-0'>
                            <?php
                            $currentMonth = date('Y-m').'-01';
                            $r = \singleton\database::getInstance( )->query(
                                null,
                                "	SELECT Count( Unit.ID ) AS Unit
												FROM Unit  LEFT JOIN Loc AS Location ON Unit.Loc = Location.Loc
																WHERE  		Location.Route = ?
																		AND Unit.Type = 'Elevator' ;",
                                array(
                                    $Route[ 'ID' ]
                                )
                            );
                            ?>
                            <div class='col-1'>&nbsp;</div>
                            <div class='col-3 border-bottom border-white my-auto'>Elevator</div>
                            <div class='col-6'><input class='form-control' type='text' readonly name='Tickets' value='<?php
                                echo $r ? sqlsrv_fetch_array($r)[ 'Unit' ] : 0;
                                ?>' /></div>
                            <div class='col-2'><button class='h-100 w-100' onClick="document.location.href='tickets.php?Route=<?php echo $Route[ 'ID' ];?>&Ticket_Last_Service_Start=<?php echo $currentMonth; ?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                        </div>
                        <div class='row g-0'>
                            <?php

                            $r = \singleton\database::getInstance( )->query(
                                null,
                                "	SELECT Count( Unit.ID ) AS Unit
												FROM Unit  LEFT JOIN Loc AS Location ON Unit.Loc = Location.Loc
																WHERE  		Location.Route = ?
																		AND Unit.Type = 'Escalator' ;",
                                array(
                                    $Route[ 'ID' ]
                                )
                            );
                            ?><div class='col-1'>&nbsp;</div>
                            <div class='col-3 border-bottom border-white my-auto'>Escalator</div>
                            <div class='col-6'><input class='form-control' type='text' readonly name='Tickets' value='<?php
                                echo $r ? sqlsrv_fetch_array($r)[ 'Unit' ] : 0;
                                ?>' /></div>
                            <div class='col-2'><button class='h-100 w-100' onClick="document.location.href='tickets.php?Route=<?php echo  $Route[ 'ID' ];?>&Status=1';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                        </div>
                        <div class='row g-0'>
                            <?php

                            $r = \singleton\database::getInstance( )->query(
                                null,
                                "	SELECT Count( Unit.ID ) AS Unit
												FROM Unit  LEFT JOIN Loc AS Location ON Unit.Loc = Location.Loc
																WHERE  		Location.Route = ?
																		AND Unit.Status = 0 ;",
                                array(
                                    $Route[ 'ID' ]
                                )
                            );
                            ?><div class='col-1'>&nbsp;</div>
                            <div class='col-3 border-bottom border-white my-auto'>Unmaintained</div>
                            <div class='col-6'><input class='form-control' type='text' readonly name='Tickets' value='<?php
                                echo $r ? sqlsrv_fetch_array($r)[ 'Unit' ] : 0;
                                ?>' /></div>
                            <div class='col-2'><button class='h-100 w-100' onClick="document.location.href='tickets.php?Route=<?php echo  $Route[ 'ID' ];?>&Status=1';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
      </div>
    </div>
  </div>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=route<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
