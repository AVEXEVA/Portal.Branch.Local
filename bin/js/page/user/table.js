$(document).ready(function( ){
    var Table_Privileges = $('#Table_Privileges').DataTable( {
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
              data : 'Access'
            },{
              data : 'User_Read'
            },{
              data : 'User_Write'
            },{
              data : 'User_Execute'
            },{
              data : 'User_Delete'
            },{
              data : 'Group_Read'
            },{
              data : 'Group_Write'
            },{
              data : 'Group_Execute'
            },{
              data : 'Group_Delete'
            },{
              data : 'Department_Read'
            },{
              data : 'Department_Write'
            },{
              data : 'Department_Execute'
            },{
              data : 'Department_Delete'
            },{
              data : 'User_Read'
            },{
              data : 'User_Write'
            },{
              data : 'User_Execute'
            },{
              data : 'User_Delete'
            },{
              data : 'User_Read'
            },{
              data : 'User_Write'
            },{
              data : 'User_Execute'
            },{
              data : 'User_Delete'
            },{
              data : 'User_Read'
            },{
              data : 'Database_Write'
            },{
              data : 'Database_Execute'
            },{
              data : 'Database_Delete'
            },{
              data : 'Server_Read'
            },{
              data : 'Server_Write'
            },{
              data : 'Server_Execute'
            },{
              data : 'Server_Delete'
            }
        ],
        initComplete : function( ){
            $("div.search").html( "<input type='text' name='Search' placeholder='Search' />" );//onChange='$(\"#Table_Tickets\").DataTable().ajax.reload( );'
            $('input.date').datepicker( { } );
            $('input.time').timepicker( {  timeFormat : 'h:i A' } );
            search( this );
            $( '.redraw' ).bind( 'change', function(){ Table_Privileges.draw(); });
        },
        buttons : [
            {
              text: 'Reset Search',
              className : 'form-control',
              action: function ( e, dt, node, config ) {
                  $( 'input:visible, select:visible' ).each( function( ){
                      $( this ).val( '' );
                  } );
                  Table_Privileges.draw( );
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
                    Table_Privileges.draw();
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
