<script src='bin/js/page/Job/table.js'></script>
<script>
function someFunction(link,URL){
  $(link).siblings().removeClass('active');
  $(link).addClass('active');
  $.ajax({
    url:"bin/php/element/job/" + URL,
    success:function(code){
      $("div.container-content").html(code);
    }
  });
}
$(document).ready(function(){
  $("div.Screen-Tabs>div>div:first-child").click();
});
</script>
