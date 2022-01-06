<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
header('Content-Type: text/javascript');?>
$( document ).ready( function( ){
  var Table_Divisions = $('#Table_Divisions').DataTable( {
    <?php \singleton\datatables::getInstance( )->preferences( );?>,
    ajax: {
      url     : 'bin/php/get/Divisions.php',
      <?php \singleton\datatables::getInstance( )->ajax_data( );?>
    },
    columns: [
      <?php \singleton\datatables::getInstance( )->ID( 'Division' );?>,  
      <?php \singleton\datatables::getInstance( )->Name( 'Division' );?>,
      <?php \singleton\datatables::getInstance( )->Locations( 'Division', 'ID'  );?>,
      <?php \singleton\datatables::getInstance( )->data_column( 'Units_Elevators'  );?>,
      <?php \singleton\datatables::getInstance( )->data_column( 'Units_Escalators' );?>,
      <?php \singleton\datatables::getInstance( )->data_column( 'Units_Others' );?>,
      <?php \singleton\datatables::getInstance( )->data_column( 'Violations_Office' );?>,
      <?php \singleton\datatables::getInstance( )->data_column( 'Violations_Field' );?>,
      <?php \singleton\datatables::getInstance( )->data_column( 'Tickets_Assigned' );?>,
      <?php \singleton\datatables::getInstance( )->data_column( 'Tickets_Active' );?>
    ],
    <?php \singleton\datatables::getInstance( )->initComplete( 'divisions' );?>,
    <?php \singleton\datatables::getInstance( )->buttons( 'division', 'divisions', 'ID' );?>
  } );
} );
