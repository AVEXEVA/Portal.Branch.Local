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
    var Table_Customers = $('#Table_Customers').DataTable( {
        <?php \singleton\datatables::getInstance( )->preferences( );?>,
        ajax      : {
            url : 'bin/php/get/Customers.php',
            <?php \singleton\datatables::getInstance( )->ajax_data( );?>
        },
        columns: [
            <?php \singleton\datatables::getInstance( )->ID('Customer' );?>,
            <?php \singleton\datatables::getInstance( )->Name( 'Customer' );?>,
            <?php \singleton\datatables::getInstance( )->Status( );?>,
            <?php \singleton\datatables::getInstance( )->Locations( 'Customer', 'ID' );?>,
            <?php \singleton\datatables::getInstance( )->Units( 'Customer', 'ID' );?>,
            <?php \singleton\datatables::getInstance( )->Jobs( 'Customer', 'ID' );?>,
            <?php \singleton\datatables::getInstance( )->Tickets( 'Customer', 'ID' );?>,
            <?php \singleton\datatables::getInstance( )->Violations( 'Customer', 'ID' );?>,
            <?php \singleton\datatables::getInstance( )->Invoices( 'Customer', 'ID' );?>
        ],
        <?php \singleton\datatables::getInstance( )->buttons('customer', 'customers', 'ID' );?>,
        <?php \singleton\datatables::getInstance( )->initComplete( 'customers' );?>
    } );
} );
