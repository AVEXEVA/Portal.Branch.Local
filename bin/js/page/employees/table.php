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
                    url : 'bin/php/get/search/Employees.php',
                    method : 'GET',
                    data    : {
                        ID : $('input:visible[name="ID"]').val(),
                        First_Name : $('input:visible[name="First_Name"]').val(),
                        Last_Name :  $('input:visible[name="Last_Name"]').val( ),
                        Supervisor : $('input:visible[name="Supervisor"]').val( ),
                        Latittude :  $('input:visible[name="Latittude"]').val( ),
                        Longitude :  $('input:visible[name="Longitude"]').val( ),
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
  var Table_Employees = $('#Table_Employees').DataTable( {
    <?php \singleton\datatables::getInstance( )->preferences( );?>,
    ajax      : {
      url : 'bin/php/get/Employees.php',
      <?php \singleton\datatables::getInstance( )->ajax_data( );?>
    },
    columns: [
      <?php \singleton\datatables::getInstance( )->ID( 'employee' );?>,
      <?php \singleton\datatables::getInstance( )->data_column('First_Name');?>,
      <?php \singleton\datatables::getInstance( )->data_column('Last_Name');?>,
      <?php \singleton\datatables::getInstance( )->data_column('Supervisor');?>,
      <?php \singleton\datatables::getInstance( )->GPSLocation( );?>
    ],
    <?php \singleton\datatables::getInstance( )->buttons('employee', 'employees', 'ID' );?>,
    <?php \singleton\datatables::getInstance( )->initComplete( 'employee' );?>
  } );
} );
