$( document ).ready( function( ){
    var Editor_Requisitions = new $.fn.dataTable.Editor( {
        idSrc    : 'ID',
        ajax     : 'index.php',
        table    : '#Table_Requisitions'
    } );
    var Table_Requisitions = $('#Table_Requisitions').DataTable( {
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
        ajax      : {
            url : 'bin/php/get/Requisitions.php',
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
                d.Search = $('input[name="Search"]').val( );
                d.ID = $('input[name="ID"]').val( );
                d.Name = $('input[name="Name"]').val( );
                d.Employee_ID = $('select[name="Employee_ID"]').val( );
                d.Date = $('select[name="Date"]').val( );
                d.Required = $('select[name="Required"]').val( );
                d.Location_ID = $('select[name="Location_ID"]').val( );
                d.Dropoff_ID = $('select[name="Dropoff_ID"]').val( );
                d.Unit_ID = $('select[name="Unit_ID"]').val( );
                d.Job_ID = $('select[name="Job_ID"]').val( );
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
                                          "<div class='col-12'><a href='customer.php?ID=" + row.ID + "'><i class='fa fa-folder-open fa-fw fa-1x'></i> Customer #" + row.ID + "</a></div>" +
                                      "</div>"
                                  :   null;
                          default :
                              return data;
                      }
                  }
              },{
                data : 'Employee_ID',
                render : function( data, type, row, meta ){
                    switch( type ){
                        case 'display' :
                            return  row.Employee_ID !== null
                                ?   "<div class='row'>" +
                                        "<div class='col-12'><a href='employee.php?ID=" + row.Employee_ID + "'><i class='fa fa-link fa-fw fa-1x'></i> " + row.Employee_Name + "</a></div>" +
                                    "</div>"
                                :   null;
                        default :
                            return data;
                    }
                }
              },{
                data : 'Items',
                render : function( data, type, row, meta ){
                    switch( type ){
                        case 'display' :
                            return  row.Item !== null
                                ?   "<div class='row'>" +
                                        "<div class='col-12'><a href='requisition.php?ID=" + row.Item + "'><i class='fa fa-link fa-fw fa-1x'></i> " + row.Item + "</a></div>" +
                                    "</div>"
                                :   null;
                        default :
                            return data;
                    }
                }
            },{
                data : 'Date'
            },{
                data : 'Required'
            },{
                data : 'Location_ID',
                render : function( data, type, row, meta ){
                    switch( type ){
                        case 'display' :
                            return  row.Location_ID !== null
                                ?   "<div class='row'>" +
                                        "<div class='col-12'><a href='location.php?ID=" + row.Location_ID + "'><i class='fa fa-building fa-fw fa-1x'></i> " + row.Location_Name + "</a></div>" +
                                    "</div>"
                                :   null;
                        default :
                            return data;
                    }
                }
            },{
                data : 'Dropoff_ID',
                render : function( data, type, row, meta ){
                    switch( type ){
                        case 'display' :
                            return  row.Dropoff_ID !== null
                                ?   "<div class='row'>" +
                                        "<div class='col-12'><a href='location.php?ID=" + row.Dropoff_ID + "'><i class='fa fa-suitcase fa-fw fa-1x'></i> " + row.Dropoff_Name + "</a></div>" +
                                    "</div>"
                                :   null;
                        default :
                            return data;
                    }

                }
            },{
                data : 'Unit_ID',
                render : function( data, type, row, meta ){
                    switch( type ){
                        case 'display' :
                            return  row.Unit_ID !== null
                                ?   "<div class='row'>" +
                                        "<div class='col-12'><a href='unit.php?ID=" + row.Unit_ID + "'><i class='fa fa-cogs fa-fw fa-1x'></i> " + row.Unit_Name + "</a></div>" +
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
                        case 'display' :
                            return  row.Job_ID !== null
                                ?   "<div class='row'>" +
                                        "<div class='col-12'><a href='job.php?ID=" + row.Job_ID + "'><i class='fa fa-suitcase fa-fw fa-1x'></i> " + row.Job_Name + "</a></div>" +
                                    "</div>"
                                :   null;
                        default :
                            return data;
                    }

                }
            }
        ],
        buttons: [
            {
                text: 'Reset Search',
                className : 'form-control',
                action: function ( e, dt, node, config ) {
                    $( 'input:visible, select:visible' ).each( function( ){
                        $( this ).val( '' );
                    } );
                    Table_Requisitions.draw( );
                }
            },{
                text : 'Get URL',
                className : 'form-control',
                action : function( e, dt, node, config ){
                    d = { }
                    d.ID = $('input[name="ID"]').val( );
                    d.Name = $('input[name="Name"]').val( );
                    d.Status = $('select[name="Status"]').val( );
                    document.location.href = 'requisitions.php?' + new URLSearchParams( d ).toString();
                }
            },{
                text : 'Create',
                className : 'form-control',
                action : function( e, dt, node, config ){
                    document.location.href='requisition.php';
                }
            },
            {
                text : 'Delete',
                className : 'form-control',
                action : function( e, dt, node, config ){
                  var rows = dt.rows( { selected : true } ).indexes( );
                  var dte = dt.cells( rows, 0 ).data( ).toArray( );
                  $.ajax ({
                    url    : 'bin/php/post/requisitions.php',
                    method : 'POST',
                    data   : {
                      action : 'delete' ,
                      data : dte
                    },
                    success : function(response){
                      Table_Requisitions.draw();
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
            $( '.redraw' ).bind( 'change', function(){ Table_Requisitions.draw(); });
        }
    } );
} );
