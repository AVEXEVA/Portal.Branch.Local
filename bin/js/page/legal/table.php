<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
header('Content-Type: text/javascript');?>

$( document ).ready( function( ){
  var Editor_Legal = new $.fn.dataTable.Editor( {
      idSrc    : 'ID',
      ajax     : 'index.php',
      table    : '#Table_Legal'
  } );
  var Table_Legal = $('#Table_Legal').DataTable( {
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
        url     : 'bin/php/get/legal.php',
        data : function( d ){
          d = {
              start : d.start,
              length : d.length,
              order : {
                  column : d.order[0].column,
                  dir : d.order[0].dir
              }
          };
          d.ID = $('input[name="ID"]').val( );
          d.Name = $('input[name="Name"]').val( );
          d.Location = $('input[name="Location"]').val( );
          d.Type = $('input[name="Type"]').val( );
          d.Date = $('input[name="Date"]').val( );
          d.Status = $('input[name="Status"]').val( );
          return d;
      }
    },
    columns: [
      <?php \singleton\datatables::getInstance( )->ID('legal.php','Legal');?>,
      <?php \singleton\datatables::getInstance( )->DataElement('Name');?>,
      <?php \singleton\datatables::getInstance( )->DataElement('Location');?>,
      <?php \singleton\datatables::getInstance( )->DataElement('Type');?>,
      <?php \singleton\datatables::getInstance( )->DataElement('Date');?>,
      <?php \singleton\datatables::getInstance( )->DataElement('Status');?>      
    ],
    initComplete : function( ){
        $("div.search").html( "<input type='text' name='Search' placeholder='Search' />" );//onChange='$(\"#Table_Tickets\").DataTable().ajax.reload( );'
        $('input.date').datepicker( { } );
        $('input.time').timepicker( {  timeFormat : 'h:i A' } );
        //search( this );
        $( '.redraw' ).bind( 'change', function(){ Table_Legal.draw(); });
    },
            text: 'Reset Search',
            action: function ( e, dt, node, config ) {
                $( 'input, select' ).each( function( ){
                    $( this ).val( '' );
                } );
                Table_Legal.draw( );
            }
        },{
            text : 'Get URL',
            action : function( e, dt, node, config ){
                var d = { };
                d.ID             = $('input[name="ID"]').val( );
                d.Name         = $('input[name="Name"]').val( );
                d.Location       = $('input[name="Location"]').val( );
                d.Date       = $('input[name="Date"]').val( );
                d.Type           = $('input[name="Type"]').val( );
                d.Status            = $('input[name="Status"]').val( );
                document.location.href = 'maintenances.php?' + new URLSearchParams( d ).toString();
            }
        },
        { extend: 'create', editor: Editor_Legal },
        { extend: 'edit',   editor: Editor_Legal },
        { extend: 'remove', editor: Editor_Legal },
        {
            text: 'Print',
            action: function ( e, dt, node, config ) {
                var rows = dt.rows( { selected : true } ).indexes( );
                var dte = dt.cells( rows, 0 ).data( ).toArray( );
                document.location.href = 'tickets.php?' + dte.join( ',' );
            }
        },
        'copy',
        'csv'
    ]
  } );
} );
