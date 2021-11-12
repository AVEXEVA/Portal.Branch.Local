$( document ).ready( function( ){
  var Editor_Employees = new $.fn.dataTable.Editor( {
      idSrc    : 'ID',
      ajax     : 'index.php',
      table    : '#Table_Overtime'
  } );
  var Table_Employees = $('#Table_Employees').DataTable( {
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
        url     : 'bin/php/get/Employees.php',
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
          d.Last_Name = $('input[name="Last_Name"]').val( );
          d.First_Name = $('input[name="First_Name"]').val( );
          d.Supervisor = $('input[name="Supervisor"]').val( );
          return d;
      }
    },
    columns: [
      {
          data : 'ID'
      },{
          data : 'Last_Name'
      },{
          data : 'First_Name'
      },{
          data : 'Supervisor'
      }
    ],
    initComplete : function( ){
        $("div.search").html( "<input type='text' name='Search' placeholder='Search' />" );//onChange='$(\"#Table_Tickets\").DataTable().ajax.reload( );'
        $('input.date').datepicker( { } );
        $('input.time').timepicker( {  timeFormat : 'h:i A' } );
        //search( this );
        $( '.redraw' ).bind( 'change', function(){ Table_Overtime.draw(); });
    },
            text: 'Reset Search',
            action: function ( e, dt, node, config ) {
                $( 'input, select' ).each( function( ){
                    $( this ).val( '' );
                } );
                Table_Employees.draw( );
            }
        },{
            text : 'Get URL',
            action : function( e, dt, node, config ){
                var d = { };
                d.ID                   = $('input[name="ID"]').val( );
                d.Last_Name            = $('input[name="Last_Name"]').val( );
                d.First_Name           = $('input[name="First_Name"]').val( );
                d.Supervisor           = $('input[name="Supervisor"]').val( );
                document.location.href = 'employees.php?' + new URLSearchParams( d ).toString();
            }
        },
        { extend: 'create', editor: Editor_Employees },
        { extend: 'edit',   editor: Editor_Employees },
        { extend: 'remove', editor: Editor_Employees },
        {
            text: 'Print',
            action: function ( e, dt, node, config ) {
                var rows = dt.rows( { selected : true } ).indexes( );
                var dte = dt.cells( rows, 0 ).data( ).toArray( );
                document.location.href = 'employees.php?' + dte.join( ',' );
            }
        },
        'copy',
        'csv'
    ]
  } );
} );
