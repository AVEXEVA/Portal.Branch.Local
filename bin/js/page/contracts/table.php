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
                  url : 'bin/php/get/search/Contracts.php',
                  method : 'GET',
                  data    : {
                      ID : $('input:visible[name="ID"]').val(),
                      Customer : $('input:visible[name="Customer"]').val(),
                      Location :  $('input:visible[name="Location"]').val( ),
                      Job : $('input:visible[name="Job"]').val( ),
                      Start_Date :  $('select:visible[name="Start_Date"]').val( ),
                      End_Date :  $('select:visible[name="End_Date"]:visible').val( ),
                      Length : $('select:visible[name="Length"]').val( ),
                      Amount : $('input:visible[name="Amount"]').val( ),
                      Billing_Cycle : $('input:visible[name="Billing_Cycle"]').val( ),
                      Escalation_Factor : $('input:visible[name="Escalation_Factor"]').val( ),
                      Escalation_Date :  $('input:visible[name="Escalation_Date"]').val( ),
                      Billing_Escalation_Cycle : $('input:visible[name="Billing_Escalation_Cycle"]').val( ),
                      Link :  $('input:visible[name="Link"]').val( ),
                      Remarks :  $('input:visible[name="Remarks"]').val( ),
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
$( document ).ready( function() {
  var Table_Contracts = $('#Table_Contracts').DataTable( {
    <?php \singleton\datatables::getInstance( )->preferences( );?>,
    ajax       : {
      url : 'bin/php/get/Contracts.php',
      <?php \singleton\datatables::getInstance( )->ajax_data( );?>
    },
    columns: [
      <?php \singleton\datatables::getInstance( )->ID( 'Contract' );?>,
      <?php \singleton\datatables::getInstance( )->Customer( );?>,
      <?php \singleton\datatables::getInstance( )->Location( );?>,
      <?php \singleton\datatables::getInstance( )->Job( );?>,
      <?php \singleton\datatables::getInstance( )->Date( 'Start_Date' );?>,
      <?php \singleton\datatables::getInstance( )->Date( 'End_Date' );?>,
      <?php \singleton\datatables::getInstance( )->data_column( 'Length' );?>,
      <?php \singleton\datatables::getInstance( )->data_column( 'Amount' );?>,
      <?php \singleton\datatables::getInstance( )->data_column( 'Billing_Cycle' );?>,
      <?php \singleton\datatables::getInstance( )->data_column( 'Escalation_Factor' );?>,
      <?php \singleton\datatables::getInstance( )->data_column( 'Escalation_Date' );?>,
      <?php \singleton\datatables::getInstance( )->data_column( 'Escalation_Type' );?>,
      <?php \singleton\datatables::getInstance( )->data_column( 'Billing_Escalation_Cycle' );?>,
      <?php \singleton\datatables::getInstance( )->data_column( 'Link' );?>,
    ],
    <?php \singleton\datatables::getInstance( )->initComplete( 'contracts' );?>,
    <?php \singleton\datatables::getInstance( )->buttons( 'contract', 'contracts', 'ID' );?>
  });
});
