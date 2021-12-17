<?php
if( session_id( ) == '' || !isset( $_SESSION ) ) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
header('Content-Type: text/javascript');?>
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
                    url : 'bin/php/get/search/Users.php',
                    method : 'GET',
                    data    : {
                        search                :  $('input:visible[name="Search"]').val(),
                        ID                    :  $('input:visible[name="ID"]').val( ),
                        Email                :  $('input:visible[name="Email"]').val( ),
                        Verified                :  $('input:visible[name="Verified"]').val( ),
                        Branch                :  $('input:visible[name="Branch"]').val( ),
                        Branch_Type                :  $('input:visible[name="Type"]').val( ),
                        Branch_ID                :  $('input:visible[name="Branch_ID"]').val( ),

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

    var Editor_Users = new $.fn.dataTable.Editor( {
        idSrc    : 'ID',
        ajax     : 'index.php',
        table    : '#Table_Users'
    } );
    var Table_Users = $('#Table_Users').DataTable( {
      <?php \singleton\datatables::getInstance( )->preferences( );?>,
      ajax: {
        url     : 'bin/php/get/Users.php',
        <?php \singleton\datatables::getInstance( )->ajax_data( );?>
      },
      columns: [
          <?php \singleton\datatables::getInstance( )->ID( 'Route' );?>,
          <?php \singleton\datatables::getInstance( )->data_column_email( 'Email' );?>,
          <?php \singleton\datatables::getInstance( )->data_column( 'Verified' );?>,
          <?php \singleton\datatables::getInstance( )->data_column( 'Branch' );?>,
          <?php \singleton\datatables::getInstance( )->data_column( 'Branch_Type' );?>,
          <?php \singleton\datatables::getInstance( )->data_column( 'Branch_ID' );?>,
          <?php \singleton\datatables::getInstance( )->data_column_image( 'Picture' );?>
      ],
      <?php \singleton\datatables::getInstance( )->buttons( 'user', 'users', 'ID' );?>,
      <?php \singleton\datatables::getInstance( )->initComplete( 'users' );?>
    } );
});
