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
      {
          className : 'ID',
          data : 'ID',
          render : function( data, type, row, meta ){
              switch( type ){
                  case 'display' :
                      return  row.ID !== null
                          ?   "<div class='row'>" +
                                  "<div class='col-12'><a href='job.php?ID=" + row.ID + "'><i class='fa fa-suitcase fa-fw fa-1x'></i> Job #" + row.ID + "</a></div>" +
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
                document.location.href = 'jobs.php?' + new URLSearchParams( d ).toString();
            }
          },{
          text : 'Create',
          className: 'form-control',
          action : function( e, dt, node, config ){
              document.location.href='job.php';}
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
      ],
  } );
} );
