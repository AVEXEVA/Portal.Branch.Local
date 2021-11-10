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
      d.Search = $("input[name='Search']").val( );
      d.Customer = $("input[name='Customer']").val( );
      d.Location = $("input[name='Location']").val( );
      d.Job = $("input[name='Job']").val( );
      return d;
      }
},
columns: [
{
  data : 'Search' ,
},{
  data   : 'Customer'
},{
  data    :  'Location'
},{
  data      : 'Job',
},{
  data      : 'Suggestion',
},{
  data      : 'Resolution',
},{
  data      : 'Fixed',
},{
]
i nitComplete : function( ){
    $("div.search").html( "<input type='text' name='Search' placeholder='Search' />" );
    $('input.date').datepicker( { } );
    $('input.time').timepicker( {  timeFormat : 'h:i A' } );
    search( this );
    $( '.redraw' ).bind( 'change', function(){ Table_Locations.draw(); });
},
buttons: [
    {
        text: 'Reset Search',
        action: function ( e, dt, node, config ) {
            $( 'input, select' ).each( function( ){
                $( this ).val( '' );
            } );
            Table_Locations.draw( );
        }
    },
    { extend: 'create', editor: Editor_Locations },
    { extend: 'edit',   editor: Editor_Locations },
    { extend: 'remove', editor: Editor_Locations },
    'print',
    'copy',
    'csv'
]
} );
