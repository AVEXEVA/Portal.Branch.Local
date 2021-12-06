$(document).ready(function( ){
    var Table_Requisition_Items = $('#Table_Requisition_Items').DataTable( {
        dom            : "<'row'<'col-sm-12't>>",
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
        responsive     : true,
        select         : {
          style : 'multi',
          selector : 'td.ID'
        },
        ajax: {
                url     : 'bin/php/get/Requisition_Items.php',
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
                    d.ID          = $('#Table_Requisition_Items input:visible[name="ID"]').val( );
                    d.Description = $('#Table_Requisition_Items input:visible[name="Description"]').val( );
                    d.Requisition = $('input[type="hidden"][name="ID"]').val( );
                    return d;
                }
        },
        columns: [
            {
                data : 'ID'
            },{
                data : 'Description'
            },{
                data : 'Image'
            }
        ],
        initComplete : function( ){
            $("div.search").html( "<input type='text' name='Search' placeholder='Search' autocomplete='off' />" );//onChange='$(\"#Table_Tickets\").DataTable().ajax.reload( );'
            $('input.date').datepicker( { } );
            $('input.time').timepicker( {  timeFormat : 'h:i A' } );
            search( this );
            $( '.redraw' ).bind( 'change', function(){ Table_Requisition_Items.draw(); });
        },
        buttons: [
            {
                text: 'Reset Search',
                action: function ( e, dt, node, config ) {
                    $( 'input, select' ).each( function( ){
                        $( this ).val( '' );
                    } );
                    Table_Requisition_Items.draw( );
                }
            },{
                text : 'Get URL',
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
                    document.location.href = 'requisitions.php?' + new URLSearchParams( d ).toString();
                }
            },
            {
                text: 'Print',
                action: function ( e, dt, node, config ) {
                    var rows = dt.rows( { selected : true } ).indexes( );
                    var dte = dt.cells( rows, 0 ).data( ).toArray( );
                    document.location.href = 'Requisitions.php?' + dte.join( ',' );
                }
            },
            'copy',
            'csv'
        ]
    } );
} );
