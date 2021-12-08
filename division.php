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
    		" SELECT
                  Zone.ID         AS ID,
                  Zone.Name       AS Name,
                  Zone.Surcharge  AS Surcharge,
                  Zone.Bonus      AS Bonus,
                  Zone.Count      AS Count,
                  Zone.Remarks    AS Notes,
                  Zone.fDesc      AS Description,
                  zone.TFMID      AS TFMID,
                  zone.TFMSource  AS TFMsource
                  FROM Zone
                  WHERE Zone.ID = ?;",
                  array(
                    $ID,
                    $Name
                  )
                );
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
    'Route_ID' => null
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
        $Divsion[ 'TFMSource' ] 	= isset( $_POST[ 'TFMSource' ] )  ? $_POST[ 'TFMSource' ]  : $Divsion[ 'TFMSource' ];
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
           <?php  $_GET[ 'Bootstrap' ] = '5.1';?>
           <?php  $_GET[ 'Entity_CSS' ] = 1;?>
           <?php	require( bin_meta . 'index.php');?>
           <?php	require( bin_css  . 'index.php');?>
           <?php  require( bin_js   . 'index.php');?>
           <style>
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
				      <div class='card-heading'>
                <div class='row g-0 px-3 py-2'>
                  <div class='col-12 col-lg-6'>
                    <h5><?php \singleton\fontawesome::getInstance( )->User( 1 );?><a href='divisions.php?<?php
                      echo http_build_query( is_array( $_SESSION[ 'Tables' ][ 'Divisions' ][ 0 ] ) ? $_SESSION[ 'Tables' ][ 'Divisions' ][ 0 ] : array( ) );
                    ?>'>Division</a>: <span><?php
                      echo is_null( $Division[ 'ID' ] )
                          ? 'New'
                          : '#' . $Division[ 'ID' ];
                    ?></span></h5>
                  </div>
                  <div class='col-6 col-lg-3'>
                      <div class='row g-0'>
                        <div class='col-4'>
                          <button
                              class='form-control rounded'
                              onClick="document.location.href='division.php';"
                            ><?php \singleton\fontawesome::getInstance( 1 )->Save( 1 );?><span class='desktop'> Save</span></button>
                        </div>
                        <div class='col-4'>
                            <button
                              class='form-control rounded'
                              onClick="document.location.href='division.php?ID=<?php echo $User[ 'ID' ];?>';"
                            ><?php \singleton\fontawesome::getInstance( 1 )->Refresh( 1 );?><span class='desktop'> Refresh</span></button>
                        </div>
                        <div class='col-4'>
                            <button
                              class='form-control rounded'
                              onClick="document.location.href='division.php';"
                            ><?php \singleton\fontawesome::getInstance( 1 )->Add( 1 );?><span class='desktop'> New</span></button>
                        </div>
                    </div>
                  </div>
                  <div class='col-6 col-lg-3'>
                      <div class='row g-0'>
                        <div class='col-4'><button class='form-control rounded' onClick="document.location.href='division.php?ID=<?php echo !is_null( $User[ 'ID' ] ) ? array_keys( $_SESSION[ 'Tables' ][ 'Divisions' ], true )[ array_search( $Division[ 'ID' ], array_keys( $_SESSION[ 'Tables' ][ 'Divisions' ], true ) ) - 1 ] : null;?>';"><?php \singleton\fontawesome::getInstance( 1 )->Previous( 1 );?><span class='desktop'> Previous</span></button></div>
                        <div class='col-4'><button class='form-control rounded' onClick="document.location.href='divisions.php?<?php echo http_build_query( is_array( $_SESSION[ 'Tables' ][ 'Users' ][ 0 ] ) ? $_SESSION[ 'Tables' ][ 'Divisions' ][ 0 ] : array( ) );?>';"><?php \singleton\fontawesome::getInstance( 1 )->Table( 1 );?><span class='desktop'> Table</span></button></div>
                        <div class='col-4'><button class='form-control rounded' onClick="document.location.href='division.php?ID=<?php echo !is_null( $User[ 'ID' ] )? array_keys( $_SESSION[ 'Tables' ][ 'Divisions' ], true )[ array_search( $Division[ 'ID' ], array_keys( $_SESSION[ 'Tables' ][ 'Divisions' ], true ) ) + 1 ] : null;?>';"><?php \singleton\fontawesome::getInstance( 1 )->Next( 1 );?><span class='desktop'> Next</span></button></div>
                      </div>
                  </div>
                </div>
              </div>
      				<div class='card-body bg-dark text-white'>
      				<div class='card-columns'>
      					<div class='card card-primary my-3'>
      						<div class='card-heading'>
      							<div class='row g-0 px-3 py-2'>
      								<div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Info( 1 );?><span>Infomation</span></h5></div>
      								<div class='col-2'>&nbsp;</div>
      							</div>
      						</div>
      						<div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Infomation' ] ) && $_SESSION[ 'Cards' ][ 'Infomation' ] == 0 ? "style='display:none;'" : null;?>>
      							<form action='division.php?ID=<?php echo $Division[ 'ID' ];?>' method='POST'>
      								<input type='hidden' name='ID' value='<?php echo isset( $Division[ 'ID' ] ) ? $Division[ 'ID' ] : null;?>' />
      				                <div class='row g-0'>
      									<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Location(1);?>Name:</div>
      									<div class='col-8'><input type='text' class='form-control edit animation-focus' name='Name' value='<?php echo $Division['Name'];?>' /></div>
                        <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Description(1);?>Description:</div>
                        <div class='col-8'><input type='text' class='form-control edit animation-focus' name='Description' value='<?php echo $Division['Description'];?>' /></div>
                        <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Note(1);?>Notes:</div>
                        <div class='col-8'><input type='text' class='form-control edit animation-focus' name='Notes' value='<?php echo $Division['Notes'];?>' /></div>
      								</div>
                    </div>
                  </div>
      					<div class='card card-primary my-3'>
      						<div class='card-heading'>
      							<div class='row g-0 px-3 py-2'>
      								<div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Maintenance( 1 );?><span>Maintenance</span></h5></div>
      								<div class='col-2 p-1 text-center rounded bg-<?php echo $Division[ 'Maintenance' ] == 1 ? 'success' : 'warning';?>'><?php echo $Division[ 'Maintenance' ] == 1 ? 'Active' : 'Inactive';?></div>
      							</div>
      						</div>
      						<div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Infomation' ] ) && $_SESSION[ 'Cards' ][ 'Infomation' ] == 0 ? "style='display:none;'" : null;?>>
      							<form action='division.php?ID=<?php echo $Division[ 'ID' ];?>' method='POST'>
      								<input type='hidden' name='ID' value='<?php echo isset( $_GET[ 'ID' ] ) ? $_GET[ 'ID' ] : null;?>' />
      								<div class='row g-0'>
      									<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Maintenance:</div>
      								    <div class='col-6'>
      								    	<select class='form-control edit' name='Maintenance'>
      								    		<option value=''>Select</option>
      								    		<option value='0' <?php echo $Division[ 'Maintenance' ] == 0 ? 'selected' : null;?>>Disabled</option>
      								    		<option value='1' <?php echo $Division[ 'Maintenance' ] == 1 ? 'selected' : null;?>>Enabled</option>
      								    	</select>
      								    </div>
      								    <div class='col-2'>&nbsp;</div>
      								</div>
      								<div class='row g-0'>
      									<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Route(1);?> Route:</div>
      								    <div class='col-6'>
      								    	<input type='text' autocomplete='off' class='form-control edit' name='Route' value='<?php echo $Division[ 'Route_Name' ];?>' />
      								    	<script>
      								    		$( 'input[name="Route"]' )
      										        .typeahead({
      										            minLength : 4,
      										            hint: true,
      										            highlight: true,
      										            limit : 5,
      										            display : 'FieldValue',
      										            source: function( query, result ){
      										                $.ajax({
      										                    url : 'bin/php/get/search/Routes.php',
      										                    method : 'GET',
      										                    data    : {
      										                        search :  $('input:visible[name="Route"]').val( )
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
      										                $( 'input[name="Route"]').val( value );
      										                $( 'input[name="Route"]').closest( 'form' ).submit( );
      										            }
      										        }
      										    );
      								    	</script>
      								    </div>
      								    <div class='col-2'><button class='h-100 w-100' type='button' <?php
      								    	if( in_array( $Division[ 'Route_ID' ], array( null, 0, '', ' ') ) ){
      								    		echo "onClick=\"document.location.href='routes.php';\"";
      								    	} else {
      								    		echo "onClick=\"document.location.href='route.php?ID=" . $Division[ 'Route_ID' ] . "';\"";
      								    	}
      								    ?>><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
      								</div>
      							</form>
      						</div>
      					</div>
                <div class='card card-primary my-3'>
                  <div class='card-heading'>
                    <div class='row g-0 px-3 py-2'>
                      <div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Ticket( 1 );?><span>Tickets</span></h5></div>
                      <div class='col-2'><button class='h-100 w-100' type='button' onClick="document.location.href='tickets.php?Location=<?php echo $Division[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                    </div>
                  </div>
                  <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Tickets' ] ) && $_SESSION[ 'Cards' ][ 'Tickets' ] == 0 ? "style='display:none;'" : null;?>>
                    <div class='row g-0'>
                        <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Ticket(1);?> Statuses</div>
                        <div class='col-6'>&nbsp;</div>
                      <div class='col-2'>&nbsp;</div>
                    </div>
                    <div class='row g-0'><?php
                        $r = \singleton\database::getInstance( )->query(
                          null,
                          "	SELECT Count( Tickets.ID ) AS Tickets
                            FROM   (
                                  (
                                    SELECT 	TicketO.ID AS ID
                                    FROM   	TicketO
                                          LEFT JOIN Loc AS Location ON TicketO.LID = Location.Loc
                                    WHERE  		Location.Owner = ?
                                        AND TicketO.Assigned = 0
                                  )
                                ) AS Tickets;",
                          array(
                            $Division[ 'ID' ]
                          )
                        );
                      ?><div class='col-1'>&nbsp;</div>
                        <div class='col-3 border-bottom border-white my-auto'>Open</div>
                        <div class='col-6'><input class='form-control' type='text' readonly name='Tickets' value='<?php
                        echo $r ? sqlsrv_fetch_array($r)[ 'Tickets' ] : 0;
                      ?>' /></div>
                      <div class='col-2'><button class='h-100 w-100' onClick="document.location.href='tickets.php?Location=<?php echo $Division[ 'Name' ];?>&Status=0';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                    </div>
                    <div class='row g-0'><?php
                        $r = \singleton\database::getInstance( )->query(
                          null,
                          "	SELECT Count( Tickets.ID ) AS Tickets
                            FROM   (
                                  (
                                    SELECT 	TicketO.ID AS ID
                                    FROM   	TicketO
                                          LEFT JOIN Loc AS Location ON TicketO.LID = Location.Loc
                                    WHERE  		Location.Owner = ?
                                        AND TicketO.Assigned = 1
                                  )
                                ) AS Tickets;",
                          array(
                            $Division[ 'ID' ]
                          )
                        );
                      ?><div class='col-1'>&nbsp;</div>
                        <div class='col-3 border-bottom border-white my-auto'>Assigned</div>
                        <div class='col-6'><input class='form-control' type='text' readonly name='Tickets' value='<?php
                        echo $r ? sqlsrv_fetch_array($r)[ 'Tickets' ] : 0;
                      ?>' /></div>
                      <div class='col-2'><button class='h-100 w-100' onClick="document.location.href='tickets.php?Location=<?php echo $Division[ 'Name' ];?>&Status=1';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                    </div>
                    <div class='row g-0'><?php
                        $r = \singleton\database::getInstance( )->query(
                          null,
                          "	SELECT Count( Tickets.ID ) AS Tickets
                            FROM   (
                                  (
                                    SELECT 	TicketO.ID AS ID
                                    FROM   	TicketO
                                          LEFT JOIN Loc AS Location ON TicketO.LID = Location.Loc
                                    WHERE  		Location.Owner = ?
                                        AND TicketO.Assigned = 2
                                  )
                                ) AS Tickets;",
                          array(
                            $Division[ 'ID' ]
                          )
                        );
                      ?><div class='col-1'>&nbsp;</div>
                        <div class='col-3 border-bottom border-white my-auto'>En Route</div>
                        <div class='col-6'><input class='form-control' type='text' readonly name='Tickets' value='<?php
                        echo $r ? sqlsrv_fetch_array($r)[ 'Tickets' ] : 0;
                      ?>' /></div>
                      <div class='col-2'><button class='h-100 w-100' onClick="document.location.href='tickets.php?Location=<?php echo $Division[ 'Name' ];?>&Status=2';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                    </div>
                    <div class='row g-0'><?php
                        $r = \singleton\database::getInstance( )->query(
                          null,
                          "	SELECT Count( Tickets.ID ) AS Tickets
                            FROM   (
                                  (
                                    SELECT 	TicketO.ID AS ID
                                    FROM   	TicketO
                                          LEFT JOIN Loc AS Location ON TicketO.LID = Location.Loc
                                    WHERE  		Location.Owner = ?
                                        AND TicketO.Assigned = 3
                                  )
                                ) AS Tickets;",
                          array(
                            $Division[ 'ID' ]
                          )
                        );
                      ?><div class='col-1'>&nbsp;</div>
                        <div class='col-3 border-bottom border-white my-auto'>On Site</div>
                        <div class='col-6'><input class='form-control' type='text' readonly name='Tickets' value='<?php
                        echo $r ? sqlsrv_fetch_array($r)[ 'Tickets' ] : 0;
                      ?>' /></div>
                      <div class='col-2'><button class='h-100 w-100' onClick="document.location.href='tickets.php?Location=<?php echo $Division[ 'Name' ];?>&Status=3';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                    </div>
                    <div class='row g-0'><?php
                        $r = \singleton\database::getInstance( )->query(
                          null,
                          "	SELECT Count( Tickets.ID ) AS Tickets
                            FROM   (
                                  (
                                    SELECT 	TicketO.ID AS ID
                                    FROM   	TicketO
                                          LEFT JOIN Loc AS Location ON TicketO.LID = Location.Loc
                                    WHERE  		Location.Owner = ?
                                        AND TicketO.Assigned = 6
                                  )
                                ) AS Tickets;",
                          array(
                            $Division[ 'ID' ]
                          )
                        );
                      ?><div class='col-1'>&nbsp;</div>
                        <div class='col-3 border-bottom border-white my-auto'>Review</div>
                        <div class='col-6'><input class='form-control' type='text' readonly name='Tickets' value='<?php
                        echo $r ? sqlsrv_fetch_array($r)[ 'Tickets' ] : 0;
                      ?>' /></div>
                      <div class='col-2'><button class='h-100 w-100' onClick="document.location.href='tickets.php?Location=<?php echo $Division[ 'Name' ];?>&Status=6';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                    </div>
                    <div class='row g-0'><?php
                        $r = \singleton\database::getInstance( )->query(
                          null,
                          "	SELECT Count( Tickets.ID ) AS Tickets
                            FROM   (
                                  (
                                    SELECT 	TicketO.ID AS ID
                                    FROM   	TicketO
                                          LEFT JOIN Loc AS Location ON TicketO.LID = Location.Loc
                                    WHERE  		Location.Owner = ?
                                        AND TicketO.Assigned = 4
                                  )
                                ) AS Tickets;",
                          array(
                            $Division[ 'ID' ]
                          )
                        );
                      ?><div class='col-1'>&nbsp;</div>
                        <div class='col-3 border-bottom border-white my-auto'>Complete</div>
                        <div class='col-6'><input class='form-control' type='text' readonly name='Tickets' value='<?php
                        echo $r ? sqlsrv_fetch_array($r)[ 'Tickets' ] : 0;
                      ?>' /></div>
                      <div class='col-2'><button class='h-100 w-100' onClick="document.location.href='tickets.php?Location=<?php echo $Division[ 'Name' ];?>&Status=4';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                    </div>
                  </div>
                </div>
                <div class='card card-primary my-3'>
                  <div class='card-heading'>
                    <div class='row g-0 px-3 py-2'>
                      <div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Unit( 1 );?><span>Units</span></h5></div>
                      <div class='col-2'><button class='h-100 w-100' onClick="document.location.href='units.php?Location=<?php echo $Division[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                    </div>
                  </div>
                  <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Units' ] ) && $_SESSION[ 'Cards' ][ 'Units' ] == 0 ? "style='display:none;'" : null;?>>
                    <?php
                      $r = \singleton\database::getInstance( )->query(
                        null,
                        "	SELECT 	Count( Unit.ID ) AS Units
                          FROM   	Elev AS Unit
                                LEFT JOIN Loc AS Location ON Unit.Loc = Location.Loc
                          WHERE  	Location.Owner = ? ;",
                        array(
                          $Division[ 'ID' ]
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
                        $r = \singleton\database::getInstance( )->query(
                          null,
                          "	SELECT 	Count( Unit.ID ) AS Units
                            FROM   	Elev AS Unit
                                  LEFT JOIN Loc AS Location ON Unit.Loc = Location.Loc
                            WHERE  		Location.Owner = ?
                                AND Unit.Type = 'Elevator'
                        ;",array($Division[ 'ID' ]));
                        //Selects the unit.ID as counts from Elev and adds it to $Division[ID]
                        echo $r ? sqlsrv_fetch_array($r)['Units'] : 0;
                      ?>' /></div>
                      <div class='col-2'><button class='h-100 w-100' onClick="document.location.href='units.php?Location=<?php echo $Division[ 'Name' ];?>&Type=Elevator';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                    </div>
                    <div class='row g-0'>
                      <div class='col-1'>&nbsp;</div>
                        <div class='col-3 border-bottom border-white my-auto'>Escalators</div>
                        <div class='col-6'><input class='form-control' type='text' readonly name='Units' value='<?php
                        $r = \singleton\database::getInstance( )->query(null,
                          " SELECT 	Count( Unit.ID ) AS Units
                            FROM   	Elev AS Unit
                                LEFT JOIN Loc AS Location ON Unit.Loc = Location.Loc
                            WHERE  		Location.Owner = ?
                            AND Unit.Type = 'Escalator'
                        ;",array($Division[ 'ID' ]));
                        echo $r ? sqlsrv_fetch_array($r)['Units'] : 0;
                      ?>' /></div>
                      <div class='col-2'><button class='h-100 w-100' onClick="document.location.href='units.php?Location=<?php echo $Division[ 'Name' ];?>&Type=Escalator';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                    </div>
                    <div class='row g-0'>
                      <div class='col-1'>&nbsp;</div>
                        <div class='col-3 border-bottom border-white my-auto'>Other</div>
                        <div class='col-6'><input class='form-control' type='text' readonly name='Units' value='<?php
                        $r = \singleton\database::getInstance( )->query(null,
                          " SELECT 	Count( Unit.ID ) AS Units
                            FROM   	Elev AS Unit
                                LEFT JOIN Loc AS Location ON Unit.Loc = Location.Loc
                            WHERE  		Location.Owner = ?
                              AND Unit.Type NOT IN ( 'Elevator', 'Escalator' )
                        ;",array($Division[ 'ID' ]));
                        echo $r ? sqlsrv_fetch_array($r)['Units'] : 0;
                      ?>' /></div>
                      <div class='col-2'><button class='h-100 w-100' readonly onClick="document.location.href='units.php?Location=<?php echo $Division[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                  </div>
                </div>
              </div>
            </div>
        </div>
    </body>
</html>
<?php }
} else {?><script>document.location.href='../login.php?Forward=divisions.php&<?php echo http_build_query( $_GET );?>';</script><?php }?>
