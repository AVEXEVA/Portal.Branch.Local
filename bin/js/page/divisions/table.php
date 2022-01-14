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
      <?php \singleton\datatables::getInstance( )->data_column_count( 'Units_Elevators', 'Division', 'ID', 'Unit', 'units' );?>,
      <?php \singleton\datatables::getInstance( )->data_column_count( 'Units_Escalators', 'Division', 'ID', 'Unit', 'units' );?>,
      <?php \singleton\datatables::getInstance( )->data_column_count( 'Units_Others', 'Division', 'ID', 'Unit', 'units' );?>,
      <?php \singleton\datatables::getInstance( )->data_column_count( 'Violations_Office', 'Division', 'ID', 'Violation', 'violations', 'Status=Preliminary Report' );?>,
      <?php \singleton\datatables::getInstance( )->data_column_count( 'Violations_Field', 'Division', 'ID', 'Violation', 'violations', 'Status=Job Created'  );?>,
      <?php \singleton\datatables::getInstance( )->data_column_count( 'Tickets_Assigned', 'Division', 'ID', 'Ticket', 'tickets', 'Status=1'  );?>,
      <?php \singleton\datatables::getInstance( )->data_column_count( 'Tickets_Active', 'Division', 'ID', 'Ticket', 'tickets', 'Status=2'  );?>
    ],
    <?php \singleton\datatables::getInstance( )->initComplete( 'divisions' );?>,
    <?php \singleton\datatables::getInstance( )->buttons( 'division', 'divisions', 'ID' );?>
  } );
} );
