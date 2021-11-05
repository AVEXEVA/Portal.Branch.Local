<script>
function clickTab(Tab,Subtab){
  $("a[tab='" + Tab + "']").click();
  setTimeout(function(){$("a[tab='" + Subtab + "']").click();},1000);
}
$(document).ready(function(){
  $("a[tab='overview-pills']").click();
});
</script>
