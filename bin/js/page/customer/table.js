$( document ).ready( function( ){
var Table_Customers = $('#Table_Customers').DataTable( {
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
url     : 'bin/php/get/customer.php',
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
      d.Status = $("input[name='Status']").val( );
      d.Location = $("input[name='Location']").val( );
      d.Units = $("input[name='Units']").val( );
      d.Jobs = $("input[name='Job']").val( );
      d.Tickets = $("input[name='Tickets']").val( );
      d.Violations = $("input[name='Violations']").val( );
      d.Invoices = $("input[name='Invoices']").val( );
      return d;
      }
},
columns: [
{
  data : 'ID' ,
},{
  data   : 'Name'
},{
  data    :  'Status'
},{
  data      : 'Location',
},{
  data      : 'Units',
},{
  data      : 'Jobs',
},{
  data      : 'Tickets',
},{
  data      : 'Violations',
},{
  data      : 'Invoices',
},{
]
}
} );

initComplete : function( ){
    $("div.search").html( "<input type='text' name='Search' placeholder='Search' />" );//onChange='$(\"#Table_Contacts\").DataTable().ajax.reload( );'
    $('input.date').datepicker( { } );
    $('input.time').timepicker( {  timeFormat : 'h:i A' } );
    search( this );
    $( '.redraw' ).bind( 'change', function(){ Table_Contacts.draw(); });
},
buttons: [
    {
        text: 'Reset Search',
        action: function ( e, dt, node, config ) {
            $( 'input, select' ).each( function( ){
                $( this ).val( '' );
            } );
            Table_Contacts.draw( );
        }
    },
    { extend: 'create', editor: Editor_Contacts },
    { extend: 'edit',   editor: Editor_Contacts },
    { extend: 'remove', editor: Editor_Contacts },
    'print',
    'copy',
    'csv'
]
} );
}
