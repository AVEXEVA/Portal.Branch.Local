<script>
  function hrefEmployees(){
      $("#Employees_Table tbody tr").each(function(){
          $(this).on('click',function(){
              document.location.href="tickets.php?Mechanic=" + $(this).children(":first-child").html();
          });
       });
  }
    $(document).ready(function() {
    var table = $('#Employees_Table').DataTable( {
        "ajax": {
            "url":"cgi-bin/php/get/Employees.php",
            "dataSrc":function(json){
                if(!json.data){
                    json.data = [];
                }
                return json.data;
            }
        },
        "columns": [
            { "data" : "ID"},
            { "data" : "Last_Name"},
            { "data" : "First_Name"},
            { "data" : "Supervisor"}
        ],
        "order": [[1, 'asc']],
        "language":{
            "loadingRecords":""
        },
        "initComplete":function(){
            hrefEmployees();
            $("input[type='search'][aria-controls='Employees_Table']").on('keyup',function(){hrefEmployees();});
            $('#Employees_Table').on( 'page.dt', function () {setTimeout(function(){hrefEmployees();},100);});
            $("#Employees_Table th").on("click",function(){setTimeout(function(){hrefEmployees();},100);});
            finishLoadingPage();
        }
    } );
  } );
</script>
