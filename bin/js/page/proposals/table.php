<?php
if( session_id( ) == '' || !isset( $_SESSION ) ) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
header('Content-Type: text/javascript');?>
$( document ).ready( function( ){
  var Table_Proposals = $('#Table_Proposals').DataTable( {
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
  url     : 'bin/php/get/Proposals.php',
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
        d.Customer = $("input[name='Customer']").val( );
        d.Location = $("input[name='Location']").val( );
        d.Status = $("select[name='Status']").val( );
        d.Job = $("input[name='Job']").val( );
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
                                "<div class='col-12'><a href='proposal.php?ID=" + row.ID + "'><i class='fa fa-ticket fa-fw fa-1x'></i> Proposal #" + row.ID + "</a></div>" +
                            "</div>"
                        :   null;
                default :
                    return data;
            }

        }
    },{
      data : 'Territory_ID',
      render : function( data, type, row, meta ){
          switch( type ){
              case 'display' :
                  return  row.Territory_ID !== null
                      ?   "<div class='row'>" +
                              "<div class='col-12'><a href='territory.php?ID=" + row.Territory_ID + "'><i class='fa fa-black-tie fa-fw fa-1x'></i> Proposal #" + row.Territory_Name + "</a></div>" +
                          "</div>"
                      :   null;
              default :
                  return data;
          }
      }
    },{
      data : 'Customer_ID',
      render : function( data, type, row, meta ){
          switch( type ){
              case 'display' :
                  return  row.Customer_ID !== null && row.Customer_ID != ''
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
                  return  row.Location_ID !== null && row.Location_ID != ''
                      ?   "<div class='row'>" +
                              "<div class='col-12'><a href='customer.php?ID=" + row.Location_ID + "'><i class='fa fa-building fa-fw fa-1x'></i> " + row.Location_Name + "</a></div>" +
                          "</div>"
                      :   null;
              default :
                  return data;
          }
      }
    },{
      data : 'Contact_ID',
      render : function( data, type, row, meta ){
          switch( type ){
              case 'display' :
                  return  row.Contact_ID !== null
                      ?   "<div class='row'>" +
                              "<div class='col-12'><a href='contact.php?ID=" + row.Contact_ID + "'><i class='fa fa-user fa-fw fa-1x'></i> " + row.Contact_Name + "</a></div>" +
                          "</div>"
                      :   null;
              default :
                  return data;
          }

      }
    },{
      data : 'Title'
    },{
      data : 'Status'
    },{
      data : 'Contact_Phone'
    },{
      data : 'Contact_Email'
    },{
      data : 'Address',
      render : function( data, type, row, meta ){
          switch( type ){
              case 'display' :
                  return  row.Contact_Street !== null && row.Contact_Street != ''
                      ?   "<div class='row'>" +
                              "<div class='col-12'>" +
                                  "<div class='row' onClick=\"document.location.href='https://www.google.com/maps/search/?api=1&query=" + encodeURI( row.Contact_Street + ' ' + row.Contact_City + ' ' + row.Contact_State + ' ' + row.Contact_Zip ) + "';\">" +
                                      "<div class='col-12'><i class='fa fa-map-signs fa-fw fa-1x'></i>" + row.Contact_Street + "</div>" +
                                      "<div class='col-12'>" + row.Contact_City + ", " + row.Contact_State + " " + row.Contact_Zip + "</div>" +
                                  "</div>" +
                              "</div>" +
                          "</div>"
                      :   null;
              default :
                  return data;
          }
        }
    },{
      data   : 'Date'
    },{
        data : 'Job_ID',
        render : function( data, type, row, meta ){
            switch( type ){
                case 'display':
                    return row.Job_ID !== null
                        ?   "<div class='row'>" +
                                "<div class='col-12'><a href='job.php?ID=" + row.Job_ID   + "'>Job #" + row.Job_ID + "</a></div>" +
                                "<div class='col-12'><a href='job.php?ID=" + row.Job_ID   + "'>" + row.Job_Name + "</a></div>" +
                            "</div>"
                        :   null;
                    default :
                        return data;
            }
        }
    },{
      data    :  'Cost'
    },{
      data      : 'Price'
    }],
    buttons: [
        {
            text: 'Reset Search',
            className : 'form-control',
            action: function ( e, dt, node, config ) {
                $( 'input:visible, select:visible' ).each( function( ){
                    $( this ).val( '' );
                } );
                Table_Proposals.draw( );
            }
        },{
          text : 'Get URL',
          className : 'form-control',
          action : function( e, dt, node, config ){
            d = { }
            d.Customer = $("input[name='Customer']").val( );
            d.Location = $("input[name='Location']").val( );
            d.Status = $("select[name='Status']").val( );
            d.Job = $("input[name='Job']").val( );
            document.location.href = 'proposals.php?' + new URLSearchParams( d ).toString();
          }
        },{
          text : 'Create',
          className : 'form-control',
          action : function( e, dt, node, config ){
            document.location.href='proposal.php';
          }
        },
        {
          text : 'Delete',
          className : 'form-control',
          action : function( e, dt, node, config ){
            var rows = dt.rows( { selected : true } ).indexes( );
            var dte = dt.cells( rows, 0 ).data( ).toArray( );
            $.ajax ({
              url    : 'bin/php/post/proposal.php',
              method : 'POST',
              data   : {
                action : 'delete' ,
                data : dte
              },
              success : function(response){
                Table_Proposals.draw();
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
    ],
    initComplete : function( ){
        $("div.search").html( "<input type='text' name='Search' placeholder='Search' />" );//onChange='$(\"#Table_Tickets\").DataTable().ajax.reload( );'
        $('input.date').datepicker( { } );
        $('input.time').timepicker( {  timeFormat : 'h:i A' } );
        //search( this );
        $( '.redraw' ).bind( 'change', function(){ Table_Proposals.draw(); });
    }
  } );
});
