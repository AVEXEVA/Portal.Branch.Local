<?php
$data = array();
$r = sqlsrv_query($NEI,"
	SELECT 
        TicketD.ID,
        TicketD.EDate,
        TicketD.Total,
        Elev.State AS Unit_State
    FROM
        TicketD
        LEFT JOIN nei.dbo.Elev ON TicketD.Elev = Elev.ID
    WHERE 
        TicketD.Loc = '{$_GET['ID']}'
;");
$Units = array();
if($r){while($array = sqlsrv_fetch_array($r)){$Units[$array['Unit_State']] = (isset($Units[$array['Unit_State']])) ? $Units[$array['Unit_State']] + $array['Total'] : $array['Total'];}}
$data = array();
if(count($Units) > 0){
    foreach($Units as $Unit=>$Total){if($Unit == ""){$Unit = "Unlisted";}$data[] = "['{$Unit}',{$Total}]";}
}?>
<script>
var data6 = [<?php
echo implode(",",$data);
?>];
var dataset6 = [
	{label:"Ticket Hours by Unit",data:data6,color:"#5482FF"}
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