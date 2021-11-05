function search( link ){
    var api = link.api();
    $('input[name="Search"]', api.table().container())
        .typeahead({
            minLength : 4,
            highlight : 1,
            displayKey : 'FieldValue',
            source: function( query, result ){
                $.ajax({
                    url : 'cgi-bin/php/get/search/Locations.php',
                    method : 'GET',
                    data    : {
                        search : $('input[name="Search"]').val(),
                        ID :  $('input[name="ID"]').val( ),
                        Customer :  $('input[name="Customer"]').val( ),
                        Name :  $('input[name="Name"]').val( ),
                        Type : $('select[name="Type"]').val( ),
                        Division : $('select[name="Division"]').val( ),
                        Route : $('select[name="Route"]').val( ),
                        Street : $('input[name="Street"]').val( ),
                        City :  $('input[name="City"]').val( ),
                        Street :  $('input[name="Street"]').val( ),
                        State :  $('input[name="State"]').val( ),
                        Zip :  $('select[name="Zip"]').val( ),
                        Status : $('select[name="Status"]').val( ),
                        Maintaiend : $('select[name="Maintained"]').val( )
                    },
                    dataType : 'json',
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
    var Editor_Locations = new $.fn.dataTable.Editor( {
        idSrc    : 'ID',
        ajax     : 'index.php',
        table    : '#Table_Locations'
    } );
    var Table_Locations = $('#Table_Locations').DataTable( {
        dom            : "<'row'<'col-sm-3 search'><'col-sm-9'B>><'row'<'col-sm-12't>>",
        processing     : true,
        serverSide     : true,
        autoWidth      : false,
        searching      : false,
        lengthChange   : false,
        scrollResize   : true,
        scrollY        : 100,
        scroller       : true,
        scrollCollapse : true,
        paging         : true,
        orderCellsTop  : true,
        autoWidth      : true,
        stateSave      : true,
        responsive     : true,
        select         : {
          style : 'multi',
          selector : 'td.ID'
        },
        ajax: {
                url : 'cgi-bin/php/get/Locations3.php',
                data    : function(d){
                    d = {
                        start : d.start, 
                        length : d.length,
                        order : {
                            column : d.order[0].column,
                            dir : d.order[0].dir
                        },
                        ID :  $('input[name="ID"]').val( ),
                        Name :  $('input[name="Name"]').val( ),
                        Customer :  $('input[name="Customer"]').val( ),
                        Type : $('select[name="Type"]').val( ),
                        Division : $('select[name="Division"]').val( ),
                        Route : $('select[name="Route"]').val( ),
                        Street : $('input[name="Street"]').val( ),
                        City :  $('input[name="City"]').val( ),
                        Street :  $('input[name="Street"]').val( ),
                        State :  $('input[name="State"]').val( ),
                        Zip :  $('input[name="Zip"]').val( ),
                        Status : $('select[name="Status"]').val( ),
                        Maintained : $('select[name="Maintained"]').val( )
                    };
                    return d;
                }
        },
        columns: [
            {
                className : 'ID',
                data : 'ID'
            },{
                data : 'Name',
                render : function( data, type, row, meta ){
                    switch( type ){
                        case 'display' :
                            return  row.ID !== null 
                                ?   "<div class='row'>" + 
                                        "<div class='col-12'><a href='location.php?ID=" + row.ID + "'>" + row.Name + "</a></div>" + 
                                    "</div>"
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
                                        "<div class='col-12'><a href='customer.php?ID=" + row.Customer_ID + "'>" + row.Customer_Name + "</a></div>" + 
                                    "</div>"
                                :   null;
                        default :
                            return data;
                    }

                }
            },{
                data : 'Type'
            },{
                data : 'Division_ID',
                render : function( data, type, row, meta ){
                    switch( type ){
                        case 'display' :
                            return  row.Division_ID !== null 
                                ?   "<div class='row'>" + 
                                        "<div class='col-12'><a href='division.php?ID=" + row.Division_ID + "'>" + row.Division_Name + "</a></div>" + 
                                    "</div>"
                                :   null;
                        default :
                            return data;
                    }

                }
            },{
                data : 'Route_ID',
                render : function( data, type, row, meta ){
                    switch( type ){
                        case 'display' :
                            return  row.Route_ID !== null 
                                ?   "<div class='row'>" + 
                                        "<div class='col-12'><a href='route.php?ID=" + row.Route_ID + "'>" + row.Route_Name + "</a></div>" + 
                                        "<div class='col-12'><a href='user.php?ID=" + row.Mechanic_ID + "'>" + row.Mechanic_Name + "</a></div>" + 
                                    "</div>"
                                :   null;
                        default :
                            return data;
                    }

                }
            },{
                data : 'Street'
            },{
                data : 'City'
            },{
                data : 'State'
            },{
                data : 'Zip'
            },{
                data : 'Units'
            },{
                data : 'Status'
            },{
                data : 'Maintained'
            }
        ],
        initComplete : function( ){
            $("div.search").html( "<input type='text' name='Search' placeholder='Search' />" );//onChange='$(\"#Table_Locations\").DataTable().ajax.reload( );' 
            $('input.date').datepicker( { } );
            $('input.time').timepicker( {  timeFormat : 'h:i A' } );
            search( this );
            $( '.redraw' ).bind( 'change', function(){ Table_Locations.draw(); });
        },
        buttons: [
            {
                text: 'Reset Search',
                action: function ( e, dt, node, config ) {
                    $( 'input, select' ).each( function( ){
                        $( this ).val( '' );
                    } );
                    Table_Locations.draw( );
                }
            },
            { extend: 'create', editor: Editor_Locations },
            { extend: 'edit',   editor: Editor_Locations },
            { extend: 'remove', editor: Editor_Locations },
            'print',
            'copy',
            'csv'
        ]
    } );
} );
