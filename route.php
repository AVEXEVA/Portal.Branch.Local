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
  ){ require('404.html');?><?php }
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
      " SELECT  Route.ID              AS ID,
                Route.Name            AS Name,
                Employee.ID           AS Employee_ID,
                Employee.fFirst + ' ' + Employee.Last AS Employee_Name,
                CASE    WHEN Units.Count IS NULL THEN 0
                        ELSE Units.Count END AS Units_Count,
                CASE    WHEN Units.Elevators IS NULL THEN 0
                        ELSE Units.Elevators END AS Units_Elevators,
                CASE    WHEN Units.Escalators IS NULL THEN 0
                        ELSE Units.Escalators END AS Units_Escalators,
                CASE    WHEN Units.Moving_Walks IS NULL THEN 0
                        ELSE Units.Moving_Walks END AS Units_Moving_Walks,
                CASE    WHEN Units.Others IS NULL THEN 0
                        ELSE Units.Others END AS Units_Others,
                CASE    WHEN Tickets.Unassigned IS NULL THEN 0
                        ELSE Tickets.Unassigned END AS Tickets_Open,
                CASE    WHEN Tickets.Assigned IS NULL THEN 0
                        ELSE Tickets.Assigned END AS Tickets_Assigned,
                CASE    WHEN Tickets.En_Route IS NULL THEN 0
                        ELSE Tickets.En_Route END AS Tickets_En_Route,
                CASE    WHEN Tickets.On_Site IS NULL THEN 0
                        ELSE Tickets.On_Site END AS Tickets_On_Site,
                CASE    WHEN Tickets.Reviewing IS NULL THEN 0
                        ELSE Tickets.Reviewing END AS Tickets_Reviewing,
                CASE    WHEN Violations.Preliminary IS NULL THEN 0
                        ELSE Violations.Preliminary END AS Violations_Preliminary_Report,
                CASE    WHEN Violations.Job_Created IS NULL THEN 0
                        ELSE Violations.Job_Created END AS Violations_Job_Created
        FROM    Route
                LEFT JOIN Emp  AS Employee  ON  Route.Mech = Employee.fWork
                LEFT JOIN (
                    SELECT      Location.Route AS Route,
                                Sum( Units.Count ) AS Count,
                                Sum( Elevators.Count) AS Elevators,
                                Sum( Escalators.Count ) AS Escalators,
                                SUM( Moving_Walk.Count ) AS Moving_Walks,
                                Sum( Others.Count ) AS Others
                    FROM        Loc AS Location
                                LEFT JOIN (
                                    SELECT      Unit.Loc AS Location,
                                                Count( Unit.ID ) AS Count
                                    FROM        Elev AS Unit
                                    GROUP BY    Unit.Loc
                                ) AS [Units] ON Units.Location = Location.Loc
                                LEFT JOIN (
                                    SELECT      Unit.Loc AS Location,
                                                Count( Unit.ID ) AS Count
                                    FROM        Elev AS Unit
                                    WHERE       Unit.Type IN ( 'Elevator', 'Roped Hydro', 'Hydraulic' )
                                    GROUP BY    Unit.Loc
                                ) AS [Elevators] ON Elevators.Location = Location.Loc
                                LEFT JOIN (
                                    SELECT      Unit.Loc AS Location,
                                                Count( Unit.ID ) AS Count
                                    FROM        Elev AS Unit
                                    WHERE       Unit.Type = 'Escalator'
                                    GROUP BY    Unit.Loc
                                ) AS [Escalators] ON Escalators.Location = Location.Loc
                                LEFT JOIN (
                                    SELECT      Unit.Loc AS Location,
                                                Count( Unit.ID ) AS Count
                                    FROM        Elev AS Unit
                                    WHERE       Unit.Type = 'Moving Walk'
                                    GROUP BY    Unit.Loc
                                ) AS [Moving_Walk] ON Escalators.Location = Location.Loc
                                LEFT JOIN (
                                    SELECT      Unit.Loc AS Location,
                                                Count( Unit.ID ) AS Count
                                    FROM        Elev AS Unit
                                    WHERE       Unit.Type NOT IN ( 'Elevator', 'Roped Hydro', 'Hydraulic', 'Escalator', 'Moving Walk' ) OR Unit.Type IS NULL
                                    GROUP BY    Unit.Loc
                                ) AS [Others] ON Others.Location = Location.Loc
                    GROUP BY    Location.Route
                ) AS Units ON Units.Route = Route.ID
                LEFT JOIN (
                  SELECT  Route.ID AS Route,
                          Unassigned.Count AS Unassigned,
                          Assigned.Count AS Assigned,
                          En_Route.Count AS En_Route,
                          On_Site.Count AS On_Site,
                          Reviewing.Count AS Reviewing
                  FROM    Route
                          LEFT JOIN (
                            SELECT    Location.Route AS Route,
                                      Count( TicketO.ID ) AS Count
                            FROM      TicketO
                                      LEFT JOIN Loc AS Location ON Location.Loc = TicketO.LID
                                      WHERE     TicketO.Assigned = 0
                                      GROUP BY  Location.Route
                          ) AS Unassigned ON Unassigned.Route = Route.ID
                          LEFT JOIN (
                            SELECT    Location.Route AS Route,
                                      Count( TicketO.ID ) AS Count
                            FROM      TicketO
                                      LEFT JOIN Loc AS Location ON Location.Loc = TicketO.LID
                            WHERE     TicketO.Assigned = 1
                            GROUP BY  Location.Route
                          ) AS Assigned ON Assigned.Route = Route.ID
                          LEFT JOIN (
                            SELECT    Location.Route AS Route,
                                      Count( TicketO.ID ) AS Count
                            FROM      TicketO
                                      LEFT JOIN Loc AS Location ON Location.Loc = TicketO.LID
                            WHERE     TicketO.Assigned = 2
                            GROUP BY  Location.Route
                          ) AS En_Route ON En_Route.Route = Route.ID
                          LEFT JOIN (
                            SELECT    Location.Route AS Route,
                                      Count( TicketO.ID ) AS Count
                            FROM      TicketO
                                      LEFT JOIN Loc AS Location ON Location.Loc = TicketO.LID
                            WHERE     TicketO.Assigned = 3
                            GROUP BY  Location.Route
                          ) AS On_Site ON On_Site.Route = Route.ID
                          LEFT JOIN (
                            SELECT    Location.Route AS Route,
                                      Count( TicketO.ID ) AS Count
                            FROM      TicketO
                                      LEFT JOIN Loc AS Location ON Location.Loc = TicketO.LID
                            WHERE     TicketO.Assigned = 6
                            GROUP BY  Location.Route
                          ) AS Reviewing ON Reviewing.Route = Route.ID
                  ) AS Tickets ON Tickets.Route = Route.ID
                  LEFT JOIN (
                      SELECT  Location.Route AS Route,
                              Preliminary.Count AS Preliminary,
                              Job_Created.Count AS Job_Created
                      FROM    Loc AS Location
                              LEFT JOIN (
                                SELECT    Location.Loc AS Location,
                                          Count( Violation.ID ) AS Count
                                FROM      Violation
                                          LEFT JOIN Loc AS Location ON Location.Loc = Violation.Loc
                                WHERE     Violation.Status = 'Preliminary Report'
                                GROUP BY  Location.Loc
                              ) AS Preliminary ON Preliminary.Location = Location.Loc
                              LEFT JOIN (
                                SELECT    Location.Loc AS Location,
                                          Count( Violation.ID ) AS Count
                                FROM      Violation
                                          LEFT JOIN Loc AS Location ON Location.Loc = Violation.Loc
                                WHERE     Violation.Status = 'Job Created'
                                GROUP BY  Location.Loc
                              ) AS Job_Created ON Job_Created.Location = Location.Loc
                  ) AS Violations ON Violations.Route = Route.ID
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
        'Employee_ID' => isset( $_GET[ 'Employee_ID' ] ) ? $_GET[ 'Employee_ID' ] : null,
        'Employee_Name' => isset( $_GET[ 'Employee_Name' ] ) ? $_GET[ 'Employee_Name' ] : null,
        'Units_Elevators' => isset( $_GET[ 'Units_Elevators' ] ) ? $_GET[ 'Units_Elevators' ] : null,
        'Units_Escalators' => isset( $_GET[ 'Units_Escalators' ] ) ? $_GET[ 'Units_Escalators' ] : null,
        'Units_Other' => isset( $_GET[ 'Units_Other' ] ) ? $_GET[ 'Units_Other' ] : null,
        'Tickets_Open' => isset( $_GET[ 'Tickets_Open' ] ) ? $_GET[ 'Tickets_Open' ] : null,
        'Tickets_Assigned' => isset( $_GET[ 'Tickets_Assigned' ] ) ? $_GET[ 'Tickets_Assigned' ] : null,
        'Tickets_En_Route' => isset( $_GET[ 'Tickets_En_Route' ] ) ? $_GET[ 'Tickets_En_Route' ] : null,
        'Tickets_On_Site' => isset( $_GET[ 'Tickets_On_Site' ] ) ? $_GET[ 'Tickets_On_Site' ] : null,
        'Tickets_Reviewing' => isset( $_GET[ 'Tickets_Reviewing' ] ) ? $_GET[ 'Tickets_Reviewing' ] : null,
        'Violations_Preliminary_Report' => isset( $_GET[ 'Violations_Preliminary_Report' ] ) ? $_GET[ 'Violations_Preliminary_Report' ] : null,
        'Violations_Job_Created' => isset( $_GET[ 'Violations_Job_Created' ] ) ? $_GET[ 'Violations_Job_Created' ] : null
      ) : sqlsrv_fetch_array($result);
      if( isset( $_POST ) && count( $_POST ) > 0 ){
        $Route[ 'Name' ]      = isset( $_POST[ 'Name' ] )    ? $_POST[ 'Name' ]    : $Route[ 'Name' ];
        $Route[ 'Employee_ID' ]      = isset( $_POST[ 'Employee_ID' ] )    ? $_POST[ 'Employee_ID' ]    : $Route[ 'Employee_ID' ];
        $Route[ 'Employee_Name' ]      = isset( $_POST[ 'Employee_Name' ] )    ? $_POST[ 'Employee_Name' ]    : $Route[ 'Employee_Name' ];
        if( empty( $_POST[ 'ID' ] ) ){
          $result = \singleton\database::getInstance( )->query(
            null,
            " DECLARE @MAXID INT;
              DECLARE @fWork INT;
              SET @MAXID = CASE WHEN ( SELECT Max( ID ) FROM Route ) IS NULL THEN 0 ELSE ( SELECT Max( ID ) FROM Route ) END ;
              SET @fWork = ( SELECT Emp.fWork FROM Emp WHERE Emp.ID = ? );
              INSERT INTO Route(
                ID,
                Name,
                Mech
              )
              VALUES ( @MAXID + 1, ?, @fWork );
              SELECT @MAXID + 1;",
            array(
              $Route[ 'Employee_ID' ],
              $Route[ 'Name' ],
            )
          );
          var_dump( sqlsrv_errors( ) );
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
      " SELECT Loc,Tag,fLong,Latt
        FROM   Loc
        WHERE  Loc.Route = ?
	;",array( isset ( $_GET ['ID'] ) ? $_GET['ID'] : null)
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
    require( bin_meta . 'index.php' );
    require( bin_css  . 'index.php' );
    require( bin_js   . 'index.php' );
  ?><script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=AIzaSyCNrTryEaTEDRz-XDSg890ajL_JRPnLgzc"></script>
</head>
<body>
  <div id="wrapper">
    <?php require(bin_php.'element/navigation.php');?>
    <div id="page-wrapper" class='content' style='height:100%;overflow-y:scroll;'>
      <div class='card card-primary'>
        <form action='route.php?ID=<?php echo $Route[ 'ID' ];?>' method='POST'>
          <input type='hidden' name='ID' value='<?php echo $Route[ 'ID' ];?>' />
          <?php \singleton\bootstrap::getInstance( )->primary_card_header( 'Route', 'Routes', $Route[ 'ID' ] );?>
          <div class='card-body bg-dark text-white'>
            <div class='row g-0' data-masonry='{"percentPosition": true }'>
              <?php if( count($finalLoc)>0 ){?>
                <div class='card card-primary my-3 col-12 col-lg-3'>
                    <?php \singleton\bootstrap::getInstance( )->card_header( 'Map' );?>
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
              <div class='card card-primary my-3 col-12 col-lg-3'>
                <?php \singleton\bootstrap::getInstance( )->card_header( 'Information' );?>
                <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Infomation' ] ) && $_SESSION[ 'Cards' ][ 'Infomation' ] == 0 ? "style='display:none;'" : null;?>>
                  <?php
                    \singleton\bootstrap::getInstance( )->card_row_form_input( 'Name', $Route[ 'Name' ] );
                    \singleton\bootstrap::getInstance( )->card_row_form_autocomplete( 'Employee', 'Employees', $Route[ 'Employee_ID' ], $Route[ 'Employee_Name' ] );
                  ?>
                </div>
              </div>
              <div class='card card-primary my-3 col-12 col-lg-3'>
                  <?php \singleton\bootstrap::getInstance( )->card_header( 'Units', 'Unit', 'Units', 'Route_ID', $Route[ 'ID' ] );?>
                  <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Units' ] ) && $_SESSION[ 'Cards' ][ 'Units' ] == 0 ? "style='display:none;'" : null;?>>
                      <?php \singleton\bootstrap::getInstance( )->card_row_form_aggregated( 'Types', 'units.php?Route_ID=' . $Route[ 'ID' ] . '&Route_Name=' . $Route[ 'Name' ] );?>
                      <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Elevators', $Route[ 'Units_Elevators' ], true, true, 'units.php?Route_ID=' . $Route[ 'ID' ] . '&Route_Name=' . $Route[ 'Name' ] . '&Type=Elevator');?>
                      <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Escalators', $Route[ 'Units_Escalators' ], true, true, 'units.php?Route_ID=' . $Route[ 'ID' ] . '&Route_Name=' . $Route[ 'Name' ] . '&Type=Escalator' );?>
                      <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Moving_Walks', $Route[ 'Units_Moving_Walks' ], true, true, 'units.php?Route_ID=' . $Route[ 'ID' ] . '&Route_Name=' . $Route[ 'Name' ] . '&Type=Escalator' );?>
                      <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Others', $Route[ 'Units_Others' ], true, true, 'units.php?Route_ID=' . $Route[ 'ID' ] . '&Route_Name=' . $Route[ 'Name' ] . '&Type=Other' );?>
                  </div>
              </div>
              <div class='card card-primary my-3 col-12 col-lg-3'>
                <?php \singleton\bootstrap::getInstance( )->card_header( 'Tickets', 'Ticket', 'Tickets', 'Route_ID', $Route[ 'ID' ] );?>
                <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Tickets' ] ) && $_SESSION[ 'Cards' ][ 'Tickets' ] == 0 ? "style='display:none;'" : null;?>>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_aggregated( 'Statuses', 'tickets.php?Route_ID=' . $Route[ 'ID' ] . '&Route_Name=' . $Route[ 'Name' ] );?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Open', $Route[ 'Tickets_Open' ], true, true, 'tickets.php?Route_ID=' . $Route[ 'ID' ] . '&Status=0');?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Assigned', $Route[ 'Tickets_Assigned' ], true, true, 'tickets.php?Route_ID=' . $Route[ 'ID' ] . '&Route_Name=' . $Route[ 'Name' ] . '&Status=1' );?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'En_Route', $Route[ 'Tickets_En_Route' ], true, true, 'tickets.php?Route_ID=' . $Route[ 'ID' ] . '&Route_Name=' . $Route[ 'Name' ] . '&Status=2' );?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'On_Site', $Route[ 'Tickets_On_Site' ], true, true, 'tickets.php?Route_ID=' . $Route[ 'ID' ] . '&Route_Name=' . $Route[ 'Name' ] . '&Status=3' );?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Reviewing', $Route[ 'Tickets_Reviewing' ], true, true, 'tickets.php?Route_ID=' . $Route[ 'ID' ] . '&Route_Name=' . $Route[ 'Name' ] . '&Status=6' );?>
                </div>
              </div>
              <div class='card card-primary my-3 col-12 col-lg-3'>
                <?php \singleton\bootstrap::getInstance( )->card_header( 'Violations', 'Violation', 'Violations', 'Route_ID', $Route[ 'ID' ] );?>
                <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Violations' ] ) && $_SESSION[ 'Cards' ][ 'Violations' ] == 0 ? "style='display:none;'" : null;?>>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_aggregated( 'Statuses', 'violations.php?Route_ID=' . $Route[ 'ID' ] . '&Route_Name=' . $Route[ 'Name' ] );?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Preliminary', $Route[ 'Violations_Preliminary_Report' ], true, true, 'violations.php?Route_ID=' . $Route[ 'ID' ] . '&Route_Name=' . $Route[ 'Name' ] . '&Status=Preliminary Report');?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Ongoing', $Route[ 'Violations_Job_Created' ], true, true, 'violations.php?Route_ID=' . $Route[ 'ID' ] . '&Route_Name=' . $Route[ 'Name' ] . '&Status=Job Created' );?>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=route<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
