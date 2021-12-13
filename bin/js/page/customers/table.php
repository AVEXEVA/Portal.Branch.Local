<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
header('Content-Type: text/javascript');?>
function search( link ){
    var api = link.api();
    $('input:visible[name="Search"]', api.table().container())
        .typeahead({
            minLength : 4,
            highlight : 1,
            displayKey : 'FieldValue',
            source: function( query, result ){
                $.ajax({
                    url : 'bin/php/get/search/Customers.php',
                    method : 'GET',
                    data    : {
                        search : $('input:visible[name="Search"]').val(),
                        ID :  $('input:visible[name="ID"]').val( ),
                        Name :  $('input:visible[name="Contact"]:visible').val( ),
                        Status :  $('select:visible[name="Contact"]:visible').val( ),
                        Location :  $('Input:visible[name="Type"]:visible').val( ),
                        Unit :  $('input:visible[name="Name"]:visible').val( ),
                        Job : $('input:visible[name="Phone"]').val( ),
                        Ticket :  $('input:visible[name="Customer"]').val( ),
                        Violation : $('select:visible[name="Type"]').val( ),
                        Invoice : $('select:visible[name="Division"]').val( ),
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
                    draw : d.draw,
                    start : d.start,
                    length : d.length,
                    order : {
                        column : d.order[0].column,
                        dir : d.order[0].dir
                    }
                };
                d.Search = $('input:visible[name="Search"]').val( );
                d.ID = $('input:visible[name="ID"]').val( );
                d.Name = $('input:visible[name="Name"]').val( );
                d.Status = $('select:visible[name="Status"]').val( );
                d.Location = $('select:visible[name="Location"]').val( );
                d.Units = $('select:visible[name="Units"]').val( );
                d.Jobs = $('select:visible[name="Jobs"]').val( );
                d.Tickets = $('select:visible[name="Tickets"]').val( );
                d.Violations = $('select:visible[name="Violations"]').val( );
                d.Invoices = $('select:visible[name="Invoices"]').val( );
                return d;
            }
        },
        columns: [
            <?php \singleton\datatables::getInstance( )->ID( );?>,
            <?php \singleton\datatables::getInstance( )->Name( );?>,
            <?php \singleton\datatables::getInstance( )->Status( );?>,
            <?php \singleton\datatables::getInstance( )->Locations( );?>,
            <?php \singleton\datatables::getInstance( )->Units( );?>,
            <?php \singleton\datatables::getInstance( )->Jobs( );?>,
            <?php \singleton\datatables::getInstance( )->Tickets( );?>,
            <?php \singleton\datatables::getInstance( )->Violations( );?>,
            <?php \singleton\datatables::getInstance( )->Invoices( );?>
        ],
        buttons: [
            {
                text: 'Reset Search',
                className : 'form-control',
                action: function ( e, dt, node, config ) {
                    $( 'input:visible, select:visible' ).each( function( ){
                        $( this ).val( '' );
                    } );
                    Table_Customers.draw( );
                }
            },{
                text : 'Get URL',
                className : 'form-control',
                action : function( e, dt, node, config ){
                    d = { }
                    d.ID = $('input[name="ID"]').val( );
                    d.Name = $('input[name="Name"]').val( );
                    d.Status = $('select[name="Status"]').val( );
                    document.location.href = 'customers.php?' + new URLSearchParams( d ).toString();
                }
            },{
                text : 'Create',
                className : 'form-control',
                action : function( e, dt, node, config ){
                    document.location.href='customer.php';
                }
            },
            {
                text : 'Delete',
                className : 'form-control',
                action : function( e, dt, node, config ){
                  var rows = dt.rows( { selected : true } ).indexes( );
                  var dte = dt.cells( rows, 0 ).data( ).toArray( );
                  $.ajax ({
                    url    : 'bin/php/post/customer.php',
                    method : 'POST',
                    data   : {
                      action : 'delete' ,
                      data : dte
                    },
                    success : function(response){
                      Table_Customers.draw();
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
            $( '.redraw' ).bind( 'change', function(){ Table_Customers.draw(); });
        }
    } );
} );
