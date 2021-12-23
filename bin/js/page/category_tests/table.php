<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
header('Content-Type: text/javascript');?>

       $(document).ready(function( ){
    var Table_Contacts = $('#Table_Category_Tests').DataTable( {
        <?php \singleton\datatables::getInstance( )->preferences( );?>,
        ajax: {
            url : 'bin/php/get/Category_Tests.php',
            <?php \singleton\datatables::getInstance( )->ajax_data( );?>
        },
        columns: [
           <?php \singleton\datatables::getInstance( )->ID( 'Category_Test' );?>,
           <?php \singleton\datatables::getInstance( )->Name( 'Category_Test' );?>,
            <?php \singleton\datatables::getInstance( )->data_column_count( 'Deficiency' ,'Deficiency','Deficiency_ID');?>
          ],
    <?php \singleton\datatables::getInstance( )->initComplete( 'Category_Tests' );?>, 
           
        <?php \singleton\datatables::getInstance( )->buttons( 'Category_Test', 'Category_Tests', 'ID' );?>
    });
  });           

