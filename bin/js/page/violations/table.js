function search( link ){
  function search( link ){
      var api = link.api();
      $('input[name="Search"]', api.table().container())
          .typeahead({
              minLength : 4,
              hint: true,
              highlight: true,
              limit : 5,
              display : 'FieldValue',
              source: function( query, result ){
                  $.ajax({
                      url : 'bin/php/get/search/violations.php',
                      method : 'GET',
                      data    : {
                          search                :  $('input:visible[name="Search"]').val(),
                          ID                    :  $('input:visible[name="ID"]').val( ),
                          Person                :  $('input:visible[name="Person"]').val( ),
                          Customer              :  $('input:visible[name="Customer"]').val( ),
                          Location              :  $('input:visible[name="Location"]').val( ),
                          Unit                  :  $('input:visible[name="Unit"]').val( ),
                          Job                   :  $('input:visible[name="Job"]').val( ),
                          Type                  :  $('select:visible[name="Type"]').val( ),
                          Level                 :  $('select:visible[name="Level"]').val( ),
                          Status                :  $('select:visible[name="Status"]').val( ),
                          Start_Date            :  $('input:visible[name="Start_Date"]').val( ),
                          End_Date              :  $('input:visible[name="End_Date"]').val( ),
                          Time_Route_Start      :  $('input:visible[name="Time_Route_Start"]').val( ),
                          Time_Route_End        :  $('input:visible[name="Time_Route_End"]').val( ),
                          Time_Site_Start       :  $('input:visible[name="Time_Site_Start"]').val( ),
                          Time_Site_End         :  $('input:visible[name="Time_Site_End"]').val( ),
                          Time_Completed_Start  :  $('input:visible[name="Time_Completed_Start"]').val( ),
                          Time_Completed_End    :  $('input:visible[name="Time_Completed_End"]').val( ),
                          LSD                   :  $('select:visible[name="LSD"]').val( )
                      },
                      dataType : 'json',
                      beforeSend : function( ){
                          abort( );
                      },
                      success : function( data ){
                          result( $.map( data, function( item ){
                              return item.FieldName + ' => ' + item.FieldValue;
                          } ) );
                      }
                  });
              },
              afterSelect: function( value ){
                  var FieldName = value.split( ' => ' )[ 0 ];
                  var FieldValue = value.split( ' => ' )[ 1 ];
                  $( 'input[name="' + FieldName.split( '_' )[ 0 ] + '"]' ).val ( FieldValue ).change( );
                  $( 'input[name="Search"]').val( '' );
              }
          }
      );
  }
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
          },{
              data : 'Search',
              render : function( data, type, row, meta ){
                  switch( type ){
                      case 'display' :
                          return  row.Search !== null
                              ?   "<div class='row'>" +
                                      "<div class='col-12'><a href='search.php?ID=" + row.Search + "'><i class='fa fa-link fa-fw fa-1x'></i>" + row.Search + "</a></div>" +
                                  "</div>"
                            :   null;
                    default :
                        return data;
                }

              }
          },{
              data : 'Name',
              render : function( data, type, row, meta ){
                  switch( type ){
                      case 'display' :
                          return  row.Name !== null
                              ?   "<div class='row'>" +
                                      "<div class='col-12'><a href='name.php?ID=" + row.Name + "'><i class='fa fa-building fa-fw fa-1x'></i>" + row.Name + "</a></div>" +
                                  "</div?
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
              data : 'Location'
          },{
              data : 'Select'
          },{


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

          {
              data : 'Customer'
            },{
              data : 'Status',
              render : function( data, type, row, meta ){
                switch( type ){
                  case 'display' :
                    switch( data ){
                      case 0: return 'Open';
                      case 1: return 'Closed';
                      case 2: return 'On Hold';
                    }
                  default :
                    return data;
                }
              }
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
              d.Search = $("input[name='Search']").val( );
              d.Name = $("input[name='Name']").val( );
              d.Date_Start = $("input[name='Date_Start']").val( );
              d.Date_End = $("input[name='Date_End']").val( );
              d.Location = $("input[name='Location']").val( );
              d.Select = $("select[name='Select']").val( );
              d.ID = $("input[name='ID']").val( );
              d.Customer = $("input[name='Customer']").val( );
              d.Status = $("input[name='Status']").val( );
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
              d.Search = $("input[name='Search']").val( );
              d.Name = $("input[name='Name']").val( );
              d.Date_Start = $("input[name='Date_Start']").val( );
              d.Date_End = $("input[name='Date_End']").val( );
              d.Location = $("input[name='Location']").val( );
              d.Select = $("select[name='Select']").val( );
              d.ID = $("input[name='ID']").val( );
              d.Customer = $("input[name='Customer']").val( );
              d.Status = $("select[name='Status']").val( );
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
