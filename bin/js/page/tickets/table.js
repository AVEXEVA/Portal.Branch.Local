function search( link ){
    var api = link.api();
    $('input[name="Search"]', api.table().container())
        .typeahead({
            minLength : 4,
            highlight : 1,
            displayKey : 'FieldValue',
            source: function( query, result ){
                $.ajax({
                    url : 'bin/php/get/search/Tickets.php',
                    method : 'GET',
                    data    : {
                        search                : $('input[name="Search"]').val(),
                        ID                    :  $('input[name="ID"]').val( ),
                        Person                :  $('input[name="Person"]').val( ),
                        Customer              :  $('input[name="Customer"]').val( ),
                        Location              :  $('input[name="Location"]').val( ),
                        Unit                  :  $('input[name="Unit"]').val( ),
                        Job                   :  $('input[name="Job"]').val( ),
                        Type                  :  $('select[name="Type"]').val( ),
                        Level                 :  $('select[name="Level"]').val( ),
                        Status                :  $('select[name="Status"]').val( ),
                        Start_Date            :  $('input[name="Start_Date"]').val( ),
                        End_Date              :  $('input[name="End_Date"]').val( ),
                        Time_Route_Start      :  $('input[name="Time_Route_Start"]').val( ),
                        Time_Route_End        :  $('input[name="Time_Route_End"]').val( ),
                        Time_Site_Start       :  $('input[name="Time_Site_Start"]').val( ),
                        Time_Site_End         :  $('input[name="Time_Site_End"]').val( ),
                        Time_Completed_Start  :  $('input[name="Time_Completed_Start"]').val( ),
                        Time_Completed_End    :  $('input[name="Time_Completed_End"]').val( ),
                        LSD                   :  $('select[name="LSD"]').val( )
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
$(document).ready(function( ){
    var Editor_Tickets = new $.fn.dataTable.Editor( {
        idSrc    : 'ID',
        ajax     : 'index.php',
        table    : '#Table_Tickets'
    } );
    var Table_Tickets = $('#Table_Tickets').DataTable( {
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
        ajax: {
                url     : 'bin/php/get/Tickets.php',
                data    : function(d){
                    d = {
                        draw : d.draw,
                        start : d.start, 
                        length : d.length,
                        order : {
                            column : d.order[0].column,
                            dir : d.order[0].dir
                        }
                    };
                    d.ID             = $('input:visible[name="ID"]').val( );
                    d.Person         = $('input:visible[name="Person"]').val( );
                    d.Customer       = $('input:visible[name="Customer"]').val( );
                    d.Location       = $('input:visible[name="Location"]').val( );
                    d.Unit           = $('input:visible[name="Unit"]').val( );
                    d.Job            = $('input:visible[name="Job"]').val( );
                    d.Type           = $('select:visible[name="Type"]').val( );
                    d.Level          = $('select:visible[name="Level"]').val( ); 
                    d.Status         = $('select:visible[name="Status"]').val( );
                    d.Start_Date     = $('input:visible[name="Start_Date"]').val( );
                    d.End_Date       = $('input:visible[name="End_Date"]').val( );
                    d.Time_Route_Start     = $('input:visible[name="Time_Route_Start"]').val( );
                    d.Time_Route_End       = $('input:visible[name="Time_Route_End"]').val( );
                    d.Time_Site_Start     = $('input:visible[name="Time_Site_Start"]').val( );
                    d.Time_Site_End       = $('input:visible[name="Time_Site_End"]').val( );
                    d.Time_Completed_Start     = $('input:visible[name="Time_Completed_Start"]').val( );
                    d.Time_Completed_End       = $('input:visible[name="Time_Completed_End"]').val( );
                    d.LSD       = $('select[name="LSD"]').val( );
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
                                        "<div class='col-12'><a href='ticket.php?ID=" + row.ID + "'><i class='fa fa-folder-open fa-fw fa-1x'></i> Ticket #" + row.ID + "</a></div>" + 
                                    "</div>"
                                :   null;
                        default :
                            return data;
                    }

                }
            },{
                data : 'Person',
                render : function( data, type, row, meta ){
                    switch( type ){
                        case 'display':
                            return row.Employee_ID !== null 
                                ?   "<a href='user.php?ID=" + row.Employee_ID + "'><i class='fa fa-user fa-fw fa-1x'></i>" + row.Person + "</a>" 
                                :   null;
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
                                        "<div class='col-12'><a href='location.php?ID=" + row.Location_ID + "'><i class='fa fa-building fa-fw fa-1x'></i>" + row.Location_Tag + "</a></div>" + 
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
                data : 'Unit_ID',
                render : function( data, type, row, meta ){
                    switch( type ){
                        case 'display' :
                            return  row.Unit_ID !== null 
                                ?   "<div class='row'>" + 
                                        "<div class='col-12'><a href='unit.php?ID=" + row.Unit_ID + "'><i class='fa fa-cogs fa-fw fa-1x'></i>" + ( row.Unit_City_ID !== null && !row.Unit_City_ID.replace(/\s/g, '' ).length < 1 ? row.Unit_City_ID : 'Missing City ID' ) + "</a></div>" + 
                                        "<div class='col-12'><a href='unit.php?ID=" + row.Unit_ID + "'><i class='fa fa-map-signs fa-fw fa-1x'></i>" + row.Unit_Building_ID + "</a></div>" + 
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
                                        "<div class='col-12'><a href='job.php?ID=" + row.Job_ID   + "'><i class='fa fa-suitcase fa-fw fa-1x'></i>" + row.Job_ID + "</a></div>" + 
                                        "<div class='col-12'><a href='job.php?ID=" + row.Job_ID   + "'>" + row.Job_Name + "</a></div>" +
                                    "</div>"
                                :   null;
                            default :
                                return data;
                    }
                }
            },{
                data : 'Level',
                render : function( data, type, row, meta ){
                    switch ( type ){
                        case 'display':
                            return row.Job_Type !== null
                                ?   "<div class='row'>" + 
                                        "<div class='col-12'>" + row.Job_Type + "</div>" +
                                        "<div class='col-12'>" + row.Level + "</div>" +
                                    "</div>"
                                :   null;
                        default :
                            return data;
                    }
                }
            },{
                data : 'Status'
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
                data : 'Time_Route',
                render: function( data, type, row, meta ){
                    switch( type ){
                        case 'display':
                            return row.Date !== null 
                                ?   "<div class='row'>" +
                                        "<div class='col-12'><i class='fa fa-clock-o fa-fw fa-1x'></i>" + row.Time_Route + "</div>" + 
                                    "</div>"
                                :   null;
                            default : 
                                return data;

                    }
                }
            },{
                data : 'Time_Site',
                render: function( data, type, row, meta ){
                    switch( type ){
                        case 'display':
                            return row.Date !== null 
                                ?   "<div class='row'>" +
                                        "<div class='col-12'><i class='fa fa-clock-o fa-fw fa-1x'></i>" + row.Time_Site + "</div>" + 
                                    "</div>"
                                :   null;
                            default : 
                                return data;

                    }
                }
            },{
                data : 'Time_Completed',
                render: function( data, type, row, meta ){
                    switch( type ){
                        case 'display':
                            return row.Date !== null 
                                ?   "<div class='row'>" +
                                        "<div class='col-12'><i class='fa fa-clock-o fa-fw fa-1x'></i>" + row.Time_Completed + "</div>" + 
                                    "</div>"
                                :   null;
                            default : 
                                return data;

                    }
                }
            },{
                data : 'Hours',
                defaultContent :"0"
            },{
                data : 'LSD',
                render : function( data, type, row, meta ){
                    switch ( type ){
                        case 'display':
                            return row.LSD == 1
                                ? 'LSD'
                                : 'Running';
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
            search( this );
            $( '.redraw' ).bind( 'change', function(){ Table_Tickets.draw(); });
        },
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
                        success : function( response ){ }
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
                    var d = { };
                    d.ID             = $('input[name="ID"]').val( );
                    d.Person         = $('input[name="Person"]').val( );
                    d.Customer       = $('input[name="Customer"]').val( );
                    d.Location       = $('input[name="Location"]').val( );
                    d.Unit           = $('input[name="Unit"]').val( );
                    d.Job            = $('input[name="Job"]').val( );
                    d.Type           = $('select[name="Type"]').val( );
                    d.Level          = $('select[name="Level"]').val( ); 
                    d.Status         = $('select[name="Status"]').val( );
                    d.Start_Date     = $('input[name="Start_Date"]').val( );
                    d.End_Date       = $('input[name="End_Date"]').val( );
                    d.Time_Route_Start     = $('input[name="Time_Route_Start"]').val( );
                    d.Time_Route_End       = $('input[name="Time_Route_End"]').val( );
                    d.Time_Site_Start     = $('input[name="Time_Site_Start"]').val( );
                    d.Time_Site_End       = $('input[name="Time_Site_End"]').val( );
                    d.Time_Completed_Start     = $('input[name="Time_Completed_Start"]').val( );
                    d.Time_Completed_End       = $('input[name="Time_Completed_End"]').val( );
                    d.LSD       = $('select[name="LSD"]').val( );
                    document.location.href = 'tickets.php?' + new URLSearchParams( d ).toString();
                }
            },
            { extend: 'create', editor: Editor_Tickets },
            { extend: 'edit',   editor: Editor_Tickets },
            { extend: 'remove', editor: Editor_Tickets },
            {
                text: 'Print',
                action: function ( e, dt, node, config ) {
                    var rows = dt.rows( { selected : true } ).indexes( );
                    var dte = dt.cells( rows, 0 ).data( ).toArray( );
                    document.location.href = 'print_tickets.php?Tickets=' + dte.join( ',' );
                }
            },
            'copy',
            'csv'
        ]
    } );
} );
