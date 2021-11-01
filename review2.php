<?php
session_start();
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
	   	|| !isset($My_Privileges['Time'])
	  		|| $My_Privileges['Time']['User_Privilege']  < 4
	  		|| $My_Privileges['Time']['Group_Privilege'] < 4
	  	    || $My_Privileges['Time']['Other_Privilege'] < 4){
				?><?php require('../404.html');?><?php }
    else {
		sqlsrv_query($NEI,"
			INSERT INTO Portal.dbo.Activity([User], [Date], [Page])
			VALUES(?,?,?)
		;",array($_SESSION['User'],date("Y-m-d H:i:s"), "review.php"));
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
		   LEFT JOIN nei.dbo.tblWork ON 'A' + convert(varchar(10),Emp.ID) + ',' = tblWork.Members
	WHERE  Emp.Field = 1
		   AND Emp.Status = 0
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
}?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php require(PROJECT_ROOT.'php/meta.php');?>
	<title>Nouveau Texas | Portal</title>
    <?php require(PROJECT_ROOT."css/index.php");?>
    <?php require(PROJECT_ROOT.'js/index.php');?>
    <!-- Bootstrap Core JavaScript -->
    <script src="../vendor/bootstrap/js/bootstrap.min.js"></script>

    <!-- Metis Menu Plugin JavaScript -->
    <script src="../vendor/metisMenu/metisMenu.js"></script>

    <!-- Custom Theme JavaScript -->
    <script src="../dist/js/sb-admin-2.js"></script>

    <?php require(PROJECT_ROOT.'js/datatables.php');?>
    <script src="cgi-bin/js/jquery.dataTables.yadcf.js"></script>

    <!--Moment JS Date Formatter-->
    <script src="../dist/js/moment.js"></script>

    <!-- Filters-->
    <script src="../dist/js/filters.js"></script>
    <script>
        function refresh_get(){
            var Supervisors = $("select[name='Supervisors']").val();
            var Mechanics = $("select[name='Mechanics']").val();
            var Week_Ending = $("input[name='Week_Ending']").val();
            document.location.href='review2.php?Supervisors=' + Supervisors + '&Mechanics=' + Mechanics + "&Date=" + Week_Ending;
        }
    </script>
    <script>
        $(document).ready(function(){
            //$("input[name='Week_Ending']").datepicker({onSelect:function(dateText, inst){refresh_get();}});
            $("#Mechanics").html($("#Mechanics option").sort(function (a, b) {return a.text == b.text ? 0 : a.text < b.text ? -1 : 1}));
            $("#Supervisors").html($("#Supervisors option").sort(function (a, b) {return a.text == b.text ? 0 : a.text < b.text ? -1 : 1}));
        });
        $(document).ready(function() {
            $('#Review_Table').DataTable({
                "columns":[
                    {"data":"Last_Name"},
                    {"data":"First_Name"},
                    {"data":"Thursday",className:"sum"},
                    {"data":"Friday",className:"sum"},
                    {"data":"Saturday",className:"sum"},
                    {"data":"Sunday",className:"sum"},
                    {"data":"Monday",className:"sum"},
                    {"data":"Tuesday",className:"sum"},
                    {"data":"Wensday",className:"sum"},
                    {"data":"Total",className:"sum"}
                ],
                responsive: true,
                "lengthMenu":[[-1,10,25,50,100],["All",10,25,50,100]],
                "initComplete":function(){finishLoadingPage();},
                "footerCallback": function(row, data, start, end, display) {
                    var api = this.api();

                    api.columns('.sum', { page: 'current' }).every(function () {
                        var sum = api
                            .cells( null, this.index(), { page: 'current'} )
                            .render('display')
                            .reduce(function (a, b) {
                                var x = parseFloat(a) || 0;
                                var y = parseFloat(b) || 0;
                                return x + y;
                            }, 0);
                        $(this.footer()).html(sum);
                    });
                }

            });
        });
    </script>
    <script>$(document).ready(function(){
        $("input[name='Week_Ending']").datepicker({
            beforeShowDay: function(date){
              return [(date.getDay() == 3), ""];},
            onSelect:function(dateText, inst){refresh_get();}
        });
    });</script>
</head>
<body onload=''>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require(PROJECT_ROOT.'php/element/navigation/index2.php');?>
        <?php require(PROJECT_ROOT.'php/element/loading.php');?>
        <div id="page-wrapper" class='content'>
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-primary">
                        <div class="panel-heading"><h3>Review Timesheets<div style='float:right'><button onClick='refresh_get();' style='color:black;'>Refresh</button></div></h3></div>
                        <div class="panel-heading" style='background-color:white;color:black;'>
                            <div class='row'>
                                <div class='col-xs-3'>
                                    <div class='row'>
                                        <div class='col-xs-6'>
                                            <label class='date' for="filter_start_date">Week Ending:</label>
                                        </div>
                                        <div class='col-xs-6'>
                                            <?php
                                            $Today = date('l');
                                            $Wednesday = date('m/d/Y');
                                            if($Today == 'Wednesday'){}
                                            elseif($Today == 'Thursday'){$Wednesday = date('m/d/Y', strtotime($Wednesday . ' +6 days'));}
                                            elseif($Today == 'Friday'){$Wednesday = date('m/d/Y', strtotime($Wednesday . ' +5 days'));}
                                            elseif($Today == 'Saturday'){$Wednesday = date('m/d/Y', strtotime($Wednesday . ' +4 days'));}
                                            elseif($Today == 'Sunday'){$Wednesday = date('m/d/Y', strtotime($Wednesday . ' +3 days'));}
                                            elseif($Today == 'Monday'){$Wednesday = date('m/d/Y', strtotime($Wednesday . ' +2 days'));}
                                            elseif($Today == 'Tuesday'){$Wednesday = date('m/d/Y', strtotime($Wednesday . ' +1 days'));}
                                            ?>
                                            <input class='start_date' size='10' name='Week_Ending' value='<?php echo strlen($_GET['Date']) > 1 ? $_GET['Date'] : $Wednesday;?>' />
                                        </div>
                                    </div>
                                    <div class='row'>
                                        <div class='col-xs-4'>
                                            <label class='tag' for='filter_today'>Quick Dates</label>
                                        </div>
                                        <div class='col-xs-8'>
                                            <button onClick="this_week();">This Week</button>
                                            <button onClick="last_week();">Last Week</button>
                                        </div>
                                    </div>
                                </div>
                                <div class='col-xs-3'>
                                    <div class='row'>
                                        <div class='col-xs-4' style='text-align:right;'>
                                            <label for='Supers' style='text-align:right;'>Supervisor:</label>
                                        </div>
                                        <div class='col-xs-8'>
                                            <select id='Supervisors' name='Supervisors' multiple='multiple' size='4' style='max-width:100%;'>
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
                                </div>
                            </div>
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <table width="100%" class="table table-striped table-bordered table-hover" id="Review_Table">
                                <thead>
                                    <tr>
                                        <th>Last Name</th>
                                        <th>First Name</th>
                                        <th>Thu</th>
                                        <th>Fri</th>
                                        <th>Sat</th>
                                        <th>Sun</th>
                                        <th>Mon</th>
                                        <th>Tue</th>
                                        <th>Wen</th>
                                        <th>Total</th>
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
                                    </tr>
                                </thead>
                                <style>
                                .hoverGray:hover {
                                    background-color:#dfdfdf !important;
                                }
                                </style>
                                <tbody><?php if(!isset($_GET['Preload'])){foreach($Mechanics as $Mechanic){
                                    $Mechanic['fFirst'] = ucfirst(strtolower($Mechanic['fFirst']));
                                    $Mechanic['Last'] = ucfirst(strtolower($Mechanic['Last']));
									$Mechanic['Super'] = ucfirst($Mechanic['Super']);
									$Selected_Supervisors = array_map('ucfirst',$Selected_Supervisors);
                                    if((in_array(ucfirst(strtolower($Mechanic['Super'])),$Selected_Supervisors) && !in_array($Mechanic['Super'],array('Office','Warehouse','firemen','Dean','Office','Firemen','',' ','  '))) || $_GET['Supervisors'] == '' || $_GET['Supervisors'] == 'All'){
                                        ?>
                                    <tr style='cursor:pointer;' class="odd gradeX hoverGray" onClick="document.location.href='time_sheet.php?Mechanic=<?php echo $Mechanic['ID'];?>'">
                                        <?php $Total = 0;?>
                                        <td class='Last_Name'><?php echo $Mechanic['Last'];?></td>
                                        <td class='First_Name'><?php echo $Mechanic['fFirst'];?></td>
                                        <td class='Thursday'><?php
                                            $Thursday = date('Y-m-d',strtotime($_GET['Date'] . ' -6 days'));
                                            $r = sqlsrv_query($NEI,"SELECT Attendance.[Start], Attendance.[End] FROM Portal.dbo.Attendance WHERE [User]='" . $Mechanic['ID'] . "' and [Start] >= '" . $Thursday . " 00:00:00.000' AND [Start] <= '" . $Thursday . " 23:59:59.999' AND [End] IS NOT NULL;");
                                            $array = sqlsrv_fetch_array($r);
                                            echo (strtotime($array['End']) - strtotime($array['Start'])) / (60 * 60);?></td>
                                        <td class='Friday'><?php
                                            $Friday = date('Y-m-d',strtotime($_GET['Date'] . ' -5 days'));
                                            $r = sqlsrv_query($NEI,"SELECT Attendance.[Start], Attendance.[End] FROM Portal.dbo.Attendance WHERE [User]='" . $Mechanic['ID'] . "' and [Start] >= '" . $Friday . " 00:00:00.000' AND [Start] <= '" . $Friday . " 23:59:59.999' AND [End] IS NOT NULL;");
                                            $array = sqlsrv_fetch_array($r);
                                            echo (strtotime($array['End']) - strtotime($array['Start'])) / (60 * 60);?></td>
                                        <td class='Saturday'><?php
                                            $Saturday = date('Y-m-d',strtotime($_GET['Date'] . ' -4 days'));
                                            $r = sqlsrv_query($NEI,"SELECT Attendance.[Start], Attendance.[End] FROM Portal.dbo.Attendance WHERE [User]='" . $Mechanic['ID'] . "' and [Start] >= '" . $Saturday . " 00:00:00.000' AND [Start] <= '" . $Saturday . " 23:59:59.999' AND [End] IS NOT NULL;");
                                            $array = sqlsrv_fetch_array($r);
                                            echo (strtotime($array['End']) - strtotime($array['Start'])) / (60 * 60);?></td>
                                        <td class='Sunday'><?php
                                            $Sunday = date('Y-m-d',strtotime($_GET['Date'] . ' -3 days'));
                                            $r = sqlsrv_query($NEI,"SELECT Attendance.[Start], Attendance.[End] FROM Portal.dbo.Attendance WHERE [User]='" . $Mechanic['ID'] . "' and [Start] >= '" . $Sunday . " 00:00:00.000' AND [Start] <= '" . $Sunday . " 23:59:59.999' AND [End] IS NOT NULL;");
                                            $array = sqlsrv_fetch_array($r);
                                            echo (strtotime($array['End']) - strtotime($array['Start'])) / (60 * 60);?></td>
                                        <td class='Monday'><?php
                                            $Monday = date('Y-m-d',strtotime($_GET['Date'] . ' -2 days'));
                                            $r = sqlsrv_query($NEI,"SELECT Attendance.[Start], Attendance.[End] FROM Portal.dbo.Attendance WHERE [User]='" . $Mechanic['ID'] . "' and [Start] >= '" . $Monday . " 00:00:00.000' AND [Start] <= '" . $Monday . " 23:59:59.999' AND [End] IS NOT NULL;");
                                            $array = sqlsrv_fetch_array($r);
                                            echo (strtotime($array['End']) - strtotime($array['Start'])) / (60 * 60);?></td>
                                        <td class='Tuesday'><?php
                                            $Tuesday = date('Y-m-d',strtotime($_GET['Date'] . ' -1 days'));
                                            $r = sqlsrv_query($NEI,"SELECT Attendance.[Start], Attendance.[End] FROM Portal.dbo.Attendance WHERE [User]='" . $Mechanic['ID'] . "' and [Start] >= '" . $Tuesday . " 00:00:00.000' AND [Start] <= '" . $Tuesday . " 23:59:59.999' AND [End] IS NOT NULL;");
                                            $array = sqlsrv_fetch_array($r);
                                            echo (strtotime($array['End']) - strtotime($array['Start'])) / (60 * 60);?></td>
                                        <td class='Wednesday'><?php
                                            $Wednesday = date('Y-m-d',strtotime($_GET['Date']));
                                            $r = sqlsrv_query($NEI,"SELECT Attendance.[Start], Attendance.[End] FROM Portal.dbo.Attendance WHERE [User]='" . $Mechanic['ID'] . "' and [Start] >= '" . $Wednesday . " 00:00:00.000' AND [Start] <= '" . $Wednesday . " 23:59:59.999' AND [End] IS NOT NULL;");
                                            $array = sqlsrv_fetch_array($r);
                                            echo (strtotime($array['End']) - strtotime($array['Start'])) / (60 * 60);?></td>
                                        <td class='Total'><?php
                                            $r = sqlsrv_query($NEI,"SELECT Attendance.[Start], Attendance.[End] FROM Portal.dbo.Attendance WHERE [User]='" . $Mechanic['ID'] . "' and [Start] >= '" . $Thursday . " 00:00:00.000' AND [Start] <= '" . $Wednesday . " 23:59:59.999' AND [End] IS NOT NULL;");
                                            $total = 0;
                                            if($r){while($row = sqlsrv_fetch_array($r)){
                                                $total += (strtotime($row['End']) - strtotime($row['Start'])) / (60 * 60);
                                            }}
                                            echo $total;
                                        ?></td>
                                    </tr>
                                <?php }}}?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th>Page Totals</th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
    </div>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=review.php';</script></head></html><?php }?>
