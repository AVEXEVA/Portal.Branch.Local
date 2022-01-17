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
            minLength : 4,
            hint: true,
            highlight: true,
            limit : 5,
            display : 'FieldValue',
            source: function( query, result ){
                $.ajax({
                    url : 'bin/php/get/search/Tickets.php',
                    method : 'GET',
                    data    : {
                        search                :  $('input:visible[name="Search"]').val(),
                        ID                    :  $('input:visible[name="ID"]').val( ),
                        Person                :  $('input:visible[name="Person"]').val( ),
                        Customer              :  $('input:visible[name="Customer"]').val( ),
                        Location              :  $('input:visible[name="Location"]').val( ),
                        Unit                  :  $('input:visible[name="Unit"]').val( ),
                        Job                   :  $('input:visible[name="Job"]').val( ),
                        Type                  :  $('select:visible[name="Type"]').val( ),
                        Level                 :  $('select:visible[name="Level"]').val( ),
                        Status                :  $('select:visible[name="Status"]').val( ),
                        Start_Date            :  $('input:visible[name="Start_Date"]').val( ),
                        End_Date              :  $('input:visible[name="End_Date"]').val( ),
                        Time_Route_Start      :  $('input:visible[name="Time_Route_Start"]').val( ),
                        Time_Route_End        :  $('input:visible[name="Time_Route_End"]').val( ),
                        Time_Site_Start       :  $('input:visible[name="Time_Site_Start"]').val( ),
                        Time_Site_End         :  $('input:visible[name="Time_Site_End"]').val( ),
                        Time_Completed_Start  :  $('input:visible[name="Time_Completed_Start"]').val( ),
                        Time_Completed_End    :  $('input:visible[name="Time_Completed_End"]').val( ),
                        LSD                   :  $('select:visible[name="LSD"]').val( )
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
$(document).ready(function( ){
    var Editor_Tickets = new $.fn.dataTable.Editor( {
        idSrc    : 'ID',
        ajax     : 'index.php',
        table    : '#Table_Tickets'
    } );
    var Table_Tickets = $('#Table_Tickets').DataTable( {
        <?php \singleton\datatables::getInstance( )->preferences( );?>,
        ajax: {
                url     : 'bin/php/get/Tickets.php',
                <?php \singleton\datatables::getInstance( )->ajax_data( );?>
        },
        columns: [
            <?php \singleton\datatables::getInstance( )->ID('ticket.php','Ticket');?>,
            <?php \singleton\datatables::getInstance( )->data_column_bit( 'Open' );?>,
            <?php \singleton\datatables::getInstance( )->Employee();?>,
            <?php \singleton\datatables::getInstance( )->Division();?>,
            <?php \singleton\datatables::getInstance( )->Route();?>,
            <?php \singleton\datatables::getInstance( )->Customer();?>,
            <?php \singleton\datatables::getInstance( )->Location(1);?>,
            <?php \singleton\datatables::getInstance( )->Unit();?>,
            <?php \singleton\datatables::getInstance( )->Job();?>,
            <?php \singleton\datatables::getInstance( )->data_column( 'Level' );?>,
            <?php \singleton\datatables::getInstance( )->data_column( 'Type' );?>,
            <?php \singleton\datatables::getInstance( )->data_column( 'Status' );?>,
            <?php \singleton\datatables::getInstance( )->date( 'Date' );?>,
            <?php /*\singleton\datatables::getInstance( )->data_column( 'Level' );?>,
            <?php \singleton\datatables::getInstance( )->Status();?>,
            <?php \singleton\datatables::getInstance( )->Date();?>,<?php */?>
            <?php \singleton\datatables::getInstance( )->Time( 'En_Route' );?>,
            <?php \singleton\datatables::getInstance( )->Time( 'On_Site' );?>,
            <?php \singleton\datatables::getInstance( )->Time( 'Completed' );?>,
            <?php \singleton\datatables::getInstance( )->TicketHours();?>
        ],
        <?php \singleton\datatables::getInstance( )->initComplete( 'tickets' );?>,
        <?php \singleton\datatables::getInstance( )->buttons( 'ticket', 'tickets', 'ID' );?>
    } );
} );
