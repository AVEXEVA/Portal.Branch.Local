<script>
var isChromium = window.chrome,
  winNav = window.navigator,
  vendorName = winNav.vendor,
  isOpera = winNav.userAgent.indexOf("OPR") > -1,
  isIEedge = winNav.userAgent.indexOf("Edge") > -1,
  isIOSChrome = winNav.userAgent.match("CriOS");
var Table_Locations = $('#Table_Locations').DataTable( {
  dom 	   : 'tlp',
      processing : true,
      serverSide : true,
      responsive : true,
      autoWidth : false,
  paging    : true,
  searching : false,
  ajax      : {
  url : 'cgi-bin/php/get/Locations2.php',
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
      d.City = $('input[name="City"]').val( );
      d.Street = $('input[name="Street"]').val( );
      d.Maintained = $('select[name="Maintained"]').val( );
      d.Status = $('select[name="Status"]').val( );
      return d;
  }
},
  columns   : [
    {
      data 	  : 'ID',
      className : 'hidden'
    },{
      data : 'Name'
    },{
      data : 'Customer'
    },{
      data : 'City'
    },{
      data : 'Street'
    },{
      data   : 'Maintained',
      render : function ( data ){
        return data == 1
          ?	'Yes'
          : 	'No';
      }
    },{
      data   : 'Status',
      render : function ( data ){
        return data == 0
          ?	'Yes'
          : 	'No';
      }
    }
  ]
} );
function redraw( ){ Table_Locations.draw( ); }
function hrefLocations(){hrefRow("Table_Locations","location");}
$("Table#Table_Locations").on("draw.dt",function(){hrefLocations();});
</script>
