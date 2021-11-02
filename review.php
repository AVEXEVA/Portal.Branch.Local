<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( ); 
    require( '/var/www/nouveautexas.com/html/portal/cgi-bin/php/index.php' );
}
if( isset( $_SESSION[ 'User' ] ,$_SESSION[ 'Hash' ] ) ){
    //Connection
    $result = sqlsrv_query($NEI,
        "   SELECT  *
		    FROM    Connection
            WHERE   Connection.Connector = ?
                    AND Connection.Hash  = ?;",
        array( 
            $_SESSION['User'],
            $_SESSION['Hash']
        )
    );
    $Connection = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC);
    //Employee
    $result = sqlsrv_query($NEI,
        "   SELECT  *,
		            Emp.fFirst AS First_Name,
                    Emp.Last   AS Last_Name
            FROM    Emp
            WHERE   Emp.ID = ?;",
        array(
            $_SESSION[ 'User' ]
        )
    );
    $User = sqlsrv_fetch_array( $result );
    //Privileges
	$result = sqlsrv_query(
        $NEI,
        "   SELECT  *
            FROM    Privilege
            WHERE   Privilege.User_ID = ?;",
        array(
            $_SESSION[ 'User' ]
        )
    );
	$Privileges = array();
	if( $result ){ while( $Privilege = sqlsrv_fetch_array( $result ) ){ $Privileges[ $Privilege[ 'Access_Table' ] ] = $Privilege; } }
    if(	!isset( $Connection[ 'ID' ] )
	   	|| !isset($Privileges[ 'Time' ] )
	  		|| $Privileges[ 'Time' ][ 'User_Privilege' ]  < 4
	  		|| $Privileges[ 'Time' ][ 'Group_Privilege' ] < 4
	  	    || $Privileges[ 'Time' ][ 'Other_Privilege' ] < 4){
				?><?php require( '../404.html' );?><?php }
    else {
		sqlsrv_query(
            $NEI,
            "   INSERT INTO Portal.dbo.Activity([User], [Date], [Page])
                VALUES( ?, ?, ? );",
            array(
                $_SESSION[ 'User' ],
                date( 'Y-m-d H:i:s' ), 
                'review.php'
            )
        );
        //GET FIELD MEHCANICS
        $Selected_Supervisors = explode(',',$_GET['Supervisors']);
        if(count($Selected_Supervisors) == 0 || !isset($_GET['Supervisors']) || $_GET['Supervisors'] == '' || $_GET['Supervisors'] == 'All'){$SQL_Supervisors = "'1' = '1'";}
        else {
            $SQL_Supervisors = "";
            $Supervisors_SQL = array();
            foreach($Selected_Supervisors as $key=>$Selected_Supervisor){$Supervisors_SQL[$key] = "tblWork.Super = '" . $Selected_Supervisor . "'";}
            $SQL_Supervisors = "(" . implode(" OR ",$Supervisors_SQL) . ")";
        }
        $Selected_Mechanics = explode(",",$_GET['Mechanics']);

        if(count($Selected_Mechanics) == 0 || !isset($_GET['Mechanics']) || $_GET['Mechanics'] == ''){$SQL_Selected_Mechanics = "'1' = '1'";}
        else {
            $SQL_Selected_Mechanics = "";
            $Selected_Mechanics_SQL = array();
            foreach($Selected_Mechanics as $key=>$Selected_Mechanic){$Selected_Mechanics_SQL[$key] = "TicketO.fWork = '" . $Selected_Mechanic . "'";}
            $SQL_Selected_Mechanics = "(" . implode(" OR ",$Selected_Mechanics_SQL) . ")";
        }
        $r = sqlsrv_query($NEI,"
        	SELECT Emp.*,
        	       tblWork.Super
        	FROM   Emp
        		   LEFT JOIN tblWork ON 'A' + convert(varchar(10),Emp.ID) + ',' = tblWork.Members
        	WHERE  Emp.Field = 1
        		   AND Emp.Status = 0
          ORDER BY Last ASC
        ;",array(),array("Scrollable"=>SQLSRV_CURSOR_KEYSET));
        $Mechanics = array();
        $row_count = sqlsrv_num_rows( $r );
        $i = 0;
        if($r){
        	while($i < $row_count){
        		$Mechanic = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
        		if(is_array($Mechanic) && $Mechanic != array()){
        			$Mechanics[] = $Mechanic;
        		}
        		$i++;
        	}
        }
?><!DOCTYPE html>
<html lang='en'>
<head>
    <?php require( '/var/www/nouveautexas.com/html/portal/cgi-bin/php/meta.php' );?>
	<title>Nouveau Texas | Portal</title>
    <?php $_GET[ 'Bootstrap' ] = '5.1';?>
    <?php require( '/var/www/nouveautexas.com/html/portal/cgi-bin/css/index.php' );?>
    <style>
        .form-group>label:first-child {
            min-width  : 175px;
            text-align : right;
        }
    </style>
    <?php require( '/var/www/nouveautexas.com/html/portal/cgi-bin/js/index.php' );?>
    <script src="../vendor/bootstrap/js/bootstrap.min.js"></script>
    <?php require( '/var/www/nouveautexas.com/html/portal/cgi-bin/js/datatables.php' );?>
</head>
<body onload='finishLoadingPage();'>
    <div id="wrapper">
        <?php require( '/var/www/nouveautexas.com/html/portal/cgi-bin/php/element/navigation/index.php' );?>
        <?php require( '/var/www/nouveautexas.com/html/portal/cgi-bin/php/element/loading.php' );?>
        <div id='page-wrapper' class='content'>
            <div class='panel panel-primary'>
                <div class='panel-heading' style='background-color:#1e1e1e;color:white;padding:20px;text-align:center;' ><?php $Icons->Ticket( 1 );?> Review Timesheets</div>
                <div class='panel-body'>
                    <div class='row'><div class='col-sm-12'>&nbsp;</div></div>
                    <div class='form-group row'>
                        <label for='Supers' class='col-auto'>Supervisor:</label>
                        <div class='col-auto'>
                            <select class='form-control' name='Supervisors' onChange='refresh_get( );'>
                                <option value='All' <?php if($_GET['Supervisors'] == "All"){?>selected='selected'<?php }?>>All</option>
                                <?php $Supervisors = array();
                                foreach($Mechanics as $Mechanic){
                                    $Mechanic['Super'] = ucfirst(strtolower($Mechanic['Super']));
                                    if(!in_array($Mechanic['Super'],$Supervisors) && !in_array($Mechanic['Super'],['Office','Warehouse','firemen','Dean','Office','Firemen','',' ','  '])){
                                        array_push($Supervisors,$Mechanic['Super']);
                                        ?><option value="<?php echo $Mechanic['Super'];?>" <?php if(in_array($Mechanic['Super'],$Selected_Supervisors)){?>selected='selected'<?php }?>><?php echo $Mechanic['Super'];?></option>
                                        <?php
                                    }
                                }?>
                            </select>
                        </div>
                    </div>
                    <div class='form-group row'>
                        <label class='date col-auto' for="filter_start_date">Week Ending:</label>
                        <?php
                            switch( $Today ){
                                case 'Wednesday' : $Wednesday = date('m/d/Y', strtotime( $Wednesday . ' +0 days')); break;
                                case 'Thursday'  : $Wednesday = date('m/d/Y', strtotime( $Wednesday . ' +6 days')); break;
                                case 'Friday'    : $Wednesday = date('m/d/Y', strtotime( $Wednesday . ' +5 days')); break;
                                case 'Saturday'  : $Wednesday = date('m/d/Y', strtotime( $Wednesday . ' +4 days')); break;
                                case 'Sunday'    : $Wednesday = date('m/d/Y', strtotime( $Wednesday . ' +3 days')); break;
                                case 'Monday'    : $Wednesday = date('m/d/Y', strtotime( $Wednesday . ' +2 days')); break;
                                case 'Tuesday'   : $Wednesday = date('m/d/Y', strtotime( $Wednesday . ' +1 days')); break;
                            }
                        ?>
                        <div class='col-auto'>
                            <input class='start_date form-control' size='10' name='Week_Ending' value='<?php echo strlen( $_GET[ 'Date' ] ) > 1 ? $_GET[ 'Date' ] : $Wednesday; ?>' />
                        </div>
                    </div>
                    <div class='form-group row'>
                        <label class='tag col-auto' for='filter_today'>Quick Dates:</label>
                        <div class='col-auto'>
                            <button class='form-control' onClick="document.location.href='review.php?Date=<?php echo date('m/d/Y', strtotime( 'next Wednesday' ) );?>'">This Week</button>
                        </div>
                        <div class='col-auto'>
                            <button class='form-control' onClick="document.location.href='review.php?Date=<?php echo date('m/d/Y', strtotime( 'last Wednesday' ) );?>'">Last Week</button>
                        </div>
                    </div>
                    <div class='row'><div class='col-sm-12'>&nbsp;</div></div>
                    <div class='form-group row'>
                        <div class='col'><button class='form-control' onClick="refresh_get( );">Search</button></div>
                    </div>
                    <div class='row'><div class='col-sm-12'>&nbsp;</div></div>
                </div>
                <div class="panel-body">
                    <style>
                    .hoverGray:hover {
                        background-color : gold !important;
                    }
                    table#Review tbody tr {
                        color : black !important;
                    }
                    table#Review tbody tr:nth-child( even ) {
                        background-color : rgba( 240, 240, 240, 1 ) !important;
                    }
                    table#Review tbody tr:nth-child( odd ) {
                        background-color : rgba( 255, 255, 255, 1 ) !important;
                    }
                    </style>
                    <?php
                        $WeekOf = DateTime::createFromFormat('m/d/Y',$_GET['Date']);
                        $Wednesday = $WeekOf->format("Y-m-d");
                        $Tuesday = $WeekOf->sub(new DateInterval('P1D'))->format("Y-m-d");
                        $Monday = $WeekOf->sub(new DateInterval('P2D'))->format("Y-m-d");
                        $Sunday = $WeekOf->sub(new DateInterval('P3D'))->format("Y-m-d");
                        $Saturday = $WeekOf->sub(new DateInterval('P4D'))->format("Y-m-d");
                        $Friday = $WeekOf->sub(new DateInterval('P5D'))->format("Y-m-d");
                        $Thursday = $WeekOf->sub(new DateInterval('P6D'))->format("Y-m-d");
                    ?>
                    <table id='Review' width="100%" class="table table-bordered table-hover" style='color : black;'>
                        <thead style='color : white;'>
                            <tr>
                                <th>Last Name</th>
                                <th>First Name</th>
                                <th colspan='2'>Thu</th>
                                <th colspan='2'>Fri</th>
                                <th colspan='2'>Sat</th>
                                <th colspan='2'>Sun</th>
                                <th colspan='2'>Mon</th>
                                <th colspan='2'>Tue</th>
                                <th colspan='2'>Wen</th>
                                <th colspan='2'>Total</th>
                                <th colspan='5'>Expenses</th>
                                
                            </tr>
                            <tr>
                              <th>&nbsp;</th>
                              <th>&nbsp;</th>
                              <th>Reg</th>
                              <th>OT</th>
                              <th>Reg</th>
                              <th>OT</th>
                              <th>Reg</th>
                              <th>OT</th>
                              <th>Reg</th>
                              <th>OT</th>
                              <th>Reg</th>
                              <th>OT</th>
                              <th>Reg</th>
                              <th>OT</th>
                              <th>Reg</th>
                              <th>OT</th>
                              <th>Reg</th>
                              <th>OT</th>
                              <th>Car</th>
                              <th>Other</th>
                              <th colspan='3'>Pictures</th>
                            </tr>
                        </thead>
                        
                        <tbody style=' background-color : white; color : black; '><?php if(!isset($_GET['Preload']) || true){foreach($Mechanics as $Mechanic){
                            $Mechanic['fFirst'] = ucfirst(strtolower($Mechanic['fFirst']));
                            $Mechanic['Last'] = ucfirst(strtolower($Mechanic['Last']));
							$Mechanic['Super'] = ucfirst($Mechanic['Super']);
							$Selected_Supervisors = array_map('ucfirst',$Selected_Supervisors);
                            if((in_array(ucfirst(strtolower($Mechanic['Super'])),$Selected_Supervisors) && !in_array($Mechanic['Super'],array('Office','Warehouse','firemen','Dean','Office','Firemen','',' ','  '))) || $_GET['Supervisors'] == '' || $_GET['Supervisors'] == 'All'){
                                ?>
                            <tr style='cursor:pointer;' class="odd gradeX hoverGray" rel='<?php echo $Mechanic[ 'ID' ];?>'>
                                <?php $Total = 0;?>
                                <td class='Last_Name'><?php echo $Mechanic['Last'];?></td>
                                <td class='First_Name'><?php echo $Mechanic['fFirst'];?></td>
                                <?php $Thursday = date('Y-m-d',strtotime($_GET['Date'] . ' -6 days'));?>
                                <td class='day Thursday' style='font-weight:bold;' rel='<?php echo $Thursday;?>'><?php
                                    $r = sqlsrv_query($NEI,"SELECT Sum(Reg) + Sum(NT) + Sum(TT) AS Summed FROM TicketD WHERE fWork='" . $Mechanic['fWork'] . "' and EDate >= '" . $Thursday . " 00:00:00.000' AND EDate <= '" . $Thursday . " 23:59:59.999'");
                                    echo sqlsrv_fetch_array($r)['Summed'];?></td>
                                <td class='day Thursday' rel='<?php echo $Thursday;?>'><?php
                                    //$Thursday = date('Y-m-d',strtotime($_GET['Date'] . ' -6 days'));
                                    $r = sqlsrv_query($NEI,"SELECT Sum(OT) + Sum(DT) AS Summed FROM TicketD WHERE fWork='" . $Mechanic['fWork'] . "' and EDate >= '" . $Thursday . " 00:00:00.000' AND EDate <= '" . $Thursday . " 23:59:59.999'");
                                    echo sqlsrv_fetch_array($r)['Summed'];?></td>
                                    <?php $Friday = date('Y-m-d',strtotime($_GET['Date'] . ' -5 days'));?>
                                <td class='day Friday' style='font-weight:bold;' rel='<?php echo $Friday;?>'><?php
                                    
                                    $r = sqlsrv_query($NEI,"SELECT Sum(Reg) + Sum(NT) + Sum(TT)  AS Summed FROM TicketD WHERE fWork='" . $Mechanic['fWork'] . "' and EDate >= '" . $Friday . " 00:00:00.000' AND EDate <= '" . $Friday . " 23:59:59.999'");
                                    echo sqlsrv_fetch_array($r)['Summed'];?></td>
                                    <?php $Saturday = date('Y-m-d',strtotime($_GET['Date'] . ' -4 days'));?>
                                <td class='day Friday' rel='<?php echo $Friday;?>'><?php
                                    //$Friday = date('Y-m-d',strtotime($_GET['Date'] . ' -5 days'));
                                    $r = sqlsrv_query($NEI,"SELECT Sum(OT) + Sum(DT) AS Summed FROM TicketD WHERE fWork='" . $Mechanic['fWork'] . "' and EDate >= '" . $Friday . " 00:00:00.000' AND EDate <= '" . $Friday . " 23:59:59.999'");
                                    echo sqlsrv_fetch_array($r)['Summed'];?></td>
                                <td class='day Saturday' style='font-weight:bold;' rel='<?php echo $Saturday;?>'><?php
                                    
                                    $r = sqlsrv_query($NEI,"SELECT Sum(Reg) + Sum(NT) + Sum(TT)  AS Summed FROM TicketD WHERE fWork='" . $Mechanic['fWork'] . "' and EDate >= '" . $Saturday . " 00:00:00.000' AND EDate <= '" . $Saturday . " 23:59:59.999'");
                                    echo sqlsrv_fetch_array($r)['Summed'];?></td>
                                <td class='day Saturday' rel='<?php echo $Saturday;?>'><?php
                                    //$Saturday = date('Y-m-d',strtotime($_GET['Date'] . ' -4 days'));
                                    $r = sqlsrv_query($NEI,"SELECT Sum(OT) + Sum(DT) AS Summed FROM TicketD WHERE fWork='" . $Mechanic['fWork'] . "' and EDate >= '" . $Saturday . " 00:00:00.000' AND EDate <= '" . $Saturday . " 23:59:59.999'");
                                    echo sqlsrv_fetch_array($r)['Summed'];?></td>
                                    <?php $Sunday = date('Y-m-d',strtotime($_GET['Date'] . ' -3 days'));?>
                                <td class='day Sunday' style='font-weight:bold;' rel='<?php echo $Sunday;?>'><?php
                                    
                                    $r = sqlsrv_query($NEI,"SELECT Sum(Reg) + Sum(NT) + Sum(TT)  AS Summed FROM TicketD WHERE fWork='" . $Mechanic['fWork'] . "' and EDate >= '" . $Sunday . " 00:00:00.000' AND EDate <= '" . $Sunday . " 23:59:59.999'");
                                    echo sqlsrv_fetch_array($r)['Summed'];?></td>
                                <td class='day Sunday' rel='<?php echo $Sunday;?>'><?php
                                    //$Sunday = date('Y-m-d',strtotime($_GET['Date'] . ' -3 days'));
                                    $r = sqlsrv_query($NEI,"SELECT Sum(OT) + Sum(DT) AS Summed FROM TicketD WHERE fWork='" . $Mechanic['fWork'] . "' and EDate >= '" . $Sunday . " 00:00:00.000' AND EDate <= '" . $Sunday . " 23:59:59.999'");
                                    echo sqlsrv_fetch_array($r)['Summed'];?></td>
                                    <?php $Monday = date('Y-m-d',strtotime($_GET['Date'] . ' -2 days'));?>
                                <td class='day Monday' style='font-weight:bold;' rel='<?php echo $Monday;?>'><?php
                                    
                                    $r = sqlsrv_query($NEI,"SELECT Sum(Reg) + Sum(NT) + Sum(TT)  AS Summed FROM TicketD WHERE fWork='" . $Mechanic['fWork'] . "' and EDate >= '" . $Monday . " 00:00:00.000' AND EDate <= '" . $Monday . " 23:59:59.999'");
                                    echo sqlsrv_fetch_array($r)['Summed'];?></td>
                                <td class='day Monday' rel='<?php echo $Monday;?>'><?php
                                    //$Monday = date('Y-m-d',strtotime($_GET['Date'] . ' -2 days'));
                                    $r = sqlsrv_query($NEI,"SELECT Sum(OT) + Sum(DT) AS Summed FROM TicketD WHERE fWork='" . $Mechanic['fWork'] . "' and EDate >= '" . $Monday . " 00:00:00.000' AND EDate <= '" . $Monday . " 23:59:59.999'");
                                    echo sqlsrv_fetch_array($r)['Summed'];?></td>
                                    <?php $Tuesday = date('Y-m-d',strtotime($_GET['Date'] . ' -1 days'));?>
                                <td class='day Tuesday' style='font-weight:bold;' rel='<?php echo $Tuesday;?>'><?php
                                    
                                    $r = sqlsrv_query($NEI,"SELECT Sum(Reg) + Sum(NT) + Sum(TT)  AS Summed FROM TicketD WHERE fWork='" . $Mechanic['fWork'] . "' and EDate >= '" . $Tuesday . " 00:00:00.000' AND EDate <= '" . $Tuesday . " 23:59:59.999'");
                                    echo sqlsrv_fetch_array($r)['Summed'];?></td>
                                <td class='day Tuesday' rel='<?php echo $Tuesday;?>'><?php
                                    //$Tuesday = date('Y-m-d',strtotime($_GET['Date'] . ' -1 days'));
                                    $r = sqlsrv_query($NEI,"SELECT Sum(OT) + Sum(DT) AS Summed FROM TicketD WHERE fWork='" . $Mechanic['fWork'] . "' and EDate >= '" . $Tuesday . " 00:00:00.000' AND EDate <= '" . $Tuesday . " 23:59:59.999'");
                                    echo sqlsrv_fetch_array($r)['Summed'];?></td>
                                    <?php $Wednesday = date('Y-m-d',strtotime($_GET['Date']));?>
                                <td class='day Wednesday' style='font-weight:bold;' rel='<?php echo $Wednesday;?>'><?php
                                    
                                    $r = sqlsrv_query($NEI,"SELECT Sum(Reg) + Sum(NT) + Sum(TT)  AS Summed FROM TicketD WHERE fWork='" . $Mechanic['fWork'] . "' and EDate >= '" . $Wednesday . " 00:00:00.000' AND EDate <= '" . $Wednesday . " 23:59:59.999'");
                                    echo sqlsrv_fetch_array($r)['Summed'];?></td>
                                <td class='day Wednesday' rel='<?php echo $Wednesday;?>'><?php
                                    //$Wednesday = date('Y-m-d',strtotime($_GET['Date']));
                                    $r = sqlsrv_query($NEI,"SELECT Sum(OT) + Sum(DT) AS Summed FROM TicketD WHERE fWork='" . $Mechanic['fWork'] . "' and EDate >= '" . $Wednesday . " 00:00:00.000' AND EDate <= '" . $Wednesday . " 23:59:59.999'");
                                    echo sqlsrv_fetch_array($r)['Summed'];?></td>
                                <td class='week Total' style='font-weight:bold;' rel='<?php echo $Thursday;?>'><?php
                                    $r = sqlsrv_query($NEI,"SELECT Sum(Reg) + Sum(NT) + Sum(TT)  AS Summed FROM TicketD WHERE fWork='" . $Mechanic['fWork'] . "' and EDate >= '" . $Thursday . " 00:00:00.000' AND EDate <= '" . $Wednesday . " 23:59:59.999'");
                                    echo sqlsrv_fetch_array($r)['Summed'];
                                ?></td>
                                <td class='week Total' rel='<?php echo $Thursday;?>'><?php
                                    $r = sqlsrv_query($NEI,"SELECT Sum(OT) + Sum(DT) AS Summed FROM TicketD WHERE fWork='" . $Mechanic['fWork'] . "' and EDate >= '" . $Thursday . " 00:00:00.000' AND EDate <= '" . $Wednesday . " 23:59:59.999'");
                                    echo sqlsrv_fetch_array($r)['Summed'];
                                ?></td>
                                <td class='Expenses' style='font-weight:bold;'><?php
                                    $r = sqlsrv_query($NEI,"SELECT Sum(Zone)  AS Summed FROM TicketD WHERE fWork='" . $Mechanic['fWork'] . "' and EDate >= '" . $Thursday . " 00:00:00.000' AND EDate <= '" . $Wednesday . " 23:59:59.999'");
                                    echo sqlsrv_fetch_array($r)['Summed'];
                                ?></td>
                                <td class='Expenses'><?php
                                    $r = sqlsrv_query($NEI,"SELECT Sum(OtherE) AS Summed FROM TicketD WHERE fWork='" . $Mechanic['fWork'] . "' and EDate >= '" . $Thursday . " 00:00:00.000' AND EDate <= '" . $Wednesday . " 23:59:59.999'");
                                    echo sqlsrv_fetch_array($r)['Summed'];
                                ?></td>
                                <td class='Expenses' colspan='3'><?php
                                $r = sqlsrv_query($NEI,"SELECT TicketPic.PicData FROM TicketD LEFT JOIN TicketPic ON TicketD.ID = TicketPic.TicketID WHERE fWork='" . $Mechanic['fWork'] . "' and EDate >= '" . $Thursday . " 00:00:00.000' AND EDate <= '" . $Wednesday . " 23:59:59.999'");
                                if($r){while($row = sqlsrv_fetch_array($r)){
                                  if(isset($row['PicData']) && !is_null($row['PicData']) && strlen($row['PicData']) > 0){
                                    $row['Type'] = 'image/jpeg';
                                    ?><img width='50px' src="<?php print "data:" . $row['Type'] . ";base64, " . $row['PicData'];?>" /></div><?php
                                    $i++;
                                  }
                                }}
                                ?></td>
                            </tr>
                        <?php }}}?>
                        </tbody>
                    </table>
                </div> 
            </div>
        </div>
        <script>
            function refresh_get(){
                var Supervisors = $("select[name='Supervisors']").val();
                var Mechanics   = $("select[name='Mechanics']").val();
                var Week_Ending = $("input[name='Week_Ending']").val();
                document.location.href='review.php?Supervisors=' + Supervisors + '&Mechanics=' + Mechanics + "&Date=" + Week_Ending;
            } 
            $( "input[name='Week_Ending']" ).datepicker({
                beforeShowDay: function( date ){
                  return [ ( date.getDay( ) == 3 ), '' ]; },
                onSelect : function( dateText, inst ){ refresh_get( ); }
            });
            $('select#Mechanics').html($('#Mechanics option').sort(function (a, b) {return a.text == b.text ? 0 : a.text < b.text ? -1 : 1}));
            $('select#Supervisors').html($('#Supervisors option').sort(function (a, b) {return a.text == b.text ? 0 : a.text < b.text ? -1 : 1}));
        </script>
        <script>
            var days = document.getElementsByClassName( 'day' );
            for(let i = 0; i < days.length; i++) {
                days[ i ].addEventListener( 'click', function( event ){
                    var link = this;
                    $( '.ticket, .tickets, .tickets-header, .tickets-footer' ).each( function(){ $( this ).remove( ); })
                    $.ajax({
                        url  : 'cgi-bin/php/get/reviewDay.php',
                        data : {
                            User : $( link ).parent().attr( 'rel' ),
                            Date : link.getAttribute( 'rel' ),
                        },
                        method : 'GET',
                        success : function( response ){ 
                            $( link ).parent().after( response );
                        }
                    });
                });
            }
            var weeks = document.getElementsByClassName( 'week' );
            for(let i = 0; i < weeks.length; i++) {
                weeks[ i ].addEventListener( 'click', function( event ){
                    var link = this;
                    $( '.ticket, .tickets' ).each( function(){ $( this ).remove( ); })
                    $.ajax({
                        url  : 'cgi-bin/php/get/reviewWeek.php',
                        data : {
                            User : $( link ).parent().attr( 'rel' ),
                            Date : link.getAttribute( 'rel' ),
                        },
                        method : 'GET',
                        success : function( response ){ 
                            $( link ).parent().after( response );
                        }
                    });
                });
            }
        </script>
    </div>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=review.php';</script></head></html><?php }?>
