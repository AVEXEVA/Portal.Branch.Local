$( document ).ready( function( ){
    var Editor_Customers = new $.fn.dataTable.Editor( {
        idSrc    : 'ID',
        ajax     : 'index.php',
        table    : '#Table_Customers'
    } );
    var Table_Customers = $('#Table_Customers').DataTable( {
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
            url : 'bin/php/get/Customers.php',
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
                d.Status = $('select[name="Status"]').val( );
                return d;
            }
        },
        columns: [
            {
                  className : 'ID',
                  data : 'ID',
                  render : function( data, type, row, meta ){
                      switch( type ){
                          case 'display' :
                              return  row.ID !== null 
                                  ?   "<div class='row'>" + 
                                          "<div class='col-12'><a href='customer.php?ID=" + row.ID + "'><i class='fa fa-folder-open fa-fw fa-1x'></i> Customer #" + row.ID + "</a></div>" + 
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
                                        "<div class='col-12'><a href='customer.php?ID=" + row.ID + "'><i class='fa fa-link fa-fw fa-1x'></i> " + row.Name + "</a></div>" + 
                                    "</div>"
                                :   null;
                        default :
                            return data;
                    }
                }
            },{
              data : 'Status'
            },{
                data : 'Locations',
                render : function( data, type, row, meta ){
                    switch( type ){
                        case 'display' :
                            return  row.Locations !== null 
                                ?   "<div class='row'>" + 
                                        "<div class='col-12'><a href='locations.php?Customer=" + row.Name + "'><i class='fa fa-link fa-building fa-fw fa-1x'></i> " + row.Locations + " locations</a></div>" + 
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
                                        "<div class='col-12'><a href='units.php?Customer=" + row.Name + "'><i class='fa fa-cogs fa-fw fa-1x'></i> " + row.Units + " units</a></div>" + 
                                    "</div>"
                                :   null;
                        default :
                            return data;
                    }

                }
            },{
                data : 'Jobs',
                render : function( data, type, row, meta ){
                    switch( type ){
                        case 'display' :
                            return  row.Jobs !== null 
                                ?   "<div class='row'>" + 
                                        "<div class='col-12'><a href='jobs.php?Customer=" + row.Name + "'><i class='fa fa-suitcase fa-fw fa-1x'></i> " + row.Jobs + " jobs</a></div>" + 
                                    "</div>"
                                :   null;
                        default :
                            return data;
                    }

                }
            },{
                data : 'Tickets',
                render : function( data, type, row, meta ){
                    switch( type ){
                        case 'display' :
                            return  row.Tickets !== null 
                                ?   "<div class='row'>" + 
                                        "<div class='col-12'><a href='tickets.php?Customer=" + row.Name + "'><i class='fa fa-ticket fa-fw fa-1x'></i> " + row.Tickets + " tickets</a></div>" + 
                                    "</div>"
                                :   null;
                        default :
                            return data;
                    }

                }
            },{
                data : 'Violations',
                render : function( data, type, row, meta ){
                    switch( type ){
                        case 'display' :
                            return  row.Tickets !== null 
                                ?   "<div class='row'>" + 
                                        "<div class='col-12'><a href='violations.php?Customer=" + row.Name + "'><i class='fa fa-warning fa-fw fa-1x'></i> " + row.Violations + " violations</a></div>" + 
                                    "</div>"
                                :   null;
                        default :
                            return data;
                    }

                }
            },{
                data : 'Invoices',
                render : function( data, type, row, meta ){
                    switch( type ){
                        case 'display' :
                            return  row.Tickets !== null 
                                ?   "<div class='row'>" + 
                                        "<div class='col-12'><a href='invoices.php?Customer=" + row.Name + "'><i class='fa fa-stack-overflow fa-fw fa-1x'></i> " + row.Invoices + " invoices</a></div>" + 
                                    "</div>"
                                :   null;
                        default :
                            return data;
                    }

                }
            }
        ],
        buttons: [
            {
                text: 'Email Ticket',
                action: function ( e, dt, node, config ) {
                    var rows = dt.rows( { selected : true } ).indexes( );
                    var dte = dt.cells( rows, 0 ).data( ).toArray( );
                    $.ajax({
                        url : 'bin/php/post/emailTicket.php',
                        method : 'POST',
                        data : {
                            email : prompt( "What email would you like to send the ticket to?"),
                            data : dte
                        },
                        success : function( response ){
                            console.log( response );
                        }
                    });
                }
            },{
                text: 'Reset Search',
                action: function ( e, dt, node, config ) {
                    $( 'input, select' ).each( function( ){
                        $( this ).val( '' );
                    } );
                    Table_Tickets.draw( );
                }
            },{
                text : 'Get URL',
                action : function( e, dt, node, config ){
                    d = { }
                    d.ID = $('input[name="ID"]').val( );
                    d.Name = $('input[name="Name"]').val( );
                    d.Status = $('select[name="Status"]').val( );
                    document.location.href = 'customers.php?' + new URLSearchParams( d ).toString();
                }
            },
            { extend: 'create', editor: Editor_Customers },
            { extend: 'edit',   editor: Editor_Customers },
            { extend: 'remove', editor: Editor_Customers },
            'copy',
            'csv'
        ],
        initComplete : function( ){
            $("div.search").html( "<input type='text' name='Search' placeholder='Search' />" );//onChange='$(\"#Table_Tickets\").DataTable().ajax.reload( );' 
            $('input.date').datepicker( { } );
            $('input.time').timepicker( {  timeFormat : 'h:i A' } );
            //search( this );
            $( '.redraw' ).bind( 'change', function(){ Table_Customers.draw(); });
        }
    } );
} );