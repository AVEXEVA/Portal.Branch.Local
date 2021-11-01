<div class='popup'>
  <div class='panel-primary'>
    <div class='panel-heading'><h4>Select Unit</h4></div>
    <div class='panel-body'>
      <table id='Table_Units' class='display' cellspacing='0' width='100%' style='font-size:12px;'>
        <thead>
          <th></th>
          <th></th>
          <th>State</th>
          <th>Label</th>
          <th>Type</th>
          <th>Status</th>
        </thead>
      </table>
      <script>
      var Table_Units = $('#Table_Units').DataTable( {
    		"ajax": "cgi-bin/php/get/Units.php?<?php echo isset($_GET['Location']) ? "Loc={$_GET['Location']}" : '';?>",
    		"processing":true,
    		"serverSide":true,
    		"columns": [
    			{
    				"className":"hidden"
    			},{
    				"className":"hidden"
    			},{
    				label: "State",
    				name: "State",
            render:function(data){
              if(data == ''){return 'N/A';}
              return data;
            }
    			},{
    			},{
    			},{
    				render:function(data){
    					switch(data){
    						case 0:return 'Active';
    						case 1:return 'Inactive';
    						case 2:return 'Demolished';
    						case 3:return 'Dismantled';
    						case 4:return 'Removed';
    						case 5:return 'No Jurisdiction';
    						default:return 'Error';
    					}
    				}
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
          selectUnit(this.api());
        }
    	} );
      function selectUnit(tbl){
        var Required = $("input[name='Required']").val();
        $("table#Table_Units tbody tr").each(function(){
          $(this).on('click',function(){
            document.location.href='purchase-requisition.php?Unit=' + tbl.row(this).data()[0] + '<?php
              echo isset($_GET['Location']) ? "&Location={$_GET['Location']}" : '';
              echo isset($_GET['DropOff']) ? "&DropOff={$_GET['DropOff']}" : '';
              echo isset($_GET['Job']) ? "&Job={$_GET['Job']}" : '';
            ?>' + "&Required=" + Required;
          });
        });
      }
      </script>
    </div>
    <div class='panel-heading'><button onClick='closePopup(this);' style='width:100%;height:50px;'>Close</button></div>
  </div>
</div>
