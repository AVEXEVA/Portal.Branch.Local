<?php
if( session_id( ) == '' || !isset( $_SESSION ) ) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
header('Content-Type: text/javascript');?>
$( document ).ready( function( ){
  var Editor_Routes = new $.fn.dataTable.Editor( {
      idSrc    : 'ID',
      ajax     : 'index.php',
      table    : '#Table_Routes'
  } );
  var Table_Routes = $('#Table_Routes').DataTable( {
      <?php \singleton\datatables::getInstance( )->preferences( );?>,
      ajax: {
        url     : 'bin/php/get/Routes.php',
        <?php \singleton\datatables::getInstance( )->ajax_data( );?>
      },
      columns : [
          <?php \singleton\datatables::getInstance( )->ID( 'Route' );?>,
          <?php \singleton\datatables::getInstance( )->Name( 'Route' );?>,
          <?php \singleton\datatables::getInstance( )->Employee( );?>,
          <?php \singleton\datatables::getInstance( )->Locations( 'Route', 'ID' );?>,
          <?php \singleton\datatables::getInstance( )->Units( 'Route', 'ID' );?>,
          <?php \singleton\datatables::getInstance( )->Violations( 'Route', 'ID' );?>
      ],
      <?php \singleton\datatables::getInstance( )->buttons( 'route', 'routes', 'ID' );?>,
      <?php \singleton\datatables::getInstance( )->initComplete( 'routes' );?>
  } );
} );
