<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
header('Content-Type: text/javascript');
?>
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
                        Customer_ID :  $('input:visible[name="Customer_ID"]').val( ),
                        Customer_Name :  $('input:visible[name="Customer_Name"]').val( ),
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
        <?php \singleton\datatables::getInstance( )->preferences( );?>,
        ajax: {
                url : 'bin/php/get/Locations.php',
                <?php \singleton\datatables::getInstance( )->ajax_data( );?>
        },
        columns: [
            <?php \singleton\datatables::getInstance( )->ID( 'Location' );?>,
            <?php \singleton\datatables::getInstance( )->Name( 'Location' );?>,
            <?php \singleton\datatables::getInstance( )->Customer( );?>,
            <?php \singleton\datatables::getInstance( )->Type( );?>,
            <?php \singleton\datatables::getInstance( )->Division( );?>,
            <?php \singleton\datatables::getInstance( )->Route( );?>,
            <?php \singleton\datatables::getInstance( )->data_column('Street');?>,
            <?php \singleton\datatables::getInstance( )->data_column('City');?>,
            <?php \singleton\datatables::getInstance( )->data_column('State');?>,
            <?php \singleton\datatables::getInstance( )->data_column('Zip');?>,
            <?php \singleton\datatables::getInstance( )->data_column('Units');?>,
            <?php \singleton\datatables::getInstance( )->data_column('Maintained');?>,
            <?php \singleton\datatables::getInstance( )->data_column('Status');?>            
        ],
        <?php \singleton\datatables::getInstance( )->initComplete( 'locations' );?>,
        <?php \singleton\datatables::getInstance( )->buttons( 'location', 'locations', 'ID' );?>
    } );
});
