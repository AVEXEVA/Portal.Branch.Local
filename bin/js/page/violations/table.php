<?php
if( session_id( ) == '' || !isset($_SESSION)) {
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
                      url : 'bin/php/get/search/Violations.php',
                      method : 'GET',
                      data    : {
                          search                :  $('input:visible[name="Search"]').val(),
                          ID                    :  $('input:visible[name="ID"]').val( ),
                          Customer              :  $('input:visible[name="Customer"]').val( ),
                          Address               : $('input:visible[name="Address"]').val( ),
                          City                  :  $('input:visible[name="City"]').val( ),
                          Street                :  $('input:visible[name="Street"]').val( ),
                          State                 :  $('input:visible[name="State"]').val( ),
                          Zip                   :  $('select:visible[name="Zip"]').val( ),
                          Units                 :  $('input:visible[name="Units"]').val( ),
                          Status                :  $('select:visible[name="Status"]').val( ),
                          Date                  :  $('input:visible[name="Date"]').val( )

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

     var Editor_Violations = new $.fn.dataTable.Editor( {
        idSrc    : 'ID',
        ajax     : 'index.php',
        table    : '#Table_Violations'
    } );

  var Table_Violations = $('#Table_Violations').DataTable( {
      <?php \singleton\datatables::getInstance( )->preferences( );?>,
      ajax: {
        url     : 'bin/php/get/Violations.php',
        <?php \singleton\datatables::getInstance( )->ajax_data( );?>
      },
      columns    : [
        <?php \singleton\datatables::getInstance( )->ID( 'Violation' );?>,
        <?php \singleton\datatables::getInstance( )->Customer( );?>,
        <?php \singleton\datatables::getInstance( )->Location( );?>,
        <?php \singleton\datatables::getInstance( )->Date( );?>,
        <?php \singleton\datatables::getInstance( )->Unit();?>,

        <?php \singleton\datatables::getInstance( )->Status();?>
    ],
    <?php \singleton\datatables::getInstance( )->buttons( 'violation', 'violations', 'ID' );?>,
    <?php \singleton\datatables::getInstance( )->initComplete( 'violations' );?>
  } );
});
