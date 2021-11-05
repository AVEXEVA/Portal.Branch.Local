<?php 
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( [ 'read_and_close' => true ] ); 
    require( '/var/www/portal.live.local/html/cgi-bin/php/index.php' );
}
?><div class='popup'>
  <div class='panel-primary'>
    <div class='panel-heading'><h4><?php $Icons->Unit( 1 );?> Unit</h4></div>
    <div class='panel-body'>
      <div class='row form-group'>
        <label class='col-auto border-bottom v1'>Search:</label>
        <div class='col-auto padding v1'><input type='text' name='Search'  style='width: 100%;' onChange='redraw( );' /></div>
      </div>
    </div>
    <div class='panel-body'>
      <table id='Table_Units' class='display' cellspacing='0' width='100%' style='font-size:12px;'>
        <thead>
          <th title='ID'>ID</th>
          <th title='City ID'>City ID</th>
          <th title='Building ID'>Building ID</th>
        </thead>
      </table>
      <script>
      var Table_Units = $('#Table_Units').DataTable( {
        dom      : 'tp',
        processing : true,
        serverSide : true,
        responsive : true,
        autoWidth  : false,
        paging     : true,
        searching  : false,
        dom        : 'tp',
        ajax  : {
          url : 'cgi-bin/php/get/lookupUnits.php',
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
            d.Location = <?php echo isset( $_GET[ 'Location' ] ) && is_numeric( $_GET[ 'Location' ] ) && $_GET[ 'Location' ] > 0 ? $_GET[ 'Location' ] : -1;?>;
            return d;
          }
        },
        order : [[ 1, "asc" ]],
        columns : [
          {
            className : 'hidden', 
            data : 'ID'
          },{
            data : 'City_ID'
          }, {
            data : 'Building_ID'
          }
        ],
        language : {
          loadingRecords : "<div style='text-align:center;'><div class='sk-cube-grid' style='display:inline-block;position:relative;';><div class='sk-cube sk-cube1' style='background-color:#cc0000'></div><div class='sk-cube sk-cube2' style='background-color:#cc0000'></div><div class='sk-cube sk-cube3' style='background-color:#cc0000'></div><div class='sk-cube sk-cube4' style='background-color:#cc0000'></div><div class='sk-cube sk-cube5' style='background-color:#cc0000'></div><div class='sk-cube sk-cube6' style='background-color:#cc0000'></div><div class='sk-cube sk-cube7' style='background-color:#cc0000'></div><div class='sk-cube sk-cube8' style='background-color:#cc0000'></div><div class='sk-cube sk-cube9' style='background-color:#cc0000'></div></div><div class='sk-cube-grid' style='display:inline-block;position:relative;top:-45px;'><div class='sk-cube sk-cube1' style='background-color:#00007f'></div><div class='sk-cube sk-cube2' style='background-color:#00007f'></div><div class='sk-cube sk-cube3' style='background-color:#00007f'></div><div class='sk-cube sk-cube4' style='background-color:#00007f'></div><div class='sk-cube sk-cube5' style='background-color:#00007f'></div><div class='sk-cube sk-cube6' style='background-color:#00007f'></div><div class='sk-cube sk-cube7' style='background-color:#00007f'></div><div class='sk-cube sk-cube8' style='background-color:#00007f'></div><div class='sk-cube sk-cube9' style='background-color:#00007f'></div></div><div class='sk-cube-grid' style='display:inline-block;position:relative;top:-84px;'><div class='sk-cube sk-cube1' style='background-color:gold'></div><div class='sk-cube sk-cube2' style='background-color:gold'></div><div class='sk-cube sk-cube3' style='background-color:gold'></div><div class='sk-cube sk-cube4' style='background-color:gold'></div><div class='sk-cube sk-cube5' style='background-color:gold'></div><div class='sk-cube sk-cube6' style='background-color:gold'></div><div class='sk-cube sk-cube7' style='background-color:gold'></div><div class='sk-cube sk-cube8' style='background-color:gold'></div><div class='sk-cube sk-cube9' style='background-color:gold'></div></div></div><div style='font-size:72px;text-align:center;' class='BankGothic'>Nouveau Elevator</div><div style='font-size:42px;text-align:center;'><i>Raising Your Life</i></div>"
        },
        paging:true,
        drawCallback : function ( settings ) { selectUnit(this.api()); }
      });
      function redraw( ){ Table_Units.draw( ); }
      function selectUnit(tbl){
        $("table#Table_Units tbody tr").each(function(){
          $(this).on('click',function(){
            var xDate = $("input[name='Date']").val();
            document.location.href="ticket.php?Date=" + xDate + "&Unit=" + tbl.row(this).data()[0] + '<?php
              echo isset($_GET['Location']) ? "&Location={$_GET['Location']}" : '';
              echo isset($_GET['Job']) ? "&Job={$_GET['Job']}" : '';
            ?>';
          });
        });
      }
      </script>
    </div>
    <div class='panel-heading'><button onClick='closePopup(this);' style='width:100%;height:50px;'>Close</button></div>
  </div>
</div>