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
        ||  !isset( $Privileges[ 'Requisition' ] )
        || 	!check( privilege_read, level_group, $Privileges[ 'Requisition' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
        \singleton\database::getInstance( )->query(
          null,
          " INSERT INTO Activity([User], [Date], [Page] )
            VALUES( ?, ?, ? );",
          array(
            $_SESSION[ 'Connection' ][ 'User' ],
            date('Y-m-d H:i:s'),
            'requisition.php'
        )
      );
      $ID = isset( $_GET[ 'ID' ] )
        ? $_GET[ 'ID' ]
        : (
          isset( $_POST[ 'ID' ] )
            ? $_POST[ 'ID' ]
            : null
        );
      $result = \singleton\database::getInstance( )->query(
        null,
        " SELECT  Requisition.*,
                  DropOff.Tag       AS DropOff_Tag,
                  Unit.State        AS Unit_State,
                  Unit.Unit         AS Unit_Label,
                  Job.ID            AS Job_ID,
                  Job.fDesc         AS Job_Name,
                  Job_Type.Type     AS Job_Type,
                  Location.Loc      AS Location_ID,
                  Location.Tag      AS Location_Name,
                  Location.Address  AS Location_Street,
                  Location.City     AS Location_City,
                  Location.State    AS Location_State,
                  Location.Zip      AS Location_Zip,
                  DropOff.Address   AS DropOff_Street,
                  DropOff.City      AS DropOff_City,
                  DropOff.State     AS DropOff_State,
                  DropOff.Zip       AS DropOff_Zip,
                  Employee.fFirst + ' ' + Employee.Last AS User_Name
          FROM    Requisition
                  LEFT JOIN Loc     AS Location   ON Requisition.Location = Location.Loc
                  LEFT JOIN Loc     AS DropOff    ON Requisition.DropOff  = DropOff.Loc
                  LEFT JOIN Elev    AS Unit       ON Requisition.Unit     = Unit.ID
                  LEFT JOIN Job     AS Job        ON Requisition.Job      = Job.ID
                  LEFT JOIN JobType AS Job_Type   ON Job_Type.ID          = Job.Type
                  LEFT JOIN Emp     AS Employee   ON Employee.ID          = Requisition.[User]
          WHERE   Requisition.ID = ?;",
        array(
          $ID
        )
      );
      $Requisition = sqlsrv_fetch_array( $result );
      $result = \singleton\database::getInstance( )->query(
        null,
        " SELECT  *
          FROM    Requisition_Item
          WHERE   Requisition_Item.Requisition = ?;",
        array(
          $ID
        )
      );
      $Requisition_Items = array( );
      if( $result ){while( $row = sqlsrv_fetch_array( $result ) ){ $Requisition_Items[ ] = $row; } }
?><!DOCTYPE html>
<html lang="en">
<head>
  <title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
  <?php  $_GET[ 'Bootstrap' ] = '5.1';?>
  <?php  $_GET[ 'Entity_CSS' ] = 1;?>
  <?php	require( bin_meta . 'index.php');?>
  <?php	require( bin_css  . 'index.php');?>
  <?php  require( bin_js   . 'index.php');?>
	<link href="css/ufd-base.css" rel="stylesheet" type="text/css" />
	<link href="css/plain.css" rel="stylesheet" type="text/css" />
  <style>
  .popup {
    z-index:999999999;
    position:absolute;
    margin-top:50px;
    top:0;
    left:0;
    background-color:#1d1d1d;
    height:100%;
    width:100%;
  }
  @media print
  {
      .no-print, .no-print *
      {
          display: none !important;
      }
      .print {
          display: block !important;
      }
  }
  .print {display:none;}
  .noprint {display:block;}
  </style>
</head>
<body onload='finishLoadingPage();'>
    <div id='wrapper'>
      <?php require(PROJECT_ROOT.'php/element/navigation.php');?>
      <div id="page-wrapper" class='content'>
        <div class='card card-primary'><form action='customer.php?ID=<?php echo $Requisition[ 'ID' ];?>' method='POST'>
          <input type='hidden' name='ID' value='<?php echo $Requisition[ 'ID' ];?>' />
          <div class='card-heading'>
            <div class='row g-0 px-3 py-2'>
              <div class='col-12 col-lg-6'>
                  <h5><?php \singleton\fontawesome::getInstance( )->Requisition( 1 );?><a href='requisitions.php?<?php
                    echo http_build_query( is_array( $_SESSION[ 'Tables' ][ 'Requisitions' ][ 0 ] ) ? $_SESSION[ 'Tables' ][ 'Requisitions' ][ 0 ] : array( ) );
                  ?>'>Requisitions</a>: <span><?php
                    echo is_null( $Requisition[ 'ID' ] )
                        ? 'New'
                        : '#' . $Requisition[ 'ID' ];
                  ?></span></h5>
              </div>
              <div class='col-6 col-lg-3'>
                  <div class='row g-0'>
                    <div class='col-4'>
                      <button
                          class='form-control rounded'
                          onClick="document.location.href='requisition.php';"
                        ><?php \singleton\fontawesome::getInstance( 1 )->Save( 1 );?><span class='desktop'> Save</span></button>
                    </div>
                    <div class='col-4'>
                        <button
                          class='form-control rounded'
                          onClick="document.location.href='requisition.php?ID=<?php echo $User[ 'ID' ];?>';"
                        ><?php \singleton\fontawesome::getInstance( 1 )->Refresh( 1 );?><span class='desktop'> Refresh</span></button>
                    </div>
                    <div class='col-4'>
                        <button
                          class='form-control rounded'
                          onClick="document.location.href='requisition.php';"
                        ><?php \singleton\fontawesome::getInstance( 1 )->Add( 1 );?><span class='desktop'> New</span></button>
                    </div>
                </div>
              </div>
              <div class='col-6 col-lg-3'>
                  <div class='row g-0'>
                    <div class='col-4'><button class='form-control rounded' onClick="document.location.href='requisition.php?ID=<?php echo !is_null( $User[ 'ID' ] ) ? array_keys( $_SESSION[ 'Tables' ][ 'Users' ], true )[ array_search( $User[ 'ID' ], array_keys( $_SESSION[ 'Tables' ][ 'Users' ], true ) ) - 1 ] : null;?>';"><?php \singleton\fontawesome::getInstance( 1 )->Previous( 1 );?><span class='desktop'> Previous</span></button></div>
                    <div class='col-4'><button class='form-control rounded' onClick="document.location.href='requisitons.php?<?php echo http_build_query( is_array( $_SESSION[ 'Tables' ][ 'Users' ][ 0 ] ) ? $_SESSION[ 'Tables' ][ 'Users' ][ 0 ] : array( ) );?>';"><?php \singleton\fontawesome::getInstance( 1 )->Table( 1 );?><span class='desktop'> Table</span></button></div>
                    <div class='col-4'><button class='form-control rounded' onClick="document.location.href='requisitions.php?ID=<?php echo !is_null( $User[ 'ID' ] )? array_keys( $_SESSION[ 'Tables' ][ 'Users' ], true )[ array_search( $User[ 'ID' ], array_keys( $_SESSION[ 'Tables' ][ 'Users' ], true ) ) + 1 ] : null;?>';"><?php \singleton\fontawesome::getInstance( 1 )->Next( 1 );?><span class='desktop'> Next</span></button></div>
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
                <div class="row g-0">
      						<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->User(1);?> User:</div>
      						<div class='col-8'><input class='form-control edit' type='text' name='Employee' size='15' value='<?php echo $Requisition['User_Name'];?>' /></div>
      					</div>
      					<div class="row g-0">
      						<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Calendar(1);?> Date:</div>
      						<div class='col-8'><input class='form-control edit' type='text' name='Date' size='15' value='<?php echo date("m/d/Y",strtotime($Requisition['Date']));?>' /></div>
      					</div>
      					<div class="row g-0">
      						<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Required</div>
      						<div class='col-8'><input class='form-control edit' type='text' name='Required' size='15' value='<?php echo date("m/d/Y",strtotime($Requisition['Required']));?>' /></div>
      					</div>
                <div class='row g-0'>
                  <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Location(1);?> Location:</div>
                  <div class='col-6'>
                    <input type='text' autocomplete='off' class='form-control edit' name='Location' value='<?php echo $Requisition[ 'Location_Name' ];?>' />
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
                    if( in_array( $Requisition[ 'Location_ID' ], array( null, 0, '', ' ') ) ){
                      echo "onClick=\"document.location.href='locations.php';\"";
                    } else {
                      echo "onClick=\"document.location.href='location.php?ID=" . $Requisition[ 'Location_ID' ] . "';\"";
                    }
                  ?>><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                </div>
                <div class='row g-0'>
                  <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Location(1);?> Dropoff:</div>
                  <div class='col-6'>
                    <input type='text' autocomplete='off' class='form-control edit' name='Dropoff' value='<?php echo $Requisition[ 'Dropoff_Name' ];?>' />
                    <script>
                      $( 'input[name="Dropoff"]' )
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
                                          search :  $('input:visible[name="Dropoff"]').val( )
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
                                  $( 'input[name="Dropoff"]').val( value );
                                  $( 'input[name="Dropoff"]').closest( 'form' ).submit( );
                              }
                          }
                      );
                    </script>
                  </div>
                  <div class='col-2'><button class='h-100 w-100' type='button' <?php
                    if( in_array( $Requisition[ 'Dropoff_ID' ], array( null, 0, '', ' ') ) ){
                      echo "onClick=\"document.location.href='locations.php';\"";
                    } else {
                      echo "onClick=\"document.location.href='location.php?ID=" . $Requisition[ 'Dropoff_ID' ] . "';\"";
                    }
                  ?>><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                </div>
                <div class='row g-0'>
                  <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Unit(1);?> Unit:</div>
                  <div class='col-6'>
                    <input type='text' autocomplete='off' class='form-control edit' name='Unit' value='<?php echo $Requisition[ 'Unit_Name' ];?>' />
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
                    if( in_array( $Requisition[ 'Unit_ID' ], array( null, 0, '', ' ') ) ){
                      echo "onClick=\"document.location.href='units.php';\"";
                    } else {
                      echo "onClick=\"document.location.href='unit.php?ID=" . $Requisition[ 'Unit_ID' ] . "';\"";
                    }
                  ?>><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                </div>
                <div class='row g-0'>
                  <label class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Job(1);?> Job:</label>
                  <div class='col-6'>
                    <input type='text' autocomplete='off' class='form-control edit' name='Job' value='<?php echo $Requisition[ 'Job_Name' ];?>' />
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
                                search :  $('input:visible[name="Job"]').val( ),
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
                            $( 'input[name="Job"]').val( value );
                            $( 'input[name="Job"]').closest( 'form' ).submit( );
                          }
                        }
                      );
                    </script>
                  </div>
                  <div class='col-2'><button class='h-100 w-100' type='button' <?php
                    if( in_array( $Requisition[ 'Job_ID' ], array( null, 0, '', ' ') ) ){
                      echo "onClick=\"document.location.href='jobs.php';\"";
                    } else {
                      echo "onClick=\"document.location.href='job.php?ID=" . $Requisition[ 'Job_ID' ] . "';\"";
                    }
                  ?>><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                </div>
      					<div class='row Labels' >
      						<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Shutdown:</div>
      						<div class='col-8'><input type='checkbox' disabled name='Shutdown' <?php echo isset($Requisition['Shutdown']) && $Requisition['Shutdown'] == 1 ? 'checked' : '';?> /></div>
      					</div>
      					<div class='row Labels' >
      						<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> A.S.A.P.:</div>
      						<div class='col-8'><input type='checkbox' disabled name='ASAP' <?php echo isset($Requisition['ASAP']) && $Requisition['ASAP'] == 1 ? 'checked' : '';?>  /></div>
      					</div>
                <div class='row Labels' >
      						<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Rush:</div>
      						<div class='col-8'><input type='checkbox' disabled name='Rush' <?php echo isset($Requisition['Rush']) && $Requisition['Rush'] == 1 ? 'checked' : '';?>  /></div>
      					</div>
                <div class='row Labels' >
      						<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> L/S/D.:</div>
      						<div class='col-8'><input type='checkbox' disabled name='LSD' <?php echo isset($Requisition['LSD']) && $Requisition['LSD'] == 1 ? 'checked' : '';?>  /></div>
      					</div>
                <div class='row Labels' >
      						<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> F.R.M.:</div>
      						<div class='col-8'><input type='checkbox' disabled name='FRM' <?php echo isset($Requisition['FRM']) && $Requisition['FRM'] == 1 ? 'checked' : '';?>  /></div>
      					</div>
                <div class='row Labels' >
                  <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Paragraph(1);?> Notes:</div>
                  <div class='col-12'><textarea name='Notes' style='width:100%;' rows='9' disabled><?php echo isset($Requisition['Notes']) ? $Requisition['Notes'] : NULL;?></textarea></div>
                </div>
                <div class='row'><div class='col-12'>&nbsp;</div></div>
              </div>
            </div>
            <div class='card card-primary my-3'>
              <div class='card-heading'>
                <div class='row g-0 px-3 py-2'>
                  <div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Info( 1 );?><span>Items</span></h5></div>
                  <div class='col-2'>&nbsp;</div>
                </div>
              </div>
              <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Infomation' ] ) && $_SESSION[ 'Cards' ][ 'Infomation' ] == 0 ? "style='display:none;'" : null;?>>
                <div class='row g-0'>
                  <div class='col-12'>
                    <table id='Table_Requisition_Items' class='display' cellspacing='0' width='100%'>
                      <thead><tr>
                        <th>ID</th>
                        <th>Description</th>
                        <th>Image</th>
                      </tr><tr>
                        <th><input type='text' class='form-control redraw' name='ID' placeholder='ID' value='<?php echo isset( $_GET[ 'ID'] ) ? $_GET[ 'ID' ] : null;?>' /></th>
                        <th><input type='text' class='form-control redraw' name='Description' placeholder='Description' value='<?php echo isset( $_GET[ 'Description'] ) ? $_GET[ 'Description' ] : null;?>' /></th>
                        <th><input type='text' class='form-control redraw' name='Image' placeholder='Image' disabled /></th>
                      </tr></thead>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </form></div>
    </div>
  </div>
	<script type="text/javascript" src="js/jquery.bgiframe.min.js"></script>
	<script type="text/javascript" src="js/jquery.ui.ufd.js"></script>
    <script>
	var Item_Count = 5;
	function newItem(){
		Item_Index = Item_Count - 1;
		$(".New-Item").before("<div class='row Item'><div class='col-1'>" + Item_Count.toString() + "</div><div class='col-2'><input type='text' name='Quantity[" + Item_Index.toString() + "]' style='width:100%;' /></div><div class='col-9'><input type='text' name='Description[" + Item_Index.toString() + "]' style='width:100%;' /></div>");
		Item_Count = Item_Count + 1;
	}
	$(document).ready(function(){
		$("input[name='Date']").datepicker();
		$("input[name='Required']").datepicker();
		$("input[name='Date']").datepicker("setDate",new Date());
	});
	$(document).ready(function(){
		$("select[name='Location']").ufd({log:true});
	});
  function closePopup(link){$(".popup").remove();}
  function saveRequisition(){
    var requisitionData = new FormData();
    requisitionData.append("Required",$("input[name='Required']").val());
    requisitionData.append('Location','<?php echo isset($_GET['Location']) ? $_GET['Location'] : '';?>');
    requisitionData.append('DropOff','<?php echo isset($_GET['DropOff']) ? $_GET['DropOff'] : '';?>');
    requisitionData.append('Unit','<?php echo isset($_GET['Unit']) ? $_GET['Unit'] : '';?>');
    requisitionData.append('Job','<?php echo isset($_GET['Job']) ? $_GET['Job'] : '';?>');
    requisitionData.append("Shutdown",$("input[name='Shutdown']").prop('checked'));
    requisitionData.append("ASAP",$("input[name='ASAP']").prop('checked'));
    requisitionData.append("Rush",$("input[name='Rush']").prop('checked'));
    requisitionData.append("LSD",$("input[name='LSD']").prop('checked'));
    requisitionData.append("FRM",$("input[name='FRM']").prop('checked'));
    var itemArray = [];
    var count = 0;
    $(".row.Item").each(function(){
      requisitionData.append("Item[" + count + "][Quantity]",$(this).find("input[name='Quantity']").val());
      requisitionData.append("Item[" + count + "][Comments]",$(this).find("input[name='Comments']").val());
      count++;
    });
    $.ajax({
      url:"bin/php/post/save_requisition.php",
      cache: false,
      processData: false,
      contentType: false,
      data: requisitionData,
      timeout:15000,
      error:function(XMLHttpRequest, textStatus, errorThrown){
        alert('Your ticket did not save. Please check your internet.')
        $(tempLink).html("Save");
        $(tempLink).prop('disabled',false);
      },
      method:"POST",
      success:function(code){
        document.location.href='requisition.php?ID=' + code;
      }
    });
  }
	</script>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=units.php';</script></head></html><?php }?>
