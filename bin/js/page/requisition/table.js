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
                    url : 'bin/php/get/search/Requisitions.php',
                    method : 'GET',
                    data    : {
                        ID                    :  $('input:visible[name="ID"]').val( ),
                        User                  :  $('input:visible[name="Person"]').val( ),
                        Start_Date            :  $('input:visible[name="Start_Date"]').val( ),
                        End_Date              :  $('input:visible[name="End_Date"]').val( ),
                        Required              :  $('input:visible[name="Location"]').val( ),
                        Location              :  $('input:visible[name="Unit"]').val( ),
                        Drop_Off              :  $('input:visible[name="Job"]').val( ),
                        Unit                  :  $('select:visible[name="Type"]').val( ),
                        Job                   :  $('select:visible[name="Level"]').val( ),
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
    var Editor_Requsistions = new $.fn.dataTable.Editor( {
        idSrc    : 'ID',
        ajax     : 'index.php',
        table    : '#Table_Requisitions'
    } );
    var Table_Requisitions = $('#Table_Requisitions').DataTable( {
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
                url     : 'bin/php/get/requisitions.php',
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
                    d.User         = $('input:visible[name="User"]').val( );
                    d.Start_Date       = $('input:visible[name="Start_Date"]').val( );
                    d.End_Date        = $('input:visible[name="End_Date"]').val( );
                    d.Required           = $('input:visible[name="Unit"]').val( );
                    d.Location            = $('input:visible[name="Job"]').val( );
                    d.Drop_Off           = $('select:visible[name="Type"]').val( );
                    d.Unit          = $('select:visible[name="Level"]').val( );
                    return d;
                }
        },
        columns: [
        },{
            data : 'ID'
        },{
            data : 'User'
        },{
            data : 'Start_Date'
        },{
            data : 'End_Date'
        },{
            data : 'Required'
        },{
            data : 'Location'
        },{
            data : 'Drop_Off'
        },{
            data : 'Unit'
        ],
        initComplete : function( ){
            $("div.search").html( "<input type='text' name='Search' placeholder='Search' autocomplete='off' />" );//onChange='$(\"#Table_Tickets\").DataTable().ajax.reload( );'
            $('input.date').datepicker( { } );
            $('input.time').timepicker( {  timeFormat : 'h:i A' } );
            search( this );
            $( '.redraw' ).bind( 'change', function(){ Table_Requisitions.draw(); });
        },{
                text: 'Reset Search',
                action: function ( e, dt, node, config ) {
                    $( 'input, select' ).each( function( ){
                        $( this ).val( '' );
                    } );
                    Table_Requisitions.draw( );
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
            { extend: 'create', editor: Editor_Requsistions },
            { extend: 'edit',   editor: Editor_Requsistions },
            { extend: 'remove', editor: Editor_Requsistions },
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
