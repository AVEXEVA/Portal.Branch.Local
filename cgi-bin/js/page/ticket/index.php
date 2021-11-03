<script src='cgi-bin/js/function/closePopup.js'></script>
<script>
  function saveTicket(link){
    $(link).html("Saving <img src='media/images/spinner.gif' height='25px' width='auto' />");
    var ticketData = new FormData();
    ticketData.append('Date',$("input[name='Date']").val());
    ticketData.append('Location','<?php echo isset($_GET['Location']) ? $_GET['Location'] : '';?>');
    ticketData.append('Unit','<?php echo isset($_GET['Unit']) ? $_GET['Unit'] : '';?>');
    ticketData.append('Job','<?php echo isset($_GET['Job']) ? $_GET['Job'] : '';?>');
    ticketData.append('Description',$("textarea[name='Description']").val());
    ticketData.append('Level',$("select[name='Level']").val());
    if(ticketData.get('Date') == '' || ticketData.get('Location') == '' || ticketData.get('Job') == '' || ticketData.get('Description') == ''){
      alert('Please fill out the necessasry information in order to continue.');
    } else {
      $.ajax({
        url:"cgi-bin/php/post/save_new_ticket.php",
        cache: false,
        processData: false,
        contentType: false,
        method:"POST",
        data: ticketData,
        success:function(code){document.location.href='ticket.php?ID=' + code;}
      });
    }
  }
  $(document).ready(function(){$("input[name='Date']").datepicker();});
  function selectLocations(link){
    $.ajax({
      url:'cgi-bin/php/element/ticket/selectLocations.php',
      method:'GET',
      success:function(code){
        $('body').append(code);
      }
    });
  }
    function selectJobs(link){
      $.ajax({
        url:'cgi-bin/php/element/ticket/selectJobs.php?Location=<?php echo $_GET['Location'];?>&Unit=<?php echo $_GET['Unit'];?>',
        method:'GET',
        success:function(code){
          $('body').append(code);
        }
      });
    }
  function selectUnits(link){
    $.ajax({
      url:'cgi-bin/php/element/ticket/selectUnits.php?Location=<?php echo $_GET['Location'];?>',
      method:'GET',
      success:function(code){
        $('body').append(code);
      }
    });
  }
</script>