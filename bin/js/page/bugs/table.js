$( document ).ready( function( ){
var Table_Bugs = $('#Table_bugs').DataTable( {
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
url     : 'bin/php/get/Bugs.php',
data : function( d ){
      d = {
          draw  : d.draw,
          start : d.start,
          length : d.length,
          order : {
              column : d.order[0].column,
              dir : d.order[0].dir
          }
      };
      d.ID = $("input[name='ID']").val( );
      d.Date = $("input[name='Date']").val( );
      d.Description = $("input[name='Description']").val( );
      d.Severity = $("input[name='Severity']").val( );
      d.Suggestion = $("input[name='Suggestion']").val( )
      d.Resolution = $("input[name='Resolution']").val( )
      d.Fixed = $("input[name='Fixed']").val( )
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
                              "<div class='col-12'><a href='customer.php?ID=" + row.ID + "'><i class='fa fa-folder-open fa-fw fa-1x'></i> Customer #" + row.ID + "</a></div>" +
                          "</div>"
                      :   null;
              default :
                  return data;
          }

      }
    },{
        data : 'Date',
        render: function( data, type, row, meta ){
            switch( type ){
                case 'display':
                    return row.Date !== null
                        ?   "<div class='row'>" +
                                "<div class='col-12'><i class='fa fa-calendar fa-fw fa-1x'></i>" + row.Date + "</div>" +
                            "</div>"
                        :   null;
                    default :
                        return data;

                    }
                }
          },{
            data    :  'Description'
          },{
            data      : 'Severity',
          },{
            data      : 'Suggestion',
          },{
            data      : 'Resolution',
          },{
            data      : 'Fixed',
          },{
]
initComplete : function( ){
    $("div.search").html( "<input type='text' name='Search' placeholder='Search' />" );//onChange='$(\"#Table_Tickets\").DataTable().ajax.reload( );'
    $('input.date').datepicker( { } );
    $('input.time').timepicker( {  timeFormat : 'h:i A' } );
    //search( this );
    $( '.redraw' ).bind( 'change', function(){ Table_Customers.draw(); });
}
} );
} );
