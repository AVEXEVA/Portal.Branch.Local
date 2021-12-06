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
        ||  !isset( $Privileges[ 'Unit' ] )
        ||  !check( privilege_read, level_group, $Privileges[ 'User' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
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
        " SELECT 	Violation.ID        AS ID,
                  Violation.Name      AS Name,
                  Job.ID              AS Job_ID,
                  Job.fDesc           AS Job_Name,
                  Customer.ID         AS Customer_ID,
                  Customer.Name       AS Customer_Name,
                  Location.Loc        AS Location_ID,
                  Location.Tag        AS Location_Name,
                  Location.Address    AS Location_Street,
                  Location.City       AS Location_City,
                  Location.State      AS Location_State,
                  Location.Zip        AS Location_Zip,
                  Location.Latt       AS Location_Latitude,
                  Location.fLong      AS Location_Longitude,
                  Unit.ID             AS Unit_ID,
                  Unit.State          AS Unit_Name,
                  Unit.State          AS Unit_City_ID,
                  Unit.Unit           AS Unit_Building_ID,
                  Violation.fdate     AS Date,
                  Violation.Status    AS Status,
                  Quote.Ref           AS Quote_ID,
                  Quote.fDesc         AS Quote_Name,
                  Inspection.ID       AS Inspection_ID,
                  Inspection.Type     AS Inspection_Name,
                  Violation.Ticket    AS Ticket,
                  Violation.Remarks   AS Note,
                  Violation.Estimate  AS Estimate,
                  Violation.Price     AS Price,
                  Violation.Custom1   AS File_Permit,
                  Violation.Custom2   AS Permit_Approved,
                  Violation.Custom3   AS Date_Sent,
                  Violation.Custom4   AS Forms_to_DOB,
                  Violation.Custom5   AS Inspection,
                  Violation.Custom6   AS Hearing,
                  Violation.Custom7   AS Due_Date,
                  Violation.Custom8   AS Forms_to_Customer,
                  Violation.Custom9   AS Recieved_from_Customer,
                  Violation.Custom10  AS Cancel_Contract
		      FROM    Violation
                  LEFT JOIN Job                      ON Violation.Job   = Job.ID
                  LEFT JOIN (
                    SELECT  Owner.ID,
                            Rol.Name
                    FROM    Owner
                            LEFT JOIN Rol            ON Owner.Rol       = Rol.ID
                  ) AS Customer                      ON Customer.ID     = Job.Owner
                  LEFT JOIN Loc        AS Location   ON Violation.Loc   = Location.Loc
                  LEFT JOIN Elev       AS Unit       ON Violation.Elev  = Unit.ID
                  LEFT JOIN Quote      AS Quote      ON Violation.Quote = Quote.Ref
                  LEFT JOIN Inspection AS Inspection ON Violation.ID    = Inspection.Violation
        	WHERE   	Violation.ID = ?
        			OR 	Violation.Name = ?;",
        array(
        	$ID,
        	$Name
        )
      );
      //var_dump( sqlsrv_errors( ) );
      $Violation =   (  empty( $ID )
                   &&  !empty( $Name )
                   &&  !$result
              )    || (empty( $ID )
                   &&  empty( $Name )
              )    ? array(
      	'ID' => null,
        'Name' => null,
        'Customer_ID' => null,
        'Customer_Name' => null,
        'Location_ID' => null,
        'Location_Name' => null,
        'Location_Street' => null,
        'Location_City' => null,
        'Location_State' => null,
        'Location_Zip' => null,
        'Location_Latitude' => null,
        'Location_Longitude' => null,
        'Unit_ID' => null,
        'Unit_Name' => null,
        'Proposal_ID' => null,
        'Proposal_Name' => null,
        'Quote_ID' => null,
        'Quote_Name' => null,
        'Job_ID' => null,
        'Job_Name' => null,
        'Ticket_ID' => null,
      	'Date' => null,
      	'Status' => null,
      	'Note' => null,
      	'Price' => null,
        'Address' => null,
        'Phone' => null,
      	'Contact' => null,
        'File_Permit' => null,
        'Permit_Approved' => null,
        'Date_Sent' => null,
        'Forms_to_DOB' => null,
        'Inspection' => null,
        'Hearing' => null,
        'Due_Date' => null,
        'Forms_to_Customer' => null,
        'Recieved_from_Customer' => null,
        'Cancel_Contract' => null,
      ) : sqlsrv_fetch_array($result);

      if( isset( $_POST ) && count( $_POST ) > 0 ){
      	$Violation[ 'Name' ] 		       = isset( $_POST[ 'Name' ] ) 	       ? $_POST[ 'Name' ] 	      : $Violation[ 'Name' ];
	      $Violation[ 'Customer_Name' ]  = isset( $_POST[ 'Customer' ] )     ? $_POST[ 'Customer' ]     : $Violation[ 'Customer_Name' ];
        $Violation[ 'Location_Name' ]  = isset( $_POST[ 'Location' ] )     ? $_POST[ 'Location' ]     : $Violation[ 'Location_Name' ];
        $Violation[ 'Unit_Name' ]      = isset( $_POST[ 'Unit' ] )         ? $_POST[ 'Unit' ]         : $Violation[ 'Unit_Name' ];
        $Violation[ 'Proposal_Name' ]  = isset( $_POST[ 'Proposal' ] )     ? $_POST[ 'Proposal' ]     : $Violation[ 'Proposal_Name' ];
        $Violation[ 'Quote_Name' ]     = isset( $_POST[ 'Quote' ] )        ? $_POST[ 'Quote' ]        : $Violation[ 'Quote_Name' ];
        $Violation[ 'Job_Name' ]       = isset( $_POST[ 'Job' ] )          ? $_POST[ 'Job' ]          : $Violation[ 'Job_Name' ];
        $Violation[ 'Ticket_ID' ]      = isset( $_POST[ 'Ticket' ] )       ? $_POST[ 'Ticket' ]       : $Violation[ 'Ticket_ID' ];
        $Violation[ 'Date' ]           = isset( $_POST[ 'Date' ] )         ? $_POST[ 'Date' ]         : $Violation[ 'Date' ];
        $Violation[ 'Status' ]         = isset( $_POST[ 'Status' ] )       ? $_POST[ 'Status' ]       : $Violation[ 'Status' ];
        $Violation[ 'Note' ]           = isset( $_POST[ 'Note' ] )         ? $_POST[ 'Note' ]         : $Violation[ 'Note' ];
        $Violation[ 'Price' ]          = isset( $_POST[ 'Price' ] )        ? $_POST[ 'Price' ]        : $Violation[ 'Price' ];

      	if( in_array( $_POST[ 'ID' ], array( null, 0, '', ' ' ) ) ){
      		$result = \singleton\database::getInstance( )->query(
      			null,
      			"	INSERT INTO Violation(
                Job,
      					Loc,
      					Elev,
                Ticket,
                fDate,
                Status,
                Quote,
      					Remarks,
                Price
      				)
      				VALUES(
                (
                  SELECT  Job.ID
                  FROM    Job
                  WHERE   Job.fDesc = ?
                ),(
                  SELECT  Loc.Loc
                  FROM    Loc
                  WHERE   Loc.Tag = ?
                ),(
                  SELECT  Elev.ID
                  FROM    Elev
                  WHERE   Elev.State = ?
                ),
                ?,
                ?,
                ?,
                ?,
                ?,
                ?
              );
      				SELECT SCOPE_IDENTITY( );",
      			array(
      				$Violation[ 'Job_Name' ],
              $Violation[ 'Location_Name' ],
              $Violation[ 'Unit_Name' ],
              empty( $Violation[ 'Ticket_ID' ] ) ? null : $Violation[ 'Ticket_ID' ],
              empty( $Violation[ 'Date' ] ) ? null : date( 'Y-m-d h:i:s', strtotime( $Violation[ 'Date' ] ) ),
              $Violation[ 'Status' ],
              $Violation[ 'Quote_ID' ],
              $Violation[ 'Note' ],
              empty( $Violation[ 'Price' ] ) ? 0 : $Violation[ 'Price' ]
      			)
      		);
      		sqlsrv_next_result( $result );
      		$Violation[ 'ID' ] = sqlsrv_fetch_array( $result )[ 0 ];
      		header( 'Location: violation.php?ID=' . $Violation[ 'ID' ] );
      		exit;
      	} else {
      		\singleton\database::getInstance( )->query(
        		null,
        		"	UPDATE 	Violation
        			SET     Violation.Job = (
                        SELECT  Top 1 
                                Job.ID
                        FROM    Job
                        WHERE   Job.fDesc = ?
                      ),
        					    Violation.Loc = (
                        SELECT  Top 1 
                                Loc.Loc
                        FROM    Loc
                        WHERE   Loc.Tag = ?
                      ),
        					    Violation.Elev = (
                        SELECT  Top 1 
                                Elev.ID
                        FROM    Elev
                        WHERE   Elev.ID = ?
                      ),
                      Violation.Ticket = ?,
                      Violation.Name = ?,
        					    Violation.fDate = ?,
                      Violation.Status = ?,
                      Violation.Quote = ?,
                      Violation.Remarks = ?,
                      Violation.Price = ?
        			WHERE   Violation.ID = ?;",
        		array(
              $Violation[ 'Job_Name' ],
              $Violation[ 'Location_Name' ],
              $Violation[ 'Unit_Name' ],
              $Violation[ 'Ticket_ID' ],
              $Violation[ 'Name' ],
              $Violation[ 'Date' ],
              $Violation[ 'Status' ],
              $Violation[ 'Quote_ID' ],
              $Violation[ 'Note' ],
              $Violation[ 'Price' ],
      				$Violation[ 'ID' ]
        		)
        	);
      	}
    }
?><!DOCTYPE html>
<html lang="en">
<head>
  	<title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
    <?php  	$_GET[ 'Bootstrap' ] = '5.1';?>
    <?php  	$_GET[ 'Entity_CSS' ] = 1;?>
    <?php	  require( bin_meta . 'index.php');?>
    <?php	  require( bin_css  . 'index.php');?>
    <?php 	require( bin_js   . 'index.php');?>
</head>
<body>
	<div id="wrapper">
    <?php require(bin_php .'element/navigation.php');?>
    <div id="page-wrapper" class='content'>
    	<div class='card card-primary'><form action='violation.php?ID=<?php echo $Violation[ 'ID' ];?>' method='POST'>
        <input type='hidden' name='ID' value='<?php echo $Violation[ 'ID' ];?>' />
        <div class='card-heading'>
        	<div class='row g-0 px-3 py-2'>
          	<div class='col-12 col-lg-6'>
              	<h5><?php \singleton\fontawesome::getInstance( )->Violation( 1 );?><a href='violations.php?<?php
                	echo http_build_query( is_array( $_SESSION[ 'Tables' ][ 'Violations' ][ 0 ] ) ? $_SESSION[ 'Tables' ][ 'Violations' ][ 0 ] : array( ) );
              	?>'>Violation</a>: <span><?php
                	echo is_null( $Violation[ 'ID' ] )
                  		? 'New'
                  		: $Violation[ 'ID' ];
              	?></span></h5>
          	</div>
          	<div class='col-6 col-lg-3'>
            		<div class='row g-0'>
              		<div class='col-4'>
	                 	<button
	                    	class='form-control rounded'
	                    	onClick="document.location.href='violation.php';"
	                  	><?php \singleton\fontawesome::getInstance( 1 )->Save( 1 );?><span class='desktop'> Save</span></button>
	                </div>
	                <div class='col-4'>
	                  	<button
	                    	class='form-control rounded'
	                    	onClick="document.location.href='violation.php?ID=<?php echo $User[ 'ID' ];?>';"
	                  	><?php \singleton\fontawesome::getInstance( 1 )->Refresh( 1 );?><span class='desktop'> Refresh</span></button>
	                </div>
	                <div class='col-4'>
	                  	<button
	                    	class='form-control rounded'
	                    	onClick="document.location.href='violation.php';"
	                  	><?php \singleton\fontawesome::getInstance( 1 )->Add( 1 );?><span class='desktop'> New</span></button>
	                </div>
	            </div>
          	</div>
          	<div class='col-6 col-lg-3'>
            		<div class='row g-0'>
              		<div class='col-4'><button class='form-control rounded' onClick="document.location.href='violation.php?ID=<?php echo !is_null( $User[ 'ID' ] ) ? array_keys( $_SESSION[ 'Tables' ][ 'Users' ], true )[ array_search( $User[ 'ID' ], array_keys( $_SESSION[ 'Tables' ][ 'Users' ], true ) ) - 1 ] : null;?>';"><?php \singleton\fontawesome::getInstance( 1 )->Previous( 1 );?><span class='desktop'> Previous</span></button></div>
              		<div class='col-4'><button class='form-control rounded' onClick="document.location.href='violations.php?<?php echo http_build_query( is_array( $_SESSION[ 'Tables' ][ 'Users' ][ 0 ] ) ? $_SESSION[ 'Tables' ][ 'Users' ][ 0 ] : array( ) );?>';"><?php \singleton\fontawesome::getInstance( 1 )->Table( 1 );?><span class='desktop'> Table</span></button></div>
              		<div class='col-4'><button class='form-control rounded' onClick="document.location.href='violation.php?ID=<?php echo !is_null( $User[ 'ID' ] )? array_keys( $_SESSION[ 'Tables' ][ 'Users' ], true )[ array_search( $User[ 'ID' ], array_keys( $_SESSION[ 'Tables' ][ 'Users' ], true ) ) + 1 ] : null;?>';"><?php \singleton\fontawesome::getInstance( 1 )->Next( 1 );?><span class='desktop'> Next</span></button></div>
            		</div>
          	</div>
        	</div>
      	</div>
      	<div class='card-body bg-dark text-white'>
      		<div class='row g-0'>
        		<div class='card card-primary col-12 col-md-6 col-lg-4 col-xl-3'>
          		<div class='card-heading'>
            		<div class='row g-0 px-3 py-2'>
              			<div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Info( 1 );?><span>Infomation</span></h5></div>
              			<div class='col-2'>&nbsp;</div>
            		</div>
          		</div>
          		<div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Infomation' ] ) && $_SESSION[ 'Cards' ][ 'Infomation' ] == 0 ? "style='display:none;'" : null;?>>
            		<div class='row g-0'>
              			<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Customer( 1 );?> Name:</div>
              			<div class='col-8'><input placeholder='Name' type='text' class='form-control edit' name='Name' value='<?php echo is_null( $Violation[ 'Name' ] ) ? null : $Violation[ 'Name' ];?>' /></div>
              	</div>
              	<div class='row g-0'>
                		<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Status:</div>
                		<div class='col-8'><select name='Status' class='form-control edit'>
                  		<option value=''>Select</option>
                  		<option value='0' <?php echo $Violation[ 'Status' ] == 0 ? 'selected' : null;?>>Active</option>
                  		<option value='1' <?php echo $Violation[ 'Status' ] == 1 ? 'selected' : null;?>>Inactive</option>
                		</select></div>
              	</div>
              	<div class='row g-0'>
                  	<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Calendar( 1 );?> Date:</div>
                  	<div class='col-8'><input type='input' class='form-control edit date' name='Date' value='<?php echo is_null( $Violation[ 'Date' ] ) ? null : date( 'm/d/Y', strtotime( $Violation[ 'Date' ] ) );?>' /></div>
              	</div>
                <div class='row g-0'>
                  <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Violation(1);?> Location:</div>
                  <div class='col-6'>
                    <input type='text' placeholder='Location' autocomplete='off' class='form-control edit' name='Location' value='<?php echo $Violation[ 'Location_Name' ];?>' />
                    <script>
                      $( 'input[name="Location"]' )
                          .typeahead({
                              minLength : 4,
                              hint: true,
                              highlight: true,
                              limit : 5,
                              display : 'FieldValue',
                              source: function( query, result ){
                                  $.ajax({
                                      url : 'bin/php/get/search/Locations.php',
                                      method : 'GET',
                                      data    : {
                                    search :  $('input:visible[name="Location"]').val( )
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
                                  $( 'input[name="Location"]').val( value );
                                  $( 'input[name="Location"]').closest( 'form' ).submit( );
                              }
                          }
                      );
                    </script>
                  </div>
                  <div class='col-2'><button class='h-100 w-100' type='button' <?php
                    if( in_array( $Violation[ 'Location_ID' ], array( null, 0, '', ' ') ) ){
                      echo "onClick=\"document.location.href='locations.php';\"";
                    } else {
                      echo "onClick=\"document.location.href='location.php?Name=" . $Violation[ 'Location_Name' ] . "';\"";
                    }
                  ?>><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                </div>
                <div class='row g-0'>
                  <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Job(1);?> Job:</div>
                  <div class='col-6'>
                    <input type='text' placeholder='Job #000000' autocomplete='off' class='form-control edit' name='Job' value='<?php echo $Violation['Job_Name'];?>' />
                    <script>
                      $( 'input[name="Job"]' )
                          .typeahead({
                              minLength : 4,
                              hint: true,
                              highlight: true,
                              limit : 5,
                              display : 'FieldValue',
                              source: function( query, result ){
                                $.ajax({
                                  url : 'bin/php/get/search/Jobs.php',
                                  method : 'GET',
                                  data    : {
                                    search :  $('input:visible[name="Job"]').val( )
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
                                $( 'input[name="Job"]').val( value );
                                $( 'input[name="Job"]').closest( 'form' ).submit( );
                              }
                          }
                      );
                    </script>
                  </div>
                  <div class='col-2'><button class='h-100 w-100' type='button' <?php
                    if( in_array( $Violation[ 'Job_ID' ], array( null, 0, '', ' ') ) ){
                      echo "onClick=\"document.location.href='jobs.php';\"";
                    } else {
                      echo "onClick=\"document.location.href='job.php?ID=" . $Violation[ 'Job_ID' ] . "';\"";
                    }
                  ?>><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                </div>
                <div class='row g-0'>
                  <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Ticket(1);?> Ticket:</div>
                  <div class='col-6'>
                    <input type='text' placeholder='Ticket #000000' autocomplete='off' class='form-control edit' name='Ticket' value='' />
                    <script>
                      $( 'input[name="Ticket"]' )
                          .typeahead({
                              minLength : 4,
                              hint: true,
                              highlight: true,
                              limit : 5,
                              display : 'FieldValue',
                              source: function( query, result ){
                                  $.ajax({
                                      url : 'bin/php/get/search/Tickets.php',
                                      method : 'GET',
                                      data    : {
                                          search :  $('input:visible[name="Ticket"]').val( ),
                                          Location : $('input:visible[name="Location"]').val( )
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
                                  $( 'input[name="Ticket"]').val( value );
                                  $( 'input[name="Ticket"]').closest( 'form' ).submit( );
                              }
                          }
                      );
                    </script>
                  </div>
                  <div class='col-2'><button class='h-100 w-100' type='button' <?php
                    if( in_array( $Violation[ 'Job_ID' ], array( null, 0, '', ' ') ) ){
                      echo "onClick=\"document.location.href='tickets.php';\"";
                    } else {
                      echo "onClick=\"document.location.href='ticket.php?ID=" . $Violation[ 'Ticket_ID' ] . "';\"";
                    }
                  ?>><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                </div>
                <div class='row g-0'>
                  <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Unit(1);?> Unit:</div>
                  <div class='col-6'>
                    <input type='text' placeholder='Unit City ID' autocomplete='off' class='form-control edit' name='Unit' value='<?php echo $Violation['Unit_Name']?>' />
                    <script>
                      $( 'input[name="Elev"]' )
                          .typeahead({
                              minLength : 4,
                              hint: true,
                              highlight: true,
                              limit : 5,
                              display : 'FieldValue',
                              source: function( query, result ){
                                  $.ajax({
                                      url : 'bin/php/get/search/Units.php',
                                      method : 'GET',
                                      data    : {
                                          search :  $('input:visible[name="Unit"]').val( ),
                                          Customer : $('input:visible[name="Customer"]').val( ),
                                          Location : $('input:visible[name="Location"]').val( )
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
                                  $( 'input[name="Unit"]').val( value );
                                  $( 'input[name="Unit"]').closest( 'form' ).submit( );
                              }
                          }
                      );
                    </script>
                  </div>
                  <div class='col-2'><button class='h-100 w-100' type='button' <?php
                    if( in_array( $Violation[ 'Unit_ID' ], array( null, 0, '', ' ') ) ){
                      echo "onClick=\"document.location.href='units.php';\"";
                    } else {
                      echo "onClick=\"document.location.href='unit.php?ID=" . $Violation[ 'Unit_ID' ] . "';\"";
                    }
                  ?>><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                </div>
                <div class='row g-0'>
                  <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Proposal(1);?> Proposal:</div>
                  <div class='col-6'>
                  <input type='text' placeholder='Proposal #000000' autocomplete='off' class='form-control edit' name='Proposal' value='' />
                  <script>
                    $( 'input[name="Proposal"]' )
                        .typeahead({
                            minLength : 4,
                            hint: true,
                            highlight: true,
                            limit : 5,
                            display : 'FieldValue',
                            source: function( query, result ){
                                $.ajax({
                                    url : 'bin/php/get/search/Proposals.php',
                                    method : 'GET',
                                    data    : {
                                        search :  $('input:visible[name="Proposal"]').val( ),
                                        Location : $('input:visible[name="Location"]').val( )
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
                                $( 'input[name="Proposal"]').val( value );
                                $( 'input[name="Proposal"]').closest( 'form' ).submit( );
                            }
                        }
                    );
                  </script>
                </div>
                <div class='col-2'><button class='h-100 w-100' type='button' <?php
                  if( in_array( $Violation[ 'Job_ID' ], array( null, 0, '', ' ') ) ){
                    echo "onClick=\"document.location.href='proposals.php';\"";
                  } else {
                    echo "onClick=\"document.location.href='proposal.php?ID=" . $Violation[ 'Proposal_ID' ] . "';\"";
                  }
                ?>><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
              </div>
              <div class='row g-0'>
                  <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Inspection(1);?> Inspection:</div>
                  <div class='col-6'>
                    <input type='text' placeholder='Inspection #000000' autocomplete='off' class='form-control edit' name='Inspection' value='<?php echo $Violation['Inspection_Name']?>' />
                    <script>
                      $( 'input[name="Inspection"]' )
                          .typeahead({
                              minLength : 4,
                              hint: true,
                              highlight: true,
                              limit : 5,
                              display : 'FieldValue',
                              source: function( query, result ){
                                  $.ajax({
                                      url : 'bin/php/get/search/Inspections.php',
                                      method : 'GET',
                                      data    : {
                                          search :  $('input:visible[name="Inspection"]').val( ),
                                          Location : $('input:visible[name="Location"]').val( )
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
                                  $( 'input[name="Inspection"]').val( value );
                                  $( 'input[name="Inspection"]').closest( 'form' ).submit( );
                              }
                          }
                      );
                    </script>
                  </div>
                  <div class='col-2'><button class='h-100 w-100' type='button' <?php
                    if( in_array( $Violation[ 'Inspection_ID' ], array( null, 0, '', ' ') ) ){
                      echo "onClick=\"document.location.href='inspections.php';\"";
                    } else {
                      echo "onClick=\"document.location.href='inspection.php?ID=" . $Violation[ 'Inspection_ID' ] . "';\"";
                    }
                  ?>><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                </div>
            	<div class='row g-0'>
            		<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Dollar( 1 );?> Price:</div>
            		<div class='col-8'><input placeholder='$.00' type='text' class='form-control edit' name='Price' value='<?php echo $Violation[ 'Price' ];?>' /></div>
            	</div>
              <div class='row g-0'>
                <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Note( 1 );?> Note:</div>
                <div class='col-8'><textarea placeholder='Notes' type='text' class='form-control edit' name='Note' value='<?php echo $Violation[ 'Note' ];?>' rows='8'></textarea></div>
              </div>
            </div>
          </div>
          <div class='card card-primary col-12 col-md-6 col-lg-4 col-xl-3'>
            <div class='card-heading'>
              <div class='row g-0 px-3 py-2'>
                  <div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Info( 1 );?><span>Dates</span></h5></div>
                  <div class='col-2'>&nbsp;</div>
              </div>
            </div>
            <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Dates' ] ) && $_SESSION[ 'Cards' ][ 'Dates' ] == 0 ? "style='display:none;'" : null;?>>
              <div class='row g-0'>
                <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Customer( 1 );?> File Permit:</div>
                <div class='col-8'><input placeholder='File_Permit' type='text' class='form-control edit date' name='File_Permit' value='<?php echo is_null( $Violation[ 'File_Permit' ] ) ? null : date( 'm/d/Y', strtotime( $Violation[ 'File_Permit' ] ) );?>' /></div>
              </div>
              <div class='row g-0'>
                <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Customer( 1 );?> Permit Approved:</div>
                <div class='col-8'><input placeholder='File_Permit' type='text' class='form-control edit date' name='File_Permit' value='<?php echo is_null( $Violation[ 'Permit_Approved' ] ) ? null : date( 'm/d/Y', strtotime( $Violation[ 'Permit_Approved' ] ) );?>' /></div>
              </div>
              <div class='row g-0'>
                <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Customer( 1 );?> Date Sent:</div>
                <div class='col-8'><input placeholder='File_Permit' type='text' class='form-control edit date' name='File_Permit' value='<?php echo is_null( $Violation[ 'Date_Sent' ] ) ? null : date( 'm/d/Y', strtotime( $Violation[ 'Date_Sent' ] ) );?>' /></div>
              </div>
              <div class='row g-0'>
                <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Customer( 1 );?> Forms to DOB:</div>
                <div class='col-8'><input placeholder='Forms_to_DOB' type='text' class='form-control edit date' name='Forms_to_DOB' value='<?php echo is_null( $Violation[ 'Forms_to_DOB' ] ) ? null : date( 'm/d/Y', strtotime( $Violation[ 'Forms_to_DOB' ] ) );?>' /></div>
              </div>
              <div class='row g-0'>
                <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Customer( 1 );?> Inspection:</div>
                <div class='col-8'><input placeholder='Inspection' type='text' class='form-control edit date' name='Inspection' value='<?php echo is_null( $Violation[ 'Inspection' ] ) ? null : date( 'm/d/Y', strtotime( $Violation[ 'Inspection' ] ) );?>' /></div>
              </div>
              <div class='row g-0'>
                <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Customer( 1 );?> Hearing:</div>
                <div class='col-8'><input placeholder='Hearing' type='text' class='form-control edit date' name='Hearing' value='<?php echo is_null( $Violation[ 'Hearing' ] ) ? null : date( 'm/d/Y', strtotime( $Violation[ 'Hearing' ] ) );?>' /></div>
              </div>
              <div class='row g-0'>
                <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Customer( 1 );?> Due Date:</div>
                <div class='col-8'><input placeholder='Due_Date' type='text' class='form-control edit date' name='Due_Date' value='<?php echo is_null( $Violation[ 'Due_Date' ] ) ? null : date( 'm/d/Y', strtotime( $Violation[ 'Due_Date' ] ) );?>' /></div>
              </div>
              <div class='row g-0'>
                <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Customer( 1 );?> Forms to Customer:</div>
                <div class='col-8'><input placeholder='Forms_to_Customer' type='text' class='form-control edit date' name='Forms_to_Customer' value='<?php echo is_null( $Violation[ 'Forms_to_Customer' ] ) ? null : date( 'm/d/Y', strtotime( $Violation[ 'Forms_to_Customer' ] ) );?>' /></div>
              </div>
              <div class='row g-0'>
                <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Customer( 1 );?> Recieved from Customer:</div>
                <div class='col-8'><input placeholder='Recieved_from_Customer' type='text' class='form-control edit date' name='Recieved_from_Customer' value='<?php echo is_null( $Violation[ 'Recieved_from_Customer' ] ) ? null : date( 'm/d/Y', strtotime( $Violation[ 'Recieved_from_Customer' ] ) );?>' /></div>
              </div>
              <div class='row g-0'>
                <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Customer( 1 );?> Cancel Contract:</div>
                <div class='col-8'><input placeholder='Cancel_Contract' type='text' class='form-control edit date' name='Cancel_Contract' value='<?php echo is_null( $Violation[ 'Cancel_Contract' ] ) ? null : date( 'm/d/Y', strtotime( $Violation[ 'Cancel_Contract' ] ) );?>' /></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </form></div>
  </div>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=violation<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";


</script></head></html><?php }?>
>>>>>>> 2ce4a5e50db66f2cb7f130c90ac17a3c393918ff
