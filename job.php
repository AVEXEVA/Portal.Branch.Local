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
    if(   !isset( $Connection[ 'ID' ] )
        ||  !isset( $Privileges[ 'Job' ] )
        ||  !check( privilege_read, level_group, $Privileges[ 'Job' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
      \singleton\database::getInstance( )->query(
            null,
            " INSERT INTO Activity([User], [Date], [Page] )
              VALUES( ?, ?, ? );",
            array(
              $_SESSION[ 'Connection' ][ 'User' ],
              date('Y-m-d H:i:s'),
              'employees.php'
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
     		'	SELECT 	 TOP 1
              		 Job.ID                AS ID,
              		 Job.fDesc             AS Name,
              		 Job.fDate             AS Date,
	                 Job.BHour             AS Budgeted_Hours,
     			         JobType.Type          AS Type,
					         Job.Remarks 		       AS Remarks,
                   Job.Status            AS Status,
                   Emp.fFirst            AS Employee_First_Name,
                   Emp.Last              AS Employee_Last_Name,
    		           Loc.Loc               AS Location_ID,
              		 Loc.Tag               AS Location_Name,
              		 Loc.Address           AS Location_Street,
              		 Loc.City              AS Location_City,
              		 Loc.State             AS Location_State,
              		 Loc.Zip               AS Location_Zip,
                   Loc.Latt              AS Location_Latitude,
                   Loc.fLong             AS Location_Longitude,
              		 Route.ID              AS Route_ID,
                   Route.Name            AS Route_Name,
                   Zone.ID               AS Division_ID,
              		 Zone.Name             AS Division_Name,
              		 Owner.ID              AS Customer_ID,
              		 OwnerRol.Name     	   AS Customer_Name,
             	 		 Owner.Status       	 AS Customer_Status,
              		 Owner.Elevs    		   AS Customer_Elevators,
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
              		 Emp.fFirst            AS Employee_First_Name,
              		 Emp.Last              AS Employee_Last_Name,
              		 Route.ID              AS Route_ID,
      						 Violation.ID          AS Violation_ID,
      						 Violation.fdate       AS Violation_Date,
      						 Violation.Status      AS Violation_Status,
      						 Violation.Remarks     AS Violation_Remarks
          	FROM 	 Job
              		 LEFT JOIN Loc           	ON Job.Loc      = Loc.Loc
              		 LEFT JOIN Zone          	ON Loc.Zone     = Zone.ID
              		 LEFT JOIN JobType       	ON Job.Type     = JobType.ID
              		 LEFT JOIN OwnerWithRol  	ON Job.Owner    = OwnerWithRol.ID
              		 LEFT JOIN Emp           	ON Emp.fWork    = Route.Mech
        					 LEFT JOIN Violation     	ON Job.ID       = Violation.Job
        					 LEFT JOIN Owner 			ON Owner.ID 	= Loc.Owner
        					 LEFT JOIN Rol AS OwnerRol 	ON OwnerRol.ID  = Owner.Rol
          	WHERE  Job.ID = ?
                    OR Job.fDesc = ?;',
         array(
         	$ID,
          $Name
         )
     );
     $Job =   (       empty( $ID )
                        &&    !empty( $Name )
                        &&    !$result
                      ) || (  empty( $ID )
                        &&    empty( $Name )
                      )  ? array(
          'ID' => null,
          'Name' => null,
          'Date' => null,
          'Type' => null,
          'Remarks' => null,
          'Status' => null,
          'Location_ID' => null,
          'Location_Name' => null,
          'Location_Street' => null,
          'Location_City' => null,
          'Location_State' => null,
          'Location_Zip' => null,
          'Location_Latitude' => null,
          'Location_Longitude' => null,
          'Customer_ID' => null,
          'Customer_Name' => null,
          'Unit_ID' => null,
          'Unit_Name' => null,
          'Violation_ID' => null,
          'Violation_Name' => null,
          'Division_ID' => null,
          'Division_Name' => null
        ) : sqlsrv_fetch_array($result);

        if( isset( $_POST ) && count( $_POST ) > 0 ){
          $Route[ 'Customer_Name' ] = isset( $_POST[ 'Customer' ] ) ? $_POST[ 'Customer' ] : $Route[ 'Customer_Name' ];
          $Route[ 'Location_Name' ] = isset( $_POST[ 'Location' ] ) ? $_POST[ 'Location' ] : $Route[ 'Location_Name' ];
          $Route[ 'Unit_Name' ] = isset( $_POST[ 'Unit' ] ) ? $_POST[ 'Unit' ] : $Route[ 'Unit_Name' ];
          $Route[ 'Name' ] = isset( $_POST[ 'Name' ] ) ? $_POST[ 'Name' ] : $Route[ 'Name' ];
          $Route[ 'Date' ] = isset( $_POST[ 'Date' ] ) ? $_POST[ 'Date' ] : $Route[ 'Date' ];
          $Route[ 'Type' ] = isset( $_POST[ 'Type' ] ) ? $_POST[ 'Type' ] : $Route[ 'Type' ];
          $Route[ 'Remarks' ] = isset( $_POST[ 'Remarks' ] ) ? $_POST[ 'Remarks' ] : $Route[ 'Remarks' ];
          if( empty( $_POST[ 'ID' ] ) ){
            $result = \singleton\database::getInstance( )->query(
              null,
              " DECLARE @MAXID INT;
                DECLARE @Customer INT;
                DECLARE @Location INT;
                DECLARE @Unit INT;
                DECLARE @Type INT;
                SET @MAXID = CASE WHEN ( SELECT Max( ID ) FROM Job ) IS NULL THEN 0 ELSE ( SELECT Max( ID ) FROM Job ) END ;
                SET @Customer = ( SELECT Owner.ID FROM Owner LEFT JOIN Rol ON Owner.Rol = Rol.ID WHERE Rol.Name = ? );
                SET @Location = ( SELECT Loc.Tag FROM Loc WHERE Loc.Tag = ? AND Loc.Owner = @Customer );
                SET @Unit = ( SELECT Elev.State + ' ' + Elev.Unit FROM Elev WHERE Elev.Owner = @Customer AND Elev.Loc = @Location );
                SET @Type = ( SELECT JobType.ID FROM JobType WHERE JobType.Type = ? );
                INSERT INTO Job(
                  ID,
                  Owner,
                  Loc,
                  Elev,
                  Type,
                  fDesc,
                  Status,
                  Remarks," . /*,
                  PO,
                  Rev,
                  Mat,
                  Labor,
                  Cost,
                  Profit,
                  Ratio,
                  Reg,
                  OT,
                  DT,
                  TT,
                  Hour,
                  BRev,
                  BLabor,
                  BCost,
                  BProfit,
                  BRatio,
                  BHour,
                  Template,
                  fDate,
                  Comm,
                  WageC,
                  NT,
                  Post,
                  EN,
                  Certified,
                  Apprentice,
                  UseDed,
                  BillRate,
                  Markup,
                  PType,
                  Charge,
                  Amount,
                  GL,
                  GLRev,
                  GandA,
                  OHLabor,
                  LastOH,
                  etc,
                  ETCModifier,
                  FP,
                  fGroup,
                  CType,
                  Elevs,
                  RateTravel,
                  RateOT,
                  RateNT,
                  RateDT,
                  RateMileage,
                  CloseDate,
                  SPHandle,
                  SRemarks,
                  LCode,
                  CreditCard,
                  NCSLock,
                  Source,
                  Audit,
                  AuditBy,
                  AuditDate,
                  Reopen,
                  fInt,
                  NCSClose,
                  Comments,
                  Level,
                  TechAlert,
                  EstDate,
                  DueDate,
                  Tech,
                  TechOrRoute,
                  TFMID,
                  TFMSource,*/ "
                  Custom1, Custom2, Custom3, Custom4, Custom5,
                  Custom6, Custom7, Custom8, Custom9, Custom10,
                  Custom11, Custom12, Custom13, Custom14, Custom15,
                  Custom16, Custom17, Custom18, Custom19, Custom20" . /*
                  TFMCustom1, TFMCustom2, TFMCustom3, TFMCustom4, TFMCustom5*/ "
                  , Rev, Mat, Labor, Cost, Profit, Ratio, Reg, OT, DT, TT, Hour, BRev, BMat, BLabor, BCost, BProfit, BRatio, BHour, NT
                )
                VALUES ( @MAXID + 1, @Customer, @Location, @Unit, @Type, " . implode( ',', array_fill( 0, 23 /*99*/, '?' ) ) . ", " . implode( ',', array_fill( 0, 19, '0' ) ) . ");
                SELECT @MAXID + 1;",
              array(
                $Job[ 'Customer_Name' ],
                $Job[ 'Location_Name' ],
                $Job[ 'Unit_Name' ],
                $Job[ 'Type' ],
                $Job[ 'Name' ],
                $Job[ 'Status' ],
                $Job[ 'Remarks' ],
                /*null,//PO
                0,//Rev
                0,//Mat
                0,//Labor
                0,//Cost
                0,//Profit
                0,//Ratio
                0,//Reg
                0,//OT
                0,//DT
                0,//TT
                0,//Hour
                0,//BRev
                0,//BMat
                0,//BLabor
                0,//BCost
                0,//BProfit
                0,//BRatio
                0,//Template
                null,//fDate
                null,//Comm
                null,//WageC
                null,//NT
                0,//Post
                null,//EN
                null,//Cerfied
                null,//Apprentice
                null,//UseCat
                null,//UseDed
                null,//BillRate
                null,//Markup
                null,//PType
                null,//Charge
                null,//Amount
                null,//GL
                null,//GLRev 
                null,//GandA
                null,//OHLabor
                null,//LastOH
                null,//etc
                null,//ETCModifier
                null,//FP
                null,//fGroup
                null,//CType
                null,//Elevs
                null,//RateTravel
                null,//RateOT
                null,//RateNT
                null,//RateDT
                null,//RateMileage
                null,//CloseDate
                null,//SPHandle
                null,//SRemarks
                null,//LCode
                null,//CreditCard
                null,//NCSLock
                null,//Source
                null,//Audit
                null,//AuditBy
                null,//ReOpen
                null,//fInt
                null,//NCSClose
                null,//Comments
                null,//Level
                null,//TechAlert
                null,//EstDate
                null,//DueDate
                null,//TFMID
                null,//TFMSource
                null,
                null,
                null,*/
                null, null, null, null, null, 
                null, null, null, null, null, 
                null, null, null, null, null, 
                null, null, null, null, null/*, 
                ' ', ' ', ' ', ' ', ' '*/
              )
            );
            var_dump( sqlsrv_errors( ) ) ;
            sqlsrv_next_result( $result );
            $Job[ 'ID' ] = sqlsrv_fetch_array( $result )[ 0 ];

            //header( 'Location: job.php?ID=' . $Job[ 'ID' ] );
            exit;
          } else {
            \singleton\database::getInstance( )->query(
              null,
              " UPDATE  Job
                SET     Job.Name = ?,
                WHERE   Route.ID = ?;",
              array(
                $Job[ 'Name' ],
                $Job[ 'ID' ]
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
  ?>
  <?php require( bin_meta . 'index.php' );?>
    <?php require( bin_css  . 'index.php' );?>
    <?php require( bin_js   . 'index.php' );?>
    <script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=AIzaSyCNrTryEaTEDRz-XDSg890ajL_JRPnLgzc"></script>
</head>
<body>
  <div id="wrapper">
    <?php require(PROJECT_ROOT.'php/element/navigation.php');?>
    <div id="page-wrapper" class='content' style='height:100%;overflow-y:scroll;'>
      <div class='card card-primary'>
        <div class='card-heading'>
          <div class='row g-0 px-3 py-2'>
            <div class='col-6'>
              <h5><?php \singleton\fontawesome::getInstance( )->Job( 1 );?><a href='jobs.php?<?php
                echo http_build_query( is_array( $_SESSION[ 'Tables' ][ 'Jobs' ][ 0 ] ) ? $_SESSION[ 'Tables' ][ 'Jobs' ][ 0 ] : array( ) );
              ?>'>Job</a>: <span><?php
                echo is_null( $Job[ 'ID' ] )
                  ? 'New'
                  : $Job[ 'Name' ];
              ?></span></h5>
            </div>
            <div class='col-2'></div>
            <div class='col-2'>
              <div class='row g-0'>
                <div class='col-4'>
                  <button
                    class='form-control rounded'
                    onClick="document.location.href='employee.php';"
                  >Create</button>
                </div>
                <div class='col-4'>
                  <button
                    class='form-control rounded'
                    onClick="document.location.href='job.php?ID=<?php echo $Job[ 'ID' ];?>';"
                  >Refresh</button>
                </div>
              </div>
            </div>
            <div class='col-2'>
              <div class='row g-0'>
                <div class='col-4'><button class='form-control rounded' onClick="document.location.href='job.php?ID=<?php echo !is_null( $Job[ 'ID' ] ) ? array_keys( $_SESSION[ 'Tables' ][ 'Jobs' ], true )[ array_search( $Job[ 'ID' ], array_keys( $_SESSION[ 'Tables' ][ 'Jobs' ], true ) ) - 1 ] : null;?>';">Previous</button></div>
                <div class='col-4'><button class='form-control rounded' onClick="document.location.href='jobs.php?<?php echo http_build_query( is_array( $_SESSION[ 'Tables' ][ 'Jobs' ][ 0 ] ) ? $_SESSION[ 'Tables' ][ 'Jobs' ][ 0 ] : array( ) );?>';">Table</button></div>
                <div class='col-4'><button class='form-control rounded' onClick="document.location.href='job.php?ID=<?php echo !is_null( $Job[ 'ID' ] )? array_keys( $_SESSION[ 'Tables' ][ 'Jobs' ], true )[ array_search( $Job[ 'ID' ], array_keys( $_SESSION[ 'Tables' ][ 'Jobs' ], true ) ) + 1 ] : null;?>';">Next</button></div>
              </div>
            </div>
          </div>
        </div>
        <div class='card-body bg-dark text-white'>
          <div class='card-columns'>
            <?php if( !in_array( $Job[ 'Location_Latitude' ], array( null, 0 ) ) && !in_array( $Job['Location_Longitude' ], array( null, 0 ) ) ){
              ?><div class='card card-primary my-3'>
                <div class='card-heading position-relative' style='z-index:1;'>
                  <div class='row g-0 px-3 py-2'>
                    <div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Info( 1 );?><span>Map</span></h5></div>
                    <div class='col-2'>&nbsp;</div>
                  </div>
                </div>
                <div id='customer_map' class='card-body p-0 bg-dark position-relative overflow-hidden' style='width:100%;height:350px;z-index:0;<?php echo isset( $_SESSION[ 'Cards' ][ 'Map' ] ) && $_SESSION[ 'Cards' ][ 'Map' ] == 0 ? 'display:none;' : null;?>'></div>
                <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB05GymhObM_JJaRCC3F4WeFn3KxIOdwEU"></script>
                  <script type="text/javascript">
                            var map;
                            function initialize() {
                                 map = new google.maps.Map(
                                    document.getElementById( 'customer_map' ),
                                    {
                                      zoom: 10,
                                      center: new google.maps.LatLng( <?php echo $Job[ 'Latitude' ];?>, <?php echo $Job[ 'Longitude' ];?> ),
                                      mapTypeId: google.maps.MapTypeId.ROADMAP
                                    }
                                );
                                var markers = [];
                                markers[0] = new google.maps.Marker({
                                    position: {
                                        lat:<?php echo $Job['Location_Latitude'];?>,
                                        lng:<?php echo $Job['Location_Longitude'];?>
                                    },
                                    map: map,
                                    title: '<?php echo $Job[ 'Name' ];?>'
                                });
                            }
                            $(document).ready(function(){ initialize(); });
                        </script>
              </div><?php
            }?>
          <div class='card card-primary my-3'><form action='job.php?ID=<?php echo $Job[ 'ID' ];?>' method='POST'>
            <div class='card-heading'>
              <div class='row g-0 px-3 py-2'>
                <div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Info( 1 );?><span>Infomation</span></h5></div>
                <div class='col-2'>&nbsp;</div>
              </div>
            </div>
            <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Infomation' ] ) && $_SESSION[ 'Cards' ][ 'Infomation' ] == 0 ? "style='display:none;'" : null;?>>
              <input type='hidden' name='ID' value='<?php echo $Job[ 'ID' ];?>' />
              <div class='row g-0'>
                <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Blank(1);?>Name:</div>
                <div class='col-8'><input type='text' class='form-control edit animation-focus' name='Name' value='<?php echo $Job['Name'];?>' /></div>
              </div>
              <div class='row g-0'>
                <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Blank(1);?>Date:</div>
                <div class='col-8'><input type='text' class='form-control edit animation-focus' name='Date' value='<?php echo $Job['Date'];?>' /></div>
              </div>
              <div class='row g-0'>
                <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Blank(1);?>Type:</div>
                <div class='col-8'><select class='form-control' name='Type'>
                  <option value=''>Select</option>
                  <?php 
                    $result = \singleton\database::getInstance( )->query(
                      null,
                      " SELECT  Job_Type.ID   AS ID,
                                Job_Type.Type AS Type 
                        FROM    JobType AS Job_Type;"
                    );
                    if( $result ){while ( $row = sqlsrv_fetch_array( $result ) ){
                      ?><option value='<?php echo $row[ 'ID' ];?>'><?php echo $row[ 'Name' ];?></option><?php
                    }}
                  ?>
                </select></div>
              </div>
              <div class='row g-0'>
                  <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Customer(1);?> Customer:</div>
                  <div class='col-6'>
                    <input type='text' autocomplete='off' class='form-control edit' name='Customer' value='<?php echo $Job[ 'Customer_Name' ];?>' />
                    <script>
                      $( 'input[name="Customer"]' )
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
                                          search :  $('input:visible[name="Customer"]').val( )
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
                                  $( 'input[name="Customer"]').val( value );
                                  $( 'input[name="Customer"]').closest( 'form' ).submit( );
                              }
                          }
                      );
                    </script>
                  </div>
                  <div class='col-2'><button class='h-100 w-100' type='button' <?php 
                    if( in_array( $Job[ 'Customer_ID' ], array( null, 0, '', ' ') ) ){
                      echo "onClick=\"document.location.href='customers.php';\"";
                    } else {
                      echo "onClick=\"document.location.href='customer.php?ID=" . $Job[ 'Customer_ID' ] . "';\"";
                    }
                  ?>><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
              </div>
              <div class='row g-0'>
                  <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Location(1);?> Location:</div>
                  <div class='col-6'>
                    <input type='text' autocomplete='off' class='form-control edit' name='Location' value='<?php echo $Job[ 'Location_Name' ];?>' />
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
                                          search :  $('input:visible[name="Location"]').val( ),
                                          Customer : $('input:visible[name="Customer"]').val( )
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
                    if( in_array( $Job[ 'Location_ID' ], array( null, 0, '', ' ') ) ){
                      echo "onClick=\"document.location.href='locations.php';\"";
                    } else {
                      echo "onClick=\"document.location.href='location.php?ID=" . $Job[ 'Location_ID' ] . "';\"";
                    }
                  ?>><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
              </div>
              <div class='row g-0'>
                  <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Unit(1);?> Unit:</div>
                  <div class='col-6'>
                    <input type='text' autocomplete='off' class='form-control edit' name='Unit' value='<?php echo $Job[ 'Unit_Name' ];?>' />
                    <script>
                      $( 'input[name="Unit"]' )
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
                    if( in_array( $Job[ 'Location_ID' ], array( null, 0, '', ' ') ) ){
                      echo "onClick=\"document.location.href='units.php';\"";
                    } else {
                      echo "onClick=\"document.location.href='unit.php?ID=" . $Job[ 'Unit_ID' ] . "';\"";
                    }
                  ?>><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
              </div>
              <div class='row g-0'>
                <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Blank(1);?>Remarks:</div>
                <div class='col-8'>&nbsp;</div>
              </div>
              <div class='row g-0'>
                <div class='col-12'><textarea class='form-control edit animation-focus' name='Remarks' rows='10'><?php echo $Job['Remarks'];?></textarea></div>
              </div>
            </div>
          </form></div>
          <div class='card card-primary my-3'>
            <div class='card-heading'>
              <div class='row g-0 px-3 py-2'>
                <div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Location( 1 );?><span>Location</span></h5></div>
                <div class='col-2'>&nbsp;</div>
              </div>
            </div>
            <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Location' ] ) && $_SESSION[ 'Cards' ][ 'Location' ] == 0 ? "style='display:none;'" : null;?>>
              <div class='row g-0'>
                <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Address(1);?> Address:</div>
                <div class='col-6'></div>
                <div class='col-2'><button class='h-100 w-100' type='button' onClick="document.location.href='map.php?Employee=<?php echo $Job[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
              </div>
              <div class='row g-0'>
                <div class='col-1'>&nbsp;</div>
                <div class='col-3 border-bottom border-white my-auto'>Street:</div>
                <div class='col-8'><input readonly type='text' class='form-control' name='Location_Street' value='<?php echo $Job['Location_Street'];?>' /></div>
              </div>
              <div class='row g-0'>
                <div class='col-1'>&nbsp;</div>
                <div class='col-3 border-bottom border-white my-auto'>City:</div>
                <div class='col-8'><input readonly type='text' class='form-control' name='Location_City' value='<?php echo $Job['Location_City'];?>' /></div>
              </div>
              <div class='row g-0'>
                <div class='col-1'>&nbsp;</div>
                <div class='col-3 border-bottom border-white my-auto'>State:</div>
                <div class='col-8'><select readonly class='form-control' name='Location_State'>
                  <option value=''>Select</option>
                  <option <?php echo $Job[ 'Location_State' ] == 'AL' ? 'selected' : null;?> value='AL'>Alabama</option>
                  <option <?php echo $Job[ 'Location_State' ] == 'AK' ? 'selected' : null;?> value='AK'>Alaska</option>
                  <option <?php echo $Job[ 'Location_State' ] == 'AZ' ? 'selected' : null;?> value='AZ'>Arizona</option>
                  <option <?php echo $Job[ 'Location_State' ] == 'AR' ? 'selected' : null;?> value='AR'>Arkansas</option>
                  <option <?php echo $Job[ 'Location_State' ] == 'CA' ? 'selected' : null;?> value='CA'>California</option>
                  <option <?php echo $Job[ 'Location_State' ] == 'CO' ? 'selected' : null;?> value='CO'>Colorado</option>
                  <option <?php echo $Job[ 'Location_State' ] == 'CT' ? 'selected' : null;?> value='CT'>Connecticut</option>
                  <option <?php echo $Job[ 'Location_State' ] == 'DE' ? 'selected' : null;?> value='DE'>Delaware</option>
                  <option <?php echo $Job[ 'Location_State' ] == 'DC' ? 'selected' : null;?> value='DC'>District Of Columbia</option>
                  <option <?php echo $Job[ 'Location_State' ] == 'FL' ? 'selected' : null;?> value='FL'>Florida</option>
                  <option <?php echo $Job[ 'Location_State' ] == 'GA' ? 'selected' : null;?> value='GA'>Georgia</option>
                  <option <?php echo $Job[ 'Location_State' ] == 'HI' ? 'selected' : null;?> value='HI'>Hawaii</option>
                  <option <?php echo $Job[ 'Location_State' ] == 'ID' ? 'selected' : null;?> value='ID'>Idaho</option>
                  <option <?php echo $Job[ 'Location_State' ] == 'IL' ? 'selected' : null;?> value='IL'>Illinois</option>
                  <option <?php echo $Job[ 'Location_State' ] == 'IN' ? 'selected' : null;?> value='IN'>Indiana</option>
                  <option <?php echo $Job[ 'Location_State' ] == 'IA' ? 'selected' : null;?> value='IA'>Iowa</option>
                  <option <?php echo $Job[ 'Location_State' ] == 'KS' ? 'selected' : null;?> value='KS'>Kansas</option>
                  <option <?php echo $Job[ 'Location_State' ] == 'KY' ? 'selected' : null;?> value='KY'>Kentucky</option>
                  <option <?php echo $Job[ 'Location_State' ] == 'LA' ? 'selected' : null;?> value='LA'>Louisiana</option>
                  <option <?php echo $Job[ 'Location_State' ] == 'ME' ? 'selected' : null;?> value='ME'>Maine</option>
                  <option <?php echo $Job[ 'Location_State' ] == 'MD' ? 'selected' : null;?> value='MD'>Maryland</option>
                  <option <?php echo $Job[ 'Location_State' ] == 'MA' ? 'selected' : null;?> value='MA'>Massachusetts</option>
                  <option <?php echo $Job[ 'Location_State' ] == 'MI' ? 'selected' : null;?> value='MI'>Michigan</option>
                  <option <?php echo $Job[ 'Location_State' ] == 'MN' ? 'selected' : null;?> value='MN'>Minnesota</option>
                  <option <?php echo $Job[ 'Location_State' ] == 'MS' ? 'selected' : null;?> value='MS'>Mississippi</option>
                  <option <?php echo $Job[ 'Location_State' ] == 'MO' ? 'selected' : null;?> value='MO'>Missouri</option>
                  <option <?php echo $Job[ 'Location_State' ] == 'MT' ? 'selected' : null;?> value='MT'>Montana</option>
                  <option <?php echo $Job[ 'Location_State' ] == 'NE' ? 'selected' : null;?> value='NE'>Nebraska</option>
                  <option <?php echo $Job[ 'Location_State' ] == 'NV' ? 'selected' : null;?> value='NV'>Nevada</option>
                  <option <?php echo $Job[ 'Location_State' ] == 'NH' ? 'selected' : null;?> value='NH'>New Hampshire</option>
                  <option <?php echo $Job[ 'Location_State' ] == 'NJ' ? 'selected' : null;?> value='NJ'>New Jersey</option>
                  <option <?php echo $Job[ 'Location_State' ] == 'NM' ? 'selected' : null;?> value='NM'>New Mexico</option>
                  <option <?php echo $Job[ 'Location_State' ] == 'NY' ? 'selected' : null;?> value='NY'>New York</option>
                  <option <?php echo $Job[ 'Location_State' ] == 'NC' ? 'selected' : null;?> value='NC'>North Carolina</option>
                  <option <?php echo $Job[ 'Location_State' ] == 'ND' ? 'selected' : null;?> value='ND'>North Dakota</option>
                  <option <?php echo $Job[ 'Location_State' ] == 'OH' ? 'selected' : null;?> value='OH'>Ohio</option>
                  <option <?php echo $Job[ 'Location_State' ] == 'OK' ? 'selected' : null;?> value='OK'>Oklahoma</option>
                  <option <?php echo $Job[ 'Location_State' ] == 'OR' ? 'selected' : null;?> value='OR'>Oregon</option>
                  <option <?php echo $Job[ 'Location_State' ] == 'PA' ? 'selected' : null;?> value='PA'>Pennsylvania</option>
                  <option <?php echo $Job[ 'Location_State' ] == 'RI' ? 'selected' : null;?> value='RI'>Rhode Island</option>
                  <option <?php echo $Job[ 'Location_State' ] == 'SC' ? 'selected' : null;?> value='SC'>South Carolina</option>
                  <option <?php echo $Job[ 'Location_State' ] == 'SD' ? 'selected' : null;?> value='SD'>South Dakota</option>
                  <option <?php echo $Job[ 'Location_State' ] == 'TN' ? 'selected' : null;?> value='TN'>Tennessee</option>
                  <option <?php echo $Job[ 'Location_State' ] == 'TX' ? 'selected' : null;?> value='TX'>Texas</option>
                  <option <?php echo $Job[ 'Location_State' ] == 'UT' ? 'selected' : null;?> value='UT'>Utah</option>
                  <option <?php echo $Job[ 'Location_State' ] == 'VT' ? 'selected' : null;?> value='VT'>Vermont</option>
                  <option <?php echo $Job[ 'Location_State' ] == 'VA' ? 'selected' : null;?> value='VA'>Virginia</option>
                  <option <?php echo $Job[ 'Location_State' ] == 'WA' ? 'selected' : null;?> value='WA'>Washington</option>
                  <option <?php echo $Job[ 'Location_State' ] == 'WV' ? 'selected' : null;?> value='WV'>West Virginia</option>
                  <option <?php echo $Job[ 'Location_State' ] == 'WI' ? 'selected' : null;?> value='WI'>Wisconsin</option>
                  <option <?php echo $Job[ 'Location_State' ] == 'WY' ? 'selected' : null;?> value='WY'>Wyoming</option>
                </select></div>
              </div>
              <div class='row g-0'>
                <div class='col-1'>&nbsp;</div>
                <div class='col-3 border-bottom border-white my-auto'>Zip:</div>
                <div class='col-8'><input readonly type='text' class='form-control' name='Location_Zip' value='<?php echo $Job['Location_Zip'];?>' /></div>
              </div>
              <div class='row g-0'>
                <div class='col-1'>&nbsp;</div>
                <div class='col-3 border-bottom border-white my-auto'>Latitude:</div>
                <div class='col-8'><input readonly type='text' class='form-control' /></div>
              </div>
              <div class='row g-0'>
                <div class='col-1'>&nbsp;</div>
                <div class='col-3 border-bottom border-white my-auto'>Longitude:</div>
                <div class='col-8'><input readonly type='text' class='form-control' /></div>
              </div>
              <div class='card-footer'>
                  <div class='row'>
                      <div class='col-12'><button class='form-control' type='submit'>Save</button></div>
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
} else {require('404.html');}?>
