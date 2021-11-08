$( document ).ready( function( ){
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
            url : 'bin/php/get/Customers2.php',
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
              data : 'ID',
              className : 'ID'
            },{
              data : 'Name'
            },{
              data : 'Status'
            },{
                data : 'Locations',
                render : function( data, type, row, meta ){
                    switch( type ){
                        case 'display' :
                            return  row.Locations !== null 
                                ?   "<div class='row'>" + 
                                        "<div class='col-12'><a href='locations.php?Customer=" + row.Name + "'>" + row.Locations + " locations</a></div>" + 
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
                                        "<div class='col-12'><a href='units.php?Customer=" + row.Name + "'>" + row.Units + " units</a></div>" + 
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
                                        "<div class='col-12'><a href='jobs.php?Customer=" + row.Name + "'>" + row.Jobs + " jobs</a></div>" + 
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
                                        "<div class='col-12'><a href='tickets.php?Customer=" + row.Name + "'>" + row.Tickets + " tickets</a></div>" + 
                                    "</div>"
                                :   null;
                        default :
                            return data;
                    }

                }
            }
        ],
        initComplete : function( ){
            $("div.search").html( "<input type='text' name='Search' placeholder='Search' />" );//onChange='$(\"#Table_Tickets\").DataTable().ajax.reload( );' 
            $('input.date').datepicker( { } );
            $('input.time').timepicker( {  timeFormat : 'h:i A' } );
            //search( this );
            $( '.redraw' ).bind( 'change', function(){ Table_Tickets.draw(); });
        }
    } );
} );