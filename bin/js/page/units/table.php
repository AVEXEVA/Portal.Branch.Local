<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
header('Content-Type: text/javascript');?>
function search( link ){
    var api = link.api();
    $('input[name="Search"]', api.table().container())
        .typeahead({
            minLength : 2,
            hint: true,
                highlight: true,
                limit : 5,
                display : 'FieldValue',
            source: function( query, result ){
                $.ajax({
                    url : 'bin/php/get/search/Units.php',
                    method : 'GET',
                      data    : {
                            search                :  $('input:visible[name="Search"]').val(),
                            ID                    :  $('input:visible[name="ID"]').val( ),
                            Name                :  $('input:visible[name="Name"]').val( ),
                            Customer                :  $('input:visible[name="Customer"]').val( ),
                            Location                :  $('input:visible[name="Location"]').val( ),
                            Type                :  $('input:visible[name="Type"]').val( ),
                            Ticket_ID                :  $('input:visible[name="Ticket_ID"]').val( ),
                            Status                :  $('input:visible[name="Status"]').val( ),

                        },

                    dataType : 'json',
                    beforeSend : function( ){
                        abort( );
                    },
                    success : function( data ){
                        result( $.map( data, function( item ){
                            return item.FieldName + ' => ' + item.FieldValue;
                        } ) );
                    }
                });
            },
            afterSelect: function( value ){
                var FieldName = value.split( ' => ' )[ 0 ];
                var FieldValue = value.split( ' => ' )[ 1 ];
                $( 'input[name="' + FieldName.split( '_' )[ 0 ] + '"]' ).val ( FieldValue ).change( );
                $( 'input[name="Search"]').val( '' );
            }
        }
    );
}
$( document ).ready( function( ){
	var Table_Units = $('#Table_Units').DataTable( {
		<?php \singleton\datatables::getInstance( )->preferences( );?>,
        ajax      : {
            url : 'bin/php/get/Units.php',
            <?php \singleton\datatables::getInstance( )->ajax_data( );?>
        },
		columns   : [
			<?php \singleton\datatables::getInstance( )->ID( 'Unit' );?>,
			<?php \singleton\datatables::getInstance( )->data_column( 'City_ID' );?>,
			<?php \singleton\datatables::getInstance( )->data_column( 'Building_ID' );?>,
            <?php \singleton\datatables::getInstance( )->Division( );?>,
			<?php \singleton\datatables::getInstance( )->Route( );?>,
			<?php \singleton\datatables::getInstance( )->Customer( );?>,
			<?php \singleton\datatables::getInstance( )->Location( );?>,
			<?php \singleton\datatables::getInstance( )->Type();?>,
			<?php \singleton\datatables::getInstance( )->Status();?>,
			<?php \singleton\datatables::getInstance( )->data_column_count( 'Tickets_Assigned', 'Unit', 'ID', 'Ticket', 'tickets' );?>,
            <?php \singleton\datatables::getInstance( )->data_column_count( 'Tickets_Active', 'Unit', 'ID', 'Ticket', 'tickets' );?>
		],
		<?php \singleton\datatables::getInstance( )->buttons('unit', 'units', 'ID' );?>,
        <?php \singleton\datatables::getInstance( )->initComplete( 'units' );?>
  } );
});
