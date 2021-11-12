$( document ).ready( function( ){
  var Editor_Violations = new $.fn.dataTable.Editor( {
  idSrc    : 'ID',
  ajax     : 'index.php',
  table    : '#Table_Violations'
} );
  var Table_Violations = $('#Table_Violations').DataTable( {
      dom            : "<'row'<'col-sm-3 search'><'col-sm-9'B>><'row'<'col-sm-12't>>",
      processing     : true,
      serverSide     : true,
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
      columns    : [
          {
              data      : 'ID',
              render : function( data, type, row, meta ){
                  switch( type ){
                      case 'display' :
                          return  row.ID !== null
                              ?   "<div class='row'>" +
                                      "<div class='col-12'><a href='violation.php?ID=" + row.ID + "'><i class='fa fa-link fa-fw fa-1x'></i> Violation #" + row.ID + "</a></div>" +
                                      "<div class='col-12'>" + ( row.Name !== null && row.Name != '' ? row.Name : 'Misisng Name' )+ "</a></div>" +
                                  "</div>"
                              :   'Missing Name';
                      default :
                          return data;
                  }
              }
          },{
              data : 'Customer_ID',
              render : function( data, type, row, meta ){
                  switch( type ){
                      case 'display' :
                          return  row.Customer_ID !== null
                              ?   "<div class='row'>" +
                                      "<div class='col-12'><a href='customer.php?ID=" + row.Customer_ID + "'><i class='fa fa-link fa-fw fa-1x'></i>" + row.Customer_Name + "</a></div>" +
                                  "</div>"
                              :   null;
                      default :
                          return data;
                  }

              }
          },{
              data : 'Location_ID',
              render : function( data, type, row, meta ){
                  switch( type ){
                      case 'display' :
                          return  row.Location_ID !== null
                              ?   "<div class='row'>" +
                                      "<div class='col-12'><a href='location.php?ID=" + row.Location_ID + "'><i class='fa fa-building fa-fw fa-1x'></i>" + row.Location_Name + "</a></div>" +
                                      "<div class='col-12'>" +
                                          "<div class='row'>" +
                                              "<div class='col-12'><i class='fa fa-map-signs fa-fw fa-1x'></i>" + row.Location_Street + "</div>" +
                                              "<div class='col-12'>" + row.Location_City + ", " + row.Location_State + " " + row.Location_Zip + "</div>" +
                                          "</div>" +
                                      "</div>" +
                                  "</div>"
                              :   null;
                      default :
                          return data;
                  }

              }
          },{
              data : 'Date',
              render: function( data, type, row, meta ){
                  switch( type ){
                      case 'display':
                          return row.Date !== null
                              ?   "<div class='row'>" +
                                      "<div class='col-12'><i class='fa fa-calendar fa-fw fa-1x'></i>" + row.Date + "</div>" +
                                  "</div>"
                              :   null;
                          default :
                              return data;

                  }
              }

          },{
              data : 'Status'
          }
      ],
      ajax : {
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
              d.Location = $('input[name="Location"]').val( );
              d.Date_Start = $('input[name="Date_Start"]').val( );
              d.Date_End = $('input[name="Date_End"]').val( );
              d.Status = $('input[name="Status"]').val( );
              return d;
          },
          url : 'bin/php/get/violations.php'
      },
      initComplete : function( ){
      $("div.search").html( "<input type='text' name='Search' placeholder='Search' />" );
      $('input.date').datepicker( { } );
      $('input.time').timepicker( {  timeFormat : 'h:i A' } );
      //search( this );
      $( '.redraw' ).bind( 'change', function(){ Table_Violations.draw(); });
  },
  buttons: [
      {
          text: 'Reset Search',
          action: function ( e, dt, node, config ) {
              $( 'input, select' ).each( function( ){
                  $( this ).val( '' );
              } );
              Table_Violations.draw( );
          }
      },{
          text : 'Get URL',
          action : function( e, dt, node, config ){
              var d = { };
              d.Search = $('input[name="Search"]').val( );
              d.Name = $('input[name="Name"]').val( );
              d.Location = $('input[name="Location"]').val( );
              d.Date_Start = $('input[name="Date_Start"]').val( );
              d.Date_End = $('input[name="Date_End"]').val( );
              d.Status = $('input[name="Status"]').val( );
              document.location.href = 'tickets.php?' + new URLSearchParams( d ).toString();
          }
      },
      { extend: 'create', editor: Editor_Violations },
      { extend: 'edit',   editor: Editor_Violations },
      { extend: 'remove', editor: Editor_Violations },
      'copy',
      'csv'
  ]
  } );
} );
