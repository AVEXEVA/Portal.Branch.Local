<script src="https://www.nouveauelevator.com/vendor/bootstrap/js/bootstrap.min.js"></script>
<?php require('cgi-bin/js/datatables.php');?>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script>
function someFunction(link,URL){
  $(link).siblings().removeClass('active');
  $(link).addClass('active');
  $.ajax({
    url:"cgi-bin/php/element/job/" + URL,
    success:function(code){
      $("div.container-content").html(code);
    }
  });
}
$(document).ready(function(){
  $("div.Screen-Tabs>div>div:first-child").click();
});
</script>
