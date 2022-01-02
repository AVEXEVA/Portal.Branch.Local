<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
header('Content-Type: text/javascript');?>
$(document).ready(function( ){

    var Editor_Users = new $.fn.dataTable.Editor( {
        idSrc    : 'ID',
        ajax     : 'index.php',
        table    : '#Table_bugs'
    } );
    var Table_bugs = $('#Table_bugs').DataTable( {
      <?php \singleton\datatables::getInstance( )->preferences( );?>,
      ajax: {
        url     : 'bin/php/get/Bugs.php',
        <?php \singleton\datatables::getInstance( )->ajax_data( );?>
      },
     columns: [
            <?php \singleton\datatables::getInstance( )->ID('customer.php','Customer');?>,
            <?php \singleton\datatables::getInstance( )->TicketDate();?>,
            <?php \singleton\datatables::getInstance( )->data_column('Description');?>,
            <?php \singleton\datatables::getInstance( )->data_column('Severity');?>,
            <?php \singleton\datatables::getInstance( )->data_column('Suggestion');?>,
            <?php \singleton\datatables::getInstance( )->data_column('Resolution');?>,
            <?php \singleton\datatables::getInstance( )->data_column('Fixed');?>
        ],
      <?php \singleton\datatables::getInstance( )->buttons( 'bug', 'bugs', 'ID' );?>,
      <?php \singleton\datatables::getInstance( )->initComplete( 'bugs' );?>
    } );
});