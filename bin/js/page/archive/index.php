<!-- Custom Date Filters-->S
<script>
    var reset_loc = 0;
    $(document).ready(function(){
        $("input.start_date").datepicker({
          onSelect:function(dateText, inst){
              document.location.href="archive.php?Dashboard=Mechanic&Mechanic=<?php echo (isset($_GET['Mechanic'])) ? $_GET['Mechanic'] : $_SESSION['User'];?>&Start_Date=" + dateText + "&End_Date=" + $("input.end_date").val() + "&Location_Tag=" + $("select[name='filter_location_tag']").val() + "&Status=<?php echo $_GET['Status'];?>" + "&Show_Hours=" + $("input#show_hours[type='radio']:checked").val() + "&Show_Tickets=" + $("input#show_tickets[type='radio']:checked").val();
          }
      });
      $("input.end_date").datepicker({
          onSelect:function(dateText, inst){
              document.location.href="archive.php?Dashboard=Mechanic&Mechanic=<?php echo (isset($_GET['Mechanic'])) ? $_GET['Mechanic'] : $_SESSION['User'];?>&Start_Date=" + $("input.start_date").val() + "&End_Date=" + dateText + "&Location_Tag=" + $("select[name='filter_location_tag']").val() + "&Status=<?php echo $_GET['Status'];?>" + "&Show_Hours=" + $("input#show_hours[type='radio']:checked").val() + "&Show_Tickets=" + $("input#show_tickets[type='radio']:checked").val();
          }
      });
  });

    function filter_location(){refresh_get();}
    function toggle_hours(){refresh_get();}
    function toggle_tickets(){refresh_get();}
    function format ( d ) {
      return '<table cellpadding="5" cellspacing="0" border="0" style="padding-left:50px;">'+
          '<tr>'+
              '<td>Full name:</td>'+
              '<td>'+d.name+'</td>'+
          '</tr>'+
          '<tr>'+
              '<td>Extension number:</td>'+
              '<td>'+d.extn+'</td>'+
          '</tr>'+
          '<tr>'+
              '<td>Extra info:</td>'+
              '<td>And any further details here (images etc)...</td>'+
          '</tr>'+
      '</table>';
}
    function hrefTickets(){$("#Table_Archive_Tickets tbody tr").each(function(){$(this).on('click',function(){document.location.href="ticket.php?ID=" + $(this).children(":first-child").html();});});}
    var Location_Tags = "";
    function refreshLocationTags(){
        Location_Tags = "";
        $(".Location").each(function(){
            Location_Tags += "," + $(this).attr('rel');
        });
        Location_Tags = Location_Tags.substring(1);
        return Location_Tags;
    }
    var Customer_Tags = "";
    function refreshCustomerTags(){
        Customer_Tags = "";
        $(".Customer").each(function(){
            Customer_Tags += "," + $(this).attr('rel');
        });
        Customer_Tags = Customer_Tags.substring(1);
        return Customer_Tags;
    }
    var table = null;
    $(document).ready(function() {
        refreshLocationTags();
        <?php /*if(isset($_GET['deferLoading'])){?>finishLoadingPage();<?php }*/?>
        <?php if(count($_GET) > 0){?>var Table_Archive_Tickets = $('#Table_Archive_Tickets').DataTable( {
        "ajax": {
            url:"bin/php/get/archive.php",
            type: "GET",
            data:function(d){
                d.Start_Date = $("input.start_date").val();
                d.End_Date = $("input.end_date").val();
                d.Location_ID = refreshLocationTags();
                d.Customer_ID = refreshCustomerTags();
            },
            complete:function(){
                setTimeout(function(){
                    //$("tr[role='row']>th:nth-child(5)").click().click();
                    hrefTickets();
                    $("input[type='search'][aria-controls='Table_Archive_Tickets']").on('keyup',function(){hrefTickets();});
                    $('#Table_Archive_Tickets').on( 'page.dt', function () {setTimeout(function(){hrefTickets();},100);});
                    $("#Table_Archive_Tickets th").on("click",function(){setTimeout(function(){hrefTickets();},100);});
                },100);
            },
            "dataSrc":function(json){
                if(!json.data){json.data = [];}
                return json.data;
            }
        },
        <?php if(isset($_GET['deferLoading'])){?>"deferLoading":0,<?php }?>
        "columns": [
            {   "data"         : "ID" },
            {   "data"         : "Tag"},
            {   "data"         : "fDesc"},
            {   "data"         : "DescRes"},
            {   "data"         : "EDate"},
            {
                "data"           : "Total",
                "defaultContent" : "0"
            },
            {
                "data"           : "Unit_State",
                "visible"        : false,
                "searchable"     : true
            },
            {
                "data"           : "Unit_Label",
                "visible"        : false,
                "searchable"     : true
            },
            {
                "data"           : "Unit_Description",
                "visible"        : false,
                "searchable"     : true
            }
        ],
        "order": [[1, 'asc']],
        "language":{"loadingRecords":""},
        "lengthMenu": [[10,25,50,100,500,-1],[10,25,50,100,500,"All"]],
        "initComplete":function(){
            //$("tr[role='row']>th:nth-child(5)").click().click();
            hrefTickets();
            $("input[type='search'][aria-controls='Table_Archive_Tickets']").on('keyup',function(){hrefTickets();});
            $('#Table_Archive_Tickets').on( 'page.dt', function () {setTimeout(function(){hrefTickets();},100);});
            $("#Table_Archive_Tickets th").on("click",function(){setTimeout(function(){hrefTickets();},100);});
            $("select[name='Table_Archive_Tickets_length']").on("click",function(){setTimeout(function(){hrefTickets();},100);});
            finishLoadingPage();
        },
        "deferLoading":0
    } );<?php } else {?>finishLoadingPage();var Table_Archive_Tickets = $('#Table_Archive_Tickets').DataTable( {} );<?php }?>
} );
function refreshGet(){
        //refreshLocationTags();
        //table.ajax.reload(null,false);
        document.location.href="archive.php?Start_Date=" + $("input.start_date").val() + "&End_Date=" + $("input.end_date").val() + "&Customer_ID=" + refreshCustomerTags() + "&Location_ID=" + refreshLocationTags();
    }

</script>
