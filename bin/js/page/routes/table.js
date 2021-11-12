$( document ).ready( function( ){
  var Editor_Routes = new $.fn.dataTable.Editor( {
      idSrc    : 'ID',
      ajax     : 'index.php',
      table    : '#Table_Product_Types'
  } );
  var Table_Routes = $('#Table_Routes').DataTable( {
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
        url     : 'bin/php/get/Routes2.php',
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
          d.User = $('input[name="Customer"]').val( );
          return d;
      },
  },
    columns : [
        {
          data : 'ID'
        },{
          data : 'Name'
        },{
          data : 'Employee'
        },{
          data : 'Locations'
        },{
          data : 'Units'
        }
    ],
    order : [ [ 1, 'asc' ] ],
    language : {
      loadingRecords : ''
    }
 } );
    initComplete : function( ){
        $("div.search").html( "<input type='text' name='Search' placeholder='Search' />" );//onChange='$(\"#Table_Tickets\").DataTable().ajax.reload( );'
        $('input.date').datepicker( { } );
        $('input.time').timepicker( {  timeFormat : 'h:i A' } );
        //search( this );
        $( '.redraw' ).bind( 'change', function(){ Table_Routes.draw(); });
    },
            text: 'Reset Search',
            action: function ( e, dt, node, config ) {
                $( 'input, select' ).each( function( ){
                    $( this ).val( '' );
                } );
                Table_Product_Types.draw( );
            }
        },{
            text : 'Get URL',
            action : function( e, dt, node, config ){
                var d = { };
                d.Search = $('input[name="Search"]').val( );
                d.ID = $('input[name="ID"]').val( );
                d.Name = $('input[name="Name"]').val( );
                d.User = $('input[name="Customer"]').val( ); 
                document.location.href = 'routes.php?' + new URLSearchParams( d ).toString();
            }
        },
        { extend: 'create', editor: Editor_Routes },
        { extend: 'edit',   editor: Editor_Routes },
        { extend: 'remove', editor: Editor_Routes },
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
