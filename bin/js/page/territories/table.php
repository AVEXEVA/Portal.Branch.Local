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
                    url : 'bin/php/get/search/Territories.php',
                    method : 'GET',
                    data    : {
                        ID                :  $('input:visible[name="ID"]').val( ),
                        Employee          :  $('input:visible[name="Employee"]').val( ),
                        Location          :  $('input:visible[name="Location"]').val( ),
                        Unit              :  $('input:visible[name="Unit"]').val( ),
                        Leads             :  $('input:visible[name="Leads"]').val( ),
                        Proposals         :  $('input:visible[name="Proposals"]').val( ),
                        Collection        :  $('input:visible[name="Collection"]').val( ),
                        Invoice           :  $('input:visible[name="Invoice"]').val( ),
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
    var Editor_Territories = new $.fn.dataTable.Editor( {
        idSrc    : 'ID',
        ajax     : 'index.php',
        table    : '#Table_Territories'
    } );
    var Table_Territories = $('#Table_Territories').DataTable( {
        dom            : "<'row'<'col-sm-9'B><'col-sm-12 col-lg-3 search'>><'row'<'col-sm-12't>>",
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
            url : 'bin/php/get/Territories.php',
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
                d.Employee = $('input[name="Employee"]').val( );
                d.Location = $('input[name="Location"]').val( );
                d.Unit = $('input[name="Unit"]').val( );
                d.Lead = $('input[name="Lead"]').val( );
                d.Proposal = $('input[name="Proposal"]').val( );
                d.Collection = $('input[name="Collection"]').val( );
                d.Invoice = $('input[name="Invoice"]').val( );
                return d;
            }
        },
        columns: [
            <?php \singleton\datatables::getInstance( )->ID( 'Territory' );?>,
            <?php \singleton\datatables::getInstance( )->Employee( 'Employee' );?>,
            <?php \singleton\datatables::getInstance( )->Locations( 'Territory', 'ID' );?>,
            <?php \singleton\datatables::getInstance( )->Units( 'Territory', 'ID' );?>,
            <?php \singleton\datatables::getInstance( )->Leads( 'Territory', 'ID' );?>,
            <?php \singleton\datatables::getInstance( )->Proposals( 'Territory', 'ID' );?>,
            <?php \singleton\datatables::getInstance( )->Collections( 'Territory', 'ID' );?>,
            <?php \singleton\datatables::getInstance( )->Invoices( 'Territory', 'ID' );?>
        ],
        buttons: [
            {
                text: "<i class='fa fa-refresh fa-fw fa-1x'></i><span class='desktop'>Refresh</span>",
                className : 'form-control',
                action: function ( e, dt, node, config ) {
                    Table_Territories.draw( );
                }
            },{
                text: "<i class='fa fa-undo fa-fw fa-1x'></i><span class='desktop'>Clear</span>",
                className : 'form-control',
                action: function ( e, dt, node, config ) {
                    $( 'input:visible, select:visible' ).each( function( ){
                        $( this ).val( '' );
                    } );
                    Table_Territories.draw( );
                }
            },{
                text : "<i class='fas fa-bookmark'></i><span class='desktop'>Bookmark</span>",
                className : 'form-control',
                action : function( e, dt, node, config ){
                    d = { }
                    d.ID = $('input[name="ID"]').val( );
                    d.Employee = $('input[name="Employee"]').val( );
                    d.Location = $('input[name="Location"]').val( );
                    d.Unit = $('input[name="Unit"]').val( );
                    d.Lead = $('input[name="Lead"]').val( );
                    d.Proposal = $('input[name="Proposal"]').val( );
                    d.Collection = $('input[name="Collection"]').val( );
                    d.Invoice = $('input[name="Invoice"]').val( );
                    document.location.href = 'territories.php?' + new URLSearchParams( d ).toString();
                }
            },{
                text : "<i class='fa fa-plus fa-fw fa-1x'></i><span class='desktop'>New</span>",
                className : 'form-control',
                action : function( e, dt, node, config ){
                    document.location.href='territory.php';
                }
            },
            {
                text : "<i class='fa fa-trash fa-fw fa-1x'></i><span class='desktop'>Delete</span>",
                className : 'form-control',
                action : function( e, dt, node, config ){
                  var rows = dt.rows( { selected : true } ).indexes( );
                  var dte = dt.cells( rows, 0 ).data( ).toArray( );
                  $.ajax ({
                    url    : 'bin/php/post/territory.php',
                    method : 'POST',
                    data   : {
                      action : 'delete' ,
                      data : dte
                    },
                    success : function(response){
                      Table_Territories.draw();
                    }
                  })
                }
              },{
                extend : 'print',
                text : "<i class='fa fa-print fa-fw fa-1x'></i><span class='desktop'>Print</span>",
                className : 'form-control'
            },{
                extend : 'copy',
                text : "<i class='fa fa-copy fa-fw fa-1x'></i><span class='desktop'>Copy</span>",
                className : 'form-control'
            },{
                extend : 'csv',
                text : "<i class='fas fa-file-csv fa-fw fa-1x'></i><span class='desktop'>Export</span>",
                className : 'form-control'
            }
        ],
        initComplete : function( ){
            $("div.search").html( "<input type='text' name='Search' placeholder='Search' class='form-control' />" );//onChange='$(\"#Table_Tickets\").DataTable().ajax.reload( );'
            $('input.date').datepicker( { } );
            $('input.time').timepicker( {  timeFormat : 'h:i A' } );
            //search( this );
            $( '.redraw' ).bind( 'change', function(){ Table_Territories.draw(); });
        }
    } );
} );
