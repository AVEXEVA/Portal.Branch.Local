<script>
    function hrefCollection(){hrefRow('Table_Collections','invoice');}
    var Table_Collections = $('#Table_Collections').DataTable( {
      dom 	     : 'tp',
      processing : true,
      serverSide : true,
      responsive : true,
      autoWidth  : false,
      paging    : true,
      searching : false,
      ajax: {
        url     : 'bin/php/get/Collections2.php',
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
          d.Customer = $('input[name="Customer"]').val( );
          d.Location = $('input[name="Location"]').val( );
          d.Job = $('input[name="Job"]').val( );
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
function hrefCollections(){hrefRow('Table_Collections','invoice');}
$('Table#Table_Collections').on('draw.dt',function(){hrefCollections();});
function redraw( ){ Table_Collections.draw( ); }
</script>
