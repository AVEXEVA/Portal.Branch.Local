
$( document ).ready( function() {
  var Editor_Contracts = new $.fn.dataTable.Editor( {
    idSrc    : 'ID',
    ajax     : 'index.php',
    table    : '#Table_Contracts',
    template : '#Form_Lead',
    formOptions: {
      inline: {
        submit: 'allIfChanged'
      }
    },
    fields : [
      {
        label : 'Customer',
        name  : 'Customer',
        type  : 'readonly', 
        attr:{ 
          disabled:true
        }
      },{
        label : 'Location',
        name  : 'Location',
        type  : 'readonly', 
        attr:{ 
          disabled : true 
        }
      },{
        label : 'Job',
        name  : 'Job',
        type  : 'readonly', 
        attr:{ 
          disabled:true
        }
      },{
        label : 'Start',
        name  : 'Start_Date',
        type  : 'datetime'
      },{
        label : 'End',
        name  : 'End_Date',
        type  : 'datetime'
      },{
        label : 'Length',
        name  : 'Length'
      },{
        label : 'Amount',
        name  : 'Amount'
      },{
        label : 'Cycle',
        name  : 'Cycle',
        type  : 'select'
      },{
        label : 'Escalation Factor',
        name  : 'Escalation_Factor'
      },{
        label : 'Escalation Date',
        name  : 'Escalation_Date'
      }
    ]
  });
  $('#Table_Contracts').on( 'click', 'tbody td:not(.control)', function (e) {
    Editor_Contracts.inline( this );
  } );
  var Table_Contracts = $('#Table_Contracts').DataTable( {
    dom 	   : "<'row'<'col-sm-3 search'><'col-sm-9'B>><'row'<'col-sm-12't>>",
    processing     : true,
    serverSide     : true,
    autoWidth      : false,
    searching      : false,
    lengthChange   : false,
    scrollResize   : true,
    scrollY        : 100,
    scroller       : true,
    scrollCollapse : true,
    paging         : true,
    orderCellsTop  : true,
    responsive     : {
      details : {
        type   : 'column',
        target : 0
      }
    },
    select         : {
      style : 'multi',
      selector : 'td.control'
    },
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
        className : 'control',
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
        data 	: 'Escalation_Date'
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
      $("div.search").html( "<input type='text' name='Search' placeholder='Search' onChange='$(\"#Table_Contracts\").DataTable().ajax.reload( );' />" );
      $( 'input.redraw' ).bind( 'change', function(){
        Table_Contracts.draw() 
      });
      $('input.date').datepicker( { } );
    },
    buttons: [
      { extend: 'create', editor: Editor_Contracts },
      { extend: 'edit',   editor: Editor_Contracts },
      { extend: 'remove', editor: Editor_Contracts },
      'print',
      'copy',
      'csv'
    ]
  } );
});
