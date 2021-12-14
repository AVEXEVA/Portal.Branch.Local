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
      ||  !isset( $Privileges[ 'Division' ] )
      || 	!check( privilege_read, level_group, $Privileges[ 'Division' ] )
  ){ ?><?php require('404.html');?><?php }
  else {
    \singleton\database::getInstance( )->query(
      null,
      " INSERT INTO Activity([User], [Date], [Page] )
        VALUES( ?, ?, ? );",
      array(
        $_SESSION[ 'Connection' ][ 'User' ],
        date('Y-m-d H:i:s'),
        'division.php'
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
  		" SELECT  Division.ID         AS ID,
                Division.Name       AS Name,
                Division.Surcharge  AS Surcharge,
                Division.Bonus      AS Bonus,
                Division.Count      AS Count,
                Division.Remarks    AS Notes,
                Division.fDesc      AS Description,
                Division.TFMID      AS TFMID,
                Division.TFMSource  AS TFMsource,
                CASE    WHEN Tickets.Unassigned IS NULL THEN 0
                        ELSE Tickets.Unassigned END AS Tickets_Open,
                CASE    WHEN Tickets.Assigned IS NULL THEN 0
                        ELSE Tickets.Assigned END AS Tickets_Assigned,
                CASE    WHEN Tickets.En_Route IS NULL THEN 0
                        ELSE Tickets.En_Route END AS Tickets_En_Route,
                CASE    WHEN Tickets.On_Site IS NULL THEN 0
                        ELSE Tickets.On_Site END AS Tickets_On_Site,
                CASE    WHEN Tickets.Reviewing IS NULL THEN 0
                        ELSE Tickets.Reviewing END AS Tickets_Reviewing
        FROM    Zone AS Division
                LEFT JOIN (
                  SELECT  Division.ID AS Division,
                          Unassigned.Count AS Unassigned,
                          Assigned.Count AS Assigned,
                          En_Route.Count AS En_Route,
                          On_Site.Count AS On_Site,
                          Reviewing.Count AS Reviewing
                  FROM    Zone AS Division
                          LEFT JOIN (
                            SELECT    Location.Zone AS Division,
                                      Count( TicketO.ID ) AS Count
                            FROM      TicketO
                                      LEFT JOIN Loc AS Location ON Location.Loc = TicketO.LID
                            WHERE     TicketO.Assigned = 0
                            GROUP BY  Location.Zone
                          ) AS Unassigned ON Unassigned.Division = Division.ID
                          LEFT JOIN (
                            SELECT    Location.Zone AS Division,
                                      Count( TicketO.ID ) AS Count
                            FROM      TicketO
                                      LEFT JOIN Loc AS Location ON Location.Loc = TicketO.LID
                            WHERE     TicketO.Assigned = 1
                            GROUP BY  Location.Zone
                          ) AS Assigned ON Assigned.Division = Division.ID
                          LEFT JOIN (
                            SELECT    Location.Zone AS Division,
                                      Count( TicketO.ID ) AS Count
                            FROM      TicketO
                                      LEFT JOIN Loc AS Location ON Location.Loc = TicketO.LID
                            WHERE     TicketO.Assigned = 2
                            GROUP BY  Location.Zone
                          ) AS En_Route ON En_Route.Division = Division.ID
                          LEFT JOIN (
                            SELECT    Location.Zone AS Division,
                                      Count( TicketO.ID ) AS Count
                            FROM      TicketO
                                      LEFT JOIN Loc AS Location ON Location.Loc = TicketO.LID
                            WHERE     TicketO.Assigned = 3
                            GROUP BY  Location.Zone
                          ) AS On_Site ON On_Site.Division = Division.ID
                          LEFT JOIN (
                            SELECT    Location.Zone AS Division,
                                      Count( TicketO.ID ) AS Count
                            FROM      TicketO
                                      LEFT JOIN Loc AS Location ON Location.Loc = TicketO.LID
                            WHERE     TicketO.Assigned = 6
                            GROUP BY  Location.Zone
                          ) AS Reviewing ON Reviewing.Division = Division.ID
              ) AS Tickets ON Tickets.Division = Division.ID
        WHERE   Division.ID = ?;",
        array(
          $ID,
          $Name
        )
      );
      //var_dump( sqlsrv_errors( ) );
      $Division  = in_array( $ID, array( null, 0, '', ' ' ) ) || !$result ? array(
        'ID' => null,
        'Name' => null,
        'Bonus' => null,
        'Count' => null,
        'Notes' => null,
        'Description' => null,
        'Price1' => null,
        'Price2' => null,
        'Price3' => null,
        'Price4' => null,
        'Price5' => null,
        'IDistance' => null,
        'ODistance' => null,
        'Color' => null,
        'fDesc' => null,
        'Tax' => null,
        'Maintenance' => null,
        'Route_Name' => null,
        'Route_ID' => null,
        'Tickets_Open' => null,
        'Tickets_Assigned' => null,
        'Tickets_En_Route' => null,
        'Tickets_On_Site' => null,
        'Tickets_Reviewing' => null,
        'Surcharge' => null,
        'TFMSouce' => null
      ) : sqlsrv_fetch_array($result);

      if( isset( $_POST ) && count( $_POST ) > 0 ){
        // if the $_Post is set and the count is null, select if available
        $Divsion[ 'ID' ] 		= isset( $_POST[ 'ID' ] ) 	 ? $_POST[ 'ID' ] 	 : $Divsion[ 'ID' ];
        $Divsion[ 'Name' ] 	= isset( $_POST[ 'Name' ] ) ? $_POST[ 'Name' ] : $Divsion[ 'Name' ];
        $Divsion[ 'Surcharge' ] 	= isset( $_POST[ 'Surcharge' ] ) ? $_POST[ 'Surcharge' ] : $Divsion[ 'Surcharge' ];
        $Divsion[ 'Bonus' ] 		= isset( $_POST[ 'Bonus' ] ) 	 ? $_POST[ 'Bonus' ] 	 : $Divsion[ 'Bonus' ];
        $Divsion[ 'Count' ] 		= isset( $_POST[ 'Count' ] ) 	 ? $_POST[ 'Count' ] 	 : $Divsion[ 'Count' ];
        $Divsion[ 'Notes' ] = isset( $_POST[ 'Remarks' ] )  ? $_POST[ 'Remarks' ]  : $Divsion[ 'Notes' ];
        $Divsion[ 'Price1' ]     = isset( $_POST[ 'Price1' ] ) 	   ? $_POST[ 'Price1' ] 	   : $Divsion[ 'Price1' ];
        $Divsion[ 'IDistance' ] 	= isset( $_POST[ 'IDistance' ] ) 	 ? $_POST[ 'IDistance' ] 	 : $Divsion[ 'IDistance' ];
        $Divsion[ 'ODistance' ] 	= isset( $_POST[ 'ODistance' ] ) 	 ? $_POST[ 'ODistance' ] 	 : $Divsion[ 'ODistance' ];
        $Divsion[ 'Internet' ] = isset( $_POST[ 'Internet' ] )  ? $_POST[ 'Internet' ]  : $Divsion[ 'Internet' ];
        $Divsion[ 'Color' ] 	= isset( $_POST[ 'Color' ] ) 	 ? $_POST[ 'Color' ] 	 : $Divsion[ 'Color' ];
        $Divsion[ 'fDesc' ] 		= isset( $_POST[ 'fDesc' ] ) 	 ? $_POST[ 'fDesc' ] 	 : $Divsion[ 'fDesc' ];
        $Divsion[ 'Tax' ] 		= isset( $_POST[ 'Tax' ] ) 	 ? $_POST[ 'Tax' ] 	 : $Divsion[ 'Tax' ];
        $Divsion[ 'TFMID' ] 			= isset( $_POST[ 'TFMID' ] ) 		 ? $_POST[ 'TFMID' ] 		 : $Divsion[ 'TFMID' ];
        $Divsion[ 'Tickets_Open' ] 	= isset( $_POST[ 'Tickets_Open' ] )  ? $_POST[ 'Tickets_Open' ]  : $Divsion[ 'Tickets_Open' ];
        $Divsion[ 'Tickets_Assigned' ] 	= isset( $_POST[ 'Tickets_Assigned' ] )  ? $_POST[ 'Tickets_Assigned' ]  : $Divsion[ 'Tickets_Assigned' ];
        $Divsion[ 'Tickets_En_Route' ] 	= isset( $_POST[ 'Tickets_En_Route' ] )  ? $_POST[ 'Tickets_En_Route' ]  : $Divsion[ 'Tickets_En_Route' ];
        $Divsion[ 'Tickets_On_Site' ] 	= isset( $_POST[ 'Tickets_On_Site' ] )  ? $_POST[ 'Tickets_On_Site' ]  : $Divsion[ 'Tickets_On_Site' ];
        $Divsion[ 'Tickets_Reviewing' ] 	= isset( $_POST[ 'Tickets_Reviewing' ] )  ? $_POST[ 'Tickets_Reviewing' ]  : $Divsion[ 'Tickets_Reviewing' ];
        if( in_array( $_POST[ 'ID' ], array( null, 0, '', ' ' ) ) ){
          $result = \singleton\database::getInstance( )->query(
            null,
            "	DECLARE @MAXID INT;
              SET @MAXID = CASE WHEN ( SELECT Max( ID ) FROM Zone ) IS NULL THEN 0 ELSE ( SELECT Max( ID ) FROM Zone ) END ;
              INSERT INTO Zone(
                ID,
                Name,
                Bonus,
                Count,
                Remarks,
                Price1,
                Price2,
                Price3,
                Price4,
                Price5,
                IDistance,
                ODistance,
                Color,
                fDesc,
                Tax
              )
              VALUES( @MAXID + 1 , ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? );
              SELECT @MAXID + 1;",
            array(
              $Divsion[ 'ID' ],
              $Divsion[ 'Name' ],
              $Divsion[ 'Bonus' ],
              $Divsion[ 'Count' ],
              $Divsion[ 'Notes' ],
              $Divsion[ 'Price1' ],
              $Divsion[ 'IDistance' ],
              $Divsion[ 'ODistance' ],
              $Divsion[ 'Color' ],
              $Divsion[ 'fDesc' ],
              $Divsion[ 'Tax' ],
              $Divsion[ 'TFMID' ],
              $Divsion[ 'TFMSource'],
              isset( $Divsion[ 'Geofence' ] ) ? $Divsion[ 'Geofence' ] : 0
            )
          );
          sqlsrv_next_result( $result );
          $Division [ 'ID' ] = sqlsrv_fetch_array( $result )[ 0 ];
        //  header( 'Location: lead.php?ID=' . $Division [ 'ID' ] );
        } else {
          \singleton\database::getInstance( )->query(
            null,
            "	UPDATE 	Zone
              SET       Zone.ID   = ?,
                        Zone.Name = ?,
                        Zone.Bonus = ?,
                        Zone.Count = ?,
                        Zone.Remarks = ?,
                        Zone.fDesc   = ?,
                        zone.TFMID   = ?,
                        zone.TFMSource = ?
              WHERE 	  Zone.ID = ?;",
            array(
              $Divsion[ 'ID' ],
              $Divsion[ 'Name' ],
              $Divsion[ 'Bonus' ],
              $Divsion[ 'Count' ],
              $Divsion[ 'Notes' ],
              $Divsion[ 'Price1' ],
              $Divsion[ 'Price2' ],
              $Divsion[ 'Price3' ],
              $Divsion[ 'Price4' ],
              $Divsion[ 'Price5' ],
              $Divsion[ 'IDistance' ],
              $Divsion[ 'ODistance' ],
              $Divsion[ 'Color' ],
              $Divsion[ 'fDesc' ],
              $Divsion[ 'Tax' ],
              !empty( $Division [ 'GeoLock' ] ) ? $Division [ 'GeoLock' ] : 0
            )
          );
        }
      }
?><!DOCTYPE html>
<html lang="en">
<head>
  <title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
  <?php
    $_GET[ 'Bootstrap' ] = '5.1';
    $_GET[ 'Entity_CSS' ] = 1;
    require( bin_meta . 'index.php');
    require( bin_css  . 'index.php');
    require( bin_js   . 'index.php');
  ?>
</head>
<body>
  <div id="wrapper">
    <?php require( bin_php . 'element/navigation.php');?>
    <div id="page-wrapper" class='content'>
      <div class='card card-primary border-0'><form action='division.php?ID=<?php echo $Division[ 'ID' ];?>' method='POST'>
        <input type='hidden' name='ID' value='<?php echo isset( $Division[ 'ID' ] ) ? $Division[ 'ID' ] : null;?>' />
        <?php \singleton\bootstrap::getInstance( )->primary_card_header( 'Division', 'Divisions', $Division[ 'ID' ] );?>
        <div class='card-body bg-dark text-white'>
          <div class='row g-0' data-masonry='{"percentPosition": true }'>
            <div class='card card-primary my-3 col-12 col-lg-3'>
              <?php \singleton\bootstrap::getInstance( )->card_header( 'Information' );?>
              <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Infomation' ] ) && $_SESSION[ 'Cards' ][ 'Infomation' ] == 0 ? "style='display:none;'" : null;?>>
                <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Name', $Division[ 'Name' ] );?>
                <?php \singleton\bootstrap::getInstance( )->card_row_form_textarea( 'Description', $Division[ 'Description' ] );?>
                <?php \singleton\bootstrap::getInstance( )->card_row_form_textarea( 'Notes', $Division[ 'Notes' ] );?>
              </div>
            </div>
            <div class='card card-primary my-3 col-12 col-lg-3'>
              <?php \singleton\bootstrap::getInstance( )->card_header( 'Tickets', 'Ticket', 'Tickets', 'Division', $Division[ 'ID' ] );?>
              <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Tickets' ] ) && $_SESSION[ 'Cards' ][ 'Tickets' ] == 0 ? "style='display:none;'" : null;?>>
                <?php \singleton\bootstrap::getInstance( )->card_row_form_aggregated( 'Statuses', 'tickets.php?Division=' . $Division[ 'ID' ] );?>
                <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Open', $Division[ 'Tickets_Open' ], true, true, 'tickets.php?Division=' . $Division[ 'ID' ] . '&Status=0');?>
                <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Assigned', $Division[ 'Tickets_Assigned' ], true, true, 'tickets.php?Division=' . $Division[ 'ID' ] ) . '&Status=1';?>
                <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'En Route', $Division[ 'Tickets_En_Route' ], true, true, 'tickets.php?Division=' . $Division[ 'ID' ] ) . '&Status=2';?>
                <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'On Site', $Division[ 'Tickets_On_Site' ], true, true, 'tickets.php?Division=' . $Division[ 'ID' ] ) . '&Status=3';?>
                <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Reviewing', $Division[ 'Tickets_Reviewing' ], true, true, 'tickets.php?Division=' . $Division[ 'ID' ] ) . '&Status=6';?>
              </div>
            </div>
          </div>
        </div>
      </form></div>
    </div>
  </div>
</body>
</html>
<?php }
} else {?><script>document.location.href='../login.php?Forward=divisions.php&<?php echo http_build_query( $_GET );?>';</script><?php }?>
