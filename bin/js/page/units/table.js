function search( link ){
    var api = link.api();
    $('input[name="Search"]', api.table().container())
        .typeahead({
            minLength : 2,
            hint: true,
                highlight: true,
                limit : 5,
                display : 'FieldValue',
            source: function( query, result ){
                $.ajax({
                    url : 'bin/php/get/search/Units.php',
                    method : 'GET',
                      data    : {
                            search                :  $('input:visible[name="Search"]').val(),
                            ID                    :  $('input:visible[name="ID"]').val( ),
                            Name                :  $('input:visible[name="Name"]').val( ),
                            Customer                :  $('input:visible[name="Customer"]').val( ),
                            Location                :  $('input:visible[name="Location"]').val( ),
                            Type                :  $('input:visible[name="Type"]').val( ),
                            Ticket_ID                :  $('input:visible[name="Ticket_ID"]').val( ),
                            Status                :  $('input:visible[name="Status"]').val( ),

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
        select         : {
            style : 'multi',
            selector : 'td.ID'
        },
		ajax      : {
	        url : 'bin/php/get/Units.php',
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
	            d.Search 		= $('input[name="Search"]').val();
	            d.ID 			= $('input[name="ID"]').val( );
	            d.Name 			= $('input[name="Name"]').val( );
	            d.Customer 		= $('input[name="Customer"]').val( );
	            d.Location 		= $('input[name="Location"]').val( );

	            d.Type 			= $('select[name="Type"]').val( );
	            d.Status 		= $('select[name="Status"]').val( );
	                d.Ticket_ID 		= $('select[name="Ticket_ID"]').val( );
	            return d;
	        }
	    },
		columns   : [
			{
				data : 'ID',
                className : 'ID',
				render : function( data, type, row, meta ){
					switch( type ){
						case 'display' :
							return  row.ID !== null
								?   "<div class='row'>" +
								"<div class='col-12'><a href='unit.php?ID=" + row.ID + "'><i class='fa fa-folder-open fa-fw fa-1x'></i> Unit #" + row.ID + "</a></div>" +
								"</div>"
								:   null;
						default :
							return data;
					}

				}
			},{
				data : 'Name',
				render : function ( data, type, row, meta ){
					switch ( type ) {
						case 'display':
							if( row.City_ID === null && row.Building_ID === null ){
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
						case '0': return "<div class='row'><div class='col-12'>Active<div></div>";
						case '1': return "<div class='row'><div class='col-12'>InActive<div></div>";
						case '2': return "<div class='row'><div class='col-12'>Demolished<div></div>";
					}
				}
			},{
				data : 'Ticket_ID',
			/*	render : function( data, type, row, meta ){
					switch ( type ){
						case 'display' :
							return row.Ticket_ID !== null
								?	"<div class='row'>" +
								"<div class='col-12'><a href='ticket.php?ID=" + row.Ticket_ID + "'><i class='fa fa-folder-open fa-fw fa-1x'></i>Ticket #" + row.Ticket_ID + "</a></div>" +
								"<div class='col-12'>" + row.Ticket_Date + "</div>" +
								"</div>"
								: 	null;
						default :
							return data;

					}
				}*/
			}
		],
	    initComplete : function( ){
	        $("div.search").html( "<input type='text' name='Search' placeholder='Search' />" );//onChange='$(\"#Table_Tickets\").DataTable().ajax.reload( );'
	        $('input.date').datepicker( { } );
	        $('input.time').timepicker( {  timeFormat : 'h:i A' } );
	        search( this );
	        $( '.redraw' ).bind( 'change', function(){ Table_Units.draw(); });
	    },
      buttons : [
          {
            text: 'Reset Search',
            className : 'form-control',
            action: function ( e, dt, node, config ) {
                $( 'input:visible, select:visible' ).each( function( ){
                    $( this ).val( '' );
                } );
				Table_Units.draw( );
            }
          },{
            text : 'Get URL',
            className : 'form-control',
			  action : function( e, dt, node, config ){
				  d = { }
				  d.ID = $('input[name="ID"]').val( );
				  d.Name = $('input[name="Name"]').val( );
				  d.Customer = $('input[name="Customer"]').val( );
				  d.Location = $('input[name="Location"]').val( );
				  d.Type = $('input[name="Type"]').val( )=== undefined ? '' : $('input[name="Type"]').val( );
				  d.Status = $('input[name="Status"]').val( )=== undefined ? '' : $('input[name="Status"]').val( );
				  d.Ticket_ID = $('input[name="Ticket_ID"]').val( );
				  document.location.href = 'unit.php?' + new URLSearchParams( d ).toString();
			  }
          },{
            text : 'Create',
            className : 'form-control',
            action : function( e, dt, node, config ){
                document.location.href='unit.php';
            }
          },{
			  text : 'Delete',
			  className : 'form-control',
			  action : function( e, dt, node, config ){
				  var rows = dt.rows( { selected : true } ).indexes( );

				  var dte = dt.cells( rows, 0 ).data( ).toArray( );

				  $.ajax ({
					  url    : 'bin/php/post/Unit.php',
					  method : 'POST',
					  data   : {
						  action : 'delete',
						  data : dte
					  },
					  success : function(response){
						  Table_Units.draw();
					  }
				  })
			  }
		  },{
			  text : 'Duplicate',
			  className : 'form-control',
			  action : function( e, dt, node, config ){
				  var rows = dt.rows( { selected : true } ).indexes( );

				  var dte = dt.cells( rows, 0 ).data( ).toArray( );

				  $.ajax ({
					  url    : 'bin/php/post/Unit.php',
					  method : 'POST',
					  data   : {
						  action : 'create',
						  data : dte
					  },
					  success : function(response){
						  Table_Units.draw();
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
      ]
  } );
});
