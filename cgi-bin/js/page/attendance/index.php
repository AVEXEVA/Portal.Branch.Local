<script>
$(document).ready(function(){
  $("input[name='Start']").datepicker();
  $("input[name='End']").datepicker();
});
var table = $('#attendance').DataTable({
  "ajax": {
      "url":"cgi-bin/php/get/attendance.php?<?php echo isset($_GET[ 'Supervisor' ]) ? 'Supervisor=' . $_GET['Supervisor'] : '';?>",
      "dataSrc":function(json){if(!json.data){json.data = [];}return json.data;}
  },
  "columns": [
      { "data": "fFirst"},
      { "data": "Last"},
      { "data": "Start"},
      { "data": "End"}
  ],
  "order": [[1, 'asc']],
  "language":{"loadingRecords":""},
  "pageLength":-1,
  "initComplete":function(){finishLoadingPage();}
});
<script>
$(document).ready(function(){
$("a[tab='overview-pills']").click();
});
</script>
<script>
$(document).ready(function(){
$("a[tab='overview-pills']").click();
});
</script>
