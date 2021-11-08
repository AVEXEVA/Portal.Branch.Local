<script>
    function hrefInvoice(){hrefRow('Table_Invoices','invoice');}
var Table_Invoices = $('#Table_Invoices').DataTable( {
  dom 	   : 'tp',
      processing : true,
      serverSide : true,
      responsive : true,
      autoWidth : false,
  paging    : true,
  searching : false,
  ajax: {
    url     : 'bin/php/get/Invoices2.php',
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
      data : 'Customer'
    },{
      data : 'Location'
    },{
      data : 'Job'
    },{
      data : 'Type'
    },{
      data   : 'Date'
    },{
      data   : 'Due'
    },{
      data      : 'Original',
      className :'sum'
    },{
      data      : 'Balance',
      className : 'sum'
    },{
      data : 'Description'
    }
  ],
  language:{
    loadingRecords : "<div style='text-align:center;'><div class='sk-cube-grid' style='display:inline-block;position:relative;';><div class='sk-cube sk-cube1' style='background-color:#cc0000'></div><div class='sk-cube sk-cube2' style='background-color:#cc0000'></div><div class='sk-cube sk-cube3' style='background-color:#cc0000'></div><div class='sk-cube sk-cube4' style='background-color:#cc0000'></div><div class='sk-cube sk-cube5' style='background-color:#cc0000'></div><div class='sk-cube sk-cube6' style='background-color:#cc0000'></div><div class='sk-cube sk-cube7' style='background-color:#cc0000'></div><div class='sk-cube sk-cube8' style='background-color:#cc0000'></div><div class='sk-cube sk-cube9' style='background-color:#cc0000'></div></div><div class='sk-cube-grid' style='display:inline-block;position:relative;top:-45px;'><div class='sk-cube sk-cube1' style='background-color:#00007f'></div><div class='sk-cube sk-cube2' style='background-color:#00007f'></div><div class='sk-cube sk-cube3' style='background-color:#00007f'></div><div class='sk-cube sk-cube4' style='background-color:#00007f'></div><div class='sk-cube sk-cube5' style='background-color:#00007f'></div><div class='sk-cube sk-cube6' style='background-color:#00007f'></div><div class='sk-cube sk-cube7' style='background-color:#00007f'></div><div class='sk-cube sk-cube8' style='background-color:#00007f'></div><div class='sk-cube sk-cube9' style='background-color:#00007f'></div></div><div class='sk-cube-grid' style='display:inline-block;position:relative;top:-84px;'><div class='sk-cube sk-cube1' style='background-color:gold'></div><div class='sk-cube sk-cube2' style='background-color:gold'></div><div class='sk-cube sk-cube3' style='background-color:gold'></div><div class='sk-cube sk-cube4' style='background-color:gold'></div><div class='sk-cube sk-cube5' style='background-color:gold'></div><div class='sk-cube sk-cube6' style='background-color:gold'></div><div class='sk-cube sk-cube7' style='background-color:gold'></div><div class='sk-cube sk-cube8' style='background-color:gold'></div><div class='sk-cube sk-cube9' style='background-color:gold'></div></div></div><div style='font-size:72px;text-align:center;' class='BankGothic'>Nouveau Elevator</div><div style='font-size:42px;text-align:center;'><i>Raising Your Life</i></div>"
  }
} );
$('#Table_Invoices tbody').on('click', 'td.details-control', function () {
  var tr = $(this).closest('tr');
  var row = Table_Invoices.row( tr );
  if ( row.child.isShown() ) {
    row.child.hide();
    tr.removeClass('shown');
  }
  else {
    row.child( formatInvoice(row.data()) ).show();
    tr.addClass('shown');
  }
} );
function hrefInvoices(){hrefRow('Table_Invoices','invoice');}
$('Table#Table_Invoices').on('draw.dt',function(){hrefInvoices();});
function redraw( ){ Table_Invoices.draw( ); }
</script>
