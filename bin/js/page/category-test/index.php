<script>
	$(document).ready(function(){
		$('.preference').each(function(e){
	 if($(this).val() == 0){
	  $(this).attr("checked", "checked");
		 }
		});
		$(".col-xs-12").each(function(){
			var html = $(this).html();
			var label = html.substring(0, html.indexOf('<input'));
			var input = html.substring(html.indexOf('<input'));
			$(this).replaceWith("<div class='col-xs-4 col-lg-2 col-md-2'>" + label + "</div><div class='col-xs-8 col-lg-4 col-md-4'>" + input + "</div>");
		});
	});
</script>
<script>
  $(document).ready(function(){
    $( function() {
        $( ".datepicker" ).datepicker();
      } );
  });
</script>
