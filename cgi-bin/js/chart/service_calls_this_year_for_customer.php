<script>
function gd(year, month, day) {
        return new Date(year, month - 1, day).getTime();
    }
    var data5 = [
        <?php 
        $Vars = array();
        $r = sqlsrv_query($NEI,"
            SELECT
                Loc.Loc
            FROM 
                Loc
            WHERE
                Loc.Owner = '{$_GET['ID']}'
        ;");
        $Locations = array();
        while($array = sqlsrv_fetch_array($r)){$Locations[] = "TicketD.Loc='{$array['Loc']}'";}
        if(count($Locations) > 0){
            $SQL_Locations = implode(" OR ",$Locations);
            $SQL_Start_Date = date('Y-01-01 00:00:00.000');
            $SQL_End_Date = date('Y-m-t 23:59:59.999');
            $Tickets = array();
            $r = sqlsrv_query($NEI,"
                SELECT 
                    TicketD.ID,
                    TicketD.EDate 
                FROM
                    TicketD
                WHERE 
                    ({$SQL_Locations})
                    AND TicketD.EDate >= '{$SQL_Start_Date}'
                    AND TicketD.EDate <= '{$SQL_End_Date}'
                    AND TicketD.Level = 1
            ;");
            while($array = sqlsrv_fetch_array($r)){
                $Tickets[substr($array['EDate'],0,10)] = isset($Tickets[substr($array['EDate'],0,10)]) ? $Tickets[substr($array['EDate'],0,10)] + 1 : 1;
            }
            $r = sqlsrv_query($NEI,"
                SELECT
                    Loc.Loc
                FROM 
                    Loc
                WHERE
                    Loc.Owner = '{$_GET['ID']}'
            ;");
            $Locations = array();
            while($array = sqlsrv_fetch_array($r)){$Locations[] = "TicketO.LID='{$array['Loc']}'";}
            $r = sqlsrv_query($NEI,"
                SELECT 
                    TicketO.ID,
                    TicketO.EDate
                FROM
                    TicketO
                WHERE 
                    ({$SQL_Locations})
                    AND TicketO.EDate >= '{$SQL_Start_Date}'
                    AND TicketO.EDate <= '{$SQL_End_Date}'
                    AND TicketO.Level = 1
            ;");
            while($array = sqlsrv_fetch_array($r)){
                $Tickets[substr($array['EDate'],0,10)] = isset($Tickets[substr($array['EDate'],0,10)]) ? $Tickets[substr($array['EDate'],0,10)] + 1 : 1;
            }
            ksort($Tickets);
            foreach($Tickets as $Date=>$Total){
                $Year = substr($Date,0,4);
                $Month = substr($Date,5,2);
                $Day = substr($Date,8,2);  
                $Vars[] = "[gd({$Year},{$Month},{$Day}),{$Total}]";
            }
        }
        echo implode(",",$Vars)
        ?>
    ]; 
    var dataset5 = [
    {
        label: "Total Service Calls",
        data: data5,
        color: "#FF0000",
        points: { fillColor: "#FF0000", show: true },
        lines: { show: true }
    }
    ];

var options5 = {
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
    $.plot($("#flot-placeholder-service-calls"), dataset5, options5);
    $("#flot-placeholder-service-calls").UseTooltip();
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
                            " : <strong>" + y + "</strong>");
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