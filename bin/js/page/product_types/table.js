$( document ).ready( function( ){
  var Editor_Product_Types = new $.fn.dataTable.Editor( {
      idSrc    : 'ID',
      ajax     : 'index.php',
      table    : '#Table_Product_Types'
  } );
  var Table_Product_Types = $('#Table_Legal').DataTable( {
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
        url     : 'bin/php/get/product_types.php',
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
          d.Description = $('input[name="Description"]').val( );
          d.Category = $('input[name="Category"]').val( );
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
                                  "<div class='col-12'><a href='legal.php?ID=" + row.ID + "'><i class='fa fa-folder-open fa-fw fa-1x'></i> Legal #" + row.ID + "</a></div>" +
                              "</div>"
                          :   null;
                  default :
                      return data;
              }
          }
      },{
        data : 'Name'
      },{
        data : 'Description'
      },{
        data : 'Category'
      },{
    ],
    initComplete : function( ){
        $("div.search").html( "<input type='text' name='Search' placeholder='Search' />" );//onChange='$(\"#Table_Tickets\").DataTable().ajax.reload( );'
        $('input.date').datepicker( { } );
        $('input.time').timepicker( {  timeFormat : 'h:i A' } );
        //search( this );
        $( '.redraw' ).bind( 'change', function(){ Table_Product_Types.draw(); });
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
                d.ID             = $('input[name="ID"]').val( );
                d.Name         = $('input[name="Name"]').val( );
                d.Description       = $('input[name="Description"]').val( );
                d.Category       = $('input[name="Category"]').val( );
                document.location.href = 'maintenances.php?' + new URLSearchParams( d ).toString();
            }
        },
        { extend: 'create', editor: Editor_Product_Types },
        { extend: 'edit',   editor: Editor_Product_Types },
        { extend: 'remove', editor: Editor_Product_Types },
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
