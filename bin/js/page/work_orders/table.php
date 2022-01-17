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
        <?php \singleton\datatables::getInstance( )->preferences_paging_off( );?>,
        ajax: {
                url     : 'bin/php/get/Work_Orders.php',
                <?php \singleton\datatables::getInstance( )->ajax_data( );?>
        },
        columns: [
            <?php \singleton\datatables::getInstance( )->data_column( 'RowGroup1' );?>,
            <?php \singleton\datatables::getInstance( )->data_column( 'Count' );?>
        ],
        <?php \singleton\datatables::getInstance( )->buttons( 'ticket', 'tickets', 'ID' );?>,
        initComplete : function( ){
            $("#Table_Tickets>tbody>tr[role='row']").on('click', "td", function () {
                document.location.href='work_orders.php?RowGroup1=' + $( this ).parent().children( ':first-child' ).html( );
            });
        },
        drawCallback : function( row, data, index ){
            $( "tr[role='row']").each(function(){
                <?php if( isset( $_GET[ 'RowGroup1' ] ) ){?>
                    if( $( this ).children( ':first-child' ).html( ) == '<?php echo $_GET[ 'RowGroup1' ];?>' ){
                        $( this ).after( "<tr><td><table id='<?php echo str_replace( ' ', '_', $_GET[ 'RowGroup1' ] );?>'></table></td></tr>" );
                        $( "#<?php echo str_replace( ' ', '_', $_GET[ 'RowGroup1' ] );?>" ).DataTable( {
                            <?php \singleton\datatables::getInstance( )->preferences_only_table( );?>,
                            ajax: {
                                    url     : 'bin/php/get/Work_Orders_RowGroup.php?RowGroup1=<?php echo $_GET[ 'RowGroup1' ];?>'
                            },
                            columns: [
                                <?php \singleton\datatables::getInstance( )->data_column_url( 'RowGroup2', 'RowLink' );?>,
                                <?php \singleton\datatables::getInstance( )->data_column( 'Count' );?>
                            ],
                            <?php \singleton\datatables::getInstance( )->initComplete( 'tickets' );?>,
                            <?php \singleton\datatables::getInstance( )->buttons( 'ticket', 'tickets', 'ID' );?>,
                        });
                    }
                <?php }?>
            })
        }
    } );
    
} );
