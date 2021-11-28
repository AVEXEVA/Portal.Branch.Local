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
                        url : 'bin/php/get/search/Users.php',
                        method : 'GET',
                        data    : {
                            search                :  $('input:visible[name="Search"]').val(),
                            ID                    :  $('input:visible[name="ID"]').val( ),
                            Email                :  $('input:visible[name="Email"]').val( ),
                            Verified                :  $('input:visible[name="Verified"]').val( ),
                            Branch                :  $('input:visible[name="Branch"]').val( ),
                            Branch_Type                :  $('input:visible[name="Type"]').val( ),
                            Branch_ID                :  $('input:visible[name="Branch_ID"]').val( ),

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
    var Editor_Users = new $.fn.dataTable.Editor( {
        idSrc    : 'ID',
        ajax     : 'index.php',
        table    : '#Table_Users'
    } );
    var Table_Users = $('#Table_Users').DataTable( {
        dom            : "<'row'<'col-sm-3 search'><'col-sm-9'B>><'row'<'col-sm-12't>>",
        processing     : true,
        serverSide     : true,
        searching      : false,
        lengthChange   : false,
        scrollResize   : true,
        scrollY        : 100,
        scroller       : true,
        scrollCollapse : true,
        paging         : true,
        orderCellsTop  : true,
        autoWidth      : true,
        select         : {
          style : 'multi',
          selector : 'td.ID'
        },
        ajax: {
                url     : 'bin/php/get/Users.php',
                data    : function(d){
                    d = {
                        draw : d.draw,
                        start : d.start,
                        length : d.length,
                        order : {
                            column : d.order[0].column,
                            dir : d.order[0].dir
                        }
                    };
                    d.ID = $('input[name="ID"]').val( );
                    d.Email = $('input[name="Email"]').val( );
                    d.Branch_Type = $('input[name="Type"]').val( );
                    d.Branch = $('input[name="Branch"]').val( );
                     d.Branch_ID = $('input[name="ID"]').val( );
                      d.Picture = $('input[name="Picture"]').val( );
                    return d;
                }
        },
        columns: [
            {
                data    : 'ID',
                className : 'ID',
                render : function( data, type, row, meta ){
                    switch( type ){
                        case 'display' :
                            return  row.ID !== null
                                ?   "<div class='row'>" +
                                "<div class='col-12'><a href='user.php?ID=" + row.ID + "'><i class='fa fa-folder-open fa-fw fa-1x'></i> User #" + row.ID + "</a></div>" +
                                "</div>"
                                :   null;
                        default :
                            return data;
                    }

                }
            },{
                data : 'Email'
            },{
                data : 'Verified'
            },{
                data : 'Branch'
            },{
                data : 'Branch_Type',
                render : function( data, type, row, meta ){
                    switch( type ){
                        case 'display' :
                            return  row.Branch_Type == "Employee"
                                ?   "<div class='row'>" +
                                "<div class='col-12'><a href='employee.php?ID=" + row.Branch_ID + "'><i class='fa fa-link fa-fw fa-1x'></i> " + row.Branch_Type + "</a></div>" +
                                "</div>"
                                :   null;
                        default :
                            return data;
                    }
                }
            },{
                data : 'Branch_ID'
            },
             {
            data:'Picture',
              render : function( data, type, row, meta ){
                    switch( type ){
                        case 'display' :
                            return   row.Picture_Type !== null
                                ?   "<div class='row'>" +
                                        "<div class='col-12'><img src=data:"+row.Picture_Type +";base64,"+row.Picture +" width='100px;' height='100px;'></div>" +
                                    "</div>"
                                :   null;
                        default :
                            return data;
                    }
                }
            }
        ],
        initComplete : function( ){
            $("div.search").html( "<input type='text' name='Search' placeholder='Search' />" );//onChange='$(\"#Table_Tickets\").DataTable().ajax.reload( );'
            $('input.date').datepicker( { } );
            $('input.time').timepicker( {  timeFormat : 'h:i A' } );
            search( this );
            $( '.redraw' ).bind( 'change', function(){ Table_Users.draw(); });
        },
        buttons : [
            {
              text: 'Reset Search',
              className : 'form-control',
              action: function ( e, dt, node, config ) {
                  $( 'input:visible, select:visible' ).each( function( ){
                      $( this ).val( '' );
                  } );
                  Table_Users.draw( );
              }
            },{
              text : 'Get URL',
              className : 'form-control',
              action : function( e, dt, node, config ){
                  d = { }
                  d.ID = $('input[name="ID"]').val( );
                  d.Email = $('input[name="Email"]').val( );
                  d.Verified = $('input[name="Verified"]').val( );
                  d.Branch = $('input[name="Branch"]').val( );
                  d.Branch_Type = $('input[name="Type"]').val( );
                  d.Branch_ID = $('input[name="Reference"]').val( );
                  document.location.href = 'users.php?' + new URLSearchParams( d ).toString();
              }
            },{
              text : 'Create',
              className : 'form-control',
              action : function( e, dt, node, config ){
                  document.location.href='user.php';
              }
            },{
              text : 'Delete',
              className : 'form-control',
              action : function( e, dt, node, config ){
                var rows = dt.rows( { selected : true } ).indexes( );
                var dte = dt.cells( rows, 0 ).data( ).toArray( );
                $.ajax ({
                  url    : 'bin/php/post/user.php',
                  method : 'POST',
                  data   : {
                    action : 'delete',
                    data : dte
                  },
                  success : function(response){
                    Table_Users.draw();
                  }
                })
              }
            },{
                extend : 'print',
                text : 'Print',
                className : 'form-control'
            },{
                extend : 'copy',
                text : 'Copy',
                className : 'form-control'
            },{
                extend : 'csv',
                text : 'CSV',
                className : 'form-control'
            }
        ]
    } );
});