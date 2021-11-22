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
                    return d;
                }
        },
        columns: [
            {
              data    : 'ID'
            },{
              data : 'Email'
            },{
              data : 'Verified'
            },{
              data : 'Branch'
            },{
              data : 'Branch_Type'
            },{
              data : 'Branch_ID'
            }
        ],
        initComplete : function( ){
            $("div.search").html( "<input type='text' name='Search' placeholder='Search' />" );//onChange='$(\"#Table_Tickets\").DataTable().ajax.reload( );' 
            $('input.date').datepicker( { } );
            $('input.time').timepicker( {  timeFormat : 'h:i A' } );
            //search( this );
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