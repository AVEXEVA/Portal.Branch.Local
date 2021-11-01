<?php
if(session_id() == '' || !isset($_SESSION) ){ session_start(); }
require('cgi-bin/php/index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
  $r = sqlsrv_query(
    $NEI,
    " SELECT *
		  FROM   Connection
		  WHERE  Connection.Connector = ?
		         AND Connection.Hash  = ?;",
    array(
      $_SESSION['User'],
      $_SESSION['Hash']
    )
  );
  $Connection = sqlsrv_fetch_array( $r, SQLSRV_FETCH_ASSOC );
  $r = sqlsrv_query(
    $NEI,
    " SELECT  *,
  		        Emp.fFirst AS First_Name,
  			      Emp.Last   AS Last_Name
  		FROM    Emp
  		WHERE   Emp.ID = ?;",
    array(
      $_SESSION['User']
    )
  );
  $User = sqlsrv_fetch_array($r);
	$r = sqlsrv_query(
    $NEI,
    " SELECT *
		  FROM   Privilege
		  WHERE  Privilege.User_ID = ?;",
    array( $_SESSION['User'] ) 
  );
  $Privileges = array();
	if($r){while($Privilege = sqlsrv_fetch_array($r)){$Privileges[$Privilege['Access_Table']] = $Privilege;}}
    if(	!isset($Connection['ID'])
	   	|| !isset($Privileges['Ticket'])
	  		|| $Privileges['Ticket']['User_Privilege']  < 4
	  		|| $Privileges['Ticket']['Group_Privilege'] < 4){
				?><?php require('../404.html');?><?php }
    else {
  		sqlsrv_query(
        $NEI,
        " INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",
        array(
          $_SESSION['User'],
          date("Y-m-d H:i:s"), 
          'tickets.php'
        )
      );
?><!DOCTYPE html>
<html lang='en'>
<head>
    <?php require('cgi-bin/php/meta.php');?>
    <title>Nouveau Elevator Portal</title>
    <?php require('cgi-bin/css/index.php');?>
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
    <?php require('cgi-bin/js/index.php');?>
</head>
<body onload='finishLoadingPage();'>
  <div id='wrapper' style='background-color:#1d1d1d !important;color:white !important;'>
    <?php require(PROJECT_ROOT.'php/element/navigation/index.php');?>
    <?php require(PROJECT_ROOT.'php/element/loading.php');?>
    <div id='page-wrapper' class='content'>
		  <div class='panel panel-primary' style='margin-bottom:0px;'>
				<div class='panel-heading'>
          <div class='row'>
            <div class='col-xs-10'><?php echo ucfirst(strtolower($User['First_Name'])) . ' ' . ucfirst(strtolower($User['Last_Name']));?>'s Tickets</div>
            <div class='col-xs-2' style='text-align:right;' onClick='document.location.href="ticket.php";'><i class='fa fa-plus fa-fw fa-1x'></i></div>
            <div style='clear:both;'></div>
          </div>
        </div>
  			<div class='panel-body'>
          <table id='Table_Tickets' class='display' cellspacing='0' width='100%' style='<?php if(isMobile()){?>font-size:10px;<?php }?>font-weight:bold;'>
  					<thead>
              <th title='Location'></th>
              <th title='ID'>ID</th>
              <th title='Status'>Status</th>
              <th title='Date'>Date</th>
              <th title='Unit'>Unit</th>
  						<th title='Type'>Type</th>
              <th title='Priority'>Priority</th>
  					</thead>
  				</table>
  			</div>
      </div>
    </div>
  </div>
  <script src='../vendor/bootstrap/js/bootstrap.min.js'></script>
  <?php $_GET[ 'Datatables_Simple' ] = 1; ?>
  <?php require('cgi-bin/js/datatables.php');?>
  <style></style>
  <script src='https://cdn.datatables.net/rowgroup/1.1.2/js/dataTables.rowGroup.min.js'></script>
  <script src='https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js'></script>
  <script>
    var grouping_id = 5;
    var grouping_name = 'Level';
    var collapsedGroups = [];
    var groupParent = [];
    var Table_Tickets = $('#Table_Tickets').DataTable( {
      dom: 'tp',
      ajax: {
        url: 'cgi-bin/php/get/Work2.php',
        dataSrc:function(json){
          if( !json.data ){ json.data = []; }
          return json.data;}
      },
      columns: [
        {
          className: 'indent',
          data : 'Tag',
          render : function(data, type, row, meta){
            if(type === 'display'){return '<?php $Icons->Ticket(1);?>';}
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
      drawCallback : function ( settings ) { 
        hrefTickets( ); 
      }
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
} else {?><html><head><script>document.location.href='../login.php?Forward=work4.php';</script></head></html><?php }?>
