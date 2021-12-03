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
                          Customer              :  $('input:visible[name="Customer"]').val( ),
                          Location              :  $('input:visible[name="Location"]').val( ),
                          Units                  :  $('input:visible[name="Units"]').val( ),
                          Status                :  $('select:visible[name="Status"]').val( ),
                          Date                  :  $('input:visible[name="Date"]').val( )
                        
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
        select         : {
            style : 'multi',
            selector : 'td.ID'
        },
      ajax : {
        url : 'bin/php/get/Violations.php',
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
             d.ID = $("input[name='ID']").val( );
            d.Customer = $("input[name='Customer']").val( );
            d.Location = $("input[name='Location']").val( );
            d.Units = $("input[name='Units']").val( );
            d.Date = $("input[name='Date']").val( );
           d.Status = $("input[name='Status']").val( );
            return d;
        }
        
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
            data : 'Customer',
            render : function( data, type, row, meta ){
                switch( type ){
                    case 'display' :
                        return  row.Customer !== null
                            ?   "<div class='row'>" +
                                    "<div class='col-12'><a href='customer.php?ID=" + row.Customer_ID + "'><i class='fa fa-link fa-fw fa-1x'></i> " + row.Customer + " </a></div>" +
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
            data : 'Date'
        },{
              data : 'Units',
              render : function( data, type, row, meta ){
                  switch( type ){
                      case 'display' :
                          return  row.Units !== null
                              ?   "<div class='row'>" +
                                      "<div class='col-12'><a href='unit.php?ID=" + row.Unit_ID + "'><i class='fa fa-cogs fa-fw fa-1x'></i> " + row.Units + " </a></div>" +
                                  "</div>"
                              :   null;
                      default :
                          return data;
                  }
              }
        },{
        data : 'Status',
        render:function(data){
          switch(data){
            case '0': return "<div class='row'><div class='col-12'>Active<div></div>";
            case '1': return "<div class='row'><div class='col-12'>InActive<div></div>";
            case '2': return "<div class='row'><div class='col-12'>Demolished<div></div>";
          }
        }
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
             d.ID = $('input[name="ID"]').val( );
            d.Customer = $('input[name="Customer"]').val( );
             d.Location = $('input[name="Location"]').val( );
             d.Date = $('input[name="Date"]').val( );
              d.Units = $('input[name="Units"]').val( );
            d.Status = $('input[name="Status"]').val( );
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
