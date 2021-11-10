$( document ).ready( function( ){
var Table_Leads = $('#Table_Leads').DataTable( {
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
url     : 'bin/php/get/leads.php',
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
      d.Address = $("input[name='Address']").val( );
      d.City = $("input[name='City']").val( );
      d.State = $("input[name='State']").val( );
      d.Zip = $("input[name='Zip']").val( );
      d.Customer = $("input[name='Customer']").val( );
      d.Violations = $("input[name='Violations']").val( );
      d.Invoices = $("input[name='Invoices']").val( );
      d.Owner = $("input[name='Owner']").val( );
      return d;
      }
},
columns: [
{
  data : 'ID' ,
},{
  data   : 'Name'
},{
  data    :  'Address'
},{
  data      : 'City',
},{
  data      : 'State',
},{
  data      : 'Zip',
},{
  data      : 'Customer',
},{
  data      : 'Violations',
},{
  data      : 'Invoices',
},{
  data      : 'Owner',
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
