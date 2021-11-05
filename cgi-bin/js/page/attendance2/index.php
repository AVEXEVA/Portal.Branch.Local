<script>
$("div#container").on('click',function(e){
  if($(e.target).closest('.popup').length === 0 && $(e.target).closest('td').length === 0){
    $('.popup').fadeOut(300);
    $('.popup').remove();
  }
});
  function schedule_pto(fDate, fWork, fFirst, Last){
    var pto = "<div class='popup' style=''><form action='#' method='POST'><div class='panel panel-primary'><div class='panel-heading'>Schedule Paid Time Off</div><div class='panel-body' style='padding:10px;'><div class='row'><div class='col-xs-4'>First Name:</div><div class='col-xs-8'>" + fFirst + "</div><div class='col-xs-4'>Last Name:</div><div class='col-xs-8'>" + Last + "</div><input type='hidden' value='" + fWork + "' name='fWork' /><input type='hidden' value='" + fDate + "' name='fDate' /><div class='col-xs-4'>All Day</div><div class='col-xs-8'><select name='AllDay'><option value='Yes'>Yes</option><option value='No'>No</option></select></div><div class='col-xs-4'>Start Time:</div><div class='col-xs-8'><input type='text' name='StartTime' /></div><div class='col-xs-4'>End Time</div><div class='col-xs-8'><input type='text' name='EndTime' /></div><div class='col-xs-4'>Description</div><div class='col-xs-8'><input type='hidden' name='Remarks' value='OUT' /><select name='fDesc'><option value='Sick'>Sick</option><option value='Vacation'>Vacation</option><option value='No Pay'>No Pay</option><option value='Personal Day'>Personal Day</option><option value='En Lieu'>En Lieu</option><option value='Medical Day'>Medical Day</option><option value='Other'>Other</option></select></div><div class='col-xs-4'>&nbsp;</div><div class='col-xs-8'><input type='submit' value='Submit' /></div></div></div></div></form></div>";
    $("body").append(pto);
    $("input[name='StartTime']").timepicker();
    $("input[name='EndTime']").timepicker();
  }
</script>
<script>
  $(document).ready(function(){
    $('#attendance').fixedHeaderTable({height: '650', width:'1500' });
    $(".fht-table-wrapper").css("height","100%");
  });
</script>
<script>
$(document).ready(function(){
$("a[tab='overview-pills']").click();
});
</script>
