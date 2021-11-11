$( document ).ready( function( ){
  var Editor_Maintenance = new $.fn.dataTable.Editor( {
      idSrc    : 'ID',
      ajax     : 'index.php',
      table    : '#Table_Maintenance'
  } );
  var Table_Maintenance = $('#Table_Maintenance').DataTable( {
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
        url     : 'bin/php/get/maintenances.php',
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
          d.State = $('input[name="State"]').val( );
          d.Unit = $('input[name="Unit"]').val( );
          d.Type = $('input[name="Type"]').val( );
          d.Location = $('input[name="Location"]').val( );
          d.Route = $('input[name="Route"]').val( );
          d.Division = $('input[name="Division"]').val( );
          d.Worked_On_Last = $('input[name="Worked_On_Last"]').val( );
          return d;
      }
    },
    columns: [
      {
          className : 'ID',
          data : 'ID',
          render : function( data, type, row, meta ){
              switch( type ){
                  case 'display' :
                      return  row.ID !== null
                          ?   "<div class='row'>" +
                                  "<div class='col-12'><a href='collection.php?ID=" + row.ID + "'><i class='fa fa-folder-open fa-fw fa-1x'></i> Invoice #" + row.ID + "</a></div>" +
                              "</div>"
                          :   null;
                  default :
                      return data;
              }
          }
      },{
        data : 'State'
      },{
        data : 'Unit'
      },{
        data : 'Type'
      },{
        data : 'Location',
      },{
        data : 'Route',
      },{
        data : 'Division'
      },{
        data : 'Worked_On_Last'
    ],
    initComplete : function( ){
        $("div.search").html( "<input type='text' name='Search' placeholder='Search' />" );//onChange='$(\"#Table_Tickets\").DataTable().ajax.reload( );'
        $('input.date').datepicker( { } );
        $('input.time').timepicker( {  timeFormat : 'h:i A' } );
        //search( this );
        $( '.redraw' ).bind( 'change', function(){ Table_Maintenance.draw(); });
    },
            text: 'Reset Search',
            action: function ( e, dt, node, config ) {
                $( 'input, select' ).each( function( ){
                    $( this ).val( '' );
                } );
                Table_Maintenance.draw( );
            }
        },{
            text : 'Get URL',
            action : function( e, dt, node, config ){
                var d = { };
                d.ID             = $('input[name="ID"]').val( );
                d.State         = $('input[name="State"]').val( );
                d.Customer       = $('input[name="Customer"]').val( );
                d.Unit       = $('input[name="Unit"]').val( );
                d.Type           = $('input[name="Type"]').val( );
                d.Location            = $('input[name="Location"]').val( );
                d.Route           = $('select[name="Route"]').val( );
                d.Division          = $('select[name="Division"]').val( );
                d.Worked_On_Last         = $('select[name="Worked_On_Last"]').val( );
                document.location.href = 'maintenances.php?' + new URLSearchParams( d ).toString();
            }
        },
        { extend: 'create', editor: Editor_Maintenance },
        { extend: 'edit',   editor: Editor_Maintenance },
        { extend: 'remove', editor: Editor_Maintenance },
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
