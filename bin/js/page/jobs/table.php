<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
header('Content-Type: text/javascript');?>
$( document ).ready( function( ){
  var Editor_Jobs = new $.fn.dataTable.Editor( {
    idSrc    : 'ID',
    ajax     : 'index.php',
    table    : '#Table_Jobs'
  } );
  var Table_Jobs = $('#Table_Jobs').DataTable( {
    <?php \singleton\datatables::getInstance( )->preferences( );?>,
    ajax      : {
      url : 'bin/php/get/Jobs.php',
      <?php \singleton\datatables::getInstance( )->ajax_data( );?>
    },
    columns   : [
      <?php \singleton\datatables::getInstance( )->ID( 'Job' );?>,
      <?php \singleton\datatables::getInstance( )->Name( 'Job' );?>,
      <?php \singleton\datatables::getInstance( )->Type( );?>,
      <?php \singleton\datatables::getInstance( )->Date( );?>,
      <?php \singleton\datatables::getInstance( )->Customer( );?>,
      <?php \singleton\datatables::getInstance( )->Location( );?>,
      <?php \singleton\datatables::getInstance( )->Status( );?>,
      <?php \singleton\datatables::getInstance( )->Tickets( 'Job', 'ID' );?>,
      <?php \singleton\datatables::getInstance( )->Invoices( 'Job', 'ID' );?>,
      
    ],
    <?php \singleton\datatables::getInstance( )->initComplete( 'jobs' );?>,
    <?php \singleton\datatables::getInstance( )->buttons( 'job', 'jobs', 'ID' );?>
  } );
} );
