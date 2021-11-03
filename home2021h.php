<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( ); 
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/cgi-bin/php/index.php' );
}
if(     !isset( $Databases[ 'Default' ], $_SESSION[ 'User' ], $_SESSION[ 'Connection' ] )
    ||  !connection_privileged( $Databases[ 'Default' ], $_SESSION[ 'User' ], $_SESSION[ 'Connection' ] ) ){
        header( 'Location: https://beta.nouveauelevator.com/login.php' );
        exit; }
$result = sqlsrv_query(
  $Databases[ 'Default' ],
  " SELECT *,
          Emp.fFirst AS First_Name,
          Emp.Last   AS Last_Name,
          Emp.Field  AS Field
    FROM  Emp
    WHERE Emp.ID = ?;",
  array(
    $_SESSION[ 'Connection' ][ 'Branch_ID' ]
  )
);
$User = sqlsrv_fetch_array( $result );

$Privileges = privileges( $Databases[ 'Default' ], $_SESSION[ 'Connection' ][ 'Branch_ID' ] );
?><!DOCTYPE html>
<html lang='en'>
<head>
  <title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
  <?php require( bin_meta . 'index.php' );?>
  <?php require( bin_css  . 'index.php' );?>
  <?php require( bin_js   . 'index.php' );?>
</head>
<body style='background-color:#0a0a0a !important;'>
  <?php require( bin_php .'element/navigation/index.php');?>
  <div id='page-wrapper' class='content' style='display:block;'>
    <section id='account-menu' style='padding:50px;background-color:#0f0f0f;'>
      <div class='row'>
        <div class='col-xs-4'>&nbsp;</div>
        <?php 
          $result = sqlsrv_query( 
            $Portal,
            " SELECT  Picture,
                      Picture_Type AS Type 
              FROM    Portal.dbo.Portal 
              WHERE   Portal.Branch = ?
                      AND Portal.Branch_ID = ?;",
            array( 
              $_SESSION[ 'Connection' ][ 'Branch' ],
              $_SESSION[ 'User' ]
            )
          );
          if( $result ){
            $row = sqlsrv_fetch_array( $result );
            if( is_null( $row[ 'Picture' ] ) ){
              ?><div class='col-xs-4'><button class='slim' style='text-align:center;' onClick="browseProfilePicture( );"><img src='media/images/icons/avatar.png'  style='max-width:100%;max-height:200px;' /></button></div><?php
            } else {
              ?><div class='col-xs-4'><button class='slim' style='text-align:center;' onClick="browseProfilePicture( );"><img src='<?php print "data:" . $row['Type'] . ";base64, " . $row['Picture'];?>'  style='max-width:100%;max-height:200px;border:1px solid red;border-radius:100px;' /></button></div><?php
            }
          } else {?><div class='col-xs-4'><button class='slim' style='text-align:center;' onClick="browseProfilePicture( );"><img src='media/images/icons/avatar.png'  style='max-width:100%;max-height:200px;' /></button></div><?php }?>
        <script>
          function browseProfilePicture( ){
            $("body").append( "<div id='UploadProfilePicture' class='hidden' style='background-color:#1d1d1d;color:white !important;top:0;position:absolute;left:0;width:100%;height:100%;'><form><div class='panel panel-primary'><div class='panel-heading'><h3>Upload User Picture</h3></div><div class='panel-body'><div class='row'><div class='col-xs-12'><input onChange='uploadProfilePicture( );' type='file' name='Profile' /></div></div></div></div></form></div>");
            $("#UploadProfilePicture input").click( );
          }
          function uploadProfilePicture( ){
            var formData = new FormData( $( '#UploadProfilePicture form' ) [ 0 ] );
            $.ajax({
              url : 'cgi-bin/php/post/uploadProfilePicture.php',
              method : 'POST',
              cache: false,
              processData: false,
              contentType: false,
              data: formData,
              timeout:10000,
              success: function( ){ document.location.href = 'home.php'; }
            });
            $("#UploadProfilePiocture").remove( );
          }
        </script>
        <div class='col-xs-4'>
          <div class='row'>
            <div class='col-xs-12'><button class='slim' onClick="document.location.href='settings.php';" style='text-align:right;'><i class="fas fa-user-cog fa-2x"></i></button></div>
          </div>
        </div>
      </div>
      <div style='height:5px;'>&nbsp;</div>
      <div class='row'>
        <div class='col-xs-2'>&nbsp;</div>
        <div class='col-xs-8' style='text-align:center;'><?php echo $User[ 'Name' ];?></div>
        <div class='col-xs-2'>&nbsp;</div>
      </div>
      <div class='row'>
        <div class='col-xs-2'>&nbsp;</div>
        <div class='col-xs-8' style='text-align:center;font-size:12px;'><?php echo $User[ 'Title' ];?></div>
        <div class='col-xs-2'>&nbsp;</div>
      </div>
    </section>
    <?php if( $User[ 'Field' ] == 1 ){?>
    <section id='clock-menu' style='background-color:#3d3d3d;'>
      <?php
      $r = sqlsrv_query($NEI, "SELECT * FROM Attendance WHERE Attendance.[User] = ? AND Attendance.[End] IS NULL",array($_SESSION['User']));
      if($r){$Attendance_Activity = sqlsrv_fetch_array($r);}
      ?>
      <div class='row' style='/*-webkit-box-shadow: 0px 1px 20px rgba(19, 19, 19, 0.8);-moz-box-shadow:0px 1px 20px rgba(19, 19, 19, 0.8);box-shadow:0px 1px 20px rgba(19, 19, 19, 0.8);*/';>
        <div class='col-xs-12' style='background-color:#1e1e1e;color:white;padding:20px;text-align:center;'>Field Work</div>
      </div>
      <div class='row' style='padding:20px;'>
        <div class='col-xs-6' style='text-align:center;'>
          <?php if(is_array($Attendance_Activity) && isset($Attendance_Activity['Start'])){
            ?><div style='border:1px solid black;box-shadow:1px 0px black;padding:5px;'><i class="fas fa-business-time fa-fw fa-1x"></i> Clock In</div><?php
            ?><div style='border:1px solid black;box-shadow:1px 2px black;background-color:rgb(170, 170, 170);color:black;padding:5px;'><?php
              echo date("m/d/Y h:i A",strtotime($Attendance_Activity['Start']));
            ?></div><?php
          } else {
            ?><div style='border:1px solid black;box-shadow:1px 0px black;padding:5px;'><i class="fas fa-business-time fa-fw fa-1x"></i> Clock In</div><?php
            ?><button rel='in' style='background-color:rgba(250,250,250,.9);border:1px solid black;box-shadow:1px 2px black;text-align:center;padding:5px;' onClick='attendance_clock(this);'>Start Work</button><?php
          }?>
        </div>
        <div class='col-xs-6' style='text-align:center;'>
          <div style='border:1px solid black;box-shadow:1px 0px black;padding:5px;'><i class="fas fa-clipboard-list"></i> Clock Out</div><?php
          if(is_array($Attendance_Activity) && isset($Attendance_Activity['Start'])){
            ?><button rel='out' onClick='attendance_clock(this);' style='background-color:rgba(250,250,250,.9);border:1px solid black;box-shadow:1px 2px black;text-align:center;padding:5px;'>Finish Work</button><?php
          } else {
            ?><button disabled rel='out' onClick='attendance_clock(this);' style='background-color:rgba(250,250,250,.9);border:1px solid black;box-shadow:1px 2px black;text-align:center;padding:5px;'>Finish Work</button><?php
          }?>
        </div>
      </div>
      
      <style>
        #Table_Tickets th {
          border : 1px solid black;
        }
        #Table_Tickets td {
          border-top : 1px solid gray !important;
        }
        .Dashboard.row .panel-heading {
          padding : 0px;
          margin  : 0px;
        }
        .Dashboard.row .panel-heading .row>div {
          
          border : 1px solid #3d3d3d;
          cursor : pointer;
        }
        .Dashboard.row .panel-body {
          display : none;
        }
        .Dashboard.row .panel-body.active {
          display : block; 
        }
        .Dashboard ul {
        	margin:0px;
        	padding:0px;
        	list-style-type:none;
        	position:relative;
        }
        .Dashboard ul li {
        	display:none;
        	padding : 15px;
        }
        .Dashboard ul li.active, .Dashboard ul li.show {
        	display:block;
        }
        .Dashboard ul li.active:first-child {
        	top:0px;
        }
        .Dashboard ul li.active:nth-child( 2 ){
        	top:50px;
        }
        .Dashboard ul li.active:nth-child( 3 ){
        	top:100px;
        }
        .Dashboard ul li:nth-child( even ) {
        	background-color:#1d1d1d;
        }
        .Dashboard ul li:nth-child( odd ) {
        	background-color:#2d2d2d;
        }
        /*Fix end of row*/
      </style>
      <script>
        function changePanel( link ){
          	changePanelHeading( $( link ).hasClass( 'active' ) ? null : $( link).attr( 'panel' ) );
          	changePanelBody( $( link ).attr( 'panel' ) );
        }
        function changePanelHeading( panel ){
          	$(".Dashboard .panel-heading ul li ").each(function(){ 
          		if( panel === null ){
          			$( this ).addClass( 'show' ); 
          			$( this ).removeClass( 'active' );
      			} else if ( panel != $( this ).attr( 'panel ') ) {
      				$( this ).removeClass( 'show' );
      			} else {
      				$( this ).addClass( 'active' );
      			}
      		});
      		if( panel != null ){
      			$( ".Dashboard .panel-heading ul li[panel='" + panel + "']").addClass( 'active' );
      		}
        } 
        function changePanelBody( panel ){
          $(".Dashboard .panel-body").each(function(){ $( this ).removeClass( 'active' ); });
          $(".Dashboard .panel-body[panel='" + panel + "']").addClass( 'active' );
        }
      </script>
      <style>

      </style>
      <div class='Dashboard row'>
        <div class='col-xs-12'>
          <div class='panel panel-primary'>
            <div class='panel-heading'>
              <div class='row'>
	            <div class='col-xs-12'>
	            	<ul>
	            		<li class='active' onClick="changePanel( this );" panel='Tickets'><?php $Icons->Ticket( 1 );?> Tickets</li>
	            		<li class='' onClick="changePanel( this );" panel='Locations'><?php $Icons->Location( 1 );?> Locations</li>
	            	</ul>
	            </div>
	          </div>
              <?php /*<div class='row'>
                <div class='col-xs-4 active' onClick="changePanel( 'Tickets' );" panelheading='Tickets'><?php $Icons->Ticket( 1 );?> Tickets</div>
                <div class='col-xs-4' onClick="changePanel( 'Locations' );" panelheading='Locations'><?php $Icons->Location( 1 );?> Locations</div>
                <div class='col-xs-2'>&nbsp;</div>
              </div>*/?>
            </div>
            <div class='panel-body active' panel='Tickets'>
              <table id='Table_Tickets' class='display' cellspacing='0' width='100%' style='<?php if(isMobile()){?>font-size:10px;<?php }?>'>
                <thead><tr>
                  <th title='Location'></th>
                  <th title='ID'>ID</th>
                  <th title='Status'>Status</th>
                  <th title='Date'>Date</th>
                  <th title='Unit'>Unit</th>
                  <th title='Type'>Type</th>
                  <th title='Priority'>Priority</th>
                </tr></thead>
                <tfoot><tr>
                  <th colspan='5' onClick="document.location.href='tickets.php';" style='cursor:pointer;'><?php $Icons->Ticket( 1 );?> Go to All Tickets</th>
                  <th colspan='2' onClick='document.location.href="ticket.php";'><i class='fa fa-plus fa-fw fa-1x'></i> New</th>
                </tr></tfoot>
              </table>
            </div>
            <div class="panel-body no-print filters" panel='Locations' style='border-bottom:1px solid #1d1d1d;'>
              <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
              <div class='row'>
                  <div class='col-xs-4'>Search:</div>
                  <div class='col-xs-8'><input type='text' name='Search' placeholder='Search' onChange='redrawLocations( );' /></div>
              </div>
            </div>
            <div class='panel-body' panel='Locations'>
              <table id='Table_Locations' class='display' cellspacing='0' width='100%' style='<?php if(isMobile()){?>font-size:9px;<?php }?>;'>
                <thead><tr>
                  <th title='ID'>ID</th>
                  <th title='Name'>Name</th>
                  <th title='Customer'>Customer</th>
                  <th title='City'>City</th>
                  <th title='Street'>Street</th>
                  <th title='Maintained'>Maintained</th>
                  <th title='Status'>Status</th>
                </tr></thead>
              </table>
            </div>
          </div>
        </div>
        <script src="https://www.nouveauelevator.com/vendor/bootstrap/js/bootstrap.min.js"></script>
        <?php $_GET[ 'Datatables_Simple' ] = 1; ?>
        <?php require('cgi-bin/js/datatables.php');?>
        <style></style>
        <script src='https://cdn.datatables.net/rowgroup/1.1.2/js/dataTables.rowGroup.min.js'></script>
        <script src='https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js'></script>
        <script>
          var grouping_id = 5;
          var grouping_name = 'Level';
          var collapsedGroups = [];
          var groupParent = [];
          var Table_Tickets = $('#Table_Tickets').DataTable( {
            dom: 'tp',
            ajax: {
              url: 'cgi-bin/php/get/Work2.php',
              dataSrc:function(json){
                if( !json.data ){ json.data = []; }
                return json.data;}
            },
            columns: [
              {
                className: 'indent',
                data : 'Tag',
                render : function(data, type, row, meta){
                  if(type === 'display'){return '<?php $Icons->Ticket(1);?>';}
                  return data;
                },
                sortable : true,
                visible : false
              },{
                data : 'ID'
              },{
                data : 'Status'
              },{
                data : 'Date',
                render: function(data) {
                  if(data === null){return data;}
                  else {return data.substr(5,2) + '/' + data.substr(8,2) + '/' + data.substr(0,4);}}
              },{
                data : 'Unit_State',
                render:function(data, type, row, meta){
                  if(type === 'display'){
                    if(row.Unit_State === null){return '';}
                    return row.Unit_State + ', </br>' + row.Unit_Label;
                  }
                  return row.Unit_State;
                }
              },{
                data : 'Level',
                render: function(data, type, row, meta){
                  return data;
                }
              },{
                data : 'Priority',
                render: function(data, type, row, meta){
                  return data == 1 ? 'Yes' : 'No';
                },
                visible : false
              }
            ],
            order: [ [ 5, 'asc' ], [0, 'asc' ] ],
            initComplete : function(){ },
            paging : false,
            createdRow : function( row, data, dataIndex ) {
              if ( data['Status'] == 'On Site' || data['Status'] == 'En Route') { $(row).addClass('gold'); } 
              else if( data['Priority'] == 1 && data['Status'] != 'Reviewing' && data['Status'] != 'Completed'){ $(row).addClass('red'); } 
              else if ( data['Level'] == 'Service Call' && data['Status'] != 'Reviewing' && data['Status'] != 'Completed' && data['Status'] != 'Signed' ){ $(row).addClass('blue'); } 
              else if( data['Status'] == 'Signed' ){ $(row).addClass('green'); } 
              else if (data['Status'] != 'Reviewing' && data['Status'] != 'Completed' ){ $(row).addClass('light'); }
            },
            rowGroup: { 
              // Uses the 'row group' plugin
              dataSrc: [
                'Level',
                'Location'
              ],
              startRender: function(rows, group, level) {
                groupParent[level] = group;

                var groupAll = '';
                for (var i = 0; i < level; i++) {groupAll += groupParent[i]; if (collapsedGroups[groupAll]) {return;}}
                groupAll += group;

                if ((typeof(collapsedGroups[groupAll]) == 'undefined') || (collapsedGroups[groupAll] === null)) {collapsedGroups[groupAll] = true;} //True = Start collapsed. False = Start expanded.

                var collapsed = collapsedGroups[groupAll];
                var newTickets = 0;
                rows.nodes().each(function(r) {
                  if(( $(r).children(':nth-child(2)').html() != 'On Site' && $(r).children(':nth-child(2)').html() != 'En Route'  && $(r).children(':nth-child(6)').html() != 'Yes') || $(r).children(':nth-child(2)').html() == 'Reviewing' || $(r).children(':nth-child(2)').html() == 'Signed' || $(r).children(':nth-child(2)').html() == 'Completed'){
                    r.style.display = (collapsed ? 'none' : '');
                  }
                  var start = new Date();
                  start.setHours(0,0,0,0);
                  var end = new Date();
                  end.setHours(23,59,59,999);
                  if( new Date($(r).children(':nth-child(3)').html()) >= start && new Date($(r).children(':nth-child(3)').html()) < end && $(r).children(':nth-child(2)').html() != 'Reviewing' && $(r).children(':nth-child(2)').html() != 'Signed' && $(r).children(':nth-child(2)').html() != 'Completed'){ newTickets++; }
                });
                var newString = newTickets > 0 ? ', ' + newTickets + ' new' : '';
                return $('<tr/>').append('<td colspan="5">' + group  + ' ( ' + rows.count() + ' total' + newString + ' ) </td>').attr('data-name', groupAll).toggleClass('collapsed', collapsed);
              }
            },
            drawCallback : function ( settings ) { 
              hrefTickets( ); 
            }
          } );
          $('tbody').on('click', 'tr.dtrg-start', function () {
              var name = $(this).data('name');
              collapsedGroups[name] = !collapsedGroups[name];
              Table_Tickets.draw( );
          });
          function hrefTickets( ){ hrefRow( 'Table_Tickets', 'ticket'); }
          function redrawTickets( ) { Table_Tickets.order( [ [ grouping_id, 'asc' ] ] ).draw( ); }
        </script>
        <script>
        var isChromium = window.chrome,
          winNav = window.navigator,
          vendorName = winNav.vendor,
          isOpera = winNav.userAgent.indexOf("OPR") > -1,
          isIEedge = winNav.userAgent.indexOf("Edge") > -1,
          isIOSChrome = winNav.userAgent.match("CriOS");
        var Table_Locations = $('#Table_Locations').DataTable( {
          dom      : 'tlp',
          processing : true,
          serverSide : true,
          responsive : true,
          ajax      : {
                  url : 'cgi-bin/php/get/Locations2.php',
                  data : function( d ){
                      d = {
                          start : d.start,
                          length : d.length,
                          order : {
                              column : d.order[0].column,
                              dir : d.order[0].dir
                          }
                      };
                      d.Search = $('input[name="Search"]').val( );
                      d.ID = $('input[name="ID"]').val( );
                      d.Name = $('input[name="Name"]').val( );
                      d.Customer = $('input[name="Customer"]').val( );
                      d.City = $('input[name="City"]').val( );
                      d.Street = $('input[name="Street"]').val( );
                      d.Maintained = $('select[name="Maintained"]').val( );
                      d.Status = $('select[name="Status"]').val( );
                      return d; 
                  }
              },
          columns   : [
            {
              data    : 'ID',
              className : 'hidden'
            },{ 
              data : 'Name'
            },{
              data : 'Customer'
            },{
              data : 'City'
            },{
              data : 'Street'
            },{
              className : 'hidden',
              data   : 'Maintained',
              render : function ( data ){
                return data == 1
                  ? 'Yes'
                  : 'No';
              }
              
            },{
              className : 'hidden',
              data   : 'Status', 
              render : function ( data ){
                return data == 0 
                  ? 'Yes'
                  : 'No';
              }
            }
          ],
          autoWidth : false,
          paging    : true,
          searching : false
        } );
        function redrawLocations( ){ Table_Locations.draw( ); }
        function hrefLocations(){hrefRow("Table_Locations","location");}
        $("Table#Table_Locations").on("draw.dt",function(){hrefLocations();});
        </script>
      </div>
    </section>
    <?php } ?>
    <secton id='main-menu' style=''>
      <script>
        function togglePanel( link ){ link.parentNode.parentNode.classList.toggle('open'); }
      </script>
      <style>
        .Home-Screen-Option {
          background-color:#1f1f1f;
          font-size:16px;
          /*border-radius:100px;*/
          position:relative;
          /*left:4%;*/
          padding:10px
        }
        @media screen and ( min-width:1400px ){
          .col-xl-1 {
            width:7.333333%;
            margin-left:.5%;
            margin-right:.5%;
          }
        }
      </style>
      <?php if(isset($Ticket) && is_array($Ticket)){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='ticket.php?ID=<?php echo $Ticket['ID'];?>';">
        <div class='nav-icon'><?php $Icons->Ticket(3);?></div>
        <div class ='nav-text'>Active Ticket</div>
      </div><?php }?>
      <?php if(isset($Privileges['Time']) && $Privileges['Time']['Other_Privilege'] >= 4){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-6' onclick="document.location.href='scheduler.php'">
        <div class='nav-icon'><i class="fa fa-question-circle fa-3x fa-fw" aria-hidden="true"></i></div>
        <div class ='nav-text'>Attendance</div>
      </div><?php }?>
      <?php if(isset($Privileges['Invoice']) && $Privileges['Invoice']['User_Privilege'] >=4){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='collections.php'">
        <div class='nav-icon'><?php $Icons->Collection(3);?></div>
        <div class ='nav-text'>Collections</div>
      </div><?php } ?>
      <?php if(isset($Privileges['Contract']) && $Privileges['Contract']['User_Privilege'] >=4){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='contracts.php'">
        <div class='nav-icon'><?php $Icons->Contract(3);?></div>
        <div class ='nav-text'>Contracts</div>
      </div><?php } ?>
      <?php if(isset($Privileges['Customer']) && $Privileges['Customer']['User_Privilege'] >=4){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='customers.php'">
        <div class='nav-icon'><?php $Icons->Customer(3);?></div>
        <div class ='nav-text'>Customers</div>
      </div><?php } ?>
      <?php /*if(isset($Privileges['Dispatch']) && $Privileges['Ticket']['Other_Privilege'] >=4){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='dispatch.php'">
        <div class='nav-icon'><?php $Icons->Dispatch(3);?></div>
        <div class ='nav-text'>Dispatch</div>
      </div><?php } */?>
      <?php /*if(isset($Privileges['Ticket']) && $Privileges['Ticket']['Other_Privilege'] >=7){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='gps_locations.php'">
        <div class='nav-icon'><i class="fa fa-tencent-weibo fa-3x" aria-hidden="true"></i></div>
        <div class ='nav-text'>Geofence</div>
      </div><?php }*/ ?>
      <?php if(isset($Privileges['Ticket']) && $Privileges['Ticket']['User_Privilege'] >= 4){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='tickets.php'">
        <div class='nav-icon'><?php $Icons->History(3);?></div>
        <div class ='nav-text'>History</div>
      </div><?php } ?>
      <?php if(isset($User['Title']) && strpos($User['Title'], 'SUPER') === false && ($User['Title'] != 'OFFICE' || in_array($User['ID'],array(895,250)))){?><div class='Home-Screen-Option col-xl-2 col-lg-2 col-md-2 col-xs-6' onclick="window.open('https://docs.google.com/forms/d/1kqijgH7gnxEVwYaobgCn8nbjNFG-vXXpecXMHkqy0GA/viewform?edit_requested=true');">
        <div class='nav-icon'><?php $Icons->Safety_Report(3);?></div>
        <div class ='nav-text'>Incident Report</div>
      </div><?php } ?>
      <?php if(isset($User['Title']) && strpos($User['Title'], 'SUPER') !== false && ($User['Title'] != 'OFFICE' || in_array($User['ID'],array(895,250)))){?><div class='Home-Screen-Option col-xl-2 col-lg-2 col-md-2 col-xs-6' onclick="window.open('https://docs.google.com/a/nouveauelevator.com/forms/d/1yeaJSLEJMkt8HYnx_fzGHJtBjU_iOlXCNtQT6r5pXTE/edit?usp=drive_web');">
        <div class='nav-icon'><?php $Icons->Safety_Report(3);?></div>
        <div class ='nav-text'>Incident Report</div>
      </div><?php } ?>
      <?php if(isset($Privileges['Invoice']) && $Privileges['Invoice']['User_Privilege'] >=4){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='invoices.php'">
        <div class='nav-icon'><?php $Icons->Invoice(3);?></div>
        <div class ='nav-text'>Invoices</div>
      </div><?php } ?>
      <?php if(isset($Privileges['Job']) && $Privileges['Job']['User_Privilege'] >=4){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='jobs.php'">
        <div class='nav-icon'><?php $Icons->Job(3);?></div>
        <div class ='nav-text'>Jobs</div>
      </div><?php } ?>
      <?php if(isset($Privileges['Lead']) && $Privileges['Lead']['User_Privilege'] >=4){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='leads.php'">
          <div class='nav-icon'><?php $Icons->Customer(3);?></div>
          <div class ='nav-text'>Leads</div>
      </div><?php } ?>
      <?php if(isset($Privileges['Location']) && $Privileges['Location']['User_Privilege'] >=4){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='locations.php'">
          <div class='nav-icon'><?php $Icons->Location(3);?></div>
          <div class ='nav-text'>Locations</div>
      </div><?php } ?>
      <div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='../login.php?Logout=TRUE'">
        <div class='nav-icon'><?php $Icons->Logout(3);?></div>
        <div class ='nav-text'>Logout</div>
      </div>
      <?php if(isset($Privileges['Map']) && $Privileges['Map']['Other_Privilege'] >= 4){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='map.php'">
        <div class='nav-icon'><?php $Icons->Map(3);?></div>
        <div class ='nav-text'>Map</div>
      </div><?php }?>
      <?php if(isset( $Privileges['Admin'] ) && $Privileges['Admin']['Other_Privilege'] >= 7 ){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='payroll.php'">
        <div class='nav-icon'><?php $Icons->Invoice(3);?></div>
        <div class ='nav-text'>Payroll</div> 
      </div><?php }?>
      <?php if(isset($Privileges['Privilege']) && $Privileges['Privilege']['User_Privilege'] >=4){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='privileges.php'">
        <div class='nav-icon'><?php $Icons->Privilege(3);?></div>
        <div class ='nav-text'>Privileges</div> 
      </div><?php } ?>
      <?php if((isset($Privileges['Code']) && $Privileges['Code']['Other_Privilege'] >= 4) && $_SESSION['User'] != 975){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='batch_process_deficiencies2.php'">
        <div class='nav-icon'><i class="fa fa-clipboard fa-3x fa-fw" aria-hidden="true"></i></div>
        <div class ='nav-text'>Processing</div>
      </div><?php }?>
      <div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='user.php'">
          <div class='nav-icon'><?php $Icons->User(3);?></div>
          <div class ='nav-text'>Profile</div>
      </div>
      <?php if(isset($Privileges['Sales_Admin']) && $Privileges['Sales_Admin']['Other_Privilege'] >= 4){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-6' onclick="document.location.href='accounts_v2019.php'">
        <div class='nav-icon'><i class="fa fa-dollar fa-3x fa-fw" aria-hidden="true"></i></div>
        <div class ='nav-text'>Profitability</div>
      </div><?php }?>
      <?php if(isset($Privileges['Invoice']) && $Privileges['Invoice']['Other_Privilege'] >= 4 ){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='proposals.php'">
        <div class='nav-icon'><?php $Icons->Proposal(3);?></div>
        <div class ='nav-text'>Proposals</div>
      </div><?php } ?>
      <?php /*<div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='pto.php'">
        <div class='nav-icon'><?php $Icons->Calendar(3);?></div>
        <div class ='nav-text'>PTO</div>
      </div>*/?>
      <?php if(isset( $Privileges['Admin'] ) && $Privileges['Admin']['Other_Privilege'] >= 7 ){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='elevt_report.php'">
        <div class='nav-icon'><?php $Icons->Customer(3);?></div>
        <div class ='nav-text'>Questions</div>
      </div><?php }?>
      <?php if(isset($Privileges['Requisition']) && $Privileges['Requisition']['User_Privilege'] >=4){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-6' onclick="document.location.href='requisitions.php'">
        <div class='nav-icon'><?php $Icons->Requisition(3);?></div>
        <div class ='nav-text'>Requisitions</div>
      </div><?php } ?>
      <?php if(isset($Privileges['Time']) && $Privileges['Time']['Other_Privilege'] >= 4){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-6' onclick="document.location.href='review.php'">
        <div class='nav-icon'><?php $Icons->Customer( 3 );?></div>
        <div class ='nav-text'>Review</div>
      </div><?php }?>
      <?php if(isset($Privileges['Invoice']) && $Privileges['Invoice']['User_Privilege'] >= 7){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='invoice-registrar-1.php'">
        <div class='nav-icon'><?php $Icons->Invoice(3);?></div>
        <div class ='nav-text'>Registrar</div>
      </div><?php } ?>
      <?php if(isset($Privileges['Route']) && $Privileges['Route']['Other_Privilege'] >=4){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='routes.php'">
        <div class='nav-icon'><?php $Icons->Route(3);?></div>
        <div class ='nav-text'>Routes</div>
      </div><?php } ?>
      <?php
      $result = sqlsrv_query(
        $NEI,
        " SELECT Route.ID
          FROM   Route
                 LEFT JOIN Emp ON Route.Mech = Emp.fWork
          WHERE  Emp.ID = ?;",
        array( $_SESSION['User'] )
      );
      $RouteNav = sqlsrv_fetch_array($result);
      if(isset($Privileges['Route']) && $Privileges['Route']['User_Privilege'] >= 4 && is_array($RouteNav) && isset($RouteNav['ID']) && $RouteNav['ID'] > 0 ){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='route.php?ID=<?php echo $RouteNav['ID'];?>'">
        <div class='nav-icon'><?php $Icons->Route(3);?></div>
        <div class ='nav-text'>Route</div>
      </div><?php } ?>
      <?php if(isset($Privileges['Safety_Report']) && $Privileges['Safety_Report']['User_Privilege'] >= 4){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='reports.php'">
        <div class='nav-icon'><?php $Icons->Report(3);?></div>
        <div class ='nav-text'>Reports</div>
      </div><?php } ?>
      <?php if(False){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='settings.php'">
        <div class='nav-icon'><?php $Icons->Unit(3);?></div>
        <div class ='nav-text'>Settings</div>
      </div><?php }?>
      <?php if(isset($Privileges['Admin']) && $Privileges['Admin']['User_Privilege'] >= 4){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='supervising.php'">
        <div class='nav-icon'><?php $Icons->Customer(3);?></div>
        <div class ='nav-text'>Supervising</div>
      </div><?php } ?>
      <?php if(isset($Privileges['Ticket']) && $Privileges['Ticket']['Other_Privilege'] >= 4){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='support.php'">
        <div class='nav-icon'><i class="fa fa-question-circle fa-3x fa-fw" aria-hidden="true"></i></div>
        <div class ='nav-text'>Support</div>
      </div><?php }?>
      <?php if(isset($Privileges['Territory']) && $Privileges['Territory']['User_Privilege'] >= 4){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='territories.php'">
          <div class='nav-icon'><?php $Icons->Territory(3);?></div>
          <div class ='nav-text'>Territories</div>
      </div><?php }?>
      <?php if((isset($Privileges['Code']) && $Privileges['Code']['Other_Privilege'] >= 4)){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='category_tests.php'">
        <div class='nav-icon'><i class="fa fa-clipboard fa-3x fa-fw" aria-hidden="true"></i></div>
        <div class ='nav-text'>Tests</div>
      </div><?php }?>
      <?php if((isset($Privileges['Testing_Admin']) && $Privileges['Testing_Admin']['Other_Privilege'] >= 4)){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='draft_category_tests.php'">
        <div class='nav-icon'><i class="fa fa-clipboard fa-3x fa-fw" aria-hidden="true"></i></div>
        <div class ='nav-text'>Testing</div>
      </div><?php }?>
      <?php if(isset($Privileges['Time']) && $Privileges['Time']['User_Privilege'] >= 4){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='timesheet.php'">
          <div class='nav-icon'><?php $Icons->Timesheet(3);?></div>
          <div class ='nav-text'>Timesheet</div>
      </div><?php }?>
      <?php if(isset($Privileges['Unit']) && $Privileges['Unit']['User_Privilege'] >= 4 || $Privileges['Unit']['Group_Privilege'] >= 4 || $Privileges['Unit']['Other_Privilege'] >= 4){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='units.php'">
        <div class='nav-icon'><?php $Icons->Unit(3);?></div>
        <div class ='nav-text'>Units</div>
      </div><?php } ?>
      <?php if(isset($Privileges['User']) && $Privileges['User']['Other_Privilege'] >= 7){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='users.php'">
        <div class='nav-icon'><?php $Icons->Users(3);?></div>
        <div class ='nav-text'>Users</div>
      </div><?php } ?>
      <?php if(isset($Privileges['Violation']) && $Privileges['Violation']['User_Privilege'] >=4){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='violations.php'">
        <div class='nav-icon'><?php $Icons->Violation(3);?></div>
        <div class ='nav-text'>Violations</div>
      </div><?php } ?>
      <div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='https://www.nouveauelevator.com/';">
        <div class='nav-icon'><?php $Icons->Web(3);?></div>
        <div class ='nav-text'>Website</div>
      </div>
      <div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='work.php'">
        <div class='nav-icon'><?php $Icons->Ticket(3);?></div>
        <div class ='nav-text'>Work</div>
      </div>
      <?php if(isset($Privileges['Admin']) && $Privileges['Admin']['Other_Privilege'] >= 7){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='interface.php'">
        <div class='nav-icon'><i class="fa fa-user-secret fa-3x fa-fw" aria-hidden="true"></i></div>
        <div class ='nav-text'>Beta</div>
      </div><?php }?>
      <?php if(isset($Privileges['Admin']) && $Privileges['Admin']['Other_Privilege'] >= 7){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='../portal2/'">
        <div class='nav-icon'><i class="fa fa-user-secret fa-3x fa-fw" aria-hidden="true"></i></div>
        <div class ='nav-text'>Legacy</div>
      </div><?php }?>
    </section>
  </div>
</div>
<script src="https://www.nouveauelevator.com/vendor/bootstrap/js/bootstrap.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
</body>
</html>