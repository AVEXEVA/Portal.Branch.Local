$( document ).ready( function( ){
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
        d.Customer = $("input[name='Customer']").val( );
        d.Location = $("input[name='Location']").val( );
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
                                  "<div class='col-12'><a href='invoice.php?ID=" + row.ID + "'><i class='fa fa-stack-overflow fa-fw fa-1x'></i> Invoice #" + row.ID + "</a></div>" +
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
      },{
        data : 'Type'
      },{
        data   : 'Date'
      },{
        data   : 'Due'
      },{
        data      : 'Original',
        className :'sum'
      },{
        data      : 'Balance',
        className : 'sum'
      },{
        data : 'Description'
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
                Table_Tickets.draw( );
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
                document.location.href = 'tickets.php?' + new URLSearchParams( d ).toString();
            }
          },{
          text : 'Create',
          className: 'form-control',
          action : function( e, dt, node, config ){
              document.location.href='invoices.php';}
            },{
            text: 'Print',
            className: 'form-control',
            action: function ( e, dt, node, config ) {
                var rows = dt.rows( { selected : true } ).indexes( );
                var dte = dt.cells( rows, 0 ).data( ).toArray( );
                document.location.href = 'print_tickets.php?Tickets=' + dte.join( ',' );
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
  } );
} );
