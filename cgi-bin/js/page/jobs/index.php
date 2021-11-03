<script src="https://www.nouveauelevator.com/vendor/bootstrap/js/bootstrap.js"></script>
<?php require('cgi-bin/js/datatables.php');?>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script>
var isChromium = window.chrome,
  winNav = window.navigator,
  vendorName = winNav.vendor,
  isOpera = winNav.userAgent.indexOf("OPR") > -1,
  isIEedge = winNav.userAgent.indexOf("Edge") > -1,
  isIOSChrome = winNav.userAgent.match("CriOS");
var Table_Jobs = $('#Table_Jobs').DataTable( {
  dom 	   : 'tlp',
      processing : true,
      serverSide : true,
      responsive : true,
      autoWidth : false,
  paging    : true,
  searching : false,
  ajax      : {
      url : 'cgi-bin/php/get/Jobs2.php',
      data : function( d ){
          d = {
              start : d.start,
              length : d.length,
              order : {
                  column : d.order[0].column,
                  dir : d.order[0].dir
              }
          };
          d.Search = $('input[name="Search"]').val( );
          d.ID = $('input[name="ID"]').val( );
          d.Name = $('input[name="Name"]').val( );
          d.Customer = $('input[name="Customer"]').val( );
          d.Location = $('input[name="Location"]').val( );
          d.Type = $('input[name="Type"]').val( );
          d.Status = $('select[name="Status"]').val( );
          return d;
        }
        },
      columns   : [
        {
          data 	  : 'ID'
        },{
          data : 'Name'
        },{
          data : 'Customer'
        },{
          data : 'Location'
        },{
          data : 'Type'
        },{
          data : 'Status'
        }
      ]
  } );
function redraw( ){ Table_Jobs.draw( ); }
function hrefJobs(){hrefRow("Table_Jobs","job");}
$("Table#Table_Jobs").on("draw.dt",function(){hrefJobs();});
</script>
