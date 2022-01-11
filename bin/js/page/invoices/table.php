<?php
if( session_id( ) == '' || !isset( $_SESSION ) ) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
header('Content-Type: text/javascript');?>
$( document ).ready( function( ){
function search( link ){
    var api = link.api();
    $('input[name="Search"]', api.table().container())
        .typeahead({
            minLength : 4,
            hint: true,
            highlight: true,
            limit : 5,
            display : 'FieldValue',
            source: function( query, result ){
                $.ajax({
                    url : 'bin/php/get/search/Invoices.php',
                    method : 'GET',
                    data    : {
                        ID                :  $('input:visible[name="ID"]').val( ),
                        Customer          :  $('input:visible[name="Customer"]').val( ),
                        Location          :  $('input:visible[name="Location"]').val( ),
                        Job               :  $('input:visible[name="Job"]').val( ),
                        Unit              :  $('input:visible[name="Unit"]').val( ),
                        Type              :  $('input:visible[name="Type"]').val( ),
                        Date              :  $('input:visible[name="Date"]').val( ),
                        Original          :  $('input:visible[name="Original"]').val( ),
                        Balance           :  $('input:visible[name="Balance"]').val( ),
                        Description       :  $('input:visible[name="Description"]').val( ),
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

$( document ).ready( function( ){
    var Table_Invoices = $('#Table_Invoices').DataTable( {
        <?php \singleton\datatables::getInstance( )->preferences( );?>,
        ajax: {
            url     : 'bin/php/get/Invoices.php',
            <?php \singleton\datatables::getInstance( )->ajax_data( );?>
        },
        columns: [
            <?php \singleton\datatables::getInstance( )->ID( 'Invoice' );?>,
            <?php \singleton\datatables::getInstance( )->Customer( );?>,
            <?php \singleton\datatables::getInstance( )->Location( );?>,
            <?php \singleton\datatables::getInstance( )->Job( );?>,
            <?php \singleton\datatables::getInstance( )->Status('Status');?>,
            <?php \singleton\datatables::getInstance( )->Type('Type');?>,
            <?php \singleton\datatables::getInstance( )->Date('Date');?>,
            <?php \singleton\datatables::getInstance( )->data_column( 'Due' );?>,
            <?php \singleton\datatables::getInstance( )->data_column( 'Original' );?>,
            <?php \singleton\datatables::getInstance( )->data_column( 'Balance' );?>,
            <?php \singleton\datatables::getInstance( )->data_column( 'Description' );?>
        ],
        <?php \singleton\datatables::getInstance( )->initComplete( 'invoices' );?>,
        <?php \singleton\datatables::getInstance( )->buttons( 'invoice', 'invoices', 'ID' );?>
    });
});
});
