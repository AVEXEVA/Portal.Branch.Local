<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
header('Content-Type: text/javascript');?>
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
    var Editor_Contacts = new $.fn.dataTable.Editor( {
        idSrc    : 'ID',
        ajax     : 'index.php',
        table    : '#Table_Contacts'
    } );
    var Table_Contacts = $('#Table_Contacts').DataTable( {
        dom            : "<'row'<'col-sm-3 search'><'col-sm-9'B>><'row'<'col-sm-12't>>",
        processing     : true,
        serverSide     : true,
        autoWidth      : false,
        searching      : false,
        lengthChange   : false,
        scrollResize   : true,
        scrollY        : 100,
        scroller       : true,
        scrollCollapse : true,
        paging         : true,
        orderCellsTop  : true,
        autoWidth      : true,
        responsive     : true,
        select         : {
          style : 'multi',
          selector : 'td.ID'
        },
        ajax: {
                url : 'bin/php/get/Contacts.php',
                data    : function(d){
                    d = {
                        draw : d.draw,
                        start : d.start,
                        length : d.length,
                        order : {
                            column : d.order[0].column,
                            dir : d.order[0].dir
                        },
                        ID :  $('input:visible[name="ID"]').val( ),
                        Contact :  $('input:visible[name="Contact"]').val( ),
                        Type :  $('select:visible[name="Type"]').val( ),
                        Name :  $('input:visible[name="Name"]').val( ),
                        Position : $('input:visible[name="Position"]').val( ),
                        Customer :  $('input:visible[name="Customer"]').val( ),
                        Phone : $('input:visible[name="Phone"]').val( ),
                        Email :  $('input:visible[name="Email"]').val( ),
                        Address :  $('input:visible[name="Address"]').val( ),
                    };
                    return d;
                }
        },
        columns: [
        <?php \singleton\datatables::getInstance( )->ID('contact.php','Contact');?>,
          {
                data : 'Contact',
                render : function( data, type, row, meta ){
                    switch( type ){
                        case 'display' :
                            return  row.ID !== null
                                ?   "<div class='row'>" +
                                        "<div class='col-12'><a href='contact.php?ID=" + row.ID + "'><i class='fa fa-link fa-fw fa-1x'></i> " + row.Contact + "</a></div>" +
                                    "</div>"
                                :   null;
                        default :
                            return data;
                    }
                }
            },
            <?php \singleton\datatables::getInstance( )->DataElement('Type');?>,
            {
              data : 'Name',
              render : function( data, type, row, meta ){
                  switch( type ){
                      case 'display' :
                        return row.Name !== null
                            ?   (
                                    row.Type == 'Customer'
                                        ?   "<div class='row'>" +
                                                "<div class='col-12'><a href='customer.php?Name=" + row.Name + "'><i class='fa fa-user fa-fw fa-1x'></i>" + row.Name + "</a></div>" +
                                            "</div>"
                                        :   (
                                                row.Type == 'Location'
                                                    ?   "<div class='row'>" +
                                                            "<div class='col-12'><a href='location.php?Name=" + row.Name + "'><i class='fa fa-building fa-fw fa-1x'></i>" + row.Name + "</a></div>" +
                                                        "</div>"
                                                    :   (
                                                            row.Type == 'Employee'
                                                                ?   "<div class='row'>" +
                                                                        "<div class='col-12'><a href='employee.php?Name=" + row.Name + "'><i class='fa fa-users fa-fw fa-1x'></i>" + row.Name + "</a></div>" +
                                                                    "</div>"
                                                                : null
                                                        )
                                            )
                                )
                            :   null;
                      default :
                          return data;
                  }

              }
            },<?php \singleton\datatables::getInstance( )->DataElement('Position');?>,
            {
                data : 'Phone',
                render : function( data, type, row, meta ){
                    switch( type ){
                        case 'display' :
                            return row.Email !== null && row.Phone != ''
                                ?   "<a href='tel:" + row.Phone + "'><i class='fa fa-phone fa-fw fa-1x'></i>" + row.Phone + "</a>"
                                :   null;
                        default :
                            return data;
                    }
                }
            },{
                data : 'Email',
                render : function( data, type, row, meta ){
                    switch( type ){
                        case 'display' :
                            return row.Email !== null && row.Email != ''
                                ?   "<a href='mailto:" + row.Email + "'><i class='fa fa-envelope fa-fw fa-1x'></i>" + row.Email + "</a>"
                                :   null;
                        default :
                            return data;
                    }
                }
            },{
                data : 'Street',
                render : function( data, type, row, meta ){
                    switch( type ){
                        case 'display' :
                            return  "<div class='row'>" +
                                        "<div class='col-12'>" +
                                            "<div class='row'>" +
                                                "<div class='col-12'><i class='fa fa-map-signs fa-fw fa-1x'></i>" + row.Street + "</div>" +
                                                "<div class='col-12'>" + row.City + ", " + row.State + " " + row.Zip + "</div>" +
                                            "</div>" +
                                        "</div>" +
                                    "</div>"
                        default :
                            return data;
                    }
                }
            }
        ],
        initComplete : function( ){
            $("div.search").html( "<input type='text' name='Search' placeholder='Search' />" );//onChange='$(\"#Table_Contacts\").DataTable().ajax.reload( );'
            $('input.date').datepicker( { } );
            $('input.time').timepicker( {  timeFormat : 'h:i A' } );
            search( this );
            $( '.redraw' ).bind( 'change', function(){ Table_Contacts.draw(); });
        },
        buttons: [
            {
                text: 'Reset Search',
                className: 'form-control',
                action: function ( e, dt, node, config ) {
                    $( 'input:visible, select:visible' ).each( function( ){
                        $( this ).val( '' );
                    } );
                    Table_Contacts.draw( );
                }
            },{
            text : 'Create',
            className: 'form-control',
            action : function( e, dt, node, config ){
                document.location.href='contact.php';}

            },{ extend: 'edit',
                editor: Editor_Contacts,
                className: 'form-control',
            },{
                text : 'Delete',
                className: 'form-control',
                action : function( e, dt, node, config ){
                  var rows = dt.rows( { selected : true } ).indexes( );
                  var dte = dt.cells( rows, 0 ).data( ).toArray( );
                  $.ajax ({
                    url    : 'bin/php/post/contact.php',
                    method : 'POST',
                    data   : {
                      action : 'delete' ,
                      data : dte
                    },
                    success : function(response){
                      Table_Contacts.draw();
                    }
                  })
                }
              },
        ]
    });
  });
});
