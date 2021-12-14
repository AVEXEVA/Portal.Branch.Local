<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
header('Content-Type: text/javascript');?>
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
                    url : 'bin/php/get/search/Invoices.php',
                    method : 'GET',
                    data    : {
                        ID                :  $('input:visible[name="ID"]').val( ),
                        Customer          :  $('input:visible[name="Customer"]').val( ),
                        Location          :  $('input:visible[name="Location"]').val( ),
                        Job               :  $('input:visible[name="Job"]').val( ),
                        Unit              :  $('input:visible[name="Unit"]').val( ),
                        Type              :  $('input:visible[name="Type"]').val( ),
                        Date              :  $('input:visible[name="Date"]').val( ),
                        Original          :  $('input:visible[name="Original"]').val( ),
                        Balance           :  $('input:visible[name="Balance"]').val( ),
                        Description       :  $('input:visible[name="Description"]').val( ),
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

$( document ).ready( function( ){
    var Editor_Invoices = new $.fn.dataTable.Editor( {
        idSrc    : 'ID',
        ajax     : 'index.php',
        table    : '#Table_Invoices'
    } );
  var Table_Invoices = $('#Table_Invoices').DataTable( {
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
      url     : 'bin/php/get/Invoices.php',
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
        d.Search = $("input[name='Search']").val( );
        d.ID = $("input[name='ID']").val( );
        d.Customer = $("input[name='Customer']").val( );
        d.Location = $("input[name='Location']").val( );
        d.Job = $("input[name='Job']").val( );
        d.Type = $("input[name='Type']").val( );
        d.Date = $("input[name='Date']").val( );
        d.Due = $("input[name='Due']").val( );
        d.Original_Sum = $("input[name='Original_Sum']").val( );
        d.Balance_Sum = $("input[name='Balance_Sum']").val( );
        d.Description = $("input[name='Description']").val( );
        return d;
      }
    },
    columns: [
        <?php \singleton\datatables::getInstance( )->ID('invoice.php','Invoice');?>,
      {
          data : 'Customer_ID',
          render : function( data, type, row, meta ){
              switch( type ){
                  case 'display' :
                      return  row.Customer_ID !== null
                          ?   "<div class='row'>" +
                                  "<div class='col-12'><a href='customer.php?ID=" + row.Customer_ID + "'><i class='fa fa-link fa-fw fa-1x'></i>" + row.Customer_Name + "</a></div>" +
                              "</div>"
                          :   null;
                  default :
                      return data;
              }

          }
      },{
          data : 'Location_ID',
          render : function( data, type, row, meta ){
              switch( type ){
                  case 'display' :
                      return  row.Location_ID !== null
                          ?   "<div class='row'>" +
                                  "<div class='col-12'><a href='location.php?ID=" + row.Location_ID + "'><i class='fa fa-building fa-fw fa-1x'></i>" + row.Location_Name + "</a></div>" +
                                  "<div class='col-12'>" +
                                      "<div class='row'>" +
                                          "<div class='col-12'><i class='fa fa-map-signs fa-fw fa-1x'></i>" + row.Location_Street + "</div>" +
                                          "<div class='col-12'>" + row.Location_City + ", " + row.Location_State + " " + row.Location_Zip + "</div>" +
                                      "</div>" +
                                  "</div>" +
                              "</div>"
                          :   null;
                  default :
                      return data;
              }

          }
      },{
          data : 'Job_ID',
          render : function( data, type, row, meta ){
              switch( type ){
                  case 'display':
                      return row.Job_ID !== null
                          ?   "<div class='row'>" +
                                  "<div class='col-12'><a href='job.php?ID=" + row.Job_ID   + "'><i class='fa fa-suitcase fa-fw fa-1x'></i>" + row.Job_ID + "</a></div>" +
                                  "<div class='col-12'><a href='job.php?ID=" + row.Job_ID   + "'>" + row.Job_Name + "</a></div>" +
                              "</div>"
                          :   null;
                      default :
                          return data;
              }
          }
      },
      
      <?php \singleton\datatables::getInstance( )->DataElement('Type');?>,
      <?php \singleton\datatables::getInstance( )->DataElement('Date');?>,
      <?php \singleton\datatables::getInstance( )->DataElement('Due');?>,
      <?php \singleton\datatables::getInstance( )->DataElement('Original','sum');?>,
      <?php \singleton\datatables::getInstance( )->DataElement('Balance','sum');?>,
      <?php \singleton\datatables::getInstance( )->DataElement('Description');?>      
    ],
    initComplete : function( ){
        $("div.search").html( "<input type='text' name='Search' placeholder='Search' />" );//onChange='$(\"#Table_Tickets\").DataTable().ajax.reload( );'
        $('input.date').datepicker( { } );
        $('input.time').timepicker( {  timeFormat : 'h:i A' } );
        //search( this );
        $( '.redraw' ).bind( 'change', function(){ Table_Invoices.draw(); });
    },
    buttons: [
        {
            text: 'Email Invoice',
            className: 'form-control',
            action: function ( e, dt, node, config ) {
                var rows = dt.rows( { selected : true } ).indexes( );
                var dte = dt.cells( rows, 0 ).data( ).toArray( );
                $.ajax({
                    url : 'bin/php/post/emailInvoice.php',
                    method : 'POST',
                    data : {
                        email : prompt( "What email would you like to send the invoice to?"),
                        data : dte
                    },
                    success : function( response ){ }
                });
            }
        },{
            text: 'Reset Search',
            className: 'form-control',
            action: function ( e, dt, node, config ) {
                $( 'input, select' ).each( function( ){
                    $( this ).val( '' );
                } );
                Table_Invoices.draw( );
            }
        },{
            text : 'Get URL',
            className: 'form-control',
            action : function( e, dt, node, config ){
                var d = { };
                d.ID             = $('input[name="ID"]').val( );
                d.Person         = $('input[name="Person"]').val( );
                d.Customer       = $('input[name="Customer"]').val( );
                d.Location       = $('input[name="Location"]').val( );
                d.Unit           = $('input[name="Unit"]').val( );
                d.Job            = $('input[name="Job"]').val( );
                d.Type           = $('select[name="Type"]').val( );
                d.Level          = $('select[name="Level"]').val( );
                d.Status         = $('select[name="Status"]').val( );
                d.Start_Date     = $('input[name="Start_Date"]').val( );
                d.End_Date       = $('input[name="End_Date"]').val( );
                d.Time_Route_Start     = $('input[name="Time_Route_Start"]').val( );
                d.Time_Route_End       = $('input[name="Time_Route_End"]').val( );
                d.Time_Site_Start     = $('input[name="Time_Site_Start"]').val( );
                d.Time_Site_End       = $('input[name="Time_Site_End"]').val( );
                d.Time_Completed_Start     = $('input[name="Time_Completed_Start"]').val( );
                d.Time_Completed_End       = $('input[name="Time_Completed_End"]').val( );
                d.LSD       = $('select[name="LSD"]').val( );
                document.location.href = 'invoices.php?' + new URLSearchParams( d ).toString();
            }
          },{
          text : 'Create',
          className: 'form-control',
          action : function( e, dt, node, config ){
              document.location.href='invoice.php';}
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
            },{
                text : 'Delete',
                className: 'form-control',
                action : function( e, dt, node, config ){
                  var rows = dt.rows( { selected : true } ).indexes( );
                  var dte = dt.cells( rows, 0 ).data( ).toArray( );
                  $.ajax ({
                    url    : 'bin/php/post/invoice.php',
                    method : 'POST',
                    data   : {
                      action : 'delete' ,
                      data : dte
                    },
                    success : function(response){
                      Table_Invoices.draw();
                  }
                })
              }
            },
        ],
      });
    });
