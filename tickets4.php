<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( [ 'read_and_close' => true ] ); 
    require( '/var/www/portal.live.local/html/cgi-bin/php/index.php' );
}
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query(
        $NEI,
        "   SELECT  Connection.*
		    FROM    Connection
		    WHERE         Connection.Connector = ?
		            AND   Connection.Hash  = ?;",
        array(
            $_SESSION['User'],
            $_SESSION['Hash']
        )
    );
    $Connection = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    $r = sqlsrv_query(
        $NEI,
        "   SELECT  *,
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
        "   SELECT *
		    FROM   Privilege
		    WHERE  Privilege.User_ID = ?;",
        array(
            $_SESSION['User']
        )
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
            "   INSERT INTO Activity([User], [Date], [Page])
                VALUES(?,?,?);",
            array(
                $_SESSION['User'],
                date('Y-m-d H:i:s'), 
                'tickets.php'
            )
        );
?><!DOCTYPE html>
<html lang="en">
<head>
    <?php require( bin_meta . 'index.php');?>
    <title>Nouveau Elevator Portal</title>
    <?php require( bin_css . 'index.php');?>
    <style>#Filters { max-width: 500px; }</style>
    <?php require( bin_js . 'index.php');?>

</head>

<body onload='finishLoadingPage();' style='background-color:#3d3d3d;'>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require(PROJECT_ROOT.'php/element/navigation/index.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id="page-wrapper" class='content'>
			<div class="panel panel-primary" style='margin-bottom:0px;'>
				<div class="panel-heading">
                    <div class='row'>
                        <div class='col-xs-10'><?php echo ucfirst( strtolower( $User[ 'First_Name' ] ) ) . ' ' . ucfirst( strtolower( $User[ 'Last_Name' ] ) ); ?>'s Tickets</div>
                        <div class='col-xs-2'><button style='width:100%;color:black;' onClick="$('#Filters').toggle();">+/-</button></div>
                    </div>
                </div>
				<div class="panel-body no-print" id='Filters' style='border-bottom:1px solid #1d1d1d;'>
                    <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
                    <div class='row'>
                        <div class='col-xs-4'>Search:</div>
                        <div class='col-xs-8'><input type='text' name='Search' placeholder='Search' onChange='redraw();' /></div>
                    </div>
                    <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
					<div class="row"> 
                        <div class='col-xs-4'>Start:</div>
                        <div class='col-xs-8'><input type='text' name='Start_Date' placeholder='Start Date' value='<?php echo isset( $_GET[ 'Start_Date' ] ) ? $_GET['Start_Date'] : date('m/d/Y', strtotime( '-7 days' ) );?>' /></div>
                    </div>
                    <div class='row'>
                        <div class='col-xs-4'>End:</div>
                        <div class='col-xs-8'><input type='text' name='End_Date' placeholder='End Date' value='<?php echo isset( $_GET[ 'End_Date' ] ) ? $_GET['End_Date'] : date('m/d/Y', strtotime( 'now' ) );?>' /></div>
                    </div>
                    <div class='row'>
                        <div class='col-xs-4'>Dates:</div>
                        <div class='col-xs-8'><select name='Filter_Date' onChange='filterDates(this);'>
                            <option value=''>Select</option>
                            <option value='Today'>Today</option>
                            <option value='Yesteray'>Yesterday</option>
                            <option value='This Week'>This Week</option>
                            <option value='Last Week'>Last Week</option>
                            <option value='This Month'>This Month</option>
                            <option value='Last Month'>Last Month</option>
                            <option value='This Year'>This Year</option>
                            <option value='Last Year'>Last Year</option>
                            <option value='All Time'>All Time</option>
                        </select></div>
                    </div>
                    <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
                </div>
    			<div class="panel-body">
    				<table id='Table_Tickets' class='display' cellspacing='0' width='100%' <?php if(isMobile()){?>style='font-size:10px;'<?php }?>>
    					<thead><tr>
    						<th title='ID'>ID</th>
    						<th title='Location'>Location</th>
                            <th title='Unit'>Unit</th>
    						<th title='Status'>Status</th>
    						<th title='Date'>Date</th>
    						<th title='Hours'>Hours</th>
    						<th title='Payroll'>Payroll</th>
    					</tr></thead>
                        <!--<tfoot><tr>
                            <th title='ID'><input type='text' name='ID' onChange='redraw( );' /></th>
                            <th title='Location'><input type='text' name='Location' onChange='redraw( );' /></th>
                            <th title='Status'><input type='text' name='Status' onChange='redraw( );' /></th>
                            <th title='Date'><input type='text' name='Date' onChange='redraw( );' /></th>
                            <th title='Hours'><input type='text' name='Hours' onChange='redraw( );' /></th>
                            <th title='Payroll'><input type='text' name='Payroll' onChange='redraw( );' /></th>
                        </tr></tfoot>-->
    				</table>
    			</div>
            </div>
		</div>
    </div>
    
    <?php require(PROJECT_ROOT.'js/datatables.php');?>
    
    <script>

    var Table_Tickets = $('#Table_Tickets').DataTable( {
        processing : true,
        serverSide : true,
        responsive : true,
        dom : 'tp',
        pagingType : 'simple',
        ajax: {
                url     : 'cgi-bin/php/get/Tickets2.php',
                data:function(d){
                    d = {
                        start : d.start, 
                        length : d.length,
                        order : {
                            column : d.order[0].column,
                            dir : d.order[0].dir
                        },
                        search : $('input[name="Search"]').val()
                    };
                    d.Start_Date    = $('input[name="Start_Date"]').val();
                    d.End_Date      = $('input[name="End_Date"]').val();
                    return d;
                }
        },
        columns: [
            {
				data      : 'ID',
                className : 'hidden'
			},{
				data : 'Location'
			},{
                data : 'Unit'
            },{
				data : 'Status'
			},{
                data : 'Date',
                render: function( data ){
                    return data.substr(0, 10) + '</br>' + data.substr( 11 );
                }
            },{
                data : 'Hours',
                defaultContent :"0"
            },{
                data : 'Payroll',
                defaultContent : 'Unpaid',
                render: function(data) { return data == '0' || data == 'Unknown' ? 'Unpaid' : 'Paid'; }
            }
        ],
        lengthMenu : [ 
            [ 10, 25, 50, 100, 500, -1 ],
            [ 10, 25, 50, 100, 500, 'All' ]
        ],
        lengthChange : false,
        order : [[0, 'asc']],
        language : { 'loadingRecords' : ''},
        initComplete : function(){ },
        drawCallback : function ( settings ) {
            hrefTickets(this.api());
        }
    } );
    function redraw(){
        Table_Tickets.draw( );
    }
    function filterDates( link ){
        var start   = $('input[name="Start_Date"]').val();
        var end     = $('input[name="End_Date"]').val();
        switch( link.value ){
            case 'Today': 
                start = '<?php echo date('m/d/Y', strtotime( 'today' ) );?>';
                end = '<?php echo date('m/d/Y', strtotime( 'today' ) );?>';
                break;
            case 'Yesterday': 
                start = '<?php echo date('m/d/Y', strtotime( 'yesterday' ) );?>';
                end = '<?php echo date('m/d/Y', strtotime( 'yesterday' ) );?>';
                break;
            case 'This Week': 
                start = '<?php echo date('m/d/Y', strtotime( '-7 days' ) );?>';
                end = '<?php echo date('m/d/Y', strtotime( 'today' ) );?>';
                break;
            case 'Last Week': 
                start = '<?php echo date('m/d/Y', strtotime( '-14 days' ) );?>';
                end = '<?php echo date('m/d/Y', strtotime( '-7 days' ) );?>';
                break;
            case 'This Month': 
                start = '<?php echo date('m/d/Y', strtotime( '-30 days' ) );?>';
                end = '<?php echo date('m/d/Y', strtotime( 'today' ) );?>';
                break;
            case 'Last Month': 
                start = '<?php echo date('m/d/Y', strtotime( '-60 days' ) );?>';
                end = '<?php echo date('m/d/Y', strtotime( '-30 days' ) );?>';
                break;
            case 'This Year': 
                start = '<?php echo date('01/01/Y', strtotime( 'today' ) );?>';
                end = '<?php echo date('12/31/Y', strtotime( 'today' ) );?>';
                break;
            case 'Last Year': 
                start = '<?php echo date('01/01/Y', strtotime( '-1 year' ) );?>';
                end = '<?php echo date('12/31/Y', strtotime( '-1 year' ) );?>';
                break;
            case 'All Time': 
                start = '<?php echo '01/01/2000';?>';``
                end = '<?php echo '12/31/2099';?>';
                break;
        }
        $('input[name="Start_Date"]').val( start );
        $('input[name="End_Date"]').val( end );
        redraw();
    }
    function hrefTickets( tbl ){
      $( 'table#Table_Tickets tbody tr' ).each( function( ){
        $( this ).on( 'click' , function( ){ document.location.href = 'ticket.php?ID=' + tbl.row(this).data().ID; });
      });
    }
    </script>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=tickets.php';</script></head></html><?php }?>
