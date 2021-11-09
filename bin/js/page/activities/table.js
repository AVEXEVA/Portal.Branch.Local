$(document).ready(function() {
  var Editor_Contacts = new $.fn.dataTable.Editor( {
      idSrc    : 'ID',
      ajax     : 'index.php',
      table    : '#Table_Activities'
  } );
  var Table_Activities = $('#Table_Activities').DataTable({
    dom            : "<'row'<'col-sm-3 search'><'col-sm-9'B>><'row'<'col-sm-12't>>",
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
    autoWidth      : true,
    responsive     : true,
    select         : {
      style : 'multi',
      selector : 'td.ID'
    },
    ajax : {
      url :'bin/php/get/Activities.php'
      data : function( d ){
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
            d.DateTime = $('select[name="DateTime"]').val( );
            d.Person = $('input[name="Person"]').val( );
            d.Page = $('select[name="Page"]').val( );
            d.Parameters = $('select[name="Parameters"]').val( );
            return d;
        }
      }
    }
    columns : [
        {
          data : ID,
          className : 'ID'
        },{
          data : DateTime,
          className : 'date'
        },{
          data : Person
        },{
          data : Page
        },{
          data : Parameters
        }
    ],
    initComplete : function( ){
      $('div.search').html( '<input type='text' name='Search' placeholder='Search' />' );//onChange='$(\"#Table_Contacts\").DataTable().ajax.reload( );' 
      $('input.date').datepicker( { } );
      $('input.time').timepicker( {  timeFormat : 'h:i A' } );
      search( this );
      $( '.redraw' ).bind( 'change', function(){ Table_Contacts.draw(); });
    },
    buttons: [
      {
          text: 'Reset Search',
          action: function ( e, dt, node, config ) {
              $( 'input, select' ).each( function( ){
                  $( this ).val( '' );
              } );
              Table_Contacts.draw( );
          }
      },
      { extend: 'create', editor: Editor_Contacts },
      { extend: 'edit',   editor: Editor_Contacts },
      { extend: 'remove', editor: Editor_Contacts },
      'print',
      'copy',
      'csv'
    ]
  });
});