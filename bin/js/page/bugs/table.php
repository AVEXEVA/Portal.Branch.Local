<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
header('Content-Type: text/javascript');?>
$( document ).ready( function( ){
var Table_Bugs = $('#Table_bugs').DataTable( {
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
url     : 'bin/php/get/Bugs.php',
data : function( d ){
      d = {
          draw  : d.draw,
          start : d.start,
          length : d.length,
          order : {
              column : d.order[0].column,
              dir : d.order[0].dir
          }
      };
      d.ID = $("input[name='ID']").val( );
      d.Date = $("input[name='Date']").val( );
      d.Description = $("input[name='Description']").val( );
      d.Severity = $("input[name='Severity']").val( );
      d.Suggestion = $("input[name='Suggestion']").val( )
      d.Resolution = $("input[name='Resolution']").val( )
      d.Fixed = $("input[name='Fixed']").val( )
      return d;
      }
},
columns: [
    <?php \singleton\datatables::getInstance( )->ID('customer.php','Customer');?>,
    <?php \singleton\datatables::getInstance( )->TicketDate();?>,
    <?php \singleton\datatables::getInstance( )->DataElement('Description');?>,
    <?php \singleton\datatables::getInstance( )->DataElement('Severity');?>,
    <?php \singleton\datatables::getInstance( )->DataElement('Suggestion');?>,
    <?php \singleton\datatables::getInstance( )->DataElement('Resolution');?>,
    <?php \singleton\datatables::getInstance( )->DataElement('Fixed');?>
],
buttons: [
    {
        text: 'Reset Search',
        className: 'form-control',
        action: function ( e, dt, node, config ) {
            $( 'input, select' ).each( function( ){
                $( this ).val( '' );
            } );
            Table_Bugs.draw( );
        }
    },{
        text : 'Get URL',
        className: 'form-control',
        action : function( e, dt, node, config ){
            d = { }
            d.ID = $('input[name="ID"]').val( );
            d.Name = $('input[name="Name"]').val( );
            d.Status = $('select[name="Status"]').val( );
            document.location.href = 'bugs.php?' + new URLSearchParams( d ).toString();
        }
    },{
        text : 'Create',
        className: 'form-control',
        action : function( e, dt, node, config ){
            document.location.href='bugs.php';
        }
    },
    {
        text : 'Delete',
        className: 'form-control',
        action : function( e, dt, node, config ){
          var rows = dt.rows( { selected : true } ).indexes( );
          var dte = dt.cells( rows, 0 ).data( ).toArray( );
          $.ajax ({
            url    : 'bin/php/post/bugs.php',
            method : 'POST',
            data   : {
              action : 'delete' ,
              data : dte
            },
            success : function(response){
              Table_Bugs.draw();
            }
          })
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
],
initComplete : function( ){
    $("div.search").html( "<input type='text' name='Search' placeholder='Search' />" );//onChange='$(\"#Table_Tickets\").DataTable().ajax.reload( );'
    $('input.date').datepicker( { } );
    $('input.time').timepicker( {  timeFormat : 'h:i A' } );
    //search( this );
    $( '.redraw' ).bind( 'change', function(){ Table_Bugs.draw(); });
}
} );
} );
