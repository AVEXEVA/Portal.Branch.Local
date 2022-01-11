<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
header('Content-Type: text/javascript');?>
$( document ).ready( function( ){
  var Editor_Collections = new $.fn.dataTable.Editor( {
      idSrc    : 'ID',
      ajax     : 'index.php',
      table    : '#Table_Collection'
  } );
  var Table_Collections = $('#Table_Collections').DataTable( {
    <?php \singleton\datatables::getInstance( )->preferences( );?>,
    ajax: {
        url     : 'bin/php/get/Collections.php',
        <?php \singleton\datatables::getInstance( )->ajax_data( );?>
    },
    columns: [
      <?php \singleton\datatables::getInstance( )->ID('collection', 'invoice' );?>,
      <?php \singleton\datatables::getInstance( )->Territory( );?>,
      <?php \singleton\datatables::getInstance( )->Customer( );?>,
      <?php \singleton\datatables::getInstance( )->Location( );?>,
      <?php \singleton\datatables::getInstance( )->Job( );?>,
      <?php \singleton\datatables::getInstance( )->Type('Type');?>,
      <?php \singleton\datatables::getInstance( )->Date('Date');?>,
      <?php \singleton\datatables::getInstance( )->data_column( 'Due' );?>,
      <?php \singleton\datatables::getInstance( )->data_column( 'Total' );?>,
      <?php \singleton\datatables::getInstance( )->data_column( 'Balance' );?>,
      <?php \singleton\datatables::getInstance( )->data_column( 'Description' );?>
    ],
    <?php \singleton\datatables::getInstance( )->initComplete( 'collections' );?>,
    <?php \singleton\datatables::getInstance( )->buttons( 'invoice', 'collections', 'ID' );?>
  } );
} );
