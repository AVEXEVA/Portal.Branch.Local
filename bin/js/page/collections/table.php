<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
header('Content-Type: text/javascript');?>
$( document ).ready( function( ){
  var Editor_Collections = new $.fn.dataTable.Editor( {
      idSrc    : 'ID',
      ajax     : 'index.php',
      table    : '#Table_Collection'
  } );
  var Table_Collections = $('#Table_Collections').DataTable( {
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
        url     : 'bin/php/get/Collections.php',
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
          d.Search = $('input[name="Search"]').val( );
          d.ID = $('input[name="ID"]').val( );
          d.Territory = $('input[name="Territory"]').val( );
          d.Customer = $('input[name="Customer"]').val( );
          d.Location = $('input[name="Location"]').val( );
          d.Job = $('input[name="Job"]').val( );
          d.Type = $('input[name="Type"]').val( );
          d.Date_Start = $('input[name="Date_Start"]').val( );
          d.Date_End = $('input[name="Date_End"]').val( );
          d.Due_Start = $('input[name="Due_Start"]').val( );
          d.Due_End = $('input[name="Due_End"]').val( );
          d.Original_Start = $('input[name="Original_Start"]').val( );
          d.Original_End = $('input[name="Original_End"]').val( );
          d.Balance_Start = $('input[name="Balance_Start"]').val( );
          d.Balance_End = $('input[name="Balance_End"]').val( );
          d.Description = $('input[name="Description"]').val( );
          return d;
      }
    },
    columns: [
      <?php \singleton\datatables::getInstance( )->ID('collection.php','Invoice');?>,
      <?php \singleton\datatables::getInstance( )->TerritoryID();?>,
      <?php \singleton\datatables::getInstance( )->CustomerID();?>,
      <?php \singleton\datatables::getInstance( )->LocationID(1);?>,
      <?php \singleton\datatables::getInstance( )->JobID();?>,
      <?php \singleton\datatables::getInstance( )->DataElement('Type');?>,
      <?php \singleton\datatables::getInstance( )->DataElement('Date');?>,
      <?php \singleton\datatables::getInstance( )->DataElement('Due');?>,
     {
        data      : 'Original',
        render : function( data, type, row, meta ){
          switch( type ){
            case 'display':
              if( row.Original > 0 ){
                return "<div class='row'>" +
                  "<div class='col-12'>" + row.Original + "</div>" +
                "</div>"
              } else if( row.Original < 0 ){
                return "<div class='row'>" +
                  "<div class='col-12'>" + row.Original + "</div>" +
                "</div>"
              } else if( row.Original == 0 ){
                return "<div class='row'>" +
                  "<div class='col-12'>" + row.Original + "</div>" +
                "</div>"
              }
            default :
              return null;
          }
        }
      },{
        data      : 'Original',
        render : function( data, type, row, meta ){
          switch( type ){
            case 'display':
              if( row.Original > 0 ){
                return "<div class='row'>" +
                  "<div class='col-12'>" + row.Original + "</div>" +
                "</div>"
              } else if( row.Original < 0 ){
                return "<div class='row'>" +
                  "<div class='col-12'>" + row.Original + "</div>" +
                "</div>"
              } else if( row.Original == 0 ){
                return "<div class='row'>" +
                  "<div class='col-12'>" + row.Original + "</div>" +
                "</div>"
              }
            default :
              return null;
          }
        }
      },
      <?php \singleton\datatables::getInstance( )->DataElement('Description');?>
    ],
    initComplete : function( ){
        $("div.search").html( "<input type='text' name='Search' placeholder='Search' />" );//onChange='$(\"#Table_Tickets\").DataTable().ajax.reload( );'
        $('input.date').datepicker( { } );
        $('input.time').timepicker( {  timeFormat : 'h:i A' } );
        //search( this );
        $( '.redraw' ).bind( 'change', function(){ Table_Tickets.draw(); });
    },
    buttons: [
        {
            text: 'Email Invoice',
            className: 'form-control',
            action: function ( e, dt, node, config ) {
                var rows = dt.rows( { selected : true } ).indexes( );
                var dte = dt.cells( rows, 0 ).data( ).toArray( );
                $.ajax({
                    url : 'bin/php/post/emailInvoice.php',
                    method : 'POST',
                    data : {
                        email : prompt( "What email would you like to send the invoice to?"),
                        data : dte
                    },
                    success : function( response ){ }
                });
            }
        },{
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
                d.ID             = $('input[name="ID"]').val( );
                d.Person         = $('input[name="Person"]').val( );
                d.Customer       = $('input[name="Customer"]').val( );
                d.Location       = $('input[name="Location"]').val( );
                d.Unit           = $('input[name="Unit"]').val( );
                d.Job            = $('input[name="Job"]').val( );
                d.Type           = $('select[name="Type"]').val( );
                d.Level          = $('select[name="Level"]').val( );
                d.Status         = $('select[name="Status"]').val( );
                d.Start_Date     = $('input[name="Start_Date"]').val( );
                d.End_Date       = $('input[name="End_Date"]').val( );
                d.Time_Route_Start     = $('input[name="Time_Route_Start"]').val( );
                d.Time_Route_End       = $('input[name="Time_Route_End"]').val( );
                d.Time_Site_Start     = $('input[name="Time_Site_Start"]').val( );
                d.Time_Site_End       = $('input[name="Time_Site_End"]').val( );
                d.Time_Completed_Start     = $('input[name="Time_Completed_Start"]').val( );
                d.Time_Completed_End       = $('input[name="Time_Completed_End"]').val( );
                d.LSD       = $('select[name="LSD"]').val( );
                document.location.href = 'tickets.php?' + new URLSearchParams( d ).toString();
            }
          },{
          text : 'Create',
          className: 'form-control',
          action : function( e, dt, node, config ){
              document.location.href='invoice.php';}
            },{
            text: 'Print',
            className: 'form-control',
            action: function ( e, dt, node, config ) {
                var rows = dt.rows( { selected : true } ).indexes( );
                var dte = dt.cells( rows, 0 ).data( ).toArray( );
                document.location.href = 'print_tickets.php?Tickets=' + dte.join( ',' );
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
  } );
} );
