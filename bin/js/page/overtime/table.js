$( document ).ready( function( ){
  var Editor_Overtime = new $.fn.dataTable.Editor( {
      idSrc    : 'ID',
      ajax     : 'index.php',
      table    : '#Table_Overtime'
  } );
  var Table_Overtime = $('#Table_Overtime').DataTable( {
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
        url     : 'bin/php/get/Overtime.php',
        data : function( d ){
          d = {
              start : d.start,
              length : d.length,
              order : {
                  column : d.order[0].column,
                  dir : d.order[0].dir
              }
          };
          d.Supervisor = $('input[name="Supervisor"]').val( );
          d.Location = $('input[name="Location"]').val( );
          d.Name = $('input[name="Name"]').val( );
          d.Emp_Supervisor = $('input[name="Emp_Supervisor"]').val( );
          d.Regular = $('input[name="Regular"]').val( );
          d.ND = $('input[name="ND"]').val( );
          d.OT = $('input[name="OT"]').val( );
          d.DT = $('input[name="DT"]').val( );
          d.Total = $('input[name="Total"]').val( );
          d.OT_to_Date = $('input[name="OT_to_Date"]').val( );
          return d;
      }
    },
    columns: [
      {
        data : 'Supervisor'
      },{
        data : 'Location'
      },{
        data : 'Name'
      },{
        data : 'Emp_Supervisor'
      },{
        data : 'Regular'
      },{
        data : 'ND'
      },{
        data : 'OT',
      },{
        data : 'DT',
      },{
        data : 'Total'
      },{
        data : 'OT_to_Date'
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
                Table_Overtime.draw( );
            }
        },{
            text : 'Get URL',
            action : function( e, dt, node, config ){
                var d = { };
                d.Supervisor         = $('input[name="ID"]').val( );
                d.Location           = $('input[name="State"]').val( );
                d.Name               = $('input[name="Customer"]').val( );
                d.Emp_Supervisor     = $('input[name="Unit"]').val( );
                d.Regular            = $('input[name="Type"]').val( );
                d.ND                 = $('input[name="Location"]').val( );
                d.OT                 = $('select[name="Route"]').val( );
                d.DT                 = $('select[name="Division"]').val( );
                d.Total              = $('select[name="Worked_On_Last"]').val( );
                d.OT_to_Date         = $('select[name="Worked_On_Last"]').val( );
                document.location.href = 'overtime.php?' + new URLSearchParams( d ).toString();
            }
        },
        { extend: 'create', editor: Editor_Overtime },
        { extend: 'edit',   editor: Editor_Overtime },
        { extend: 'remove', editor: Editor_Overtime },
        {
            text: 'Print',
            action: function ( e, dt, node, config ) {
                var rows = dt.rows( { selected : true } ).indexes( );
                var dte = dt.cells( rows, 0 ).data( ).toArray( );
                document.location.href = 'overtime.php?' + dte.join( ',' );
            }
        },
        'copy',
        'csv'
    ]
  } );
} );
