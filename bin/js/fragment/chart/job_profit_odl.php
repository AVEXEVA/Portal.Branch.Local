<script>
function gd(year, month, day) {return new Date(year, month - 1, day).getTime();}
var job_profit_data = [<?php 
	$data = array();
	$job_result = $database->query(null,"
		SELECT * 
		FROM   Job
		WHERE  Job.ID = ?
	;",array($_GET['ID']));
	$invoice_result = $database->query(null,"
		SELECT    Invoice.fDate as fDate,
				  Invoice.Amount as Amount
		FROM      nei.dbo.Invoice
		WHERE     Invoice.Job       =  ?
				  AND Invoice.fDate >= '2013-01-01 00:00:00.000'
		ORDER BY fDate ASC
	;",array($_GET['ID']));
	$job_item_result = $database->query($Paradox,"
		SELECT [JOBLABOR].[WEEK ENDING]    AS fDate,
			   [JOBLABOR].[TOTAL COST]     AS Amount
		FROM   Paradox.dbo.JOBLABOR
		WHERE  convert(date,[WEEK ENDING]) >= '2013-01-01 00:00:00.000'
			   AND [JOBLABOR].[JOB #]      =  ?

	;",array($_GET['ID']));
	$job_start_date = substr(sqlsrv_fetch_array($job_result)['fDate'],0,10);
	$job_end_date = date('Y-m-d');
	$dates_values = createDateRangeArray($job_start_date,$job_end_date);
	if(strtotime($job_start_date) <= strtotime("2013-01-01")){$job_start_date = "2013-01-01";}
	$dates_values = new DatePeriod(
		 new DateTime($job_start_date),
		 new DateInterval('P1D'),
		 new DateTime($job_end_date)
	);
	$dates = array();
	$totals = array();
	foreach($dates_values as $date){
		$dates[$date] = 0;
		$totals[$date] = 0;
	}
	if($job_item_result){
		while($array = sqlsrv_fetch_array($job_item_result)){
			$date = substr($array['fDate'],0,10);
			$dates[$date] = (is_numeric($dates[$date])) ? $dates[$date] - floatval($array['Amount']) : 0 - floatval($array['Amount']);
		}
	}
	ksort($dates);
	$job_item_result = $database->query(null,"
		SELECT JobI.fDate as fDate,
			   JobI.Amount as Amount
		FROM   JobI
		WHERE  JobI.Job='{$_GET['ID']}'
			   AND JobI.Type  =  1
			   AND JobI.Labor =  1
			   AND JobI.fDate <  '2017-03-30 00:00:00.000'
			   AND JobI.fDate >= '2013-01-01 00:00:00.000'
	;");
	if($job_item_result){
		while($array = sqlsrv_fetch_array($job_item_result)){
			$date = substr($array['fDate'],0,10);
			$dates[$date] = (is_numeric($dates[$date])) ? $dates[$date] + floatval($array['Amount']) : floatval($array['Amount']);
		}
	}
	ksort($dates);
	$job_item_result = $database->query(null,"
		SELECT   JobI.fDate as fDate,
				 JobI.Amount as Amount
		FROM     JobI
		WHERE    JobI.Job       =  ?
				 AND JobI.Type  =  1
				 AND JobI.fDate <  '2017-03-30 00:00:00.000'
				 AND JobI.fDate >= '2013-01-01 00:00:00.000'
		ORDER BY fDate ASC
	;",array($_GET['ID']));
	if($job_item_result){
		while($array = sqlsrv_fetch_array($job_item_result)){
			$date = substr($array['fDate'],0,10);
			$dates[$date] = (is_numeric($dates[$date])) ? $dates[$date] - floatval($array['Amount']) : 0 - floatval($array['Amount']);
		}
	}
	ksort($dates);
	$job_item_result = $database->query(null,"
		SELECT   JobI.fDate as fDate,
				 JobI.Amount as Amount
		FROM     JobI
		WHERE    JobI.Job       =  ?
				 AND JobI.Type  =  1
				 AND JobI.fDate >= '2017-03-30 00:00:00.000'
		ORDER BY fDate ASC
	;",array($_GET['ID']));
	if($job_item_result){
		while($array = sqlsrv_fetch_array($job_item_result)){
			$date = substr($array['fDate'],0,10);
			$dates[$date] = (is_numeric($dates[$date])) ? $dates[$date] - floatval($array['Amount']) : 0 - floatval($array['Amount']);
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
			$dates[$date] = (isset($dates[$date])) ? $dates[$date] + ($array['Amount'] * (1 - $Overhead)): $array['Amount'] * (1 - $Overhead);
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
	echo implode(",",$data);
?>]; 
var job_profit_dataset = [
	{
		label: "Profit",
		data: job_profit_data,
		color: "#FF0000",
		points: { fillColor: "#FF0000", show: true },
		lines: { show: true }
	}
];

var job_profit_options = {
    series: {shadowSize: 5},
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
    $.plot($("#flot-placeholder-profit"), job_profit_dataset, job_profit_options);
    $("#flot-placeholder-profit").UseTooltip();
});

function gd(year, month, day) {return new Date(year, month - 1, day).getTime();}

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