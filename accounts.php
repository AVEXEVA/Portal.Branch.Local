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
        ||  !isset( $Privileges[ 'Account' ] )
        || 	!check( privilege_read, level_group, $Privileges[ 'Account' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
        \singleton\database::getInstance( )->query(
          null,
          " INSERT INTO Activity([User], [Date], [Page] )
            VALUES( ?, ?, ? );",
          array(
            $_SESSION[ 'Connection' ][ 'User' ],
            date('Y-m-d H:i:s'),
            'accounts.php'
        )
      );
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Peter D. Speranza">
    <title>Nouveau Elevator Portal</title>
    <?php require( bin_css . 'index.php');?>
    <?php require( bin_js . 'index.php');?>
    <script> function refresh(){ document.location.href='accounts_v2019.php?Start=' + $("input[name='Start']").val() + '&End=' + $("input[name='End']").val(); } </script>
    <script src="https://www.nouveauelevator.com/vendor/flot/excanvas.min.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/flot/jquery.flot.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/flot/jquery.flot.pie.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/flot/jquery.flot.resize.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/flot/jquery.flot.time.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/flot/jquery.flot.categories.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/flot-tooltip/jquery.flot.tooltip.min.js"></script>
</head>
<body onload="finishLoadingPage();"
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require(PROJECT_ROOT.'php/element/navigation.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id="page-wrapper" class='content'>
          <div>
              Start <input type='text' name='Start' value='<?php echo $_GET['Start'];?>' style='color:black;' />
              End <input type='text' name='End' value='<?php echo $_GET['End'];?>' style='color:black;' />
              <button onClick='refresh();' style='color:black;'>Refresh</button>
          </div>
          <div style='text-align:right;'>
            Enter "Maintained" into the "Search" bar to view only maintained locations.
          </div>
			<table id='Table_Customers' class='display' cellspacing='0' width='100%' max-height='800px'>
				<thead>
          <th>Location</th>
					<th>Customer</th>
					<th>Customer</th>
					<th>Territory</th>
					<th>Route</th>
					<th>Location</th>
					<th>Revenue</th>
					<th>Material</th>
					<th>Labor</th>
					<th>Profit</th>
					<th>Profit %</th>
					<th>Overhead</th>
          <th>Profit w/ OH</th>
          <th>Bills</th>
					<th>Profit w/ OH w/o Bills </th>
          <th>Profit Percentage</th>
          <th>Grade</th>
          <th></th>
				</thead>
			</table>
    </div>
  </div>
	<!-- Bootstrap Core JavaScript -->


    <?php require('bin/js/datatables.php');?>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/fixedheader/3.1.2/css/fixedHeader.dataTables.min.css">
    <script src='https://cdn.datatables.net/fixedheader/3.1.5/js/dataTables.fixedHeader.min.js'></script>
    <script src="https://nightly.datatables.net/rowgroup/js/dataTables.rowGroup.min.js"></script>
    <style>
    .dtrg-group td {
      background-color:#f0f0f0 !important;
    }
    </style>
    <!-- Custom Date Filters-->

    <style>
    .Totals.dtrg-group.dtrg-end.dtrg-level-0>td {
        color :black !important;
    }
    @media not print {
    .agood {
      color:darkgreen;
    }
    .vgood {
      color:green;
    }
    .good {
      color:lime;
    }
    .middle {
      color:gray;
    }
    .bad {
      color:pink;
    }
    .vbad {
      color:red;
    }
    .tbad {
      color:darkred;
    }
    }
    tr.group,
    tr.group:hover {
      color:black !important;
      background-color: #ddd !important;
    }
    tr.Totals {
      color:black !important;
    }
    </style>
    <script>
    function getColor(value){
        if(value > 0){return "rgb(0, " + (255 * (value / 589)) + ", 0)";}
        if(value < 0){return "rgb(" + (-255 * (value / 589)) + ", 0, 0)";}
    }
        $(document).ready(function(){
            var groupColumn = 1;
            var Table_Customers = $('#Table_Customers').DataTable( {
                "ajax": {
                    "url":"bin/php/reports/Accounts_v2019.php?Start=" + $("input[name='Start']").val() + "&End=" + $("input[name='End']").val(),
                    "dataSrc":function(json){
                        if(!json.data){json.data = [];}
                        return json.data;}
                },
                <?php if(!isset($_GET['Print'])){?>'scrollY':'75vh',<?php }?>
                dom     : 'Bfrtip',
                paging  : false,
                order   : [[ groupColumn, 'asc' ]],
        				buttons : [
      					'copy',
      					'excel',
      					'csv',
      					'pdf',
      					'print'
        				],
                columns : [
                { "data": "Location_ID", "className":"hidden"},
                { "data": "Customer_Name",  "visible": false, "targets": groupColumn },
                { "data": "Customer_N",  "visible": false},
                { "data": "Territory_Name"},
                { 'data': 'Route_Name'},
                { "data": "Location_Name"},
                { "data": "Invoices_Sum"},
                { "data": "Materials_Sum"},
                { "data": "Labor_Sum"},
                { "data": "Profit"},
                { "data": "Profit_Percentage_Raw"},
                { "data": "Overhead"},
                { "data": "Profit_with_Overhead"},
                { "data": "Bills_Sum"},
                { "data": "Profit_with_Overhead_without_Bills"},
                { "data": "Profit_Percentage"},
                { "data": 'Grade', 'className':'hidden'},
                { "data": "Active", "visible": false}
                //,{ "data": "Cost_Margin"}
                ],
                drawCallback: function ( settings ) {
                    var api = this.api();
                    var rows = api.rows( {page:'current'} ).nodes();
                    var last=null;

                    api.column(groupColumn, {page:'current'} ).data().each( function ( group, i ) {
                        if ( last !== group ) {
                            $(rows).eq( i ).before(
                                '<tr class="group" style="color:black !important;"><td colspan="13"><b><i>'+group+'</i></b></td></tr>'
                            );

                            last = group;
                          }
                        } );
                      },
                      rowGroup: {
                      startRender: null,
                      endRender: function ( rows, group ) {
                        var Invoices_Sum = rows
                        .data()
                        .pluck('Invoices_Sum')
                        .reduce( function (a, b) {
                          return a + b.replace(/[^-\d.]/g, '')*1;
                        }, 0);
                        var I = Invoices_Sum;
                        Invoices_Sum = $.fn.dataTable.render.number(',', '.', 2, '$').display( Invoices_Sum );
                        var Materials_Sum = rows
                            .data()
                            .pluck('Materials_Sum')
                            .reduce( function (a, b) {
                                return a + b.replace(/[^-\d.]/g, '')*1;
                            }, 0);
                        Materials_Sum = $.fn.dataTable.render.number(',', '.', 2, '$').display( Materials_Sum );
                        var Customer_ID  = rows
                            .data()
                            .pluck('Customer_ID').reduce( function (a, b) {
                                return b.replace(/[^-\d.]/g, '')*1;
                            }, 0);;
                        var Labor_Sum = rows
                            .data()
                            .pluck('Labor_Sum')
                            .reduce( function (a, b) {
                                return a + b.replace(/[^-\d.]/g, '')*1;
                            }, 0);
                        Labor_Sum = $.fn.dataTable.render.number(',', '.', 2, '$').display( Labor_Sum );
                        var Profit = rows
                            .data()
                            .pluck('Profit')
                            .reduce( function (a, b) {
                                return a + b.replace(/[^-\d.]/g, '')*1;
                            }, 0);
                        var P2 = Profit;
                        Profit = $.fn.dataTable.render.number(',', '.', 2, '$').display( Profit );

                        var Overhead = rows
                            .data()
                            .pluck('Overhead')
                            .reduce( function (a, b) {
                                return a + b.replace(/[^-\d.]/g, '')*1;
                            }, 0);
                        Overhead = $.fn.dataTable.render.number(',', '.', 2, '$').display( Overhead );
                        var Profit_with_Overhead = rows
                            .data()
                            .pluck('Profit_with_Overhead')
                            .reduce( function (a, b) {
                                return a + b.replace(/[^-\d.]/g, '')*1;
                            }, 0);
                        Profit_with_Overhead = $.fn.dataTable.render.number(',', '.', 2, '$').display( Profit_with_Overhead );
                        var Bills_Sum = rows
                            .data()
                            .pluck('Bills_Sum')
                            .reduce( function (a, b) {
                                return a + b.replace(/[^-\d.]/g, '')*1;
                            }, 0);
                        Bills_Sum = $.fn.dataTable.render.number(',', '.', 2, '$').display( Bills_Sum );
                        var Profit_with_Overhead_without_Bills = rows
                            .data()
                            .pluck('Profit_with_Overhead_without_Bills')
                            .reduce( function (a, b) {
                                return a + b.replace(/[^-\d.]/g, '')*1;
                            }, 0);
                        var P = Profit_with_Overhead_without_Bills;
                        Profit_with_Overhead_without_Bills = $.fn.dataTable.render.number(',', '.', 2, '$').display( Profit_with_Overhead_without_Bills );
                        var Perc2 = P2 < 0 && I < 0 ? ((P2 / I) * -100) : ((P2 / I) * 100);
                        var Profit_Percentage = P < 0 && I < 0 ? ((P / I) * -100) : ((P / I) * 100);
                        var Grade = ((Profit_Percentage / 85) * .65);

                        Profit_Percentage = Profit_Percentage.toFixed(2) + '%';
                        Perc2 = Perc2.toFixed(2) + '%';

                        if(P > 350000){
                          Grade = Grade + .35;
                        } else {
                          Grade = Grade + ((P / 350000) * .35);
                        }
                        Grade = Grade.toFixed(2) + '%';
                        //Profit_Percentage = $.fn.dataTable.render.number(',', '.', 2, '').display( Profit_Percentage ) + '%';

                          return $("<tr/ class='Totals' rel='" + Customer_ID + "''>")
                              .append( '<td colspan="3">Totals for '+group+'</td>' )
                              .append( '<td>'+Invoices_Sum+'</td>')
                              .append( '<td>'+Materials_Sum+'</td>')
                              .append( '<td>'+Labor_Sum+'</td>')
                              .append( '<td>'+Profit+'</td>' )
                              .append( '<td>'+Perc2+'</td>' )
                              .append( '<td>'+Overhead+'</td>' )
                              .append( '<td>'+Profit_with_Overhead+'</td>' )
                              .append( '<td>'+Bills_Sum+'</td>' )
                              .append( '<td>'+Profit_with_Overhead_without_Bills+'</td>' )
                              .append( '<td>'+Profit_Percentage+'</td>' );
                              //.append( '<td>'+Grade+'</td>' );
                      },
                      dataSrc: 'Customer_Name'
                },
                "createdRow": function ( row, data, index ) {
                  //alert(data[10]);
                  /*if(data['Profit_Margin'] > 250){$('td', row).eq(11).addClass('agood');}
                  else if(data['Profit_Margin'] > 100){$('td', row).eq(11).addClass('vgood');}
                  else if(data['Profit_Margin'] > 25){$('td', row).eq(11).addClass('good');}
                  else if(data['Profit_Margin'] > -25){$('td', row).eq(11).addClass('middle');}
                  else if(data['Profit_Margin'] > -100){$('td', row).eq(11).addClass('bad');}
                  else if(data['Profit_Margin'] > -250){$('td', row).eq(11).addClass('vbad');}
                  else {$('td', row).eq(11).addClass('tbad');}*/
                  $('td', row).eq(11).css('color', getColor(data['Profit_Margin']));
                },
                //"lengthMenu":[[-1,10,25,50,100,500],["All",10,25,50,100,500]],
                "language":{"loadingRecords":""},
                "initComplete":function(){
                  finishLoadingPage();
                  $("table#Table_Customers tbody tr[role='row']").on("click", function(){
                    $("tr.Chart").remove();
                    var link = this;
                    var rand = Math.floor(Math.random() * 9999999);
                    $.ajax({
                      url:"bin/js/chart/location_profit.php?Fetched=1&rand=" + rand + "&ID=" + $(this).children(":first-child").html(),
                      method:"GET",
                      success:function(code){
                        $(link).after("<tr class='Chart' ><td colspan='13'><div id='flot-placeholder-profit-" + rand + "' style='height:500px;width:100%;'><div></td></tr>");
                        $(link).after(code);
                      }
                    });
                  });
                  <?php if(isset($_GET['Charts'])){?>
                    $("table#Table_Customers tbody tr[role='row']").each(function(){
                      var link = this;
                      var rand = Math.floor(Math.random() * 9999999);
                      $.ajax({
                        url:"bin/js/chart/location_profit.php?Fetched=1&rand=" + rand + "&ID=" + $(this).children(":first-child").html(),
                        method:"GET",
                        success:function(code){
                          $(link).after("<tr class='Chart' ><td colspan='13'><div id='flot-placeholder-profit-" + rand + "' style='height:500px;width:100%;'><div></td></tr>");
                          $(link).after(code);
                        }
                      });
                    });
                  <?php }?>
                  $("table#Table_Customers tbody tr.Totals").on("click", function(){
                    $("tr.Chart").remove();
                    var link = this;
                    var past = this;
                    $.ajax({
                      url:"bin/js/chart/customer_profit.php?Fetched=1&ID=" + $(link).attr('rel'),
                      method:"GET",
                      success:function(code){
                        $(past).after("<tr class='Chart'><td colspan='13'><div id='flot-placeholder-profit' style='height:500px;width:100%;'><div></td></tr>");
                        $(past).after(code);
                      }
                    });
                  });
                  $("table#Table_Customers tbody tr.group").on("click", function(){
                    $("tr.Chart").remove();
                    var link = this;
                    var IDs = [];
                    while($(link).next().attr('role') == 'row'){
                      var link = $(link).next();
                      IDs.push($(link).children(':first-child').html());
                    }
                    $.ajax({
                      url     : "bin/js/chart/customer_overall_profit.php?Fetched=1&IDs=" + IDs.join(','),
                      method  : "GET",
                      success : function(code){
                        $(link).after("<tr class='Chart'><td colspan='13'><div id='flot-placeholder-profit' style='height:500px;width:100%;'><div></td></tr>");
                        $(link).after(code);
                      }
                    });
                  });
                }
            } );
        });
        $(document).ready(function(){
          $("input[name='Start']").datepicker();
          $("input[name='End']").datepicker();
        });
      </script>
    </body>
  </html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=accounts_v2019.php';</script></head></html><?php }?>
