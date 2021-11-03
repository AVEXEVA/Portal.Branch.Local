<script>
  var Table_Contracts = $('#Table_Contracts').DataTable( {
    dom 	   : 'tp',
    processing : true,
    serverSide : true,
    responsive : true,
    autoWidth  : false,
    paging     : true,
    searching  : false,
    ajax       : {
      url : 'cgi-bin/php/get/Contracts2.php',
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
        d.Customer = $('input[name="Customer"]').val( );
        d.Location = $('input[name="Location"]').val( );
        d.Start_Date = $('input[name="Start_Date"]').val( );
        d.End_Date = $('input[name="End_Date"]').val( );
        d.Cycle = $('select[name="Cycle"]').val( );
        return d;
      }
    },
    columns: [
      {
        data 	: 'ID'
      },{
        data 	: 'Customer'
      },{
        data 	: 'Location'
      },{
        data 	: 'Job'
      },{
        data 	: 'Start_Date'
      },{
        data 	: 'Amount'
      },{
        data 	: 'Length',
        render  : function( data ){ return data + ' months'; }
      },{
        data 	: 'Cycle'
      },{
        data 	: 'End_Date'
      },{
        data 	: 'Escalation_Factor'
      },{
        data 	: 'Escalation_Date'
      },{
        data 	: 'Escalation_Type'
      },{
        data 	: 'Escalation_Cycle'
      },{
        data 	: 'Link',
        render  : function( d ){ return d !== null ? "<a href='" + d + "'>" + d + "</a>" : ''; }
      },{
        data 	: 'Remarks'
      }
    ]
  } );
  //Datepickers
  $('input[name="Start_Date"]').datepicker( { } );
  $('input[name="End_Date"]').datepicker( { } );
  //Events
  function redraw( ){ Table_Contracts.draw(); }
  function hrefContracts(){hrefRow('Table_Contracts','contract');}
  $('Table#Table_Contracts').on('draw.dt',function(){hrefContracts();});
</script>
