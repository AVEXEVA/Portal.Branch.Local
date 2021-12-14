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
                    url : 'bin/php/get/search/Tickets.php',
                    method : 'GET',
                    data    : {
                        search                :  $('input:visible[name="Search"]').val(),
                        ID                    :  $('input:visible[name="ID"]').val( ),
                        Person                :  $('input:visible[name="Person"]').val( ),
                        Customer              :  $('input:visible[name="Customer"]').val( ),
                        Location              :  $('input:visible[name="Location"]').val( ),
                        Unit                  :  $('input:visible[name="Unit"]').val( ),
                        Job                   :  $('input:visible[name="Job"]').val( ),
                        Type                  :  $('select:visible[name="Type"]').val( ),
                        Level                 :  $('select:visible[name="Level"]').val( ),
                        Status                :  $('select:visible[name="Status"]').val( ),
                        Start_Date            :  $('input:visible[name="Start_Date"]').val( ),
                        End_Date              :  $('input:visible[name="End_Date"]').val( ),
                        Time_Route_Start      :  $('input:visible[name="Time_Route_Start"]').val( ),
                        Time_Route_End        :  $('input:visible[name="Time_Route_End"]').val( ),
                        Time_Site_Start       :  $('input:visible[name="Time_Site_Start"]').val( ),
                        Time_Site_End         :  $('input:visible[name="Time_Site_End"]').val( ),
                        Time_Completed_Start  :  $('input:visible[name="Time_Completed_Start"]').val( ),
                        Time_Completed_End    :  $('input:visible[name="Time_Completed_End"]').val( ),
                        LSD                   :  $('select:visible[name="LSD"]').val( )
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
$(document).ready(function( ){
    var Editor_Tickets = new $.fn.dataTable.Editor( {
        idSrc    : 'ID',
        ajax     : 'index.php',
        table    : '#Table_Tickets'
    } );
    var Table_Tickets = $('#Table_Tickets').DataTable( {
        dom            : "<'row desktop'<'col-sm-3 search'><'col-sm-9'B>><'row'<'col-sm-12't>>",
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
                url     : 'bin/php/get/Tickets.php',
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
                    d.ID             = $('input:visible[name="ID"]').val( );
                    d.Person         = $('input:visible[name="Person"]').val( );
                    d.Customer       = $('input:visible[name="Customer"]').val( );
                    d.Location       = $('input:visible[name="Location"]').val( );
                    d.Unit           = $('input:visible[name="Unit"]').val( );
                    d.Job            = $('input:visible[name="Job"]').val( );
                    d.Type           = $('select:visible[name="Type"]').val( );
                    d.Level          = $('select:visible[name="Level"]').val( );
                    d.Status         = $('select:visible[name="Status"]').val( );
                    d.Start_Date     = $('input:visible[name="Start_Date"]').val( );
                    d.End_Date       = $('input:visible[name="End_Date"]').val( );
                    d.Time_Route_Start     = $('input:visible[name="Time_Route_Start"]').val( );
                    d.Time_Route_End       = $('input:visible[name="Time_Route_End"]').val( );
                    d.Time_Site_Start     = $('input:visible[name="Time_Site_Start"]').val( );
                    d.Time_Site_End       = $('input:visible[name="Time_Site_End"]').val( );
                    d.Time_Completed_Start     = $('input:visible[name="Time_Completed_Start"]').val( );
                    d.Time_Completed_End       = $('input:visible[name="Time_Completed_End"]').val( );
                    d.LSD       = $('select[name="LSD"]').val( );
                    return d;
                }
        },
        columns: [
            <?php \singleton\datatables::getInstance( )->ID('ticket.php','Ticket');?>,
            <?php \singleton\datatables::getInstance( )->TicketPerson();?>,            
            <?php \singleton\datatables::getInstance( )->CustomerID();?>,
            <?php \singleton\datatables::getInstance( )->LocationID(1);?>,
            <?php \singleton\datatables::getInstance( )->UnitID();?>,
            <?php \singleton\datatables::getInstance( )->JobID();?>,
            <?php \singleton\datatables::getInstance( )->TicketLevel();?>,
            <?php \singleton\datatables::getInstance( )->Status();?>,
            <?php \singleton\datatables::getInstance( )->TicketDate();?>,
            <?php \singleton\datatables::getInstance( )->TicketTimeRoute();?>,
            <?php \singleton\datatables::getInstance( )->TicketTimeSite();?>,
            <?php \singleton\datatables::getInstance( )->TicketTimeCompleted();?>,
            <?php \singleton\datatables::getInstance( )->TicketHours();?>,
            <?php \singleton\datatables::getInstance( )->TicketLSD();?>            
        ],
        initComplete : function( ){
            $("div.search").html( "<input type='text' name='Search' placeholder='Search' autocomplete='off' />" );//onChange='$(\"#Table_Tickets\").DataTable().ajax.reload( );'
            $('input.date').datepicker( { } );
            $('input.time').timepicker( {  timeFormat : 'h:i A' } );
            search( this );
            $( '.redraw' ).bind( 'change', function(){ Table_Tickets.draw(); });
        },
        buttons: [
            {
                text: 'Email Ticket',className: 'form-control',
                action: function ( e, dt, node, config ) {
                    var rows = dt.rows( { selected : true } ).indexes( );
                    var dte = dt.cells( rows, 0 ).data( ).toArray( );
                    $.ajax({
                        url : 'bin/php/post/emailTicket.php',
                        method : 'POST',
                        data : {
                            email : prompt( "What email would you like to send the ticket to?"),
                            data : dte
                        },
                        success : function( response ){ }
                    });
                }
            },{
                text: 'Reset Search',className: 'form-control',
                action: function ( e, dt, node, config ) {
                    $( 'input, select' ).each( function( ){
                        $( this ).val( '' );
                    } );
                    Table_Tickets.draw( );
                }
            },{
                text : 'Get URL',className: 'form-control',
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
            },
            {
                text: 'Create',
                className: 'form-control',
                action: function ( e, dt, node, config ) {
                    var rows = dt.rows( { selected : true } ).indexes( );
                    var dte = dt.cells( rows, 0 ).data( ).toArray( );
                    document.location.href = 'invoices.php?Tickets=' + dte.join( ',' );
                }
            },
            { extend: 'create',className: 'form-control', editor: Editor_Tickets },
            { extend: 'edit', className: 'form-control',  editor: Editor_Tickets },
            { extend: 'remove',className: 'form-control', editor: Editor_Tickets },
            {
                text: 'Print',className: 'form-control',
                action: function ( e, dt, node, config ) {
                    var rows = dt.rows( { selected : true } ).indexes( );
                    var dte = dt.cells( rows, 0 ).data( ).toArray( );
                    document.location.href = 'print_tickets.php?Tickets=' + dte.join( ',' );
                }
            },
            { extend: 'copy', className: 'form-control' },
            { extend: 'csv', className: 'form-control' },

        ]
    } );
} );
