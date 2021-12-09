function search( link ){
    var api = link.api();
    $('input:visible[name="Search"]', api.table().container())
        .typeahead({
            minLength : 4,
            highlight : 1,
            displayKey : 'FieldValue',
            source: function( query, result ){
                $.ajax({
                    url : 'bin/php/get/search/Locations.php',
                    method : 'GET',
                    data    : {
                        search : $('input:visible[name="Search"]').val(),
                        ID :  $('input:visible[name="ID"]').val( ),
                        Customer :  $('input:visible[name="Customer"]').val( ),
                        Name :  $('input:visible[name="Name"]:visible').val( ),
                        Type : $('select:visible[name="Type"]').val( ),
                        Division : $('select:visible[name="Division"]').val( ),
                        Route : $('select:visible[name="Route"]').val( ),
                        Street : $('input:visible[name="Street"]').val( ),
                        City :  $('input:visible[name="City"]').val( ),
                        Street :  $('input:visible[name="Street"]').val( ),
                        State :  $('input:visible[name="State"]').val( ),
                        Zip :  $('select:visible[name="Zip"]').val( ),
                        Status : $('select:visible[name="Status"]').val( ),
                        Maintaiend : $('select:visible[name="Maintained"]').val( )
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
                $( 'input:visible[name="' + FieldName.split( '_' )[ 0 ] + '"]' ).val ( FieldValue ).change( );
                $( 'input:visible[name="Search"]').val( '' );
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
        dom            : "<'row'<'col-sm-3 search'><'col-sm-6'B><'col-sm-3 columns-visibility'>><'row'<'col-sm-12't>>",
        processing     : true,
        serverSide     : true,
        autoWidth      : false,
        searching      : false,
        lengthChange   : false,
        scrollResize   : true,
        scrollY        : 100,
        scroller       : true,
        scrollCollapse : true,
        orderCellsTop  : true,
        autoWidth      : true,
        responsive     : true,
        select         : {
          style : 'multi',
          selector : 'td.ID'
        },
        ajax: {
                url : 'bin/php/get/Locations.php',
                data    : function(d){
                    d = {
                        draw : d.draw,
                        start : d.start,
                        length : d.length,
                        order : {
                            column : d.order[0].column,
                            dir : d.order[0].dir
                        },
                        ID :  $('input:visible[name="ID"]').val( ),
                        Name :  $('input:visible[name="Name"]').val( ),
                        Customer :  $('input:visible[name="Customer"]').val( ),
                        Type : $('select:visible[name="Type"]').val( ),
                        Division : $('select:visible[name="Division"]').val( ),
                        Route : $('select:visible[name="Route"]').val( ),
                        Street : $('input:visible[name="Street"]').val( ),
                        City :  $('input:visible[name="City"]').val( ),
                        Street :  $('input:visible[name="Street"]').val( ),
                        State :  $('input:visible[name="State"]').val( ),
                        Zip :  $('input:visible[name="Zip"]').val( ),
                        Status : $('select:visible[name="Status"]').val( ),
                        Maintained : $('select:visible[name="Maintained"]').val( )
                    };
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
                                      "<div class='col-12'><a href='customer.php?ID=" + row.ID + "'><i class='fa fa-folder-open fa-fw fa-1x'></i> Location #" + row.ID + "</a></div>" +
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
                                        "<div class='col-12'><a href='location.php?ID=" + row.ID + "'><i class='fa fa-building fa-fw fa-1x'></i>" + row.Name + "</a></div>" +
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
                                        "<div class='col-12'><a href='customer.php?ID=" + row.Customer_ID + "'><i class='fa fa-link fa-fw fa-1x'></i>" + row.Customer_Name + "</a></div>" +
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
                                        "<div class='col-12'><a href='division.php?ID=" + row.Division_ID + "'><i class='fa fa-divide fa-fw fa-1x'></i>" + row.Division_Name + "</a></div>" +
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
        initComplete : function( settings, json ){
            $("div.search").html( "<input type='text' name='Search' placeholder='Search' style='width: 100%;' />" );//onChange='$(\"#Table_Locations\").DataTable().ajax.reload( );'
            $("div.columns-visibility").html(
                "<div class='desktop bg-dark'>" +
                  "<div class='row'>" +
                    "<div class='col-3'>Toggle Columns:</div>" +
                    "<div class='col-9'>" +
                      "<a class='toggle-vis text-white' data-column='0'>ID</a>" + ' ' +
                      "<a class='toggle-vis text-white' data-column='1'>Name</a>" + ' ' +
                      "<a class='toggle-vis text-white' data-column='2'>Customer</a>" + ' ' +
                      "<a class='toggle-vis text-white' data-column='3'>Type</a>" + ' ' +
                      "<a class='toggle-vis text-white' data-column='4'>Division</a>" + ' ' +
                      "<a class='toggle-vis text-white' data-column='5'>Route</a>" + ' ' +
                      "<a class='toggle-vis text-white' data-column='6'>Street</a>" + ' ' +
                      "<a class='toggle-vis text-white' data-column='7'>City</a>" + ' ' +
                      "<a class='toggle-vis text-white' data-column='8'>State</a>" + ' ' +
                      "<a class='toggle-vis text-white' data-column='9'>Zip</a>" + ' ' +
                      "<a class='toggle-vis text-white' data-column='10'>Units</a>" + ' ' +
                      "<a class='toggle-vis text-white' data-column='11'>Maintained</a>" + ' ' +
                      "<a class='toggle-vis text-white' data-column='12'>Status</a>" + ' ' +
                      "<a class='toggle-vis text-white' data-column='13'>Labor</a>" + ' ' +
                      "<a class='toggle-vis text-white' data-column='14'>Revenue</a>" + ' ' +
                      "<a class='toggle-vis text-white' data-column='15'>Net Income</a>" +
                    "</div>" +
                  "</div>" +
                "</div>"
            );
            $('input.date').datepicker( { } );
            $('input.time').timepicker( {  timeFormat : 'h:i A' } );
            search( this );
            $( '.redraw' ).bind( 'change', function(){ Table_Locations.draw(); });
            $('a.toggle-vis').bind( 'click', function( e ){
                e.preventDefault();
                columnVisibility( this, Table_Locations );
            });
        },
        buttons: [
            {
                text: 'Reset Search',
                className : 'form-control',
                action: function ( e, dt, node, config ) {
                    $( 'input, select' ).each( function( ){
                        $( this ).val( '' );
                    } );
                    Table_Locations.draw( );
                }
            },{
                text : 'Create',
                className : 'form-control',
                action : function( e, dt, node, config ){
                    document.location.href='location.php';
                }
              },{
              text : 'Delete',
              className : 'form-control',
              action : function( e, dt, node, config ){
                var rows = dt.rows( { selected : true } ).indexes( );
                var dte = dt.cells( rows, 0 ).data( ).toArray( );
                $.ajax ({
                  url    : 'bin/php/post/Location.php',
                  method : 'POST',
                  data   : {
                    action : 'delete' ,
                    data : dte
                  },
                  success : function(response){
                    Table_Leads.draw();
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
