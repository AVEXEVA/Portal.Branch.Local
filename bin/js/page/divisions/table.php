<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
header('Content-Type: text/javascript');?>

$( document ).ready( function( ){
var Table_Divisions = $('#Table_Divisions').DataTable( {
dom            : "<'row'<'col-sm-3 search'><'col-sm-9'B>><'row'<'col-sm-12't>>",
processing     : true,
serverSide     : true,
searching      : false,
lengthChange   : false,
scrollResize   : true,
scrollY        : 100,
scroller       : true,
scrollCollapse : true,
paging         : true,
orderCellsTop  : true,
autoWidth      : true,
//stateSave      : true,
responsive     : {
details : {
  type   : 'column',
  target : 0
}
},
select         : {
style : 'multi',
selector : 'td.ID'
},
ajax: {
url     : 'bin/php/get/Divisions.php',
data : function( d ){
      d = {
          start : d.start,
          length : d.length,
          order : {
              column : d.order[0].column,
              dir : d.order[0].dir
          }
      };
      d.ID = $("input[name='ID']").val( );
      d.Name = $("input[name='Name']").val( );
      d.Locations = $("input[name='Locations']").val( );
      d.Units = $("input[name='Units']").val( );
      d.Violations = $("input[name='Violations']").val( );
      d.Tickets = $("input[name='Tickets']").val( );
      return d;
      }
},
columns: [
  <?php \singleton\datatables::getInstance( )->ID('divisions.php','Customer');?>,  
  <?php \singleton\datatables::getInstance( )->Name('divisions.php');?>,
  <?php \singleton\datatables::getInstance( )->Locations('Division');?>,
  <?php \singleton\datatables::getInstance( )->Units('Division');?>,
  <?php \singleton\datatables::getInstance( )->Violations('Division');?>,
  <?php \singleton\datatables::getInstance( )->Tickets('Division');?>        
],
buttons: [
  {
    text: 'Reset Search',
    className: 'form-control',
    action: function ( e, dt, node, config ) {
      $( 'input, select' ).each( function( ){
        $( this ).val( '' );
      } );
      Table_Divisions.draw( );
    }
  },{
    text : 'Get URL',
    className: 'form-control',
    action : function( e, dt, node, config ){
      var d = { };
      d.ID = $("input[name='ID']").val( );
      d.Name = $("input[name='Name']").val( );
      d.Date = $("input[name='Date']").val( );
      d.Customer = $("input[name='Customer']").val( );
      d.Locaton = $("input[name='Location']").val( );
      d.Type = $("select[name='Type']").val( );
      d.Status = $("select[name='Status']").val( );
      d.Tickets = $("input[name='Tickets']").val( );
      d.Invoices = $("input[name='Invoices']").val( );
      document.location.href = 'divisions.php?' + new URLSearchParams( d ).toString();
    }
  },{
    text : 'Create',
    className: 'form-control',
    action : function( e, dt, node, config ){
      document.location.href='division.php';
    }
  },{
    text: 'Print',
    className: 'form-control',
    action: function ( e, dt, node, config ) {
        var rows = dt.rows( { selected : true } ).indexes( );
        var dte = dt.cells( rows, 0 ).data( ).toArray( );
        document.location.href = 'divisions.php?Divisions=' + dte.join( ',' );
    }
  },{
    extend : 'copy',
    text : 'Copy',
    className : 'form-control'
  },{
    extend : 'csv',
    text : 'CSV',
    className : 'form-control'
  }
]
} );
} );
