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
        d.Customer = $('input[name="Customer"]').val( );
        d.Location = $('input[name="Location"]').val( );
        d.Type = $('input[name="Type"]').val( );
        d.Status = $('select[name="Status"]').val( );
        return d;
      }
    },
    columns   : [
      {
          className : 'ID',
          data : 'ID',
          render : function( data, type, row, meta ){
              switch( type ){
                  case 'display' :
                      return  row.ID !== null 
                          ?   "<div class='row'>" + 
                                  "<div class='col-12'><a href='job`.php?ID=" + row.ID + "'><i class='fa fa-suitcase fa-fw fa-1x'></i> Job #" + row.ID + "</a></div>" + 
                              "</div>"
                          :   null;
                  default :
                      return data;
              }

          }
      },{
        data : 'Name'
      },{
        data : 'Date'
      },{
          data : 'Customer_ID',
          render : function( data, type, row, meta ){
              switch( type ){
                  case 'display' :
                      return  row.Customer_ID !== null 
                          ?   "<div class='row'>" + 
                                  "<div class='col-12'><a href='customer.php?ID=" + row.Customer_ID + "'><i class='fa fa-link fa-fw fa-1x'></i>" + row.Customer_Name + "</a></div>" + 
                              "</div>"
                          :   null;
                  default :
                      return data;
              }

          }
      },{
          data : 'Location_ID',
          render : function( data, type, row, meta ){
              switch( type ){
                  case 'display' :
                      return  row.Location_ID !== null 
                          ?   "<div class='row'>" + 
                                  "<div class='col-12'><a href='location.php?ID=" + row.Location_ID + "'><i class='fa fa-building fa-fw fa-1x'></i>" + row.Location_Name + "</a></div>" + 
                                  "<div class='col-12'>" +
                                      "<div class='row'>" +
                                          "<div class='col-12'><i class='fa fa-map-signs fa-fw fa-1x'></i>" + row.Location_Street + "</div>" + 
                                          "<div class='col-12'>" + row.Location_City + ", " + row.Location_State + " " + row.Location_Zip + "</div>" + 
                                      "</div>" +
                                  "</div>" +  
                              "</div>"
                          :   null;
                  default :
                      return data;
              }

          }
      },{
        data : 'Type'
      },{
        data : 'Status',
        render : function( data, type, row, meta ){
          switch( type ){
            case 'display' :
              switch( data ){
                case 0: return 'Open';
                case 1: return 'Closed';
                case 2: return 'On Hold';
              }
            default :
              return data;
          }
        }
      },{
          data : 'Tickets',
          render : function( data, type, row, meta ){
              switch( type ){
                  case 'display' :
                      return  row.Tickets !== null 
                          ?   "<div class='row'>" + 
                                  "<div class='col-12'><a href='tickets.php?Job=" + row.Name + "'><i class='fa fa-ticket fa-fw fa-1x'></i>" + row.Tickets + " tickets</a></div>" + 
                              "</div>"
                          :   null;
                  default :
                      return data;
              }

          }
      },{
          data : 'Invoices',
          render : function( data, type, row, meta ){
              switch( type ){
                  case 'display' :
                      return  row.Invoices !== null 
                          ?   "<div class='row'>" + 
                                  "<div class='col-12'><a href='invoices.php?Job=" + row.Name + "'><i class='fa fa-stack-overflow fa-fw fa-1x'></i>" + row.Invoices + " invoices</a></div>" + 
                              "</div>"
                          :   null;
                  default :
                      return data;
              }

          }
      }
    ],
    initComplete : function( ){
          $("div.search").html( "<input type='text' name='Search' placeholder='Search' />" );//onChange='$(\"#Table_Tickets\").DataTable().ajax.reload( );'
          $('input.date').datepicker( { } );
          $('input.time').timepicker( {  timeFormat : 'h:i A' } );
          //search( this );
          $( '.redraw' ).bind( 'change', function(){ Table_Units.draw(); });
      },
      buttons: [
          {
              text: 'Reset Search',
              action: function ( e, dt, node, config ) {
                  $( 'input, select' ).each( function( ){
                      $( this ).val( '' );
                  } );
                  Table_Units.draw( );
              }
          },{
              text : 'Get URL',
              action : function( e, dt, node, config ){
                  var d = { };
                  d.Search = $('input[name="Search"]').val( );
                  d.ID = $('input[name="ID"]').val( );
                  d.Name = $('input[name="Name"]').val( );
                  d.Customer = $('input[name="Customer"]').val( );
                  d.Location = $('input[name="Location"]').val( );
                  d.Type = $('input[name="Type"]').val( );
                  d.Status = $('select[name="Status"]').val( );
                  document.location.href = 'jobs.php?' + new URLSearchParams( d ).toString();
              }
          },
          { extend: 'create', editor: Editor_Jobs },
          { extend: 'edit',   editor: Editor_Jobs },
          { extend: 'remove', editor: Editor_Jobs },
          'copy',
          'csv'
      ]
    } );
} );