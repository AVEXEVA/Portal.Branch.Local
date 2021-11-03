<script src="https://www.nouveauelevator.com/vendor/flot/excanvas.min.js"></script>
<script src="https://www.nouveauelevator.com/vendor/flot/jquery.flot.js"></script>
<script src="https://www.nouveauelevator.com/vendor/flot/jquery.flot.pie.js"></script>
<script src="https://www.nouveauelevator.com/vendor/flot/jquery.flot.resize.js"></script>
<script src="https://www.nouveauelevator.com/vendor/flot/jquery.flot.time.js"></script>
<script src="https://www.nouveauelevator.com/vendor/flot/jquery.flot.categories.js"></script>
<script src="https://www.nouveauelevator.com/vendor/flot-tooltip/jquery.flot.tooltip.min.js"></script>
<script>
function someFunction(link,URL){
  $(link).siblings().removeClass('active');
  $(link).addClass('active');
  $.ajax({
    url:"cgi-bin/php/element/customer/" + URL,
    success:function(code){
      $("div.container-content").html(code);
    }
  });
}
$(document).ready(function(){
  //$("div.Screen-Tabs>div>div:first-child").click();
});
</script>
