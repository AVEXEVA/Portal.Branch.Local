<script>
var asyncArray = new Array();
function asyncPage(a){
  var tab = $(a).attr("tab");
  $("div#main-tab-content>div.tab-pane.active").each(function(){$(this).removeClass("active");});
  $(a).parent().siblings().removeClass("active");
  $(a).parent().addClass("active");
$("div#" + tab).remove();
$("#loading-pills").addClass('active');
$("#loading-pills .loading").css("display","block");
$.ajax({
url:"bin/php/element/admin/" + tab + ".php",
method:"GET",
success:function(code){$("div#main-tab-content").append(code);}
});
}
function asyncSubPage(a){
  var tab = $(a).attr("tab");
var maintab = $(a).attr("main");
  $("div#sub-tab-content." + maintab + ">div.tab-pane.active").each(function(){$(this).removeClass("active");});
  $(a).parent().siblings().removeClass("active");
  $(a).parent().addClass("active");
$("div#" + tab).remove();
$("#loading-sub-pills").addClass('active');
$("#loading-sub-pills .loading").css("display","block");
$.ajax({
url:"bin/php/element/admin/" + tab + ".php",
method:"GET",
data:"ID=<?php echo $_GET['ID'];?>",
success:function(code){$("div#sub-tab-content." + maintab).append(code);}
});
}
</script>
<script>
$(document).ready(function(){
$("a[tab='overview-pills']").click();
});
</script>
