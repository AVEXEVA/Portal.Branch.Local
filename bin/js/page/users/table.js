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
        //stateSave      : true,
        /*responsive     : {
          details : {
            type   : 'column',
            target : 0
          }
        },*/
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
                    d.ID            = $('input[name="ID"]').val( );
                    d.First_Name    = $('input[name="First_Name"]').val( );
                    d.Last_Name     = $('input[name="Last_Name"]').val( );
                    return d;
                }
        },
        columns: [
            {
                data    : 'ID',
                visible : false
            },{
                data : 'First_Name'
            },{
                data : 'Last_Name'
            }
        ],
        initComplete : function( ){
            $("div.search").html( "<input type='text' name='Search' placeholder='Search' />" );//onChange='$(\"#Table_Tickets\").DataTable().ajax.reload( );' 
            $('input.date').datepicker( { } );
            $('input.time').timepicker( {  timeFormat : 'h:i A' } );
            //search( this );
            $( '.redraw' ).bind( 'change', function(){ Table_Tickets.draw(); });
        },
        buttons: [
            {
                text: 'Reset Search',
                action: function ( e, dt, node, config ) {
                    $( 'input, select' ).each( function( ){
                        $( this ).val( '' );
                    } );
                    Table_Tickets.draw( );
                }
            },{
                text : 'Get URL',
                action : function( e, dt, node, config ){
                    var d = { };
                    d.ID            = $('input[name="ID"]').val( );
                    d.First_Name    = $('input[name="First_Name"]').val( );
                    d.Last_Name     = $('input[name="Last_Name"]').val( );
                    document.location.href = 'users.php?' + new URLSearchParams( d ).toString();
                }
            },
            { extend: 'create', editor: Editor_Users },
            { extend: 'edit',   editor: Editor_Users },
            { extend: 'remove', editor: Editor_Users },
            'copy',
            'csv'
        ]
    } );
});