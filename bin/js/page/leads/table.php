<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
header('Content-Type: text/javascript');?>
$( document ).ready( function( ){
  var Editor_Leads = new $.fn.dataTable.Editor({
    idSrc    : 'ID',
    ajax: 'php/post/Lead.php',
    table: '#Table_Leads',
    template: '#Form_Lead'
  });
var Table_Leads = $('#Table_Leads').DataTable( {
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
  url     : 'bin/php/get/Leads.php',
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
      d.ID = $("input[name='ID']").val( );
      d.Name = $("input[name='Name']").val( );
      d.Customer = $("input[name='Customer']").val( );
      d.Type = $("input[name='Type']").val( );
      d.Street = $("input[name='Street']").val( );
      d.City = $("input[name='City']").val( );
      d.State = $("input[name='State']").val( );
      d.Zip = $("input[name='Zip']").val( );
      return d;
    }
  },
  columns: [
      <?php \singleton\datatables::getInstance( )->ID('lead.php','Lead');?>,
      <?php \singleton\datatables::getInstance( )->Name('lead.php');?>,    
      <?php \singleton\datatables::getInstance( )->DataElement('Type');?>,
      <?php \singleton\datatables::getInstance( )->CustomerID();?>,
      <?php \singleton\datatables::getInstance( )->Street();?>,
      <?php \singleton\datatables::getInstance( )->Contact();?>,      
      <?php \singleton\datatables::getInstance( )->DataElement('Probability');?>,
      <?php \singleton\datatables::getInstance( )->DataElement('Level');?>,
      <?php \singleton\datatables::getInstance( )->DataElement('Status');?>
  ],
  initComplete : function( ){
      $("div.search").html( "<input type='text' name='Search' placeholder='Search' />" );//onChange='$(\"#Table_Contacts\").DataTable().ajax.reload( );'
      $('input.date').datepicker( { } );
      $('input.time').timepicker( {  timeFormat : 'h:i A' } );
      //search( this );
      $( '.redraw' ).bind( 'change', function(){ Table_Leads.draw(); });
  },
  buttons: [
      {
          text: 'Reset Search',
          className : 'form-control',
          action: function ( e, dt, node, config ) {
              $( 'input:visible, select:visible' ).each( function( ){
                  $( this ).val( '' );
              } );
              Table_Leads.draw( );
          }
      },{
          text : 'Get URL',
          className : 'form-control',
          action : function( e, dt, node, config ){
              d = { }
              d.ID = $("input[name='ID']").val( );
              d.Name = $("input[name='Name']").val( );
              d.Customer = $("input[name='Customer']").val( );
              d.Type = $("input[name='Type']").val( );
              d.Street = $("input[name='Street']").val( );
              d.City = $("input[name='City']").val( );
              d.State = $("input[name='State']").val( );
              d.Zip = $("input[name='Zip']").val( );
              document.location.href = 'lead.php?' + new URLSearchParams( d ).toString();
          }
      },{
        text : 'Create',
        className : 'form-control',
        action : function( e, dt, node, config ){
            document.location.href='lead.php';
        }
      },{
        text : 'Delete',
        className : 'form-control',
        action : function( e, dt, node, config ){
          var rows = dt.rows( { selected : true } ).indexes( );
          var dte = dt.cells( rows, 0 ).data( ).toArray( );
          $.ajax ({
            url    : 'bin/php/post/lead.php',
            method : 'POST',
            data   : {
              action : 'delete' ,
              data : dte
            },
            success : function(response){
              Table_Leads.draw();
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