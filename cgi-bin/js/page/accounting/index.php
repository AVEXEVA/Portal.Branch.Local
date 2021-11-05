<script>
var TIMELINE = new Array();
var GETTING_TIMELINE = 0;
var Last_Ref = 0;
function numberWithCommas(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}
function getTimeline(){
  if(GETTING_TIMELINE == 0){
    GETTING_TIMELINE = 1;
    $.ajax({
      url:"cgi-bin/php/get/Accounting.php",
      data:{
        Ref : Last_Ref
      },
      method:"GET",
      success:function(code){
        var jsonData = JSON.parse(code);
        for(i in jsonData){
          Last_Ref = i;
          if(TIMELINE[i]){}
          else {
            TIMELINE[i] = jsonData[i];
            $("#Timeline").prepend("<div class='row'>"
              + '<div class="col-xs-1"><?php $Icons->Invoice(1);?></div>'
              + "<div class='col-xs-1'>Invoice</div>"
              + "<div class='col-xs-1'>#" + jsonData[i].Ref + "</div>"
              + "<div class='col-xs-1'>$" + numberWithCommas(jsonData[i].Amount) + "</div>"
              + "<div class='col-xs-3'>" + jsonData[i].Location_Tag + "</div>"
            + "</div>");
          }
        }
        GETTING_TIMELINE = 0;
      }
    });
  }
}
$(document).ready(function(){
  getTimeline();
  setInterval(getTimeline, 5000);
});
</script>
