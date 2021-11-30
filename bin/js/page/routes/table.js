$( document ).ready( function( ){
  var Editor_Routes = new $.fn.dataTable.Editor( {
      idSrc    : 'ID',
      ajax     : 'index.php',
      table    : '#Table_Routes'
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
      select         : {
        style : 'multi',
        selector : 'td.ID'
      },
      ajax: {
        url     : 'bin/php/get/Routes.php',
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
          d.Name = $('input[name="Name"]').val( );
          d.Person = $('input[name="Person"]').val( );
          return d;
        },
      },
      columns : [
          {
          className : 'ID',
          data : 'ID',
          render : function( data, type, row, meta ){
                  switch( type ){
                      case 'display' :
                          return  row.ID !== null
                              ?   "<div class='row'>" +
                                      "<div class='col-12'><a href='route.php?ID=" + row.ID + "'><i class='fa fa-folder-open fa-fw fa-1x'></i> Route #" + row.ID + "</a></div>" +
                                  "</div>"
                              :   null;
                      default :
                          return data;
                  }

              }
          },{
            data : 'Name'
          },{
            data : 'Person'
          },{
            data : 'Locations'
          },{
            data : 'Units'
          },
          {
           data : 'Violation' 
         },
         {
          data : 'Assigned' ,
          render : function( data, type, row, meta ){
          switch(data){
            case '2': return "<div class='row'><div class='col-12'><a href='ticket.php'><i class='fa fa-folder-open fa-fw fa-1x'></i>Ticket #En Route</a><div></div>";
            case '3': return "<div class='row'><div class='col-12'><a href='ticket.php'><i class='fa fa-folder-open fa-fw fa-1x'></i>Ticket #OnSite</a><div></div>";
           default :
             switch ( type ){
            case 'display' :
              return (row.start !== null && row.end ===null)
                ? "<div class='row'>" +
                "<div class='col-12'></div>Active</div></div>"
                :  "<div class='row'><div class='col-12'>Offline</div>"
            default :
              return data;

          }
          }
         }
         }
      ],
      order : [ [ 1, 'asc' ] ],
      language : {
        loadingRecords : ''
      },
      buttons: [
        {
          text: 'Reset Search',
          className : 'form-control',
          action: function ( e, dt, node, config ) {
              $( 'input, select' ).each( function( ){
                  $( this ).val( '' );
              } );
              Table_Customers.draw( );
          }
        },{
          text : 'Get URL',
          className : 'form-control',
          action : function( e, dt, node, config ){
              d = { }
              d.Name = $('input[name="Name"]').val( );
              d.Person = $('select[name="Person"]').val( );
              document.location.href = 'routes.php?' + new URLSearchParams( d ).toString();
          }
        },{
          text : 'Create',
          className : 'form-control',
          action : function( e, dt, node, config ){
              document.location.href='route.php';
          }
        },{
          text : 'Delete',
          className : 'form-control',
          action : function( e, dt, node, config ){
            var rows = dt.rows( { selected : true } ).indexes( );
            var dte = dt.cells( rows, 0 ).data( ).toArray( );
            $.ajax ({
              url    : 'bin/php/post/route.php',
              method : 'POST',
              data   : {
                action : 'delete',
                data : dte
              },
              success : function(response){
                Table_Routes.draw();
              }
            })
          }
        },{
            extend : 'print',
            text : 'Print',
            className : 'form-control'
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
        $( '.redraw' ).bind( 'change', function(){ Table_Routes.draw(); });
    }
  } );
} );
