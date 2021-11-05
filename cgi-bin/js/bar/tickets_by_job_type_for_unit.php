<?php
$data = array();
$r = $database->query(null,"
	SELECT 
        TicketD.Total AS Total,
        JobType.Type AS Job_Type
    FROM
        (TicketD
        LEFT JOIN nei.dbo.Job ON TicketD.Job = Job.ID)
        LEFT JOIN nei.dbo.JobType ON Job.Type = JobType.ID
    WHERE 
        TicketD.Elev = '{$_GET['ID']}'
;");
$Units = array();
if($r){while($array = sqlsrv_fetch_array($r)){$Units[$array['Job_Type']] = (isset($Units[$array['Job_Type']])) ? $Units[$array['Job_Type']] + $array['Total'] : $array['Total'];}}
$data = array();
if(count($Units) > 0){
    foreach($Units as $Unit=>$Total){$data[] = "['{$Unit}',{$Total}]";}
}?>
<script>
var data6 = [<?php
echo implode(",",$data);
?>];
var dataset6 = [
	{label:"Ticket Hours by Job Type",data:data6,color:"#5482FF"}
];
var options6 = {
    series: {
        bars: {
            show: true
        }
    },
    bars: {
        align: "center",
        barWidth: 0.5
    },
    xaxis: {
        axisLabel: "World Cities",
        //axisLabelUseCanvas: true,
        axisLabelFontSizePixels: 12,
        axisLabelFontFamily: 'Verdana, Arial',
        mode:"categories",
        axisLabelPadding: 10//,
        //ticks: ticks
    },
    yaxis: {
        axisLabel: "Average Temperature",
        axisLabelUseCanvas: true,
        axisLabelFontSizePixels: 12,
        axisLabelFontFamily: 'Verdana, Arial',
        axisLabelPadding: 3,
        tickFormatter: function (v, axis) {
            return v + " hrs";
        }
    },
    legend: {
        noColumns: 0,
        labelBoxBorderColor: "#000000",
        position: "nw"
    },
    grid: {
        hoverable: true,
        borderWidth: 2,
        backgroundColor: { colors: ["#ffffff", "#EDF5FF"] }
    }
};

$(document).ready(function () {
    $.plot($("#flot-placeholder-bar-graph"), dataset6, options6);
    //$("#flot-placeholder-bar-graph").UseTooltip();
});

function gd(year, month, day) {
    return new Date(year, month, day).getTime();
}
</script>