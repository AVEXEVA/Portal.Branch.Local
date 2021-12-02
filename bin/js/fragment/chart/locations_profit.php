<?php 
session_start( [ 'read_and_close' => true ] );
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = $database->query(null,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Elevator'){
        $r = $database->query(null,"SELECT * FROM nei.dbo.Emp WHERE ID = ?",array($_GET['User']));
        $My_User = sqlsrv_fetch_array($r);
        $Field = ($User['Field'] == 1 && $User['Title'] != "OFFICE") ? True : False;
        $r = $database->query($Portal,"
            SELECT Access, Owner, Group, Other
            FROM   Portal.dbo.Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
        $My_Privileges = array();
        while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access']] = $array2;}
        $Privileged = FALSE;
        if(isset($My_Privileges['Executive']) && $My_Privileges['Executive']['Owner'] >= 4 && $My_Privileges['Executive']['Group'] >= 4 && $My_Privileges['Executive']['Other'] >= 4){$Privileged = TRUE;}
    }
    if(!isset($array['ID']) || !$Privileged){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
$Random_Graph_ID = rand(1,9999999);
?><div class="flot-chart"><div class="flot-chart-content" id="flot-placeholder-<?php echo $Random_Graph_ID;?>-graph"></div></div><script><?php
?>
function gd(year, month, day) {return new Date(year, month - 1, day).getTime();}
var a_<?php echo $Random_Graph_ID;?>_data = [<?php 
		$data = array();
		$Locations = explode(",",$_GET['Locations']);
		$Check = true;
		if(count($Locations) > 0){
			foreach($Locations as $Location){
				if(is_numeric($Location)){continue;}
				else{$Check = false;break;}
			}
			if($Check){
				$tLocations = array();
				$aLocations = array();
				foreach($Locations as $Location){
					$tLocations[] = "Loc.Loc = '{$Location}'";
					$aLocations[] = "Loc.Loc='{$Location}'";}
				$tLocations = implode(" OR ",$tLocations);
				$aLocations = implode(" OR ",$aLocations);
				$job_result = $database->query(null,"
					SELECT Job.ID AS ID
					FROM   nei.dbo.Job 
						   LEFT JOIN nei.dbo.Loc ON Job.Loc = Loc.Loc
					WHERE  {$tLocations}
				;");
				if($job_result){
					$Jobs = array();
					$tJobs = array();
					$aJobs = array();
					while($Job = sqlsrv_fetch_array($job_result)){
						$tJobs[] = "Job.ID = '{$Job['ID']}'";
						$aJobs[] = "[JOBLABOR].[JOB #] = '{$Job['ID']}'";}
					$tJobs = implode(" OR ",$tJobs);
					$aJobs = implode(" OR ",$aJobs);
					$invoice_result = $database->query(null,"
						SELECT   Invoice.fDate  AS fDate,
							     Invoice.Amount AS Amount
						FROM     nei.dbo.Invoice 
						         LEFT JOIN nei.dbo.Job ON Invoice.Job = Job.ID
							     LEFT JOIN nei.dbo.Loc ON Job.Loc     = Loc.Loc
						WHERE    ({$tJobs})
							     AND Invoice.fDate >= '2013-01-01 00:00:00.000'
						ORDER BY fDate ASC
					;");
					$job_item_result = $database->query($Paradox,"
						SELECT [JOBLABOR].[WEEK ENDING] AS fDate,
							   [JOBLABOR].[TOTAL COST]  AS Amount
						FROM   nei.dbo.JOBLABOR
						WHERE  convert(date,[WEEK ENDING]) >= '2013-01-01 00:00:00.000'
							   AND ({$aJobs})

					;");
					$job_start_date = substr(sqlsrv_fetch_array($job_result)['fDate'],0,10);
					$job_end_date = date('Y-m-d');
					//$dates_values = createDateRangeArray($job_start_date,$job_end_date);
					if(strtotime($job_start_date) <= strtotime("2013-01-01")){$job_start_date = "2013-01-01";}
					$dates_values = new DatePeriod(
						 new DateTime($job_start_date),
						 new DateInterval('P1D'),
						 new DateTime($job_end_date)
					);
					$dates = array();
					$totals = array();
					if($job_item_result){
						while($array = sqlsrv_fetch_array($job_item_result)){
							$date = substr($array['fDate'],0,10);
							$dates[$date] = (isset($dates[$date]) && is_numeric($dates[$date])) ? $dates[$date] - floatval($array['Amount']) : 0 - floatval($array['Amount']);
						}
					}
					ksort($dates);
					$job_item_result = $database->query(null,"
						SELECT JobI.fDate  AS fDate,
							   JobI.Amount AS Amount
						FROM   nei.dbo.JobI 
						       LEFT JOIN nei.dbo.Job ON JobI.Job = Job.ID
						WHERE  ({$tJobs})
							   AND JobI.Type  =  1
							   AND JobI.Labor =  1
							   AND JobI.fDate <  '2017-03-30 00:00:00.000'
							   AND JobI.fDate >= '2013-01-01 00:00:00.000'
					;");
					if($job_item_result){
						while($array = sqlsrv_fetch_array($job_item_result)){
							$date = substr($array['fDate'],0,10);
							$dates[$date] = (isset($dates[$date]) && is_numeric($dates[$date])) ? $dates[$date] + floatval($array['Amount']) : floatval($array['Amount']);
						}
					}
					ksort($dates);
					$job_item_result = $database->query(null,"
						SELECT   JobI.fDate  AS fDate,
							     JobI.Amount AS Amount
						FROM     nei.dbo.JobI 
							     LEFT JOIN nei.dbo.Job ON JobI.Job = Job.ID
						WHERE    ({$tJobs})
							     AND JobI.Type  =  1
							     AND JobI.fDate <  '2017-03-30 00:00:00.000'
							     AND JobI.fDate >= '2013-01-01 00:00:00.000'
						ORDER BY fDate ASC
					;");
					if($job_item_result){
						while($array = sqlsrv_fetch_array($job_item_result)){
							$date = substr($array['fDate'],0,10);
							$dates[$date] = (isset($dates[$date]) && is_numeric($dates[$date])) ? $dates[$date] - floatval($array['Amount']) : 0 - floatval($array['Amount']);
						}
					}
					ksort($dates);
					$job_item_result = $database->query(null,"
						SELECT   JobI.fDate  AS fDate,
							     JobI.Amount AS Amount
						FROM     nei.dbo.JobI 
						         LEFT JOIN nei.dbo.Job ON JobI.Job = Job.ID
						WHERE    ({$tJobs})
							     AND JobI.Type  =  1
							     AND JobI.fDate >= '2017-03-30 00:00:00.000'
						ORDER BY fDate ASC
					;");
					if($job_item_result){
						while($array = sqlsrv_fetch_array($job_item_result)){
							$date = substr($array['fDate'],0,10);
							$dates[$date] = (isset($dates[$date]) && is_numeric($dates[$date])) ? $dates[$date] - floatval($array['Amount']) : 0 - floatval($array['Amount']);
						}
					}
					ksort($dates);
					if($invoice_result){
						while($array = sqlsrv_fetch_array($invoice_result)){
							$date = substr($array['fDate'],0,10);
							$Year = substr($array['fDate'],0,4);
							switch($Year){
								case "2012":$Overhead = .1608;break;
								case "2013":$Overhead = .1450;break;
								case "2014":$Overhead = .1770;break;
								case "2015":$Overhead = .1791;break;
								case "2016":$Overhead = .1520;break;
								case "2017":$Overhead = .1620;break;
							}
							$dates[$date] = (isset($dates[$date]) && isset($dates[$date])) ? $dates[$date] + ($array['Amount'] * (1 - $Overhead)): $array['Amount'] * (1 - $Overhead);
						}
					}
					$total = 0;
					ksort($dates);
					foreach($dates as $date=>$value){
						$total = $total + $value;
						$totals[$date] = $total;
					}
					ksort($totals);
					foreach($totals as $date=>$total){
						$Year = substr($date,0,4);
						$Month = substr($date,5,2);
						$Day = substr($date,8,2);
						$data[] = "[gd({$Year},{$Month},{$Day}),{$total}]";
					}
				}
				echo implode(",",$data);
			}
		}
?>]; 
var a_<?php echo $Random_Graph_ID;?>_dataset = [
	{
		label: "Profit",
		data: a_<?php echo $Random_Graph_ID;?>_data ,
		color: "#FF0000",
		points: { fillColor: "#FF0000", show: true },
		lines: { show: true }
	}
];

var a_<?php echo $Random_Graph_ID;?>_options = {
    series: {
        shadowSize: 5
    },
    xaxes: [{
        mode: "time",                
        tickFormatter: function (val, axis) {
            return new Date(val);
        },
        color: "black",
        position: "top",
        axisLabel: "Date",
        axisLabelUseCanvas: true,
        axisLabelFontSizePixels: 12,
        axisLabelFontFamily: 'Verdana, Arial',
        axisLabelPadding: 5
    },
    {
        mode: "time",
        timeformat: "%m/%d",
        tickSize: [3, "day"],
        color: "black",        
        axisLabel: "Date",
        axisLabelUseCanvas: true,
        axisLabelFontSizePixels: 12,
        axisLabelFontFamily: 'Verdana, Arial',
        axisLabelPadding: 10
    }],
    yaxis: {        
        color: "black",
        tickDecimals: 2,
        axisLabel: "Hours A Day",
        axisLabelUseCanvas: true,
        axisLabelFontSizePixels: 12,
        axisLabelFontFamily: 'Verdana, Arial',
        axisLabelPadding: 5
    },
    legend: {
        noColumns: 0,
        labelFormatter: function (label, series) {
            return "<font color=\"white\">" + label + "</font>";
        },
        backgroundColor: "#000",
        backgroundOpacity: 0.9,
        labelBoxBorderColor: "#000000",
        position: "nw"
    },
    grid: {
        hoverable: true,
        borderWidth: 3,
        mouseActiveRadius: 50,
        backgroundColor: { colors: ["#ffffff", "#EDF5FF"] },
        axisMargin: 20
    }
};

$(document).ready(function () {
    $.plot($("#flot-placeholder-<?php echo $Random_Graph_ID;?>-graph"), a_<?php echo $Random_Graph_ID;?>_dataset, a_<?php echo $Random_Graph_ID;?>_options);
    $("#flot-placeholder-<?php echo $Random_Graph_ID;?>-graph").UseTooltip();
});




function gd(year, month, day) {
    return new Date(year, month - 1, day).getTime();
}

var previousPoint = null, previousLabel = null;
var monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

$.fn.UseTooltip = function () {
    $(this).bind("plothover", function (event, pos, item) {
        if (item) {
            if ((previousLabel != item.series.label) || (previousPoint != item.dataIndex)) {
                previousPoint = item.dataIndex;
                previousLabel = item.series.label;
                $("#tooltip").remove();
                
                var x = item.datapoint[0];
                var y = item.datapoint[1];
                var date = new Date(x);
                var color = item.series.color;

                showTooltip(item.pageX, item.pageY, color,
                            "<strong>" + item.series.label + "</strong><br>"  +
                            (date.getMonth() + 1) + "/" + date.getDate() +
                            "/" + date.getFullYear() + " : <strong>" + y + "</strong>");
            }
        } else {
            $("#tooltip").remove();
            previousPoint = null;
        }
    });
};

function showTooltip(x, y, color, contents) {
    $('<div id="tooltip">' + contents + '</div>').css({
        position: 'absolute',
        display: 'none',
        top: y - 40,
        left: x - 120,
        border: '2px solid ' + color,
        padding: '3px',
        'font-size': '9px',
        'border-radius': '5px',
        'background-color': '#fff',
        'font-family': 'Verdana, Arial, Helvetica, Tahoma, sans-serif',
        opacity: 0.9
    }).appendTo("body").fadeIn(200);
}
</script>
<?php 
$Locations = explode(",",$_GET['Locations']);
$Check = true;
if(count($Locations) > 0){
    foreach($Locations as $Location){
        if(is_numeric($Location)){continue;}
        else{$Check = false;break;}
    }
    if($Check){
        $tLocations = array();
        foreach($Locations as $Location){$tLocations[] = "Loc.Loc = '{$Location}'";}
        $tLocations = implode(" OR ",$tLocations);
        $job_result = $database->query(null,"
            SELECT Job.ID
            FROM Job LEFT JOIN nei.dbo.Loc ON Job.Loc = Loc.Loc 
            WHERE ({$tLocations})
        ;");
        if($job_result){
            $tJobs = array();
            $aJobs = array();
            while($Job = sqlsrv_fetch_array($job_result)){$tJobs[] = "Job.ID = '{$Job['ID']}'";$aJobs[] = "[JOBLABOR].[JOB #] = '{$Job['ID']}'";}
            $tJobs = implode(" OR ",$tJobs);
            $aJobs = implode(" OR ",$aJobs);
?>
<table id="Table_Jobs_Profit" class="display" cellspacing='0' width='100%'>
    <thead style='border:1px solid black;'>
        <th></th>
        <th>2012</th>
        <th>2013</th>
        <th>2014</th>
        <th>2015</th>
        <th>2016</th>
        <th>2017</th>
        <th>3 Year</th>
        <th>5 Year</th>
    </thead>
    <tbody style='border:1px solid black;'>
        <tr>
            <td><b>Revenue</b></td>
            <td><?php 
                $r = $database->query(null,"
                    SELECT Sum(Invoice.Amount) AS Total_Revenue_2012
                    FROM nei.dbo.Invoice LEFT JOIN nei.dbo.Job ON Invoice.Job = Job.ID
                    WHERE Invoice.fDate >= '2012-01-01 00:00:00.000' AND Invoice.fDate < '2013-01-01 00:00:00.000' AND ({$tJobs})
                ;");
                $Total_Revenue_2012 = $r ? sqlsrv_fetch_array($r)['Total_Revenue_2012'] : 0;
                echo money_format('%(n',$Total_Revenue_2012);
            ?></td>
            <td><?php 
                $r = $database->query(null,"
                    SELECT Sum(Invoice.Amount) AS Total_Revenue_2013
                    FROM nei.dbo.Invoice LEFT JOIN nei.dbo.Job ON Invoice.Job = Job.ID
                    WHERE Invoice.fDate >= '2013-01-01 00:00:00.000' AND Invoice.fDate < '2014-01-01 00:00:00.000' AND ({$tJobs})
                ;");
                $Total_Revenue_2013 = $r ? sqlsrv_fetch_array($r)['Total_Revenue_2013'] : 0;
                echo money_format('%(n',$Total_Revenue_2013);
            ?></td>
            <td><?php 
                $r = $database->query(null,"
                    SELECT Sum(Invoice.Amount) AS Total_Revenue_2014
                    FROM nei.dbo.Invoice LEFT JOIN nei.dbo.Job ON Invoice.Job = Job.ID
                    WHERE Invoice.fDate >= '2014-01-01 00:00:00.000' AND Invoice.fDate < '2015-01-01 00:00:00.000' AND ({$tJobs})
                ;");
                $Total_Revenue_2014 = $r ? sqlsrv_fetch_array($r)['Total_Revenue_2014'] : 0;
                echo money_format('%(n',$Total_Revenue_2014);
            ?></td>
            <td><?php 
                $r = $database->query(null,"
                    SELECT Sum(Invoice.Amount) AS Total_Revenue_2015
                    FROM nei.dbo.Invoice LEFT JOIN nei.dbo.Job ON Invoice.Job = Job.ID
                    WHERE Invoice.fDate >= '2015-01-01 00:00:00.000' AND Invoice.fDate < '2016-01-01 00:00:00.000' AND ({$tJobs})
                ;");
                $Total_Revenue_2015 = $r ? sqlsrv_fetch_array($r)['Total_Revenue_2015'] : 0;
                echo money_format('%(n',$Total_Revenue_2015);
            ?></td>
            <td><?php 
                $r = $database->query(null,"
                    SELECT Sum(Invoice.Amount) AS Total_Revenue_2016
                    FROM nei.dbo.Invoice LEFT JOIN nei.dbo.Job ON Invoice.Job = Job.ID
                    WHERE Invoice.fDate >= '2016-01-01 00:00:00.000' AND Invoice.fDate < '2017-01-01 00:00:00.000' AND ({$tJobs})
                ;");
                $Total_Revenue_2016 = $r ? sqlsrv_fetch_array($r)['Total_Revenue_2016'] : 0;
                echo money_format('%(n',$Total_Revenue_2016);
            ?></td>
            <td><?php 
                $r = $database->query(null,"
                    SELECT Sum(Invoice.Amount) AS Total_Revenue_2017
                    FROM nei.dbo.Invoice LEFT JOIN nei.dbo.Job ON Invoice.Job = Job.ID
                    WHERE Invoice.fDate >= '2017-01-01 00:00:00.000' AND Invoice.fDate < '2018-01-01 00:00:00.000' AND ({$tJobs})
                ;");
                $Total_Revenue_2017 = $r ? sqlsrv_fetch_array($r)['Total_Revenue_2017'] : 0;
                echo money_format('%(n',$Total_Revenue_2017);
            ?></td>
            <td><?php 
                $r = $database->query(null,"
                    SELECT Sum(Invoice.Amount) AS Total_Revenue_3_Year
                    FROM nei.dbo.Invoice LEFT JOIN nei.dbo.Job ON Invoice.Job = Job.ID
                    WHERE Invoice.fDate >= '2015-01-01 00:00:00.000' AND Invoice.fDate < '2018-01-01 00:00:00.000' AND ({$tJobs})
                ;");
                $Total_Revenue_3_Year = $r ? sqlsrv_fetch_array($r)['Total_Revenue_3_Year'] : 0;
                echo money_format('%(n',$Total_Revenue_3_Year);
            ?></td>
            <td><?php 
                $r = $database->query(null,"
                    SELECT Sum(Invoice.Amount) AS Total_Revenue_5_Year
                    FROM nei.dbo.Invoice LEFT JOIN nei.dbo.Job ON Invoice.Job = Job.ID
                    WHERE Invoice.fDate >= '2013-01-01 00:00:00.000' AND Invoice.fDate < '2018-01-01 00:00:00.000' AND ({$tJobs})
                ;");
                $Total_Revenue_5_Year = $r ? sqlsrv_fetch_array($r)['Total_Revenue_5_Year'] : 0;
                echo money_format('%(n',$Total_Revenue_5_Year);
            ?></td>
        </tr>
        <tr>
            <td><b>Labor</b></td>
            <td><?php 
                $r = $database->query(null,"
                    SELECT 
                        Sum(JobI.Amount) AS Total_Labor_2012
                    FROM 
                        Loc 
                        LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                        LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                    WHERE 
                        ({$tJobs})
                        AND JobI.Type='1' AND JobI.Labor = '1'
                        AND JobI.fDate >= '2012-01-01 00:00:00.000' AND JobI.fDate < '2013-01-01 00:00:00.000'
                ;");
                $Temp_Labor_2012 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2012'] : 0;
                $r = $database->query($Paradox,"
                    SELECT 
                        SUM([JOBLABOR].[TOTAL COST])     AS Total_Labor_2012
                    FROM 
                        JOBLABOR
                    WHERE 
                        ({$aJobs})
                        AND convert(date,[WEEK ENDING]) >= '2012-01-01 00:00:00.000'
                        AND convert(date,[WEEK ENDING]) < '2013-01-01 00:00:00.000'
                ;");
                $Total_Labor_2012 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2012'] : 0;
                echo money_format('%(n',$Total_Labor_2012);
            ?></td>
            <td><?php 
                $r = $database->query(null,"
                    SELECT 
                        Sum(JobI.Amount) AS Total_Labor_2013
                    FROM 
                        Loc 
                        LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                        LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                    WHERE 
                        ({$tJobs})
                        AND JobI.Type='1' AND JobI.Labor = '1'
                        AND JobI.fDate >= '2013-01-01 00:00:00.000' AND JobI.fDate < '2014-01-01 00:00:00.000'
                ;");
                $Temp_Labor_2013 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2013'] : 0;
                $r = $database->query($Paradox,"
                    SELECT 
                        SUM([JOBLABOR].[TOTAL COST])     AS Total_Labor_2013
                    FROM 
                        JOBLABOR
                    WHERE 
                        ({$aJobs})
                        AND convert(date,[WEEK ENDING]) >= '2013-01-01 00:00:00.000'
                        AND convert(date,[WEEK ENDING]) < '2014-01-01 00:00:00.000'
                ;");
                $Total_Labor_2013 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2013'] : 0;
                echo money_format('%(n',$Total_Labor_2013);
            ?></td>
            <td><?php 
                $r = $database->query(null,"
                    SELECT 
                        Sum(JobI.Amount) AS Total_Labor_2014
                    FROM 
                        Loc 
                        LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                        LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                    WHERE 
                        ({$tJobs})
                        AND JobI.Type='1' AND JobI.Labor = '1'
                        AND JobI.fDate >= '2014-01-01 00:00:00.000' AND JobI.fDate < '2015-01-01 00:00:00.000'
                ;");
                $Temp_Labor_2014 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2014'] : 0;
                $r = $database->query($Paradox,"
                    SELECT 
                        SUM([JOBLABOR].[TOTAL COST])     AS Total_Labor_2014
                    FROM 
                        JOBLABOR
                    WHERE 
                        ({$aJobs})
                        AND convert(date,[WEEK ENDING]) >= '2014-01-01 00:00:00.000'
                        AND convert(date,[WEEK ENDING]) < '2015-01-01 00:00:00.000'
                ;");
                $Total_Labor_2014 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2014'] : 0;
                echo money_format('%(n',$Total_Labor_2014);
            ?></td>
            <td><?php 
                $r = $database->query(null,"
                    SELECT 
                        Sum(JobI.Amount) AS Total_Labor_2015
                    FROM 
                        Loc 
                        LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                        LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                    WHERE 
                        ({$tJobs})
                        AND JobI.Type='1' AND JobI.Labor = '1'
                        AND JobI.fDate >= '2015-01-01 00:00:00.000' AND JobI.fDate < '2016-01-01 00:00:00.000'
                ;");
                $Temp_Labor_2015 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2015'] : 0;
                $r = $database->query($Paradox,"
                    SELECT 
                        SUM([JOBLABOR].[TOTAL COST])     AS Total_Labor_2015
                    FROM 
                        JOBLABOR
                    WHERE 
                        ({$aJobs})
                        AND convert(date,[WEEK ENDING]) >= '2015-01-01 00:00:00.000'
                        AND convert(date,[WEEK ENDING]) < '2016-01-01 00:00:00.000'
                ;");
                $Total_Labor_2015 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2015'] : 0;
                echo money_format('%(n',$Total_Labor_2015);
            ?></td>
            <td><?php 
                $r = $database->query(null,"
                    SELECT 
                        Sum(JobI.Amount) AS Total_Labor_2016
                    FROM 
                        Loc 
                        LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                        LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                    WHERE 
                        ({$tJobs})
                        AND JobI.Type='1' AND JobI.Labor = '1'
                        AND JobI.fDate >= '2016-01-01 00:00:00.000' AND JobI.fDate < '2017-01-01 00:00:00.000'
                ;");
                $Temp_Labor_2016 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2016'] : 0;
                $r = $database->query($Paradox,"
                    SELECT 
                        SUM([JOBLABOR].[TOTAL COST])     AS Total_Labor_2016
                    FROM 
                        JOBLABOR
                    WHERE 
                        ({$aJobs})
                        AND convert(date,[WEEK ENDING]) >= '2016-01-01 00:00:00.000'
                        AND convert(date,[WEEK ENDING]) < '2017-01-01 00:00:00.000'
                ;");
                $Total_Labor_2016 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2016'] : 0;
                echo money_format('%(n',$Total_Labor_2016);
            ?></td>
            <td><?php 
                $r = $database->query(null,"
                    SELECT 
                        Sum(JobI.Amount) AS Total_Labor_2017
                    FROM 
                        Loc 
                        LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                        LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                    WHERE 
                        ({$tJobs})
                        AND JobI.Type='1' AND JobI.Labor = '1'
                        AND JobI.fDate >= '2017-01-01 00:00:00.000' AND JobI.fDate < '2018-01-01 00:00:00.000'
                ;");
                
                $Temp_Labor_2017 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2017'] : 0;
                $r = $database->query($Paradox,"
                    SELECT 
                        SUM([JOBLABOR].[TOTAL COST])     AS Total_Labor_2017
                    FROM 
                        JOBLABOR
                    WHERE 
                        ({$aJobs})
                        AND convert(date,[WEEK ENDING]) >= '2017-01-01 00:00:00.000'
                        AND convert(date,[WEEK ENDING]) < '2017-03-30 00:00:00.000'
                ;");
                $Total_Labor_2017 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2017'] : 0;
                $r = $database->query(null,"
                    SELECT 
                        Sum(JobI.Amount) AS Total_Labor_2017
                    FROM 
                        Loc 
                        LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                        LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                    WHERE 
                        ({$tJobs})
                        AND JobI.Type='1' AND JobI.Labor = '1'
                        AND JobI.fDate >= '2017-03-30 00:00:00.000' AND JobI.fDate < '2018-01-01 00:00:00.000'
                ");
                $Total_Labor_2017 = $r ? $Total_Labor_2017 + sqlsrv_fetch_array($r)['Total_Labor_2017'] : $Total_Labor_2017;
                echo money_format('%(n',$Total_Labor_2017);
            ?></td>
            <td><?php 
                $r = $database->query(null,"
                    SELECT 
                        Sum(JobI.Amount) AS Total_Labor_3_Year
                    FROM 
                        Loc 
                        LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                        LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                    WHERE 
                        ({$tJobs})
                        AND JobI.Type='1' AND JobI.Labor = '1'
                        AND JobI.fDate >= '2015-01-01 00:00:00.000' AND JobI.fDate < '2018-01-01 00:00:00.000'
                ;");
                $Temp_Labor_3_Year = $r ? sqlsrv_fetch_array($r)['Total_Labor_3_Year'] : 0;
                $r = $database->query($Paradox,"
                    SELECT 
                        SUM([JOBLABOR].[TOTAL COST])     AS Total_Labor_3_Year
                    FROM 
                        JOBLABOR
                    WHERE 
                        ({$aJobs})
                        AND convert(date,[WEEK ENDING]) >= '2015-01-01 00:00:00.000'
                        AND convert(date,[WEEK ENDING]) < '2017-03-30 00:00:00.000'
                ;");
                $Total_Labor_3_Year = $r ? sqlsrv_fetch_array($r)['Total_Labor_3_Year'] : 0;
                $r = $database->query(null,"
                    SELECT 
                        Sum(JobI.Amount) AS Total_Labor_3_Year
                    FROM 
                        Loc 
                        LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                        LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                    WHERE 
                        ({$tJobs})
                        AND JobI.Type='1' AND JobI.Labor = '1'
                        AND JobI.fDate >= '2015-03-30 00:00:00.000' AND JobI.fDate < '2018-01-01 00:00:00.000'
                ");
                $Total_Labor_3_Year = $r ? $Total_Labor_3_Year + sqlsrv_fetch_array($r)['Total_Labor_3_Year'] : $Total_Labor_3_Year;
                echo money_format('%(n',$Total_Labor_3_Year);
            ?></td>
            <td><?php 
                $r = $database->query(null,"
                    SELECT 
                        Sum(JobI.Amount) AS Total_Labor_5_Year
                    FROM 
                        Loc 
                        LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                        LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                    WHERE 
                        ({$tJobs})
                        AND JobI.Type='1' AND JobI.Labor = '1'
                        AND JobI.fDate >= '2013-01-01 00:00:00.000' AND JobI.fDate < '2018-01-01 00:00:00.000'
                ;");
                $Temp_Labor_5_Year = $r ? sqlsrv_fetch_array($r)['Total_Labor_5_Year'] : 0;
                $r = $database->query($Paradox,"
                    SELECT 
                        SUM([JOBLABOR].[TOTAL COST])     AS Total_Labor_5_Year
                    FROM 
                        JOBLABOR
                    WHERE 
                        ({$aJobs})
                        AND convert(date,[WEEK ENDING]) >= '2013-01-01 00:00:00.000'
                        AND convert(date,[WEEK ENDING]) < '2017-03-30 00:00:00.000'
                ;");
                $Total_Labor_5_Year = $r ? sqlsrv_fetch_array($r)['Total_Labor_5_Year'] : 0;
                $r = $database->query(null,"
                    SELECT 
                        Sum(JobI.Amount) AS Total_Labor_5_Year
                    FROM 
                        Loc 
                        LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                        LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                    WHERE 
                        ({$tJobs})
                        AND JobI.Type='1' AND JobI.Labor = '1'
                        AND JobI.fDate >= '2017-03-30 00:00:00.000' AND JobI.fDate < '2018-01-01 00:00:00.000'
                ");
                $Total_Labor_5_Year = $r ? $Total_Labor_5_Year + sqlsrv_fetch_array($r)['Total_Labor_5_Year'] : $Total_Labor_5_Year;
                echo money_format('%(n',$Total_Labor_5_Year);
            ?></td>
        </tr>
        <tr style='border-bottom:1px solid black;'>
            <td><b>Materials</b></td>
            <td><?php 
                $r = $database->query(null,"
                    SELECT 
                        Sum(JobI.Amount) AS Total_Materials_2012
                    FROM 
                        Loc 
                        LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                        LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                    WHERE 
                        ({$tJobs})
                        AND JobI.Type='1'
                        AND JobI.fDate >= '2012-01-01 00:00:00.000' AND JobI.fDate < '2013-01-01 00:00:00.000'
                ;");
                $Total_Materials_2012 = $r ? sqlsrv_fetch_array($r)['Total_Materials_2012'] - $Temp_Labor_2012 : 0;
                echo money_format('%(n',$Total_Materials_2012);
            ?></td>
            <td><?php 
                $r = $database->query(null,"
                    SELECT 
                        Sum(JobI.Amount) AS Total_Materials_2013
                    FROM 
                        Loc 
                        LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                        LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                    WHERE 
                        ({$tJobs})
                        AND JobI.Type='1'
                        AND JobI.fDate >= '2013-01-01 00:00:00.000' AND JobI.fDate < '2014-01-01 00:00:00.000'
                ;");
                $Total_Materials_2013 = $r ? sqlsrv_fetch_array($r)['Total_Materials_2013'] - $Temp_Labor_2013 : 0;
                echo money_format('%(n',$Total_Materials_2013);
            ?></td>
            <td><?php 
                $r = $database->query(null,"
                    SELECT 
                        Sum(JobI.Amount) AS Total_Materials_2014
                    FROM 
                        Loc 
                        LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                        LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                    WHERE 
                        ({$tJobs})
                        AND JobI.Type='1'
                        AND JobI.fDate >= '2014-01-01 00:00:00.000' AND JobI.fDate < '2015-01-01 00:00:00.000'
                ;");
                $Total_Materials_2014 = $r ? sqlsrv_fetch_array($r)['Total_Materials_2014'] - $Temp_Labor_2014 : 0;
                echo money_format('%(n',$Total_Materials_2014);
            ?></td>
            <td><?php 
                $r = $database->query(null,"
                    SELECT 
                        Sum(JobI.Amount) AS Total_Materials_2015
                    FROM 
                        Loc 
                        LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                        LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                    WHERE 
                        ({$tJobs})
                        AND JobI.Type='1'
                        AND JobI.fDate >= '2015-01-01 00:00:00.000' AND JobI.fDate < '2016-01-01 00:00:00.000'
                ;");
                $Total_Materials_2015 = $r ? sqlsrv_fetch_array($r)['Total_Materials_2015'] - $Temp_Labor_2015 : 0;
                echo money_format('%(n',$Total_Materials_2015);
            ?></td>
            <td><?php 
                $r = $database->query(null,"
                    SELECT 
                        Sum(JobI.Amount) AS Total_Materials_2016
                    FROM 
                        Loc 
                        LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                        LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                    WHERE 
                        ({$tJobs})
                        AND JobI.Type='1'
                        AND JobI.fDate >= '2016-01-01 00:00:00.000' AND JobI.fDate < '2017-01-01 00:00:00.000'
                ;");
                $Total_Materials_2016 = $r ? sqlsrv_fetch_array($r)['Total_Materials_2016'] - $Temp_Labor_2016 : 0;
                echo money_format('%(n',$Total_Materials_2016);
            ?></td>
            <td><?php 
                $r = $database->query(null,"
                    SELECT 
                        Sum(JobI.Amount) AS Total_Materials_2017
                    FROM 
                        Loc 
                        LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                        LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                    WHERE 
                        ({$tJobs})
                        AND JobI.Type='1'
                        AND JobI.fDate >= '2017-01-01 00:00:00.000' AND JobI.fDate < '2018-01-01 00:00:00.000'
                ;");
                $Total_Materials_2017 = $r ? sqlsrv_fetch_array($r)['Total_Materials_2017'] - $Temp_Labor_2017 : 0;
                echo money_format('%(n',$Total_Materials_2017);
            ?></td>
            <td><?php 
                $r = $database->query(null,"
                    SELECT 
                        Sum(JobI.Amount) AS Total_Materials_3_Year
                    FROM 
                        Loc 
                        LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                        LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                    WHERE 
                        ({$tJobs})
                        AND JobI.Type='1'
                        AND JobI.fDate >= '2015-01-01 00:00:00.000' AND JobI.fDate < '2018-01-01 00:00:00.000'
                ;");
                $Total_Materials_3_Year = $r ? sqlsrv_fetch_array($r)['Total_Materials_3_Year'] - $Temp_Labor_3_Year : 0;
                echo money_format('%(n',$Total_Materials_3_Year);
            ?></td>
            <td><?php 
                $r = $database->query(null,"
                    SELECT 
                        Sum(JobI.Amount) AS Total_Materials_5_Year
                    FROM 
                        Loc 
                        LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                        LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                    WHERE 
                        ({$tJobs})
                        AND JobI.Type='1'
                        AND JobI.fDate >= '2013-01-01 00:00:00.000' AND JobI.fDate < '2018-01-01 00:00:00.000'
                ;");
                $Total_Materials_5_Year = $r ? sqlsrv_fetch_array($r)['Total_Materials_5_Year'] - $Temp_Labor_5_Year : 0;
                echo money_format('%(n',$Total_Materials_5_Year);
            ?></td>
        </tr>
        <tr>
            <td><b>Net Income</b></td>
            <td><?php
                $Total_Net_Income_2012 = $Total_Revenue_2012 - ($Total_Labor_2012 + $Total_Materials_2012);
                echo substr(money_format('%(n',$Total_Net_Income_2012),0,99);
            ?></td>
            <td><?php
                $Total_Net_Income_2013 = $Total_Revenue_2013 - ($Total_Labor_2013 + $Total_Materials_2013);
                echo substr(money_format('%(n',$Total_Net_Income_2013),0,99);
            ?></td>
            <td><?php
                $Total_Net_Income_2014 = $Total_Revenue_2014 - ($Total_Labor_2014 + $Total_Materials_2014);
                echo substr(money_format('%(n',$Total_Net_Income_2014),0,99);
            ?></td>
            <td><?php
                $Total_Net_Income_2015 = $Total_Revenue_2015 - ($Total_Labor_2015 + $Total_Materials_2015);
                echo substr(money_format('%(n',$Total_Net_Income_2015),0,99);
            ?></td>
            <td><?php
                $Total_Net_Income_2016 = $Total_Revenue_2016 - ($Total_Labor_2016 + $Total_Materials_2016);
                echo substr(money_format('%(n',$Total_Net_Income_2016),0,99);
            ?></td>
            <td><?php
                $Total_Net_Income_2017 = $Total_Revenue_2017 - ($Total_Labor_2017 + $Total_Materials_2017);
                echo substr(money_format('%(n',$Total_Net_Income_2017),0,99);
            ?></td>
            <td><?php
                $Total_Net_Income_3_Year = $Total_Revenue_3_Year - ($Total_Labor_3_Year + $Total_Materials_3_Year);
                echo substr(money_format('%(n',$Total_Net_Income_3_Year),0,99);
            ?></td>
            <td><?php
                $Total_Net_Income_5_Year = $Total_Revenue_5_Year - ($Total_Labor_5_Year + $Total_Materials_5_Year);
                echo substr(money_format('%(n',$Total_Net_Income_5_Year),0,99);
            ?></td>
        </tr>
        <tr>
            <td><b>Overhead %</b></td>
            <td>16.08%</td>
            <td>14.50%</td>
            <td>17.70%</td>
            <td>17.91%</td>
            <td>15.20%</td>
            <td>16.20%</td>
            <td>Cumulative</td>
            <td>Cumulative</td>
        </tr>
        <tr style='border-bottom:1px solid black;'>
            <td><b>Overhead Cost</b></td>
            <td><?php 
                $Overhead_Cost_2012 = $Total_Revenue_2012 * .1608;
                echo money_format('%(n',$Overhead_Cost_2012);
            ?></td>
            <td><?php 
                $Overhead_Cost_2013 = $Total_Revenue_2013 * .1450;
                echo money_format('%(n',$Overhead_Cost_2013);
            ?></td>
            <td><?php 
                $Overhead_Cost_2014 = $Total_Revenue_2014 * .1770;
                echo money_format('%(n',$Overhead_Cost_2014);
            ?></td>
            <td><?php 
                $Overhead_Cost_2015 = $Total_Revenue_2015 * .1791;
                echo money_format('%(n',$Overhead_Cost_2015);
            ?></td>
            <td><?php 
                $Overhead_Cost_2016 = $Total_Revenue_2016 * .1520;
                echo money_format('%(n',$Overhead_Cost_2016);
            ?></td>
            <td><?php 
                $Overhead_Cost_2017 = $Total_Revenue_2017 * .1620;
                echo money_format('%(n',$Overhead_Cost_2017);
            ?></td>
            <td><?php 
                $Overhead_Cost_3_Year = $Overhead_Cost_2015 + $Overhead_Cost_2016 + $Overhead_Cost_2017;
                echo money_format('%(n',$Overhead_Cost_3_Year);
            ?></td>
            <td><?php 
                $Overhead_Cost_5_Year = $Overhead_Cost_2013 + $Overhead_Cost_2014 + $Overhead_Cost_3_Year;
                echo money_format('%(n',$Overhead_Cost_5_Year);
            ?></td>
        </tr>
        <tr>
            <td><b>Profit</b></td>
            <td><?php 
                $Total_Profit_2012 = $Total_Revenue_2012 - ($Total_Labor_2012 + $Total_Materials_2012 + $Overhead_Cost_2012);
                echo money_format('%(n',$Total_Profit_2012);
            ?></td>
            <td><?php 
                $Total_Profit_2013 = $Total_Revenue_2013 - ($Total_Labor_2013 + $Total_Materials_2013 + $Overhead_Cost_2013);
                echo money_format('%(n',$Total_Profit_2013);
            ?></td>
            <td><?php 
                $Total_Profit_2014 = $Total_Revenue_2014 - ($Total_Labor_2014 + $Total_Materials_2014 + $Overhead_Cost_2014);
                echo money_format('%(n',$Total_Profit_2014);
            ?></td>
            <td><?php 
                $Total_Profit_2015 = $Total_Revenue_2015 - ($Total_Labor_2015 + $Total_Materials_2015 + $Overhead_Cost_2015);
                echo money_format('%(n',$Total_Profit_2015);
            ?></td>
            <td><?php 
                $Total_Profit_2016 = $Total_Revenue_2016 - ($Total_Labor_2016 + $Total_Materials_2016 + $Overhead_Cost_2016);
                echo money_format('%(n',$Total_Profit_2016);
            ?></td>
            <td><?php 
                $Total_Profit_2017 = $Total_Revenue_2017 - ($Total_Labor_2017 + $Total_Materials_2017 + $Overhead_Cost_2017);
                echo money_format('%(n',$Total_Profit_2017);
            ?></td>
            <td><?php 
                $Total_Profit_3_Year = $Total_Revenue_3_Year - ($Total_Labor_3_Year + $Total_Materials_3_Year + $Overhead_Cost_3_Year);
                echo money_format('%(n',$Total_Profit_3_Year);
            ?></td>
            <td><?php 
                $Total_Profit_5_Year = $Total_Revenue_5_Year - ($Total_Labor_5_Year + $Total_Materials_5_Year + $Overhead_Cost_5_Year);
                echo money_format('%(n',$Total_Profit_5_Year);
            ?></td>
        </tr>
    </tbody>
</table>
<br />

<?php }}}
     }
}?>