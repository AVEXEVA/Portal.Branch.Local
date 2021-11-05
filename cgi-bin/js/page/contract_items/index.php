<script>
  function contract_item_covered(){
    var contractData = new FormData($('form#Manage_Contract_Item')[0]);
    $.ajax({
      url:"cgi-bin/php/post/cover_contract_item.php",
      cache: false,
      processData: false,
      contentType: false,
      data: contractData,
      timeout:15000,
      error:function(XMLHttpRequest, textStatus, errorThrown){
        alert('Your ticket did not save. Please check your internet.')
      },
      method:"POST",
      success:function(code){
        //document.location.href='contract_items.php';
      }
    });
  }
</script>
<script>
var Table_Contract_Items = null;
$(document).ready(function(){
  Table_Contract_Items = $('#Table_Contract_Items').DataTable( {
      "ajax": {
          "url":"cgi-bin/php/get/contract_category_items.php",
          "data": function ( d ) {
             return $.extend( {}, d, {
               "Territory": $("input[name='Start']").val(),
               "Customer": $("input[name='Customer']").val(),
               "Location": $("input[name='Location']").val(),
               "Unit": $("input[name='Unit']").val()
             } );
           },
          "dataSrc":function(json){
            if(!json.data){json.data = [];}
            return json.data;
          }
      },
      /*"processing":true,
      "serverSide":true,*/
      "columns": [
          { "data": "Contract"},
          { "data": "Customer"},
          { "data": "Location"},
          { "data": "Unit"},
          { "data": "Unit_Part"},
          { "data": "Unit_Part_Condition"},
          { "data": "Unit_Part_Remedy"},
          { "data": "Unit_Part_Covered"}
      ],
      "order": [[1, 'desc']],
      "lengthMenu":[[10,25,50,100,500,-1],[10,25,50,100,500,"All"]],
      "language":{"loadingRecords":""},
      "initComplete":function(){},
  } );
});
  function refresh(){
    Table_Escalations.draw();
  }
</script>
