<script>
    $(document).ready(function() {
        var Table_Connections = $('#Table_Connections').DataTable( {
            "ajax": {
                "url":"cgi-bin/php/get/Connection.php?ID=<?php echo $_GET['ID'];?>",
                "dataSrc":function(json){if(!json.data){json.data = [];}return json.data;}
            },
            "columns": [
                { "data": "ID"},
                { "data": "Page"},
                { "data": "Date"}
            ],
            "order": [[1, 'asc']],
            "language":{
                "loadingRecords":""
            },
            "lengthMenu":[[10,25,50,100,500,-1],[10,25,50,100,500,"All"]],
            "initComplete":function(){
                $("tr[role='row']>th:nth-child(3)").click().click();
                finishLoadingPage();
            }
        } );
    } );
</script>
