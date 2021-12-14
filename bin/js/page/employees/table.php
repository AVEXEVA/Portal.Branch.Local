<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
header('Content-Type: text/javascript');?>

function search( link ){
    var api = link.api();
    $('input:visible[name="Search"]', api.table().container())
        .typeahead({
            minLength : 4,
            highlight : 1,
            displayKey : 'FieldValue',
            source: function( query, result ){
                $.ajax({
                    url : 'bin/php/get/search/Employees.php',
                    method : 'GET',
                    data    : {
                        ID : $('input:visible[name="ID"]').val(),
                        First_Name : $('input:visible[name="First_Name"]').val(),
                        Last_Name :  $('input:visible[name="Last_Name"]').val( ),
                        Supervisor : $('input:visible[name="Supervisor"]').val( ),
                        Latittude :  $('input:visible[name="Latittude"]').val( ),
                        Longitude :  $('input:visible[name="Longitude"]').val( ),
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
          d.ID = $('input:visible[name="ID"]').val( );
          d.First_Name = $('input:visible[name="Last_Name"]').val( );
          d.Last_Name = $('input:visible[name="First_Name"]').val( );
          d.Supervisor = $('input:visible[name="Supervisor"]').val( );
          d.Latittude = $('input:visible[name="Latittude"]').val( );
          d.Longitude = $('input:visible[name="Longitude"]').val( );
          return d;
      }
    },
    columns: [
        <?php \singleton\datatables::getInstance( )->ID('employee.php','Employee');?>,
        <?php \singleton\datatables::getInstance( )->DataElement('First_Name');?>,
        <?php \singleton\datatables::getInstance( )->DataElement('Last_Name');?>,
        <?php \singleton\datatables::getInstance( )->DataElement('Supervisor');?>,
        <?php \singleton\datatables::getInstance( )->GPSLocation( );?>,
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
        $( '.redraw' ).bind( 'change', function(){ Table_Employees.draw(); });
    }
  } );
} );
