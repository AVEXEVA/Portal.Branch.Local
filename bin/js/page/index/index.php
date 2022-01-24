<script>
  function changePanel( link ){
      changePanelHeading( $( link ).hasClass( 'active' ) ? null : $( link).attr( 'card' ) );
      changePanelBody( $( link ).attr( 'card' ) );
  }
  function changePanelHeading( card ){
      $(".dashboard .card-heading ul li ").each(function(){
        if( card === null ){
          $( this ).addClass( 'show' );
          $( this ).removeClass( 'active' );
      } else if ( card != $( this ).attr( 'card ') ) {
        $( this ).removeClass( 'show' );
      } else {
        $( this ).addClass( 'active' );
      }
    });
    if( card != null ){
      $( ".dashboard .card-heading ul li[card='" + card + "']").addClass( 'active' );
    }
  }
  function changePanelBody( card ){
    $(".dashboard .card-body").each(function(){ $( this ).removeClass( 'active' ); });
    $(".dashboard .card-body[card='" + card + "']").addClass( 'active' );
  }
</script>
<script>
var checked = true;
function changeSelectAllSafety(){
  $(".popup input.safety").each(function(){
    $(this).prop('checked',checked);
  });
  checked = !checked;
}
var safety_acknowledgement_popup;
$.ajax({
  url : 'bin/php/element/attendance/safety.html',
  success : function( html ){ safety_acknowledgement_popup = html; }
});
var dt_notes = '';
var covid_19_questionaire_popup;
$.ajax({
  url : 'bin/php/element/attendance/covid-19.html',
  success : function( html ){ covid_19_questionaire_popup = html; }
});
function attendance_clock(link){
  //Notes
  if($("textarea[name='notes_in']").length > 0){dt_notes = $("textarea[name='notes_in']").val();}
  if($("textarea[name='notes_out']").length > 0){dt_notes = $("textarea[name='notes_out']").val();}
  //IF Tree
  if($(link).attr('rel').includes('in')){
    $("body").css("overflow","hidden");
    $('.popup').remove();
    $('body').append(safety_acknowledgement_popup);
  } else if($(link).attr('rel').includes('out')) {
    $('.popup').remove();
      $.ajax({
        url:"review-tickets.php",
        success:function(code){
          $("body").append(code);
        }
      });
  }
}
function complete_clockout(){
  if('<?php
    $r = $database->query(null,"SELECT * FROM TicketO LEFT JOIN TicketDPDA ON TicketO.ID = TicketDPDA.ID WHERE ((TicketO.Assigned >= 2 AND TicketO.Assigned <= 3)  OR (TicketDPDA.Comments = '' OR TicketDPDA.Comments IS NULL ) AND 0 = 1 ) AND TicketO.fWork = ? ;",array( isset( $User['fWork'] ) ? $User[ 'fWork' ] : 0 ) );
    if($r && is_array(sqlsrv_fetch_array($r))){echo 'True';}
    else{echo 'False';}
  ?>' == 'True'){alert('You must finish your tickets before you clock out.');}
  else {
    $(".popup").remove();
    var link = $("button[rel='out']");
    var d = new Date();
    var hours = d.getHours();
    var minutes = d.getMinutes();
    var flip = '';
    if(hours >= 12){
      hours = hours - 12;
      if(hours == 0){
        hours = 12;
      }
      flip = 'PM';
    } else {
      flip = 'AM';
    }
    if(hours < 10){
      hours = "0" + hours.toString();
    }
    if(minutes < 10){
      minutes = "0" + minutes.toString();
    }
    var year = d.getFullYear();
    var month = parseFloat(d.getMonth()) + 1;
    var day = d.getDate();
    $.ajax({method:"POST",data: {Notes : dt_notes}, url:"bin/php/post/clock_out.php"});
    $(link).replaceWith("<div style='border:1px solid black;box-shadow:1px 2px black;'>" + month + "/" + day + "/" + year + " " + hours + ":" + minutes + " " + flip + "</div>");
  }
}
function submit_Acknowledgement_of_Safety(link){
  if(     !$("input[name='Hardhat']").is(':checked')
      ||  !$("input[name='Safety_Book']").is(':checked')
      ||  !$("input[name='First_Aid_Kit']").is(':checked')
      ||  !$("input[name='Dusk_Masks']").is(':checked')
      ||  !$("input[name='Lock_Out_Kit']").is(':checked')
      ||  !$("input[name='Safety_Glasses']").is(':checked')
      ||  !$("input[name='GFCI']").is(':checked')
  ){
      alert('You must have all of your safety equipment in order to clock in');
      return;
  }
  $("div.popup").replaceWith( covid_19_questionaire_popup );
}
function submit_COVID_19_Questionaire(link){
  if(     $("input[name='COVID_19_Questionaire_1']:checked").length == 0
      ||  $("input[name='COVID_19_Questionaire_2']:checked").length == 0
      ||  $("input[name='COVID_19_Questionaire_3']:checked").length == 0
      ||  $("input[name='COVID_19_Questionaire_4']:checked").length == 0
  ){
    alert('Please answer all questions'); return;
  }
  $(link).html("Saving <img src='media/images/spinner.gif' height='25px' width='auto' />");
  $(link).attr('disabled','disabled')
  $.ajax({
    url:'bin/php/post/covid-19-questionaire.php',
    data:{
      1 : $("input[name='COVID_19_Questionaire_1']:checked").val(),
      2 : $("input[name='COVID_19_Questionaire_2']:checked").val(),
      3 : $("input[name='COVID_19_Questionaire_3']:checked").val(),
      4 : $("input[name='COVID_19_Questionaire_4']:checked").val()
    },
    method:'POST',
    success:function(code){
      if(code == 'No'){
        alert('An employee who does not pass the screening questions must not report to work and should report to their supervisor.');
          document.location.href='home.php';
      } else {
        $.ajax( {
          method:"POST",
          data:{Notes : dt_notes},
          url:"bin/php/post/clock_in.php",
          success:function(code){ document.location.href='home.php'; }
        } );
      }
    }
  });
}
function clock_in_menu(){
  $("body").append("<div class='popup' style='background-color:#1d1d1d;color:white !important;top:0;position:absolute;left:0;width:100%;height:100%;'><div class='panel panel-primary'><div class='panel-heading'><div class='row'><div class='col-xs-12'>&nbsp;</div></div><div class='row'><div class='col-xs-12'>&nbsp;</div></div></div><div class='panel-heading'>Clock In Notes</div></div class='panel-bodwy'><div class='row'><div class='col-xs-12'>&nbsp;</div></div><div class='row'><div class='col-xs-12'><textarea name='notes_in' style='width:100%;color:black;' rows='10'></textarea></div><div class='col-xs-12'>&nbsp;</div><div class='col-xs-12'>&nbsp;</div><div class='col-xs-12'><input style='color:black;width:100%;height:50px;' rel='in_notes' type='button' onClick='attendance_clock(this);' value='Save' /></div></div></div></div>");
}
function clock_out_menu(){
  $("body").append("<div class='popup' style='background-color:#1d1d1d;color:white !important;top:0;position:absolute;left:0;width:100%;height:100%;'><div class='panel panel-primary'><div class='panel-heading'><div class='row'><div class='col-xs-12'>&nbsp;</div></div><div class='row'><div class='col-xs-12'>&nbsp;</div></div></div><div class='panel-heading'>Clock In Notes</div><div class='panel-body'><div class='row'><div class='col-xs-12'>&nbsp;</div></div><div class='row'><div class='col-xs-12'><textarea name='notes_out' style='width:100%;color:black;' rows='10'></textarea></div><div class='col-xs-12'>&nbsp;</div><div class='col-xs-12'>&nbsp;</div><div class='col-xs-12'><input style='color:black;width:100%;height:50px;' rel='out_notes' type='button' onClick='attendance_clock(this);' value='Save' /></div></div></div></div>");
}
var grouping_id = 5;
var grouping_name = 'Level';
var collapsedGroups = [];
var groupParent = [];
$( document ).ready( function(){
  var Table_Tickets = $('#Table_Tickets').DataTable( {
    dom: 'tp',
    ajax: {
      url: 'bin/php/get/Work2.php',
      dataSrc:function(json){
        if( !json.data ){ json.data = []; }
        return json.data;}
    },
    columns: [
      {
        className: 'indent',
        data : 'Tag',
        render : function(data, type, row, meta){
          if(type === 'display'){return "<?php \singleton\fontawesome::getInstance( )->Ticket(1);?>";}
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
});
$('tbody').on('click', 'tr.dtrg-start', function () {
    var name = $(this).data('name');
    collapsedGroups[name] = !collapsedGroups[name];
    Table_Tickets.draw( );
});
function hrefTickets( ){ hrefRow( 'Table_Tickets', 'ticket'); }
function redrawTickets( ) { Table_Tickets.order( [ [ grouping_id, 'asc' ] ] ).draw( ); }
var isChromium = window.chrome,
  winNav = window.navigator,
  vendorName = winNav.vendor,
  isOpera = winNav.userAgent.indexOf("OPR") > -1,
  isIEedge = winNav.userAgent.indexOf("Edge") > -1,
  isIOSChrome = winNav.userAgent.match("CriOS");
$(document).ready(function(){
  var Table_Locations = $('#Table_Locations').DataTable( {
    dom      : 'tp',
    processing : true,
    serverSide : true,
    responsive : true,
    ajax      : {
            url : 'bin/php/get/Locations2.php',
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
});
function redrawLocations( ){ Table_Locations.draw( ); }
function hrefLocations(){hrefRow("Table_Locations","location");}
$("Table#Table_Locations").on("draw.dt",function(){hrefLocations();});
function browseProfilePicture( ){
  $("body").append( "<div id='UploadProfilePicture' class='hidden' style='background-color:#1d1d1d;color:white !important;top:0;position:absolute;left:0;width:100%;height:100%;'><form id='upload' enctype='multipart/form-data'><div class='panel panel-primary'><div class='panel-heading'><h3>Upload User Picture</h3></div><div class='panel-body'><div class='row'><div class='col-12'><input onChange='uploadProfilePicture( );' type='file' name='Profile' /></div></div></div></div></form></div>");
  $("#UploadProfilePicture input").click( );
}
function uploadProfilePicture( ){
   var formData = new FormData( $( '#upload' ) [ 0 ] );
  $.ajax({
    url : 'bin/php/post/uploadProfilePicture.php',
    enctype: 'multipart/form-data',
    method : 'POST',
    cache: false,
    processData: false,
    contentType: false,
    data: formData,
    timeout:10000,
    success: function( ){ document.location.href = 'index.php'; }
  });
  $("#UploadProfilePiocture").remove( );
}
</script>
