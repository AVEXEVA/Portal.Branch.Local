$( document ).ready( function( ){
  var Table_Jobs = $('#Table_Jobs').DataTable( {
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
    ajax      : {
      url : 'bin/php/get/Jobs2.php',
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
        data    : 'ID'
      },{
        data : 'Name'
      },{
          data : 'Customer_ID',
          render : function( data, type, row, meta ){
              switch( type ){
                  case 'display' :
                      return  row.Customer_ID !== null 
                          ?   "<div class='row'>" + 
                                  "<div class='col-12'><a href='customer.php?ID=" + row.Customer_ID + "'>" + row.Customer_Name + "</a></div>" + 
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
                                  "<div class='col-12'><a href='location.php?ID=" + row.Location_ID + "'>" + row.Location_Name + "</a></div>" + 
                              "</div>"
                          :   null;
                  default :
                      return data;
              }

          }
      },{
        data : 'Type'
      },{
        data : 'Status'
      }
    ]
    } );
} );