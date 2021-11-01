<script>
function gd(year, month, day) {return new Date(year, month - 1, day).getTime();}
var job_profit_dollar_ratio_data = [<?php 
	$Year = 2012;
	$Month = 12;
	$Day = 31;
	if($Total_Revenue_2012 != 0){
		$Ratio = $Total_Profit_2012 / $Total_Revenue_2012;
		echo "[gd({$Year},{$Month},{$Day}),{$Ratio}],";
	}
		$Year++;
	if($Total_Revenue_2013 != 0){
		$Ratio = $Total_Profit_2013 / $Total_Revenue_2013;
		echo "[gd({$Year},{$Month},{$Day}),{$Ratio}],";
	}
	$Year++;
	if($Total_Revenue_2014 != 0){
		$Ratio = $Total_Profit_2014 / $Total_Revenue_2014;
		echo "[gd({$Year},{$Month},{$Day}),{$Ratio}],";
	}
	$Year++;
	if($Total_Revenue_2015 != 0){
		$Ratio = $Total_Profit_2015 / $Total_Revenue_2015;
		echo "[gd({$Year},{$Month},{$Day}),{$Ratio}],";
	}
	$Year++;
	if($Total_Revenue_2016 != 0){
		$Ratio = $Total_Profit_2016 / $Total_Revenue_2016;
		echo "[gd({$Year},{$Month},{$Day}),{$Ratio}],";
	}
	$Year++;
	if($Total_Revenue_2017 != 0){
		$Month = date("m");
		$Day = date("d");
		$Ratio = $Total_Profit_2017 / $Total_Revenue_2017;
		echo "[gd({$Year},{$Month},{$Day}),{$Ratio}]";
	}
?>]; 
var job_profit_dollar_ratio_dataset = [
	{
		label: "Profit Billed Ratio",
		data: job_profit_dollar_ratio_data,
		color: "#FF0000",
		points: { fillColor: "#FF0000", show: true },
		lines: { show: true }
	}
];
var job_profit_dollar_ratio_options = {
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
    $.plot($("#flot-placeholder-profit-billed-ratio"), job_profit_dollar_ratio_dataset, job_profit_dollar_ratio_options);
    $("#flot-placeholder-profit-dollar-ratio").UseTooltipRatio();
});
var previousPoint = null, previousLabel = null;
var monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
$.fn.UseTooltipRatio = function () {
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