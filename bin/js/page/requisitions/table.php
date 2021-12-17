<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
header('Content-Type: text/javascript');?>
$( document ).ready( function( ){
    var Editor_Requisitions = new $.fn.dataTable.Editor( {
        idSrc    : 'ID',
        ajax     : 'index.php',
        table    : '#Table_Requisitions'
    } );
    var Table_Requisitions = $('#Table_Requisitions').DataTable( {
        <?php \singleton\datatables::getInstance()->preferences( );?>,


        ajax      : {
            url : 'bin/php/get/Requisitions.php',
            <?php \singleton\datatables::getInstance()->ajax_data( );?>
        },
        columns: [
                <?php \singleton\datatables::getInstance( )->ID(' Requisition' );?>,
                <?php \singleton\datatables::getInstance( )->Employee( );?>,
                <?php \singleton\datatables::getInstance( )->data_column( 'Items' );?>,
                <?php \singleton\datatables::getInstance( )->Date( );?>,
                <?php \singleton\datatables::getInstance( )->Date( 'Required' );?>,
                <?php \singleton\datatables::getInstance( )->Location( );?>,
                <?php \singleton\datatables::getInstance( )->data_column( 'DropOff_Name');?>,
                <?php \singleton\datatables::getInstance( )->Unit( );?>,
                <?php \singleton\datatables::getInstance( )->Job( );?>
        ],
        <?php \singleton\datatables::getInstance()->initComplete( 'requisitions' ); ?>,
        <?php \singleton\datatables::getInstance()->buttons( 'requisition', 'requisitions', 'ID' );?>
    } );
} );
