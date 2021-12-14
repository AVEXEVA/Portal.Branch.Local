<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
header('Content-Type: text/javascript');?>
$( document ).ready( function( ){
var Table_Faults = $('#Table_Faults').DataTable( {
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
url     : 'bin/php/get/Faults.php',
data : function( d ){
      d = {
          start : d.start,
          length : d.length,
          order : {
              column : d.order[0].column,
              dir : d.order[0].dir
          }
      };
      d.Search = $('input[name='Search']').val( );
      d.Customer = $('input[name='Customer']').val( );
      d.Location = $('input[name='Location']').val( );
      d.Job = $('input[name='Job']').val( );
      return d;
      }
},
columns: [
  <?php \singleton\datatables::getInstance( )->DataElement('ID');?>,
  <?php \singleton\datatables::getInstance( )->DataElement('Location');?>,
  <?php \singleton\datatables::getInstance( )->DataElement('Unit');?>,
  <?php \singleton\datatables::getInstance( )->DataElement('Date');?>,
  <?php \singleton\datatables::getInstance( )->DataElement('Fault');?>,
  <?php \singleton\datatables::getInstance( )->DataElement('Status');?>
]
} );
}
