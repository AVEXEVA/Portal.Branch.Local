<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
header('Content-Type: text/javascript');?>
$( document ).ready( function( ){
  var Editor_Jobs = new $.fn.dataTable.Editor( {
    idSrc    : 'ID',
    ajax     : 'index.php',
    table    : '#Table_Jobs'
  } );
  var Table_Jobs = $('#Table_Jobs').DataTable( {
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
    ajax      : {
      url : 'bin/php/get/Jobs.php',
      data : function( d ){
        d = {
            draw : d.draw,
            start : d.start,
            length : d.length,
            order : {
                column : d.order[0].column,
                dir : d.order[0].dir
            }
        };
        d.ID = $("input[name='ID']").val( );
        d.Name = $("input[name='Name']").val( );
        d.Date = $("input[name='Date']").val( );
        d.Customer = $("input[name='Customer']").val( );
        d.Locaton = $("input[name='Location']").val( );
        d.Type = $("select[name='Type']").val( );
        d.Status = $("select[name='Status']").val( );
        d.Tickets = $("input[name='Tickets']").val( );
        d.Invoices = $("input[name='Invoices']").val( );
        return d;
      }
    },
    columns   : [
      <?php \singleton\datatables::getInstance( )->ID('job.php','Job');?>,
      <?php \singleton\datatables::getInstance( )->DataElement('Name');?>,
      <?php \singleton\datatables::getInstance( )->DataElement('Date');?>,
      <?php \singleton\datatables::getInstance( )->CustomerID();?>,
      <?php \singleton\datatables::getInstance( )->LocationID(1);?>,      
      <?php \singleton\datatables::getInstance( )->DataElement('Type');?>,
      <?php \singleton\datatables::getInstance( )->Status('Job');?>,
      <?php \singleton\datatables::getInstance( )->Tickets('Job');?>,
      <?php \singleton\datatables::getInstance( )->Invoices('Job');?>      
    ],
    buttons: [
      {
        text: 'Reset Search',
        className: 'form-control',
        action: function ( e, dt, node, config ) {
          $( 'input, select' ).each( function( ){
            $( this ).val( '' );
          } );
          Table_Tickets.draw( );
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
          document.location.href = 'jobs.php?' + new URLSearchParams( d ).toString();
        }
      },{
        text : 'Create',
        className: 'form-control',
        action : function( e, dt, node, config ){ 
          document.location.href='job.php'; 
        }
      },{
        text: 'Print',
        className: 'form-control',
        action: function ( e, dt, node, config ) {
            var rows = dt.rows( { selected : true } ).indexes( );
            var dte = dt.cells( rows, 0 ).data( ).toArray( );
            document.location.href = 'jobs.php?Tickets=' + dte.join( ',' );
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
