<script><?php
?>
function gd(year, month, day) {
        return new Date(year, month - 1, day).getTime();
    }
    <?php 
        $data = array();
        $job_result = true;
        if($job_result){
            $Jobs = array();
            $dates = array();
            $totals = array();
            if(FALSE){while($array = sqlsrv_fetch_array($job_result)){$Jobs[] = "[JOBLABOR].[JOB #]='{$array['ID']}'";}}
            $Jobs = implode(" OR ",$Jobs);
            $invoice_result = sqlsrv_query($NEI,"
                SELECT 
                    Invoice.Amount AS Amount,
                    Invoice.fDate as fDate,
                    Job.Custom1 AS Supervisor
                FROM 
                    (Invoice
                    LEFT JOIN nei.dbo.Loc ON Invoice.Loc = Loc.Loc)
                    LEFT JOIN nei.dbo.Job ON Invoice.Job = Job.ID
                WHERE 
                    Job.Type = '2'
                    AND Invoice.fDate >= '2013-01-01 00:00:00.000'
            ;");
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
                    $Division = $array['Supervisor'];
                    $Division = str_replace(' ', '', $Division);
                    if(!isset($dates[$Division])){$dates[$Division] = array();}
                    $dates[$Division][$date] = (isset($dates[$Division][$date])) ? $dates[$Division][$date] + ($array['Amount'] * (1 - $Overhead)): $array['Amount'] * (1 - $Overhead);
                }
            }
            $job_item_result = sqlsrv_query($Paradox,"
                SELECT 
                    [JOBLABOR].[WEEK ENDING]    AS fDate,
                    [JOBLABOR].[TOTAL COST]     AS Amount,
                    Job.Custom1 AS Supervisor
                FROM 
                    (nei.dbo.Job as Job
                    LEFT JOIN Paradox.dbo.JOBLABOR AS JOBLABOR ON Job.ID = [JOBLABOR].[JOB #])
                    LEFT JOIN nei.dbo.Loc AS Loc ON Job.Loc = Loc.Loc
                WHERE 
                    Job.Type = '2'
                    AND convert(date,[WEEK ENDING]) >= '2013-01-01 00:00:00.000'
                    AND convert(date,[WEEK ENDING]) < '2017-03-30 00:00:00.000'
            ;");
            if($job_item_result){
                while($array = sqlsrv_fetch_array($job_item_result)){
                    $Division = $array['Supervisor'];
                    $Division = str_replace(' ', '', $Division);
                    if(!isset($dates[$Division])){$dates[$Division] = array();}
                    $date = substr($array['fDate'],0,10);
                    $dates[$Division][$date] = (isset($dates[$Division][$date])) ? $dates[$Division][$date] - floatval($array['Amount']) : 0 - floatval($array['Amount']);
                }
            }
            ksort($dates);
            
            $job_item_result = sqlsrv_query($NEI,"
                SELECT 
                    JobI.Amount AS Amount, 
                    JobI.fDate as fDate,
                    Job.Custom1 AS Supervisor
                FROM 
                    Loc 
                    LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                    LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                WHERE  
                    Job.Type = '2'
                    AND JobI.Type='1'
                    AND JobI.fDate >= '2017-03-30 00:00:00.000'
            ;");
            if($job_item_result){
                while($array = sqlsrv_fetch_array($job_item_result)){
                    $Division = $array['Supervisor'];
                    $Division = str_replace(' ', '', $Division);
                    if(!isset($dates[$Division])){$dates[$Division] = array();}
                    $date = substr($array['fDate'],0,10);
                    $dates[$Division][$date] = (isset($dates[$Division][$date])) ? $dates[$Division][$date] - floatval($array['Amount']) : 0 - floatval($array['Amount']);
                }
            }
            ksort($dates);
            $job_item_result = sqlsrv_query($NEI,"
                SELECT 
                    JobI.Amount AS Amount,
                    JobI.fDate as fDate,
                    Job.Custom1 AS Supervisor
                FROM 
                    Loc 
                    LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                    LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                WHERE 
                    Job.Type = '2'
                    AND JobI.Type='1'
                    AND JobI.Labor='1'
                    AND JobI.fDate < '2017-03-30 00:00:00.000'
                    AND JobI.fDate >= '2013-01-01 00:00:00.000'
                ORDER BY JobI.fDate ASC
            ;");
            if($job_item_result){
                while($array = sqlsrv_fetch_array($job_item_result)){
                    $Division = $array['Supervisor'];
                    $Division = str_replace(' ', '', $Division);
                    if(!isset($dates[$Division])){$dates[$Division] = array();}
                    $date = substr($array['fDate'],0,10);
                    $dates[$Division][$date] = (isset($dates[$Division][$date])) ? $dates[$Division][$date] + floatval($array['Amount']) : 0 + floatval($array['Amount']);
                }
            }
            ksort($dates);
            $job_item_result = sqlsrv_query($NEI,"
                SELECT 
                    JobI.Amount AS Amount,
                    JobI.fDate as fDate,
                    Job.Custom1 AS Supervisor
                FROM 
                    Loc 
                    LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                    LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                WHERE 
                    Job.Type = '2'
                    AND JobI.Type='1'
                    AND JobI.fDate < '2017-03-30 00:00:00.000'
                    AND JobI.fDate >= '2013-01-01 00:00:00.000'
                ORDER BY JobI.fDate ASC
            ;");
            if($job_item_result){
                while($array = sqlsrv_fetch_array($job_item_result)){
                    $Division = $array['Supervisor'];
                    $Division = str_replace(' ', '', $Division);
                    if(!isset($dates[$Division])){$dates[$Division] = array();}
                    $date = substr($array['fDate'],0,10);
                    $dates[$Division][$date] = (isset($dates[$Division][$date])) ? $dates[$Division][$date] - floatval($array['Amount']) : 0 - floatval($array['Amount']);
                }
            }
            ksort($dates);
            foreach($dates as $Division=>$array){
                $totals[$Division] = array();
                $total = 0;
                ksort($array);
                foreach($array as $date=>$value){
                    $total = $total + $value;
                    $totals[$Division][$date] = $total;
                }
            }
            foreach($totals as $Division=>$array){
                $data5 = array();
                ?>var data_modernization_profit_by_<?php echo $Division;?> = [<?php
                foreach($array as $Date=>$Total){
                    $Year = substr($Date,0,4);
                    $Month = substr($Date,5,2);
                    $Day = substr($Date,8,2);
                    $data5[] = "[gd({$Year},{$Month},{$Day}),{$Total}]";
                }
                echo implode(",",$data5);
                ?>];<?php 
            }?>
            var dataset_modernization_profit_by_supervisor = [<?php 
            $i = 0;
            foreach($totals as $Division=>$array){
                $i++;
                $color = dechex(rand(0x000000, 0xFFFFFF));
                ?>{
                    label: "<?php echo $Division;?>",
                    data: data_modernization_profit_by_<?php echo $Division;?>,
                    color: "#<?php echo $color;?>",
                    points: { fillColor: "#<?php echo $color;?>", show: true },
                    lines: { show: true }
                }<?php 
                if($i != count($totals)){?>,<?php }?><?php 
            }?>];<?php
        }?>
var options_modernization_profit_by_supervisor = {
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
    $.plot($("#flot-placeholder-modernization-profit-by-supervisor"), dataset_modernization_profit_by_supervisor, options_modernization_profit_by_supervisor);
    $("#flot-placeholder-modernization-profit-by-supervisor").UseTooltip();
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