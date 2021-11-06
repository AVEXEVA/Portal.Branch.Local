<script>
function popupViolation(violationID){
  $(".Ticket").remove();
  $.ajax({
    url:"violation.php",
    method:"GET",
    data:{
      container:0,
      ID:violationID
    },
    success:function(code){
      $("body").append(code);
    }
  })
}
function popupTicket(ticketID){
  $(".Ticket").remove();
  $.ajax({
    url:"bin/php/tooltip/Ticket.php",
    method:"GET",
    data:{
      ID:ticketID
    },
    success:function(code){
      $("body").append(code);
    }
  })
}
$(document).on('click',function(e){
  $(".Ticket").remove();
	if($(e.target).closest('.popup').length === 0){
		$('.popup').fadeOut(300);
		$('.popup').remove();
	}
});
</script>
