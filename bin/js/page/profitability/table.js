$( document ).ready( function( ){
    var Editor_Profitability = new $.fn.dataTable.Editor( {
        idSrc    : 'ID',
        ajax     : 'index.php',
        table    : '#Table_Profitability'
    } );
    var Table_Profitability = $('#Table_Profitability').DataTable( {
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
        select         : {
          style : 'multi',
          selector : 'td.ID'
        },
        ajax      : {
            url : 'bin/php/get/Profitability.php',
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
                d.Customer_ID = $('input[name="Search"]').val( );
                d.Profit = $('input[name="ID"]').val( );
                d.Revenue = $('input[name="Name"]').val( );
                d.Material = $('select[name="Status"]').val( );
                d.Labor = $('select[name="Status"]').val( );
                return d;
            }
        },
        columns: [
         {
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
                data : 'Profit',
                render : function( data, type, row, meta ){
                    switch( type ){
                        case 'display' :
                            return  row.Profit !== null
                                ?   "<div class='row'>" +
                                        "<div class='col-12'><a href='profitability.php?ID=" + row.Profit + "'><i class='fa fa-link fa-fw fa-1x'></i> " + row.Profit + "</a></div>" +
                                    "</div>"
                                :   null;
                        default :
                            return data;
                    }
                }
              },{
                data : 'Revenue',
                render : function( data, type, row, meta ){
                    switch( type ){
                        case 'display' :
                            return  row.Revenue !== null
                                ?   "<div class='row'>" +
                                        "<div class='col-12'><a href='profitability.php?ID=" + row.Revenue + "'><i class='fa fa-link fa-fw fa-1x'></i> " + row.Revenue + "</a></div>" +
                                    "</div>"
                                :   null;
                        default :
                            return data;
                    }
                }
            },{
                data : 'Material'
            },{
                data : 'Labor'
            }
        ],
        buttons: [
            {
                text: 'Reset Search',
                className : 'form-control',
                action: function ( e, dt, node, config ) {
                    $( 'input:visible, select:visible' ).each( function( ){
                        $( this ).val( '' );
                    } );
                    Table_Profitability.draw( );
                }
            },{
                text : 'Get URL',
                className : 'form-control',
                action : function( e, dt, node, config ){
                    d = { }
                    d.ID = $('input[name="ID"]').val( );
                    d.Name = $('input[name="Name"]').val( );
                    d.Status = $('select[name="Status"]').val( );
                    document.location.href = 'Profitability.php?' + new URLSearchParams( d ).toString();
                }
            },{
                text : 'Create',
                className : 'form-control',
                action : function( e, dt, node, config ){
                    document.location.href='bin/php/reports/Profitability.php';
                }
            },
            {
                text : 'Delete',
                className : 'form-control',
                action : function( e, dt, node, config ){
                  var rows = dt.rows( { selected : true } ).indexes( );
                  var dte = dt.cells( rows, 0 ).data( ).toArray( );
                  $.ajax ({
                    url    : 'bin/php/post/profitability.php',
                    method : 'POST',
                    data   : {
                      action : 'delete' ,
                      data : dte
                    },
                    success : function(response){
                      Table_Profitability.draw();
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
        ],
        initComplete : function( ){
            $("div.search").html( "<input type='text' name='Search' placeholder='Search' />" );//onChange='$(\"#Table_Tickets\").DataTable().ajax.reload( );'
            $('input.date').datepicker( { } );
            $('input.time').timepicker( {  timeFormat : 'h:i A' } );
            //search( this );
            $( '.redraw' ).bind( 'change', function(){ Table_Profitability.draw(); });
        }
    } );
} );
