<div class='popup'>
  <div class='panel-primary'>
    <div class='panel-heading'><h4>Select Job</h4></div>
    <div class='panel-body'>
      <table id='Table_Jobs' class='display' cellspacing='0' width='100%'>
        <thead>
          <th>ID</th>
          <th>Job</th>
          <th></th>
          <th>Date</th>
          <th></th>
        </thead>
      </table>
      <script>
      var Table_Jobs = $('#Table_Jobs').DataTable( {
    		"ajax": "cgi-bin/php/get/lookupJobs.php?<?php echo isset($_GET['Location']) ? "Loc={$_GET['Location']}" : '';?>",
    		"processing":true,
    		"serverSide":true,
        "columns": [
  				{
  				},{
  				},{
            "className":"hidden"
  				},{
  					render: function(data){if (data == null){return null;}else {return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}}
  				},{
            "className":"hidden"
  				}
  			],
    		"language":{
    			"loadingRecords":"<div style='text-align:center;'><div class='sk-cube-grid' style='display:inline-block;position:relative;';><div class='sk-cube sk-cube1' style='background-color:#cc0000'></div><div class='sk-cube sk-cube2' style='background-color:#cc0000'></div><div class='sk-cube sk-cube3' style='background-color:#cc0000'></div><div class='sk-cube sk-cube4' style='background-color:#cc0000'></div><div class='sk-cube sk-cube5' style='background-color:#cc0000'></div><div class='sk-cube sk-cube6' style='background-color:#cc0000'></div><div class='sk-cube sk-cube7' style='background-color:#cc0000'></div><div class='sk-cube sk-cube8' style='background-color:#cc0000'></div><div class='sk-cube sk-cube9' style='background-color:#cc0000'></div></div><div class='sk-cube-grid' style='display:inline-block;position:relative;top:-45px;'><div class='sk-cube sk-cube1' style='background-color:#00007f'></div><div class='sk-cube sk-cube2' style='background-color:#00007f'></div><div class='sk-cube sk-cube3' style='background-color:#00007f'></div><div class='sk-cube sk-cube4' style='background-color:#00007f'></div><div class='sk-cube sk-cube5' style='background-color:#00007f'></div><div class='sk-cube sk-cube6' style='background-color:#00007f'></div><div class='sk-cube sk-cube7' style='background-color:#00007f'></div><div class='sk-cube sk-cube8' style='background-color:#00007f'></div><div class='sk-cube sk-cube9' style='background-color:#00007f'></div></div><div class='sk-cube-grid' style='display:inline-block;position:relative;top:-84px;'><div class='sk-cube sk-cube1' style='background-color:gold'></div><div class='sk-cube sk-cube2' style='background-color:gold'></div><div class='sk-cube sk-cube3' style='background-color:gold'></div><div class='sk-cube sk-cube4' style='background-color:gold'></div><div class='sk-cube sk-cube5' style='background-color:gold'></div><div class='sk-cube sk-cube6' style='background-color:gold'></div><div class='sk-cube sk-cube7' style='background-color:gold'></div><div class='sk-cube sk-cube8' style='background-color:gold'></div><div class='sk-cube sk-cube9' style='background-color:gold'></div></div></div><div style='font-size:72px;text-align:center;' class='BankGothic'>Nouveau Elevator</div><div style='font-size:42px;text-align:center;'><i>Raising Your Life</i></div>"
    		},
    		"paging":true,
    		"select":true,
    		"initComplete":function(){},
    		"scrollY" : "600px",
    		"scrollCollapse":true,
    		"lengthChange": false,
    		"order": [[ 1, "ASC" ]],
        "drawCallback": function ( settings ) {
          selectJob(this.api());
        }
    	} );
      function selectJob(tbl){
        $("table#Table_Jobs tbody tr").each(function(){
          $(this).on('click',function(){
            var xDate = $("input[name='Date']").val();
            document.location.href="ticket.php?Date=" + xDate + "&Job=" + tbl.row(this).data()[0] + '<?php
              echo isset($_GET['Location']) ? "&Location={$_GET['Location']}" : '';
              echo isset($_GET['Unit']) ? "&Unit={$_GET['Unit']}" : '';
            ?>';
          });
        });
      }
      </script>
    </div>
    <div class='panel-heading'><button onClick='closePopup(this);' style='width:100%;height:50px;'>Close</button></div>
  </div>
</div>
