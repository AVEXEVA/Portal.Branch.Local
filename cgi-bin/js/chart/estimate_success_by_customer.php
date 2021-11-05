<script><?php
?>
function gd(year, month, day) {
        return new Date(year, month - 1, day).getTime();
    }
    var estimate_success_rate_data = [
        <?php 
        $data = array();
        $job_result = true;
        if($job_result){
            $dates = array();
            $totals = array();
            //if(FALSE){while($array = sqlsrv_fetch_array($job_result)){$Jobs[] = "[JOBLABOR].[JOB #]='{$array['ID']}'";}}
            //$Jobs = implode(" OR ",$Jobs);
            $Estimates = $database->query(null,"
                SELECT Estimate.ID     AS ID,
					   Estimate.fDate  AS Date,
				       Estimate.Status AS Status
				FROM   nei.dbo.Estimate
				       LEFT JOIN nei.dbo.Loc ON Estimate.LocID = Loc.Loc
				WHERE  Loc.Owner = ?
				ORDER BY Estimate.fDate ASC
            ;",array($_GET['ID']));
			$Totals  = array();
			$Successes = array();
            if($Estimates){
                while($Estimate = sqlsrv_fetch_array($Estimates)){
                    $date = substr($Estimate['Date'],0,10);
					$Totals[$date] = isset($Totals[$date]) ? $Totals[$date] + 1 : 1;
					if($Estimate['Status'] == 4){$Successes[$date] = isset($Successes[$date]) ? $Successes[$date] + 1 : 1;}
                }
            }
			ksort($Totals);
			$Total = 0;
			foreach($Totals as $date=>$value){
				$Total = $Total + $Totals[$date];
				$Totals[$date] = $Total;
				
			}
            ksort($Totals);
			$Success = 0;
			foreach($Totals as $date=>$value){
				if(isset($Successes[$date])){$Success = $Success + $Successes[$date];}
				$Totals[$date] = $Success / $value;
			}
            ksort($Totals);
            foreach($Totals as $date=>$rate){
                $Year = substr($date,0,4);
                $Month = substr($date,5,2);
                $Day = substr($date,8,2);
                $data[] = "[gd({$Year},{$Month},{$Day}),{$rate}]";
            }
        }
		
        echo implode(",",$data);?>
    ]; 
    var estimate_success_rate_dataset = [
    {
        label: "Profit",
        data: estimate_success_rate_data,
        color: "#FF0000",
        points: { fillColor: "#FF0000", show: true },
        lines: { show: true }
    }
    ];

var estimate_success_rate_options = {
    series: {
        shadowSize: 5
    },
    xaxes: [{
        mode: "time",                
        tickFormatter: function (val, axis) {
			var date = new Date(val);
            return date.getMonth()+ 1 + "/" + date.getDate() + "/" + (date.getYear() - 100);
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
    $.plot($("#flot-placeholder-estimate-success-rate-by-customer"), estimate_success_rate_dataset, estimate_success_rate_options);
    $("#flot-placeholder-estimate-success-rate-by-customer").UseTooltip();
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