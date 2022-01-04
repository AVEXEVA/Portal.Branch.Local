$(document).ready(function( ){
  var Editor_Privileges = new $.fn.dataTable.Editor( {
      idSrc    : 'ID',
      ajax     : 'bin/php/post/privilege.php',
      table    : '#Table_Privileges',
      fields   : [
        { 
          name : 'ID'
        },{
          name : 'User'
        },{
          name : 'Access'
        },{
          name : 'Owner_Read'
        },{
          name : 'Owner_Write'
        },{
          name : 'Owner_Execute'
        },{
          name : 'Owner_Delete'
        },{
          name : 'Group_Read'
        },{
          name : 'Group_Write'
        },{
          name : 'Group_Execute'
        },{
          name : 'Group_Delete'
        },{
          name : 'Department_Read'
        },{
          name : 'Department_Write'
        },{
          name : 'Department_Execute'
        },{
          name : 'Department_Delete'
        },{
          name : 'Database_Read'
        },{
          name : 'Database_Write'
        },{
          name : 'Database_Execute'
        },{
          name : 'Database_Delete'
        },{
          name : 'Server_Read'
        },{
          name : 'Server_Write'
        },{
          name : 'Server_Execute'
        },{
          name : 'Server_Delete'
        }
      ]
  } );
  $('#Table_Privileges').on( 'change', 'input.editor-checkbox', function () {
    Editor_Privileges
      .edit( $(this).closest('tr'), false )
      .set( $( this ).attr( 'name' ), $(this).prop( 'checked' ) ? true : false )
      .submit();
    document.location.href='user.php?ID=' + $( 'input[name="ID"]' ).val( );
  } );
  $('#Table_Privileges').on( 'click', 'tbody td:nth-child( 2 )', function (e) {
      Editor_Privileges.inline( this );
  } );
  var Table_Privileges = $('#Table_Privileges').DataTable( {
      dom            : "<'row'<'col-sm-3 search'><'col-sm-9'B>><'row'<'col-sm-12't>>",
      processing     : true,
      serverSide     : true,
      searching      : false,
      lengthChange   : false,
      //scrollResize   : true,
      scrollY        : 500,
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
              url     : 'bin/php/get/Privileges.php',
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
          data : 'ID'
        },{
          data : 'Access'
        },{
          data : 'Owner_Read',
          render : function( data, type, row, meta ){
            switch( type ){
              case 'display' :
                return data !== null 
                  ? "<input class='editor-checkbox form-input' type='checkbox' name='Owner_Read' " + ( data == true ? 'checked' : null ) + " />"
                  : null;
              default : 
                return data;
            }
          }
        },{
          data : 'Owner_Write',
          render : function( data, type, row, meta ){
            switch( type ){
              case 'display' :
                return data !== null 
                  ? "<input class='editor-checkbox form-input' type='checkbox' name='Owner_Write' " + ( data == true ? 'checked' : null ) + " />"
                  : null;
              default : 
                return data;
            }
          }
        },{
          data : 'Owner_Execute',
          render : function( data, type, row, meta ){
            switch( type ){
              case 'display' :
                return data !== null 
                  ? "<input class='editor-checkbox form-input' type='checkbox' name='Owner_Execute' " + ( data == true ? 'checked' : null ) + " />"
                  : null;
              default : 
                return data;
            }
          }
        },{
          data : 'Owner_Delete',
          render : function( data, type, row, meta ){
            switch( type ){
              case 'display' :
                return data !== null 
                  ? "<input class='editor-checkbox form-input' type='checkbox' name='Owner_Delete' " + ( data == true ? 'checked' : null ) + " />"
                  : null;
              default : 
                return data;
            }
          }
        },{
          data : 'Group_Read',
          render : function( data, type, row, meta ){
            switch( type ){
              case 'display' :
                return data !== null 
                  ? "<input class='editor-checkbox form-input' type='checkbox' name='Group_Read' " + ( data == true ? 'checked' : null ) + " />"
                  : null;
              default : 
                return data;
            }
          }
        },{
          data : 'Group_Write',
          render : function( data, type, row, meta ){
            switch( type ){
              case 'display' :
                return data !== null 
                  ? "<input class='editor-checkbox form-input' type='checkbox' name='Group_Write' " + ( data == true ? 'checked' : null ) + " />"
                  : null;
              default : 
                return data;
            }
          }
        },{
          data : 'Group_Execute',
          render : function( data, type, row, meta ){
            switch( type ){
              case 'display' :
                return data !== null 
                  ? "<input class='editor-checkbox form-input' type='checkbox' name='Group_Execute' " + ( data == true ? 'checked' : null ) + " />"
                  : null;
              default : 
                return data;
            }
          }
        },{
          data : 'Group_Delete',
          render : function( data, type, row, meta ){
            switch( type ){
              case 'display' :
                return data !== null 
                  ? "<input class='editor-checkbox form-input' type='checkbox' name='Group_Delete' " + ( data == true ? 'checked' : null ) + " />"
                  : null;
              default : 
                return data;
            }
          }
        },{
          data : 'Department_Read',
          render : function( data, type, row, meta ){
            switch( type ){
              case 'display' :
                return data !== null 
                  ? "<input class='editor-checkbox form-input' type='checkbox' name='Department_Read' " + ( data == true ? 'checked' : null ) + " />"
                  : null;
              default : 
                return data;
            }
          }
        },{
          data : 'Department_Write',
          render : function( data, type, row, meta ){
            switch( type ){
              case 'display' :
                return data !== null 
                  ? "<input class='editor-checkbox form-input' type='checkbox' name='Department_Write' " + ( data == true ? 'checked' : null ) + " />"
                  : null;
              default : 
                return data;
            }
          }
        },{
          data : 'Department_Execute',
          render : function( data, type, row, meta ){
            switch( type ){
              case 'display' :
                return data !== null 
                  ? "<input class='editor-checkbox form-input' type='checkbox' name='Department_Execute' " + ( data == true ? 'checked' : null ) + " />"
                  : null;
              default : 
                return data;
            }
          }
        },{
          data : 'Department_Delete',
          render : function( data, type, row, meta ){
            switch( type ){
              case 'display' :
                return data !== null 
                  ? "<input class='editor-checkbox form-input' type='checkbox' name='Department_Delete' " + ( data == true ? 'checked' : null ) + " />"
                  : null;
              default : 
                return data;
            }
          }
        },{
          data : 'Database_Read',
          render : function( data, type, row, meta ){
            switch( type ){
              case 'display' :
                return data !== null 
                  ? "<input class='editor-checkbox form-input' type='checkbox' name='Database_Read' " + ( data == true ? 'checked' : null ) + " />"
                  : null;
              default : 
                return data;
            }
          }
        },{
          data : 'Database_Write',
          render : function( data, type, row, meta ){
            switch( type ){
              case 'display' :
                return data !== null 
                  ? "<input class='editor-checkbox form-input' type='checkbox' name='Database_Write' " + ( data == true ? 'checked' : null ) + " />"
                  : null;
              default : 
                return data;
            }
          }
        },{
          data : 'Database_Execute',
          render : function( data, type, row, meta ){
            switch( type ){
              case 'display' :
                return data !== null 
                  ? "<input class='editor-checkbox form-input' type='checkbox' name='Database_Execute' " + ( data == true ? 'checked' : null ) + " />"
                  : null;
              default : 
                return data;
            }
          }
        },{
          data : 'Database_Delete',
          render : function( data, type, row, meta ){
            switch( type ){
              case 'display' :
                return data !== null 
                  ? "<input class='editor-checkbox form-input' type='checkbox' name='Database_Delete' " + ( data == true ? 'checked' : null ) + " />"
                  : null;
              default : 
                return data;
            }
          }
        },{
          data : 'Server_Read',
          render : function( data, type, row, meta ){
            switch( type ){
              case 'display' :
                return data !== null 
                  ? "<input class='editor-checkbox form-input' type='checkbox' name='Server_Read' " + ( data == true ? 'checked' : null ) + " />"
                  : null;
              default : 
                return data;
            }
          }
        },{
          data : 'Server_Write',
          render : function( data, type, row, meta ){
            switch( type ){
              case 'display' :
                return data !== null 
                  ? "<input class='editor-checkbox form-input' type='checkbox' name='Server_Write' " + ( data == true ? 'checked' : null ) + " />"
                  : null;
              default : 
                return data;
            }
          }
        },{
          data : 'Server_Execute',
          render : function( data, type, row, meta ){
            switch( type ){
              case 'display' :
                return data !== null 
                  ? "<input class='editor-checkbox form-input' type='checkbox' name='Server_Execute' " + ( data == true ? 'checked' : null ) + " />"
                  : null;
              default : 
                return data;
            }
          }
        },{
          data : 'Server_Delete',
          render : function( data, type, row, meta ){
            switch( type ){
              case 'display' :
                return data !== null 
                  ? "<input class='editor-checkbox form-input' type='checkbox' name='Server_Delete' " + ( data == true ? 'checked' : null ) + " />"
                  : null;
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
          //search( this );
          $( '.redraw' ).bind( 'change', function(){ Table_Privileges.draw(); });
      },
      buttons : [
        {
          text : 'Create',
          className : 'form-control',
          action : function( e, dt, node, config ){
            $.ajax({
              url : 'user.php',
              method : 'POST',
              data : {
                ID : $( 'input[name="ID"]' ).val( ),
                Privilege : {
                  'Access' : 'Blank'
                }
              },
              success : function( ){ document.location.href='user.php?ID=' + $( 'input[name="ID"]' ).val( ); }
            });
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
          text : 'Access Field',
          className : 'form-control',
          action : function( e, dt, node, config ){
            $.ajax({
              url : 'bin/php/post/privilege.php',
              method : 'POST',
              data : {
                action : 'access_field',
                User : $( 'input[name="ID"]' ).val( ),
              },
              success : function( code ){ document.location.href = 'user.php?ID=' + $( 'input[name="ID"]' ).val( ); }
            });
          }
        },{
          text : 'Access Office',
          className : 'form-control',
          action : function( e, dt, node, config ){
            $.ajax({
              url : 'bin/php/post/privilege.php',
              method : 'POST',
              data : {
                action : 'access_office',
                User : $( 'input[name="ID"]' ).val( ),
              },
              success : function( code ){ document.location.href = 'user.php?ID=' + $( 'input[name="ID"]' ).val( ); }
            });
          }
        },{
          text : 'Access Admin',
          className : 'form-control',
          action : function( e, dt, node, config ){
            $.ajax({
              url : 'bin/php/post/privilege.php',
              method : 'POST',
              data : {
                action : 'access_admin',
                User : $( 'input[name="ID"]' ).val( ),
              },
              success : function( code ){ document.location.href = 'user.php?ID=' + $( 'input[name="ID"]' ).val( ); }
            });
          }
        }
      ]
  } );
});
