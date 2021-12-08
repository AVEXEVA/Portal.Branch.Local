$( document ).ready( function( ){
var Table_Divisions = $('#Table_Divisions').DataTable( {
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
url     : 'bin/php/get/divisions.php',
data : function( d ){
      d = {
          start : d.start,
          length : d.length,
          order : {
              column : d.order[0].column,
              dir : d.order[0].dir
          }
      };
      d.ID = $("input[name='ID']").val( );
      d.Name = $("input[name='Name']").val( );
      d.Locations = $("input[name='Locations']").val( );
      d.Units = $("input[name='Units']").val( );
      d.Violations = $("input[name='Violations']").val( );
      d.Tickets = $("input[name='Tickets']").val( );
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
                            "<div class='col-12'><a href='divisions.php?ID=" + row.ID + "'><i class='fa fa-folder-open fa-fw fa-1x'></i> Customer #" + row.ID + "</a></div>" +
                        "</div>"
                    :   null;
            default :
                return data;
          }
        }
        },{
        data : 'Name',
        render : function( data, type, row, meta ){
            switch( type ){
                case 'display' :
                    return  row.ID !== null
                        ?   "<div class='row'>" +
                                "<div class='col-12'><a href='divisions.php?ID=" + row.ID + "'><i class='fa fa-link fa-fw fa-1x'></i> " + row.Name + "</a></div>" +
                            "</div>"
                        :   null;
                default :
                    return data;
              }
        }
        },{
        data : 'Locations',
        render : function( data, type, row, meta ){
            switch( type ){
                case 'display' :
                    return  row.Locations !== null
                        ?   "<div class='row'>" +
                                "<div class='col-12'><a href='locations.php?Division=" + row.Name + "'><i class='fa fa-link fa-building fa-fw fa-1x'></i> " + row.Locations + " locations</a></div>" +
                            "</div>"
                        :   null;
                default :
                    return data;
            }
        }
      },{
        data : 'Units',
        render : function( data, type, row, meta ){
            switch( type ){
                case 'display' :
                    return  row.Units !== null
                        ?   "<div class='row'>" +
                                "<div class='col-12'><a href='units.php?Division=" + row.Name + "'><i class='fa fa-cogs fa-fw fa-1x'></i> " + row.Units + " units</a></div>" +
                            "</div>"
                        :   null;
                default :
                    return data;
            }
        }
      },{
          data : 'Violations',
          render : function( data, type, row, meta ){
              switch( type ){
                  case 'display' :
                      return  row.Tickets !== null
                          ?   "<div class='row'>" +
                                  "<div class='col-12'><a href='violations.php?Division=" + row.Name + "'><i class='fa fa-warning fa-fw fa-1x'></i> " + row.Violations + " violations</a></div>" +
                              "</div>"
                          :   null;
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
                                    "<div class='col-12'><a href='tickets.php?Division=" + row.Name + "'><i class='fa fa-ticket fa-fw fa-1x'></i> " + row.Tickets + " tickets</a></div>" +
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
      Table_Divisions.draw( );
    }
  },{
    text : 'Get URL',
    className: 'form-control',
    action : function( e, dt, node, config ){
      var d = { };
      d.ID = $("input[name='ID']").val( );
      d.Name = $("input[name='Name']").val( );
      d.Date = $("input[name='Date']").val( );
      d.Customer = $("input[name='Customer']").val( );
      d.Locaton = $("input[name='Location']").val( );
      d.Type = $("select[name='Type']").val( );
      d.Status = $("select[name='Status']").val( );
      d.Tickets = $("input[name='Tickets']").val( );
      d.Invoices = $("input[name='Invoices']").val( );
      document.location.href = 'divisions.php?' + new URLSearchParams( d ).toString();
    }
  },{
    text : 'Create',
    className: 'form-control',
    action : function( e, dt, node, config ){
      document.location.href='division.php';
    }
  },{
    text: 'Print',
    className: 'form-control',
    action: function ( e, dt, node, config ) {
        var rows = dt.rows( { selected : true } ).indexes( );
        var dte = dt.cells( rows, 0 ).data( ).toArray( );
        document.location.href = 'divisions.php?Divisions=' + dte.join( ',' );
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
]
} );
} );
