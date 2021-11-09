$(document).ready(function( ){
    var Editor_Accounting = new $.fn.dataTable.Editor( {
        idSrc    : 'ID',
        ajax     : 'index.php',
        table    : '#Table_Tickets'
    } );
    var Table_Accounting = $('#Table_Tickets').DataTable( {
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
        responsive     : true,
        select         : {
          style : 'multi',
          selector : 'td.ID'
        },
        ajax: {
                url     : 'bin/php/get/Accounting.php',
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
