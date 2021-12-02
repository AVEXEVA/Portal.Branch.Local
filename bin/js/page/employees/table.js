$( document ).ready( function( ){
  var Editor_Employees = new $.fn.dataTable.Editor( {
      idSrc    : 'ID',
      ajax     : 'index.php',
      table    : '#Table_Employees'
  } );
  var Table_Employees = $('#Table_Employees').DataTable( {
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
      responsive     : {
        details : {
          type   : 'column',
          target : 0
        }
      },
      select         : {
        style : 'multi',
        selector : 'td.ID'
      },
      ajax: {
        url     : 'bin/php/get/Employees.php',
        data : function( d ){
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
          d.Last_Name = $('input[name="Last_Name"]').val( );
          d.First_Name = $('input[name="First_Name"]').val( );
          d.Supervisor = $('input[name="Supervisor"]').val( );
          return d;
      }
    },
    columns: [
      {
          className : 'ID',
          data : 'ID',
          render : function( data, type, row, meta ){
              switch( type ){
                  case 'display' :
                      return  row.ID !== null
                          ?   "<div class='row'>" +
                                  "<div class='col-12'><a href='employee.php?ID=" + row.ID + "'><i class='fa fa-folder-open fa-fw fa-1x'></i> Employee #" + row.ID + "</a></div>" +
                              "</div>"
                          :   null;
                  default :
                      return data;
              }

          }
      },{
          data : 'Last_Name'
      },{
          data : 'First_Name'
      },{
          data : 'Supervisor'
      }
    ],
    buttons: [
        {
            text: 'Reset Search',
            className: 'form-control',
            action: function ( e, dt, node, config ) {
                $( 'input, select' ).each( function( ){
                    $( this ).val( '' );
                } );
                Table_Employees.draw( );
            }
        },{
            text : 'Get URL',
            className: 'form-control',
            action : function( e, dt, node, config ){
                d = { }
                d.ID = $('input[name="ID"]').val( );
                d.Name = $('input[name="Name"]').val( );
                d.Status = $('select[name="Status"]').val( );
                document.location.href = 'employees.php?' + new URLSearchParams( d ).toString();
            }
        },{
            text : 'Create',
            className: 'form-control',
            action : function( e, dt, node, config ){
                document.location.href='employee.php';
            }
        },
        {
            text : 'Delete',
            className: 'form-control',
            action : function( e, dt, node, config ){
              var rows = dt.rows( { selected : true } ).indexes( );
              var dte = dt.cells( rows, 0 ).data( ).toArray( );
              $.ajax ({
                url    : 'bin/php/post/employee.php',
                method : 'POST',
                data   : {
                  action : 'delete' ,
                  data : dte
                },
                success : function(response){
                  Table_Employees.draw();
                }
              })
            }
         },{
            extend : 'copy',
            text : 'Copy',
            className : 'form-control'
         },{
            extend : 'csv',
            text : 'CSV',
            className : 'form-control'
        }
],
    initComplete : function( ){
        $("div.search").html( "<input type='text' name='Search' placeholder='Search' />" );//onChange='$(\"#Table_Tickets\").DataTable().ajax.reload( );'
        $('input.date').datepicker( { } );
        $('input.time').timepicker( {  timeFormat : 'h:i A' } );
        //search( this );
        $( '.redraw' ).bind( 'change', function(){ Table_Customers.draw(); });
    }
  } );
} );
