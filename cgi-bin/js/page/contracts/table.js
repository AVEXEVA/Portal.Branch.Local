$( document ).ready( function() {
  var Table_Contracts = $('#Table_Contracts').DataTable( {
    dom 	   : "<'row'<'col-sm-3 search'><'col-sm-7'><'col-sm-2'B>><'row'<'col-sm-12't>>",
    processing     : true,
    serverSide     : true,
    responsive     : true,
    autoWidth      : false,
    searching      : false,
    lengthChange   : false,
    scrollResize   : true,
    scrollY        : 100,
    scroller       : true,
    scrollCollapse : true,
    paging         : true,
    orderCellsTop  : true,
    select         : true,
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
        d.Job = $('input[name="Job"]').val( );
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
        data  : 'End_Date'
      },{
        data 	: 'Length',
        render  : function( data ){ return data + ' months'; }
      },{
        data 	: 'Amount'
      },{
        data 	: 'Cycle'
      },{
        data 	: 'Escalation_Factor'
      },{
        data 	: 'Escalation_Date',
        visible : false
      },{
        data 	: 'Escalation_Type',
        visible : false
      },{
        data 	: 'Escalation_Cycle',
        visible : false
      },{
        data 	: 'Link',
        render  : function( d ){ return d !== null ? "<a href='" + d + "'>" + d + "</a>" : ''; }
      },{
        data 	: 'Remarks',
        render : $.fn.dataTable.render.ellipsis( 200 )
      }
    ],
    initComplete : function( ){
      $.ajax( {
        url : 'cgi-bin/php/element/table/search.php',
        success : function( html ){ $("div.search").html( html ); }
      } );
      $( 'input.redraw' ).bind( 'change', function(){
        Table_Contracts.draw() 
      });
      $('input.date').datepicker( { } );
    },
    buttons: [
      {
        text : 'View',
        action : function( e, dt, node, config ){
          var selected = Table_Contracts.rows( { selected : true } )[ 0 ][ 0 ];
          if( selected !== undefined ){
            var ID = Table_Contracts.row( selected ).data( ).ID;
            if( $.isNumeric( ID ) ){
              document.location.href = 'contract.php?ID=' + ID;  
            }  
          }
          
        }
      },
      'print',
      'copy',
      'csv'
    ]
  } );
});