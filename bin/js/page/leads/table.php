<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
header('Content-Type: text/javascript');?>
$( document ).ready( function( ){
  var Editor_Leads = new $.fn.dataTable.Editor({
    idSrc    : 'ID',
    ajax: 'php/post/Lead.php',
    table: '#Table_Leads',
    template: '#Form_Lead'
  });
  var Table_Leads = $('#Table_Leads').DataTable( {
    <?php \singleton\datatables::getInstance( )->preferences( );?>,
    ajax: {
      url     : 'bin/php/get/Leads.php',
      <?php \singleton\datatables::getInstance( )->ajax_data( );?>
    },
    columns: [
      <?php \singleton\datatables::getInstance( )->ID( 'Lead' );?>,
      <?php \singleton\datatables::getInstance( )->Name( 'Lead' );?>,    
      <?php \singleton\datatables::getInstance( )->data_column( 'Type' );?>,
      <?php \singleton\datatables::getInstance( )->Customer( );?>,
      <?php \singleton\datatables::getInstance( )->data_column_address( );?>,
      <?php \singleton\datatables::getInstance( )->Contact( );?>,
      <?php \singleton\datatables::getInstance( )->data_column( 'Probability' );?>,
      <?php \singleton\datatables::getInstance( )->data_column( 'Level' );?>,
      <?php \singleton\datatables::getInstance( )->data_column( 'Status' );?>
    ],
    <?php \singleton\datatables::getInstance( )->initComplete( 'leads' );?>,
    <?php \singleton\datatables::getInstance( )->buttons( 'lead', 'leads', 'ID' );?>
  });
});
