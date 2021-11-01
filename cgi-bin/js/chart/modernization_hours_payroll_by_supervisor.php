<script><?php
?>
function gd(year, month, day) {
        return new Date(year, month - 1, day).getTime();
    }
    <?php 
        $data = array();
        $data2 = array();
        $data3 = array();
        $job_result = sqlsrv_query($NEI,"
            SELECT Job.Custom1 AS Supervisor, ((TicketD.Reg * PRWage.Reg) + (TicketD.OT * PRWage.OT1) + (TicketD.DT * PRWage.OT2) + (TicketD.TT * PRWage.Reg)) as Total, TicketD.EDate AS Dated
            FROM 
                (((TicketD 
                LEFT JOIN nei.dbo.Job ON Job.ID = TicketD.Job) 
                LEFT JOIN nei.dbo.Loc ON Job.Loc = Loc.Loc)
                LEFT JOIN nei.dbo.Emp ON TicketD.fWork = Emp.fWork)
                LEFT JOIN PRWage ON Emp.WageCat = PRWage.ID
            WHERE Job.Type='2' AND TicketD.EDate >= '2017-03-30 00:00:00.000' AND TicketD.Total <= 24 AND TicketD.Total > 0
        ;");
        if($job_result){
            $Jobs = array();
            while($Job = sqlsrv_fetch_array($job_result)){$Jobs[] = $Job;}
            foreach($Jobs as $Job){
                if($Job['Total'] == ''){continue;}
                $Division = $Job['Supervisor'];
                if(!isset($data3[$Division])){$data3[$Division] = array();}
                $Job['Dated'] = substr($Job['Dated'],0,10);
                $data3[$Division][$Job['Dated']] = isset($data3[$Division][$Job['Dated']]) ? $data3[$Division][$Job['Dated']] + $Job['Total'] : $Job['Total'];  
            }
            foreach($data3 as $Division=>$array){
                ksort($array);
                $data = array();
                ?>var data_modernization_hours_payroll_by_<?php echo $Division;?> = [<?php 
                    foreach($array as $Date=>$Total){
                        $Year = substr($Date,0,4);
                        $Month = substr($Date,5,2);
                        $Day = substr($Date,8,2);
                        $data[] = "[gd({$Year},{$Month},{$Day}),{$Total}]";
                    }
                    echo implode(",",$data);
                ?>];<?php 
            }
        }?>
    var dataset_modernization_hours_payroll_by_supervisor = [<?php 
        $i = 0;
        foreach($data3 as $Division=>$array){
            $i++;
            $color = dechex(rand(0x000000, 0xFFFFFF));
            ?>{
                label: "<?php echo $Division;?>",
                data: data_modernization_hours_payroll_by_<?php echo $Division;?>,
                color: "#<?php echo $color;?>",
                points: { fillColor: "#<?php echo $color;?>", show: true },
                lines: { show: true }
            }<?php 
            if($i != count($data3)){?>,<?php }?><?php 
        }?>];
var options_modernization_hours_payroll_by_supervisor = {
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
    $.plot($("#flot-placeholder-modernization-hours-payroll-by-supervisor"), dataset_modernization_hours_payroll_by_supervisor, options_modernization_hours_payroll_by_supervisor);
    $("#flot-placeholder-modernization-hours-payroll-by-supervisor").UseTooltip();
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