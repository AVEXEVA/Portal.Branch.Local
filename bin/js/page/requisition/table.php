<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
header('Content-Type: text/javascript');?>
$(document).ready(function( ){
    var Table_Requisition_Items = $('#Table_Requisition_Items').DataTable( {
        <?php \singleton\datatables::getInstance()->preferences( );?>,


          ajax: {
                url     : 'bin/php/get/Requisition_Items.php',
              <?php \singleton\datatables::getInstance()->ajax_data( );?>
        },
        columns: [
            <?php \singleton\datatables::getInstance( )->ID(' Requisition' );?>,
            <?php \singleton\datatables::getInstance( )->data_column( 'Description' );?>,
            <?php \singleton\datatables::getInstance( )->data_column( 'Quantity' );?>,
            <?php \singleton\datatables::getInstance( )->data_column_image( 'Image' );?>
        ],
        <?php \singleton\datatables::getInstance()->initComplete( 'Requisition' ); ?>,
        <?php \singleton\datatables::getInstance()->buttons( 'Requisition_Item', 'Requisition_Items', 'ID' )?>
    } );
} );
