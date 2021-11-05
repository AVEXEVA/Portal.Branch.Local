<?php
session_start( [ 'read_and_close' => true ] );
require('cgi-bin/php/index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"
		SELECT *
		FROM   Connection
		WHERE  Connection.Connector = ?
		       AND Connection.Hash  = ?
	;",array($_SESSION['User'],$_SESSION['Hash']));
    $My_Connection = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    $r = sqlsrv_query($NEI,"
		SELECT *,
		       Emp.fFirst AS First_Name,
			   Emp.Last   AS Last_Name
		FROM   Emp
		WHERE  Emp.ID = ?
	;",array($_SESSION['User']));
    $My_User = sqlsrv_fetch_array($r);
	$r = sqlsrv_query($NEI,"
		SELECT *
		FROM   Privilege
		WHERE  Privilege.User_ID = ?
	;",array($_SESSION['User']));
	$My_Privileges = array();
	if($r){while($My_Privilege = sqlsrv_fetch_array($r)){$My_Privileges[$My_Privilege['Access_Table']] = $My_Privilege;}}
    if(	!isset($My_Connection['ID'])
	   	|| !isset($My_Privileges['Ticket'])
	  		|| $My_Privileges['Ticket']['User_Privilege']  < 4
	  		|| $My_Privileges['Ticket']['Group_Privilege'] < 4){
				?><?php require('../404.html');?><?php }
    else {
		sqlsrv_query($NEI,"
			INSERT INTO Portal.dbo.Activity([User], [Date], [Page])
			VALUES(?,?,?)
		;",array($_SESSION['User'],date("Y-m-d H:i:s"), "tickets.php"));
?><!DOCTYPE html>
<html lang="en">
<head>
    <?php require( bin_meta . 'index.php');?>
    <title>Nouveau Texas | Portal</title>
    <?php require( bin_css . 'index.php');?>
    <?php require( bin_js . 'index.php');?>
</head>

<body style='background-color:#1d1d1d !important;color:white !important;'>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
      <?php require(PROJECT_ROOT.'php/element/navigation/index.php');?>
      <?php require(PROJECT_ROOT.'php/element/loading.php');?>
      <div id="page-wrapper" class='content'>
			  <div class="panel panel-primary" style='margin-bottom:0px;'>
  				<div class="panel-heading"><?php
  				$_GET['Mechanic'] = isset($_GET['Mechanic']) ? $_GET['Mechanic'] : $_SESSION['User'];
  				if(is_numeric($_GET['Mechanic'])){$r = sqlsrv_query($NEI,"SELECT Emp.* FROM Emp WHERE Emp.ID='" . $_GET['Mechanic']. "';");$r = sqlsrv_fetch_array($r);$Mechanic = $r;}
  						else {$Mechanic = $User;}?><h4><div style='float:left;' onclick="document.location.href='home.php';"><?php $Icons->Ticket();?><?php echo proper($Mechanic['fFirst'] . " " . $Mechanic['Last']);?>'s Tickets</div><div style='float:right;' onClick='document.location.href="ticket.php";'><i class='fa fa-plus fa-fw fa-1x'></i></div><div style='clear:both;'></div></h4></div>
    			<div class="panel-body">
            <style>
            table#Table_Tickets tbody tr {
              height:50px;
            }
            table#Table_Tickets tbody tr td {
              padding:5px;
              padding-top:12.5px;
            }
            </style>
            <table id='Table_Tickets' class='display' cellspacing='0' width='100%' style='<?php if(isMobile()){?>font-size:12px;<?php }?>font-weight:bold;'>
    					<thead>
                <th title='Location'></th>
                <th title='Status'>Status</th>
                <th title='Date'>Scheduled</th>
                <th title='Unit'><?php if($Mobile){?>Unit<?php }?></th>
    						<th title='Location of the Ticket'><?php if($Mobile){?>Location<?php }?></th>
    						<th title='Type'>Type</th>
    					</thead>
    				</table>
    			</div>
        </div>
	    </div>
    </div>
    <!-- Bootstrap Core JavaScript -->
    <script src="https://www.nouveauelevator.com/vendor/bootstrap/js/bootstrap.min.js"></script>

    <!-- Metis Menu Plugin JavaScript -->
    <script src="https://www.nouveauelevator.com/vendor/metisMenu/metisMenu.js"></script>

    <?php require(PROJECT_ROOT.'js/datatables.php');?>
    <script src="cgi-bin/js/jquery.dataTables.yadcf.js"></script>

    <!-- Custom Theme JavaScript -->
    <script src="../dist/js/sb-admin-2.js"></script>

    <!--Moment JS Date Formatter-->
    <script src="../dist/js/moment.js"></script>

    <!-- JQUERY UI Javascript -->
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

    <!-- Custom Date Filters-->
    <script src="../dist/js/filters.js"></script>

    <script src="cgi-bin/js/function/formatTicket.js"></script>
    <style>
      table#Table_Tickets tbody tr.gold {background-color:gold !important;color:black !important;}
      table#Table_Tickets tbody tr.red {background-color:red !important;color:black !important;}
      table#Table_Tickets tbody tr.blue.odd {background-color:#007bc5 !important;color:white !important;}
      table#Table_Tickets tbody tr.blue.even {background-color:#005b92 !important;color:white !important;}
      table#Table_Tickets tbody tr.light.odd {background-color:whitesmoke !important;color:black !important;}
      table#Table_Tickets tbody tr.light.even {background-color:#e0e0e0 !important;color:black !important;}
      table#Table_Tickets tbody tr.green.odd {background-color:#a1f1a1 !important;color:black !important;}
      table#Table_Tickets tbody tr.green.even {background-color:#90ee90 !important;color:black !important;}
      td.indent {
        width:0px;;
      }
    </style>
      <script>
          $(document).ready(function() {
              var Table_Tickets = $('#Table_Tickets').DataTable( {
                  "ajax": {
                          "url": "cgi-bin/php/get/Work.php",
                          "dataSrc":function(json){
                              if(!json.data){json.data = [];}
                              return json.data;}
                  },
                  "columns": [
                      {
                        "className":"indent",
                        "data":"Tag",
                        "render":function(data, type, row, meta){
                          if(type === 'display'){return '<?php $Icons->Ticket(1);?>';}
                          return data;
                        },
                        sortable:true
                      },{
                        "data": "Status"
                      },{
                          "data": "Date",
                          render: function(data) {
                            if(data === null){return data;}
                            else {return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}}
                      },{
            						"data": "Unit_State",
                        render:function(data, type, row, meta){
                          if(type === 'display'){
                            if(row.Unit_State === null){return '';}
                            return row.Unit_State + ', </br>' + row.Unit_Label;
                          }
                          return data;
                        }
            					},{
                        "data" : "Job_Type",
                        "visible":false
                      }, {
                        "data" : "Level",
                        render: function(data, type, row, meta){
                          if(type === 'display'){
                            return row.Job_Type + ', </br>' + row.Level;
                          }
                          return data;
                        }
                      }
                  ],
                  "order": [[0, 'asc']],
                  "language":{"loadingRecords":""},
                  "initComplete":function(){finishLoadingPage();},
    			        "paging":false,
                  "createdRow": function( row, data, dataIndex ) {
                    if ( data['Status'] == "On Site" ) {
                      $(row).addClass('gold');
                    } else if( data['Priority'] == 1 && data['Status'] != 'Reviewing' && data['Status'] != 'Completed'){
                      $(row).addClass('red');
                    } else if ( data['Level'] == 'Service Call' && data['Status'] != 'Reviewing' && data['Status'] != 'Completed' && data['Status'] != 'Signed' ){
                      $(row).addClass('blue');
                    } else if( data['Status'] == 'Signed' ){
                    $(row).addClass('green');
                    } else if (data['Status'] != 'Reviewing' && data['Status'] != 'Completed'){
                      $(row).addClass('light');
                    }
                  },
                  "drawCallback": function ( settings ) {
                    var api = this.api();
                    var rows = api.rows( {page:'current'} ).nodes();
                    var last=null;

                    api.column(0, {page:'current'} ).data().each( function ( group, i ) {
                       if (last !== group) {
                           //var Level = api.row(i).data().Level;
                           $(rows).eq(i).before('<tr class="group" style="height:25px;"><td colspan="5" style="padding:5px;"><b><i>' + group /*+ " - " + Level*/ + '</i></b></td></tr>');
                           last = group;
                       }
                    });
                    hrefTickets(this.api());
                  }
              } );
          } );
          function hrefTickets(tbl){
            $("table#Table_Tickets tbody tr").each(function(){
              $(this).on('click',function(){
                document.location.href='ticket.php?ID=' + tbl.row(this).data().ID;
              });
            });
          }

      </script>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=work.php';</script></head></html><?php }?>
