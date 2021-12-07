function search( link ){
    var api = link.api();
    $('input:visible[name="Search"]', api.table().container())
        .typeahead({
            minLength : 4,
            highlight : 1,
            displayKey : 'FieldValue',
            source: function( query, result ){
                $.ajax({
                    url : 'bin/php/get/search/Contracts.php',
                    method : 'GET',
                    data    : {
                        Customer : $('input:visible[name="Customer"]').val(),
                        Location :  $('input:visible[name="Location"]').val( ),
                        Job : $('input:visible[name="Job"]').val( ),
                        Start_Date :  $('input:visible[name="Start_Date"]').val( ),
                        End_Date :  $('input:visible[name="End_Date"]:visible').val( ),
                        Length : $('select:visible[name="Length"]').val( ),
                        Amount : $('select:visible[name="Amount"]').val( ),
                        Cycle : $('select:visible[name="Cycle"]').val( ),
                        Escalation_Factor : $('input:visible[name="Escalation_Factor"]').val( ),
                        Escalation_Date :  $('input:visible[name="Escalation_Date"]').val( ),
                    },
                    dataType : 'json',
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
                $( 'input:visible[name="' + FieldName.split( '_' )[ 0 ] + '"]' ).val ( FieldValue ).change( );
                $( 'input:visible[name="Search"]').val( '' );
            }
        }
    );
}
$( document ).ready( function() {
  var Table_Contracts = $('#Table_Contracts').DataTable( {
    dom 	   : "<'row'<'col-sm-3 search'><'col-sm-9'B>><'row'<'col-sm-12't>>",
    processing     : true,
    serverSide     : true,
    autoWidth      : false,
    searching      : false,
    lengthChange   : false,
    scrollResize   : true,
    scrollY        : 100,
    scroller       : true,
    scrollCollapse : true,
    paging         : true,
    orderCellsTop  : true,
    select         : {
      style : 'multi',
      selector : 'td.ID'
    },
    ajax       : {
      url : 'bin/php/get/Contracts.php',
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
        d.Customer = $('input[name="Customer"]').val( );
        d.Location = $('input[name="Location"]').val( );
        d.Job = $('input[name="Job"]').val( );
        d.Start_Date = $('input[name="Start_Date"]').val( );
        d.End_Date = $('input[name="End_Date"]').val( );
        d.Length = $('input[name="Length"]').val( );
        d.Amount = $('select[name="Amount"]').val( );
        d.Cycle = $('select[name="Cycle"]').val( );
        d.Escalation_Factor = $('input[name="Escalation_Factor"]').val( );
        d.Escalation_Date = $('input[name="Escalation_Date"]').val( );
        d.Escalation_Cycle = $('select[name="Escalation_Cycle"]').val( );
        d.Link = $('input[name="Link"]').val( );
        d.Remarks = $('input[name="Remarks"]').val( );
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
                                  "<div class='col-12'><a href='contract.php?ID=" + row.ID + "'><i class='fa fa-folder-open fa-fw fa-1x'></i> Contract #" + row.ID + "</a></div>" +
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
                  case 'display' :
                      return  row.Job_ID !== null && row.Job_ID != ''
                          ?   "<div class='row'>" +
                                  "<div class='col-12'><a href='job.php?ID=" + row.Job_ID + "'><i class='fa fa-suitcase fa-fw fa-1x'></i>" + ( row.Job_Name !== null ? row.Job_Name : 'Job #' + row.Job_ID ) + "</a></div>" +
                              "</div>"
                          :   null;
                  default :
                      return data;
              }
          }
      },{
        data 	: 'Start_Date'
      },{
        data  : 'End_Date'
      },{
        data 	: 'Length',
        render  : function( data ){ return data + ' months'; }
      },{
        data 	: 'Amount'
      },{
        data 	: 'Cycle'
      },{
        data 	: 'Escalation_Factor'
      },{
        data 	: 'Escalation_Date'
      },{
        data 	: 'Escalation_Type',
        visible : false
      },{
        data 	: 'Escalation_Cycle',
        visible : false
      },{
        data 	: 'Link',
        render  : function( d ){ return d !== null ? "<a href='" + d + "'>" + d + "</a>" : ''; }
      },{
        data 	: 'Remarks',
        render : $.fn.dataTable.render.ellipsis( 200 )
      }
    ],
    initComplete : function( ){
        $("div.search").html( "<input type='text' name='Search' placeholder='Search' />" );//onChange='$(\"#Table_Contacts\").DataTable().ajax.reload( );'
        $('input.date').datepicker( { } );
        $('input.time').timepicker( {  timeFormat : 'h:i A' } );
        search( this );
        $( '.redraw' ).bind( 'change', function(){ Table_Contracts.draw(); });
    },
    buttons: [
      {
          text: 'Reset Search',
          className: 'form-control',
          action: function ( e, dt, node, config ) {
              $( 'input:visible, select:visible' ).each( function( ){
                  $( this ).val( '' );
              } );
              Table_Contracts.draw( );
          }
      },{
      text : 'Create',
      className: 'form-control',
      action : function( e, dt, node, config ){
          document.location.href='contract.php';}

      },{
        text : 'Delete',
        className: 'form-control',
        action : function( e, dt, node, config ){
          var rows = dt.rows( { selected : true } ).indexes( );
          var dte = dt.cells( rows, 0 ).data( ).toArray( );
          $.ajax ({
            url    : 'bin/php/post/contract.php',
            method : 'POST',
            data   : {
              action : 'delete' ,
              data : dte
            },
            success : function(response){
              Table_Contracts.draw();
            }
          })
        }
      }
    ]
  });
});
