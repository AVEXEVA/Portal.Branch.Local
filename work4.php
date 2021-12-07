<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
if( isset( $_SESSION[ 'Connection' ][ 'User' ], $_SESSION[ 'Connection' ][ 'Hash' ] ) ){
  //Connection
    $result = \singleton\database::getInstance( )->query(
      'Portal',
      " SELECT  [Connection].[ID]
        FROM    dbo.[Connection]
        WHERE       [Connection].[User] = ?
                AND [Connection].[Hash] = ?;",
      array(
        $_SESSION[ 'Connection' ][ 'User' ],
        $_SESSION[ 'Connection' ][ 'Hash' ]
      )
    );
    $Connection = sqlsrv_fetch_array($result);
    //User
	$result = \singleton\database::getInstance( )->query(
		null,
		" SELECT  Emp.fFirst  AS First_Name,
		          Emp.Last    AS Last_Name,
		          Emp.fFirst + ' ' + Emp.Last AS Name,
		          Emp.Title AS Title,
		          Emp.Field   AS Field
		  FROM  Emp
		  WHERE   Emp.ID = ?;",
		array(
		  	$_SESSION[ 'Connection' ][ 'User' ]
		)
	);
	$User   = sqlsrv_fetch_array( $result );
	//Privileges
	$Access = 0;
	$Hex = 0;
	$result = \singleton\database::getInstance( )->query(
		'Portal',
		"   SELECT  [Privilege].[Access],
                    [Privilege].[Owner],
                    [Privilege].[Group],
                    [Privilege].[Department],
                    [Privilege].[Database],
                    [Privilege].[Server],
                    [Privilege].[Other],
                    [Privilege].[Token],
                    [Privilege].[Internet]
		  FROM      dbo.[Privilege]
		  WHERE     Privilege.[User] = ?;",
		array(
		  	$_SESSION[ 'Connection' ][ 'User' ],
		)
	);
    $Privileges = array();
    if( $result ){while( $Privilege = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC ) ){

        $key = $Privilege['Access'];
        unset( $Privilege[ 'Access' ] );
        $Privileges[ $key ] = implode( '', array(
        	dechex( $Privilege[ 'Owner' ] ),
        	dechex( $Privilege[ 'Group' ] ),
        	dechex( $Privilege[ 'Department' ] ),
        	dechex( $Privilege[ 'Database' ] ),
        	dechex( $Privilege[ 'Server' ] ),
        	dechex( $Privilege[ 'Other' ] ),
        	dechex( $Privilege[ 'Token' ] ),
        	dechex( $Privilege[ 'Internet' ] )
        ) );
    }}
    if( 	!isset( $Connection[ 'ID' ] )
        ||  !isset( $Privileges[ 'User' ] )
        || 	!check( privilege_read, level_group, $Privileges[ 'User' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
        \singleton\database::getInstance( )->query(
          null,
          " INSERT INTO Activity([User], [Date], [Page] )
            VALUES( ?, ?, ? );",
          array(
            $_SESSION[ 'Connection' ][ 'User' ],
            date('Y-m-d H:i:s'),
            'work4.php'
        )
      );
?><!DOCTYPE html>
<html lang='en'>
<head>
    <title>Nouveau Elevator Portal</title>
    <style>
    table#Table_Tickets tbody tr { height:50px; }
    table#Table_Tickets tbody tr td { padding:5px; padding-top:12.5px; }
    div#page-wrapper .panel-primary .panel-body table tbody tr td.sorting_1 { background-color:transparent !important; color:inherit !important; }
    table#Table_Tickets tbody tr.gold { background-color:gold !important; color:black !important; }
    table#Table_Tickets tbody tr.red { background-color:red !important; color:black !important; }
    table#Table_Tickets tbody tr.blue.odd { background-color:#007bc5 !important; color:white !important; }
    table#Table_Tickets tbody tr.blue.even { background-color:#005b92 !important; color:white !important; }
    table#Table_Tickets tbody tr.light.odd { background-color:whitesmoke !important; color:black !important; }
    table#Table_Tickets tbody tr.light.even { background-color:#e0e0e0 !important; color:black !important; }
    table#Table_Tickets tbody tr.green.odd { background-color:#a1f1a1 !important; color:black !important; }
    table#Table_Tickets tbody tr.green.even { background-color:#90ee90 !important; color:black !important; }
    td.indent { width:0px; }
    table.dataTable tr.dtrg-group td{background-color:#1d1d1d;color:white;}
    table.dataTable tr.dtrg-group.dtrg-level-0 td{font-weight:bold}
    table.dataTable tr.dtrg-group.dtrg-level-1 td,table.dataTable tr.dtrg-group.dtrg-level-2 td{background-color:#5d5d5d;color:white;padding-top:0.25em;padding-bottom:0.25em;padding-left:2em;font-size:0.9em}
    /*table.dataTable tr.dtrg-group.dtrg-level-2 td{background-color:#1d1d1d;color:white;}*/
    </style>
    <title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
       <?php  $_GET[ 'Bootstrap' ] = '5.1';?>
       <?php  $_GET[ 'Entity_CSS' ] = 1;?>
       <?php	require( bin_meta . 'index.php');?>
       <?php	require( bin_css  . 'index.php');?>
       <?php  require( bin_js   . 'index.php');?>
</head>
<body onload='finishLoadingPage();'>
  <div id='wrapper'>
    <?php require( bin_php . 'element/navigation.php');?>
    <?php require( bin_php . 'element/loading.php');?>
    <div id='page-wrapper' class='content'>
		  <div class='card card-full card-primary border-0'>
				<div class='card-heading'><h4><?php \singleton\fontawesome::getInstance( )->Ticket( 1 );?> Work</h4></div>
  			<div class='card-body bg-dark'>
          <table id='Table_Tickets' class='display' cellspacing='0' width='100%'>
  					<thead>
              <th class='text-white border border-white' title='Location'><?php \singleton\fontawesome::getInstance( )->Location();?>Location</th>
              <th class='text-white border border-white' title='ID'><?php \singleton\fontawesome::getInstance( )->Proposal();?>ID</th>
              <th class='text-white border border-white' title='Status'><?php \singleton\fontawesome::getInstance( )->Update();?>Status</th>
              <th class='text-white border border-white' title='Date'><?php \singleton\fontawesome::getInstance( )->Calendar();?>Date</th>
              <th class='text-white border border-white' title='Unit'><?php \singleton\fontawesome::getInstance( )->Unit();?>Unit</th>
  						<th class='text-white border border-white' title='Type'><?php \singleton\fontawesome::getInstance( )->Note();?>Type</th>
              <th class='text-white border border-white' title='Priority'><?php \singleton\fontawesome::getInstance( )->List1();?>Priority</th>
  					</thead>
  				</table>
  			</div>
      </div>
    </div>
  </div>
  <script>
    var grouping_id = 5;
    var grouping_name = 'Level';
    var collapsedGroups = [];
    var groupParent = [];
    var Table_Tickets = $('#Table_Tickets').DataTable( {
      dom            : "<'row'<'col-sm-3 search'><'col-sm-9'B>><'row'<'col-sm-12't>>",
      processing     : true,
      serverSide     : true,
      searching      : false,
      lengthChange   : false,
      scrollResize   : true,
      scrollY        : 100,
      scroller       : false,
      scrollCollapse : false,
      paging         : false,
      orderCellsTop  : true,
      autoWidth      : true,
      ajax: {
        url: 'bin/php/get/Work2.php',
        dataSrc:function(json){
          if( !json.data ){ json.data = []; }
          return json.data;}
      },
      columns: [
        {
          className: 'indent',
          data : 'Tag',
          render : function(data, type, row, meta){
            if(type === 'display'){return '<?php \singleton\fontawesome::getInstance( )->Ticket(1);?>';}
            return data;
          },
          sortable : true,
          visible : false
        },{
          data : 'ID'
        },{
          data : 'Status'
        },{
          data : 'Date',
          render: function(data) {
            if(data === null){return data;}
            else {return data.substr(5,2) + '/' + data.substr(8,2) + '/' + data.substr(0,4);}}
        },{
  				data : 'Unit_State',
          render:function(data, type, row, meta){
            if(type === 'display'){
              if(row.Unit_State === null){return '';}
              return row.Unit_State + ', </br>' + row.Unit_Label;
            }
            return row.Unit_State;
          }
  			},{
          data : 'Level',
          render: function(data, type, row, meta){
            return data;
          }
        },{
          data : 'Priority',
          render: function(data, type, row, meta){
            return data == 1 ? 'Yes' : 'No';
          },
          className : 'hidden'
        }
      ],
      order: [ [ 5, 'asc' ], [0, 'asc' ] ],
      initComplete : function(){ },
      paging : false,
      createdRow : function( row, data, dataIndex ) {
        if ( data['Status'] == 'On Site' || data['Status'] == 'En Route') { $(row).addClass('gold'); }
        else if( data['Priority'] == 1 && data['Status'] != 'Reviewing' && data['Status'] != 'Completed'){ $(row).addClass('red'); }
        else if ( data['Level'] == 'Service Call' && data['Status'] != 'Reviewing' && data['Status'] != 'Completed' && data['Status'] != 'Signed' ){ $(row).addClass('blue'); }
        else if( data['Status'] == 'Signed' ){ $(row).addClass('green'); }
        else if (data['Status'] != 'Reviewing' && data['Status'] != 'Completed' ){ $(row).addClass('light'); }
      },
      rowGroup: {
        // Uses the 'row group' plugin
        dataSrc: [
          'Level',
          'Location'
        ],
        startRender: function(rows, group, level) {
          groupParent[level] = group;

          var groupAll = '';
          for (var i = 0; i < level; i++) {groupAll += groupParent[i]; if (collapsedGroups[groupAll]) {return;}}
          groupAll += group;

          if ((typeof(collapsedGroups[groupAll]) == 'undefined') || (collapsedGroups[groupAll] === null)) {collapsedGroups[groupAll] = true;} //True = Start collapsed. False = Start expanded.

          var collapsed = collapsedGroups[groupAll];
          var newTickets = 0;
          rows.nodes().each(function(r) {
            if(( $(r).children(':nth-child(2)').html() != 'On Site' && $(r).children(':nth-child(2)').html() != 'En Route'  && $(r).children(':nth-child(6)').html() != 'Yes') || $(r).children(':nth-child(2)').html() == 'Reviewing' || $(r).children(':nth-child(2)').html() == 'Signed' || $(r).children(':nth-child(2)').html() == 'Completed'){
              r.style.display = (collapsed ? 'none' : '');
            }
            var start = new Date();
            start.setHours(0,0,0,0);
            var end = new Date();
            end.setHours(23,59,59,999);
            if( new Date($(r).children(':nth-child(3)').html()) >= start && new Date($(r).children(':nth-child(3)').html()) < end && $(r).children(':nth-child(2)').html() != 'Reviewing' && $(r).children(':nth-child(2)').html() != 'Signed' && $(r).children(':nth-child(2)').html() != 'Completed'){ newTickets++; }
          });
          var newString = newTickets > 0 ? ', ' + newTickets + ' new' : '';
          return $('<tr/>').append('<td colspan="'+rows.columns()[0].length+'">' + group  + ' ( ' + rows.count() + ' total' + newString + ' ) </td>').append('<td></td>').attr('data-name', groupAll).toggleClass('collapsed', collapsed);
        }
      },
      buttons: [
        {
            text: 'Email Ticket',
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
                    success : function( response ){
                        console.log( response );
                    }
                });
            }
        },{
            text: 'Reset Search',
            action: function ( e, dt, node, config ) {
                $( 'input, select' ).each( function( ){
                    $( this ).val( '' );
                } );
                Table_Tickets.draw( );
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
                document.location.href = 'tickets.php?' + new URLSearchParams( d ).toString();
            }
        },
        {
          text: 'create',
          action : function( e, dt, node, config ){ document.location.href = 'ticket.php'; }
        },
        {
            text: 'Print',
            action: function ( e, dt, node, config ) {
                var rows = dt.rows( { selected : true } ).indexes( );
                var dte = dt.cells( rows, 0 ).data( ).toArray( );
                document.location.href = 'print_tickets.php?Tickets=' + dte.join( ',' );
            }
        }
        ]
    } );
    $('tbody').on('click', 'tr.dtrg-start', function () {
        var name = $(this).data('name');
        collapsedGroups[name] = !collapsedGroups[name];
        Table_Tickets.draw( );
    });
    function hrefTickets( ){ hrefRow( 'Table_Tickets', 'ticket'); }
    function redraw( ) { Table_Tickets.order( [ [ grouping_id, 'asc' ] ] ).draw( ); }
  </script>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=work.php';</script></head></html><?php }?>
