$( document ).ready( function( )  {
  var Editor_Job = new $.fn.dataTable.Editor( {
    idSrc    : 'ID',
    ajax     : 'index.php',
    table    : '#Table_Job'
  } );
var Table_Job = $('#Table_Job').DataTable( {
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
//stateSave      : true,
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
url     : 'bin/php/get/Job.php',
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
      d.ID = $('input[name='ID']').val( );
      d.Name = $('input[name='Name']').val( );
      d.Date = $('input[name='Date']').val( );
      d.Customer_ID = $('input[name='Customer_ID']').val( );
      d.Customer_Name = $('input[name='Customer_Name']').val( );
      d.Locaton_ID = $('input[name='Location_ID']').val( );
      d.Location_Name = $('input[name='Location_Name']').val( );
      d.Location_Street = $('input[name='Location_Street']').val( );
      d.Location_State = $('input[name='Location_State']').val( );
      d.Location_Zip = $('input[name='Location_Zip']').val( );
      d.Type = $('input[name='Type']').val( );
      d.Status = $('input[name='Status']').val( );
      d.Tickets = $('input[name='Tickets']').val( );
      d.Invoices = $('input[name='Invoices']').val( );
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
                              "<div class='col-12'><a href='job`.php?ID=" + row.ID + "'><i class='fa fa-suitcase fa-fw fa-1x'></i> Job #" + row.ID + "</a></div>" +
                          "</div>"
                      :   null;
              default :
                  return data;
          }
      }
},{
  data   : 'Name'
},{
  data   : 'Date'
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
  data      : 'Type',
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
  $( '.redraw' ).bind( 'change', function(){ Table_Job.draw(); });
},
buttons: [
  {
      text: 'Reset Search',
      action: function ( e, dt, node, config ) {
          $( 'input, select' ).each( function( ){
              $( this ).val( '' );
          } );
          Table_Job.draw( );
      }
  },{
      text : 'Get URL',
      action : function( e, dt, node, config ){
          var d = { };
          d.ID = $('input[name='ID']').val( );
          d.Name = $('input[name='Name']').val( );
          d.Date = $('input[name='Date']').val( );
          d.Customer_ID = $('input[name='Customer_ID']').val( );
          d.Customer_Name = $('input[name='Customer_Name']').val( );
          d.Locaton_ID = $('input[name='Location_ID']').val( );
          d.Location_Name = $('input[name='Location_Name']').val( );
          d.Location_Street = $('input[name='Location_Street']').val( );
          d.Location_State = $('input[name='Location_State']').val( );
          d.Location_Zip = $('input[name='Location_Zip']').val( );
          d.Type = $('input[name='Type']').val( );
          d.Status = $('input[name='Status']').val( );
          d.Tickets = $('input[name='Tickets']').val( );
          d.Invoices = $('input[name='Invoices']').val( );
          document.location.href = 'jobs.php?' + new URLSearchParams( d ).toString();
      }
  },
  { extend: 'create', editor: Editor_Job },
  { extend: 'edit',   editor: Editor_Job },
  { extend: 'remove', editor: Editor_Job },
  'copy',
  'csv'
]
} );
} );
