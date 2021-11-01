<script>
function gd(year, month, day) {return new Date(year, month - 1, day).getTime();}
var customer_revenue_data = [
	<?php 
	$data = array();
	$job_result = sqlsrv_query($NEI,"
		SELECT Job.ID AS ID
		FROM   Job 
		WHERE  Job.Owner = ?
	;",array($_GET['ID']));
	if($job_result){
		$Jobs = array();
		$dates = array();
		$totals = array();
		while($array = sqlsrv_fetch_array($job_result)){$Jobs[] = "[JOBLABOR].[JOB #]='{$array['ID']}'";}
		$Jobs = implode(" OR ",$Jobs);
		$invoice_result = sqlsrv_query($NEI,"
			SELECT Invoice.Amount AS Amount,
				   Invoice.fDate  AS fDate
			FROM   nei.dbo.Invoice
				   LEFT JOIN nei.dbo.Loc ON Invoice.Loc = Loc.Loc
			WHERE  Invoice.Job = ?
				   AND Invoice.fDate >= '2013-01-01 00:00:00.000'
		;",array($_GET['ID']));
		if($invoice_result){
			while($array = sqlsrv_fetch_array($invoice_result)){
				$date = substr($array['fDate'],0,10);
				$Year = substr($array['fDate'],0,4);
				switch($Year){
					case "2012":$Overhead = .0;break;
					case "2013":$Overhead = .0;break;
					case "2014":$Overhead = .0;break;
					case "2015":$Overhead = .0;break;
					case "2016":$Overhead = .0;break;
					case "2017":$Overhead = .0;break;
				}
				$dates[$date] = (isset($dates[$date])) ? $dates[$date] + ($array['Amount'] * (1 - $Overhead)): $array['Amount'] * (1 - $Overhead);
			}
		}
		ksort($dates);
		$total = 0;
		foreach($dates as $date=>$value){
			$total = $total + $value;
			$totals[$date] = $total;
		}
		ksort($totals);
		foreach($totals as $date=>$total){
			$Year = substr($date,0,4);
			$Month = substr($date,5,2);
			$Day = substr($date,8,2);
			if($date == ""){continue;}
			if($Year == '' || is_null($Year)){continue;}  
			$data[] = "[gd({$Year},{$Month},{$Day}),{$total}]";
		}
	}
	echo implode(",",$data);?>
]; 
var customer_revenue_dataset = [
	{
		label: "Profit",
		data: customer_revenue_data,
		color: "#FF0000",
		points: { fillColor: "#FF0000", show: true },
		lines: { show: true }
	}
];
var customer_revenue_options = {
    series: {
        shadowSize: 5
    },
    xaxes: [{
        mode: "time",                
        tickFormatter: function (val, axis) {
            var date = new Date(val);
			return (date.getMonth() + 1) + '/' + date.getDate() + '/' +  date.getFullYear();
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
		tickFormatter : function(val, axis){
			return "$" + parseFloat(val).toLocaleString();
		},
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
    $.plot($("#flot-placeholder-customer-revenue"), customer_revenue_dataset, customer_revenue_options);
    $("#flot-placeholder-customer-revenue").UseTooltip();
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
                            "/" + date.getFullYear() + " : <strong>$" + parseFloat(y).toLocaleString() + "</strong>");
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