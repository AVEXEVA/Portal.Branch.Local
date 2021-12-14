<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
header('Content-Type: text/javascript');
?>
$( document ).ready( function( ){
function search( link ){
    var api = link.api();
    $('input:visible[name="Search"]', api.table().container())
        .typeahead({
            minLength : 4,
            highlight : 1,
            displayKey : 'FieldValue',
            source: function( query, result ){
                $.ajax({
                    url : 'bin/php/get/search/Contacts.php',
                    method : 'GET',
                    data    : {
                        search : $('input:visible[name="Search"]').val(),
                        ID :  $('input:visible[name="ID"]').val( ),
                        Contact :  $('input:visible[name="Contact"]:visible').val( ),
                        Type :  $('select:visible[name="Type"]:visible').val( ),
                        Name :  $('input:visible[name="Name"]:visible').val( ),
                        Phone : $('input:visible[name="Phone"]').val( ),
                        Email : $('input:visible[name="Email"]').val( ),
                        Customer :  $('input:visible[name="Customer"]').val( ),
                        Type : $('select:visible[name="Type"]').val( ),
                        Division : $('select:visible[name="Division"]').val( ),
                        Route : $('select:visible[name="Route"]').val( ),
                        Address : $('input:visible[name="Address"]').val( ),
                        City :  $('input:visible[name="City"]').val( ),
                        Street :  $('input:visible[name="Street"]').val( ),
                        State :  $('input:visible[name="State"]').val( ),
                        Zip :  $('select:visible[name="Zip"]').val( ),
                        Status : $('select:visible[name="Status"]').val( ),
                        Maintaiend : $('select:visible[name="Maintained"]').val( )
                    },
                    dataType : 'json',
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
                $( 'input:visible[name="' + FieldName.split( '_' )[ 0 ] + '"]' ).val ( FieldValue ).change( );
                $( 'input:visible[name="Search"]').val( '' );
            }
        }
    );
}
$(document).ready(function( ){
    var Table_Contacts = $('#Table_Contacts').DataTable( {
        <?php \singleton\datatables::getInstance( )->preferences( );?>,
        ajax: {
            url : 'bin/php/get/Contacts.php',
            <?php \singleton\datatables::getInstance( )->ajax_data( );?>
        },
        columns: [
	       <?php \singleton\datatables::getInstance( )->ID( 'Contact' );?>,
           {
                data : 'Contact',
                render : function( data, type, row, meta ){
                    switch( type ){
                        case 'display' :
                            return  row.ID !== null
                                ?   "<div class='row'>" +
                                        "<div class='col-12'><a href='contact.php?ID=" + row.ID + "'><?php \singleton\fontawesome::getInstance( )->Contact( 1 );?> " + row.Contact + "</a></div>" +
                                    "</div>"
                                :   null;
                        default :
                            return data;
                    }
                }
            },
            <?php \singleton\datatables::getInstance( )->Type( );?>,
            {
              data : 'Entity_Name',
                render : function( data, type, row, meta ){
                    switch( type ){
                        case 'display' :
                            switch( row.Entity_Type ){
                                case 'Customer':
                                    return "<div class='row'>" +
                                                "<div class='col-12'><a href='customer.php?Name=" + row.Entity_Name + "'><?php echo \singleton\fontawesome::getInstance( )->Customer( 1 );?>" + row.Entity_Name + "</a></div>" +
                                            "</div>";
                                case 'Location':
                                    return "<div class='row'>" +
                                                "<div class='col-12'><a href='location.php?Name=" + row.Entity_Name + "'><?php echo \singleton\fontawesome::getInstance( )->Location( 1 );?>" + row.Entity_Name + "</a></div>" +
                                            "</div>";
                                case 'Employee':
                                    return "<div class='row'>" +
                                                "<div class='col-12'><a href='employee.php?Name=" + row.Entity_Name + "'><?php echo \singleton\fontawesome::getInstance( )->Employee( 1 );?>" + row.Entity_Name + "</a></div>" +
                                            "</div>";
                                default : 
                                    return null;
                            }
                        default : 
                            return data;
                    }
                }
            },
            <?php \singleton\datatables::getInstance( )->data_column( 'Position' );?>,
            <?php \singleton\datatables::getInstance( )->data_column_tel( 'Phone' );?>,
            <?php \singleton\datatables::getInstance( )->data_column_email( 'Email' );?>,
            <?php \singleton\datatables::getInstance( )->data_column_address( );?>
        ],
        <?php \singleton\datatables::getInstance( )->initComplete( 'Contacts' );?>,
        buttons: [
            <?php \singleton\datatables::getInstance( )->button_url( 'contacts' );?>,
            <?php \singleton\datatables::getInstance( )->button_reset( 'contacts' );?>,
            <?php \singleton\datatables::getInstance( )->button_create( 'contact' );?>,
            <?php \singleton\datatables::getInstance( )->button_edit( 'contact', 'ID' );?>,
            <?php \singleton\datatables::getInstance( )->button_delete( 'contact', 'contacts' );?>,
            <?php \singleton\datatables::getInstance( )->button_export( );?>
        ]
    });
  });
});
