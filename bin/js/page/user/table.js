$(document).ready(function( ){
  var Editor_Privileges = new $.fn.dataTable.Editor( {
      idSrc    : 'ID',
      ajax     : 'user.php',
      table    : '#Table_Privileges'
  } );
  $('#Table_Privileges').on( 'click', 'tbody td:not(:first-child)', function (e) {
      Editor_Privileges.inline( this );
  } );
  var Table_Privileges = $('#Table_Privileges').DataTable( {
      dom            : "<'row'<'col-sm-3 search'><'col-sm-9'B>><'row'<'col-sm-12't>>",
      processing     : true,
      serverSide     : true,
      searching      : false,
      lengthChange   : false,
      scrollResize   : true,
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
                  ? "<input disabled type='checkbox' name='Owner_Read' " + ( data == true ? 'checked' : null ) + " />"
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
                  ? "<input disabled type='checkbox' name='Owner_Read' " + ( data == true ? 'checked' : null ) + " />"
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
                  ? "<input disabled type='checkbox' name='Owner_Read' " + ( data == true ? 'checked' : null ) + " />"
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
                  ? "<input disabled type='checkbox' name='Owner_Read' " + ( data == true ? 'checked' : null ) + " />"
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
                  ? "<input disabled type='checkbox' name='Owner_Read' " + ( data == true ? 'checked' : null ) + " />"
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
                  ? "<input disabled type='checkbox' name='Owner_Read' " + ( data == true ? 'checked' : null ) + " />"
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
                  ? "<input disabled type='checkbox' name='Owner_Read' " + ( data == true ? 'checked' : null ) + " />"
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
                  ? "<input disabled type='checkbox' name='Owner_Read' " + ( data == true ? 'checked' : null ) + " />"
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
                  ? "<input disabled type='checkbox' name='Owner_Read' " + ( data == true ? 'checked' : null ) + " />"
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
                  ? "<input disabled type='checkbox' name='Owner_Read' " + ( data == true ? 'checked' : null ) + " />"
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
                  ? "<input disabled type='checkbox' name='Owner_Read' " + ( data == true ? 'checked' : null ) + " />"
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
                  ? "<input disabled type='checkbox' name='Owner_Read' " + ( data == true ? 'checked' : null ) + " />"
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
                  ? "<input disabled type='checkbox' name='Owner_Read' " + ( data == true ? 'checked' : null ) + " />"
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
                  ? "<input disabled type='checkbox' name='Owner_Read' " + ( data == true ? 'checked' : null ) + " />"
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
                  ? "<input disabled type='checkbox' name='Owner_Read' " + ( data == true ? 'checked' : null ) + " />"
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
                  ? "<input disabled type='checkbox' name='Owner_Read' " + ( data == true ? 'checked' : null ) + " />"
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
                  ? "<input disabled type='checkbox' name='Owner_Read' " + ( data == true ? 'checked' : null ) + " />"
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
                  ? "<input disabled type='checkbox' name='Owner_Read' " + ( data == true ? 'checked' : null ) + " />"
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
                  ? "<input disabled type='checkbox' name='Owner_Read' " + ( data == true ? 'checked' : null ) + " />"
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
                  ? "<input disabled type='checkbox' name='Owner_Read' " + ( data == true ? 'checked' : null ) + " />"
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
              $.ajax({
                url : 'user.php',
                method : 'POST',
                data : {
                  ID : $( 'input[name="ID"]' ).val( ),
                  Privilege : {
                    'Access' : 'Blank'
                  }
                }
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
