    function hrefInvoice(){hrefRow('Table_Invoices','invoice');}
var Table_Invoices = $('#Table_Invoices').DataTable( {
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
  //stateSave      : true,
  responsive     : {
    details : {
      type   : 'column',
      target : 0
    }
  },
  select         : {
    style : 'multi',
    selector : 'td.ID'
  },
  ajax: {
    url     : 'bin/php/get/Invoices2.php',
    data : function( d ){
          d = {
              start : d.start,
              length : d.length,
              order : {
                  column : d.order[0].column,
                  dir : d.order[0].dir
              }
          };
          d.Search = $('input[name='Search']').val( );
          d.Customer = $('input[name='Customer']').val( );
          d.Location = $('input[name='Location']').val( );
          d.Job = $('input[name='Job']').val( );
          return d;
          }
  },
  columns: [
    {
      data : 'ID' ,
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
                                "<div class='col-12'><a href='location.php?ID=" + row.Location_ID + "'>" + row.Location_Tag + "</a></div>" +
                            "</div>"
                        :   null;
                default :
                    return data;
            }

        }
    },{
        data : 'Job_ID',
        render : function( data, type, row, meta ){
            switch( type ){
                case 'display':
                    return row.Job_ID !== null
                        ?   "<div class='row'>" +
                                "<div class='col-12'><a href='job.php?ID=" + row.Job_ID   + "'>" + row.Job_ID + "</a></div>" +
                                "<div class='col-12'><a href='job.php?ID=" + row.Job_ID   + "'>" + row.Job_Name + "</a></div>" +
                            "</div>"
                        :   null;
                    default :
                        return data;
            }
        }
    },{
      data : 'Type'
    },{
      data   : 'Date'
    },{
      data   : 'Due'
    },{
      data      : 'Original',
      className :'sum'
    },{
      data      : 'Balance',
      className : 'sum'
    },{
      data : 'Description'
    }
  ]
} );
