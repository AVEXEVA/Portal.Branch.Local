<div class='popup'>
  <div class='panel-primary'>
    <div class='panel-heading'><h4>Select Customer</h4></div>
    <div class='panel-body'>
      <table id='Table_Customers' class='display' cellspacing='0' width='100%' style='font-size:12px;'>
        <thead>
          <th title="Customer's ID"></th>
          <th title="Customer's Name"></th>
        </thead>
      </table>
      <script>
      var Table_Customers = $('#Table_Customers').DataTable( {
        "processing":true,
        "serverSide":true,
        "ajax": "cgi-bin/php/get/Customers.php",
        "order": [[ 1, "asc" ]],
        "columns": [
          {
  					"className":"hidden"
  				},{

  				}
        ],
        "language":{
  				"loadingRecords":"<div style='text-align:center;'><div class='sk-cube-grid' style='display:inline-block;position:relative;';><div class='sk-cube sk-cube1' style='background-color:#cc0000'></div><div class='sk-cube sk-cube2' style='background-color:#cc0000'></div><div class='sk-cube sk-cube3' style='background-color:#cc0000'></div><div class='sk-cube sk-cube4' style='background-color:#cc0000'></div><div class='sk-cube sk-cube5' style='background-color:#cc0000'></div><div class='sk-cube sk-cube6' style='background-color:#cc0000'></div><div class='sk-cube sk-cube7' style='background-color:#cc0000'></div><div class='sk-cube sk-cube8' style='background-color:#cc0000'></div><div class='sk-cube sk-cube9' style='background-color:#cc0000'></div></div><div class='sk-cube-grid' style='display:inline-block;position:relative;top:-45px;'><div class='sk-cube sk-cube1' style='background-color:#00007f'></div><div class='sk-cube sk-cube2' style='background-color:#00007f'></div><div class='sk-cube sk-cube3' style='background-color:#00007f'></div><div class='sk-cube sk-cube4' style='background-color:#00007f'></div><div class='sk-cube sk-cube5' style='background-color:#00007f'></div><div class='sk-cube sk-cube6' style='background-color:#00007f'></div><div class='sk-cube sk-cube7' style='background-color:#00007f'></div><div class='sk-cube sk-cube8' style='background-color:#00007f'></div><div class='sk-cube sk-cube9' style='background-color:#00007f'></div></div><div class='sk-cube-grid' style='display:inline-block;position:relative;top:-84px;'><div class='sk-cube sk-cube1' style='background-color:gold'></div><div class='sk-cube sk-cube2' style='background-color:gold'></div><div class='sk-cube sk-cube3' style='background-color:gold'></div><div class='sk-cube sk-cube4' style='background-color:gold'></div><div class='sk-cube sk-cube5' style='background-color:gold'></div><div class='sk-cube sk-cube6' style='background-color:gold'></div><div class='sk-cube sk-cube7' style='background-color:gold'></div><div class='sk-cube sk-cube8' style='background-color:gold'></div><div class='sk-cube sk-cube9' style='background-color:gold'></div></div></div><div style='font-size:72px;text-align:center;' class='BankGothic'>Nouveau Elevator</div><div style='font-size:42px;text-align:center;'><i>Raising Your Life</i></div>"
  			},
  			"paging":true,
  			"initComplete":function(){},
  			"scrollY" : "600px",
  			"scrollCollapse":true,
  			"lengthChange": false,
        "drawCallback": function ( settings ) {
          selectCustomer(this.api());
        }
      });
      function selectCustomer(tbl){
        $("table#Table_Customers tbody tr").each(function(){
          $(this).on('click',function(){
            document.location.href='location.php?Customer=' + tbl.row(this).data()[0];
          });
        });
      }
      </script>
    </div>
    <div class='panel-heading'><button onClick='closePopup(this);' style='width:100%;height:50px;'>Close</button></div>
  </div>
</div>
