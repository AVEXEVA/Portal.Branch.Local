<script>
var actions_data = [
	<?php
	$data = array();
	$dates = array();
	$totals = array();
	$r = sqlsrv_query($NEI,"
		SELECT ID,
			   Date AS Timestamped
		FROM   Portal.dbo.Activity
	;");
	if($r){
		while($array = sqlsrv_fetch_array($r)){
			$date = substr($array['Timestamped'],0,10);
			$dates[$date] = (isset($dates[$date])) ? $dates[$date] + 1 : 1;
		}
	}
	ksort($dates);
	$total = 0;
	foreach($dates as $date=>$value){
		$total = $total + $value;
		$totals[$date] = $value;
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
	echo implode(",",$data);?>
];
var actions_dataset = [
	{
		label: "Actions",
		data: actions_data,
		color: "#FF0000",
		points: { fillColor: "#FF0000", show: true },
		lines: { show: true }
	}
];
var actions_options = {
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
			return parseFloat(val).toLocaleString();
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
    $.plot($("#flot-actions"), actions_dataset, actions_options);
    $("#flot-actions").UseTooltip();
});
function gd(year, month, day) {
    return new Date(year, month - 1, day).getTime();
}
var previousPoint = null, previousLabel = null;
var monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
</script>
