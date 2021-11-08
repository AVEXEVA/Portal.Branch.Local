<script>
  var here;
<?php
$result = $database->query(null,"SELECT TicketO.ID AS Ticket_ID FROM TicketD WHERE TicketO.Invoice = ?",array($_GET['ID']));
if($result){while($Ticket = sqlsrv_fetch_array($result)){?>
  $(document).ready(function(){
    var TicketID = <?php echo $Ticket['Ticket_ID'];?>;
    $.ajax({
      url:"short-ticket.php?ID=" + TicketID,
      method:"GET",
      success:function(code){
        $("div#page-wrapper.content").append(code);
      }
    });
  });
<?php }}?>
<?php
$result = $database->query(null,"SELECT TicketD.ID AS Ticket_ID FROM TicketD WHERE TicketD.Invoice = ?",array($_GET['ID']));
if($result){while($Ticket = sqlsrv_fetch_array($result)){?>
  $(document).ready(function(){
    var TicketID = <?php echo $Ticket['Ticket_ID'];?>;
    $.ajax({
      url:"short-ticket.php?ID=" + TicketID,
      method:"GET",
      success:function(code){
        $("div#page-wrapper.content").append(code);
      }
    });
  });
<?php }}?>
<?php
$result = $database->query(null,"SELECT TicketDArchive.ID AS Ticket_ID FROM TicketDArchive WHERE TicketDArchive.Invoice = ?",array($_GET['ID']));
if($result){while($Ticket = sqlsrv_fetch_array($result)){?>
  $(document).ready(function(){
    var TicketID = <?php echo $Ticket['Ticket_ID'];?>;
    $.ajax({
      url:"short-ticket.php?ID=" + TicketID,
      method:"GET",
      success:function(code){
        $("div#page-wrapper.content").append(code);
      }
    });
  });
<?php }}?>
</script>
<script>
$(document).ready(function(){
    var Table_Invoice_Items = $('#Table_Invoice_Items').DataTable( {
        "ajax": "bin/php/get/Items_by_Invoice.php?ID=<?php echo $_GET['ID'];?>",
        "columns": [
            { "data": "Dated"},
            { "data": "Description"},
            { "data": "Amount"}
        ],
        "order": [[1, 'asc']],
        "language":{
            "loadingRecords":""
        },
        "initComplete":function(){
        }
    } );
    var Table_Payments = $('#Table_Payments').DataTable( {
        "ajax": "bin/php/get/Payments_by_Invoice.php?ID=<?php echo $_GET['ID'];?>",
        "columns": [
            { "data": "Dated"},
            { "data": "Description"},
            { "data": "Amount"}
        ],
        "order": [[1, 'asc']],
        "language":{
            "loadingRecords":""
        },
        "initComplete":function(){}
    } );
});
</script>
