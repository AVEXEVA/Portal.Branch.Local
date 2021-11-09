function search( link ){
    var api = link.api();
    $('input[name="Search"]', api.table().container())
        .typeahead({
            minLength : 4,
            highlight : 1,
            displayKey : 'FieldValue',
            source: function( query, result ){
                $.ajax({
                    url : 'bin/php/get/search/Units.php',
                    method : 'GET',
										data : function( d ){
						            d = {
						                start : d.start,
						                length : d.length,
						                order : {
						                    column : d.order[0].column,
						                    dir : d.order[0].dir
						                }
						            };
						            d.Search 		= $('input[name="Search"]').val();
						            d.ID 			= $('input[name="ID"]').val( );
						            d.Name 			= $('input[name="Name"]').val( );
						            d.Customer 		= $('input[name="Customer"]').val( );
						            d.Location 		= $('input[name="Location"]').val( );
						            d.Building_ID 	= $('input[name="Building_ID"]').val( );
						            d.Type 			= $('select[name="Type"]').val( );
						            d.Status 		= $('select[name="Status"]').val( );
						            return d;
						        }
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
	var Editor_Units = new $.fn.dataTable.Editor( {
		idSrc    : 'ID',
	    ajax     : 'index.php',
	    table    : '#Table_Units'
	} );
	var Table_Units = $('#Table_Units').DataTable( {
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
		ajax      : {
	        url : 'bin/php/get/Units.php',
	        data : function( d ){
	            d = {
	                start : d.start,
	                length : d.length,
	                order : {
	                    column : d.order[0].column,
	                    dir : d.order[0].dir
	                }
	            };
	            d.Search 		= $('input[name="Search"]').val();
	            d.ID 			= $('input[name="ID"]').val( );
	            d.Name 			= $('input[name="Name"]').val( );
	            d.Customer 		= $('input[name="Customer"]').val( );
	            d.Location 		= $('input[name="Location"]').val( );
	            d.Building_ID 	= $('input[name="Building_ID"]').val( );
	            d.Type 			= $('select[name="Type"]').val( );
	            d.Status 		= $('select[name="Status"]').val( );
	            return d;
	        }
	    },
		columns   : [
			{
				data : 'ID'
			},{
				data : 'Name',
				render : function ( data, type, row, meta ){
					switch ( type ) {
						case 'display':
							if( row.City_ID === null && row.State === null ){
								return null;
							} else {
								return "<div class='row'>" +
											( row.City_ID !== null ? "<div class='col-12'><a href='unit.php?ID=" + row.ID + "'>" + row.City_ID + "</a></div>" : null ) +
											( row.Building_ID !== null ? "<div class='col-12'><a href='unit.php?ID=" + row.ID + "'>" + row.Building_ID + "</a></div>" : null ) +
										"</div>";
							}
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
	                                    "<div class='col-12'><a href='customer.php?ID=" + row.Customer_ID + "'>" + row.Customer_Name + "</a></div>" +
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
	                                    "<div class='col-12'><a href='location.php?ID=" + row.Location_ID + "'>" + row.Location_Name + "</a></div>" +
	                                "</div>"
	                            :   null;
	                    default :
	                        return data;
	                }

	            }
	        },{
				data : 'Type'
			},{
				data : 'Status',
				render:function(data){
					switch(data){
						case 0: return 'Active';
						case 1: return 'Inactive';
						case 2: return 'Demolished';
					}
				}
			},{
				data : 'Ticket_ID',
				render : function( data, type, row, meta ){
					switch ( type ){
						case 'display' :
							return row.Ticket_ID !== null
								?	"<div class='row'>" +
										"<div class='col-12'><a href='ticket.php?ID=" + row.Ticket_ID + "'>Ticket #" + row.Ticket_ID + "</a></div>" +
										"<div class='col-12'>" + row.Ticket_Date + "</div>" +
									"</div>"
								: 	null;
							default :
								return data;

					}
				}
			}
		],
	    initComplete : function( ){
	        $("div.search").html( "<input type='text' name='Search' placeholder='Search' />" );//onChange='$(\"#Table_Tickets\").DataTable().ajax.reload( );'
	        $('input.date').datepicker( { } );
	        $('input.time').timepicker( {  timeFormat : 'h:i A' } );
	        search( this );
	        $( '.redraw' ).bind( 'change', function(){ Table_Units.draw(); });
	    },
	    buttons: [
	        {
	            text: 'Reset Search',
	            action: function ( e, dt, node, config ) {
	                $( 'input, select' ).each( function( ){
	                    $( this ).val( '' );
	                } );
	                Table_Units.draw( );
	            }
	        },{
	            text : 'Get URL',
	            action : function( e, dt, node, config ){
	                var d = { };
	                d.Search 		= $('input[name="Search"]').val();
	                d.ID 			= $('input[name="ID"]').val( );
	                d.Name 			= $('input[name="Name"]').val( );
	                d.Customer 		= $('input[name="Customer"]').val( );
	                d.Location 		= $('input[name="Location"]').val( );
	                d.Building_ID 	= $('input[name="Building_ID"]').val( );
	                d.Type 			= $('select[name="Type"]').val( );
	                d.Status 		= $('select[name="Status"]').val( );
	                document.location.href = 'units.php?' + new URLSearchParams( d ).toString();
	            }
	        },
	        { extend: 'create', editor: Editor_Units },
	        { extend: 'edit',   editor: Editor_Units },
	        { extend: 'remove', editor: Editor_Units },
	        'copy',
	        'csv'
	    ]
	} );
} );