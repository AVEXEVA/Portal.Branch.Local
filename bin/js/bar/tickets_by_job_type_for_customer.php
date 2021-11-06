<?php
$data = array();
$r = $database->query(null,"
	SELECT Sum(TicketD.Total) AS Total,
           JobType.Type  AS Job_Type
    FROM   nei.dbo.TicketD
           LEFT JOIN nei.dbo.Job     ON TicketD.Job = Job.ID
           LEFT JOIN nei.dbo.JobType ON Job.Type    = JobType.ID
    WHERE  Job.Owner = ?
		   AND Job.Type <> 9
		   AND Job.Type <> 12
	GROUP BY JobType.Type
;",array($_GET['ID']));
/*$Units = array();
if($r){while($array = sqlsrv_fetch_array($r)){$Units[$array['Job_Type']] = (isset($Units[$array['Job_Type']])) ? $Units[$array['Job_Type']] + $array['Total'] : $array['Total'];}}
$data = array();
if(count($Units) > 0){
    foreach($Units as $Unit=>$Total){$data[] = "['{$Unit}',{$Total}]";}
}*/?>
<script>
var owner = '<?php echo $_GET['ID'];?>';
<?php
$data = array();
while($array = sqlsrv_fetch_array($r)){$data[] = $array;}
$data3 = array();
$data4 = array();
if(count($data) > 0){foreach($data as $key=>$value){
	$data2[] = "[{$key},{$value['Total']}]";
	$data3[] = "[{$key},'{$value['Job_Type']}']";
}}
?>
var data66 = [<?php echo implode(",",$data2);?>];
var ticks = [<?php echo implode(",",$data3);?>];
var dataset66 = [
	{label:"Ticket Hours by Job Type",data:data66,color:"#5482FF"}
];
//var ticks = [[0, "0 to 30"], [1, "30 to 60"], [2, "60 to 90"], [3, "90 to 120"],[4, "120+"]];
var options66 = {
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
        axisLabelPadding: 10,
        ticks: ticks
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
    $.plot($("#flot-placeholder-bar-graph-job-type-hours"), dataset66, options66);
    //$("#flot-placeholder-bar-graph").UseTooltip();
});

function gd(year, month, day) {
    return new Date(year, month, day).getTime();
}
</script>