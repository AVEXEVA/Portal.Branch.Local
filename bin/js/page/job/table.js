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
          start : d.start,
          length : d.length,
          order : {
              column : d.order[0].column,
              dir : d.order[0].dir
          }
      };
      d.Search = $('input[name='Search']').val( );
      d.Customer = $('input[name='Customer']').val( );
      d.Location = $('input[name='Location']').val( );
      d.Job = $('input[name='Job']').val( );
      return d;
      }
},
columns: [
{
  data : 'ID' ,
},{
  data   : 'Name'
},{
  data    :  'Customer_ID'
},{
  data      : 'Customer_Name',
},{
  data      : 'Location_ID',
},{
  data      : 'Location_Name',
},{
  data      : 'Type',
},{
  data      : 'Status',
},{
]
} );
