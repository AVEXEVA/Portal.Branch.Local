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
                      url : 'bin/php/get/search/Violations.php',
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
$( document ).ready( function( ){
  
     var Editor_Users = new $.fn.dataTable.Editor( {
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
      ajax : {
        data : function( d ){
            d = {
              draw : d.draw,
                start : d.start,
                length : d.length,
                order : {
                    column : d.order[0].column,
                    dir : d.order[0].dir
                }
            };
            d.Search = $("input[name='Search']").val( );
            d.Name = $("input[name='Customer']").val( );
            d.Location = $("input[name='Location']").val( );
            d.Date = $("input[name='Date']").val( );
            d.ID = $("input[name='ID']").val( );
              d.Units = $("input[name='Units']").val( );
            d.Status = $("input[name='Status']").val( );
            return d;
        },
        url : 'bin/php/get/Violations.php'
    },
      columns    : [
        {
          className : 'ID',
          data : 'ID',
          render : function( data, type, row, meta ){
            switch( type ){
              case 'display' :
                return  row.ID !== null
                  ?   "<div class='row'>" +
                          "<div class='col-12'><a href='violations.php?ID=" + row.ID + "'><i class='fa fa-folder-open fa-fw fa-1x'></i> Route #" + row.ID + "</a></div>" +
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
                      return  row.ID !== null
                          ?   "<div class='row'>" +
                                  "<div class='col-12'><a href='violation.php?ID=" + row.ID + "'><i class='fa fa-link fa-fw fa-1x'></i> " + row.Name + "</a></div>" +
                              "</div>"
                          :   null;
                  default :
                      return data;
              }
          }
        },{
            data : 'Locations',
            render : function( data, type, row, meta ){
                switch( type ){
                    case 'display' :
                        return  row.Locations !== null
                            ?   "<div class='row'>" +
                                    "<div class='col-12'><a href='location.php?ID=" + row.Location_ID + "'><i class='fa fa-link fa-building fa-fw fa-1x'></i> " + row.Locations + " locations</a></div>" +
                                "</div>"
                            :   null;
                    default :
                        return data;
                }
            }
          },{
              data : 'Units',
              render : function( data, type, row, meta ){
                  switch( type ){
                      case 'display' :
                          return  row.Units !== null
                              ?   "<div class='row'>" +
                                      "<div class='col-12'><a href='unit.php?ID=" + row.Unit_ID + "'><i class='fa fa-cogs fa-fw fa-1x'></i> " + row.Units + " units</a></div>" +
                                  "</div>"
                              :   null;
                      default :
                          return data;
                  }
              }
        },{
            data : 'Customer',
            render : function( data, type, row, meta ){
                switch( type ){
                    case 'display' :
                        return  row.Customer !== null
                            ?   "<div class='row'>" +
                                    "<div class='col-12'><a href='customer.php?ID=" + row.Customer_ID + "'><i class='fa fa-link fa-fw fa-1x'></i> " + row.Locations + " locations</a></div>" +
                                "</div>"
                            :   null;
                    default :
                        return data;
                }
            }
        },{
            data : 'Date'
        }
    ],
    
    initComplete : function( ){
      $("div.search").html( "<input type='text' name='Search' placeholder='Search' />" );
      $('input.date').datepicker( { } );
      $('input.time').timepicker( {  timeFormat : 'h:i A' } );
      search( this );
      $( '.redraw' ).bind( 'change', function(){ Table_Violations.draw(); });
    },
    buttons : [
      {
        text: 'Reset Search',
        className : 'form-control',
        action: function ( e, dt, node, config ) {
            $( 'input, select' ).each( function( ){
                $( this ).val( '' );
            } );
            Table_Violations.draw( );
        }
      },{
        text : 'Get URL',
        className : 'form-control',
        action : function( e, dt, node, config ){
            d = { }
            d.Name = $('input[name="Name"]').val( );
            d.Person = $('select[name="Person"]').val( );
            document.location.href = 'violations.php?' + new URLSearchParams( d ).toString();
        }
      },{
        text : 'Create',
        className : 'form-control',
        action : function( e, dt, node, config ){
            document.location.href='violation.php';
        }
      },{
        text : 'Delete',
        className : 'form-control',
        action : function( e, dt, node, config ){
          var rows = dt.rows( { selected : true } ).indexes( );
          var dte = dt.cells( rows, 0 ).data( ).toArray( );
          $.ajax ({
            url    : 'bin/php/post/violation.php',
            method : 'POST',
            data   : {
              action : 'delete',
              data : dte
            },
            success : function(response){
              Table_Violations.draw();
            }
          })
        }
      },{
          extend : 'print',
          text : 'Print',
          className : 'form-control'
      },{
          extend : 'copy',
          text : 'Copy',
          className : 'form-control'
      },{
          extend : 'csv',
          text : 'CSV',
          className : 'form-control'
      }
    ]
  } );
});
